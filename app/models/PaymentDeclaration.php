<?php
/**
 * Modelo de Declaraciones de Pago
 * 
 * Gestiona las declaraciones de pago en línea (Pago Móvil BCV y Transferencia
 * Bancaria) que los residentes registran y que el administrador debe confirmar
 * o rechazar.
 * 
 * @package App\Models
 * @author Jesús H. Marcano V.
 * @version 1.0.0
 */

class PaymentDeclaration {
    private $conn;
    private $table_name = "declaraciones_pago";

    public $id;
    public $pago_id;
    public $metodo;
    public $banco_origen;
    public $referencia_bancaria;
    public $monto_declarado;
    public $moneda;
    public $monto_moneda_original;
    public $fecha_operacion;
    public $hora_operacion;
    public $estado;
    public $declarado_por;
    public $revisado_por;
    public $notas_admin;
    public $fecha_declaracion;
    public $fecha_revision;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear una nueva declaración de pago
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (pago_id, metodo, banco_origen, referencia_bancaria, monto_declarado, moneda, monto_moneda_original, fecha_operacion, hora_operacion, estado, declarado_por) 
                  VALUES (:pago_id, :metodo, :banco_origen, :referencia_bancaria, :monto_declarado, :moneda, :monto_moneda_original, :fecha_operacion, :hora_operacion, :estado, :declarado_por)";

        $stmt = $this->conn->prepare($query);

        $this->metodo = htmlspecialchars(strip_tags($this->metodo));
        $this->banco_origen = htmlspecialchars(strip_tags($this->banco_origen));
        $this->referencia_bancaria = htmlspecialchars(strip_tags($this->referencia_bancaria));
        $this->estado = isset($this->estado) ? $this->estado : 'pendiente';
        $this->moneda = isset($this->moneda) ? $this->moneda : 'USD';
        $this->monto_moneda_original = isset($this->monto_moneda_original) ? $this->monto_moneda_original : null;

        $stmt->bindParam(":pago_id", $this->pago_id);
        $stmt->bindParam(":metodo", $this->metodo);
        $stmt->bindParam(":banco_origen", $this->banco_origen);
        $stmt->bindParam(":referencia_bancaria", $this->referencia_bancaria);
        $stmt->bindParam(":monto_declarado", $this->monto_declarado);
        $stmt->bindParam(":moneda", $this->moneda);
        $stmt->bindParam(":monto_moneda_original", $this->monto_moneda_original);
        $stmt->bindParam(":fecha_operacion", $this->fecha_operacion);
        $stmt->bindParam(":hora_operacion", $this->hora_operacion);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":declarado_por", $this->declarado_por);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Leer todas las declaraciones con información relacionada
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll() {
        $query = "SELECT d.*, p.concepto, p.mes_pago, p.monto, p.monto_original, p.monto_mora,
                         r.apartamento, u.nombre, u.email,
                         du.nombre as declarado_nombre,
                         ru.nombre as revisado_nombre
                  FROM " . $this->table_name . " d
                  LEFT JOIN pagos p ON d.pago_id = p.id
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios du ON d.declarado_por = du.id
                  LEFT JOIN usuarios ru ON d.revisado_por = ru.id
                  ORDER BY d.fecha_declaracion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Leer declaraciones pendientes
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function getPending() {
        $query = "SELECT d.*, p.concepto, p.mes_pago, p.monto, p.monto_original, p.monto_mora,
                         r.apartamento, u.nombre, u.email,
                         du.nombre as declarado_nombre
                  FROM " . $this->table_name . " d
                  LEFT JOIN pagos p ON d.pago_id = p.id
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios du ON d.declarado_por = du.id
                  WHERE d.estado = 'pendiente'
                  ORDER BY d.fecha_declaracion ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Leer las declaraciones de un pago específico
     * 
     * @param int $pago_id ID del pago
     * @return PDOStatement Resultado de la consulta
     */
    public function getByPayment($pago_id) {
        $query = "SELECT d.*, du.nombre as declarado_nombre, ru.nombre as revisado_nombre
                  FROM " . $this->table_name . " d
                  LEFT JOIN usuarios du ON d.declarado_por = du.id
                  LEFT JOIN usuarios ru ON d.revisado_por = ru.id
                  WHERE d.pago_id = :pago_id
                  ORDER BY d.fecha_declaracion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pago_id", $pago_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Leer una declaración por ID
     * 
     * @return array|false Datos de la declaración o false si no existe
     */
    public function readOne() {
        $query = "SELECT d.*, p.concepto, p.mes_pago, p.monto, p.monto_original, p.monto_mora, p.estado as pago_estado,
                         r.apartamento, u.nombre, u.email, u.id as usuario_id,
                         du.nombre as declarado_nombre
                  FROM " . $this->table_name . " d
                  LEFT JOIN pagos p ON d.pago_id = p.id
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios du ON d.declarado_por = du.id
                  WHERE d.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Confirmar una declaración de pago
     * 
     * @param int $revisado_por ID del usuario (admin) que confirma
     * @param string|null $notas Notas del administrador
     * @return bool True si se actualizó exitosamente
     */
    public function confirm($revisado_por, $notas = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = 'confirmada', revisado_por = :revisado_por, notas_admin = :notas, fecha_revision = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":revisado_por", $revisado_por);
        $stmt->bindParam(":notas", $notas);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Rechazar una declaración de pago
     * 
     * @param int $revisado_por ID del usuario (admin) que rechaza
     * @param string|null $notas Motivo del rechazo
     * @return bool True si se actualizó exitosamente
     */
    public function reject($revisado_por, $notas = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = 'rechazada', revisado_por = :revisado_por, notas_admin = :notas, fecha_revision = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":revisado_por", $revisado_por);
        $stmt->bindParam(":notas", $notas);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Obtener estadísticas de declaraciones
     * 
     * @return array Estadísticas de declaraciones
     */
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_declaraciones,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas
                  FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $stats['total_confirmado'] = 0.00;
        $stats['total_confirmado_moneda'] = [];

        // Sumar los montos confirmados en su equivalente a la moneda base
        // (las declaraciones pueden estar en USD o en Bs).
        $amounts = "SELECT moneda, monto_declarado FROM " . $this->table_name . " WHERE estado = 'confirmada'";
        $stmt_amounts = $this->conn->prepare($amounts);
        $stmt_amounts->execute();
        while ($row = $stmt_amounts->fetch(PDO::FETCH_ASSOC)) {
            $base_amount = convertCurrency((float)$row['monto_declarado'], $row['moneda'] ?: 'USD', baseCurrency());
            $stats['total_confirmado'] += $base_amount;
            $ccy = $row['moneda'] ?: 'USD';
            if (!isset($stats['total_confirmado_moneda'][$ccy])) {
                $stats['total_confirmado_moneda'][$ccy] = 0.00;
            }
            $stats['total_confirmado_moneda'][$ccy] += (float)$row['monto_declarado'];
        }

        return $stats;
    }
}
?>