<?php $page_title = 'Gestión de Usuarios'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-users"></i> Gestión de Usuarios</h1>
            <a href="<?= APP_URL ?>/users/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </a>
        </div>
    </div>
</div>

<!-- Buscador -->
<div class="row mb-4">
    <div class="col-md-6">
        <form method="GET" action="<?= APP_URL ?>/users" class="input-group">
            <input type="text" class="form-control" name="search" 
                   placeholder="Buscar por nombre o email..." 
                   value="<?= isset($search) ? htmlspecialchars($search) : '' ?>">
            <button class="btn btn-outline-secondary" type="submit">
                <i class="fas fa-search"></i> Buscar
            </button>
            <?php if(!empty($search)): ?>
                <a href="<?= APP_URL ?>/users" class="btn btn-outline-danger">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Lista de Usuarios</h5>
            </div>
            <div class="card-body">
                <?php if(empty($users)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay usuarios registrados
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Teléfono</th>
                                    <th>Fecha de Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td>
                                            <i class="fas fa-user"></i>
                                            <?= htmlspecialchars($user['nombre']) ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-envelope"></i>
                                            <?= htmlspecialchars($user['email']) ?>
                                        </td>
                                        <td>
                                            <?php if($user['rol'] === 'admin'): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-user-shield"></i> Administrador
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-user"></i> Residente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($user['telefono'])): ?>
                                                <i class="fas fa-phone"></i>
                                                <?= htmlspecialchars($user['telefono']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar"></i>
                                            <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/users/edit/<?= $user['id'] ?>" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nombre']) ?>')"
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
                    
                    <div class="mt-3">
                        <p class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Total de usuarios: <strong><?= count($users) ?></strong>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId, userName) {
    if(confirm(`¿Está seguro de que desea eliminar al usuario "${userName}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `<?= APP_URL ?>/users/delete/${userId}`;
    }
}
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
