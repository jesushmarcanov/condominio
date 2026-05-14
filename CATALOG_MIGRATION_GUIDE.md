# Guía de Migración a Catálogos Centralizados

## ✅ Completado

### 1. Archivo de Catálogos Creado
- **Archivo**: `config/catalogs.php`
- **Contenido**: Definiciones centralizadas de todos los catálogos del sistema
- **Cargado en**: `index.php` (línea agregada después de config.php)

### 2. Catálogos Definidos

#### Estados de Residentes
```php
RESIDENT_STATUSES = ['activo', 'inactivo']
```

#### Estados de Pagos
```php
PAYMENT_STATUSES = ['pagado', 'pendiente', 'atrasado']
```

#### Métodos de Pago
```php
PAYMENT_METHODS = ['efectivo', 'transferencia', 'tarjeta', 'deposito']
```

#### Estados de Incidencias
```php
INCIDENT_STATUSES = ['pendiente', 'en_proceso', 'resuelta', 'cancelada']
```

#### Categorías de Incidencias
```php
INCIDENT_CATEGORIES = ['agua', 'electricidad', 'gas', 'estructura', 'limpieza', 'seguridad', 'otro']
```

#### Prioridades de Incidencias
```php
INCIDENT_PRIORITIES = ['baja', 'media', 'alta']
```

### 3. Funciones Helper Creadas

- `getCatalogKeys($catalog)` - Obtener claves para validaciones
- `getCatalogLabel($catalog, $key)` - Obtener etiqueta traducida
- `isValidCatalogValue($catalog, $value)` - Validar valor
- `getCatalogOptions($catalog, $selected, $includeEmpty)` - Generar opciones HTML
- `getStatusBadgeClass($status, $type)` - Obtener clase CSS de badge

### 4. Controladores Actualizados ✅

#### PaymentController.php ✅
- Línea 97-98: Validación en `storePayment()` - ACTUALIZADO
- Línea 233-234: Validación en `updatePayment()` - ACTUALIZADO

#### ResidentController.php ✅
- Línea 65: Validación en `storeResident()` - ACTUALIZADO
- Línea 241: Validación en `updateResident()` - ACTUALIZADO

#### IncidentController.php ✅
- Validaciones en `storeIncident()` - ACTUALIZADO
- Validaciones en `updateIncident()` - ACTUALIZADO

## 📋 Pendiente de Actualización

### Vistas a Actualizar

#### Formularios de Pagos
**Archivos**:
- `app/views/admin/payments/create.php`
- `app/views/admin/payments/edit.php`

**Actualizar selects**:
```php
<!-- Estado -->
<select name="estado" class="form-control" required>
    <?= getCatalogOptions(PAYMENT_STATUSES, $payment['estado'] ?? '') ?>
</select>

<!-- Método de Pago -->
<select name="metodo_pago" class="form-control" required>
    <?= getCatalogOptions(PAYMENT_METHODS, $payment['metodo_pago'] ?? '') ?>
</select>
```

#### Formularios de Residentes
**Archivos**:
- `app/views/admin/residents/create.php`
- `app/views/admin/residents/edit.php`

**Actualizar select**:
```php
<select name="estado" class="form-control" required>
    <?= getCatalogOptions(RESIDENT_STATUSES, $resident['estado'] ?? 'activo') ?>
</select>
```

#### Formularios de Incidencias
**Archivos**:
- `app/views/incidents/create.php`
- `app/views/admin/incidents/create.php`
- `app/views/admin/incidents/edit.php`

**Actualizar selects**:
```php
<!-- Categoría -->
<select name="categoria" class="form-control" required>
    <?= getCatalogOptions(INCIDENT_CATEGORIES, $incident['categoria'] ?? '') ?>
</select>

<!-- Prioridad -->
<select name="prioridad" class="form-control" required>
    <?= getCatalogOptions(INCIDENT_PRIORITIES, $incident['prioridad'] ?? 'media') ?>
</select>

<!-- Estado (solo admin) -->
<select name="estado" class="form-control" required>
    <?= getCatalogOptions(INCIDENT_STATUSES, $incident['estado'] ?? 'pendiente') ?>
</select>
```

#### Badges en Vistas
**Reemplazar badges hardcodeados**:

**Antes**:
```php
<span class="badge bg-<?= $payment['estado'] == 'pagado' ? 'success' : 'warning' ?>">
    <?= ucfirst($payment['estado']) ?>
</span>
```

**Después**:
```php
<span class="badge bg-<?= getStatusBadgeClass($payment['estado'], 'payment') ?>">
    <?= getCatalogLabel(PAYMENT_STATUSES, $payment['estado']) ?>
</span>
```

### Filtros en Vistas

#### Filtros de Pagos
**Archivo**: `app/views/payments/index.php`

```php
<select name="status" class="form-control">
    <?= getCatalogOptions(PAYMENT_STATUSES, $status ?? '', true) ?>
</select>
```

#### Filtros de Incidencias
**Archivo**: `app/views/incidents/index.php`

```php
<!-- Estado -->
<select name="status" class="form-control">
    <?= getCatalogOptions(INCIDENT_STATUSES, $status ?? '', true) ?>
</select>

<!-- Categoría -->
<select name="category" class="form-control">
    <?= getCatalogOptions(INCIDENT_CATEGORIES, $category ?? '', true) ?>
</select>

<!-- Prioridad -->
<select name="priority" class="form-control">
    <?= getCatalogOptions(INCIDENT_PRIORITIES, $priority ?? '', true) ?>
</select>
```

## 🔍 Verificación

### Checklist de Actualización

- [x] Actualizar validaciones en PaymentController
- [x] Actualizar validaciones en ResidentController
- [x] Actualizar validaciones en IncidentController
- [ ] Actualizar formulario de crear pago
- [ ] Actualizar formulario de editar pago
- [ ] Actualizar formulario de crear residente
- [ ] Actualizar formulario de editar residente
- [ ] Actualizar formulario de crear incidencia
- [ ] Actualizar formulario de editar incidencia
- [ ] Actualizar filtros en listado de pagos
- [ ] Actualizar filtros en listado de incidencias
- [ ] Actualizar badges en todas las vistas
- [ ] Probar creación de registros
- [ ] Probar edición de registros
- [ ] Probar filtros
- [ ] Verificar reportes

## 🎯 Beneficios

1. **Consistencia**: Un solo lugar para definir valores permitidos
2. **Mantenibilidad**: Cambios centralizados
3. **Traducción**: Labels en español desde el catálogo
4. **Validación**: Funciones helper para validar valores
5. **UI**: Generación automática de opciones HTML
6. **Estilos**: Clases CSS consistentes para badges

## 📝 Notas

- No se requiere migración de datos en la base de datos
- Los valores actuales en la BD son compatibles con los catálogos
- La base de datos ya usa ENUM con los valores correctos
- Solo se requiere actualizar el código PHP y las vistas

