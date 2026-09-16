<?php $page_title = 'Detalle de Reserva'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <a href="<?= APP_URL ?>/reservations" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Volver a Reservas
        </a>
        <h1><i class="fas fa-calendar-check"></i> Reserva #<?= $reservation['id'] ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-calendar"></i> Información de la Reserva</h5>
            </div>
            <div class="card-body">
                <p><strong>Área Común:</strong>
                    <a href="<?= APP_URL ?>/common-areas/show/<?= $reservation['area_comun_id'] ?>"><?= htmlspecialchars($reservation['area_nombre']) ?></a>
                </p>
                <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($reservation['fecha_reserva'])) ?></p>
                <p><strong>Horario:</strong> <?= date('H:i', strtotime($reservation['hora_inicio'])) ?> - <?= date('H:i', strtotime($reservation['hora_fin'])) ?></p>
                <p><strong>Estado:</strong>
                    <?php
                        $r_class = $reservation['estado'] == 'confirmada' ? 'success' : ($reservation['estado'] == 'completada' ? 'info' : 'danger');
                    ?>
                    <span class="badge bg-<?= $r_class ?>">
                        <?= getCatalogLabel(RESERVATION_STATUSES, $reservation['estado']) ?>
                    </span>
                </p>
                <?php if($reservation['area_horario']): ?>
                <p><strong>Horario del área:</strong> <?= htmlspecialchars($reservation['area_horario']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Información del Residente</h5>
            </div>
            <div class="card-body">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($reservation['residente_nombre']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($reservation['residente_email']) ?></p>
                <p><strong>Apartamento:</strong> <?= htmlspecialchars($reservation['apartamento']) ?></p>
            </div>
        </div>
    </div>
</div>

<?php if($reservation['estado'] == 'confirmada'): ?>
<div class="row mt-4">
    <div class="col-12">
        <a href="<?= APP_URL ?>/reservations/cancel/<?= $reservation['id'] ?>"
           class="btn btn-danger"
           onclick="return confirm('¿Deseas cancelar esta reserva?')">
            <i class="fas fa-ban"></i> Cancelar Reserva
        </a>
    </div>
</div>
<?php endif; ?>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
