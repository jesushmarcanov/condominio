<?php $page_title = 'Resumen Financiero'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-file-invoice-dollar"></i> Resumen Financiero</h1>
        <p class="text-muted">Análisis financiero detallado por año</p>
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
                <form method="GET" action="<?= APP_URL ?>/reports/financialSummary">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="year" class="form-label">Año</label>
                            <select class="form-select" id="year" name="year">
                                <?php for($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="<?= APP_URL ?>/reports/financialSummary?year=<?= $year ?>&export=csv" 
                                   class="btn btn-success">
                                    <i class="fas fa-file-csv"></i> Exportar CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Resumen General -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5><i class="fas fa-dollar-sign"></i> Total Ingresos</h5>
                <h3><?= formatCurrency($total_ingresos) ?></h3>
                <small>Año <?= $year ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5><i class="fas fa-receipt"></i> Total Pagos</h5>
                <h3><?= $total_pagos ?></h3>
                <small>Transacciones realizadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5><i class="fas fa-chart-line"></i> Promedio Mensual</h5>
                <h3><?= formatCurrency($total_ingresos / 12) ?></h3>
                <small>Promedio de ingresos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5><i class="fas fa-percentage"></i> Crecimiento</h5>
                <h3>+12.5%</h3>
                <small>vs año anterior</small>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico Principal -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Evolución Financiera Mensual</h5>
            </div>
            <div class="card-body">
                <canvas id="financialChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Detallada -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-table"></i> Detalle Mensual</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Mes</th>
                                <th>Ingresos</th>
                                <th>Pagos Realizados</th>
                                <th>Pagos Pendientes</th>
                                <th>Tasa Cobro</th>
                                <th>Promedio Pago</th>
                                <th>Tendencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            foreach($data as $row): 
                                $tasa_cobro = $row['pagos_realizados'] + $row['pagos_pendientes'] > 0 ? 
                                             ($row['pagos_realizados'] / ($row['pagos_realizados'] + $row['pagos_pendientes'])) * 100 : 0;
                                $promedio_pago = $row['pagos_realizados'] > 0 ? $row['ingresos'] / $row['pagos_realizados'] : 0;
                            ?>
                                <tr>
                                    <td><strong><?= $meses[$row['mes'] - 1] ?></strong></td>
                                    <td class="fw-bold text-success"><?= formatCurrency($row['ingresos']) ?></td>
                                    <td><?= $row['pagos_realizados'] ?></td>
                                    <td><?= $row['pagos_pendientes'] ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-<?= $tasa_cobro >= 90 ? 'success' : ($tasa_cobro >= 70 ? 'warning' : 'danger') ?>" 
                                                 role="progressbar" style="width: <?= $tasa_cobro ?>%">
                                                <?= round($tasa_cobro, 1) ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= formatCurrency($promedio_pago) ?></td>
                                    <td>
                                        <?php if($row['ingresos'] > 0): ?>
                                            <i class="fas fa-arrow-up text-success"></i> 
                                            <span class="text-success">Positivo</span>
                                        <?php else: ?>
                                            <i class="fas fa-minus text-muted"></i> 
                                            <span class="text-muted">Neutro</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th>TOTAL</th>
                                <th class="fw-bold text-success"><?= formatCurrency($total_ingresos) ?></th>
                                <th><?= $total_pagos ?></th>
                                <th><?= array_sum(array_column($data, 'pagos_pendientes')) ?></th>
                                <th><?= round(array_sum(array_column($data, 'pagos_realizados')) / $total_pagos * 100, 1) ?>%</th>
                                <th><?= formatCurrency($total_ingresos / $total_pagos) ?></th>
                                <th>
                                    <i class="fas fa-chart-line text-success"></i> 
                                    <span class="text-success">Crecimiento</span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Métricas Adicionales -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Distribución Mensual</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyDistributionChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-percentage"></i> Tasa de Cobro Mensual</h5>
            </div>
            <div class="card-body">
                <canvas id="collectionRateChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Comparación Trimestral</h5>
            </div>
            <div class="card-body">
                <canvas id="quarterlyComparisonChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Proyecciones -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Proyecciones y Metas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted">Meta Anual</h6>
                            <h4 class="text-primary"><?= formatCurrency($total_ingresos * 1.15) ?></h4>
                            <small class="text-success">+15% objetivo</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted">Proyección Q1</h6>
                            <h4 class="text-info"><?= formatCurrency($total_ingresos * 0.25) ?></h4>
                            <small class="text-muted">Primer trimestre</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted">Meta Tasa Cobro</h6>
                            <h4 class="text-success">95%</h4>
                            <small class="text-muted">Objetivo anual</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted">Ingresos Extra</h6>
                            <h4 class="text-warning"><?= formatCurrency($total_ingresos * 0.05) ?></h4>
                            <small class="text-muted">Servicios adicionales</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress {
    background-color: #e9ecef;
}

.progress-bar {
    font-size: 0.8rem;
}

.table th {
    font-weight: 600;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.text-center h4 {
    font-size: 1.5rem;
    font-weight: bold;
}
</style>

<script>
// Datos para los gráficos
const financialData = <?= json_encode($data) ?>;
const totalIngresos = <?= $total_ingresos ?>;
const totalPagos = <?= $total_pagos ?>;
const year = <?= $year ?>;
</script>

<?php 
$scripts = ['financial-summary.js'];
include APP_PATH . '/views/layouts/footer.php'; 
?>
