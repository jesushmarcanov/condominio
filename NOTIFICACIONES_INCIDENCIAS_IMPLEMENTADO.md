# Sistema de Notificaciones y Eventos para Incidencias

## Resumen de Implementación

Se ha implementado un sistema completo de notificaciones automáticas y registro de eventos para incidencias, que notifica a residentes y administradores sobre cambios importantes y registra todas las acciones en la base de datos para auditoría.

## Archivos Creados/Modificados

### 1. Base de Datos
**`database/incident_events.sql`**
- Nueva tabla `incident_events` para registro de eventos
- Almacena historial completo de acciones sobre incidencias
- Incluye: tipo de evento, valores anteriores/nuevos, IP, user agent
- Índices optimizados para consultas rápidas

### 2. Modelos
**`app/models/IncidentEvent.php`** (NUEVO)
- Modelo para gestionar eventos de incidencias
- Métodos estáticos para registrar eventos comunes
- Funciones de consulta con filtros
- Estadísticas de eventos

### 3. Controladores
**`app/controllers/IncidentController.php`** (MODIFICADO)
- Agregado sistema de notificaciones automáticas
- Registro de eventos en base de datos
- Métodos helper para crear notificaciones
- Notificación a administradores

### 4. Configuración
**`index.php`** (MODIFICADO)
- Carga del nuevo modelo IncidentEvent

## Funcionalidades Implementadas

### 1. Notificaciones Automáticas

#### Al Crear Incidencia
**Residente:**
- ✅ Recibe notificación de confirmación
- Título: "Nueva Incidencia Registrada"
- Mensaje: Confirmación con título de la incidencia
- Tipo: Info (azul)

**Administradores:**
- ✅ Todos los admins reciben notificación
- Título: "Nueva Incidencia Reportada"
- Mensaje: Título y prioridad de la incidencia
- Tipo: Info (azul)

#### Al Cambiar Estado
**Residente:**
- ✅ Recibe notificación del cambio
- Título: "Actualización de Incidencia"
- Mensaje: Estado anterior → Estado nuevo
- Tipo: Success (verde) si se resuelve, Info (azul) en otros casos

#### Al Asignar Administrador (Preparado)
**Residente:**
- ✅ Recibe notificación de asignación
- Título: "Incidencia Asignada"
- Mensaje: Confirmación de asignación
- Tipo: Info (azul)

### 2. Registro de Eventos en Base de Datos

#### Eventos Registrados
1. **created** - Incidencia creada
2. **status_changed** - Cambio de estado
3. **assigned** - Asignación a administrador
4. **updated** - Actualización de datos
5. **deleted** - Eliminación de incidencia
6. **commented** - Comentario agregado (preparado)

#### Información Capturada
- ID de incidencia
- ID de usuario que realiza la acción
- Tipo de evento
- Valor anterior (si aplica)
- Valor nuevo (si aplica)
- Descripción del evento
- Dirección IP del usuario
- User Agent (navegador)
- Fecha y hora exacta

### 3. Prevención de Duplicados

- ✅ Verifica notificaciones existentes antes de crear
- ✅ Evita spam de notificaciones repetidas
- ✅ Compara por usuario_id, título y estado de lectura

### 4. Logs Detallados

- ✅ Registro en error_log de PHP
- ✅ Información de depuración
- ✅ Errores capturados y registrados
- ✅ Estadísticas de procesamiento

## Estructura de la Tabla incident_events

```sql
CREATE TABLE incident_events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  incident_id INT NOT NULL,
  user_id INT NOT NULL,
  event_type ENUM('created','status_changed','assigned','updated','deleted','commented'),
  old_value VARCHAR(255),
  new_value VARCHAR(255),
  description TEXT,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incidencias(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

## Flujo de Notificaciones

### Escenario 1: Residente Crea Incidencia

```
1. Residente reporta incidencia
   ↓
2. Sistema crea incidencia en BD
   ↓
3. Registro de evento: "created"
   - Usuario: Residente
   - Descripción: "Incidencia creada: [título]"
   - IP y User Agent capturados
   ↓
4. Notificación al residente
   - Confirmación de registro
   - Tipo: Info
   ↓
5. Notificación a todos los admins
   - Nueva incidencia reportada
   - Incluye título y prioridad
   - Tipo: Info
```

### Escenario 2: Admin Cambia Estado

```
1. Admin actualiza estado de incidencia
   ↓
2. Sistema detecta cambio de estado
   ↓
3. Registro de evento: "status_changed"
   - Usuario: Admin
   - Valor anterior: "pendiente"
   - Valor nuevo: "en_proceso"
   - IP y User Agent capturados
   ↓
4. Notificación al residente
   - Actualización de estado
   - Muestra cambio: Pendiente → En Proceso
   - Tipo: Info (o Success si se resuelve)
```

## Métodos Principales

### IncidentController

#### createIncidentNotification()
```php
private function createIncidentNotification(
    $incident_id,
    $residente_id,
    $action,
    $titulo,
    $old_status = null,
    $new_status = null
)
```
- Crea notificación para el residente
- Soporta múltiples tipos de acciones
- Previene duplicados
- Traduce estados al español

#### notifyAdmins()
```php
private function notifyAdmins($titulo, $mensaje, $tipo = 'info')
```
- Notifica a todos los administradores
- Previene duplicados por admin
- Registra cantidad de notificaciones enviadas

#### translateStatus()
```php
private function translateStatus($status)
```
- Traduce estados de inglés a español
- Usado en mensajes de notificación

### IncidentEvent (Métodos Estáticos)

#### logCreated()
```php
public static function logCreated($db, $incident_id, $user_id, $description)
```
- Registra evento de creación
- Captura IP y User Agent automáticamente

#### logStatusChanged()
```php
public static function logStatusChanged(
    $db,
    $incident_id,
    $user_id,
    $old_status,
    $new_status,
    $description
)
```
- Registra cambio de estado
- Guarda valores anterior y nuevo

#### logAssigned()
```php
public static function logAssigned(
    $db,
    $incident_id,
    $user_id,
    $assigned_to,
    $description
)
```
- Registra asignación a administrador
- Guarda ID del admin asignado

## Instalación

### 1. Ejecutar Script SQL
```bash
mysql -u usuario -p nombre_bd < database/incident_events.sql
```

O desde phpMyAdmin:
1. Abrir phpMyAdmin
2. Seleccionar base de datos
3. Ir a pestaña "SQL"
4. Copiar y pegar contenido de `incident_events.sql`
5. Ejecutar

### 2. Verificar Carga de Modelos
El archivo `index.php` ya incluye la carga del modelo:
```php
require_once APP_PATH . '/models/IncidentEvent.php';
```

### 3. Probar Funcionalidad
Ver sección "Pruebas" más abajo

## Tipos de Notificaciones

### Por Color/Tipo

| Tipo | Color | Uso |
|------|-------|-----|
| info | Azul | Información general, nuevas incidencias |
| success | Verde | Incidencia resuelta, acción exitosa |
| warning | Amarillo | Advertencias, pagos vencidos |
| error | Rojo | Errores, problemas críticos |

### Por Acción

| Acción | Título | Destinatario |
|--------|--------|--------------|
| created | "Nueva Incidencia Registrada" | Residente |
| created | "Nueva Incidencia Reportada" | Administradores |
| status_changed | "Actualización de Incidencia" | Residente |
| assigned | "Incidencia Asignada" | Residente |

## Consultas Útiles

### Ver Eventos de una Incidencia
```php
$incidentEvent = new IncidentEvent($db);
$events = $incidentEvent->readByIncident($incident_id);
$events_list = $events->fetchAll(PDO::FETCH_ASSOC);
```

### Ver Eventos de un Usuario
```php
$incidentEvent = new IncidentEvent($db);
$events = $incidentEvent->readByUser($user_id, 10); // Últimos 10
$events_list = $events->fetchAll(PDO::FETCH_ASSOC);
```

### Estadísticas de Eventos
```php
$incidentEvent = new IncidentEvent($db);
$stats = $incidentEvent->getStats();
// Retorna: total_events, created_events, status_changed_events, etc.
```

### Filtrar Eventos
```php
$filters = [
    'incident_id' => 5,
    'event_type' => 'status_changed',
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31',
    'limit' => 50
];
$events = $incidentEvent->readAll($filters);
```

## Pruebas

### 1. Probar Creación de Incidencia

**Como Residente:**
```
1. Iniciar sesión como residente
2. Ir a "Reportar Incidencia"
3. Llenar formulario y enviar
4. Verificar:
   ✓ Mensaje de éxito
   ✓ Notificación en el icono de campana
   ✓ Notificación con título "Nueva Incidencia Registrada"
```

**Como Administrador:**
```
1. Iniciar sesión como admin
2. Verificar notificación en campana
3. Verificar título "Nueva Incidencia Reportada"
4. Verificar que incluye título y prioridad
```

**En Base de Datos:**
```sql
-- Ver evento registrado
SELECT * FROM incident_events 
WHERE event_type = 'created' 
ORDER BY created_at DESC 
LIMIT 1;

-- Verificar datos capturados
-- Debe incluir: incident_id, user_id, description, ip_address, user_agent
```

### 2. Probar Cambio de Estado

**Como Administrador:**
```
1. Ir a "Gestionar Incidencias"
2. Editar una incidencia
3. Cambiar estado (ej: Pendiente → En Proceso)
4. Guardar cambios
```

**Como Residente:**
```
1. Verificar notificación recibida
2. Verificar título "Actualización de Incidencia"
3. Verificar mensaje muestra cambio de estado
4. Verificar tipo correcto (Info o Success)
```

**En Base de Datos:**
```sql
-- Ver evento de cambio de estado
SELECT * FROM incident_events 
WHERE event_type = 'status_changed' 
ORDER BY created_at DESC 
LIMIT 1;

-- Verificar old_value y new_value
-- Deben mostrar estados anterior y nuevo
```

### 3. Probar Prevención de Duplicados

```
1. Crear incidencia
2. Verificar notificación creada
3. Intentar crear notificación duplicada manualmente
4. Verificar que no se crea duplicado
5. Revisar logs en error_log
```

### 4. Verificar Logs

**En servidor (error_log):**
```bash
tail -f /path/to/error_log

# Buscar líneas como:
[IncidentController] Notificación creada para incidencia ID: X
[IncidentController] Notificaciones enviadas a X administradores
[IncidentController] Notificación duplicada omitida
```

## Extensiones Futuras

### 1. Notificaciones por Email
```php
// En createIncidentNotification()
if ($this->shouldSendEmail($usuario_id)) {
    $this->sendEmailNotification($usuario_id, $notif_titulo, $notif_mensaje);
}
```

### 2. Notificaciones Push
```php
// Integración con servicio push
$this->sendPushNotification($usuario_id, $notif_titulo, $notif_mensaje);
```

### 3. Comentarios en Incidencias
```php
// Nuevo método en IncidentController
public function addComment($incident_id) {
    // Agregar comentario
    // Registrar evento 'commented'
    // Notificar a residente y admins
}
```

### 4. Historial Visible en UI
```php
// Nueva vista para mostrar eventos
public function history($incident_id) {
    $events = $incidentEvent->readByIncident($incident_id);
    $this->view('incidents/history', ['events' => $events]);
}
```

### 5. Webhooks
```php
// Disparar webhook en eventos importantes
$this->triggerWebhook('incident.created', $incident_data);
```

## Seguridad

### Datos Capturados
- ✅ IP Address: Para auditoría y seguridad
- ✅ User Agent: Identificar navegador/dispositivo
- ✅ Usuario ID: Quién realizó la acción
- ✅ Timestamp: Cuándo ocurrió

### Protección de Datos
- ✅ Sanitización de inputs con htmlspecialchars
- ✅ Prepared statements (previene SQL injection)
- ✅ Validación de permisos antes de notificar
- ✅ Logs de errores sin exponer datos sensibles

### Privacidad
- ✅ Solo el residente ve sus notificaciones
- ✅ Admins ven notificaciones generales
- ✅ Eventos vinculados a usuarios autorizados

## Mantenimiento

### Limpieza de Eventos Antiguos
```sql
-- Eliminar eventos mayores a 1 año
DELETE FROM incident_events 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Optimización de Índices
```sql
-- Analizar uso de índices
EXPLAIN SELECT * FROM incident_events 
WHERE incident_id = 1 
ORDER BY created_at DESC;
```

### Monitoreo de Tamaño
```sql
-- Ver tamaño de tabla
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES
WHERE table_name = 'incident_events';
```

## Conclusión

El sistema de notificaciones y eventos para incidencias está completamente implementado con:

- ✅ Notificaciones automáticas a residentes
- ✅ Notificaciones automáticas a administradores
- ✅ Registro completo de eventos en BD
- ✅ Prevención de duplicados
- ✅ Captura de IP y User Agent
- ✅ Logs detallados para depuración
- ✅ Traducción de estados al español
- ✅ Tipos de notificación por color
- ✅ Consultas optimizadas con índices
- ✅ Métodos estáticos para fácil uso
- ✅ Preparado para extensiones futuras

El sistema proporciona trazabilidad completa de todas las acciones sobre incidencias y mantiene informados a todos los usuarios relevantes en tiempo real.
