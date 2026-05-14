-- Tabla para registrar eventos de incidencias
-- Esta tabla almacena un historial completo de todas las acciones realizadas sobre las incidencias

CREATE TABLE IF NOT EXISTS `incident_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incident_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` enum('created','status_changed','assigned','updated','deleted','commented') NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `incident_id` (`incident_id`),
  KEY `user_id` (`user_id`),
  KEY `event_type` (`event_type`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `incident_events_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para mejorar el rendimiento de consultas
CREATE INDEX idx_incident_events_incident_created ON incident_events(incident_id, created_at DESC);
CREATE INDEX idx_incident_events_user_created ON incident_events(user_id, created_at DESC);

-- Comentarios de la tabla
ALTER TABLE `incident_events` COMMENT = 'Registro de eventos y cambios en incidencias para auditoría';
