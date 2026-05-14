<?php
/**
 * Vista de Listado de Reglas de Mora
 * 
 * Muestra todas las reglas de mora configuradas con opciones de gestión.
 * Solo accesible para administradores.
 */

$page_title = 'Reglas de Mora';
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-percentage"></i> Reglas de Mora</h2>
                <a href="/late-fee-rules/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Regla
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['flash_message']; 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Listado de Reglas</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($rules)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay reglas de mora configuradas. 
                            <a href="/late-fee-rules/create">Crear la primera regla</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Frecuencia</th>
                                        <th>Días Gracia</th>
                                        <th>Tope Máximo</th>
                                        <th>Tipo Pago</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rules as $rule): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($rule['nombre']); ?></strong></td>
                                            <td>
                                                <?php if ($rule['tipo_recargo'] === 'porcentaje'): ?>
                                                    <span class="badge bg-info">Porcentaje</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Monto Fijo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($rule['tipo_recargo'] === 'porcentaje'): ?>
                                                    <?php echo number_format($rule['valor_recargo'], 2); ?>%
                                                <?php else: ?>
                                                    $<?php echo number_format($rule['valor_recargo'], 2); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $frecuencias = [
                                                    'unica' => 'Única',
                                                    'diaria' => 'Diaria',
                                                    'semanal' => 'Semanal',
                                                    'mensual' => 'Mensual'
                                                ];
                                                echo $frecuencias[$rule['frecuencia']] ?? $rule['frecuencia'];
                                                ?>
                                            </td>
                                            <td><?php echo $rule['dias_gracia']; ?> días</td>
                                            <td>
                                                <?php if ($rule['tope_maximo']): ?>
                                                    $<?php echo number_format($rule['tope_maximo'], 2); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin tope</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($rule['tipo_pago']): ?>
                                                    <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($rule['tipo_pago']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Global</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($rule['activa']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Activa
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times-circle"></i> Inactiva
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/late-fee-rules/edit/<?php echo $rule['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form method="POST" action="/late-fee-rules/toggle/<?php echo $rule['id']; ?>" 
                                                          style="display: inline;">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-<?php echo $rule['activa'] ? 'warning' : 'success'; ?>" 
                                                                title="<?php echo $rule['activa'] ? 'Desactivar' : 'Activar'; ?>">
                                                            <i class="fas fa-<?php echo $rule['activa'] ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?php echo $rule['id']; ?>)"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Herramientas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="/late-fee-rules/simulate" class="btn btn-outline-info btn-block w-100">
                                <i class="fas fa-calculator"></i> Simulador de Cálculo
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/late-fees/report" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-file-alt"></i> Reporte de Mora
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/late-fees/stats" class="btn btn-outline-primary btn-block w-100">
                                <i class="fas fa-chart-bar"></i> Estadísticas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar esta regla de mora?</p>
                <p class="text-danger"><strong>Nota:</strong> No se puede eliminar una regla que tenga mora aplicada en pagos activos.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(ruleId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = '/late-fee-rules/delete/' + ruleId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
