<?php
/**
 * Vista de Reporte de Pagos
 * 
 * Muestra un reporte detallado de pagos con filtros de fecha y estado.
 * Permite exportar los datos a CSV.
 */

$page_title = 'Reporte de Pagos';
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Reporte de Pagos
                    </h3>
                    <div class="card-tools">
                        <a href="<?= APP_URL ?>/reports/income?export=csv&start_date=<?= htmlspecialchars($start_date) ?>&end_date=<?= htmlspecialchars($end_date) ?>" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-csv"></i> Exportar CSV
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" action="<?= APP_URL ?>/payments/report" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha Inicio</label>
                                    <input type="date" name="start_date" class="form-control" 
                                           value="<?= htmlspecialchars($start_date) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha Fin</label>
                                    <input type="date" name="end_date" class="form-control" 
                                           value="<?= htmlspecialchars($end_date) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Resumen -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Pagos</span>
                                    <span class="info-box-number"><?= count($payments) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Monto</span>
                                    <span class="info-box-number">$<?= number_format(array_sum(array_column($payments, 'monto')), 2) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Período</span>
                                    <span class="info-box-number"><?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de Pagos -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Residente</th>
                                    <th>Apartamento</th>
                                    <th>Concepto</th>
                                    <th>Mes</th>
                                    <th>Fecha Pago</th>
                                    <th>Monto</th>
                                    <th>Mora</th>
                                    <th>Total</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($payments)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center">No se encontraron pagos en el período seleccionado</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($payments as $payment): ?>
                                        <tr>
                                            <td><?= $payment['id'] ?></td>
                                            <td><?= htmlspecialchars($payment['residente_nombre'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($payment['apartamento'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($payment['concepto']) ?></td>
                                            <td><?= htmlspecialchars($payment['mes_pago']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($payment['fecha_pago'])) ?></td>
                                            <td>$<?= number_format($payment['monto_original'] ?? $payment['monto'], 2) ?></td>
                                            <td>
                                                <?php if(isset($payment['monto_mora']) && $payment['monto_mora'] > 0): ?>
                                                    <span class="badge badge-warning">$<?= number_format($payment['monto_mora'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">$0.00</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong>$<?= number_format(($payment['monto_original'] ?? $payment['monto']) + ($payment['monto_mora'] ?? 0), 2) ?></strong></td>
                                            <td><?= ucfirst($payment['metodo_pago']) ?></td>
                                            <td>
                                                <?php
                                                $badge_class = 'secondary';
                                                switch($payment['estado']) {
                                                    case 'pagado':
                                                        $badge_class = 'success';
                                                        break;
                                                    case 'pendiente':
                                                        $badge_class = 'warning';
                                                        break;
                                                    case 'atrasado':
                                                        $badge_class = 'danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $badge_class ?>">
                                                    <?= ucfirst($payment['estado']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if(!empty($payments)): ?>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="6" class="text-right">TOTALES:</td>
                                        <td>$<?= number_format(array_sum(array_column($payments, 'monto_original') ?: array_column($payments, 'monto')), 2) ?></td>
                                        <td>$<?= number_format(array_sum(array_column($payments, 'monto_mora')), 2) ?></td>
                                        <td><strong>$<?= number_format(array_sum(array_map(function($p) { 
                                            return ($p['monto_original'] ?? $p['monto']) + ($p['monto_mora'] ?? 0); 
                                        }, $payments)), 2) ?></strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
