# Sistema de Reglas de Mora - Guía de Instalación y Configuración

## Descripción General

El Sistema de Reglas de Mora es un módulo que automatiza el cálculo y gestión de recargos por pagos atrasados en ResiTech. Permite configurar reglas flexibles, calcular mora automáticamente mediante cron job, y proporcionar transparencia completa a residentes y administradores.

## Características Principales

- ✅ Configuración flexible de reglas de mora (porcentaje o monto fijo)
- ✅ Cálculo automático diario mediante cron job
- ✅ Múltiples frecuencias: única, diaria, semanal, mensual
- ✅ Topes máximos configurables
- ✅ Historial completo de auditoría
- ✅ Notificaciones automáticas por email y sistema
- ✅ Reportes detallados y estadísticas
- ✅ Ajustes manuales con justificación
- ✅ Simulador de cálculo

## Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Composer instalado
- Acceso a cron jobs (para cálculo automático)
- Sistema ResiTech base instalado y funcionando

## Instalación

### Paso 1: Backup de Base de Datos

**IMPORTANTE:** Siempre realice un backup antes de ejecutar migraciones.

```bash
mysqldump -u usuario -p nombre_base_datos > backup_pre_mora_$(date +%Y%m%d).sql
```

### Paso 2: Ejecutar Migración de Base de Datos

La migración crea las tablas necesarias y agrega campos a la tabla de pagos existente.

```bash
mysql -u usuario -p nombre_base_datos < database/add_late_fee_system.sql
```

**Verificar migración:**

```sql
-- Verificar que las tablas se crearon
SHOW TABLES LIKE 'late_fee%';

-- Verificar campos agregados a pagos
DESCRIBE pagos;

-- Verificar migración de datos
SELECT COUNT(*) FROM pagos WHERE monto_original IS NOT NULL;
```

### Paso 3: Verificar Archivos del Sistema

Asegúrese de que los siguientes archivos existen:

**Modelos:**
- `app/models/LateFeeRule.php`
- `app/models/LateFeeHistory.php`

**Servicios:**
- `app/services/LateFeeService.php`

**Controladores:**
- `app/controllers/LateFeeController.php`

**Vistas Administrativas:**
- `app/views/admin/late_fee_rules/index.php`
- `app/views/admin/late_fee_rules/create.php`
- `app/views/admin/late_fee_rules/edit.php`
- `app/views/admin/late_fee_rules/simulate.php`
- `app/views/admin/late_fees/report.php`
- `app/views/admin/late_fees/stats.php`

**Templates de Email:**
- `app/views/emails/late_fee_notification.php`

**Script Cron:**
- `calculate_late_fees.php`

### Paso 4: Configurar Cron Job

El cálculo automático de mora se ejecuta mediante un cron job diario.

**Editar crontab:**

```bash
crontab -e
```

**Agregar línea (ejecutar diariamente a las 2:00 AM):**

```cron
0 2 * * * php /ruta/completa/al/proyecto/calculate_late_fees.php >> /ruta/logs/late_fees_cron.log 2>&1
```

**Alternativas de horario:**

```cron
# Ejecutar a las 3:00 AM
0 3 * * * php /ruta/completa/al/proyecto/calculate_late_fees.php >> /ruta/logs/late_fees_cron.log 2>&1

# Ejecutar a la medianoche
0 0 * * * php /ruta/completa/al/proyecto/calculate_late_fees.php >> /ruta/logs/late_fees_cron.log 2>&1
```

**Crear directorio de logs:**

```bash
mkdir -p /ruta/logs
chmod 755 /ruta/logs
```

### Paso 5: Prueba Manual del Script

Antes de confiar en el cron job, ejecute el script manualmente:

```bash
php calculate_late_fees.php
```

**Verificar salida:**

```bash
tail -f /ruta/logs/late_fees_cron.log
```

**Salida esperada:**

```
[calculate_late_fees.php] ========================================
[calculate_late_fees.php] Inicio: 2024-01-15 14:30:00
[calculate_late_fees.php] Conexión establecida
[calculate_late_fees.php] Iniciando cálculo de mora...
[calculate_late_fees.php] Completado exitosamente
[calculate_late_fees.php] Pagos procesados: 10
[calculate_late_fees.php] Mora aplicada: 5
[calculate_late_fees.php] Notificaciones enviadas: 5
[calculate_late_fees.php] Fin: 2024-01-15 14:30:05
[calculate_late_fees.php] ========================================
```

## Configuración Inicial

### Crear Primera Regla de Mora

1. Acceder como administrador
2. Navegar a: **Pagos > Reglas de Mora** o `/late-fee-rules`
3. Hacer clic en **"Nueva Regla"**
4. Configurar la regla:

**Ejemplo 1: Mora Estándar 2% Mensual**
- Nombre: "Mora Estándar 2% Mensual"
- Días de Gracia: 5
- Tipo de Recargo: Porcentaje
- Valor: 2.00
- Frecuencia: Mensual
- Tope Máximo: (dejar vacío o configurar límite)
- Tipo de Pago: (dejar vacío para aplicar a todos)
- Estado: Activa

**Ejemplo 2: Mora Fija $50 Única**
- Nombre: "Mora Fija $50"
- Días de Gracia: 3
- Tipo de Recargo: Monto Fijo
- Valor: 50.00
- Frecuencia: Única
- Tope Máximo: (no aplica)
- Tipo de Pago: (dejar vacío)
- Estado: Activa

5. Guardar la regla

### Probar el Simulador

1. Navegar a: **Reglas de Mora > Simulador**
2. Seleccionar una regla
3. Ingresar monto de prueba: $1000
4. Ingresar días de atraso: 35
5. Hacer clic en **"Calcular Mora"**
6. Verificar que el cálculo sea correcto

## Uso del Sistema

### Para Administradores

#### Gestionar Reglas de Mora

**Listar reglas:**
- Ruta: `/late-fee-rules`
- Muestra todas las reglas con su estado

**Crear regla:**
- Ruta: `/late-fee-rules/create`
- Formulario con validación en tiempo real

**Editar regla:**
- Ruta: `/late-fee-rules/edit/{id}`
- Advertencia si la regla tiene mora aplicada

**Activar/Desactivar:**
- Botón de toggle en listado
- Desactivar detiene nuevos cálculos, preserva mora existente

**Eliminar:**
- Solo si no tiene mora aplicada en pagos activos
- Confirmación requerida

#### Ajustar Mora Manualmente

1. Ir a **Pagos > Editar Pago**
2. Si el pago tiene mora, aparece sección de ajuste
3. Ingresar nuevo monto de mora
4. **Justificación obligatoria** (mínimo 10 caracteres)
5. Guardar ajuste
6. El ajuste queda registrado en historial

#### Ver Reportes

**Reporte de Mora:**
- Ruta: `/late-fees/report`
- Filtros: fecha inicio, fecha fin, estado
- Exportar a Excel
- Totales: monto original, mora, total

**Estadísticas:**
- Ruta: `/late-fees/stats`
- Dashboard con métricas clave
- Gráfico de ingresos mensuales
- Top 10 residentes con mora
- Distribución por regla

### Para Residentes

#### Ver Pagos con Mora

- Los pagos con mora se destacan visualmente
- Badge rojo indica "Mora"
- Columna adicional muestra monto de mora
- Total incluye mora

#### Ver Detalles de Mora

Al ver un pago con mora:
- Desglose completo: original + mora = total
- Días de atraso
- Fecha de aplicación
- Regla aplicada
- Explicación del cálculo
- Historial de ajustes (si existen)

#### Notificaciones

Los residentes reciben:
- Notificación en sistema cuando se aplica mora
- Email con detalles completos
- Notificación de ajustes manuales

## Casos de Uso Comunes

### Caso 1: Aplicar Mora a Pagos Atrasados

**Automático (recomendado):**
1. El cron job se ejecuta diariamente
2. Detecta pagos atrasados
3. Calcula mora según reglas
4. Aplica mora y registra en historial
5. Envía notificaciones

**Manual:**
1. Ejecutar: `php calculate_late_fees.php`

### Caso 2: Condonar Mora a un Residente

1. Ir a **Pagos > Editar Pago**
2. En sección de ajuste de mora
3. Ingresar monto: 0.00
4. Justificación: "Condonación por [motivo]"
5. Guardar

### Caso 3: Cambiar Política de Mora

1. Desactivar regla actual
2. Crear nueva regla con nueva política
3. Activar nueva regla
4. La mora existente se preserva
5. Nuevos cálculos usan nueva regla

### Caso 4: Simular Impacto de Nueva Regla

1. Ir a **Simulador**
2. Seleccionar regla (o crear temporal)
3. Probar con diferentes montos y días
4. Analizar resultados antes de activar

## Mantenimiento

### Monitoreo del Cron Job

**Verificar ejecución:**

```bash
tail -f /ruta/logs/late_fees_cron.log
```

**Verificar última ejecución:**

```bash
grep "Completado exitosamente" /ruta/logs/late_fees_cron.log | tail -1
```

**Verificar errores:**

```bash
grep "ERROR\|EXCEPCIÓN" /ruta/logs/late_fees_cron.log
```

### Limpieza de Logs

**Rotar logs mensualmente:**

```bash
# Crear script de rotación
cat > /ruta/scripts/rotate_late_fee_logs.sh << 'EOF'
#!/bin/bash
LOG_DIR="/ruta/logs"
ARCHIVE_DIR="$LOG_DIR/archive"
mkdir -p $ARCHIVE_DIR

# Mover log actual a archivo
mv $LOG_DIR/late_fees_cron.log $ARCHIVE_DIR/late_fees_cron_$(date +%Y%m).log

# Comprimir logs antiguos (más de 30 días)
find $ARCHIVE_DIR -name "*.log" -mtime +30 -exec gzip {} \;

# Eliminar logs comprimidos antiguos (más de 1 año)
find $ARCHIVE_DIR -name "*.log.gz" -mtime +365 -delete
EOF

chmod +x /ruta/scripts/rotate_late_fee_logs.sh
```

**Agregar a crontab (primer día del mes):**

```cron
0 0 1 * * /ruta/scripts/rotate_late_fee_logs.sh
```

### Auditoría

**Revisar historial de mora:**

```sql
SELECT 
    p.id,
    p.concepto,
    lfh.tipo_operacion,
    lfh.monto_calculado,
    lfh.monto_aplicado,
    lfh.justificacion,
    lfh.created_at
FROM late_fee_history lfh
JOIN pagos p ON lfh.pago_id = p.id
WHERE lfh.tipo_operacion = 'ajuste_manual'
ORDER BY lfh.created_at DESC
LIMIT 50;
```

**Verificar integridad:**

```sql
-- Pagos con mora pero sin historial
SELECT p.id, p.concepto, p.monto_mora
FROM pagos p
LEFT JOIN late_fee_history lfh ON p.id = lfh.pago_id
WHERE p.monto_mora > 0 AND lfh.id IS NULL;

-- Mora aplicada sin regla
SELECT id, concepto, monto_mora, regla_mora_id
FROM pagos
WHERE monto_mora > 0 AND regla_mora_id IS NULL;
```

## Solución de Problemas

### Problema: El cron job no se ejecuta

**Verificar:**

1. Crontab configurado correctamente: `crontab -l`
2. Ruta absoluta al script PHP
3. Permisos de ejecución: `chmod +x calculate_late_fees.php`
4. Servicio cron activo: `systemctl status cron`

**Probar manualmente:**

```bash
php /ruta/completa/calculate_late_fees.php
```

### Problema: No se aplica mora a pagos atrasados

**Verificar:**

1. Existe regla activa: `SELECT * FROM late_fee_rules WHERE activa = 1;`
2. Pagos están en estado atrasado: `SELECT * FROM pagos WHERE estado = 'atrasado';`
3. Días de gracia no exceden atraso
4. Revisar logs del cron job

### Problema: Mora calculada incorrectamente

**Verificar:**

1. Usar simulador con mismos parámetros
2. Revisar configuración de regla
3. Verificar tipo de recargo (porcentaje vs monto fijo)
4. Verificar frecuencia y multiplicador
5. Verificar tope máximo

### Problema: Notificaciones no se envían

**Verificar:**

1. Configuración de email en `.env`
2. Servicio de email funcionando
3. Logs de email: `tail -f logs/emails/email_*.log`
4. Residente tiene email configurado

## Rollback

Si necesita revertir el sistema de mora:

### Paso 1: Backup de Datos de Mora

```sql
-- Exportar datos de mora
SELECT * FROM late_fee_rules INTO OUTFILE '/tmp/late_fee_rules_backup.csv';
SELECT * FROM late_fee_history INTO OUTFILE '/tmp/late_fee_history_backup.csv';
SELECT id, monto_original, monto_mora, fecha_aplicacion_mora, regla_mora_id 
FROM pagos WHERE monto_mora > 0 
INTO OUTFILE '/tmp/pagos_mora_backup.csv';
```

### Paso 2: Desactivar Cron Job

```bash
crontab -e
# Comentar o eliminar línea del cron job
```

### Paso 3: Ejecutar Script de Rollback

```sql
-- Rollback script
START TRANSACTION;

-- Eliminar foreign key
ALTER TABLE pagos DROP FOREIGN KEY fk_pagos_regla_mora;

-- Eliminar columnas agregadas
ALTER TABLE pagos 
DROP COLUMN regla_mora_id,
DROP COLUMN fecha_aplicacion_mora,
DROP COLUMN monto_mora,
DROP COLUMN monto_original;

-- Eliminar tablas nuevas
DROP TABLE IF EXISTS late_fee_history;
DROP TABLE IF EXISTS late_fee_rules;

COMMIT;
```

### Paso 4: Restaurar Backup Original

```bash
mysql -u usuario -p nombre_base_datos < backup_pre_mora_YYYYMMDD.sql
```

## Mejores Prácticas

### Configuración de Reglas

1. **Empezar conservador:** Configure tasas bajas inicialmente
2. **Período de gracia razonable:** 3-5 días es común
3. **Tope máximo:** Considere limitar mora al 50-100% del monto original
4. **Una regla global:** Evite múltiples reglas conflictivas
5. **Comunicar cambios:** Notifique a residentes antes de activar

### Monitoreo

1. **Revisar logs diariamente** los primeros días
2. **Auditar ajustes manuales** mensualmente
3. **Analizar estadísticas** para detectar patrones
4. **Verificar integridad** de datos trimestralmente

### Comunicación

1. **Informar a residentes** sobre nueva política
2. **Explicar cálculo** de forma clara
3. **Proporcionar ejemplos** en documentación
4. **Responder consultas** rápidamente

## Soporte

Para soporte adicional:

- Revisar logs del sistema
- Consultar documentación de diseño: `.kiro/specs/reglas-mora/design.md`
- Revisar requisitos: `.kiro/specs/reglas-mora/requirements.md`
- Contactar al equipo de desarrollo

## Changelog

### Versión 1.0.0 (2024-01-15)
- Implementación inicial del sistema de mora
- Soporte para reglas de porcentaje y monto fijo
- Cálculo automático mediante cron job
- Notificaciones por email y sistema
- Reportes y estadísticas
- Ajustes manuales con auditoría
- Simulador de cálculo

---

**Última actualización:** 2024-01-15
**Versión del documento:** 1.0.0
