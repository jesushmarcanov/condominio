-- Migración: add_medios_pago.sql
-- Descripción: Crea la tabla `medios_pagos` (catálogo CRUD de formas de pago)
--              y adapta `pagos.metodo_pago` de ENUM a VARCHAR(50) para admitir
--              cualquier medio que el administrador registre.
-- Fecha: 2026
-- Módulo: Configuración → Medios de Pago (MedioPagoController / MedioPago)
--
--  Idempotente: se puede ejecutar varias veces.
--
-- Importar:
--   mysql -u root -p condominio_db < database/add_medios_pago.sql
-- (o desde phpMyAdmin / pestaña Importar)

START TRANSACTION;

-- Crear la tabla si aún no existe.
CREATE TABLE IF NOT EXISTS `medios_pagos` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `valor` VARCHAR(50) NOT NULL COMMENT 'Valor slug guardado en pagos.metodo_pago (ej: efectivo)',
    `nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre visible en el select de formas de pago',
    `activa` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = visible en el formulario de pagos',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_medios_pagos_valor` (`valor`),
    KEY `idx_medios_pagos_activa` (`activa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed idempotente: los métodos que ya existían en el catálogo/código.
INSERT INTO `medios_pagos` (`valor`, `nombre`, `activa`) VALUES
    ('efectivo', 'Efectivo', 1),
    ('transferencia', 'Transferencia Bancaria', 1),
    ('pago_movil', 'Pago Móvil', 1),
    ('tarjeta', 'Tarjeta de Crédito/Débito', 1),
    ('deposito', 'Depósito Bancario', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `activa` = VALUES(`activa`);

-- Ampliar pagos.metodo_pago de ENUM a VARCHAR(50) (idempotente).
-- Si la columna ya es VARCHAR con 'varchar' en su tipo, no se toca.
SET @dbname = DATABASE();
SET @tablename = 'pagos';
SET @colname = 'metodo_pago';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE table_schema = @dbname
       AND table_name = @tablename
       AND column_name = @colname
       AND COLUMN_TYPE LIKE '%varchar%') > 0,
    'SELECT 1',
    "ALTER TABLE pagos MODIFY COLUMN metodo_pago VARCHAR(50) NOT NULL DEFAULT 'efectivo'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

COMMIT;