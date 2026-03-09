<?php
// Clase base de Controladores

class Controller {
    protected $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Cargar una vista
    protected function view($view, $data = []) {
        // Extraer variables para que estén disponibles en la vista
        extract($data);
        
        // Incluir el archivo de la vista
        require_once APP_PATH . '/views/' . $view . '.php';
    }
    
    // Verificar si el usuario está autenticado
    protected function requireAuth() {
        if(!isLoggedIn()) {
            flash('Debe iniciar sesión para acceder a esta página', 'warning');
            redirect('/login');
        }
    }
    
    // Verificar si el usuario es administrador
    protected function requireAdmin() {
        $this->requireAuth();
        if(!isAdmin()) {
            flash('No tiene permisos para acceder a esta página', 'error');
            redirect('/dashboard');
        }
    }
    
    // Verificar si el usuario es residente
    protected function requireResident() {
        $this->requireAuth();
        if(!isResident()) {
            flash('No tiene permisos para acceder a esta página', 'error');
            redirect('/dashboard');
        }
    }
    
    // Obtener datos del usuario actual
    protected function getCurrentUser() {
        if(isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'rol' => $_SESSION['user_role']
            ];
        }
        return null;
    }
    
    // Validar datos del formulario
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach($rules as $field => $field_rules) {
            $value = isset($data[$field]) ? trim($data[$field]) : '';
            
            foreach($field_rules as $rule => $rule_value) {
                switch($rule) {
                    case 'required':
                        if(empty($value)) {
                            $errors[$field] = "El campo {$field} es requerido";
                        }
                        break;
                        
                    case 'email':
                        if(!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "El campo {$field} debe ser un email válido";
                        }
                        break;
                        
                    case 'min':
                        if(!empty($value) && strlen($value) < $rule_value) {
                            $errors[$field] = "El campo {$field} debe tener al menos {$rule_value} caracteres";
                        }
                        break;
                        
                    case 'max':
                        if(!empty($value) && strlen($value) > $rule_value) {
                            $errors[$field] = "El campo {$field} no debe exceder {$rule_value} caracteres";
                        }
                        break;
                        
                    case 'numeric':
                        if(!empty($value) && !is_numeric($value)) {
                            $errors[$field] = "El campo {$field} debe ser numérico";
                        }
                        break;
                        
                    case 'in':
                        if(!empty($value) && !in_array($value, $rule_value)) {
                            $errors[$field] = "El campo {$field} tiene un valor inválido";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    // Obtener datos POST sanitizados
    protected function getPostData() {
        $data = [];
        foreach($_POST as $key => $value) {
            $data[$key] = sanitize($value);
        }
        return $data;
    }
    
    // Enviar respuesta JSON
    protected function jsonResponse($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    // Redireccionar con mensaje flash
    protected function redirectWithMessage($url, $message, $type = 'success') {
        flash($message, $type);
        redirect($url);
    }
}
?>
