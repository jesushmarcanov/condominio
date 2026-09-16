-- Migration: add_online_payments.sql
-- Descripción: Agrega tablas y modifica catálogos para el sistema de pagos en línea
-- (Pago Móvil BCV y Transferencia Bancaria en Venezuela)
-- Fecha: 2026
-- Requisitos: Roadmap - Integración con pasarelas de pago (pago móvil y transferencia)

START TRANSACTION;

-- 1. Crear tabla de cuentas bancarias del condominio (datos de cobro)
CREATE TABLE IF NOT EXISTS cuentas_bancarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    banco VARCHAR(60) NOT NULL,
    tipo ENUM('corriente', 'ahorro') NOT NULL DEFAULT 'corriente',
    numero_cuenta VARCHAR(30) NOT NULL,
    titular VARCHAR(100) NOT NULL,
    pago_movil_telefono VARCHAR(20) NULL COMMENT 'Teléfono Pago Móvil BCV',
    pago_movil_cedula VARCHAR(20) NULL COMMENT 'Cédula/RIF del Pago Móvil BCV',
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Crear tabla de declaraciones de pago en línea
CREATE TABLE IF NOT EXISTS declaraciones_pago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pago_id INT NOT NULL,
    metodo ENUM('pago_movil', 'transferencia') NOT NULL,
    banco_origen VARCHAR(60) NOT NULL,
    referencia_bancaria VARCHAR(30) NOT NULL,
    monto_declarado DECIMAL(10,2) NOT NULL,
    fecha_operacion DATE NOT NULL,
    hora_operacion TIME NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    declarado_por INT NULL,
    revisado_por INT NULL,
    notas_admin TEXT NULL,
    fecha_declaracion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_revision TIMESTAMP NULL,
    FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE CASCADE,
    FOREIGN KEY (declarado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_pago_id (pago_id),
    INDEX idx_estado (estado),
    INDEX idx_declarado_por (declarado_por),
    INDEX idx_fecha_declaracion (fecha_declaracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Ampliar el ENUM metodo_pago de la tabla pagos para incluir 'pago_movil'
-- (solo si aún no está presente)
SET @dbname = DATABASE();
SET @tablename = 'pagos';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = 'metodo_pago')
      AND (COLUMN_TYPE LIKE '%pago_movil%')
  ) > 0,
  'SELECT 1',
  "ALTER TABLE pagos MODIFY COLUMN metodo_pago ENUM('efectivo','transferencia','tarjeta','deposito','pago_movil') NOT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

COMMIT;

-- Verificación de la migración
SELECT 'Migración completada exitosamente' AS status;
SELECT 'Tablas creadas:' AS info;
SHOW TABLES LIKE 'cuentas_bancarias';
SHOW TABLES LIKE 'declaraciones_pago';
SELECT 'Columna metodo_pago actualizada:' AS info;
SHOW COLUMNS FROM pagos LIKE 'metodo_pago';