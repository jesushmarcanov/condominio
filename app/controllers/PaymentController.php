<?php
// Controlador de Pagos

class PaymentController extends Controller {
    private $payment;
    private $resident;
    private $emailService;
    private $lateFeeService;
    private $lateFeeHistory;
    private $bankAccount;
    private $paymentDeclaration;
    private $paymentNotificationService;
    private $medioPago;
    
    public function __construct() {
        parent::__construct();
        $this->payment = new Payment($this->db);
        $this->resident = new Resident($this->db);
        $this->emailService = new EmailService($this->db);
        $this->bankAccount = new BankAccount($this->db);
        $this->paymentDeclaration = new PaymentDeclaration($this->db);
        $this->paymentNotificationService = new PaymentNotificationService($this->db);
        $this->medioPago = new MedioPago($this->db);
        
        // Cargar modelos y servicios de mora si existen
        if (file_exists(APP_PATH . '/models/LateFeeRule.php')) {
            require_once APP_PATH . '/models/LateFeeRule.php';
            require_once APP_PATH . '/models/LateFeeHistory.php';
            require_once APP_PATH . '/services/LateFeeService.php';
            $this->lateFeeService = new LateFeeService($this->db);
            $this->lateFeeHistory = new LateFeeHistory($this->db);
        }
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
                'residents' => $residents,
                'medios_pago' => $this->getMediosPago()
            ]);
        }
    }
    
    // Guardar nuevo pago
    private function storePayment() {
        $data = $this->getPostData();
        $errors = $this->validateRules('payment.store', $data);
        
        if(!empty($errors)) {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/create', [
                'errors' => $errors,
                'data' => $data,
                'residents' => $residents,
                'medios_pago' => $this->getMediosPago()
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
                'residents' => $residents,
                'medios_pago' => $this->getMediosPago()
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
        $this->payment->moneda = baseCurrency();
        $this->payment->monto_moneda_original = null;
        
        if($this->payment->create()) {
            // Send payment confirmation email
            // $this->sendPaymentConfirmationEmail($data['residente_id'], $this->payment->id);
            
            flash('Pago registrado correctamente', 'success');
            
            if(isset($_POST['save_and_print'])) {
                redirect('/payments?print=' . $this->payment->id);
                return;
            }
            
            redirect('/payments');
        } else {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/create', [
                'error' => 'Error al registrar el pago',
                'data' => $data,
                'residents' => $residents,
                'medios_pago' => $this->getMediosPago()
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
    
    // Ver e imprimir recibo de pago
    public function receipt($id) {
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
                flash('No tiene permisos para ver este recibo', 'error');
                redirect('/payments');
                return;
            }
        }
        
        $empresa_data = (new Empresa($this->db))->getData();
        
        if(isset($_GET['embed'])) {
            $this->view('payments/receipt_embed', [
                'payment' => $payment_data,
                'is_admin' => isAdmin(),
                'empresa' => $empresa_data
            ]);
            return;
        }
        
        $this->view('payments/receipt', [
            'payment' => $payment_data,
            'is_admin' => isAdmin(),
            'empresa' => $empresa_data
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
            
            // Obtener historial de mora si existe
            $late_fee_history = [];
            if (isset($this->lateFeeHistory)) {
                $history_stmt = $this->lateFeeHistory->getByPaymentId($id);
                $late_fee_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $this->view('admin/payments/edit', [
                'payment' => $payment_data,
                'residents' => $residents,
                'late_fee_history' => $late_fee_history,
                'medios_pago' => $this->getMediosPago()
            ]);
        }
    }
    
    // Actualizar pago
    private function updatePayment($id) {
        $data = $this->getPostData();
        $errors = $this->validateRules('payment.update', $data);
        
        if(!empty($errors)) {
            $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('admin/payments/edit', [
                'errors' => $errors,
                'payment' => $data,
                'residents' => $residents,
                'medios_pago' => $this->getMediosPago()
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
                'residents' => $residents,
                'medios_pago' => $this->getMediosPago()
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
                'residents' => $residents,
                'medios_pago' => $this->getMediosPago()
            ]);
        }
    }

    /**
     * Medios de pago activos para el select del formulario.
     *
     * @return array Lista de medios activos [['id','valor','nombre'], ...]
     */
private function getMediosPago() {
        return $this->medioPago->getActive()->fetchAll(PDO::FETCH_ASSOC);
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
    
    /**
     * Ajustar manualmente la mora de un pago
     * POST /payments/:id/adjust-late-fee
     */
    public function adjustLateFee($id) {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('Método no permitido', 'error');
            redirect('/payments/edit/' . $id);
            return;
        }
        
        // Verificar que el servicio de mora esté disponible
        if (!isset($this->lateFeeService)) {
            flash('El sistema de mora no está disponible', 'error');
            redirect('/payments/edit/' . $id);
            return;
        }
        
        $data = $this->getPostData();
        
        // Validar datos
        $errors = $this->validateRules('payment.late_fee_adjust', $data);
        
        if (!empty($errors)) {
            flash('Error en la validación: ' . implode(', ', $errors), 'error');
            redirect('/payments/edit/' . $id);
            return;
        }
        
        // Obtener pago actual
        $this->payment->id = $id;
        $payment_data = $this->payment->readOne();
        
        if (!$payment_data) {
            flash('Pago no encontrado', 'error');
            redirect('/payments');
            return;
        }
        
        // Obtener usuario actual
        if (!isset($_SESSION['user_id'])) {
            flash('Usuario no autenticado', 'error');
            redirect('/login');
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Aplicar ajuste usando el servicio
        $result = $this->lateFeeService->adjustLateFee(
            $id,
            floatval($data['monto_mora']),
            $user_id,
            $data['justificacion']
        );
        
        if ($result) {
            flash('Mora ajustada correctamente', 'success');
            error_log("[PaymentController] Ajuste manual aplicado - Pago ID: $id, Usuario: $user_id, Nuevo monto: {$data['monto_mora']}");
        } else {
            flash('Error al ajustar la mora', 'error');
        }
        
        redirect('/payments/edit/' . $id);
    }

    // ------------------------------------------------------------------
    // Pagos en Línea (Pago Móvil BCV + Transferencia Bancaria)
    // ------------------------------------------------------------------

    /**
     * Mostrar la pantalla de pago en línea para un pago
     * GET /payments/pay/:id
     */
    public function pay($id) {
        $this->requireAuth();

        $this->payment->id = $id;
        $payment_data = $this->payment->readOne();

        if (!$payment_data) {
            flash('Pago no encontrado', 'error');
            redirect('/payments');
            return;
        }

        // Verificar permisos (residente dueño del pago o admin)
        if (isResident()) {
            $current_user = $this->getCurrentUser();
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if (!$resident_data || $resident_data['id'] != $payment_data['residente_id']) {
                flash('No tiene permisos para pagar este pago', 'error');
                redirect('/payments');
                return;
            }
        }

        // No permitir pagar un pago ya pagado
        if ($payment_data['estado'] === 'pagado') {
            flash('Este pago ya ha sido pagado', 'info');
            redirect('/payments/show/' . $id);
            return;
        }

        // Obtener cuentas bancarias activas
        $bank_accounts = $this->bankAccount->getActive()->fetchAll(PDO::FETCH_ASSOC);

        // Obtener historial de declaraciones del pago
        $declarations = $this->paymentDeclaration->getByPayment($id)->fetchAll(PDO::FETCH_ASSOC);

        // Si no hay cuentas activas, advertir
        if (empty($bank_accounts)) {
            flash('La administración aún no ha configurado cuentas bancarias para recibir pagos en línea', 'warning');
        }

        $this->view('payments/pay', [
            'payment' => $payment_data,
            'bank_accounts' => $bank_accounts,
            'declarations' => $declarations,
            'is_admin' => isAdmin()
        ]);
    }

    /**
     * Registrar una declaración de pago en línea
     * POST /payments/declare/:id
     */
    public function declarePayment($id) {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('Método no permitido', 'error');
            redirect('/payments/pay/' . $id);
            return;
        }

        $this->payment->id = $id;
        $payment_data = $this->payment->readOne();

        if (!$payment_data) {
            flash('Pago no encontrado', 'error');
            redirect('/payments');
            return;
        }

        // No permitir declarar sobre un pago pagado
        if ($payment_data['estado'] === 'pagado') {
            flash('Este pago ya ha sido pagado', 'info');
            redirect('/payments/show/' . $id);
            return;
        }

        // Verificar permisos (residente dueño del pago o admin)
        if (isResident()) {
            $current_user = $this->getCurrentUser();
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if (!$resident_data || $resident_data['id'] != $payment_data['residente_id']) {
                flash('No tiene permisos para declarar este pago', 'error');
                redirect('/payments');
                return;
            }
        }

        // Validar datos
        $data = $this->getPostData();
        $errors = $this->validateRules('payment.declare', $data);

        if (!empty($errors)) {
            flash('Error en la validación: ' . implode(', ', $errors), 'error');
            redirect('/payments/pay/' . $id);
            return;
        }

        // Obtener usuario actual
        $user_id = $_SESSION['user_id'] ?? null;

        // Crear declaración
        $this->paymentDeclaration->pago_id = $id;
        $this->paymentDeclaration->metodo = $data['metodo'];
        $this->paymentDeclaration->banco_origen = $data['banco_origen'];
        $this->paymentDeclaration->referencia_bancaria = $data['referencia_bancaria'];
        $this->paymentDeclaration->monto_declarado = $data['monto_declarado'];
        $this->paymentDeclaration->fecha_operacion = $data['fecha_operacion'];
        $this->paymentDeclaration->hora_operacion = $data['hora_operacion'];
        $this->paymentDeclaration->moneda = $data['moneda'];
        $this->paymentDeclaration->monto_moneda_original = $data['monto_declarado'];
        $this->paymentDeclaration->estado = 'pendiente';
        $this->paymentDeclaration->declarado_por = $user_id;

        if (!$this->paymentDeclaration->create()) {
            flash('Error al registrar la declaración de pago', 'error');
            redirect('/payments/pay/' . $id);
            return;
        }

        error_log("[PaymentController] Declaración de pago creada - ID: " . $this->paymentDeclaration->id . ", Pago ID: $id, Usuario: $user_id");

        // Notificar a administradores (email + in-app)
        $declaration_full = array_merge(
            $this->paymentDeclaration->readOne(),
            $payment_data
        );
        $this->paymentNotificationService->notifyAdminsDeclaration($declaration_full);

        flash('Declaración de pago registrada. La administración la verificará y confirmará. Le avisaremos por email.', 'success');
        redirect('/payments/show/' . $id);
    }

    /**
     * Listar declaraciones de pago (solo admin)
     * GET /payments/declarations
     */
    public function declarations() {
        $this->requireAdmin();

        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

        $declarations_stmt = $this->paymentDeclaration->readAll();
        $all_declarations = $declarations_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Aplicar filtro de estado
        if (!empty($status)) {
            $all_declarations = array_filter($all_declarations, function ($d) use ($status) {
                return $d['estado'] === $status;
            });
        }

        $stats = $this->paymentDeclaration->getStats();

        $this->view('admin/payments/declarations', [
            'declarations' => $all_declarations,
            'stats' => $stats,
            'status' => $status
        ]);
    }

    /**
     * Confirmar una declaración de pago (solo admin)
     * POST /payments/declarations/confirm/:id
     */
    public function confirmDeclaration($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('Método no permitido', 'error');
            redirect('/payments/declarations');
            return;
        }

        $this->paymentDeclaration->id = $id;
        $declaration = $this->paymentDeclaration->readOne();

        if (!$declaration) {
            flash('Declaración no encontrada', 'error');
            redirect('/payments/declarations');
            return;
        }

        if ($declaration['estado'] !== 'pendiente') {
            flash('Esta declaración ya fue procesada', 'error');
            redirect('/payments/declarations');
            return;
        }

        $data = $this->getPostData();
        $notas = !empty($data['notas_admin']) ? $data['notas_admin'] : null;

        // Confirmar declaración
        if (!$this->paymentDeclaration->confirm($_SESSION['user_id'], $notas)) {
            flash('Error al confirmar la declaración', 'error');
            redirect('/payments/declarations');
            return;
        }

        // Marcar el pago como pagado
        $this->payment->id = $declaration['pago_id'];
        $payment_data = $this->payment->readOne();

        if ($payment_data) {
            $this->payment->residente_id = $payment_data['residente_id'];
            $this->payment->monto = $payment_data['monto'];
            $this->payment->concepto = $payment_data['concepto'];
            $this->payment->mes_pago = $payment_data['mes_pago'];
            $this->payment->fecha_pago = $payment_data['fecha_pago'];
            $this->payment->metodo_pago = $declaration['metodo'];
            $this->payment->referencia = $declaration['referencia_bancaria'];
            $this->payment->estado = 'pagado';
            $this->payment->moneda = $declaration['moneda'] ?: 'USD';
            $this->payment->monto_moneda_original = $declaration['monto_moneda_original'];
            $this->payment->monto_original = $payment_data['monto_original'] ?? $payment_data['monto'];
            $this->payment->monto_mora = $payment_data['monto_mora'] ?? 0;
            $this->payment->fecha_aplicacion_mora = $payment_data['fecha_aplicacion_mora'] ?? null;
            $this->payment->regla_mora_id = $payment_data['regla_mora_id'] ?? null;
            $this->payment->update();

            error_log("[PaymentController] Pago marcado como pagado por declaración - Pago ID: " . $declaration['pago_id'] . ", Declaración ID: $id");
        }

        // Obtener datos del residente para notificar
        $resident_data = $this->resident->getByUserId($declaration['usuario_id']);
        if ($resident_data) {
            $this->paymentNotificationService->notifyResidentStatusChange($resident_data, $declaration, 'confirmada', $notas);
        }

        flash('Declaración confirmada y pago marcado como pagado', 'success');
        redirect('/payments/declarations');
    }

    /**
     * Rechazar una declaración de pago (solo admin)
     * POST /payments/declarations/reject/:id
     */
    public function rejectDeclaration($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('Método no permitido', 'error');
            redirect('/payments/declarations');
            return;
        }

        $this->paymentDeclaration->id = $id;
        $declaration = $this->paymentDeclaration->readOne();

        if (!$declaration) {
            flash('Declaración no encontrada', 'error');
            redirect('/payments/declarations');
            return;
        }

        if ($declaration['estado'] !== 'pendiente') {
            flash('Esta declaración ya fue procesada', 'error');
            redirect('/payments/declarations');
            return;
        }

        $data = $this->getPostData();
        $notas = !empty($data['notas_admin']) ? $data['notas_admin'] : null;

        if (empty($notas)) {
            flash('Debe indicar el motivo del rechazo', 'error');
            redirect('/payments/declarations');
            return;
        }

        // Rechazar declaración
        if (!$this->paymentDeclaration->reject($_SESSION['user_id'], $notas)) {
            flash('Error al rechazar la declaración', 'error');
            redirect('/payments/declarations');
            return;
        }

        // Notificar al residente
        $resident_data = $this->resident->getByUserId($declaration['usuario_id']);
        if ($resident_data) {
            $this->paymentNotificationService->notifyResidentStatusChange($resident_data, $declaration, 'rechazada', $notas);
        }

        flash('Declaración rechazada. Se notificó al residente.', 'success');
        redirect('/payments/declarations');
    }

    // ------------------------------------------------------------------
    // Cuentas Bancarias (solo admin)
    // ------------------------------------------------------------------

    /**
     * Listar cuentas bancarias (solo admin)
     * GET /bank-accounts
     */
    public function bankAccounts() {
        $this->requireAdmin();

        $accounts = $this->bankAccount->readAll()->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/bank_accounts/index', [
            'accounts' => $accounts
        ]);
    }

    /**
     * Crear cuenta bancaria (solo admin)
     * GET/POST /bank-accounts/create
     */
    public function bankAccountCreate() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getPostData();
            $errors = $this->validateRules('bank_account.store', $data);

            if (!empty($errors)) {
                $this->view('admin/bank_accounts/create', [
                    'errors' => $errors,
                    'data' => $data
                ]);
                return;
            }

            $this->bankAccount->banco = $data['banco'];
            $this->bankAccount->tipo = $data['tipo'];
            $this->bankAccount->numero_cuenta = $data['numero_cuenta'];
            $this->bankAccount->titular = $data['titular'];
            $this->bankAccount->pago_movil_telefono = $data['pago_movil_telefono'] ?? '';
            $this->bankAccount->pago_movil_cedula = $data['pago_movil_cedula'] ?? '';
            $this->bankAccount->activa = isset($data['activa']) ? true : false;

            if ($this->bankAccount->create()) {
                flash('Cuenta bancaria registrada correctamente', 'success');
                redirect('/bank-accounts');
            } else {
                $this->view('admin/bank_accounts/create', [
                    'error' => 'Error al registrar la cuenta bancaria',
                    'data' => $data
                ]);
            }
            return;
        }

        $this->view('admin/bank_accounts/create');
    }

    /**
     * Editar cuenta bancaria (solo admin)
     * GET/POST /bank-accounts/edit/:id
     */
    public function bankAccountEdit($id) {
        $this->requireAdmin();

        $this->bankAccount->id = $id;
        $account = $this->bankAccount->readOne();

        if (!$account) {
            flash('Cuenta bancaria no encontrada', 'error');
            redirect('/bank-accounts');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getPostData();
            $errors = $this->validateRules('bank_account.update', $data);

            if (!empty($errors)) {
                $this->view('admin/bank_accounts/edit', [
                    'errors' => $errors,
                    'account' => array_merge($account, $data)
                ]);
                return;
            }

            $this->bankAccount->banco = $data['banco'];
            $this->bankAccount->tipo = $data['tipo'];
            $this->bankAccount->numero_cuenta = $data['numero_cuenta'];
            $this->bankAccount->titular = $data['titular'];
            $this->bankAccount->pago_movil_telefono = $data['pago_movil_telefono'] ?? '';
            $this->bankAccount->pago_movil_cedula = $data['pago_movil_cedula'] ?? '';
            $this->bankAccount->activa = isset($data['activa']) ? true : false;

            if ($this->bankAccount->update()) {
                flash('Cuenta bancaria actualizada correctamente', 'success');
                redirect('/bank-accounts');
            } else {
                $this->view('admin/bank_accounts/edit', [
                    'error' => 'Error al actualizar la cuenta bancaria',
                    'account' => array_merge($account, $data)
                ]);
            }
            return;
        }

        $this->view('admin/bank_accounts/edit', [
            'account' => $account
        ]);
    }

    /**
     * Eliminar cuenta bancaria (solo admin)
     * POST /bank-accounts/delete/:id
     */
    public function bankAccountDelete($id) {
        $this->requireAdmin();

        $this->bankAccount->id = $id;
        if ($this->bankAccount->delete()) {
            flash('Cuenta bancaria eliminada correctamente', 'success');
        } else {
            flash('Error al eliminar la cuenta bancaria', 'error');
        }

        redirect('/bank-accounts');
    }
}
?>
