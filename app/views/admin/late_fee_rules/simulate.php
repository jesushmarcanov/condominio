<?php
/**
 * Vista de Simulador de Cálculo de Mora
 * 
 * Permite simular el cálculo de mora para diferentes escenarios.
 * Solo accesible para administradores.
 */

$page_title = 'Simulador de Cálculo de Mora';
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-calculator"></i> Simulador de Cálculo de Mora</h2>
                <a href="/late-fee-rules" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Parámetros de Simulación</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/late-fee-rules/simulate">
                        <div class="mb-3">
                            <label for="regla_id" class="form-label">Regla de Mora *</label>
                            <select class="form-select <?php echo isset($errors['regla_id']) ? 'is-invalid' : ''; ?>" 
                                    id="regla_id" 
                                    name="regla_id" 
                                    required>
                                <option value="">Seleccione una regla...</option>
                                <?php foreach ($rules as $rule): ?>
                                    <option value="<?php echo $rule['id']; ?>" 
                                            <?php echo ($data['regla_id'] ?? '') == $rule['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($rule['nombre']); ?>
                                        <?php if (!$rule['activa']): ?>
                                            (Inactiva)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['regla_id'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['regla_id']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="monto" class="form-label">Monto Original del Pago *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control <?php echo isset($errors['monto']) ? 'is-invalid' : ''; ?>" 
                                       id="monto" 
                                       name="monto" 
                                       value="<?php echo htmlspecialchars($data['monto'] ?? ''); ?>"
                                       step="0.01"
                                       min="0.01"
                                       required>
                                <?php if (isset($errors['monto'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['monto']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="dias_atraso" class="form-label">Días de Atraso *</label>
                            <input type="number" 
                                   class="form-control <?php echo isset($errors['dias_atraso']) ? 'is-invalid' : ''; ?>" 
                                   id="dias_atraso" 
                                   name="dias_atraso" 
                                   value="<?php echo htmlspecialchars($data['dias_atraso'] ?? ''); ?>"
                                   min="0"
                                   required>
                            <?php if (isset($errors['dias_atraso'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['dias_atraso']; ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Días transcurridos desde la fecha de vencimiento</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calculator"></i> Calcular Mora
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <?php if ($result): ?>
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> Resultado del Cálculo</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><strong>Regla Aplicada:</strong> <?php echo htmlspecialchars($result['regla']['nombre']); ?></h6>
                            <p class="mb-0">
                                <strong>Tipo:</strong> <?php echo $result['regla']['tipo_recargo'] === 'porcentaje' ? 'Porcentaje' : 'Monto Fijo'; ?> |
                                <strong>Valor:</strong> <?php echo $result['regla']['tipo_recargo'] === 'porcentaje' ? $result['regla']['valor_recargo'] . '%' : '$' . number_format($result['regla']['valor_recargo'], 2); ?> |
                                <strong>Frecuencia:</strong> <?php echo ucfirst($result['regla']['frecuencia']); ?>
                            </p>
                        </div>

                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Monto Original:</strong></td>
                                <td class="text-end">$<?php echo number_format($result['monto_original'], 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Días de Atraso:</strong></td>
                                <td class="text-end"><?php echo $result['dias_atraso']; ?> días</td>
                            </tr>
                            <tr>
                                <td><strong>Días de Gracia:</strong></td>
                                <td class="text-end"><?php echo $result['dias_gracia']; ?> días</td>
                            </tr>
                            <tr>
                                <td><strong>Días Efectivos:</strong></td>
                                <td class="text-end"><?php echo $result['dias_efectivos']; ?> días</td>
                            </tr>
                            <tr>
                                <td><strong>Multiplicador:</strong></td>
                                <td class="text-end">×<?php echo $result['multiplicador']; ?></td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Monto de Mora:</strong></td>
                                <td class="text-end"><strong>$<?php echo number_format($result['monto_mora'], 2); ?></strong></td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Monto Total a Pagar:</strong></td>
                                <td class="text-end"><strong>$<?php echo number_format($result['monto_total'], 2); ?></strong></td>
                            </tr>
                        </table>

                        <?php if ($result['tope_aplicado']): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> <strong>Tope Máximo Aplicado:</strong> 
                                El cálculo fue limitado al tope máximo de $<?php echo number_format($result['regla']['tope_maximo'], 2); ?>
                            </div>
                        <?php endif; ?>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Explicación del Cálculo</h6>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($result['explicacion']); ?></pre>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Instrucciones</h5>
                    </div>
                    <div class="card-body">
                        <h6>Cómo usar el simulador:</h6>
                        <ol>
                            <li>Seleccione una regla de mora activa</li>
                            <li>Ingrese el monto original del pago</li>
                            <li>Ingrese los días de atraso</li>
                            <li>Haga clic en "Calcular Mora"</li>
                        </ol>

                        <h6 class="mt-4">Ejemplos de uso:</h6>
                        <ul>
                            <li>Verificar cómo se calcula la mora para un pago específico</li>
                            <li>Probar diferentes escenarios antes de crear una regla</li>
                            <li>Explicar a los residentes cómo se calculan los recargos</li>
                        </ul>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> 
                            Los días de atraso se cuentan desde la fecha de vencimiento, 
                            pero el período de gracia se resta automáticamente.
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($rules)): ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No hay reglas de mora configuradas. 
                    <a href="/late-fee-rules/create">Crear una regla</a> para usar el simulador.
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
