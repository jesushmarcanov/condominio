<?php
// Controlador de Reportes

class ReportController extends Controller {
    private $report;
    
    public function __construct() {
        parent::__construct();
        $this->report = new Report($this->db);
    }
    
    // Índice de reportes
    public function index() {
        $this->requireAdmin();
        
        // Obtener estadísticas dinámicas
        $user = new User($this->db);
        $resident = new Resident($this->db);
        $payment = new Payment($this->db);
        $incident = new Incident($this->db);
        
        // Estadísticas generales
        $stats['usuarios'] = $user->getStats();
        $stats['residentes'] = $resident->getStats();
        $stats['pagos'] = $payment->getStats();
        $stats['incidencias'] = $incident->getStats();
        
        // Ingresos del mes actual
        $current_month = date('Y-m');
        $monthly_income = $payment->getMonthlyIncome(1); // Último mes
        $stats['ingresos_mensuales'] = $monthly_income[0]['ingresos'] ?? 0;
        
        // Incidencias activas (pendientes + en proceso)
        $stats['incidencias_activas'] = $stats['incidencias']['incidencias_pendientes'] + $stats['incidencias']['incidencias_en_proceso'];
        
        $this->view('admin/reports/index', [
            'stats' => $stats
        ]);
    }
    
    // Reporte de ingresos
    public function income() {
        $this->requireAdmin();
        
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
        $export = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        
        // Depuración - registrar parámetros
        error_log("ReportController::income() - start_date: $start_date, end_date: $end_date, export: $export");
        
        $data = $this->report->generateIncomeReport($start_date, $end_date);
        
        if($export === 'csv') {
            error_log("Exportando a CSV - filename: reporte_ingresos_" . date('Y-m-d') . ".csv");
            $filename = 'reporte_ingresos_' . date('Y-m-d') . '.csv';
            $this->report->exportToCSV($data, $filename);
        } else {
            $this->view('admin/reports/income', [
                'payments' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total' => array_sum(array_column($data, 'monto'))
            ]);
        }
    }
    
    // Reporte de pagos pendientes
    public function pendingPayments() {
        $this->requireAdmin();
        
        $export = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        $data = $this->report->generatePendingPaymentsReport();
        
        if($export === 'csv') {
            $filename = 'reporte_pagos_pendientes_' . date('Y-m-d') . '.csv';
            $this->report->exportToCSV($data, $filename);
        } else {
            $this->view('admin/reports/pending_payments', [
                'payments' => $data,
                'total_pendiente' => array_sum(array_column($data, 'monto'))
            ]);
        }
    }
    
    // Reporte de incidencias
    public function incidents() {
        $this->requireAdmin();
        
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        $export = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        
        $data = $this->report->generateIncidentReport($start_date, $end_date, $status);
        
        if($export === 'csv') {
            $filename = 'reporte_incidencias_' . date('Y-m-d') . '.csv';
            $this->report->exportToCSV($data, $filename);
        } else {
            $this->view('admin/reports/incidents', [
                'incidents' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status
            ]);
        }
    }
    
    // Reporte de residentes
    public function residents() {
        $this->requireAdmin();
        
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        $export = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        
        $data = $this->report->generateResidentReport($status);
        
        if($export === 'csv') {
            $filename = 'reporte_residentes_' . date('Y-m-d') . '.csv';
            $this->report->exportToCSV($data, $filename);
        } else {
            $this->view('admin/reports/residents', [
                'residents' => $data,
                'status' => $status
            ]);
        }
    }
    
    // Dashboard de estadísticas
    public function dashboard() {
        $this->requireAdmin();
        
        $stats = $this->report->getGeneralStats();
        
        // Aplanar el array de estadísticas para la vista
        $flat_stats = [
            'total_residentes' => $stats['residentes']['total_residentes'] ?? 0,
            'residentes_activos' => $stats['residentes']['residentes_activos'] ?? 0,
            'total_ingresos' => $stats['pagos']['total_ingresos'] ?? 0,
            'ingresos_mes' => $this->getMonthlyIncome(),
            'total_incidencias' => $stats['incidencias']['total_incidencias'] ?? 0,
            'incidencias_abiertas' => ($stats['incidencias']['incidencias_abiertas'] ?? 0) + ($stats['incidencias']['incidencias_proceso'] ?? 0),
            'pagos_pendientes' => $stats['pagos']['pagos_pendientes'] ?? 0,
            'pagos_atrasados' => $stats['pagos']['pagos_atrasados'] ?? 0,
            'total_pagos' => $stats['pagos']['total_pagos'] ?? 0,
            'pagos_realizados' => $stats['pagos']['pagos_realizados'] ?? 0,
            'total_apartamentos' => 50, // Este valor debería venir de configuración o base de datos
            'tiempo_promedio_resolucion' => $this->getAverageResolutionTime()
        ];
        
        // Datos para gráficos
        $monthly_income = $this->report->getChartData('monthly_income');
        $monthly_incidents = $this->report->getChartData('monthly_incidents');
        $incidents_by_category = $this->report->getChartData('incidents_by_category');
        $payment_methods = $this->report->getChartData('payment_methods');
        
        // Verificar si se solicita exportación CSV
        if(isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->exportDashboardCSV($flat_stats, $monthly_income, $monthly_incidents, $incidents_by_category, $payment_methods);
            return;
        }
        
        $this->view('admin/reports/dashboard', [
            'stats' => $flat_stats,
            'monthly_income' => $monthly_income,
            'monthly_incidents' => $monthly_incidents,
            'incidents_by_category' => $incidents_by_category,
            'payment_methods' => $payment_methods
        ]);
    }
    
    // Resumen financiero mensual
    public function financialSummary() {
        $this->requireAdmin();
        
        $year = isset($_GET['year']) ? sanitize($_GET['year']) : date('Y');
        $export = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        
        $data = $this->report->getMonthlyFinancialSummary($year);
        
        if($export === 'csv') {
            $filename = 'resumen_financiero_' . $year . '.csv';
            $this->report->exportToCSV($data, $filename);
        } else {
            $this->view('admin/reports/financial_summary', [
                'data' => $data,
                'year' => $year,
                'total_ingresos' => array_sum(array_column($data, 'ingresos')),
                'total_pagos' => array_sum(array_column($data, 'pagos_realizados'))
            ]);
        }
    }
    
    // API para datos de gráficos
    public function chartData() {
        $this->requireAdmin();
        
        $type = isset($_GET['type']) ? sanitize($_GET['type']) : 'monthly_income';
        
        $data = $this->report->getChartData($type);
        $this->jsonResponse($data);
    }
    
    // Reporte personalizado
    public function custom() {
        $this->requireAdmin();
        
        // Manejar exportación CSV desde GET
        if(isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->exportCustomCSV();
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateCustomReport();
        } else {
            $this->view('admin/reports/custom');
        }
    }
    
    // Generar reporte personalizado
    private function generateCustomReport() {
        $data = $this->getPostData();
        
        // Validar datos básicos
        $errors = [];
        if(empty($data['report_type'])) $errors[] = 'Debe seleccionar un tipo de reporte';
        if(empty($data['start_date'])) $errors[] = 'La fecha de inicio es requerida';
        if(empty($data['end_date'])) $errors[] = 'La fecha de fin es requerida';
        
        if(!empty($errors)) {
            $this->view('admin/reports/custom', [
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }
        
        $report_data = [];
        $filename = '';
        
        switch($data['report_type']) {
            case 'income':
                $report_data = $this->report->generateIncomeReport($data['start_date'], $data['end_date']);
                $filename = 'reporte_ingresos_' . date('Y-m-d') . '.csv';
                break;
                
            case 'incidents':
                $status = isset($data['status']) ? $data['status'] : '';
                $report_data = $this->report->generateIncidentReport($data['start_date'], $data['end_date'], $status);
                $filename = 'reporte_incidencias_' . date('Y-m-d') . '.csv';
                break;
                
            case 'residents':
                $status = isset($data['resident_status']) ? $data['resident_status'] : '';
                $report_data = $this->report->generateResidentReport($status);
                $filename = 'reporte_residentes_' . date('Y-m-d') . '.csv';
                break;
                
            case 'payments':
                $report_data = $this->report->generatePendingPaymentsReport();
                $filename = 'reporte_pagos_pendientes_' . date('Y-m-d') . '.csv';
                break;
                
            default:
                $this->view('admin/reports/custom', [
                    'error' => 'Tipo de reporte inválido',
                    'data' => $data
                ]);
                return;
        }
        
        // Aplicar filtros adicionales si se especificaron
        if(!empty($data['payment_method']) && $data['report_type'] === 'income') {
            $report_data = array_filter($report_data, function($row) use ($data) {
                return $row['metodo_pago'] === $data['payment_method'];
            });
        }
        
        if(!empty($data['min_amount']) && $data['report_type'] === 'income') {
            $report_data = array_filter($report_data, function($row) use ($data) {
                return floatval($row['monto']) >= floatval($data['min_amount']);
            });
        }
        
        if(!empty($data['category']) && $data['report_type'] === 'incidents') {
            $report_data = array_filter($report_data, function($row) use ($data) {
                return $row['categoria'] === $data['category'];
            });
        }
        
        if(!empty($data['priority']) && $data['report_type'] === 'incidents') {
            $report_data = array_filter($report_data, function($row) use ($data) {
                return $row['prioridad'] === $data['priority'];
            });
        }
        
        if(!empty($data['floor']) && $data['report_type'] === 'residents') {
            $report_data = array_filter($report_data, function($row) use ($data) {
                return $row['piso'] === $data['floor'];
            });
        }
        
        if(!empty($data['tower']) && $data['report_type'] === 'residents') {
            $report_data = array_filter($report_data, function($row) use ($data) {
                return $row['torre'] === $data['tower'];
            });
        }
        
        // Ordenar datos si se especificó
        if(!empty($data['sort_by'])) {
            usort($report_data, function($a, $b) use ($data) {
                switch($data['sort_by']) {
                    case 'amount':
                        return floatval($b['monto'] ?? 0) - floatval($a['monto'] ?? 0);
                    case 'name':
                        return strcmp($a['residente_nombre'] ?? $a['nombre'] ?? '', $b['residente_nombre'] ?? $b['nombre'] ?? '');
                    case 'status':
                        return strcmp($a['estado'] ?? '', $b['estado'] ?? '');
                    case 'date':
                    default:
                        return strcmp($b['fecha_pago'] ?? $b['fecha_reporte'] ?? '', $a['fecha_pago'] ?? $a['fecha_reporte'] ?? '');
                }
            });
        }
        
        if(isset($data['export']) && $data['export'] === 'csv') {
            $this->report->exportToCSV($report_data, $filename);
        } else {
            $this->view('admin/reports/custom_result', [
                'data' => $report_data,
                'report_type' => $data['report_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date']
            ]);
        }
    }
    
    // Métodos auxiliares para el dashboard
    private function getMonthlyIncome() {
        $query = "SELECT COALESCE(SUM(monto), 0) as total 
                  FROM pagos 
                  WHERE estado = 'pagado' 
                  AND MONTH(fecha_pago) = MONTH(CURRENT_DATE) 
                  AND YEAR(fecha_pago) = YEAR(CURRENT_DATE)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function getOverduePayments() {
        $query = "SELECT COUNT(*) as total 
                  FROM pagos 
                  WHERE estado = 'pendiente' 
                  AND fecha_pago < DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function getAverageResolutionTime() {
        $query = "SELECT AVG(DATEDIFF(fecha_resolucion, fecha_reporte)) as avg_days 
                  FROM incidencias 
                  WHERE estado = 'resuelta' 
                  AND fecha_resolucion IS NOT NULL";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['avg_days'] ?? 0, 1);
    }
    
    // Exportar dashboard a CSV
    private function exportDashboardCSV($stats, $monthly_income, $monthly_incidents, $incidents_by_category, $payment_methods) {
        $filename = 'dashboard_estadistico_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Estadísticas Generales
        fputcsv($output, ['ESTADÍSTICAS GENERALES']);
        fputcsv($output, ['Métrica', 'Valor', 'Descripción']);
        
        $general_stats = [
            'Total Residentes' => [$stats['total_residentes'], 'Número total de residentes registrados'],
            'Residentes Activos' => [$stats['residentes_activos'], 'Residentes con estado activo'],
            'Total Ingresos' => ['$' . number_format($stats['total_ingresos'], 2), 'Suma total de todos los ingresos'],
            'Ingresos Mes Actual' => ['$' . number_format($stats['ingresos_mes'], 2), 'Ingresos del mes en curso'],
            'Total Incidencias' => [$stats['total_incidencias'], 'Número total de incidencias reportadas'],
            'Incidencias Abiertas' => [$stats['incidencias_abiertas'], 'Incidencias pendientes o en proceso'],
            'Pagos Pendientes' => [$stats['pagos_pendientes'], 'Pagos con estado pendiente'],
            'Pagos Atrasados' => [$stats['pagos_atrasados'], 'Pagos con estado atrasado'],
            'Total Pagos' => [$stats['total_pagos'], 'Número total de pagos registrados'],
            'Pagos Realizados' => [$stats['pagos_realizados'], 'Pagos con estado pagado'],
            'Total Apartamentos' => [$stats['total_apartamentos'], 'Número total de apartamentos'],
            'Tiempo Promedio Resolución' => [$stats['tiempo_promedio_resolucion'] . ' días', 'Tiempo promedio para resolver incidencias']
        ];
        
        foreach($general_stats as $metric => $data) {
            fputcsv($output, [$metric, $data[0], $data[1]]);
        }
        
        fputcsv($output, []); // Línea vacía
        
        // Ingresos Mensuales
        fputcsv($output, ['INGRESOS MENSUALES']);
        fputcsv($output, ['Mes', 'Ingresos', 'Cantidad de Pagos']);
        
        foreach($monthly_income as $income) {
            fputcsv($output, [
                date('M Y', strtotime($income['period'])),
                '$' . number_format($income['amount'], 2),
                $income['count']
            ]);
        }
        
        fputcsv($output, []); // Línea vacía
        
        // Incidencias Mensuales
        fputcsv($output, ['INCIDENCIAS MENSUALES']);
        fputcsv($output, ['Mes', 'Cantidad de Incidencias']);
        
        foreach($monthly_incidents as $incident) {
            fputcsv($output, [
                date('M Y', strtotime($incident['period'])),
                $incident['count']
            ]);
        }
        
        fputcsv($output, []); // Línea vacía
        
        // Incidencias por Categoría
        fputcsv($output, ['INCIDENCIAS POR CATEGORÍA']);
        fputcsv($output, ['Categoría', 'Cantidad']);
        
        foreach($incidents_by_category as $category) {
            fputcsv($output, [
                ucfirst($category['category']),
                $category['count']
            ]);
        }
        
        fputcsv($output, []); // Línea vacía
        
        // Métodos de Pago
        fputcsv($output, ['MÉTODOS DE PAGO']);
        fputcsv($output, ['Método', 'Cantidad', 'Porcentaje']);
        
        $total_payments = array_sum(array_column($payment_methods, 'count'));
        foreach($payment_methods as $method) {
            $percentage = $total_payments > 0 ? ($method['count'] / $total_payments) * 100 : 0;
            fputcsv($output, [
                ucfirst($method['method']),
                $method['count'],
                number_format($percentage, 1) . '%'
            ]);
        }
        
        fputcsv($output, []); // Línea vacía
        fputcsv($output, ['Fecha de Generación', date('d/m/Y H:i:s')]);
        
        fclose($output);
        exit;
    }
    
    // Exportar reporte personalizado a CSV
    private function exportCustomCSV() {
        $report_type = isset($_GET['report_type']) ? sanitize($_GET['report_type']) : '';
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
        
        if(empty($report_type) || empty($start_date) || empty($end_date)) {
            header('Location: ' . APP_URL . '/reports/custom');
            exit;
        }
        
        $report_data = [];
        $filename = '';
        
        switch($report_type) {
            case 'income':
                $report_data = $this->report->generateIncomeReport($start_date, $end_date);
                $filename = 'reporte_ingresos_' . date('Y-m-d') . '.csv';
                break;
                
            case 'incidents':
                $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
                $report_data = $this->report->generateIncidentReport($start_date, $end_date, $status);
                $filename = 'reporte_incidencias_' . date('Y-m-d') . '.csv';
                break;
                
            case 'residents':
                $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
                $report_data = $this->report->generateResidentReport($status);
                $filename = 'reporte_residentes_' . date('Y-m-d') . '.csv';
                break;
                
            case 'payments':
                $report_data = $this->report->generatePendingPaymentsReport();
                $filename = 'reporte_pagos_pendientes_' . date('Y-m-d') . '.csv';
                break;
                
            default:
                header('Location: ' . APP_URL . '/reports/custom');
                exit;
        }
        
        // Aplicar filtros adicionales desde parámetros GET
        if(!empty($_GET['payment_method']) && $report_type === 'income') {
            $payment_method = sanitize($_GET['payment_method']);
            $report_data = array_filter($report_data, function($row) use ($payment_method) {
                return $row['metodo_pago'] === $payment_method;
            });
        }
        
        if(!empty($_GET['min_amount']) && $report_type === 'income') {
            $min_amount = floatval($_GET['min_amount']);
            $report_data = array_filter($report_data, function($row) use ($min_amount) {
                return floatval($row['monto']) >= $min_amount;
            });
        }
        
        if(!empty($_GET['category']) && $report_type === 'incidents') {
            $category = sanitize($_GET['category']);
            $report_data = array_filter($report_data, function($row) use ($category) {
                return $row['categoria'] === $category;
            });
        }
        
        if(!empty($_GET['priority']) && $report_type === 'incidents') {
            $priority = sanitize($_GET['priority']);
            $report_data = array_filter($report_data, function($row) use ($priority) {
                return $row['prioridad'] === $priority;
            });
        }
        
        if(!empty($_GET['floor']) && $report_type === 'residents') {
            $floor = sanitize($_GET['floor']);
            $report_data = array_filter($report_data, function($row) use ($floor) {
                return $row['piso'] === $floor;
            });
        }
        
        if(!empty($_GET['tower']) && $report_type === 'residents') {
            $tower = sanitize($_GET['tower']);
            $report_data = array_filter($report_data, function($row) use ($tower) {
                return $row['torre'] === $tower;
            });
        }
        
        $this->report->exportToCSV($report_data, $filename);
    }
}
?>
