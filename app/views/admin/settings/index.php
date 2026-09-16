<?php $page_title = 'Configuración General'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-cogs"></i> Configuración General</h1>
        </div>
    </div>
</div>

<?php
$rate = isset($config['tasa_cambio']) && $config['tasa_cambio'] !== null ? (float)$config['tasa_cambio'] : null;
$has_rate = $rate !== null && $rate > 0;
?>

<!-- Aviso si falta la tasa -->
<?php if (!$has_rate): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Falta configurar la tasa de cambio.</strong> El sistema mostrará los montos solo en
            <?= $config['moneda_base'] === 'VES' ? 'bolívares (Bs)' : 'dólares (USD)' ?>
            hasta que se establezca una tasa Bs/USD. Puede obtenerla del BCV o ingresarla manualmente.
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Moneda base -->
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-coins"></i> Moneda Base</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Es la moneda base con la que el sistema <strong>muestra</strong> los montos en el dashboard,
                    listados, recibos y reportes. La otra moneda se muestra como conversión en todas las pantallas.
                </p>
                <p class="text-muted small">
                    Al cambiarla <strong>los montos guardados no se modifican</strong>: cada registro conserva la
                    moneda con la que fue creado y el sistema convierte automáticamente al mostrar, usando la tasa vigente.
                </p>
                <form method="POST" action="<?= APP_URL ?>/settings" id="form_base" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="base">
                    <div class="mb-3">
                        <label for="moneda_base" class="form-label">Moneda base de visualización</label>
                        <select class="form-select" id="moneda_base" name="moneda_base" required>
                            <option value="USD" <?= $config['moneda_base'] === 'USD' ? 'selected' : '' ?>>USD - Dólar Estadounidense ($)</option>
                            <option value="VES" <?= $config['moneda_base'] === 'VES' ? 'selected' : '' ?>>VES - Bolívar (Bs)</option>
                        </select>
                    </div>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i>
                        Al cambiar la moneda base los montos guardados se <strong>conservan tal cual</strong> y se
                        convertirán de forma automática en todas las pantallas con la tasa vigente
                        <?= $has_rate ? '(1 USD = Bs ' . number_format($rate, 2) . ')' : 'una vez se configure la tasa' ?>.
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mt-2">
                        <i class="fas fa-save"></i> Guardar Moneda Base
                    </button>
                </form>
            </div>
        </div>

        <!-- Reflejo en la segunda moneda -->
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Reflejo en Ambas Monedas</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/settings">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="toggle">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggle_dual" name="activo" value="1"
                               <?= !empty($config['multimoneda_activo']) ? 'checked' : '' ?>
                               onchange="this.form.submit()">
                        <label class="form-check-label" for="toggle_dual">
                            Mostrar la conversión a la segunda moneda junto a cada monto
                        </label>
                    </div>
                    <p class="text-muted small mt-2 mb-0">
                        Al activarlo, en las vistas web, recibos PDF, reportes Excel y correos se mostrará
                        el monto en ambas monedas (p. ej. <strong>$ 1,500.00</strong> <span class="text-muted">· Bs 54,000.00</span>).
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Tasa de cambio -->
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Tasa de Cambio Bs/USD</h5>
            </div>
            <div class="card-body">
                <?php if ($has_rate): ?>
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block">Tasa</small>
                            <strong><?= number_format($rate, 2) ?> Bs</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block">Vigente</small>
                            <strong><?= $config['tasa_fecha'] ? date('d/m/Y', strtotime($config['tasa_fecha'])) : '-' ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block">Origen</small>
                            <strong><?= $config['tasa_origen'] === 'bcv' ? 'BCV' : 'Manual' ?></strong>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle"></i> No se ha configurado ninguna tasa de cambio todavía.
                </div>
                <?php endif; ?>

                <!-- Obtener del BCV -->
                <form method="POST" action="<?= APP_URL ?>/settings" class="mb-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="bcv">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-cloud-download-alt"></i>
                        <?= $has_rate ? 'Actualizar Tasa desde el BCV' : 'Obtener Tasa del BCV' ?>
                    </button>
                    <small class="text-muted d-block mt-1 text-center">
                        Fuente: tasa oficial del Banco Central de Venezuela (requiere conexión a internet).
                    </small>
                </form>

                <hr>

                <!-- Manual -->
                <h6><i class="fas fa-pencil-alt"></i> Ingreso Manual</h6>
                <form method="POST" action="<?= APP_URL ?>/settings" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="rate">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-2">
                                <label for="tasa" class="form-label">Tasa (Bs por 1 USD)</label>
                                <input type="number" class="form-control" id="tasa" name="tasa"
                                       value="<?= $has_rate ? number_format($rate, 2, '.', '') : '' ?>"
                                       step="0.0001" min="0.0001" required>
                                <div class="invalid-feedback">Ingrese la tasa</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <label for="tasa_fecha" class="form-label">Fecha de Vigencia</label>
                                <input type="date" class="form-control" id="tasa_fecha" name="tasa_fecha"
                                       value="<?= $config['tasa_fecha'] ? date('Y-m-d', strtotime($config['tasa_fecha'])) : date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-success btn-sm w-100">
                        <i class="fas fa-save"></i> Guardar Tasa Manualmente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('form_base').addEventListener('submit', function(e) {
    var msg = 'Al cambiar la moneda base, los montos guardados NO se modifican; solo cambiará la moneda en la que se muestran en todas las pantallas (dashboard, listados, reportes).\n\n¿Desea continuar?';
    if (!confirm(msg)) {
        e.preventDefault();
    }
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>