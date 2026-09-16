<?php $page_title = 'Gestión de Pagos'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-dollar-sign"></i> Gestión de Pagos</h1>
            <?php if($is_admin): ?>
            <a href="<?= APP_URL ?>/payments/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Pago
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="month" class="form-label">Mes</label>
                        <input type="month" class="form-control" id="month" name="month" value="<?= $month ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="pagado" <?= $status == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                            <option value="pendiente" <?= $status == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="atrasado" <?= $status == 'atrasado' ? 'selected' : '' ?>>Atrasado</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Pagos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if(!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Residente</th>
                                    <th>Apartamento</th>
                                    <th>Concepto</th>
                                    <th>Mes</th>
                                    <th>Monto</th>
                                    <th>Mora</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <?php if($is_admin): ?>
                                    <th>Acciones</th>
                                    <?php else: ?>
                                    <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_mora = 0;
                                foreach($payments as $payment): 
                                    $ccy = $payment['moneda'] ?? baseCurrency();
                                    $monto_original = $payment['monto_original'] ?? $payment['monto'];
                                    $monto_mora = $payment['monto_mora'] ?? 0;
                                    $monto_total = $monto_original + $monto_mora;
                                    $total_mora += $monto_mora;
                                    $has_late_fee = $monto_mora > 0;
                                ?>
                                <tr <?= $has_late_fee ? 'class="table-warning"' : '' ?>>
                                    <td><?= $payment['nombre'] ?></td>
                                    <td><?= $payment['apartamento'] ?></td>
                                    <td>
                                        <?= $payment['concepto'] ?>
                                        <?php if ($has_late_fee): ?>
                                            <span class="badge bg-danger ms-1" title="Tiene mora aplicada">
                                                <i class="fas fa-exclamation-triangle"></i> Mora
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('m/Y', strtotime($payment['mes_pago'])) ?></td>
                                    <td><?= formatCurrencyFrom($monto_original, $ccy) ?></td>
                                    <td>
                                        <?php if ($has_late_fee): ?>
                                            <span class="text-danger">+<?= formatCurrencyFrom($monto_mora, $ccy) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= formatCurrencyFrom($monto_total, $ccy) ?></strong></td>
                                    <td><?= formatDate($payment['fecha_pago']) ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= $payment['metodo_pago'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $payment['estado'] == 'pagado' ? 'success' : 
                                            ($payment['estado'] == 'pendiente' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= $payment['estado'] ?>
                                        </span>
                                    </td>
                                    <?php if($is_admin): ?>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= APP_URL ?>/payments/show/<?= $payment['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    title="Imprimir Recibo"
                                                    onclick="openReceipt(<?= $payment['id'] ?>)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <a href="<?= APP_URL ?>/payments/edit/<?= $payment['id'] ?>" 
                                               class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger btn-delete" 
                                                    onclick="confirmDelete('<?= APP_URL ?>/payments/delete/<?= $payment['id'] ?>')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <?php else: ?>
                                    <td>
                                        <?php if($payment['estado'] != 'pagado'): ?>
                                        <a href="<?= APP_URL ?>/payments/pay/<?= $payment['id'] ?>"
                                           class="btn btn-sm btn-success" title="Pagar en Línea">
                                            <i class="fas fa-credit-card"></i> Pagar en Línea
                                        </a>
                                        <?php else: ?>
                                        <a href="<?= APP_URL ?>/payments/show/<?= $payment['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if($payment['estado'] == 'pagado'): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success" 
                                                title="Imprimir Recibo"
                                                onclick="openReceipt(<?= $payment['id'] ?>)">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Resumen -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>Total de pagos:</strong> <?= count($payments) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Total monto:</strong> <?= formatCurrencyDual(array_sum(array_map(function($p) {
                                return convertCurrency($p['monto'], $p['moneda'] ?? baseCurrency(), baseCurrency());
                            }, $payments))) ?>
                        </div>
                        <?php if ($total_mora > 0): ?>
                        <div class="col-md-4">
                            <strong class="text-danger">Total mora pendiente:</strong> 
                            <span class="text-danger"><?= formatCurrencyDual($total_mora) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-dollar-sign fa-3x text-muted mb-3"></i>
                        <h5>No hay pagos registrados</h5>
                        <p class="text-muted">
                            <?php if($is_admin): ?>
                                <a href="<?= APP_URL ?>/payments/create">Registra el primer pago</a>
                            <?php else: ?>
                                No tienes pagos registrados
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Recibo de Pago -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">
                    <i class="fas fa-receipt"></i> Recibo de Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" style="background:#e9ecef;">
                <iframe id="receiptFrame" src="about:blank" style="width:100%;height:70vh;border:0;display:block;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="receiptPrintBtn">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function openReceipt(paymentId) {
    document.getElementById('receiptFrame').src = APP_URL + '/payments/receipt/' + paymentId + '?embed=1';
    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.getElementById('receiptPrintBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            const frame = document.getElementById('receiptFrame');
            if (frame && frame.contentWindow) {
                frame.contentWindow.print();
            }
        });
    }

    // Auto-abrir el recibo tras "Guardar e Imprimir Recibo" (?print=ID)
    const printPayment = new URLSearchParams(window.location.search).get('print');
    if (printPayment) {
        openReceipt(printPayment);
    }
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
