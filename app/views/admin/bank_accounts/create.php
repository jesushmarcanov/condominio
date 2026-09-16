<?php $page_title = 'Nueva Cuenta Bancaria'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-plus-circle"></i> Nueva Cuenta Bancaria</h1>
            <a href="<?= APP_URL ?>/bank-accounts" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Cuentas
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
                <h5 class="mb-0"><i class="fas fa-university"></i> Datos de la Cuenta</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/bank-accounts/create" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="banco" class="form-label">Banco</label>
                                <select class="form-select" id="banco" name="banco" required>
                                    <option value="">Seleccionar banco</option>
                                    <?= getCatalogOptions(BANCOS_VENEZUELA, $data['banco'] ?? '', false) ?>
                                </select>
                                <div class="invalid-feedback">Seleccione el banco</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo de Cuenta</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="corriente" <?= (($data['tipo'] ?? '') === 'corriente') ? 'selected' : '' ?>>Corriente</option>
                                    <option value="ahorro" <?= (($data['tipo'] ?? '') === 'ahorro') ? 'selected' : '' ?>>Ahorro</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el tipo de cuenta</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="numero_cuenta" class="form-label">Número de Cuenta</label>
                        <input type="text" class="form-control" id="numero_cuenta" name="numero_cuenta"
                               value="<?= htmlspecialchars($data['numero_cuenta'] ?? '') ?>"
                               placeholder="Ej: 01020012345678901234" maxlength="30" required>
                        <div class="invalid-feedback">El número de cuenta es requerido</div>
                    </div>

                    <div class="mb-3">
                        <label for="titular" class="form-label">Titular de la Cuenta</label>
                        <input type="text" class="form-control" id="titular" name="titular"
                               value="<?= htmlspecialchars($data['titular'] ?? '') ?>"
                               placeholder="Nombre del titular (persona natural o jurídica)" maxlength="100" required>
                        <div class="invalid-feedback">El titular es requerido</div>
                    </div>

                    <hr>
                    <h6><i class="fas fa-mobile-alt"></i> Pago Móvil (opcional)</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pago_movil_telefono" class="form-label">Teléfono Pago Móvil</label>
                                <input type="text" class="form-control" id="pago_movil_telefono" name="pago_movil_telefono"
                                       value="<?= htmlspecialchars($data['pago_movil_telefono'] ?? '') ?>"
                                       placeholder="Ej: 04121234567" maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pago_movil_cedula" class="form-label">Cédula / RIF del Titular</label>
                                <input type="text" class="form-control" id="pago_movil_cedula" name="pago_movil_cedula"
                                       value="<?= htmlspecialchars($data['pago_movil_cedula'] ?? '') ?>"
                                       placeholder="Ej: V-12345678" maxlength="20">
                            </div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="activa" name="activa" checked>
                        <label class="form-check-label" for="activa">Cuenta activa (visible para los residentes)</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/bank-accounts" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cuenta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>