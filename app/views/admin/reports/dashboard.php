<?php $page_title = 'Dashboard Estadístico'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Estadístico</h1>
                <p class="text-muted">Gráficos interactivos y métricas en tiempo real</p>
            </div>
            <div>
                <a href="<?= APP_URL ?>/reports/dashboard?export=csv" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> Exportar CSV
                </a>
                <a href="<?= APP_URL ?>/reports" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Generales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5><i class="fas fa-users"></i> Total Residentes</h5>
                <h3><?= $stats['total_residentes'] ?></h3>
                <small>Activos: <?= $stats['residentes_activos'] ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5><i class="fas fa-dollar-sign"></i> Ingresos Totales</h5>
                <h3><?= formatCurrency($stats['total_ingresos']) ?></h3>
                <small>Mes actual: <?= formatCurrency($stats['ingresos_mes']) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5><i class="fas fa-tools"></i> Incidencias</h5>
                <h3><?= $stats['total_incidencias'] ?></h3>
                <small>Abiertas: <?= $stats['incidencias_abiertas'] ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5><i class="fas fa-clock"></i> Pagos Pendientes</h5>
                <h3><?= $stats['pagos_pendientes'] ?></h3>
                <small>Atrasados: <?= $stats['pagos_atrasados'] ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos Principales -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Ingresos Mensuales</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyIncomeChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Métodos de Pago</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentMethodsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos Secundarios -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Incidencias por Categoría</h5>
            </div>
            <div class="card-body">
                <canvas id="incidentsCategoryChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Incidencias Mensuales</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyIncidentsChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Métricas Detalladas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-percentage"></i> Tasa de Cobro</h5>
            </div>
            <div class="card-body text-center">
                <div class="progress mb-3" style="height: 25px;">
                    <?php 
                    $tasa_cobro = $stats['total_pagos'] > 0 ? 
                                 ($stats['pagos_realizados'] / $stats['total_pagos']) * 100 : 0;
                    ?>
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: <?= $tasa_cobro ?>%">
                        <?= round($tasa_cobro, 1) ?>%
                    </div>
                </div>
                <small class="text-muted">
                    <?= $stats['pagos_realizados'] ?> de <?= $stats['total_pagos'] ?> pagos realizados
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clock"></i> Tiempo Promedio Resolución</h5>
            </div>
            <div class="card-body text-center">
                <h3 class="text-info"><?= $stats['tiempo_promedio_resolucion'] ?> días</h3>
                <small class="text-muted">Promedio de resolución de incidencias</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-building"></i> Ocupación</h5>
            </div>
            <div class="card-body text-center">
                <div class="progress mb-3" style="height: 25px;">
                    <?php 
                    $ocupacion = ($stats['residentes_activos'] / $stats['total_apartamentos']) * 100;
                    ?>
                    <div class="progress-bar bg-primary" role="progressbar" 
                         style="width: <?= $ocupacion ?>%">
                        <?= round($ocupacion, 1) ?>%
                    </div>
                </div>
                <small class="text-muted">
                    <?= $stats['residentes_activos'] ?> de <?= $stats['total_apartamentos'] ?> apartamentos ocupados
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Actividad Reciente</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-primary">Últimos Pagos</h6>
                        <div id="recentPayments">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Juan Pérez - Apto 101</span>
                                <span class="badge bg-success">$500</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Maria García - Apto 205</span>
                                <span class="badge bg-success">$500</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Carlos López - Apto 310</span>
                                <span class="badge bg-success">$500</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-warning">Incidencias Nuevas</h6>
                        <div id="recentIncidents">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Fuga en baño</span>
                                <span class="badge bg-danger">Alta</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Luz pasillo</span>
                                <span class="badge bg-warning">Media</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Aire acondicionado</span>
                                <span class="badge bg-info">Baja</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-info">Residentes Nuevos</h6>
                        <div id="recentResidents">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Ana Rodríguez</span>
                                <small class="text-muted">Apto 402</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Luis Martínez</span>
                                <small class="text-muted">Apto 215</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Sofía Hernández</span>
                                <small class="text-muted">Apto 108</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botones de Acción -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Actualizar Dashboard
                    </button>
                    <a href="<?= APP_URL ?>/reports/financialSummary" class="btn btn-success">
                        <i class="fas fa-file-invoice-dollar"></i> Resumen Financiero
                    </a>
                    <a href="<?= APP_URL ?>/reports/custom" class="btn btn-info">
                        <i class="fas fa-cog"></i> Reporte Personalizado
                    </a>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.progress {
    background-color: #e9ecef;
}

.badge {
    font-size: 0.8em;
}

@media print {
    .btn, .d-flex.gap-2 {
        display: none !important;
    }
}
</style>

<script>
// Datos para los gráficos
const monthlyIncomeData = <?= json_encode($monthly_income) ?>;
const incidentsCategoryData = <?= json_encode($incidents_by_category) ?>;
const monthlyIncidentsData = <?= json_encode($monthly_incidents) ?>;
const paymentMethodsData = <?= json_encode($payment_methods) ?>;

function refreshDashboard() {
    location.reload();
}
</script>

<?php 
$scripts = ['dashboard.js'];
include APP_PATH . '/views/layouts/footer.php'; 
?>
