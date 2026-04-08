<?php $page_title = 'Detalles de Incidencia'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-eye"></i> Detalles de Incidencia</h1>
            <div>
                <a href="<?= APP_URL ?>/incidents" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <?php if($is_admin): ?>
                <a href="<?= APP_URL ?>/incidents/edit/<?= $incident['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-triangle"></i> Información de la Incidencia</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID de Incidencia:</strong> #<?= $incident['id'] ?></p>
                        <p><strong>Título:</strong> <?= $incident['titulo'] ?></p>
                        <p><strong>Residente:</strong> <?= $incident['residente_nombre'] ?></p>
                        <p><strong>Apartamento:</strong> <?= $incident['apartamento'] ?></p>
                        <p><strong>Email:</strong> <?= $incident['residente_email'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Categoría:</strong> 
                            <span class="badge bg-info"><?= ucfirst($incident['categoria']) ?></span>
                        </p>
                        <p><strong>Prioridad:</strong> 
                            <span class="priority-<?= $incident['prioridad'] ?>"><?= ucfirst($incident['prioridad']) ?></span>
                        </p>
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?= 
                                $incident['estado'] == 'resuelta' ? 'success' : 
                                ($incident['estado'] == 'en_proceso' ? 'warning' : 'danger') 
                            ?>">
                                <?= ucfirst($incident['estado']) ?>
                            </span>
                        </p>
                        <p><strong>Fecha de Reporte:</strong> <?= formatDate($incident['fecha_reporte']) ?></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6><strong>Descripción del Problema:</strong></h6>
                    <div class="alert alert-light">
                        <p class="mb-0"><?= nl2br($incident['descripcion']) ?></p>
                    </div>
                </div>
                
                <?php if($is_admin && $incident['notas_admin']): ?>
                <div class="mb-3">
                    <h6><strong>Notas del Administrador:</strong></h6>
                    <div class="alert alert-warning">
                        <p class="mb-0"><?= nl2br($incident['notas_admin']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($incident['fecha_resolucion']): ?>
                <div class="mb-3">
                    <h6><strong>Fecha de Resolución:</strong></h6>
                    <p><?= formatDate($incident['fecha_resolucion']) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha de Registro:</strong> <?= formatDate($incident['created_at']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Última Actualización:</strong> <?= formatDate($incident['updated_at']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Estado de la Incidencia -->
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Estado de la Incidencia</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <?php if($incident['estado'] == 'resuelta'): ?>
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                        <h5 class="mt-3 text-success">Resuelta</h5>
                        <p class="text-muted">La incidencia ha sido solucionada</p>
                    <?php elseif($incident['estado'] == 'en_proceso'): ?>
                        <i class="fas fa-tools fa-4x text-warning"></i>
                        <h5 class="mt-3 text-warning">En Proceso</h5>
                        <p class="text-muted">Se está trabajando en la solución</p>
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
                        <h5 class="mt-3 text-danger">Pendiente</h5>
                        <p class="text-muted">Esperando atención</p>
                    <?php endif; ?>
                </div>
                
                <?php if($is_admin): ?>
                <div class="d-grid gap-2">
                    <?php if($incident['estado'] == 'pendiente'): ?>
                    <button type="button" class="btn btn-warning" onclick="changeStatus('en_proceso')">
                        <i class="fas fa-tools"></i> Iniciar Proceso
                    </button>
                    <?php endif; ?>
                    
                    <?php if($incident['estado'] == 'en_proceso'): ?>
                    <button type="button" class="btn btn-success" onclick="changeStatus('resuelta')">
                        <i class="fas fa-check"></i> Marcar como Resuelta
                    </button>
                    <?php endif; ?>
                    
                    <button type="button" class="btn btn-outline-danger" onclick="changeStatus('cancelada')">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Información de Asignación -->
        <?php if($is_admin): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="fas fa-user-shield"></i> Asignación</h5>
            </div>
            <div class="card-body">
                <?php if($incident['admin_nombre']): ?>
                    <p><strong>Asignado a:</strong> <?= $incident['admin_nombre'] ?></p>
                    <p><strong>Fecha de Asignación:</strong> <?= formatDate($incident['updated_at']) ?></p>
                <?php else: ?>
                    <p class="text-muted">No asignado a ningún administrador</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="assignToMe()">
                        <i class="fas fa-user-plus"></i> Asignarme
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Acciones -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-cog"></i> Acciones</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= APP_URL ?>/incidents" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Ver Todas las Incidencias
                    </a>
                    
                    <?php if($is_admin): ?>
                    <a href="<?= APP_URL ?>/incidents/edit/<?= $incident['id'] ?>" class="btn btn-outline-warning">
                        <i class="fas fa-edit"></i> Editar Incidencia
                    </a>
                    
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Eliminar Incidencia
                    </button>
                    
                    <a href="<?= APP_URL ?>/pdf/incident-receipt/<?= $incident['id'] ?>" class="btn btn-outline-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= APP_URL ?>/residents/show/<?= $incident['residente_id'] ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-user"></i> Ver Residente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seguimiento de la Incidencia -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Seguimiento de la Incidencia</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <h6>Incidencia Reportada</h6>
                            <p class="text-muted">El residente reportó el problema</p>
                            <small class="text-muted"><?= formatDate($incident['fecha_reporte']) ?></small>
                        </div>
                    </div>
                    
                    <?php if($incident['estado'] == 'en_proceso' || $incident['estado'] == 'resuelta'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6>En Proceso</h6>
                            <p class="text-muted">Se inició el trabajo en la incidencia</p>
                            <small class="text-muted"><?= formatDate($incident['updated_at']) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($incident['estado'] == 'resuelta'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6>Resuelta</h6>
                            <p class="text-muted">La incidencia fue solucionada exitosamente</p>
                            <small class="text-muted"><?= formatDate($incident['fecha_resolucion']) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
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

<script>
function changeStatus(newStatus) {
    if(confirm('¿Está seguro de cambiar el estado a "' + newStatus + '"?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= APP_URL ?>/incidents/changeStatus/<?= $incident['id'] ?>';
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'estado';
        statusInput.value = newStatus;
        
        const notesInput = document.createElement('input');
        notesInput.type = 'hidden';
        notesInput.name = 'notas_admin';
        notesInput.value = 'Cambio de estado desde el dashboard';
        
        form.appendChild(statusInput);
        form.appendChild(notesInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function assignToMe() {
    if(confirm('¿Está seguro de asignarse esta incidencia?')) {
        showToast('Incidencia asignada correctamente', 'success');
        setTimeout(() => location.reload(), 1500);
    }
}

function confirmDelete() {
    if(confirm('¿Está seguro de que desea eliminar esta incidencia? Esta acción no se puede deshacer.')) {
        window.location.href = '<?= APP_URL ?>/incidents/delete/<?= $incident['id'] ?>';
    }
}
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
