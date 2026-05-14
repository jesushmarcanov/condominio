<?php
/**
 * Vista de Reporte de Mora
 * 
 * Muestra un reporte detallado de pagos con mora aplicada.
 * Solo accesible para administradores.
 */

$page_title = 'Reporte de Mora';
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-file-alt"></i> Reporte de Mora</h2>
                <a href="/late-fee-rules" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="/late-fees/report">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Fecha Inicio</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="<?php echo htmlspecialchars($start_date ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Fecha Fin</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="<?php echo htmlspecialchars($end_date ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="pendiente" <?php echo ($estado ?? '') === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="atrasado" <?php echo ($estado ?? '') === 'atrasado' ? 'selected' : ''; ?>>Atrasado</option>
                                    <option value="pagado" <?php echo ($estado ?? '') === 'pagado' ? 'selected' : ''; ?>>Pagado</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="/late-fees/report" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Monto Original Total</h5>
                    <h2>$<?php echo number_format($total_original ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Mora Total Aplicada</h5>
                    <h2>$<?php echo number_format($total_mora ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Monto Total</h5>
                    <h2>$<?php echo number_format($total_general ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de pagos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pagos con Mora</h5>
                    <div>
                        <a href="/late-fees/report?<?php echo http_build_query(array_merge($_GET, ['export' => 'excel'])); ?>" 
                           class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No se encontraron pagos con mora en el período seleccionado.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Apartamento</th>
                                        <th>Residente</th>
                                        <th>Concepto</th>
                                        <th>Mes</th>
                                        <th>Vencimiento</th>
                                        <th>Monto Original</th>
                                        <th>Mora</th>
                                        <th>Total</th>
                                        <th>Fecha Aplicación</th>
                                        <th>Regla</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['id']; ?></td>
                                            <td><?php echo htmlspecialchars($payment['apartamento']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['residente_nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['concepto']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['mes_pago']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($payment['fecha_pago'])); ?></td>
                                            <td>$<?php echo number_format($payment['monto_original'], 2); ?></td>
                                            <td class="text-danger">$<?php echo number_format($payment['monto_mora'], 2); ?></td>
                                            <td><strong>$<?php echo number_format($payment['monto_original'] + $payment['monto_mora'], 2); ?></strong></td>
                                            <td><?php echo $payment['fecha_aplicacion_mora'] ? date('d/m/Y', strtotime($payment['fecha_aplicacion_mora'])) : '-'; ?></td>
                                            <td>
                                                <?php if ($payment['regla_nombre']): ?>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($payment['regla_nombre']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_class = [
                                                    'pendiente' => 'bg-warning',
                                                    'atrasado' => 'bg-danger',
                                                    'pagado' => 'bg-success'
                                                ];
                                                $class = $badge_class[$payment['estado']] ?? 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $class; ?>">
                                                    <?php echo ucfirst($payment['estado']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <td colspan="6" class="text-end"><strong>TOTALES:</strong></td>
                                        <td><strong>$<?php echo number_format($total_original ?? 0, 2); ?></strong></td>
                                        <td class="text-danger"><strong>$<?php echo number_format($total_mora ?? 0, 2); ?></strong></td>
                                        <td><strong>$<?php echo number_format($total_general ?? 0, 2); ?></strong></td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
