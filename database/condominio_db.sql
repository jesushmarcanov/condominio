-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: condominio_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `condominio_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `condominio_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `condominio_db`;

--
-- Table structure for table `areas_comunes`
--

DROP TABLE IF EXISTS `areas_comunes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas_comunes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `horario_disponible` varchar(100) DEFAULT NULL,
  `estado` enum('disponible','mantenimiento','no_disponible') DEFAULT 'disponible',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas_comunes`
--

LOCK TABLES `areas_comunes` WRITE;
/*!40000 ALTER TABLE `areas_comunes` DISABLE KEYS */;
INSERT INTO `areas_comunes` VALUES (1,'Piscina','Area de la piscina del edificio, incluye un caney',25,'Viernes a Domingo 9:00 AM - 6:00 PM','disponible','2026-09-04 15:49:17','2026-09-04 15:49:17');
/*!40000 ALTER TABLE `areas_comunes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `actualizado_por` int(11) DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`),
  KEY `actualizado_por` (`actualizado_por`),
  CONSTRAINT `configuracion_ibfk_1` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES (1,'moneda_base','USD','Moneda base de almacenamiento (USD o VES)',1,'2026-09-05 01:05:31'),(2,'tasa_cambio','807.39','Tasa de cambio en Bs por 1 USD (BCV)',1,'2026-09-05 00:48:49'),(3,'tasa_fecha','2026-09-04','Fecha de vigencia de la tasa',1,'2026-09-05 00:48:44'),(4,'tasa_origen','manual','Origen de la tasa: bcv o manual',1,'2026-09-05 00:48:49'),(5,'multimoneda_activo','1','Mostrar conversi├│n a la segunda moneda (1=activado)',1,'2026-09-05 00:59:51');
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_bancarias`
--

DROP TABLE IF EXISTS `cuentas_bancarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuentas_bancarias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banco` varchar(60) NOT NULL,
  `tipo` enum('corriente','ahorro') NOT NULL DEFAULT 'corriente',
  `numero_cuenta` varchar(30) NOT NULL,
  `titular` varchar(100) NOT NULL,
  `pago_movil_telefono` varchar(20) DEFAULT NULL COMMENT 'Tel├®fono Pago M├│vil BCV',
  `pago_movil_cedula` varchar(20) DEFAULT NULL COMMENT 'C├®dula/RIF del Pago M├│vil BCV',
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activa` (`activa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_bancarias`
--

LOCK TABLES `cuentas_bancarias` WRITE;
/*!40000 ALTER TABLE `cuentas_bancarias` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_bancarias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuotas_mantenimiento`
--

DROP TABLE IF EXISTS `cuotas_mantenimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuotas_mantenimiento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mes` varchar(7) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_limite` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activa','vencida') DEFAULT 'activa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mes` (`mes`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuotas_mantenimiento`
--

LOCK TABLES `cuotas_mantenimiento` WRITE;
/*!40000 ALTER TABLE `cuotas_mantenimiento` DISABLE KEYS */;
INSERT INTO `cuotas_mantenimiento` VALUES (1,'2024-01',1500.00,'2024-01-10','Cuota de mantenimiento Enero 2024','activa','2026-02-10 17:41:14','2026-09-05 00:40:26'),(2,'2024-02',1500.00,'2024-02-10','Cuota de mantenimiento Febrero 2024','activa','2026-02-10 17:41:14','2026-09-05 00:40:26'),(3,'2024-03',1500.00,'2024-03-10','Cuota de mantenimiento Marzo 2024','activa','2026-02-10 17:41:14','2026-09-05 00:40:26');
/*!40000 ALTER TABLE `cuotas_mantenimiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `declaraciones_pago`
--

DROP TABLE IF EXISTS `declaraciones_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `declaraciones_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pago_id` int(11) NOT NULL,
  `metodo` enum('pago_movil','transferencia') NOT NULL,
  `banco_origen` varchar(60) NOT NULL,
  `referencia_bancaria` varchar(30) NOT NULL,
  `monto_declarado` decimal(10,2) NOT NULL,
  `moneda` enum('USD','VES') DEFAULT 'USD',
  `monto_moneda_original` decimal(10,2) DEFAULT NULL,
  `fecha_operacion` date NOT NULL,
  `hora_operacion` time NOT NULL,
  `estado` enum('pendiente','confirmada','rechazada') NOT NULL DEFAULT 'pendiente',
  `declarado_por` int(11) DEFAULT NULL,
  `revisado_por` int(11) DEFAULT NULL,
  `notas_admin` text DEFAULT NULL,
  `fecha_declaracion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `revisado_por` (`revisado_por`),
  KEY `idx_pago_id` (`pago_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_declarado_por` (`declarado_por`),
  KEY `idx_fecha_declaracion` (`fecha_declaracion`),
  CONSTRAINT `declaraciones_pago_ibfk_1` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declaraciones_pago_ibfk_2` FOREIGN KEY (`declarado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `declaraciones_pago_ibfk_3` FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `declaraciones_pago`
--

LOCK TABLES `declaraciones_pago` WRITE;
/*!40000 ALTER TABLE `declaraciones_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `declaraciones_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('success','failure') NOT NULL,
  `error_message` text DEFAULT NULL,
  `duration_ms` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_recipient` (`recipient`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
INSERT INTO `email_logs` VALUES (1,'jhmarcano@gmail.com','Pago Vencido - Cuota de Mantenimiento','success',NULL,2483.15,'2026-04-16 16:38:33');
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencias`
--

DROP TABLE IF EXISTS `incidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `residente_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `categoria` enum('agua','electricidad','gas','estructura','limpieza','seguridad','otro') NOT NULL,
  `prioridad` enum('baja','media','alta') DEFAULT 'media',
  `estado` enum('pendiente','en_proceso','resuelta','cancelada') DEFAULT 'pendiente',
  `fecha_reporte` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  `administrador_id` int(11) DEFAULT NULL,
  `notas_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_incidencias_residente_id` (`residente_id`),
  KEY `idx_incidencias_administrador_id` (`administrador_id`),
  CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`residente_id`) REFERENCES `residentes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incidencias_ibfk_2` FOREIGN KEY (`administrador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencias`
--

LOCK TABLES `incidencias` WRITE;
/*!40000 ALTER TABLE `incidencias` DISABLE KEYS */;
INSERT INTO `incidencias` VALUES (1,1,'Grietas en el pasillo','Se ven una serie de grietas en la pared principal del pasillo','estructura','media','pendiente','2026-03-09 17:28:19',NULL,NULL,NULL,'2026-03-09 19:28:19','2026-03-09 19:28:19'),(2,2,'Huecos abiertos en la pared del pasillo','Se hicieron dos trabajos de tuberia en la pared del pasillo, sin embargo, no se taparon los huecos que dejaron abiertos','estructura','alta','pendiente','2026-04-15 17:08:04',NULL,NULL,NULL,'2026-04-15 19:08:04','2026-04-15 19:08:04'),(3,4,'Filtracion en baño','Hay una filtracion en el baño que viene del piso superior','estructura','alta','pendiente','2026-09-04 13:41:56',NULL,NULL,NULL,'2026-09-04 15:41:56','2026-09-04 15:41:56');
/*!40000 ALTER TABLE `incidencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incident_events`
--

DROP TABLE IF EXISTS `incident_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incident_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incident_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` enum('created','status_changed','assigned','updated','deleted','commented') NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `incident_id` (`incident_id`),
  KEY `user_id` (`user_id`),
  KEY `event_type` (`event_type`),
  KEY `created_at` (`created_at`),
  KEY `idx_incident_events_incident_created` (`incident_id`,`created_at`),
  KEY `idx_incident_events_user_created` (`user_id`,`created_at`),
  CONSTRAINT `incident_events_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de eventos y cambios en incidencias para auditoría';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incident_events`
--

LOCK TABLES `incident_events` WRITE;
/*!40000 ALTER TABLE `incident_events` DISABLE KEYS */;
INSERT INTO `incident_events` VALUES (1,2,1,'created','','','Incidencia creada: Huecos abiertos en la pared del pasillo - Prioridad: alta','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:149.0) Gecko/20100101 Firefox/149.0','2026-04-15 19:08:04'),(2,3,6,'created','','','Incidencia creada: Filtracion en baño - Prioridad: alta','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 15:41:56');
/*!40000 ALTER TABLE `incident_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `late_fee_history`
--

DROP TABLE IF EXISTS `late_fee_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `late_fee_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pago_id` int(11) NOT NULL,
  `regla_mora_id` int(11) DEFAULT NULL,
  `monto_calculado` decimal(10,2) NOT NULL,
  `monto_aplicado` decimal(10,2) NOT NULL,
  `dias_atraso` int(11) NOT NULL,
  `tipo_operacion` enum('calculo_automatico','ajuste_manual','eliminacion') NOT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que realizó ajuste manual',
  `justificacion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `regla_mora_id` (`regla_mora_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_pago_id` (`pago_id`),
  KEY `idx_tipo_operacion` (`tipo_operacion`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `late_fee_history_ibfk_1` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `late_fee_history_ibfk_2` FOREIGN KEY (`regla_mora_id`) REFERENCES `late_fee_rules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `late_fee_history_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `late_fee_history`
--

LOCK TABLES `late_fee_history` WRITE;
/*!40000 ALTER TABLE `late_fee_history` DISABLE KEYS */;
INSERT INTO `late_fee_history` VALUES (2,4,NULL,25.00,25.00,67,'calculo_automatico',NULL,NULL,'2026-04-16 23:56:27'),(3,2,NULL,75.00,75.00,52,'calculo_automatico',NULL,NULL,'2026-04-16 23:56:27'),(4,8,NULL,250.00,250.00,37,'calculo_automatico',NULL,NULL,'2026-04-16 23:56:27'),(6,3,NULL,75.00,75.00,22,'calculo_automatico',NULL,NULL,'2026-04-16 23:56:27'),(7,6,NULL,50.00,50.00,7,'calculo_automatico',NULL,NULL,'2026-04-16 23:56:27'),(8,5,NULL,30.49,30.49,7,'calculo_automatico',NULL,NULL,'2026-04-16 23:56:27'),(11,7,NULL,100.00,100.00,4,'calculo_automatico',NULL,NULL,'2026-04-16 23:56:27');
/*!40000 ALTER TABLE `late_fee_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `late_fee_rules`
--

DROP TABLE IF EXISTS `late_fee_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `late_fee_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `dias_gracia` int(11) NOT NULL DEFAULT 0,
  `tipo_recargo` enum('porcentaje','monto_fijo') NOT NULL,
  `valor_recargo` decimal(10,2) NOT NULL,
  `frecuencia` enum('unica','diaria','semanal','mensual') NOT NULL DEFAULT 'unica',
  `tope_maximo` decimal(10,2) DEFAULT NULL,
  `tipo_pago` varchar(50) DEFAULT NULL COMMENT 'NULL = aplica a todos los tipos',
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activa` (`activa`),
  KEY `idx_tipo_pago` (`tipo_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `late_fee_rules`
--

LOCK TABLES `late_fee_rules` WRITE;
/*!40000 ALTER TABLE `late_fee_rules` DISABLE KEYS */;
INSERT INTO `late_fee_rules` VALUES (1,'Mora Estándar - 2% Mensual',5,'porcentaje',2.00,'mensual',NULL,NULL,0,'2026-04-16 22:17:49','2026-04-16 23:56:27');
/*!40000 ALTER TABLE `late_fee_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('info','warning','success','error') DEFAULT 'info',
  `leida` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notificaciones_usuario_id` (`usuario_id`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,1,'Notificación de Prueba','Esta es una notificación de prueba creada por el script test_notifications.php','info',1,'2026-04-07 00:13:35'),(2,2,'Pago Vencido - Fondo de Reserva','Estimado residente, le recordamos que tiene un pago pendiente:\n\nConcepto: Fondo de Reserva\nMonto: $500.00\nMes: 2026-01\nFecha de vencimiento: 2026-02-06\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-07 00:15:50'),(3,2,'Pago Vencido - Cuota de Mantenimiento','Estimado residente, le recordamos que tiene un pago pendiente:\n\nConcepto: Cuota de Mantenimiento\nMonto: $1,500.00\nMes: 2026-02\nFecha de vencimiento: 2026-02-21\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-07 00:15:50'),(4,1,'Notificación de Prueba','Esta es una notificación de prueba creada por el script test_notifications.php','info',1,'2026-04-07 00:16:03'),(5,3,'Nueva Incidencia Registrada','Su incidencia &#039;Huecos abiertos en la pared del pasillo&#039; ha sido registrada exitosamente. Le notificaremos sobre cualquier actualización.','info',0,'2026-04-15 19:08:04'),(6,1,'Nueva Incidencia Reportada','Se ha reportado una nueva incidencia: Huecos abiertos en la pared del pasillo (Prioridad: alta)','info',0,'2026-04-15 19:08:04'),(7,3,'Pago Vencido - mensualidad','Estimado residente, le recordamos que tiene un pago pendiente:\n\nConcepto: mensualidad\nMonto: $609.89\nMes: 2026-04\nFecha de vencimiento: 2026-04-07\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-15 22:50:14'),(8,2,'Recargo por Mora Aplicado - Fondo de Reserva','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: Fondo de Reserva\nMonto original: $500.00\nRecargo por mora: $25.00\nMonto total: $525.00\nFecha de vencimiento: 2026-02-06\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(9,2,'Recargo por Mora Aplicado - Cuota de Mantenimiento','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: Cuota de Mantenimiento\nMonto original: $1,500.00\nRecargo por mora: $75.00\nMonto total: $1,575.00\nFecha de vencimiento: 2026-02-21\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(10,2,'Recargo por Mora Aplicado - Cuota de Mantenimiento - Test 3','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: Cuota de Mantenimiento - Test 3\nMonto original: $5,000.00\nRecargo por mora: $250.00\nMonto total: $5,250.00\nFecha de vencimiento: 2026-03-08\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(11,2,'Recargo por Mora Aplicado - Cuota de Mantenimiento - Test 3','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: Cuota de Mantenimiento - Test 3\nMonto original: $5,000.00\nRecargo por mora: $250.00\nMonto total: $5,250.00\nFecha de vencimiento: 2026-03-08\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(12,2,'Recargo por Mora Aplicado - Cuota de Mantenimiento','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: Cuota de Mantenimiento\nMonto original: $1,500.00\nRecargo por mora: $75.00\nMonto total: $1,575.00\nFecha de vencimiento: 2026-03-23\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(13,2,'Recargo por Mora Aplicado - Cuota de Mantenimiento - Test 1','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: Cuota de Mantenimiento - Test 1\nMonto original: $1,000.00\nRecargo por mora: $50.00\nMonto total: $1,050.00\nFecha de vencimiento: 2026-04-07\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(14,3,'Recargo por Mora Aplicado - mensualidad','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: mensualidad\nMonto original: $609.89\nRecargo por mora: $30.49\nMonto total: $640.38\nFecha de vencimiento: 2026-04-07\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(15,2,'Recargo por Mora Aplicado - Cuota de Mantenimiento - Test 2','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: Cuota de Mantenimiento - Test 2\nMonto original: $2,000.00\nRecargo por mora: $100.00\nMonto total: $2,100.00\nFecha de vencimiento: 2026-04-10\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(16,2,'Recargo por Mora Aplicado - Cuota de Mantenimiento - Test 2','Estimado residente,\n\nSe ha aplicado un recargo por mora a su pago:\n\nConcepto: Cuota de Mantenimiento - Test 2\nMonto original: $2,000.00\nRecargo por mora: $100.00\nMonto total: $2,100.00\nFecha de vencimiento: 2026-04-10\n\nPor favor, regularice su situación a la brevedad.','warning',0,'2026-04-16 23:56:27'),(17,6,'Nueva Incidencia Registrada','Su incidencia &#039;Filtracion en baño&#039; ha sido registrada exitosamente. Le notificaremos sobre cualquier actualización.','info',0,'2026-09-04 15:41:56');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `residente_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `moneda` enum('USD','VES') DEFAULT 'USD',
  `monto_moneda_original` decimal(10,2) DEFAULT NULL,
  `monto_original` decimal(10,2) DEFAULT NULL,
  `monto_mora` decimal(10,2) DEFAULT 0.00,
  `fecha_aplicacion_mora` date DEFAULT NULL,
  `regla_mora_id` int(11) DEFAULT NULL,
  `concepto` varchar(100) NOT NULL,
  `mes_pago` varchar(7) NOT NULL,
  `fecha_pago` date NOT NULL,
  `metodo_pago` enum('efectivo','transferencia','tarjeta','deposito','pago_movil') NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `estado` enum('pagado','pendiente','atrasado') DEFAULT 'pagado',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pagos_residente_id` (`residente_id`),
  KEY `idx_estado_fecha` (`estado`,`fecha_pago`),
  KEY `idx_regla_mora` (`regla_mora_id`),
  CONSTRAINT `fk_pagos_regla_mora` FOREIGN KEY (`regla_mora_id`) REFERENCES `late_fee_rules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`residente_id`) REFERENCES `residentes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (1,1,1500.00,'VES',NULL,1500.00,0.00,NULL,NULL,'Cuota de mantenimiento','2026-02','2026-02-22','efectivo','1111','pagado','2026-02-22 13:59:47','2026-09-05 00:40:20'),(2,1,1500.00,'VES',NULL,1500.00,75.00,'2026-04-16',NULL,'Cuota de Mantenimiento','2026-02','2026-02-21','transferencia','','atrasado','2026-04-07 00:15:38','2026-09-05 00:40:20'),(3,1,1500.00,'VES',NULL,1500.00,75.00,'2026-04-16',NULL,'Cuota de Mantenimiento','2026-03','2026-03-23','transferencia','','atrasado','2026-04-07 00:15:38','2026-09-05 00:40:20'),(4,1,500.00,'VES',NULL,500.00,25.00,'2026-04-16',NULL,'Fondo de Reserva','2026-01','2026-02-06','transferencia','','atrasado','2026-04-07 00:15:38','2026-09-05 00:40:20'),(5,2,609.89,'VES',NULL,609.89,30.49,'2026-04-16',NULL,'mensualidad','2026-04','2026-04-07','efectivo','','atrasado','2026-04-07 17:13:20','2026-09-05 00:40:20'),(6,1,1000.00,'VES',NULL,NULL,50.00,'2026-04-16',NULL,'Cuota de Mantenimiento - Test 1','2026-03','2026-04-07','','','atrasado','2026-04-16 23:56:12','2026-09-05 00:40:20'),(7,1,2000.00,'VES',NULL,NULL,100.00,'2026-04-16',NULL,'Cuota de Mantenimiento - Test 2','2026-03','2026-04-10','','','atrasado','2026-04-16 23:56:12','2026-09-05 00:40:20'),(8,1,5000.00,'VES',NULL,NULL,250.00,'2026-04-16',NULL,'Cuota de Mantenimiento - Test 3','2026-02','2026-03-08','','','atrasado','2026-04-16 23:56:12','2026-09-05 00:40:20'),(13,4,1750.00,'VES',NULL,NULL,0.00,NULL,NULL,'Cuota de mantenimiento','2026-09','2026-09-04','efectivo','','pagado','2026-09-04 23:41:34','2026-09-05 00:40:20'),(15,3,850.00,'USD',NULL,NULL,0.00,NULL,NULL,'mensualidad','2026-09','2026-09-04','transferencia','5555','pendiente','2026-09-05 01:56:29','2026-09-05 01:56:29');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservas`
--

DROP TABLE IF EXISTS `reservas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area_comun_id` int(11) NOT NULL,
  `residente_id` int(11) NOT NULL,
  `fecha_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('confirmada','cancelada','completada') DEFAULT 'confirmada',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reservas_area_comun_id` (`area_comun_id`),
  KEY `idx_reservas_residente_id` (`residente_id`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`area_comun_id`) REFERENCES `areas_comunes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`residente_id`) REFERENCES `residentes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservas`
--

LOCK TABLES `reservas` WRITE;
/*!40000 ALTER TABLE `reservas` DISABLE KEYS */;
INSERT INTO `reservas` VALUES (1,1,4,'2026-09-04','09:00:00','18:00:00','confirmada','2026-09-04 15:50:11','2026-09-04 15:50:11');
/*!40000 ALTER TABLE `reservas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `residentes`
--

DROP TABLE IF EXISTS `residentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `residentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `apartamento` varchar(10) NOT NULL,
  `piso` int(11) NOT NULL,
  `torre` varchar(50) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  KEY `idx_residentes_usuario_id` (`usuario_id`),
  CONSTRAINT `residentes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `residentes`
--

LOCK TABLES `residentes` WRITE;
/*!40000 ALTER TABLE `residentes` DISABLE KEYS */;
INSERT INTO `residentes` VALUES (1,2,'15',1,'Bahia','2026-02-10','activo','2026-02-10 19:16:42','2026-02-10 19:16:42'),(2,3,'65',6,'Bahia','2026-04-07','activo','2026-04-07 17:12:07','2026-04-07 17:12:07'),(3,5,'16',1,'','2026-05-06','activo','2026-05-06 16:12:35','2026-05-06 16:12:35'),(4,6,'64',6,'Bahia','2026-09-04','activo','2026-09-04 15:30:50','2026-09-04 15:30:50');
/*!40000 ALTER TABLE `residentes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','resident') NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','admin@condominio.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','555-0000','2026-02-10 17:41:14','2026-02-10 17:41:14'),(2,'Jesús Hilario Marcano Velásquez','jhmarcano@gmail.com','$2y$10$MSi0x1RHgZC5YUGOsVAra.qkIhuXl6wtx2AYFAzbJdfPxV/C5NLly','resident','584148055174','2026-02-10 19:16:42','2026-02-10 19:16:42'),(3,'Jesus Marcano','jesushmarcanov@gmail.com','$2y$10$CxgIQLrOy1o3GEaZ4Smaj.N/mQzG27G6WnV66MeHN.qbYFOB76spC','resident','0412-9806228','2026-04-07 17:12:07','2026-04-07 17:12:07'),(4,'Pepito Perez','pepito@correo.com','$2y$10$kPGnjrhCqeKGohZFFPpGnu4hyMKAeQ98R.iXZ2p09jM9Axf.sXMnK','resident','','2026-05-06 15:49:20','2026-05-06 15:49:20'),(5,'Pepito Perez','pepitopep@correo.com','$2y$10$jdDXbaIB0xHQubkMHeUhIujhR553MBbO3.kRXo7BV8KRToocIt.w6','resident','','2026-05-06 16:12:35','2026-05-06 16:12:35'),(6,'Jose Garcia','jgarcia@gmail.com','$2y$10$FHtd6MvcHI6YYya1PyXWlOeuWtML6aDihNp85AYBS.8TTmbfhXVHS','resident','+584122971347','2026-09-04 15:30:50','2026-09-04 15:30:50');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'condominio_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-15 20:46:20
