<?php $page_title = 'Pagar en Línea'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-credit-card"></i> Pagar en Línea</h1>
            <a href="<?= APP_URL ?>/payments/show/<?= $payment['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Pago
            </a>
        </div>
    </div>
</div>

<?php
$monto_original = $payment['monto_original'] ?? $payment['monto'];
$monto_mora = $payment['monto_mora'] ?? 0;
$monto_total = $monto_original + $monto_mora;
$ccy = $payment['moneda'] ?? baseCurrency();
$monto_base = convertCurrency($monto_total, $ccy, baseCurrency());
?>

<div class="row">
    <div class="col-md-5">
        <!-- Resumen del Pago -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Resumen del Pago</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tr>
                        <td><strong>Concepto:</strong></td>
                        <td><?= htmlspecialchars($payment['concepto']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Mes:</strong></td>
                        <td><?= date('m/Y', strtotime($payment['mes_pago'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Monto Original:</strong></td>
                        <td><?= formatCurrencyFrom($monto_original, $ccy) ?></td>
                    </tr>
                    <?php if ($monto_mora > 0): ?>
                    <tr class="table-warning">
                        <td><strong>Mora:</strong></td>
                        <td class="text-danger">+<?= formatCurrencyFrom($monto_mora, $ccy) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-success">
                        <td><strong>Total a Pagar:</strong></td>
                        <td><h5 class="mb-0"><?= formatCurrencyFrom($monto_total, $ccy) ?></h5></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Cómo Pagar</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li>Realice la transferencia o pago móvil por el monto total indicado.</li>
                    <li>Use los datos bancarios mostrados a la derecha.</li>
                    <li>Registre los datos de la operación en el formulario.</li>
                    <li>La administración verificará el pago y lo confirmará.</li>
                    <li>Recibirá una notificación por email.</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <!-- Cuentas Bancarias -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-university"></i> Datos Bancarios del Condominio</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($bank_accounts)): ?>
                    <?php foreach ($bank_accounts as $account): ?>
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6><i class="fas fa-building-columns"></i> <?= htmlspecialchars($account['banco']) ?></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Cuenta <?= htmlspecialchars(strtoupper($account['tipo'])) ?></small>
                                <strong><?= htmlspecialchars($account['numero_cuenta']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Titular</small>
                                <strong><?= htmlspecialchars($account['titular']) ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($account['pago_movil_telefono'])): ?>
                        <hr class="my-2">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Pago Móvil</small>
                                <strong><?= htmlspecialchars($account['pago_movil_telefono']) ?></strong>
                            </div>
                            <?php if (!empty($account['pago_movil_cedula'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Cédula/RIF</small>
                                <strong><?= htmlspecialchars($account['pago_movil_cedula']) ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        No hay cuentas bancarias configuradas. Contacte a la administración.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario de Declaración -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-paper-plane"></i> Declarar Pago Realizado</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Complete los datos de la operación que acaba de realizar en su banco:</p>

                <form method="POST" action="<?= APP_URL ?>/payments/declare/<?= $payment['id'] ?>" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="metodo" class="form-label">Método de Pago</label>
                                <select class="form-select" id="metodo" name="metodo" required>
                                    <option value="">Seleccionar método</option>
                                    <?= getCatalogOptions(ONLINE_PAYMENT_METHODS, isset($data['metodo']) ? $data['metodo'] : '') ?>
                                </select>
                                <div class="invalid-feedback">Seleccione el método de pago utilizado</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="banco_origen" class="form-label">Banco desde el que Pagó</label>
                                <select class="form-select" id="banco_origen" name="banco_origen" required>
                                    <option value="">Seleccionar banco</option>
                                    <?= getCatalogOptions(BANCOS_VENEZUELA, isset($data['banco_origen']) ? $data['banco_origen'] : '', false) ?>
                                </select>
                                <div class="invalid-feedback">Seleccione el banco de origen</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="referencia_bancaria" class="form-label">Número de Referencia</label>
                        <input type="text" class="form-control" id="referencia_bancaria" name="referencia_bancaria"
                               value="<?= isset($data['referencia_bancaria']) ? $data['referencia_bancaria'] : '' ?>"
                               placeholder="Número de confirmación u operación" maxlength="30" required>
                        <div class="invalid-feedback">La referencia es requerida</div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="moneda" class="form-label">Moneda del Pago</label>
                                <select class="form-select" id="moneda" name="moneda">
                                    <option value="USD" <?= isset($data['moneda']) && $data['moneda'] === 'USD' ? 'selected' : '' ?>>USD - Dólares ($)</option>
                                    <option value="VES" <?= isset($data['moneda']) && $data['moneda'] === 'VES' ? 'selected' : '' ?>><?= dualCurrencyEnabled() && bcvRate() ? 'VES - Bolívares (Bs)' : 'VES - Bolívares (Bs) (sin tasa configurada)' ?></option>
                                </select>
                                <div class="invalid-feedback">Seleccione la moneda</div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="monto_declarado" class="form-label">Monto Pagado</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="mt_currency">$</span>
                                    <input type="number" class="form-control" id="monto_declarado" name="monto_declarado"
                                           value="<?= isset($data['monto_declarado']) ? $data['monto_declarado'] : number_format($monto_total, 2, '.', '') ?>"
                                           step="0.01" min="0.01" required>
                                </div>
                                <small class="text-muted d-block" id="mt_hint">
                                    Equivalente: <?= formatCurrencyDualText($monto_base) ?>
                                </small>
                                <div class="invalid-feedback">Indique el monto pagado</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_operacion" class="form-label">Fecha de Operación</label>
                                <input type="date" class="form-control" id="fecha_operacion" name="fecha_operacion"
                                       value="<?= isset($data['fecha_operacion']) ? $data['fecha_operacion'] : date('Y-m-d') ?>" required>
                                <div class="invalid-feedback">La fecha es requerida</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hora_operacion" class="form-label">Hora de Operación</label>
                                <input type="time" class="form-control" id="hora_operacion" name="hora_operacion"
                                       value="<?= isset($data['hora_operacion']) ? $data['hora_operacion'] : date('H:i') ?>" required>
                                <div class="invalid-feedback">La hora es requerida</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-3">
                        <small><i class="fas fa-shield-alt"></i> Su declaración será verificada por la administración antes de confirmar el pago.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/payments/show/<?= $payment['id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Enviar Declaración
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Historial de Declaraciones -->
        <?php if (!empty($declarations)): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Declaraciones Realizadas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($declarations as $decl): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($decl['fecha_declaracion'])) ?></td>
                                <td><?= htmlspecialchars(getCatalogLabel(ONLINE_PAYMENT_METHODS, $decl['metodo'])) ?></td>
                                <td><?= htmlspecialchars($decl['referencia_bancaria']) ?></td>
                                <td><?= formatCurrencyFrom($decl['monto_declarado'], $decl['moneda']) ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($decl['estado'], 'declaration') ?>">
                                        <?= htmlspecialchars(getCatalogLabel(PAYMENT_DECLARATION_STATUSES, $decl['estado'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

<?php if (dualCurrencyEnabled() && bcvRate()): ?>
<script>
(function () {
    var rate = <?= (float)bcvRate() ?>;
    var base = <?= json_encode(baseCurrency()) ?>;
    var totalBase = <?= (float)$monto_base ?>;
    var currencyPrefix, currencyField, amountField, hintEl;

    function fmt (n) {
        return n.toFixed(2);
    }

    function updateFromCurrency () {
        var ccy = currencyField.value;
        var amount;
        if (ccy === base) {
            amount = totalBase;
        } else if (base === 'USD' && ccy === 'VES') {
            amount = totalBase * rate;
        } else {
            amount = totalBase / rate;
        }
        amountField.value = fmt(amount);
        currencyPrefix.textContent = ccy === 'USD' ? '$' : 'Bs';
        hintEl.textContent = 'Equivalente: ' + fmt(ccy === 'USD' ? amount : amount) + (ccy === base ? ' ' + ccy : ' en ' + ccy);
    }

    document.addEventListener('DOMContentLoaded', function () {
        currencyField = document.getElementById('moneda');
        amountField = document.getElementById('monto_declarado');
        currencyPrefix = document.getElementById('mt_currency');
        hintEl = document.getElementById('mt_hint');
        if (currencyField && amountField) {
            currencyField.addEventListener('change', updateFromCurrency);
        }
    });
})();
</script>
<?php endif; ?>