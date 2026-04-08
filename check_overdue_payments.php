<?php
/**
 * Script de Detección Automática de Pagos Vencidos
 * 
 * Este script se ejecuta desde línea de comandos para detectar pagos vencidos
 * y generar notificaciones automáticas.
 * 
 * Uso: php check_overdue_payments.php
 * 
 * Configuración recomendada de cron:
 * 0 8 * * * php /ruta/al/proyecto/check_overdue_payments.php
 * (Ejecutar diariamente a las 8:00 AM)
 */

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Registrar inicio de ejecución
error_log("[check_overdue_payments.php] ========================================");
error_log("[check_overdue_payments.php] Inicio de ejecución: " . date('Y-m-d H:i:s'));

try {
    // Cargar configuración de base de datos
    require_once CONFIG_PATH . '/database.php';
    
    // Cargar clases necesarias
    require_once APP_PATH . '/models/Database.php';
    require_once APP_PATH . '/models/Payment.php';
    require_once APP_PATH . '/models/Resident.php';
    require_once APP_PATH . '/models/Notification.php';
    require_once APP_PATH . '/services/NotificationService.php';
    
    // Inicializar conexión a base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    error_log("[check_overdue_payments.php] Conexión a base de datos establecida");
    
    // Instanciar NotificationService
    $notificationService = new NotificationService($db);
    
    // Ejecutar procesamiento de pagos vencidos
    error_log("[check_overdue_payments.php] Iniciando procesamiento de pagos vencidos...");
    $result = $notificationService->processOverduePayments();
    
    // Registrar resultado
    if ($result['success']) {
        error_log("[check_overdue_payments.php] Procesamiento completado exitosamente");
        error_log("[check_overdue_payments.php] Pagos procesados: " . $result['processed']);
        error_log("[check_overdue_payments.php] Notificaciones creadas: " . $result['notifications_created']);
        error_log("[check_overdue_payments.php] Pagos actualizados: " . $result['payments_updated']);
        
        if (isset($result['errors']) && $result['errors'] > 0) {
            error_log("[check_overdue_payments.php] Errores encontrados: " . $result['errors']);
        }
        
        error_log("[check_overdue_payments.php] Fin de ejecución: " . date('Y-m-d H:i:s'));
        error_log("[check_overdue_payments.php] ========================================");
        
        // Retornar código de éxito
        exit(0);
        
    } else {
        error_log("[check_overdue_payments.php] ERROR: Procesamiento falló");
        error_log("[check_overdue_payments.php] Mensaje de error: " . ($result['error'] ?? 'Error desconocido'));
        error_log("[check_overdue_payments.php] Fin de ejecución: " . date('Y-m-d H:i:s'));
        error_log("[check_overdue_payments.php] ========================================");
        
        // Retornar código de error
        exit(1);
    }
    
} catch (Exception $e) {
    // Manejo de errores sin interrumpir procesamiento
    error_log("[check_overdue_payments.php] EXCEPCIÓN CAPTURADA: " . $e->getMessage());
    error_log("[check_overdue_payments.php] Stack trace: " . $e->getTraceAsString());
    error_log("[check_overdue_payments.php] Fin de ejecución: " . date('Y-m-d H:i:s'));
    error_log("[check_overdue_payments.php] ========================================");
    
    // Retornar código de error
    exit(1);
}
?>
