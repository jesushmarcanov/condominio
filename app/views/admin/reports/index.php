<?php $page_title = 'Reportes'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-chart-bar"></i> Reportes y Estadísticas</h1>
        <p class="text-muted">Genera y visualiza reportes detallados del condominio</p>
    </div>
</div>

<div class="row">
    <!-- Reporte de Ingresos -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-dollar-sign fa-3x text-success mb-3"></i>
                <h5>Reporte de Ingresos</h5>
                <p class="text-muted">Visualiza los ingresos generados por los pagos de cuotas</p>
                <a href="<?= APP_URL ?>/reports/income" class="btn btn-success">
                    <i class="fas fa-chart-line"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Pagos Pendientes -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Pagos Pendientes</h5>
                <p class="text-muted">Lista de residentes con pagos pendientes o atrasados</p>
                <a href="<?= APP_URL ?>/reports/pendingPayments" class="btn btn-warning">
                    <i class="fas fa-clock"></i> Ver Pendientes
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte de Incidencias -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-tools fa-3x text-info mb-3"></i>
                <h5>Reporte de Incidencias</h5>
                <p class="text-muted">Estadísticas y análisis de incidencias reportadas</p>
                <a href="<?= APP_URL ?>/reports/incidents" class="btn btn-info">
                    <i class="fas fa-chart-pie"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte de Residentes -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h5>Reporte de Residentes</h5>
                <p class="text-muted">Información detallada de todos los residentes</p>
                <a href="<?= APP_URL ?>/reports/residents" class="btn btn-primary">
                    <i class="fas fa-list"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Dashboard Estadístico -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-tachometer-alt fa-3x text-secondary mb-3"></i>
                <h5>Dashboard Estadístico</h5>
                <p class="text-muted">Gráficos interactivos y métricas en tiempo real</p>
                <a href="<?= APP_URL ?>/reports/dashboard" class="btn btn-secondary">
                    <i class="fas fa-chart-area"></i> Ver Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte Personalizado -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-cog fa-3x text-dark mb-3"></i>
                <h5>Reporte Personalizado</h5>
                <p class="text-muted">Crea reportes con filtros personalizados</p>
                <a href="<?= APP_URL ?>/reports/custom" class="btn btn-dark">
                    <i class="fas fa-filter"></i> Crear Reporte
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Rápidas -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Estadísticas Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="stat-item">
                            <h4 class="text-success"><?= $stats['residentes']['residentes_activos'] ?></h4>
                            <p class="text-muted">Total Residentes</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <h4 class="text-info"><?= formatCurrency($stats['ingresos_mensuales']) ?></h4>
                            <p class="text-muted">Ingresos Mensuales</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <h4 class="text-warning"><?= $stats['incidencias_activas'] ?></h4>
                            <p class="text-muted">Incidencias Activas</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <h4 class="text-danger"><?= $stats['pagos']['pagos_pendientes'] ?></h4>
                            <p class="text-muted">Pagos Pendientes</p>
                        </div>
                    </div>
                </div>
                
                <!-- Métricas Adicionales -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="stat-item">
                            <h6 class="text-primary"><?= formatCurrency($stats['pagos']['total_ingresos']) ?></h6>
                            <p class="text-muted small">Total Ingresos</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <h6 class="text-success"><?= $stats['incidencias']['incidencias_resueltas'] ?></h6>
                            <p class="text-muted small">Incidencias Resueltas</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <h6 class="text-info"><?= $stats['pagos']['pagos_realizados'] ?></h6>
                            <p class="text-muted small">Pagos Realizados</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <h6 class="text-secondary"><?= number_format(($stats['incidencias']['incidencias_resueltas'] / max($stats['incidencias']['total_incidencias'], 1)) * 100, 1) ?>%</h6>
                            <p class="text-muted small">Tasa de Resolución</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-item h4 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
