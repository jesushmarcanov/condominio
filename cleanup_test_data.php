<?php
/**
 * Script de Limpieza de Datos de Prueba
 * Elimina los datos creados durante el checkpoint de la Tarea 4
 */

require_once 'config/database.php';
require_once 'app/models/Database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "ERROR: No se pudo conectar a la base de datos\n";
    exit(1);
}

echo "Iniciando limpieza de datos de prueba...\n\n";

try {
    $db->beginTransaction();
    
    // Obtener IDs de pagos de prueba (los últimos 3 creados con concepto "Test")
    $query = "SELECT id FROM pagos WHERE concepto LIKE '%Test%' ORDER BY id DESC LIMIT 3";
    $stmt = $db->query($query);
    $test_payments = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($test_payments) > 0) {
        $payment_ids = implode(', ', $test_payments);
        echo "Pagos de prueba encontrados: $payment_ids\n";
        
        // Eliminar historial de mora
        $query = "DELETE FROM late_fee_history WHERE pago_id IN ($payment_ids)";
        $stmt = $db->prepare($query);
        $stmt->execute();
        echo "✓ Historial de mora eliminado: " . $stmt->rowCount() . " registros\n";
        
        // Eliminar pagos
        $query = "DELETE FROM pagos WHERE id IN ($payment_ids)";
        $stmt = $db->prepare($query);
        $stmt->execute();
        echo "✓ Pagos de prueba eliminados: " . $stmt->rowCount() . " registros\n";
    } else {
        echo "No se encontraron pagos de prueba para eliminar\n";
    }
    
    // Obtener IDs de reglas de prueba (las que tienen "Test" en el nombre)
    $query = "SELECT id FROM late_fee_rules WHERE nombre LIKE '%Test%'";
    $stmt = $db->query($query);
    $test_rules = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($test_rules) > 0) {
        $rule_ids = implode(', ', $test_rules);
        echo "Reglas de prueba encontradas: $rule_ids\n";
        
        // Eliminar reglas
        $query = "DELETE FROM late_fee_rules WHERE id IN ($rule_ids)";
        $stmt = $db->prepare($query);
        $stmt->execute();
        echo "✓ Reglas de prueba eliminadas: " . $stmt->rowCount() . " registros\n";
    } else {
        echo "No se encontraron reglas de prueba para eliminar\n";
    }
    
    $db->commit();
    echo "\n✓ Limpieza completada exitosamente\n";
    
} catch (PDOException $e) {
    $db->rollBack();
    echo "✗ Error durante la limpieza: " . $e->getMessage() . "\n";
    exit(1);
}
?>
