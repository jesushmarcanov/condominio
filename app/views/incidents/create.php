<?php $page_title = 'Nueva Incidencia'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-plus"></i> Nueva Incidencia</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php if(isset($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= APP_URL ?>/incidents" class="needs-validation" novalidate>
                    <?php if(isAdmin()): ?>
                    <div class="mb-3">
                        <label for="residente_id" class="form-label">Residente</label>
                        <select class="form-select" id="residente_id" name="residente_id" required>
                            <option value="">Seleccionar residente</option>
                            <?php if(isset($residents)): ?>
                                <?php foreach($residents as $resident): ?>
                                    <option value="<?= $resident['id'] ?>" 
                                            <?= isset($data['residente_id']) && $data['residente_id'] == $resident['id'] ? 'selected' : '' ?>>
                                        <?= $resident['nombre'] ?> - <?= $resident['apartamento'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">
                            Debe seleccionar un residente
                        </div>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="residente_id" value="<?= $resident_id ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?= isset($data['titulo']) ? $data['titulo'] : '' ?>" 
                               placeholder="Ej: Fuga de agua en el baño" required>
                        <div class="invalid-feedback">
                            El título es requerido
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                  placeholder="Describa detalladamente el problema..." required><?= isset($data['descripcion']) ? $data['descripcion'] : '' ?></textarea>
                        <div class="invalid-feedback">
                            La descripción es requerida
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="">Seleccionar categoría</option>
                                    <option value="agua" <?= isset($data['categoria']) && $data['categoria'] == 'agua' ? 'selected' : '' ?>>Agua</option>
                                    <option value="electricidad" <?= isset($data['categoria']) && $data['categoria'] == 'electricidad' ? 'selected' : '' ?>>Electricidad</option>
                                    <option value="gas" <?= isset($data['categoria']) && $data['categoria'] == 'gas' ? 'selected' : '' ?>>Gas</option>
                                    <option value="estructura" <?= isset($data['categoria']) && $data['categoria'] == 'estructura' ? 'selected' : '' ?>>Estructura</option>
                                    <option value="limpieza" <?= isset($data['categoria']) && $data['categoria'] == 'limpieza' ? 'selected' : '' ?>>Limpieza</option>
                                    <option value="seguridad" <?= isset($data['categoria']) && $data['categoria'] == 'seguridad' ? 'selected' : '' ?>>Seguridad</option>
                                    <option value="otro" <?= isset($data['categoria']) && $data['categoria'] == 'otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                                <div class="invalid-feedback">
                                    Debe seleccionar una categoría
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prioridad" class="form-label">Prioridad</label>
                                <select class="form-select" id="prioridad" name="prioridad" required>
                                    <option value="">Seleccionar prioridad</option>
                                    <option value="baja" <?= isset($data['prioridad']) && $data['prioridad'] == 'baja' ? 'selected' : '' ?>>Baja</option>
                                    <option value="media" <?= isset($data['prioridad']) && $data['prioridad'] == 'media' ? 'selected' : '' ?>>Media</option>
                                    <option value="alta" <?= isset($data['prioridad']) && $data['prioridad'] == 'alta' ? 'selected' : '' ?>>Alta</option>
                                </select>
                                <div class="invalid-feedback">
                                    Debe seleccionar una prioridad
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/incidents" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Incidencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
