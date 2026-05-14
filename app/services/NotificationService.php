<?php
/**
 * Servicio de Notificaciones
 * 
 * Encapsula la lógica de negocio para la detección automática de pagos vencidos
 * y la generación de notificaciones persistentes en la base de datos.
 * 
 * Este servicio es utilizado tanto por el controlador web como por el script
 * de ejecución programada (cron job).
 * 
 * Funcionalidades principales:
 * - Detectar pagos con estado pendiente o atrasado cuya fecha de pago ha vencido
 * - Generar notificaciones automáticas sin duplicados
 * - Actualizar el estado de pagos pendientes a atrasados
 * - Registrar logs detallados de cada operación
 * 
 * @package App\Services
 * @author Sistema de Gestión de Condominio
 * @version 1.0.0
 */

class NotificationService {
    private $db;
    private $payment;
    private $resident;
    private $notification;
    private $emailService;
    
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
        $this->notification = new Notification($db);
        $this->emailService = new EmailService($db);
    }
    
    /**
     * Procesar pagos vencidos y generar notificaciones
     * Punto de entrada principal del servicio
     * 
     * @return array Resultado del procesamiento con estadísticas
     */
    public function processOverduePayments() {
        try {
            error_log("[NotificationService] Inicio de detección de pagos vencidos");
            
            $overdue_payments = $this->detectOverduePayments();
            $notifications_created = 0;
            $payments_updated = 0;
            $errors = 0;
            
            foreach ($overdue_payments as $payment_data) {
                try {
                    // Obtener información del residente
                    $this->resident->id = $payment_data['residente_id'];
                    $resident_data = $this->resident->readOne();
                    
                    if (!$resident_data) {
                        error_log("[NotificationService] Residente no encontrado para pago ID: " . $payment_data['id']);
                        $errors++;
                        continue;
                    }
                    
                    // Generar notificación
                    if ($this->generateNotification($payment_data, $resident_data)) {
                        $notifications_created++;
                    }
                    
                    // Actualizar estado de pago pendiente a atrasado
                    if ($payment_data['estado'] === 'pendiente') {
                        if ($this->updatePaymentToOverdue($payment_data['id'])) {
                            $payments_updated++;
                        }
                    }
                    
                } catch (Exception $e) {
                    error_log("[NotificationService] Error procesando pago ID " . $payment_data['id'] . ": " . $e->getMessage());
                    $errors++;
                }
            }
            
            error_log("[NotificationService] Procesados: " . count($overdue_payments) . " pagos, Notificaciones creadas: " . $notifications_created . ", Pagos actualizados: " . $payments_updated . ", Errores: " . $errors);
            
            return [
                'success' => true,
                'processed' => count($overdue_payments),
                'notifications_created' => $notifications_created,
                'payments_updated' => $payments_updated,
                'errors' => $errors
            ];
            
        } catch (PDOException $e) {
            error_log("[NotificationService] Error de base de datos: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error de conexión a base de datos',
                'processed' => 0
            ];
        } catch (Exception $e) {
            error_log("[NotificationService] Error general: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'processed' => 0
            ];
        }
    }
    
    /**
     * Detectar pagos vencidos
     * Busca pagos con estado pendiente o atrasado cuya fecha de pago es anterior a hoy
     * 
     * @return array Lista de pagos vencidos
     */
    private function detectOverduePayments() {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email 
                  FROM pagos p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE (p.estado = 'pendiente' OR p.estado = 'atrasado')
                  AND p.fecha_pago < CURDATE()
                  ORDER BY p.fecha_pago ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generar notificación para un pago vencido
     * 
     * @param array $payment_data Datos del pago vencido
     * @param array $resident_data Datos del residente
     * @return bool True si se creó la notificación, false si ya existe o hubo error
     */
    private function generateNotification($payment_data, $resident_data) {
        try {
            // Validar datos requeridos
            if (empty($payment_data['concepto']) || empty($payment_data['monto'])) {
                error_log("[NotificationService] Datos de pago incompletos para ID: " . $payment_data['id']);
                return false;
            }
            
            if (empty($resident_data['usuario_id'])) {
                error_log("[NotificationService] Usuario ID no encontrado para residente ID: " . $payment_data['residente_id']);
                return false;
            }
            
            // Construir título y mensaje
            $titulo = $this->buildNotificationTitle($payment_data['concepto']);
            $mensaje = $this->buildNotificationMessage($payment_data);
            
            // Verificar duplicados
            if ($this->checkDuplicate($resident_data['usuario_id'], $titulo)) {
                error_log("[NotificationService] Notificación duplicada omitida para usuario ID: " . $resident_data['usuario_id']);
                return false;
            }
            
            // Crear notificación
            $this->notification->usuario_id = $resident_data['usuario_id'];
            $this->notification->titulo = $titulo;
            $this->notification->mensaje = $mensaje;
            $this->notification->tipo = 'warning';
            $this->notification->leida = false;
            
            if (!$this->notification->create()) {
                error_log("[NotificationService] Error al crear notificación para usuario ID: " . $resident_data['usuario_id']);
                return false;
            }
            
            error_log("[NotificationService] Notificación creada para usuario ID: " . $resident_data['usuario_id'] . ", Pago ID: " . $payment_data['id']);
            
            // Send email notification
            $this->sendPaymentEmail($payment_data, $resident_data, 'overdue');
            
            return true;
            
        } catch (Exception $e) {
            error_log("[NotificationService] Excepción al generar notificación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si existe notificación duplicada
     * 
     * @param int $usuario_id ID del usuario
     * @param string $titulo Título de la notificación
     * @return bool True si existe duplicado, false si no
     */
    private function checkDuplicate($usuario_id, $titulo) {
        $duplicate = $this->notification->findDuplicate($usuario_id, $titulo, false);
        return $duplicate !== null;
    }
    
    /**
     * Actualizar estado de pago pendiente a atrasado
     * 
     * @param int $payment_id ID del pago
     * @return bool True si se actualizó correctamente, false si hubo error
     */
    private function updatePaymentToOverdue($payment_id) {
        try {
            $this->payment->id = $payment_id;
            $payment_data = $this->payment->readOne();
            
            if (!$payment_data) {
                error_log("[NotificationService] Pago no encontrado para actualizar ID: " . $payment_id);
                return false;
            }
            
            // Actualizar solo si el estado es pendiente
            if ($payment_data['estado'] !== 'pendiente') {
                return false;
            }
            
            $this->payment->residente_id = $payment_data['residente_id'];
            $this->payment->monto = $payment_data['monto'];
            $this->payment->concepto = $payment_data['concepto'];
            $this->payment->mes_pago = $payment_data['mes_pago'];
            $this->payment->fecha_pago = $payment_data['fecha_pago'];
            $this->payment->metodo_pago = $payment_data['metodo_pago'];
            $this->payment->referencia = $payment_data['referencia'];
            $this->payment->estado = 'atrasado';
            
            if (!$this->payment->update()) {
                error_log("[NotificationService] Error al actualizar estado de pago ID: " . $payment_id);
                return false;
            }
            
            error_log("[NotificationService] Estado actualizado a 'atrasado' para pago ID: " . $payment_id);
            return true;
            
        } catch (PDOException $e) {
            error_log("[NotificationService] Excepción al actualizar pago: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Construir título de notificación
     * 
     * @param string $concepto Concepto del pago
     * @return string Título formateado
     */
    private function buildNotificationTitle($concepto) {
        return "Pago Vencido - " . $concepto;
    }
    
    /**
     * Construir mensaje de notificación
     * 
     * @param array $payment_data Datos del pago
     * @return string Mensaje formateado
     */
    private function buildNotificationMessage($payment_data) {
        $mensaje = "Estimado residente, le recordamos que tiene un pago pendiente:\n\n";
        $mensaje .= "Concepto: " . $payment_data['concepto'] . "\n";
        $mensaje .= "Monto: $" . number_format($payment_data['monto'], 2) . "\n";
        $mensaje .= "Mes: " . $payment_data['mes_pago'] . "\n";
        $mensaje .= "Fecha de vencimiento: " . $payment_data['fecha_pago'] . "\n\n";
        $mensaje .= "Por favor, regularice su situación a la brevedad.";
        
        return $mensaje;
    }
    
    /**
     * Send payment email notification
     * 
     * @param array $payment_data Payment data
     * @param array $resident_data Resident data
     * @param string $type Email type (overdue, confirmation, reminder)
     * @return void
     */
    private function sendPaymentEmail($payment_data, $resident_data, $type) {
        if (!$this->emailService->isEnabled()) {
            error_log("[NotificationService] Email service disabled, skipping email");
            return;
        }
        
        try {
            $recipient = $resident_data['email'];
            
            if (empty($recipient)) {
                error_log("[NotificationService] No email address for resident ID: " . $resident_data['id']);
                return;
            }
            
            // Prepare template variables
            $variables = [
                'resident_name' => $resident_data['nombre'],
                'apartment' => $resident_data['apartamento'],
                'tower' => $resident_data['torre'],
                'payment_concept' => $payment_data['concepto'],
                'payment_amount' => number_format($payment_data['monto'], 2),
                'payment_month' => $payment_data['mes_pago'],
                'due_date' => date('d/m/Y', strtotime($payment_data['fecha_pago'])),
                'reference' => $payment_data['referencia'] ?? 'N/A',
                'type' => $type
            ];
            
            // Load and render template
            $html_body = $this->emailService->loadTemplate('payment_notification', $variables);
            
            // Determine subject based on type
            $subjects = [
                'overdue' => 'Pago Vencido - ' . $payment_data['concepto'],
                'confirmation' => 'Confirmación de Pago - ' . $payment_data['concepto'],
                'reminder' => 'Recordatorio de Pago - ' . $payment_data['concepto']
            ];
            
            $subject = $subjects[$type] ?? 'Notificación de Pago';
            
            // Send email (non-blocking - errors are logged but don't stop execution)
            $result = $this->emailService->sendHtmlEmail($recipient, $subject, $html_body);
            
            if ($result['success']) {
                error_log("[NotificationService] Email sent to: $recipient");
            } else {
                error_log("[NotificationService] Email failed: " . $result['error']);
            }
            
        } catch (Exception $e) {
            error_log("[NotificationService] Email exception: " . $e->getMessage());
            // Don't throw - email failures should not break notification creation
        }
    }
}
?>
