<?php
/**
 * Modelo de Configuración General
 * 
 * Gestiona la tabla clave/valor `configuracion` que almacena la configuración
 * global de la aplicación (moneda base, tasa de cambio, preferencias, etc.).
 * 
 * @package App\Models
 * @author Jesús H. Marcano V.
 * @version 1.0.0
 */

class AppSetting {
    private $conn;
    private $table_name = "configuracion";

    public $id;
    public $clave;
    public $valor;
    public $descripcion;
    public $actualizado_por;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Leer todas las configuraciones
     * 
     * @return array<string, string|null> Clave => valor
     */
    public function readAll() {
        $query = "SELECT s.clave, s.valor, s.descripcion, u.nombre as actualizado_nombre,
                         s.fecha_actualizacion
                  FROM " . $this->table_name . " s
                  LEFT JOIN usuarios u ON s.actualizado_por = u.id
                  ORDER BY s.clave ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Leer una configuración por clave
     * 
     * @param string $clave Clave a buscar
     * @return string|null Valor de la configuración o null si no existe
     */
    public function get($clave) {
        $query = "SELECT valor FROM " . $this->table_name . " WHERE clave = :clave LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":clave", $clave);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['valor'] : null;
    }

    /**
     * Establecer o actualizar una configuración (UPSERT)
     * 
     * @param string $clave Clave de la configuración
     * @param mixed $valor Valor a guardar
     * @param int|null $usuario_id Usuario que realiza el cambio
     * @return bool True si se guardó exitosamente
     */
    public function set($clave, $valor, $usuario_id = null) {
        $valor = $valor === null ? null : (string)$valor;

        $query = "INSERT INTO " . $this->table_name . " (clave, valor, actualizado_por) VALUES (:clave, :valor, :usuario_id)
                  ON DUPLICATE KEY UPDATE valor = VALUES(valor), actualizado_por = VALUES(actualizado_por)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":clave", $clave);
        $stmt->bindParam(":valor", $valor);
        $stmt->bindParam(":usuario_id", $usuario_id, $usuario_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

        return $stmt->execute();
    }
}
?>