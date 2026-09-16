<?php $page_title = 'Nueva Área Común'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-plus"></i> Nueva Área Común</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php if(isset($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= APP_URL ?>/common-areas/create" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?= isset($data['nombre']) ? $data['nombre'] : '' ?>"
                               placeholder="Ej: Salón de Eventos" required>
                        <div class="invalid-feedback">El nombre es requerido</div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                  placeholder="Describa el área común..."><?= isset($data['descripcion']) ? $data['descripcion'] : '' ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacidad" class="form-label">Capacidad (personas)</label>
                                <input type="number" class="form-control" id="capacidad" name="capacidad"
                                       value="<?= isset($data['capacidad']) ? $data['capacidad'] : '' ?>"
                                       min="1" placeholder="Ej: 50">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="horario_disponible" class="form-label">Horario disponible</label>
                                <input type="text" class="form-control" id="horario_disponible" name="horario_disponible"
                                       value="<?= isset($data['horario_disponible']) ? $data['horario_disponible'] : '' ?>"
                                       placeholder="Ej: Lunes a Domingo 8:00 - 22:00">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <?= getCatalogOptions(COMMON_AREA_STATUSES, $data['estado'] ?? 'disponible') ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/common-areas" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Área
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
