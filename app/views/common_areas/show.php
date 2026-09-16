<?php $page_title = 'Detalle de Área Común'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <a href="<?= APP_URL ?>/common-areas" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Volver a Áreas Comunes
        </a>
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-tree"></i> <?= htmlspecialchars($area['nombre']) ?></h1>
            <?php
                $badge_class = $area['estado'] == 'disponible' ? 'success' : ($area['estado'] == 'mantenimiento' ? 'warning' : 'danger');
            ?>
            <span class="badge bg-<?= $badge_class ?> fs-6">
                <?= getCatalogLabel(COMMON_AREA_STATUSES, $area['estado']) ?>
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información del Área -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Información del Área</h5>
            </div>
            <div class="card-body">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($area['nombre']) ?></p>
                <p><strong>Descripción:</strong> <?= htmlspecialchars($area['descripcion'] ?: 'Sin descripción') ?></p>
                <?php if($area['capacidad']): ?>
                <p><strong>Capacidad:</strong> <?= $area['capacidad'] ?> personas</p>
                <?php endif; ?>
                <?php if($area['horario_disponible']): ?>
                <p><strong>Horario:</strong> <?= htmlspecialchars($area['horario_disponible']) ?></p>
                <?php endif; ?>
                <p><strong>Estado:</strong>
                    <span class="badge bg-<?= $badge_class ?>"><?= getCatalogLabel(COMMON_AREA_STATUSES, $area['estado']) ?></span>
                </p>
                <?php if($is_admin): ?>
                <div class="d-grid gap-2">
                    <a href="<?= APP_URL ?>/common-areas/edit/<?= $area['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar Área
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($area['estado'] == 'disponible'): ?>
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Reservar</h5>
            </div>
            <div class="card-body text-center">
                <p class="text-muted">¿Deseas reservar esta área común?</p>
                <a href="<?= APP_URL ?>/reservations/create?area_id=<?= $area['id'] ?>" class="btn btn-success">
                    <i class="fas fa-calendar-check"></i> Hacer Reserva
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Reservas del Área -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar"></i> Reservas del Área</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($reservations)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Horario</th>
                                    <th>Residente</th>
                                    <th>Apartamento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reservations as $res): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($res['fecha_reserva'])) ?></td>
                                    <td><?= date('H:i', strtotime($res['hora_inicio'])) ?> - <?= date('H:i', strtotime($res['hora_fin'])) ?></td>
                                    <td><?= htmlspecialchars($res['residente_nombre']) ?></td>
                                    <td><?= htmlspecialchars($res['apartamento']) ?></td>
                                    <td>
                                        <?php
                                            $r_class = $res['estado'] == 'confirmada' ? 'success' : ($res['estado'] == 'completada' ? 'info' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $r_class ?>">
                                            <?= getCatalogLabel(RESERVATION_STATUSES, $res['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No hay reservas registradas para esta área</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
