<?php $page_title = 'Reporte de Incidencias'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-tools"></i> Reporte de Incidencias</h1>
        <p class="text-muted">Estadísticas y análisis de incidencias reportadas</p>
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
                <form method="GET" action="<?= APP_URL ?>/reports/incidents">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $start_date ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $end_date ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="abierto" <?= $status === 'abierto' ? 'selected' : '' ?>>Abierto</option>
                                <option value="en_progreso" <?= $status === 'en_progreso' ? 'selected' : '' ?>>En Progreso</option>
                                <option value="resuelto" <?= $status === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                <option value="cerrado" <?= $status === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="<?= APP_URL ?>/reports/incidents?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&export=csv" 
                                   class="btn btn-success">
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
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5><i class="fas fa-list"></i> Total Incidencias</h5>
                <h3><?= count($incidents) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5><i class="fas fa-exclamation-circle"></i> Abiertas</h5>
                <h3><?= count(array_filter($incidents, fn($i) => $i['estado'] === 'abierto')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5><i class="fas fa-spinner"></i> En Progreso</h5>
                <h3><?= count(array_filter($incidents, fn($i) => $i['estado'] === 'en_progreso')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5><i class="fas fa-check-circle"></i> Resueltas</h5>
                <h3><?= count(array_filter($incidents, fn($i) => in_array($i['estado'], ['resuelto', 'cerrado']))) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Incidencias -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Detalle de Incidencias</h5>
            </div>
            <div class="card-body">
                <?php if(empty($incidents)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se encontraron incidencias en el período seleccionado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Prioridad</th>
                                    <th>Residente</th>
                                    <th>Estado</th>
                                    <th>Fecha Reporte</th>
                                    <th>Fecha Resolución</th>
                                    <th>Tiempo Resolución</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($incidents as $incident): ?>
                                    <?php
                                    $prioridad_class = $incident['prioridad'] === 'alta' ? 'danger' : 
                                                       ($incident['prioridad'] === 'media' ? 'warning' : 'info');
                                    $estado_class = $incident['estado'] === 'abierto' ? 'danger' : 
                                                   ($incident['estado'] === 'en_progreso' ? 'warning' : 
                                                   ($incident['estado'] === 'resuelto' ? 'success' : 'secondary'));
                                    
                                    $tiempo_resolucion = '';
                                    if($incident['fecha_resolucion']) {
                                        $dias = (strtotime($incident['fecha_resolucion']) - strtotime($incident['fecha_reporte'])) / 86400;
                                        $tiempo_resolucion = round($dias) . ' días';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $incident['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($incident['titulo']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= substr(htmlspecialchars($incident['descripcion']), 0, 50) ?>...</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($incident['categoria']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $prioridad_class ?>">
                                                <?= htmlspecialchars($incident['prioridad']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($incident['residente_nombre']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $estado_class ?>">
                                                <?= htmlspecialchars($incident['estado']) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($incident['fecha_reporte']) ?></td>
                                        <td><?= $incident['fecha_resolucion'] ? formatDate($incident['fecha_resolucion']) : '-' ?></td>
                                        <td><?= $tiempo_resolucion ?: '-' ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/incidents/show/<?= $incident['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= APP_URL ?>/incidents/edit/<?= $incident['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
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

<!-- Gráficos -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Incidencias por Estado</h5>
            </div>
            <div class="card-body">
                <canvas id="incidentStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Incidencias por Categoría</h5>
            </div>
            <div class="card-body">
                <canvas id="incidentCategoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Incidencias por Prioridad</h5>
            </div>
            <div class="card-body">
                <canvas id="incidentPriorityChart" height="100"></canvas>
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
</style>

<?php 
$scripts = ['reports.js'];
include APP_PATH . '/views/layouts/footer.php'; 
?>
