<?php
// Modelo de Incidencias

class Incident {
    private $conn;
    private $table_name = "incidencias";

    public $id;
    public $residente_id;
    public $titulo;
    public $descripcion;
    public $categoria;
    public $prioridad;
    public $estado;
    public $fecha_reporte;
    public $fecha_resolucion;
    public $administrador_id;
    public $notas_admin;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear incidencia
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (residente_id, titulo, descripcion, categoria, prioridad, estado, fecha_reporte) VALUES (:residente_id, :titulo, :descripcion, :categoria, :prioridad, :estado, :fecha_reporte)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->residente_id = htmlspecialchars(strip_tags($this->residente_id));
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->prioridad = htmlspecialchars(strip_tags($this->prioridad));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->fecha_reporte = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(":residente_id", $this->residente_id);
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":prioridad", $this->prioridad);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":fecha_reporte", $this->fecha_reporte);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Leer todas las incidencias con información de residentes
    public function readAll() {
        $query = "SELECT i.*, r.apartamento, u.nombre as residente_nombre, u.email as residente_email,
                         a.nombre as admin_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN residentes r ON i.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios a ON i.administrador_id = a.id
                  ORDER BY i.fecha_reporte DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Leer incidencias por residente
    public function readByResident($residente_id) {
        $query = "SELECT i.*, r.apartamento, u.nombre as residente_nombre, u.email as residente_email,
                         a.nombre as admin_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN residentes r ON i.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios a ON i.administrador_id = a.id
                  WHERE i.residente_id = :residente_id
                  ORDER BY i.fecha_reporte DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":residente_id", $residente_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Leer una incidencia por ID
    public function readOne() {
        $query = "SELECT i.*, r.apartamento, u.nombre as residente_nombre, u.email as residente_email,
                         a.nombre as admin_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN residentes r ON i.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios a ON i.administrador_id = a.id
                  WHERE i.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar incidencia
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET titulo = :titulo, descripcion = :descripcion, categoria = :categoria, prioridad = :prioridad, estado = :estado, administrador_id = :administrador_id, notas_admin = :notas_admin";
        
        // Si el estado es 'resuelta', actualizar fecha de resolución
        if($this->estado === 'resuelta') {
            $query .= ", fecha_resolucion = :fecha_resolucion";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->prioridad = htmlspecialchars(strip_tags($this->prioridad));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->administrador_id = htmlspecialchars(strip_tags($this->administrador_id));
        $this->notas_admin = htmlspecialchars(strip_tags($this->notas_admin));
        
        // Bind parameters
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":prioridad", $this->prioridad);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":administrador_id", $this->administrador_id);
        $stmt->bindParam(":notas_admin", $this->notas_admin);
        $stmt->bindParam(":id", $this->id);
        
        // Si el estado es 'resuelta', bindear fecha de resolución
        if($this->estado === 'resuelta') {
            $this->fecha_resolucion = date('Y-m-d H:i:s');
            $stmt->bindParam(":fecha_resolucion", $this->fecha_resolucion);
        }
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar incidencia
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Obtener incidencias por estado
    public function getByStatus($estado) {
        $query = "SELECT i.*, r.apartamento, u.nombre as residente_nombre, u.email as residente_email,
                         a.nombre as admin_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN residentes r ON i.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios a ON i.administrador_id = a.id
                  WHERE i.estado = :estado
                  ORDER BY i.fecha_reporte DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener incidencias por categoría
    public function getByCategory($categoria) {
        $query = "SELECT i.*, r.apartamento, u.nombre as residente_nombre, u.email as residente_email,
                         a.nombre as admin_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN residentes r ON i.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios a ON i.administrador_id = a.id
                  WHERE i.categoria = :categoria
                  ORDER BY i.fecha_reporte DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener incidencias por prioridad
    public function getByPriority($prioridad) {
        $query = "SELECT i.*, r.apartamento, u.nombre as residente_nombre, u.email as residente_email,
                         a.nombre as admin_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN residentes r ON i.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios a ON i.administrador_id = a.id
                  WHERE i.prioridad = :prioridad
                  ORDER BY i.fecha_reporte DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":prioridad", $prioridad);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener estadísticas de incidencias
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_incidencias,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as incidencias_pendientes,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as incidencias_en_proceso,
                    SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as incidencias_resueltas,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as incidencias_canceladas,
                    SUM(CASE WHEN prioridad = 'alta' THEN 1 ELSE 0 END) as incidencias_alta_prioridad,
                    SUM(CASE WHEN prioridad = 'media' THEN 1 ELSE 0 END) as incidencias_media_prioridad,
                    SUM(CASE WHEN prioridad = 'baja' THEN 1 ELSE 0 END) as incidencias_baja_prioridad,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as incidencias_30_dias,
                    COALESCE(AVG(DATEDIFF(fecha_resolucion, fecha_reporte)), 0) as tiempo_promedio_resolucion
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener incidencias por categoría para estadísticas
    public function getIncidentsByCategory() {
        $query = "SELECT categoria, COUNT(*) as cantidad 
                  FROM " . $this->table_name . " 
                  GROUP BY categoria 
                  ORDER BY cantidad DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener incidencias mensuales
    public function getMonthlyIncidents($months = 12) {
        $query = "SELECT 
                    DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                    COUNT(*) as cantidad
                  FROM " . $this->table_name . " 
                  WHERE fecha_reporte >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                  GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m')
                  ORDER BY mes ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":months", $months);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
