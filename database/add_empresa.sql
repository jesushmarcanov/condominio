-- Migración: add_empresa.sql
-- Descripción: Crea (o adapta) la tabla `empresa` con los datos del condominio
--              como empresa (fila única, id = 1).
-- Fecha: 2026
-- Módulo: Datos del Condominio (CompanyController / Empresa)
--
--  Idempotente: se puede ejecutar varias veces.
--  También migra la tabla creada previamente en la demo (con `telefono` y
--  `moneda`) a la estructura que usa el código: `telefono1` / `telefono2`.
--
-- Importar:
--   mysql -u root -p condominio_db < database/add_empresa.sql
-- (o desde phpMyAdmin / pestaña Importar)

START TRANSACTION;

-- Crear la tabla si aún no existe (estructura usada por el nuevo código).
CREATE TABLE IF NOT EXISTS `empresa` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL COMMENT 'Nombre del condominio',
    `rif` VARCHAR(20) DEFAULT NULL COMMENT 'RIF (formato V/J/G-XXXXXXXX-X)',
    `direccion` VARCHAR(255) DEFAULT NULL,
    `telefono1` VARCHAR(30) DEFAULT NULL,
    `telefono2` VARCHAR(30) DEFAULT NULL,
    `email_contacto` VARCHAR(120) DEFAULT NULL,
    `representante_legal` VARCHAR(150) DEFAULT NULL COMMENT 'Representante legal / administrador',
    `horario_admin` VARCHAR(150) DEFAULT NULL COMMENT 'Horario de administración / atención',
    `sitio_web` VARCHAR(150) DEFAULT NULL,
    `logo` VARCHAR(200) DEFAULT NULL COMMENT 'Ruta relativa del logo (public/uploads/condominio/...)',
    `actualizado_por` INT DEFAULT NULL,
    `fecha_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrar la tabla antigua de la demo: `telefono` -> `telefono1`.
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'empresa' AND COLUMN_NAME = 'telefono') > 0
        AND (SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'empresa' AND COLUMN_NAME = 'telefono1') = 0,
        'ALTER TABLE `empresa` CHANGE COLUMN `telefono` `telefono1` VARCHAR(30) DEFAULT NULL',
        'SELECT 1'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar `telefono2` si no existe.
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'empresa' AND COLUMN_NAME = 'telefono2') = 0,
        'ALTER TABLE `empresa` ADD COLUMN `telefono2` VARCHAR(30) DEFAULT NULL AFTER `telefono1`',
        'SELECT 1'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Limpiar posibles datos de prueba con apariencia de código (ej: `<?= ... ?>`).
UPDATE `empresa` SET `nombre` = '' WHERE `nombre` LIKE '<?%' OR `nombre` LIKE '%?>';
UPDATE `empresa` SET `nombre` = '' WHERE `nombre` REGEXP '<[^>]*>';

-- Fila única (id = 1): seed para que el módulo siempre tenga un registro.
INSERT INTO `empresa` (`id`, `nombre`) VALUES (1, '')
ON DUPLICATE KEY UPDATE `id` = `id`;

COMMIT;