<?php $page_title = 'Resultado de Reporte Personalizado'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-chart-bar"></i> Resultado de Reporte Personalizado</h1>
        <p class="text-muted">Resultados del reporte generado</p>
    </div>
</div>

<!-- Información del Reporte -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Tipo de Reporte:</strong><br>
                        <span class="badge bg-primary">
                            <?php 
                            $report_types = [
                                'income' => 'Reporte de Ingresos',
                                'incidents' => 'Reporte de Incidencias',
                                'residents' => 'Reporte de Residentes',
                                'payments' => 'Reporte de Pagos'
                            ];
                            echo $report_types[$report_type] ?? 'Desconocido';
                            ?>
                        </span>
                    </div>
                    <div class="col-md-3">
                        <strong>Período:</strong><br>
                        <?= formatDate($start_date) ?> - <?= formatDate($end_date) ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Registros:</strong><br>
                        <span class="badge bg-success"><?= count($data) ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha Generación:</strong><br>
                        <?= formatDate(date('Y-m-d')) ?>
                    </div>
                </div>
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
                    <a href="<?= APP_URL ?>/reports/custom?export=csv&report_type=<?= $report_type ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                       class="btn btn-success">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Reporte
                    </button>
                    <a href="<?= APP_URL ?>/reports/custom" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Nuevo Reporte
                    </a>
                    <a href="<?= APP_URL ?>/reports" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> Todos los Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen Ejecutivo -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Resumen Ejecutivo</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <?php if($report_type === 'income'): ?>
                        <div class="col-md-3">
                            <h4 class="text-success"><?= formatCurrency(array_sum(array_column($data, 'monto'))) ?></h4>
                            <p class="text-muted">Total Ingresos</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info"><?= count($data) ?></h4>
                            <p class="text-muted">Total Pagos</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-primary"><?= formatCurrency(array_sum(array_column($data, 'monto')) / count($data)) ?></h4>
                            <p class="text-muted">Promedio</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning"><?= count(array_unique(array_column($data, 'residente_id'))) ?></h4>
                            <p class="text-muted">Residentes Únicos</p>
                        </div>
                    <?php elseif($report_type === 'incidents'): ?>
                        <div class="col-md-3">
                            <h4 class="text-danger"><?= count($data) ?></h4>
                            <p class="text-muted">Total Incidencias</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning"><?= count(array_filter($data, fn($i) => $i['estado'] === 'abierto')) ?></h4>
                            <p class="text-muted">Abiertas</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success"><?= count(array_filter($data, fn($i) => in_array($i['estado'], ['resuelto', 'cerrado']))) ?></h4>
                            <p class="text-muted">Resueltas</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info"><?= count(array_unique(array_column($data, 'categoria'))) ?></h4>
                            <p class="text-muted">Categorías</p>
                        </div>
                    <?php elseif($report_type === 'residents'): ?>
                        <div class="col-md-3">
                            <h4 class="text-primary"><?= count($data) ?></h4>
                            <p class="text-muted">Total Residentes</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success"><?= count(array_filter($data, fn($r) => $r['estado'] === 'activo')) ?></h4>
                            <p class="text-muted">Activos</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info"><?= count(array_unique(array_column($data, 'apartamento'))) ?></h4>
                            <p class="text-muted">Apartamentos</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning"><?= count(array_unique(array_column($data, 'piso'))) ?></h4>
                            <p class="text-muted">Pisos</p>
                        </div>
                    <?php elseif($report_type === 'payments'): ?>
                        <div class="col-md-3">
                            <h4 class="text-success"><?= formatCurrency(array_sum(array_column($data, 'monto'))) ?></h4>
                            <p class="text-muted">Total Pagado</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info"><?= count($data) ?></h4>
                            <p class="text-muted">Total Transacciones</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning"><?= count(array_filter($data, fn($p) => $p['estado'] === 'pendiente')) ?></h4>
                            <p class="text-muted">Pendientes</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-primary"><?= count(array_unique(array_column($data, 'metodo_pago'))) ?></h4>
                            <p class="text-muted">Métodos de Pago</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-table"></i> Detalle de Registros</h5>
            </div>
            <div class="card-body">
                <?php if(empty($data)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se encontraron registros con los filtros seleccionados.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <?php if($report_type === 'income' || $report_type === 'payments'): ?>
                                    <tr>
                                        <th>ID</th>
                                        <th>Residente</th>
                                        <th>Apartamento</th>
                                        <th>Monto</th>
                                        <th>Método</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                    </tr>
                                <?php elseif($report_type === 'incidents'): ?>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Categoría</th>
                                        <th>Prioridad</th>
                                        <th>Residente</th>
                                        <th>Estado</th>
                                        <th>Fecha Reporte</th>
                                        <th>Fecha Resolución</th>
                                    </tr>
                                <?php elseif($report_type === 'residents'): ?>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Apartamento</th>
                                        <th>Piso</th>
                                        <th>Estado</th>
                                        <th>Fecha Ingreso</th>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody>
                                <?php foreach($data as $item): ?>
                                    <?php if($report_type === 'income' || $report_type === 'payments'): ?>
                                        <tr>
                                            <td><?= $item['id'] ?></td>
                                            <td><?= htmlspecialchars($item['residente_nombre']) ?></td>
                                            <td><?= htmlspecialchars($item['apartamento']) ?></td>
                                            <td class="fw-bold text-success"><?= formatCurrency($item['monto']) ?></td>
                                            <td><?= htmlspecialchars($item['metodo_pago']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $item['estado'] === 'pagado' ? 'success' : 'warning' ?>">
                                                    <?= htmlspecialchars($item['estado']) ?>
                                                </span>
                                            </td>
                                            <td><?= formatDate($item['fecha_pago']) ?></td>
                                            <td><?= htmlspecialchars(substr($item['descripcion'], 0, 50)) ?>...</td>
                                        </tr>
                                    <?php elseif($report_type === 'incidents'): ?>
                                        <tr>
                                            <td><?= $item['id'] ?></td>
                                            <td><?= htmlspecialchars($item['titulo']) ?></td>
                                            <td><?= htmlspecialchars($item['categoria']) ?></td>
                                            <td><?= htmlspecialchars($item['prioridad']) ?></td>
                                            <td><?= htmlspecialchars($item['residente_nombre']) ?></td>
                                            <td><?= htmlspecialchars($item['estado']) ?></td>
                                            <td><?= formatDate($item['fecha_reporte']) ?></td>
                                            <td><?= $item['fecha_resolucion'] ? formatDate($item['fecha_resolucion']) : '-' ?></td>
                                        </tr>
                                    <?php elseif($report_type === 'residents'): ?>
                                        <tr>
                                            <td><?= $item['id'] ?></td>
                                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                                            <td><?= htmlspecialchars($item['email']) ?></td>
                                            <td><?= htmlspecialchars($item['telefono']) ?></td>
                                            <td><?= htmlspecialchars($item['apartamento']) ?></td>
                                            <td><?= htmlspecialchars($item['piso']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $item['estado'] === 'activo' ? 'success' : 'secondary' ?>">
                                                    <?= htmlspecialchars($item['estado']) ?>
                                                </span>
                                            </td>
                                            <td><?= formatDate($item['fecha_ingreso']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Distribución por Categoría</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Tendencia Temporal</h5>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="200"></canvas>
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

@media print {
    .btn, .d-flex.gap-2 {
        display: none !important;
    }
}

.text-center h4 {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}
</style>

<script>
// Datos para los gráficos
const reportData = <?= json_encode($data) ?>;
const reportType = '<?= $report_type ?>';
const startDate = '<?= $start_date ?>';
const endDate = '<?= $end_date ?>';
</script>

<?php 
$scripts = ['custom-reports.js'];
include APP_PATH . '/views/layouts/footer.php'; 
?>
