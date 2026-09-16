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

// Configuración de pagos en línea
// En modo test, el administrador debe verificar manualmente cada declaración
// contra su banco antes de confirmar el pago (no hay integración API real).
define('PAYMENT_TEST_MODE', filter_var(getenv('PAYMENT_TEST_MODE') ?: 'true', FILTER_VALIDATE_BOOLEAN));

// Zona horaria
date_default_timezone_set('America/Caracas');

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
        return formatAmountIn(0, baseCurrency());
    }
    return formatAmountIn((float)$amount, baseCurrency());
}

// ----- Helpers de multimoneda (USD / VES) -----

/**
 * Leer una clave de la configuración de moneda (global cargada en el bootstrap)
 */
function currencyConfig($key) {
    if (isset($GLOBALS['CURRENCY_CFG']) && isset($GLOBALS['CURRENCY_CFG'][$key])) {
        return $GLOBALS['CURRENCY_CFG'][$key];
    }
    return null;
}

/**
 * Moneda base de almacenamiento ('USD' o 'VES')
 */
function baseCurrency() {
    $base = currencyConfig('moneda_base');
    return $base ? $base : 'USD';
}

/**
 * La otra moneda (secundaria de visualización)
 */
function otherCurrency() {
    return baseCurrency() === 'VES' ? 'USD' : 'VES';
}

/**
 * Símbolo de una moneda ('USD' => '$', 'VES' => 'Bs')
 */
function currencySymbol($ccy = null) {
    $ccy = $ccy ? strtoupper($ccy) : baseCurrency();
    return $ccy === 'VES' ? 'Bs' : '$';
}

/**
 * Tasa de cambio Bs por 1 USD (float) o null si no está configurada
 */
function bcvRate() {
    $rate = currencyConfig('tasa_cambio');
    return ($rate === null || $rate === '') ? null : (float)$rate;
}

/**
 * Fecha de vigencia de la tasa
 */
function bcvRateDate() {
    return currencyConfig('tasa_fecha');
}

/**
 * Origen de la tasa ('bcv' o 'manual')
 */
function bcvRateSource() {
    $src = currencyConfig('tasa_origen');
    return $src ? $src : 'bcv';
}

/**
 * ¿Está activo el reflejo en la segunda moneda?
 */
function dualCurrencyEnabled() {
    $activo = currencyConfig('multimoneda_activo');
    return $activo === null ? true : (bool)$activo;
}

/**
 * Convertir un monto entre monedas
 */
function convertCurrency($amount, $from, $to) {
    $rate = bcvRate();
    if (!$rate || $rate <= 0 || $from === $to) {
        return (float)$amount;
    }
    if (strtoupper($from) === 'VES') {
        return (float)$amount / $rate;
    }
    return (float)$amount * $rate;
}

/**
 * Formatear un monto en la moneda indicada (símbolo + número, formato $1,500.00)
 */
function formatAmountIn($amount, $ccy) {
    return currencySymbol($ccy) . ' ' . number_format((float)$amount, 2);
}

/**
 * Calcular los valores en USD y VES de un monto expresado en moneda base.
 * 
 * @param float $amount Monto en moneda base
 * @return array{USD: float|null, VES: float|null}
 */
function currencyPairValues($amount) {
    $base = baseCurrency();
    $rate = bcvRate();
    $has_rate = $rate !== null && $rate > 0;

    $usd = null;
    $ves = null;

    if ($base === 'USD') {
        $usd = (float)$amount;
        if ($has_rate) {
            $ves = (float)$amount * $rate;
        }
    } else { // base VES
        $ves = (float)$amount;
        if ($has_rate) {
            $usd = (float)$amount / $rate;
        }
    }

    return ['USD' => $usd, 'VES' => $ves];
}

/**
 * Formatear un monto (expresado en moneda base) como texto dual,
 * p. ej. "$ 1,500.00 · Bs 54,000.00". Solo la moneda base si no hay tasa.
 *
 * El monto PRINCIPAL siempre se muestra en la moneda base configurada
 * y la conversión secundaria en la otra moneda.
 */
function formatCurrencyDualText($amount, $separator = ' · ') {
    $values = currencyPairValues((float)$amount);
    $primary = currencyPairPrimaryKey($values);
    $secondary = otherCurrency();

    if ($values['USD'] !== null && $values['VES'] !== null && dualCurrencyEnabled()) {
        return formatAmountIn($values[$primary], $primary) . $separator
             . formatAmountIn($values[$secondary], $secondary);
    }

    return formatAmountIn($values[$primary], $primary);
}

/**
 * Clave de la moneda principal (la moneda base) de un par de valores.
 */
function currencyPairPrimaryKey($values) {
    if (is_array($values)) {
        $primary = baseCurrency();
        if (isset($values[$primary]) && $values[$primary] !== null) {
            return $primary;
        }
        foreach (['USD', 'VES'] as $ccy) {
            if (isset($values[$ccy]) && $values[$ccy] !== null) {
                return $ccy;
            }
        }
    }
    return 'USD';
}

/**
 * Formatear dual en HTML (para pantalla): monto principal (en moneda base)
 * + conversión secundaria pequeña.
 *
 * Ej.: <span class="money-primary">Bs 54,000.00</span>
 *      <span class="money-secondary">$ 66.90</span>
 */
function formatCurrencyDual($amount) {
    $values = currencyPairValues((float)$amount);

    if ($values['USD'] !== null && $values['VES'] !== null && dualCurrencyEnabled()) {
        $primary = $values[baseCurrency()] !== null ? baseCurrency() : otherCurrency();
        $secondary = otherCurrency();
        return '<span class="money-primary">' . formatAmountIn($values[$primary], $primary) . '</span>'
             . '<span class="money-secondary">' . formatAmountIn($values[$secondary], $secondary) . '</span>';
    }

    $primary = currencyPairPrimaryKey($values);
    return '<span class="money-primary">' . formatAmountIn($values[$primary], $primary) . '</span>';
}

/**
 * Convertir y formatear un monto que está en la moneda dada, mostrando
 * su equivalente en la moneda base (formato dual HTML). Útil para pagos
 * declarados en Bs o USD.
 */
function formatCurrencyFrom($amount, $ccy) {
    $values = currencyPairValues(convertCurrency($amount, strtoupper($ccy), baseCurrency()));

    if ($values['USD'] !== null && $values['VES'] !== null && dualCurrencyEnabled()) {
        $primary = $values[baseCurrency()] !== null ? baseCurrency() : otherCurrency();
        $secondary = otherCurrency();
        return '<span class="money-primary">' . formatAmountIn($values[$primary], $primary) . '</span>'
             . '<span class="money-secondary">' . formatAmountIn($values[$secondary], $secondary) . '</span>';
    }

    $primary = currencyPairPrimaryKey($values);
    return '<span class="money-primary">' . formatAmountIn($values[$primary], $primary) . '</span>';
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

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf_token() {
    $token = $_POST['_csrf_token'] ?? '';
    return !empty($token) && hash_equals(csrf_token(), $token);
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
