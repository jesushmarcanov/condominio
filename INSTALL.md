# Guía de Instalación - Sistema de Gestión de Condominio

## Requisitos del Sistema

### Servidor Web
- **Apache 2.4+** o **Nginx 1.18+**
- Módulo `mod_rewrite` habilitado (Apache)
- Soporte para archivos `.htaccess`

### PHP
- **PHP 7.4+** (recomendado PHP 8.0+)
- Extensiones requeridas:
  - `pdo_mysql`
  - `mbstring`
  - `json`
  - `session`
  - `filter`

### Base de Datos
- **MySQL 5.7+** o **MariaDB 10.2+**
- Usuario con privilegios de CREATE, INSERT, UPDATE, DELETE, SELECT

## Pasos de Instalación

### 1. Descargar el Proyecto

```bash
# Si tienes Git
git clone <repository-url> condominio
cd condominio

# O descarga los archivos y descomprímelos en la carpeta del servidor
```

### 2. Configurar la Base de Datos

#### Opción A: Usando phpMyAdmin
1. Abre phpMyAdmin
2. Crea una nueva base de datos llamada `condominio_db`
3. Selecciona la base de datos
4. Haz clic en "Importar"
5. Selecciona el archivo `database/condominio_db.sql`
6. Haz clic en "Ejecutar"

#### Opción B: Usando línea de comandos
```bash
# Iniciar sesión en MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE condominio_db;

# Salir de MySQL
exit

# Importar el archivo SQL
mysql -u root -p condominio_db < database/condominio_db.sql
```

### 3. Configurar la Conexión a la Base de Datos

Edita el archivo `config/database.php`:

```php
<?php
class Database {
    private $host = 'localhost';        // Servidor de base de datos
    private $db_name = 'condominio_db'; // Nombre de la base de datos
    private $username = 'root';        // Usuario de la base de datos
    private $password = '';            // Contraseña del usuario
    // ...
}
```

### 4. Configurar el Servidor Web

#### Apache
1. Asegúrate que `mod_rewrite` esté habilitado:
   ```bash
   sudo a2enmod rewrite
   ```

2. Configura el Virtual Host:
   ```apache
   <VirtualHost *:80>
       ServerName condominio.local
       DocumentRoot /ruta/al/proyecto/public
       
       <Directory /ruta/al/proyecto/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. Reinicia Apache:
   ```bash
   sudo systemctl restart apache2
   ```

#### Nginx
```nginx
server {
    listen 80;
    server_name condominio.local;
    root /ruta/al/proyecto/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Establecer Permisos

```bash
# Permisos básicos
chmod -R 755 .

# Permisos para la carpeta de logs
chmod -R 777 logs/

# Permisos para archivos de configuración (si es necesario)
chmod 644 config/database.php
chmod 644 config/config.php
```

### 6. Configurar el Archivo .htaccess

Crea el archivo `.htaccess` en la raíz del proyecto:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]

# Seguridad
<Files config/*>
    Require all denied
</Files>

<Files app/*>
    Require all denied
</Files>

<Files database/*>
    Require all denied
</Files>

<Files logs/*>
    Require all denied
</Files>
```

### 7. Verificar la Instalación

1. Abre tu navegador web
2. Navega a `http://localhost/condominio` (o la URL que configuraste)
3. Deberías ver la página de login

### 8. Iniciar Sesión

Usa las credenciales por defecto:
- **Email**: admin@condominio.com
- **Contraseña**: password

## Configuración Adicional

### Cambiar Contraseña de Administrador

1. Inicia sesión como administrador
2. Ve a "Mi Perfil"
3. Cambia la contraseña por una segura

### Configurar Correo Electrónico (Opcional)

Edita `config/config.php`:

```php
// Configuración de correo
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'tu_email@gmail.com');
define('MAIL_PASSWORD', 'tu_contraseña');
define('MAIL_FROM_EMAIL', 'noreply@condominio.com');
define('MAIL_FROM_NAME', 'Sistema de Condominio');
```

### Configurar Zona Horaria

Edita `config/config.php`:

```php
// Zona horaria
date_default_timezone_set('America/Mexico_City');
```

## Solución de Problemas

### Error de Conexión a la Base de Datos
- Verifica que el servidor MySQL esté corriendo
- Confirma que el usuario y contraseña son correctos
- Asegúrate que la base de datos `condominio_db` existe

### Error 404
- Verifica que `mod_rewrite` esté habilitado (Apache)
- Confirma que el archivo `.htaccess` existe
- Revisa la configuración del Virtual Host

### Error de Permisos
```bash
# Para errores de escritura en logs
chmod -R 777 logs/

# Para errores de acceso a archivos
chmod -R 755 .
```

### Páginas en Blanco
- Activa la visualización de errores en `config/config.php`:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```
- Revisa el archivo de errores de Apache/Nginx

## Características Opcionales

### Integración con SendGrid para Correos

1. Instala SendGrid:
   ```bash
   composer require sendgrid/sendgrid
   ```

2. Configura las credenciales en `config/config.php`

### Sistema de Reservas de Áreas Comunes

Esta característica está preparada en la base de datos pero requiere desarrollo adicional.

## Mantenimiento

### Backup de la Base de Datos
```bash
# Crear backup
mysqldump -u root -p condominio_db > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u root -p condominio_db < backup_20240101.sql
```

### Actualización del Sistema
1. Respaldar la base de datos
2. Reemplazar archivos con la nueva versión
3. Ejecutar scripts de actualización (si los hay)
4. Verificar configuración

## Soporte

Si encuentras algún problema:

1. Revisa los logs de errores del servidor web
2. Verifica la configuración de PHP y la base de datos
3. Consulta la documentación en el archivo `README.md`
4. Crea un issue en el repositorio del proyecto

---

**¡Listo! Tu sistema de gestión de condominio está instalado y funcionando.**
