<?php
/**
 * Modelo de Cuentas Bancarias
 * 
 * Gestiona las cuentas bancarias del condominio utilizadas para recibir
 * pagos en línea (Pago Móvil BCV y Transferencia Bancaria).
 * 
 * @package App\Models
 * @author Jesús H. Marcano V.
 * @version 1.0.0
 */

class BankAccount {
    private $conn;
    private $table_name = "cuentas_bancarias";

    public $id;
    public $banco;
    public $tipo;
    public $numero_cuenta;
    public $titular;
    public $pago_movil_telefono;
    public $pago_movil_cedula;
    public $activa;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear una nueva cuenta bancaria
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (banco, tipo, numero_cuenta, titular, pago_movil_telefono, pago_movil_cedula, activa) 
                  VALUES (:banco, :tipo, :numero_cuenta, :titular, :pago_movil_telefono, :pago_movil_cedula, :activa)";

        $stmt = $this->conn->prepare($query);

        $this->banco = htmlspecialchars(strip_tags($this->banco));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->numero_cuenta = htmlspecialchars(strip_tags($this->numero_cuenta));
        $this->titular = htmlspecialchars(strip_tags($this->titular));
        $this->pago_movil_telefono = htmlspecialchars(strip_tags($this->pago_movil_telefono ?? ''));
        $this->pago_movil_cedula = htmlspecialchars(strip_tags($this->pago_movil_cedula ?? ''));
        $this->activa = isset($this->activa) ? $this->activa : true;

        $stmt->bindParam(":banco", $this->banco);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":numero_cuenta", $this->numero_cuenta);
        $stmt->bindParam(":titular", $this->titular);
        $stmt->bindParam(":pago_movil_telefono", $this->pago_movil_telefono);
        $stmt->bindParam(":pago_movil_cedula", $this->pago_movil_cedula);
        $stmt->bindParam(":activa", $this->activa, PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Leer todas las cuentas bancarias
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY activa DESC, banco ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Leer una cuenta bancaria por ID
     * 
     * @return array|false Datos de la cuenta o false si no existe
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar una cuenta bancaria existente
     * 
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET banco = :banco, tipo = :tipo, numero_cuenta = :numero_cuenta, titular = :titular, 
                      pago_movil_telefono = :pago_movil_telefono, pago_movil_cedula = :pago_movil_cedula, activa = :activa
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->banco = htmlspecialchars(strip_tags($this->banco));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->numero_cuenta = htmlspecialchars(strip_tags($this->numero_cuenta));
        $this->titular = htmlspecialchars(strip_tags($this->titular));
        $this->pago_movil_telefono = htmlspecialchars(strip_tags($this->pago_movil_telefono ?? ''));
        $this->pago_movil_cedula = htmlspecialchars(strip_tags($this->pago_movil_cedula ?? ''));
        $this->activa = isset($this->activa) ? $this->activa : true;

        $stmt->bindParam(":banco", $this->banco);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":numero_cuenta", $this->numero_cuenta);
        $stmt->bindParam(":titular", $this->titular);
        $stmt->bindParam(":pago_movil_telefono", $this->pago_movil_telefono);
        $stmt->bindParam(":pago_movil_cedula", $this->pago_movil_cedula);
        $stmt->bindParam(":activa", $this->activa, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar una cuenta bancaria
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
     * Obtener cuentas bancarias activas
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function getActive() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE activa = TRUE 
                  ORDER BY banco ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>