<?php
// Configuración de la base de datos
// Las credenciales se leen de variables de entorno (.env) para no exponer
// datos sensibles en el repositorio. Los valores por defecto solo sirven
// para el entorno de desarrollo local (XAMPP).

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'condominio_db');
define('DB_USER', getenv('DB_USER') ?: 'local_user');
define('DB_PASS', getenv('DB_PASS') ?: '123456');
?>