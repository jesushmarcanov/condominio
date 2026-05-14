// JavaScript específico para el Dashboard

document.addEventListener('DOMContentLoaded', function() {
    
    // Inicializar gráfico de ingresos mensuales
    initIncomeChart();
    
    // Inicializar gráfico de incidencias por categoría
    initIncidentChart();
    
    // Inicializar gráfico de incidencias mensuales
    initMonthlyIncidentsChart();
    
    // Inicializar gráfico de métodos de pago
    initPaymentMethodsChart();
    
    // Actualizar estadísticas en tiempo real
    updateStats();
    
    // Auto-refrescar cada 5 minutos
    setInterval(updateStats, 300000);
});

// Gráfico de ingresos mensuales
function initIncomeChart() {
    var ctx = document.getElementById('monthlyIncomeChart');
    if (!ctx) return;
    
    // Usar datos pasados desde el servidor
    if (typeof monthlyIncomeData !== 'undefined' && monthlyIncomeData.length > 0) {
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyIncomeData.map(item => {
                    var date = new Date(item.period + '-01');
                    return date.toLocaleDateString('es-MX', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Ingresos Mensuales',
                    data: monthlyIncomeData.map(item => parseFloat(item.amount)),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ingresos: $' + context.parsed.y.toLocaleString('es-MX');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-MX');
                            }
                        }
                    }
                }
            }
        });
    }
}

// Gráfico de incidencias por categoría
function initIncidentChart() {
    var ctx = document.getElementById('incidentsCategoryChart');
    if (!ctx) return;
    
    // Usar datos pasados desde el servidor
    if (typeof incidentsCategoryData !== 'undefined' && incidentsCategoryData.length > 0) {
        var chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: incidentsCategoryData.map(item => item.category.charAt(0).toUpperCase() + item.category.slice(1)),
                datasets: [{
                    data: incidentsCategoryData.map(item => parseInt(item.count)),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                        '#FF6384'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Gráfico de incidencias mensuales
function initMonthlyIncidentsChart() {
    var ctx = document.getElementById('monthlyIncidentsChart');
    if (!ctx) return;
    
    // Usar datos pasados desde el servidor
    if (typeof monthlyIncidentsData !== 'undefined' && monthlyIncidentsData.length > 0) {
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthlyIncidentsData.map(item => {
                    var date = new Date(item.period + '-01');
                    return date.toLocaleDateString('es-MX', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Incidencias Mensuales',
                    data: monthlyIncidentsData.map(item => parseInt(item.count)),
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
}

// Gráfico de métodos de pago
function initPaymentMethodsChart() {
    var ctx = document.getElementById('paymentMethodsChart');
    if (!ctx) return;
    
    // Usar datos pasados desde el servidor
    if (typeof paymentMethodsData !== 'undefined' && paymentMethodsData.length > 0) {
        var chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: paymentMethodsData.map(item => {
                    var methods = {
                        'efectivo': 'Efectivo',
                        'transferencia': 'Transferencia',
                        'tarjeta': 'Tarjeta',
                        'deposito': 'Depósito'
                    };
                    return methods[item.method] || item.method;
                }),
                datasets: [{
                    data: paymentMethodsData.map(item => parseInt(item.count)),
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8',
                        '#ffc107',
                        '#6f42c1'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' pagos (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Actualizar estadísticas
function updateStats() {
    // Mostrar indicador de carga
    var statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach(function(card) {
        card.style.opacity = '0.5';
    });
    
    // Simular actualización de datos
    setTimeout(function() {
        statsCards.forEach(function(card) {
            card.style.opacity = '1';
        });
        
        // Mostrar notificación de actualización
        showToast('Estadísticas actualizadas', 'success');
    }, 1000);
}

// Función para exportar dashboard a PDF
function exportDashboardToPDF() {
    showToast('Generando PDF...', 'info');
    
    // Aquí se implementaría la lógica para exportar a PDF
    // Por ahora solo mostramos un mensaje
    setTimeout(function() {
        showToast('PDF generado correctamente', 'success');
    }, 2000);
}

// Función para imprimir dashboard
function printDashboard() {
    window.print();
}

// Manejo de filtros de fecha
document.addEventListener('DOMContentLoaded', function() {
    var dateRangeButtons = document.querySelectorAll('.date-range-btn');
    dateRangeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var range = this.dataset.range;
            updateDateRange(range);
        });
    });
});

function updateDateRange(range) {
    var startDate, endDate;
    var today = new Date();
    
    switch(range) {
        case '7d':
            startDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            break;
        case '30d':
            startDate = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            break;
        case '90d':
            startDate = new Date(today.getTime() - 90 * 24 * 60 * 60 * 1000);
            break;
        case '1y':
            startDate = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());
            break;
        default:
            return;
    }
    
    endDate = today;
    
    // Actualizar los campos de fecha si existen
    var startDateInput = document.getElementById('start_date');
    var endDateInput = document.getElementById('end_date');
    
    if (startDateInput) {
        startDateInput.value = startDate.toISOString().split('T')[0];
    }
    
    if (endDateInput) {
        endDateInput.value = endDate.toISOString().split('T')[0];
    }
    
    // Recargar los datos del dashboard
    location.reload();
}

// Animación de números
function animateNumber(element, target, duration = 1000) {
    var start = 0;
    var increment = target / (duration / 16);
    var current = start;
    
    var timer = setInterval(function() {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

// Aplicar animación a los números de estadísticas
document.addEventListener('DOMContentLoaded', function() {
    var statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(function(element) {
        var target = parseInt(element.textContent.replace(/[^0-9]/g, ''));
        if (!isNaN(target)) {
            animateNumber(element, target);
        }
    });
});
