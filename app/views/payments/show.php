<?php $page_title = 'Detalles del Pago'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-eye"></i> Detalles del Pago</h1>
            <div>
                <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <?php if($is_admin): ?>
                <a href="<?= APP_URL ?>/payments/edit/<?= $payment['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-dollar-sign"></i> Información del Pago</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID de Pago:</strong> #<?= $payment['id'] ?></p>
                        <p><strong>Residente:</strong> <?= $payment['residente_nombre'] ?></p>
                        <p><strong>Apartamento:</strong> <?= $payment['apartamento'] ?></p>
                        <p><strong>Email:</strong> <?= $payment['residente_email'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Concepto:</strong> <?= $payment['concepto'] ?></p>
                        <p><strong>Monto:</strong> 
                            <span class="badge bg-success fs-6"><?= formatCurrency($payment['monto']) ?></span>
                        </p>
                        <p><strong>Mes de Pago:</strong> <?= date('F Y', strtotime($payment['mes_pago'])) ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?= 
                                $payment['estado'] == 'pagado' ? 'success' : 
                                ($payment['estado'] == 'pendiente' ? 'warning' : 'danger') 
                            ?>">
                                <?= ucfirst($payment['estado']) ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha de Pago:</strong> <?= formatDate($payment['fecha_pago']) ?></p>
                        <p><strong>Método de Pago:</strong> 
                            <span class="badge bg-info"><?= ucfirst($payment['metodo_pago']) ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Referencia:</strong> <?= $payment['referencia'] ?: 'N/A' ?></p>
                        <p><strong>Fecha de Registro:</strong> <?= formatDate($payment['created_at']) ?></p>
                    </div>
                </div>
                
                <?php if($payment['updated_at'] != $payment['created_at']): ?>
                <hr>
                <p><strong>Última Actualización:</strong> <?= formatDate($payment['updated_at']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Desglose de Mora -->
        <?php if (isset($payment['monto_mora']) && $payment['monto_mora'] > 0): ?>
        <div class="card mt-3">
            <div class="card-header bg-warning text-dark">
                <h5><i class="fas fa-percentage"></i> Desglose de Mora</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Este pago tiene recargos por mora aplicados</strong>
                </div>

                <table class="table table-bordered">
                    <tr>
                        <td><strong>Monto Original:</strong></td>
                        <td class="text-end">$<?= number_format($payment['monto_original'] ?? $payment['monto'], 2) ?></td>
                    </tr>
                    <tr class="table-warning">
                        <td><strong>Recargo por Mora:</strong></td>
                        <td class="text-end text-danger">+$<?= number_format($payment['monto_mora'], 2) ?></td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>Monto Total a Pagar:</strong></td>
                        <td class="text-end"><strong>$<?= number_format(($payment['monto_original'] ?? $payment['monto']) + $payment['monto_mora'], 2) ?></strong></td>
                    </tr>
                </table>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <p><strong>Fecha de Vencimiento:</strong> <?= formatDate($payment['fecha_pago']) ?></p>
                        <?php
                        $fecha_vencimiento = new DateTime($payment['fecha_pago']);
                        $fecha_actual = new DateTime();
                        $dias_atraso = $fecha_actual->diff($fecha_vencimiento)->days;
                        if ($fecha_actual > $fecha_vencimiento) {
                            echo "<p><strong>Días de Atraso:</strong> <span class='text-danger'>{$dias_atraso} días</span></p>";
                        }
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php if (isset($payment['fecha_aplicacion_mora'])): ?>
                            <p><strong>Fecha de Aplicación de Mora:</strong> <?= formatDate($payment['fecha_aplicacion_mora']) ?></p>
                        <?php endif; ?>
                        <?php if (isset($payment['regla_mora_nombre'])): ?>
                            <p><strong>Regla Aplicada:</strong> <span class="badge bg-info"><?= htmlspecialchars($payment['regla_mora_nombre']) ?></span></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-info-circle"></i> Explicación del Recargo</h6>
                    <p class="mb-0">
                        El recargo por mora se aplicó debido a que el pago excedió la fecha de vencimiento. 
                        Este monto se calcula automáticamente según las reglas de mora configuradas por la administración.
                        <?php if ($payment['estado'] != 'pagado'): ?>
                        <br><strong>Importante:</strong> El monto de mora puede incrementar si el pago continúa atrasado.
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Historial de Mora -->
                <?php if (isset($late_fee_history) && !empty($late_fee_history)): ?>
                <hr>
                <h6><i class="fas fa-history"></i> Historial de Mora</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Calculado</th>
                                <th>Aplicado</th>
                                <th>Días</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($late_fee_history as $history): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($history['created_at'])) ?></td>
                                    <td>
                                        <?php
                                        $tipo_badges = [
                                            'calculo_automatico' => '<span class="badge bg-info">Automático</span>',
                                            'ajuste_manual' => '<span class="badge bg-warning">Ajuste</span>',
                                            'eliminacion' => '<span class="badge bg-danger">Eliminación</span>'
                                        ];
                                        echo $tipo_badges[$history['tipo_operacion']] ?? $history['tipo_operacion'];
                                        ?>
                                    </td>
                                    <td>$<?= number_format($history['monto_calculado'], 2) ?></td>
                                    <td>$<?= number_format($history['monto_aplicado'], 2) ?></td>
                                    <td><?= $history['dias_atraso'] ?></td>
                                </tr>
                                <?php if ($history['justificacion']): ?>
                                    <tr>
                                        <td colspan="5" class="text-muted small">
                                            <em><?= htmlspecialchars($history['justificacion']) ?></em>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <!-- Estado del Pago -->
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Estado del Pago</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <?php if($payment['estado'] == 'pagado'): ?>
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                        <h5 class="mt-3 text-success">Pagado</h5>
                        <p class="text-muted">El pago ha sido completado exitosamente</p>
                    <?php elseif($payment['estado'] == 'pendiente'): ?>
                        <i class="fas fa-clock fa-4x text-warning"></i>
                        <h5 class="mt-3 text-warning">Pendiente</h5>
                        <p class="text-muted">El pago está pendiente de confirmación</p>
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
                        <h5 class="mt-3 text-danger">Atrasado</h5>
                        <p class="text-muted">El pago está atrasado</p>
                    <?php endif; ?>
                </div>
                
                <?php if($is_admin && $payment['estado'] != 'pagado'): ?>
                <button type="button" class="btn btn-success" onclick="markAsPaid(<?= $payment['id'] ?>)">
                    <i class="fas fa-check"></i> Marcar como Pagado
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-cog"></i> Acciones</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= APP_URL ?>/payments" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Ver Todos los Pagos
                    </a>
                    
                    <?php if($is_admin): ?>
                    <a href="<?= APP_URL ?>/payments/edit/<?= $payment['id'] ?>" class="btn btn-outline-warning">
                        <i class="fas fa-edit"></i> Editar Pago
                    </a>
                    
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Eliminar Pago
                    </button>
                    
                    <a href="<?= APP_URL ?>/pdf/payment-receipt/<?= $payment['id'] ?>" class="btn btn-outline-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= APP_URL ?>/residents/show/<?= $payment['residente_id'] ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-user"></i> Ver Residente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Cambios -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Historial de Cambios</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6>Pago Registrado</h6>
                            <p class="text-muted">Se registró el pago por <?= formatCurrency($payment['monto']) ?></p>
                            <small class="text-muted"><?= formatDate($payment['created_at']) ?></small>
                        </div>
                    </div>
                    
                    <?php if($payment['updated_at'] != $payment['created_at']): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6>Pago Actualizado</h6>
                            <p class="text-muted">Se modificó la información del pago</p>
                            <small class="text-muted"><?= formatDate($payment['updated_at']) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-content h6 {
    margin-bottom: 5px;
    color: #495057;
}

.timeline-content p {
    margin-bottom: 5px;
}
</style>

<script>
function markAsPaid(paymentId) {
    if(confirm('¿Está seguro de marcar este pago como pagado?')) {
        // Aquí iría la lógica para marcar como pagado
        showToast('Pago marcado como pagado correctamente', 'success');
        setTimeout(() => location.reload(), 1500);
    }
}

function confirmDelete() {
    if(confirm('¿Está seguro de que desea eliminar este pago? Esta acción no se puede deshacer.')) {
        window.location.href = '<?= APP_URL ?>/payments/delete/<?= $payment['id'] ?>';
    }
}
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
