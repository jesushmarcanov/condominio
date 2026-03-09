<?php $page_title = 'Reporte Personalizado'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-cog"></i> Reporte Personalizado</h1>
        <p class="text-muted">Crea reportes con filtros personalizados</p>
    </div>
</div>

<!-- Formulario de Reporte Personalizado -->
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-filter"></i> Configurar Reporte</h5>
            </div>
            <div class="card-body">
                <?php if(isset($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= APP_URL ?>/reports/custom">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="report_type" class="form-label">Tipo de Reporte</label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="income" <?= isset($data['report_type']) && $data['report_type'] === 'income' ? 'selected' : '' ?>>
                                    Reporte de Ingresos
                                </option>
                                <option value="incidents" <?= isset($data['report_type']) && $data['report_type'] === 'incidents' ? 'selected' : '' ?>>
                                    Reporte de Incidencias
                                </option>
                                <option value="residents" <?= isset($data['report_type']) && $data['report_type'] === 'residents' ? 'selected' : '' ?>>
                                    Reporte de Residentes
                                </option>
                                <option value="payments" <?= isset($data['report_type']) && $data['report_type'] === 'payments' ? 'selected' : '' ?>>
                                    Reporte de Pagos
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="export" class="form-label">Formato de Exportación</label>
                            <select class="form-select" id="export" name="export">
                                <option value="">Ver en pantalla</option>
                                <option value="csv" <?= isset($data['export']) && $data['export'] === 'csv' ? 'selected' : '' ?>>
                                    Exportar a CSV
                                </option>
                                <option value="pdf" <?= isset($data['export']) && $data['export'] === 'pdf' ? 'selected' : '' ?>>
                                    Exportar a PDF
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= isset($data['start_date']) ? htmlspecialchars($data['start_date']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= isset($data['end_date']) ? htmlspecialchars($data['end_date']) : '' ?>" required>
                        </div>
                    </div>

                    <!-- Filtros específicos para incidencias -->
                    <div id="incident_filters" class="row mt-3" style="display: none;">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="abierto" <?= isset($data['status']) && $data['status'] === 'abierto' ? 'selected' : '' ?>>Abierto</option>
                                <option value="en_progreso" <?= isset($data['status']) && $data['status'] === 'en_progreso' ? 'selected' : '' ?>>En Progreso</option>
                                <option value="resuelto" <?= isset($data['status']) && $data['status'] === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                <option value="cerrado" <?= isset($data['status']) && $data['status'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label">Categoría</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Todas</option>
                                <option value="plomeria" <?= isset($data['category']) && $data['category'] === 'plomeria' ? 'selected' : '' ?>>Plomería</option>
                                <option value="electrica" <?= isset($data['category']) && $data['category'] === 'electrica' ? 'selected' : '' ?>>Eléctrica</option>
                                <option value="estructura" <?= isset($data['category']) && $data['category'] === 'estructura' ? 'selected' : '' ?>>Estructura</option>
                                <option value="limpieza" <?= isset($data['category']) && $data['category'] === 'limpieza' ? 'selected' : '' ?>>Limpieza</option>
                                <option value="seguridad" <?= isset($data['category']) && $data['category'] === 'seguridad' ? 'selected' : '' ?>>Seguridad</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="priority" class="form-label">Prioridad</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="">Todas</option>
                                <option value="alta" <?= isset($data['priority']) && $data['priority'] === 'alta' ? 'selected' : '' ?>>Alta</option>
                                <option value="media" <?= isset($data['priority']) && $data['priority'] === 'media' ? 'selected' : '' ?>>Media</option>
                                <option value="baja" <?= isset($data['priority']) && $data['priority'] === 'baja' ? 'selected' : '' ?>>Baja</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filtros específicos para residentes -->
                    <div id="resident_filters" class="row mt-3" style="display: none;">
                        <div class="col-md-4">
                            <label for="resident_status" class="form-label">Estado</label>
                            <select class="form-select" id="resident_status" name="resident_status">
                                <option value="">Todos</option>
                                <option value="activo" <?= isset($data['resident_status']) && $data['resident_status'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= isset($data['resident_status']) && $data['resident_status'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="floor" class="form-label">Piso</label>
                            <input type="text" class="form-control" id="floor" name="floor" 
                                   placeholder="Ej: 1, 2, 3..."
                                   value="<?= isset($data['floor']) ? htmlspecialchars($data['floor']) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="tower" class="form-label">Torre</label>
                            <input type="text" class="form-control" id="tower" name="tower" 
                                   placeholder="Ej: A, B, C..."
                                   value="<?= isset($data['tower']) ? htmlspecialchars($data['tower']) : '' ?>">
                        </div>
                    </div>

                    <!-- Filtros específicos para pagos -->
                    <div id="payment_filters" class="row mt-3" style="display: none;">
                        <div class="col-md-4">
                            <label for="payment_status" class="form-label">Estado</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="">Todos</option>
                                <option value="pagado" <?= isset($data['payment_status']) && $data['payment_status'] === 'pagado' ? 'selected' : '' ?>>Pagado</option>
                                <option value="pendiente" <?= isset($data['payment_status']) && $data['payment_status'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="atrasado" <?= isset($data['payment_status']) && $data['payment_status'] === 'atrasado' ? 'selected' : '' ?>>Atrasado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="payment_method" class="form-label">Método de Pago</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="">Todos</option>
                                <option value="efectivo" <?= isset($data['payment_method']) && $data['payment_method'] === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                <option value="transferencia" <?= isset($data['payment_method']) && $data['payment_method'] === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                <option value="tarjeta" <?= isset($data['payment_method']) && $data['payment_method'] === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                                <option value="cheque" <?= isset($data['payment_method']) && $data['payment_method'] === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="min_amount" class="form-label">Monto Mínimo</label>
                            <input type="number" class="form-control" id="min_amount" name="min_amount" 
                                   placeholder="0.00" step="0.01"
                                   value="<?= isset($data['min_amount']) ? htmlspecialchars($data['min_amount']) : '' ?>">
                        </div>
                    </div>

                    <!-- Opciones adicionales -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="group_by" class="form-label">Agrupar por</label>
                            <select class="form-select" id="group_by" name="group_by">
                                <option value="">Sin agrupar</option>
                                <option value="day" <?= isset($data['group_by']) && $data['group_by'] === 'day' ? 'selected' : '' ?>>Día</option>
                                <option value="week" <?= isset($data['group_by']) && $data['group_by'] === 'week' ? 'selected' : '' ?>>Semana</option>
                                <option value="month" <?= isset($data['group_by']) && $data['group_by'] === 'month' ? 'selected' : '' ?>>Mes</option>
                                <option value="quarter" <?= isset($data['group_by']) && $data['group_by'] === 'quarter' ? 'selected' : '' ?>>Trimestre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="sort_by" class="form-label">Ordenar por</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="date" <?= isset($data['sort_by']) && $data['sort_by'] === 'date' ? 'selected' : '' ?>>Fecha</option>
                                <option value="amount" <?= isset($data['sort_by']) && $data['sort_by'] === 'amount' ? 'selected' : '' ?>>Monto</option>
                                <option value="name" <?= isset($data['sort_by']) && $data['sort_by'] === 'name' ? 'selected' : '' ?>>Nombre</option>
                                <option value="status" <?= isset($data['sort_by']) && $data['sort_by'] === 'status' ? 'selected' : '' ?>>Estado</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_charts" name="include_charts" 
                                       <?= isset($data['include_charts']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="include_charts">
                                    Incluir gráficos en el reporte
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_summary" name="include_summary" 
                                       <?= isset($data['include_summary']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="include_summary">
                                    Incluir resumen ejecutivo
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-chart-bar"></i> Generar Reporte
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                                <a href="<?= APP_URL ?>/reports" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Plantillas Predefinidas -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-bookmark"></i> Plantillas Predefinidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-primary">Mensual</h6>
                                <p class="small text-muted">Reporte mensual completo</p>
                                <button class="btn btn-sm btn-outline-primary" onclick="useTemplate('monthly')">
                                    Usar Plantilla
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-success">Trimestral</h6>
                                <p class="small text-muted">Resumen trimestral</p>
                                <button class="btn btn-sm btn-outline-success" onclick="useTemplate('quarterly')">
                                    Usar Plantilla
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <h6 class="text-warning">Anual</h6>
                                <p class="small text-muted">Reporte anual completo</p>
                                <button class="btn btn-sm btn-outline-warning" onclick="useTemplate('annual')">
                                    Usar Plantilla
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h6 class="text-info">Personalizado</h6>
                                <p class="small text-muted">Configuración avanzada</p>
                                <button class="btn btn-sm btn-outline-info" onclick="useTemplate('advanced')">
                                    Usar Plantilla
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.form-check {
    margin-bottom: 0.5rem;
}

.border-primary {
    border-color: #0d6efd !important;
}

.border-success {
    border-color: #198754 !important;
}

.border-warning {
    border-color: #ffc107 !important;
}

.border-info {
    border-color: #0dcaf0 !important;
}
</style>

<script>
// Mostrar/ocultar filtros específicos según el tipo de reporte
document.getElementById('report_type').addEventListener('change', function() {
    const reportType = this.value;
    
    // Ocultar todos los filtros específicos
    document.getElementById('incident_filters').style.display = 'none';
    document.getElementById('resident_filters').style.display = 'none';
    document.getElementById('payment_filters').style.display = 'none';
    
    // Mostrar filtros específicos según el tipo
    switch(reportType) {
        case 'incidents':
            document.getElementById('incident_filters').style.display = 'flex';
            break;
        case 'residents':
            document.getElementById('resident_filters').style.display = 'flex';
            break;
        case 'payments':
        case 'income':
            document.getElementById('payment_filters').style.display = 'flex';
            break;
    }
});

// Función para usar plantillas predefinidas
function useTemplate(type) {
    const today = new Date();
    let startDate, endDate;
    
    switch(type) {
        case 'monthly':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'quarterly':
            const quarter = Math.floor(today.getMonth() / 3);
            startDate = new Date(today.getFullYear(), quarter * 3, 1);
            endDate = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
            break;
        case 'annual':
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today.getFullYear(), 11, 31);
            break;
        case 'advanced':
            startDate = new Date(today.getFullYear() - 1, 0, 1);
            endDate = today;
            break;
    }
    
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    
    if (type === 'advanced') {
        document.getElementById('include_charts').checked = true;
        document.getElementById('include_summary').checked = true;
        document.getElementById('group_by').value = 'month';
    }
}

// Función para resetear el formulario
function resetForm() {
    document.querySelector('form').reset();
    document.getElementById('incident_filters').style.display = 'none';
    document.getElementById('resident_filters').style.display = 'none';
    document.getElementById('payment_filters').style.display = 'none';
}

// Inicializar si hay un tipo seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const reportType = document.getElementById('report_type').value;
    if (reportType) {
        document.getElementById('report_type').dispatchEvent(new Event('change'));
    }
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
