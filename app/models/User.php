<?php
// Modelo de Usuario

class User {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $rol;
    public $telefono;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Login de usuario
    public function login($email, $password) {
        $query = "SELECT id, nombre, email, password, rol, telefono FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row && password_verify($password, $row['password'])) {
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->email = $row['email'];
            $this->rol = $row['rol'];
            $this->telefono = $row['telefono'];
            return true;
        }
        
        return false;
    }

    // Crear usuario
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (nombre, email, password, rol, telefono) VALUES (:nombre, :email, :password, :rol, :telefono)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->rol = htmlspecialchars(strip_tags($this->rol));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":rol", $this->rol);
        $stmt->bindParam(":telefono", $this->telefono);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Leer todos los usuarios
    public function readAll() {
        $query = "SELECT id, nombre, email, rol, telefono, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Leer un usuario por ID
    public function readOne() {
        $query = "SELECT id, nombre, email, rol, telefono, created_at, updated_at FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nombre = $row['nombre'];
            $this->email = $row['email'];
            $this->rol = $row['rol'];
            $this->telefono = $row['telefono'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }

    // Actualizar usuario
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET nombre = :nombre, email = :email, rol = :rol, telefono = :telefono";
        
        // Si se proporciona nueva contraseña, actualizarla también
        if(!empty($this->password)) {
            $query .= ", password = :password";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->rol = htmlspecialchars(strip_tags($this->rol));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":rol", $this->rol);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":id", $this->id);
        
        // Si se proporciona nueva contraseña, hashearla y bindearla
        if(!empty($this->password)) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $this->password);
        }
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar usuario
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Verificar si email existe
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Buscar usuarios por nombre o email
    public function search($search_term) {
        $query = "SELECT id, nombre, email, rol, telefono, created_at FROM " . $this->table_name . " 
                  WHERE nombre LIKE :search OR email LIKE :search ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%{$search_term}%";
        $stmt->bindParam(":search", $search_term);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener estadísticas de usuarios
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as total_admins,
                    SUM(CASE WHEN rol = 'resident' THEN 1 ELSE 0 END) as total_residents,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as nuevos_30_dias
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
