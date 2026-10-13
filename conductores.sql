-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 25-09-2026 a las 13:25:11
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_transporte`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conductores`
--

DROP TABLE IF EXISTS `conductores`;
CREATE TABLE IF NOT EXISTS `conductores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `tipo_documento` enum('DNI','CE','PASAPORTE') DEFAULT 'DNI',
  `numero_documento` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
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
  KEY `idx_cond_licencia` (`fecha_vencimiento_licencia`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `conductores`
--

INSERT INTO `conductores` (`id`, `usuario_id`, `tipo_documento`, `numero_documento`, `foto`, `numero_licencia`, `categoria_licencia`, `fecha_emision_licencia`, `fecha_vencimiento_licencia`, `fecha_nacimiento`, `direccion`, `contacto_emergencia_nombre`, `contacto_emergencia_telefono`, `fecha_ingreso`, `sueldo_base`, `comision_porcentaje`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 3, 'DNI', NULL, 'conductor_20260925122815_1f096957.png', 'A90696289', 'A-IIIb', '2025-02-05', '2028-06-25', '1978-08-09', 'Los incas 451, La victoria - Chiclayo', '12111David Rivas Santur4554', '906962893sasasaFFGDD', '2026-03-02', 2000.00, 0.00, 'ACTIVO', '', '2026-09-25 17:28:15', '2026-09-25 18:16:00'),
(2, 8, 'DNI', NULL, 'conductor_20260925125518_8b85eafc.png', 'B16354562', 'B-IIa', '2025-06-03', '2028-10-01', '1968-05-23', 'Santa Ana 251 - Chiclayo', 'Paul Rivas', '905654545', '2026-03-25', 1800.00, 0.00, 'ACTIVO', '', '2026-09-25 17:55:18', '2026-09-25 17:55:18');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `conductores`
--
ALTER TABLE `conductores`
  ADD CONSTRAINT `conductores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
