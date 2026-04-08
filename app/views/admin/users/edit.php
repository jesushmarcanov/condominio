<?php $page_title = 'Editar Usuario'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>
            <a href="<?= APP_URL ?>/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Usuarios
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Información del Usuario</h5>
            </div>
            <div class="card-body">
                <?php if(isset($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <!-- Información del usuario -->
                <div class="alert alert-info">
                    <strong>Usuario ID:</strong> <?= $user->id ?><br>
                    <strong>Fecha de Registro:</strong> <?= isset($user->created_at) ? date('d/m/Y H:i', strtotime($user->created_at)) : 'N/A' ?>
                </div>

                <form method="POST" action="<?= APP_URL ?>/users/edit/<?= $user->id ?>" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($user->nombre) ?>" 
                                       placeholder="Ej: Juan Pérez" required>
                                <div class="invalid-feedback">El nombre es requerido</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user->email) ?>" 
                                       placeholder="usuario@ejemplo.com" required>
                                <div class="invalid-feedback">El email es requerido y debe ser válido</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" placeholder="Dejar en blanco para mantener actual">
                                    <button type="button" class="btn btn-outline-secondary password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Dejar en blanco si no desea cambiar la contraseña</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirmar nueva contraseña">
                                    <button type="button" class="btn btn-outline-secondary password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol *</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="admin" <?= $user->rol == 'admin' ? 'selected' : '' ?>>
                                        🛡️ Administrador
                                    </option>
                                    <option value="resident" <?= $user->rol == 'resident' ? 'selected' : '' ?>>
                                        👤 Residente
                                    </option>
                                </select>
                                <div class="invalid-feedback">Debe seleccionar un rol</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?= htmlspecialchars($user->telefono ?? '') ?>" 
                                       placeholder="555-0000">
                                <small class="form-text text-muted">Opcional</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Advertencia</h6>
                        <p class="mb-0">Cambiar el rol de un usuario puede afectar sus permisos y acceso al sistema. Asegúrese de que el cambio sea correcto.</p>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="<?= APP_URL ?>/users" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Guía de Roles -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-info-circle"></i> Información sobre Roles</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>🛡️ Administrador:</strong>
                    <ul class="mt-2">
                        <li>Acceso completo al sistema</li>
                        <li>Gestión de usuarios</li>
                        <li>Gestión de residentes</li>
                        <li>Gestión de pagos</li>
                        <li>Gestión de incidencias</li>
                        <li>Acceso a reportes</li>
                    </ul>
                </div>
                
                <div class="mb-0">
                    <strong>👤 Residente:</strong>
                    <ul class="mt-2">
                        <li>Ver sus propios pagos</li>
                        <li>Ver sus propias incidencias</li>
                        <li>Crear nuevas incidencias</li>
                        <li>Ver notificaciones</li>
                        <li>Actualizar su perfil</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de contraseñas coincidentes
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePasswords() {
        if (password.value && confirmPassword.value) {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPassword.setCustomValidity('');
            }
        } else if (!password.value && !confirmPassword.value) {
            // Si ambos están vacíos, es válido (no se cambia la contraseña)
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePasswords);
    confirmPassword.addEventListener('keyup', validatePasswords);
    
    // Toggle password visibility
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});

function confirmDelete() {
    if(confirm('¿Está seguro de que desea eliminar este usuario?\n\nEsta acción no se puede deshacer.')) {
        window.location.href = '<?= APP_URL ?>/users/delete/<?= $user->id ?>';
    }
}
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
