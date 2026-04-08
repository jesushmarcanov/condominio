<?php $page_title = 'Crear Usuario'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h1>
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

                <form method="POST" action="<?= APP_URL ?>/users/create" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= isset($data['nombre']) ? htmlspecialchars($data['nombre']) : '' ?>" 
                                       placeholder="Ej: Juan Pérez" required>
                                <div class="invalid-feedback">El nombre es requerido</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= isset($data['email']) ? htmlspecialchars($data['email']) : '' ?>" 
                                       placeholder="usuario@ejemplo.com" required>
                                <div class="invalid-feedback">El email es requerido y debe ser válido</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" placeholder="Mínimo 6 caracteres" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Repetir contraseña" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Las contraseñas deben coincidir</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol *</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="admin" <?= isset($data['rol']) && $data['rol'] == 'admin' ? 'selected' : '' ?>>
                                        <i class="fas fa-user-shield"></i> Administrador
                                    </option>
                                    <option value="resident" <?= isset($data['rol']) && $data['rol'] == 'resident' ? 'selected' : '' ?>>
                                        <i class="fas fa-user"></i> Residente
                                    </option>
                                </select>
                                <div class="invalid-feedback">Debe seleccionar un rol</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?= isset($data['telefono']) ? htmlspecialchars($data['telefono']) : '' ?>" 
                                       placeholder="555-0000">
                                <small class="form-text text-muted">Opcional</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Información sobre Roles</h6>
                        <p class="mb-1"><strong>Administrador:</strong> Acceso completo al sistema, puede gestionar usuarios, residentes, pagos e incidencias.</p>
                        <p class="mb-0"><strong>Residente:</strong> Acceso limitado, solo puede ver sus propios pagos e incidencias.</p>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/users" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Usuario
                        </button>
                    </div>
                </form>
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
    
    // Validación del formulario
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
