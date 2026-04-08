# Sistema de Notificaciones de Pagos Vencidos

## Descripción

El módulo de notificaciones de pagos vencidos detecta automáticamente pagos atrasados y genera notificaciones persistentes en la base de datos para informar a los residentes sobre sus obligaciones pendientes.

## Características

- **Detección Automática**: Identifica pagos con estado "pendiente" o "atrasado" cuya fecha de pago ha vencido
- **Notificaciones Persistentes**: Crea registros en la base de datos que los residentes pueden consultar
- **Prevención de Duplicados**: Evita generar múltiples notificaciones para el mismo pago no leído
- **Actualización de Estados**: Cambia automáticamente el estado de pagos pendientes a atrasados
- **Logging Completo**: Registra cada ejecución con estadísticas detalladas

## Arquitectura

El módulo está compuesto por:

- **Notification Model** (`app/models/Notification.php`): Gestiona operaciones CRUD de notificaciones
- **NotificationService** (`app/services/NotificationService.php`): Lógica de negocio para detección y generación
- **NotificationController** (`app/controllers/NotificationController.php`): Interfaces de usuario
- **Script de Cron** (`check_overdue_payments.php`): Ejecutable para detección programada

## Configuración del Cron Job

### Requisitos Previos

- PHP 7.4 o superior instalado en el servidor
- Acceso a la configuración de cron jobs (crontab)
- Permisos de ejecución en el script

### Paso 1: Verificar la Ruta del Script

El script ejecutable se encuentra en la raíz del proyecto:

```
/ruta/completa/al/proyecto/check_overdue_payments.php
```

Reemplace `/ruta/completa/al/proyecto/` con la ruta absoluta donde está instalado el sistema.

### Paso 2: Dar Permisos de Ejecución

```bash
chmod +x /ruta/completa/al/proyecto/check_overdue_payments.php
```

### Paso 3: Configurar el Cron Job

Abra el editor de crontab:

```bash
crontab -e
```

Agregue una de las siguientes líneas según la frecuencia deseada:

#### Ejecución Diaria a las 8:00 AM (Recomendado)

```cron
0 8 * * * /usr/bin/php /ruta/completa/al/proyecto/check_overdue_payments.php >> /ruta/completa/al/proyecto/logs/cron_notifications.log 2>&1
```

#### Ejecución Diaria a las 6:00 AM

```cron
0 6 * * * /usr/bin/php /ruta/completa/al/proyecto/check_overdue_payments.php >> /ruta/completa/al/proyecto/logs/cron_notifications.log 2>&1
```

#### Ejecución Cada 12 Horas (8:00 AM y 8:00 PM)

```cron
0 8,20 * * * /usr/bin/php /ruta/completa/al/proyecto/check_overdue_payments.php >> /ruta/completa/al/proyecto/logs/cron_notifications.log 2>&1
```

#### Ejecución Cada Hora (Para Testing)

```cron
0 * * * * /usr/bin/php /ruta/completa/al/proyecto/check_overdue_payments.php >> /ruta/completa/al/proyecto/logs/cron_notifications.log 2>&1
```

### Paso 4: Verificar la Ruta de PHP

Si no está seguro de la ruta de PHP en su servidor, ejecute:

```bash
which php
```

Esto mostrará la ruta completa (por ejemplo: `/usr/bin/php` o `/usr/local/bin/php`). Use esta ruta en el cron job.

### Paso 5: Crear el Archivo de Log

```bash
touch /ruta/completa/al/proyecto/logs/cron_notifications.log
chmod 666 /ruta/completa/al/proyecto/logs/cron_notifications.log
```

### Paso 6: Verificar la Configuración

Guarde el archivo crontab y verifique que se haya agregado correctamente:

```bash
crontab -l
```

## Ejecución Manual

Para probar el script manualmente antes de configurar el cron:

```bash
php /ruta/completa/al/proyecto/check_overdue_payments.php
```

El script mostrará el resultado en la consola y también lo registrará en los logs del sistema.

## Logs y Monitoreo

### Ubicación de Logs

Los logs se registran en dos lugares:

1. **Log del Sistema PHP**: Ubicación según configuración de `error_log` en `php.ini`
2. **Log del Cron**: `/ruta/completa/al/proyecto/logs/cron_notifications.log`

### Formato de Logs

```
[check_overdue_payments.php] ========================================
[check_overdue_payments.php] Inicio de ejecución: 2024-01-15 08:00:00
[check_overdue_payments.php] Conexión a base de datos establecida
[check_overdue_payments.php] Iniciando procesamiento de pagos vencidos...
[NotificationService] Inicio de detección de pagos vencidos
[NotificationService] Notificación creada para usuario ID: 5, Pago ID: 12
[NotificationService] Estado actualizado a 'atrasado' para pago ID: 12
[NotificationService] Procesados: 3 pagos, Notificaciones creadas: 2, Pagos actualizados: 1, Errores: 0
[check_overdue_payments.php] Procesamiento completado exitosamente
[check_overdue_payments.php] Pagos procesados: 3
[check_overdue_payments.php] Notificaciones creadas: 2
[check_overdue_payments.php] Pagos actualizados: 1
[check_overdue_payments.php] Fin de ejecución: 2024-01-15 08:00:15
[check_overdue_payments.php] ========================================
```

### Monitorear Logs en Tiempo Real

```bash
tail -f /ruta/completa/al/proyecto/logs/cron_notifications.log
```

### Verificar Últimas Ejecuciones

```bash
tail -n 50 /ruta/completa/al/proyecto/logs/cron_notifications.log
```

## Solución de Problemas

### El Cron No Se Ejecuta

1. **Verificar que el cron esté activo**:
   ```bash
   sudo service cron status
   ```

2. **Verificar permisos del script**:
   ```bash
   ls -la /ruta/completa/al/proyecto/check_overdue_payments.php
   ```

3. **Verificar logs del sistema cron**:
   ```bash
   grep CRON /var/log/syslog
   ```

### Error de Conexión a Base de Datos

1. Verificar configuración en `config/database.php`
2. Verificar que el usuario de cron tenga acceso a la base de datos
3. Revisar logs para mensajes de error específicos

### No Se Crean Notificaciones

1. **Verificar que existan pagos vencidos**:
   ```sql
   SELECT * FROM pagos 
   WHERE (estado = 'pendiente' OR estado = 'atrasado') 
   AND fecha_pago < CURDATE();
   ```

2. **Verificar que los residentes tengan usuario_id**:
   ```sql
   SELECT r.*, u.id as usuario_id 
   FROM residentes r 
   LEFT JOIN usuarios u ON r.usuario_id = u.id 
   WHERE u.id IS NULL;
   ```

3. **Revisar logs para mensajes de error**

### Notificaciones Duplicadas

El sistema previene automáticamente la creación de notificaciones duplicadas. Si un residente tiene una notificación no leída del mismo pago, no se creará una nueva hasta que la marque como leída.

## Integración con el Sistema Existente

### Modelo Payment

El sistema utiliza el modelo `Payment` existente sin modificaciones. Los métodos utilizados son:

- `getPendingPayments()`: Obtiene pagos pendientes o atrasados
- `readOne()`: Lee datos de un pago específico
- `update()`: Actualiza el estado del pago

### Modelo Resident

El sistema utiliza el modelo `Resident` existente para obtener información de residentes:

- `readOne()`: Obtiene datos del residente incluyendo usuario_id

### Tabla Notificaciones

El sistema utiliza la tabla `notificaciones` existente sin modificar su estructura:

```sql
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    leida BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

## Visualización de Notificaciones

### Para Residentes

Los residentes pueden ver sus notificaciones en:

- **Panel Principal**: Contador de notificaciones no leídas
- **Página de Notificaciones**: `/notifications` - Lista completa de notificaciones

### Para Administradores

Los administradores tienen acceso a:

- **Vista Global**: `/notifications/admin` - Todas las notificaciones del sistema
- **Filtros**: Por residente, tipo y estado de lectura
- **Estadísticas**: Total generadas, leídas, no leídas

## Formato de Notificaciones

Las notificaciones de pagos vencidos siguen este formato:

**Título**: `Pago Vencido - [Concepto del Pago]`

**Mensaje**:
```
Estimado residente, le recordamos que tiene un pago pendiente:

Concepto: Cuota de Mantenimiento
Monto: $1500.00
Mes: 2024-01
Fecha de vencimiento: 2024-01-10

Por favor, regularice su situación a la brevedad.
```

**Tipo**: `warning`

## Mantenimiento

### Limpieza de Notificaciones Antiguas

Para mantener la base de datos limpia, se recomienda eliminar notificaciones leídas antiguas periódicamente:

```sql
-- Eliminar notificaciones leídas de más de 6 meses
DELETE FROM notificaciones 
WHERE leida = TRUE 
AND created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

Puede agregar esto como un cron job mensual:

```cron
0 2 1 * * mysql -u usuario -p'contraseña' condominio_db -e "DELETE FROM notificaciones WHERE leida = TRUE AND created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);"
```

### Backup de Logs

Se recomienda rotar los logs periódicamente para evitar que crezcan demasiado:

```bash
# Agregar a logrotate
sudo nano /etc/logrotate.d/condominio

# Contenido:
/ruta/completa/al/proyecto/logs/cron_notifications.log {
    weekly
    rotate 4
    compress
    missingok
    notifempty
}
```

## Preguntas Frecuentes

### ¿Con qué frecuencia debe ejecutarse el cron?

Se recomienda ejecutarlo una vez al día, preferiblemente en la mañana (8:00 AM). Esto permite detectar pagos que vencieron el día anterior.

### ¿Qué pasa si el script falla?

El script está diseñado para manejar errores sin interrumpir el procesamiento. Si un pago falla, se registra el error y continúa con los siguientes. El código de salida indicará si hubo errores.

### ¿Se envían emails a los residentes?

En la versión actual, solo se crean notificaciones en la base de datos. El envío de emails está planificado para una fase futura.

### ¿Cómo se previenen notificaciones duplicadas?

El sistema verifica si existe una notificación no leída con el mismo título para el mismo usuario. Si existe, no crea una nueva. Cuando el residente marca la notificación como leída, el sistema puede generar una nueva en la siguiente ejecución si el pago sigue pendiente.

### ¿Puedo ejecutar el script manualmente?

Sí, puede ejecutar el script manualmente en cualquier momento:

```bash
php check_overdue_payments.php
```

Esto es útil para testing o para forzar una verificación inmediata.

## Soporte

Para reportar problemas o solicitar ayuda:

1. Revisar los logs del sistema
2. Verificar la configuración del cron
3. Ejecutar el script manualmente para ver errores
4. Contactar al equipo de desarrollo con los logs relevantes

---

**Última actualización**: 2024-01-15
**Versión del módulo**: 1.0.0
