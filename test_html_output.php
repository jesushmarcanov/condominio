<?php
// Script para ver el HTML generado antes de convertirlo a PDF

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

echo "=== Generando HTML de Reporte de Ingresos ===\n\n";

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear instancia de Report
$report = new Report($db);

// Obtener datos
$start_date = date('Y-m-01');
$end_date = date('Y-m-d');

$payments = $report->generateIncomeReport($start_date, $end_date);
$total = array_sum(array_column($payments, 'monto'));

echo "Registros encontrados: " . count($payments) . "\n";
echo "Total: $" . number_format($total, 2) . "\n\n";

// Generar HTML (copiado del método getIncomeReportHtml)
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0; color: #666; }
        .info { margin-bottom: 20px; }
        .info p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #007bff; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .total { text-align: right; font-weight: bold; font-size: 14px; margin-top: 20px; }
        .footer { text-align: center; margin-top: 40px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema de Gestión de Condominio</h1>
        <h2>Reporte de Ingresos</h2>
        <p>Período: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)) . '</p>
    </div>
    
    <div class="info">
        <p><strong>Fecha de generación:</strong> ' . date('d/m/Y H:i:s') . '</p>
        <p><strong>Total de registros:</strong> ' . count($payments) . '</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Residente</th>
                <th>Apartamento</th>
                <th>Concepto</th>
                <th>Fecha</th>
                <th>Método</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>';

foreach ($payments as $payment) {
    $html .= '
            <tr>
                <td>' . htmlspecialchars($payment['id']) . '</td>
                <td>' . htmlspecialchars($payment['residente_nombre'] ?? $payment['nombre'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($payment['apartamento'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($payment['descripcion'] ?? $payment['concepto'] ?? 'N/A') . '</td>
                <td>' . date('d/m/Y', strtotime($payment['fecha_pago'])) . '</td>
                <td>' . ucfirst($payment['metodo_pago']) . '</td>
                <td>$' . number_format($payment['monto'], 2) . '</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>
    
    <div class="total">
        <p>TOTAL: $' . number_format($total, 2) . '</p>
    </div>
    
    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión de Condominio</p>
    </div>
</body>
</html>';

// Guardar HTML en archivo
$filename = 'test_income_report.html';
file_put_contents($filename, $html);

echo "✅ HTML generado y guardado en: $filename\n";
echo "Puedes abrir este archivo en tu navegador para ver cómo se ve el reporte\n\n";

// Mostrar un preview del HTML
echo "Preview del HTML (primeras 500 caracteres):\n";
echo substr($html, 0, 500) . "...\n";

?>
