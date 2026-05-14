# Requirements Document

## Introduction

Este documento define los requisitos para el sistema de reglas de mora (late payment fees) en la aplicación ResiTech de gestión de condominios. El sistema permitirá configurar reglas de recargos por pagos atrasados, calcularlos automáticamente y reflejarlos en pagos, reportes y estados de cuenta.

## Glossary

- **Sistema_Mora**: El subsistema de gestión de reglas y cálculo de recargos por pagos atrasados
- **Regla_Mora**: Configuración que define cómo se calculan los recargos (período de gracia, tipo, monto, frecuencia, tope)
- **Pago**: Registro de cuota de mantenimiento u otro concepto que un residente debe pagar
- **Residente**: Usuario del sistema que habita en el condominio y tiene obligaciones de pago
- **Administrador**: Usuario con permisos para configurar reglas de mora y ajustar recargos
- **Monto_Original**: El monto del pago sin incluir recargos por mora
- **Monto_Mora**: El monto del recargo calculado por pago atrasado
- **Monto_Total**: La suma del monto original más el monto de mora
- **Periodo_Gracia**: Número de días después de la fecha de vencimiento antes de aplicar recargos
- **Dias_Atraso**: Número de días transcurridos desde la fecha de vencimiento (después del período de gracia)
- **Tope_Maximo**: Límite superior opcional para el monto de mora
- **Historial_Mora**: Registro de auditoría de todos los cálculos y ajustes de mora aplicados

## Requirements

### Requirement 1: Configuración de Reglas de Mora

**User Story:** Como administrador, quiero configurar reglas de mora personalizables, para que el sistema calcule automáticamente los recargos según las políticas del condominio.

#### Acceptance Criteria

1. THE Sistema_Mora SHALL permitir crear una Regla_Mora con los siguientes campos: nombre, período de gracia (días), tipo de recargo (porcentaje o monto fijo), valor del recargo, frecuencia de cálculo (único, diario, semanal, mensual), tope máximo opcional, y estado (activa/inactiva)
2. WHEN el Administrador crea una Regla_Mora con tipo porcentaje, THE Sistema_Mora SHALL validar que el valor esté entre 0.01 y 100
3. WHEN el Administrador crea una Regla_Mora con tipo monto fijo, THE Sistema_Mora SHALL validar que el valor sea mayor a 0
4. THE Sistema_Mora SHALL validar que el período de gracia sea un número entero no negativo
5. WHERE una Regla_Mora tiene tope máximo configurado, THE Sistema_Mora SHALL validar que el tope sea mayor que 0
6. THE Sistema_Mora SHALL permitir asociar una Regla_Mora a tipos de pago específicos o aplicarla globalmente
7. THE Sistema_Mora SHALL permitir activar o desactivar una Regla_Mora sin eliminarla
8. THE Sistema_Mora SHALL permitir editar una Regla_Mora existente
9. THE Sistema_Mora SHALL permitir eliminar una Regla_Mora que no tenga mora aplicada en pagos activos
10. THE Sistema_Mora SHALL mostrar una lista de todas las Reglas_Mora con su estado y configuración

### Requirement 2: Cálculo Automático de Mora

**User Story:** Como administrador, quiero que el sistema calcule automáticamente los recargos por mora, para que los pagos atrasados reflejen el monto correcto sin intervención manual.

#### Acceptance Criteria

1. WHEN un Pago tiene estado atrasado y han transcurrido los días del Periodo_Gracia, THE Sistema_Mora SHALL calcular el Monto_Mora según la Regla_Mora aplicable
2. WHEN la Regla_Mora tiene tipo porcentaje, THE Sistema_Mora SHALL calcular el Monto_Mora como (Monto_Original × porcentaje / 100) multiplicado por el factor de frecuencia
3. WHEN la Regla_Mora tiene tipo monto fijo, THE Sistema_Mora SHALL calcular el Monto_Mora como el valor fijo multiplicado por el factor de frecuencia
4. WHEN la frecuencia es única, THE Sistema_Mora SHALL aplicar el recargo una sola vez
5. WHEN la frecuencia es diaria, THE Sistema_Mora SHALL multiplicar el recargo por los Dias_Atraso
6. WHEN la frecuencia es semanal, THE Sistema_Mora SHALL multiplicar el recargo por el número de semanas completas de atraso
7. WHEN la frecuencia es mensual, THE Sistema_Mora SHALL multiplicar el recargo por el número de meses completos de atraso
8. WHERE la Regla_Mora tiene Tope_Maximo configurado, WHEN el Monto_Mora calculado excede el tope, THE Sistema_Mora SHALL limitar el Monto_Mora al valor del tope
9. THE Sistema_Mora SHALL actualizar el Monto_Total del Pago sumando Monto_Original más Monto_Mora
10. THE Sistema_Mora SHALL registrar la fecha de aplicación de mora en el Pago
11. THE Sistema_Mora SHALL ejecutar el cálculo automático diariamente mediante proceso programado
12. WHEN un Pago cambia de estado pendiente a atrasado, THE Sistema_Mora SHALL verificar si debe aplicar mora

### Requirement 3: Integración con Pagos

**User Story:** Como residente, quiero ver el desglose de mi pago incluyendo el monto original y los recargos por mora, para entender claramente cuánto debo y por qué.

#### Acceptance Criteria

1. WHEN un Pago tiene Monto_Mora mayor a cero, THE Sistema_Mora SHALL mostrar el Monto_Original, Monto_Mora y Monto_Total por separado
2. THE Sistema_Mora SHALL mostrar el cálculo detallado de la mora (días de atraso, tasa aplicada, frecuencia)
3. THE Sistema_Mora SHALL mostrar la fecha en que se aplicó la mora
4. THE Sistema_Mora SHALL mostrar el nombre de la Regla_Mora aplicada
5. WHEN un Residente visualiza un Pago con mora, THE Sistema_Mora SHALL mostrar una explicación clara de por qué se aplicó el recargo
6. WHEN un Pago con mora es pagado, THE Sistema_Mora SHALL registrar el Monto_Original y Monto_Mora pagados por separado
7. THE Sistema_Mora SHALL permitir al Administrador ajustar manualmente el Monto_Mora de un Pago
8. WHEN el Administrador ajusta manualmente la mora, THE Sistema_Mora SHALL registrar el ajuste en el Historial_Mora con justificación

### Requirement 4: Reportes y Estado de Cuenta

**User Story:** Como administrador, quiero generar reportes que incluyan información detallada de mora, para analizar los ingresos por recargos y la morosidad del condominio.

#### Acceptance Criteria

1. THE Sistema_Mora SHALL incluir columnas de Monto_Original, Monto_Mora y Monto_Total en el reporte de pagos
2. THE Sistema_Mora SHALL generar un reporte de ingresos por mora agrupado por mes
3. THE Sistema_Mora SHALL mostrar estadísticas de mora: total recaudado, total pendiente, promedio por pago
4. WHEN un Residente consulta su estado de cuenta, THE Sistema_Mora SHALL mostrar el desglose de mora para cada pago atrasado
5. THE Sistema_Mora SHALL incluir el Historial_Mora en el estado de cuenta del Residente
6. THE Sistema_Mora SHALL permitir exportar reportes de mora a Excel y PDF
7. THE Sistema_Mora SHALL mostrar en el dashboard administrativo el total de mora pendiente de cobro
8. THE Sistema_Mora SHALL generar un reporte de pagos con mora aplicada en un rango de fechas

### Requirement 5: Persistencia de Datos

**User Story:** Como desarrollador, quiero que el sistema almacene toda la información de mora de forma estructurada, para garantizar integridad, trazabilidad y auditoría.

#### Acceptance Criteria

1. THE Sistema_Mora SHALL crear una tabla late_fee_rules con campos: id, nombre, dias_gracia, tipo_recargo, valor_recargo, frecuencia, tope_maximo, tipo_pago, activa, created_at, updated_at
2. THE Sistema_Mora SHALL agregar campos a la tabla pagos: monto_original, monto_mora, fecha_aplicacion_mora, regla_mora_id
3. THE Sistema_Mora SHALL crear una tabla late_fee_history con campos: id, pago_id, regla_mora_id, monto_calculado, monto_aplicado, dias_atraso, tipo_operacion, usuario_id, justificacion, created_at
4. THE Sistema_Mora SHALL registrar en late_fee_history cada cálculo automático de mora
5. THE Sistema_Mora SHALL registrar en late_fee_history cada ajuste manual de mora
6. THE Sistema_Mora SHALL mantener la integridad referencial entre pagos y late_fee_rules
7. THE Sistema_Mora SHALL preservar el Historial_Mora incluso si se elimina una Regla_Mora
8. WHEN se actualiza un Pago existente para agregar campos de mora, THE Sistema_Mora SHALL migrar el monto actual a monto_original y establecer monto_mora en 0

### Requirement 6: Interfaz de Administración

**User Story:** Como administrador, quiero una interfaz intuitiva para gestionar reglas de mora, para configurar y mantener el sistema sin necesidad de conocimientos técnicos.

#### Acceptance Criteria

1. THE Sistema_Mora SHALL proporcionar una vista de listado de Reglas_Mora con opciones de crear, editar, activar/desactivar y eliminar
2. THE Sistema_Mora SHALL proporcionar un formulario de creación/edición de Regla_Mora con validación en tiempo real
3. THE Sistema_Mora SHALL mostrar una vista previa del cálculo de mora al configurar una regla
4. THE Sistema_Mora SHALL permitir simular el cálculo de mora para un monto y días de atraso específicos
5. THE Sistema_Mora SHALL mostrar alertas visuales cuando una Regla_Mora esté inactiva
6. THE Sistema_Mora SHALL proporcionar una interfaz para ajustar manualmente la mora de un Pago con campo de justificación obligatorio
7. THE Sistema_Mora SHALL mostrar el Historial_Mora de un Pago en la vista de detalle
8. THE Sistema_Mora SHALL validar que no se pueda eliminar una Regla_Mora si tiene mora aplicada en pagos pendientes

### Requirement 7: Interfaz de Residente

**User Story:** Como residente, quiero ver claramente los recargos por mora en mis pagos, para entender mi situación financiera y tomar acciones correctivas.

#### Acceptance Criteria

1. WHEN un Residente visualiza la lista de pagos, THE Sistema_Mora SHALL indicar visualmente cuáles tienen mora aplicada
2. WHEN un Residente visualiza el detalle de un Pago con mora, THE Sistema_Mora SHALL mostrar el desglose completo: Monto_Original, Monto_Mora, Monto_Total
3. THE Sistema_Mora SHALL mostrar la fórmula de cálculo de mora de forma comprensible para usuarios no técnicos
4. THE Sistema_Mora SHALL mostrar los Dias_Atraso y la fecha de vencimiento original
5. THE Sistema_Mora SHALL mostrar el Periodo_Gracia que se aplicó
6. THE Sistema_Mora SHALL proporcionar una explicación textual de por qué se aplicó la mora
7. WHEN un Residente tiene múltiples pagos con mora, THE Sistema_Mora SHALL mostrar el total acumulado de mora pendiente

### Requirement 8: Manejo de Casos Especiales

**User Story:** Como administrador, quiero que el sistema maneje correctamente casos especiales de mora, para garantizar cálculos justos y precisos en todas las situaciones.

#### Acceptance Criteria

1. WHEN un Pago es pagado parcialmente, THE Sistema_Mora SHALL recalcular la mora sobre el saldo pendiente
2. WHEN un Administrador ajusta manualmente la mora a cero, THE Sistema_Mora SHALL detener el cálculo automático para ese Pago
3. WHEN un Pago atrasado cambia a estado pagado, THE Sistema_Mora SHALL congelar el Monto_Mora en el valor actual
4. WHEN se desactiva una Regla_Mora, THE Sistema_Mora SHALL detener nuevos cálculos pero preservar mora ya aplicada
5. WHEN existen múltiples Reglas_Mora aplicables a un Pago, THE Sistema_Mora SHALL aplicar la regla más específica (tipo de pago sobre global)
6. IF no existe Regla_Mora activa para un Pago atrasado, THEN THE Sistema_Mora SHALL no aplicar mora
7. WHEN la fecha de vencimiento de un Pago es modificada, THE Sistema_Mora SHALL recalcular los Dias_Atraso y la mora
8. WHEN un Pago tiene mora aplicada y se cambia su estado de atrasado a pendiente, THE Sistema_Mora SHALL eliminar la mora aplicada

### Requirement 9: Notificaciones de Mora

**User Story:** Como residente, quiero recibir notificaciones cuando se aplique mora a mis pagos, para estar informado de los recargos y tomar acción.

#### Acceptance Criteria

1. WHEN el Sistema_Mora aplica mora a un Pago por primera vez, THE Sistema_Mora SHALL generar una notificación para el Residente
2. THE Sistema_Mora SHALL incluir en la notificación: concepto del pago, Monto_Original, Monto_Mora, Monto_Total, y fecha de aplicación
3. THE Sistema_Mora SHALL enviar notificación por email cuando se aplique mora
4. THE Sistema_Mora SHALL crear una notificación en el sistema web
5. WHEN el Administrador ajusta manualmente la mora, THE Sistema_Mora SHALL notificar al Residente del ajuste
6. THE Sistema_Mora SHALL incluir en la notificación de ajuste manual la justificación proporcionada por el Administrador

### Requirement 10: Compatibilidad con Sistema Existente

**User Story:** Como desarrollador, quiero que el sistema de mora se integre sin romper funcionalidad existente, para garantizar una transición suave y sin interrupciones.

#### Acceptance Criteria

1. THE Sistema_Mora SHALL mantener compatibilidad con el modelo Payment existente
2. THE Sistema_Mora SHALL mantener compatibilidad con el PaymentController existente
3. THE Sistema_Mora SHALL extender las vistas de pagos existentes sin reemplazarlas
4. THE Sistema_Mora SHALL integrarse con el NotificationService existente
5. THE Sistema_Mora SHALL integrarse con el EmailService existente
6. THE Sistema_Mora SHALL integrarse con el sistema de reportes existente
7. THE Sistema_Mora SHALL proporcionar migraciones de base de datos que no destruyan datos existentes
8. WHEN se instala el Sistema_Mora en una base de datos existente, THE Sistema_Mora SHALL migrar pagos existentes estableciendo monto_original igual al monto actual y monto_mora en 0
