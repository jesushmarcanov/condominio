<?php $page_title = 'Gestión de Incidencias'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-exclamation-triangle"></i> Gestión de Incidencias</h1>
            <a href="<?= APP_URL ?>/incidents/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Incidencia
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
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="pendiente" <?= $status == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="en_proceso" <?= $status == 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                            <option value="resuelta" <?= $status == 'resuelta' ? 'selected' : '' ?>>Resuelta</option>
                            <option value="cancelada" <?= $status == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Categoría</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Todas</option>
                            <option value="agua" <?= $category == 'agua' ? 'selected' : '' ?>>Agua</option>
                            <option value="electricidad" <?= $category == 'electricidad' ? 'selected' : '' ?>>Electricidad</option>
                            <option value="gas" <?= $category == 'gas' ? 'selected' : '' ?>>Gas</option>
                            <option value="estructura" <?= $category == 'estructura' ? 'selected' : '' ?>>Estructura</option>
                            <option value="limpieza" <?= $category == 'limpieza' ? 'selected' : '' ?>>Limpieza</option>
                            <option value="seguridad" <?= $category == 'seguridad' ? 'selected' : '' ?>>Seguridad</option>
                            <option value="otro" <?= $category == 'otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Prioridad</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="">Todas</option>
                            <option value="alta" <?= $priority == 'alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="media" <?= $priority == 'media' ? 'selected' : '' ?>>Media</option>
                            <option value="baja" <?= $priority == 'baja' ? 'selected' : '' ?>>Baja</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="<?= APP_URL ?>/incidents" class="btn btn-secondary ms-2">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Incidencias -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if(!empty($incidents)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Residente</th>
                                    <th>Categoría</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <?php if($is_admin): ?>
                                    <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($incidents as $incident): ?>
                                <tr>
                                    <td>#<?= $incident['id'] ?></td>
                                    <td>
                                        <a href="<?= APP_URL ?>/incidents/show/<?= $incident['id'] ?>">
                                            <?= $incident['titulo'] ?>
                                        </a>
                                    </td>
                                    <td><?= $incident['residente_nombre'] ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= $incident['categoria'] ?></span>
                                    </td>
                                    <td>
                                        <span class="priority-<?= $incident['prioridad'] ?>">
                                            <?= $incident['prioridad'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $incident['estado'] == 'resuelta' ? 'success' : 
                                            ($incident['estado'] == 'en_proceso' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= $incident['estado'] ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($incident['fecha_reporte']) ?></td>
                                    <?php if($is_admin): ?>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= APP_URL ?>/incidents/show/<?= $incident['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= APP_URL ?>/incidents/edit/<?= $incident['id'] ?>" 
                                               class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger btn-delete" 
                                                    onclick="confirmDelete('<?= APP_URL ?>/incidents/delete/<?= $incident['id'] ?>')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <span class="badge bg-danger">Pendientes: <?= count(array_filter($incidents, fn($i) => $i['estado'] == 'pendiente')) ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning">En Proceso: <?= count(array_filter($incidents, fn($i) => $i['estado'] == 'en_proceso')) ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-success">Resueltas: <?= count(array_filter($incidents, fn($i) => $i['estado'] == 'resuelta')) ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Total:</strong> <?= count($incidents) ?>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                        <h5>No hay incidencias registradas</h5>
                        <p class="text-muted">
                            <a href="<?= APP_URL ?>/incidents/create">Reporta la primera incidencia</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
