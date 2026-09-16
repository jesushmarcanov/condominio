            </div><!-- /.main-content -->
        </div><!-- /#content -->
    </div><!-- /.wrapper -->

    <!-- Footer -->
    <footer class="bg-dark text-light py-3" style="margin-left: <?= isLoggedIn() ? '260px' : '0' ?>;">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> Jesús H. Marcano V. — <?= APP_NAME ?>. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Versión <?= APP_VERSION ?></p>
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
    
    <!-- Sidebar Toggle Script -->
    <?php if(isLoggedIn()): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const sidebarHeader = document.getElementById('sidebarHeader');
            
            if (sidebarCollapse) {
                sidebarCollapse.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    content.classList.toggle('active');
                });
            }

            // Alternar modo mini (solo iconos) al hacer clic en el logo/nombre
            function toggleMiniSidebar(force) {
                if (window.innerWidth <= 768) {
                    return; // en móvil se mantiene el comportamiento actual
                }
                const isMini = typeof force === 'boolean'
                    ? force
                    : !document.body.classList.contains('sidebar-mini');
                document.body.classList.toggle('sidebar-mini', isMini);
                sidebar.classList.toggle('mini', isMini);
                content.classList.toggle('mini', isMini);
            }

            if (sidebarHeader) {
                sidebarHeader.addEventListener('click', function() {
                    toggleMiniSidebar();
                });
                sidebarHeader.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggleMiniSidebar();
                    }
                });
            }
            
            // Cerrar sidebar en móvil al hacer clic en un enlace
            if (window.innerWidth <= 768) {
                const sidebarLinks = document.querySelectorAll('.sidebar a:not(.dropdown-toggle)');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        sidebar.classList.add('active');
                        content.classList.add('active');
                    });
                });
            }
        });
    </script>
    <?php endif; ?>
    
    <!-- Custom JS -->
    <script src="<?= APP_URL ?>/public/js/main.js"></script>
    
    <!-- Notification Badge Update -->
    <?php if(isLoggedIn()): ?>
    <script>
        // Actualizar contador de notificaciones no leídas
        function updateNotificationBadge() {
            fetch(APP_URL + '/notifications/getUnreadCount')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.count > 0) {
                        const badge = document.getElementById('notification-badge');
                        if (badge) {
                            badge.textContent = data.count;
                            badge.style.display = 'inline-block';
                        }
                    } else {
                        const badge = document.getElementById('notification-badge');
                        if (badge) {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error al obtener notificaciones:', error));
        }
        
        // Actualizar al cargar la página
        document.addEventListener('DOMContentLoaded', updateNotificationBadge);
        
        // Actualizar cada 60 segundos
        setInterval(updateNotificationBadge, 60000);
    </script>
    <?php endif; ?>
    
    <?php if(isset($scripts)): ?>
        <?php foreach($scripts as $script): ?>
            <script src="<?= APP_URL ?>/public/js/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
