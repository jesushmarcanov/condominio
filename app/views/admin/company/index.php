<?php
/**
 * Vista: Datos del Condominio (Empresa)
 * Acceso: admin (requireAdmin en CompanyController).
 *
 * Formulario de fila única (id=1) con los datos del condominio como empresa:
 * nombre, RIF, dirección, teléfonos, email de contacto, representante legal,
 * horario de administración, sitio web y logo.
 *
 * El logo es multipart (enctype="multipart/form-data"); el CSRF viaja en el
 * body POST y por tanto sobrevive a la subida de archivos.
 */

$page_title = 'Datos del Condominio';
$empresa = $empresa ?? [];
$errors  = $errors ?? [];
?>

<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid py-4">

    <?php if(!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-building"></i> Datos del Condominio</h1>
            <p class="text-muted mb-0">Identidad de la empresa administradora / titular del condominio.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 col-xl-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-image"></i> Logo del Condominio</h5>
                </div>
                <div class="card-body text-center">
                    <?php if(!empty($empresa['logo'])): ?>
                        <img src="<?= APP_URL ?>/<?= htmlspecialchars($empresa['logo']) ?>"
                             alt="Logo del Condominio"
                             class="img-fluid rounded mb-3 border"
                             style="max-height:140px;" id="logoPreview">
                    <?php else: ?>
                        <div class="text-muted mb-3" id="logoPlaceholder">
                            <i class="fas fa-building fa-5x"></i>
                            <p class="mt-2 mb-0">Aún no has subido un logo.</p>
                        </div>
                        <img id="logoPreview" class="img-fluid rounded mb-3 border d-none"
                             style="max-height:140px;" alt="Vista previa del logo">
                    <?php endif; ?>
                    <p class="text-muted small">JPG, PNG o WebP · máx. 2&nbsp;MB</p>
                </div>
            </div>
        </div>

        <div class="col-lg-7 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-pen"></i> Datos</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>/empresa"
                          enctype="multipart/form-data" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <input type="hidden" name="accion" value="update">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Condominio *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required
                                   maxlength="150"
                                   value="<?= htmlspecialchars($empresa['nombre'] ?? '') ?>">
                            <div class="invalid-feedback">Indica el nombre del condominio.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rif" class="form-label">RIF</label>
                                    <input type="text" class="form-control" id="rif" name="rif"
                                           maxlength="20" placeholder="J-00000000-0"
                                           value="<?= htmlspecialchars($empresa['rif'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefono1" class="form-label">Teléfono principal</label>
                                    <input type="text" class="form-control" id="telefono1" name="telefono1"
                                           maxlength="30" placeholder="0212-0000000"
                                           value="<?= htmlspecialchars($empresa['telefono1'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefono2" class="form-label">Teléfono secundario</label>
                                    <input type="text" class="form-control" id="telefono2" name="telefono2"
                                           maxlength="30" placeholder="0414-0000000"
                                           value="<?= htmlspecialchars($empresa['telefono2'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_contacto" class="form-label">Email de contacto</label>
                                    <input type="email" class="form-control" id="email_contacto" name="email_contacto"
                                           maxlength="120" placeholder="admin@condominio.com"
                                           value="<?= htmlspecialchars($empresa['email_contacto'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2"
                                      maxlength="255"><?= htmlspecialchars($empresa['direccion'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="representante_legal" class="form-label">Representante legal</label>
                                    <input type="text" class="form-control" id="representante_legal" name="representante_legal"
                                           maxlength="150"
                                           value="<?= htmlspecialchars($empresa['representante_legal'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="horario_admin" class="form-label">Horario de administración</label>
                                    <input type="text" class="form-control" id="horario_admin" name="horario_admin"
                                           maxlength="150" placeholder="Lun-Vie 9:00-16:00"
                                           value="<?= htmlspecialchars($empresa['horario_admin'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sitio_web" class="form-label">Sitio web</label>
                            <input type="url" class="form-control" id="sitio_web" name="sitio_web"
                                   maxlength="150" placeholder="https://www.condominio.com"
                                   value="<?= htmlspecialchars($empresa['sitio_web'] ?? '') ?>">
                            <div class="invalid-feedback">Introduce una URL válida (http/https).</div>
                        </div>

                        <div class="mb-4">
                            <label for="logo" class="form-label">Logo (nuevo)</label>
                            <input type="file" class="form-control" id="logo" name="logo"
                                   accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Déjalo vacío para conservar el logo actual.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('logo');
    const preview = document.getElementById('logoPreview');
    const placeholder = document.getElementById('logoPlaceholder');

    if (input && preview) {
        input.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                if (placeholder) placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
