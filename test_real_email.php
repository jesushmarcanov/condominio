<?php
/**
 * Script de prueba para envío real de emails
 */

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar autoloader de Composer
require_once ROOT_PATH . '/vendor/autoload.php';

// Cargar variables de entorno
try {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->safeLoad();
    echo "✓ Variables de entorno cargadas\n";
} catch (Exception $e) {
    echo "✗ Error cargando .env: " . $e->getMessage() . "\n";
    exit(1);
}

// Cargar clases necesarias
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Database.php';
require_once APP_PATH . '/services/EmailService.php';

// Inicializar conexión a base de datos
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "✗ Error: No se pudo conectar a la base de datos\n";
    exit(1);
}
echo "✓ Conexión a base de datos establecida\n\n";

// Crear instancia de EmailService
$emailService = new EmailService($db);

if (!$emailService->isEnabled()) {
    echo "✗ EmailService está deshabilitado\n";
    exit(1);
}
echo "✓ EmailService está habilitado\n";

// Verificar modo
$testMode = ($_ENV['MAIL_TEST_MODE'] ?? 'false') === 'true';
if ($testMode) {
    echo "⚠️  MODO DE PRUEBA ACTIVADO - Los emails se guardarán en logs\n\n";
} else {
    echo "📧 MODO PRODUCCIÓN - Los emails se enviarán realmente\n\n";
}

// Datos de prueba para email de pago vencido
$variables = [
    'resident_name' => 'Juan Pérez',
    'apartment' => '101',
    'tower' => 'A',
    'payment_concept' => 'Cuota de Mantenimiento',
    'payment_amount' => '1,500.00',
    'payment_month' => '2026-03',
    'due_date' => '15/03/2026',
    'reference' => 'PAG-2026-03-001',
    'type' => 'overdue'
];

echo "=== Enviando email de pago vencido ===\n";
echo "Para: jhmarcano@gmail.com\n";
echo "Residente: {$variables['resident_name']}\n";
echo "Apartamento: {$variables['apartment']} - Torre {$variables['tower']}\n";
echo "Concepto: {$variables['payment_concept']}\n";
echo "Monto: \${$variables['payment_amount']}\n";
echo "Fecha de vencimiento: {$variables['due_date']}\n\n";

echo "Enviando...\n";

// Cargar plantilla y enviar email
$html_body = $emailService->loadTemplate('payment_notification', $variables);
$subject = 'Pago Vencido - ' . $variables['payment_concept'];

$result = $emailService->sendHtmlEmail(
    'jhmarcano@gmail.com',
    $subject,
    $html_body
);

if ($result['success']) {
    echo "\n✅ Email enviado exitosamente\n";
    echo "  Mensaje: " . $result['message'] . "\n";
    
    if ($testMode) {
        echo "\n📁 El email fue guardado en: logs/emails/email_" . date('Y-m-d') . ".log\n";
    } else {
        echo "\n📬 El email fue enviado a: jhmarcano@gmail.com\n";
        echo "   Por favor revisa tu bandeja de entrada (y spam si no lo ves)\n";
    }
} else {
    echo "\n✗ Error al enviar email\n";
    echo "  Error: " . $result['error'] . "\n";
    echo "\n💡 Posibles causas:\n";
    echo "  - Credenciales incorrectas\n";
    echo "  - Gmail bloqueó el acceso (necesitas habilitar 'Acceso de apps menos seguras' o usar contraseña de aplicación)\n";
    echo "  - Firewall bloqueando el puerto 587\n";
}

echo "\n=== Prueba completada ===\n";
?>
