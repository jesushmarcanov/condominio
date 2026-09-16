<?php $page_title = 'Declaraciones de Pago en Línea'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-hand-holding-usd"></i> Declaraciones de Pago en Línea</h1>
            <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Pagos
            </a>
        </div>
    </div>
</div>

<!-- Resumen -->
<?php if (PAYMENT_TEST_MODE): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-flask"></i>
            <strong>Modo Test:</strong> las declaraciones de pago en línea se verifican manualmente. Confirme la
            transferencia o pago móvil en su banca en línea antes de confirmar una declaración.
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= (int) $stats['pendientes'] ?></h4>
                        <p class="card-text">Por Verificar</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= (int) $stats['confirmadas'] ?></h4>
                        <p class="card-text">Confirmadas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= (int) $stats['rechazadas'] ?></h4>
                        <p class="card-text">Rechazadas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= formatCurrencyDual($stats['total_confirmado']) ?></h4>
                        <p class="card-text">Total Confirmado</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtro por estado -->
<div class="row mb-3">
    <div class="col-12">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link <?= $status === '' ? 'active' : '' ?>" href="<?= APP_URL ?>/payments/declarations">
                    Todos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status === 'pendiente' ? 'active' : '' ?>" href="<?= APP_URL ?>/payments/declarations?status=pendiente">
                    Pendientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status === 'confirmada' ? 'active' : '' ?>" href="<?= APP_URL ?>/payments/declarations?status=confirmada">
                    Confirmadas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status === 'rechazada' ? 'active' : '' ?>" href="<?= APP_URL ?>/payments/declarations?status=rechazada">
                    Rechazadas
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Lista de Declaraciones -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($declarations)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha Declaración</th>
                                    <th>Residente</th>
                                    <th>Apartamento</th>
                                    <th>Concepto</th>
                                    <th>Método</th>
                                    <th>Referencia</th>
                                    <th>Monto Declarado</th>
                                    <th>Fecha Operación</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($declarations as $decl): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($decl['fecha_declaracion'])) ?></td>
                                    <td><?= htmlspecialchars($decl['nombre']) ?></td>
                                    <td><?= htmlspecialchars($decl['apartamento']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($decl['concepto']) ?>
                                        <br>
                                        <small class="text-muted"><?= date('m/Y', strtotime($decl['mes_pago'])) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars(getCatalogLabel(ONLINE_PAYMENT_METHODS, $decl['metodo'])) ?></td>
                                    <td><?= htmlspecialchars($decl['referencia_bancaria']) ?></td>
                                    <td><?= formatCurrencyFrom($decl['monto_declarado'], $decl['moneda']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($decl['fecha_operacion'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= getStatusBadgeClass($decl['estado'], 'declaration') ?>">
                                            <?= htmlspecialchars(getCatalogLabel(PAYMENT_DECLARATION_STATUSES, $decl['estado'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= APP_URL ?>/payments/show/<?= $decl['pago_id'] ?>"
                                               class="btn btn-sm btn-outline-primary" title="Ver Pago">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($decl['estado'] === 'pendiente'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" title="Confirmar"
                                                    onclick="$('#confirmModal-<?= $decl['id'] ?>').modal('show')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Rechazar"
                                                    onclick="$('#rejectModal-<?= $decl['id'] ?>').modal('show')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No hay declaraciones<?= $status !== '' ? ' con estado ' . htmlspecialchars($status) : '' ?></h5>
                        <p class="text-muted">Las declaraciones de pago en línea aparecerán aquí</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modales de Confirmación y Rechazo -->
<?php foreach($declarations as $decl): ?>
    <?php if ($decl['estado'] === 'pendiente'): ?>
    <!-- Modal Confirmar -->
    <div class="modal fade" id="confirmModal-<?= $decl['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= APP_URL ?>/payments/declarations/confirm/<?= $decl['id'] ?>">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-check-circle"></i> Confirmar Declaración</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Confirma la declaración de <strong><?= htmlspecialchars($decl['nombre']) ?></strong> por
                           <strong><?= formatCurrencyFrom($decl['monto_declarado'], $decl['moneda']) ?></strong>?</p>
                        <ul class="small">
                            <li>Método: <?= htmlspecialchars(getCatalogLabel(ONLINE_PAYMENT_METHODS, $decl['metodo'])) ?></li>
                            <li>Referencia: <?= htmlspecialchars($decl['referencia_bancaria']) ?></li>
                            <li>Fecha operación: <?= date('d/m/Y', strtotime($decl['fecha_operacion'])) ?></li>
                        </ul>
                        <div class="mb-3">
                            <label for="notas_admin_<?= $decl['id'] ?>" class="form-label">Nota para el residente (opcional)</label>
                            <textarea class="form-control" id="notas_admin_<?= $decl['id'] ?>" name="notas_admin" rows="2" maxlength="500"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Rechazar -->
    <div class="modal fade" id="rejectModal-<?= $decl['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= APP_URL ?>/payments/declarations/reject/<?= $decl['id'] ?>">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-times-circle"></i> Rechazar Declaración</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Rechazará la declaración de <strong><?= htmlspecialchars($decl['nombre']) ?></strong>.</p>
                        <div class="mb-3">
                            <label for="notas_admin_<?= $decl['id'] ?>_reject" class="form-label">
                                Motivo del rechazo <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="notas_admin_<?= $decl['id'] ?>_reject" name="notas_admin" rows="3" maxlength="500" required></textarea>
                            <div class="invalid-feedback">El motivo es obligatorio</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Rechazar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>