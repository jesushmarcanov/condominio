<?php
/**
 * Modelo de Reglas de Mora
 * 
 * Gestiona las operaciones CRUD de reglas de mora en la base de datos.
 * Las reglas de mora definen cómo se calculan los recargos por pagos atrasados,
 * incluyendo porcentajes, montos fijos, frecuencias y topes máximos.
 * 
 * Tipos de recargo:
 * - porcentaje: Recargo calculado como porcentaje del monto original
 * - monto_fijo: Recargo de monto fijo independiente del monto original
 * 
 * Frecuencias:
 * - unica: Se aplica una sola vez
 * - diaria: Se aplica por cada día de atraso
 * - semanal: Se aplica por cada semana de atraso
 * - mensual: Se aplica por cada mes de atraso
 * 
 * @package App\Models
 * @author Sistema de Gestión de Condominio
 * @version 1.0.0
 */

class LateFeeRule {
    private $conn;
    private $table_name = "late_fee_rules";

    public $id;
    public $nombre;
    public $dias_gracia;
    public $tipo_recargo;
    public $valor_recargo;
    public $frecuencia;
    public $tope_maximo;
    public $tipo_pago;
    public $activa;
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
     * Crear una nueva regla de mora
     * 
     * Inserta un nuevo registro de regla de mora en la base de datos.
     * Los datos deben estar previamente asignados a las propiedades públicas.
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, dias_gracia, tipo_recargo, valor_recargo, frecuencia, tope_maximo, tipo_pago, activa) 
                  VALUES (:nombre, :dias_gracia, :tipo_recargo, :valor_recargo, :frecuencia, :tope_maximo, :tipo_pago, :activa)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->dias_gracia = htmlspecialchars(strip_tags($this->dias_gracia));
        $this->tipo_recargo = htmlspecialchars(strip_tags($this->tipo_recargo));
        $this->valor_recargo = htmlspecialchars(strip_tags($this->valor_recargo));
        $this->frecuencia = htmlspecialchars(strip_tags($this->frecuencia));
        $this->tope_maximo = $this->tope_maximo ? htmlspecialchars(strip_tags($this->tope_maximo)) : null;
        $this->tipo_pago = $this->tipo_pago ? htmlspecialchars(strip_tags($this->tipo_pago)) : null;
        $this->activa = isset($this->activa) ? $this->activa : true;
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":dias_gracia", $this->dias_gracia);
        $stmt->bindParam(":tipo_recargo", $this->tipo_recargo);
        $stmt->bindParam(":valor_recargo", $this->valor_recargo);
        $stmt->bindParam(":frecuencia", $this->frecuencia);
        $stmt->bindParam(":tope_maximo", $this->tope_maximo);
        $stmt->bindParam(":tipo_pago", $this->tipo_pago);
        $stmt->bindParam(":activa", $this->activa);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Leer todas las reglas de mora
     * 
     * Obtiene todas las reglas de mora ordenadas por fecha de creación.
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer una regla de mora específica por ID
     * 
     * Obtiene los datos completos de una regla de mora.
     * El ID debe estar previamente asignado a la propiedad $this->id.
     * 
     * @return array|false Datos de la regla o false si no existe
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar una regla de mora existente
     * 
     * Actualiza todos los campos de la regla especificada.
     * Los datos deben estar previamente asignados a las propiedades públicas.
     * 
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, 
                      dias_gracia = :dias_gracia, 
                      tipo_recargo = :tipo_recargo, 
                      valor_recargo = :valor_recargo, 
                      frecuencia = :frecuencia, 
                      tope_maximo = :tope_maximo, 
                      tipo_pago = :tipo_pago, 
                      activa = :activa 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->dias_gracia = htmlspecialchars(strip_tags($this->dias_gracia));
        $this->tipo_recargo = htmlspecialchars(strip_tags($this->tipo_recargo));
        $this->valor_recargo = htmlspecialchars(strip_tags($this->valor_recargo));
        $this->frecuencia = htmlspecialchars(strip_tags($this->frecuencia));
        $this->tope_maximo = $this->tope_maximo ? htmlspecialchars(strip_tags($this->tope_maximo)) : null;
        $this->tipo_pago = $this->tipo_pago ? htmlspecialchars(strip_tags($this->tipo_pago)) : null;
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":dias_gracia", $this->dias_gracia);
        $stmt->bindParam(":tipo_recargo", $this->tipo_recargo);
        $stmt->bindParam(":valor_recargo", $this->valor_recargo);
        $stmt->bindParam(":frecuencia", $this->frecuencia);
        $stmt->bindParam(":tope_maximo", $this->tope_maximo);
        $stmt->bindParam(":tipo_pago", $this->tipo_pago);
        $stmt->bindParam(":activa", $this->activa);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Eliminar una regla de mora
     * 
     * Elimina la regla especificada de la base de datos.
     * El ID debe estar previamente asignado a la propiedad $this->id.
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
     * Obtener reglas de mora activas
     * 
     * Obtiene todas las reglas de mora que están activas.
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function getActiveRules() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE activa = 1 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Obtener regla para un tipo de pago específico
     * 
     * Busca una regla activa específica para un tipo de pago.
     * 
     * @param string $tipo_pago Tipo de pago a buscar
     * @return array|false Datos de la regla o false si no existe
     */
    public function getRuleForPaymentType($tipo_pago) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE tipo_pago = :tipo_pago 
                  AND activa = 1 
                  ORDER BY created_at DESC 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tipo_pago", $tipo_pago);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener regla global (aplicable a todos los tipos de pago)
     * 
     * Busca una regla activa con tipo_pago NULL (regla global).
     * 
     * @return array|false Datos de la regla o false si no existe
     */
    public function getGlobalRule() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE tipo_pago IS NULL 
                  AND activa = 1 
                  ORDER BY created_at DESC 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si la regla puede ser eliminada
     * 
     * Una regla no puede ser eliminada si tiene mora aplicada en pagos.
     * 
     * @return bool True si puede ser eliminada, false si tiene mora aplicada
     */
    public function canDelete() {
        $query = "SELECT COUNT(*) as count FROM pagos 
                  WHERE regla_mora_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    }

    /**
     * Activar una regla de mora
     * 
     * Cambia el estado de la regla a activa.
     * 
     * @return bool True si se activó exitosamente, false en caso contrario
     */
    public function activate() {
        $query = "UPDATE " . $this->table_name . " 
                  SET activa = 1 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Desactivar una regla de mora
     * 
     * Cambia el estado de la regla a inactiva.
     * Las reglas inactivas no se aplican en cálculos automáticos.
     * 
     * @return bool True si se desactivó exitosamente, false en caso contrario
     */
    public function deactivate() {
        $query = "UPDATE " . $this->table_name . " 
                  SET activa = 0 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>
