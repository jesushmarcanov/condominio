# Cómo Verificar las Notificaciones en el Sistema

## Resumen

El sistema de notificaciones está funcionando correctamente. Se han creado notificaciones de prueba y el sistema detectó pagos vencidos automáticamente.

## Estado Actual

✅ **Sistema funcionando correctamente**
- Tabla `notificaciones` existe en la base de datos
- Se crearon 3 notificaciones de prueba
- El script de detección automática funciona correctamente
- Se detectaron 3 pagos vencidos
- Se generaron 2 notificaciones automáticas para pagos vencidos

## Notificaciones Creadas

### Para el Usuario: Jesús Hilario Marcano Velásquez (jhmarcano@gmail.com)
1. **Pago Vencido - Fondo de Reserva**
   - Monto: $500.00
   - Estado: No leída
   
2. **Pago Vencido - Cuota de Mantenimiento**
   - Monto: $1500.00
   - Estado: No leída

### Para el Administrador (admin@condominio.com)
1. **Notificación de Prueba** (creada por el script de test)

## Cómo Ver las Notificaciones

### Opción 1: Como Residente

1. **Iniciar sesión en el sistema**
   - URL: http://localhost/condominio/login
   - Email: jhmarcano@gmail.com
   - Contraseña: (la contraseña que configuraste para este usuario)

2. **Ver el contador de notificaciones**
   - En la barra de navegación superior, verás un ícono de campana (🔔)
   - Debe mostrar un badge rojo con el número "2" (notificaciones no leídas)

3. **Ver las notificaciones**
   - Haz clic en el ícono de campana
   - Te llevará a `/notifications`
   - Verás la lista de tus notificaciones de pagos vencidos

4. **Dashboard del residente**
   - En el dashboard también verás las últimas 3 notificaciones
   - URL: http://localhost/condominio/dashboard

### Opción 2: Como Administrador

1. **Iniciar sesión como admin**
   - URL: http://localhost/condominio/login
   - Email: admin@condominio.com
   - Contraseña: password

2. **Ver todas las notificaciones del sistema**
   - Accede a: http://localhost/condominio/notifications/admin
   - Verás todas las notificaciones de todos los usuarios
   - Puedes filtrar por residente, tipo y estado de lectura

3. **Ver estadísticas**
   - En la vista de administración verás:
     - Total de notificaciones generadas
     - Notificaciones leídas
     - Notificaciones no leídas

## Problemas Comunes y Soluciones

### No veo el contador de notificaciones

**Causa**: El JavaScript no se está ejecutando o hay un error en la consola del navegador.

**Solución**:
1. Abre las herramientas de desarrollo del navegador (F12)
2. Ve a la pestaña "Console"
3. Busca errores de JavaScript
4. Verifica que la URL de la aplicación en `config/config.php` sea correcta:
   ```php
   define('APP_URL', 'http://localhost/condominio');
   ```

### El contador muestra "0" pero hay notificaciones

**Causa**: El endpoint AJAX no está funcionando correctamente.

**Solución**:
1. Verifica que estés autenticado (iniciado sesión)
2. Abre las herramientas de desarrollo (F12)
3. Ve a la pestaña "Network"
4. Busca la petición a `/notifications/getUnreadCount`
5. Verifica que retorne un JSON con `{"success":true,"count":2}`

### No puedo acceder a /notifications

**Causa**: Las rutas no están configuradas correctamente.

**Solución**:
1. Verifica que el archivo `.htaccess` esté configurado correctamente
2. Verifica que `mod_rewrite` esté habilitado en Apache
3. Verifica que las rutas en `index.php` incluyan las rutas de notificaciones

### Las notificaciones no se muestran en el dashboard

**Causa**: El método `dashboard()` en `UserController.php` no está cargando las notificaciones.

**Solución**:
1. Verifica que el código en `UserController.php` incluya:
   ```php
   $notification = new Notification($this->db);
   $stats['mis_notificaciones'] = $notification->readByUser($current_user['id'])->fetchAll(PDO::FETCH_ASSOC);
   ```

## Verificación Manual en la Base de Datos

Si quieres verificar directamente en la base de datos:

```sql
-- Ver todas las notificaciones
SELECT n.*, u.nombre, u.email 
FROM notificaciones n
LEFT JOIN usuarios u ON n.usuario_id = u.id
ORDER BY n.created_at DESC;

-- Contar notificaciones no leídas por usuario
SELECT u.nombre, u.email, COUNT(*) as no_leidas
FROM notificaciones n
LEFT JOIN usuarios u ON n.usuario_id = u.id
WHERE n.leida = FALSE
GROUP BY u.id;

-- Ver pagos vencidos
SELECT p.*, r.apartamento, u.nombre, u.email 
FROM pagos p
LEFT JOIN residentes r ON p.residente_id = r.id
LEFT JOIN usuarios u ON r.usuario_id = u.id
WHERE (p.estado = 'pendiente' OR p.estado = 'atrasado')
AND p.fecha_pago < CURDATE();
```

## Generar Más Notificaciones

Si quieres generar más notificaciones de prueba:

1. **Ejecutar el script de datos de prueba**:
   ```bash
   php create_test_data.php
   ```

2. **Ejecutar el detector de pagos vencidos**:
   ```bash
   php check_overdue_payments.php
   ```

3. **Verificar las notificaciones creadas**:
   ```bash
   php test_notifications.php
   ```

## Configurar Ejecución Automática (Cron)

Para que el sistema detecte pagos vencidos automáticamente cada día:

1. Abre el crontab:
   ```bash
   crontab -e
   ```

2. Agrega esta línea (ejecutar diariamente a las 8:00 AM):
   ```cron
   0 8 * * * php /ruta/completa/al/proyecto/check_overdue_payments.php >> /ruta/completa/al/proyecto/logs/cron_notifications.log 2>&1
   ```

3. Guarda y cierra el editor.

## Archivos de Prueba Creados

Se crearon los siguientes archivos de prueba que puedes eliminar después:

- `test_notifications.php` - Script para verificar el sistema
- `create_test_data.php` - Script para crear datos de prueba
- `VERIFICAR_NOTIFICACIONES.md` - Este archivo

## Próximos Pasos

1. ✅ Iniciar sesión en el sistema
2. ✅ Verificar que ves las notificaciones
3. ✅ Probar marcar notificaciones como leídas
4. ✅ Verificar el contador en tiempo real
5. ⏳ Configurar el cron job para ejecución automática
6. ⏳ Eliminar los archivos de prueba cuando ya no los necesites

## Soporte

Si sigues teniendo problemas:

1. Revisa los logs de PHP en tu servidor
2. Revisa los logs de Apache/Nginx
3. Verifica la consola del navegador (F12 → Console)
4. Ejecuta los scripts de prueba para diagnosticar el problema

---

**Última actualización**: 2026-04-07
**Estado**: Sistema funcionando correctamente ✅
