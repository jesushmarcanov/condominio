<?php $page_title = 'Cuentas Bancarias'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-university"></i> Cuentas Bancarias</h1>
            <div>
                <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Pagos
                </a>
                <a href="<?= APP_URL ?>/bank-accounts/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Cuenta
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Estas cuentas se muestran a los residentes al pagar en línea. Deben estar totalmente cargadas
                    para que los residentes puedan declarar sus pagos por servicio de la administración.
                </p>

                <?php if (!empty($accounts)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Banco</th>
                                    <th>Tipo</th>
                                    <th>Número de Cuenta</th>
                                    <th>Titular</th>
                                    <th>Pago Móvil</th>
                                    <th>Cédula/RIF</th>
                                    <th>Activa</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($accounts as $account): ?>
                                <tr>
                                    <td><?= htmlspecialchars($account['banco']) ?></td>
                                    <td>
                                        <span class="text-uppercase"><?= htmlspecialchars($account['tipo']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($account['numero_cuenta']) ?></td>
                                    <td><?= htmlspecialchars($account['titular']) ?></td>
                                    <td>
                                        <?= !empty($account['pago_movil_telefono']) ? htmlspecialchars($account['pago_movil_telefono']) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <?= !empty($account['pago_movil_cedula']) ? htmlspecialchars($account['pago_movil_cedula']) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <?php if ($account['activa']): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= APP_URL ?>/bank-accounts/edit/<?= $account['id'] ?>"
                                               class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-delete"
                                                    onclick="confirmDelete('<?= APP_URL ?>/bank-accounts/delete/<?= $account['id'] ?>')"
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
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-university fa-3x text-muted mb-3"></i>
                        <h5>No hay cuentas bancarias configuradas</h5>
                        <p class="text-muted">Registre al menos una cuenta para que los residentes puedan pagar en línea</p>
                        <a href="<?= APP_URL ?>/bank-accounts/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Cuenta
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>