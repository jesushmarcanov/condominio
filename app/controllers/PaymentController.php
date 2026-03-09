<?php
// Controlador de Pagos

class PaymentController extends Controller {
    private $payment;
    private $resident;
    
    public function __construct() {
        parent::__construct();
        $this->payment = new Payment($this->db);
        $this->resident = new Resident($this->db);
    }
    
    // Listar pagos
    public function index() {
        $this->requireAuth();
        
        $current_user = $this->getCurrentUser();
        $resident_id = null;
        
        if(isResident()) {
            // Si es residente, solo ver sus pagos
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if($resident_data) {
                $resident_id = $resident_data['id'];
            }
        }
        
        // Filtros
        $month = isset($_GET['month']) ? sanitize($_GET['month']) : '';
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        
        if($resident_id) {
            $payments = $this->payment->readByResident($resident_id);
        } elseif(!empty($month)) {
            $payments = $this->payment->getPaymentsByMonth($month);
        } else {
            $payments = $this->payment->readAll();
        }
        
        $payments_list = $payments->fetchAll(PDO::FETCH_ASSOC);
        
        // Filtrar por estado si se especifica
        if(!empty($status)) {
            $payments_list = array_filter($payments_list, function($payment) use ($status) {
                return $payment['estado'] === $status;
            });
        }
        
        $this->view('payments/index', [
            'payments' => $payments_list,
            'month' => $month,
            'status' => $status,
            'is_admin' => isAdmin()
        ]);
    }
    
    // Crear pago (solo admin)
    public function create() {
        $this->requireAdmin();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storePayment();
        } else {
            // Obtener residentes activos
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            
            $this->view('admin/payments/create', [
                'residents' => $residents
            ]);
        }
    }
    
    // Guardar nuevo pago
    private function storePayment() {
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'residente_id' => ['required' => true, 'numeric' => true],
            'monto' => ['required' => true, 'numeric' => true],
            'concepto' => ['required' => true, 'max' => 100],
            'mes_pago' => ['required' => true],
            'fecha_pago' => ['required' => true],
            'metodo_pago' => ['required' => true, 'in' => ['efectivo', 'transferencia', 'tarjeta', 'deposito']],
            'referencia' => ['max' => 100],
            'estado' => ['required' => true, 'in' => ['pagado', 'pendiente', 'atrasado']]
        ]);
        
        if(!empty($errors)) {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/create', [
                'errors' => $errors,
                'data' => $data,
                'residents' => $residents
            ]);
            return;
        }
        
        // Verificar si ya existe un pago para el mismo mes y residente
        $this->payment->residente_id = $data['residente_id'];
        $this->payment->mes_pago = $data['mes_pago'];
        $this->payment->id = 0;
        if($this->payment->paymentExists()) {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/create', [
                'error' => 'Ya existe un pago para este residente en el mes especificado',
                'data' => $data,
                'residents' => $residents
            ]);
            return;
        }
        
        // Crear pago
        $this->payment->residente_id = $data['residente_id'];
        $this->payment->monto = $data['monto'];
        $this->payment->concepto = $data['concepto'];
        $this->payment->mes_pago = $data['mes_pago'];
        $this->payment->fecha_pago = $data['fecha_pago'];
        $this->payment->metodo_pago = $data['metodo_pago'];
        $this->payment->referencia = $data['referencia'];
        $this->payment->estado = $data['estado'];
        
        if($this->payment->create()) {
            flash('Pago registrado correctamente', 'success');
            redirect('/payments');
        } else {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/create', [
                'error' => 'Error al registrar el pago',
                'data' => $data,
                'residents' => $residents
            ]);
        }
    }
    
    // Ver detalles del pago
    public function show($id) {
        $this->requireAuth();
        
        $this->payment->id = $id;
        $payment_data = $this->payment->readOne();
        
        if(!$payment_data) {
            flash('Pago no encontrado', 'error');
            redirect('/payments');
            return;
        }
        
        // Verificar permisos
        if(isResident()) {
            $current_user = $this->getCurrentUser();
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if(!$resident_data || $resident_data['id'] != $payment_data['residente_id']) {
                flash('No tiene permisos para ver este pago', 'error');
                redirect('/payments');
                return;
            }
        }
        
        $this->view('payments/show', [
            'payment' => $payment_data,
            'is_admin' => isAdmin()
        ]);
    }
    
    // Editar pago (solo admin)
    public function edit($id) {
        $this->requireAdmin();
        
        $this->payment->id = $id;
        $payment_data = $this->payment->readOne();
        
        if(!$payment_data) {
            flash('Pago no encontrado', 'error');
            redirect('/payments');
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updatePayment($id);
        } else {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            
            $this->view('admin/payments/edit', [
                'payment' => $payment_data,
                'residents' => $residents
            ]);
        }
    }
    
    // Actualizar pago
    private function updatePayment($id) {
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'residente_id' => ['required' => true, 'numeric' => true],
            'monto' => ['required' => true, 'numeric' => true],
            'concepto' => ['required' => true, 'max' => 100],
            'mes_pago' => ['required' => true],
            'fecha_pago' => ['required' => true],
            'metodo_pago' => ['required' => true, 'in' => ['efectivo', 'transferencia', 'tarjeta', 'deposito']],
            'referencia' => ['max' => 100],
            'estado' => ['required' => true, 'in' => ['pagado', 'pendiente', 'atrasado']]
        ]);
        
        if(!empty($errors)) {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/edit', [
                'errors' => $errors,
                'payment' => $data,
                'residents' => $residents
            ]);
            return;
        }
        
        // Verificar si ya existe un pago para el mismo mes y residente (excepto el actual)
        $this->payment->residente_id = $data['residente_id'];
        $this->payment->mes_pago = $data['mes_pago'];
        $this->payment->id = $id;
        if($this->payment->paymentExists()) {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/edit', [
                'error' => 'Ya existe un pago para este residente en el mes especificado',
                'payment' => $data,
                'residents' => $residents
            ]);
            return;
        }
        
        // Actualizar pago
        $this->payment->id = $id;
        $this->payment->residente_id = $data['residente_id'];
        $this->payment->monto = $data['monto'];
        $this->payment->concepto = $data['concepto'];
        $this->payment->mes_pago = $data['mes_pago'];
        $this->payment->fecha_pago = $data['fecha_pago'];
        $this->payment->metodo_pago = $data['metodo_pago'];
        $this->payment->referencia = $data['referencia'];
        $this->payment->estado = $data['estado'];
        
        if($this->payment->update()) {
            flash('Pago actualizado correctamente', 'success');
            redirect('/payments');
        } else {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/edit', [
                'error' => 'Error al actualizar el pago',
                'payment' => $data,
                'residents' => $residents
            ]);
        }
    }
    
    // Eliminar pago (solo admin)
    public function delete($id) {
        $this->requireAdmin();
        
        $this->payment->id = $id;
        if($this->payment->delete()) {
            flash('Pago eliminado correctamente', 'success');
        } else {
            flash('Error al eliminar el pago', 'error');
        }
        
        redirect('/payments');
    }
    
    // Ver pagos pendientes (solo admin)
    public function pending() {
        $this->requireAdmin();
        
        $payments = $this->payment->getPendingPayments()->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('admin/payments/pending', [
            'payments' => $payments
        ]);
    }
    
    // Generar reporte de pagos
    public function report() {
        $this->requireAdmin();
        
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
        $export = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        
        $report = new Report($this->db);
        $data = $report->generateIncomeReport($start_date, $end_date);
        
        if($export === 'csv') {
            $filename = 'reporte_pagos_' . date('Y-m-d') . '.csv';
            $report->exportToCSV($data, $filename);
        } else {
            $this->view('admin/payments/report', [
                'payments' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
        }
    }
    
    // Estadísticas de pagos
    public function stats() {
        $this->requireAdmin();
        
        $stats = $this->payment->getStats();
        $monthly_income = $this->payment->getMonthlyIncome();
        
        $this->view('admin/payments/stats', [
            'stats' => $stats,
            'monthly_income' => $monthly_income
        ]);
    }
}
?>
