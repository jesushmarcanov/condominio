<?php $page_title = 'Reporte de Ingresos'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-dollar-sign"></i> Reporte de Ingresos</h1>
        <p class="text-muted">Visualiza los ingresos generados por los pagos de cuotas</p>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-filter"></i> Filtros</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= APP_URL ?>/reports/income">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $start_date ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $end_date ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="<?= APP_URL ?>/excel/income?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                                   class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> Exportar Excel
                                </a>
                                <a href="<?= APP_URL ?>/pdf/income?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                                   class="btn btn-danger" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Descargar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5><i class="fas fa-dollar-sign"></i> Total Ingresos</h5>
                <h3><?= formatCurrency($total) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5><i class="fas fa-receipt"></i> Total Pagos</h5>
                <h3><?= count($payments) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5><i class="fas fa-chart-line"></i> Promedio</h5>
                <h3><?= formatCurrency(count($payments) > 0 ? $total / count($payments) : 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5><i class="fas fa-calendar"></i> Período</h5>
                <h3><?= formatDate($start_date) ?> - <?= formatDate($end_date) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Ingresos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Detalle de Ingresos</h5>
            </div>
            <div class="card-body">
                <?php if(empty($payments)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se encontraron pagos en el período seleccionado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Residente</th>
                                    <th>Apartamento</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th>Fecha Pago</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($payments as $payment): ?>
                                    <tr>
                                        <td><?= $payment['id'] ?></td>
                                        <td><?= htmlspecialchars($payment['residente_nombre']) ?></td>
                                        <td><?= htmlspecialchars($payment['apartamento']) ?></td>
                                        <td class="fw-bold text-success"><?= formatCurrency($payment['monto']) ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= htmlspecialchars($payment['metodo_pago']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $estado_class = $payment['estado'] === 'pagado' ? 'success' : 
                                                          ($payment['estado'] === 'pendiente' ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge bg-<?= $estado_class ?>">
                                                <?= htmlspecialchars($payment['estado']) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($payment['fecha_pago']) ?></td>
                                        <td><?= htmlspecialchars($payment['descripcion']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="3">TOTAL</th>
                                    <th class="fw-bold text-success"><?= formatCurrency($total) ?></th>
                                    <th colspan="4"><?= count($payments) ?> pagos</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de Ingresos -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Gráfico de Ingresos</h5>
            </div>
            <div class="card-body">
                <canvas id="incomeChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.85em;
}

.table th {
    font-weight: 600;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>

<?php 
$scripts = ['reports.js'];
include APP_PATH . '/views/layouts/footer.php'; 
?>
