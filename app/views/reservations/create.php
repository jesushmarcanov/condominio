<?php $page_title = 'Nueva Reserva'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-calendar-plus"></i> Nueva Reserva</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if(isset($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= APP_URL ?>/reservations/create" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="area_comun_id" class="form-label">Área Común</label>
                        <select class="form-select" id="area_comun_id" name="area_comun_id" required>
                            <option value="">Seleccionar área</option>
                            <?php foreach($areas as $area): ?>
                                <option value="<?= $area['id'] ?>"
                                        <?= (isset($data['area_comun_id']) && $data['area_comun_id'] == $area['id']) || (isset($preselected_area) && $preselected_area == $area['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($area['nombre']) ?>
                                    <?= $area['capacidad'] ? ' - Capacidad: ' . $area['capacidad'] : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Debe seleccionar un área</div>
                    </div>

                    <?php if($is_admin): ?>
                    <div class="mb-3">
                        <label for="residente_id" class="form-label">Residente</label>
                        <select class="form-select" id="residente_id" name="residente_id" required>
                            <option value="">Seleccionar residente</option>
                            <?php foreach($residents as $resident): ?>
                                <option value="<?= $resident['id'] ?>"
                                        <?= isset($data['residente_id']) && $data['residente_id'] == $resident['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($resident['nombre']) ?> - <?= htmlspecialchars($resident['apartamento']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Debe seleccionar un residente</div>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="residente_id" value="<?= $resident_id ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="fecha_reserva" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha_reserva" name="fecha_reserva"
                               value="<?= isset($data['fecha_reserva']) ? $data['fecha_reserva'] : date('Y-m-d') ?>"
                               min="<?= date('Y-m-d') ?>" required>
                        <div class="invalid-feedback">Debe seleccionar una fecha</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hora_inicio" class="form-label">Hora de inicio</label>
                                <input type="time" class="form-control" id="hora_inicio" name="hora_inicio"
                                       value="<?= isset($data['hora_inicio']) ? $data['hora_inicio'] : '' ?>" required>
                                <div class="invalid-feedback">Debe indicar la hora de inicio</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hora_fin" class="form-label">Hora de fin</label>
                                <input type="time" class="form-control" id="hora_fin" name="hora_fin"
                                       value="<?= isset($data['hora_fin']) ? $data['hora_fin'] : '' ?>" required>
                                <div class="invalid-feedback">Debe indicar la hora de fin</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/reservations" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-calendar-check"></i> Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
