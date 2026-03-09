<?php $page_title = 'Nuevo Pago'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-plus"></i> Nuevo Pago</h1>
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

                <form method="POST" action="<?= APP_URL ?>/payments" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="residente_id" class="form-label">Residente</label>
                        <select class="form-select" id="residente_id" name="residente_id" required>
                            <option value="">Seleccionar residente</option>
                            <?php if(isset($residents)): ?>
                                <?php foreach($residents as $resident): ?>
                                    <option value="<?= $resident['id'] ?>" 
                                            data-apartamento="<?= $resident['apartamento'] ?>"
                                            data-nombre="<?= $resident['nombre'] ?>"
                                            <?= isset($data['residente_id']) && $data['residente_id'] == $resident['id'] ? 'selected' : '' ?>>
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
                                <label for="monto" class="form-label">Monto</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="monto" name="monto" 
                                           value="<?= isset($data['monto']) ? $data['monto'] : '1500.00' ?>" 
                                           step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback">
                                    El monto es requerido y debe ser válido
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="concepto" class="form-label">Concepto</label>
                                <input type="text" class="form-control" id="concepto" name="concepto" 
                                       value="<?= isset($data['concepto']) ? $data['concepto'] : 'Cuota de mantenimiento' ?>" 
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
                                <label for="mes_pago" class="form-label">Mes de Pago</label>
                                <input type="month" class="form-control" id="mes_pago" name="mes_pago" 
                                       value="<?= isset($data['mes_pago']) ? $data['mes_pago'] : date('Y-m') ?>" required>
                                <div class="invalid-feedback">
                                    El mes de pago es requerido
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                       value="<?= isset($data['fecha_pago']) ? $data['fecha_pago'] : date('Y-m-d') ?>" required>
                                <div class="invalid-feedback">
                                    La fecha de pago es requerida
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="metodo_pago" class="form-label">Método de Pago</label>
                                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                    <option value="">Seleccionar método</option>
                                    <option value="efectivo" <?= isset($data['metodo_pago']) && $data['metodo_pago'] == 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                    <option value="transferencia" <?= isset($data['metodo_pago']) && $data['metodo_pago'] == 'transferencia' ? 'selected' : '' ?>>Transferencia Bancaria</option>
                                    <option value="tarjeta" <?= isset($data['metodo_pago']) && $data['metodo_pago'] == 'tarjeta' ? 'selected' : '' ?>>Tarjeta de Crédito/Débito</option>
                                    <option value="deposito" <?= isset($data['metodo_pago']) && $data['metodo_pago'] == 'deposito' ? 'selected' : '' ?>>Depósito Bancario</option>
                                </select>
                                <div class="invalid-feedback">
                                    Debe seleccionar un método de pago
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="referencia" class="form-label">Referencia (Opcional)</label>
                                <input type="text" class="form-control" id="referencia" name="referencia" 
                                       value="<?= isset($data['referencia']) ? $data['referencia'] : '' ?>" 
                                       placeholder="Número de referencia, folio, etc.">
                                <small class="form-text text-muted">Útil para transferencias y depósitos</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="pagado" <?= isset($data['estado']) && $data['estado'] == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                            <option value="pendiente" <?= isset($data['estado']) && $data['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="atrasado" <?= isset($data['estado']) && $data['estado'] == 'atrasado' ? 'selected' : '' ?>>Atrasado</option>
                        </select>
                        <div class="invalid-feedback">
                            Debe seleccionar un estado
                        </div>
                    </div>

                    <!-- Información del residente seleccionado -->
                    <div id="residenteInfo" class="alert alert-info d-none">
                        <h6><i class="fas fa-info-circle"></i> Información del Residente</h6>
                        <div id="residenteDetails"></div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const residenteSelect = document.getElementById('residente_id');
    const residenteInfo = document.getElementById('residenteInfo');
    const residenteDetails = document.getElementById('residenteDetails');
    
    residenteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const nombre = selectedOption.dataset.nombre;
            const apartamento = selectedOption.dataset.apartamento;
            
            residenteDetails.innerHTML = `
                <p><strong>Nombre:</strong> ${nombre}</p>
                <p><strong>Apartamento:</strong> ${apartamento}</p>
            `;
            residenteInfo.classList.remove('d-none');
        } else {
            residenteInfo.classList.add('d-none');
        }
    });
    
    // Auto-seleccionar método de pago según el estado
    const estadoSelect = document.getElementById('estado');
    estadoSelect.addEventListener('change', function() {
        const metodoPago = document.getElementById('metodo_pago');
        
        if (this.value === 'pendiente') {
            metodoPago.value = 'transferencia';
        } else if (this.value === 'atrasado') {
            metodoPago.value = 'efectivo';
        }
    });
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
