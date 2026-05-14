-- Migration: add_late_fee_system.sql
-- Descripción: Agrega tablas y campos para el sistema de reglas de mora
-- Fecha: 2024
-- Requisitos: 5.1, 5.2, 5.3, 5.8, 10.7, 10.8

START TRANSACTION;

-- 1. Crear tabla de reglas de mora
CREATE TABLE IF NOT EXISTS late_fee_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    dias_gracia INT NOT NULL DEFAULT 0,
    tipo_recargo ENUM('porcentaje', 'monto_fijo') NOT NULL,
    valor_recargo DECIMAL(10,2) NOT NULL,
    frecuencia ENUM('unica', 'diaria', 'semanal', 'mensual') NOT NULL DEFAULT 'unica',
    tope_maximo DECIMAL(10,2) NULL,
    tipo_pago VARCHAR(50) NULL COMMENT 'NULL = aplica a todos los tipos',
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activa (activa),
    INDEX idx_tipo_pago (tipo_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Modificar tabla pagos para agregar campos de mora
-- Primero verificar si las columnas ya existen para evitar errores en re-ejecución

-- Agregar monto_original
SET @dbname = DATABASE();
SET @tablename = 'pagos';
SET @columnname = 'monto_original';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE pagos ADD COLUMN monto_original DECIMAL(10,2) NULL AFTER monto'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar monto_mora
SET @columnname = 'monto_mora';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE pagos ADD COLUMN monto_mora DECIMAL(10,2) DEFAULT 0.00 AFTER monto_original'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar fecha_aplicacion_mora
SET @columnname = 'fecha_aplicacion_mora';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE pagos ADD COLUMN fecha_aplicacion_mora DATE NULL AFTER monto_mora'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar regla_mora_id
SET @columnname = 'regla_mora_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE pagos ADD COLUMN regla_mora_id INT NULL AFTER fecha_aplicacion_mora'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Migrar datos existentes: monto actual -> monto_original, monto_mora = 0
UPDATE pagos 
SET monto_original = monto, 
    monto_mora = 0.00 
WHERE monto_original IS NULL;

-- 4. Agregar foreign key para regla_mora_id (si no existe)
SET @fk_name = 'fk_pagos_regla_mora';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (constraint_name = @fk_name)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE pagos ADD CONSTRAINT fk_pagos_regla_mora FOREIGN KEY (regla_mora_id) REFERENCES late_fee_rules(id) ON DELETE SET NULL'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. Agregar índices para optimización de consultas
-- Verificar y crear índice idx_estado_fecha
SET @index_name = 'idx_estado_fecha';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @index_name)
  ) > 0,
  'SELECT 1',
  'CREATE INDEX idx_estado_fecha ON pagos(estado, fecha_pago)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar y crear índice idx_regla_mora
SET @index_name = 'idx_regla_mora';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @index_name)
  ) > 0,
  'SELECT 1',
  'CREATE INDEX idx_regla_mora ON pagos(regla_mora_id)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 6. Crear tabla de historial de mora
CREATE TABLE IF NOT EXISTS late_fee_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pago_id INT NOT NULL,
    regla_mora_id INT NULL,
    monto_calculado DECIMAL(10,2) NOT NULL,
    monto_aplicado DECIMAL(10,2) NOT NULL,
    dias_atraso INT NOT NULL,
    tipo_operacion ENUM('calculo_automatico', 'ajuste_manual', 'eliminacion') NOT NULL,
    usuario_id INT NULL COMMENT 'Usuario que realizó ajuste manual',
    justificacion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE CASCADE,
    FOREIGN KEY (regla_mora_id) REFERENCES late_fee_rules(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_pago_id (pago_id),
    INDEX idx_tipo_operacion (tipo_operacion),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Insertar regla de mora por defecto como ejemplo
INSERT INTO late_fee_rules (nombre, dias_gracia, tipo_recargo, valor_recargo, frecuencia, tope_maximo, tipo_pago, activa)
VALUES ('Mora Estándar - 2% Mensual', 5, 'porcentaje', 2.00, 'mensual', NULL, NULL, TRUE)
ON DUPLICATE KEY UPDATE nombre = nombre;

COMMIT;

-- Verificación de la migración
SELECT 'Migración completada exitosamente' AS status;
SELECT 'Tablas creadas:' AS info;
SHOW TABLES LIKE 'late_fee%';
SELECT 'Columnas agregadas a pagos:' AS info;
DESCRIBE pagos;
