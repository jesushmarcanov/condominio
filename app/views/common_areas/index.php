<?php $page_title = 'Áreas Comunes'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-tree"></i> Áreas Comunes</h1>
            <?php if($is_admin): ?>
            <a href="<?= APP_URL ?>/common-areas/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Área
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Enlace a Reservas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><i class="fas fa-calendar-check"></i> Reservas</h5>
                    <p class="text-muted mb-0">
                        <?= $is_admin ? 'Administra todas las reservas de áreas comunes' : 'Consulta y gestiona tus reservas de áreas comunes' ?>
                    </p>
                </div>
                <a href="<?= APP_URL ?>/reservations" class="btn btn-outline-primary">
                    <i class="fas fa-list"></i> Ver Reservas
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Áreas -->
<div class="row">
    <?php if(!empty($areas)): ?>
        <?php foreach($areas as $area): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0">
                            <?= htmlspecialchars($area['nombre']) ?>
                        </h5>
                        <?php
                            $badge_class = $area['estado'] == 'disponible' ? 'success' : ($area['estado'] == 'mantenimiento' ? 'warning' : 'danger');
                        ?>
                        <span class="badge bg-<?= $badge_class ?>">
                            <?= getCatalogLabel(COMMON_AREA_STATUSES, $area['estado']) ?>
                        </span>
                    </div>
                    <p class="card-text text-muted">
                        <?= htmlspecialchars($area['descripcion'] ?: 'Sin descripción') ?>
                    </p>
                    <div class="mb-3">
                        <?php if($area['capacidad']): ?>
                        <span class="badge bg-secondary me-1">
                            <i class="fas fa-users"></i> Capacidad: <?= $area['capacidad'] ?>
                        </span>
                        <?php endif; ?>
                        <?php if($area['horario_disponible']): ?>
                        <span class="badge bg-secondary">
                            <i class="fas fa-clock"></i> <?= htmlspecialchars($area['horario_disponible']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= APP_URL ?>/common-areas/show/<?= $area['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> Ver Detalle
                        </a>
                        <?php if($area['estado'] == 'disponible'): ?>
                        <a href="<?= APP_URL ?>/reservations/create?area_id=<?= $area['id'] ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-calendar-plus"></i> Reservar
                        </a>
                        <?php endif; ?>
                        <?php if($is_admin): ?>
                        <a href="<?= APP_URL ?>/common-areas/edit/<?= $area['id'] ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger btn-delete"
                                onclick="confirmDelete('<?= APP_URL ?>/common-areas/delete/<?= $area['id'] ?>')"
                                title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-tree fa-3x text-muted mb-3"></i>
                <h5>No hay áreas comunes registradas</h5>
                <?php if($is_admin): ?>
                <p class="text-muted">
                    <a href="<?= APP_URL ?>/common-areas/create">Crea la primera área común</a>
                </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(url) {
    if(confirm('¿Está seguro de que desea eliminar esta área común? Esta acción no se puede deshacer.')) {
        window.location.href = url;
    }
}
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

