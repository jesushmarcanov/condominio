# Guía de Configuración del Cron Job (Programador de Tareas de Windows)

Esta guía te ayudará a configurar la ejecución automática del script de detección de pagos vencidos en Windows.

## Opción 1: Programador de Tareas de Windows (Recomendado)

### Paso 1: Abrir el Programador de Tareas

1. Presiona `Windows + R`
2. Escribe `taskschd.msc` y presiona Enter
3. Se abrirá el "Programador de tareas"

### Paso 2: Crear una Nueva Tarea

1. En el panel derecho, haz clic en **"Crear tarea básica..."**
2. Nombre: `CondoWeb - Detección de Pagos Vencidos`
3. Descripción: `Ejecuta diariamente el script de detección de pagos vencidos y envía notificaciones por email`
4. Haz clic en **"Siguiente"**

### Paso 3: Configurar el Desencadenador (Trigger)

1. Selecciona **"Diariamente"**
2. Haz clic en **"Siguiente"**
3. Configura:
   - **Fecha de inicio**: Hoy
   - **Hora**: `08:00:00` (8:00 AM)
   - **Repetir cada**: `1` días
4. Haz clic en **"Siguiente"**

### Paso 4: Configurar la Acción

1. Selecciona **"Iniciar un programa"**
2. Haz clic en **"Siguiente"**
3. Configura:
   - **Programa o script**: `C:\xampp\htdocs\condominio\run_overdue_payments.bat`
   - **Iniciar en (opcional)**: `C:\xampp\htdocs\condominio`
4. Haz clic en **"Siguiente"**

### Paso 5: Finalizar

1. Revisa el resumen
2. Marca la casilla **"Abrir el cuadro de diálogo Propiedades para esta tarea al hacer clic en Finalizar"**
3. Haz clic en **"Finalizar"**

### Paso 6: Configuración Avanzada (Opcional pero Recomendado)

En el cuadro de diálogo de propiedades que se abre:

#### Pestaña "General":
- ✅ Marca **"Ejecutar tanto si el usuario inició sesión como si no"**
- ✅ Marca **"Ejecutar con los privilegios más altos"**
- ✅ Configura para: **Windows 10**

#### Pestaña "Desencadenadores":
- Haz clic en **"Editar"**
- ✅ Marca **"Habilitado"**
- Puedes ajustar la hora si lo deseas

#### Pestaña "Acciones":
- Verifica que la ruta sea correcta

#### Pestaña "Condiciones":
- ❌ Desmarca **"Iniciar la tarea solo si el equipo está conectado a la corriente alterna"** (si es laptop)
- ✅ Marca **"Activar la tarea si se omitió una ejecución programada"**

#### Pestaña "Configuración":
- ✅ Marca **"Permitir que la tarea se ejecute a petición"**
- ✅ Marca **"Ejecutar la tarea lo antes posible después de perder una ejecución programada"**
- ✅ Marca **"Si la tarea no se ejecuta, reintentar cada**: `10 minutos`
- ✅ **Intentar hasta**: `3` veces

### Paso 7: Probar la Tarea

1. En el Programador de tareas, busca tu tarea en la lista
2. Haz clic derecho sobre ella
3. Selecciona **"Ejecutar"**
4. Verifica que se ejecute correctamente
5. Revisa el archivo `logs\cron_execution.log` para confirmar

---

## Opción 2: Usar el Programador de Tareas desde CMD (Avanzado)

Si prefieres usar la línea de comandos, puedes crear la tarea con este comando:

```cmd
schtasks /create /tn "CondoWeb-Pagos-Vencidos" /tr "C:\xampp\htdocs\condominio\run_overdue_payments.bat" /sc daily /st 08:00 /ru SYSTEM
```

Para eliminar la tarea:
```cmd
schtasks /delete /tn "CondoWeb-Pagos-Vencidos" /f
```

Para ver el estado de la tarea:
```cmd
schtasks /query /tn "CondoWeb-Pagos-Vencidos"
```

---

## Opción 3: Usar un Servicio de Cron Online (Alternativa)

Si tu servidor está en producción y tiene acceso a internet, puedes usar servicios como:

- **EasyCron**: https://www.easycron.com/
- **Cron-job.org**: https://cron-job.org/
- **SetCronJob**: https://www.setcronjob.com/

Estos servicios harán una petición HTTP a tu servidor en el horario configurado.

Para esto, necesitarías crear un endpoint web que ejecute el script:

```php
// crear archivo: public/cron/check_payments.php
<?php
// Verificar token de seguridad
$token = $_GET['token'] ?? '';
if ($token !== 'tu-token-secreto-aqui') {
    http_response_code(403);
    die('Acceso denegado');
}

// Ejecutar el script
require_once '../../check_overdue_payments.php';
?>
```

---

## Verificación y Monitoreo

### Verificar que el Cron Job está funcionando:

1. **Revisar logs de ejecución**:
   ```cmd
   type C:\xampp\htdocs\condominio\logs\cron_execution.log
   ```

2. **Revisar logs de email**:
   ```cmd
   type C:\xampp\htdocs\condominio\logs\emails\email_YYYY-MM-DD.log
   ```

3. **Consultar la base de datos**:
   ```sql
   SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 10;
   SELECT * FROM notificaciones ORDER BY fecha_creacion DESC LIMIT 10;
   ```

### Horarios Recomendados:

- **8:00 AM**: Buena hora para enviar recordatorios (la gente revisa su email en la mañana)
- **6:00 PM**: Alternativa si prefieres enviar en la tarde
- **Lunes a Viernes**: Puedes configurar para que solo se ejecute en días laborables

### Modificar el Horario:

1. Abre el Programador de tareas
2. Busca la tarea "CondoWeb - Detección de Pagos Vencidos"
3. Haz clic derecho → **"Propiedades"**
4. Ve a la pestaña **"Desencadenadores"**
5. Haz doble clic en el desencadenador
6. Modifica la hora
7. Haz clic en **"Aceptar"**

---

## Solución de Problemas

### La tarea no se ejecuta:

1. **Verifica que el servicio del Programador de tareas está activo**:
   - Abre `services.msc`
   - Busca "Programador de tareas"
   - Debe estar en estado "En ejecución"

2. **Verifica los permisos**:
   - La tarea debe ejecutarse con privilegios de administrador
   - El usuario debe tener permisos de escritura en la carpeta `logs/`

3. **Verifica las rutas**:
   - Asegúrate de que las rutas en `run_overdue_payments.bat` sean correctas
   - Verifica que PHP esté en `C:\xampp\php\php.exe`

4. **Revisa el historial de la tarea**:
   - En el Programador de tareas, selecciona tu tarea
   - Ve a la pestaña "Historial" en el panel inferior
   - Busca errores o advertencias

### El script se ejecuta pero no envía emails:

1. **Verifica el archivo .env**:
   - Asegúrate de que `MAIL_TEST_MODE=false`
   - Verifica las credenciales de Gmail

2. **Revisa los logs**:
   ```cmd
   type C:\xampp\htdocs\condominio\logs\cron_execution.log
   ```

3. **Ejecuta manualmente para ver errores**:
   ```cmd
   cd C:\xampp\htdocs\condominio
   php check_overdue_payments.php
   ```

---

## Desactivar Temporalmente

Si necesitas desactivar temporalmente el cron job:

1. Abre el Programador de tareas
2. Busca la tarea
3. Haz clic derecho → **"Deshabilitar"**

Para reactivarla:
- Haz clic derecho → **"Habilitar"**

---

## Notas Importantes

- ⚠️ El script solo enviará notificaciones para pagos que aún no tengan notificación
- ⚠️ Si el servidor está apagado a la hora programada, la tarea se ejecutará cuando se encienda (si configuraste "Ejecutar la tarea lo antes posible después de perder una ejecución programada")
- ⚠️ Los logs se acumulan con el tiempo, considera limpiarlos periódicamente
- ✅ El sistema tiene protección contra duplicados, no enviará el mismo email dos veces

---

## Contacto y Soporte

Si tienes problemas con la configuración, revisa:
- Los logs en `logs/cron_execution.log`
- Los logs de email en `logs/emails/`
- La tabla `email_logs` en la base de datos

Para más información, consulta `EMAIL_SETUP_GUIDE.md`
