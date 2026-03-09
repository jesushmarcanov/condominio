// JavaScript para Reportes Personalizados

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('categoryChart')) {
        initCategoryChart();
    }
    
    if (document.getElementById('trendChart')) {
        initTrendChart();
    }
    
    // Inicializar tooltips y otros elementos
    initializeTooltips();
    initializeFilters();
});

// Gráfico de distribución por categoría
function initCategoryChart() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    const categoryData = getCategoryData();
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(categoryData),
            datasets: [{
                data: Object.values(categoryData),
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
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Gráfico de tendencia temporal
function initTrendChart() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    
    const trendData = getTrendData();
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'Registros',
                data: trendData.data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
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

// Función para obtener datos de categoría
function getCategoryData() {
    const categoryData = {};
    
    if (reportType === 'incidents') {
        // Agrupar incidencias por categoría
        reportData.forEach(item => {
            const category = item.categoria || 'Sin categoría';
            categoryData[category] = (categoryData[category] || 0) + 1;
        });
    } else if (reportType === 'payments' || reportType === 'income') {
        // Agrupar pagos por método
        reportData.forEach(item => {
            const method = item.metodo_pago || 'Sin método';
            categoryData[method] = (categoryData[method] || 0) + 1;
        });
    } else if (reportType === 'residents') {
        // Agrupar residentes por estado
        reportData.forEach(item => {
            const status = item.estado || 'Sin estado';
            categoryData[status] = (categoryData[status] || 0) + 1;
        });
    }
    
    return categoryData;
}

// Función para obtener datos de tendencia
function getTrendData() {
    const trendData = {
        labels: [],
        data: []
    };
    
    // Agrupar datos por mes
    const monthlyData = {};
    
    reportData.forEach(item => {
        let date;
        if (reportType === 'incidents') {
            date = new Date(item.fecha_reporte);
        } else if (reportType === 'payments' || reportType === 'income') {
            date = new Date(item.fecha_pago);
        } else if (reportType === 'residents') {
            date = new Date(item.fecha_ingreso);
        }
        
        if (date && !isNaN(date.getTime())) {
            const monthKey = date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short' });
            monthlyData[monthKey] = (monthlyData[monthKey] || 0) + 1;
        }
    });
    
    // Ordenar por fecha
    const sortedMonths = Object.keys(monthlyData).sort((a, b) => {
        const dateA = new Date(a);
        const dateB = new Date(b);
        return dateA - dateB;
    });
    
    trendData.labels = sortedMonths;
    trendData.data = sortedMonths.map(month => monthlyData[month]);
    
    return trendData;
}

// Inicializar tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Inicializar filtros
function initializeFilters() {
    // Filtro de búsqueda
    const searchInput = document.getElementById('search-filter');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTable(this.value);
        });
    }
    
    // Filtros de columna
    const columnFilters = document.querySelectorAll('.column-filter');
    columnFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            applyColumnFilters();
        });
    });
}

// Filtrar tabla
function filterTable(searchTerm) {
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

// Aplicar filtros de columna
function applyColumnFilters() {
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        let showRow = true;
        const cells = row.querySelectorAll('td');
        
        document.querySelectorAll('.column-filter').forEach((filter, index) => {
            if (filter.value && cells[index]) {
                const cellText = cells[index].textContent.toLowerCase();
                const filterValue = filter.value.toLowerCase();
                if (!cellText.includes(filterValue)) {
                    showRow = false;
                }
            }
        });
        
        row.style.display = showRow ? '' : 'none';
    });
}

// Función para exportar a diferentes formatos
function exportReport(format) {
    switch (format) {
        case 'csv':
            exportToCSV();
            break;
        case 'excel':
            exportToExcel();
            break;
        case 'pdf':
            exportToPDF();
            break;
        default:
            console.error('Formato no soportado:', format);
    }
}

// Exportar a CSV
function exportToCSV() {
    let csv = '';
    
    // Obtener encabezados
    const headers = [];
    document.querySelectorAll('table th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv += headers.join(',') + '\n';
    
    // Obtener filas
    document.querySelectorAll('table tbody tr').forEach(row => {
        const rowData = [];
        row.querySelectorAll('td').forEach(td => {
            let cellText = td.textContent.trim();
            // Escapar comillas y comas
            cellText = cellText.replace(/"/g, '""');
            if (cellText.includes(',') || cellText.includes('"')) {
                cellText = `"${cellText}"`;
            }
            rowData.push(cellText);
        });
        csv += rowData.join(',') + '\n';
    });
    
    // Descargar archivo
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    const filename = `reporte_${reportType}_${new Date().toISOString().split('T')[0]}.csv`;
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Exportar a Excel (simplificado)
function exportToExcel() {
    // Esta es una implementación básica
    // Para una funcionalidad completa, se necesitaría una librería como SheetJS
    alert('La exportación a Excel requiere una librería adicional. Por ahora, puede exportar a CSV y abrirlo en Excel.');
}

// Exportar a PDF (simplificado)
function exportToPDF() {
    // Esta es una implementación básica
    // Para una funcionalidad completa, se necesitaría una librería como jsPDF
    window.print();
}

// Función para imprimir reporte
function printReport() {
    // Preparar para impresión
    const originalTitle = document.title;
    document.title = `Reporte ${reportType} - ${startDate} al ${endDate}`;
    
    window.print();
    
    // Restaurar título original
    document.title = originalTitle;
}

// Función para compartir reporte
function shareReport() {
    const url = window.location.href;
    const title = `Reporte ${reportType} - ${startDate} al ${endDate}`;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // Copiar al portapapeles
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Enlace copiado al portapapeles', 'success');
        });
    }
}

// Mostrar notificación
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-eliminar después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Función para generar resumen ejecutivo
function generateExecutiveSummary() {
    const summary = {
        totalRecords: reportData.length,
        dateRange: `${startDate} al ${endDate}`,
        reportType: reportType,
        keyMetrics: {}
    };
    
    if (reportType === 'income' || reportType === 'payments') {
        summary.keyMetrics = {
            totalAmount: reportData.reduce((sum, item) => sum + (parseFloat(item.monto) || 0), 0),
            averageAmount: reportData.reduce((sum, item) => sum + (parseFloat(item.monto) || 0), 0) / reportData.length,
            paymentMethods: [...new Set(reportData.map(item => item.metodo_pago))].length
        };
    } else if (reportType === 'incidents') {
        summary.keyMetrics = {
            openIncidents: reportData.filter(item => item.estado === 'abierto').length,
            resolvedIncidents: reportData.filter(item => ['resuelto', 'cerrado'].includes(item.estado)).length,
            categories: [...new Set(reportData.map(item => item.categoria))].length
        };
    } else if (reportType === 'residents') {
        summary.keyMetrics = {
            activeResidents: reportData.filter(item => item.estado === 'activo').length,
            inactiveResidents: reportData.filter(item => item.estado === 'inactivo').length,
            apartments: [...new Set(reportData.map(item => item.apartamento))].length
        };
    }
    
    return summary;
}

// Función para actualizar estadísticas en tiempo real
function updateStatistics() {
    const summary = generateExecutiveSummary();
    
    // Actualizar elementos del DOM si existen
    const totalElement = document.getElementById('total-records');
    if (totalElement) {
        totalElement.textContent = summary.totalRecords;
    }
    
    // Actualizar métricas clave
    Object.keys(summary.keyMetrics).forEach(key => {
        const element = document.getElementById(`metric-${key}`);
        if (element) {
            let value = summary.keyMetrics[key];
            if (typeof value === 'number' && key.includes('Amount')) {
                value = '$' + value.toLocaleString();
            }
            element.textContent = value;
        }
    });
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateStatistics();
    
    // Event listeners para botones de acción
    const printBtn = document.querySelector('button[onclick*="print"]');
    if (printBtn) {
        printBtn.addEventListener('click', printReport);
    }
    
    const shareBtn = document.querySelector('button[onclick*="share"]');
    if (shareBtn) {
        shareBtn.addEventListener('click', shareReport);
    }
    
    // Botones de exportación
    document.querySelectorAll('[data-export]').forEach(btn => {
        btn.addEventListener('click', function() {
            const format = this.getAttribute('data-export');
            exportReport(format);
        });
    });
});

// Función para manejar errores de carga de datos
function handleDataError(error) {
    console.error('Error al cargar datos del reporte:', error);
    showNotification('Error al cargar los datos del reporte', 'danger');
}

// Función para mostrar loading
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'report-loading';
    loading.className = 'position-fixed top-50 start-50 translate-middle';
    loading.style.cssText = 'z-index: 9999;';
    loading.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    `;
    
    document.body.appendChild(loading);
}

// Función para ocultar loading
function hideLoading() {
    const loading = document.getElementById('report-loading');
    if (loading) {
        loading.parentNode.removeChild(loading);
    }
}
