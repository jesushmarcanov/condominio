<?php $page_title = 'Pagos Pendientes'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-clock"></i> Pagos Pendientes</h1>
            <div>
                <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Pagos
                </a>
                <a href="<?= APP_URL ?>/payments/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Pago
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= count($payments) ?></h4>
                        <p class="card-text">Total Pendientes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= formatCurrency(array_sum(array_column($payments, 'monto'))) ?></h4>
                        <p class="card-text">Monto Total Pendiente</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= count(array_filter($payments, function($p) { return $p['estado'] === 'atrasado'; })) ?></h4>
                        <p class="card-text">Pagos Atrasados</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-times fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Pagos Pendientes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if(!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Residente</th>
                                    <th>Apartamento</th>
                                    <th>Concepto</th>
                                    <th>Mes</th>
                                    <th>Monto</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Estado</th>
                                    <th>Días Atraso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($payments as $payment): ?>
                                <tr>
                                    <td><?= $payment['nombre'] ?></td>
                                    <td><?= $payment['apartamento'] ?></td>
                                    <td><?= $payment['concepto'] ?></td>
                                    <td><?= date('m/Y', strtotime($payment['mes_pago'])) ?></td>
                                    <td><?= formatCurrency($payment['monto']) ?></td>
                                    <td><?= formatDate($payment['fecha_pago']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $payment['estado'] == 'pendiente' ? 'warning' : 'danger' 
                                        ?>">
                                            <?= $payment['estado'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $days_late = 0;
                                        if($payment['estado'] === 'atrasado') {
                                            $due_date = new DateTime($payment['fecha_pago']);
                                            $today = new DateTime();
                                            $days_late = $today->diff($due_date)->days;
                                            if($due_date > $today) $days_late = 0;
                                        }
                                        ?>
                                        <?php if($days_late > 0): ?>
                                            <span class="badge bg-danger"><?= $days_late ?> días</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= APP_URL ?>/payments/show/<?= $payment['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= APP_URL ?>/payments/edit/<?= $payment['id'] ?>" 
                                               class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success btn-mark-paid" 
                                                    onclick="markAsPaid(<?= $payment['id'] ?>)"
                                                    title="Marcar como Pagado">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger btn-delete" 
                                                    onclick="confirmDelete('<?= APP_URL ?>/payments/delete/<?= $payment['id'] ?>')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Acciones Masivas -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-tools"></i> Acciones Masivas</h6>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="sendReminders()">
                                            <i class="fas fa-envelope"></i> Enviar Recordatorios
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="generateReport()">
                                            <i class="fas fa-file-pdf"></i> Generar Reporte
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="exportToExcel()">
                                            <i class="fas fa-file-excel"></i> Exportar a Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>¡No hay pagos pendientes!</h5>
                        <p class="text-muted">Todos los pagos están al día</p>
                        <a href="<?= APP_URL ?>/payments" class="btn btn-primary">
                            Ver todos los pagos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

<script>
function markAsPaid(paymentId) {
    if(confirm('¿Está seguro de marcar este pago como pagado?')) {
        // Crear formulario para actualizar estado
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= APP_URL ?>/payments/edit/' + paymentId;
        
        // Agregar campos del formulario
        const fields = {
            'estado': 'pagado',
            'metodo_pago': 'efectivo',
            '_method': 'PUT'
        };
        
        for(const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

function sendReminders() {
    alert('Función de enviar recordatorios por correo electrónico en desarrollo');
}

function generateReport() {
    window.open('<?= APP_URL ?>/payments/report?export=pdf', '_blank');
}

function exportToExcel() {
    window.open('<?= APP_URL ?>/payments/report?export=csv', '_blank');
}
</script>
