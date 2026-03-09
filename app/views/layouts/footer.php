</main>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= APP_NAME ?></h5>
                    <p>Sistema de gestión integral para condominios</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.</p>
                    <p>Versión <?= APP_VERSION ?></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Variables globales de la aplicación -->
    <script>
        const APP_URL = '<?= APP_URL ?>';
        const APP_NAME = '<?= APP_NAME ?>';
    </script>
    
    <!-- Custom JS -->
    <script src="<?= APP_URL ?>/public/js/main.js"></script>
    
    <?php if(isset($scripts)): ?>
        <?php foreach($scripts as $script): ?>
            <script src="<?= APP_URL ?>/public/js/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
