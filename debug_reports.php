<?php
// Script de depuración para reportes

// Incluir configuración
require_once 'config/config.php';
require_once 'config/database.php';

// Conectar a la base de datos
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Conexión a la base de datos: OK</h2>";
    
    // Verificar si existe la tabla pagos
    $query = "SHOW TABLES LIKE 'pagos'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        echo "<h3>Tabla 'pagos': EXISTE</h3>";
        
        // Verificar estructura de la tabla
        $query = "DESCRIBE pagos";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Estructura de la tabla pagos:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
        foreach($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si hay datos
        $query = "SELECT COUNT(*) as total FROM pagos";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h4>Total de registros en pagos: {$result['total']}</h4>";
        
        // Mostrar algunos datos de ejemplo
        if($result['total'] > 0) {
            $query = "SELECT * FROM pagos LIMIT 3";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Ejemplos de datos:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Monto</th><th>Concepto</th><th>Fecha Pago</th><th>Estado</th></tr>";
            foreach($payments as $payment) {
                echo "<tr>";
                echo "<td>{$payment['id']}</td>";
                echo "<td>{$payment['monto']}</td>";
                echo "<td>{$payment['concepto']}</td>";
                echo "<td>{$payment['fecha_pago']}</td>";
                echo "<td>{$payment['estado']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<h3>Tabla 'pagos': NO EXISTE</h3>";
    }
    
    // Verificar si existen las otras tablas necesarias
    $tables = ['residentes', 'usuarios', 'incidencias'];
    foreach($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            echo "<h3>Tabla '$table': EXISTE</h3>";
            
            $query = "SELECT COUNT(*) as total FROM $table";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Registros: {$result['total']}</p>";
        } else {
            echo "<h3>Tabla '$table': NO EXISTE</h3>";
        }
    }
    
    // Probar la consulta del reporte de ingresos
    echo "<h3>Probando consulta de reporte de ingresos:</h3>";
    try {
        $query = "SELECT 
                    p.id,
                    p.monto,
                    p.concepto as descripcion,
                    p.mes_pago,
                    p.fecha_pago,
                    p.metodo_pago,
                    p.referencia,
                    p.estado,
                    r.apartamento,
                    u.nombre as residente_nombre,
                    u.email as residente_email
                  FROM pagos p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  ORDER BY p.fecha_pago DESC
                  LIMIT 5";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Consulta ejecutada correctamente. Registros encontrados: " . count($results) . "</p>";
        
        if(!empty($results)) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Monto</th><th>Residente</th><th>Apartamento</th><th>Estado</th></tr>";
            foreach($results as $row) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['monto']}</td>";
                echo "<td>{$row['residente_nombre']}</td>";
                echo "<td>{$row['apartamento']}</td>";
                echo "<td>{$row['estado']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Error en consulta: " . $e->getMessage() . "</p>";
    }
    
} catch(PDOException $e) {
    echo "<h2 style='color: red;'>Error de conexión: " . $e->getMessage() . "</h2>";
}

echo "<hr>";
echo "<h3>Configuración actual:</h3>";
echo "<p>DB_HOST: " . DB_HOST . "</p>";
echo "<p>DB_NAME: " . DB_NAME . "</p>";
echo "<p>DB_USER: " . DB_USER . "</p>";
echo "<p>APP_URL: " . APP_URL . "</p>";
?>
