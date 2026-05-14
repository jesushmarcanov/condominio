<?php
/**
 * Script de Cálculo Automático de Mora
 * 
 * Ejecuta el cálculo diario de recargos por mora para pagos atrasados.
 * Debe ejecutarse mediante cron job diariamente.
 * 
 * Uso: php calculate_late_fees.php
 * 
 * Configuración de cron recomendada:
 * 0 2 * * * php /ruta/completa/al/proyecto/calculate_late_fees.php >> /ruta/logs/late_fees_cron.log 2>&1
 * (Ejecutar diariamente a las 2:00 AM)
 */

define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Cargar variables de entorno
try {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
} catch (Exception $e) {
    error_log("[calculate_late_fees.php] .env file error: " . $e->getMessage());
}

// Registrar inicio
error_log("[calculate_late_fees.php] ========================================");
error_log("[calculate_late_fees.php] Inicio: " . date('Y-m-d H:i:s'));

try {
    // Cargar configuración
    require_once CONFIG_PATH . '/database.php';
    
    // Cargar clases
    require_once APP_PATH . '/models/Database.php';
    require_once APP_PATH . '/models/Payment.php';
    require_once APP_PATH . '/models/Resident.php';
    require_once APP_PATH . '/models/Notification.php';
    require_once APP_PATH . '/models/LateFeeRule.php';
    require_once APP_PATH . '/models/LateFeeHistory.php';
    require_once APP_PATH . '/services/EmailService.php';
    require_once APP_PATH . '/services/NotificationService.php';
    require_once APP_PATH . '/services/LateFeeService.php';
    
    // Conectar a base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    error_log("[calculate_late_fees.php] Conexión establecida");
    
    // Instanciar servicio
    $lateFeeService = new LateFeeService($db);
    
    // Ejecutar cálculo
    error_log("[calculate_late_fees.php] Iniciando cálculo de mora...");
    $result = $lateFeeService->processOverduePayments();
    
    // Registrar resultado
    if ($result['success']) {
        error_log("[calculate_late_fees.php] Completado exitosamente");
        error_log("[calculate_late_fees.php] Pagos procesados: " . $result['processed']);
        error_log("[calculate_late_fees.php] Mora aplicada: " . $result['late_fees_applied']);
        error_log("[calculate_late_fees.php] Notificaciones enviadas: " . $result['notifications_sent']);
        
        if (isset($result['errors']) && $result['errors'] > 0) {
            error_log("[calculate_late_fees.php] Errores: " . $result['errors']);
        }
        
        error_log("[calculate_late_fees.php] Fin: " . date('Y-m-d H:i:s'));
        error_log("[calculate_late_fees.php] ========================================");
        exit(0);
    } else {
        error_log("[calculate_late_fees.php] Error en el procesamiento: " . ($result['error'] ?? 'Error desconocido'));
        error_log("[calculate_late_fees.php] Fin: " . date('Y-m-d H:i:s'));
        error_log("[calculate_late_fees.php] ========================================");
        exit(1);
    }
    
} catch (Exception $e) {
    error_log("[calculate_late_fees.php] EXCEPCIÓN: " . $e->getMessage());
    error_log("[calculate_late_fees.php] Stack trace: " . $e->getTraceAsString());
    error_log("[calculate_late_fees.php] Fin: " . date('Y-m-d H:i:s'));
    error_log("[calculate_late_fees.php] ========================================");
    exit(1);
}
?>
