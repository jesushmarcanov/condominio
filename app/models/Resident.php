<?php
// Modelo de Residente

class Resident {
    private $conn;
    private $table_name = "residentes";

    public $id;
    public $usuario_id;
    public $apartamento;
    public $piso;
    public $torre;
    public $fecha_ingreso;
    public $estado;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear residente
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (usuario_id, apartamento, piso, torre, fecha_ingreso, estado) VALUES (:usuario_id, :apartamento, :piso, :torre, :fecha_ingreso, :estado)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));
        $this->apartamento = htmlspecialchars(strip_tags($this->apartamento));
        $this->piso = htmlspecialchars(strip_tags($this->piso));
        $this->torre = htmlspecialchars(strip_tags($this->torre));
        $this->fecha_ingreso = htmlspecialchars(strip_tags($this->fecha_ingreso));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Bind parameters
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":apartamento", $this->apartamento);
        $stmt->bindParam(":piso", $this->piso);
        $stmt->bindParam(":torre", $this->torre);
        $stmt->bindParam(":fecha_ingreso", $this->fecha_ingreso);
        $stmt->bindParam(":estado", $this->estado);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Leer todos los residentes con información de usuario
    public function readAll() {
        $query = "SELECT r.*, u.nombre, u.email, u.telefono 
                  FROM " . $this->table_name . " r
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  ORDER BY r.apartamento ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Leer un residente por ID
    public function readOne() {
        $query = "SELECT r.*, u.nombre, u.email, u.telefono 
                  FROM " . $this->table_name . " r
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE r.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->usuario_id = $row['usuario_id'];
            $this->apartamento = $row['apartamento'];
            $this->piso = $row['piso'];
            $this->torre = $row['torre'];
            $this->fecha_ingreso = $row['fecha_ingreso'];
            $this->estado = $row['estado'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return $row;
        }
        
        return false;
    }

    // Actualizar residente
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET apartamento = :apartamento, piso = :piso, torre = :torre, fecha_ingreso = :fecha_ingreso, estado = :estado WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->apartamento = htmlspecialchars(strip_tags($this->apartamento));
        $this->piso = htmlspecialchars(strip_tags($this->piso));
        $this->torre = htmlspecialchars(strip_tags($this->torre));
        $this->fecha_ingreso = htmlspecialchars(strip_tags($this->fecha_ingreso));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Bind parameters
        $stmt->bindParam(":apartamento", $this->apartamento);
        $stmt->bindParam(":piso", $this->piso);
        $stmt->bindParam(":torre", $this->torre);
        $stmt->bindParam(":fecha_ingreso", $this->fecha_ingreso);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar residente
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Buscar residentes por nombre, apartamento o torre
    public function search($search_term) {
        $query = "SELECT r.*, u.nombre, u.email, u.telefono 
                  FROM " . $this->table_name . " r
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE u.nombre LIKE :search OR r.apartamento LIKE :search OR r.torre LIKE :search 
                  ORDER BY r.apartamento ASC";
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%{$search_term}%";
        $stmt->bindParam(":search", $search_term);
        $stmt->execute();
        
        return $stmt;
    }

    // Verificar si apartamento está disponible
    public function isApartmentAvailable() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE apartamento = :apartamento AND id != :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":apartamento", $this->apartamento);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->rowCount() == 0;
    }

    // Obtener residentes activos
    public function getActiveResidents() {
        $query = "SELECT r.*, u.nombre, u.email 
                  FROM " . $this->table_name . " r
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE r.estado = 'activo'
                  ORDER BY r.apartamento ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener estadísticas de residentes
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_residentes,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as residentes_activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as residentes_inactivos,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as nuevos_30_dias
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener residente por usuario_id
    public function getByUserId($user_id) {
        $query = "SELECT r.*, u.nombre, u.email, u.telefono 
                  FROM " . $this->table_name . " r
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE r.usuario_id = :user_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
