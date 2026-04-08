<?php
// Script para probar la generación del PDF de ingresos

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar autoloader de Composer
require_once 'vendor/autoload.php';

// Cargar configuración
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/config.php';

// Cargar modelos y servicios
require_once APP_PATH . '/models/Database.php';
require_once APP_PATH . '/models/Report.php';
require_once APP_PATH . '/services/PdfService.php';

// Iniciar sesión
session_start();

echo "=== Test de Generación de PDF de Ingresos ===\n\n";

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "❌ ERROR: No se pudo conectar a la base de datos\n";
    exit(1);
}

echo "✅ Conexión a base de datos exitosa\n";

// Crear instancia de Report
$report = new Report($db);

// Obtener datos
$start_date = date('Y-m-01');
$end_date = date('Y-m-d');

echo "Obteniendo datos de ingresos...\n";
$income_data = $report->generateIncomeReport($start_date, $end_date);
$total = array_sum(array_column($income_data, 'monto'));

echo "Registros encontrados: " . count($income_data) . "\n";
echo "Total: $" . number_format($total, 2) . "\n\n";

if (count($income_data) == 0) {
    echo "⚠️ No hay datos para generar el PDF\n";
    exit(0);
}

// Crear instancia de PdfService
echo "Creando servicio de PDF...\n";
$pdfService = new PdfService();

// Generar PDF
echo "Generando PDF...\n";
try {
    $pdfService->generateIncomeReport($income_data, $start_date, $end_date, $total);
    echo "✅ PDF generado exitosamente\n";
    echo "El PDF debería haberse descargado automáticamente\n";
} catch (Exception $e) {
    echo "❌ ERROR al generar PDF: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

?>
