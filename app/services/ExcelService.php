<?php
require_once ROOT_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExcelService {
    
    public function generateIncomeReport($payments, $start_date, $end_date, $total) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'REPORTE DE INGRESOS');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Información
        $sheet->setCellValue('A2', 'Sistema de Gestión de Condominio');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'Período: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)));
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A4', 'Fecha de generación: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A4:G4');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Encabezados
        $headers = ['ID', 'Residente', 'Apartamento', 'Concepto', 'Fecha', 'Método', 'Monto'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $sheet->getStyle($col . '6')->getFont()->setBold(true);
            $sheet->getStyle($col . '6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('007bff');
            $sheet->getStyle($col . '6')->getFont()->getColor()->setRGB('FFFFFF');
            $col++;
        }
        
        // Datos
        $row = 7;
        foreach ($payments as $payment) {
            $sheet->setCellValue('A' . $row, $payment['id']);
            $sheet->setCellValue('B' . $row, $payment['residente_nombre'] ?? 'N/A');
            $sheet->setCellValue('C' . $row, $payment['apartamento'] ?? 'N/A');
            $sheet->setCellValue('D' . $row, $payment['descripcion'] ?? $payment['concepto'] ?? 'N/A');
            $sheet->setCellValue('E' . $row, date('d/m/Y', strtotime($payment['fecha_pago'])));
            $sheet->setCellValue('F' . $row, ucfirst($payment['metodo_pago']));
            $sheet->setCellValue('G' . $row, $payment['monto']);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
            $row++;
        }
        
        // Total
        $sheet->setCellValue('F' . $row, 'TOTAL:');
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('G' . $row, $total);
        $sheet->getStyle('G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
        
        // Ajustar anchos
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes
        $sheet->getStyle('A6:G' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $this->downloadExcel($spreadsheet, 'reporte_ingresos_' . date('Ymd') . '.xlsx');
    }
    
    public function generatePendingPaymentsReport($payments, $total) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'REPORTE DE PAGOS PENDIENTES');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Sistema de Gestión de Condominio');
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'Fecha de generación: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Encabezados
        $headers = ['ID', 'Residente', 'Apartamento', 'Concepto', 'Mes', 'Fecha Vencimiento', 'Estado', 'Monto'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $sheet->getStyle($col . '5')->getFont()->setBold(true);
            $sheet->getStyle($col . '5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('dc3545');
            $sheet->getStyle($col . '5')->getFont()->getColor()->setRGB('FFFFFF');
            $col++;
        }
        
        // Datos
        $row = 6;
        foreach ($payments as $payment) {
            $sheet->setCellValue('A' . $row, $payment['id']);
            $sheet->setCellValue('B' . $row, $payment['residente_nombre'] ?? 'N/A');
            $sheet->setCellValue('C' . $row, $payment['apartamento'] ?? 'N/A');
            $sheet->setCellValue('D' . $row, $payment['concepto']);
            $sheet->setCellValue('E' . $row, $payment['mes_pago']);
            $sheet->setCellValue('F' . $row, date('d/m/Y', strtotime($payment['fecha_vencimiento'])));
            $sheet->setCellValue('G' . $row, ucfirst($payment['estado']));
            $sheet->setCellValue('H' . $row, $payment['monto']);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
            
            // Color según estado
            if ($payment['estado'] === 'atrasado') {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('dc3545');
            } else {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('ffc107');
            }
            $row++;
        }
        
        // Total
        $sheet->setCellValue('G' . $row, 'TOTAL:');
        $sheet->getStyle('G' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('H' . $row, $total);
        $sheet->getStyle('H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
        
        // Ajustar anchos
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes
        $sheet->getStyle('A5:H' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $this->downloadExcel($spreadsheet, 'reporte_pagos_pendientes_' . date('Ymd') . '.xlsx');
    }
    
    public function generateIncidentReport($incidents, $start_date, $end_date) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'REPORTE DE INCIDENCIAS');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Sistema de Gestión de Condominio');
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'Período: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)));
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Encabezados
        $headers = ['ID', 'Residente', 'Apartamento', 'Título', 'Categoría', 'Prioridad', 'Estado', 'Fecha'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $sheet->getStyle($col . '5')->getFont()->setBold(true);
            $sheet->getStyle($col . '5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffc107');
            $sheet->getStyle($col . '5')->getFont()->getColor()->setRGB('000000');
            $col++;
        }
        
        // Datos
        $row = 6;
        foreach ($incidents as $incident) {
            $sheet->setCellValue('A' . $row, $incident['id']);
            $sheet->setCellValue('B' . $row, $incident['residente_nombre'] ?? 'N/A');
            $sheet->setCellValue('C' . $row, $incident['apartamento'] ?? 'N/A');
            $sheet->setCellValue('D' . $row, $incident['titulo']);
            $sheet->setCellValue('E' . $row, ucfirst($incident['categoria']));
            $sheet->setCellValue('F' . $row, ucfirst($incident['prioridad']));
            $sheet->setCellValue('G' . $row, ucfirst(str_replace('_', ' ', $incident['estado'])));
            $sheet->setCellValue('H' . $row, date('d/m/Y', strtotime($incident['fecha_reporte'])));
            
            // Color según prioridad
            if ($incident['prioridad'] === 'alta') {
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('dc3545');
            } elseif ($incident['prioridad'] === 'media') {
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('ffc107');
            } else {
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('28a745');
            }
            $row++;
        }
        
        // Ajustar anchos
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes
        $sheet->getStyle('A5:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $this->downloadExcel($spreadsheet, 'reporte_incidencias_' . date('Ymd') . '.xlsx');
    }
    
    public function generateResidentReport($residents, $status) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'REPORTE DE RESIDENTES');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Sistema de Gestión de Condominio');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        if ($status) {
            $sheet->setCellValue('A3', 'Filtro: Estado ' . ucfirst($status));
            $sheet->mergeCells('A3:I3');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Encabezados
        $headers = ['ID', 'Nombre', 'Email', 'Teléfono', 'Apartamento', 'Piso', 'Torre', 'Estado', 'Fecha Ingreso'];
        $col = 'A';
        $headerRow = $status ? 5 : 4;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6f42c1');
            $sheet->getStyle($col . $headerRow)->getFont()->getColor()->setRGB('FFFFFF');
            $col++;
        }
        
        // Datos
        $row = $headerRow + 1;
        foreach ($residents as $resident) {
            $sheet->setCellValue('A' . $row, $resident['id']);
            $sheet->setCellValue('B' . $row, $resident['nombre']);
            $sheet->setCellValue('C' . $row, $resident['email']);
            $sheet->setCellValue('D' . $row, $resident['telefono']);
            $sheet->setCellValue('E' . $row, $resident['apartamento']);
            $sheet->setCellValue('F' . $row, $resident['piso']);
            $sheet->setCellValue('G' . $row, $resident['torre'] ?: '-');
            $sheet->setCellValue('H' . $row, ucfirst($resident['estado']));
            $sheet->setCellValue('I' . $row, date('d/m/Y', strtotime($resident['fecha_ingreso'])));
            
            // Color según estado
            if ($resident['estado'] === 'activo') {
                $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('28a745');
            } else {
                $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('6c757d');
            }
            $row++;
        }
        
        // Ajustar anchos
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes
        $sheet->getStyle('A' . $headerRow . ':I' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $this->downloadExcel($spreadsheet, 'reporte_residentes_' . date('Ymd') . '.xlsx');
    }
    
    private function downloadExcel($spreadsheet, $filename) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
?>
