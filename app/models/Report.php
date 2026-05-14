<?php
// Modelo de Reportes

class Report {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generar reporte de ingresos
    public function generateIncomeReport($start_date = null, $end_date = null) {
        $query = "SELECT 
                    p.id,
                    p.monto,
                    p.concepto as descripcion,
                    p.mes_pago,
                    p.fecha_pago,
                    p.metodo_pago,
                    p.referencia,
                    p.estado,
                    r.apartamento,
                    u.nombre as residente_nombre,
                    u.email as residente_email
                  FROM pagos p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id";
        
        if($start_date && $end_date) {
            $query .= " WHERE p.fecha_pago BETWEEN :start_date AND :end_date";
        }
        
        $query .= " ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if($start_date && $end_date) {
            $stmt->bindParam(":start_date", $start_date);
            $stmt->bindParam(":end_date", $end_date);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Generar reporte de pagos pendientes
    public function generatePendingPaymentsReport() {
        $query = "SELECT 
                    p.id,
                    p.monto,
                    p.concepto,
                    p.mes_pago,
                    p.estado,
                    p.fecha_pago as fecha_vencimiento,
                    r.apartamento,
                    u.nombre as residente_nombre,
                    u.email as residente_email,
                    u.telefono,
                    DATEDIFF(NOW(), p.fecha_pago) as dias_atraso
                  FROM pagos p
                  LEFT JOIN residentes r ON p.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  WHERE p.estado IN ('pendiente', 'atrasado')
                  ORDER BY p.fecha_pago ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Generar reporte de incidencias
    public function generateIncidentReport($start_date = null, $end_date = null, $estado = null) {
        $query = "SELECT 
                    i.id,
                    i.titulo,
                    i.descripcion,
                    i.categoria,
                    i.prioridad,
                    i.estado,
                    i.fecha_reporte,
                    i.fecha_resolucion,
                    i.notas_admin,
                    r.apartamento,
                    u.nombre as residente_nombre,
                    u.email as residente_email,
                    a.nombre as admin_nombre,
                    DATEDIFF(i.fecha_resolucion, i.fecha_reporte) as dias_resolucion
                  FROM incidencias i
                  LEFT JOIN residentes r ON i.residente_id = r.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios a ON i.administrador_id = a.id
                  WHERE 1=1";
        
        if($start_date && $end_date) {
            $query .= " AND i.fecha_reporte BETWEEN :start_date AND :end_date";
        }
        
        if($estado) {
            $query .= " AND i.estado = :estado";
        }
        
        $query .= " ORDER BY i.fecha_reporte DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if($start_date && $end_date) {
            $stmt->bindParam(":start_date", $start_date);
            $stmt->bindParam(":end_date", $end_date);
        }
        
        if($estado) {
            $stmt->bindParam(":estado", $estado);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Generar reporte de residentes
    public function generateResidentReport($estado = null) {
        $query = "SELECT 
                    r.id,
                    r.apartamento,
                    r.piso,
                    r.torre,
                    r.fecha_ingreso,
                    r.estado,
                    u.nombre,
                    u.email,
                    u.telefono,
                    u.created_at as fecha_registro,
                    (SELECT COUNT(*) FROM pagos WHERE residente_id = r.id AND estado = 'pagado') as total_pagos,
                    (SELECT COUNT(*) FROM incidencias WHERE residente_id = r.id) as total_incidencias
                  FROM residentes r
                  LEFT JOIN usuarios u ON r.usuario_id = u.id";
        
        if($estado) {
            $query .= " WHERE r.estado = :estado";
        }
        
        $query .= " ORDER BY r.apartamento ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if($estado) {
            $stmt->bindParam(":estado", $estado);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas generales
    public function getGeneralStats() {
        $stats = [];
        
        // Estadísticas de usuarios
        $query = "SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as total_admins,
                    SUM(CASE WHEN rol = 'resident' THEN 1 ELSE 0 END) as total_residents
                  FROM usuarios";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Estadísticas de residentes
        $query = "SELECT 
                    COUNT(*) as total_residentes,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as residentes_activos
                  FROM residentes";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['residentes'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Estadísticas de pagos
        $query = "SELECT 
                    COUNT(*) as total_pagos,
                    COALESCE(SUM(monto), 0) as total_ingresos,
                    COALESCE(SUM(CASE WHEN estado = 'pagado' THEN monto ELSE 0 END), 0) as total_pagado,
                    COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END), 0) as total_pendiente,
                    COUNT(CASE WHEN estado = 'pagado' THEN 1 END) as pagos_realizados,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pagos_pendientes,
                    COUNT(CASE WHEN estado = 'pendiente' AND fecha_pago < DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH) THEN 1 END) as pagos_atrasados
                  FROM pagos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pagos'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Estadísticas de incidencias
        $query = "SELECT 
                    COUNT(*) as total_incidencias,
                    SUM(CASE WHEN estado = 'abierto' THEN 1 ELSE 0 END) as incidencias_abiertas,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as incidencias_proceso,
                    SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as incidencias_resueltas
                  FROM incidencias";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['incidencias'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $stats;
    }

    // Obtener datos para gráficos
    public function getChartData($type = 'monthly_income') {
        switch($type) {
            case 'monthly_income':
                $query = "SELECT 
                            DATE_FORMAT(fecha_pago, '%Y-%m') as period,
                            COALESCE(SUM(monto), 0) as amount,
                            COUNT(*) as count
                          FROM pagos 
                          WHERE estado = 'pagado' 
                          AND fecha_pago >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                          GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
                          ORDER BY period ASC";
                break;
                
            case 'monthly_incidents':
                $query = "SELECT 
                            DATE_FORMAT(fecha_reporte, '%Y-%m') as period,
                            COUNT(*) as count
                          FROM incidencias 
                          WHERE fecha_reporte >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                          GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m')
                          ORDER BY period ASC";
                break;
                
            case 'incidents_by_category':
                $query = "SELECT 
                            categoria as category,
                            COUNT(*) as count
                          FROM incidencias 
                          GROUP BY categoria 
                          ORDER BY count DESC";
                break;
                
            case 'payment_methods':
                $query = "SELECT 
                            metodo_pago as method,
                            COUNT(*) as count,
                            COALESCE(SUM(monto), 0) as total
                          FROM pagos 
                          WHERE estado = 'pagado'
                          GROUP BY metodo_pago 
                          ORDER BY count DESC";
                break;
                
            default:
                return [];
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Asegurar que siempre haya datos, incluso si están vacíos
        if (empty($results)) {
            // Retornar estructura vacía según el tipo
            switch($type) {
                case 'monthly_income':
                    return [['period' => date('Y-m'), 'amount' => 0, 'count' => 0]];
                case 'monthly_incidents':
                    return [['period' => date('Y-m'), 'count' => 0]];
                case 'incidents_by_category':
                    return [['category' => 'Sin datos', 'count' => 0]];
                case 'payment_methods':
                    return [['method' => 'Sin datos', 'count' => 0, 'total' => 0]];
            }
        }
        
        return $results;
    }

    // Exportar reporte a CSV
    public function exportToCSV($data, $filename) {
        // Limpiar cualquier salida previa
        if (ob_get_contents()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Pragma: no-cache');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 - asegura caracteres especiales se muestren correctamente
        fwrite($output, "\xEF\xBB\xBF");
        
        if(!empty($data)) {
            // Limpiar y formatear encabezados
            $headers = array_keys($data[0]);
            $formatted_headers = array_map(function($header) {
                // Convertir snake_case a Title Case y traducir algunos términos
                $translated = [
                    'id' => 'ID',
                    'monto' => 'Monto',
                    'concepto' => 'Concepto',
                    'mes_pago' => 'Mes de Pago',
                    'fecha_pago' => 'Fecha de Pago',
                    'fecha_vencimiento' => 'Fecha Vencimiento',
                    'metodo_pago' => 'Método de Pago',
                    'referencia' => 'Referencia',
                    'estado' => 'Estado',
                    'apartamento' => 'Apartamento',
                    'residente_nombre' => 'Residente',
                    'residente_email' => 'Email Residente',
                    'telefono' => 'Teléfono',
                    'dias_atraso' => 'Días de Atraso',
                    'titulo' => 'Título',
                    'descripcion' => 'Descripción',
                    'categoria' => 'Categoría',
                    'prioridad' => 'Prioridad',
                    'fecha_reporte' => 'Fecha Reporte',
                    'fecha_resolucion' => 'Fecha Resolución',
                    'notas_admin' => 'Notas Administrador',
                    'admin_nombre' => 'Administrador',
                    'dias_resolucion' => 'Días Resolución',
                    'piso' => 'Piso',
                    'torre' => 'Torre',
                    'fecha_ingreso' => 'Fecha Ingreso',
                    'nombre' => 'Nombre',
                    'email' => 'Email',
                    'created_at' => 'Fecha Registro',
                    'total_pagos' => 'Total Pagos',
                    'total_incidencias' => 'Total Incidencias',
                    'period' => 'Período',
                    'amount' => 'Monto',
                    'count' => 'Cantidad',
                    'method' => 'Método',
                    'ingresos' => 'Ingresos',
                    'pagos_realizados' => 'Pagos Realizados',
                    'pendiente' => 'Pendiente',
                    'pagos_pendientes' => 'Pagos Pendientes'
                ];
                
                return $translated[$header] ?? ucwords(str_replace('_', ' ', $header));
            }, $headers);
            
            fputcsv($output, $formatted_headers);
            
            // Datos con formato especial para campos específicos
            foreach($data as $row) {
                $formatted_row = [];
                foreach($row as $key => $value) {
                    // Formatear campos especiales
                    if(strstr($key, 'monto') || strstr($key, 'ingreso') || strstr($key, 'total') || strstr($key, 'amount')) {
                        $formatted_row[] = '$' . number_format(floatval($value), 2);
                    } elseif(strstr($key, 'fecha') || strstr($key, 'date') || strstr($key, 'created_at')) {
                        $formatted_row[] = $value ? date('d/m/Y', strtotime($value)) : '';
                    } elseif(strstr($key, 'email')) {
                        $formatted_row[] = strtolower($value);
                    } elseif(strstr($key, 'dias') || strstr($key, 'count')) {
                        $formatted_row[] = is_numeric($value) ? intval($value) : $value;
                    } else {
                        $formatted_row[] = $value;
                    }
                }
                fputcsv($output, $formatted_row);
            }
        } else {
            // Si no hay datos, agregar mensaje
            fputcsv($output, ['No se encontraron datos para este reporte']);
        }
        
        // Agregar metadata al final
        fputcsv($output, []);
        fputcsv($output, ['INFORMACIÓN DEL REPORTE']);
        fputcsv($output, ['Fecha de Generación:', date('d/m/Y H:i:s')]);
        fputcsv($output, ['Sistema:', 'Condominio Management System']);
        fputcsv($output, ['Total de Registros:', count($data)]);
        
        fclose($output);
        exit;
    }

    // Obtener resumen financiero mensual
    public function getMonthlyFinancialSummary($year = null) {
        if(!$year) {
            $year = date('Y');
        }
        
        $query = "SELECT 
                    MONTH(fecha_pago) as mes,
                    COALESCE(SUM(CASE WHEN estado = 'pagado' THEN monto ELSE 0 END), 0) as ingresos,
                    COUNT(CASE WHEN estado = 'pagado' THEN 1 END) as pagos_realizados,
                    COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END), 0) as pendiente,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pagos_pendientes
                  FROM pagos 
                  WHERE YEAR(fecha_pago) = :year
                  GROUP BY MONTH(fecha_pago)
                  ORDER BY mes";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
