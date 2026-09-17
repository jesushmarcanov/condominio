<?php
/**
 * Modelo de Medios de Pago
 *
 * Gestiona el catálogo de formas o medios de pago (tabla `medios_pagos`).
 * Cada medio tiene un `valor` (slug que se guarda en `pagos.metodo_pago`)
 * y un `nombre` visible en el select del formulario de pagos.
 *
 * @package App\Models
 * @version 1.0.0
 */

class MedioPago {
    private $conn;
    private $table_name = "medios_pagos";

    public $id;
    public $valor;
    public $nombre;
    public $activa;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear un nuevo medio de pago
     *
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (valor, nombre, activa)
                  VALUES (:valor, :nombre, :activa)";

        $stmt = $this->conn->prepare($query);

        $this->valor = htmlspecialchars(strip_tags($this->valor));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->activa = isset($this->activa) ? $this->activa : true;

        $stmt->bindParam(":valor", $this->valor);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":activa", $this->activa, PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Leer todos los medios de pago
     *
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY activa DESC, nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Leer un medio de pago por ID
     *
     * @return array|false Datos del medio o false si no existe
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar un medio de pago existente
     *
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET valor = :valor, nombre = :nombre, activa = :activa
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->valor = htmlspecialchars(strip_tags($this->valor));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->activa = isset($this->activa) ? $this->activa : true;

        $stmt->bindParam(":valor", $this->valor);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":activa", $this->activa, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar un medio de pago
     *
     * @return bool True si se eliminó exitosamente, false en caso contrario
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Obtener medios de pago activos (los que se muestran en el select de pagos)
     *
     * @return PDOStatement Resultado de la consulta
     */
    public function getActive() {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE activa = TRUE
                  ORDER BY nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>