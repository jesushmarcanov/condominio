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
- Notificaciones de pagos vencidos

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
- Composer (opcional)

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <repository-url>
   cd condominio
   ```

2. **Configurar la base de datos**
   ```sql
   CREATE DATABASE condominio_db;
   -- Importar el archivo database/condominio_db.sql
   mysql -u root -p condominio_db < database/condominio_db.sql
   ```

3. **Configurar la conexión**
   Editar el archivo `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'condominio_db';
   private $username = 'root';
   private $password = 'tu_contraseña';
   ```

4. **Configurar el servidor web**
   - Asegurarse que el document root apunte a la carpeta `public/`
   - Configurar URL amigables (mod_rewrite en Apache)

5. **Permisos de archivos**
   ```bash
   chmod -R 755 .
   chmod -R 777 logs/
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
- Notificaciones por email

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
- [ ] Notificaciones por SMS
- [ ] Aplicación móvil
- [ ] Integración con pasarelas de pago
- [ ] Sistema de encuestas
- [ ] Chat interno
- [ ] Backup automático
- [ ] Multi-condominio

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
