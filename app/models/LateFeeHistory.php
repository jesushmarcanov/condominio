<?php
/**
 * Modelo de Historial de Mora
 * 
 * Mantiene registro de auditoría de todos los cálculos y ajustes de mora.
 * Proporciona trazabilidad completa de las operaciones realizadas sobre
 * los recargos por mora, incluyendo cálculos automáticos, ajustes manuales
 * y eliminaciones.
 * 
 * Tipos de operación:
 * - calculo_automatico: Cálculo realizado por el script cron
 * - ajuste_manual: Modificación realizada por un administrador
 * - eliminacion: Eliminación de mora previamente aplicada
 * 
 * @package App\Models
 * @author Sistema de Gestión de Condominio
 * @version 1.0.0
 */

class LateFeeHistory {
    private $conn;
    private $table_name = "late_fee_history";

    public $id;
    public $pago_id;
    public $regla_mora_id;
    public $monto_calculado;         // Monto calculado por el algoritmo
    public $monto_aplicado;          // Monto realmente aplicado
    public $dias_atraso;
    public $tipo_operacion;          // 'calculo_automatico', 'ajuste_manual', 'eliminacion'
    public $usuario_id;              // Usuario que realizó la operación (nullable)
    public $justificacion;           // Justificación para ajustes manuales
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
     * Crear un nuevo registro de historial
     * 
     * Inserta un nuevo registro de auditoría en la base de datos.
     * Los datos deben estar previamente asignados a las propiedades públicas.
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (pago_id, regla_mora_id, monto_calculado, monto_aplicado, dias_atraso, tipo_operacion, usuario_id, justificacion) 
                  VALUES (:pago_id, :regla_mora_id, :monto_calculado, :monto_aplicado, :dias_atraso, :tipo_operacion, :usuario_id, :justificacion)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->pago_id = htmlspecialchars(strip_tags($this->pago_id));
        $this->regla_mora_id = $this->regla_mora_id ? htmlspecialchars(strip_tags($this->regla_mora_id)) : null;
        $this->monto_calculado = htmlspecialchars(strip_tags($this->monto_calculado));
        $this->monto_aplicado = htmlspecialchars(strip_tags($this->monto_aplicado));
        $this->dias_atraso = htmlspecialchars(strip_tags($this->dias_atraso));
        $this->tipo_operacion = htmlspecialchars(strip_tags($this->tipo_operacion));
        $this->usuario_id = $this->usuario_id ? htmlspecialchars(strip_tags($this->usuario_id)) : null;
        $this->justificacion = $this->justificacion ? htmlspecialchars(strip_tags($this->justificacion)) : null;
        
        // Bind parameters
        $stmt->bindParam(":pago_id", $this->pago_id);
        $stmt->bindParam(":regla_mora_id", $this->regla_mora_id);
        $stmt->bindParam(":monto_calculado", $this->monto_calculado);
        $stmt->bindParam(":monto_aplicado", $this->monto_aplicado);
        $stmt->bindParam(":dias_atraso", $this->dias_atraso);
        $stmt->bindParam(":tipo_operacion", $this->tipo_operacion);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":justificacion", $this->justificacion);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Obtener historial de mora por ID de pago
     * 
     * Obtiene todos los registros de historial asociados a un pago específico,
     * ordenados por fecha de creación descendente (más reciente primero).
     * 
     * @param int $payment_id ID del pago
     * @return PDOStatement Resultado de la consulta
     */
    public function getByPaymentId($payment_id) {
        $query = "SELECT h.*, 
                         r.nombre as regla_nombre,
                         u.nombre as usuario_nombre
                  FROM " . $this->table_name . " h
                  LEFT JOIN late_fee_rules r ON h.regla_mora_id = r.id
                  LEFT JOIN usuarios u ON h.usuario_id = u.id
                  WHERE h.pago_id = :payment_id
                  ORDER BY h.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_id", $payment_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Obtener historial reciente de mora
     * 
     * Obtiene los registros más recientes de historial de mora,
     * útil para auditoría y reportes.
     * 
     * @param int $limit Número máximo de registros a retornar (default: 100)
     * @return PDOStatement Resultado de la consulta
     */
    public function getRecentHistory($limit = 100) {
        $query = "SELECT h.*, 
                         p.concepto as pago_concepto,
                         p.monto_original as pago_monto,
                         r.nombre as regla_nombre,
                         u.nombre as usuario_nombre,
                         res.apartamento as residente_apartamento
                  FROM " . $this->table_name . " h
                  LEFT JOIN pagos p ON h.pago_id = p.id
                  LEFT JOIN late_fee_rules r ON h.regla_mora_id = r.id
                  LEFT JOIN usuarios u ON h.usuario_id = u.id
                  LEFT JOIN residentes res ON p.residente_id = res.id
                  ORDER BY h.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
