<?php
$pago_ccy = $payment['moneda'] ?? baseCurrency();
$monto_original = $payment['monto_original'] ?? $payment['monto'];
$monto_mora = $payment['monto_mora'] ?? 0;
$monto_total = $monto_original + $monto_mora;
$tiene_mora = $monto_mora > 0;
$residente_nombre = $payment['nombre'] ?? $payment['residente_nombre'] ?? 'N/A';
$residente_email = $payment['email'] ?? $payment['residente_email'] ?? 'N/A';
?>

<style>
.receipt {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    border: 2px solid #212529;
    border-radius: 4px;
    padding: 28px 32px;
    font-size: 14px;
    color: #212529;
}

.receipt-header {
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 3px double #212529;
    padding-bottom: 16px;
}

.receipt-brand {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #007bff;
    color: #fff;
    font-size: 1.6rem;
    border-radius: 50%;
}

.receipt-logo {
    margin-left: auto;
    text-align: right;
}

.receipt-logo img {
    max-width: 110px;
    max-height: 60px;
}

.receipt-title {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: 2px;
    margin: 18px 0 14px;
    color: #007bff;
}

.receipt-meta {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
    font-size: 0.85rem;
    border: 1px dashed #adb5bd;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 18px;
}

.receipt-section-title {
    font-size: 0.95rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 4px;
    margin: 16px 0 10px;
}

.receipt-body p {
    margin-bottom: 6px;
}

.receipt-total-table {
    margin-bottom: 10px;
    max-width: 380px;
}

.receipt-total-table td {
    border-top: none;
    border-bottom: 1px dotted #dee2e6;
    padding: 6px 8px;
    font-size: 0.95rem;
}

.receipt-total-table tr:last-child td {
    border-bottom: none;
}

.receipt-amount {
    text-align: center;
    border: 2px solid #212529;
    border-radius: 8px;
    padding: 16px 12px;
    margin: 16px 0;
    background: #f8f9fa;
}

.receipt-amount small {
    letter-spacing: 2px;
    color: #6c757d;
    font-weight: 600;
}

.receipt-amount-main {
    font-size: 1.7rem;
    font-weight: 700;
    color: #212529;
}

.receipt-amount-sub {
    font-size: 0.8rem;
    color: #6c757d;
}

.receipt-amount-equiv {
    font-size: 0.9rem;
    color: #007bff;
    font-weight: 600;
    margin-top: 4px;
}

.receipt-rate {
    font-size: 0.8rem;
    color: #6c757d;
    background: #e9ecef;
    border-radius: 4px;
    padding: 6px 10px;
}

.receipt-sign {
    margin-top: 34px;
    max-width: 320px;
}

.receipt-sign-line {
    border-bottom: 1px solid #212529;
    height: 26px;
}

.receipt-sign-label {
    font-size: 0.75rem;
    text-align: center;
    color: #495057;
}

.receipt-footer {
    margin-top: 24px;
    border-top: 1px solid #dee2e6;
    padding-top: 12px;
    text-align: center;
    font-size: 0.75rem;
    color: #6c757d;
}

@media print {
    @page { margin: 12mm; }
    body { background: #fff !important; }
    #sidebar, .top-navbar, .no-print, footer, .notification-icon { display: none !important; }
    #content, .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .card { border: none !important; box-shadow: none !important; }
    .receipt { border: 2px solid #000 !important; box-shadow: none !important; max-width: 100% !important; padding: 16px; }
    .receipt-brand, .receipt-title { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="receipt">
    <div class="receipt-header">
        <div class="receipt-brand">
            <i class="fas fa-building"></i>
        </div>
        <div>
            <h2 class="mb-0"><?= APP_NAME ?></h2>
            <p class="text-muted mb-0">Sistema de Gestión de Condominio</p>
        </div>
        <?php if (!empty($empresa['logo'] ?? '')): ?>
            <div class="receipt-logo">
                <img src="<?= htmlspecialchars(APP_URL . '/' . $empresa['logo']) ?>" alt="Logo del condominio">
            </div>
        <?php endif; ?>
    </div>

    <div class="receipt-title">
        COMPROBANTE DE PAGO
    </div>

    <div class="receipt-meta">
        <span><strong>No. Recibo:</strong> <?= str_pad($payment['id'], 8, '0', STR_PAD_LEFT) ?></span>
        <span><strong>Fecha de emisión:</strong> <?= date('d/m/Y H:i:s') ?></span>
    </div>

    <div class="receipt-body">
        <h5 class="receipt-section-title">Información del Residente</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($residente_nombre) ?></p>
            </div>
            <div class="col-md-3">
                <p><strong>Apartamento:</strong> <?= htmlspecialchars($payment['apartamento'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-3">
                <p><strong>Email:</strong> <?= htmlspecialchars($residente_email) ?></p>
            </div>
        </div>

        <h5 class="receipt-section-title">Detalles del Pago</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Concepto:</strong> <?= htmlspecialchars($payment['concepto']) ?></p>
                <p><strong>Mes de Pago:</strong> <?= date('m/Y', strtotime($payment['mes_pago'])) ?></p>
                <p><strong>Fecha de Pago:</strong> <?= formatDate($payment['fecha_pago']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Método de Pago:</strong> <?= ucfirst($payment['metodo_pago']) ?></p>
                <p><strong>Referencia:</strong> <?= $payment['referencia'] ? htmlspecialchars($payment['referencia']) : 'N/A' ?></p>
                <p><strong>Estado:</strong> <span class="badge bg-<?=
                    $payment['estado'] == 'pagado' ? 'success' :
                    ($payment['estado'] == 'pendiente' ? 'warning' : 'danger')
                ?>"><?= ucfirst($payment['estado']) ?></span></p>
            </div>
        </div>

        <?php if($tiene_mora): ?>
        <h5 class="receipt-section-title">Desglose del Monto</h5>
        <table class="receipt-total-table table table-sm">
            <tr>
                <td>Monto Original</td>
                <td class="text-end"><?= formatAmountIn($monto_original, $pago_ccy) ?></td>
            </tr>
            <tr>
                <td>Recargo por Mora</td>
                <td class="text-end text-danger">+ <?= formatAmountIn($monto_mora, $pago_ccy) ?></td>
            </tr>
        </table>
        <?php endif; ?>

        <div class="receipt-amount">
            <div><small>MONTO TOTAL</small></div>
            <div class="receipt-amount-main"><?= formatAmountIn($monto_total, $pago_ccy) ?></div>
            <?php if($tiene_mora): ?>
            <div class="receipt-amount-sub">(Más recargo por mora incluido)</div>
            <?php endif; ?>
            <?php if(dualCurrencyEnabled() && bcvRate()): ?>
            <div class="receipt-amount-equiv">
                Equivalente: <?= formatCurrencyDualText(convertCurrency($monto_total, $pago_ccy, baseCurrency())) ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if(bcvRate()): ?>
        <p class="receipt-rate">
            <i class="fas fa-info-circle"></i>
            Tasa de cambio: 1 USD = Bs <?= number_format(bcvRate(), 2) ?> (vigente <?= date('d/m/Y', strtotime(bcvRateDate())) ?>)
        </p>
        <?php endif; ?>

        <div class="receipt-sign">
            <div class="receipt-sign-line"></div>
            <div class="receipt-sign-label">Recibido por (Firma y sello de la administración)</div>
        </div>
    </div>

    <div class="receipt-footer">
        <p class="mb-0">Este recibo fue generado automáticamente por <?= APP_NAME ?>.</p>
        <p class="mb-0">Para cualquier consulta, por favor contacte a la administración del condominio.</p>
    </div>
</div>