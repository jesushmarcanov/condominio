<?php
// Controlador de Incidencias

class IncidentController extends Controller {
    private $incident;
    private $resident;
    private $notification;
    private $user;
    private $emailService;
    
    public function __construct() {
        parent::__construct();
        $this->incident = new Incident($this->db);
        $this->resident = new Resident($this->db);
        $this->notification = new Notification($this->db);
        $this->user = new User($this->db);
        $this->emailService = new EmailService($this->db);
    }
    
    // Listar incidencias
    public function index() {
        $this->requireAuth();
        
        $current_user = $this->getCurrentUser();
        $resident_id = null;
        
        if(isResident()) {
            // Si es residente, solo ver sus incidencias
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if($resident_data) {
                $resident_id = $resident_data['id'];
            }
        }
        
        // Filtros
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
        $priority = isset($_GET['priority']) ? sanitize($_GET['priority']) : '';
        
        if($resident_id) {
            $incidents = $this->incident->readByResident($resident_id);
        } elseif(!empty($status)) {
            $incidents = $this->incident->getByStatus($status);
        } elseif(!empty($category)) {
            $incidents = $this->incident->getByCategory($category);
        } elseif(!empty($priority)) {
            $incidents = $this->incident->getByPriority($priority);
        } else {
            $incidents = $this->incident->readAll();
        }
        
        $incidents_list = $incidents->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('incidents/index', [
            'incidents' => $incidents_list,
            'status' => $status,
            'category' => $category,
            'priority' => $priority,
            'is_admin' => isAdmin()
        ]);
    }
    
    // Crear incidencia
    public function create() {
        $this->requireAuth();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeIncident();
        } else {
            $resident_id = null;
            
            if(isResident()) {
                $current_user = $this->getCurrentUser();
                $resident_data = $this->resident->getByUserId($current_user['id']);
                if($resident_data) {
                    $resident_id = $resident_data['id'];
                }
            } else {
                // Si es admin, mostrar lista de residentes
                $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
                $this->view('admin/incidents/create', [
                    'residents' => $residents
                ]);
                return;
            }
            
            $this->view('incidents/create', [
                'resident_id' => $resident_id
            ]);
        }
    }
    
    // Guardar nueva incidencia
    private function storeIncident() {
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'residente_id' => ['required' => true, 'numeric' => true],
            'titulo' => ['required' => true, 'max' => 100],
            'descripcion' => ['required' => true],
            'categoria' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_CATEGORIES)],
            'prioridad' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_PRIORITIES)]
        ]);
        
        if(!empty($errors)) {
            if(isAdmin()) {
                $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
                $this->view('admin/incidents/create', [
                    'errors' => $errors,
                    'data' => $data,
                    'residents' => $residents
                ]);
            } else {
                $this->view('incidents/create', [
                    'errors' => $errors,
                    'data' => $data
                ]);
            }
            return;
        }
        
        // Crear incidencia
        $this->incident->residente_id = $data['residente_id'];
        $this->incident->titulo = $data['titulo'];
        $this->incident->descripcion = $data['descripcion'];
        $this->incident->categoria = $data['categoria'];
        $this->incident->prioridad = $data['prioridad'];
        $this->incident->estado = 'pendiente';
        
        if($this->incident->create()) {
            // Registrar evento en la base de datos
            IncidentEvent::logCreated(
                $this->db,
                $this->incident->id,
                $_SESSION['user_id'],
                "Incidencia creada: {$data['titulo']} - Prioridad: {$data['prioridad']}"
            );
            
            // Crear notificación para el residente
            $this->createIncidentNotification(
                $this->incident->id,
                $data['residente_id'],
                'created',
                $data['titulo']
            );
            
            // Notificar a todos los administradores con email
            $this->resident->id = $data['residente_id'];
            $resident_data = $this->resident->readOne();
            $this->incident->id = $this->incident->id;
            $incident_data = $this->incident->readOne();
            $this->notifyAdminsWithEmail($incident_data, $resident_data);
            
            flash('Incidencia registrada correctamente', 'success');
            redirect('/incidents');
        } else {
            if(isAdmin()) {
                $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
                $this->view('admin/incidents/create', [
                    'error' => 'Error al registrar la incidencia',
                    'data' => $data,
                    'residents' => $residents
                ]);
            } else {
                $this->view('incidents/create', [
                    'error' => 'Error al registrar la incidencia',
                    'data' => $data
                ]);
            }
        }
    }
    
    // Ver detalles de la incidencia
    public function show($id) {
        $this->requireAuth();
        
        $this->incident->id = $id;
        $incident_data = $this->incident->readOne();
        
        if(!$incident_data) {
            flash('Incidencia no encontrada', 'error');
            redirect('/incidents');
            return;
        }
        
        // Verificar permisos
        if(isResident()) {
            $current_user = $this->getCurrentUser();
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if(!$resident_data || $resident_data['id'] != $incident_data['residente_id']) {
                flash('No tiene permisos para ver esta incidencia', 'error');
                redirect('/incidents');
                return;
            }
        }
        
        $this->view('incidents/show', [
            'incident' => $incident_data,
            'is_admin' => isAdmin()
        ]);
    }
    
    // Editar incidencia (solo admin)
    public function edit($id) {
        $this->requireAdmin();
        
        $this->incident->id = $id;
        $incident_data = $this->incident->readOne();
        
        if(!$incident_data) {
            flash('Incidencia no encontrada', 'error');
            redirect('/incidents');
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateIncident($id);
        } else {
            $this->view('admin/incidents/edit', [
                'incident' => $incident_data
            ]);
        }
    }
    
    // Actualizar incidencia
    private function updateIncident($id) {
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'titulo' => ['required' => true, 'max' => 100],
            'descripcion' => ['required' => true],
            'categoria' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_CATEGORIES)],
            'prioridad' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_PRIORITIES)],
            'estado' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_STATUSES)]
        ]);
        
        if(!empty($errors)) {
            $this->view('admin/incidents/edit', [
                'errors' => $errors,
                'incident' => $data
            ]);
            return;
        }
        
        // Actualizar incidencia
        $this->incident->id = $id;
        
        // Obtener datos anteriores para comparar
        $old_incident = $this->incident->readOne();
        $old_status = $old_incident['estado'];
        
        $this->incident->titulo = $data['titulo'];
        $this->incident->descripcion = $data['descripcion'];
        $this->incident->categoria = $data['categoria'];
        $this->incident->prioridad = $data['prioridad'];
        $this->incident->estado = $data['estado'];
        $this->incident->administrador_id = $_SESSION['user_id'];
        $this->incident->notas_admin = $data['notas_admin'];
        
        if($this->incident->update()) {
            // Registrar evento de cambio de estado si cambió
            if($old_status !== $data['estado']) {
                IncidentEvent::logStatusChanged(
                    $this->db,
                    $id,
                    $_SESSION['user_id'],
                    $old_status,
                    $data['estado'],
                    "Estado cambiado de '{$old_status}' a '{$data['estado']}'"
                );
                
                // Notificar cambio de estado
                $this->createIncidentNotification(
                    $id,
                    $old_incident['residente_id'],
                    'status_changed',
                    $data['titulo'],
                    $old_status,
                    $data['estado']
                );
            }
            
            flash('Incidencia actualizada correctamente', 'success');
            redirect('/incidents');
        } else {
            $this->view('admin/incidents/edit', [
                'error' => 'Error al actualizar la incidencia',
                'incident' => $data
            ]);
        }
    }
    
    // Eliminar incidencia (solo admin)
    public function delete($id) {
        $this->requireAdmin();
        
        $this->incident->id = $id;
        if($this->incident->delete()) {
            flash('Incidencia eliminada correctamente', 'success');
        } else {
            flash('Error al eliminar la incidencia', 'error');
        }
        
        redirect('/incidents');
    }
    
    // Cambiar estado de incidencia (solo admin)
    public function changeStatus($id) {
        $this->requireAdmin();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_status = sanitize($_POST['estado']);
            $notas = sanitize($_POST['notas_admin']);
            
            $this->incident->id = $id;
            $incident_data = $this->incident->readOne();
            
            if(!$incident_data) {
                $this->jsonResponse(['error' => 'Incidencia no encontrada'], 404);
                return;
            }
            
            // Actualizar estado
            $this->incident->titulo = $incident_data['titulo'];
            $this->incident->descripcion = $incident_data['descripcion'];
            $this->incident->categoria = $incident_data['categoria'];
            $this->incident->prioridad = $incident_data['prioridad'];
            $this->incident->estado = $new_status;
            $this->incident->administrador_id = $_SESSION['user_id'];
            $this->incident->notas_admin = $notas;
            
            if($this->incident->update()) {
                $this->jsonResponse(['success' => 'Estado actualizado correctamente']);
            } else {
                $this->jsonResponse(['error' => 'Error al actualizar el estado'], 500);
            }
        }
    }
    
    // Generar reporte de incidencias
    public function report() {
        $this->requireAdmin();
        
        $start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        $export = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        
        $report = new Report($this->db);
        $data = $report->generateIncidentReport($start_date, $end_date, $status);
        
        if($export === 'csv') {
            $filename = 'reporte_incidencias_' . date('Y-m-d') . '.csv';
            $report->exportToCSV($data, $filename);
        } else {
            $this->view('admin/incidents/report', [
                'incidents' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status
            ]);
        }
    }
    
    // Estadísticas de incidencias
    public function stats() {
        $this->requireAdmin();
        
        $stats = $this->incident->getStats();
        $incidents_by_category = $this->incident->getIncidentsByCategory();
        $monthly_incidents = $this->incident->getMonthlyIncidents();
        
        $this->view('admin/incidents/stats', [
            'stats' => $stats,
            'incidents_by_category' => $incidents_by_category,
            'monthly_incidents' => $monthly_incidents
        ]);
    }
    
    /**
     * Crear notificación para incidencia
     * 
     * @param int $incident_id ID de la incidencia
     * @param int $residente_id ID del residente
     * @param string $action Acción realizada (created, status_changed, assigned)
     * @param string $titulo Título de la incidencia
     * @param string $old_status Estado anterior (opcional)
     * @param string $new_status Estado nuevo (opcional)
     */
    private function createIncidentNotification($incident_id, $residente_id, $action, $titulo, $old_status = null, $new_status = null) {
        try {
            // Obtener usuario_id del residente
            $this->resident->id = $residente_id;
            $resident_data = $this->resident->readOne();
            
            if (!$resident_data || !isset($resident_data['usuario_id'])) {
                error_log("[IncidentController] No se pudo obtener usuario_id para residente ID: $residente_id");
                return false;
            }
            
            $usuario_id = $resident_data['usuario_id'];
            
            // Construir título y mensaje según la acción
            switch ($action) {
                case 'created':
                    $notif_titulo = "Nueva Incidencia Registrada";
                    $notif_mensaje = "Su incidencia '{$titulo}' ha sido registrada exitosamente. Le notificaremos sobre cualquier actualización.";
                    $tipo = 'info';
                    break;
                    
                case 'status_changed':
                    $notif_titulo = "Actualización de Incidencia";
                    $notif_mensaje = "El estado de su incidencia '{$titulo}' ha cambiado de '{$this->translateStatus($old_status)}' a '{$this->translateStatus($new_status)}'.";
                    $tipo = $new_status === 'resuelta' ? 'success' : 'info';
                    break;
                    
                case 'assigned':
                    $notif_titulo = "Incidencia Asignada";
                    $notif_mensaje = "Su incidencia '{$titulo}' ha sido asignada a un administrador para su atención.";
                    $tipo = 'info';
                    break;
                    
                default:
                    return false;
            }
            
            // Verificar duplicados
            $duplicate = $this->notification->findDuplicate($usuario_id, $notif_titulo, false);
            if ($duplicate) {
                error_log("[IncidentController] Notificación duplicada omitida para usuario ID: $usuario_id");
                return false;
            }
            
            // Crear notificación
            $this->notification->usuario_id = $usuario_id;
            $this->notification->titulo = $notif_titulo;
            $this->notification->mensaje = $notif_mensaje;
            $this->notification->tipo = $tipo;
            $this->notification->leida = false;
            
            if ($this->notification->create()) {
                error_log("[IncidentController] Notificación creada para incidencia ID: $incident_id, Usuario ID: $usuario_id");
                
                // Send email notification
                $this->incident->id = $incident_id;
                $incident_data = $this->incident->readOne();
                if ($incident_data) {
                    $email_type = $action === 'status_changed' && $new_status === 'resuelta' ? 'resolved' : $action;
                    $this->sendIncidentEmail($incident_id, $residente_id, $email_type, $incident_data);
                }
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("[IncidentController] Error al crear notificación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Notificar a todos los administradores
     * 
     * @param string $titulo Título de la notificación
     * @param string $mensaje Mensaje de la notificación
     * @param string $tipo Tipo de notificación (info, warning, success, error)
     */
    private function notifyAdmins($titulo, $mensaje, $tipo = 'info') {
        try {
            // Obtener todos los usuarios administradores
            $query = "SELECT id FROM usuarios WHERE rol = 'admin'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($admins as $admin) {
                // Verificar duplicados
                $duplicate = $this->notification->findDuplicate($admin['id'], $titulo, false);
                if ($duplicate) {
                    continue;
                }
                
                // Crear notificación
                $notification = new Notification($this->db);
                $notification->usuario_id = $admin['id'];
                $notification->titulo = $titulo;
                $notification->mensaje = $mensaje;
                $notification->tipo = $tipo;
                $notification->leida = false;
                
                $notification->create();
            }
            
            error_log("[IncidentController] Notificaciones enviadas a " . count($admins) . " administradores");
            return true;
            
        } catch (Exception $e) {
            error_log("[IncidentController] Error al notificar administradores: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Notificar a administradores con email sobre nueva incidencia
     * 
     * @param array $incident_data Incident data
     * @param array $resident_data Resident data
     * @return void
     */
    private function notifyAdminsWithEmail($incident_data, $resident_data) {
        // Create database notifications
        $titulo = 'Nueva Incidencia Reportada';
        $mensaje = "Se ha reportado una nueva incidencia: {$incident_data['titulo']} (Prioridad: {$incident_data['prioridad']})";
        $this->notifyAdmins($titulo, $mensaje, 'info');
        
        // Send email notification
        $urgent = ($incident_data['prioridad'] === 'alta');
        $this->sendAdminNotification($titulo, $mensaje, $resident_data, $incident_data, $urgent);
    }
    
    /**
     * Traducir estado de incidencia a español
     * 
     * @param string $status Estado en inglés
     * @return string Estado en español
     */
    private function translateStatus($status) {
        $translations = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'resuelta' => 'Resuelta',
            'cancelada' => 'Cancelada'
        ];
        
        return isset($translations[$status]) ? $translations[$status] : $status;
    }
    
    /**
     * Send incident email notification
     * 
     * @param int $incident_id Incident ID
     * @param int $residente_id Resident ID
     * @param string $type Email type (created, status_changed, assigned, resolved)
     * @param array $incident_data Incident data
     * @return void
     */
    private function sendIncidentEmail($incident_id, $residente_id, $type, $incident_data) {
        if (!$this->emailService->isEnabled()) {
            error_log("[IncidentController] Email service disabled, skipping email");
            return;
        }
        
        try {
            // Get resident data
            $this->resident->id = $residente_id;
            $resident_data = $this->resident->readOne();
            
            if (!$resident_data || empty($resident_data['email'])) {
                error_log("[IncidentController] No email address for resident ID: $residente_id");
                return;
            }
            
            // Prepare template variables
            $variables = [
                'resident_name' => $resident_data['nombre'],
                'apartment' => $resident_data['apartamento'],
                'tower' => $resident_data['torre'],
                'incident_title' => $incident_data['titulo'],
                'incident_description' => $incident_data['descripcion'],
                'incident_category' => ucfirst($incident_data['categoria']),
                'incident_priority' => ucfirst($incident_data['prioridad']),
                'incident_status' => $this->translateStatus($incident_data['estado']),
                'incident_id' => $incident_id,
                'incident_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/incidents/show/' . $incident_id,
                'admin_notes' => $incident_data['notas_admin'] ?? '',
                'type' => $type
            ];
            
            // Load and render template
            $html_body = $this->emailService->loadTemplate('incident_notification', $variables);
            
            // Determine subject based on type
            $subjects = [
                'created' => 'Nueva Incidencia Registrada - ' . $incident_data['titulo'],
                'status_changed' => 'Actualización de Incidencia - ' . $incident_data['titulo'],
                'assigned' => 'Incidencia Asignada - ' . $incident_data['titulo'],
                'resolved' => 'Incidencia Resuelta - ' . $incident_data['titulo']
            ];
            
            $subject = $subjects[$type] ?? 'Notificación de Incidencia';
            
            // Send email (non-blocking - errors are logged but don't stop execution)
            $result = $this->emailService->sendHtmlEmail($resident_data['email'], $subject, $html_body);
            
            if ($result['success']) {
                error_log("[IncidentController] Email sent to: " . $resident_data['email']);
            } else {
                error_log("[IncidentController] Email failed: " . $result['error']);
            }
            
        } catch (Exception $e) {
            error_log("[IncidentController] Email exception: " . $e->getMessage());
            // Don't throw - email failures should not break incident creation
        }
    }
    
    /**
     * Send administrator notification email
     * 
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $resident_data Resident data
     * @param array $incident_data Incident data (optional)
     * @param bool $urgent Whether this is urgent
     * @return void
     */
    private function sendAdminNotification($title, $message, $resident_data, $incident_data = null, $urgent = false) {
        if (!$this->emailService->isEnabled()) {
            error_log("[IncidentController] Email service disabled, skipping admin email");
            return;
        }
        
        try {
            // Get all administrator emails
            $query = "SELECT u.email FROM usuarios u WHERE u.rol = 'admin' AND u.email IS NOT NULL AND u.email != ''";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($admins)) {
                error_log("[IncidentController] No administrator emails found");
                return;
            }
            
            // Prepare template variables
            $variables = [
                'title' => $title,
                'message' => $message,
                'resident_name' => $resident_data['nombre'],
                'apartment' => $resident_data['apartamento'],
                'tower' => $resident_data['torre'],
                'resident_email' => $resident_data['email'] ?? 'No disponible',
                'urgent' => $urgent
            ];
            
            // Add incident details if provided
            if ($incident_data) {
                $variables['details'] = [
                    'Título' => $incident_data['titulo'],
                    'Categoría' => ucfirst($incident_data['categoria']),
                    'Prioridad' => ucfirst($incident_data['prioridad']),
                    'Estado' => $this->translateStatus($incident_data['estado'])
                ];
                $variables['action_url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/incidents/show/' . $incident_data['id'];
            }
            
            // Load and render template
            $html_body = $this->emailService->loadTemplate('admin_notification', $variables);
            
            // Send email to all administrators
            $result = $this->emailService->sendHtmlEmail($admins, $title, $html_body);
            
            if ($result['success']) {
                error_log("[IncidentController] Admin notification sent to " . count($admins) . " administrators");
            } else {
                error_log("[IncidentController] Admin notification failed: " . $result['error']);
            }
            
        } catch (Exception $e) {
            error_log("[IncidentController] Admin notification exception: " . $e->getMessage());
        }
    }
}
?>
