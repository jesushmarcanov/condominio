<?php
// Controlador de Incidencias

class IncidentController extends Controller {
    private $incident;
    private $resident;
    
    public function __construct() {
        parent::__construct();
        $this->incident = new Incident($this->db);
        $this->resident = new Resident($this->db);
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
            'categoria' => ['required' => true, 'in' => ['agua', 'electricidad', 'gas', 'estructura', 'limpieza', 'seguridad', 'otro']],
            'prioridad' => ['required' => true, 'in' => ['baja', 'media', 'alta']]
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
            'categoria' => ['required' => true, 'in' => ['agua', 'electricidad', 'gas', 'estructura', 'limpieza', 'seguridad', 'otro']],
            'prioridad' => ['required' => true, 'in' => ['baja', 'media', 'alta']],
            'estado' => ['required' => true, 'in' => ['pendiente', 'en_proceso', 'resuelta', 'cancelada']]
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
        $this->incident->titulo = $data['titulo'];
        $this->incident->descripcion = $data['descripcion'];
        $this->incident->categoria = $data['categoria'];
        $this->incident->prioridad = $data['prioridad'];
        $this->incident->estado = $data['estado'];
        $this->incident->administrador_id = $_SESSION['user_id'];
        $this->incident->notas_admin = $data['notas_admin'];
        
        if($this->incident->update()) {
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
}
?>
