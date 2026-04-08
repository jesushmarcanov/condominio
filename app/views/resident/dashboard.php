<?php $page_title = 'Mi Dashboard'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-home"></i> Mi Dashboard</h1>
        <p class="text-muted">Bienvenido, <?= $user['nombre'] ?></p>
    </div>
</div>

<?php if(isset($stats['residente_info'])): ?>
<!-- Información del Apartamento -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <h5><i class="fas fa-home"></i> Información del Apartamento</h5>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Apartamento:</strong> <?= $stats['residente_info']['apartamento'] ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Piso:</strong> <?= $stats['residente_info']['piso'] ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Torre:</strong> <?= $stats['residente_info']['torre'] ?: 'N/A' ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Estado:</strong> 
                        <span class="badge bg-<?= $stats['residente_info']['estado'] == 'activo' ? 'success' : 'secondary' ?>">
                            <?= $stats['residente_info']['estado'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Acciones Rápidas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-dollar-sign fa-3x text-primary mb-3"></i>
                <h5>Mis Pagos</h5>
                <p class="text-muted">Consulta tu historial de pagos</p>
                <a href="<?= APP_URL ?>/payments" class="btn btn-primary">Ver Pagos</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Reportar Incidencia</h5>
                <p class="text-muted">Reporta un problema en tu apartamento</p>
                <a href="<?= APP_URL ?>/incidents/create" class="btn btn-warning">Nueva Incidencia</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-user-edit fa-3x text-info mb-3"></i>
                <h5>Mi Perfil</h5>
                <p class="text-muted">Actualiza tu información personal</p>
                <a href="<?= APP_URL ?>/profile" class="btn btn-info">Editar Perfil</a>
            </div>
        </div>
    </div>
</div>

<!-- Notificaciones Recientes -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-bell"></i> Notificaciones Recientes</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($stats['mis_notificaciones'])): ?>
                    <div class="list-group">
                        <?php foreach(array_slice($stats['mis_notificaciones'], 0, 3) as $notif): ?>
                        <div class="list-group-item <?= !$notif['leida'] ? 'list-group-item-light border-start border-3 border-primary' : '' ?>">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <?php if(!$notif['leida']): ?>
                                            <span class="badge bg-primary me-1">Nueva</span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($notif['titulo']) ?>
                                    </h6>
                                    <p class="mb-1 small"><?= htmlspecialchars(substr($notif['mensaje'], 0, 100)) ?>...</p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> <?= formatDate($notif['created_at']) ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?= 
                                    $notif['tipo'] == 'warning' ? 'warning' : 
                                    ($notif['tipo'] == 'error' ? 'danger' : 
                                    ($notif['tipo'] == 'success' ? 'success' : 'info'))
                                ?>">
                                    <?= ucfirst($notif['tipo']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?= APP_URL ?>/notifications" class="btn btn-sm btn-outline-primary">Ver todas las notificaciones</a>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No tienes notificaciones</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Mis Pagos Recientes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-dollar-sign"></i> Mis Pagos Recientes</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($stats['mis_pagos'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($stats['mis_pagos'], 0, 5) as $pago): ?>
                                <tr>
                                    <td><?= $pago['concepto'] ?></td>
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
                    <p class="text-muted">No tienes pagos registrados</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Mis Incidencias Recientes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-triangle"></i> Mis Incidencias</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($stats['mis_incidencias'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($stats['mis_incidencias'], 0, 5) as $incidencia): ?>
                                <tr>
                                    <td><?= $incidencia['titulo'] ?></td>
                                    <td><?= formatDate($incidencia['fecha_reporte']) ?></td>
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
                    <p class="text-muted">No tienes incidencias registradas</p>
                    <div class="text-center">
                        <a href="<?= APP_URL ?>/incidents/create" class="btn btn-sm btn-primary">Reportar Incidencia</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
