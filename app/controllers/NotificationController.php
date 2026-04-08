<?php
/**
 * Controlador de Notificaciones
 * 
 * Gestiona las interfaces de usuario para visualización y gestión de notificaciones.
 * Proporciona funcionalidades tanto para residentes como para administradores.
 * 
 * Funcionalidades:
 * - Listar notificaciones del usuario actual
 * - Marcar notificaciones como leídas
 * - Obtener contador de notificaciones no leídas (AJAX)
 * - Vista de administración con filtros (solo admin)
 * - Estadísticas de notificaciones (solo admin)
 * 
 * @package App\Controllers
 * @author Sistema de Gestión de Condominio
 * @version 1.0.0
 */

class NotificationController extends Controller {
    private $notification;
    private $resident;
    
    /**
     * Constructor
     * 
     * Inicializa el controlador y crea instancias de los modelos necesarios.
     */
    public function __construct() {
        parent::__construct();
        $this->notification = new Notification($this->db);
        $this->resident = new Resident($this->db);
    }
    
    /**
     * Listar notificaciones del usuario actual
     * 
     * Muestra todas las notificaciones del usuario autenticado ordenadas por fecha.
     * Requiere autenticación.
     * 
     * @return void
     */
    public function index() {
        $this->requireAuth();
        
        $current_user = $this->getCurrentUser();
        
        // Obtener notificaciones del usuario
        $notifications = $this->notification->readByUser($current_user['id']);
        $notifications_list = $notifications->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('notifications/index', [
            'notifications' => $notifications_list,
            'is_admin' => isAdmin()
        ]);
    }
    
    /**
     * Marcar notificación como leída
     * 
     * Actualiza el estado de una notificación a leída.
     * Solo el propietario de la notificación o un administrador pueden marcarla.
     * Requiere autenticación.
     * 
     * @param int $id ID de la notificación
     * @return void
     */
    public function markAsRead($id) {
        $this->requireAuth();
        
        $current_user = $this->getCurrentUser();
        
        // Obtener la notificación
        $query = "SELECT * FROM notificaciones WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $notification_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$notification_data) {
            flash('Notificación no encontrada', 'error');
            redirect('/notifications');
            return;
        }
        
        // Verificar permisos: solo el propietario o admin pueden marcar como leída
        if ($notification_data['usuario_id'] != $current_user['id'] && !isAdmin()) {
            flash('No tiene permisos para acceder a esta notificación', 'error');
            redirect('/notifications');
            return;
        }
        
        // Marcar como leída
        $this->notification->id = $id;
        if ($this->notification->markAsRead()) {
            flash('Notificación marcada como leída', 'success');
        } else {
            flash('Error al marcar la notificación', 'error');
        }
        
        redirect('/notifications');
    }
    
    /**
     * Obtener contador de notificaciones no leídas (AJAX)
     * 
     * Retorna el número de notificaciones no leídas del usuario actual en formato JSON.
     * Utilizado para actualizar el contador en tiempo real en la interfaz.
     * Requiere autenticación.
     * 
     * @return void Envía respuesta JSON y termina la ejecución
     */
    public function getUnreadCount() {
        $this->requireAuth();
        
        $current_user = $this->getCurrentUser();
        
        // Obtener contador
        $count = $this->notification->countUnreadByUser($current_user['id']);
        
        // Enviar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        exit;
    }
    
    /**
     * Vista de administración de notificaciones (solo admin)
     * 
     * Muestra todas las notificaciones del sistema con opciones de filtrado.
     * Permite filtrar por usuario, tipo y estado de lectura.
     * Requiere permisos de administrador.
     * 
     * @return void
     */
    public function admin() {
        $this->requireAdmin();
        
        // Obtener filtros
        $filters = [];
        if (isset($_GET['usuario_id']) && !empty($_GET['usuario_id'])) {
            $filters['usuario_id'] = sanitize($_GET['usuario_id']);
        }
        if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
            $filters['tipo'] = sanitize($_GET['tipo']);
        }
        if (isset($_GET['leida']) && $_GET['leida'] !== '') {
            $filters['leida'] = $_GET['leida'] === '1' ? true : false;
        }
        
        // Obtener notificaciones con filtros
        $notifications = $this->notification->readAll($filters);
        $notifications_list = $notifications->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener lista de residentes para el filtro
        $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener estadísticas
        $stats = $this->notification->getStats();
        
        $this->view('admin/notifications/index', [
            'notifications' => $notifications_list,
            'residents' => $residents,
            'filters' => $filters,
            'stats' => $stats
        ]);
    }
    
    /**
     * Obtener estadísticas de notificaciones (solo admin)
     * 
     * Retorna estadísticas generales de notificaciones en formato JSON.
     * Requiere permisos de administrador.
     * 
     * @return void Envía respuesta JSON y termina la ejecución
     */
    public function stats() {
        $this->requireAdmin();
        
        // Obtener estadísticas
        $stats = $this->notification->getStats();
        
        // Enviar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        exit;
    }
}
?>
