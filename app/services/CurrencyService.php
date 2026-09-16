<?php
/**
 * Servicio de Moneda y Cambio (Multimoneda USD / VES)
 * 
 * Centraliza la lógica de multimoneda del sistema:
 * - Moneda base configurable (USD o VES)
 * - Tasa de cambio Bs/USD del Banco Central de Venezuela
 * - Obtención automática de la tasa desde BCV Today (sin API key)
 * - Conversión y formateo de montos en ambas monedas
 * 
 * Fuente de tasa: https://bcv.today/api/v1/rate.json
 * (dataset JSON gratuito con la tasa oficial publicada por el BCV)
 * 
 * @package App\Services
 * @author Jesús H. Marcano V.
 * @version 1.0.0
 */

class CurrencyService {
    const BCV_API_URL = 'https://bcv.today/api/v1/rate.json';

    private $db;
    private $setting;
    private static $config = null;

    /**
     * Constructor
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->db = $db;
        $this->setting = new AppSetting($db);
    }

    /**
     * Obtener la configuración de moneda (con caché estática)
     * 
     * @return array Configuración de moneda
     */
    public function getConfig() {
        if (self::$config === null) {
            self::$config = [
                'moneda_base' => $this->setting->get('moneda_base') ?: 'USD',
                'tasa_cambio' => $this->setting->get('tasa_cambio'),
                'tasa_fecha' => $this->setting->get('tasa_fecha'),
                'tasa_origen' => $this->setting->get('tasa_origen') ?: 'bcv',
                'multimoneda_activo' => $this->setting->get('multimoneda_activo') !== '0'
            ];
        }
        return self::$config;
    }

    /**
     * Exportar la configuración a variables globales para los helpers de formato
     * 
     * @return array Configuración cargada
     */
    public function exportToGlobals() {
        $config = $this->getConfig();
        $GLOBALS['CURRENCY_CFG'] = $config;
        return $config;
    }

    /**
     * Invalidar la caché de configuración (tras guardar cambios)
     * 
     * @return void
     */
    public function clearCache() {
        self::$config = null;
    }

    /**
     * Moneda base de almacenamiento
     * 
     * @return string 'USD' o 'VES'
     */
    public function baseCurrency() {
        return $this->getConfig()['moneda_base'];
    }

    /**
     * Tasa de cambio Bs por 1 USD
     * 
     * @return float|null Tasa o null si no configurada
     */
    public function rate() {
        $rate = $this->getConfig()['tasa_cambio'];
        return ($rate === null || $rate === '') ? null : (float)$rate;
    }

    /**
     * Fecha de vigencia de la tasa
     * 
     * @return string|null Fecha o null
     */
    public function rateDate() {
        $date = $this->getConfig()['tasa_fecha'];
        return ($date === null || $date === '') ? null : $date;
    }

    /**
     * Origen de la tasa ('bcv' o 'manual')
     * 
     * @return string
     */
    public function rateSource() {
        return $this->getConfig()['tasa_origen'];
    }

    /**
     * ¿Está activado el reflejo en la segunda moneda?
     * 
     * @return bool
     */
    public function dualEnabled() {
        return $this->getConfig()['multimoneda_activo'];
    }

    /**
     * Dividir un monto entre moneda base y la otra moneda
     * 
     * @return array{base: float, other: float} Montos en cada moneda
     */
    public function split($amount) {
        $base = $this->baseCurrency();
        $other = $base === 'VES' ? 'USD' : 'VES';
        $rate = $this->rate();

        if (!$rate || $rate <= 0) {
            return ['base' => (float)$amount, 'other' => null];
        }

        if ($base === 'VES') {
            // $amount está en Bs
            return ['base' => (float)$amount, 'other' => (float)$amount / $rate];
        }

        // $amount está en USD
        return ['base' => (float)$amount, 'other' => (float)$amount * $rate];
    }

    /**
     * Convertir un monto entre monedas
     * 
     * @param float $amount Monto a convertir
     * @param string $from Moneda de origen ('USD' o 'VES')
     * @param string $to Moneda de destino ('USD' o 'VES')
     * @return float|null Monto convertido o null si no hay tasa
     */
    public function convert($amount, $from, $to) {
        $rate = $this->rate();
        if (!$rate || $rate <= 0 || $from === $to) {
            return (float)$amount;
        }
        if ($from === 'VES') {
            return (float)$amount / $rate;
        }
        return (float)$amount * $rate;
    }

    /**
     * Normalizar un monto de la moneda dada a la moneda base
     * 
     * @param float $amount Monto en la moneda indicada
     * @param string $currency Moneda del monto ('USD' o 'VES')
     * @return float|null Equivalente en moneda base
     */
    public function toBase($amount, $currency = null) {
        $currency = $currency ?: $this->baseCurrency();
        return $this->convert($amount, $currency, $this->baseCurrency());
    }

    /**
     * Obtener la tasa vigente desde el BCV
     * 
     * @return array{tasa: float, fecha: string}|false Tasa obtenida o false si falló
     */
    public function fetchBcvRate() {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
                'header' => "User-Agent: CondoWeb/1.0\r\nCache-Control: no-cache\r\n"
            ]
        ]);

        $json = @file_get_contents(self::BCV_API_URL, false, $context);

        if ($json === false && function_exists('curl_init')) {
            $ch = curl_init(self::BCV_API_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'CondoWeb/1.0',
                CURLOPT_HTTPHEADER => ['Cache-Control: no-cache']
            ]);
            $json = curl_exec($ch);
            curl_close($ch);
        }

        if ($json === false) {
            return false;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['USD'])) {
            return false;
        }

        $rate = (float)$data['USD'];
        if ($rate <= 1 || $rate >= 100000) {
            return false;
        }

        $fecha = isset($data['effective_date']) ? $data['effective_date']
               : (isset($data['date']) ? $data['date'] : date('Y-m-d'));

        return ['tasa' => $rate, 'fecha' => $fecha];
    }

    /**
     * Actualizar la tasa de cambio desde el BCV
     * 
     * @param int|null $usuario_id Usuario que ejecuta la actualización
     * @return array{success: bool, message: string} Resultado de la operación
     */
    public function refreshFromBcv($usuario_id = null) {
        $result = $this->fetchBcvRate();

        if ($result === false) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener la tasa del BCV. Verifique la conexión a internet e intente de nuevo.'
            ];
        }

        $this->setting->set('tasa_cambio', $result['tasa'], $usuario_id);
        $this->setting->set('tasa_fecha', $result['fecha'], $usuario_id);
        $this->setting->set('tasa_origen', 'bcv', $usuario_id);
        $this->clearCache();

        return [
            'success' => true,
            'message' => 'Tasa actualizada desde el BCV: 1 USD = Bs ' . number_format($result['tasa'], 2) .
                         ' (vigente ' . $result['fecha'] . ')'
        ];
    }

    /**
     * Método estático de conveniencia: configurar la tasa manualmente
     * 
     * @param float $tasa Bs por 1 USD
     * @param string|null $fecha Fecha de vigencia
     * @param int|null $usuario_id Usuario que realiza el cambio
     * @return bool True si se guardó
     */
    public function setManualRate($tasa, $fecha = null, $usuario_id = null) {
        if ($tasa <= 0) {
            return false;
        }
        $ok = $this->setting->set('tasa_cambio', $tasa, $usuario_id);
        $ok = $this->setting->set('tasa_origen', 'manual', $usuario_id) && $ok;
        if ($fecha) {
            $ok = $this->setting->set('tasa_fecha', $fecha, $usuario_id) && $ok;
        }
        $this->clearCache();
        return $ok;
    }

/**
 * Guardar la moneda base
 * 
 * @param string $moneda 'USD' o 'VES'
 * @param int|null $usuario_id Usuario que realiza el cambio
 * @return bool True si se guardó
 */
public function setBaseCurrency($moneda, $usuario_id = null) {
    $moneda = strtoupper($moneda);
    if (!in_array($moneda, ['USD', 'VES'], true)) {
        return false;
    }
    $ok = $this->setting->set('moneda_base', $moneda, $usuario_id);
    $this->clearCache();
    return $ok;
}

/**
 * Cambiar la moneda base sin tocar los montos guardados.
 * 
 * Solo actualiza la configuración moneda_base. Los montos existentes NO se
 * convierten ni modifican; cada registro conserva su moneda de creación.
 * La conversión a la moneda base se realiza en tiempo de visualización
 * (dashboard, listados, reportes) usando la tasa vigente.
 * 
 * Nota: los montos de las tablas que NO tienen columna moneda (cuotas de
 * mantenimiento, reglas/historial de mora) se interpretan siempre en la
 * moneda base actual; los que sí la tienen (pagos, declaraciones_pago)
 * se interpretan en la moneda que guardan.
 * 
 * @param string $newBase 'USD' o 'VES'
 * @param int|null $usuario_id Usuario que realiza el cambio
 * @return array{success: bool, message: string, from?: string, to?: string}
 */
public function changeBase($newBase, $usuario_id = null) {
    $newBase = strtoupper($newBase);
    if (!in_array($newBase, ['USD', 'VES'], true)) {
        return ['success' => false, 'message' => 'Moneda no válida'];
    }

    $oldBase = $this->getConfig()['moneda_base'];

    if (!$this->setting->set('moneda_base', $newBase, $usuario_id)) {
        return ['success' => false, 'message' => 'No se pudo guardar la nueva moneda base'];
    }

    $this->clearCache();

    return [
        'success' => true,
        'message' => 'La moneda base ahora es ' . ($newBase === 'VES' ? 'Bolívares (Bs)' : 'Dólares (USD)') .
                     '. Los montos guardados no se modifican: se convierten automáticamente en todas las pantallas con la tasa vigente.',
        'from' => $oldBase,
        'to' => $newBase
    ];
}

    /**
     * Activar/desactivar el reflejo en la segunda moneda
     * 
     * @param bool $activo Activar o desactivar
     * @param int|null $usuario_id Usuario que realiza el cambio
     * @return bool True si se guardó
     */
    public function setDualEnabled($activo, $usuario_id = null) {
        $ok = $this->setting->set('multimoneda_activo', $activo ? '1' : '0', $usuario_id);
        $this->clearCache();
        return $ok;
    }
}
?>