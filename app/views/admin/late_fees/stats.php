<?php
/**
 * Vista de Estadísticas de Mora
 * 
 * Dashboard con estadísticas y gráficos de mora.
 * Solo accesible para administradores.
 */

$page_title = 'Estadísticas de Mora';
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-chart-bar"></i> Estadísticas de Mora</h2>
                <a href="/late-fee-rules" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">Total Pagos con Mora</h6>
                    <h2><?php echo number_format($stats['total_pagos_con_mora'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="card-title">Mora Total Aplicada</h6>
                    <h2>$<?php echo number_format($stats['total_mora_aplicada'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Promedio de Mora</h6>
                    <h2>$<?php echo number_format($stats['promedio_mora'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h6 class="card-title">Mora Pendiente de Cobro</h6>
                    <h2>$<?php echo number_format($stats['mora_pendiente_cobro'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ingresos Mensuales -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Ingresos por Mora - Últimos 12 Meses</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyIncomeChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Residentes con Mora -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Top 10 Residentes con Mora</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($top_residents)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay datos disponibles.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Apartamento</th>
                                        <th>Residente</th>
                                        <th>Pagos</th>
                                        <th>Total Mora</th>
                                        <th>Pendiente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_residents as $resident): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($resident['apartamento']); ?></td>
                                            <td><?php echo htmlspecialchars($resident['residente_nombre']); ?></td>
                                            <td><?php echo $resident['total_pagos_con_mora']; ?></td>
                                            <td>$<?php echo number_format($resident['total_mora'], 2); ?></td>
                                            <td class="text-danger">$<?php echo number_format($resident['mora_pendiente'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Distribución por Regla -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Distribución por Regla</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($rules_distribution)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay datos disponibles.
                        </div>
                    <?php else: ?>
                        <canvas id="rulesDistributionChart" height="200"></canvas>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Regla</th>
                                        <th>Pagos</th>
                                        <th>Total Mora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rules_distribution as $rule): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($rule['regla_nombre'] ?? 'Sin regla'); ?></td>
                                            <td><?php echo $rule['total_pagos']; ?></td>
                                            <td>$<?php echo number_format($rule['total_mora'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Gráfico de Ingresos Mensuales
<?php if (!empty($monthly_income)): ?>
const monthlyIncomeCtx = document.getElementById('monthlyIncomeChart').getContext('2d');
const monthlyIncomeChart = new Chart(monthlyIncomeCtx, {
    type: 'line',
    data: {
        labels: [
            <?php foreach ($monthly_income as $month): ?>
                '<?php echo $month['mes']; ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Ingresos por Mora',
            data: [
                <?php foreach ($monthly_income as $month): ?>
                    <?php echo $month['ingresos_mora']; ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgb(255, 193, 7)',
            backgroundColor: 'rgba(255, 193, 7, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Mora: $' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(2);
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Gráfico de Distribución por Regla
<?php if (!empty($rules_distribution)): ?>
const rulesDistributionCtx = document.getElementById('rulesDistributionChart').getContext('2d');
const rulesDistributionChart = new Chart(rulesDistributionCtx, {
    type: 'doughnut',
    data: {
        labels: [
            <?php foreach ($rules_distribution as $rule): ?>
                '<?php echo addslashes($rule['regla_nombre'] ?? 'Sin regla'); ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            data: [
                <?php foreach ($rules_distribution as $rule): ?>
                    <?php echo $rule['total_mora']; ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': $' + context.parsed.toFixed(2);
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
