<?php $page_title = 'Reservas'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-calendar-check"></i> <?= $is_admin ? 'Reservas' : 'Mis Reservas' ?></h1>
            <a href="<?= APP_URL ?>/reservations/create" class="btn btn-success">
                <i class="fas fa-plus"></i> Nueva Reserva
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="confirmada" <?= $status == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                            <option value="cancelada" <?= $status == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            <option value="completada" <?= $status == 'completada' ? 'selected' : '' ?>>Completada</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="<?= APP_URL ?>/reservations" class="btn btn-secondary ms-2">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Reservas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if(!empty($reservations)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Área</th>
                                    <?php if($is_admin): ?>
                                    <th>Residente</th>
                                    <th>Apartamento</th>
                                    <?php endif; ?>
                                    <th>Fecha</th>
                                    <th>Horario</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reservations as $res): ?>
                                <tr>
                                    <td>#<?= $res['id'] ?></td>
                                    <td><?= htmlspecialchars($res['area_nombre']) ?></td>
                                    <?php if($is_admin): ?>
                                    <td><?= htmlspecialchars($res['residente_nombre']) ?></td>
                                    <td><?= htmlspecialchars($res['apartamento']) ?></td>
                                    <?php endif; ?>
                                    <td><?= date('d/m/Y', strtotime($res['fecha_reserva'])) ?></td>
                                    <td><?= date('H:i', strtotime($res['hora_inicio'])) ?> - <?= date('H:i', strtotime($res['hora_fin'])) ?></td>
                                    <td>
                                        <?php
                                            $r_class = $res['estado'] == 'confirmada' ? 'success' : ($res['estado'] == 'completada' ? 'info' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $r_class ?>">
                                            <?= getCatalogLabel(RESERVATION_STATUSES, $res['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= APP_URL ?>/reservations/show/<?= $res['id'] ?>"
                                               class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($res['estado'] == 'confirmada'): ?>
                                            <a href="<?= APP_URL ?>/reservations/cancel/<?= $res['id'] ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('¿Deseas cancelar esta reserva?')"
                                               title="Cancelar">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                        <h5>No hay reservas registradas</h5>
                        <p class="text-muted">
                            <a href="<?= APP_URL ?>/reservations/create">Realiza la primera reserva</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
