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
        .status-new { background-color: #3498db; color: white; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .status-updated { background-color: #f39c12; color: white; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .status-resolved { background-color: #27ae60; color: white; padding: 15px; border-radius: 4px; margin: 15px 0; }
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
        <div class="header">
            <h1>CondoWeb</h1>
            <p>Sistema de Gestión de Condominio</p>
        </div>
        <div class="content">
            <?php if ($type === 'created'): ?>
                <div class="status-new">
                    <h2 style="margin: 0 0 10px 0;">📝 Nueva Incidencia Registrada</h2>
                    <p style="margin: 0;">Su incidencia ha sido registrada exitosamente.</p>
                </div>
            <?php elseif ($type === 'status_changed'): ?>
                <div class="status-updated">
                    <h2 style="margin: 0 0 10px 0;">🔄 Actualización de Incidencia</h2>
                    <p style="margin: 0;">El estado de su incidencia ha cambiado.</p>
                </div>
            <?php elseif ($type === 'resolved'): ?>
                <div class="status-resolved">
                    <h2 style="margin: 0 0 10px 0;">✓ Incidencia Resuelta</h2>
                    <p style="margin: 0;">Su incidencia ha sido resuelta.</p>
                </div>
            <?php elseif ($type === 'assigned'): ?>
                <div class="status-updated">
                    <h2 style="margin: 0 0 10px 0;">👤 Incidencia Asignada</h2>
                    <p style="margin: 0;">Su incidencia ha sido asignada a un administrador.</p>
                </div>
            <?php endif; ?>
            
            <p>Estimado/a <strong><?php echo htmlspecialchars($resident_name); ?></strong>,</p>
            
            <?php if ($type === 'created'): ?>
                <p>Le confirmamos que su incidencia ha sido registrada en nuestro sistema. Le notificaremos sobre cualquier actualización.</p>
            <?php elseif ($type === 'status_changed'): ?>
                <p>El estado de su incidencia ha sido actualizado. A continuación encontrará los detalles.</p>
            <?php elseif ($type === 'resolved'): ?>
                <p>Nos complace informarle que su incidencia ha sido resuelta. Gracias por su paciencia.</p>
            <?php elseif ($type === 'assigned'): ?>
                <p>Su incidencia ha sido asignada a un administrador para su atención. Le mantendremos informado del progreso.</p>
            <?php endif; ?>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #2c3e50;">Detalles de la Incidencia</h3>
                <div class="info-row">
                    <span class="label">Apartamento:</span>
                    <span class="value"><?php echo htmlspecialchars($apartment); ?> - Torre <?php echo htmlspecialchars($tower); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Título:</span>
                    <span class="value"><?php echo htmlspecialchars($incident_title); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Descripción:</span>
                    <span class="value"><?php echo htmlspecialchars($incident_description); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Categoría:</span>
                    <span class="value"><?php echo htmlspecialchars($incident_category); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Prioridad:</span>
                    <span class="value"><?php echo htmlspecialchars($incident_priority); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Estado:</span>
                    <span class="value"><?php echo htmlspecialchars($incident_status); ?></span>
                </div>
                <?php if (isset($admin_notes) && !empty($admin_notes)): ?>
                <div class="info-row">
                    <span class="label">Notas del Administrador:</span>
                    <span class="value"><?php echo htmlspecialchars($admin_notes); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($incident_url)): ?>
            <p style="text-align: center;">
                <a href="<?php echo htmlspecialchars($incident_url); ?>" class="button">Ver Detalles de la Incidencia</a>
            </p>
            <?php endif; ?>
            
            <p>Si tiene alguna pregunta o necesita asistencia adicional, por favor contacte a la administración.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> CondoWeb. Todos los derechos reservados.</p>
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
