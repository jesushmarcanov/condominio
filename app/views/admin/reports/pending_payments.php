<?php $page_title = 'Pagos Pendientes'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-exclamation-triangle"></i> Pagos Pendientes</h1>
        <p class="text-muted">Lista de residentes con pagos pendientes o atrasados</p>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5><i class="fas fa-clock"></i> Total Pendiente</h5>
                <h3><?= formatCurrency($total_pendiente) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5><i class="fas fa-exclamation-circle"></i> Pagos Atrasados</h5>
                <h3><?= count(array_filter($payments, fn($p) => $p['estado'] === 'atrasado')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5><i class="fas fa-hourglass-half"></i> Pagos Pendientes</h5>
                <h3><?= count(array_filter($payments, fn($p) => $p['estado'] === 'pendiente')) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Acciones -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <a href="<?= APP_URL ?>/excel/pending-payments" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="<?= APP_URL ?>/pdf/pending-payments" class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                    <a href="<?= APP_URL ?>/reports" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Pagos Pendientes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Detalle de Pagos Pendientes</h5>
            </div>
            <div class="card-body">
                <?php if(empty($payments)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> No hay pagos pendientes en este momento.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Residente</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Apartamento</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Días Atraso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($payments as $payment): ?>
                                    <?php
                                    $dias_atraso = $payment['estado'] === 'atrasado' ? 
                                                  max(0, (strtotime(date('Y-m-d')) - strtotime($payment['fecha_vencimiento'])) / 86400) : 0;
                                    $estado_class = $payment['estado'] === 'atrasado' ? 'danger' : 'warning';
                                    ?>
                                    <tr>
                                        <td><?= $payment['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($payment['residente_nombre']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($payment['residente_email'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($payment['telefono'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($payment['apartamento']) ?></td>
                                        <td class="fw-bold text-warning"><?= formatCurrency($payment['monto']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $estado_class ?>">
                                                <?= htmlspecialchars($payment['estado']) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($payment['fecha_vencimiento']) ?></td>
                                        <td>
                                            <?php if($dias_atraso > 0): ?>
                                                <span class="badge bg-danger"><?= round($dias_atraso) ?> días</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/payments/show/<?= $payment['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="mailto:<?= htmlspecialchars($payment['residente_email'] ?? '') ?>?subject=Recordatorio de Pago - Condominio" 
                                                   class="btn btn-sm btn-outline-info" title="Enviar recordatorio">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                                <a href="<?= APP_URL ?>/payments/edit/<?= $payment['id'] ?>" 
                                                   class="btn btn-sm btn-outline-success" title="Marcar como pagado">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="5">TOTAL</th>
                                    <th class="fw-bold text-warning"><?= formatCurrency($total_pendiente) ?></th>
                                    <th colspan="4"><?= count($payments) ?> pagos pendientes</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Adicionales -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Distribución por Estado</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Montos por Rango</h5>
            </div>
            <div class="card-body">
                <canvas id="amountChart" height="200"></canvas>
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

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

@media print {
    .btn-group, .btn {
        display: none !important;
    }
}
</style>

<?php 
$scripts = ['reports.js'];
include APP_PATH . '/views/layouts/footer.php'; 
?>
