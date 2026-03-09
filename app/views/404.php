<?php $page_title = 'Página No Encontrada'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 text-center">
        <div class="error-template">
            <h1 class="display-1">404</h1>
            <h2>Página No Encontrada</h2>
            <p class="text-muted">La página que estás buscando no existe o ha sido movida.</p>
            
            <div class="error-actions mt-4">
                <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">
                    <i class="fas fa-home"></i> Ir al Dashboard
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver Atrás
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.error-template {
    padding: 40px 0;
}
.display-1 {
    font-size: 6rem;
    font-weight: bold;
    color: #dc3545;
}
.error-actions .btn {
    margin: 0 10px;
}
</style>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
