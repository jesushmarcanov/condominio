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
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= APP_URL ?>/dashboard">
                <i class="fas fa-building"></i> <?= APP_NAME ?>
            </a>
            
            <?php if(isLoggedIn()): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if(isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users"></i> Residentes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/residents">Gestionar Residentes</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/residents/create">Nuevo Residente</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-dollar-sign"></i> Pagos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/payments">Gestionar Pagos</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/payments/create">Nuevo Pago</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/payments/pending">Pagos Pendientes</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/payments/stats">Estadísticas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-exclamation-triangle"></i> Incidencias
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/incidents">Gestionar Incidencias</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/incidents/create">Nueva Incidencia</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/incidents/stats">Estadísticas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar"></i> Reportes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/reports">Todos los Reportes</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/reports/income">Reporte de Ingresos</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/reports/pendingPayments">Pagos Pendientes</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/reports/incidents">Reporte de Incidencias</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/reports/residents">Reporte de Residentes</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/reports/dashboard">Dashboard Estadístico</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/dashboard">
                            <i class="fas fa-home"></i> Mi Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/payments">
                            <i class="fas fa-dollar-sign"></i> Mis Pagos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/incidents">
                            <i class="fas fa-exclamation-triangle"></i> Mis Incidencias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/incidents/create">
                            <i class="fas fa-plus"></i> Reportar Incidencia
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= $_SESSION['user_name'] ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/profile">
                                <i class="fas fa-user-edit"></i> Mi Perfil
                            </a></li>
                            <?php if(isResident()): ?>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/residents/myProfile">
                                <i class="fas fa-home"></i> Datos del Apartamento
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/logout">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php $flash = getFlash(); if($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container mt-4">
