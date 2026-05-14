# Sistema de Gestión de Condominio

Una aplicación web completa para la gestión integral de condominios, desarrollada con PHP siguiendo el patrón MVC (Modelo-Vista-Controlador).

## Características Principales

### 🏢 Gestión de Residentes
- Registro de nuevos residentes
- Edición y eliminación de registros
- Búsqueda por nombre o número de apartamento
- Información personal y de contacto

### 💳 Control de Pagos
- Registro de cuotas de mantenimiento
- Visualización de pagos por residente
- Reportes de pagos pendientes
- **Notificaciones automáticas de pagos vencidos**
- **Detección automática de pagos atrasados**
- **Sistema de alertas persistentes**

### 🚨 Gestión de Incidencias
- Registro de problemas (agua, electricidad, etc.)
- Seguimiento de estados (pendiente, en proceso, resuelta)
- Asignación a administradores
- Notificaciones automáticas

### 📊 Reportes y Estadísticas
- Reportes de ingresos mensuales
- Estadísticas de incidencias
- Dashboard con gráficos interactivos
- Exportación a CSV y PDF

### 👥 Roles de Usuario
- **Administrador**: Acceso completo a todas las funcionalidades
- **Residente**: Acceso limitado a su información personal

## Tecnologías Utilizadas

### Backend
- **PHP 7.4+** con patrón MVC
- **MySQL/MariaDB** para base de datos
- **PDO** para conexión segura a la base de datos

### Frontend
- **HTML5** y **CSS3**
- **Bootstrap 5** para diseño responsive
- **JavaScript** vanilla
- **Chart.js** para gráficos
- **Font Awesome** para iconos

### Seguridad
- Encriptación de contraseñas con `password_hash()`
- Prevención de XSS con `htmlspecialchars()`
- Validación de datos del lado del servidor
- Sesiones seguras

## Instalación

### Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o MariaDB 10.2+
- Servidor web (Apache o Nginx)
- Composer para gestión de dependencias

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <repository-url>
   cd condominio
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar la base de datos**
   ```sql
   CREATE DATABASE condominio_db;
   -- Importar el archivo database/condominio_db.sql
   mysql -u root -p condominio_db < database/condominio_db.sql
   -- Importar la tabla de logs de email
   mysql -u root -p condominio_db < database/email_logs.sql
   ```

4. **Configurar la conexión**
   Editar el archivo `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'condominio_db';
   private $username = 'root';
   private $password = 'tu_contraseña';
   ```

5. **Configurar el servicio de correo electrónico**
   Copiar el archivo `.env.example` a `.env` y configurar las credenciales:
   ```bash
   cp .env.example .env
   ```
   
   Editar `.env` con tus credenciales de correo:
   ```env
   # Para Gmail
   MAIL_DRIVER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=tu-email@gmail.com
   MAIL_PASSWORD=tu-app-password
   MAIL_FROM_ADDRESS=noreply@condoweb.com
   MAIL_FROM_NAME=CondoWeb
   MAIL_TEST_MODE=false
   ```
   
   **Nota para Gmail**: Si usas Gmail con autenticación de dos factores, necesitas generar una "Contraseña de aplicación" en https://myaccount.google.com/apppasswords

6. **Configurar el servidor web**
   - Asegurarse que el document root apunte a la carpeta `public/`
   - Configurar URL amigables (mod_rewrite en Apache)

7. **Permisos de archivos**
   ```bash
   chmod -R 755 .
   chmod -R 777 logs/
   chmod 600 .env
   ```

### Usuario por Defecto
- **Email**: admin@condominio.com
- **Contraseña**: password

## Estructura del Proyecto

```
condominio/
├── app/
│   ├── controllers/     # Controladores MVC
│   ├── models/         # Modelos de datos
│   └── views/          # Vistas HTML
├── config/             # Archivos de configuración
├── database/           # Scripts SQL
├── public/             # Archivos públicos
│   ├── css/           # Hojas de estilo
│   ├── js/            # Archivos JavaScript
│   └── images/        # Imágenes
├── logs/              # Logs de la aplicación
└── index.php          # Front controller
```

## Funcionalidades Detalladas

### Dashboard Administrativo
- Estadísticas en tiempo real
- Gráficos interactivos
- Actividades recientes
- Accesos rápidos

### Gestión de Pagos
- Registro de pagos múltiples métodos
- Cálculo automático de moras
- Reportes por período
- Exportación a Excel/CSV

### Sistema de Incidencias
- Categorización de problemas
- Prioridades (alta, media, baja)
- Seguimiento completo
- Notificaciones por email a residentes y administradores

### Notificaciones por Email
- **Pagos Vencidos**: Notificación automática cuando un pago vence
- **Confirmación de Pago**: Email de confirmación al registrar un pago
- **Incidencias**: Notificaciones sobre creación, actualización y resolución
- **Administradores**: Alertas urgentes para incidencias de alta prioridad
- **Modo de Prueba**: Opción para registrar emails en archivos sin enviarlos
- **Registro de Logs**: Seguimiento completo de todos los emails enviados

### Reportes
- Reportes financieros
- Estadísticas de ocupación
- Análisis de incidencias
- Datos exportables

## API Endpoints

La aplicación incluye endpoints AJAX para:

- `/reports/chartData` - Datos para gráficos
- `/residents/getActiveResidents` - Lista de residentes activos
- `/incidents/changeStatus/{id}` - Cambiar estado de incidencia

## Seguridad Implementada

- **Autenticación**: Sistema de login seguro
- **Autorización**: Control de acceso por roles
- **Validación**: Validación de datos en servidor
- **Sanitización**: Limpieza de datos de entrada
- **Encriptación**: Contraseñas hasheadas
- **CSRF**: Tokens de protección (implementación pendiente)

## Personalización

### Agregar Nuevos Módulos
1. Crear modelo en `app/models/`
2. Crear controlador en `app/controllers/`
3. Crear vistas en `app/views/`
4. Agregar rutas en `index.php`

### Modificar Estilos
- Editar `public/css/style.css`
- Las variables CSS están definidas al inicio del archivo

### Configuración Adicional
- Editar `config/config.php` para ajustes generales
- Modificar `config/database.php` para conexión a BD

## Contribución

1. Fork del proyecto
2. Crear rama de características (`git checkout -b feature/nueva-funcionalidad`)
3. Commit de cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT.

## Soporte

Para reportes de problemas o solicitudes de características:
- Crear un issue en el repositorio
- Enviar un email a soporte@condominio.com

## Roadmap Futuro

- [ ] Sistema de reservas de áreas comunes
- [x] Notificaciones por email (implementado)
- [ ] Notificaciones por SMS
- [ ] Aplicación móvil
- [ ] Integración con pasarelas de pago
- [ ] Sistema de encuestas
- [ ] Chat interno
- [ ] Backup automático
- [ ] Multi-condominio

## Configuración de Email

El sistema incluye un módulo completo de notificaciones por correo electrónico que envía alertas automáticas a residentes y administradores.

### Proveedores Soportados

- **SMTP**: Gmail, Outlook, servidores SMTP personalizados
- **SendGrid**: API de SendGrid (opcional)

### Configuración con Gmail

1. Habilitar autenticación de dos factores en tu cuenta de Gmail
2. Generar una contraseña de aplicación en https://myaccount.google.com/apppasswords
3. Configurar el archivo `.env`:

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicacion
MAIL_FROM_ADDRESS=noreply@condoweb.com
MAIL_FROM_NAME=CondoWeb
MAIL_TEST_MODE=false
```

### Configuración con SendGrid

1. Crear una cuenta en SendGrid
2. Generar una API Key
3. Configurar el archivo `.env`:

```env
MAIL_DRIVER=sendgrid
SENDGRID_API_KEY=tu-api-key-de-sendgrid
MAIL_FROM_ADDRESS=noreply@condoweb.com
MAIL_FROM_NAME=CondoWeb
MAIL_TEST_MODE=false
```

### Modo de Prueba

Para probar el sistema sin enviar emails reales, activa el modo de prueba:

```env
MAIL_TEST_MODE=true
```

Los emails se registrarán en `logs/emails/email_YYYY-MM-DD.log` en lugar de enviarse.

### Tipos de Notificaciones

- **Pago Vencido**: Se envía automáticamente cuando un pago vence
- **Confirmación de Pago**: Se envía al registrar un nuevo pago
- **Recordatorio de Pago**: Se puede configurar para enviar 3 días antes del vencimiento
- **Incidencia Creada**: Notifica al residente cuando reporta una incidencia
- **Incidencia Actualizada**: Notifica cambios de estado
- **Incidencia Resuelta**: Confirma la resolución de la incidencia
- **Alerta a Administradores**: Notifica a todos los administradores sobre nuevas incidencias

### Monitoreo de Emails

Consulta los logs de email en la base de datos:

```sql
SELECT * FROM email_logs 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;
```

### Solución de Problemas

**Email no se envía:**
- Verifica las credenciales en el archivo `.env`
- Revisa los logs en `logs/emails/` o la tabla `email_logs`
- Asegúrate de que el puerto SMTP no esté bloqueado por el firewall
- Para Gmail, verifica que uses una contraseña de aplicación

**Emails van a spam:**
- Configura registros SPF, DKIM y DMARC en tu dominio
- Usa un dominio verificado como remitente
- Considera usar SendGrid para mejor entregabilidad

## Módulo de Notificaciones de Pagos Vencidos

El sistema incluye un módulo completo de notificaciones automáticas para pagos vencidos:

- **Detección Automática**: Identifica pagos pendientes o atrasados cuya fecha de pago ha vencido
- **Notificaciones Persistentes**: Crea registros en la base de datos que los residentes pueden consultar
- **Prevención de Duplicados**: Evita generar múltiples notificaciones para el mismo pago
- **Actualización de Estados**: Cambia automáticamente el estado de pagos pendientes a atrasados
- **Ejecución Programada**: Script ejecutable mediante cron jobs para detección diaria

### Configuración

Para configurar el sistema de notificaciones automáticas, consulte el archivo [README_NOTIFICATIONS.md](README_NOTIFICATIONS.md) que incluye:

- Instrucciones detalladas de configuración del cron job
- Ejemplos de configuración para diferentes frecuencias
- Guía de monitoreo y logs
- Solución de problemas comunes
- Integración con el sistema existente

### Componentes

- **Notification Model**: Gestiona operaciones CRUD de notificaciones
- **NotificationService**: Lógica de negocio para detección y generación
- **NotificationController**: Interfaces de usuario para residentes y administradores
- **Script de Cron**: `check_overdue_payments.php` - Ejecutable para detección programada



## Changelog

### v1.0.0 (2024-01-01)
- Versión inicial
- Gestión completa de residentes
- Sistema de pagos
- Gestión de incidencias
- Reportes básicos
- Dashboard administrativo

---

**Desarrollado con ❤️ para la gestión eficiente de condominios**
