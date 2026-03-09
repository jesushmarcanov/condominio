<?php
// Controlador de Residentes

class ResidentController extends Controller {
    private $resident;
    private $user;
    
    public function __construct() {
        parent::__construct();
        $this->resident = new Resident($this->db);
        $this->user = new User($this->db);
    }
    
    // Listar residentes (solo admin)
    public function index() {
        $this->requireAdmin();
        
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
        
        if(!empty($search)) {
            $residents = $this->resident->search($search);
        } else {
            $residents = $this->resident->readAll();
        }
        
        $this->view('admin/residents/index', [
            'residents' => $residents->fetchAll(PDO::FETCH_ASSOC),
            'search' => $search
        ]);
    }
    
    // Crear residente (solo admin)
    public function create() {
        $this->requireAdmin();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeResident();
        } else {
            // Obtener usuarios residentes sin residente asociado
            $query = "SELECT u.id, u.nombre, u.email 
                      FROM usuarios u 
                      LEFT JOIN residentes r ON u.id = r.usuario_id 
                      WHERE u.rol = 'resident' AND r.id IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $available_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->view('admin/residents/create', [
                'available_users' => $available_users
            ]);
        }
    }
    
    // Guardar nuevo residente
    private function storeResident() {
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'nombre' => ['required' => true, 'max' => 100],
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'min' => 6],
            'apartamento' => ['required' => true, 'max' => 10],
            'piso' => ['required' => true, 'numeric' => true],
            'torre' => ['max' => 50],
            'fecha_ingreso' => ['required' => true],
            'estado' => ['required' => true, 'in' => ['activo', 'inactivo']]
        ]);
        
        if(!empty($errors)) {
            $this->view('admin/residents/create', [
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }
        
        // Verificar si el email ya existe
        $this->user->email = $data['email'];
        if($this->user->emailExists()) {
            $this->view('admin/residents/create', [
                'error' => 'El email ya está registrado',
                'data' => $data
            ]);
            return;
        }
        
        // Verificar si el apartamento está disponible
        $this->resident->apartamento = $data['apartamento'];
        $this->resident->id = 0; // Para que no excluya el ID actual
        if(!$this->resident->isApartmentAvailable()) {
            $this->view('admin/residents/create', [
                'error' => 'El apartamento ya está ocupado',
                'data' => $data
            ]);
            return;
        }
        
        // Crear usuario primero
        $this->user->nombre = $data['nombre'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->rol = 'resident';
        $this->user->telefono = $data['telefono'] ?? '';
        
        if(!$this->user->create()) {
            $this->view('admin/residents/create', [
                'error' => 'Error al crear el usuario',
                'data' => $data
            ]);
            return;
        }
        
        // Obtener el ID del usuario creado
        $user_id = $this->db->lastInsertId();
        
        // Crear residente
        $this->resident->usuario_id = $user_id;
        $this->resident->apartamento = $data['apartamento'];
        $this->resident->piso = $data['piso'];
        $this->resident->torre = $data['torre'] ?? '';
        $this->resident->fecha_ingreso = $data['fecha_ingreso'];
        $this->resident->estado = $data['estado'];
        
        if($this->resident->create()) {
            flash('Residente creado correctamente', 'success');
            redirect('/residents');
        } else {
            // Si falla la creación del residente, eliminar el usuario creado
            $this->user->id = $user_id;
            $this->user->delete();
            
            $this->view('admin/residents/create', [
                'error' => 'Error al crear el residente',
                'data' => $data
            ]);
        }
    }
    
    // Ver detalles del residente
    public function show($id) {
        $this->requireAdmin();
        
        $this->resident->id = $id;
        $resident_data = $this->resident->readOne();
        
        if(!$resident_data) {
            flash('Residente no encontrado', 'error');
            redirect('/residents');
            return;
        }
        
        // Obtener pagos del residente
        $payment = new Payment($this->db);
        $payments = $payment->readByResident($id)->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener incidencias del residente
        $incident = new Incident($this->db);
        $incidents = $incident->readByResident($id)->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular estadísticas del residente
        $pagados = array_filter($payments, fn($p) => $p['estado'] === 'pagado');
        $pendientes = array_filter($payments, fn($p) => $p['estado'] === 'pendiente');
        
        // Crear actividad reciente combinando pagos e incidencias
        $recent_activity = [];
        
        // Agregar pagos recientes
        foreach(array_slice($payments, 0, 5) as $payment) {
            $recent_activity[] = [
                'type' => 'payment',
                'title' => 'Pago Registrado',
                'description' => "Pago de {$payment['concepto']} por {$payment['monto']}",
                'date' => $payment['fecha_pago']
            ];
        }
        
        // Agregar incidencias recientes
        foreach(array_slice($incidents, 0, 5) as $incident) {
            $recent_activity[] = [
                'type' => 'incident',
                'title' => 'Incidencia Reportada',
                'description' => $incident['titulo'],
                'date' => $incident['fecha_reporte']
            ];
        }
        
        // Ordenar por fecha
        usort($recent_activity, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
        
        $stats = [
            'total_pagos' => count($payments),
            'pagos_pendientes' => count($pendientes),
            'total_incidencias' => count($incidents),
            'total_pagado' => array_sum(array_column($pagados, 'monto')),
            'total_pendiente' => array_sum(array_column($pendientes, 'monto')),
            'incidencias_resueltas' => count(array_filter($incidents, fn($i) => $i['estado'] === 'resuelta')),
            'incidencias_pendientes' => count(array_filter($incidents, fn($i) => in_array($i['estado'], ['abierto', 'en_proceso'])))
        ];
        
        $this->view('admin/residents/show', [
            'resident' => $resident_data,
            'payments' => $payments,
            'incidents' => $incidents,
            'stats' => $stats,
            'recent_activity' => $recent_activity
        ]);
    }
    
    // Editar residente (solo admin)
    public function edit($id) {
        $this->requireAdmin();
        
        $this->resident->id = $id;
        $resident_data = $this->resident->readOne();
        
        if(!$resident_data) {
            flash('Residente no encontrado', 'error');
            redirect('/residents');
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateResident($id);
        } else {
            $this->view('admin/residents/edit', [
                'resident' => $resident_data
            ]);
        }
    }
    
    // Actualizar residente
    private function updateResident($id) {
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'nombre' => ['required' => true, 'max' => 100],
            'email' => ['required' => true, 'email' => true, 'max' => 100],
            'telefono' => ['max' => 20],
            'apartamento' => ['required' => true, 'max' => 10],
            'piso' => ['required' => true, 'numeric' => true],
            'torre' => ['max' => 50],
            'fecha_ingreso' => ['required' => true],
            'estado' => ['required' => true, 'in' => ['activo', 'inactivo']]
        ]);
        
        if(!empty($errors)) {
            // Obtener datos actuales del residente para mostrar en el formulario
            $this->resident->id = $id;
            $current_data = $this->resident->readOne();
            
            $this->view('admin/residents/edit', [
                'errors' => $errors,
                'resident' => array_merge($current_data, $data)
            ]);
            return;
        }
        
        // Verificar si el apartamento está disponible (excluyendo el residente actual)
        $this->resident->apartamento = $data['apartamento'];
        $this->resident->id = $id;
        if(!$this->resident->isApartmentAvailable()) {
            $this->resident->id = $id;
            $current_data = $this->resident->readOne();
            
            $this->view('admin/residents/edit', [
                'error' => 'El apartamento ya está ocupado',
                'resident' => array_merge($current_data, $data)
            ]);
            return;
        }
        
        // Obtener datos actuales del residente
        $this->resident->id = $id;
        $current_resident = $this->resident->readOne();
        
        // Actualizar datos del usuario
        $this->user->id = $current_resident['usuario_id'];
        $this->user->nombre = $data['nombre'];
        $this->user->email = $data['email'];
        $this->user->telefono = $data['telefono'];
        $this->user->rol = 'resident'; // Mantener el rol como residente
        
        // Verificar si el email ya existe (excluyendo el usuario actual)
        if($data['email'] !== $current_resident['email']) {
            $this->user->email = $data['email'];
            if($this->user->emailExists()) {
                $this->view('admin/residents/edit', [
                    'error' => 'El email ya está registrado por otro usuario',
                    'resident' => array_merge($current_resident, $data)
                ]);
                return;
            }
        }
        
        $user_updated = $this->user->update();
        
        // Actualizar datos del residente
        $this->resident->id = $id;
        $this->resident->apartamento = $data['apartamento'];
        $this->resident->piso = $data['piso'];
        $this->resident->torre = $data['torre'];
        $this->resident->fecha_ingreso = $data['fecha_ingreso'];
        $this->resident->estado = $data['estado'];
        
        $resident_updated = $this->resident->update();
        
        if($user_updated && $resident_updated) {
            flash('Residente actualizado correctamente', 'success');
            redirect('/residents');
        } else {
            $this->view('admin/residents/edit', [
                'error' => 'Error al actualizar el residente',
                'resident' => array_merge($current_resident, $data)
            ]);
        }
    }
    
    // Eliminar residente (solo admin)
    public function delete($id) {
        $this->requireAdmin();
        
        $this->resident->id = $id;
        if($this->resident->delete()) {
            flash('Residente eliminado correctamente', 'success');
        } else {
            flash('Error al eliminar el residente', 'error');
        }
        
        redirect('/residents');
    }
    
    // Obtener residentes activos (para select/options)
    public function getActiveResidents() {
        $this->requireAuth();
        
        $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
        $this->jsonResponse($residents);
    }
    
    // Ver mi perfil de residente
    public function myProfile() {
        $this->requireResident();
        
        $current_user = $this->getCurrentUser();
        $resident_data = $this->resident->getByUserId($current_user['id']);
        
        if(!$resident_data) {
            flash('No se encontró su información de residente', 'error');
            redirect('/dashboard');
            return;
        }
        
        $this->view('resident/profile', [
            'user' => $current_user,
            'resident' => $resident_data
        ]);
    }
}
?>
