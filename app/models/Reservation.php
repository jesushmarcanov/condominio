<?php
/**
 * Modelo de Reservas
 * 
 * Gestiona las operaciones CRUD de reservas de áreas comunes.
 * Cada reserva asocia un residente con un área común en una
 * fecha y rango horario específicos.
 * 
 * Estados de reserva:
 * - confirmada: Reserva activa y válida
 * - cancelada: Reserva cancelada
 * - completada: Reserva cuyo evento ya se realizó
 * 
 * @package App\Models
 * @version 1.0.0
 */

class Reservation {
    private $conn;
    private $table_name = "reservas";

    public $id;
    public $area_comun_id;
    public $residente_id;
    public $fecha_reserva;
    public $hora_inicio;
    public $hora_fin;
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
     * Crear una nueva reserva
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (area_comun_id, residente_id, fecha_reserva, hora_inicio, hora_fin, estado) VALUES (:area_comun_id, :residente_id, :fecha_reserva, :hora_inicio, :hora_fin, :estado)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->area_comun_id = htmlspecialchars(strip_tags($this->area_comun_id));
        $this->residente_id = htmlspecialchars(strip_tags($this->residente_id));
        $this->fecha_reserva = htmlspecialchars(strip_tags($this->fecha_reserva));
        $this->hora_inicio = htmlspecialchars(strip_tags($this->hora_inicio));
        $this->hora_fin = htmlspecialchars(strip_tags($this->hora_fin));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Bind parameters
        $stmt->bindParam(":area_comun_id", $this->area_comun_id);
        $stmt->bindParam(":residente_id", $this->residente_id);
        $stmt->bindParam(":fecha_reserva", $this->fecha_reserva);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fin", $this->hora_fin);
        $stmt->bindParam(":estado", $this->estado);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Leer todas las reservas con información relacionada
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll() {
        $query = "SELECT rv.*, 
                         ac.nombre as area_nombre, ac.capacidad as area_capacidad,
                         res.apartamento, u.nombre as residente_nombre, u.email as residente_email
                  FROM " . $this->table_name . " rv
                  LEFT JOIN areas_comunes ac ON rv.area_comun_id = ac.id
                  LEFT JOIN residentes res ON rv.residente_id = res.id
                  LEFT JOIN usuarios u ON res.usuario_id = u.id
                  ORDER BY rv.fecha_reserva DESC, rv.hora_inicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer reservas de un residente específico
     * 
     * @param int $residente_id ID del residente
     * @return PDOStatement Resultado de la consulta
     */
    public function readByResident($residente_id) {
        $query = "SELECT rv.*, 
                         ac.nombre as area_nombre, ac.capacidad as area_capacidad,
                         res.apartamento, u.nombre as residente_nombre, u.email as residente_email
                  FROM " . $this->table_name . " rv
                  LEFT JOIN areas_comunes ac ON rv.area_comun_id = ac.id
                  LEFT JOIN residentes res ON rv.residente_id = res.id
                  LEFT JOIN usuarios u ON res.usuario_id = u.id
                  WHERE rv.residente_id = :residente_id
                  ORDER BY rv.fecha_reserva DESC, rv.hora_inicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":residente_id", $residente_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer una reserva por ID con información relacionada
     * 
     * @return array|false Datos de la reserva o false si no existe
     */
    public function readOne() {
        $query = "SELECT rv.*, 
                         ac.nombre as area_nombre, ac.descripcion as area_descripcion, ac.capacidad as area_capacidad, ac.horario_disponible as area_horario,
                         res.apartamento, u.nombre as residente_nombre, u.email as residente_email
                  FROM " . $this->table_name . " rv
                  LEFT JOIN areas_comunes ac ON rv.area_comun_id = ac.id
                  LEFT JOIN residentes res ON rv.residente_id = res.id
                  LEFT JOIN usuarios u ON res.usuario_id = u.id
                  WHERE rv.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar el estado de una reserva
     * 
     * Permite cambiar el estado de una reserva (confirmada -> cancelada/completada).
     * 
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Eliminar una reserva
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
     * Verificar si existe un conflicto de horario en la misma área y fecha
     * 
     * Comprueba si ya existe una reserva confirmada para el mismo área
     * y fecha con un horario que se traslape con el rango solicitado.
     * 
     * @return bool True si hay conflicto, false si el horario está libre
     */
    public function hasScheduleConflict() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE area_comun_id = :area_comun_id 
                  AND fecha_reserva = :fecha_reserva 
                  AND estado = 'confirmada'
                  AND id != :id
                  AND hora_inicio < :hora_fin 
                  AND hora_fin > :hora_inicio 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":area_comun_id", $this->area_comun_id);
        $stmt->bindParam(":fecha_reserva", $this->fecha_reserva);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fin", $this->hora_fin);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Verificar si el rango horario es válido (inicio < fin)
     * 
     * @return bool True si el horario es válido, false en caso contrario
     */
    public function isValidTimeRange() {
        if (empty($this->hora_inicio) || empty($this->hora_fin)) {
            return false;
        }
        return strtotime($this->hora_inicio) < strtotime($this->hora_fin);
    }
}
?>
