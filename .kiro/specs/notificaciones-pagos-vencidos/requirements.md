# Documento de Requisitos

## Introducción

El módulo de notificaciones de pagos vencidos permite al sistema detectar automáticamente pagos atrasados, generar notificaciones persistentes en la base de datos y eventualmente enviar recordatorios a los residentes. Este módulo se integra con el sistema existente de pagos y residentes del condominio.

## Glosario

- **Sistema_Notificaciones**: Módulo responsable de detectar pagos vencidos y generar notificaciones
- **Detector_Pagos_Vencidos**: Componente que identifica pagos con estado pendiente o atrasado
- **Generador_Notificaciones**: Componente que crea registros de notificaciones en la base de datos
- **Notificación**: Registro en la tabla notificaciones que contiene un mensaje para un residente
- **Pago_Vencido**: Pago con estado "pendiente" o "atrasado" cuya fecha de pago es anterior a la fecha actual
- **Residente**: Usuario del sistema con rol "resident" asociado a un apartamento
- **Administrador**: Usuario del sistema con rol "admin"

## Requisitos

### Requisito 1: Detección de Pagos Vencidos

**User Story:** Como administrador, quiero que el sistema detecte automáticamente los pagos vencidos, para poder notificar a los residentes morosos.

#### Acceptance Criteria

1. WHEN el Detector_Pagos_Vencidos se ejecuta, THE Sistema_Notificaciones SHALL identificar todos los pagos con estado "pendiente" cuya fecha_pago es anterior a la fecha actual
2. WHEN el Detector_Pagos_Vencidos se ejecuta, THE Sistema_Notificaciones SHALL identificar todos los pagos con estado "atrasado"
3. WHEN un pago vencido es detectado, THE Sistema_Notificaciones SHALL obtener la información del residente asociado incluyendo usuario_id, nombre, email y apartamento
4. THE Detector_Pagos_Vencidos SHALL retornar una lista de pagos vencidos con información completa del residente y del pago

### Requisito 2: Generación de Notificaciones

**User Story:** Como administrador, quiero que el sistema genere notificaciones automáticas para pagos vencidos, para mantener un registro de los avisos enviados.

#### Acceptance Criteria

1. WHEN un pago vencido es detectado, THE Generador_Notificaciones SHALL crear un registro en la tabla notificaciones con el usuario_id del residente
2. THE Generador_Notificaciones SHALL establecer el título de la notificación como "Pago Vencido - [concepto]"
3. THE Generador_Notificaciones SHALL incluir en el mensaje los detalles del pago: concepto, monto, mes_pago y fecha_pago
4. THE Generador_Notificaciones SHALL establecer el tipo de notificación como "warning"
5. THE Generador_Notificaciones SHALL establecer el campo leida como FALSE
6. IF ya existe una notificación no leída para el mismo pago y residente, THEN THE Generador_Notificaciones SHALL omitir la creación de una notificación duplicada

### Requisito 3: Actualización de Estado de Pagos

**User Story:** Como administrador, quiero que el sistema actualice automáticamente el estado de pagos pendientes a atrasados, para mantener la información actualizada.

#### Acceptance Criteria

1. WHEN un pago con estado "pendiente" tiene fecha_pago anterior a la fecha actual, THE Sistema_Notificaciones SHALL actualizar el estado del pago a "atrasado"
2. WHEN el estado de un pago se actualiza a "atrasado", THE Sistema_Notificaciones SHALL registrar la fecha de actualización en el campo updated_at
3. THE Sistema_Notificaciones SHALL procesar todos los pagos pendientes vencidos en una sola ejecución

### Requisito 4: Visualización de Notificaciones para Residentes

**User Story:** Como residente, quiero ver mis notificaciones de pagos vencidos, para estar informado de mis obligaciones pendientes.

#### Acceptance Criteria

1. WHEN un residente accede a su panel, THE Sistema_Notificaciones SHALL mostrar todas sus notificaciones ordenadas por fecha de creación descendente
2. THE Sistema_Notificaciones SHALL mostrar un indicador visual para notificaciones no leídas
3. WHEN un residente visualiza una notificación, THE Sistema_Notificaciones SHALL actualizar el campo leida a TRUE
4. THE Sistema_Notificaciones SHALL mostrar el contador de notificaciones no leídas en la interfaz del residente

### Requisito 5: Gestión de Notificaciones para Administradores

**User Story:** Como administrador, quiero ver todas las notificaciones generadas por el sistema, para supervisar los avisos enviados a los residentes.

#### Acceptance Criteria

1. WHEN un administrador accede al módulo de notificaciones, THE Sistema_Notificaciones SHALL mostrar todas las notificaciones del sistema
2. THE Sistema_Notificaciones SHALL permitir filtrar notificaciones por residente, tipo y estado de lectura
3. THE Sistema_Notificaciones SHALL mostrar estadísticas de notificaciones: total generadas, leídas, no leídas
4. WHEN un administrador selecciona un residente, THE Sistema_Notificaciones SHALL mostrar el historial completo de notificaciones de ese residente

### Requisito 6: Ejecución Programada del Detector

**User Story:** Como administrador, quiero que el sistema ejecute automáticamente la detección de pagos vencidos, para no tener que hacerlo manualmente.

#### Acceptance Criteria

1. THE Sistema_Notificaciones SHALL proporcionar un endpoint o script ejecutable para la detección de pagos vencidos
2. THE Sistema_Notificaciones SHALL registrar en logs cada ejecución del detector incluyendo fecha, hora y cantidad de pagos procesados
3. WHEN el detector se ejecuta, THE Sistema_Notificaciones SHALL procesar todos los pagos vencidos y generar las notificaciones correspondientes
4. IF ocurre un error durante la ejecución, THEN THE Sistema_Notificaciones SHALL registrar el error en logs sin interrumpir el procesamiento de otros pagos

### Requisito 7: Prevención de Notificaciones Duplicadas

**User Story:** Como residente, quiero recibir solo una notificación por cada pago vencido, para no ser saturado con mensajes repetidos.

#### Acceptance Criteria

1. WHEN el Generador_Notificaciones verifica duplicados, THE Sistema_Notificaciones SHALL buscar notificaciones existentes con el mismo usuario_id y título
2. WHEN existe una notificación no leída del mismo tipo para el mismo pago, THE Sistema_Notificaciones SHALL omitir la creación de una nueva notificación
3. WHEN un residente marca una notificación como leída y el pago sigue vencido, THE Sistema_Notificaciones SHALL permitir generar una nueva notificación en la siguiente ejecución
4. THE Sistema_Notificaciones SHALL considerar el mes_pago y concepto para identificar notificaciones del mismo pago

### Requisito 8: Integración con Módulo de Pagos Existente

**User Story:** Como desarrollador, quiero que el módulo de notificaciones se integre con el sistema de pagos existente, para aprovechar la funcionalidad actual.

#### Acceptance Criteria

1. THE Sistema_Notificaciones SHALL utilizar el modelo Payment existente para consultar pagos vencidos
2. THE Sistema_Notificaciones SHALL utilizar el modelo Resident existente para obtener información de residentes
3. WHEN un pago cambia de estado a "pagado", THE Sistema_Notificaciones SHALL permitir marcar las notificaciones relacionadas como resueltas
4. THE Sistema_Notificaciones SHALL utilizar la tabla notificaciones existente en la base de datos sin modificar su estructura
