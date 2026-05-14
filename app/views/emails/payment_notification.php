<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background-color: #2c3e50; color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 20px; }
        .info-box { background-color: #ecf0f1; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #2c3e50; }
        .value { color: #555; }
        .alert { background-color: #e74c3c; color: white; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .success { background-color: #27ae60; color: white; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .warning { background-color: #f39c12; color: white; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .footer { background-color: #ecf0f1; padding: 20px; text-align: center; font-size: 12px; color: #7f8c8d; }
        @media only screen and (max-width: 600px) {
            .container { margin: 0; border-radius: 0; }
            .content { padding: 20px 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CondoWeb</h1>
            <p>Sistema de Gestión de Condominio</p>
        </div>
        <div class="content">
            <?php if ($type === 'overdue'): ?>
                <div class="alert">
                    <h2 style="margin: 0 0 10px 0;">⚠️ Pago Vencido</h2>
                    <p style="margin: 0;">Tiene un pago pendiente que ha vencido.</p>
                </div>
            <?php elseif ($type === 'confirmation'): ?>
                <div class="success">
                    <h2 style="margin: 0 0 10px 0;">✓ Pago Confirmado</h2>
                    <p style="margin: 0;">Su pago ha sido registrado exitosamente.</p>
                </div>
            <?php elseif ($type === 'reminder'): ?>
                <div class="warning">
                    <h2 style="margin: 0 0 10px 0;">🔔 Recordatorio de Pago</h2>
                    <p style="margin: 0;">Su pago está próximo a vencer.</p>
                </div>
            <?php endif; ?>
            
            <p>Estimado/a <strong><?php echo htmlspecialchars($resident_name); ?></strong>,</p>
            
            <?php if ($type === 'overdue'): ?>
                <p>Le recordamos que tiene un pago pendiente que ha vencido. Por favor, regularice su situación a la brevedad.</p>
            <?php elseif ($type === 'confirmation'): ?>
                <p>Le confirmamos que hemos recibido su pago correctamente. Gracias por su puntualidad.</p>
            <?php elseif ($type === 'reminder'): ?>
                <p>Le recordamos que tiene un pago próximo a vencer. Por favor, realice el pago antes de la fecha de vencimiento.</p>
            <?php endif; ?>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #2c3e50;">Detalles del Pago</h3>
                <div class="info-row">
                    <span class="label">Apartamento:</span>
                    <span class="value"><?php echo htmlspecialchars($apartment); ?> - Torre <?php echo htmlspecialchars($tower); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Concepto:</span>
                    <span class="value"><?php echo htmlspecialchars($payment_concept); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Monto:</span>
                    <span class="value">$<?php echo htmlspecialchars($payment_amount); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Mes de Pago:</span>
                    <span class="value"><?php echo htmlspecialchars($payment_month); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Fecha de Vencimiento:</span>
                    <span class="value"><?php echo htmlspecialchars($due_date); ?></span>
                </div>
                <?php if (isset($reference) && $reference !== 'N/A'): ?>
                <div class="info-row">
                    <span class="label">Referencia:</span>
                    <span class="value"><?php echo htmlspecialchars($reference); ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($payment_method)): ?>
                <div class="info-row">
                    <span class="label">Método de Pago:</span>
                    <span class="value"><?php echo htmlspecialchars($payment_method); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <p>Si tiene alguna pregunta o necesita asistencia, por favor contacte a la administración.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> CondoWeb. Todos los derechos reservados.</p>
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
