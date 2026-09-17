<?php $page_title = 'Nuevo Medio de Pago'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-plus-circle"></i> Nuevo Medio de Pago</h1>
            <a href="<?= APP_URL ?>/medios-pago" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Medios de Pago
            </a>
        </div>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Datos del Medio de Pago</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/medios-pago/create" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required
                               value="<?= isset($data['nombre']) ? htmlspecialchars($data['nombre']) : '' ?>"
                               placeholder="Ej: Efectivo, Transferencia Bancaria, Pago Móvil...">
                        <small class="form-text text-muted">Nombre que se muestra en el select de pagos.</small>
                    </div>

                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor (slug)</label>
                        <input type="text" class="form-control" id="valor" name="valor" required
                               value="<?= isset($data['valor']) ? htmlspecialchars($data['valor']) : '' ?>"
                               placeholder="Ej: efectivo, transferencia, pago_movil..."
                               pattern="[a-z0-9_]+">
                        <small class="form-text text-muted">
                            Identificador único en minúsculas, números y guión bajo. Se guarda en <code>pagos.metodo_pago</code>.
                        </small>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="activa" name="activa"
                               value="1" <?= !isset($data['activa']) || $data['activa'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activa">Activo</label>
                        <small class="form-text text-muted d-block">Si está activo aparece en el formulario de pagos.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/medios-pago" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Medio de Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>