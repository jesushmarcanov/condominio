// JavaScript para Resumen Financiero

document.addEventListener('DOMContentLoaded', function() {
    initFinancialChart();
    initMonthlyDistributionChart();
    initCollectionRateChart();
    initQuarterlyComparisonChart();
});

// Gráfico principal financiero
function initFinancialChart() {
    const ctx = document.getElementById('financialChart').getContext('2d');
    
    // Datos desde PHP
    const labels = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    const ingresos = financialData.map(item => item.ingresos);
    const pagos = financialData.map(item => item.pagos_realizados);
    const pendientes = financialData.map(item => item.pagos_pendientes);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingresos,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    yAxisID: 'y'
                },
                {
                    label: 'Pagos Realizados',
                    data: pagos,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    type: 'bar',
                    yAxisID: 'y1'
                },
                {
                    label: 'Pagos Pendientes',
                    data: pendientes,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    type: 'bar',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label === 'Ingresos') {
                                label += '$' + context.parsed.y.toLocaleString();
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Ingresos ($)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Cantidad de Pagos'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
}

// Gráfico de distribución mensual
function initMonthlyDistributionChart() {
    const ctx = document.getElementById('monthlyDistributionChart').getContext('2d');
    
    const labels = financialData.map(item => {
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                      'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return months[item.mes - 1];
    });
    
    const ingresos = financialData.map(item => item.ingresos);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: ingresos,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(199, 199, 199, 0.8)',
                    'rgba(83, 102, 255, 0.8)',
                    'rgba(255, 99, 255, 0.8)',
                    'rgba(99, 255, 132, 0.8)',
                    'rgba(255, 206, 186, 0.8)',
                    'rgba(201, 203, 207, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = '$' + context.parsed.toLocaleString();
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Gráfico de tasa de cobro
function initCollectionRateChart() {
    const ctx = document.getElementById('collectionRateChart').getContext('2d');
    
    const labels = financialData.map(item => {
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                      'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return months[item.mes - 1];
    });
    
    const tasasCobro = financialData.map(item => {
        const total = item.pagos_realizados + item.pagos_pendientes;
        return total > 0 ? (item.pagos_realizados / total) * 100 : 0;
    });
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Tasa de Cobro (%)',
                data: tasasCobro,
                backgroundColor: tasasCobro.map(rate => {
                    if (rate >= 90) return 'rgba(75, 192, 192, 0.8)';
                    if (rate >= 70) return 'rgba(255, 206, 86, 0.8)';
                    return 'rgba(255, 99, 132, 0.8)';
                })
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Tasa de cobro: ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

// Gráfico de comparación trimestral
function initQuarterlyComparisonChart() {
    const ctx = document.getElementById('quarterlyComparisonChart').getContext('2d');
    
    // Agrupar datos por trimestres
    const quarterlyData = {
        Q1: { ingresos: 0, pagos: 0 },
        Q2: { ingresos: 0, pagos: 0 },
        Q3: { ingresos: 0, pagos: 0 },
        Q4: { ingresos: 0, pagos: 0 }
    };
    
    financialData.forEach(item => {
        const quarter = Math.ceil(item.mes / 3);
        const quarterKey = `Q${quarter}`;
        quarterlyData[quarterKey].ingresos += item.ingresos;
        quarterlyData[quarterKey].pagos += item.pagos_realizados;
    });
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            datasets: [
                {
                    label: 'Ingresos',
                    data: Object.values(quarterlyData).map(q => q.ingresos),
                    backgroundColor: 'rgba(75, 192, 192, 0.8)'
                },
                {
                    label: 'Pagos Realizados',
                    data: Object.values(quarterlyData).map(q => q.pagos),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label === 'Ingresos') {
                                label += '$' + context.parsed.y.toLocaleString();
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Función para actualizar el año
function updateYear() {
    const yearSelect = document.getElementById('year');
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            window.location.href = `${APP_URL}/reports/financialSummary?year=${this.value}`;
        });
    }
}

// Función para exportar datos
function exportFinancialData() {
    const csvContent = generateFinancialCSV();
    downloadCSV(csvContent, `resumen_financiero_${year}.csv`);
}

function generateFinancialCSV() {
    let csv = 'Mes,Ingresos,Pagos Realizados,Pagos Pendientes,Tasa Cobro\n';
    
    const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    financialData.forEach(item => {
        const total = item.pagos_realizados + item.pagos_pendientes;
        const tasaCobro = total > 0 ? (item.pagos_realizados / total) * 100 : 0;
        
        csv += `${months[item.mes - 1]},${item.ingresos},${item.pagos_realizados},${item.pagos_pendientes},${tasaCobro.toFixed(1)}%\n`;
    });
    
    return csv;
}

function downloadCSV(content, filename) {
    const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Función para imprimir reporte
function printFinancialReport() {
    window.print();
}

// Función para calcular métricas adicionales
function calculateMetrics() {
    const totalIngresos = financialData.reduce((sum, item) => sum + item.ingresos, 0);
    const totalPagos = financialData.reduce((sum, item) => sum + item.pagos_realizados, 0);
    const promedioMensual = totalIngresos / 12;
    
    // Actualizar métricas en la página si existen los elementos
    const totalElement = document.getElementById('total-ingresos');
    const promedioElement = document.getElementById('promedio-mensual');
    
    if (totalElement) {
        totalElement.textContent = '$' + totalIngresos.toLocaleString();
    }
    
    if (promedioElement) {
        promedioElement.textContent = '$' + promedioMensual.toLocaleString();
    }
}

// Inicializar eventos
document.addEventListener('DOMContentLoaded', function() {
    updateYear();
    calculateMetrics();
    
    // Botones de exportación
    const exportBtn = document.querySelector('a[href*="export=csv"]');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            exportFinancialData();
        });
    }
    
    // Botón de imprimir
    const printBtn = document.querySelector('button[onclick*="print"]');
    if (printBtn) {
        printBtn.addEventListener('click', printFinancialReport);
    }
});
