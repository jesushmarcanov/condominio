<?php $page_title = 'Gestión de Residentes'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-users"></i> Gestión de Residentes</h1>
            <a href="<?= APP_URL ?>/residents/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Residente
            </a>
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
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= isset($_GET['search']) ? sanitize($_GET['search']) : '' ?>"
                               placeholder="Nombre, email, apartamento...">
                    </div>
                    <div class="col-md-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="activo" <?= isset($_GET['estado']) && $_GET['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= isset($_GET['estado']) && $_GET['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="piso" class="form-label">Piso</label>
                        <input type="text" class="form-control" id="piso" name="piso" 
                               value="<?= isset($_GET['piso']) ? sanitize($_GET['piso']) : '' ?>"
                               placeholder="Ej: 1, 2, 3...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <a href="<?= APP_URL ?>/residents" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Residentes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if(isset($residents) && count($residents) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Apartamento</th>
                                    <th>Piso</th>
                                    <th>Torre</th>
                                    <th>Estado</th>
                                    <th>Fecha Ingreso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($residents as $resident): ?>
                                    <tr>
                                        <td><?= $resident['id'] ?></td>
                                        <td><?= $resident['nombre'] ?></td>
                                        <td><?= $resident['email'] ?></td>
                                        <td><?= $resident['telefono'] ?: 'N/A' ?></td>
                                        <td><?= $resident['apartamento'] ?></td>
                                        <td><?= $resident['piso'] ?></td>
                                        <td><?= $resident['torre'] ?: 'N/A' ?></td>
                                        <td>
                                            <span class="badge bg-<?= $resident['estado'] == 'activo' ? 'success' : 'secondary' ?>">
                                                <?= $resident['estado'] ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($resident['fecha_ingreso']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= APP_URL ?>/residents/show/<?= $resident['id'] ?>" 
                                                   class="btn btn-outline-primary" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= APP_URL ?>/residents/edit/<?= $resident['id'] ?>" 
                                                   class="btn btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="confirmDelete(<?= $resident['id'] ?>, '<?= $resident['nombre'] ?>')"
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
                    
                    <!-- Paginación -->
                    <nav aria-label="Paginación de residentes">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Anterior</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">2</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron residentes</h5>
                        <p class="text-muted">No hay residentes registrados o no coinciden con los filtros aplicados.</p>
                        <a href="<?= APP_URL ?>/residents/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Registrar Primer Residente
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Rápidas -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary"><?= isset($stats['total_residentes']) ? $stats['total_residentes'] : 0 ?></h4>
                <p class="mb-0">Total Residentes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success"><?= isset($stats['residentes_activos']) ? $stats['residentes_activos'] : 0 ?></h4>
                <p class="mb-0">Residentes Activos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-warning"><?= isset($stats['residentes_inactivos']) ? $stats['residentes_inactivos'] : 0 ?></h4>
                <p class="mb-0">Residentes Inactivos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info"><?= isset($stats['nuevos_30_dias']) ? $stats['nuevos_30_dias'] : 0 ?></h4>
                <p class="mb-0">Nuevos (30 días)</p>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    if(confirm(`¿Está seguro de que desea eliminar al residente "${name}"? Esta acción no se puede deshacer.`)) {
        window.location.href = `<?= APP_URL ?>/residents/delete/${id}`;
    }
}
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
