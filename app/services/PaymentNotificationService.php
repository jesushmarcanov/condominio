<?php
/**
 * Servicio de Notificaciones de Pagos en Línea
 * 
 * Encapsula la lógica de envío de notificaciones (email + in-app) relacionadas
 * con las declaraciones de pago en línea (Pago Móvil BCV y Transferencia).
 * 
 * Funcionalidades:
 * - Notificar a los administradores cuando hay una nueva declaración
 * - Notificar al residente cuando su declaración es confirmada o rechazada
 * - Crear notificaciones persistentes en la base de datos
 * 
 * @package App\Services
 * @author Jesús H. Marcano V.
 * @version 1.0.0
 */

class PaymentNotificationService {
    private $db;
    private $notification;
    private $emailService;

    /**
     * Constructor
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->db = $db;
        $this->notification = new Notification($db);
        $this->emailService = new EmailService($db);
    }

    /**
     * Obtener todos los administradores del sistema
     * 
     * @return array Lista de administradores con id, nombre y email
     */
    private function getAdmins() {
        $query = "SELECT id, nombre, email FROM usuarios WHERE rol = 'admin' ORDER BY nombre ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Notificar a los administradores sobre una nueva declaración
     * 
     * @param array $declaration Datos de la declaración (con join a pago/residente)
     * @return void
     */
    public function notifyAdminsDeclaration($declaration) {
        $title = 'Nueva Declaración de Pago en Línea';
        $message = 'Un residente ha registrado una declaración de pago que requiere verificación.';

        $admins = $this->getAdmins();

        foreach ($admins as $admin) {
            // Notificación in-app
            $this->notification->usuario_id = $admin['id'];
            $this->notification->titulo = $title;
            $this->notification->mensaje = 'Pago #' . $declaration['pago_id'] . ' - ' . $declaration['concepto'] . ' por ' . formatAmountIn($declaration['monto_declarado'] ?? 0, $declaration['moneda'] ?? 'USD') . '. Revisar en el panel de Declaraciones.';
            $this->notification->tipo = 'warning';
            $this->notification->leida = false;
            $this->notification->create();

            // Email
            $recipient = $admin['email'];
            if (empty($recipient) || !$this->emailService->isEnabled()) {
                continue;
            }

            try {
                $variables = [
                    'recipient_role' => 'admin',
                    'title' => $title,
                    'message' => $message,
                    'resident_name' => $declaration['nombre'] ?? '',
                    'apartment' => $declaration['apartamento'] ?? '',
                    'tower' => $declaration['torre'] ?? '',
                    'payment_concept' => $declaration['concepto'] ?? '',
                    'payment_month' => $declaration['mes_pago'] ?? '',
'payment_amount' => formatCurrencyDualText(convertCurrency(($declaration['monto_original'] ?? $declaration['monto'] ?? 0) + ($declaration['monto_mora'] ?? 0), $declaration['moneda'] ?? baseCurrency(), baseCurrency())),
                    'declared_amount' => formatAmountIn($declaration['monto_declarado'] ?? 0, $declaration['moneda'] ?? 'USD'),
                    'exchange_rate' => bcvRate() ? '1 USD = Bs ' . number_format(bcvRate(), 2) . ' (vigente ' . date('d/m/Y', strtotime(bcvRateDate())) . ')' : '',
                    'method' => getCatalogLabel(ONLINE_PAYMENT_METHODS, $declaration['metodo'] ?? ''),
                    'origin_bank' => $declaration['banco_origen'] ?? '',
                    'reference' => $declaration['referencia_bancaria'] ?? '',
                    'operation_date' => date('d/m/Y', strtotime($declaration['fecha_operacion'])),
                    'action_url' => APP_URL . '/payments/declarations'
                ];

                $html_body = $this->emailService->loadTemplate('payment_declaration', $variables);
                $this->emailService->sendHtmlEmail($recipient, $title, $html_body);
            } catch (Exception $e) {
                error_log("[PaymentNotificationService] Error enviando email a admin: " . $e->getMessage());
            }
        }
    }

    /**
     * Notificar al residente sobre el resultado de su declaración
     * 
     * @param array $resident_data Datos del residente/usuario
     * @param array $declaration Datos de la declaración
     * @param string $status Estado final ('confirmada' o 'rechazada')
     * @param string|null $notas Notas del administrador
     * @return void
     */
    public function notifyResidentStatusChange($resident_data, $declaration, $status, $notas = null) {
        $confirmed = $status === 'confirmada';
        $title = $confirmed ? 'Pago Confirmado' : 'Declaración de Pago Rechazada';
        $tipo = $confirmed ? 'success' : 'error';

        // Notificación in-app
        if (!empty($resident_data['usuario_id'])) {
            $this->notification->usuario_id = $resident_data['usuario_id'];
            $this->notification->titulo = $title;
            $this->notification->mensaje = $confirmed
                ? 'Su pago ' . ($declaration['concepto'] ?? '') . ' por ' . formatAmountIn($declaration['monto_declarado'] ?? 0, $declaration['moneda'] ?? 'USD') . ' ha sido confirmado.'
                : 'Su declaración de pago fue rechazada. ' . (!empty($notas) ? 'Motivo: ' . $notas : 'Verifique los datos e intente nuevamente.');
            $this->notification->tipo = $tipo;
            $this->notification->leida = false;
            $this->notification->create();
        }

        // Email
        $recipient = $resident_data['email'] ?? '';
        if (empty($recipient) || !$this->emailService->isEnabled()) {
            return;
        }

        $message = $confirmed
            ? 'Le confirmamos que su declaración de pago ha sido verificada y confirmada por la administración. Gracias por su puntualidad.'
            : 'Lamentablemente, su declaración de pago ha sido rechazada por la administración. Revise la nota y realice una nueva declaración si corresponde.';

        try {
            $variables = [
                'recipient_role' => 'resident',
                'status' => $status,
                'title' => $title,
                'message' => $message,
                'resident_name' => $resident_data['nombre'] ?? '',
                'apartment' => $resident_data['apartamento'] ?? '',
                'tower' => $resident_data['torre'] ?? '',
                'payment_concept' => $declaration['concepto'] ?? '',
                'payment_month' => $declaration['mes_pago'] ?? '',
                'payment_amount' => formatCurrencyDualText(convertCurrency(($declaration['monto_original'] ?? $declaration['monto'] ?? 0) + ($declaration['monto_mora'] ?? 0), $declaration['moneda'] ?? baseCurrency(), baseCurrency())),
                'declared_amount' => formatAmountIn($declaration['monto_declarado'] ?? 0, $declaration['moneda'] ?? 'USD'),
                'exchange_rate' => bcvRate() ? '1 USD = Bs ' . number_format(bcvRate(), 2) . ' (vigente ' . date('d/m/Y', strtotime(bcvRateDate())) . ')' : '',
                'method' => getCatalogLabel(ONLINE_PAYMENT_METHODS, $declaration['metodo'] ?? ''),
                'origin_bank' => $declaration['banco_origen'] ?? '',
                'reference' => $declaration['referencia_bancaria'] ?? '',
                'operation_date' => date('d/m/Y', strtotime($declaration['fecha_operacion'])),
                'admin_note' => $notas,
                'action_url' => APP_URL . '/payments/show/' . ($declaration['pago_id'] ?? '')
            ];

            $subject = $title . ' - ' . ($declaration['concepto'] ?? '');
            $html_body = $this->emailService->loadTemplate('payment_declaration', $variables);
            $this->emailService->sendHtmlEmail($recipient, $subject, $html_body);
        } catch (Exception $e) {
            error_log("[PaymentNotificationService] Error enviando email a residente: " . $e->getMessage());
        }
    }
}
?>