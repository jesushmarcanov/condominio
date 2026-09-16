<?php $page_title = 'Recibo de Pago #' . $payment['id']; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row no-print">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-receipt"></i> Recibo de Pago</h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir Recibo
                </button>
                <a href="<?= APP_URL ?>/payments" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <?php include APP_PATH . '/views/payments/receipt_content.php'; ?>

        <div class="no-print mt-3 text-center text-muted small">
            Consejo: al imprimir, seleccione "Guardar como PDF" o su impresora para conservar o entregar el recibo.
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>