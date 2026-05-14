# Guía de Configuración de Email - CondoWeb

Esta guía proporciona instrucciones detalladas para configurar el sistema de notificaciones por correo electrónico en CondoWeb.

## Tabla de Contenidos

1. [Requisitos Previos](#requisitos-previos)
2. [Configuración con Gmail](#configuración-con-gmail)
3. [Configuración con SendGrid](#configuración-con-sendgrid)
4. [Configuración con Otros Proveedores SMTP](#configuración-con-otros-proveedores-smtp)
5. [Modo de Prueba](#modo-de-prueba)
6. [Seguridad y Mejores Prácticas](#seguridad-y-mejores-prácticas)
7. [Solución de Problemas](#solución-de-problemas)

## Requisitos Previos

- PHP 7.4 o superior con extensión OpenSSL habilitada
- Composer instalado
- Acceso a un servidor SMTP o cuenta de SendGrid
- Archivo `.env` configurado (copiar de `.env.example`)

## Configuración con Gmail

Gmail es una opción popular para desarrollo y pequeñas implementaciones.

### Paso 1: Habilitar Autenticación de Dos Factores

1. Ve a tu cuenta de Google: https://myaccount.google.com
2. Navega a "Seguridad"
3. Habilita "Verificación en dos pasos"

### Paso 2: Generar Contraseña de Aplicación

1. Ve a https://myaccount.google.com/apppasswords
2. Selecciona "Correo" como aplicación
3. Selecciona "Otro" como dispositivo y escribe "CondoWeb"
4. Copia la contraseña generada (16 caracteres)

### Paso 3: Configurar .env

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=xxxx-xxxx-xxxx-xxxx
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME=CondoWeb
MAIL_TEST_MODE=false
```

### Limitaciones de Gmail

- Límite de 500 emails por día
- Límite de 100 destinatarios por email
- No recomendado para producción con alto volumen

## Configuración con SendGrid

SendGrid es ideal para producción y alto volumen de emails.

### Paso 1: Crear Cuenta en SendGrid

1. Regístrate en https://sendgrid.com
2. Verifica tu cuenta de email
3. Completa el proceso de onboarding

### Paso 2: Generar API Key

1. Ve a Settings > API Keys
2. Clic en "Create API Key"
3. Nombre: "CondoWeb Production"
4. Permisos: "Full Access" o "Mail Send"
5. Copia la API Key (solo se muestra una vez)

### Paso 3: Verificar Dominio (Recomendado)

1. Ve a Settings > Sender Authentication
2. Clic en "Verify a Single Sender" o "Authenticate Your Domain"
3. Sigue las instrucciones para agregar registros DNS
4. Espera la verificación (puede tomar hasta 48 horas)

### Paso 4: Configurar .env

```env
MAIL_DRIVER=sendgrid
SENDGRID_API_KEY=SG.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME=CondoWeb
MAIL_TEST_MODE=false
```

### Ventajas de SendGrid

- Hasta 100 emails gratis por día
- Excelente entregabilidad
- Estadísticas detalladas
- Manejo de rebotes y cancelaciones de suscripción

## Configuración con Otros Proveedores SMTP

### Outlook/Office 365

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@outlook.com
MAIL_PASSWORD=tu-contraseña
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME=CondoWeb
```

### Mailtrap (Solo para Desarrollo)

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu-username-mailtrap
MAIL_PASSWORD=tu-password-mailtrap
MAIL_FROM_ADDRESS=dev@condoweb.local
MAIL_FROM_NAME=CondoWeb Dev
```

### SMTP Personalizado

```env
MAIL_DRIVER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu-contraseña-smtp
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME=CondoWeb
```

## Modo de Prueba

El modo de prueba permite probar el sistema sin enviar emails reales.

### Activar Modo de Prueba

```env
MAIL_TEST_MODE=true
```

### Comportamiento en Modo de Prueba

- Los emails NO se envían realmente
- El contenido se guarda en `logs/emails/email_YYYY-MM-DD.log`
- Útil para desarrollo y pruebas
- No requiere credenciales SMTP válidas

### Ejemplo de Log

```
[2024-01-15 10:30:45] TO: residente@example.com | SUBJECT: Pago Vencido - Cuota de Mantenimiento
--------------------------------------------------------------------------------
<!DOCTYPE html>
<html>
...contenido del email...
</html>
```

## Seguridad y Mejores Prácticas

### Protección del Archivo .env

```bash
# Establecer permisos restrictivos
chmod 600 .env

# Asegurarse de que .env está en .gitignore
echo ".env" >> .gitignore
```

### Rotación de Credenciales

- Cambia las contraseñas SMTP cada 90 días
- Regenera las API Keys de SendGrid periódicamente
- Usa diferentes credenciales para desarrollo y producción

### Configuración de DNS (Producción)

Para mejorar la entregabilidad, configura estos registros DNS:

**SPF Record:**
```
v=spf1 include:_spf.google.com ~all
```
(Para Gmail, ajusta según tu proveedor)

**DKIM Record:**
Sigue las instrucciones de tu proveedor de email

**DMARC Record:**
```
v=DMARC1; p=none; rua=mailto:dmarc@tudominio.com
```

### Separación de Ambientes

**Desarrollo:**
```env
MAIL_TEST_MODE=true
```

**Staging:**
```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
# ... credenciales de Mailtrap
```

**Producción:**
```env
MAIL_DRIVER=sendgrid
SENDGRID_API_KEY=tu-api-key-produccion
```

## Solución de Problemas

### Error: "SMTP connection failed"

**Causas posibles:**
- Credenciales incorrectas
- Puerto bloqueado por firewall
- Servidor SMTP no accesible

**Soluciones:**
1. Verifica las credenciales en `.env`
2. Prueba con telnet: `telnet smtp.gmail.com 587`
3. Verifica que el puerto 587 esté abierto
4. Revisa los logs: `tail -f logs/emails/email_*.log`

### Error: "Authentication failed"

**Para Gmail:**
- Asegúrate de usar una contraseña de aplicación, no tu contraseña normal
- Verifica que la autenticación de dos factores esté habilitada

**Para otros proveedores:**
- Verifica usuario y contraseña
- Algunos proveedores requieren habilitar "acceso de aplicaciones menos seguras"

### Emails van a la carpeta de Spam

**Soluciones:**
1. Configura registros SPF, DKIM y DMARC
2. Usa un dominio verificado como remitente
3. Evita palabras spam en el asunto
4. Considera usar SendGrid para mejor entregabilidad
5. Pide a los usuarios que agreguen tu email a contactos

### Error: "Email service is disabled"

**Causa:**
El sistema detectó configuración inválida y deshabilitó el servicio de email.

**Solución:**
1. Revisa los logs del sistema
2. Verifica que todas las variables requeridas estén en `.env`
3. Asegúrate de que el archivo `.env` sea legible por PHP

### Emails no se envían pero no hay errores

**Verifica:**
1. Consulta la tabla `email_logs`:
   ```sql
   SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 10;
   ```
2. Revisa el campo `error_message` para detalles
3. Verifica que `MAIL_TEST_MODE` no esté en `true`

### Rendimiento lento al enviar emails

**Optimizaciones:**
- El sistema ya incluye retry logic con exponential backoff
- Considera implementar una cola de emails para alto volumen
- Usa SendGrid en lugar de SMTP para mejor rendimiento
- Verifica la latencia de red al servidor SMTP

## Monitoreo y Mantenimiento

### Consultar Logs de Email

```sql
-- Emails enviados hoy
SELECT COUNT(*) as total, status 
FROM email_logs 
WHERE DATE(created_at) = CURDATE()
GROUP BY status;

-- Tasa de éxito últimos 7 días
SELECT 
    DATE(created_at) as fecha,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as exitosos,
    ROUND(SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as tasa_exito
FROM email_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at);

-- Errores más comunes
SELECT error_message, COUNT(*) as ocurrencias
FROM email_logs
WHERE status = 'failure' AND error_message IS NOT NULL
GROUP BY error_message
ORDER BY ocurrencias DESC
LIMIT 10;
```

### Limpieza de Logs Antiguos

```sql
-- Eliminar logs de más de 90 días
DELETE FROM email_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

## Soporte

Para problemas adicionales:
- Revisa los logs del sistema en `logs/`
- Consulta la documentación de tu proveedor SMTP
- Crea un issue en el repositorio del proyecto

---

**Última actualización:** Enero 2024
