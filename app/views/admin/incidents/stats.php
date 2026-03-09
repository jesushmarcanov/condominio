<?php $page_title = 'Estadísticas de Incidencias'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-chart-line"></i> Estadísticas de Incidencias</h1>
    </div>
</div>

<!-- Resumen General -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $stats['total_incidencias'] ?></h4>
                        <p class="card-text">Total Incidencias</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $stats['incidencias_resueltas'] ?></h4>
                        <p class="card-text">Resueltas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $stats['incidencias_pendientes'] ?></h4>
                        <p class="card-text">Pendientes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $stats['incidencias_en_proceso'] ?></h4>
                        <p class="card-text">En Proceso</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-cogs fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-area"></i> Incidencias Mensuales</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyIncidentsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Incidencias por Categoría</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Incidencias por Prioridad</h5>
            </div>
            <div class="card-body">
                <canvas id="priorityChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Estado de Incidencias</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Detalles -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Incidencias por Categoría</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_by_category = array_sum(array_column($incidents_by_category, 'cantidad'));
                            foreach($incidents_by_category as $category): 
                            $percentage = $total_by_category > 0 ? ($category['cantidad'] / $total_by_category) * 100 : 0;
                            ?>
                            <tr>
                                <td><?= ucfirst($category['categoria']) ?></td>
                                <td><?= $category['cantidad'] ?></td>
                                <td><?= number_format($percentage, 1) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar"></i> Incidencias Mensuales</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th>Cantidad</th>
                                <th>Tendencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $monthly_data = array_slice($monthly_incidents, -6); // Últimos 6 meses
                            foreach($monthly_data as $index => $month): 
                            $prev_month = $index > 0 ? $monthly_data[$index - 1]['cantidad'] : $month['cantidad'];
                            $trend = $month['cantidad'] - $prev_month;
                            $trend_icon = $trend > 0 ? '↑' : ($trend < 0 ? '↓' : '→');
                            $trend_color = $trend > 0 ? 'text-danger' : ($trend < 0 ? 'text-success' : 'text-muted');
                            ?>
                            <tr>
                                <td><?= date('M Y', strtotime($month['mes'] . '-01')) ?></td>
                                <td><?= $month['cantidad'] ?></td>
                                <td><span class="<?= $trend_color ?>"><?= $trend_icon ?> <?= abs($trend) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tiempo Promedio de Resolución -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-header">
                <h5><i class="fas fa-hourglass-half"></i> Métricas de Rendimiento</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h6>Tiempo Promedio de Resolución</h6>
                        <h4 class="text-primary">
                            <?php 
                            $avg_resolution_time = $stats['incidencias_resueltas'] > 0 ? 
                                $stats['tiempo_promedio_resolucion'] : 0;
                            echo $avg_resolution_time . ' días';
                            ?>
                        </h4>
                    </div>
                    <div class="col-md-3">
                        <h6>Tasa de Resolución</h6>
                        <h4 class="text-success">
                            <?php 
                            $resolution_rate = $stats['total_incidencias'] > 0 ? 
                                ($stats['incidencias_resueltas'] / $stats['total_incidencias']) * 100 : 0;
                            echo number_format($resolution_rate, 1) . '%';
                            ?>
                        </h4>
                    </div>
                    <div class="col-md-3">
                        <h6>Incidencias Críticas</h6>
                        <h4 class="text-danger">
                            <?= $stats['incidencias_alta_prioridad'] ?>
                        </h4>
                    </div>
                    <div class="col-md-3">
                        <h6>Incidencias Este Mes</h6>
                        <h4 class="text-info">
                            <?= $monthly_incidents[count($monthly_incidents) - 1]['cantidad'] ?? 0 ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

<script>
// Gráfico de Incidencias Mensuales
const monthlyIncidentsCtx = document.getElementById('monthlyIncidentsChart').getContext('2d');
const monthlyIncidentsChart = new Chart(monthlyIncidentsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($item) { return date('M Y', strtotime($item['mes'] . '-01')); }, $monthly_incidents)) ?>,
        datasets: [{
            label: 'Incidencias Mensuales',
            data: <?= json_encode(array_column($monthly_incidents, 'cantidad')) ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico de Incidencias por Categoría
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_map(function($item) { return ucfirst($item['categoria']); }, $incidents_by_category)) ?>,
        datasets: [{
            data: <?= json_encode(array_column($incidents_by_category, 'cantidad')) ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico de Prioridad
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
const priorityChart = new Chart(priorityCtx, {
    type: 'bar',
    data: {
        labels: ['Baja', 'Media', 'Alta'],
        datasets: [{
            label: 'Incidencias por Prioridad',
            data: [<?= $stats['incidencias_baja_prioridad'] ?>, <?= $stats['incidencias_media_prioridad'] ?>, <?= $stats['incidencias_alta_prioridad'] ?>],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico de Estado
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: ['Pendientes', 'En Proceso', 'Resueltas', 'Canceladas'],
        datasets: [{
            data: [<?= $stats['incidencias_pendientes'] ?>, <?= $stats['incidencias_en_proceso'] ?>, <?= $stats['incidencias_resueltas'] ?>, <?= $stats['incidencias_canceladas'] ?>],
            backgroundColor: [
                'rgba(255, 193, 7, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
