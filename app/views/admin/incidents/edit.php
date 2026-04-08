<?php $page_title = 'Editar Incidencia'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-edit"></i> Editar Incidencia</h1>
            <a href="<?= APP_URL ?>/incidents" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Incidencias
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-triangle"></i> Información de la Incidencia</h5>
            </div>
            <div class="card-body">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Información del Residente -->
                <div class="alert alert-info">
                    <strong>Residente:</strong> <?= htmlspecialchars($incident['residente_nombre'] ?? 'N/A') ?><br>
                    <strong>Apartamento:</strong> <?= htmlspecialchars($incident['apartamento'] ?? 'N/A') ?><br>
                    <strong>Fecha de Reporte:</strong> <?= isset($incident['fecha_reporte']) ? date('d/m/Y H:i', strtotime($incident['fecha_reporte'])) : 'N/A' ?>
                </div>
                
                <form method="POST" action="<?= APP_URL ?>/incidents/edit/<?= $incident['id'] ?>">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?= htmlspecialchars($incident['titulo'] ?? '') ?>"
                               placeholder="Breve descripción del problema" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción *</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="5" 
                                  placeholder="Describa detalladamente la incidencia" required><?= htmlspecialchars($incident['descripcion'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría *</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="">Seleccione una categoría</option>
                                    <option value="agua" <?= isset($incident['categoria']) && $incident['categoria'] == 'agua' ? 'selected' : '' ?>>
                                        💧 Agua
                                    </option>
                                    <option value="electricidad" <?= isset($incident['categoria']) && $incident['categoria'] == 'electricidad' ? 'selected' : '' ?>>
                                        ⚡ Electricidad
                                    </option>
                                    <option value="gas" <?= isset($incident['categoria']) && $incident['categoria'] == 'gas' ? 'selected' : '' ?>>
                                        🔥 Gas
                                    </option>
                                    <option value="estructura" <?= isset($incident['categoria']) && $incident['categoria'] == 'estructura' ? 'selected' : '' ?>>
                                        🏢 Estructura
                                    </option>
                                    <option value="limpieza" <?= isset($incident['categoria']) && $incident['categoria'] == 'limpieza' ? 'selected' : '' ?>>
                                        🧹 Limpieza
                                    </option>
                                    <option value="seguridad" <?= isset($incident['categoria']) && $incident['categoria'] == 'seguridad' ? 'selected' : '' ?>>
                                        🔒 Seguridad
                                    </option>
                                    <option value="otro" <?= isset($incident['categoria']) && $incident['categoria'] == 'otro' ? 'selected' : '' ?>>
                                        📦 Otro
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prioridad" class="form-label">Prioridad *</label>
                                <select class="form-select" id="prioridad" name="prioridad" required>
                                    <option value="">Seleccione una prioridad</option>
                                    <option value="baja" <?= isset($incident['prioridad']) && $incident['prioridad'] == 'baja' ? 'selected' : '' ?>>
                                        🟢 Baja
                                    </option>
                                    <option value="media" <?= isset($incident['prioridad']) && $incident['prioridad'] == 'media' ? 'selected' : '' ?>>
                                        🟡 Media
                                    </option>
                                    <option value="alta" <?= isset($incident['prioridad']) && $incident['prioridad'] == 'alta' ? 'selected' : '' ?>>
                                        🔴 Alta
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="pendiente" <?= isset($incident['estado']) && $incident['estado'] == 'pendiente' ? 'selected' : '' ?>>
                                ⏳ Pendiente
                            </option>
                            <option value="en_proceso" <?= isset($incident['estado']) && $incident['estado'] == 'en_proceso' ? 'selected' : '' ?>>
                                🔄 En Proceso
                            </option>
                            <option value="resuelta" <?= isset($incident['estado']) && $incident['estado'] == 'resuelta' ? 'selected' : '' ?>>
                                ✅ Resuelta
                            </option>
                            <option value="cancelada" <?= isset($incident['estado']) && $incident['estado'] == 'cancelada' ? 'selected' : '' ?>>
                                ❌ Cancelada
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notas_admin" class="form-label">Notas del Administrador</label>
                        <textarea class="form-control" id="notas_admin" name="notas_admin" rows="4" 
                                  placeholder="Notas internas sobre la resolución o seguimiento"><?= htmlspecialchars($incident['notas_admin'] ?? '') ?></textarea>
                    </div>
                    
                    <?php if(isset($incident['fecha_resolucion']) && $incident['fecha_resolucion']): ?>
                    <div class="alert alert-success">
                        <strong>Fecha de Resolución:</strong> <?= date('d/m/Y H:i', strtotime($incident['fecha_resolucion'])) ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/incidents" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Guía de Estados -->
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="fas fa-info-circle"></i> Guía de Estados</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>⏳ Pendiente:</strong> Incidencia reportada, esperando atención
                </div>
                <div class="mb-2">
                    <strong>🔄 En Proceso:</strong> Se está trabajando en la resolución
                </div>
                <div class="mb-2">
                    <strong>✅ Resuelta:</strong> Problema solucionado completamente
                </div>
                <div class="mb-0">
                    <strong>❌ Cancelada:</strong> Incidencia cancelada o no válida
                </div>
            </div>
        </div>
        
        <!-- Guía de Categorías -->
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="fas fa-info-circle"></i> Guía de Categorías</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>💧 Agua:</strong> Fugas, falta de agua, problemas con tuberías
                </div>
                <div class="mb-2">
                    <strong>⚡ Electricidad:</strong> Cortes de luz, problemas con enchufes, cortocircuitos
                </div>
                <div class="mb-2">
                    <strong>🔥 Gas:</strong> Fugas de gas, problemas con calentadores, estufas
                </div>
                <div class="mb-2">
                    <strong>🏢 Estructura:</strong> Grietas, filtraciones, problemas en paredes/techos
                </div>
                <div class="mb-2">
                    <strong>🧹 Limpieza:</strong> Problemas en áreas comunes, basura, mantenimiento
                </div>
                <div class="mb-2">
                    <strong>🔒 Seguridad:</strong> Problemas con cerraduras, acceso, vigilancia
                </div>
                <div class="mb-0">
                    <strong>📦 Otro:</strong> Cualquier otro tipo de problema
                </div>
            </div>
        </div>
        
        <!-- Guía de Prioridades -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-exclamation-triangle"></i> Guía de Prioridades</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>🟢 Baja:</strong> Problemas menores que no afectan la funcionalidad
                </div>
                <div class="mb-2">
                    <strong>🟡 Media:</strong> Problemas que afectan parcialmente el funcionamiento
                </div>
                <div class="mb-0">
                    <strong>🔴 Alta:</strong> Emergencias que requieren atención inmediata
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const titulo = document.getElementById('titulo').value.trim();
        const descripcion = document.getElementById('descripcion').value.trim();
        
        if(titulo.length < 5) {
            e.preventDefault();
            alert('El título debe tener al menos 5 caracteres');
            return;
        }
        
        if(descripcion.length < 10) {
            e.preventDefault();
            alert('La descripción debe tener al menos 10 caracteres');
            return;
        }
    });
});
</script>
