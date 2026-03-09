<?php $page_title = 'Mi Perfil'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-user"></i> Mi Perfil</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
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

                <form method="POST" action="<?= APP_URL ?>/profile" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= $user_data->nombre ?>" required>
                                <div class="invalid-feedback">
                                    El nombre es requerido
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= $user_data->email ?>" required>
                                <div class="invalid-feedback">
                                    El email es requerido y debe ser válido
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?= $user_data->telefono ?>" 
                                       placeholder="555-0000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <input type="text" class="form-control" id="rol" name="rol" 
                                       value="<?= ucfirst($user_data->rol) ?>" readonly>
                                <small class="form-text text-muted">El rol no puede ser modificado</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5>Cambiar Contraseña</h5>
                    <p class="text-muted">Deja estos campos en blanco si no deseas cambiar tu contraseña</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6">
                                    <button type="button" class="btn btn-outline-secondary password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    <button type="button" class="btn btn-outline-secondary password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Información de la cuenta -->
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Información de la Cuenta</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID de Usuario:</strong> <?= $user['id'] ?></p>
                        <p><strong>Fecha de Registro:</strong> <?= formatDate($user_data->created_at) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Última Actualización:</strong> <?= formatDate($user_data->updated_at) ?></p>
                        <p><strong>Estado:</strong> <span class="badge bg-success">Activo</span></p>
                    </div>
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
        }
    }
    
    password.addEventListener('change', validatePasswords);
    confirmPassword.addEventListener('keyup', validatePasswords);
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
