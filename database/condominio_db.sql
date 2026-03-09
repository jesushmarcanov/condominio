-- Base de datos para el Sistema de Gestión de Condominio

CREATE DATABASE IF NOT EXISTS condominio_db;
USE condominio_db;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'resident') NOT NULL,
    telefono VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de residentes (extiende usuarios)
CREATE TABLE residentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE NOT NULL,
    apartamento VARCHAR(10) NOT NULL,
    piso INT NOT NULL,
    torre VARCHAR(50),
    fecha_ingreso DATE,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de pagos
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    residente_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    concepto VARCHAR(100) NOT NULL,
    mes_pago VARCHAR(7) NOT NULL, -- Formato: YYYY-MM
    fecha_pago DATE NOT NULL,
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta', 'deposito') NOT NULL,
    referencia VARCHAR(100),
    estado ENUM('pagado', 'pendiente', 'atrasado') DEFAULT 'pagado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (residente_id) REFERENCES residentes(id) ON DELETE CASCADE
);

-- Tabla de incidencias
CREATE TABLE incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    residente_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    categoria ENUM('agua', 'electricidad', 'gas', 'estructura', 'limpieza', 'seguridad', 'otro') NOT NULL,
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    estado ENUM('pendiente', 'en_proceso', 'resuelta', 'cancelada') DEFAULT 'pendiente',
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion TIMESTAMP NULL,
    administrador_id INT NULL,
    notas_admin TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (residente_id) REFERENCES residentes(id) ON DELETE CASCADE,
    FOREIGN KEY (administrador_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de cuotas de mantenimiento
CREATE TABLE cuotas_mantenimiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mes VARCHAR(7) NOT NULL UNIQUE, -- Formato: YYYY-MM
    monto DECIMAL(10,2) NOT NULL,
    fecha_limite DATE NOT NULL,
    descripcion TEXT,
    estado ENUM('activa', 'vencida') DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de notificaciones
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    leida BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de areas comunes (para futura implementación)
CREATE TABLE areas_comunes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    capacidad INT,
    horario_disponible VARCHAR(100),
    estado ENUM('disponible', 'mantenimiento', 'no_disponible') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de reservas (para futura implementación)
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    area_comun_id INT NOT NULL,
    residente_id INT NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado ENUM('confirmada', 'cancelada', 'completada') DEFAULT 'confirmada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (area_comun_id) REFERENCES areas_comunes(id) ON DELETE CASCADE,
    FOREIGN KEY (residente_id) REFERENCES residentes(id) ON DELETE CASCADE
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password, rol, telefono) VALUES 
('Administrador', 'admin@condominio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '555-0000');

-- Insertar cuotas de mantenimiento de ejemplo
INSERT INTO cuotas_mantenimiento (mes, monto, fecha_limite, descripcion) VALUES 
('2024-01', 1500.00, '2024-01-10', 'Cuota de mantenimiento Enero 2024'),
('2024-02', 1500.00, '2024-02-10', 'Cuota de mantenimiento Febrero 2024'),
('2024-03', 1500.00, '2024-03-10', 'Cuota de mantenimiento Marzo 2024');

-- Crear índices para mejor rendimiento
CREATE INDEX idx_residentes_usuario_id ON residentes(usuario_id);
CREATE INDEX idx_pagos_residente_id ON pagos(residente_id);
CREATE INDEX idx_incidencias_residente_id ON incidencias(residente_id);
CREATE INDEX idx_incidencias_administrador_id ON incidencias(administrador_id);
CREATE INDEX idx_notificaciones_usuario_id ON notificaciones(usuario_id);
CREATE INDEX idx_reservas_area_comun_id ON reservas(area_comun_id);
CREATE INDEX idx_reservas_residente_id ON reservas(residente_id);
