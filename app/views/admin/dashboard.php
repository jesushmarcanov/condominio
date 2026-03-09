<?php $page_title = 'Dashboard Administrativo'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard Administrativo</h1>
        <p class="text-muted">Bienvenido, <?= $user['nombre'] ?></p>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= $stats['usuarios']['total_usuarios'] ?></h4>
                        <p class="mb-0">Usuarios Totales</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= $stats['residentes']['residentes_activos'] ?></h4>
                        <p class="mb-0">Residentes Activos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= formatCurrency($stats['pagos']['total_ingresos']) ?></h4>
                        <p class="mb-0">Total Ingresos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= $stats['incidencias']['incidencias_pendientes'] ?></h4>
                        <p class="mb-0">Incidencias Pendientes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y Tablas -->
<div class="row">
    <!-- Pagos Recientes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-dollar-sign"></i> Pagos Recientes</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($stats['pagos_recientes'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Residente</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($stats['pagos_recientes'], 0, 5) as $pago): ?>
                                <tr>
                                    <td><?= $pago['nombre'] ?></td>
                                    <td><?= formatCurrency($pago['monto']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $pago['estado'] == 'pagado' ? 'success' : 'warning' ?>">
                                            <?= $pago['estado'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?= APP_URL ?>/payments" class="btn btn-sm btn-outline-primary">Ver todos</a>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay pagos registrados</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Incidencias Recientes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-triangle"></i> Incidencias Recientes</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($stats['incidencias_recientes'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Residente</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($stats['incidencias_recientes'], 0, 5) as $incidencia): ?>
                                <tr>
                                    <td><?= $incidencia['titulo'] ?></td>
                                    <td><?= $incidencia['residente_nombre'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $incidencia['estado'] == 'resuelta' ? 'success' : 
                                            ($incidencia['estado'] == 'en_proceso' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= $incidencia['estado'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?= APP_URL ?>/incidents" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay incidencias registradas</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de Ingresos Mensuales -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Ingresos Mensuales</h5>
            </div>
            <div class="card-body">
                <canvas id="incomeChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<?php 
$scripts = ['dashboard.js']; 
include APP_PATH . '/views/layouts/footer.php'; 
?>
