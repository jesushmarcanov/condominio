<?php
// Modelo de Pagos

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
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear pago
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (residente_id, monto, concepto, mes_pago, fecha_pago, metodo_pago, referencia, estado) VALUES (:residente_id, :monto, :concepto, :mes_pago, :fecha_pago, :metodo_pago, :referencia, :estado)";
        
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
        
        // Bind parameters
        $stmt->bindParam(":residente_id", $this->residente_id);
        $stmt->bindParam(":monto", $this->monto);
        $stmt->bindParam(":concepto", $this->concepto);
        $stmt->bindParam(":mes_pago", $this->mes_pago);
        $stmt->bindParam(":fecha_pago", $this->fecha_pago);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":referencia", $this->referencia);
        $stmt->bindParam(":estado", $this->estado);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Leer todos los pagos con información de residentes
    public function readAll() {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email 
                  FROM " . $this->table_name . " p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Leer pagos por residente
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

    // Leer un pago por ID
    public function readOne() {
        $query = "SELECT p.*, r.apartamento, u.nombre, u.email 
                  FROM " . $this->table_name . " p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar pago
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET residente_id = :residente_id, monto = :monto, concepto = :concepto, mes_pago = :mes_pago, fecha_pago = :fecha_pago, metodo_pago = :metodo_pago, referencia = :referencia, estado = :estado WHERE id = :id";
        
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
        
        // Bind parameters
        $stmt->bindParam(":residente_id", $this->residente_id);
        $stmt->bindParam(":monto", $this->monto);
        $stmt->bindParam(":concepto", $this->concepto);
        $stmt->bindParam(":mes_pago", $this->mes_pago);
        $stmt->bindParam(":fecha_pago", $this->fecha_pago);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":referencia", $this->referencia);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar pago
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Obtener pagos pendientes
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

    // Obtener pagos por mes
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

    // Obtener estadísticas de pagos
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_pagos,
                    COALESCE(SUM(monto), 0) as total_ingresos,
                    COALESCE(SUM(CASE WHEN estado = 'pagado' THEN monto ELSE 0 END), 0) as total_pagado,
                    COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END), 0) as total_pendiente,
                    COALESCE(SUM(CASE WHEN estado = 'atrasado' THEN monto ELSE 0 END), 0) as total_atrasado,
                    COUNT(CASE WHEN estado = 'pagado' THEN 1 END) as pagos_realizados,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pagos_pendientes,
                    COUNT(CASE WHEN estado = 'atrasado' THEN 1 END) as pagos_atrasados,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as pagos_30_dias
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener ingresos mensuales
    public function getMonthlyIncome($months = 12) {
        $query = "SELECT 
                    DATE_FORMAT(fecha_pago, '%Y-%m') as mes,
                    SUM(monto) as ingresos,
                    COUNT(*) as cantidad_pagos
                  FROM " . $this->table_name . " 
                  WHERE estado = 'pagado' 
                  AND fecha_pago >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                  GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
                  ORDER BY mes ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":months", $months);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verificar si ya existe pago para el mismo mes y residente
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
}
?>
