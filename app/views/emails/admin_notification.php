<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background-color: #c0392b; color: #ffffff; padding: 30px 20px; text-align: center; }
        .header.normal { background-color: #2c3e50; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 20px; }
        .info-box { background-color: #ecf0f1; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #2c3e50; }
        .value { color: #555; }
        .urgent { background-color: #e74c3c; color: white; padding: 15px; border-radius: 4px; margin: 15px 0; font-weight: bold; }
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
        <div class="header <?php echo (isset($urgent) && $urgent) ? '' : 'normal'; ?>">
            <h1>CondoWeb - Administración</h1>
            <p>Notificación del Sistema</p>
        </div>
        <div class="content">
            <?php if (isset($urgent) && $urgent): ?>
                <div class="urgent">
                    ⚠️ ATENCIÓN URGENTE REQUERIDA
                </div>
            <?php endif; ?>
            
            <h2><?php echo htmlspecialchars($title); ?></h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #2c3e50;">Información del Residente</h3>
                <div class="info-row">
                    <span class="label">Nombre:</span>
                    <span class="value"><?php echo htmlspecialchars($resident_name); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Apartamento:</span>
                    <span class="value"><?php echo htmlspecialchars($apartment); ?> - Torre <?php echo htmlspecialchars($tower); ?></span>
                </div>
                <?php if (isset($resident_email)): ?>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($resident_email); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($details)): ?>
            <div class="info-box">
                <h3 style="margin-top: 0; color: #2c3e50;">Detalles</h3>
                <?php foreach ($details as $key => $value): ?>
                <div class="info-row">
                    <span class="label"><?php echo htmlspecialchars($key); ?>:</span>
                    <span class="value"><?php echo htmlspecialchars($value); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($action_url)): ?>
            <p style="text-align: center;">
                <a href="<?php echo htmlspecialchars($action_url); ?>" class="button">Gestionar Ahora</a>
            </p>
            <?php endif; ?>
            
            <p>Por favor, tome las acciones necesarias lo antes posible.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> CondoWeb. Todos los derechos reservados.</p>
            <p>Este es un correo automático del sistema de notificaciones.</p>
        </div>
    </div>
</body>
</html>
