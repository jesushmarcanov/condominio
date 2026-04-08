<?php
/**
 * Script para Crear Datos de Prueba
 * 
 * Crea pagos vencidos para probar el sistema de notificaciones
 * 
 * Uso: php create_test_data.php
 */

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

echo "========================================\n";
echo "Creando Datos de Prueba\n";
echo "========================================\n\n";

try {
    // Cargar configuración
    require_once CONFIG_PATH . '/database.php';
    require_once APP_PATH . '/models/Database.php';
    
    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Verificar si hay residentes
    echo "1. Verificando residentes...\n";
    $query = "SELECT COUNT(*) as total FROM residentes";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] == 0) {
        echo "   ⚠ No hay residentes. Creando residente de prueba...\n";
        
        // Obtener usuario residente
        $query = "SELECT id FROM usuarios WHERE rol = 'resident' LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "   ⚠ No hay usuarios con rol 'resident'. Creando usuario...\n";
            
            // Crear usuario residente
            $query = "INSERT INTO usuarios (nombre, email, password, rol, telefono) 
                      VALUES ('Residente Prueba', 'residente@test.com', :password, 'resident', '555-1234')";
            $stmt = $db->prepare($query);
            $password = password_hash('password', PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            $user_id = $db->lastInsertId();
            echo "   ✓ Usuario creado con ID: $user_id\n";
        } else {
            $user_id = $user['id'];
        }
        
        // Crear residente
        $query = "INSERT INTO residentes (usuario_id, apartamento, piso, torre, fecha_ingreso, estado) 
                  VALUES (:usuario_id, '101', 1, 'A', CURDATE(), 'activo')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':usuario_id', $user_id);
        $stmt->execute();
        $residente_id = $db->lastInsertId();
        echo "   ✓ Residente creado con ID: $residente_id\n\n";
    } else {
        echo "   ✓ Hay {$result['total']} residente(s) en el sistema\n\n";
        
        // Obtener primer residente
        $query = "SELECT id FROM residentes LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $residente = $stmt->fetch(PDO::FETCH_ASSOC);
        $residente_id = $residente['id'];
    }
    
    // Crear pagos vencidos de prueba
    echo "2. Creando pagos vencidos de prueba...\n";
    
    $pagos = [
        [
            'concepto' => 'Cuota de Mantenimiento',
            'monto' => 1500.00,
            'mes_pago' => date('Y-m', strtotime('-2 months')),
            'fecha_pago' => date('Y-m-d', strtotime('-45 days')),
            'estado' => 'pendiente'
        ],
        [
            'concepto' => 'Cuota de Mantenimiento',
            'monto' => 1500.00,
            'mes_pago' => date('Y-m', strtotime('-1 month')),
            'fecha_pago' => date('Y-m-d', strtotime('-15 days')),
            'estado' => 'pendiente'
        ],
        [
            'concepto' => 'Fondo de Reserva',
            'monto' => 500.00,
            'mes_pago' => date('Y-m', strtotime('-3 months')),
            'fecha_pago' => date('Y-m-d', strtotime('-60 days')),
            'estado' => 'atrasado'
        ]
    ];
    
    foreach ($pagos as $pago) {
        $query = "INSERT INTO pagos (residente_id, monto, concepto, mes_pago, fecha_pago, metodo_pago, referencia, estado) 
                  VALUES (:residente_id, :monto, :concepto, :mes_pago, :fecha_pago, 'transferencia', '', :estado)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':residente_id', $residente_id);
        $stmt->bindParam(':monto', $pago['monto']);
        $stmt->bindParam(':concepto', $pago['concepto']);
        $stmt->bindParam(':mes_pago', $pago['mes_pago']);
        $stmt->bindParam(':fecha_pago', $pago['fecha_pago']);
        $stmt->bindParam(':estado', $pago['estado']);
        $stmt->execute();
        
        echo "   ✓ Pago creado: {$pago['concepto']} - \${$pago['monto']} - {$pago['fecha_pago']} - {$pago['estado']}\n";
    }
    
    echo "\n3. Verificando pagos vencidos...\n";
    $query = "SELECT COUNT(*) as total 
              FROM pagos 
              WHERE (estado = 'pendiente' OR estado = 'atrasado') 
              AND fecha_pago < CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total de pagos vencidos: {$result['total']}\n\n";
    
    echo "========================================\n";
    echo "Datos de prueba creados exitosamente\n";
    echo "========================================\n\n";
    echo "Ahora puedes ejecutar:\n";
    echo "  php check_overdue_payments.php\n";
    echo "para generar las notificaciones automáticas.\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
