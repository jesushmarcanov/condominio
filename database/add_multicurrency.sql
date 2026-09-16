-- Migration: add_multicurrency.sql
-- Descripción: Agrega soporte multimoneda (USD y Bolívares) al sistema.
-- - Tabla configuración con moneda base, tasa de cambio y origen de la tasa
-- - Columnas de moneda en declaraciones_pago y pagos
-- - Backfill: los montos históricos de pagos/cuotas (están expresados en USD,
--   el valor por defecto del seed) quedan marcados con moneda='USD'
-- Fecha: 2026
-- Requisitos: Roadmap - Multimoneda (dólares y bolívares)
--
-- NOTA SOBRE EL CAMBIO MANUAL DE MONEDA BASE:
--   Al cambiar la moneda base en Configuración (POST /settings, acción 'base'),
--   el método CurrencyService::changeBase() SOLO actualiza la configuración
--   moneda_base: NO modifica ni convierte ningún monto guardado. Cada registro
--   conserva la moneda con la que fue creado; la conversión se realiza en tiempo
--   de visualización (dashboard, listados, reportes) con la tasa vigente.
--   Los montos de tablas sin columna moneda (cuotas, reglas/historial de mora) se
--   interpretan en la moneda base actual; los de pagos/declaraciones_pago se
--   interpretan en la moneda que guardan.

START TRANSACTION;

-- 1. Tabla de configuración (clave/valor)
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT NULL,
    descripcion VARCHAR(255) NULL,
    actualizado_por INT NULL,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Configuración inicial multimoneda
INSERT INTO configuracion (clave, valor, descripcion) VALUES
    ('moneda_base', 'USD', 'Moneda base de almacenamiento (USD o VES)'),
    ('tasa_cambio', NULL, 'Tasa de cambio en Bs por 1 USD (BCV)'),
    ('tasa_fecha', NULL, 'Fecha de vigencia de la tasa'),
    ('tasa_origen', 'bcv', 'Origen de la tasa: bcv o manual'),
    ('multimoneda_activo', '1', 'Mostrar conversión a la segunda moneda (1=activado)')
ON DUPLICATE KEY UPDATE clave = clave;

-- 3. Columna de moneda en declaraciones_pago
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = 'declaraciones_pago'
          AND table_schema = DATABASE()
          AND column_name = 'moneda'
    ) > 0,
    'SELECT 1',
    "ALTER TABLE declaraciones_pago
       ADD COLUMN moneda ENUM('USD','VES') DEFAULT 'USD' AFTER monto_declarado,
       ADD COLUMN monto_moneda_original DECIMAL(10,2) NULL AFTER moneda"
));
PREPARE alterDeclaraciones FROM @preparedStatement;
EXECUTE alterDeclaraciones;
DEALLOCATE PREPARE alterDeclaraciones;

-- 4. Columna de moneda en pagos
SET @preparedStatement2 = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = 'pagos'
          AND table_schema = DATABASE()
          AND column_name = 'moneda'
    ) > 0,
    'SELECT 1',
    "ALTER TABLE pagos
       ADD COLUMN moneda ENUM('USD','VES') DEFAULT 'USD' AFTER monto,
       ADD COLUMN monto_moneda_original DECIMAL(10,2) NULL AFTER moneda"
));
PREPARE alterPagos FROM @preparedStatement2;
EXECUTE alterPagos;
DEALLOCATE PREPARE alterPagos;

-- 5. Backfill: los montos históricos existentes se crearon en la moneda semilla (USD).
--    Marcamos las filas de pagos que aún no tienen moneda según su valor de relleno.
UPDATE pagos SET moneda = 'USD' WHERE moneda NOT IN ('USD','VES') OR moneda IS NULL;
UPDATE declaraciones_pago SET moneda = 'USD' WHERE moneda NOT IN ('USD','VES') OR moneda IS NULL;

COMMIT;

-- Verificación de la migración
SELECT 'Migración completada exitosamente' AS status;
SELECT 'Tablas creadas:' AS info;
SHOW TABLES LIKE 'configuracion';
SELECT 'Registros de configuración:' AS info;
SELECT clave, valor, descripcion FROM configuracion ORDER BY clave;
SELECT 'Columna moneda en declaraciones_pago:' AS info;
SHOW COLUMNS FROM declaraciones_pago LIKE 'moneda';
SELECT 'Columna moneda en pagos:' AS info;
SHOW COLUMNS FROM pagos LIKE 'moneda';