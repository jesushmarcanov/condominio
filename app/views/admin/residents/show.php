<?php $page_title = 'Detalles del Residente'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-user"></i> Detalles del Residente</h1>
            <div>
                <a href="<?= APP_URL ?>/residents" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= APP_URL ?>/residents/edit/<?= $resident['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> <?= $resident['id'] ?></p>
                        <p><strong>Nombre:</strong> <?= $resident['nombre'] ?></p>
                        <p><strong>Email:</strong> <?= $resident['email'] ?></p>
                        <p><strong>Teléfono:</strong> <?= $resident['telefono'] ?: 'No registrado' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?= $resident['estado'] == 'activo' ? 'success' : 'secondary' ?>">
                                <?= $resident['estado'] ?>
                            </span>
                        </p>
                        <p><strong>Fecha de Registro:</strong> <?= formatDate($resident['created_at']) ?></p>
                        <p><strong>Última Actualización:</strong> <?= formatDate($resident['updated_at']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-home"></i> Información del Apartamento</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Apartamento:</strong> <?= $resident['apartamento'] ?></p>
                        <p><strong>Piso:</strong> <?= $resident['piso'] ?></p>
                        <p><strong>Torre:</strong> <?= $resident['torre'] ?: 'N/A' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha de Ingreso:</strong> <?= formatDate($resident['fecha_ingreso']) ?></p>
                        <p><strong>Tiempo en el Condominio:</strong> 
                            <?php
                            $ingreso = new DateTime($resident['fecha_ingreso']);
                            $hoy = new DateTime();
                            $diferencia = $ingreso->diff($hoy);
                            echo $diferencia->y > 0 ? $diferencia->y . ' años' : '';
                            echo $diferencia->m > 0 ? ' ' . $diferencia->m . ' meses' : '';
                            echo $diferencia->d > 0 ? ' ' . $diferencia->d . ' días' : '';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Estadísticas del Residente -->
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-success"><?= $stats['total_pagos'] ?? 0 ?></h4>
                        <p class="text-muted">Pagos Realizados</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning"><?= $stats['pagos_pendientes'] ?? 0 ?></h4>
                        <p class="text-muted">Pagos Pendientes</p>
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <h4 class="text-info"><?= $stats['total_incidencias'] ?? 0 ?></h4>
                        <p class="text-muted">Incidencias Reportadas</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-primary"><?= formatCurrency($stats['total_pagado'] ?? 0) ?></h4>
                        <p class="text-muted">Total Pagado</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= APP_URL ?>/payments?residente_id=<?= $resident['id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-dollar-sign"></i> Ver Pagos
                    </a>
                    <a href="<?= APP_URL ?>/incidents?residente_id=<?= $resident['id'] ?>" class="btn btn-outline-warning">
                        <i class="fas fa-exclamation-triangle"></i> Ver Incidencias
                    </a>
                    <a href="<?= APP_URL ?>/payments/create?residente_id=<?= $resident['id'] ?>" class="btn btn-outline-success">
                        <i class="fas fa-plus"></i> Registrar Pago
                    </a>
                    <a href="<?= APP_URL ?>/incidents/create?residente_id=<?= $resident['id'] ?>" class="btn btn-outline-danger">
                        <i class="fas fa-plus"></i> Reportar Incidencia
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Estado de Cuenta -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-credit-card"></i> Estado de Cuenta</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Pagado:</span>
                        <strong class="text-success"><?= formatCurrency($stats['total_pagado'] ?? 0) ?></strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Pendiente:</span>
                        <strong class="text-warning"><?= formatCurrency($stats['total_pendiente'] ?? 0) ?></strong>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Saldo Total:</span>
                        <strong class="text-primary"><?= formatCurrency(($stats['total_pagado'] ?? 0) + ($stats['total_pendiente'] ?? 0)) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Actividad -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Actividad Reciente</h5>
            </div>
            <div class="card-body">
                <?php if(isset($recent_activity) && count($recent_activity) > 0): ?>
                    <div class="timeline">
                        <?php foreach($recent_activity as $activity): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?= $activity['type'] == 'payment' ? 'success' : 'warning' ?>"></div>
                                <div class="timeline-content">
                                    <h6><?= $activity['title'] ?></h6>
                                    <p class="text-muted"><?= $activity['description'] ?></p>
                                    <small class="text-muted"><?= formatDate($activity['date']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No hay actividad reciente registrada.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-content h6 {
    margin-bottom: 5px;
    color: #495057;
}

.timeline-content p {
    margin-bottom: 5px;
}
</style>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
