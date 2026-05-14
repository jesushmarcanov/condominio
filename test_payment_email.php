<?php
/**
 * Script de prueba para emails de pagos vencidos
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
echo "✓ EmailService está habilitado\n\n";

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
echo "Residente: {$variables['resident_name']}\n";
echo "Apartamento: {$variables['apartment']} - Torre {$variables['tower']}\n";
echo "Concepto: {$variables['payment_concept']}\n";
echo "Monto: \${$variables['payment_amount']}\n";
echo "Fecha de vencimiento: {$variables['due_date']}\n\n";

// Cargar plantilla y enviar email
$html_body = $emailService->loadTemplate('payment_notification', $variables);
$subject = 'Pago Vencido - ' . $variables['payment_concept'];

$result = $emailService->sendHtmlEmail(
    'residente@example.com',
    $subject,
    $html_body
);

if ($result['success']) {
    echo "✓ Email enviado exitosamente\n";
    echo "  Mensaje: " . $result['message'] . "\n";
    
    if (($_ENV['MAIL_TEST_MODE'] ?? 'false') === 'true') {
        echo "\n📁 El email fue guardado en: logs/emails/email_" . date('Y-m-d') . ".log\n";
        echo "\n=== Contenido del email ===\n";
        echo "Para: residente@example.com\n";
        echo "Asunto: $subject\n";
        echo "---\n";
        echo substr($html_body, 0, 500) . "...\n";
    }
} else {
    echo "✗ Error al enviar email\n";
    echo "  Error: " . $result['error'] . "\n";
}

echo "\n=== Prueba completada ===\n";
?>
