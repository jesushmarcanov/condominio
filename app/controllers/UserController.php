<?php
// Controlador de Usuarios

class UserController extends Controller {
    private $user;
    private $resident;
    
    public function __construct() {
        parent::__construct();
        $this->user = new User($this->db);
        $this->resident = new Resident($this->db);
    }
    
    // Mostrar formulario de login
    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        if(isLoggedIn()) {
            redirect('/dashboard');
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            $this->view('auth/login');
        }
    }
    
    // Procesar login
    private function handleLogin() {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        // Validar datos
        $errors = [];
        if(empty($email)) $errors[] = 'El email es requerido';
        if(empty($password)) $errors[] = 'La contraseña es requerida';
        
        if(!empty($errors)) {
            $this->view('auth/login', [
                'errors' => $errors,
                'email' => $email
            ]);
            return;
        }
        
        // Intentar login
        if($this->user->login($email, $password)) {
            // Establecer sesión
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['user_name'] = $this->user->nombre;
            $_SESSION['user_email'] = $this->user->email;
            $_SESSION['user_role'] = $this->user->rol;
            
            flash('Bienvenido ' . $this->user->nombre, 'success');
            redirect('/dashboard');
        } else {
            $this->view('auth/login', [
                'error' => 'Email o contraseña incorrectos',
                'email' => $email
            ]);
        }
    }
    
    // Cerrar sesión
    public function logout() {
        session_destroy();
        flash('Has cerrado sesión correctamente', 'success');
        redirect('/login');
    }
    
    // Mostrar dashboard
    public function dashboard() {
        $this->requireAuth();
        
        $current_user = $this->getCurrentUser();
        $stats = [];
        
        if(isAdmin()) {
            // Estadísticas para administradores
            $stats['usuarios'] = $this->user->getStats();
            $stats['residentes'] = $this->resident->getStats();
            
            // Obtener estadísticas adicionales
            $payment = new Payment($this->db);
            $incident = new Incident($this->db);
            $stats['pagos'] = $payment->getStats();
            $stats['incidencias'] = $incident->getStats();
            
            // Actividades recientes
            $stats['pagos_recientes'] = $payment->readAll()->fetchAll(PDO::FETCH_ASSOC);
            $stats['incidencias_recientes'] = $incident->readAll()->fetchAll(PDO::FETCH_ASSOC);
            
            $this->view('admin/dashboard', [
                'user' => $current_user,
                'stats' => $stats
            ]);
        } else {
            // Estadísticas para residentes
            $resident_data = $this->resident->getByUserId($current_user['id']);
            
            if($resident_data) {
                $payment = new Payment($this->db);
                $incident = new Incident($this->db);
                
                $stats['mis_pagos'] = $payment->readByResident($resident_data['id'])->fetchAll(PDO::FETCH_ASSOC);
                $stats['mis_incidencias'] = $incident->readByResident($resident_data['id'])->fetchAll(PDO::FETCH_ASSOC);
                $stats['residente_info'] = $resident_data;
            }
            
            $this->view('resident/dashboard', [
                'user' => $current_user,
                'stats' => $stats
            ]);
        }
    }
    
    // Mostrar perfil de usuario
    public function profile() {
        $this->requireAuth();
        
        $current_user = $this->getCurrentUser();
        $this->user->id = $current_user['id'];
        $this->user->readOne();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateProfile();
        } else {
            $this->view('user/profile', [
                'user' => $current_user,
                'user_data' => $this->user
            ]);
        }
    }
    
    // Actualizar perfil
    private function updateProfile() {
        $current_user = $this->getCurrentUser();
        
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'nombre' => ['required' => true, 'max' => 100],
            'email' => ['required' => true, 'email' => true, 'max' => 100],
            'telefono' => ['max' => 20]
        ]);
        
        if(!empty($errors)) {
            $this->view('user/profile', [
                'user' => $current_user,
                'user_data' => $this->user,
                'errors' => $errors
            ]);
            return;
        }
        
        // Verificar si el email ya existe (excepto el actual)
        $this->user->email = $data['email'];
        if($this->user->emailExists() && $this->user->email != $current_user['email']) {
            $this->view('user/profile', [
                'user' => $current_user,
                'user_data' => $this->user,
                'error' => 'El email ya está registrado'
            ]);
            return;
        }
        
        // Actualizar datos
        $this->user->id = $current_user['id'];
        $this->user->nombre = $data['nombre'];
        $this->user->email = $data['email'];
        $this->user->telefono = $data['telefono'];
        
        // Si se proporciona nueva contraseña
        if(!empty($data['password'])) {
            $this->user->password = $data['password'];
        }
        
        if($this->user->update()) {
            // Actualizar sesión
            $_SESSION['user_name'] = $this->user->nombre;
            $_SESSION['user_email'] = $this->user->email;
            
            flash('Perfil actualizado correctamente', 'success');
            redirect('/profile');
        } else {
            $this->view('user/profile', [
                'user' => $current_user,
                'user_data' => $this->user,
                'error' => 'Error al actualizar el perfil'
            ]);
        }
    }
    
    // Listar usuarios (solo admin)
    public function index() {
        $this->requireAdmin();
        
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
        
        if(!empty($search)) {
            $users = $this->user->search($search);
        } else {
            $users = $this->user->readAll();
        }
        
        $this->view('admin/users/index', [
            'users' => $users->fetchAll(PDO::FETCH_ASSOC),
            'search' => $search
        ]);
    }
    
    // Crear usuario (solo admin)
    public function create() {
        $this->requireAdmin();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeUser();
        } else {
            $this->view('admin/users/create');
        }
    }
    
    // Guardar nuevo usuario
    private function storeUser() {
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'nombre' => ['required' => true, 'max' => 100],
            'email' => ['required' => true, 'email' => true, 'max' => 100],
            'password' => ['required' => true, 'min' => 6],
            'rol' => ['required' => true, 'in' => ['admin', 'resident']],
            'telefono' => ['max' => 20]
        ]);
        
        if(!empty($errors)) {
            $this->view('admin/users/create', [
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }
        
        // Verificar si el email ya existe
        $this->user->email = $data['email'];
        if($this->user->emailExists()) {
            $this->view('admin/users/create', [
                'error' => 'El email ya está registrado',
                'data' => $data
            ]);
            return;
        }
        
        // Crear usuario
        $this->user->nombre = $data['nombre'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->rol = $data['rol'];
        $this->user->telefono = $data['telefono'];
        
        if($this->user->create()) {
            flash('Usuario creado correctamente', 'success');
            redirect('/users');
        } else {
            $this->view('admin/users/create', [
                'error' => 'Error al crear el usuario',
                'data' => $data
            ]);
        }
    }
    
    // Editar usuario (solo admin)
    public function edit($id) {
        $this->requireAdmin();
        
        $this->user->id = $id;
        if(!$this->user->readOne()) {
            flash('Usuario no encontrado', 'error');
            redirect('/users');
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateUser($id);
        } else {
            $this->view('admin/users/edit', [
                'user' => $this->user
            ]);
        }
    }
    
    // Actualizar usuario
    private function updateUser($id) {
        $data = $this->getPostData();
        $errors = $this->validate($data, [
            'nombre' => ['required' => true, 'max' => 100],
            'email' => ['required' => true, 'email' => true, 'max' => 100],
            'rol' => ['required' => true, 'in' => ['admin', 'resident']],
            'telefono' => ['max' => 20]
        ]);
        
        if(!empty($errors)) {
            $this->view('admin/users/edit', [
                'errors' => $errors,
                'user' => $this->user
            ]);
            return;
        }
        
        // Verificar si el email ya existe (excepto el actual)
        $this->user->email = $data['email'];
        if($this->user->emailExists() && $this->user->email != $this->user->email) {
            $this->view('admin/users/edit', [
                'error' => 'El email ya está registrado',
                'user' => $this->user
            ]);
            return;
        }
        
        // Actualizar usuario
        $this->user->id = $id;
        $this->user->nombre = $data['nombre'];
        $this->user->email = $data['email'];
        $this->user->rol = $data['rol'];
        $this->user->telefono = $data['telefono'];
        
        // Si se proporciona nueva contraseña
        if(!empty($data['password'])) {
            $this->user->password = $data['password'];
        }
        
        if($this->user->update()) {
            flash('Usuario actualizado correctamente', 'success');
            redirect('/users');
        } else {
            $this->view('admin/users/edit', [
                'error' => 'Error al actualizar el usuario',
                'user' => $this->user
            ]);
        }
    }
    
    // Eliminar usuario (solo admin)
    public function delete($id) {
        $this->requireAdmin();
        
        $this->user->id = $id;
        if($this->user->delete()) {
            flash('Usuario eliminado correctamente', 'success');
        } else {
            flash('Error al eliminar el usuario', 'error');
        }
        
        redirect('/users');
    }
}
?>
