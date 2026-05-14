<?php
// Configuración general de la aplicación

// Configuración de la aplicación
define('APP_NAME', 'CondoWeb');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/condominio');

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora

// Configuración de correo (opcional)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_FROM_EMAIL', 'noreply@condominio.com');
define('MAIL_FROM_NAME', 'Sistema de Condominio');

// Configuración de seguridad
define('HASH_ALGORITHM', PASSWORD_DEFAULT);
define('SALT_LENGTH', 22);

// Configuración de subida de archivos
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Funciones helper
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatDate($date) {
    if ($date === null || $date === '') {
        return '';
    }
    return date('d/m/Y H:i', strtotime($date));
}

function formatCurrency($amount) {
    if ($amount === null || $amount === '') {
        return '$0.00';
    }
    return '$' . number_format((float)$amount, 2);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isResident() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'resident';
}

function redirect($url) {
    header("Location: " . APP_URL . $url);
    exit();
}

function flash($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
