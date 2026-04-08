<?php $page_title = 'Login'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .login-container {
        width: 100%;
        padding: 20px;
    }
    
    .login-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }
    
    .login-card .card-body {
        padding: 40px;
    }
    
    .login-icon {
        width: 80px;
        height: 80px;
        background: #2c3e50;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    
    .login-icon i {
        font-size: 2.5rem;
        color: white;
    }
</style>

<div class="login-container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card login-card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="login-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3 class="fw-bold"><?= APP_NAME ?></h3>
                        <p class="text-muted">Inicie sesión para continuar</p>
                    </div>

                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if(isset($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= APP_URL ?>/login">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= isset($email) ? $email : '' ?>" 
                                       placeholder="usuario@ejemplo.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <small class="text-muted">
                            <strong>Usuario de prueba:</strong><br>
                            admin@condominio.com / password
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-white">
                    &copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.
                </small>
            </div>
        </div>
    </div>
</div>
