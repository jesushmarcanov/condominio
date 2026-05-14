# Documentación del Sistema - CondoWeb

## 📋 Información General

**Nombre**: CondoWeb - Sistema de Gestión de Condominio  
**Versión**: 1.2.0  
**Arquitectura**: MVC (Modelo-Vista-Controlador)  
**Lenguaje**: PHP 8.2+  
**Base de Datos**: MySQL 5.7+ / MariaDB 10.2+  
**Servidor Web**: Apache 2.4+ con mod_rewrite  

## 🏗️ Arquitectura del Sistema

### Estructura de Directorios Real

```
C:\xampp\htdocs\condominio\
├── app/
│   ├── controllers/          # Controladores MVC
│   │   ├── Controller.php    # Controlador base
│   │   ├── UserController.php
│   │   ├── ResidentController.php
│   │   ├── PaymentController.php
│   │   ├── IncidentController.php
│   │   ├── ReportController.php
│   │   ├── NotificationController.php
│   │   ├── LateFeeController.php
│   │   ├── PdfController.php
│   │   └── ExcelController.php
│   ├── models/              # Modelos de datos
│   │   ├── Database.php
│   │   ├── User.php
│   │   ├── Resident.php
│   │   ├── Payment.php
│   │   ├── Incident.php
│   │   ├── IncidentEvent.php
│   │   ├── Report.php
│   │   ├── Notification.php
│   │   ├── LateFeeRule.php
│   │   └── LateFeeHistory.php
│   ├── services/            # Servicios de negocio
│   │   ├── EmailService.php
│   │   ├── NotificationService.php
│   │   ├── LateFeeService.php
│   │   ├── PdfService.php
│   │   └── ExcelService.php
│   └── views/               # Vistas HTML/PHP
│       ├── layouts/         # Plantillas base
│       ├── admin/           # Vistas de administrador
│       ├── resident/        # Vistas de residente
│       ├── auth/            # Autenticación
│       ├── payments/        # Pagos
│       ├── incidents/       # Incidencias
│       ├── notifications/   # Notificaciones
│       └── emails/          # Plantillas de email
├── config/                  # Configuración
│   ├── config.php          # Configuración general
│   ├── database.php        # Conexión a BD
│   └── catalogs.php        # Catálogos centralizados
├── database/               # Scripts SQL
│   ├── condominio_db.sql  # Estructura base
│   ├── add_late_fee_system.sql
│   ├── email_logs.sql
│   └── incident_events.sql
├── public/                 # Archivos públicos
│   ├── css/
│   ├── js/
│   └── images/
├── logs/                   # Logs del sistema
│   └── emails/            # Logs de emails
├── vendor/                # Dependencias Composer
├── .env                   # Variables de entorno (NO COMMITEAR)
├── .env.example          # Ejemplo de variables
├── .htaccess             # Configuración Apache
├── index.php             # **FRONT CONTROLLER** (raíz del proyecto)
├── composer.json         # Dependencias PHP
└── README.md            # Documentación principal
```

### ⚠️ IMPORTANTE: Ubicación del Front Controller

**El front controller (`index.php`) está en la RAÍZ del proyecto**, NO en una carpeta `public/`.

**URL de acceso**: `http://localhost/condominio/`

**Configuración de Apache**:
```apache
DocumentRoot "C:/xampp/htdocs"
<Directory "C:/xampp/htdocs/condominio">
    AllowOverride All
    Require all granted
</Directory>
```

**Archivo .htaccess** (en la raíz):
```apache
RewriteEngine On
RewriteBase /condominio/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]
```

## 🔧 Configuración Real del Sistema

### Variables de Entorno (.env)

```env
# Configuración de Email
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=jhmarcano@gmail.com
MAIL_PASSWORD=<app-password>
MAIL_FROM_ADDRESS=noreply@condoweb.com
MAIL_FROM_NAME=ResiTech
MAIL_TEST_MODE=false

# Configuración de Base de Datos (opcional, se usa database.php)
DB_HOST=localhost
DB_NAME=condominio_db
DB_USER=root
DB_PASS=
```

### Configuración de Base de Datos (config/database.php)

```php
class Database {
    private $host = 'localhost';
    private $db_name = 'condominio_db';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
```

### Configuración General (config/config.php)

```php
// URL base de la aplicación
define('APP_NAME', 'ResiTech');
define('APP_VERSION', '1.2.0');
define('APP_URL', 'http://localhost/condominio');

// Rutas del sistema
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora

// Manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Catálogos Centralizados (config/catalogs.php)

```php
// Estados de Residentes
define('RESIDENT_STATUSES', [
    'activo' => 'Activo',
    'inactivo' => 'Inactivo'
]);

// Estados de Pagos
define('PAYMENT_STATUSES', [
    'pagado' => 'Pagado',
    'pendiente' => 'Pendiente',
    'atrasado' => 'Atrasado'
]);

// Métodos de Pago
define('PAYMENT_METHODS', [
    'efectivo' => 'Efectivo',
    'transferencia' => 'Transferencia',
    'tarjeta' => 'Tarjeta',
    'deposito' => 'Depósito'
]);

// Estados de Incidencias
define('INCIDENT_STATUSES', [
    'pendiente' => 'Pendiente',
    'en_proceso' => 'En Proceso',
    'resuelta' => 'Resuelta',
    'cancelada' => 'Cancelada'
]);

// Funciones helper disponibles:
// - getCatalogKeys($catalog)
// - getCatalogLabel($catalog, $key)
// - getCatalogOptions($catalog, $selected, $includeEmpty)
// - getStatusBadgeClass($status, $type)
```

## 🚀 Funcionalidades Implementadas

### 1. Gestión de Usuarios y Residentes

**Controladores**: `UserController.php`, `ResidentController.php`

**Rutas**:
- `/login` - Inicio de sesión
- `/logout` - Cerrar sesión
- `/dashboard` - Dashboard principal
- `/profile` - Perfil de usuario
- `/residents` - Listado de residentes (admin)
- `/residents/create` - Crear residente (admin)
- `/residents/edit/{id}` - Editar residente (admin)
- `/residents/show/{id}` - Ver detalles (admin)
- `/residents/myProfile` - Mi perfil de residente (resident)

**Roles**:
- `admin` - Administrador con acceso completo
- `resident` - Residente con acceso limitado

### 2. Sistema de Pagos

**Controlador**: `PaymentController.php`

**Rutas**:
- `/payments` - Listado de pagos
- `/payments/create` - Crear pago (admin)
- `/payments/edit/{id}` - Editar pago (admin)
- `/payments/show/{id}` - Ver detalles
- `/payments/pending` - Pagos pendientes (admin)
- `/payments/stats` - Estadísticas (admin)
- `/payments/report` - Reporte de pagos (admin)
- `/payments/{id}/adjust-late-fee` - Ajustar mora (admin)

**Características**:
- Registro de pagos con múltiples métodos
- Cálculo automático de moras
- Historial de ajustes de mora
- Exportación a CSV/Excel
- Filtros por mes y estado

### 3. Sistema de Mora (Late Fees)

**Controlador**: `LateFeeController.php`  
**Servicio**: `LateFeeService.php`

**Rutas**:
- `/late-fee-rules` - Listado de reglas (admin)
- `/late-fee-rules/create` - Crear regla (admin)
- `/late-fee-rules/edit/{id}` - Editar regla (admin)
- `/late-fee-rules/simulate` - Simulador de mora (admin)
- `/late-fees/report` - Reporte de mora (admin)
- `/late-fees/stats` - Estadísticas de mora (admin)

**Características**:
- Reglas configurables de mora
- Tipos: porcentaje o monto fijo
- Frecuencias: única, diaria, semanal, mensual
- Días de gracia configurables
- Tope máximo opcional
- Simulador de cálculo
- Ajustes manuales con justificación
- Historial completo de cambios

**Script Cron**: `calculate_late_fees.php` - Ejecutar diariamente

### 4. Gestión de Incidencias

**Controlador**: `IncidentController.php`

**Rutas**:
- `/incidents` - Listado de incidencias
- `/incidents/create` - Crear incidencia
- `/incidents/edit/{id}` - Editar incidencia (admin)
- `/incidents/show/{id}` - Ver detalles
- `/incidents/stats` - Estadísticas (admin)
- `/incidents/changeStatus/{id}` - Cambiar estado (admin, AJAX)

**Categorías**:
- Agua, Electricidad, Gas, Estructura, Limpieza, Seguridad, Otro

**Estados**:
- Pendiente, En Proceso, Resuelta, Cancelada

**Prioridades**:
- Baja, Media, Alta

### 5. Sistema de Notificaciones

**Controlador**: `NotificationController.php`  
**Servicio**: `NotificationService.php`

**Rutas**:
- `/notifications` - Mis notificaciones
- `/notifications/markAsRead/{id}` - Marcar como leída
- `/notifications/getUnreadCount` - Contador (AJAX)
- `/notifications/admin` - Vista admin (admin)

**Tipos de Notificaciones**:
- Pagos vencidos (automático)
- Confirmación de pago
- Incidencias creadas/actualizadas
- Alertas administrativas

**Script Cron**: `check_overdue_payments.php` - Ejecutar diariamente

### 6. Sistema de Emails

**Servicio**: `EmailService.php`

**Configuración**:
- SMTP (Gmail, Outlook, etc.)
- Plantillas HTML personalizables
- Modo de prueba (logs en archivos)
- Registro en base de datos

**Plantillas Disponibles**:
- `emails/payment_notification.php`
- `emails/late_fee_notification.php`
- `emails/incident_notification.php`
- `emails/admin_notification.php`

**Logs**:
- Tabla: `email_logs`
- Archivos: `logs/emails/email_YYYY-MM-DD.log`

### 7. Reportes y Estadísticas

**Controlador**: `ReportController.php`

**Rutas**:
- `/reports` - Índice de reportes (admin)
- `/reports/dashboard` - Dashboard estadístico (admin)
- `/reports/income` - Reporte de ingresos (admin)
- `/reports/pendingPayments` - Pagos pendientes (admin)
- `/reports/incidents` - Reporte de incidencias (admin)
- `/reports/residents` - Reporte de residentes (admin)
- `/reports/custom` - Reporte personalizado (admin)
- `/reports/chartData` - Datos para gráficos (AJAX)

**Exportación**:
- CSV
- Excel (PhpSpreadsheet)
- PDF (TCPDF)

### 8. Exportación de Documentos

**Controladores**: `PdfController.php`, `ExcelController.php`

**Rutas PDF**:
- `/pdf/income` - Reporte de ingresos
- `/pdf/pending-payments` - Pagos pendientes
- `/pdf/incidents` - Incidencias
- `/pdf/payment-receipt/{id}` - Recibo de pago
- `/pdf/incident-receipt/{id}` - Recibo de incidencia

**Rutas Excel**:
- `/excel/income` - Reporte de ingresos
- `/excel/pending-payments` - Pagos pendientes
- `/excel/incidents` - Incidencias
- `/excel/residents` - Residentes

## 🔐 Seguridad Implementada

### Autenticación y Autorización

```php
// Verificar autenticación
protected function requireAuth() {
    if(!isLoggedIn()) {
        flash('Debe iniciar sesión', 'warning');
        redirect('/login');
    }
}

// Verificar rol de administrador
protected function requireAdmin() {
    $this->requireAuth();
    if(!isAdmin()) {
        flash('No tiene permisos', 'error');
        redirect('/dashboard');
    }
}

// Verificar rol de residente
protected function requireResident() {
    $this->requireAuth();
    if(!isResident()) {
        flash('No tiene permisos', 'error');
        redirect('/dashboard');
    }
}
```

### Validación de Datos

```php
// Ejemplo de validación
$errors = $this->validate($data, [
    'nombre' => ['required' => true, 'max' => 100],
    'email' => ['required' => true, 'email' => true],
    'monto' => ['required' => true, 'numeric' => true, 'min' => 0],
    'estado' => ['required' => true, 'in' => getCatalogKeys(PAYMENT_STATUSES)]
]);
```

### Sanitización

```php
// Función helper global
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
```

### Contraseñas

```php
// Hasheo de contraseñas
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Verificación
if(password_verify($password, $hashed)) {
    // Contraseña correcta
}
```

## 📊 Base de Datos

### Tablas Principales

1. **usuarios** - Usuarios del sistema
2. **residentes** - Información de residentes
3. **pagos** - Registro de pagos
4. **incidencias** - Incidencias reportadas
5. **incident_events** - Historial de cambios en incidencias
6. **notificaciones** - Notificaciones del sistema
7. **late_fee_rules** - Reglas de mora
8. **late_fee_history** - Historial de ajustes de mora
9. **email_logs** - Registro de emails enviados
10. **cuotas_mantenimiento** - Cuotas mensuales
11. **areas_comunes** - Áreas comunes (futuro)
12. **reservas** - Reservas de áreas (futuro)

### Relaciones

```
usuarios (1) -----> (1) residentes
residentes (1) ---> (N) pagos
residentes (1) ---> (N) incidencias
usuarios (1) -----> (N) notificaciones
pagos (1) --------> (N) late_fee_history
late_fee_rules (1) -> (N) pagos
```

## 🛠️ Dependencias

### Composer (composer.json)

```json
{
    "require": {
        "php": ">=7.4",
        "vlucas/phpdotenv": "^5.5",
        "phpmailer/phpmailer": "^6.8",
        "tecnickcom/tcpdf": "^6.6",
        "phpoffice/phpspreadsheet": "^1.28"
    }
}
```

### Instalación

```bash
composer install
```

## 🔄 Scripts de Mantenimiento

### Cálculo Automático de Mora

**Archivo**: `calculate_late_fees.php`

**Ejecución manual**:
```bash
php calculate_late_fees.php
```

**Cron job (diario a las 2:00 AM)**:
```cron
0 2 * * * cd C:\xampp\htdocs\condominio && php calculate_late_fees.php >> logs/late_fees.log 2>&1
```

### Verificación de Pagos Vencidos

**Archivo**: `check_overdue_payments.php`

**Ejecución manual**:
```bash
php check_overdue_payments.php
```

**Cron job (diario a las 1:00 AM)**:
```cron
0 1 * * * cd C:\xampp\htdocs\condominio && php check_overdue_payments.php >> logs/overdue.log 2>&1
```

## 📝 Credenciales por Defecto

**Administrador**:
- Email: `admin@condominio.com`
- Contraseña: `password`

**⚠️ IMPORTANTE**: Cambiar la contraseña después de la primera instalación.

## 🐛 Debugging

### Activar Errores

En `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Logs del Sistema

- **Errores PHP**: `logs/error.log`
- **Emails**: `logs/emails/email_YYYY-MM-DD.log`
- **Mora**: `logs/late_fees.log`
- **Pagos vencidos**: `logs/overdue.log`

### Consultas Útiles

```sql
-- Ver últimos emails enviados
SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 10;

-- Ver notificaciones no leídas
SELECT * FROM notificaciones WHERE leida = 0;

-- Ver pagos con mora
SELECT * FROM pagos WHERE monto_mora > 0;

-- Ver historial de ajustes de mora
SELECT * FROM late_fee_history ORDER BY created_at DESC;
```

## 📞 Soporte

Para problemas o preguntas:
1. Revisar logs del sistema
2. Verificar configuración en archivos `.env` y `config/`
3. Consultar esta documentación
4. Revisar archivos `*_GUIDE.md` y `*_SETUP.md`

---

**Última actualización**: 2024-01-15  
**Versión del documento**: 1.0.0
