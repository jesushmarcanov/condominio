<?php
// Front Controller - Punto de entrada principal de la aplicación

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Cargar configuración
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/config.php';

// Iniciar sesión
session_start();

// Cargar clases básicas
require_once APP_PATH . '/models/Database.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Resident.php';
require_once APP_PATH . '/models/Payment.php';
require_once APP_PATH . '/models/Incident.php';
require_once APP_PATH . '/models/Report.php';
require_once APP_PATH . '/models/Notification.php';

// Cargar servicios
require_once APP_PATH . '/services/NotificationService.php';
require_once APP_PATH . '/services/PdfService.php';
require_once APP_PATH . '/services/ExcelService.php';

// Cargar controladores
require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/controllers/UserController.php';
require_once APP_PATH . '/controllers/ResidentController.php';
require_once APP_PATH . '/controllers/PaymentController.php';
require_once APP_PATH . '/controllers/IncidentController.php';
require_once APP_PATH . '/controllers/ReportController.php';
require_once APP_PATH . '/controllers/NotificationController.php';
require_once APP_PATH . '/controllers/PdfController.php';
require_once APP_PATH . '/controllers/ExcelController.php';

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
        
    case '/incidents/report':
        $controller = new IncidentController();
        $controller->report();
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
        
    default:
        // Página 404
        http_response_code(404);
        include APP_PATH . '/views/404.php';
        break;
}
?>
