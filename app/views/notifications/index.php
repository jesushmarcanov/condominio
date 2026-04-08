<?php $page_title = 'Mis Notificaciones'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-bell"></i> Mis Notificaciones</h1>
        </div>
    </div>
</div>

<!-- Lista de Notificaciones -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if(!empty($notifications)): ?>
                    <div class="list-group">
                        <?php foreach($notifications as $notification): ?>
                        <div class="list-group-item <?= !$notification['leida'] ? 'list-group-item-light border-start border-4 border-primary' : '' ?>">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0">
                                            <?php if(!$notification['leida']): ?>
                                                <span class="badge bg-primary me-2">Nueva</span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($notification['titulo']) ?>
                                        </h5>
                                    </div>
                                    <p class="mb-2"><?= nl2br(htmlspecialchars($notification['mensaje'])) ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> <?= formatDate($notification['created_at']) ?>
                                    </small>
                                </div>
                                <div class="ms-3">
                                    <span class="badge bg-<?= 
                                        $notification['tipo'] == 'warning' ? 'warning' : 
                                        ($notification['tipo'] == 'error' ? 'danger' : 
                                        ($notification['tipo'] == 'success' ? 'success' : 'info'))
                                    ?> mb-2">
                                        <?= ucfirst($notification['tipo']) ?>
                                    </span>
                                    <?php if(!$notification['leida']): ?>
                                    <form method="POST" action="<?= APP_URL ?>/notifications/markAsRead/<?= $notification['id'] ?>" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Marcar como leída">
                                            <i class="fas fa-check"></i> Marcar como leída
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Resumen -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Total de notificaciones:</strong> <?= count($notifications) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>No leídas:</strong> <?= count(array_filter($notifications, function($n) { return !$n['leida']; })) ?>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5>No tienes notificaciones</h5>
                        <p class="text-muted">Cuando tengas notificaciones, aparecerán aquí</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
