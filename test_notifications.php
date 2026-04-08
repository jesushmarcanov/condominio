<?php
/**
 * Script de Prueba para Verificar el Sistema de Notificaciones
 * 
 * Este script verifica:
 * 1. Conexión a la base de datos
 * 2. Existencia de la tabla notificaciones
 * 3. Datos de prueba
 * 4. Funcionalidad del modelo Notification
 * 
 * Uso: php test_notifications.php
 */

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

echo "========================================\n";
echo "Test del Sistema de Notificaciones\n";
echo "========================================\n\n";

try {
    // Cargar configuración
    require_once CONFIG_PATH . '/database.php';
    require_once APP_PATH . '/models/Database.php';
    require_once APP_PATH . '/models/Notification.php';
    
    // Conectar a la base de datos
    echo "1. Probando conexión a base de datos...\n";
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    echo "   ✓ Conexión exitosa\n\n";
    
    // Verificar tabla notificaciones
    echo "2. Verificando tabla notificaciones...\n";
    $query = "SHOW TABLES LIKE 'notificaciones'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        throw new Exception("La tabla 'notificaciones' no existe");
    }
    echo "   ✓ Tabla existe\n\n";
    
    // Contar notificaciones existentes
    echo "3. Contando notificaciones existentes...\n";
    $query = "SELECT COUNT(*) as total FROM notificaciones";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total de notificaciones: " . $result['total'] . "\n\n";
    
    // Verificar usuarios
    echo "4. Verificando usuarios...\n";
    $query = "SELECT id, nombre, email, rol FROM usuarios LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) == 0) {
        echo "   ⚠ No hay usuarios en el sistema\n\n";
    } else {
        echo "   Usuarios encontrados:\n";
        foreach ($users as $user) {
            echo "   - ID: {$user['id']}, Nombre: {$user['nombre']}, Email: {$user['email']}, Rol: {$user['rol']}\n";
        }
        echo "\n";
    }
    
    // Crear notificación de prueba
    echo "5. Creando notificación de prueba...\n";
    if (count($users) > 0) {
        $notification = new Notification($db);
        $notification->usuario_id = $users[0]['id'];
        $notification->titulo = "Notificación de Prueba";
        $notification->mensaje = "Esta es una notificación de prueba creada por el script test_notifications.php";
        $notification->tipo = "info";
        $notification->leida = false;
        
        if ($notification->create()) {
            echo "   ✓ Notificación creada con ID: " . $notification->id . "\n\n";
        } else {
            echo "   ✗ Error al crear notificación\n\n";
        }
    } else {
        echo "   ⚠ No se puede crear notificación sin usuarios\n\n";
    }
    
    // Listar notificaciones
    echo "6. Listando todas las notificaciones...\n";
    $query = "SELECT n.*, u.nombre, u.email 
              FROM notificaciones n
              LEFT JOIN usuarios u ON n.usuario_id = u.id
              ORDER BY n.created_at DESC
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($notifications) == 0) {
        echo "   ⚠ No hay notificaciones en el sistema\n\n";
    } else {
        echo "   Notificaciones encontradas:\n";
        foreach ($notifications as $notif) {
            $leida = $notif['leida'] ? 'Leída' : 'No leída';
            echo "   - ID: {$notif['id']}, Usuario: {$notif['nombre']}, Título: {$notif['titulo']}, Estado: {$leida}\n";
        }
        echo "\n";
    }
    
    // Verificar pagos vencidos
    echo "7. Verificando pagos vencidos...\n";
    $query = "SELECT COUNT(*) as total 
              FROM pagos 
              WHERE (estado = 'pendiente' OR estado = 'atrasado') 
              AND fecha_pago < CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Pagos vencidos encontrados: " . $result['total'] . "\n\n";
    
    if ($result['total'] > 0) {
        echo "   Detalles de pagos vencidos:\n";
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email 
                  FROM pagos p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE (p.estado = 'pendiente' OR p.estado = 'atrasado')
                  AND p.fecha_pago < CURDATE()
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($payments as $payment) {
            echo "   - Pago ID: {$payment['id']}, Residente: {$payment['nombre']}, Concepto: {$payment['concepto']}, Monto: \${$payment['monto']}, Estado: {$payment['estado']}\n";
        }
        echo "\n";
    }
    
    echo "========================================\n";
    echo "Test completado exitosamente\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
