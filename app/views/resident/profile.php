<?php $page_title = 'Datos del Apartamento'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-home"></i> Datos del Apartamento</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Información del Apartamento</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Apartamento:</strong> <?= $resident['apartamento'] ?></p>
                        <p><strong>Piso:</strong> <?= $resident['piso'] ?></p>
                        <p><strong>Torre:</strong> <?= $resident['torre'] ?: 'N/A' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?= $resident['estado'] == 'activo' ? 'success' : 'secondary' ?>">
                                <?= $resident['estado'] ?>
                            </span>
                        </p>
                        <p><strong>Fecha de Ingreso:</strong> <?= formatDate($resident['fecha_ingreso']) ?></p>
                        <p><strong>Fecha de Registro:</strong> <?= formatDate($resident['created_at']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Datos del Residente</h5>
            </div>
            <div class="card-body">
                <p><strong>Nombre:</strong> <?= $resident['nombre'] ?></p>
                <p><strong>Email:</strong> <?= $resident['email'] ?></p>
                <p><strong>Teléfono:</strong> <?= $resident['telefono'] ?: 'No registrado' ?></p>
                <p><strong>Rol:</strong> <span class="badge bg-info">Residente</span></p>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas del Residente -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-dollar-sign"></i> Estadísticas de Pagos</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-success"><?= $stats['pagos_realizados'] ?></h4>
                        <p class="text-muted">Pagos Realizados</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning"><?= $stats['pagos_pendientes'] ?></h4>
                        <p class="text-muted">Pagos Pendientes</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="<?= APP_URL ?>/payments" class="btn btn-primary btn-sm">
                        <i class="fas fa-list"></i> Ver Todos los Pagos
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-triangle"></i> Estadísticas de Incidencias</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-danger"><?= $stats['incidencias_pendientes'] ?></h4>
                        <p class="text-muted">Pendientes</p>
                    </div>
                    <div class="col-4">
                        <h4 class="text-warning"><?= $stats['incidencias_en_proceso'] ?></h4>
                        <p class="text-muted">En Proceso</p>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success"><?= $stats['incidencias_resueltas'] ?></h4>
                        <p class="text-muted">Resueltas</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="<?= APP_URL ?>/incidents" class="btn btn-warning btn-sm">
                        <i class="fas fa-list"></i> Ver Todas las Incidencias
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= APP_URL ?>/payments" class="btn btn-outline-primary d-block">
                            <i class="fas fa-dollar-sign"></i> Mis Pagos
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= APP_URL ?>/incidents" class="btn btn-outline-warning d-block">
                            <i class="fas fa-exclamation-triangle"></i> Mis Incidencias
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= APP_URL ?>/incidents/create" class="btn btn-outline-danger d-block">
                            <i class="fas fa-plus"></i> Reportar Incidencia
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= APP_URL ?>/profile" class="btn btn-outline-info d-block">
                            <i class="fas fa-user-edit"></i> Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial Reciente -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Actividad Reciente</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($recent_activity)): ?>
                    <div class="timeline">
                        <?php foreach(array_slice($recent_activity, 0, 5) as $item): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?= $item['marker'] ?>"></div>
                            <div class="timeline-content">
                                <h6><?= $item['title'] ?></h6>
                                <p class="text-muted"><?= htmlspecialchars($item['description']) ?></p>
                                <small class="text-muted"><?= formatDate($item['date']) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Aún no tienes actividad registrada</p>
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
