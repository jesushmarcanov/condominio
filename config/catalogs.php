<?php
/**
 * Catálogos Centralizados del Sistema
 * 
 * Define todos los catálogos de valores permitidos en el sistema
 * para mantener consistencia en validaciones, formularios y reportes.
 * 
 * @package Config
 * @version 1.0.0
 */

// Catálogo de Estados de Residentes
define('RESIDENT_STATUSES', [
    'activo' => 'Activo',
    'inactivo' => 'Inactivo'
]);

// Catálogo de Estados de Pagos
define('PAYMENT_STATUSES', [
    'pagado' => 'Pagado',
    'pendiente' => 'Pendiente',
    'atrasado' => 'Atrasado'
]);

// Catálogo de Métodos de Pago
define('PAYMENT_METHODS', [
    'efectivo' => 'Efectivo',
    'transferencia' => 'Transferencia',
    'tarjeta' => 'Tarjeta',
    'deposito' => 'Depósito'
]);

// Catálogo de Estados de Incidencias
define('INCIDENT_STATUSES', [
    'pendiente' => 'Pendiente',
    'en_proceso' => 'En Proceso',
    'resuelta' => 'Resuelta',
    'cancelada' => 'Cancelada'
]);

// Catálogo de Categorías de Incidencias
define('INCIDENT_CATEGORIES', [
    'agua' => 'Agua',
    'electricidad' => 'Electricidad',
    'gas' => 'Gas',
    'estructura' => 'Estructura',
    'limpieza' => 'Limpieza',
    'seguridad' => 'Seguridad',
    'otro' => 'Otro'
]);

// Catálogo de Prioridades de Incidencias
define('INCIDENT_PRIORITIES', [
    'baja' => 'Baja',
    'media' => 'Media',
    'alta' => 'Alta'
]);

// Catálogo de Estados de Áreas Comunes
define('COMMON_AREA_STATUSES', [
    'disponible' => 'Disponible',
    'mantenimiento' => 'En Mantenimiento',
    'no_disponible' => 'No Disponible'
]);

// Catálogo de Estados de Reservas
define('RESERVATION_STATUSES', [
    'confirmada' => 'Confirmada',
    'cancelada' => 'Cancelada',
    'completada' => 'Completada'
]);

// Catálogo de Estados de Cuotas de Mantenimiento
define('MAINTENANCE_FEE_STATUSES', [
    'activa' => 'Activa',
    'vencida' => 'Vencida'
]);

// Catálogo de Tipos de Notificaciones
define('NOTIFICATION_TYPES', [
    'info' => 'Información',
    'warning' => 'Advertencia',
    'success' => 'Éxito',
    'error' => 'Error'
]);

// Catálogo de Roles de Usuario
define('USER_ROLES', [
    'admin' => 'Administrador',
    'resident' => 'Residente'
]);

/**
 * Funciones Helper para Catálogos
 */

/**
 * Obtener las claves de un catálogo
 * 
 * @param array $catalog Catálogo a procesar
 * @return array Array de claves
 */
function getCatalogKeys($catalog) {
    return array_keys($catalog);
}

/**
 * Obtener el label de un valor del catálogo
 * 
 * @param array $catalog Catálogo a consultar
 * @param string $key Clave a buscar
 * @return string Label del valor o la clave si no existe
 */
function getCatalogLabel($catalog, $key) {
    return $catalog[$key] ?? ucfirst($key);
}

/**
 * Validar si un valor existe en un catálogo
 * 
 * @param array $catalog Catálogo a validar
 * @param string $value Valor a verificar
 * @return bool True si existe, false si no
 */
function isValidCatalogValue($catalog, $value) {
    return array_key_exists($value, $catalog);
}

/**
 * Obtener opciones HTML para un select
 * 
 * @param array $catalog Catálogo a convertir
 * @param string $selected Valor seleccionado (opcional)
 * @param bool $includeEmpty Incluir opción vacía (opcional)
 * @return string HTML de opciones
 */
function getCatalogOptions($catalog, $selected = '', $includeEmpty = true) {
    $html = '';
    
    if ($includeEmpty) {
        $html .= '<option value="">Seleccione una opción</option>';
    }
    
    foreach ($catalog as $key => $label) {
        $selectedAttr = ($key === $selected) ? 'selected' : '';
        $html .= "<option value=\"{$key}\" {$selectedAttr}>{$label}</option>";
    }
    
    return $html;
}

/**
 * Obtener clase CSS de badge según el estado
 * 
 * @param string $status Estado a evaluar
 * @param string $type Tipo de catálogo (payment, incident, resident)
 * @return string Clase CSS de Bootstrap
 */
function getStatusBadgeClass($status, $type = 'payment') {
    $classes = [
        'payment' => [
            'pagado' => 'success',
            'pendiente' => 'warning',
            'atrasado' => 'danger'
        ],
        'incident' => [
            'pendiente' => 'warning',
            'en_proceso' => 'info',
            'resuelta' => 'success',
            'cancelada' => 'secondary'
        ],
        'resident' => [
            'activo' => 'success',
            'inactivo' => 'secondary'
        ],
        'notification' => [
            'info' => 'info',
            'warning' => 'warning',
            'success' => 'success',
            'error' => 'danger'
        ]
    ];
    
    return $classes[$type][$status] ?? 'secondary';
}

?>
