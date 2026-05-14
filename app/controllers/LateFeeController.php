<?php
/**
 * Controlador de Reglas de Mora
 * 
 * Gestiona las operaciones CRUD de reglas de mora y funcionalidades relacionadas.
 * Solo accesible para administradores.
 * 
 * @package App\Controllers
 * @author Sistema de Gestión de Condominio
 * @version 1.0.0
 */

class LateFeeController extends Controller {
    private $lateFeeRule;
    private $lateFeeService;
    private $payment;
    
    public function __construct() {
        parent::__construct();
        $this->lateFeeRule = new LateFeeRule($this->db);
        $this->lateFeeService = new LateFeeService($this->db);
        $this->payment = new Payment($this->db);
    }
    
    /**
     * Listar todas las reglas de mora
     * GET /admin/late-fee-rules
     */
    public function index() {
        $this->requireAdmin();
        
        $rules = $this->lateFeeRule->readAll();
        $rules_list = $rules->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('admin/late_fee_rules/index', [
            'rules' => $rules_list
        ]);
    }
    
    /**
     * Mostrar formulario de creación de regla
     * GET /admin/late-fee-rules/create
     */
    public function create() {
        $this->requireAdmin();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
        } else {
            $this->view('admin/late_fee_rules/create', [
                'data' => []
            ]);
        }
    }
    
    /**
     * Guardar nueva regla de mora
     * POST /admin/late-fee-rules
     */
    public function store() {
        $this->requireAdmin();
        
        $data = $this->getPostData();
        
        // Validar datos
        $errors = $this->validateLateFeeRule($data);
        
        if(!empty($errors)) {
            $this->view('admin/late_fee_rules/create', [
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }
        
        // Crear regla
        $this->lateFeeRule->nombre = $data['nombre'];
        $this->lateFeeRule->dias_gracia = $data['dias_gracia'];
        $this->lateFeeRule->tipo_recargo = $data['tipo_recargo'];
        $this->lateFeeRule->valor_recargo = $data['valor_recargo'];
        $this->lateFeeRule->frecuencia = $data['frecuencia'];
        $this->lateFeeRule->tope_maximo = !empty($data['tope_maximo']) ? $data['tope_maximo'] : null;
        $this->lateFeeRule->tipo_pago = !empty($data['tipo_pago']) ? $data['tipo_pago'] : null;
        $this->lateFeeRule->activa = isset($data['activa']) ? 1 : 0;
        
        if($this->lateFeeRule->create()) {
            flash('Regla de mora creada correctamente', 'success');
            redirect('/admin/late-fee-rules');
        } else {
            $this->view('admin/late_fee_rules/create', [
                'error' => 'Error al crear la regla de mora',
                'data' => $data
            ]);
        }
    }
    
    /**
     * Mostrar formulario de edición de regla
     * GET /admin/late-fee-rules/:id/edit
     */
    public function edit($id) {
        $this->requireAdmin();
        
        $this->lateFeeRule->id = $id;
        $rule_data = $this->lateFeeRule->readOne();
        
        if(!$rule_data) {
            flash('Regla de mora no encontrada', 'error');
            redirect('/admin/late-fee-rules');
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
        } else {
            $this->view('admin/late_fee_rules/edit', [
                'rule' => $rule_data
            ]);
        }
    }
    
    /**
     * Actualizar regla de mora existente
     * POST /admin/late-fee-rules/:id
     */
    public function update($id) {
        $this->requireAdmin();
        
        $data = $this->getPostData();
        
        // Validar datos
        $errors = $this->validateLateFeeRule($data);
        
        if(!empty($errors)) {
            $this->view('admin/late_fee_rules/edit', [
                'errors' => $errors,
                'rule' => $data
            ]);
            return;
        }
        
        // Actualizar regla
        $this->lateFeeRule->id = $id;
        $this->lateFeeRule->nombre = $data['nombre'];
        $this->lateFeeRule->dias_gracia = $data['dias_gracia'];
        $this->lateFeeRule->tipo_recargo = $data['tipo_recargo'];
        $this->lateFeeRule->valor_recargo = $data['valor_recargo'];
        $this->lateFeeRule->frecuencia = $data['frecuencia'];
        $this->lateFeeRule->tope_maximo = !empty($data['tope_maximo']) ? $data['tope_maximo'] : null;
        $this->lateFeeRule->tipo_pago = !empty($data['tipo_pago']) ? $data['tipo_pago'] : null;
        $this->lateFeeRule->activa = isset($data['activa']) ? 1 : 0;
        
        if($this->lateFeeRule->update()) {
            flash('Regla de mora actualizada correctamente', 'success');
            redirect('/admin/late-fee-rules');
        } else {
            $this->view('admin/late_fee_rules/edit', [
                'error' => 'Error al actualizar la regla de mora',
                'rule' => $data
            ]);
        }
    }
    
    /**
     * Eliminar regla de mora
     * POST /admin/late-fee-rules/:id/delete
     */
    public function delete($id) {
        $this->requireAdmin();
        
        $this->lateFeeRule->id = $id;
        
        // Verificar si la regla puede ser eliminada
        if(!$this->lateFeeRule->canDelete()) {
            flash('No se puede eliminar esta regla porque tiene mora aplicada en pagos', 'error');
            redirect('/admin/late-fee-rules');
            return;
        }
        
        if($this->lateFeeRule->delete()) {
            flash('Regla de mora eliminada correctamente', 'success');
        } else {
            flash('Error al eliminar la regla de mora', 'error');
        }
        
        redirect('/admin/late-fee-rules');
    }
    
    /**
     * Activar/desactivar regla de mora
     * POST /admin/late-fee-rules/:id/toggle
     */
    public function toggle($id) {
        $this->requireAdmin();
        
        $this->lateFeeRule->id = $id;
        $rule_data = $this->lateFeeRule->readOne();
        
        if(!$rule_data) {
            flash('Regla de mora no encontrada', 'error');
            redirect('/admin/late-fee-rules');
            return;
        }
        
        // Cambiar estado
        if($rule_data['activa']) {
            $result = $this->lateFeeRule->deactivate();
            $message = 'Regla de mora desactivada correctamente';
        } else {
            $result = $this->lateFeeRule->activate();
            $message = 'Regla de mora activada correctamente';
        }
        
        if($result) {
            flash($message, 'success');
        } else {
            flash('Error al cambiar el estado de la regla', 'error');
        }
        
        redirect('/admin/late-fee-rules');
    }
    
    /**
     * Simulador de cálculo de mora
     * GET /admin/late-fee-rules/simulate
     * 
     * Permite simular el cálculo de mora para un monto y días de atraso específicos
     * Requirement 6.4
     */
    public function simulate() {
        $this->requireAdmin();
        
        $result = null;
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getPostData();
            
            // Validar datos de simulación
            $errors = $this->validate($data, [
                'monto' => ['required' => true, 'numeric' => true, 'min' => 0.01],
                'dias_atraso' => ['required' => true, 'numeric' => true, 'min' => 0],
                'regla_id' => ['required' => true, 'numeric' => true]
            ]);
            
            if(empty($errors)) {
                // Obtener regla seleccionada
                $this->lateFeeRule->id = $data['regla_id'];
                $rule = $this->lateFeeRule->readOne();
                
                if($rule) {
                    // Crear datos de pago simulado
                    $simulated_payment = [
                        'id' => 0,
                        'monto_original' => $data['monto'],
                        'monto' => $data['monto'],
                        'fecha_pago' => date('Y-m-d', strtotime('-' . $data['dias_atraso'] . ' days')),
                        'concepto' => 'Simulación'
                    ];
                    
                    // Calcular mora usando el servicio
                    $late_fee = $this->lateFeeService->calculateLateFee($simulated_payment);
                    
                    // Calcular detalles paso a paso
                    $dias_efectivos = max(0, $data['dias_atraso'] - $rule['dias_gracia']);
                    
                    // Calcular multiplicador de frecuencia
                    switch($rule['frecuencia']) {
                        case 'unica':
                            $multiplicador = 1;
                            break;
                        case 'diaria':
                            $multiplicador = $dias_efectivos;
                            break;
                        case 'semanal':
                            $multiplicador = floor($dias_efectivos / 7);
                            break;
                        case 'mensual':
                            $multiplicador = floor($dias_efectivos / 30);
                            break;
                        default:
                            $multiplicador = 1;
                    }
                    
                    // Calcular monto base
                    if($rule['tipo_recargo'] === 'porcentaje') {
                        $monto_base = ($data['monto'] * $rule['valor_recargo'] / 100) * $multiplicador;
                    } else {
                        $monto_base = $rule['valor_recargo'] * $multiplicador;
                    }
                    
                    // Aplicar tope si existe
                    $tope_aplicado = false;
                    if($rule['tope_maximo'] && $monto_base > $rule['tope_maximo']) {
                        $monto_base = $rule['tope_maximo'];
                        $tope_aplicado = true;
                    }
                    
                    $result = [
                        'monto_original' => $data['monto'],
                        'dias_atraso' => $data['dias_atraso'],
                        'regla' => $rule,
                        'dias_gracia' => $rule['dias_gracia'],
                        'dias_efectivos' => $dias_efectivos,
                        'multiplicador' => $multiplicador,
                        'monto_mora' => $late_fee,
                        'monto_total' => $data['monto'] + $late_fee,
                        'tope_aplicado' => $tope_aplicado,
                        'explicacion' => $this->generateExplanation($rule, $data['monto'], $dias_efectivos, $multiplicador, $late_fee)
                    ];
                } else {
                    $errors['regla_id'] = 'Regla de mora no encontrada';
                }
            }
            
            $this->view('admin/late_fee_rules/simulate', [
                'rules' => $this->lateFeeRule->getActiveRules()->fetchAll(PDO::FETCH_ASSOC),
                'data' => $data,
                'errors' => $errors,
                'result' => $result
            ]);
        } else {
            // GET request - mostrar formulario
            $this->view('admin/late_fee_rules/simulate', [
                'rules' => $this->lateFeeRule->getActiveRules()->fetchAll(PDO::FETCH_ASSOC),
                'data' => [],
                'errors' => [],
                'result' => null
            ]);
        }
    }
    
    /**
     * Ajustar manualmente la mora de un pago
     * POST /admin/payments/:id/adjust-late-fee
     * 
     * Permite al administrador ajustar manualmente el monto de mora con justificación
     * Requirements 3.7, 3.8, 6.6
     */
    public function adjustLateFee($id) {
        $this->requireAdmin();
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('Método no permitido', 'error');
            redirect('/admin/payments/' . $id);
            return;
        }
        
        $data = $this->getPostData();
        
        // Validar datos
        $errors = $this->validate($data, [
            'monto_mora' => ['required' => true, 'numeric' => true, 'min' => 0],
            'justificacion' => ['required' => true, 'min' => 10, 'max' => 500]
        ]);
        
        if(!empty($errors)) {
            flash('Error en la validación: ' . implode(', ', $errors), 'error');
            redirect('/admin/payments/' . $id);
            return;
        }
        
        // Obtener pago actual
        $this->payment->id = $id;
        $payment_data = $this->payment->readOne();
        
        if(!$payment_data) {
            flash('Pago no encontrado', 'error');
            redirect('/admin/payments');
            return;
        }
        
        // Obtener usuario actual
        if(!isset($_SESSION['user_id'])) {
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
        
        if($result) {
            flash('Mora ajustada correctamente', 'success');
            error_log("[LateFeeController] Ajuste manual aplicado - Pago ID: $id, Usuario: $user_id, Nuevo monto: {$data['monto_mora']}");
        } else {
            flash('Error al ajustar la mora', 'error');
        }
        
        redirect('/admin/payments/' . $id);
    }
    
    /**
     * Generar explicación detallada del cálculo de mora
     * 
     * @param array $rule Regla de mora aplicada
     * @param float $monto Monto original
     * @param int $dias_efectivos Días efectivos de atraso
     * @param int $multiplicador Multiplicador de frecuencia
     * @param float $mora_calculada Mora calculada final
     * @return string Explicación del cálculo
     */
    private function generateExplanation($rule, $monto, $dias_efectivos, $multiplicador, $mora_calculada) {
        $explicacion = "Cálculo de mora paso a paso:\n\n";
        
        // Paso 1: Días de gracia
        $explicacion .= "1. Período de gracia: {$rule['dias_gracia']} días\n";
        $explicacion .= "   Días efectivos de atraso: {$dias_efectivos} días\n\n";
        
        // Paso 2: Frecuencia
        $explicacion .= "2. Frecuencia: {$rule['frecuencia']}\n";
        $explicacion .= "   Multiplicador aplicado: {$multiplicador}\n\n";
        
        // Paso 3: Cálculo base
        if($rule['tipo_recargo'] === 'porcentaje') {
            $explicacion .= "3. Tipo de recargo: Porcentaje ({$rule['valor_recargo']}%)\n";
            $explicacion .= "   Cálculo: \${$monto} × {$rule['valor_recargo']}% × {$multiplicador}\n";
        } else {
            $explicacion .= "3. Tipo de recargo: Monto fijo (\${$rule['valor_recargo']})\n";
            $explicacion .= "   Cálculo: \${$rule['valor_recargo']} × {$multiplicador}\n";
        }
        
        // Paso 4: Tope máximo
        if($rule['tope_maximo']) {
            $explicacion .= "\n4. Tope máximo configurado: \${$rule['tope_maximo']}\n";
            if($mora_calculada >= $rule['tope_maximo']) {
                $explicacion .= "   ⚠ Tope aplicado - Mora limitada al máximo\n";
            }
        }
        
        $explicacion .= "\nMora final: \$" . number_format($mora_calculada, 2);
        
        return $explicacion;
    }
    
    /**
     * Reporte de mora con filtros
     * GET /admin/late-fees/report
     * 
     * Genera un reporte detallado de pagos con mora aplicada
     * Requirements 4.2, 4.3, 4.8
     */
    public function report() {
        $this->requireAdmin();
        
        // Obtener filtros
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
        $estado = isset($_GET['estado']) ? sanitize($_GET['estado']) : '';
        $export = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        
        // Construir query con filtros
        $query = "SELECT 
                    p.id,
                    p.concepto,
                    p.mes_pago,
                    p.fecha_pago,
                    p.monto_original,
                    p.monto_mora,
                    p.fecha_aplicacion_mora,
                    p.estado,
                    r.apartamento,
                    u.nombre as residente_nombre,
                    lfr.nombre as regla_nombre
                  FROM pagos p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN late_fee_rules lfr ON p.regla_mora_id = lfr.id
                  WHERE p.monto_mora > 0";
        
        $params = [];
        
        if(!empty($start_date)) {
            $query .= " AND p.fecha_aplicacion_mora >= :start_date";
            $params[':start_date'] = $start_date;
        }
        
        if(!empty($end_date)) {
            $query .= " AND p.fecha_aplicacion_mora <= :end_date";
            $params[':end_date'] = $end_date;
        }
        
        if(!empty($estado)) {
            $query .= " AND p.estado = :estado";
            $params[':estado'] = $estado;
        }
        
        $query .= " ORDER BY p.fecha_aplicacion_mora DESC";
        
        $stmt = $this->db->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular totales
        $total_mora = 0;
        $total_original = 0;
        foreach($payments as $payment) {
            $total_mora += $payment['monto_mora'];
            $total_original += $payment['monto_original'];
        }
        
        // Exportar si se solicita
        if($export === 'excel') {
            $this->exportReportToExcel($payments, $total_mora, $total_original);
            return;
        }
        
        $this->view('admin/late_fees/report', [
            'payments' => $payments,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'estado' => $estado,
            'total_mora' => $total_mora,
            'total_original' => $total_original,
            'total_general' => $total_original + $total_mora
        ]);
    }
    
    /**
     * Dashboard de estadísticas de mora
     * GET /admin/late-fees/stats
     * 
     * Muestra estadísticas generales y gráficos de mora
     * Requirements 4.2, 4.3, 4.8
     */
    public function stats() {
        $this->requireAdmin();
        
        // Obtener estadísticas generales
        $stats = $this->lateFeeService->getLateFeeStats();
        
        // Obtener ingresos mensuales (últimos 12 meses)
        $monthly_income = $this->lateFeeService->getMonthlyLateFeeIncome(12);
        
        // Obtener top residentes con mora
        $query = "SELECT 
                    r.apartamento,
                    u.nombre as residente_nombre,
                    COUNT(p.id) as total_pagos_con_mora,
                    SUM(p.monto_mora) as total_mora,
                    SUM(CASE WHEN p.estado IN ('pendiente', 'atrasado') THEN p.monto_mora ELSE 0 END) as mora_pendiente
                  FROM pagos p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.monto_mora > 0
                  GROUP BY r.id, r.apartamento, u.nombre
                  ORDER BY total_mora DESC
                  LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $top_residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener distribución por regla
        $query = "SELECT 
                    lfr.nombre as regla_nombre,
                    COUNT(p.id) as total_pagos,
                    SUM(p.monto_mora) as total_mora
                  FROM pagos p
                  LEFT JOIN late_fee_rules lfr ON p.regla_mora_id = lfr.id
                  WHERE p.monto_mora > 0
                  GROUP BY lfr.id, lfr.nombre
                  ORDER BY total_mora DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $rules_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('admin/late_fees/stats', [
            'stats' => $stats,
            'monthly_income' => $monthly_income,
            'top_residents' => $top_residents,
            'rules_distribution' => $rules_distribution
        ]);
    }
    
    /**
     * Exportar reporte de mora a Excel
     * 
     * @param array $payments Datos de pagos
     * @param float $total_mora Total de mora
     * @param float $total_original Total original
     */
    private function exportReportToExcel($payments, $total_mora, $total_original) {
        require_once APP_PATH . '/services/ExcelService.php';
        
        $excelService = new ExcelService();
        
        // Preparar datos para exportación
        $headers = [
            'ID',
            'Apartamento',
            'Residente',
            'Concepto',
            'Mes Pago',
            'Fecha Vencimiento',
            'Monto Original',
            'Monto Mora',
            'Monto Total',
            'Fecha Aplicación Mora',
            'Regla Aplicada',
            'Estado'
        ];
        
        $data = [];
        foreach($payments as $payment) {
            $data[] = [
                $payment['id'],
                $payment['apartamento'],
                $payment['residente_nombre'],
                $payment['concepto'],
                $payment['mes_pago'],
                $payment['fecha_pago'],
                number_format($payment['monto_original'], 2),
                number_format($payment['monto_mora'], 2),
                number_format($payment['monto_original'] + $payment['monto_mora'], 2),
                $payment['fecha_aplicacion_mora'],
                $payment['regla_nombre'] ?? 'N/A',
                $payment['estado']
            ];
        }
        
        // Agregar fila de totales
        $data[] = [
            '',
            '',
            '',
            '',
            '',
            'TOTALES:',
            number_format($total_original, 2),
            number_format($total_mora, 2),
            number_format($total_original + $total_mora, 2),
            '',
            '',
            ''
        ];
        
        $filename = 'reporte_mora_' . date('Y-m-d') . '.xlsx';
        $excelService->exportToExcel($headers, $data, $filename, 'Reporte de Mora');
    }
    
    /**
     * Validar datos de regla de mora
     * 
     * @param array $data Datos a validar
     * @return array Errores de validación
     */
    private function validateLateFeeRule($data) {
        $errors = $this->validate($data, [
            'nombre' => ['required' => true, 'max' => 100],
            'dias_gracia' => ['required' => true, 'numeric' => true],
            'tipo_recargo' => ['required' => true, 'in' => ['porcentaje', 'monto_fijo']],
            'valor_recargo' => ['required' => true, 'numeric' => true],
            'frecuencia' => ['required' => true, 'in' => ['unica', 'diaria', 'semanal', 'mensual']],
            'tope_maximo' => ['numeric' => true],
            'tipo_pago' => ['max' => 50]
        ]);
        
        // Validaciones adicionales
        if(isset($data['dias_gracia']) && is_numeric($data['dias_gracia']) && $data['dias_gracia'] < 0) {
            $errors['dias_gracia'] = 'Los días de gracia no pueden ser negativos';
        }
        
        if(isset($data['valor_recargo']) && is_numeric($data['valor_recargo'])) {
            if($data['valor_recargo'] <= 0) {
                $errors['valor_recargo'] = 'El valor del recargo debe ser mayor a 0';
            }
            
            // Validar rango de porcentaje
            if(isset($data['tipo_recargo']) && $data['tipo_recargo'] === 'porcentaje') {
                if($data['valor_recargo'] < 0.01 || $data['valor_recargo'] > 100) {
                    $errors['valor_recargo'] = 'El porcentaje debe estar entre 0.01 y 100';
                }
            }
        }
        
        if(isset($data['tope_maximo']) && !empty($data['tope_maximo']) && is_numeric($data['tope_maximo'])) {
            if($data['tope_maximo'] < 0) {
                $errors['tope_maximo'] = 'El tope máximo no puede ser negativo';
            }
        }
        
        return $errors;
    }
}
?>
