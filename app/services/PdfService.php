<?php
require_once ROOT_PATH . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService {
    private $dompdf;
    private $options;
    
    public function __construct() {
        $this->options = new Options();
        $this->options->set('isHtml5ParserEnabled', true);
        $this->options->set('isRemoteEnabled', true);
        $this->options->set('defaultFont', 'Arial');
        
        $this->dompdf = new Dompdf($this->options);
    }
    
    public function generateFromHtml($html, $filename = 'document.pdf', $orientation = 'portrait', $paper_size = 'letter', $download = true) {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paper_size, $orientation);
        $this->dompdf->render();
        
        $this->dompdf->stream($filename, ['Attachment' => $download]);
    }
    
    public function generateIncomeReport($payments, $start_date, $end_date, $total) {
        $html = $this->getIncomeReportHtml($payments, $start_date, $end_date, $total);
        $filename = 'reporte_ingresos_' . date('Ymd') . '.pdf';
        $this->generateFromHtml($html, $filename);
    }
    
    public function generatePendingPaymentsReport($payments, $total) {
        $html = $this->getPendingPaymentsReportHtml($payments, $total);
        $filename = 'reporte_pagos_pendientes_' . date('Ymd') . '.pdf';
        $this->generateFromHtml($html, $filename);
    }
    
    public function generateIncidentReport($incidents, $start_date, $end_date) {
        $html = $this->getIncidentReportHtml($incidents, $start_date, $end_date);
        $filename = 'reporte_incidencias_' . date('Ymd') . '.pdf';
        $this->generateFromHtml($html, $filename);
    }
    
    public function generateResidentReport($residents, $status) {
        $html = $this->getResidentReportHtml($residents, $status);
        $filename = 'reporte_residentes_' . date('Ymd') . '.pdf';
        $this->generateFromHtml($html, $filename);
    }
    
    public function generatePaymentReceipt($payment) {
        $html = $this->getPaymentReceiptHtml($payment);
        $filename = 'comprobante_pago_' . $payment['id'] . '.pdf';
        $this->generateFromHtml($html, $filename);
    }
    
    public function generateIncidentReceipt($incident) {
        $html = $this->getIncidentReceiptHtml($incident);
        $filename = 'reporte_incidencia_' . $incident['id'] . '.pdf';
        $this->generateFromHtml($html, $filename);
    }
    
    private function getIncomeReportHtml($payments, $start_date, $end_date, $total) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; font-size: 12px; }
.header { text-align: center; margin-bottom: 30px; }
.header h1 { margin: 0; color: #333; }
.header p { margin: 5px 0; color: #666; }
.info { margin-bottom: 20px; }
.info p { margin: 5px 0; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th { background-color: #007bff; color: white; padding: 10px; text-align: left; }
td { padding: 8px; border-bottom: 1px solid #ddd; }
tr:nth-child(even) { background-color: #f8f9fa; }
.total { text-align: right; font-weight: bold; font-size: 14px; margin-top: 20px; }
.footer { text-align: center; margin-top: 40px; font-size: 10px; color: #666; }
</style></head><body>
<div class="header">
<h1>Sistema de Gestión de Condominio</h1>
<h2>Reporte de Ingresos</h2>
<p>Período: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)) . '</p>
</div>
<div class="info">
<p><strong>Fecha de generación:</strong> ' . date('d/m/Y H:i:s') . '</p>
<p><strong>Total de registros:</strong> ' . count($payments) . '</p>
</div>
<table><thead><tr>
<th>ID</th><th>Residente</th><th>Apartamento</th><th>Concepto</th><th>Fecha</th><th>Método</th><th>Monto</th>
</tr></thead><tbody>';
        
        foreach ($payments as $payment) {
            $html .= '<tr>
<td>' . htmlspecialchars($payment['id']) . '</td>
<td>' . htmlspecialchars($payment['residente_nombre'] ?? 'N/A') . '</td>
<td>' . htmlspecialchars($payment['apartamento'] ?? 'N/A') . '</td>
<td>' . htmlspecialchars($payment['descripcion'] ?? $payment['concepto'] ?? 'N/A') . '</td>
<td>' . date('d/m/Y', strtotime($payment['fecha_pago'])) . '</td>
<td>' . ucfirst($payment['metodo_pago']) . '</td>
<td>$' . number_format($payment['monto'], 2) . '</td>
</tr>';
        }
        
        $html .= '</tbody></table>
<div class="total"><p>TOTAL: $' . number_format($total, 2) . '</p></div>
<div class="footer"><p>Este documento fue generado automáticamente por el Sistema de Gestión de Condominio</p></div>
</body></html>';
        
        return $html;
    }
    
    private function getPendingPaymentsReportHtml($payments, $total) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; font-size: 12px; }
.header { text-align: center; margin-bottom: 30px; }
.header h1 { margin: 0; color: #333; }
.header p { margin: 5px 0; color: #666; }
.info { margin-bottom: 20px; }
.info p { margin: 5px 0; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th { background-color: #dc3545; color: white; padding: 10px; text-align: left; }
td { padding: 8px; border-bottom: 1px solid #ddd; }
tr:nth-child(even) { background-color: #f8f9fa; }
.status-pendiente { color: #ffc107; font-weight: bold; }
.status-atrasado { color: #dc3545; font-weight: bold; }
.total { text-align: right; font-weight: bold; font-size: 14px; margin-top: 20px; color: #dc3545; }
.footer { text-align: center; margin-top: 40px; font-size: 10px; color: #666; }
</style></head><body>
<div class="header">
<h1>Sistema de Gestión de Condominio</h1>
<h2>Reporte de Pagos Pendientes</h2>
<p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
</div>
<div class="info">
<p><strong>Total de pagos pendientes:</strong> ' . count($payments) . '</p>
</div>
<table><thead><tr>
<th>ID</th><th>Residente</th><th>Apartamento</th><th>Concepto</th><th>Mes</th><th>Fecha Vencimiento</th><th>Estado</th><th>Monto</th>
</tr></thead><tbody>';
        
        foreach ($payments as $payment) {
            $status_class = $payment['estado'] === 'atrasado' ? 'status-atrasado' : 'status-pendiente';
            $html .= '<tr>
<td>' . htmlspecialchars($payment['id']) . '</td>
<td>' . htmlspecialchars($payment['residente_nombre'] ?? 'N/A') . '</td>
<td>' . htmlspecialchars($payment['apartamento'] ?? 'N/A') . '</td>
<td>' . htmlspecialchars($payment['concepto']) . '</td>
<td>' . htmlspecialchars($payment['mes_pago']) . '</td>
<td>' . date('d/m/Y', strtotime($payment['fecha_vencimiento'])) . '</td>
<td class="' . $status_class . '">' . ucfirst($payment['estado']) . '</td>
<td>$' . number_format($payment['monto'], 2) . '</td>
</tr>';
        }
        
        $html .= '</tbody></table>
<div class="total"><p>TOTAL PENDIENTE: $' . number_format($total, 2) . '</p></div>
<div class="footer"><p>Este documento fue generado automáticamente por el Sistema de Gestión de Condominio</p></div>
</body></html>';
        
        return $html;
    }
    
    private function getIncidentReportHtml($incidents, $start_date, $end_date) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; font-size: 11px; }
.header { text-align: center; margin-bottom: 30px; }
.header h1 { margin: 0; color: #333; }
.header p { margin: 5px 0; color: #666; }
.info { margin-bottom: 20px; }
.info p { margin: 5px 0; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th { background-color: #ffc107; color: #333; padding: 10px; text-align: left; font-size: 10px; }
td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10px; }
tr:nth-child(even) { background-color: #f8f9fa; }
.priority-alta { color: #dc3545; font-weight: bold; }
.priority-media { color: #ffc107; font-weight: bold; }
.priority-baja { color: #28a745; font-weight: bold; }
.footer { text-align: center; margin-top: 40px; font-size: 10px; color: #666; }
</style></head><body>
<div class="header">
<h1>Sistema de Gestión de Condominio</h1>
<h2>Reporte de Incidencias</h2>
<p>Período: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)) . '</p>
</div>
<div class="info">
<p><strong>Fecha de generación:</strong> ' . date('d/m/Y H:i:s') . '</p>
<p><strong>Total de incidencias:</strong> ' . count($incidents) . '</p>
</div>
<table><thead><tr>
<th>ID</th><th>Residente</th><th>Apto</th><th>Título</th><th>Categoría</th><th>Prioridad</th><th>Estado</th><th>Fecha</th>
</tr></thead><tbody>';
        
        foreach ($incidents as $incident) {
            $priority_class = 'priority-' . $incident['prioridad'];
            $html .= '<tr>
<td>' . htmlspecialchars($incident['id']) . '</td>
<td>' . htmlspecialchars($incident['residente_nombre'] ?? 'N/A') . '</td>
<td>' . htmlspecialchars($incident['apartamento'] ?? 'N/A') . '</td>
<td>' . htmlspecialchars($incident['titulo']) . '</td>
<td>' . ucfirst($incident['categoria']) . '</td>
<td class="' . $priority_class . '">' . ucfirst($incident['prioridad']) . '</td>
<td>' . ucfirst(str_replace('_', ' ', $incident['estado'])) . '</td>
<td>' . date('d/m/Y', strtotime($incident['fecha_reporte'])) . '</td>
</tr>';
        }
        
        $html .= '</tbody></table>
<div class="footer"><p>Este documento fue generado automáticamente por el Sistema de Gestión de Condominio</p></div>
</body></html>';
        
        return $html;
    }
    
    private function getResidentReportHtml($residents, $status) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; font-size: 11px; }
.header { text-align: center; margin-bottom: 30px; }
.header h1 { margin: 0; color: #333; }
.header p { margin: 5px 0; color: #666; }
.info { margin-bottom: 20px; }
.info p { margin: 5px 0; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th { background-color: #6f42c1; color: white; padding: 10px; text-align: left; font-size: 10px; }
td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10px; }
tr:nth-child(even) { background-color: #f8f9fa; }
.status-activo { color: #28a745; font-weight: bold; }
.status-inactivo { color: #6c757d; font-weight: bold; }
.footer { text-align: center; margin-top: 40px; font-size: 10px; color: #666; }
</style></head><body>
<div class="header">
<h1>Sistema de Gestión de Condominio</h1>
<h2>Reporte de Residentes</h2>
<p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>';
        
        if ($status) {
            $html .= '<p>Filtro: Estado ' . ucfirst($status) . '</p>';
        }
        
        $html .= '</div>
<div class="info">
<p><strong>Total de residentes:</strong> ' . count($residents) . '</p>
<p><strong>Activos:</strong> ' . count(array_filter($residents, fn($r) => $r['estado'] === 'activo')) . '</p>
<p><strong>Inactivos:</strong> ' . count(array_filter($residents, fn($r) => $r['estado'] === 'inactivo')) . '</p>
</div>
<table><thead><tr>
<th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Apartamento</th><th>Piso</th><th>Torre</th><th>Estado</th><th>Fecha Ingreso</th>
</tr></thead><tbody>';
        
        foreach ($residents as $resident) {
            $status_class = 'status-' . $resident['estado'];
            $html .= '<tr>
<td>' . htmlspecialchars($resident['id']) . '</td>
<td>' . htmlspecialchars($resident['nombre']) . '</td>
<td>' . htmlspecialchars($resident['email']) . '</td>
<td>' . htmlspecialchars($resident['telefono']) . '</td>
<td>' . htmlspecialchars($resident['apartamento']) . '</td>
<td>' . htmlspecialchars($resident['piso']) . '</td>
<td>' . htmlspecialchars($resident['torre'] ?: '-') . '</td>
<td class="' . $status_class . '">' . ucfirst($resident['estado']) . '</td>
<td>' . date('d/m/Y', strtotime($resident['fecha_ingreso'])) . '</td>
</tr>';
        }
        
        $html .= '</tbody></table>
<div class="footer"><p>Este documento fue generado automáticamente por el Sistema de Gestión de Condominio</p></div>
</body></html>';
        
        return $html;
    }
    
    private function getPaymentReceiptHtml($payment) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; font-size: 14px; }
.header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #007bff; padding-bottom: 20px; }
.header h1 { margin: 0; color: #007bff; }
.header p { margin: 5px 0; color: #666; }
.receipt-number { text-align: right; font-size: 12px; color: #666; margin-bottom: 30px; }
.section { margin-bottom: 30px; }
.section h3 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
.amount-box { background-color: #007bff; color: white; padding: 20px; text-align: center; margin: 30px 0; }
.amount-box h2 { margin: 0; font-size: 24px; }
.footer { text-align: center; margin-top: 60px; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
table { width: 100%; margin-bottom: 15px; }
td { padding: 8px 0; }
.label { font-weight: bold; width: 40%; }
</style></head><body>
<div class="header">
<h1>COMPROBANTE DE PAGO</h1>
<p>Sistema de Gestión de Condominio</p>
</div>
<div class="receipt-number">
<p><strong>No. Comprobante:</strong> ' . str_pad($payment['id'], 8, '0', STR_PAD_LEFT) . '</p>
<p><strong>Fecha de emisión:</strong> ' . date('d/m/Y H:i:s') . '</p>
</div>
<div class="section">
<h3>Información del Residente</h3>
<table>
<tr><td class="label">Nombre:</td><td>' . htmlspecialchars($payment['residente_nombre'] ?? 'N/A') . '</td></tr>
<tr><td class="label">Apartamento:</td><td>' . htmlspecialchars($payment['apartamento'] ?? 'N/A') . '</td></tr>
<tr><td class="label">Email:</td><td>' . htmlspecialchars($payment['email'] ?? $payment['residente_email'] ?? 'N/A') . '</td></tr>
</table>
</div>
<div class="section">
<h3>Detalles del Pago</h3>
<table>
<tr><td class="label">Concepto:</td><td>' . htmlspecialchars($payment['concepto']) . '</td></tr>
<tr><td class="label">Mes de pago:</td><td>' . htmlspecialchars($payment['mes_pago']) . '</td></tr>
<tr><td class="label">Fecha de pago:</td><td>' . date('d/m/Y', strtotime($payment['fecha_pago'])) . '</td></tr>
<tr><td class="label">Método de pago:</td><td>' . ucfirst($payment['metodo_pago']) . '</td></tr>
<tr><td class="label">Referencia:</td><td>' . htmlspecialchars($payment['referencia'] ?? 'N/A') . '</td></tr>
<tr><td class="label">Estado:</td><td><strong>' . ucfirst($payment['estado']) . '</strong></td></tr>
</table>
</div>
<div class="amount-box">
<h2>MONTO TOTAL: $' . number_format($payment['monto'], 2) . '</h2>
</div>
<div class="footer">
<p>Este comprobante fue generado automáticamente por el Sistema de Gestión de Condominio</p>
<p>Para cualquier consulta, por favor contacte a la administración</p>
</div>
</body></html>';
        
        return $html;
    }
    
    private function getIncidentReceiptHtml($incident) {
        $priority_colors = ['alta' => '#dc3545', 'media' => '#ffc107', 'baja' => '#28a745'];
        $priority_color = $priority_colors[$incident['prioridad']] ?? '#6c757d';
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; font-size: 14px; }
.header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #ffc107; padding-bottom: 20px; }
.header h1 { margin: 0; color: #ffc107; }
.header p { margin: 5px 0; color: #666; }
.receipt-number { text-align: right; font-size: 12px; color: #666; margin-bottom: 30px; }
.section { margin-bottom: 30px; }
.section h3 { color: #333; border-bottom: 2px solid #ffc107; padding-bottom: 5px; }
.priority-box { background-color: ' . $priority_color . '; color: white; padding: 15px; text-align: center; margin: 20px 0; }
.priority-box h3 { margin: 0; }
.footer { text-align: center; margin-top: 60px; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
table { width: 100%; margin-bottom: 15px; }
td { padding: 8px 0; }
.label { font-weight: bold; width: 40%; }
.description-box { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
</style></head><body>
<div class="header">
<h1>REPORTE DE INCIDENCIA</h1>
<p>Sistema de Gestión de Condominio</p>
</div>
<div class="receipt-number">
<p><strong>No. Incidencia:</strong> ' . str_pad($incident['id'], 6, '0', STR_PAD_LEFT) . '</p>
<p><strong>Fecha de emisión:</strong> ' . date('d/m/Y H:i:s') . '</p>
</div>
<div class="section">
<h3>Información del Residente</h3>
<table>
<tr><td class="label">Nombre:</td><td>' . htmlspecialchars($incident['residente_nombre'] ?? 'N/A') . '</td></tr>
<tr><td class="label">Apartamento:</td><td>' . htmlspecialchars($incident['apartamento'] ?? 'N/A') . '</td></tr>
<tr><td class="label">Email:</td><td>' . htmlspecialchars($incident['email'] ?? 'N/A') . '</td></tr>
</table>
</div>
<div class="priority-box">
<h3>PRIORIDAD: ' . strtoupper($incident['prioridad']) . '</h3>
</div>
<div class="section">
<h3>Detalles de la Incidencia</h3>
<table>
<tr><td class="label">Título:</td><td><strong>' . htmlspecialchars($incident['titulo']) . '</strong></td></tr>
<tr><td class="label">Categoría:</td><td>' . ucfirst($incident['categoria']) . '</td></tr>
<tr><td class="label">Estado:</td><td><strong>' . ucfirst(str_replace('_', ' ', $incident['estado'])) . '</strong></td></tr>
<tr><td class="label">Fecha de reporte:</td><td>' . date('d/m/Y H:i', strtotime($incident['fecha_reporte'])) . '</td></tr>';
        
        if (!empty($incident['fecha_resolucion'])) {
            $html .= '<tr><td class="label">Fecha de resolución:</td><td>' . date('d/m/Y H:i', strtotime($incident['fecha_resolucion'])) . '</td></tr>';
        }
        
        $html .= '</table>
</div>
<div class="section">
<h3>Descripción</h3>
<div class="description-box">' . nl2br(htmlspecialchars($incident['descripcion'])) . '</div>
</div>';
        
        if (!empty($incident['notas_admin'])) {
            $html .= '<div class="section">
<h3>Notas del Administrador</h3>
<div class="description-box">' . nl2br(htmlspecialchars($incident['notas_admin'])) . '</div>
</div>';
        }
        
        $html .= '<div class="footer">
<p>Este reporte fue generado automáticamente por el Sistema de Gestión de Condominio</p>
<p>Para cualquier consulta, por favor contacte a la administración</p>
</div>
</body></html>';
        
        return $html;
    }
}
?>
