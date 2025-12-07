-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 29-11-2025 a las 03:01:13
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
-- Base de datos: `sistema_escolar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
CREATE TABLE IF NOT EXISTS `asistencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estudiante_id` int NOT NULL,
  `materia_id` int NOT NULL,
  `profesor_id` int NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('presente','ausente','justificado','tardanza') COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `materia_id` (`materia_id`),
  KEY `profesor_id` (`profesor_id`),
  KEY `idx_asistencias_estudiante_fecha` (`estudiante_id`,`fecha`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `estudiante_id`, `materia_id`, `profesor_id`, `fecha`, `estado`, `observaciones`, `fecha_creacion`) VALUES
(1, 1, 11, 1, '2024-01-15', 'presente', '', '2025-10-15 20:07:36'),
(2, 1, 12, 2, '2024-01-16', 'presente', '', '2025-10-15 20:07:36'),
(3, 1, 13, 3, '2024-01-17', 'presente', '', '2025-10-15 20:07:36'),
(4, 1, 11, 1, '2024-01-22', 'presente', '', '2025-10-15 20:07:36'),
(5, 1, 12, 2, '2024-01-23', 'tardanza', 'Llegó 10 minutos tarde', '2025-10-15 20:07:36'),
(6, 1, 13, 3, '2024-01-24', 'presente', '', '2025-10-15 20:07:36'),
(7, 1, 11, 1, '2024-01-29', 'presente', '', '2025-10-15 20:07:36'),
(8, 1, 12, 2, '2024-01-30', 'presente', '', '2025-10-15 20:07:36'),
(9, 1, 13, 3, '2024-01-31', 'ausente', 'Falta justificada por enfermedad', '2025-10-15 20:07:36'),
(10, 2, 11, 1, '2024-01-15', 'presente', '', '2025-10-15 20:07:36'),
(11, 2, 12, 2, '2024-01-16', 'presente', '', '2025-10-15 20:07:36'),
(12, 2, 13, 3, '2024-01-17', 'presente', '', '2025-10-15 20:07:36'),
(13, 2, 11, 1, '2024-01-22', 'presente', '', '2025-10-15 20:07:36'),
(14, 2, 12, 2, '2024-01-23', 'presente', '', '2025-10-15 20:07:36'),
(15, 2, 13, 3, '2024-01-24', 'presente', '', '2025-10-15 20:07:36'),
(16, 2, 11, 1, '2024-01-29', 'presente', '', '2025-10-15 20:07:36'),
(17, 2, 12, 2, '2024-01-30', 'presente', '', '2025-10-15 20:07:36'),
(18, 2, 13, 3, '2024-01-31', 'presente', '', '2025-10-15 20:07:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

DROP TABLE IF EXISTS `aulas`;
CREATE TABLE IF NOT EXISTS `aulas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacidad` int DEFAULT '30',
  `tipo` enum('aula','laboratorio','biblioteca','gimnasio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aula',
  `estado` enum('activo','inactivo','mantenimiento') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `aulas`
--

INSERT INTO `aulas` (`id`, `nombre`, `ubicacion`, `capacidad`, `tipo`, `estado`, `fecha_creacion`) VALUES
(1, 'Aula 101', 'Primer piso - Ala Norte', 30, 'aula', 'activo', '2025-10-15 20:07:35'),
(2, 'Aula 102', 'Primer piso - Ala Norte', 30, 'aula', 'activo', '2025-10-15 20:07:35'),
(3, 'Aula 103', 'Primer piso - Ala Norte', 30, 'aula', 'activo', '2025-10-15 20:07:35'),
(4, 'Aula 201', 'Segundo piso - Ala Norte', 30, 'aula', 'activo', '2025-10-15 20:07:35'),
(5, 'Aula 202', 'Segundo piso - Ala Norte', 30, 'aula', 'activo', '2025-10-15 20:07:35'),
(6, 'Aula 203', 'Segundo piso - Ala Norte', 30, 'aula', 'activo', '2025-10-15 20:07:35'),
(7, 'Laboratorio de Ciencias', 'Segundo piso - Ala Sur', 25, 'laboratorio', 'activo', '2025-10-15 20:07:35'),
(8, 'Sala de Computación', 'Primer piso - Ala Sur', 20, 'laboratorio', 'activo', '2025-10-15 20:07:35'),
(9, 'Biblioteca', 'Planta baja - Ala Central', 50, 'biblioteca', 'activo', '2025-10-15 20:07:35'),
(10, 'Gimnasio', 'Planta baja - Ala Este', 100, 'gimnasio', 'activo', '2025-10-15 20:07:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

DROP TABLE IF EXISTS `calificaciones`;
CREATE TABLE IF NOT EXISTS `calificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estudiante_id` int NOT NULL,
  `materia_id` int NOT NULL,
  `profesor_id` int NOT NULL,
  `tipo_evaluacion` enum('examen','tarea','proyecto','participacion','practica') COLLATE utf8mb4_unicode_ci NOT NULL,
  `calificacion` decimal(5,2) NOT NULL,
  `fecha_evaluacion` date NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `materia_id` (`materia_id`),
  KEY `profesor_id` (`profesor_id`),
  KEY `idx_calificaciones_estudiante_materia` (`estudiante_id`,`materia_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id`, `estudiante_id`, `materia_id`, `profesor_id`, `tipo_evaluacion`, `calificacion`, `fecha_evaluacion`, `observaciones`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 11, 1, 'examen', 85.50, '2024-01-15', 'Buen desempeño en el examen', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(2, 1, 12, 2, 'examen', 92.00, '2024-01-16', 'Excelente trabajo', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(3, 1, 13, 3, 'examen', 78.50, '2024-01-17', 'Necesita mejorar en ciencias', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(4, 1, 11, 1, 'tarea', 90.00, '2024-01-20', 'Tarea completada correctamente', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(5, 1, 12, 2, 'proyecto', 88.00, '2024-01-25', 'Proyecto bien presentado', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(6, 2, 11, 1, 'examen', 95.00, '2024-01-15', 'Excelente calificación', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(7, 2, 12, 2, 'examen', 89.50, '2024-01-16', 'Muy buen trabajo', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(8, 2, 13, 3, 'examen', 92.00, '2024-01-17', 'Destacada en ciencias', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(9, 2, 11, 1, 'tarea', 94.00, '2024-01-20', 'Tarea impecable', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(10, 2, 12, 2, 'proyecto', 91.00, '2024-01-25', 'Proyecto creativo', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(11, 3, 16, 1, 'examen', 82.00, '2024-01-15', 'Buen desempeño', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(12, 3, 17, 2, 'examen', 87.50, '2024-01-16', 'Muy bien', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(13, 3, 18, 3, 'examen', 85.00, '2024-01-17', 'Satisfactorio', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(14, 3, 16, 1, 'tarea', 88.00, '2024-01-20', 'Tarea bien realizada', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(15, 3, 17, 2, 'proyecto', 86.00, '2024-01-25', 'Proyecto completo', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(17, 29, 27, 3, 'proyecto', 90.00, '2025-10-18', NULL, '2025-10-19 01:59:20', '2025-10-19 01:59:20'),
(18, 18, 9, 5, 'proyecto', 90.00, '2025-10-18', NULL, '2025-10-19 02:06:01', '2025-10-19 02:06:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

DROP TABLE IF EXISTS `configuraciones`;
CREATE TABLE IF NOT EXISTS `configuraciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('texto','numero','booleano','json') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'texto',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `descripcion`, `tipo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'nombre_escuela', 'Escuela Primaria \"Benito Juárez\"', 'Nombre oficial de la institución educativa', 'texto', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(2, 'direccion_escuela', 'Av. Principal #123, Col. Centro, Ciudad, Estado', 'Dirección completa de la escuela', 'texto', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(3, 'telefono_escuela', '555-0000', 'Teléfono principal de la escuela', 'texto', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(4, 'email_escuela', 'contacto@escuela.com', 'Correo electrónico de contacto', 'texto', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(5, 'director_escuela', 'Lic. María Elena González', 'Nombre del director(a) de la escuela', 'texto', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(6, 'ciclo_escolar_actual', '2024-2025', 'Ciclo escolar vigente', 'texto', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(7, 'calificacion_minima', '6.0', 'Calificación mínima aprobatoria', 'numero', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(8, 'calificacion_maxima', '10.0', 'Calificación máxima posible', 'numero', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(9, 'porcentaje_asistencia_minimo', '80', 'Porcentaje mínimo de asistencia requerido', 'numero', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(10, 'dias_clase_por_semana', '5', 'Número de días de clase por semana', 'numero', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(11, 'horas_clase_por_dia', '6', 'Número de horas de clase por día', 'numero', '2025-10-15 20:07:36', '2025-10-15 20:07:36'),
(12, 'activo', '1', 'Estado del sistema (1=activo, 0=inactivo)', 'booleano', '2025-10-15 20:07:36', '2025-10-15 20:07:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

DROP TABLE IF EXISTS `estudiantes`;
CREATE TABLE IF NOT EXISTS `estudiantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricula` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_paterno` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_materno` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `grado_id` int NOT NULL,
  `grupo_id` int NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `nombre_tutor` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_tutor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `estado` enum('activo','inactivo','egresado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula` (`matricula`),
  KEY `grupo_id` (`grupo_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_estudiantes_matricula` (`matricula`),
  KEY `idx_estudiantes_grado_grupo` (`grado_id`,`grupo_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `matricula`, `nombre`, `apellido_paterno`, `apellido_materno`, `fecha_nacimiento`, `grado_id`, `grupo_id`, `telefono`, `email`, `direccion`, `nombre_tutor`, `telefono_tutor`, `usuario_id`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'EST001', 'Juan', 'Pérez', 'García', NULL, 3, 5, '555-0123', 'juan.perez@email.com', NULL, 'Carlos Pérez', '555-0124', 5, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(2, 'EST002', 'María', 'López', 'Rodríguez', NULL, 3, 6, '555-0125', 'maria.lopez@email.com', NULL, 'Elena López', '555-0126', 6, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(3, 'EST003', 'Carlos', 'Hernández', 'Silva', NULL, 4, 7, '555-0127', 'carlos.hernandez@email.com', NULL, 'Roberto Hernández', '555-0128', NULL, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(4, 'EST004', 'Ana', 'González', 'Morales', NULL, 4, 7, '555-0129', 'ana.gonzalez@email.com', NULL, 'Patricia González', '555-0130', NULL, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(5, 'EST005', 'Luis', 'Ramírez', 'Castro', NULL, 5, 9, '555-0131', 'luis.ramirez@email.com', NULL, 'Miguel Ramírez', '555-0132', NULL, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(6, 'EST006', 'Sofia', 'Torres', 'Vega', NULL, 5, 9, '555-0133', 'sofia.torres@email.com', NULL, 'Isabel Torres', '555-0134', NULL, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(7, 'EST007', 'Diego', 'Mendoza', 'Ruiz', NULL, 6, 11, '555-0135', 'diego.mendoza@email.com', NULL, 'Fernando Mendoza', '555-0136', NULL, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(8, 'EST008', 'Valentinaa', 'Jiménez', 'Herrera', '2000-01-01', 6, 11, '555-0137', 'valentina.jimenez@email.com', 'Dirección de prueba', 'Carmen Jiménez', '555-0138', NULL, 'activo', '2025-10-15 20:07:35', '2025-10-17 06:42:24'),
(10, 'EST3452', 'Test', 'Estudiante', 'Prueba', '2010-01-01', 1, 1, '555-9999', 'test.estudiante@email.com', 'Dirección de prueba', NULL, NULL, NULL, 'activo', '2025-10-17 01:10:34', '2025-10-17 01:10:34'),
(11, 'EST2892', 'Juan Carlos', 'García', 'López', '2005-03-15', 3, 5, '555-1234', 'juan.garcia@email.com', 'Calle Principal 123', NULL, NULL, NULL, 'activo', '2025-10-17 01:11:07', '2025-10-17 01:11:07'),
(12, 'EST8576', 'María', 'Fernández', 'González', '2008-07-20', 3, 1, '555-8888', 'maria.fernandez@email.com', 'Av. Principal 456', NULL, NULL, NULL, 'activo', '2025-10-17 01:12:24', '2025-10-17 01:12:24'),
(13, 'EST4071', 'Almanza', 'Viveros', 'Hernández', '2005-03-15', 3, 5, '555-1234', 'almanza.viveros@email.com', 'Calle Principal 123', NULL, NULL, NULL, 'activo', '2025-10-17 01:22:10', '2025-10-17 01:22:10'),
(14, 'EST4392', 'José María', 'García', 'López', '2005-01-10', 2, 1, '555-1000', 'josé maría.garcía@test.com', 'Dirección de prueba 1', NULL, NULL, NULL, 'activo', '2025-10-17 01:23:19', '2025-10-17 01:23:19'),
(15, 'EST3612', 'María Elena', 'Rodríguez', 'Pérez', '2006-02-11', 3, 5, '555-1001', 'maría elena.rodríguez@test.com', 'Dirección de prueba 2', NULL, NULL, NULL, 'activo', '2025-10-17 01:23:19', '2025-10-17 01:23:19'),
(16, 'EST8157', 'Carlos Alberto', 'Hernández', 'Silva', '2007-03-12', 4, 6, '555-1002', 'carlos alberto.hernández@test.com', 'Dirección de prueba 3', NULL, NULL, NULL, 'activo', '2025-10-17 01:23:19', '2025-10-17 01:23:19'),
(17, 'EST0446', 'Ana Patricia', 'González', 'Morales', '2008-04-13', 2, 7, '555-1003', 'ana patricia.gonzález@test.com', 'Dirección de prueba 4', NULL, NULL, NULL, 'activo', '2025-10-17 01:23:19', '2025-10-17 01:23:19'),
(18, 'EST6391', 'Obed', 'Viveros', 'Martinez', '2000-10-23', 1, 1, '123-456-7890', 'obed.viveros@email.com', 'Dirección de prueba', NULL, NULL, NULL, 'activo', '2025-10-17 01:38:59', '2025-10-17 01:38:59'),
(19, 'EST2827', 'María', 'González', 'López', '2005-03-15', 1, 1, '555-123-4567', 'maria.gonzalez@email.com', 'Calle Principal 123', NULL, NULL, NULL, 'activo', '2025-10-17 01:40:38', '2025-10-17 01:40:38'),
(22, 'EST4292', 'Carlos', 'López', 'Martínez', '2004-08-20', 1, 1, '555-987-6543', 'carlos.lopez@email.com', 'Avenida Central 789', NULL, NULL, NULL, 'activo', '2025-10-17 06:06:48', '2025-10-17 06:06:48'),
(29, 'EST8619', 'valerina', 'capuchina', 'Herrera', '2004-01-21', 10, 104, '11234567654456787654', 'cafrg@uv.mx', '', NULL, NULL, NULL, 'activo', '2025-10-17 07:07:10', '2025-10-17 07:07:10'),
(30, 'EST5823', 'pedro', 'garcia', 'Herrera', '2008-01-17', 11, 96, '11294848484', 'slkjjdhshshh@gmail.com', '', NULL, NULL, NULL, 'activo', '2025-10-17 07:13:56', '2025-10-17 07:13:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

DROP TABLE IF EXISTS `eventos`;
CREATE TABLE IF NOT EXISTS `eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text,
  `tipo` enum('academico','deportivo','cultural','social','reunion','otro') NOT NULL DEFAULT 'academico',
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `organizador_id` int DEFAULT NULL,
  `grupo_id` int DEFAULT NULL,
  `grado_id` int DEFAULT NULL,
  `estado` enum('programado','en_curso','finalizado','cancelado') NOT NULL DEFAULT 'programado',
  `participantes_max` int DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT '0.00',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_eventos_fecha` (`fecha_inicio`),
  KEY `idx_eventos_tipo` (`tipo`),
  KEY `idx_eventos_estado` (`estado`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grados`
--

DROP TABLE IF EXISTS `grados`;
CREATE TABLE IF NOT EXISTS `grados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grados`
--

INSERT INTO `grados` (`id`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, '1° Grado', 'Primer grado de educación primaria', 'activo', '2025-10-15 20:07:35'),
(2, '2° Grado', 'Segundo grado de educación primaria', 'activo', '2025-10-15 20:07:35'),
(3, '3° Grado', 'Tercer grado de educación primaria', 'activo', '2025-10-15 20:07:35'),
(4, '4° Grado', 'Cuarto grado de educación primaria', 'activo', '2025-10-15 20:07:35'),
(5, '5° Grado', 'Quinto grado de educación primaria', 'activo', '2025-10-15 20:07:35'),
(6, '6° Grado', 'Sexto grado de educación primaria', 'activo', '2025-10-15 20:07:35'),
(7, '1° Grado', 'Primer grado de educación primaria', 'activo', '2025-10-16 06:49:21'),
(8, '2° Grado', 'Segundo grado de educación primaria', 'activo', '2025-10-16 06:49:21'),
(9, '3° Grado', 'Tercer grado de educación primaria', 'activo', '2025-10-16 06:49:21'),
(10, '4° Grado', 'Cuarto grado de educación primaria', 'activo', '2025-10-16 06:49:21'),
(11, '5° Grado', 'Quinto grado de educación primaria', 'activo', '2025-10-16 06:49:21'),
(12, '6° Grado', 'Sexto grado de educación primaria', 'activo', '2025-10-16 06:49:21'),
(13, '1° Grado', NULL, 'activo', '2025-10-16 06:50:10'),
(14, '2° Grado', NULL, 'activo', '2025-10-16 06:50:10'),
(15, '3° Grado', NULL, 'activo', '2025-10-16 06:50:10'),
(16, '4° Grado', NULL, 'activo', '2025-10-16 06:50:10'),
(17, '5° Grado', NULL, 'activo', '2025-10-16 06:50:10'),
(18, '6° Grado', NULL, 'activo', '2025-10-16 06:50:10'),
(19, '1° Grado', NULL, 'activo', '2025-10-16 06:52:13'),
(20, '2° Grado', NULL, 'activo', '2025-10-16 06:52:13'),
(21, '3° Grado', NULL, 'activo', '2025-10-16 06:52:13'),
(22, '4° Grado', NULL, 'activo', '2025-10-16 06:52:13'),
(23, '5° Grado', NULL, 'activo', '2025-10-16 06:52:13'),
(24, '6° Grado', NULL, 'activo', '2025-10-16 06:52:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

DROP TABLE IF EXISTS `grupos`;
CREATE TABLE IF NOT EXISTS `grupos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grado_id` int NOT NULL,
  `capacidad` int DEFAULT '30',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `grado_id` (`grado_id`)
) ENGINE=MyISAM AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupos`
--

INSERT INTO `grupos` (`id`, `nombre`, `grado_id`, `capacidad`, `estado`, `fecha_creacion`) VALUES
(1, 'A', 1, 30, 'activo', '2025-10-15 20:07:35'),
(2, 'B', 1, 30, 'activo', '2025-10-15 20:07:35'),
(3, 'A', 2, 30, 'activo', '2025-10-15 20:07:35'),
(4, 'B', 2, 30, 'activo', '2025-10-15 20:07:35'),
(5, 'A', 3, 30, 'activo', '2025-10-15 20:07:35'),
(6, 'B', 3, 30, 'activo', '2025-10-15 20:07:35'),
(7, 'A', 4, 30, 'activo', '2025-10-15 20:07:35'),
(8, 'B', 4, 30, 'activo', '2025-10-15 20:07:35'),
(9, 'A', 5, 30, 'activo', '2025-10-15 20:07:35'),
(10, 'B', 5, 30, 'activo', '2025-10-15 20:07:35'),
(11, 'A', 6, 30, 'activo', '2025-10-15 20:07:35'),
(12, 'B', 6, 30, 'activo', '2025-10-15 20:07:35'),
(13, 'A', 1, 30, 'activo', '2025-10-16 06:49:21'),
(14, 'B', 1, 30, 'activo', '2025-10-16 06:49:21'),
(15, 'A', 2, 30, 'activo', '2025-10-16 06:49:21'),
(16, 'B', 2, 30, 'activo', '2025-10-16 06:49:21'),
(17, 'A', 3, 30, 'activo', '2025-10-16 06:49:21'),
(18, 'B', 3, 30, 'activo', '2025-10-16 06:49:21'),
(19, 'A', 4, 30, 'activo', '2025-10-16 06:49:21'),
(20, 'B', 4, 30, 'activo', '2025-10-16 06:49:21'),
(21, 'A', 5, 30, 'activo', '2025-10-16 06:49:21'),
(22, 'B', 5, 30, 'activo', '2025-10-16 06:49:21'),
(23, 'A', 6, 30, 'activo', '2025-10-16 06:49:21'),
(24, 'B', 6, 30, 'activo', '2025-10-16 06:49:21'),
(25, 'A', 7, 30, 'activo', '2025-10-16 06:49:21'),
(26, 'B', 7, 30, 'activo', '2025-10-16 06:49:21'),
(27, 'A', 8, 30, 'activo', '2025-10-16 06:49:21'),
(28, 'B', 8, 30, 'activo', '2025-10-16 06:49:21'),
(29, 'A', 9, 30, 'activo', '2025-10-16 06:49:21'),
(30, 'B', 9, 30, 'activo', '2025-10-16 06:49:21'),
(31, 'A', 10, 30, 'activo', '2025-10-16 06:49:21'),
(32, 'B', 10, 30, 'activo', '2025-10-16 06:49:21'),
(33, 'A', 11, 30, 'activo', '2025-10-16 06:49:21'),
(34, 'B', 11, 30, 'activo', '2025-10-16 06:49:21'),
(35, 'A', 12, 30, 'activo', '2025-10-16 06:49:21'),
(36, 'B', 12, 30, 'activo', '2025-10-16 06:49:21'),
(37, 'A', 1, 30, 'activo', '2025-10-16 06:50:10'),
(38, 'B', 1, 30, 'activo', '2025-10-16 06:50:10'),
(39, 'A', 2, 30, 'activo', '2025-10-16 06:50:10'),
(40, 'B', 2, 30, 'activo', '2025-10-16 06:50:10'),
(41, 'A', 3, 30, 'activo', '2025-10-16 06:50:10'),
(42, 'B', 3, 30, 'activo', '2025-10-16 06:50:10'),
(43, 'A', 4, 30, 'activo', '2025-10-16 06:50:10'),
(44, 'B', 4, 30, 'activo', '2025-10-16 06:50:10'),
(45, 'A', 5, 30, 'activo', '2025-10-16 06:50:10'),
(46, 'B', 5, 30, 'activo', '2025-10-16 06:50:10'),
(47, 'A', 6, 30, 'activo', '2025-10-16 06:50:10'),
(48, 'B', 6, 30, 'activo', '2025-10-16 06:50:10'),
(49, 'A', 7, 30, 'activo', '2025-10-16 06:50:10'),
(50, 'B', 7, 30, 'activo', '2025-10-16 06:50:10'),
(51, 'A', 8, 30, 'activo', '2025-10-16 06:50:10'),
(52, 'B', 8, 30, 'activo', '2025-10-16 06:50:10'),
(53, 'A', 9, 30, 'activo', '2025-10-16 06:50:10'),
(54, 'B', 9, 30, 'activo', '2025-10-16 06:50:10'),
(55, 'A', 10, 30, 'activo', '2025-10-16 06:50:10'),
(56, 'B', 10, 30, 'activo', '2025-10-16 06:50:10'),
(57, 'A', 11, 30, 'activo', '2025-10-16 06:50:10'),
(58, 'B', 11, 30, 'activo', '2025-10-16 06:50:10'),
(59, 'A', 12, 30, 'activo', '2025-10-16 06:50:10'),
(60, 'B', 12, 30, 'activo', '2025-10-16 06:50:10'),
(61, 'A', 13, 30, 'activo', '2025-10-16 06:50:10'),
(62, 'B', 13, 30, 'activo', '2025-10-16 06:50:10'),
(63, 'A', 14, 30, 'activo', '2025-10-16 06:50:10'),
(64, 'B', 14, 30, 'activo', '2025-10-16 06:50:10'),
(65, 'A', 15, 30, 'activo', '2025-10-16 06:50:10'),
(66, 'B', 15, 30, 'activo', '2025-10-16 06:50:10'),
(67, 'A', 16, 30, 'activo', '2025-10-16 06:50:10'),
(68, 'B', 16, 30, 'activo', '2025-10-16 06:50:10'),
(69, 'A', 17, 30, 'activo', '2025-10-16 06:50:10'),
(70, 'B', 17, 30, 'activo', '2025-10-16 06:50:10'),
(71, 'A', 18, 30, 'activo', '2025-10-16 06:50:10'),
(72, 'B', 18, 30, 'activo', '2025-10-16 06:50:10'),
(73, 'A', 1, 30, 'activo', '2025-10-16 06:52:13'),
(74, 'B', 1, 30, 'activo', '2025-10-16 06:52:13'),
(75, 'A', 2, 30, 'activo', '2025-10-16 06:52:13'),
(76, 'B', 2, 30, 'activo', '2025-10-16 06:52:13'),
(77, 'A', 3, 30, 'activo', '2025-10-16 06:52:13'),
(78, 'B', 3, 30, 'activo', '2025-10-16 06:52:13'),
(79, 'A', 4, 30, 'activo', '2025-10-16 06:52:13'),
(80, 'B', 4, 30, 'activo', '2025-10-16 06:52:13'),
(81, 'A', 5, 30, 'activo', '2025-10-16 06:52:13'),
(82, 'B', 5, 30, 'activo', '2025-10-16 06:52:13'),
(83, 'A', 6, 30, 'activo', '2025-10-16 06:52:13'),
(84, 'B', 6, 30, 'activo', '2025-10-16 06:52:13'),
(85, 'A', 7, 30, 'activo', '2025-10-16 06:52:13'),
(86, 'B', 7, 30, 'activo', '2025-10-16 06:52:13'),
(87, 'A', 8, 30, 'activo', '2025-10-16 06:52:13'),
(88, 'B', 8, 30, 'activo', '2025-10-16 06:52:13'),
(89, 'A', 9, 30, 'activo', '2025-10-16 06:52:13'),
(90, 'B', 9, 30, 'activo', '2025-10-16 06:52:13'),
(91, 'A', 10, 30, 'activo', '2025-10-16 06:52:13'),
(92, 'B', 10, 30, 'activo', '2025-10-16 06:52:13'),
(93, 'A', 11, 30, 'activo', '2025-10-16 06:52:13'),
(94, 'B', 11, 30, 'activo', '2025-10-16 06:52:13'),
(95, 'A', 12, 30, 'activo', '2025-10-16 06:52:13'),
(96, 'B', 12, 30, 'activo', '2025-10-16 06:52:13'),
(97, 'A', 13, 30, 'activo', '2025-10-16 06:52:13'),
(98, 'B', 13, 30, 'activo', '2025-10-16 06:52:13'),
(99, 'A', 14, 30, 'activo', '2025-10-16 06:52:13'),
(100, 'B', 14, 30, 'activo', '2025-10-16 06:52:13'),
(101, 'A', 15, 30, 'activo', '2025-10-16 06:52:13'),
(102, 'B', 15, 30, 'activo', '2025-10-16 06:52:13'),
(103, 'A', 16, 30, 'activo', '2025-10-16 06:52:13'),
(104, 'B', 16, 30, 'activo', '2025-10-16 06:52:13'),
(105, 'A', 17, 30, 'activo', '2025-10-16 06:52:13'),
(106, 'B', 17, 30, 'activo', '2025-10-16 06:52:13'),
(107, 'A', 18, 30, 'activo', '2025-10-16 06:52:13'),
(108, 'B', 18, 30, 'activo', '2025-10-16 06:52:13'),
(109, 'A', 19, 30, 'activo', '2025-10-16 06:52:13'),
(110, 'B', 19, 30, 'activo', '2025-10-16 06:52:13'),
(111, 'A', 20, 30, 'activo', '2025-10-16 06:52:13'),
(112, 'B', 20, 30, 'activo', '2025-10-16 06:52:13'),
(113, 'A', 21, 30, 'activo', '2025-10-16 06:52:13'),
(114, 'B', 21, 30, 'activo', '2025-10-16 06:52:13'),
(115, 'A', 22, 30, 'activo', '2025-10-16 06:52:13'),
(116, 'B', 22, 30, 'activo', '2025-10-16 06:52:13'),
(117, 'A', 23, 30, 'activo', '2025-10-16 06:52:13'),
(118, 'B', 23, 30, 'activo', '2025-10-16 06:52:13'),
(119, 'A', 24, 30, 'activo', '2025-10-16 06:52:13'),
(120, 'B', 24, 30, 'activo', '2025-10-16 06:52:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

DROP TABLE IF EXISTS `horarios`;
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `materia_id` int NOT NULL,
  `profesor_id` int NOT NULL,
  `grupo_id` int NOT NULL,
  `aula_id` int NOT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `materia_id` (`materia_id`),
  KEY `profesor_id` (`profesor_id`),
  KEY `grupo_id` (`grupo_id`),
  KEY `aula_id` (`aula_id`),
  KEY `idx_horarios_dia_hora` (`dia_semana`,`hora_inicio`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `materia_id`, `profesor_id`, `grupo_id`, `aula_id`, `dia_semana`, `hora_inicio`, `hora_fin`, `estado`, `fecha_creacion`) VALUES
(1, 11, 1, 5, 1, 'lunes', '08:00:00', '09:00:00', 'activo', '2025-10-15 20:07:35'),
(2, 12, 2, 5, 2, 'lunes', '09:00:00', '10:00:00', 'activo', '2025-10-15 20:07:35'),
(3, 13, 3, 5, 3, 'lunes', '10:00:00', '11:00:00', 'activo', '2025-10-15 20:07:35'),
(4, 11, 1, 5, 1, 'martes', '08:00:00', '09:00:00', 'activo', '2025-10-15 20:07:35'),
(5, 14, 2, 5, 2, 'martes', '09:00:00', '10:00:00', 'activo', '2025-10-15 20:07:35'),
(6, 15, 3, 5, 4, 'martes', '10:00:00', '11:00:00', 'activo', '2025-10-15 20:07:35'),
(7, 12, 2, 5, 2, 'miercoles', '08:00:00', '09:00:00', 'activo', '2025-10-15 20:07:35'),
(8, 13, 3, 5, 3, 'miercoles', '09:00:00', '10:00:00', 'activo', '2025-10-15 20:07:35'),
(9, 11, 1, 5, 1, 'miercoles', '10:00:00', '11:00:00', 'activo', '2025-10-15 20:07:35'),
(10, 14, 2, 5, 2, 'jueves', '08:00:00', '09:00:00', 'activo', '2025-10-15 20:07:35'),
(11, 15, 3, 5, 4, 'jueves', '09:00:00', '10:00:00', 'activo', '2025-10-15 20:07:35'),
(12, 11, 1, 5, 1, 'jueves', '10:00:00', '11:00:00', 'activo', '2025-10-15 20:07:35'),
(13, 12, 2, 5, 2, 'viernes', '08:00:00', '09:00:00', 'activo', '2025-10-15 20:07:35'),
(14, 13, 3, 5, 3, 'viernes', '09:00:00', '10:00:00', 'activo', '2025-10-15 20:07:35'),
(15, 14, 2, 5, 2, 'viernes', '10:00:00', '11:00:00', 'activo', '2025-10-15 20:07:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

DROP TABLE IF EXISTS `inventario`;
CREATE TABLE IF NOT EXISTS `inventario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `categoria` enum('equipo','mobiliario','material','tecnologia','deportivo','otro') NOT NULL DEFAULT 'equipo',
  `descripcion` text,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `cantidad_total` int NOT NULL DEFAULT '1',
  `cantidad_disponible` int NOT NULL DEFAULT '1',
  `ubicacion` varchar(200) DEFAULT NULL,
  `estado_general` enum('excelente','bueno','regular','malo','inutilizable') NOT NULL DEFAULT 'bueno',
  `valor_estimado` decimal(10,2) DEFAULT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `proveedor` varchar(200) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `observaciones` text,
  `estado` enum('disponible','en_uso','mantenimiento','perdido','dado_baja') NOT NULL DEFAULT 'disponible',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_inventario_codigo` (`codigo`),
  KEY `idx_inventario_categoria` (`categoria`),
  KEY `idx_inventario_estado` (`estado`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

DROP TABLE IF EXISTS `libros`;
CREATE TABLE IF NOT EXISTS `libros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `autor` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `editorial` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isbn` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `año_publicacion` year DEFAULT NULL,
  `cantidad_total` int NOT NULL DEFAULT '1',
  `cantidad_disponible` int NOT NULL DEFAULT '1',
  `ubicacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('disponible','prestado','reservado','mantenimiento') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disponible',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_libros_codigo` (`codigo`),
  KEY `idx_libros_titulo` (`titulo`),
  KEY `idx_libros_autor` (`autor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

DROP TABLE IF EXISTS `materias`;
CREATE TABLE IF NOT EXISTS `materias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `creditos` int DEFAULT '1',
  `grado_id` int NOT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `grado_id` (`grado_id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `codigo`, `nombre`, `descripcion`, `creditos`, `grado_id`, `estado`, `fecha_creacion`) VALUES
(1, 'MAT-1', 'Matemáticas I', 'Fundamentos de matemáticas para primer grado', 1, 1, 'activo', '2025-10-15 20:07:35'),
(2, 'ESP-1', 'Español I', 'Lengua y literatura para primer grado', 1, 1, 'activo', '2025-10-15 20:07:35'),
(3, 'CNS-1', 'Ciencias Naturales I', 'Introducción a las ciencias naturales', 1, 1, 'activo', '2025-10-15 20:07:35'),
(4, 'HIS-1', 'Historia I', 'Historia local y familiar', 1, 1, 'activo', '2025-10-15 20:07:35'),
(5, 'EDF-1', 'Educación Física I', 'Desarrollo físico y deportes', 1, 1, 'activo', '2025-10-15 20:07:35'),
(6, 'MAT-2', 'Matemáticas II', 'Matemáticas para segundo grado', 1, 2, 'activo', '2025-10-15 20:07:35'),
(7, 'ESP-2', 'Español II', 'Lengua y literatura para segundo grado', 1, 2, 'activo', '2025-10-15 20:07:35'),
(8, 'CNS-2', 'Ciencias Naturales II', 'Ciencias naturales para segundo grado', 1, 2, 'activo', '2025-10-15 20:07:35'),
(9, 'HIS-2', 'Historia II', 'Historia regional', 1, 2, 'activo', '2025-10-15 20:07:35'),
(10, 'EDF-2', 'Educación Física II', 'Educación física para segundo grado', 1, 2, 'activo', '2025-10-15 20:07:35'),
(11, 'MAT-3', 'Matemáticas III', 'Matemáticas para tercer grado', 1, 3, 'activo', '2025-10-15 20:07:35'),
(12, 'ESP-3', 'Español III', 'Lengua y literatura para tercer grado', 1, 3, 'activo', '2025-10-15 20:07:35'),
(13, 'CNS-3', 'Ciencias Naturales III', 'Ciencias naturales para tercer grado', 1, 3, 'activo', '2025-10-15 20:07:35'),
(14, 'HIS-3', 'Historia III', 'Historia nacional', 1, 3, 'activo', '2025-10-15 20:07:35'),
(15, 'EDF-3', 'Educación Física III', 'Educación física para tercer grado', 1, 3, 'activo', '2025-10-15 20:07:35'),
(16, 'MAT-4', 'Matemáticas IV', 'Matemáticas para cuarto grado', 1, 4, 'activo', '2025-10-15 20:07:35'),
(17, 'ESP-4', 'Español IV', 'Lengua y literatura para cuarto grado', 1, 4, 'activo', '2025-10-15 20:07:35'),
(18, 'CNS-4', 'Ciencias Naturales IV', 'Ciencias naturales para cuarto grado', 1, 4, 'activo', '2025-10-15 20:07:35'),
(19, 'HIS-4', 'Historia IV', 'Historia universal', 1, 4, 'activo', '2025-10-15 20:07:35'),
(20, 'EDF-4', 'Educación Física IV', 'Educación física para cuarto grado', 1, 4, 'activo', '2025-10-15 20:07:35'),
(21, 'MAT-5', 'Matemáticas V', 'Matemáticas para quinto grado', 1, 5, 'activo', '2025-10-15 20:07:35'),
(22, 'ESP-5', 'Español V', 'Lengua y literatura para quinto grado', 1, 5, 'activo', '2025-10-15 20:07:35'),
(23, 'CNS-5', 'Ciencias Naturales V', 'Ciencias naturales para quinto grado', 1, 5, 'activo', '2025-10-15 20:07:35'),
(24, 'HIS-5', 'Historia V', 'Historia de México', 1, 5, 'activo', '2025-10-15 20:07:35'),
(25, 'EDF-5', 'Educación Física V', 'Educación física para quinto grado', 1, 5, 'activo', '2025-10-15 20:07:35'),
(26, 'MAT-6', 'Matemáticas VI', 'Matemáticas para sexto grado', 1, 6, 'activo', '2025-10-15 20:07:35'),
(27, 'ESP-6', 'Español VI', 'Lengua y literatura para sexto grado', 1, 6, 'activo', '2025-10-15 20:07:35'),
(28, 'CNS-6', 'Ciencias Naturales VI', 'Ciencias naturales para sexto grado', 1, 6, 'activo', '2025-10-15 20:07:35'),
(29, 'HIS-6', 'Historia VI', 'Historia contemporánea', 1, 6, 'activo', '2025-10-15 20:07:35'),
(30, 'EDF-6', 'Educación Física VI', 'Educación física para sexto grado', 1, 6, 'activo', '2025-10-15 20:07:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inventario_id` int NOT NULL,
  `tipo_movimiento` enum('prestamo','devolucion','mantenimiento','baja','traslado','ajuste') NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `estudiante_id` int DEFAULT NULL,
  `profesor_id` int DEFAULT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `fecha_movimiento` datetime NOT NULL,
  `fecha_devolucion_esperada` datetime DEFAULT NULL,
  `ubicacion_anterior` varchar(200) DEFAULT NULL,
  `ubicacion_nueva` varchar(200) DEFAULT NULL,
  `observaciones` text,
  `estado` enum('activo','completado','cancelado') NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_movimientos_inventario` (`inventario_id`),
  KEY `idx_movimientos_fecha` (`fecha_movimiento`),
  KEY `idx_movimientos_tipo` (`tipo_movimiento`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('info','warning','success','danger') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `leida` tinyint(1) DEFAULT '0',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notificaciones_usuario_leida` (`usuario_id`,`leida`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `titulo`, `mensaje`, `tipo`, `leida`, `fecha_creacion`) VALUES
(1, 1, 'Bienvenido al Sistema', 'Bienvenido al Sistema de Control Escolar. Su cuenta ha sido creada exitosamente.', 'success', 1, '2025-10-15 20:07:36'),
(2, 2, 'Nuevo Horario Asignado', 'Se le ha asignado un nuevo horario para la materia de Matemáticas.', 'info', 0, '2025-10-15 20:07:36'),
(3, 3, 'Recordatorio de Evaluación', 'Recuerde que mañana tiene programada una evaluación de Español.', 'warning', 0, '2025-10-15 20:07:36'),
(4, 4, 'Actualización de Datos', 'Por favor actualice su información personal en el sistema.', 'info', 0, '2025-10-15 20:07:36'),
(5, 5, 'Nueva Calificación', 'Se ha registrado una nueva calificación en su materia de Matemáticas.', 'info', 0, '2025-10-15 20:07:36'),
(6, 6, 'Asistencia Registrada', 'Su asistencia ha sido registrada correctamente para hoy.', 'success', 0, '2025-10-15 20:07:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo_pago` varchar(50) NOT NULL,
  `estudiante_id` int NOT NULL,
  `concepto` enum('matricula','mensualidad','inscripcion','materiales','actividad','multa','otro') NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `monto_pagado` decimal(10,2) DEFAULT '0.00',
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `metodo_pago` enum('efectivo','transferencia','tarjeta','cheque','otro') DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial','vencido','cancelado') NOT NULL DEFAULT 'pendiente',
  `referencia_pago` varchar(100) DEFAULT NULL,
  `observaciones` text,
  `usuario_registro_id` int DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_pago` (`codigo_pago`),
  KEY `idx_pagos_estudiante` (`estudiante_id`),
  KEY `idx_pagos_estado` (`estado`),
  KEY `idx_pagos_fecha_vencimiento` (`fecha_vencimiento`),
  KEY `idx_pagos_codigo` (`codigo_pago`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes_eventos`
--

DROP TABLE IF EXISTS `participantes_eventos`;
CREATE TABLE IF NOT EXISTS `participantes_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `estudiante_id` int DEFAULT NULL,
  `profesor_id` int DEFAULT NULL,
  `tipo_participante` enum('estudiante','profesor','invitado') NOT NULL,
  `nombre_invitado` varchar(200) DEFAULT NULL,
  `asistio` tinyint(1) DEFAULT '0',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_participantes_evento` (`evento_id`),
  KEY `idx_participantes_estudiante` (`estudiante_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos_libros`
--

DROP TABLE IF EXISTS `prestamos_libros`;
CREATE TABLE IF NOT EXISTS `prestamos_libros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libro_id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `profesor_id` int DEFAULT NULL,
  `fecha_prestamo` date NOT NULL,
  `fecha_devolucion_esperada` date NOT NULL,
  `fecha_devolucion_real` date DEFAULT NULL,
  `estado` enum('prestado','devuelto','vencido','perdido') NOT NULL DEFAULT 'prestado',
  `observaciones` text,
  `multa` decimal(10,2) DEFAULT '0.00',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_prestamos_estudiante` (`estudiante_id`),
  KEY `idx_prestamos_libro` (`libro_id`),
  KEY `idx_prestamos_fecha` (`fecha_prestamo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

DROP TABLE IF EXISTS `profesores`;
CREATE TABLE IF NOT EXISTS `profesores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_paterno` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_materno` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `especialidad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `fecha_ingreso` date NOT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_profesores_codigo` (`codigo`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id`, `codigo`, `nombre`, `apellido_paterno`, `apellido_materno`, `fecha_nacimiento`, `especialidad`, `telefono`, `email`, `direccion`, `fecha_ingreso`, `salario`, `usuario_id`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'PROF001', 'Ana', 'García', 'López', NULL, 'Matemáticas', '555-1001', 'ana.garcia@escuela.com', NULL, '2020-08-15', 15000.00, 2, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(2, 'PROF002', 'Roberto', 'Martínez', 'Silva', NULL, 'Español', '555-1002', 'roberto.martinez@escuela.com', NULL, '2020-08-20', 15000.00, 3, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(3, 'PROF003', 'Carmen', 'Rodríguez', 'Pérez', NULL, 'Ciencias', '555-1003', 'carmen.rodriguez@escuela.com', NULL, '2020-09-10', 15000.00, 4, 'activo', '2025-10-15 20:07:35', '2025-10-15 20:07:35'),
(5, 'PROF950', 'carlos', 'garcia', 'García', '1999-06-17', 'matematicas', '12345678876543', '217O02237@itsx.edu.mx', NULL, '2025-10-17', 1200000.00, NULL, 'activo', '2025-10-18 04:19:44', '2025-10-18 04:20:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos_pago`
--

DROP TABLE IF EXISTS `recibos_pago`;
CREATE TABLE IF NOT EXISTS `recibos_pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pago_id` int NOT NULL,
  `numero_recibo` varchar(50) NOT NULL,
  `monto_recibido` decimal(10,2) NOT NULL,
  `fecha_recibo` datetime NOT NULL,
  `metodo_pago` enum('efectivo','transferencia','tarjeta','cheque','otro') NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `usuario_caja_id` int DEFAULT NULL,
  `observaciones` text,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_recibo` (`numero_recibo`),
  KEY `idx_recibos_pago` (`pago_id`),
  KEY `idx_recibos_numero` (`numero_recibo`),
  KEY `idx_recibos_fecha` (`fecha_recibo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('admin','profesor','estudiante') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'estudiante',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre`, `apellido`, `rol`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'admin', '$2y$10$DXgpSZtyzzW44OSyHAFvLeoJzK5f0fZJOlgIsxbncrTnHDQrKD3Bq', 'admin@escuela.com', 'Administrador', 'Sistema', 'admin', 'activo', '2025-10-15 20:07:35', '2025-10-28 04:58:26'),
(2, 'prof1', '$2y$10$XhS/s1yLR9Nvlm2PN9SsNuNszzkF5c5nw3f62jmL6v/PK0do.2nem', 'ana.garcia@escuela.com', 'Ana', 'García López', 'profesor', 'activo', '2025-10-15 20:07:35', '2025-10-27 20:49:41'),
(3, 'prof2', '$2y$10$XhS/s1yLR9Nvlm2PN9SsNuNszzkF5c5nw3f62jmL6v/PK0do.2nem', 'roberto.martinez@escuela.com', 'Roberto', 'Martínez Silva', 'profesor', 'activo', '2025-10-15 20:07:35', '2025-10-27 20:49:41'),
(4, 'prof3', '$2y$10$XhS/s1yLR9Nvlm2PN9SsNuNszzkF5c5nw3f62jmL6v/PK0do.2nem', 'carmen.rodriguez@escuela.com', 'Carmen', 'Rodríguez Pérez', 'profesor', 'activo', '2025-10-15 20:07:35', '2025-10-27 20:49:41'),
(5, 'est1', '$2y$10$XhS/s1yLR9Nvlm2PN9SsNuNszzkF5c5nw3f62jmL6v/PK0do.2nem', 'juan.perez@email.com', 'Juan', 'Pérez García', 'estudiante', 'activo', '2025-10-15 20:07:35', '2025-10-27 20:49:41'),
(6, 'est2', '$2y$10$XhS/s1yLR9Nvlm2PN9SsNuNszzkF5c5nw3f62jmL6v/PK0do.2nem', 'maria.lopez@email.com', 'María', 'López Rodríguez', 'estudiante', 'activo', '2025-10-15 20:07:35', '2025-10-27 20:49:41');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
