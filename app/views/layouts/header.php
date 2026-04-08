<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet">
</head>
<body>
    <?php if(isLoggedIn()): ?>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-building"></i> <?= APP_NAME ?></h3>
                <p><?= isAdmin() ? 'Panel de Administración' : 'Panel de Residente' ?></p>
            </div>

            <ul class="list-unstyled components">
                <!-- Dashboard -->
                <li>
                    <a href="<?= APP_URL ?>/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>

                <?php if(isAdmin()): ?>
                <!-- Usuarios (Solo Admin) -->
                <li>
                    <a href="#usersSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-user-shield"></i> Usuarios
                    </a>
                    <ul class="collapse list-unstyled" id="usersSubmenu">
                        <li><a href="<?= APP_URL ?>/users"><i class="fas fa-list"></i> Gestionar Usuarios</a></li>
                        <li><a href="<?= APP_URL ?>/users/create"><i class="fas fa-plus"></i> Nuevo Usuario</a></li>
                    </ul>
                </li>

                <!-- Residentes (Solo Admin) -->
                <li>
                    <a href="#residentsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-users"></i> Residentes
                    </a>
                    <ul class="collapse list-unstyled" id="residentsSubmenu">
                        <li><a href="<?= APP_URL ?>/residents"><i class="fas fa-list"></i> Gestionar Residentes</a></li>
                        <li><a href="<?= APP_URL ?>/residents/create"><i class="fas fa-plus"></i> Nuevo Residente</a></li>
                    </ul>
                </li>

                <!-- Pagos (Solo Admin) -->
                <li>
                    <a href="#paymentsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-dollar-sign"></i> Pagos
                    </a>
                    <ul class="collapse list-unstyled" id="paymentsSubmenu">
                        <li><a href="<?= APP_URL ?>/payments"><i class="fas fa-list"></i> Gestionar Pagos</a></li>
                        <li><a href="<?= APP_URL ?>/payments/create"><i class="fas fa-plus"></i> Nuevo Pago</a></li>
                        <li><a href="<?= APP_URL ?>/payments/pending"><i class="fas fa-clock"></i> Pagos Pendientes</a></li>
                        <li><a href="<?= APP_URL ?>/payments/stats"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
                    </ul>
                </li>

                <!-- Incidencias (Solo Admin) -->
                <li>
                    <a href="#incidentsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-exclamation-triangle"></i> Incidencias
                    </a>
                    <ul class="collapse list-unstyled" id="incidentsSubmenu">
                        <li><a href="<?= APP_URL ?>/incidents"><i class="fas fa-list"></i> Gestionar Incidencias</a></li>
                        <li><a href="<?= APP_URL ?>/incidents/create"><i class="fas fa-plus"></i> Nueva Incidencia</a></li>
                        <li><a href="<?= APP_URL ?>/incidents/stats"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
                    </ul>
                </li>

                <!-- Reportes (Solo Admin) -->
                <li>
                    <a href="#reportsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-chart-line"></i> Reportes
                    </a>
                    <ul class="collapse list-unstyled" id="reportsSubmenu">
                        <li><a href="<?= APP_URL ?>/reports"><i class="fas fa-file-alt"></i> Todos los Reportes</a></li>
                        <li><a href="<?= APP_URL ?>/reports/income"><i class="fas fa-money-bill-wave"></i> Ingresos</a></li>
                        <li><a href="<?= APP_URL ?>/reports/pendingPayments"><i class="fas fa-clock"></i> Pagos Pendientes</a></li>
                        <li><a href="<?= APP_URL ?>/reports/incidents"><i class="fas fa-exclamation-circle"></i> Incidencias</a></li>
                        <li><a href="<?= APP_URL ?>/reports/residents"><i class="fas fa-users"></i> Residentes</a></li>
                        <li><a href="<?= APP_URL ?>/reports/dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                    </ul>
                </li>

                <!-- Notificaciones (Solo Admin) -->
                <li>
                    <a href="<?= APP_URL ?>/notifications/admin">
                        <i class="fas fa-bell"></i> Notificaciones
                    </a>
                </li>

                <?php else: ?>
                <!-- Menú para Residentes -->
                <li>
                    <a href="<?= APP_URL ?>/payments">
                        <i class="fas fa-dollar-sign"></i> Mis Pagos
                    </a>
                </li>

                <li>
                    <a href="<?= APP_URL ?>/incidents">
                        <i class="fas fa-exclamation-triangle"></i> Mis Incidencias
                    </a>
                </li>

                <li>
                    <a href="<?= APP_URL ?>/incidents/create">
                        <i class="fas fa-plus-circle"></i> Reportar Incidencia
                    </a>
                </li>

                <li>
                    <a href="<?= APP_URL ?>/notifications">
                        <i class="fas fa-bell"></i> Notificaciones
                    </a>
                </li>
                <?php endif; ?>

                <!-- Separador -->
                <li style="border-top: 2px solid rgba(255, 255, 255, 0.1); margin: 10px 0;"></li>

                <!-- Perfil -->
                <li>
                    <a href="<?= APP_URL ?>/profile">
                        <i class="fas fa-user-circle"></i> Mi Perfil
                    </a>
                </li>

                <?php if(isResident()): ?>
                <li>
                    <a href="<?= APP_URL ?>/residents/myProfile">
                        <i class="fas fa-home"></i> Datos del Apartamento
                    </a>
                </li>
                <?php endif; ?>

                <!-- Cerrar Sesión -->
                <li>
                    <a href="<?= APP_URL ?>/logout">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div>
                    <button type="button" id="sidebarCollapse" class="navbar-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="ms-3 fw-bold text-muted"><?= isset($page_title) ? $page_title : 'Dashboard' ?></span>
                </div>

                <div class="user-info">
                    <!-- Notificaciones -->
                    <a href="<?= APP_URL ?>/notifications" class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                              id="notification-badge" style="display: none; font-size: 0.65rem;">
                            0
                        </span>
                    </a>

                    <!-- Usuario -->
                    <div class="dropdown">
                        <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.9rem;"><?= $_SESSION['user_name'] ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <?= isAdmin() ? 'Administrador' : 'Residente' ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down ms-2"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/profile">
                                <i class="fas fa-user-edit"></i> Mi Perfil
                            </a></li>
                            <?php if(isResident()): ?>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/residents/myProfile">
                                <i class="fas fa-home"></i> Datos del Apartamento
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php $flash = getFlash(); if($flash): ?>
            <div class="main-content">
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="main-content">
    <?php else: ?>
        <!-- Login Page (Sin Sidebar) -->
    <?php endif; ?>
