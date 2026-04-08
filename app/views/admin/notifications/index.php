<?php $page_title = 'Gestión de Notificaciones'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-bell"></i> Gestión de Notificaciones</h1>
        </div>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="text-primary"><?= isset($stats['total_notificaciones']) ? $stats['total_notificaciones'] : 0 ?></h4>
                        <p class="mb-0">Total Generadas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-bell fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="text-success"><?= isset($stats['notificaciones_leidas']) ? $stats['notificaciones_leidas'] : 0 ?></h4>
                        <p class="mb-0">Leídas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="text-warning"><?= isset($stats['notificaciones_no_leidas']) ? $stats['notificaciones_no_leidas'] : 0 ?></h4>
                        <p class="mb-0">No Leídas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= APP_URL ?>/notifications/admin" class="row g-3">
                    <div class="col-md-4">
                        <label for="usuario_id" class="form-label">Residente</label>
                        <select class="form-select" id="usuario_id" name="usuario_id">
                            <option value="">Todos los residentes</option>
                            <?php if(isset($residents) && count($residents) > 0): ?>
                                <?php foreach($residents as $resident): ?>
                                    <option value="<?= $resident['usuario_id'] ?>" 
                                            <?= isset($filters['usuario_id']) && $filters['usuario_id'] == $resident['usuario_id'] ? 'selected' : '' ?>>
                                        <?= $resident['nombre'] ?> - Apto <?= $resident['apartamento'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo">
                            <option value="">Todos los tipos</option>
                            <option value="info" <?= isset($filters['tipo']) && $filters['tipo'] == 'info' ? 'selected' : '' ?>>Info</option>
                            <option value="warning" <?= isset($filters['tipo']) && $filters['tipo'] == 'warning' ? 'selected' : '' ?>>Warning</option>
                            <option value="success" <?= isset($filters['tipo']) && $filters['tipo'] == 'success' ? 'selected' : '' ?>>Success</option>
                            <option value="error" <?= isset($filters['tipo']) && $filters['tipo'] == 'error' ? 'selected' : '' ?>>Error</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="leida" class="form-label">Estado de Lectura</label>
                        <select class="form-select" id="leida" name="leida">
                            <option value="">Todas</option>
                            <option value="0" <?= isset($filters['leida']) && $filters['leida'] === false ? 'selected' : '' ?>>No Leídas</option>
                            <option value="1" <?= isset($filters['leida']) && $filters['leida'] === true ? 'selected' : '' ?>>Leídas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="<?= APP_URL ?>/notifications/admin" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Notificaciones -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if(isset($notifications) && count($notifications) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Residente</th>
                                    <th>Email</th>
                                    <th>Apartamento</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($notifications as $notification): ?>
                                    <tr>
                                        <td><?= $notification['id'] ?></td>
                                        <td><?= $notification['nombre'] ?></td>
                                        <td><?= $notification['email'] ?></td>
                                        <td>
                                            <?php if(isset($notification['apartamento'])): ?>
                                                Apto <?= $notification['apartamento'] ?>
                                                <?php if(isset($notification['torre']) && $notification['torre']): ?>
                                                    - Torre <?= $notification['torre'] ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $notification['titulo'] ?></td>
                                        <td>
                                            <?php
                                            $badge_class = 'secondary';
                                            switch($notification['tipo']) {
                                                case 'info': $badge_class = 'info'; break;
                                                case 'warning': $badge_class = 'warning'; break;
                                                case 'success': $badge_class = 'success'; break;
                                                case 'error': $badge_class = 'danger'; break;
                                            }
                                            ?>
                                            <span class="badge bg-<?= $badge_class ?>">
                                                <?= ucfirst($notification['tipo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $notification['leida'] ? 'success' : 'warning' ?>">
                                                <?= $notification['leida'] ? 'Leída' : 'No Leída' ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($notification['created_at']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#notificationModal<?= $notification['id'] ?>"
                                                        title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="<?= APP_URL ?>/notifications/admin?usuario_id=<?= $notification['usuario_id'] ?>" 
                                                   class="btn btn-outline-info" 
                                                   title="Ver historial del residente">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal para ver detalles -->
                                    <div class="modal fade" id="notificationModal<?= $notification['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?= $notification['titulo'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Residente:</strong> <?= $notification['nombre'] ?></p>
                                                    <p><strong>Email:</strong> <?= $notification['email'] ?></p>
                                                    <?php if(isset($notification['apartamento'])): ?>
                                                        <p><strong>Apartamento:</strong> 
                                                            Apto <?= $notification['apartamento'] ?>
                                                            <?php if(isset($notification['piso'])): ?>
                                                                - Piso <?= $notification['piso'] ?>
                                                            <?php endif; ?>
                                                            <?php if(isset($notification['torre']) && $notification['torre']): ?>
                                                                - Torre <?= $notification['torre'] ?>
                                                            <?php endif; ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <p><strong>Tipo:</strong> <span class="badge bg-<?= $badge_class ?>"><?= ucfirst($notification['tipo']) ?></span></p>
                                                    <p><strong>Estado:</strong> <span class="badge bg-<?= $notification['leida'] ? 'success' : 'warning' ?>"><?= $notification['leida'] ? 'Leída' : 'No Leída' ?></span></p>
                                                    <p><strong>Fecha:</strong> <?= formatDate($notification['created_at']) ?></p>
                                                    <hr>
                                                    <p><strong>Mensaje:</strong></p>
                                                    <p><?= nl2br($notification['mensaje']) ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron notificaciones</h5>
                        <p class="text-muted">No hay notificaciones registradas o no coinciden con los filtros aplicados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
