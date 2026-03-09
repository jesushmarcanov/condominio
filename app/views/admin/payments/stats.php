<?php $page_title = 'Estadísticas de Pagos'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-chart-line"></i> Estadísticas de Pagos</h1>
    </div>
</div>

<!-- Resumen General -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $stats['total_pagos'] ?></h4>
                        <p class="card-text">Total Pagos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x"></i>
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
                        <h4 class="card-title"><?= formatCurrency($stats['total_ingresos']) ?></h4>
                        <p class="card-text">Total Ingresos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
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
                        <h4 class="card-title"><?= $stats['pagos_pendientes'] ?></h4>
                        <p class="card-text">Pagos Pendientes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= formatCurrency($stats['total_atrasado']) ?></h4>
                        <p class="card-text">Total Atrasado</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
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
                <h5><i class="fas fa-chart-area"></i> Ingresos Mensuales</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyIncomeChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Estado de Pagos</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Ingresos Mensuales -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-table"></i> Detalle de Ingresos Mensuales</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th>Ingresos</th>
                                <th>Cantidad de Pagos</th>
                                <th>Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($monthly_income as $income): ?>
                            <tr>
                                <td><?= date('M Y', strtotime($income['mes'] . '-01')) ?></td>
                                <td><?= formatCurrency($income['ingresos']) ?></td>
                                <td><?= $income['cantidad_pagos'] ?></td>
                                <td><?= formatCurrency($income['cantidad_pagos'] > 0 ? $income['ingresos'] / $income['cantidad_pagos'] : 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

<script>
// Gráfico de Ingresos Mensuales
const monthlyIncomeCtx = document.getElementById('monthlyIncomeChart').getContext('2d');
const monthlyIncomeChart = new Chart(monthlyIncomeCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($item) { return date('M Y', strtotime($item['mes'] . '-01')); }, $monthly_income)) ?>,
        datasets: [{
            label: 'Ingresos Mensuales',
            data: <?= json_encode(array_column($monthly_income, 'ingresos')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Ingresos: $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// Gráfico de Estado de Pagos
const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
const paymentStatusChart = new Chart(paymentStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pagados', 'Pendientes', 'Atrasados'],
        datasets: [{
            data: [<?= $stats['pagos_realizados'] ?>, <?= $stats['pagos_pendientes'] ?>, <?= $stats['pagos_atrasados'] ?>],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
</script>
