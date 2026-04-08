<?php
class ExcelController extends Controller {
    private $excelService;
    private $report;
    
    public function __construct() {
        parent::__construct();
        $this->excelService = new ExcelService();
        $this->report = new Report($this->db);
    }
    
    /**
     * Exportar reporte de ingresos a Excel
     * GET /excel/income?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
     */
    public function income() {
        $this->requireAdmin();
        
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
        
        $data = $this->report->generateIncomeReport($start_date, $end_date);
        $total = array_sum(array_column($data, 'monto'));
        
        $this->excelService->generateIncomeReport($data, $start_date, $end_date, $total);
    }
    
    /**
     * Exportar reporte de pagos pendientes a Excel
     * GET /excel/pending-payments
     */
    public function pendingPayments() {
        $this->requireAdmin();
        
        $data = $this->report->generatePendingPaymentsReport();
        $total = array_sum(array_column($data, 'monto'));
        
        $this->excelService->generatePendingPaymentsReport($data, $total);
    }
    
    /**
     * Exportar reporte de incidencias a Excel
     * GET /excel/incidents?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD&status=
     */
    public function incidents() {
        $this->requireAdmin();
        
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        
        $data = $this->report->generateIncidentReport($start_date, $end_date, $status);
        
        $this->excelService->generateIncidentReport($data, $start_date, $end_date);
    }
    
    /**
     * Exportar reporte de residentes a Excel
     * GET /excel/residents?status=
     */
    public function residents() {
        $this->requireAdmin();
        
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        
        $data = $this->report->generateResidentReport($status);
        
        $this->excelService->generateResidentReport($data, $status);
    }
}
?>
