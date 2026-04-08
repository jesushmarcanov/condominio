<?php
/**
 * Modelo de Notificaciones
 * 
 * Gestiona las operaciones CRUD de notificaciones en la base de datos.
 * Las notificaciones se utilizan para informar a los usuarios sobre eventos
 * importantes como pagos vencidos, incidencias, etc.
 * 
 * @package App\Models
 * @author Sistema de Gestión de Condominio
 * @version 1.0.0
 */

class Notification {
    private $conn;
    private $table_name = "notificaciones";

    public $id;
    public $usuario_id;
    public $titulo;
    public $mensaje;
    public $tipo;
    public $leida;
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
     * Crear una nueva notificación
     * 
     * Inserta un nuevo registro de notificación en la base de datos.
     * Los datos deben estar previamente asignados a las propiedades públicas.
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (usuario_id, titulo, mensaje, tipo, leida) VALUES (:usuario_id, :titulo, :mensaje, :tipo, :leida)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->mensaje = htmlspecialchars(strip_tags($this->mensaje));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->leida = isset($this->leida) ? $this->leida : false;
        
        // Bind parameters
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":mensaje", $this->mensaje);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":leida", $this->leida, PDO::PARAM_BOOL);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Leer todas las notificaciones de un usuario
     * 
     * @param int $usuario_id ID del usuario
     * @return PDOStatement Resultado de la consulta
     */
    public function readByUser($usuario_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE usuario_id = :usuario_id 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer notificaciones no leídas de un usuario
     * 
     * @param int $usuario_id ID del usuario
     * @return PDOStatement Resultado de la consulta
     */
    public function readUnreadByUser($usuario_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE usuario_id = :usuario_id AND leida = FALSE 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Contar notificaciones no leídas de un usuario
     * 
     * @param int $usuario_id ID del usuario
     * @return int Cantidad de notificaciones no leídas
     */
    public function countUnreadByUser($usuario_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE usuario_id = :usuario_id AND leida = FALSE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Marcar una notificación como leída
     * 
     * Actualiza el campo 'leida' a TRUE para la notificación especificada.
     * El ID debe estar previamente asignado a la propiedad $this->id.
     * 
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function markAsRead() {
        $query = "UPDATE " . $this->table_name . " SET leida = TRUE WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Buscar notificación duplicada
     * 
     * Verifica si existe una notificación con el mismo usuario_id, título y estado de lectura.
     * Se utiliza para prevenir la creación de notificaciones duplicadas.
     * 
     * @param int $usuario_id ID del usuario
     * @param string $titulo Título de la notificación
     * @param bool $leida Estado de lectura (default: false)
     * @return array|null Datos de la notificación si existe, null si no existe
     */
    public function findDuplicate($usuario_id, $titulo, $leida = false) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE usuario_id = :usuario_id AND titulo = :titulo AND leida = :leida 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":leida", $leida, PDO::PARAM_BOOL);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    /**
     * Leer todas las notificaciones con información de usuarios (admin)
     * 
     * Obtiene todas las notificaciones con datos relacionados de usuarios y residentes.
     * Permite aplicar filtros opcionales.
     * 
     * @param array $filters Filtros opcionales: usuario_id, tipo, leida
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll($filters = []) {
        $query = "SELECT n.*, u.nombre, u.email, r.apartamento, r.piso, r.torre
                  FROM " . $this->table_name . " n
                  LEFT JOIN usuarios u ON n.usuario_id = u.id
                  LEFT JOIN residentes r ON u.id = r.usuario_id
                  WHERE 1=1";
        
        // Aplicar filtros
        if (!empty($filters['usuario_id'])) {
            $query .= " AND n.usuario_id = :usuario_id";
        }
        
        if (!empty($filters['tipo'])) {
            $query .= " AND n.tipo = :tipo";
        }
        
        if (isset($filters['leida']) && $filters['leida'] !== '') {
            $query .= " AND n.leida = :leida";
        }
        
        $query .= " ORDER BY n.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind filtros
        if (!empty($filters['usuario_id'])) {
            $stmt->bindParam(":usuario_id", $filters['usuario_id']);
        }
        
        if (!empty($filters['tipo'])) {
            $stmt->bindParam(":tipo", $filters['tipo']);
        }
        
        if (isset($filters['leida']) && $filters['leida'] !== '') {
            $stmt->bindParam(":leida", $filters['leida'], PDO::PARAM_BOOL);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Obtener estadísticas de notificaciones
     * 
     * Calcula estadísticas generales: total, leídas, no leídas y por tipo.
     * 
     * @return array Estadísticas de notificaciones
     */
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_notificaciones,
                    SUM(CASE WHEN leida = TRUE THEN 1 ELSE 0 END) as notificaciones_leidas,
                    SUM(CASE WHEN leida = FALSE THEN 1 ELSE 0 END) as notificaciones_no_leidas,
                    SUM(CASE WHEN tipo = 'info' THEN 1 ELSE 0 END) as tipo_info,
                    SUM(CASE WHEN tipo = 'warning' THEN 1 ELSE 0 END) as tipo_warning,
                    SUM(CASE WHEN tipo = 'success' THEN 1 ELSE 0 END) as tipo_success,
                    SUM(CASE WHEN tipo = 'error' THEN 1 ELSE 0 END) as tipo_error
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
