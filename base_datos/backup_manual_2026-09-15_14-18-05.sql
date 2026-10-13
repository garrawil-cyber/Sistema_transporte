
-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: localhost    Database: sistema_transporte
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asientos`
--

DROP TABLE IF EXISTS `asientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `viaje_id` int NOT NULL,
  `numero_asiento` varchar(5) NOT NULL,
  `piso` enum('1','2') DEFAULT '1',
  `disponible` tinyint(1) DEFAULT '1',
  `reservado_hasta` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_asiento_viaje` (`viaje_id`,`numero_asiento`),
  CONSTRAINT `asientos_ibfk_1` FOREIGN KEY (`viaje_id`) REFERENCES `viajes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asientos`
--

LOCK TABLES `asientos` WRITE;
/*!40000 ALTER TABLE `asientos` DISABLE KEYS */;
INSERT INTO `asientos` VALUES (1,1,'1A','1',1,NULL),(2,1,'1B','1',1,NULL),(3,1,'1C','1',1,NULL),(4,2,'1A','1',1,NULL),(5,2,'1B','1',1,NULL),(6,2,'1C','1',1,NULL),(7,3,'1A','1',1,NULL),(8,3,'1B','1',1,NULL),(9,3,'1C','1',1,NULL),(10,4,'1','1',1,NULL),(11,4,'2','1',1,NULL),(12,4,'3','1',1,NULL),(13,4,'4','1',1,NULL),(14,4,'5','1',1,NULL),(15,4,'6','1',1,NULL),(16,4,'7','1',1,NULL),(17,4,'8','1',1,NULL),(18,4,'9','1',1,NULL),(19,4,'10','1',0,NULL),(20,4,'11','1',1,NULL),(21,4,'12','1',1,NULL),(22,4,'13','1',1,NULL),(23,4,'14','1',1,NULL),(24,4,'15','1',1,NULL),(25,4,'16','1',1,NULL),(26,4,'17','1',1,NULL),(27,4,'18','1',1,NULL),(28,4,'19','1',1,NULL),(29,4,'20','1',1,NULL),(30,4,'21','1',1,NULL),(31,4,'22','1',1,NULL),(32,4,'23','2',1,NULL),(33,4,'24','2',1,NULL),(34,4,'25','2',1,NULL),(35,4,'26','2',1,NULL),(36,4,'27','2',0,NULL),(37,4,'28','2',1,NULL),(38,4,'29','2',1,NULL),(39,4,'30','2',1,NULL),(40,4,'31','2',1,NULL),(41,4,'32','2',1,NULL),(42,4,'33','2',1,NULL),(43,4,'34','2',1,NULL),(44,4,'35','2',1,NULL),(45,4,'36','2',1,NULL),(46,4,'37','2',1,NULL),(47,4,'38','2',1,NULL),(48,4,'39','2',1,NULL),(49,4,'40','2',1,NULL),(50,4,'41','2',1,NULL),(51,4,'42','2',1,NULL),(52,4,'43','2',1,NULL),(53,4,'44','2',1,NULL);
/*!40000 ALTER TABLE `asientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `modulo` varchar(50) DEFAULT NULL,
  `accion` enum('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','ACCESO','ERROR','EXPORT','IMPRIMIR') DEFAULT NULL,
  `registro_id` int DEFAULT NULL,
  `descripcion` text,
  `valores_anteriores` text,
  `valores_nuevos` text,
  `ip_origen` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `metodo_http` varchar(10) DEFAULT NULL,
  `url_accedida` varchar(255) DEFAULT NULL,
  `duracion_ms` int DEFAULT NULL,
  `fecha_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_auditoria_fecha` (`fecha_hora`),
  KEY `idx_auditoria_accion` (`accion`),
  KEY `idx_auditoria_tabla` (`tabla_afectada`),
  KEY `idx_auditoria_modulo` (`modulo`),
  CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (1,1,NULL,'auth','LOGOUT',NULL,'Cierre de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/auth/logout',NULL,'2026-09-12 17:21:10'),(2,1,NULL,'auth','LOGIN',NULL,'Inicio de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/auth/login',NULL,'2026-09-12 17:21:29'),(3,1,NULL,'auth','LOGOUT',NULL,'Cierre de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/auth/logout',NULL,'2026-09-12 17:38:04'),(4,1,NULL,'auth','LOGIN',NULL,'Inicio de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/auth/login',NULL,'2026-09-12 17:38:35'),(5,1,'clientes','clientes','UPDATE',4,'Actualizó un registro en clientes (ID: 4)','{\"id\":4,\"tipo_documento\":\"DNI\",\"numero_documento\":\"12345688\",\"nombre\":\"Martha\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"121212121\",\"email\":\"martha@gmail.com\",\"direccion\":\"Chiclayo, Lambayeque\",\"created_at\":\"2026-09-12 10:50:51\"}','{\"tipo_documento\":\"DNI\",\"numero_documento\":\"12345688\",\"nombre\":\"Martha Yolanda\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"121212121\",\"email\":\"martha@gmail.com\",\"direccion\":\"Chiclayo, Lambayeque\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/clientes/update/4',NULL,'2026-09-12 17:39:15'),(6,1,'buses','buses','UPDATE',1,'Actualizó un registro en buses (ID: 1)','{\"id\":1,\"placa\":\"ABC-123\",\"marca\":\"Mercedes Benz\",\"modelo\":\"O500\",\"anio\":\"2022\",\"capacidad_total\":44,\"tipo_servicio\":\"SEMICAMA\",\"estado\":\"ACTIVO\",\"created_at\":\"2026-08-19 17:20:47\"}','{\"placa\":\"ABC-123\",\"marca\":\"Mercedes Benz\",\"modelo\":\"O500\",\"anio\":2022,\"capacidad_total\":44,\"tipo_servicio\":\"SEMICAMA\",\"estado\":\"ACTIVO\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/buses/update/1',NULL,'2026-09-12 17:46:55'),(7,1,NULL,'auth','LOGOUT',NULL,'Cierre de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/auth/logout',NULL,'2026-09-12 18:11:48'),(8,1,NULL,'auth','LOGIN',NULL,'Inicio de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/auth/login',NULL,'2026-09-13 10:59:10'),(9,1,'usuarios','usuarios','UPDATE',6,'Actualizó un registro en usuarios (ID: 6)','{\"id\":6,\"rol_id\":5,\"nombre\":\"Ana\",\"apellido\":\"López\",\"email\":\"conductor3@transporte.com\",\"password\":\"[PROTEGIDO]\",\"telefono\":\"987789456\",\"activo\":1,\"ultimo_acceso\":null,\"created_at\":\"2026-08-20 16:51:09\",\"updated_at\":\"2026-08-20 16:51:09\"}','{\"rol_id\":5,\"nombre\":\"Ana\",\"apellido\":\"López\",\"email\":\"conductor3@transporte.com\",\"telefono\":\"987789456\",\"activo\":1}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/usuarios/update/6',NULL,'2026-09-13 10:59:29'),(10,1,'clientes','clientes','DELETE',2,'Eliminó un registro en clientes (ID: 2)','{\"id\":2,\"tipo_documento\":\"DNI\",\"numero_documento\":\"87654321\",\"nombre\":\"María\",\"apellido\":\"Gómez\",\"telefono\":\"987123456\",\"email\":\"maria@email.com\",\"direccion\":null,\"created_at\":\"2026-08-19 17:20:47\"}',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/clientes/delete/2',NULL,'2026-09-13 11:36:28'),(11,1,'clientes','clientes','INSERT',5,'Creó un registro en clientes (ID: 5)',NULL,'{\"tipo_documento\":\"DNI\",\"numero_documento\":\"16631277\",\"nombre\":\"Wilmer\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"914914644\",\"email\":\"garrawil@gmail.com\",\"direccion\":\"Piura\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/clientes/store',NULL,'2026-09-13 12:00:03'),(12,1,'clientes','clientes','UPDATE',5,'Actualizó un registro en clientes (ID: 5)','{\"id\":5,\"tipo_documento\":\"DNI\",\"numero_documento\":\"16631277\",\"nombre\":\"Wilmer\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"914914644\",\"email\":\"garrawil@gmail.com\",\"direccion\":\"Piura\",\"created_at\":\"2026-09-13 12:00:03\"}','{\"tipo_documento\":\"DNI\",\"numero_documento\":\"16631277\",\"nombre\":\"Wilmer\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"914914644\",\"email\":\"garrawil@gmail.com\",\"direccion\":\"Lima\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/encomiendas/create',NULL,'2026-09-13 12:17:46'),(13,1,'clientes','clientes','UPDATE',1,'Actualizó un registro en clientes (ID: 1)','{\"id\":1,\"tipo_documento\":\"DNI\",\"numero_documento\":\"12345678\",\"nombre\":\"Juan\",\"apellido\":\"Pérez\",\"telefono\":\"987654321\",\"email\":\"juan@email.com\",\"direccion\":null,\"created_at\":\"2026-08-19 17:20:47\"}','{\"tipo_documento\":\"DNI\",\"numero_documento\":\"12345678\",\"nombre\":\"Juan\",\"apellido\":\"Pérez\",\"telefono\":\"987654321\",\"email\":\"juan@email.com\",\"direccion\":\"Arequipa\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/encomiendas/create',NULL,'2026-09-13 12:17:46'),(14,1,'backups','backup','INSERT',2,'Creó un registro en backups (ID: 2)',NULL,'{\"nombre_archivo\":\"backup_manual_2026-09-13_14-13-40.sql\",\"tipo\":\"manual\",\"tamano_bytes\":48154,\"ruta\":\"manual\\/backup_manual_2026-09-13_14-13-40.sql\",\"usuario_id\":1,\"estado\":\"COMPLETADO\",\"fecha_creacion\":\"2026-09-13 14:13:41\",\"observaciones\":null}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/backup/crear',NULL,'2026-09-13 14:13:41'),(15,1,'backups','backup','DELETE',1,'Eliminó un registro en backups (ID: 1)','{\"id\":1,\"nombre_archivo\":\"backup_manual_2026-09-12_16-41-54.sql\",\"tipo\":\"manual\",\"tamano_bytes\":38559,\"ruta\":\"manual\\/backup_manual_2026-09-12_16-41-54.sql\",\"usuario_id\":1,\"estado\":\"COMPLETADO\",\"fecha_creacion\":\"2026-09-12 16:41:54\",\"observaciones\":null}',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/backup/eliminar/1',NULL,'2026-09-13 14:13:49'),(16,1,NULL,'auth','LOGIN',NULL,'Inicio de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','POST','/Sistema_transporte/public/auth/login',NULL,'2026-09-13 14:37:44'),(17,1,'encomiendas','encomiendas','UPDATE',1,'Cambió estado de GUI-20260001 a ENTREGADO','{\"estado\":\"REGISTRADO\"}','{\"estado\":\"ENTREGADO\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/encomiendas/updateStatus/1?estado=ENTREGADO',NULL,'2026-09-13 14:42:01'),(18,1,'rutas','rutas','INSERT',7,'Creó un registro en rutas (ID: 7)',NULL,'{\"origen\":\"Chiclayo\",\"destino\":\"Trujillo\",\"distancia_km\":\"420\",\"duracion_estimada\":\"00:00:00\",\"precio_base\":\"45\",\"activo\":1}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/rutas/store',NULL,'2026-09-13 14:55:32'),(19,1,'rutas','rutas','UPDATE',7,'Actualizó un registro en rutas (ID: 7)','{\"id\":7,\"origen\":\"Chiclayo\",\"destino\":\"Trujillo\",\"distancia_km\":\"420.00\",\"duracion_estimada\":\"00:00:00\",\"precio_base\":\"45.00\",\"activo\":1,\"created_at\":\"2026-09-13 14:55:32\"}','{\"origen\":\"Chiclayo\",\"destino\":\"Trujillo\",\"distancia_km\":\"420.00\",\"duracion_estimada\":\"00:00:00\",\"precio_base\":\"45.00\",\"activo\":1}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/rutas/update/7',NULL,'2026-09-13 14:58:39'),(20,1,'rutas','rutas','DELETE',7,'Eliminó un registro en rutas (ID: 7)','{\"id\":7,\"origen\":\"Chiclayo\",\"destino\":\"Trujillo\",\"distancia_km\":\"420.00\",\"duracion_estimada\":\"00:00:00\",\"precio_base\":\"45.00\",\"activo\":1,\"created_at\":\"2026-09-13 14:55:32\"}',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/rutas/delete/7',NULL,'2026-09-13 14:58:43'),(21,1,'rutas','rutas','INSERT',8,'Creó un registro en rutas (ID: 8)',NULL,'{\"origen\":\"chiclayo\",\"destino\":\"Trujillo\",\"distancia_km\":\"260\",\"duracion_estimada\":\"04:00:00\",\"precio_base\":\"45\",\"activo\":1}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/rutas/store',NULL,'2026-09-13 14:59:56'),(22,1,'rutas','rutas','UPDATE',8,'Actualizó un registro en rutas (ID: 8)','{\"id\":8,\"origen\":\"chiclayo\",\"destino\":\"Trujillo\",\"distancia_km\":\"260.00\",\"duracion_estimada\":\"04:00:00\",\"precio_base\":\"45.00\",\"activo\":1,\"created_at\":\"2026-09-13 14:59:56\"}','{\"origen\":\"chiclayo\",\"destino\":\"Trujillo\",\"distancia_km\":\"280.00\",\"duracion_estimada\":\"04:00:00\",\"precio_base\":\"45.00\",\"activo\":1}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/rutas/update/8',NULL,'2026-09-13 15:03:02'),(23,1,NULL,'auth','LOGIN',NULL,'Inicio de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/auth/login',NULL,'2026-09-15 09:39:03'),(24,1,'clientes','clientes','UPDATE',4,'Actualizó un registro en clientes (ID: 4)','{\"id\":4,\"tipo_documento\":\"DNI\",\"numero_documento\":\"12345688\",\"nombre\":\"Martha Yolanda\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"121212121\",\"email\":\"martha@gmail.com\",\"direccion\":\"Chiclayo, Lambayeque\",\"created_at\":\"2026-09-12 10:50:51\"}','{\"tipo_documento\":\"DNI\",\"numero_documento\":\"12345688\",\"nombre\":\"Martha Yolanda\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"121212121\",\"email\":\"martha@gmail.com\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/giros/create',NULL,'2026-09-15 09:52:16'),(25,1,'clientes','clientes','UPDATE',3,'Actualizó un registro en clientes (ID: 3)','{\"id\":3,\"tipo_documento\":\"CE\",\"numero_documento\":\"E001234\",\"nombre\":\"Carlos\",\"apellido\":\"López\",\"telefono\":\"998877665\",\"email\":\"carlos@email.com\",\"direccion\":null,\"created_at\":\"2026-08-19 17:20:47\"}','{\"tipo_documento\":\"DNI\",\"numero_documento\":\"E001234\",\"nombre\":\"Carlos\",\"apellido\":\"López\",\"telefono\":\"998877665\",\"email\":\"carlos@email.com\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/giros/create',NULL,'2026-09-15 09:52:16'),(26,1,'giros','giros','EXPORT',1,'Envió comprobante del giro GIR-260915-0001 por email a garrawil@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/giros/enviarEmail/1',NULL,'2026-09-15 10:04:55'),(27,1,'giros','giros','EXPORT',1,'Envió comprobante del giro GIR-260915-0001 por email a garrawil@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/giros/enviarEmail/1',NULL,'2026-09-15 10:07:54'),(28,1,'backups','backup','DELETE',2,'Eliminó un registro en backups (ID: 2)','{\"id\":2,\"nombre_archivo\":\"backup_manual_2026-09-13_14-13-40.sql\",\"tipo\":\"manual\",\"tamano_bytes\":48154,\"ruta\":\"manual\\/backup_manual_2026-09-13_14-13-40.sql\",\"usuario_id\":1,\"estado\":\"COMPLETADO\",\"fecha_creacion\":\"2026-09-13 14:13:41\",\"observaciones\":null}',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/backup/eliminar/2',NULL,'2026-09-15 10:50:35'),(29,1,'backups','backup','INSERT',3,'Creó un registro en backups (ID: 3)',NULL,'{\"nombre_archivo\":\"backup_manual_2026-09-15_10-50-38.sql\",\"tipo\":\"manual\",\"tamano_bytes\":57003,\"ruta\":\"manual\\/backup_manual_2026-09-15_10-50-38.sql\",\"usuario_id\":1,\"estado\":\"COMPLETADO\",\"fecha_creacion\":\"2026-09-15 10:50:39\",\"observaciones\":null}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/backup/crear',NULL,'2026-09-15 10:50:39'),(30,1,'clientes','clientes','UPDATE',5,'Actualizó un registro en clientes (ID: 5)','{\"id\":5,\"tipo_documento\":\"DNI\",\"numero_documento\":\"16631277\",\"nombre\":\"Wilmer\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"914914644\",\"email\":\"garrawil@gmail.com\",\"direccion\":\"Lima\",\"created_at\":\"2026-09-13 12:00:03\"}','{\"tipo_documento\":\"DNI\",\"numero_documento\":\"16631277\",\"nombre\":\"Wilmer\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"914914644\",\"email\":\"garrawil@gmail.com\",\"direccion\":\"\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/ventas/create',NULL,'2026-09-15 11:15:02'),(31,1,'comprobantes_electronicos','facturacion','INSERT',1,'Creó un registro en comprobantes_electronicos (ID: 1)',NULL,'{\"venta_id\":\"1\",\"tipo\":\"BOLETA\",\"serie\":\"B001\",\"correlativo\":\"00000001\",\"numero_completo\":\"B001-00000001\",\"cliente_tipo_doc\":\"DNI\",\"cliente_numero_doc\":\"16631277\",\"cliente_nombre\":\"Wilmer Garcia Ramirez\",\"subtotal\":67.8,\"igv\":12.2,\"total\":80,\"moneda\":\"PEN\",\"xml\":\"<?xml version=\\\"1.0\\\" encoding=\\\"utf-8\\\"?><Invoice xmlns=\\\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\\\" xmlns:cac=\\\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\\\" xmlns:cbc=\\\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\\\" xmlns:ds=\\\"http:\\/\\/www.w3.org\\/2000\\/09\\/xmldsig#\\\" xmlns:ext=\\\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\\\"><ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent\\/><\\/ext:UBLExtension><\\/ext:UBLExtensions><cbc:UBLVersionID>2.1<\\/cbc:UBLVersionID><cbc:CustomizationID>2.0<\\/cbc:CustomizationID><cbc:ID>B001-1<\\/cbc:ID><cbc:IssueDate>2026-09-15<\\/cbc:IssueDate><cbc:IssueTime>11:15:21<\\/cbc:IssueTime><cbc:InvoiceTypeCode listID=\\\"0101\\\">03<\\/cbc:InvoiceTypeCode><cbc:Note languageLocaleID=\\\"1000\\\"><![CDATA[SON OCHENTA CON 00\\/100 SOLES]]><\\/cbc:Note><cbc:DocumentCurrencyCode>PEN<\\/cbc:DocumentCurrencyCode><cac:Signature><cbc:ID>SIGN20561234567<\\/cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>20561234567<\\/cbc:ID><\\/cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[MI CAUTIVO DE AYABACA]]><\\/cbc:Name><\\/cac:PartyName><\\/cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#GREENTER-SIGN<\\/cbc:URI><\\/cac:ExternalReference><\\/cac:DigitalSignatureAttachment><\\/cac:Signature><cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\\\"6\\\">20561234567<\\/cbc:ID><\\/cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[MI CAUTIVO DE AYABACA]]><\\/cbc:Name><\\/cac:PartyName><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[MI CAUTIVO DE AYABACA]]><\\/cbc:RegistrationName><cac:RegistrationAddress><cbc:ID>200101<\\/cbc:ID><cbc:AddressTypeCode>0000<\\/cbc:AddressTypeCode><cbc:CitySubdivisionName>CENTRO<\\/cbc:CitySubdivisionName><cbc:CityName>AYABACA<\\/cbc:CityName><cbc:CountrySubentity>PIURA<\\/cbc:CountrySubentity><cbc:District>AYABACA<\\/cbc:District><cac:AddressLine><cbc:Line><![CDATA[AV. 28 DE JULIO NRO. 123]]><\\/cbc:Line><\\/cac:AddressLine><cac:Country><cbc:IdentificationCode>PE<\\/cbc:IdentificationCode><\\/cac:Country><\\/cac:RegistrationAddress><\\/cac:PartyLegalEntity><\\/cac:Party><\\/cac:AccountingSupplierParty><cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\\\"1\\\">16631277<\\/cbc:ID><\\/cac:PartyIdentification><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[Wilmer Garcia Ramirez]]><\\/cbc:RegistrationName><\\/cac:PartyLegalEntity><cac:Contact><cbc:ElectronicMail>garrawil@gmail.com<\\/cbc:ElectronicMail><\\/cac:Contact><\\/cac:Party><\\/cac:AccountingCustomerParty><cac:PaymentTerms><cbc:ID>FormaPago<\\/cbc:ID><cbc:PaymentMeansID>Contado<\\/cbc:PaymentMeansID><\\/cac:PaymentTerms><cac:TaxTotal><cbc:TaxAmount currencyID=\\\"PEN\\\">12.20<\\/cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\\\"PEN\\\">67.80<\\/cbc:TaxableAmount><cbc:TaxAmount currencyID=\\\"PEN\\\">12.20<\\/cbc:TaxAmount><cac:TaxCategory><cac:TaxScheme><cbc:ID>1000<\\/cbc:ID><cbc:Name>IGV<\\/cbc:Name><cbc:TaxTypeCode>VAT<\\/cbc:TaxTypeCode><\\/cac:TaxScheme><\\/cac:TaxCategory><\\/cac:TaxSubtotal><\\/cac:TaxTotal><cac:LegalMonetaryTotal><cbc:LineExtensionAmount currencyID=\\\"PEN\\\">67.80<\\/cbc:LineExtensionAmount><cbc:TaxInclusiveAmount currencyID=\\\"PEN\\\">80.00<\\/cbc:TaxInclusiveAmount><cbc:PayableAmount currencyID=\\\"PEN\\\">80.00<\\/cbc:PayableAmount><\\/cac:LegalMonetaryTotal><cac:InvoiceLine><cbc:ID>1<\\/cbc:ID><cbc:InvoicedQuantity unitCode=\\\"NIU\\\">1<\\/cbc:InvoicedQuantity><cbc:LineExtensionAmount currencyID=\\\"PEN\\\">67.80<\\/cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID=\\\"PEN\\\">80<\\/cbc:PriceAmount><cbc:PriceTypeCode>01<\\/cbc:PriceTypeCode><\\/cac:AlternativeConditionPrice><\\/cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID=\\\"PEN\\\">12.20<\\/cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\\\"PEN\\\">67.80<\\/cbc:TaxableAmount><cbc:TaxAmount currencyID=\\\"PEN\\\">12.20<\\/cbc:TaxAmount><cac:TaxCategory><cbc:Percent>18<\\/cbc:Percent><cbc:TaxExemptionReasonCode>10<\\/cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID>1000<\\/cbc:ID><cbc:Name>IGV<\\/cbc:Name><cbc:TaxTypeCode>VAT<\\/cbc:TaxTypeCode><\\/cac:TaxScheme><\\/cac:TaxCategory><\\/cac:TaxSubtotal><\\/cac:TaxTotal><cac:Item><cbc:Description><![CDATA[Servicio de transporte: Arequipa → Cusco - Asiento 27]]><\\/cbc:Description><cac:SellersItemIdentification><cbc:ID>P001<\\/cbc:ID><\\/cac:SellersItemIdentification><\\/cac:Item><cac:Price><cbc:PriceAmount currencyID=\\\"PEN\\\">67.8<\\/cbc:PriceAmount><\\/cac:Price><\\/cac:InvoiceLine><\\/Invoice>\",\"cdr\":null,\"cdr_code\":\"SIMULADO\",\"cdr_description\":\"Comprobante generado en MODO SIMULACIÓN. No fue enviado a SUNAT.\",\"estado\":\"SIMULADO\",\"fecha_envio\":\"2026-09-15 11:15:24\",\"usuario_id\":1}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/factura/emitir/1',NULL,'2026-09-15 11:15:24'),(32,1,'clientes','clientes','UPDATE',5,'Actualizó un registro en clientes (ID: 5)','{\"id\":5,\"tipo_documento\":\"DNI\",\"numero_documento\":\"16631277\",\"nombre\":\"Wilmer\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"914914644\",\"email\":\"garrawil@gmail.com\",\"direccion\":\"\",\"created_at\":\"2026-09-13 12:00:03\"}','{\"tipo_documento\":\"DNI\",\"numero_documento\":\"16631277\",\"nombre\":\"Wilmer\",\"apellido\":\"Garcia Ramirez\",\"telefono\":\"914914644\",\"email\":\"garrawil@gmail.com\",\"direccion\":\"\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/ventas/create',NULL,'2026-09-15 11:17:41'),(33,1,NULL,'auth','LOGOUT',NULL,'Cierre de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/auth/logout',NULL,'2026-09-15 13:48:50'),(34,1,NULL,'auth','LOGIN',NULL,'Inicio de sesión: admin@gmail.com',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','POST','/Sistema_transporte/public/auth/login',NULL,'2026-09-15 13:49:08'),(35,1,'backups','backup','DELETE',3,'Eliminó un registro en backups (ID: 3)','{\"id\":3,\"nombre_archivo\":\"backup_manual_2026-09-15_10-50-38.sql\",\"tipo\":\"manual\",\"tamano_bytes\":57003,\"ruta\":\"manual\\/backup_manual_2026-09-15_10-50-38.sql\",\"usuario_id\":1,\"estado\":\"COMPLETADO\",\"fecha_creacion\":\"2026-09-15 10:50:39\",\"observaciones\":null}',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','GET','/Sistema_transporte/public/backup/eliminar/3',NULL,'2026-09-15 14:18:02');
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria_sesiones`
--

DROP TABLE IF EXISTS `auditoria_sesiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_sesiones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha_login` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_logout` datetime DEFAULT NULL,
  `duracion_minutos` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_sesion_usuario` (`usuario_id`),
  KEY `idx_sesion_activo` (`activo`),
  CONSTRAINT `auditoria_sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_sesiones`
--

LOCK TABLES `auditoria_sesiones` WRITE;
/*!40000 ALTER TABLE `auditoria_sesiones` DISABLE KEYS */;
INSERT INTO `auditoria_sesiones` VALUES (1,1,'nf3gefqvqfmnpvio2ugo83h1o6','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','2026-09-12 17:21:29','2026-09-12 17:38:04',16,0),(2,1,'nf3gefqvqfmnpvio2ugo83h1o6','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','2026-09-12 17:38:35','2026-09-12 18:11:48',33,0),(3,1,'71vop36jd4893980ds4dirosbs','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','2026-09-13 10:59:10',NULL,NULL,1),(4,1,'sv1lqa8r6ql22ajqu8tp6sp6pu','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-13 14:37:44',NULL,NULL,1),(5,1,'165dqqmo9u47pauq835qeou8t0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','2026-09-15 09:39:03','2026-09-15 13:48:50',249,0),(6,1,'165dqqmo9u47pauq835qeou8t0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','2026-09-15 13:49:08',NULL,NULL,1);
/*!40000 ALTER TABLE `auditoria_sesiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_archivo` varchar(255) NOT NULL,
  `tipo` enum('manual','automatico') DEFAULT 'manual',
  `tamano_bytes` bigint DEFAULT '0',
  `ruta` varchar(500) NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `estado` enum('COMPLETADO','ERROR') DEFAULT 'COMPLETADO',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `observaciones` text,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_backup_tipo` (`tipo`),
  KEY `idx_backup_fecha` (`fecha_creacion`),
  CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buses`
--

DROP TABLE IF EXISTS `buses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `placa` varchar(10) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `anio` year DEFAULT NULL,
  `capacidad_total` int NOT NULL,
  `tipo_servicio` enum('ECONOMICO','SEMICAMA','CAMA') DEFAULT 'ECONOMICO',
  `estado` enum('ACTIVO','MANTENIMIENTO','BAJA') DEFAULT 'ACTIVO',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `placa` (`placa`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buses`
--

LOCK TABLES `buses` WRITE;
/*!40000 ALTER TABLE `buses` DISABLE KEYS */;
INSERT INTO `buses` VALUES (1,'ABC-123','Mercedes Benz','O500',2022,44,'SEMICAMA','ACTIVO','2026-08-19 22:20:47'),(2,'DEF-456','Scania','K320',2023,48,'CAMA','ACTIVO','2026-08-19 22:20:47'),(3,'GHI-789','Volvo','9800',2021,40,'ECONOMICO','ACTIVO','2026-08-19 22:20:47');
/*!40000 ALTER TABLE `buses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caja_movimientos`
--

DROP TABLE IF EXISTS `caja_movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caja_movimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `caja_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `tipo` enum('INGRESO','EGRESO') NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO',
  `referencia` varchar(100) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_mov_caja` (`caja_id`),
  KEY `idx_mov_tipo` (`tipo`),
  CONSTRAINT `caja_movimientos_ibfk_1` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `caja_movimientos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caja_movimientos`
--

LOCK TABLES `caja_movimientos` WRITE;
/*!40000 ALTER TABLE `caja_movimientos` DISABLE KEYS */;
INSERT INTO `caja_movimientos` VALUES (1,2,1,'EGRESO','refrigerio',12.00,'EFECTIVO','Recibo Nro 104','2026-09-13 13:49:01'),(2,2,1,'EGRESO','Pago movilidad',6.00,'EFECTIVO','Recibo 112','2026-09-13 14:08:32');
/*!40000 ALTER TABLE `caja_movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cajas`
--

DROP TABLE IF EXISTS `cajas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cajas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `fecha_apertura` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_inicial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_final_sistema` decimal(10,2) DEFAULT NULL,
  `monto_final_real` decimal(10,2) DEFAULT NULL,
  `diferencia` decimal(10,2) DEFAULT NULL,
  `total_ventas_efectivo` decimal(10,2) DEFAULT '0.00',
  `total_ventas_tarjeta` decimal(10,2) DEFAULT '0.00',
  `total_ventas_transferencia` decimal(10,2) DEFAULT '0.00',
  `total_encomiendas` decimal(10,2) DEFAULT '0.00',
  `total_giros` decimal(10,2) DEFAULT '0.00',
  `total_ingresos` decimal(10,2) DEFAULT '0.00',
  `total_egresos` decimal(10,2) DEFAULT '0.00',
  `total_ventas` int DEFAULT '0',
  `estado` enum('ABIERTA','CERRADA') DEFAULT 'ABIERTA',
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_caja_usuario` (`usuario_id`),
  KEY `idx_caja_estado` (`estado`),
  KEY `idx_caja_fecha` (`fecha_apertura`),
  CONSTRAINT `cajas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cajas`
--

LOCK TABLES `cajas` WRITE;
/*!40000 ALTER TABLE `cajas` DISABLE KEYS */;
INSERT INTO `cajas` VALUES (1,1,'2026-09-10 17:10:10','2026-09-13 13:45:51',105.00,119.00,120.00,1.00,0.00,0.00,0.00,14.00,0.00,0.00,0.00,0,'CERRADA','','2026-09-10 22:10:10'),(2,1,'2026-09-13 13:46:31','2026-09-15 13:19:00',80.00,232.50,232.00,-0.50,160.00,0.00,0.00,0.00,10.50,0.00,18.00,2,'CERRADA','','2026-09-13 18:46:31'),(3,1,'2026-09-15 13:50:36',NULL,155.00,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,'ABIERTA',NULL,'2026-09-15 18:50:36');
/*!40000 ALTER TABLE `cajas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_documento` enum('DNI','CE','PASAPORTE') DEFAULT 'DNI',
  `numero_documento` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `direccion` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_documento` (`numero_documento`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'DNI','12345678','Juan','Pérez','987654321','juan@email.com','Arequipa','2026-08-19 22:20:47'),(3,'DNI','E001234','Carlos','López','998877665','carlos@email.com',NULL,'2026-08-19 22:20:47'),(4,'DNI','12345688','Martha Yolanda','Garcia Ramirez','121212121','martha@gmail.com','Chiclayo, Lambayeque','2026-09-12 15:50:51'),(5,'DNI','16631277','Wilmer','Garcia Ramirez','914914644','garrawil@gmail.com','','2026-09-13 17:00:03');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `combustible_cargas`
--

DROP TABLE IF EXISTS `combustible_cargas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `combustible_cargas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bus_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `conductor_id` int DEFAULT NULL,
  `viaje_id` int DEFAULT NULL,
  `fecha_carga` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_combustible` enum('DIESEL','GASOLINA','GNV','GAS') DEFAULT 'DIESEL',
  `cantidad_galones` decimal(10,2) NOT NULL,
  `precio_galon` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `kilometraje_actual` int DEFAULT NULL,
  `kilometraje_anterior` int DEFAULT NULL,
  `kilometros_recorridos` int DEFAULT NULL,
  `rendimiento_km_galon` decimal(10,2) DEFAULT NULL,
  `estacion_servicio` varchar(150) DEFAULT NULL,
  `numero_factura` varchar(50) DEFAULT NULL,
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `conductor_id` (`conductor_id`),
  KEY `viaje_id` (`viaje_id`),
  KEY `idx_carga_bus` (`bus_id`),
  KEY `idx_carga_fecha` (`fecha_carga`),
  CONSTRAINT `combustible_cargas_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `combustible_cargas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `combustible_cargas_ibfk_3` FOREIGN KEY (`conductor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `combustible_cargas_ibfk_4` FOREIGN KEY (`viaje_id`) REFERENCES `viajes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `combustible_cargas`
--

LOCK TABLES `combustible_cargas` WRITE;
/*!40000 ALTER TABLE `combustible_cargas` DISABLE KEYS */;
/*!40000 ALTER TABLE `combustible_cargas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comprobantes_electronicos`
--

DROP TABLE IF EXISTS `comprobantes_electronicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comprobantes_electronicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `tipo` enum('FACTURA','BOLETA','NOTA_CREDITO','NOTA_DEBITO') DEFAULT 'BOLETA',
  `serie` varchar(4) NOT NULL,
  `correlativo` varchar(8) NOT NULL,
  `numero_completo` varchar(20) NOT NULL,
  `cliente_tipo_doc` varchar(2) NOT NULL,
  `cliente_numero_doc` varchar(20) NOT NULL,
  `cliente_nombre` varchar(200) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `igv` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `moneda` varchar(3) DEFAULT 'PEN',
  `xml` longtext,
  `xml_firmado` longtext,
  `cdr` longtext,
  `cdr_code` varchar(10) DEFAULT NULL,
  `cdr_description` text,
  `hash_cpe` varchar(100) DEFAULT NULL,
  `estado` enum('PENDIENTE','ACEPTADO','RECHAZADO','ANULADO','SIMULADO') DEFAULT 'PENDIENTE',
  `fecha_emision` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_envio` datetime DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comprobante` (`serie`,`correlativo`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_cpe_venta` (`venta_id`),
  KEY `idx_cpe_estado` (`estado`),
  KEY `idx_cpe_fecha` (`fecha_emision`),
  CONSTRAINT `comprobantes_electronicos_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas_pasajes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comprobantes_electronicos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comprobantes_electronicos`
--

LOCK TABLES `comprobantes_electronicos` WRITE;
/*!40000 ALTER TABLE `comprobantes_electronicos` DISABLE KEYS */;
INSERT INTO `comprobantes_electronicos` VALUES (1,1,'BOLETA','B001','00000001','B001-00000001','DN','16631277','Wilmer Garcia Ramirez',67.80,12.20,80.00,'PEN','<?xml version=\"1.0\" encoding=\"utf-8\"?><Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\" xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\" xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\" xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:ext=\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\"><ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions><cbc:UBLVersionID>2.1</cbc:UBLVersionID><cbc:CustomizationID>2.0</cbc:CustomizationID><cbc:ID>B001-1</cbc:ID><cbc:IssueDate>2026-09-15</cbc:IssueDate><cbc:IssueTime>11:15:21</cbc:IssueTime><cbc:InvoiceTypeCode listID=\"0101\">03</cbc:InvoiceTypeCode><cbc:Note languageLocaleID=\"1000\"><![CDATA[SON OCHENTA CON 00/100 SOLES]]></cbc:Note><cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode><cac:Signature><cbc:ID>SIGN20561234567</cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>20561234567</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[MI CAUTIVO DE AYABACA]]></cbc:Name></cac:PartyName></cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#GREENTER-SIGN</cbc:URI></cac:ExternalReference></cac:DigitalSignatureAttachment></cac:Signature><cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"6\">20561234567</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[MI CAUTIVO DE AYABACA]]></cbc:Name></cac:PartyName><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[MI CAUTIVO DE AYABACA]]></cbc:RegistrationName><cac:RegistrationAddress><cbc:ID>200101</cbc:ID><cbc:AddressTypeCode>0000</cbc:AddressTypeCode><cbc:CitySubdivisionName>CENTRO</cbc:CitySubdivisionName><cbc:CityName>AYABACA</cbc:CityName><cbc:CountrySubentity>PIURA</cbc:CountrySubentity><cbc:District>AYABACA</cbc:District><cac:AddressLine><cbc:Line><![CDATA[AV. 28 DE JULIO NRO. 123]]></cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode>PE</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty><cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"1\">16631277</cbc:ID></cac:PartyIdentification><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[Wilmer Garcia Ramirez]]></cbc:RegistrationName></cac:PartyLegalEntity><cac:Contact><cbc:ElectronicMail>garrawil@gmail.com</cbc:ElectronicMail></cac:Contact></cac:Party></cac:AccountingCustomerParty><cac:PaymentTerms><cbc:ID>FormaPago</cbc:ID><cbc:PaymentMeansID>Contado</cbc:PaymentMeansID></cac:PaymentTerms><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">12.20</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">67.80</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">12.20</cbc:TaxAmount><cac:TaxCategory><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:LegalMonetaryTotal><cbc:LineExtensionAmount currencyID=\"PEN\">67.80</cbc:LineExtensionAmount><cbc:TaxInclusiveAmount currencyID=\"PEN\">80.00</cbc:TaxInclusiveAmount><cbc:PayableAmount currencyID=\"PEN\">80.00</cbc:PayableAmount></cac:LegalMonetaryTotal><cac:InvoiceLine><cbc:ID>1</cbc:ID><cbc:InvoicedQuantity unitCode=\"NIU\">1</cbc:InvoicedQuantity><cbc:LineExtensionAmount currencyID=\"PEN\">67.80</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID=\"PEN\">80</cbc:PriceAmount><cbc:PriceTypeCode>01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">12.20</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">67.80</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">12.20</cbc:TaxAmount><cac:TaxCategory><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:Item><cbc:Description><![CDATA[Servicio de transporte: Arequipa → Cusco - Asiento 27]]></cbc:Description><cac:SellersItemIdentification><cbc:ID>P001</cbc:ID></cac:SellersItemIdentification></cac:Item><cac:Price><cbc:PriceAmount currencyID=\"PEN\">67.8</cbc:PriceAmount></cac:Price></cac:InvoiceLine></Invoice>',NULL,NULL,'SIMULADO','Comprobante generado en MODO SIMULACIÓN. No fue enviado a SUNAT.',NULL,'SIMULADO','2026-09-15 11:15:24','2026-09-15 11:15:24',1,NULL,'2026-09-15 16:15:24');
/*!40000 ALTER TABLE `comprobantes_electronicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conductores`
--

DROP TABLE IF EXISTS `conductores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conductores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `numero_licencia` varchar(50) NOT NULL,
  `categoria_licencia` varchar(10) NOT NULL,
  `fecha_emision_licencia` date DEFAULT NULL,
  `fecha_vencimiento_licencia` date NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` text,
  `contacto_emergencia_nombre` varchar(150) DEFAULT NULL,
  `contacto_emergencia_telefono` varchar(20) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `sueldo_base` decimal(10,2) DEFAULT '0.00',
  `comision_porcentaje` decimal(5,2) DEFAULT '0.00',
  `estado` enum('ACTIVO','INACTIVO','VACACIONES','LICENCIA') DEFAULT 'ACTIVO',
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  KEY `idx_cond_estado` (`estado`),
  KEY `idx_cond_licencia` (`fecha_vencimiento_licencia`),
  CONSTRAINT `conductores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conductores`
--

LOCK TABLES `conductores` WRITE;
/*!40000 ALTER TABLE `conductores` DISABLE KEYS */;
/*!40000 ALTER TABLE `conductores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `tipo` enum('TEXTO','NUMERO','BOOLEANO','JSON') DEFAULT 'TEXTO',
  `descripcion` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES (1,'empresa_nombre','Transportes Ejemplo SAC','TEXTO','Nombre de la empresa','2026-08-19 22:20:47','2026-08-19 22:20:47'),(2,'empresa_ruc','20561234567','TEXTO','RUC de la empresa','2026-08-19 22:20:47','2026-08-19 22:20:47'),(3,'comision_giro_porcentaje','3.5','NUMERO','Comisión aplicada a giros en %','2026-08-19 22:20:47','2026-08-19 22:20:47'),(4,'tiempo_reserva_asiento','15','NUMERO','Minutos para liberar asiento reservado','2026-08-19 22:20:47','2026-08-19 22:20:47');
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `encomienda_historial`
--

DROP TABLE IF EXISTS `encomienda_historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `encomienda_historial` (
  `id` int NOT NULL AUTO_INCREMENT,
  `encomienda_id` int DEFAULT NULL,
  `estado_anterior` varchar(20) DEFAULT NULL,
  `estado_nuevo` varchar(20) DEFAULT NULL,
  `fecha_cambio` datetime DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `encomienda_historial`
--

LOCK TABLES `encomienda_historial` WRITE;
/*!40000 ALTER TABLE `encomienda_historial` DISABLE KEYS */;
/*!40000 ALTER TABLE `encomienda_historial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `encomiendas`
--

DROP TABLE IF EXISTS `encomiendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `encomiendas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `guia` varchar(50) NOT NULL,
  `remitente_id` int NOT NULL,
  `destinatario_id` int NOT NULL,
  `viaje_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `peso_kg` decimal(8,2) NOT NULL,
  `dimensiones` varchar(50) DEFAULT NULL,
  `descripcion` text,
  `valor_declarado` decimal(10,2) DEFAULT NULL,
  `costo_envio` decimal(10,2) NOT NULL,
  `estado` enum('REGISTRADO','EN_BODEGA','EN_TRANSITO','ENTREGADO','DEVUELTO') DEFAULT 'REGISTRADO',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega` datetime DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guia` (`guia`),
  KEY `remitente_id` (`remitente_id`),
  KEY `destinatario_id` (`destinatario_id`),
  KEY `viaje_id` (`viaje_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_encomienda_guia` (`guia`),
  KEY `idx_encomienda_estado` (`estado`),
  CONSTRAINT `encomiendas_ibfk_1` FOREIGN KEY (`remitente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `encomiendas_ibfk_2` FOREIGN KEY (`destinatario_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `encomiendas_ibfk_3` FOREIGN KEY (`viaje_id`) REFERENCES `viajes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `encomiendas_ibfk_4` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `encomiendas`
--

LOCK TABLES `encomiendas` WRITE;
/*!40000 ALTER TABLE `encomiendas` DISABLE KEYS */;
INSERT INTO `encomiendas` VALUES (1,'GUI-20260001',5,1,1,1,2.80,'','contiene un libro',5.00,14.00,'ENTREGADO','2026-09-13 12:17:46','2026-09-13 14:42:01',NULL);
/*!40000 ALTER TABLE `encomiendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturacion_correlativos`
--

DROP TABLE IF EXISTS `facturacion_correlativos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturacion_correlativos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(20) NOT NULL,
  `serie` varchar(4) NOT NULL,
  `ultimo_correlativo` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tipo_serie` (`tipo`,`serie`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturacion_correlativos`
--

LOCK TABLES `facturacion_correlativos` WRITE;
/*!40000 ALTER TABLE `facturacion_correlativos` DISABLE KEYS */;
INSERT INTO `facturacion_correlativos` VALUES (1,'FACTURA','F001',0,'2026-09-12 17:11:36'),(2,'BOLETA','B001',1,'2026-09-15 16:15:21'),(3,'NOTA_CREDITO','FC01',0,'2026-09-12 17:11:36'),(4,'NOTA_DEBITO','FD01',0,'2026-09-12 17:11:36');
/*!40000 ALTER TABLE `facturacion_correlativos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giros`
--

DROP TABLE IF EXISTS `giros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `giros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `remitente_id` int NOT NULL,
  `destinatario_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `comision` decimal(10,2) DEFAULT '0.00',
  `moneda` enum('PEN','USD') DEFAULT 'PEN',
  `metodo_pago_origen` enum('EFECTIVO','TARJETA','TRANSFERENCIA') DEFAULT 'EFECTIVO',
  `metodo_pago_destino` enum('EFECTIVO','DEPOSITO','OTRO') DEFAULT 'EFECTIVO',
  `estado` enum('REGISTRADO','PAGADO','ANULADO') DEFAULT 'REGISTRADO',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_pago` datetime DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `remitente_id` (`remitente_id`),
  KEY `destinatario_id` (`destinatario_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_giro_codigo` (`codigo`),
  KEY `idx_giro_estado` (`estado`),
  CONSTRAINT `giros_ibfk_1` FOREIGN KEY (`remitente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `giros_ibfk_2` FOREIGN KEY (`destinatario_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `giros_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giros`
--

LOCK TABLES `giros` WRITE;
/*!40000 ALTER TABLE `giros` DISABLE KEYS */;
INSERT INTO `giros` VALUES (1,'GIR-260915-0001',4,3,1,300.00,10.50,'PEN','EFECTIVO','EFECTIVO','REGISTRADO','2026-09-15 09:52:16',NULL,NULL);
/*!40000 ALTER TABLE `giros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manifiestos`
--

DROP TABLE IF EXISTS `manifiestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `manifiestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `viaje_id` int NOT NULL,
  `contenido` longtext NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha_generacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `viaje_id` (`viaje_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `manifiestos_ibfk_1` FOREIGN KEY (`viaje_id`) REFERENCES `viajes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manifiestos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manifiestos`
--

LOCK TABLES `manifiestos` WRITE;
/*!40000 ALTER TABLE `manifiestos` DISABLE KEYS */;
/*!40000 ALTER TABLE `manifiestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mantenimientos`
--

DROP TABLE IF EXISTS `mantenimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mantenimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bus_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `tipo` enum('PREVENTIVO','CORRECTIVO','REVISION') DEFAULT 'PREVENTIVO',
  `fecha_programada` date DEFAULT NULL,
  `fecha_realizada` date DEFAULT NULL,
  `kilometraje` int DEFAULT NULL,
  `descripcion` text NOT NULL,
  `taller_responsable` varchar(150) DEFAULT NULL,
  `costo_mano_obra` decimal(10,2) DEFAULT '0.00',
  `costo_repuestos` decimal(10,2) DEFAULT '0.00',
  `costo_total` decimal(10,2) DEFAULT '0.00',
  `proximo_mantenimiento_km` int DEFAULT NULL,
  `proximo_mantenimiento_fecha` date DEFAULT NULL,
  `estado` enum('PROGRAMADO','EN_PROCESO','COMPLETADO','CANCELADO') DEFAULT 'PROGRAMADO',
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_mant_bus` (`bus_id`),
  KEY `idx_mant_estado` (`estado`),
  KEY `idx_mant_fecha` (`fecha_programada`),
  CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `mantenimientos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mantenimientos`
--

LOCK TABLES `mantenimientos` WRITE;
/*!40000 ALTER TABLE `mantenimientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `mantenimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'ADMIN','Administrador del sistema con todos los permisos','2026-08-19 22:20:47'),(2,'VENDEDOR','Personal de venta de pasajes y encomiendas','2026-08-19 22:20:47'),(3,'BODEGA','Encargado de recepción y entrega de encomiendas','2026-08-19 22:20:47'),(4,'CONTADOR','Acceso a reportes financieros','2026-08-19 22:20:47'),(5,'CONDUCTOR','Personal encargado de conducir el bus','2026-08-20 15:43:13');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rutas`
--

DROP TABLE IF EXISTS `rutas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rutas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `origen` varchar(100) NOT NULL,
  `destino` varchar(100) NOT NULL,
  `distancia_km` decimal(8,2) DEFAULT NULL,
  `duracion_estimada` time DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rutas`
--

LOCK TABLES `rutas` WRITE;
/*!40000 ALTER TABLE `rutas` DISABLE KEYS */;
INSERT INTO `rutas` VALUES (1,'Lima','Arequipa',1012.00,'16:30:00',150.00,1,'2026-08-19 22:20:47'),(2,'Lima','Cusco',1100.00,'21:00:00',180.00,1,'2026-08-19 22:20:47'),(3,'Arequipa','Cusco',530.00,'09:00:00',80.00,1,'2026-08-19 22:20:47'),(4,'Piura','Chiclayo',240.00,'14:45:00',25.00,0,'2026-08-20 21:55:50'),(5,'Chiclayo','Piura',240.00,'02:30:00',25.00,0,'2026-09-05 22:28:47'),(6,'Chiclayo','Piura',240.00,'14:30:00',30.00,1,'2026-09-05 22:30:29'),(8,'chiclayo','Trujillo',280.00,'04:00:00',45.00,1,'2026-09-13 19:59:56');
/*!40000 ALTER TABLE `rutas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_favoritos`
--

DROP TABLE IF EXISTS `usuario_favoritos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_favoritos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `icono` varchar(50) NOT NULL,
  `url` varchar(200) NOT NULL,
  `orden` int DEFAULT '0',
  `fecha_agregado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorito` (`usuario_id`,`modulo`),
  KEY `idx_fav_usuario` (`usuario_id`),
  CONSTRAINT `usuario_favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_favoritos`
--

LOCK TABLES `usuario_favoritos` WRITE;
/*!40000 ALTER TABLE `usuario_favoritos` DISABLE KEYS */;
INSERT INTO `usuario_favoritos` VALUES (1,1,'home','Dashboard','fa-home','home',0,'2026-09-10 20:59:46');
/*!40000 ALTER TABLE `usuario_favoritos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rol_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,1,'Wilmer','Garcia Ramirez','admin@gmail.com','$2y$10$RuTXIg7UWDQJjgSQLWJtkeLi/x8llhul7BH3.eWkVxlJrsEt.L8aO','999888777',1,'2026-09-15 13:49:08','2026-08-19 22:20:47','2026-09-15 18:49:08'),(3,5,'Carlos','Gómez','carlos.conductor@transporte.com','$2y$10$92IXUNPkj0OoR0Q5byMi.Ye4oKoEa3Ro91lC/.og/at2.uheWG/igi','987123456',1,NULL,'2026-08-20 21:47:31','2026-08-20 21:47:31'),(4,5,'Juan','Pérez','conductor1@transporte.com','$2y$10$92IXUNPkj0OoR0Q5byMi.Ye4oKoEa3Ro91lC/.og/at2.uheWG/igi','987654321',1,NULL,'2026-08-20 21:51:09','2026-09-13 16:34:43'),(6,5,'Ana','López','conductor3@transporte.com','$2y$10$92IXUNPkj0OoR0Q5byMi.Ye4oKoEa3Ro91lC/.og/at2.uheWG/igi','987789456',1,NULL,'2026-08-20 21:51:09','2026-08-20 21:51:09');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `v_asientos_disponibles`
--

DROP TABLE IF EXISTS `v_asientos_disponibles`;
/*!50001 DROP VIEW IF EXISTS `v_asientos_disponibles`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_asientos_disponibles` AS SELECT 
 1 AS `viaje_id`,
 1 AS `ruta`,
 1 AS `fecha_salida`,
 1 AS `fecha_llegada`,
 1 AS `total_asientos`,
 1 AS `disponibles`,
 1 AS `ocupados`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ventas_pasajes`
--

DROP TABLE IF EXISTS `ventas_pasajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas_pasajes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `viaje_id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `asiento_id` int NOT NULL,
  `fecha_venta` datetime DEFAULT CURRENT_TIMESTAMP,
  `precio_pagado` decimal(10,2) NOT NULL,
  `comision` decimal(10,2) DEFAULT '0.00',
  `metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO',
  `numero_comprobante` varchar(50) DEFAULT NULL,
  `estado` enum('ACTIVA','ANULADA','USADA') DEFAULT 'ACTIVA',
  PRIMARY KEY (`id`),
  KEY `viaje_id` (`viaje_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `asiento_id` (`asiento_id`),
  KEY `idx_venta_fecha` (`fecha_venta`),
  KEY `idx_venta_cliente` (`cliente_id`),
  CONSTRAINT `ventas_pasajes_ibfk_1` FOREIGN KEY (`viaje_id`) REFERENCES `viajes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_pasajes_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_pasajes_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_pasajes_ibfk_4` FOREIGN KEY (`asiento_id`) REFERENCES `asientos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas_pasajes`
--

LOCK TABLES `ventas_pasajes` WRITE;
/*!40000 ALTER TABLE `ventas_pasajes` DISABLE KEYS */;
INSERT INTO `ventas_pasajes` VALUES (1,4,5,1,36,'2026-09-15 11:15:02',80.00,0.00,'EFECTIVO','BOL-000001','ACTIVA'),(2,4,5,1,19,'2026-09-15 11:17:41',80.00,0.00,'EFECTIVO','BOL-000002','ACTIVA');
/*!40000 ALTER TABLE `ventas_pasajes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `viajes`
--

DROP TABLE IF EXISTS `viajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `viajes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruta_id` int NOT NULL,
  `bus_id` int NOT NULL,
  `usuario_creador` int DEFAULT NULL,
  `hash_manifiesto` varchar(64) DEFAULT NULL,
  `fecha_salida` datetime NOT NULL,
  `fecha_llegada` datetime NOT NULL,
  `precio_final` decimal(10,2) NOT NULL,
  `estado` enum('PROGRAMADO','EN_CURSO','COMPLETADO','CANCELADO') DEFAULT 'PROGRAMADO',
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hash` (`hash_manifiesto`),
  KEY `ruta_id` (`ruta_id`),
  KEY `bus_id` (`bus_id`),
  KEY `usuario_creador` (`usuario_creador`),
  CONSTRAINT `viajes_ibfk_1` FOREIGN KEY (`ruta_id`) REFERENCES `rutas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `viajes_ibfk_2` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `viajes_ibfk_3` FOREIGN KEY (`usuario_creador`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `viajes_ibfk_4` FOREIGN KEY (`usuario_creador`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `viajes`
--

LOCK TABLES `viajes` WRITE;
/*!40000 ALTER TABLE `viajes` DISABLE KEYS */;
INSERT INTO `viajes` VALUES (1,1,1,NULL,NULL,'2026-09-15 17:20:47','2026-09-16 09:50:47',150.00,'PROGRAMADO',NULL,'2026-08-19 22:20:47'),(2,2,2,3,NULL,'2026-09-15 17:20:47','2026-09-16 14:20:47',180.00,'PROGRAMADO',NULL,'2026-08-19 22:20:47'),(3,3,3,4,NULL,'2026-09-15 21:30:47','2026-09-16 14:20:47',80.00,'PROGRAMADO',NULL,'2026-08-19 22:20:47'),(4,3,1,6,NULL,'2026-09-15 23:30:00','2026-09-15 06:00:00',80.00,'PROGRAMADO','','2026-08-20 21:57:50');
/*!40000 ALTER TABLE `viajes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'sistema_transporte'
--

--
-- Dumping routines for database 'sistema_transporte'
--

--
-- Final view structure for view `v_asientos_disponibles`
--

/*!50001 DROP VIEW IF EXISTS `v_asientos_disponibles`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_asientos_disponibles` AS select `v`.`id` AS `viaje_id`,concat(`r`.`origen`,' - ',`r`.`destino`) AS `ruta`,`v`.`fecha_salida` AS `fecha_salida`,`v`.`fecha_llegada` AS `fecha_llegada`,count(`a`.`id`) AS `total_asientos`,sum((case when (`a`.`disponible` = 1) then 1 else 0 end)) AS `disponibles`,(count(`a`.`id`) - sum((case when (`a`.`disponible` = 1) then 1 else 0 end))) AS `ocupados` from ((`viajes` `v` join `rutas` `r` on((`v`.`ruta_id` = `r`.`id`))) join `asientos` `a` on((`v`.`id` = `a`.`viaje_id`))) where (`v`.`estado` = 'PROGRAMADO') group by `v`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-15 14:18:05