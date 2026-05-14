# Implementation Plan: Sistema de Reglas de Mora

## Overview

Este plan implementa el sistema de reglas de mora (late payment fees) para CondoWeb, integrándose con la aplicación existente de gestión de condominios. El sistema permite configurar reglas flexibles de recargos por pagos atrasados, calcularlos automáticamente mediante un script cron, y proporcionar transparencia completa a residentes y administradores.

La implementación sigue un enfoque incremental: primero la capa de datos (migración y modelos), luego la lógica de negocio (servicio de cálculo), después los controladores y vistas administrativas, integración con pagos existentes, vistas de residentes, script cron, y finalmente reportes y ajustes.

## Tasks

- [x] 1. Crear migración de base de datos
  - Crear archivo `database/add_late_fee_system.sql` con las tres tablas: `late_fee_rules`, modificaciones a `pagos`, y `late_fee_history`
  - Incluir lógica de verificación para evitar duplicar columnas si ya existen
  - Incluir migración de datos existentes (monto → monto_original, monto_mora = 0)
  - Agregar índices para optimización de consultas
  - Insertar regla de mora por defecto como ejemplo
  - _Requirements: 5.1, 5.2, 5.3, 5.8, 10.7, 10.8_

- [ ] 2. Crear modelos de datos
  - [x] 2.1 Crear modelo LateFeeRule
    - Crear archivo `app/models/LateFeeRule.php` con propiedades públicas y métodos CRUD
    - Implementar métodos: `create()`, `readAll()`, `readOne()`, `update()`, `delete()`
    - Implementar métodos de negocio: `getActiveRules()`, `getRuleForPaymentType()`, `getGlobalRule()`, `canDelete()`, `activate()`, `deactivate()`
    - _Requirements: 1.1, 1.7, 1.8, 1.9, 1.10_
  
  - [x] 2.2 Crear modelo LateFeeHistory
    - Crear archivo `app/models/LateFeeHistory.php` con propiedades públicas
    - Implementar métodos: `create()`, `getByPaymentId()`, `getRecentHistory()`
    - _Requirements: 5.3, 5.4, 5.5, 5.7_
  
  - [x] 2.3 Extender modelo Payment
    - Agregar propiedades públicas: `monto_original`, `monto_mora`, `fecha_aplicacion_mora`, `regla_mora_id`
    - Implementar métodos auxiliares: `getMonto_total()`, `hasLateFee()`, `getLateFeePercentage()`
    - Actualizar método `readOne()` para incluir nuevos campos
    - Actualizar método `readAll()` para incluir nuevos campos
    - _Requirements: 3.1, 5.2_

- [ ] 3. Implementar servicio de cálculo de mora
  - [x] 3.1 Crear LateFeeService con métodos de cálculo
    - Crear archivo `app/services/LateFeeService.php` con constructor que recibe conexión DB
    - Implementar método `calculateLateFee()` con algoritmo completo de cálculo
    - Implementar métodos privados: `getDaysOverdue()`, `getFrequencyMultiplier()`, `calculateByPercentage()`, `calculateByFixedAmount()`, `applyMaxCap()`, `findApplicableRule()`
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 8.5_
  
  - [x] 3.2 Implementar aplicación y gestión de mora
    - Implementar método `applyLateFee()` para actualizar pago con mora calculada
    - Implementar método `removeLateFee()` para eliminar mora de un pago
    - Implementar método `adjustLateFee()` para ajustes manuales con justificación
    - Implementar método privado `logCalculation()` para registrar en historial
    - _Requirements: 2.9, 2.10, 3.7, 3.8, 5.4, 5.5_
  
  - [x] 3.3 Implementar procesamiento automático de pagos atrasados
    - Implementar método `processOverduePayments()` que procesa todos los pagos atrasados
    - Implementar método privado `getOverduePayments()` para obtener pagos que requieren cálculo
    - Implementar método privado `sendLateFeeNotification()` para notificar a residentes
    - Incluir manejo de errores y logging detallado
    - _Requirements: 2.11, 2.12, 9.1, 9.2, 9.3, 9.4_
  
  - [x] 3.4 Implementar métodos de consulta y estadísticas
    - Implementar método `getLateFeeBreakdown()` para desglose detallado de mora de un pago
    - Implementar método `getLateFeeStats()` para estadísticas generales
    - Implementar método `getMonthlyLateFeeIncome()` para ingresos mensuales por mora
    - _Requirements: 4.1, 4.2, 4.3, 4.7_

- [x] 4. Checkpoint - Verificar capa de datos y lógica de negocio
  - Ejecutar migración en base de datos de desarrollo
  - Verificar que las tablas se crean correctamente
  - Probar creación de regla de mora mediante código
  - Probar cálculo de mora con diferentes escenarios
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Crear controlador administrativo
  - [x] 5.1 Crear LateFeeController con métodos CRUD
    - Crear archivo `app/controllers/LateFeeController.php` extendiendo Controller
    - Implementar métodos: `index()`, `create()`, `store()`, `edit()`, `update()`, `delete()`, `toggle()`
    - Incluir validación de datos según reglas definidas en diseño
    - Implementar prevención de eliminación de reglas con mora aplicada
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 6.1, 6.8_
  
  - [x] 5.2 Implementar simulador y ajustes manuales
    - Implementar método `simulate()` para simulación de cálculo de mora
    - Implementar método `adjustLateFee()` para ajustes manuales con validación de justificación
    - _Requirements: 3.7, 3.8, 6.4, 6.6_
  
  - [x] 5.3 Implementar métodos de reportes
    - Implementar método `report()` para reporte de mora con filtros
    - Implementar método `stats()` para dashboard de estadísticas
    - _Requirements: 4.2, 4.3, 4.8_

- [ ] 6. Crear vistas administrativas
  - [x] 6.1 Crear vista de listado de reglas
    - Crear archivo `app/views/admin/late_fee_rules/index.php`
    - Mostrar tabla con todas las reglas: nombre, tipo, valor, frecuencia, tope, estado
    - Incluir botones de acción: crear, editar, activar/desactivar, eliminar
    - Agregar indicadores visuales de estado (activa/inactiva)
    - _Requirements: 1.10, 6.1, 6.5_
  
  - [x] 6.2 Crear formularios de creación y edición
    - Crear archivo `app/views/admin/late_fee_rules/create.php` con formulario completo
    - Crear archivo `app/views/admin/late_fee_rules/edit.php` similar a create
    - Incluir validación JavaScript en tiempo real
    - Mostrar advertencia en edit si hay mora aplicada
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.8, 6.2_
  
  - [x] 6.3 Crear vista de simulador
    - Crear archivo `app/views/admin/late_fee_rules/simulate.php`
    - Incluir inputs: monto, días de atraso, selección de regla
    - Mostrar cálculo paso a paso con explicación
    - _Requirements: 6.3, 6.4_
  
  - [x] 6.4 Crear vistas de reportes administrativos
    - Crear archivo `app/views/admin/late_fees/report.php` con filtros y tabla detallada
    - Crear archivo `app/views/admin/late_fees/stats.php` con dashboard de estadísticas
    - Incluir gráficos de tendencias y exportación a Excel/PDF
    - _Requirements: 4.2, 4.3, 4.6, 4.7, 4.8_

- [ ] 7. Integrar con sistema de pagos existente
  - [x] 7.1 Modificar vista de edición de pagos
    - Modificar `app/views/admin/payments/edit.php` para agregar sección de ajuste de mora
    - Incluir campo de monto de mora editable y campo de justificación obligatorio
    - Mostrar historial de mora del pago
    - _Requirements: 3.7, 3.8, 6.6, 6.7_
  
  - [x] 7.2 Modificar PaymentController para ajustes de mora
    - Agregar integración con LateFeeService en constructor
    - Agregar llamada a `adjustLateFee()` en método `update()` si se modifica mora
    - _Requirements: 3.7, 3.8, 10.2_
  
  - [x] 7.3 Actualizar vistas de listado de pagos para administradores
    - Modificar `app/views/admin/payments/pending.php` para mostrar columna de mora
    - Modificar `app/views/admin/payments/stats.php` para incluir estadísticas de mora
    - _Requirements: 4.1, 4.7_

- [ ] 8. Crear vistas para residentes
  - [x] 8.1 Modificar vista de listado de pagos de residentes
    - Modificar `app/views/payments/index.php` para agregar indicador visual de pagos con mora (badge rojo)
    - Agregar columna "Mora" en la tabla
    - Mostrar total de mora pendiente destacado
    - _Requirements: 7.1, 7.7_
  
  - [x] 8.2 Modificar vista de detalle de pago
    - Modificar `app/views/payments/show.php` para agregar sección de desglose de mora
    - Mostrar: monto original, monto de mora, monto total, días de atraso, regla aplicada, fecha de aplicación
    - Incluir explicación comprensible del cálculo
    - Mostrar historial de ajustes si existen
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 7.2, 7.3, 7.4, 7.5, 7.6_

- [ ] 9. Implementar notificaciones de mora
  - [x] 9.1 Crear template de email de mora
    - Crear archivo `app/views/emails/late_fee_notification.php`
    - Incluir tabla con desglose: concepto, monto original, recargo, total, fecha vencimiento, días atraso
    - Incluir mensaje de advertencia y llamado a acción
    - _Requirements: 9.1, 9.2, 9.3, 9.4_
  
  - [x] 9.2 Integrar notificaciones en LateFeeService
    - Verificar que `sendLateFeeNotification()` crea notificación en sistema y envía email
    - Implementar notificación para ajustes manuales con justificación
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 10.4, 10.5_

- [x] 10. Crear script cron de cálculo automático
  - Crear archivo `calculate_late_fees.php` en raíz del proyecto
  - Cargar autoloader, variables de entorno, y configuración de base de datos
  - Instanciar LateFeeService y llamar a `processOverduePayments()`
  - Incluir logging detallado de inicio, progreso, resultado y errores
  - Incluir manejo de excepciones con exit codes apropiados
  - _Requirements: 2.11, 10.7_

- [x] 11. Configurar rutas
  - Agregar rutas en `index.php` o archivo de rutas para LateFeeController
  - Rutas: `/admin/late-fee-rules`, `/admin/late-fee-rules/create`, `/admin/late-fee-rules/{id}/edit`, `/admin/late-fee-rules/{id}/delete`, `/admin/late-fee-rules/{id}/toggle`, `/admin/late-fee-rules/simulate`, `/admin/payments/{id}/adjust-late-fee`, `/admin/late-fees/report`, `/admin/late-fees/stats`
  - Verificar que todas las rutas requieren autenticación de administrador
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.6_

- [ ] 12. Implementar manejo de casos especiales
  - [x] 12.1 Implementar lógica de pagos parciales
    - Modificar `calculateLateFee()` para recalcular mora sobre saldo pendiente
    - _Requirements: 8.1_
  
  - [x] 12.2 Implementar detención de cálculo automático
    - Agregar verificación en `processOverduePayments()` para no recalcular si mora fue ajustada manualmente a cero
    - _Requirements: 8.2_
  
  - [x] 12.3 Implementar congelamiento de mora al pagar
    - Agregar lógica en PaymentController para congelar mora cuando estado cambia a "pagado"
    - _Requirements: 8.3_
  
  - [x] 12.4 Implementar preservación de mora al desactivar regla
    - Verificar que `deactivate()` no elimina mora ya aplicada
    - _Requirements: 8.4_
  
  - [x] 12.5 Implementar recálculo al modificar fecha de vencimiento
    - Agregar lógica en PaymentController para recalcular mora si fecha_pago cambia
    - _Requirements: 8.7_
  
  - [x] 12.6 Implementar eliminación de mora al cambiar estado
    - Agregar lógica para eliminar mora si estado cambia de "atrasado" a "pendiente"
    - _Requirements: 8.8_

- [x] 13. Integrar con sistema de reportes existente
  - Modificar ReportController para incluir datos de mora en reportes existentes
  - Actualizar `app/views/admin/reports/financial_summary.php` para mostrar ingresos por mora
  - Actualizar `app/views/admin/reports/income.php` para desglosar mora
  - Actualizar dashboard administrativo para mostrar total de mora pendiente
  - _Requirements: 4.1, 4.6, 4.7, 10.6_

- [x] 14. Checkpoint final - Pruebas de integración
  - Ejecutar migración en base de datos limpia
  - Crear regla de mora de prueba
  - Crear pago atrasado de prueba
  - Ejecutar script cron manualmente: `php calculate_late_fees.php`
  - Verificar que se aplicó mora correctamente
  - Verificar que se envió notificación
  - Probar ajuste manual de mora
  - Probar simulador de cálculo
  - Verificar reportes con datos de mora
  - Probar vistas de residente con mora
  - Verificar casos especiales (pago parcial, desactivar regla, etc.)
  - Ensure all tests pass, ask the user if questions arise.

- [x] 15. Documentación y deployment
  - Crear archivo `LATE_FEE_SYSTEM_SETUP.md` con instrucciones de instalación
  - Documentar configuración de cron job
  - Documentar proceso de rollback
  - Crear script de rollback `database/rollback_late_fee_system.sql`
  - Documentar casos de uso comunes para administradores
  - _Requirements: 10.7_

## Notes

- Todas las tareas referencian requisitos específicos para trazabilidad
- Los checkpoints (tareas 4 y 14) aseguran validación incremental
- La implementación sigue el orden: datos → lógica → controladores → vistas → integración
- El sistema mantiene compatibilidad completa con código existente (Requirement 10)
- Todos los cálculos incluyen logging detallado para auditoría
- Las notificaciones se integran con servicios existentes (NotificationService, EmailService)
