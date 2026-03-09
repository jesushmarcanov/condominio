<?php $page_title = 'Crear Incidencia'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-plus"></i> Crear Nueva Incidencia</h1>
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
                
                <form method="POST" action="<?= APP_URL ?>/incidents/create">
                    <div class="mb-3">
                        <label for="residente_id" class="form-label">Residente *</label>
                        <select class="form-select" id="residente_id" name="residente_id" required>
                            <option value="">Seleccione un residente</option>
                            <?php foreach($residents as $resident): ?>
                                <option value="<?= $resident['id'] ?>" 
                                        <?= isset($data['residente_id']) && $data['residente_id'] == $resident['id'] ? 'selected' : '' ?>>
                                    <?= $resident['nombre'] ?> - Apartamento <?= $resident['apartamento'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?= isset($data['titulo']) ? htmlspecialchars($data['titulo']) : '' ?>"
                               placeholder="Breve descripción del problema" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción *</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="5" 
                                  placeholder="Describa detalladamente la incidencia" required><?= isset($data['descripcion']) ? htmlspecialchars($data['descripcion']) : '' ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría *</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="">Seleccione una categoría</option>
                                    <option value="agua" <?= isset($data['categoria']) && $data['categoria'] == 'agua' ? 'selected' : '' ?>>
                                        💧 Agua
                                    </option>
                                    <option value="electricidad" <?= isset($data['categoria']) && $data['categoria'] == 'electricidad' ? 'selected' : '' ?>>
                                        ⚡ Electricidad
                                    </option>
                                    <option value="gas" <?= isset($data['categoria']) && $data['categoria'] == 'gas' ? 'selected' : '' ?>>
                                        🔥 Gas
                                    </option>
                                    <option value="estructura" <?= isset($data['categoria']) && $data['categoria'] == 'estructura' ? 'selected' : '' ?>>
                                        🏢 Estructura
                                    </option>
                                    <option value="limpieza" <?= isset($data['categoria']) && $data['categoria'] == 'limpieza' ? 'selected' : '' ?>>
                                        🧹 Limpieza
                                    </option>
                                    <option value="seguridad" <?= isset($data['categoria']) && $data['categoria'] == 'seguridad' ? 'selected' : '' ?>>
                                        🔒 Seguridad
                                    </option>
                                    <option value="otro" <?= isset($data['categoria']) && $data['categoria'] == 'otro' ? 'selected' : '' ?>>
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
                                    <option value="baja" <?= isset($data['prioridad']) && $data['prioridad'] == 'baja' ? 'selected' : '' ?>>
                                        🟢 Baja
                                    </option>
                                    <option value="media" <?= isset($data['prioridad']) && $data['prioridad'] == 'media' ? 'selected' : '' ?>>
                                        🟡 Media
                                    </option>
                                    <option value="alta" <?= isset($data['prioridad']) && $data['prioridad'] == 'alta' ? 'selected' : '' ?>>
                                        🔴 Alta
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/incidents" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Incidencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
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
    // Auto-ajustar la categoría según palabras clave en el título
    const tituloInput = document.getElementById('titulo');
    const categoriaSelect = document.getElementById('categoria');
    
    const keywords = {
        'agua': ['agua', 'fuga', 'tuberia', 'grifo', 'ducha', 'bano', 'inodoro'],
        'electricidad': ['luz', 'electricidad', 'enchufe', 'cortocircuito', 'energia', 'bombilla'],
        'gas': ['gas', 'calentador', 'estufa', 'horno', 'fuga de gas'],
        'estructura': ['pared', 'techo', 'piso', 'grieta', 'filtracion', 'humedad'],
        'limpieza': ['basura', 'limpieza', 'mantenimiento', 'area comun', 'jardin'],
        'seguridad': ['cerradura', 'puerta', 'seguridad', 'vigilancia', 'acceso']
    };
    
    tituloInput.addEventListener('input', function() {
        const titulo = this.value.toLowerCase();
        
        for(const [categoria, words] of Object.entries(keywords)) {
            if(words.some(word => titulo.includes(word))) {
                categoriaSelect.value = categoria;
                break;
            }
        }
    });
    
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
