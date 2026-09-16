<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background-color: #2c3e50; color: #ffffff; padding: 30px 20px; text-align: center; }
        .header.admin { background-color: #2980b9; }
        .header.success { background-color: #27ae60; }
        .header.danger { background-color: #c0392b; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 20px; }
        .info-box { background-color: #ecf0f1; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #2c3e50; }
        .value { color: #555; }
        .button { display: inline-block; padding: 12px 24px; background-color: #3498db; color: #ffffff; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        .footer { background-color: #ecf0f1; padding: 20px; text-align: center; font-size: 12px; color: #7f8c8d; }
        @media only screen and (max-width: 600px) {
            .container { margin: 0; border-radius: 0; }
            .content { padding: 20px 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header <?php echo isset($recipient_role) && $recipient_role === 'admin' ? 'admin' : (isset($status) && $status === 'rechazada' ? 'danger' : (isset($status) && $status === 'confirmada' ? 'success' : '')); ?>">
            <h1>CondoWeb</h1>
            <p>Sistema de Gestión de Condominio</p>
        </div>
        <div class="content">
            <h2><?php echo htmlspecialchars($title); ?></h2>
            <p><?php echo htmlspecialchars($message); ?></p>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #2c3e50;">Detalles del Pago</h3>
                <?php if (isset($resident_name)): ?>
                <div class="info-row">
                    <span class="label">Residente:</span>
                    <span class="value"><?php echo htmlspecialchars($resident_name); ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($apartment)): ?>
                <div class="info-row">
                    <span class="label">Apartamento:</span>
                    <span class="value"><?php echo htmlspecialchars($apartment); ?> - Torre <?php echo htmlspecialchars(isset($tower) ? $tower : ''); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="label">Concepto:</span>
                    <span class="value"><?php echo htmlspecialchars($payment_concept); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Mes de Pago:</span>
                    <span class="value"><?php echo htmlspecialchars($payment_month); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Monto:</span>
                    <span class="value"><?php echo $payment_amount; ?></span>
                </div>
                <?php if (isset($declared_amount)): ?>
                <div class="info-row">
                    <span class="label">Monto Declarado:</span>
                    <span class="value"><?php echo $declared_amount; ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($exchange_rate)): ?>
                <div class="info-row">
                    <span class="label">Tasa Bs/USD:</span>
                    <span class="value"><?php echo htmlspecialchars($exchange_rate); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #2c3e50;">Detalles de la Operación</h3>
                <div class="info-row">
                    <span class="label">Método:</span>
                    <span class="value"><?php echo htmlspecialchars(isset($method) ? $method : ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Banco Origen:</span>
                    <span class="value"><?php echo htmlspecialchars(isset($origin_bank) ? $origin_bank : ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Referencia:</span>
                    <span class="value"><?php echo htmlspecialchars(isset($reference) ? $reference : ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Fecha de Operación:</span>
                    <span class="value"><?php echo htmlspecialchars(isset($operation_date) ? $operation_date : ''); ?></span>
                </div>
                <?php if (isset($admin_note)): ?>
                <div class="info-row">
                    <span class="label">Nota de la Administración:</span>
                    <span class="value"><?php echo htmlspecialchars($admin_note); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if (isset($action_url)): ?>
            <p style="text-align: center;">
                <a href="<?php echo htmlspecialchars($action_url); ?>" class="button">Ver Detalles</a>
            </p>
            <?php endif; ?>
            <?php if (isset($status) && $status === 'rechazada'): ?>
            <p>Si el pago fue rechazado, por favor verifique los datos de la operación e intente nuevamente, o contacte a la administración.</p>
            <?php endif; ?>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> CondoWeb. Todos los derechos reservados.</p>
            <p>Este es un correo automático del sistema, por favor no responder.</p>
        </div>
    </div>
</body>
</html>