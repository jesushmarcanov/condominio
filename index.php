<?php
// Front Controller - Punto de entrada principal de la aplicación

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Cargar autoloader de Composer
require_once ROOT_PATH . '/vendor/autoload.php';

// Cargar variables de entorno
try {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
} catch (Exception $e) {
    // Si no existe .env, continuar sin variables de entorno
    error_log("[Bootstrap] .env file not found or invalid: " . $e->getMessage());
}

// Cargar configuración
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/catalogs.php';

// Iniciar sesión
session_start();

// Validar CSRF en solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        http_response_code(403);
        flash('Token de seguridad inválido. Intente de nuevo.', 'error');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/'));
        exit();
    }
}

// Cargar clases básicas
require_once APP_PATH . '/models/Database.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Resident.php';
require_once APP_PATH . '/models/Payment.php';
require_once APP_PATH . '/models/Incident.php';
require_once APP_PATH . '/models/CommonArea.php';
require_once APP_PATH . '/models/Reservation.php';
require_once APP_PATH . '/models/Report.php';
require_once APP_PATH . '/models/Notification.php';
require_once APP_PATH . '/models/IncidentEvent.php';
require_once APP_PATH . '/models/BankAccount.php';
require_once APP_PATH . '/models/PaymentDeclaration.php';
require_once APP_PATH . '/models/AppSetting.php';
require_once APP_PATH . '/models/Empresa.php';
require_once APP_PATH . '/models/MedioPago.php';

// Cargar modelos de mora si existen
if (file_exists(APP_PATH . '/models/LateFeeRule.php')) {
    require_once APP_PATH . '/models/LateFeeRule.php';
}
if (file_exists(APP_PATH . '/models/LateFeeHistory.php')) {
    require_once APP_PATH . '/models/LateFeeHistory.php';
}

// Cargar servicios
require_once APP_PATH . '/services/EmailService.php';
require_once APP_PATH . '/services/NotificationService.php';
require_once APP_PATH . '/services/PdfService.php';
require_once APP_PATH . '/services/ExcelService.php';
require_once APP_PATH . '/services/PaymentNotificationService.php';

// Cargar servicio de moneda y exportar configuración a globales
require_once APP_PATH . '/services/CurrencyService.php';
$currencyService = new CurrencyService((new Database())->getConnection());
$currencyService->exportToGlobals();

// Cargar servicio de mora si existe
if (file_exists(APP_PATH . '/services/LateFeeService.php')) {
    require_once APP_PATH . '/services/LateFeeService.php';
}

// Cargar validación centralizada
require_once APP_PATH . '/validation/Validator.php';
require_once APP_PATH . '/validation/ValidationRules.php';

// Cargar controladores
require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/controllers/UserController.php';
require_once APP_PATH . '/controllers/ResidentController.php';
require_once APP_PATH . '/controllers/PaymentController.php';
require_once APP_PATH . '/controllers/SettingsController.php';
require_once APP_PATH . '/controllers/IncidentController.php';
require_once APP_PATH . '/controllers/CommonAreaController.php';
require_once APP_PATH . '/controllers/ReportController.php';
require_once APP_PATH . '/controllers/NotificationController.php';
require_once APP_PATH . '/controllers/PdfController.php';
require_once APP_PATH . '/controllers/ExcelController.php';
require_once APP_PATH . '/controllers/CompanyController.php';
require_once APP_PATH . '/controllers/MedioPagoController.php';

// Cargar controlador de mora si existe
if (file_exists(APP_PATH . '/controllers/LateFeeController.php')) {
    require_once APP_PATH . '/controllers/LateFeeController.php';
}

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Depuración - registrar la solicitud
error_log("Request: $request, Method: $method");

// Separar la ruta de los parámetros GET
$parsed_url = parse_url($request);
$request_path = $parsed_url['path'];

// Depuración - registrar el path procesado
error_log("Request path: $request_path");

// Limpiar la ruta (eliminar /condominio si está presente)
$request_path = str_replace('/condominio', '', $request_path);
if ($request_path === '') $request_path = '/';

// Depuración - registrar el path final
error_log("Final path: $request_path");

// Enrutamiento básico
switch ($request_path) {
    case '/':
    case '/login':
        $controller = new UserController();
        $controller->login();
        break;
        
    case '/logout':
        $controller = new UserController();
        $controller->logout();
        break;
        
    case '/dashboard':
        $controller = new UserController();
        $controller->dashboard();
        break;
        
    case '/profile':
        $controller = new UserController();
        $controller->profile();
        break;
        
    case '/users':
        $controller = new UserController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
        break;
        
    case '/users/create':
        $controller = new UserController();
        $controller->create();
        break;
        
    case (preg_match('/^\/users\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new UserController();
        $controller->edit($matches[1]);
        break;
        
    case (preg_match('/^\/users\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new UserController();
        $controller->delete($matches[1]);
        break;
        
    case '/residents':
        $controller = new ResidentController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
        break;
        
    case '/residents/create':
        $controller = new ResidentController();
        $controller->create();
        break;
        
    case (preg_match('/^\/residents\/show\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new ResidentController();
        $controller->show($matches[1]);
        break;
        
    case (preg_match('/^\/residents\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new ResidentController();
        $controller->edit($matches[1]);
        break;
        
    case (preg_match('/^\/residents\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new ResidentController();
        $controller->delete($matches[1]);
        break;
        
    case '/residents/getActiveResidents':
        if ($method !== 'GET') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método no permitido. Use GET']);
            break;
        }
        $controller = new ResidentController();
        $controller->getActiveResidents();
        break;
        
    case '/residents/myProfile':
        $controller = new ResidentController();
        $controller->myProfile();
        break;
        
    case '/payments':
        $controller = new PaymentController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
        break;
        
    case '/payments/create':
        $controller = new PaymentController();
        $controller->create();
        break;
        
    case '/payments/pending':
        $controller = new PaymentController();
        $controller->pending();
        break;
        
    case '/payments/stats':
        $controller = new PaymentController();
        $controller->stats();
        break;
        
    case (preg_match('/^\/payments\/show\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->show($matches[1]);
        break;
        
    case (preg_match('/^\/payments\/receipt\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->receipt($matches[1]);
        break;
        
    case (preg_match('/^\/payments\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->edit($matches[1]);
        break;
        
    case (preg_match('/^\/payments\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->delete($matches[1]);
        break;
        
    case '/payments/report':
        $controller = new PaymentController();
        $controller->report();
        break;
        
    case '/incidents':
        $controller = new IncidentController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
        break;
        
    case '/incidents/create':
        $controller = new IncidentController();
        $controller->create();
        break;
        
    case '/incidents/stats':
        $controller = new IncidentController();
        $controller->stats();
        break;
        
    case (preg_match('/^\/incidents\/show\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new IncidentController();
        $controller->show($matches[1]);
        break;
        
    case (preg_match('/^\/incidents\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new IncidentController();
        $controller->edit($matches[1]);
        break;
        
    case (preg_match('/^\/incidents\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new IncidentController();
        $controller->delete($matches[1]);
        break;
        
    case (preg_match('/^\/incidents\/changeStatus\/(\d+)$/', $request_path, $matches) ? true : false):
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido. Use POST']);
            break;
        }
        $controller = new IncidentController();
        $controller->changeStatus($matches[1]);
        break;
        
    case '/incidents/report':
        $controller = new IncidentController();
        $controller->report();
        break;
        
    case '/common-areas':
        $controller = new CommonAreaController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
        break;
        
    case '/common-areas/create':
        $controller = new CommonAreaController();
        $controller->create();
        break;
        
    case (preg_match('/^\/common-areas\/show\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new CommonAreaController();
        $controller->show($matches[1]);
        break;
        
    case (preg_match('/^\/common-areas\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new CommonAreaController();
        $controller->edit($matches[1]);
        break;
        
    case (preg_match('/^\/common-areas\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new CommonAreaController();
        $controller->delete($matches[1]);
        break;
        
    case '/reservations':
        $controller = new CommonAreaController();
        $controller->reservations();
        break;
        
    case '/reservations/create':
        $controller = new CommonAreaController();
        $controller->createReservation();
        break;
        
    case (preg_match('/^\/reservations\/show\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new CommonAreaController();
        $controller->showReservation($matches[1]);
        break;
        
    case (preg_match('/^\/reservations\/cancel\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new CommonAreaController();
        $controller->cancelReservation($matches[1]);
        break;
        
    case '/reports':
        $controller = new ReportController();
        $controller->index();
        break;
        
    case '/reports/income':
        $controller = new ReportController();
        $controller->income();
        break;
        
    case '/reports/pendingPayments':
        $controller = new ReportController();
        $controller->pendingPayments();
        break;
        
    case '/reports/incidents':
        $controller = new ReportController();
        $controller->incidents();
        break;
        
    case '/reports/residents':
        $controller = new ReportController();
        $controller->residents();
        break;
        
    case '/reports/dashboard':
        $controller = new ReportController();
        $controller->dashboard();
        break;
        
    case '/reports/financialSummary':
        $controller = new ReportController();
        $controller->financialSummary();
        break;
        
    case '/reports/custom':
        $controller = new ReportController();
        if ($method === 'GET') {
            $controller->custom();
        } elseif ($method === 'POST') {
            $controller->custom();
        }
        break;
        
    case '/reports/chartData':
        $controller = new ReportController();
        $controller->chartData();
        break;
        
    case '/notifications':
        $controller = new NotificationController();
        $controller->index();
        break;
        
    case (preg_match('/^\/notifications\/markAsRead\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new NotificationController();
        $controller->markAsRead($matches[1]);
        break;
        
    case '/notifications/getUnreadCount':
        $controller = new NotificationController();
        $controller->getUnreadCount();
        break;
        
    case '/notifications/admin':
        $controller = new NotificationController();
        $controller->admin();
        break;
        
    case '/notifications/stats':
        $controller = new NotificationController();
        $controller->stats();
        break;
        
    case '/pdf/income':
        $controller = new PdfController();
        $controller->income();
        break;
        
    case '/pdf/pending-payments':
        $controller = new PdfController();
        $controller->pendingPayments();
        break;
        
    case '/pdf/incidents':
        $controller = new PdfController();
        $controller->incidents();
        break;
        
    case '/pdf/residents':
        $controller = new PdfController();
        $controller->residents();
        break;
        
    case '/excel/income':
        $controller = new ExcelController();
        $controller->income();
        break;
        
    case '/excel/pending-payments':
        $controller = new ExcelController();
        $controller->pendingPayments();
        break;
        
    case '/excel/incidents':
        $controller = new ExcelController();
        $controller->incidents();
        break;
        
    case '/excel/residents':
        $controller = new ExcelController();
        $controller->residents();
        break;
        
    case (preg_match('/^\/pdf\/payment-receipt\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PdfController();
        $controller->paymentReceipt($matches[1]);
        break;
        
    case (preg_match('/^\/pdf\/incident-receipt\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PdfController();
        $controller->incidentReceipt($matches[1]);
        break;
        
    // Late Fee Rules Management (Admin only)
    case '/late-fee-rules':
        if (class_exists('LateFeeController')) {
            $controller = new LateFeeController();
            $controller->index();
        } else {
            http_response_code(404);
            include APP_PATH . '/views/404.php';
        }
        break;
        
    case '/late-fee-rules/create':
        if (class_exists('LateFeeController')) {
            $controller = new LateFeeController();
            $controller->create();
        } else {
            http_response_code(404);
            include APP_PATH . '/views/404.php';
        }
        break;
        
    case (preg_match('/^\/late-fee-rules\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
        if (class_exists('LateFeeController')) {
            $controller = new LateFeeController();
            $controller->edit($matches[1]);
        } else {
            http_response_code(404);
            include APP_PATH . '/views/404.php';
        }
        break;
        
    case (preg_match('/^\/late-fee-rules\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
        if (class_exists('LateFeeController')) {
            $controller = new LateFeeController();
            $controller->delete($matches[1]);
        } else {
            http_response_code(404);
            include APP_PATH . '/views/404.php';
        }
        break;
        
    case (preg_match('/^\/late-fee-rules\/toggle\/(\d+)$/', $request_path, $matches) ? true : false):
        if (class_exists('LateFeeController')) {
            $controller = new LateFeeController();
            $controller->toggle($matches[1]);
        } else {
            http_response_code(404);
            include APP_PATH . '/views/404.php';
        }
        break;
        
    case '/late-fee-rules/simulate':
        if (class_exists('LateFeeController')) {
            $controller = new LateFeeController();
            $controller->simulate();
        } else {
            http_response_code(404);
            include APP_PATH . '/views/404.php';
        }
        break;
        
    case (preg_match('/^\/payments\/(\d+)\/adjust-late-fee$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->adjustLateFee($matches[1]);
        break;
        
    // Pagos en Línea (Pago Móvil BCV + Transferencia Bancaria)
    case (preg_match('/^\/payments\/pay\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->pay($matches[1]);
        break;
        
    case (preg_match('/^\/payments\/declare\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->declarePayment($matches[1]);
        break;
        
    case '/payments/declarations':
        $controller = new PaymentController();
        $controller->declarations();
        break;
        
    case (preg_match('/^\/payments\/declarations\/confirm\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->confirmDeclaration($matches[1]);
        break;
        
    case (preg_match('/^\/payments\/declarations\/reject\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->rejectDeclaration($matches[1]);
        break;
        
    // Cuentas Bancarias (Admin only)
    case '/bank-accounts':
        $controller = new PaymentController();
        $controller->bankAccounts();
        break;
        
    case '/bank-accounts/create':
        $controller = new PaymentController();
        $controller->bankAccountCreate();
        break;
        
    case (preg_match('/^\/bank-accounts\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->bankAccountEdit($matches[1]);
        break;
        
    case (preg_match('/^\/bank-accounts\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new PaymentController();
        $controller->bankAccountDelete($matches[1]);
        break;
        
    // Medios de Pago (Admin only)
    case '/medios-pago':
        $controller = new MedioPagoController();
        $controller->index();
        break;

    case '/medios-pago/create':
        $controller = new MedioPagoController();
        $controller->create();
        break;

    case (preg_match('/^\/medios-pago\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new MedioPagoController();
        $controller->edit($matches[1]);
        break;

    case (preg_match('/^\/medios-pago\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
        $controller = new MedioPagoController();
        $controller->delete($matches[1]);
        break;

    // Configuración General (Admin only)
    case '/settings':
        $controller = new SettingsController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->update();
        }
        break;
        
    case '/late-fees/report':
        if (class_exists('LateFeeController')) {
            $controller = new LateFeeController();
            $controller->report();
        } else {
            http_response_code(404);
            include APP_PATH . '/views/404.php';
        }
        break;
        
    case '/late-fees/stats':
        if (class_exists('LateFeeController')) {
            $controller = new LateFeeController();
            $controller->stats();
        } else {
            http_response_code(404);
            include APP_PATH . '/views/404.php';
        }
        break;
        
    // Datos del Condominio (Empresa) — Admin only
    case '/empresa':
        $controller = new CompanyController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->update();
        }
        break;
        
    default:
        // Página 404
        http_response_code(404);
        include APP_PATH . '/views/404.php';
        break;
}
?>
