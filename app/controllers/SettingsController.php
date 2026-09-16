<?php
/**
 * Controlador de Configuración General (Multimoneda)
 * 
 * Gestiona la configuración global de la aplicación: moneda base, tasa de
 * cambio Bs/USD (automática desde BCV o manual) y el reflejo en la segunda
 * moneda en todas las pantallas.
 * 
 * @package App\Controllers
 * @author Jesús H. Marcano V.
 * @version 1.0.0
 */

class SettingsController extends Controller {
    private $setting;
    private $currencyService;

    public function __construct() {
        parent::__construct();
        $this->setting = new AppSetting($this->db);
        $this->currencyService = new CurrencyService($this->db);
    }

    /**
     * Mostrar la configuración general
     * GET /settings
     */
    public function index() {
        $this->requireAdmin();

        $config = $this->currencyService->getConfig();

        $this->view('admin/settings/index', [
            'config' => $config,
            'animated_snapshot' => null
        ]);
    }

    /**
     * Actualizar la configuración de moneda
     * POST /settings
     */
    public function update() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('Método no permitido', 'error');
            redirect('/settings');
            return;
        }

        $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        switch ($accion) {
            case 'base':
                $data = $this->getPostData();
                $errors = $this->validateRules('settings.base', $data);
                if (!empty($errors)) {
                    flash('Error en la validación: ' . implode(', ', $errors), 'error');
                    redirect('/settings');
                    return;
                }
                $result = $this->currencyService->changeBase($data['moneda_base'], $user_id);
                flash($result['message'], $result['success'] ? 'success' : 'error');
                break;

            case 'rate':
                $data = $this->getPostData();
                $errors = $this->validateRules('settings.rate', $data);
                if (!empty($errors)) {
                    flash('Error en la validación: ' . implode(', ', $errors), 'error');
                    redirect('/settings');
                    return;
                }
                $fecha = !empty($data['tasa_fecha']) ? $data['tasa_fecha'] : date('Y-m-d');
                if ($this->currencyService->setManualRate((float)$data['tasa'], $fecha, $user_id)) {
                    flash('Tasa de cambio guardada manualmente: 1 USD = Bs ' . number_format((float)$data['tasa'], 2), 'success');
                } else {
                    flash('No se pudo guardar la tasa de cambio', 'error');
                }
                break;

            case 'bcv':
                $result = $this->currencyService->refreshFromBcv($user_id);
                if ($result['success']) {
                    flash($result['message'], 'success');
                } else {
                    flash($result['message'], 'error');
                }
                break;

            case 'toggle':
                $activo = isset($_POST['activo']) && $_POST['activo'] === '1';
                $this->currencyService->setDualEnabled($activo, $user_id);
                flash($activo ? 'Reflejo en la segunda moneda activado' : 'Reflejo en la segunda moneda desactivado', 'success');
                break;

            default:
                flash('Acción no reconocida', 'error');
                break;
        }

        redirect('/settings');
    }
}
?>