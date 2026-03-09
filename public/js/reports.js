// JavaScript para Reportes

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos según la página
    if(document.getElementById('incomeChart')) {
        initIncomeChart();
    }
    
    if(document.getElementById('statusChart')) {
        initStatusChart();
    }
    
    if(document.getElementById('amountChart')) {
        initAmountChart();
    }
    
    if(document.getElementById('incidentStatusChart')) {
        initIncidentStatusChart();
    }
    
    if(document.getElementById('incidentCategoryChart')) {
        initIncidentCategoryChart();
    }
    
    if(document.getElementById('incidentPriorityChart')) {
        initIncidentPriorityChart();
    }
    
    if(document.getElementById('residentStatusChart')) {
        initResidentStatusChart();
    }
    
    if(document.getElementById('residentFloorChart')) {
        initResidentFloorChart();
    }
    
    if(document.getElementById('residentTowerChart')) {
        initResidentTowerChart();
    }
    
    if(document.getElementById('residentIngressChart')) {
        initResidentIngressChart();
    }
});

// Gráfico de ingresos
function initIncomeChart() {
    const ctx = document.getElementById('incomeChart').getContext('2d');
    
    // Obtener datos de la tabla
    const tableData = getTableData();
    const labels = tableData.map(item => item.date);
    const amounts = tableData.map(item => item.amount);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos',
                data: amounts,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
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

// Gráfico de estado de pagos
function initStatusChart() {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    // Contar estados desde la tabla
    const statusCounts = getStatusCounts();
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusCounts),
            datasets: [{
                data: Object.values(statusCounts),
                backgroundColor: [
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Gráfico de montos por rango
function initAmountChart() {
    const ctx = document.getElementById('amountChart').getContext('2d');
    
    // Agrupar montos por rangos
    const amountRanges = getAmountRanges();
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(amountRanges),
            datasets: [{
                label: 'Cantidad de Pagos',
                data: Object.values(amountRanges),
                backgroundColor: 'rgba(153, 102, 255, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Gráfico de incidencias por estado
function initIncidentStatusChart() {
    const ctx = document.getElementById('incidentStatusChart').getContext('2d');
    
    const statusCounts = getIncidentStatusCounts();
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Object.keys(statusCounts),
            datasets: [{
                data: Object.values(statusCounts),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Gráfico de incidencias por categoría
function initIncidentCategoryChart() {
    const ctx = document.getElementById('incidentCategoryChart').getContext('2d');
    
    const categoryCounts = getIncidentCategoryCounts();
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(categoryCounts),
            datasets: [{
                label: 'Incidencias',
                data: Object.values(categoryCounts),
                backgroundColor: 'rgba(54, 162, 235, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Gráfico de incidencias por prioridad
function initIncidentPriorityChart() {
    const ctx = document.getElementById('incidentPriorityChart').getContext('2d');
    
    const priorityCounts = getIncidentPriorityCounts();
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(priorityCounts),
            datasets: [{
                label: 'Incidencias',
                data: Object.values(priorityCounts),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Gráfico de residentes por estado
function initResidentStatusChart() {
    const ctx = document.getElementById('residentStatusChart').getContext('2d');
    
    const statusCounts = getResidentStatusCounts();
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusCounts),
            datasets: [{
                data: Object.values(statusCounts),
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Gráfico de residentes por piso
function initResidentFloorChart() {
    const ctx = document.getElementById('residentFloorChart').getContext('2d');
    
    const floorCounts = getResidentFloorCounts();
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(floorCounts),
            datasets: [{
                label: 'Residentes',
                data: Object.values(floorCounts),
                backgroundColor: 'rgba(75, 192, 192, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Gráfico de residentes por torre
function initResidentTowerChart() {
    const ctx = document.getElementById('residentTowerChart').getContext('2d');
    
    const towerCounts = getResidentTowerCounts();
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Object.keys(towerCounts),
            datasets: [{
                data: Object.values(towerCounts),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Gráfico de ingresos por mes
function initResidentIngressChart() {
    const ctx = document.getElementById('residentIngressChart').getContext('2d');
    
    const monthlyData = getMonthlyIngressData();
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.labels,
            datasets: [{
                label: 'Nuevos Residentes',
                data: monthlyData.data,
                borderColor: 'rgb(153, 102, 255)',
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Funciones auxiliares para obtener datos de las tablas
function getTableData() {
    const rows = document.querySelectorAll('table tbody tr');
    const data = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const amountText = cells[3]?.textContent || '0';
            const amount = parseFloat(amountText.replace(/[^0-9.-]/g, ''));
            const dateText = cells[6]?.textContent || '';
            
            data.push({
                date: dateText,
                amount: amount || 0
            });
        }
    });
    
    return data;
}

function getStatusCounts() {
    const statusCounts = {};
    const badges = document.querySelectorAll('.badge');
    
    badges.forEach(badge => {
        const status = badge.textContent.trim();
        statusCounts[status] = (statusCounts[status] || 0) + 1;
    });
    
    return statusCounts;
}

function getAmountRanges() {
    const ranges = {
        '0-500': 0,
        '501-1000': 0,
        '1001-1500': 0,
        '1500+': 0
    };
    
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const amountText = cells[3]?.textContent || '0';
        const amount = parseFloat(amountText.replace(/[^0-9.-]/g, ''));
        
        if (amount <= 500) ranges['0-500']++;
        else if (amount <= 1000) ranges['501-1000']++;
        else if (amount <= 1500) ranges['1001-1500']++;
        else ranges['1500+']++;
    });
    
    return ranges;
}

function getIncidentStatusCounts() {
    const statusCounts = {};
    const badges = document.querySelectorAll('.badge');
    
    badges.forEach(badge => {
        const status = badge.textContent.trim();
        if (['Abierto', 'En Progreso', 'Resuelto', 'Cerrado'].includes(status)) {
            statusCounts[status] = (statusCounts[status] || 0) + 1;
        }
    });
    
    return statusCounts;
}

function getIncidentCategoryCounts() {
    const categoryCounts = {};
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const category = cells[2]?.textContent.trim();
        if (category) {
            categoryCounts[category] = (categoryCounts[category] || 0) + 1;
        }
    });
    
    return categoryCounts;
}

function getIncidentPriorityCounts() {
    const priorityCounts = {};
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const priority = cells[3]?.textContent.trim();
        if (priority) {
            priorityCounts[priority] = (priorityCounts[priority] || 0) + 1;
        }
    });
    
    return priorityCounts;
}

function getResidentStatusCounts() {
    const statusCounts = {};
    const badges = document.querySelectorAll('.badge');
    
    badges.forEach(badge => {
        const status = badge.textContent.trim();
        if (['Activo', 'Inactivo'].includes(status)) {
            statusCounts[status] = (statusCounts[status] || 0) + 1;
        }
    });
    
    return statusCounts;
}

function getResidentFloorCounts() {
    const floorCounts = {};
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const floor = cells[5]?.textContent.trim();
        if (floor) {
            floorCounts[floor] = (floorCounts[floor] || 0) + 1;
        }
    });
    
    return floorCounts;
}

function getResidentTowerCounts() {
    const towerCounts = {};
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const tower = cells[6]?.textContent.trim();
        if (tower && tower !== '-') {
            towerCounts[tower] = (towerCounts[tower] || 0) + 1;
        }
    });
    
    return towerCounts;
}

function getMonthlyIngressData() {
    const monthlyData = {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
    };
    
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const dateText = cells[8]?.textContent.trim();
        if (dateText) {
            const date = new Date(dateText);
            const month = date.getMonth();
            monthlyData.data[month]++;
        }
    });
    
    return monthlyData;
}

// Función para exportar a CSV (si no existe en el backend)
function exportToCSV(data, filename) {
    let csv = '';
    
    // Obtener encabezados
    const headers = Object.keys(data[0] || {});
    csv += headers.join(',') + '\n';
    
    // Obtener filas
    data.forEach(row => {
        const values = headers.map(header => {
            const value = row[header] || '';
            return `"${value.toString().replace(/"/g, '""')}"`;
        });
        csv += values.join(',') + '\n';
    });
    
    // Descargar archivo
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
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
function printReport() {
    window.print();
}

// Función para actualizar datos en tiempo real
function refreshData() {
    location.reload();
}

// Función para filtrar tabla
function filterTable(searchTerm) {
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

// Event listeners para filtros
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('input[type="search"], input[placeholder*="Buscar"]');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            filterTable(this.value);
        });
    });
    
    // Botones de exportación
    const exportButtons = document.querySelectorAll('a[href*="export=csv"]');
    exportButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            window.open(url, '_blank');
        });
    });
});
