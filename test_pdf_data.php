<?php
// Script para probar que los datos se obtienen correctamente

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar configuración
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/config.php';

// Cargar modelos
require_once APP_PATH . '/models/Database.php';
require_once APP_PATH . '/models/Report.php';

// Iniciar sesión
session_start();

echo "=== Test de Datos para PDF ===\n\n";

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "❌ ERROR: No se pudo conectar a la base de datos\n";
    exit(1);
}

echo "✅ Conexión a base de datos exitosa\n\n";

// Crear instancia de Report
$report = new Report($db);

// Test 1: Reporte de Ingresos
echo "1. Probando reporte de ingresos...\n";
$start_date = date('Y-m-01'); // Primer día del mes actual
$end_date = date('Y-m-d');    // Hoy

$income_data = $report->generateIncomeReport($start_date, $end_date);

echo "   Período: $start_date a $end_date\n";
echo "   Registros encontrados: " . count($income_data) . "\n";

if (count($income_data) > 0) {
    echo "   ✅ Datos obtenidos correctamente\n";
    echo "   Primer registro:\n";
    $first = $income_data[0];
    echo "      - ID: " . $first['id'] . "\n";
    echo "      - Residente: " . ($first['residente_nombre'] ?? 'N/A') . "\n";
    echo "      - Apartamento: " . ($first['apartamento'] ?? 'N/A') . "\n";
    echo "      - Descripción: " . ($first['descripcion'] ?? 'N/A') . "\n";
    echo "      - Monto: $" . number_format($first['monto'], 2) . "\n";
    echo "      - Fecha: " . $first['fecha_pago'] . "\n";
    
    $total = array_sum(array_column($income_data, 'monto'));
    echo "   Total: $" . number_format($total, 2) . "\n";
} else {
    echo "   ⚠️ No hay datos de ingresos en este período\n";
}

echo "\n";

// Test 2: Reporte de Pagos Pendientes
echo "2. Probando reporte de pagos pendientes...\n";
$pending_data = $report->generatePendingPaymentsReport();

echo "   Registros encontrados: " . count($pending_data) . "\n";

if (count($pending_data) > 0) {
    echo "   ✅ Datos obtenidos correctamente\n";
    echo "   Primer registro:\n";
    $first = $pending_data[0];
    echo "      - ID: " . $first['id'] . "\n";
    echo "      - Residente: " . ($first['residente_nombre'] ?? 'N/A') . "\n";
    echo "      - Apartamento: " . ($first['apartamento'] ?? 'N/A') . "\n";
    echo "      - Concepto: " . ($first['concepto'] ?? 'N/A') . "\n";
    echo "      - Monto: $" . number_format($first['monto'], 2) . "\n";
    echo "      - Estado: " . $first['estado'] . "\n";
    
    $total = array_sum(array_column($pending_data, 'monto'));
    echo "   Total pendiente: $" . number_format($total, 2) . "\n";
} else {
    echo "   ⚠️ No hay pagos pendientes\n";
}

echo "\n";

// Test 3: Reporte de Incidencias
echo "3. Probando reporte de incidencias...\n";
$incidents_data = $report->generateIncidentReport($start_date, $end_date);

echo "   Período: $start_date a $end_date\n";
echo "   Registros encontrados: " . count($incidents_data) . "\n";

if (count($incidents_data) > 0) {
    echo "   ✅ Datos obtenidos correctamente\n";
    echo "   Primer registro:\n";
    $first = $incidents_data[0];
    echo "      - ID: " . $first['id'] . "\n";
    echo "      - Residente: " . ($first['residente_nombre'] ?? 'N/A') . "\n";
    echo "      - Apartamento: " . ($first['apartamento'] ?? 'N/A') . "\n";
    echo "      - Título: " . $first['titulo'] . "\n";
    echo "      - Categoría: " . $first['categoria'] . "\n";
    echo "      - Prioridad: " . $first['prioridad'] . "\n";
    echo "      - Estado: " . $first['estado'] . "\n";
} else {
    echo "   ⚠️ No hay incidencias en este período\n";
}

echo "\n";

// Resumen
echo "=== RESUMEN ===\n";
echo "Ingresos: " . count($income_data) . " registros\n";
echo "Pagos pendientes: " . count($pending_data) . " registros\n";
echo "Incidencias: " . count($incidents_data) . " registros\n";

if (count($income_data) == 0 && count($pending_data) == 0 && count($incidents_data) == 0) {
    echo "\n⚠️ ADVERTENCIA: No hay datos en la base de datos\n";
    echo "Necesitas crear algunos registros de prueba para ver los PDFs con información.\n";
    echo "Puedes usar el script create_test_data.php para generar datos de prueba.\n";
} else {
    echo "\n✅ Hay datos disponibles para generar PDFs\n";
}

echo "\n";
?>
