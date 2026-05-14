<?php
/**
 * Script de prueba para el sistema de emails
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
    $dotenv->safeLoad(); // Usar safeLoad en lugar de load
    echo "✓ Variables de entorno cargadas\n";
} catch (Exception $e) {
    echo "✗ Error cargando .env: " . $e->getMessage() . "\n";
    exit(1);
}

// Mostrar configuración
echo "\n=== Configuración de Email ===\n";
echo "MAIL_DRIVER: " . ($_ENV['MAIL_DRIVER'] ?? getenv('MAIL_DRIVER') ?? '(no definido)') . "\n";
echo "MAIL_HOST: " . ($_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST') ?? '(no definido)') . "\n";
echo "MAIL_PORT: " . ($_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT') ?? '(no definido)') . "\n";
echo "MAIL_USERNAME: " . (($_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME')) ? '***' : '(vacío)') . "\n";
echo "MAIL_PASSWORD: " . (($_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD')) ? '***' : '(vacío)') . "\n";
echo "MAIL_FROM_ADDRESS: " . ($_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?? '(no definido)') . "\n";
echo "MAIL_FROM_NAME: " . ($_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?? '(no definido)') . "\n";
echo "MAIL_TEST_MODE: " . ($_ENV['MAIL_TEST_MODE'] ?? getenv('MAIL_TEST_MODE') ?? '(no definido)') . "\n";
echo "==============================\n\n";

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

// Verificar si el servicio está habilitado
if ($emailService->isEnabled()) {
    echo "✓ EmailService está habilitado\n\n";
} else {
    echo "✗ EmailService está deshabilitado\n\n";
    exit(1);
}

// Probar envío de email
echo "=== Enviando email de prueba ===\n";
$result = $emailService->sendHtmlEmail(
    'test@example.com',
    'Email de Prueba - CondoWeb',
    '<h1>Prueba de Email</h1><p>Este es un email de prueba del sistema CondoWeb.</p><p>Fecha: ' . date('Y-m-d H:i:s') . '</p>'
);

if ($result['success']) {
    echo "✓ Email enviado exitosamente\n";
    echo "  Mensaje: " . $result['message'] . "\n";
    
    if (getenv('MAIL_TEST_MODE') === 'true') {
        echo "\n📁 El email fue guardado en: logs/emails/email_" . date('Y-m-d') . ".log\n";
    }
} else {
    echo "✗ Error al enviar email\n";
    echo "  Error: " . $result['error'] . "\n";
}

echo "\n=== Prueba completada ===\n";
?>
