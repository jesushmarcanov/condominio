<?php
/**
 * Modelo de Pagos
 * 
 * Gestiona las operaciones CRUD de pagos en la base de datos.
 * Los pagos representan las cuotas de mantenimiento y otros conceptos
 * que los residentes deben pagar al condominio.
 * 
 * Estados de pago:
 * - pendiente: Pago registrado pero no realizado
 * - atrasado: Pago pendiente cuya fecha de vencimiento ha pasado
 * - pagado: Pago completado
 * 
 * @package App\Models
 * @author Jesús H. Marcano V.
 * @version 1.0.0
 */

class Payment {
    private $conn;
    private $table_name = "pagos";

    public $id;
    public $residente_id;
    public $monto;
    public $concepto;
    public $mes_pago;
    public $fecha_pago;
    public $metodo_pago;
    public $referencia;
    public $estado;
    public $moneda;
    public $monto_moneda_original;
    public $created_at;
    public $updated_at;
    
    // Late fee properties
    public $monto_original;
    public $monto_mora;
    public $fecha_aplicacion_mora;
    public $regla_mora_id;

    /**
     * Constructor
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear un nuevo pago
     * 
     * Inserta un nuevo registro de pago en la base de datos.
     * Los datos deben estar previamente asignados a las propiedades públicas.
     * 
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (residente_id, monto, concepto, mes_pago, fecha_pago, metodo_pago, referencia, estado, moneda, monto_moneda_original) VALUES (:residente_id, :monto, :concepto, :mes_pago, :fecha_pago, :metodo_pago, :referencia, :estado, :moneda, :monto_moneda_original)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->residente_id = htmlspecialchars(strip_tags($this->residente_id));
        $this->monto = htmlspecialchars(strip_tags($this->monto));
        $this->concepto = htmlspecialchars(strip_tags($this->concepto));
        $this->mes_pago = htmlspecialchars(strip_tags($this->mes_pago));
        $this->fecha_pago = htmlspecialchars(strip_tags($this->fecha_pago));
        $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
        $this->referencia = htmlspecialchars(strip_tags($this->referencia));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->moneda = $this->moneda !== null ? htmlspecialchars(strip_tags($this->moneda)) : 'USD';
        $this->monto_moneda_original = $this->monto_moneda_original !== null ? htmlspecialchars(strip_tags($this->monto_moneda_original)) : null;
        
        // Bind parameters
        $stmt->bindParam(":residente_id", $this->residente_id);
        $stmt->bindParam(":monto", $this->monto);
        $stmt->bindParam(":concepto", $this->concepto);
        $stmt->bindParam(":mes_pago", $this->mes_pago);
        $stmt->bindParam(":fecha_pago", $this->fecha_pago);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":referencia", $this->referencia);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":moneda", $this->moneda);
        $stmt->bindParam(":monto_moneda_original", $this->monto_moneda_original);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Leer todos los pagos con información de residentes
     * 
     * Obtiene todos los pagos con datos relacionados de residentes, usuarios
     * y campos de mora (monto_original, monto_mora, fecha_aplicacion_mora, regla_mora_id).
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function readAll() {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email,
                  p.monto_original, p.monto_mora, p.fecha_aplicacion_mora, p.regla_mora_id
                  FROM " . $this->table_name . " p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer pagos de un residente específico
     * 
     * @param int $residente_id ID del residente
     * @return PDOStatement Resultado de la consulta
     */
    public function readByResident($residente_id) {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email 
                  FROM " . $this->table_name . " p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.residente_id = :residente_id
                  ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":residente_id", $residente_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Leer un pago específico por ID
     * 
     * Obtiene los datos completos de un pago incluyendo información del residente
     * y campos de mora (monto_original, monto_mora, fecha_aplicacion_mora, regla_mora_id).
     * El ID debe estar previamente asignado a la propiedad $this->id.
     * 
     * @return array|false Datos del pago o false si no existe
     */
    public function readOne() {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email,
                  p.monto_original, p.monto_mora, p.fecha_aplicacion_mora, p.regla_mora_id
                  FROM " . $this->table_name . " p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar un pago existente
     * 
     * Actualiza todos los campos del pago especificado.
     * Los datos deben estar previamente asignados a las propiedades públicas.
     * 
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET residente_id = :residente_id, monto = :monto, concepto = :concepto, mes_pago = :mes_pago, fecha_pago = :fecha_pago, metodo_pago = :metodo_pago, referencia = :referencia, estado = :estado, moneda = COALESCE(:moneda, moneda), monto_moneda_original = COALESCE(:monto_moneda_original, monto_moneda_original) WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->residente_id = htmlspecialchars(strip_tags($this->residente_id));
        $this->monto = htmlspecialchars(strip_tags($this->monto));
        $this->concepto = htmlspecialchars(strip_tags($this->concepto));
        $this->mes_pago = htmlspecialchars(strip_tags($this->mes_pago));
        $this->fecha_pago = htmlspecialchars(strip_tags($this->fecha_pago));
        $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
        $this->referencia = htmlspecialchars(strip_tags($this->referencia));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->moneda = $this->moneda !== null ? htmlspecialchars(strip_tags($this->moneda)) : null;
        $this->monto_moneda_original = $this->monto_moneda_original !== null ? htmlspecialchars(strip_tags($this->monto_moneda_original)) : null;
        
        // Bind parameters
        $stmt->bindParam(":residente_id", $this->residente_id);
        $stmt->bindParam(":monto", $this->monto);
        $stmt->bindParam(":concepto", $this->concepto);
        $stmt->bindParam(":mes_pago", $this->mes_pago);
        $stmt->bindParam(":fecha_pago", $this->fecha_pago);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":referencia", $this->referencia);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":moneda", $this->moneda);
        $stmt->bindParam(":monto_moneda_original", $this->monto_moneda_original);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Eliminar un pago
     * 
     * Elimina el pago especificado de la base de datos.
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
     * Obtener pagos pendientes o atrasados de un residente
     * 
     * @param int $residente_id ID del residente
     * @return PDOStatement Resultado de la consulta
     */
    public function getPendingForResident($residente_id) {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email
                  FROM " . $this->table_name . " p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.residente_id = :residente_id
                  AND (p.estado = 'pendiente' OR p.estado = 'atrasado')
                  ORDER BY p.fecha_pago ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":residente_id", $residente_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtener pagos pendientes o atrasados
     * 
     * Obtiene todos los pagos con estado 'pendiente' o 'atrasado'.
     * Utilizado por el sistema de notificaciones para detectar pagos vencidos.
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function getPendingPayments() {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email 
                  FROM " . $this->table_name . " p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.estado = 'pendiente' OR p.estado = 'atrasado'
                  ORDER BY p.fecha_pago ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Obtener pagos de un mes específico
     * 
     * @param string $month Mes en formato YYYY-MM
     * @return PDOStatement Resultado de la consulta
     */
    public function getPaymentsByMonth($month) {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email 
                  FROM " . $this->table_name . " p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.mes_pago = :month
                  ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":month", $month);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Obtener estadísticas de pagos
     * 
     * Calcula estadísticas generales: total de pagos, ingresos, pagos por estado, etc.
     * 
     * @return array Estadísticas de pagos
     */
    public function getStats() {
        // Las filas pueden estar en USD o VES (moneda del momento de creación),
        // por lo que las agregamos en PHP convirtiendo cada monto a la moneda base
        // con la tasa vigente, en lugar de sumar valores en monedas mezcladas.
        $rows = $this->conn->query(
            "SELECT monto, moneda, estado, created_at FROM " . $this->table_name
        )->fetchAll(PDO::FETCH_ASSOC);

        $base = function_exists('baseCurrency') ? baseCurrency() : 'USD';

        $stats = [
            'total_pagos' => 0,
            'total_ingresos' => 0.0,
            'total_pagado' => 0.0,
            'total_pendiente' => 0.0,
            'total_atrasado' => 0.0,
            'pagos_realizados' => 0,
            'pagos_pendientes' => 0,
            'pagos_atrasados' => 0,
            'pagos_30_dias' => 0,
        ];

        $treinta_dias = strtotime('-30 days');

        foreach ($rows as $row) {
            $stats['total_pagos']++;
            $ccy = !empty($row['moneda']) ? $row['moneda'] : $base;
            $monto = function_exists('convertCurrency')
                ? convertCurrency((float)$row['monto'], $ccy, $base)
                : (float)$row['monto'];

            $stats['total_ingresos'] += $monto;

            if ($row['estado'] === 'pagado') {
                $stats['total_pagado'] += $monto;
                $stats['pagos_realizados']++;
            } elseif ($row['estado'] === 'pendiente') {
                $stats['total_pendiente'] += $monto;
                $stats['pagos_pendientes']++;
            } elseif ($row['estado'] === 'atrasado') {
                $stats['total_atrasado'] += $monto;
                $stats['pagos_atrasados']++;
            }

            if (strtotime($row['created_at']) >= $treinta_dias) {
                $stats['pagos_30_dias']++;
            }
        }

        return $stats;
    }

    /**
     * Obtener ingresos mensuales
     * 
     * Calcula los ingresos totales por mes para los últimos N meses.
     * 
     * @param int $months Número de meses a consultar (default: 12)
     * @return array Ingresos mensuales agrupados por mes
     */
    public function getMonthlyIncome($months = 12) {
        $stmt = $this->conn->prepare(
            "SELECT monto, moneda, fecha_pago FROM " . $this->table_name . "
             WHERE estado = 'pagado'
             AND fecha_pago >= DATE_SUB(NOW(), INTERVAL :months MONTH)"
        );
        $stmt->bindParam(":months", $months);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $base = function_exists('baseCurrency') ? baseCurrency() : 'USD';

        $meses = [];
        foreach ($rows as $row) {
            $mes = date('Y-m', strtotime($row['fecha_pago']));
            $ccy = !empty($row['moneda']) ? $row['moneda'] : $base;
            $monto = function_exists('convertCurrency')
                ? convertCurrency((float)$row['monto'], $ccy, $base)
                : (float)$row['monto'];

            if (!isset($meses[$mes])) {
                $meses[$mes] = ['mes' => $mes, 'ingresos' => 0.0, 'cantidad_pagos' => 0];
            }
            $meses[$mes]['ingresos'] += $monto;
            $meses[$mes]['cantidad_pagos']++;
        }

        $result = array_values($meses);
        usort($result, function ($a, $b) {
            return strcmp($a['mes'], $b['mes']);
        });

        return $result;
    }

    /**
     * Verificar si ya existe un pago para el mismo mes y residente
     * 
     * Previene la creación de pagos duplicados para el mismo residente y mes.
     * 
     * @return bool True si existe un pago duplicado, false si no existe
     */
    public function paymentExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE residente_id = :residente_id AND mes_pago = :mes_pago AND id != :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":residente_id", $this->residente_id);
        $stmt->bindParam(":mes_pago", $this->mes_pago);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtener el monto total del pago (original + mora)
     * 
     * Calcula el monto total sumando el monto original y el monto de mora.
     * Si monto_original no está definido, usa el campo monto como fallback.
     * 
     * @return float Monto total del pago
     */
    public function getMonto_total() {
        $original = $this->monto_original ?? $this->monto ?? 0;
        $mora = $this->monto_mora ?? 0;
        return floatval($original) + floatval($mora);
    }
    
    /**
     * Verificar si el pago tiene mora aplicada
     * 
     * @return bool True si el pago tiene mora, false en caso contrario
     */
    public function hasLateFee() {
        return isset($this->monto_mora) && floatval($this->monto_mora) > 0;
    }
    
    /**
     * Obtener el porcentaje de mora respecto al monto original
     * 
     * Calcula qué porcentaje representa la mora sobre el monto original.
     * Si el monto original es cero, retorna 0 para evitar división por cero.
     * 
     * @return float Porcentaje de mora (0-100+)
     */
    public function getLateFeePercentage() {
        $original = $this->monto_original ?? $this->monto ?? 0;
        if (floatval($original) == 0) {
            return 0;
        }
        $mora = $this->monto_mora ?? 0;
        return (floatval($mora) / floatval($original)) * 100;
    }
}
?>
