<?php $page_title = 'Reporte de Residentes'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-users"></i> Reporte de Residentes</h1>
        <p class="text-muted">Información detallada de todos los residentes</p>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-filter"></i> Filtros</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= APP_URL ?>/reports/residents">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="activo" <?= $status === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $status === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="<?= APP_URL ?>/reports/residents?export=csv" class="btn btn-success">
                                    <i class="fas fa-file-csv"></i> Exportar CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5><i class="fas fa-users"></i> Total Residentes</h5>
                <h3><?= count($residents) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5><i class="fas fa-user-check"></i> Activos</h5>
                <h3><?= count(array_filter($residents, fn($r) => $r['estado'] === 'activo')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <h5><i class="fas fa-user-times"></i> Inactivos</h5>
                <h3><?= count(array_filter($residents, fn($r) => $r['estado'] === 'inactivo')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5><i class="fas fa-building"></i> Apartamentos</h5>
                <h3><?= count(array_unique(array_column($residents, 'apartamento'))) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Residentes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Detalle de Residentes</h5>
            </div>
            <div class="card-body">
                <?php if(empty($residents)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se encontraron residentes con los filtros seleccionados.
                    </div>
                <?php else: ?>
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
                                    <?php
                                    $estado_class = $resident['estado'] === 'activo' ? 'success' : 'secondary';
                                    ?>
                                    <tr>
                                        <td><?= $resident['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($resident['nombre']) ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($resident['email']) ?>">
                                                <?= htmlspecialchars($resident['email']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($resident['telefono']) ?></td>
                                        <td><?= htmlspecialchars($resident['apartamento']) ?></td>
                                        <td><?= htmlspecialchars($resident['piso']) ?></td>
                                        <td><?= htmlspecialchars($resident['torre']) ?: '-' ?></td>
                                        <td>
                                            <span class="badge bg-<?= $estado_class ?>">
                                                <?= htmlspecialchars($resident['estado']) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($resident['fecha_ingreso']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/residents/show/<?= $resident['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= APP_URL ?>/residents/edit/<?= $resident['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="mailto:<?= htmlspecialchars($resident['email']) ?>" 
                                                   class="btn btn-sm btn-outline-info" title="Enviar email">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
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

<!-- Estadísticas Adicionales -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Residentes por Estado</h5>
            </div>
            <div class="card-body">
                <canvas id="residentStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Residentes por Piso</h5>
            </div>
            <div class="card-body">
                <canvas id="residentFloorChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Distribución por Torres</h5>
            </div>
            <div class="card-body">
                <canvas id="residentTowerChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar"></i> Ingresos por Mes</h5>
            </div>
            <div class="card-body">
                <canvas id="residentIngressChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.85em;
}

.table th {
    font-weight: 600;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table a {
    text-decoration: none;
}

.table a:hover {
    text-decoration: underline;
}
</style>

<?php 
$scripts = ['reports.js'];
include APP_PATH . '/views/layouts/footer.php'; 
?>
