<?php $page_title = 'Medios de Pago'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-money-bill-wave"></i> Medios de Pago</h1>
            <div>
                <a href="<?= APP_URL ?>/settings" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Configuración
                </a>
                <a href="<?= APP_URL ?>/medios-pago/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Medio de Pago
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
                    Estas formas de pago aparecen en el select <strong>Método de Pago</strong>
                    del formulario de registro de pagos. Los medios activos son los visibles.
                </p>

                <?php if (!empty($medios)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Valor (slug)</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medios as $medio): ?>
                                <tr>
                                    <td><?= htmlspecialchars($medio['nombre']) ?></td>
                                    <td><code><?= htmlspecialchars($medio['valor']) ?></code></td>
                                    <td>
                                        <?php if ($medio['activa']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= APP_URL ?>/medios-pago/edit/<?= $medio['id'] ?>"
                                               class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-delete"
                                                    onclick="confirmDelete('<?= APP_URL ?>/medios-pago/delete/<?= $medio['id'] ?>')"
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
                        <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                        <h5>No hay medios de pago configurados</h5>
                        <p class="text-muted">Registre al menos una forma de pago para el formulario de pagos</p>
                        <a href="<?= APP_URL ?>/medios-pago/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Medio de Pago
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(url) {
    if (confirm('¿Está seguro de que desea eliminar este medio de pago?')) {
        window.location.href = url;
    }
}
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>