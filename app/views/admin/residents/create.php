<?php $page_title = 'Nuevo Residente'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-user-plus"></i> Nuevo Residente</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-home"></i> Datos del Residente</h5>
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

                <form method="POST" action="<?= APP_URL ?>/residents/create" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Información Personal</h6>
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= isset($data['nombre']) ? $data['nombre'] : '' ?>" required>
                                <div class="invalid-feedback">El nombre es requerido</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= isset($data['email']) ? $data['email'] : '' ?>" required>
                                <div class="invalid-feedback">El email es requerido y debe ser válido</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?= isset($data['telefono']) ? $data['telefono'] : '' ?>"
                                       placeholder="555-0000">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="mb-3">Información del Apartamento</h6>
                            
                            <div class="mb-3">
                                <label for="apartamento" class="form-label">Apartamento *</label>
                                <input type="text" class="form-control" id="apartamento" name="apartamento" 
                                       value="<?= isset($data['apartamento']) ? $data['apartamento'] : '' ?>" 
                                       placeholder="Ej: A-101" required>
                                <div class="invalid-feedback">El número de apartamento es requerido</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="piso" class="form-label">Piso *</label>
                                <input type="number" class="form-control" id="piso" name="piso" 
                                       value="<?= isset($data['piso']) ? $data['piso'] : '' ?>" 
                                       min="1" max="50" required>
                                <div class="invalid-feedback">El piso es requerido</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="torre" class="form-label">Torre</label>
                                <input type="text" class="form-control" id="torre" name="torre" 
                                       value="<?= isset($data['torre']) ? $data['torre'] : '' ?>"
                                       placeholder="Ej: A, B, C">
                                <small class="form-text">Opcional, si el condominio tiene varias torres</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="fecha_ingreso" class="form-label">Fecha de Ingreso *</label>
                                <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" 
                                       value="<?= isset($data['fecha_ingreso']) ? $data['fecha_ingreso'] : date('Y-m-d') ?>" 
                                       required>
                                <div class="invalid-feedback">La fecha de ingreso es requerida</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="activo" <?= isset($data['estado']) && $data['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactivo" <?= isset($data['estado']) && $data['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/residents" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Residente
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
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
