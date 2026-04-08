<?php $page_title = 'Editar Pago'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-edit"></i> Editar Pago</h1>
            <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Pagos
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-dollar-sign"></i> Información del Pago</h5>
            </div>
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

                <!-- Información del pago actual -->
                <div class="alert alert-info">
                    <strong>Residente Actual:</strong> <?= htmlspecialchars($payment['nombre'] ?? 'N/A') ?><br>
                    <strong>Apartamento:</strong> <?= htmlspecialchars($payment['apartamento'] ?? 'N/A') ?><br>
                    <strong>Fecha de Creación:</strong> <?= isset($payment['created_at']) ? date('d/m/Y H:i', strtotime($payment['created_at'])) : 'N/A' ?>
                </div>

                <form method="POST" action="<?= APP_URL ?>/payments/edit/<?= $payment['id'] ?>" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="residente_id" class="form-label">Residente *</label>
                        <select class="form-select" id="residente_id" name="residente_id" required>
                            <option value="">Seleccionar residente</option>
                            <?php if(isset($residents)): ?>
                                <?php foreach($residents as $resident): ?>
                                    <option value="<?= $resident['id'] ?>" 
                                            data-apartamento="<?= $resident['apartamento'] ?>"
                                            data-nombre="<?= $resident['nombre'] ?>"
                                            <?= isset($payment['residente_id']) && $payment['residente_id'] == $resident['id'] ? 'selected' : '' ?>>
                                        <?= $resident['nombre'] ?> - <?= $resident['apartamento'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">
                            Debe seleccionar un residente
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="monto" class="form-label">Monto *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="monto" name="monto" 
                                           value="<?= htmlspecialchars($payment['monto'] ?? '0.00') ?>" 
                                           step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback">
                                    El monto es requerido y debe ser válido
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="concepto" class="form-label">Concepto *</label>
                                <input type="text" class="form-control" id="concepto" name="concepto" 
                                       value="<?= htmlspecialchars($payment['concepto'] ?? '') ?>" 
                                       placeholder="Ej: Cuota de mantenimiento Enero" required>
                                <div class="invalid-feedback">
                                    El concepto es requerido
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mes_pago" class="form-label">Mes de Pago *</label>
                                <input type="month" class="form-control" id="mes_pago" name="mes_pago" 
                                       value="<?= htmlspecialchars($payment['mes_pago'] ?? date('Y-m')) ?>" required>
                                <div class="invalid-feedback">
                                    El mes de pago es requerido
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_pago" class="form-label">Fecha de Pago *</label>
                                <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                       value="<?= htmlspecialchars($payment['fecha_pago'] ?? date('Y-m-d')) ?>" required>
                                <div class="invalid-feedback">
                                    La fecha de pago es requerida
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="metodo_pago" class="form-label">Método de Pago *</label>
                                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                    <option value="">Seleccionar método</option>
                                    <option value="efectivo" <?= isset($payment['metodo_pago']) && $payment['metodo_pago'] == 'efectivo' ? 'selected' : '' ?>>
                                        💵 Efectivo
                                    </option>
                                    <option value="transferencia" <?= isset($payment['metodo_pago']) && $payment['metodo_pago'] == 'transferencia' ? 'selected' : '' ?>>
                                        🏦 Transferencia Bancaria
                                    </option>
                                    <option value="tarjeta" <?= isset($payment['metodo_pago']) && $payment['metodo_pago'] == 'tarjeta' ? 'selected' : '' ?>>
                                        💳 Tarjeta de Crédito/Débito
                                    </option>
                                    <option value="deposito" <?= isset($payment['metodo_pago']) && $payment['metodo_pago'] == 'deposito' ? 'selected' : '' ?>>
                                        🏧 Depósito Bancario
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    Debe seleccionar un método de pago
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="referencia" class="form-label">Referencia</label>
                                <input type="text" class="form-control" id="referencia" name="referencia" 
                                       value="<?= htmlspecialchars($payment['referencia'] ?? '') ?>" 
                                       placeholder="Número de referencia, folio, etc.">
                                <small class="form-text text-muted">Útil para transferencias y depósitos</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="pagado" <?= isset($payment['estado']) && $payment['estado'] == 'pagado' ? 'selected' : '' ?>>
                                ✅ Pagado
                            </option>
                            <option value="pendiente" <?= isset($payment['estado']) && $payment['estado'] == 'pendiente' ? 'selected' : '' ?>>
                                ⏳ Pendiente
                            </option>
                            <option value="atrasado" <?= isset($payment['estado']) && $payment['estado'] == 'atrasado' ? 'selected' : '' ?>>
                                ⚠️ Atrasado
                            </option>
                        </select>
                        <div class="invalid-feedback">
                            Debe seleccionar un estado
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <a href="<?= APP_URL ?>/payments/show/<?= $payment['id'] ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Guía de Estados -->
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="fas fa-info-circle"></i> Guía de Estados</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>✅ Pagado:</strong> El pago ha sido completado y confirmado
                </div>
                <div class="mb-2">
                    <strong>⏳ Pendiente:</strong> El pago está registrado pero no se ha completado
                </div>
                <div class="mb-0">
                    <strong>⚠️ Atrasado:</strong> El pago está vencido y requiere atención
                </div>
            </div>
        </div>
        
        <!-- Guía de Métodos de Pago -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-credit-card"></i> Métodos de Pago</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>💵 Efectivo:</strong> Pago en efectivo en oficina
                </div>
                <div class="mb-2">
                    <strong>🏦 Transferencia:</strong> Transferencia bancaria electrónica
                </div>
                <div class="mb-2">
                    <strong>💳 Tarjeta:</strong> Pago con tarjeta de crédito o débito
                </div>
                <div class="mb-0">
                    <strong>🏧 Depósito:</strong> Depósito bancario en sucursal
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const monto = parseFloat(document.getElementById('monto').value);
        
        if(monto <= 0) {
            e.preventDefault();
            alert('El monto debe ser mayor a 0');
            return;
        }
    });
});

function confirmDelete() {
    if(confirm('¿Está seguro de que desea eliminar este pago? Esta acción no se puede deshacer.')) {
        window.location.href = '<?= APP_URL ?>/payments/delete/<?= $payment['id'] ?>';
    }
}
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
