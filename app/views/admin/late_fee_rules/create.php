<?php
/**
 * Vista de Creación de Regla de Mora
 * 
 * Formulario para crear una nueva regla de mora.
 * Solo accesible para administradores.
 */

$page_title = 'Nueva Regla de Mora';
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-plus-circle"></i> Nueva Regla de Mora</h2>
                <a href="/late-fee-rules" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Información de la Regla</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/late-fee-rules" id="lateFeeForm">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Regla *</label>
                            <input type="text" 
                                   class="form-control <?php echo isset($errors['nombre']) ? 'is-invalid' : ''; ?>" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="<?php echo htmlspecialchars($data['nombre'] ?? ''); ?>"
                                   maxlength="100"
                                   required>
                            <?php if (isset($errors['nombre'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['nombre']; ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Ejemplo: "Mora Estándar 2% Mensual"</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dias_gracia" class="form-label">Días de Gracia *</label>
                                    <input type="number" 
                                           class="form-control <?php echo isset($errors['dias_gracia']) ? 'is-invalid' : ''; ?>" 
                                           id="dias_gracia" 
                                           name="dias_gracia" 
                                           value="<?php echo htmlspecialchars($data['dias_gracia'] ?? '5'); ?>"
                                           min="0"
                                           required>
                                    <?php if (isset($errors['dias_gracia'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['dias_gracia']; ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Días después del vencimiento antes de aplicar mora</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo_recargo" class="form-label">Tipo de Recargo *</label>
                                    <select class="form-select <?php echo isset($errors['tipo_recargo']) ? 'is-invalid' : ''; ?>" 
                                            id="tipo_recargo" 
                                            name="tipo_recargo" 
                                            required
                                            onchange="updateValueLabel()">
                                        <option value="">Seleccione...</option>
                                        <option value="porcentaje" <?php echo ($data['tipo_recargo'] ?? '') === 'porcentaje' ? 'selected' : ''; ?>>
                                            Porcentaje
                                        </option>
                                        <option value="monto_fijo" <?php echo ($data['tipo_recargo'] ?? '') === 'monto_fijo' ? 'selected' : ''; ?>>
                                            Monto Fijo
                                        </option>
                                    </select>
                                    <?php if (isset($errors['tipo_recargo'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['tipo_recargo']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valor_recargo" class="form-label" id="valor_label">Valor del Recargo *</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="valor_prefix">$</span>
                                        <input type="number" 
                                               class="form-control <?php echo isset($errors['valor_recargo']) ? 'is-invalid' : ''; ?>" 
                                               id="valor_recargo" 
                                               name="valor_recargo" 
                                               value="<?php echo htmlspecialchars($data['valor_recargo'] ?? ''); ?>"
                                               step="0.01"
                                               min="0.01"
                                               required>
                                        <span class="input-group-text" id="valor_suffix"></span>
                                        <?php if (isset($errors['valor_recargo'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['valor_recargo']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="form-text text-muted" id="valor_help">Ingrese el valor del recargo</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="frecuencia" class="form-label">Frecuencia de Aplicación *</label>
                                    <select class="form-select <?php echo isset($errors['frecuencia']) ? 'is-invalid' : ''; ?>" 
                                            id="frecuencia" 
                                            name="frecuencia" 
                                            required>
                                        <option value="">Seleccione...</option>
                                        <option value="unica" <?php echo ($data['frecuencia'] ?? '') === 'unica' ? 'selected' : ''; ?>>
                                            Única (una sola vez)
                                        </option>
                                        <option value="diaria" <?php echo ($data['frecuencia'] ?? '') === 'diaria' ? 'selected' : ''; ?>>
                                            Diaria
                                        </option>
                                        <option value="semanal" <?php echo ($data['frecuencia'] ?? '') === 'semanal' ? 'selected' : ''; ?>>
                                            Semanal
                                        </option>
                                        <option value="mensual" <?php echo ($data['frecuencia'] ?? '') === 'mensual' ? 'selected' : ''; ?>>
                                            Mensual
                                        </option>
                                    </select>
                                    <?php if (isset($errors['frecuencia'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['frecuencia']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tope_maximo" class="form-label">Tope Máximo (Opcional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control <?php echo isset($errors['tope_maximo']) ? 'is-invalid' : ''; ?>" 
                                               id="tope_maximo" 
                                               name="tope_maximo" 
                                               value="<?php echo htmlspecialchars($data['tope_maximo'] ?? ''); ?>"
                                               step="0.01"
                                               min="0">
                                        <?php if (isset($errors['tope_maximo'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['tope_maximo']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="form-text text-muted">Límite máximo de mora (dejar vacío para sin límite)</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo_pago" class="form-label">Tipo de Pago (Opcional)</label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($errors['tipo_pago']) ? 'is-invalid' : ''; ?>" 
                                           id="tipo_pago" 
                                           name="tipo_pago" 
                                           value="<?php echo htmlspecialchars($data['tipo_pago'] ?? ''); ?>"
                                           maxlength="50">
                                    <?php if (isset($errors['tipo_pago'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['tipo_pago']; ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Dejar vacío para aplicar a todos los tipos de pago</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="activa" 
                                       name="activa" 
                                       value="1"
                                       <?php echo ($data['activa'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activa">
                                    Activar regla inmediatamente
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/late-fee-rules" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Regla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Ayuda</h5>
                </div>
                <div class="card-body">
                    <h6>Tipo de Recargo</h6>
                    <ul>
                        <li><strong>Porcentaje:</strong> Se calcula como un % del monto original</li>
                        <li><strong>Monto Fijo:</strong> Se aplica un monto fijo independiente del monto original</li>
                    </ul>

                    <h6>Frecuencia</h6>
                    <ul>
                        <li><strong>Única:</strong> Se aplica una sola vez</li>
                        <li><strong>Diaria:</strong> Se multiplica por cada día de atraso</li>
                        <li><strong>Semanal:</strong> Se multiplica por cada semana completa</li>
                        <li><strong>Mensual:</strong> Se multiplica por cada mes completo</li>
                    </ul>

                    <h6>Ejemplos</h6>
                    <p><strong>Ejemplo 1:</strong> Porcentaje 2%, Frecuencia Mensual<br>
                    Pago de $1000 con 35 días de atraso = $20 (1 mes)</p>

                    <p><strong>Ejemplo 2:</strong> Monto Fijo $50, Frecuencia Única<br>
                    Cualquier pago atrasado = $50</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateValueLabel() {
    const tipoRecargo = document.getElementById('tipo_recargo').value;
    const valorLabel = document.getElementById('valor_label');
    const valorPrefix = document.getElementById('valor_prefix');
    const valorSuffix = document.getElementById('valor_suffix');
    const valorHelp = document.getElementById('valor_help');
    const valorInput = document.getElementById('valor_recargo');

    if (tipoRecargo === 'porcentaje') {
        valorLabel.textContent = 'Porcentaje de Recargo *';
        valorPrefix.textContent = '';
        valorSuffix.textContent = '%';
        valorHelp.textContent = 'Porcentaje entre 0.01 y 100';
        valorInput.max = '100';
    } else if (tipoRecargo === 'monto_fijo') {
        valorLabel.textContent = 'Monto Fijo de Recargo *';
        valorPrefix.textContent = '$';
        valorSuffix.textContent = '';
        valorHelp.textContent = 'Monto fijo a aplicar';
        valorInput.removeAttribute('max');
    } else {
        valorLabel.textContent = 'Valor del Recargo *';
        valorPrefix.textContent = '$';
        valorSuffix.textContent = '';
        valorHelp.textContent = 'Ingrese el valor del recargo';
        valorInput.removeAttribute('max');
    }
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateValueLabel();
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
