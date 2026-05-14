# Rutas API Agregadas al Router

## Resumen de Cambios

Se han agregado las rutas faltantes al router principal (`index.php`) con validación de permisos y métodos HTTP correctos.

## Rutas Agregadas

### 1. `/residents/getActiveResidents`

**Descripción:** Obtiene lista de residentes activos en formato JSON

**Método HTTP:** GET (validado)

**Permisos:** Requiere autenticación (cualquier usuario autenticado)

**Respuesta:**
```json
[
  {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "apartamento": "101",
    "piso": 1,
    "torre": "A"
  },
  ...
]
```

**Uso:**
```javascript
// Desde JavaScript
fetch('/condominio/residents/getActiveResidents')
  .then(response => response.json())
  .then(data => {
    console.log(data);
  });
```

**Validaciones:**
- ✅ Método HTTP: Solo GET permitido
- ✅ Autenticación: Usuario debe estar logueado
- ❌ Método POST/PUT/DELETE: Retorna 405 Method Not Allowed

**Código de Respuesta:**
- `200 OK` - Lista de residentes activos
- `405 Method Not Allowed` - Si se usa método incorrecto
- `401 Unauthorized` - Si no está autenticado

---

### 2. `/incidents/changeStatus/{id}`

**Descripción:** Cambia el estado de una incidencia específica

**Método HTTP:** POST (validado)

**Permisos:** Solo administradores

**Parámetros:**
- `id` (URL): ID de la incidencia
- `estado` (POST): Nuevo estado (pendiente, en_proceso, resuelta, cancelada)
- `notas_admin` (POST): Notas del administrador (opcional)

**Request Body:**
```json
{
  "estado": "en_proceso",
  "notas_admin": "Se está trabajando en la solución"
}
```

**Respuesta Exitosa:**
```json
{
  "success": "Estado actualizado correctamente"
}
```

**Respuesta de Error:**
```json
{
  "error": "Incidencia no encontrada"
}
```

**Uso:**
```javascript
// Desde JavaScript
fetch('/condominio/incidents/changeStatus/5', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
  body: new URLSearchParams({
    'estado': 'en_proceso',
    'notas_admin': 'Trabajando en ello'
  })
})
.then(response => response.json())
.then(data => {
  console.log(data);
});
```

**Validaciones:**
- ✅ Método HTTP: Solo POST permitido
- ✅ Autenticación: Usuario debe estar logueado
- ✅ Autorización: Solo administradores
- ✅ Incidencia existe: Verifica que el ID sea válido
- ✅ Estado válido: Debe ser uno de los estados permitidos
- ❌ Método GET/PUT/DELETE: Retorna 405 Method Not Allowed

**Código de Respuesta:**
- `200 OK` - Estado actualizado correctamente
- `404 Not Found` - Incidencia no encontrada
- `405 Method Not Allowed` - Si se usa método incorrecto
- `401 Unauthorized` - Si no está autenticado
- `403 Forbidden` - Si no es administrador
- `500 Internal Server Error` - Error al actualizar

**Efectos Secundarios:**
- 📧 Crea notificación para el residente
- 📝 Registra evento en `incident_events`
- 🔔 Actualiza badge de notificaciones

---

## Estructura del Router

### Validación de Métodos HTTP

```php
case '/ruta':
    if ($method !== 'POST') {
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Método no permitido. Use POST']);
        break;
    }
    // Procesar solicitud
    break;
```

### Validación de Permisos

Los controladores ya incluyen validación de permisos:

```php
// En ResidentController::getActiveResidents()
$this->requireAuth(); // Requiere autenticación

// En IncidentController::changeStatus()
$this->requireAdmin(); // Requiere ser administrador
```

## Códigos de Estado HTTP Utilizados

| Código | Significado | Uso |
|--------|-------------|-----|
| 200 | OK | Solicitud exitosa |
| 401 | Unauthorized | No autenticado |
| 403 | Forbidden | Sin permisos |
| 404 | Not Found | Recurso no encontrado |
| 405 | Method Not Allowed | Método HTTP incorrecto |
| 500 | Internal Server Error | Error del servidor |

## Pruebas

### Probar `/residents/getActiveResidents`

#### Prueba 1: GET (Correcto)
```bash
curl -X GET http://localhost/condominio/residents/getActiveResidents \
  -H "Cookie: PHPSESSID=tu_session_id"
```

**Resultado Esperado:** Lista de residentes activos en JSON

#### Prueba 2: POST (Incorrecto)
```bash
curl -X POST http://localhost/condominio/residents/getActiveResidents \
  -H "Cookie: PHPSESSID=tu_session_id"
```

**Resultado Esperado:** 
```json
{
  "error": "Método no permitido. Use GET"
}
```
**Código HTTP:** 405

#### Prueba 3: Sin Autenticación
```bash
curl -X GET http://localhost/condominio/residents/getActiveResidents
```

**Resultado Esperado:** Redirección a login o error 401

---

### Probar `/incidents/changeStatus/{id}`

#### Prueba 1: POST (Correcto - Admin)
```bash
curl -X POST http://localhost/condominio/incidents/changeStatus/1 \
  -H "Cookie: PHPSESSID=admin_session_id" \
  -d "estado=en_proceso&notas_admin=Trabajando"
```

**Resultado Esperado:**
```json
{
  "success": "Estado actualizado correctamente"
}
```

#### Prueba 2: GET (Incorrecto)
```bash
curl -X GET http://localhost/condominio/incidents/changeStatus/1 \
  -H "Cookie: PHPSESSID=admin_session_id"
```

**Resultado Esperado:**
```json
{
  "error": "Método no permitido. Use POST"
}
```
**Código HTTP:** 405

#### Prueba 3: Como Residente (Sin Permisos)
```bash
curl -X POST http://localhost/condominio/incidents/changeStatus/1 \
  -H "Cookie: PHPSESSID=resident_session_id" \
  -d "estado=en_proceso"
```

**Resultado Esperado:** Error 403 Forbidden o redirección

#### Prueba 4: ID Inexistente
```bash
curl -X POST http://localhost/condominio/incidents/changeStatus/99999 \
  -H "Cookie: PHPSESSID=admin_session_id" \
  -d "estado=en_proceso"
```

**Resultado Esperado:**
```json
{
  "error": "Incidencia no encontrada"
}
```
**Código HTTP:** 404

---

## Pruebas desde el Navegador

### Usando Console del Navegador

#### Probar getActiveResidents
```javascript
// Abrir console (F12)
fetch('/condominio/residents/getActiveResidents')
  .then(r => r.json())
  .then(data => console.table(data))
  .catch(err => console.error(err));
```

#### Probar changeStatus
```javascript
// Abrir console (F12)
fetch('/condominio/incidents/changeStatus/1', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
  body: 'estado=en_proceso&notas_admin=Prueba desde console'
})
  .then(r => r.json())
  .then(data => console.log(data))
  .catch(err => console.error(err));
```

---

## Integración con Frontend

### Ejemplo: Select de Residentes Activos

```html
<select id="residente_id" name="residente_id">
  <option value="">Seleccionar residente</option>
</select>

<script>
// Cargar residentes activos al cargar la página
document.addEventListener('DOMContentLoaded', function() {
  fetch('/condominio/residents/getActiveResidents')
    .then(response => response.json())
    .then(residents => {
      const select = document.getElementById('residente_id');
      residents.forEach(resident => {
        const option = document.createElement('option');
        option.value = resident.id;
        option.textContent = `${resident.nombre} - ${resident.apartamento}`;
        select.appendChild(option);
      });
    })
    .catch(error => console.error('Error:', error));
});
</script>
```

### Ejemplo: Cambiar Estado de Incidencia

```html
<button onclick="cambiarEstado(5, 'en_proceso')">
  Marcar En Proceso
</button>

<script>
function cambiarEstado(incidentId, nuevoEstado) {
  if (!confirm('¿Cambiar estado de la incidencia?')) return;
  
  fetch(`/condominio/incidents/changeStatus/${incidentId}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `estado=${nuevoEstado}&notas_admin=Cambio desde UI`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Estado actualizado correctamente');
      location.reload();
    } else {
      alert('Error: ' + data.error);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error al actualizar estado');
  });
}
</script>
```

---

## Seguridad

### Validaciones Implementadas

1. **Método HTTP**
   - ✅ Validación en el router
   - ✅ Retorna 405 si el método es incorrecto
   - ✅ Header Content-Type: application/json

2. **Autenticación**
   - ✅ `requireAuth()` en controladores
   - ✅ Verifica sesión activa
   - ✅ Redirección a login si no autenticado

3. **Autorización**
   - ✅ `requireAdmin()` para rutas administrativas
   - ✅ Verifica rol del usuario
   - ✅ Error 403 si no tiene permisos

4. **Validación de Datos**
   - ✅ Sanitización de inputs
   - ✅ Validación de estados permitidos
   - ✅ Verificación de existencia de recursos

5. **Protección CSRF**
   - ⚠️ Considerar agregar tokens CSRF para POST

### Recomendaciones Adicionales

```php
// Agregar validación CSRF (futuro)
case '/incidents/changeStatus/{id}':
    if (!$this->validateCSRFToken()) {
        http_response_code(403);
        echo json_encode(['error' => 'Token CSRF inválido']);
        break;
    }
    // Procesar...
```

---

## Logs y Debugging

### Verificar Logs de Eventos

```sql
-- Ver últimos cambios de estado
SELECT * FROM incident_events 
WHERE event_type = 'status_changed' 
ORDER BY created_at DESC 
LIMIT 10;
```

### Verificar Notificaciones Creadas

```sql
-- Ver notificaciones recientes
SELECT * FROM notificaciones 
WHERE tipo = 'info' 
ORDER BY created_at DESC 
LIMIT 10;
```

### Logs en PHP

```bash
# Ver error_log
tail -f /path/to/error_log | grep IncidentController
```

---

## Resumen de Cambios en index.php

### Antes
```php
case (preg_match('/^\/residents\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
    $controller = new ResidentController();
    $controller->edit($matches[1]);
    break;
    
case '/payments':
    // ...
```

### Después
```php
case (preg_match('/^\/residents\/edit\/(\d+)$/', $request_path, $matches) ? true : false):
    $controller = new ResidentController();
    $controller->edit($matches[1]);
    break;
    
case (preg_match('/^\/residents\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
    $controller = new ResidentController();
    $controller->delete($matches[1]);
    break;
    
case '/residents/getActiveResidents':
    if ($method !== 'GET') {
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Método no permitido. Use GET']);
        break;
    }
    $controller = new ResidentController();
    $controller->getActiveResidents();
    break;
    
case '/payments':
    // ...
```

```php
case (preg_match('/^\/incidents\/delete\/(\d+)$/', $request_path, $matches) ? true : false):
    $controller = new IncidentController();
    $controller->delete($matches[1]);
    break;
    
case (preg_match('/^\/incidents\/changeStatus\/(\d+)$/', $request_path, $matches) ? true : false):
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido. Use POST']);
        break;
    }
    $controller = new IncidentController();
    $controller->changeStatus($matches[1]);
    break;
    
case '/incidents/report':
    // ...
```

---

## Conclusión

Las rutas API han sido agregadas exitosamente con:

- ✅ Validación de métodos HTTP
- ✅ Códigos de estado HTTP correctos
- ✅ Respuestas en formato JSON
- ✅ Validación de permisos en controladores
- ✅ Documentación completa
- ✅ Ejemplos de uso
- ✅ Pruebas sugeridas
- ✅ Integración con frontend

El sistema ahora tiene endpoints API RESTful correctamente configurados y seguros.
