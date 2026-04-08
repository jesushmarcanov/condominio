<?php
/**
 * Controlador de PDF
 * 
 * Maneja las solicitudes de generación de documentos PDF.
 * Proporciona endpoints dedicados para diferentes tipos de reportes.
 * 
 * @package App\Controllers
 * @author Sistema de Gestión de Condominio
 * @version 1.0.0
 */

class PdfController extends Controller {
    private $pdfService;
    private $report;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->pdfService = new PdfService();
        $this->report = new Report($this->db);
    }
    
    /**
     * Generar PDF de reporte de ingresos
     * 
     * GET /pdf/income?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
     */
    public function income() {
        $this->requireAdmin();
        
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
        
        $data = $this->report->generateIncomeReport($start_date, $end_date);
        $total = array_sum(array_column($data, 'monto'));
        
        $this->pdfService->generateIncomeReport($data, $start_date, $end_date, $total);
    }
    
    /**
     * Generar PDF de reporte de pagos pendientes
     * 
     * GET /pdf/pending-payments
     */
    public function pendingPayments() {
        $this->requireAdmin();
        
        $data = $this->report->generatePendingPaymentsReport();
        $total = array_sum(array_column($data, 'monto'));
        
        $this->pdfService->generatePendingPaymentsReport($data, $total);
    }
    
    /**
     * Generar PDF de reporte de incidencias
     * 
     * GET /pdf/incidents?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD&status=
     */
    public function incidents() {
        $this->requireAdmin();
        
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        
        $data = $this->report->generateIncidentReport($start_date, $end_date, $status);
        
        $this->pdfService->generateIncidentReport($data, $start_date, $end_date);
    }
    
    /**
     * Generar PDF de reporte de residentes
     * 
     * GET /pdf/residents?status=
     */
    public function residents() {
        $this->requireAdmin();
        
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        
        $data = $this->report->generateResidentReport($status);
        
        $this->pdfService->generateResidentReport($data, $status);
    }
    
    /**
     * Generar PDF de comprobante de pago
     * 
     * GET /pdf/payment-receipt/{id}
     */
    public function paymentReceipt($id) {
        $this->requireAuth();
        
        $payment = new Payment($this->db);
        $payment->id = $id;
        $payment_data = $payment->readOne();
        
        if (!$payment_data) {
            flash('Pago no encontrado', 'error');
            redirect('/payments');
            return;
        }
        
        // Verificar permisos: solo el propietario o admin pueden ver el comprobante
        $current_user = $this->getCurrentUser();
        $resident = new Resident($this->db);
        $resident_data = $resident->getByResidentId($payment_data['residente_id']);
        
        if (!isAdmin() && $resident_data['usuario_id'] != $current_user['id']) {
            flash('No tiene permisos para ver este comprobante', 'error');
            redirect('/payments');
            return;
        }
        
        $this->pdfService->generatePaymentReceipt($payment_data);
    }
    
    /**
     * Generar PDF de reporte de incidencia
     * 
     * GET /pdf/incident-receipt/{id}
     */
    public function incidentReceipt($id) {
        $this->requireAuth();
        
        $incident = new Incident($this->db);
        $incident->id = $id;
        $incident_data = $incident->readOne();
        
        if (!$incident_data) {
            flash('Incidencia no encontrada', 'error');
            redirect('/incidents');
            return;
        }
        
        // Verificar permisos: solo el propietario o admin pueden ver el reporte
        $current_user = $this->getCurrentUser();
        $resident = new Resident($this->db);
        $resident_data = $resident->getByResidentId($incident_data['residente_id']);
        
        if (!isAdmin() && $resident_data['usuario_id'] != $current_user['id']) {
            flash('No tiene permisos para ver este reporte', 'error');
            redirect('/incidents');
            return;
        }
        
        $this->pdfService->generateIncidentReceipt($incident_data);
    }
}
?>
