<?php
/**
 * Template de Email: Notificación de Mora Aplicada
 * 
 * Variables disponibles:
 * - $resident_name: Nombre del residente
 * - $payment_concept: Concepto del pago
 * - $payment_amount: Monto original del pago
 * - $late_fee_amount: Monto de mora aplicado
 * - $total_amount: Monto total (original + mora)
 * - $due_date: Fecha de vencimiento
 * - $days_overdue: Días de atraso
 * - $rule_name: Nombre de la regla aplicada
 */

// Cargar template base
$base_path = __DIR__ . '/base.php';
ob_start();
?>

<tr>
    <td style="padding: 20px; background-color: #ffffff;">
        <h2 style="color: #d32f2f; margin-top: 0;">
            <span style="font-size: 24px;">⚠️</span> Recargo por Mora Aplicado
        </h2>
        
        <p style="font-size: 16px; color: #333;">Estimado/a <strong><?php echo htmlspecialchars($resident_name); ?></strong>,</p>
        
        <p style="font-size: 14px; color: #555; line-height: 1.6;">
            Le informamos que se ha aplicado un recargo por mora a su pago debido a que ha excedido la fecha de vencimiento establecida.
        </p>
        
        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #856404;">
                <strong>⚠️ Importante:</strong> Este recargo se ha calculado automáticamente según las políticas de mora del condominio.
            </p>
        </div>
        
        <h3 style="color: #495057; margin-top: 30px; margin-bottom: 15px;">Detalles del Pago</h3>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
            <tr style="background-color: #f8f9fa;">
                <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold;">Concepto:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($payment_concept); ?></td>
            </tr>
            <tr>
                <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold;">Monto Original:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;">$<?php echo number_format($payment_amount, 2); ?></td>
            </tr>
            <tr style="background-color: #ffebee;">
                <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; color: #d32f2f;">Recargo por Mora:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6; color: #d32f2f; font-weight: bold;">+$<?php echo number_format($late_fee_amount, 2); ?></td>
            </tr>
            <tr style="background-color: #e8f5e9;">
                <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; font-size: 16px;">Monto Total a Pagar:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold; font-size: 16px; color: #2e7d32;">$<?php echo number_format($total_amount, 2); ?></td>
            </tr>
            <tr>
                <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold;">Fecha de Vencimiento:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;"><?php echo date('d/m/Y', strtotime($due_date)); ?></td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold;">Días de Atraso:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6; color: #d32f2f;"><?php echo $days_overdue; ?> días</td>
            </tr>
            <?php if (isset($rule_name) && $rule_name): ?>
            <tr>
                <td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold;">Regla Aplicada:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($rule_name); ?></td>
            </tr>
            <?php endif; ?>
        </table>
        
        <div style="background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #1976d2;">¿Por qué se aplicó este recargo?</h4>
            <p style="margin: 0; color: #0d47a1; line-height: 1.6;">
                El recargo por mora se aplica automáticamente cuando un pago excede su fecha de vencimiento. 
                Este sistema ayuda a mantener la puntualidad en los pagos y asegurar el correcto funcionamiento del condominio.
            </p>
        </div>
        
        <div style="background-color: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #c62828;">Acción Requerida</h4>
            <p style="margin: 0; color: #b71c1c; line-height: 1.6;">
                <strong>Le solicitamos regularizar su situación a la brevedad posible.</strong> 
                Si el pago continúa atrasado, podrían aplicarse recargos adicionales según las políticas del condominio.
            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?php echo APP_URL ?? ''; ?>/payments" 
               style="display: inline-block; padding: 12px 30px; background-color: #2196f3; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Ver Mis Pagos
            </a>
        </div>
        
        <hr style="border: none; border-top: 1px solid #dee2e6; margin: 30px 0;">
        
        <h4 style="color: #495057;">¿Necesita Ayuda?</h4>
        <p style="font-size: 14px; color: #555; line-height: 1.6;">
            Si tiene alguna consulta sobre este recargo o necesita información adicional, 
            por favor contacte a la administración del condominio.
        </p>
        
        <p style="font-size: 14px; color: #555; line-height: 1.6;">
            <strong>Formas de contacto:</strong><br>
            📧 Email: administracion@condominio.com<br>
            📞 Teléfono: (555) 123-4567<br>
            🏢 Horario de atención: Lunes a Viernes, 9:00 AM - 6:00 PM
        </p>
        
        <p style="font-size: 14px; color: #555; margin-top: 30px;">
            Atentamente,<br>
            <strong>Administración del Condominio</strong>
        </p>
        
        <div style="background-color: #f8f9fa; padding: 15px; margin-top: 30px; border-radius: 5px;">
            <p style="font-size: 12px; color: #6c757d; margin: 0; text-align: center;">
                Este es un mensaje automático generado por el sistema de gestión del condominio. 
                Por favor, no responda directamente a este correo.
            </p>
        </div>
    </td>
</tr>

<?php
$content = ob_get_clean();
include $base_path;
?>
