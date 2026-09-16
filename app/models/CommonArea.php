<?php
/**
 * Modelo de Áreas Comunes
 * 
 * Gestiona las operaciones CRUD de áreas comunes del condominio
 * (salones de eventos, piscinas, áreas de juego, gimnasios, etc.).
 * 
 * Estados de área:
 * - disponible: Disponible para reservas
 * - mantenimiento: En mantenimiento (no se puede reservar)
 * - no_disponible: Fuera de servicio
 * 
 * @package App\Models
 * @version 1.0.0
 */

class CommonArea {
    private $conn;
    private $table_name = "areas_comunes";

    public $id;
    public $nombre;
    public $descripcion;
    public $capacidad;
    public $horario_disponible;
    public $estado;
    public $created_at;
    public $updated_at;

    /**
     * Constructor
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear una nueva área común
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (nombre, descripcion, capacidad, horario_disponible, estado) VALUES (:nombre, :descripcion, :capacidad, :horario_disponible, :estado)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->capacidad = htmlspecialchars(strip_tags($this->capacidad));
        $this->horario_disponible = htmlspecialchars(strip_tags($this->horario_disponible));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":capacidad", $this->capacidad);
        $stmt->bindParam(":horario_disponible", $this->horario_disponible);
        $stmt->bindParam(":estado", $this->estado);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Leer todas las áreas comunes
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer un área común por ID
     * 
     * @return array|false Datos del área o false si no existe
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar un área común existente
     * 
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET nombre = :nombre, descripcion = :descripcion, capacidad = :capacidad, horario_disponible = :horario_disponible, estado = :estado WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->capacidad = htmlspecialchars(strip_tags($this->capacidad));
        $this->horario_disponible = htmlspecialchars(strip_tags($this->horario_disponible));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":capacidad", $this->capacidad);
        $stmt->bindParam(":horario_disponible", $this->horario_disponible);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Eliminar un área común
     * 
     * @return bool True si se eliminó exitosamente, false en caso contrario
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Obtener áreas comunes disponibles para reserva
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function getAvailableAreas() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE estado = 'disponible' 
                  ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
