<?php
/**
 * Modelo de Eventos de Incidencias
 * 
 * Gestiona el registro de eventos y cambios en las incidencias para auditoría.
 * Cada evento registra quién hizo qué cambio, cuándo y desde dónde.
 * 
 * @package App\Models
 * @author ResiTech
 * @version 1.0.0
 */

class IncidentEvent {
    private $conn;
    private $table_name = "incident_events";

    public $id;
    public $incident_id;
    public $user_id;
    public $event_type;
    public $old_value;
    public $new_value;
    public $description;
    public $ip_address;
    public $user_agent;
    public $created_at;

    /**
     * Constructor
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear un nuevo evento
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (incident_id, user_id, event_type, old_value, new_value, description, ip_address, user_agent) 
                  VALUES 
                  (:incident_id, :user_id, :event_type, :old_value, :new_value, :description, :ip_address, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->incident_id = htmlspecialchars(strip_tags($this->incident_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->event_type = htmlspecialchars(strip_tags($this->event_type));
        $this->old_value = htmlspecialchars(strip_tags($this->old_value ?? ''));
        $this->new_value = htmlspecialchars(strip_tags($this->new_value ?? ''));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->ip_address = $this->getClientIP();
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Bind parameters
        $stmt->bindParam(":incident_id", $this->incident_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":event_type", $this->event_type);
        $stmt->bindParam(":old_value", $this->old_value);
        $stmt->bindParam(":new_value", $this->new_value);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":ip_address", $this->ip_address);
        $stmt->bindParam(":user_agent", $this->user_agent);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Leer eventos de una incidencia
     * 
     * @param int $incident_id ID de la incidencia
     * @return PDOStatement Resultado de la consulta
     */
    public function readByIncident($incident_id) {
        $query = "SELECT e.*, u.nombre as user_name, u.email as user_email
                  FROM " . $this->table_name . " e
                  LEFT JOIN usuarios u ON e.user_id = u.id
                  WHERE e.incident_id = :incident_id
                  ORDER BY e.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":incident_id", $incident_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer eventos de un usuario
     * 
     * @param int $user_id ID del usuario
     * @param int $limit Límite de resultados (opcional)
     * @return PDOStatement Resultado de la consulta
     */
    public function readByUser($user_id, $limit = null) {
        $query = "SELECT e.*, i.titulo as incident_title
                  FROM " . $this->table_name . " e
                  LEFT JOIN incidencias i ON e.incident_id = i.id
                  WHERE e.user_id = :user_id
                  ORDER BY e.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer todos los eventos con filtros
     * 
     * @param array $filters Filtros opcionales
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll($filters = []) {
        $query = "SELECT e.*, u.nombre as user_name, i.titulo as incident_title
                  FROM " . $this->table_name . " e
                  LEFT JOIN usuarios u ON e.user_id = u.id
                  LEFT JOIN incidencias i ON e.incident_id = i.id
                  WHERE 1=1";
        
        // Aplicar filtros
        if (!empty($filters['incident_id'])) {
            $query .= " AND e.incident_id = :incident_id";
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND e.user_id = :user_id";
        }
        
        if (!empty($filters['event_type'])) {
            $query .= " AND e.event_type = :event_type";
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND e.created_at >= :date_from";
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND e.created_at <= :date_to";
        }
        
        $query .= " ORDER BY e.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind filtros
        if (!empty($filters['incident_id'])) {
            $stmt->bindParam(":incident_id", $filters['incident_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $stmt->bindParam(":user_id", $filters['user_id']);
        }
        
        if (!empty($filters['event_type'])) {
            $stmt->bindParam(":event_type", $filters['event_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $stmt->bindParam(":date_from", $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $stmt->bindParam(":date_to", $filters['date_to']);
        }
        
        if (!empty($filters['limit'])) {
            $stmt->bindParam(":limit", $filters['limit'], PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Obtener estadísticas de eventos
     * 
     * @return array Estadísticas de eventos
     */
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_events,
                    SUM(CASE WHEN event_type = 'created' THEN 1 ELSE 0 END) as created_events,
                    SUM(CASE WHEN event_type = 'status_changed' THEN 1 ELSE 0 END) as status_changed_events,
                    SUM(CASE WHEN event_type = 'assigned' THEN 1 ELSE 0 END) as assigned_events,
                    SUM(CASE WHEN event_type = 'updated' THEN 1 ELSE 0 END) as updated_events,
                    SUM(CASE WHEN event_type = 'deleted' THEN 1 ELSE 0 END) as deleted_events,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as events_24h,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as events_7d
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener IP del cliente
     * 
     * @return string IP del cliente
     */
    private function getClientIP() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        return $ip;
    }

    /**
     * Registrar evento de creación
     * 
     * @param int $incident_id ID de la incidencia
     * @param int $user_id ID del usuario
     * @param string $description Descripción del evento
     * @return bool True si se registró exitosamente
     */
    public static function logCreated($db, $incident_id, $user_id, $description = '') {
        $event = new self($db);
        $event->incident_id = $incident_id;
        $event->user_id = $user_id;
        $event->event_type = 'created';
        $event->description = $description;
        
        return $event->create();
    }

    /**
     * Registrar evento de cambio de estado
     * 
     * @param int $incident_id ID de la incidencia
     * @param int $user_id ID del usuario
     * @param string $old_status Estado anterior
     * @param string $new_status Estado nuevo
     * @param string $description Descripción del evento
     * @return bool True si se registró exitosamente
     */
    public static function logStatusChanged($db, $incident_id, $user_id, $old_status, $new_status, $description = '') {
        $event = new self($db);
        $event->incident_id = $incident_id;
        $event->user_id = $user_id;
        $event->event_type = 'status_changed';
        $event->old_value = $old_status;
        $event->new_value = $new_status;
        $event->description = $description;
        
        return $event->create();
    }

    /**
     * Registrar evento de asignación
     * 
     * @param int $incident_id ID de la incidencia
     * @param int $user_id ID del usuario que asigna
     * @param int $assigned_to ID del usuario asignado
     * @param string $description Descripción del evento
     * @return bool True si se registró exitosamente
     */
    public static function logAssigned($db, $incident_id, $user_id, $assigned_to, $description = '') {
        $event = new self($db);
        $event->incident_id = $incident_id;
        $event->user_id = $user_id;
        $event->event_type = 'assigned';
        $event->new_value = $assigned_to;
        $event->description = $description;
        
        return $event->create();
    }
}
?>
