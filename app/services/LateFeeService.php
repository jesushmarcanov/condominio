<?php
/**
 * Servicio de Cálculo de Mora
 * 
 * Encapsula la lógica de negocio para el cálculo automático de recargos por mora
 * en pagos atrasados. Gestiona la aplicación de reglas de mora, ajustes manuales,
 * registro de historial y notificaciones.
 * 
 * Funcionalidades principales:
 * - Calcular mora según reglas configuradas (porcentaje o monto fijo)
 * - Aplicar frecuencias (única, diaria, semanal, mensual)
 * - Aplicar topes máximos
 * - Procesar pagos atrasados automáticamente
 * - Registrar historial de auditoría
 * - Enviar notificaciones de mora aplicada
 * - Gestionar ajustes manuales
 * 
 * @package App\Services
 * @author Sistema de Gestión de Condominio
 * @version 1.0.0
 */

class LateFeeService {
    private $db;
    private $payment;
    private $resident;
    private $lateFeeRule;
    private $lateFeeHistory;
    private $notification;
    private $notificationService;
    
    /**
     * Constructor
     * 
     * Inicializa el servicio con la conexión a la base de datos
     * y crea instancias de los modelos necesarios.
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->db = $db;
        $this->payment = new Payment($db);
        $this->resident = new Resident($db);
        $this->lateFeeRule = new LateFeeRule($db);
        $this->lateFeeHistory = new LateFeeHistory($db);
        $this->notification = new Notification($db);
        $this->notificationService = new NotificationService($db);
    }
    
    /**
     * Procesar pagos atrasados y aplicar mora
     * Punto de entrada principal para el cálculo automático
     * 
     * @return array Resultado del procesamiento con estadísticas
     */
    public function processOverduePayments(): array {
        try {
            error_log("[LateFeeService] Inicio de procesamiento de mora");
            
            // Obtener pagos atrasados sin mora o con mora desactualizada
            $overdue_payments = $this->getOverduePayments();
            
            $processed = 0;
            $late_fees_applied = 0;
            $notifications_sent = 0;
            $errors = 0;
            
            foreach ($overdue_payments as $payment_data) {
                try {
                    $processed++;
                    
                    // Calcular mora
                    $late_fee_amount = $this->calculateLateFee($payment_data);
                    
                    if ($late_fee_amount <= 0) {
                        continue;
                    }
                    
                    // Obtener regla aplicada
                    $rule = $this->findApplicableRule($payment_data);
                    
                    if (!$rule) {
                        continue;
                    }
                    
                    // Aplicar mora
                    if ($this->applyLateFee($payment_data['id'], $late_fee_amount, $rule['id'])) {
                        $late_fees_applied++;
                        
                        // Registrar en historial
                        $days_overdue = $this->getDaysOverdue($payment_data['fecha_pago'], $rule['dias_gracia']);
                        $this->logCalculation(
                            $payment_data['id'],
                            $rule['id'],
                            $late_fee_amount,
                            $late_fee_amount,
                            $days_overdue
                        );
                        
                        // Enviar notificación (solo la primera vez)
                        if ($payment_data['monto_mora'] == 0 || $payment_data['monto_mora'] === null) {
                            $this->resident->id = $payment_data['residente_id'];
                            $resident_data = $this->resident->readOne();
                            
                            if ($resident_data) {
                                $this->sendLateFeeNotification($payment_data, $resident_data, $late_fee_amount);
                                $notifications_sent++;
                            }
                        }
                    }
                    
                } catch (Exception $e) {
                    error_log("[LateFeeService] Error procesando pago ID {$payment_data['id']}: " . $e->getMessage());
                    $errors++;
                }
            }
            
            error_log("[LateFeeService] Procesamiento completado: {$processed} pagos, {$late_fees_applied} moras aplicadas, {$notifications_sent} notificaciones");
            
            return [
                'success' => true,
                'processed' => $processed,
                'late_fees_applied' => $late_fees_applied,
                'notifications_sent' => $notifications_sent,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            error_log("[LateFeeService] Error general: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'processed' => 0
            ];
        }
    }
    
    /**
     * Calcular mora para un pago
     * 
     * Implementa el algoritmo completo de cálculo:
     * 1. Obtener regla aplicable
     * 2. Calcular días de atraso
     * 3. Obtener multiplicador de frecuencia
     * 4. Calcular monto base según tipo
     * 5. Aplicar tope máximo
     * 
     * @param array $payment_data Datos del pago
     * @return float Monto de mora calculado
     */
    public function calculateLateFee(array $payment_data): float {
        try {
            // 1. Obtener regla aplicable
            $rule = $this->findApplicableRule($payment_data);
            
            if (!$rule || !$rule['activa']) {
                error_log("[LateFeeService] No hay regla activa para pago ID: " . $payment_data['id']);
                return 0.00;
            }
            
            // 2. Calcular días de atraso
            $days_overdue = $this->getDaysOverdue($payment_data['fecha_pago'], $rule['dias_gracia']);
            
            if ($days_overdue <= 0) {
                return 0.00;
            }
            
            // 3. Obtener multiplicador de frecuencia
            $frequency_multiplier = $this->getFrequencyMultiplier($rule['frecuencia'], $days_overdue);
            
            // 4. Calcular monto base según tipo
            if ($rule['tipo_recargo'] === 'porcentaje') {
                $base_amount = $this->calculateByPercentage(
                    $payment_data['monto_original'] ?? $payment_data['monto'],
                    $rule['valor_recargo'],
                    $frequency_multiplier
                );
            } else {
                $base_amount = $this->calculateByFixedAmount(
                    $rule['valor_recargo'],
                    $frequency_multiplier
                );
            }
            
            // 5. Aplicar tope máximo si existe
            $final_amount = $this->applyMaxCap($base_amount, $rule['tope_maximo']);
            
            error_log("[LateFeeService] Pago ID: {$payment_data['id']}, Días atraso: {$days_overdue}, Mora calculada: {$final_amount}");
            
            return round($final_amount, 2);
            
        } catch (Exception $e) {
            error_log("[LateFeeService] Error calculando mora: " . $e->getMessage());
            return 0.00;
        }
    }
    
    /**
     * Aplicar mora a un pago
     * 
     * Actualiza los campos de mora en la tabla pagos.
     * 
     * @param int $payment_id ID del pago
     * @param float $amount Monto de mora a aplicar
     * @param int $rule_id ID de la regla aplicada
     * @return bool True si se aplicó correctamente, false en caso contrario
     */
    public function applyLateFee(int $payment_id, float $amount, int $rule_id): bool {
        try {
            $query = "UPDATE pagos 
                      SET monto_mora = :monto_mora,
                          fecha_aplicacion_mora = CURDATE(),
                          regla_mora_id = :regla_mora_id
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':monto_mora', $amount);
            $stmt->bindParam(':regla_mora_id', $rule_id);
            $stmt->bindParam(':id', $payment_id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("[LateFeeService] Error aplicando mora: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar mora de un pago
     * 
     * @param int $payment_id ID del pago
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function removeLateFee(int $payment_id): bool {
        try {
            $query = "UPDATE pagos 
                      SET monto_mora = 0.00,
                          fecha_aplicacion_mora = NULL,
                          regla_mora_id = NULL
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $payment_id);
            
            if ($stmt->execute()) {
                // Registrar eliminación en historial
                $this->lateFeeHistory->pago_id = $payment_id;
                $this->lateFeeHistory->regla_mora_id = null;
                $this->lateFeeHistory->monto_calculado = 0.00;
                $this->lateFeeHistory->monto_aplicado = 0.00;
                $this->lateFeeHistory->dias_atraso = 0;
                $this->lateFeeHistory->tipo_operacion = 'eliminacion';
                $this->lateFeeHistory->usuario_id = null;
                $this->lateFeeHistory->justificacion = 'Mora eliminada';
                $this->lateFeeHistory->create();
                
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("[LateFeeService] Error eliminando mora: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajustar manualmente la mora de un pago
     * 
     * @param int $payment_id ID del pago
     * @param float $new_amount Nuevo monto de mora
     * @param int $user_id ID del usuario que realiza el ajuste
     * @param string $justification Justificación del ajuste
     * @return bool True si se ajustó correctamente, false en caso contrario
     */
    public function adjustLateFee(int $payment_id, float $new_amount, int $user_id, string $justification): bool {
        try {
            // Obtener pago actual
            $this->payment->id = $payment_id;
            $payment_data = $this->payment->readOne();
            
            if (!$payment_data) {
                error_log("[LateFeeService] Pago no encontrado para ajuste: $payment_id");
                return false;
            }
            
            $old_amount = $payment_data['monto_mora'] ?? 0.00;
            
            // Actualizar mora
            $query = "UPDATE pagos 
                      SET monto_mora = :monto_mora,
                          fecha_aplicacion_mora = CURDATE()
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':monto_mora', $new_amount);
            $stmt->bindParam(':id', $payment_id);
            
            if (!$stmt->execute()) {
                return false;
            }
            
            // Registrar ajuste en historial
            $this->lateFeeHistory->pago_id = $payment_id;
            $this->lateFeeHistory->regla_mora_id = $payment_data['regla_mora_id'];
            $this->lateFeeHistory->monto_calculado = $old_amount;
            $this->lateFeeHistory->monto_aplicado = $new_amount;
            $this->lateFeeHistory->dias_atraso = $this->getDaysOverdue($payment_data['fecha_pago'], 0);
            $this->lateFeeHistory->tipo_operacion = 'ajuste_manual';
            $this->lateFeeHistory->usuario_id = $user_id;
            $this->lateFeeHistory->justificacion = $justification;
            $this->lateFeeHistory->create();
            
            error_log("[LateFeeService] Ajuste manual - Pago ID: $payment_id, Usuario: $user_id, Monto anterior: $old_amount, Monto nuevo: $new_amount");
            
            return true;
            
        } catch (Exception $e) {
            error_log("[LateFeeService] Error en ajuste manual: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener desglose detallado de mora de un pago
     * 
     * @param int $payment_id ID del pago
     * @return array Desglose de mora con todos los detalles
     */
    public function getLateFeeBreakdown(int $payment_id): array {
        try {
            $this->payment->id = $payment_id;
            $payment_data = $this->payment->readOne();
            
            if (!$payment_data) {
                return [];
            }
            
            $breakdown = [
                'payment_id' => $payment_id,
                'monto_original' => $payment_data['monto_original'] ?? $payment_data['monto'],
                'monto_mora' => $payment_data['monto_mora'] ?? 0.00,
                'monto_total' => ($payment_data['monto_original'] ?? $payment_data['monto']) + ($payment_data['monto_mora'] ?? 0.00),
                'fecha_aplicacion_mora' => $payment_data['fecha_aplicacion_mora'],
                'regla_mora_id' => $payment_data['regla_mora_id'],
                'dias_atraso' => $this->getDaysOverdue($payment_data['fecha_pago'], 0),
                'history' => []
            ];
            
            // Obtener información de la regla
            if ($payment_data['regla_mora_id']) {
                $this->lateFeeRule->id = $payment_data['regla_mora_id'];
                $rule_data = $this->lateFeeRule->readOne();
                if ($rule_data) {
                    $breakdown['regla_nombre'] = $rule_data['nombre'];
                    $breakdown['regla_tipo'] = $rule_data['tipo_recargo'];
                    $breakdown['regla_valor'] = $rule_data['valor_recargo'];
                    $breakdown['regla_frecuencia'] = $rule_data['frecuencia'];
                }
            }
            
            // Obtener historial
            $history_stmt = $this->lateFeeHistory->getByPaymentId($payment_id);
            $breakdown['history'] = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $breakdown;
            
        } catch (Exception $e) {
            error_log("[LateFeeService] Error obteniendo desglose: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas generales de mora
     * 
     * @return array Estadísticas de mora
     */
    public function getLateFeeStats(): array {
        try {
            $query = "SELECT 
                        COUNT(*) as total_pagos_con_mora,
                        COALESCE(SUM(monto_mora), 0) as total_mora_aplicada,
                        COALESCE(AVG(monto_mora), 0) as promedio_mora,
                        COALESCE(MAX(monto_mora), 0) as mora_maxima,
                        COUNT(CASE WHEN estado IN ('pendiente', 'atrasado') THEN 1 END) as pagos_pendientes_con_mora,
                        COALESCE(SUM(CASE WHEN estado IN ('pendiente', 'atrasado') THEN monto_mora ELSE 0 END), 0) as mora_pendiente_cobro
                      FROM pagos 
                      WHERE monto_mora > 0";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("[LateFeeService] Error obteniendo estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener ingresos mensuales por mora
     * 
     * @param int $months Número de meses a consultar
     * @return array Ingresos mensuales de mora
     */
    public function getMonthlyLateFeeIncome(int $months = 12): array {
        try {
            $query = "SELECT 
                        DATE_FORMAT(fecha_aplicacion_mora, '%Y-%m') as mes,
                        SUM(monto_mora) as ingresos_mora,
                        COUNT(*) as cantidad_pagos
                      FROM pagos 
                      WHERE monto_mora > 0 
                      AND fecha_aplicacion_mora >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                      GROUP BY DATE_FORMAT(fecha_aplicacion_mora, '%Y-%m')
                      ORDER BY mes ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':months', $months);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("[LateFeeService] Error obteniendo ingresos mensuales: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener pagos atrasados que requieren cálculo de mora
     * 
     * @return array Lista de pagos atrasados
     */
    private function getOverduePayments(): array {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email 
                  FROM pagos p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.estado IN ('pendiente', 'atrasado')
                  AND p.fecha_pago < CURDATE()
                  ORDER BY p.fecha_pago ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular días de atraso considerando período de gracia
     * 
     * @param string $due_date Fecha de vencimiento
     * @param int $grace_days Días de gracia
     * @return int Días de atraso (0 si está dentro del período de gracia)
     */
    private function getDaysOverdue(string $due_date, int $grace_days): int {
        $due = new DateTime($due_date);
        $today = new DateTime();
        $interval = $today->diff($due);
        $days = $interval->days;
        
        // Si la fecha de vencimiento es futura, no hay atraso
        if ($today < $due) {
            return 0;
        }
        
        // Restar días de gracia
        $days_overdue = $days - $grace_days;
        
        return max(0, $days_overdue);
    }
    
    /**
     * Calcular multiplicador según frecuencia
     * 
     * @param string $frequency Frecuencia (unica, diaria, semanal, mensual)
     * @param int $days_overdue Días de atraso
     * @return int Multiplicador de frecuencia
     */
    private function getFrequencyMultiplier(string $frequency, int $days_overdue): int {
        switch ($frequency) {
            case 'unica':
                return 1;
            case 'diaria':
                return $days_overdue;
            case 'semanal':
                return floor($days_overdue / 7);
            case 'mensual':
                return floor($days_overdue / 30);
            default:
                return 1;
        }
    }
    
    /**
     * Calcular mora por porcentaje
     * 
     * @param float $original_amount Monto original del pago
     * @param float $percentage Porcentaje de recargo
     * @param int $multiplier Multiplicador de frecuencia
     * @return float Monto de mora calculado
     */
    private function calculateByPercentage(float $original_amount, float $percentage, int $multiplier): float {
        return ($original_amount * $percentage / 100) * $multiplier;
    }
    
    /**
     * Calcular mora por monto fijo
     * 
     * @param float $fixed_amount Monto fijo de recargo
     * @param int $multiplier Multiplicador de frecuencia
     * @return float Monto de mora calculado
     */
    private function calculateByFixedAmount(float $fixed_amount, int $multiplier): float {
        return $fixed_amount * $multiplier;
    }
    
    /**
     * Aplicar tope máximo al monto calculado
     * 
     * @param float $calculated_amount Monto calculado
     * @param float|null $max_cap Tope máximo (null = sin tope)
     * @return float Monto final con tope aplicado
     */
    private function applyMaxCap(float $calculated_amount, ?float $max_cap): float {
        if ($max_cap === null || $max_cap <= 0) {
            return $calculated_amount;
        }
        
        return min($calculated_amount, $max_cap);
    }
    
    /**
     * Buscar regla aplicable para un pago
     * 
     * Prioridad: regla específica por tipo de pago > regla global
     * 
     * @param array $payment_data Datos del pago
     * @return array|false Datos de la regla o false si no existe
     */
    private function findApplicableRule(array $payment_data): array|false {
        // Primero buscar regla específica por tipo de pago
        $specific_rule = $this->lateFeeRule->getRuleForPaymentType($payment_data['concepto']);
        
        if ($specific_rule && $specific_rule['activa']) {
            return $specific_rule;
        }
        
        // Si no hay específica, buscar regla global (tipo_pago = NULL)
        $global_rule = $this->lateFeeRule->getGlobalRule();
        
        if ($global_rule && $global_rule['activa']) {
            return $global_rule;
        }
        
        return false;
    }
    
    /**
     * Registrar cálculo en historial
     * 
     * @param int $payment_id ID del pago
     * @param int $rule_id ID de la regla aplicada
     * @param float $calculated Monto calculado
     * @param float $applied Monto aplicado
     * @param int $days_overdue Días de atraso
     * @return bool True si se registró correctamente
     */
    private function logCalculation(int $payment_id, int $rule_id, float $calculated, float $applied, int $days_overdue): bool {
        $this->lateFeeHistory->pago_id = $payment_id;
        $this->lateFeeHistory->regla_mora_id = $rule_id;
        $this->lateFeeHistory->monto_calculado = $calculated;
        $this->lateFeeHistory->monto_aplicado = $applied;
        $this->lateFeeHistory->dias_atraso = $days_overdue;
        $this->lateFeeHistory->tipo_operacion = 'calculo_automatico';
        $this->lateFeeHistory->usuario_id = null;
        $this->lateFeeHistory->justificacion = null;
        
        return $this->lateFeeHistory->create();
    }
    
    /**
     * Enviar notificación de mora aplicada
     * 
     * @param array $payment_data Datos del pago
     * @param array $resident_data Datos del residente
     * @param float $late_fee_amount Monto de mora aplicado
     * @return void
     */
    private function sendLateFeeNotification(array $payment_data, array $resident_data, float $late_fee_amount): void {
        try {
            // Crear notificación en sistema
            $titulo = "Recargo por Mora Aplicado - " . $payment_data['concepto'];
            $mensaje = "Estimado residente,\n\n";
            $mensaje .= "Se ha aplicado un recargo por mora a su pago:\n\n";
            $mensaje .= "Concepto: " . $payment_data['concepto'] . "\n";
            $mensaje .= "Monto original: $" . number_format($payment_data['monto_original'] ?? $payment_data['monto'], 2) . "\n";
            $mensaje .= "Recargo por mora: $" . number_format($late_fee_amount, 2) . "\n";
            $mensaje .= "Monto total: $" . number_format(($payment_data['monto_original'] ?? $payment_data['monto']) + $late_fee_amount, 2) . "\n";
            $mensaje .= "Fecha de vencimiento: " . $payment_data['fecha_pago'] . "\n\n";
            $mensaje .= "Por favor, regularice su situación a la brevedad.";
            
            $this->notification->usuario_id = $resident_data['usuario_id'];
            $this->notification->titulo = $titulo;
            $this->notification->mensaje = $mensaje;
            $this->notification->tipo = 'warning';
            $this->notification->leida = false;
            
            $this->notification->create();
            
            error_log("[LateFeeService] Notificación de mora creada para usuario ID: " . $resident_data['usuario_id']);
            
        } catch (Exception $e) {
            error_log("[LateFeeService] Error enviando notificación: " . $e->getMessage());
        }
    }
}
?>
