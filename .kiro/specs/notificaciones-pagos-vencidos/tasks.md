# Plan de Implementación: Notificaciones de Pagos Vencidos

## Descripción General

Este plan implementa el módulo de notificaciones de pagos vencidos que detecta automáticamente pagos atrasados, genera notificaciones persistentes en la base de datos y proporciona interfaces para residentes y administradores. La implementación se integra con el sistema MVC existente en PHP.

## Tareas

- [x] 1. Crear modelo Notification para gestión de notificaciones
  - Crear archivo `app/models/Notification.php`
  - Implementar constructor y propiedades del modelo
  - Implementar método `create()` para crear notificaciones
  - Implementar método `readByUser()` para leer notificaciones por usuario
  - Implementar método `readUnreadByUser()` para notificaciones no leídas
  - Implementar método `countUnreadByUser()` para contar no leídas
  - Implementar método `markAsRead()` para marcar como leída
  - Implementar método `findDuplicate()` para buscar duplicados
  - Implementar método `readAll()` con filtros para administradores
  - Implementar método `getStats()` para estadísticas
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4, 7.1, 7.2, 7.3, 7.4_

- [ ]* 1.1 Escribir tests unitarios para modelo Notification
  - Test para crear notificación exitosamente
  - Test para leer notificaciones por usuario
  - Test para contar notificaciones no leídas
  - Test para marcar como leída
  - Test para encontrar duplicados
  - Test para no encontrar duplicado cuando está leída
  - _Requirements: 2.1, 2.5, 2.6, 4.3, 7.1, 7.2, 7.3_

- [x] 2. Crear servicio NotificationService para lógica de negocio
  - Crear archivo `app/services/NotificationService.php`
  - Implementar constructor con dependencias (Payment, Resident, Notification)
  - Implementar método `processOverduePayments()` como punto de entrada principal
  - Implementar método privado `detectOverduePayments()` para detectar pagos vencidos
  - Implementar método privado `generateNotification()` para crear notificaciones
  - Implementar método privado `checkDuplicate()` para verificar duplicados
  - Implementar método privado `updatePaymentToOverdue()` para actualizar estado
  - Implementar método privado `buildNotificationTitle()` para construir título
  - Implementar método privado `buildNotificationMessage()` para construir mensaje
  - Agregar manejo de errores con try-catch y logging
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 3.1, 3.2, 3.3, 6.3, 6.4, 7.1, 7.2, 7.3, 7.4, 8.1, 8.2_

- [ ]* 2.1 Escribir tests unitarios para NotificationService
  - Test para detectar pagos vencidos con estado pendiente
  - Test para detectar pagos con estado atrasado
  - Test para no generar notificación duplicada
  - Test para generar notificación cuando no existe duplicado
  - Test para actualizar estado de pago pendiente a atrasado
  - Test para construir título de notificación correctamente
  - Test para construir mensaje con todos los datos
  - Test para manejar error cuando residente no existe
  - Test para procesar múltiples pagos vencidos
  - _Requirements: 1.1, 1.2, 2.6, 3.1, 7.1, 7.2, 7.3_

- [x] 3. Checkpoint - Verificar modelos y servicios
  - Asegurar que todos los tests pasen
  - Verificar que no hay errores de sintaxis
  - Preguntar al usuario si hay dudas o ajustes necesarios

- [x] 4. Crear controlador NotificationController para interfaces de usuario
  - Crear archivo `app/controllers/NotificationController.php`
  - Implementar constructor extendiendo Controller base
  - Implementar método `index()` para listar notificaciones del usuario actual
  - Implementar método `markAsRead()` para marcar notificación como leída
  - Implementar método `getUnreadCount()` para obtener contador (AJAX)
  - Implementar método `admin()` para vista de administración
  - Implementar método `stats()` para estadísticas de administrador
  - Agregar validación de permisos con `requireAuth()` y `requireAdmin()`
  - Agregar validación de propiedad de notificaciones
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4_

- [ ]* 4.1 Escribir tests unitarios para NotificationController
  - Test para residente ve solo sus notificaciones
  - Test para admin ve todas las notificaciones
  - Test para marcar notificación como leída
  - Test para no permitir marcar notificación de otro usuario
  - Test para obtener contador de no leídas (AJAX)
  - _Requirements: 4.1, 4.2, 4.3, 5.1_

- [x] 5. Crear vistas para residentes
  - Crear archivo `app/views/notifications/index.php` para lista de notificaciones
  - Mostrar notificaciones ordenadas por fecha descendente
  - Agregar indicador visual para notificaciones no leídas
  - Implementar funcionalidad para marcar como leída al hacer clic
  - Agregar contador de notificaciones no leídas en el layout
  - Integrar con el dashboard del residente
  - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [x] 6. Crear vistas para administradores
  - Crear archivo `app/views/admin/notifications/index.php` para lista global
  - Implementar filtros por residente, tipo y estado de lectura
  - Mostrar estadísticas: total generadas, leídas, no leídas
  - Implementar vista de historial por residente
  - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [x] 7. Crear script ejecutable para detección automática
  - Crear archivo `check_overdue_payments.php` en la raíz del proyecto
  - Configurar para ejecución desde línea de comandos
  - Inicializar conexión a base de datos
  - Instanciar NotificationService
  - Ejecutar `processOverduePayments()`
  - Registrar inicio, resultado y fin en logs
  - Implementar manejo de errores sin interrumpir procesamiento
  - Retornar código de salida apropiado (0 = éxito, 1 = error)
  - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [ ]* 7.1 Escribir test para script de cron
  - Test para script ejecuta sin errores
  - Test para script registra logs correctamente
  - Test para script maneja errores de BD sin fallar
  - _Requirements: 6.1, 6.2, 6.4_

- [x] 8. Checkpoint - Verificar funcionalidad completa
  - Asegurar que todos los tests pasen
  - Verificar que las vistas se renderizan correctamente
  - Probar el script de cron manualmente
  - Preguntar al usuario si hay dudas o ajustes necesarios

- [x] 9. Agregar rutas al sistema de enrutamiento
  - Agregar ruta GET `/notifications` para NotificationController::index()
  - Agregar ruta POST `/notifications/mark-read/{id}` para markAsRead()
  - Agregar ruta GET `/notifications/unread-count` para getUnreadCount()
  - Agregar ruta GET `/admin/notifications` para admin()
  - Agregar ruta GET `/admin/notifications/stats` para stats()
  - Verificar que las rutas requieren autenticación apropiada
  - _Requirements: 4.1, 5.1_

- [ ]* 9.1 Escribir tests de integración
  - Test para flujo completo de detección y notificación
  - Test para no crear notificación duplicada en BD
  - Test para actualizar estado de múltiples pagos
  - _Requirements: 1.1, 2.6, 3.1, 3.3, 7.1, 7.2_

- [x] 10. Integración final y documentación
  - Actualizar el modelo Payment si es necesario para compatibilidad
  - Verificar integración con módulo de pagos existente
  - Agregar comentarios de documentación en el código
  - Crear archivo README con instrucciones para configurar cron job
  - _Requirements: 8.1, 8.2, 8.3, 8.4_

- [x] 11. Checkpoint final - Validación completa
  - Ejecutar todos los tests (unitarios, integración, e2e)
  - Verificar que no hay errores de sintaxis o linting
  - Probar flujo completo: detección → notificación → visualización
  - Confirmar con el usuario que todo funciona correctamente

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia los requisitos específicos que implementa para trazabilidad
- Los checkpoints aseguran validación incremental del progreso
- El script de cron debe configurarse manualmente en el servidor (fuera del alcance de implementación)
- La estructura de la tabla `notificaciones` ya existe y no requiere modificaciones
