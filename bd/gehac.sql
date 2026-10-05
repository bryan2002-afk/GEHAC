-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.7 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.20.0.7320
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para gehac
CREATE DATABASE IF NOT EXISTS `gehac` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `gehac`;

-- Volcando estructura para tabla gehac.actividad
CREATE TABLE IF NOT EXISTS `actividad` (
  `id_actividad` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `fecha` date NOT NULL,
  `lugar` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `ponente` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `imagen` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `id_departamento` int NOT NULL,
  `id_semestre` int NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `cupo` int DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_actividad`),
  KEY `fk_actividad_departamento` (`id_departamento`),
  KEY `fk_actividad_semestre` (`id_semestre`),
  CONSTRAINT `fk_actividad_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamento` (`id_departamento`),
  CONSTRAINT `fk_actividad_semestre` FOREIGN KEY (`id_semestre`) REFERENCES `semestre` (`id_semestre`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.actividad: ~2 rows (aproximadamente)
INSERT INTO `actividad` (`id_actividad`, `nombre`, `hora_inicio`, `hora_fin`, `fecha`, `lugar`, `ponente`, `descripcion`, `imagen`, `id_departamento`, `id_semestre`, `fecha_registro`, `cupo`, `estado`) VALUES
	(15, 'Día de la Samaritana', '12:00:00', '13:00:00', '2026-06-02', 'Piso D - UMMA', 'Alumnos', 'Traer Vaso de plastico', 'img_6a1c7618ef9df.jpg', 2, 2, '2026-05-31 11:55:36', 2, 0),
	(16, 'Ceremonia Inauguración', '10:00:00', '10:30:00', '2026-06-05', 'Piso D - UMMA', 'Coordinación', 'Llegar temprano', 'img_6a1c766002bf9.png', 4, 2, '2026-05-31 11:56:48', 10, 1);

-- Volcando estructura para tabla gehac.alumno_inscrito
CREATE TABLE IF NOT EXISTS `alumno_inscrito` (
  `id_inscrito` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_semestre` int NOT NULL,
  `horas_culturales` int NOT NULL DEFAULT '0',
  `horas_academicas` int NOT NULL DEFAULT '0',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_inscrito`),
  KEY `fk_inscrito_usuario` (`id_usuario`),
  KEY `fk_inscrito_semestre` (`id_semestre`),
  CONSTRAINT `fk_inscrito_semestre` FOREIGN KEY (`id_semestre`) REFERENCES `semestre` (`id_semestre`),
  CONSTRAINT `fk_inscrito_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.alumno_inscrito: ~3 rows (aproximadamente)
INSERT INTO `alumno_inscrito` (`id_inscrito`, `id_usuario`, `id_semestre`, `horas_culturales`, `horas_academicas`, `fecha_registro`) VALUES
	(53, 47, 2, 0, 0, '2026-05-31 11:53:09'),
	(54, 46, 2, 0, 0, '2026-05-31 11:53:09'),
	(55, 18, 2, 0, 2, '2026-05-31 11:53:09');

-- Volcando estructura para tabla gehac.blog
CREATE TABLE IF NOT EXISTS `blog` (
  `id_blog` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `contenido` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_blog`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.blog: 1 rows
INSERT INTO `blog` (`id_blog`, `nombre`, `contenido`, `fecha_registro`) VALUES
	(11, 'Blog 1', 'Un blog es una herramienta digital que permite publicar contenido en línea de forma regular y accesible.', '2026-05-28 12:12:57');

-- Volcando estructura para tabla gehac.carrera
CREATE TABLE IF NOT EXISTS `carrera` (
  `id_carrera` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `revoe` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_carrera`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.carrera: ~6 rows (aproximadamente)
INSERT INTO `carrera` (`id_carrera`, `nombre`, `revoe`, `fecha_registro`) VALUES
	(1, 'Ing. Sistemas Computacionales', 'OLS', '2026-05-20 13:02:03'),
	(2, 'Ing. Civil', 'CVS', '2026-05-21 17:04:31'),
	(3, 'Gastronomia', 'GTR', '2026-05-21 17:05:30'),
	(4, 'Psicopedagogía', 'PSG', '2026-05-21 17:06:03'),
	(8, 'Arquitectura', 'ARQ', '2026-05-21 17:43:10'),
	(9, 'Diseño Grafico', 'DSG', '2026-05-27 08:42:25');

-- Volcando estructura para tabla gehac.categoria
CREATE TABLE IF NOT EXISTS `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.categoria: ~2 rows (aproximadamente)
INSERT INTO `categoria` (`id_categoria`, `nombre`, `fecha_registro`) VALUES
	(3, 'Actividad Académica', '2026-05-22 19:30:27'),
	(5, 'Actividad Cultural', '2026-05-22 19:35:19');

-- Volcando estructura para tabla gehac.departamento
CREATE TABLE IF NOT EXISTS `departamento` (
  `id_departamento` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jefe` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_departamento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.departamento: ~2 rows (aproximadamente)
INSERT INTO `departamento` (`id_departamento`, `nombre`, `jefe`, `fecha_registro`) VALUES
	(2, 'Departamento de Difusión y Promoción', 'Licenciada. Marisol Hernandez', '2026-05-22 14:12:58'),
	(4, 'Coordinación Académica', 'Licenciado. Antonio Garcia Muñoz', '2026-05-22 14:23:54');

-- Volcando estructura para tabla gehac.disponible
CREATE TABLE IF NOT EXISTS `disponible` (
  `id_disponible` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_actividad` int NOT NULL,
  `asistencia` tinyint(1) DEFAULT '0',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `procesado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_disponible`),
  KEY `fk_disponible_actividad` (`id_actividad`),
  KEY `fk_disponible_usuario` (`id_usuario`),
  CONSTRAINT `fk_disponible_actividad` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`),
  CONSTRAINT `fk_disponible_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.disponible: ~4 rows (aproximadamente)
INSERT INTO `disponible` (`id_disponible`, `id_usuario`, `id_actividad`, `asistencia`, `fecha_registro`, `procesado`) VALUES
	(44, 18, 15, 0, '2026-05-31 12:19:42', 0),
	(45, 18, 16, 1, '2026-05-31 12:19:45', 1),
	(46, 47, 15, 0, '2026-05-31 12:20:10', 0),
	(47, 47, 16, 0, '2026-05-31 12:20:15', 0);

-- Volcando estructura para tabla gehac.escuela
CREATE TABLE IF NOT EXISTS `escuela` (
  `id_escuela` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `direccion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_escuela`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.escuela: 1 rows
INSERT INTO `escuela` (`id_escuela`, `nombre`, `direccion`, `clave`, `fecha_registro`) VALUES
	(16, 'Universidad Mundo Maya, Campus Oaxaca.', 'Av. Lázaro Cárdenas No. 3032-Lote-9, Centro, Tel. 205 47 16 Santa Lucía del Camino, Oax.', 'UMMA', '2026-05-20 22:16:27');

-- Volcando estructura para tabla gehac.hora
CREATE TABLE IF NOT EXISTS `hora` (
  `id_hora` int NOT NULL AUTO_INCREMENT,
  `id_actividad` int NOT NULL,
  `id_categoria` int NOT NULL,
  `horas` bigint NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_hora`),
  KEY `fk_hora_actividad` (`id_actividad`),
  KEY `fk_hora_categoria` (`id_categoria`),
  CONSTRAINT `fk_hora_actividad` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`),
  CONSTRAINT `fk_hora_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.hora: ~2 rows (aproximadamente)
INSERT INTO `hora` (`id_hora`, `id_actividad`, `id_categoria`, `horas`, `fecha_registro`) VALUES
	(16, 16, 3, 2, '2026-05-31 11:57:04'),
	(17, 15, 5, 5, '2026-05-31 11:57:12');

-- Volcando estructura para tabla gehac.modalidad
CREATE TABLE IF NOT EXISTS `modalidad` (
  `id_modalidad` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `horas_academicas` int NOT NULL DEFAULT '30',
  `horas_culturales` int NOT NULL DEFAULT '15',
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_modalidad`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.modalidad: ~2 rows (aproximadamente)
INSERT INTO `modalidad` (`id_modalidad`, `nombre`, `horas_academicas`, `horas_culturales`, `fecha_registro`) VALUES
	(15, 'Sabatino', 15, 15, '2026-05-20 21:50:39'),
	(16, 'Escolarizado', 30, 15, '2026-05-20 21:50:48');

-- Volcando estructura para tabla gehac.modulo
CREATE TABLE IF NOT EXISTS `modulo` (
  `id_modulo` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `archivo_php` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `icono` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.modulo: ~16 rows (aproximadamente)
INSERT INTO `modulo` (`id_modulo`, `nombre`, `archivo_php`, `icono`, `fecha_registro`) VALUES
	(1, 'Dashboard', 'dashboard.php', 'fas fa-th-larg', '2026-05-27 16:11:45'),
	(2, 'Usuarios', 'usuarios.php', 'fas fa-users', '2026-05-27 16:14:49'),
	(3, 'Roles', 'roles.php', 'fas fa-user-shield', '2026-05-27 16:14:49'),
	(4, 'Blogs', 'blogs.php', 'fas fa-blog', '2026-05-27 16:14:49'),
	(5, 'Escuelas', 'escuelas.php', 'fas fa-school', '2026-05-27 16:14:49'),
	(6, 'Modalidades', 'modalidades.php', 'fas fa-laptop-house', '2026-05-27 16:14:49'),
	(7, 'Carreras', 'carreras.php', 'fas fa-graduation-cap', '2026-05-27 16:14:49'),
	(8, 'Semestres', 'semestres.php', 'fas fa-calendar-alt', '2026-05-27 16:14:49'),
	(9, 'Alumnos', 'alumnos.php', 'fas fa-user-graduate', '2026-05-27 16:14:49'),
	(10, 'Departamentos', 'departamentos.php', 'fas fa-building', '2026-05-27 16:14:49'),
	(11, 'Actividades', 'actividades.php', 'fas fa-tasks', '2026-05-27 16:14:49'),
	(12, 'Categorias', 'categorias.php', 'fas fa-tags', '2026-05-27 16:14:49'),
	(13, 'Número de Horas', 'horas.php', 'fas fa-clock', '2026-05-27 16:14:49'),
	(14, 'Alumnos Inscritos', 'alumnos_inscritos.php', 'fas fa-user-check', '2026-05-27 16:14:49'),
	(15, 'Inscritos a Actividades', 'inscritos_actividades.php', 'fas fa-clipboard-check', '2026-05-27 16:14:49'),
	(16, 'Catalogo de Actividades', 'catalogo.php', 'fas fa-book-open', '2026-05-27 16:14:49');

-- Volcando estructura para tabla gehac.permiso
CREATE TABLE IF NOT EXISTS `permiso` (
  `id_permiso` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_permiso`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.permiso: ~4 rows (aproximadamente)
INSERT INTO `permiso` (`id_permiso`, `nombre`, `fecha_registro`) VALUES
	(1, 'VER', '2026-05-27 15:54:09'),
	(2, 'CREAR', '2026-05-27 15:54:16'),
	(3, 'EDITAR', '2026-05-27 15:54:23'),
	(4, 'BORRAR', '2026-05-27 15:54:34');

-- Volcando estructura para tabla gehac.rol
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.rol: ~3 rows (aproximadamente)
INSERT INTO `rol` (`id_rol`, `nombre`, `descripcion`, `fecha_registro`) VALUES
	(1, 'Administrador', 'Administra todo el sistema GEHAC.', '2026-05-20 13:09:45'),
	(3, 'Alumno', 'Consulta sus horas realizadas de actividades, y puede inscribirse a actividades.', '2026-05-20 13:10:01'),
	(4, 'Coordinador', 'Administra actividades y registros.', '2026-05-21 15:30:33');

-- Volcando estructura para tabla gehac.rol_permiso
CREATE TABLE IF NOT EXISTS `rol_permiso` (
  `id_rol_permiso` int NOT NULL AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `id_modulo` int NOT NULL,
  `id_permiso` int NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rol_permiso`),
  KEY `fk_rol_permiso_rol` (`id_rol`),
  KEY `fk_rol_permiso_modulo` (`id_modulo`),
  KEY `fk_rol_permiso_permiso` (`id_permiso`),
  CONSTRAINT `fk_rol_permiso_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rol_permiso_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permiso` (`id_permiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rol_permiso_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.rol_permiso: ~99 rows (aproximadamente)
INSERT INTO `rol_permiso` (`id_rol_permiso`, `id_rol`, `id_modulo`, `id_permiso`, `fecha_registro`) VALUES
	(1, 1, 1, 1, '2026-05-27 16:28:24'),
	(2, 1, 2, 1, '2026-05-27 16:29:12'),
	(3, 1, 3, 1, '2026-05-27 16:29:39'),
	(4, 1, 4, 1, '2026-05-27 16:31:57'),
	(5, 1, 5, 1, '2026-05-27 16:31:57'),
	(6, 1, 6, 1, '2026-05-27 16:31:57'),
	(7, 1, 7, 1, '2026-05-27 16:31:57'),
	(8, 1, 8, 1, '2026-05-27 16:31:57'),
	(9, 1, 9, 1, '2026-05-27 16:31:57'),
	(10, 1, 10, 1, '2026-05-27 16:31:57'),
	(11, 1, 11, 1, '2026-05-27 16:31:57'),
	(12, 1, 12, 1, '2026-05-27 16:31:57'),
	(13, 1, 13, 1, '2026-05-27 16:31:57'),
	(14, 1, 14, 1, '2026-05-27 16:31:57'),
	(15, 1, 15, 1, '2026-05-27 16:31:57'),
	(16, 1, 16, 1, '2026-05-27 16:31:57'),
	(17, 1, 1, 2, '2026-05-27 17:23:57'),
	(18, 1, 2, 2, '2026-05-27 17:23:57'),
	(19, 1, 3, 2, '2026-05-27 17:23:57'),
	(20, 1, 4, 2, '2026-05-27 17:23:57'),
	(21, 1, 5, 2, '2026-05-27 17:23:57'),
	(22, 1, 6, 2, '2026-05-27 17:23:57'),
	(23, 1, 7, 2, '2026-05-27 17:23:57'),
	(24, 1, 8, 2, '2026-05-27 17:23:57'),
	(25, 1, 9, 2, '2026-05-27 17:23:57'),
	(26, 1, 10, 2, '2026-05-27 17:23:57'),
	(27, 1, 11, 2, '2026-05-27 17:23:57'),
	(28, 1, 12, 2, '2026-05-27 17:23:57'),
	(29, 1, 13, 2, '2026-05-27 17:23:57'),
	(30, 1, 14, 2, '2026-05-27 17:23:57'),
	(31, 1, 15, 2, '2026-05-27 17:23:57'),
	(32, 1, 16, 2, '2026-05-27 17:23:57'),
	(33, 1, 1, 3, '2026-05-27 17:37:26'),
	(34, 1, 2, 3, '2026-05-27 17:37:26'),
	(35, 1, 3, 3, '2026-05-27 17:37:26'),
	(36, 1, 4, 3, '2026-05-27 17:37:26'),
	(37, 1, 5, 3, '2026-05-27 17:37:26'),
	(38, 1, 6, 3, '2026-05-27 17:37:26'),
	(39, 1, 7, 3, '2026-05-27 17:37:26'),
	(40, 1, 8, 3, '2026-05-27 17:37:26'),
	(41, 1, 9, 3, '2026-05-27 17:37:26'),
	(42, 1, 10, 3, '2026-05-27 17:37:26'),
	(43, 1, 11, 3, '2026-05-27 17:37:26'),
	(44, 1, 12, 3, '2026-05-27 17:37:26'),
	(45, 1, 13, 3, '2026-05-27 17:37:26'),
	(46, 1, 14, 3, '2026-05-27 17:37:26'),
	(47, 1, 15, 3, '2026-05-27 17:37:26'),
	(48, 1, 16, 3, '2026-05-27 17:37:26'),
	(49, 1, 1, 4, '2026-05-27 17:38:11'),
	(50, 1, 2, 4, '2026-05-27 17:38:11'),
	(51, 1, 3, 4, '2026-05-27 17:38:11'),
	(52, 1, 4, 4, '2026-05-27 17:38:11'),
	(53, 1, 5, 4, '2026-05-27 17:38:11'),
	(54, 1, 6, 4, '2026-05-27 17:38:11'),
	(55, 1, 7, 4, '2026-05-27 17:38:11'),
	(56, 1, 8, 4, '2026-05-27 17:38:11'),
	(57, 1, 9, 4, '2026-05-27 17:38:11'),
	(58, 1, 10, 4, '2026-05-27 17:38:11'),
	(59, 1, 11, 4, '2026-05-27 17:38:11'),
	(60, 1, 12, 4, '2026-05-27 17:38:11'),
	(61, 1, 13, 4, '2026-05-27 17:38:11'),
	(62, 1, 14, 4, '2026-05-27 17:38:11'),
	(63, 1, 15, 4, '2026-05-27 17:38:11'),
	(64, 1, 16, 4, '2026-05-27 17:38:11'),
	(65, 4, 1, 1, '2026-05-27 22:59:07'),
	(66, 4, 2, 1, '2026-05-27 22:59:07'),
	(68, 4, 4, 1, '2026-05-27 22:59:07'),
	(70, 4, 6, 1, '2026-05-27 22:59:07'),
	(72, 4, 8, 1, '2026-05-27 22:59:07'),
	(73, 4, 9, 1, '2026-05-27 22:59:07'),
	(75, 4, 11, 1, '2026-05-27 22:59:07'),
	(77, 4, 13, 1, '2026-05-27 22:59:07'),
	(78, 4, 14, 1, '2026-05-27 22:59:07'),
	(79, 4, 15, 1, '2026-05-27 22:59:07'),
	(80, 4, 16, 1, '2026-05-27 22:59:07'),
	(81, 4, 2, 2, '2026-05-27 23:04:07'),
	(82, 4, 2, 3, '2026-05-27 23:05:10'),
	(83, 4, 2, 4, '2026-05-27 23:05:28'),
	(84, 4, 4, 2, '2026-05-27 23:08:00'),
	(85, 4, 4, 3, '2026-05-27 23:08:25'),
	(86, 4, 4, 4, '2026-05-27 23:08:39'),
	(87, 4, 6, 3, '2026-05-27 23:12:57'),
	(88, 4, 6, 2, '2026-05-27 23:13:37'),
	(89, 4, 6, 4, '2026-05-27 23:14:07'),
	(90, 4, 8, 2, '2026-05-27 23:16:23'),
	(91, 4, 8, 3, '2026-05-27 23:16:51'),
	(92, 4, 8, 4, '2026-05-27 23:17:04'),
	(93, 4, 9, 2, '2026-05-27 23:19:01'),
	(94, 4, 9, 3, '2026-05-27 23:19:17'),
	(95, 4, 11, 2, '2026-05-27 23:22:46'),
	(96, 4, 11, 3, '2026-05-27 23:23:02'),
	(97, 4, 11, 4, '2026-05-27 23:23:14'),
	(98, 4, 13, 2, '2026-05-27 23:25:19'),
	(99, 4, 13, 3, '2026-05-27 23:25:38'),
	(100, 4, 13, 4, '2026-05-27 23:25:52'),
	(101, 4, 14, 2, '2026-05-27 23:27:02'),
	(102, 4, 14, 3, '2026-05-27 23:27:26'),
	(103, 4, 14, 4, '2026-05-27 23:27:40'),
	(104, 4, 15, 2, '2026-05-27 23:29:17'),
	(105, 4, 15, 3, '2026-05-27 23:29:46'),
	(106, 4, 15, 4, '2026-05-27 23:30:01'),
	(107, 4, 16, 2, '2026-05-27 23:30:46'),
	(108, 3, 16, 1, '2026-05-27 23:35:18'),
	(109, 3, 16, 2, '2026-05-27 23:36:38');

-- Volcando estructura para tabla gehac.semestre
CREATE TABLE IF NOT EXISTS `semestre` (
  `id_semestre` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `fecha_registro` datetime DEFAULT (now()),
  PRIMARY KEY (`id_semestre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.semestre: ~1 rows (aproximadamente)
INSERT INTO `semestre` (`id_semestre`, `nombre`, `periodo_inicio`, `periodo_fin`, `fecha_registro`) VALUES
	(2, 'Periodo Enero-Junio 2026', '2026-01-15', '2026-06-15', '2026-05-21 18:31:44');

-- Volcando estructura para tabla gehac.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_s` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `apellido_p` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `apellido_m` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `matricula` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `correo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_carrera` int DEFAULT NULL,
  `id_modalidad` int DEFAULT NULL,
  `id_rol` int DEFAULT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`) USING BTREE,
  UNIQUE KEY `correo` (`correo`),
  KEY `fk_usuario_carrera` (`id_carrera`) USING BTREE,
  KEY `fk_usuario_modalidad` (`id_modalidad`) USING BTREE,
  KEY `fk_usuario_rol` (`id_rol`) USING BTREE,
  CONSTRAINT `fk_usuario_carrera` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`),
  CONSTRAINT `fk_usuario_modalidad` FOREIGN KEY (`id_modalidad`) REFERENCES `modalidad` (`id_modalidad`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gehac.usuario: ~5 rows (aproximadamente)
INSERT INTO `usuario` (`id_usuario`, `nombre`, `nombre_s`, `apellido_p`, `apellido_m`, `matricula`, `estado`, `correo`, `password`, `id_carrera`, `id_modalidad`, `id_rol`, `fecha_registro`) VALUES
	(18, 'Bryan', 'Julian', 'Suriano', 'Ramirez', 'ols2231001', 1, 'ols2231001@UNIVERSIDADMUNDOMAYA.edu.mx', '$2y$10$QE1ETVnaMgvoRoH6Qj1/3eeE0z7sHw8LD6bxn/h/fNNb6PoMF4Tt2', 1, 16, 3, '2026-05-22 09:08:58'),
	(43, 'Antonio', '', 'Garcia', 'Muñoz', NULL, 1, 'antoniogarcia@gmail.com', '$2y$10$0nBaGmVEcpoPJsseX3iY7u9NqAsqnnluUIjUNutKAwrO6BuAc1sm6', NULL, NULL, 4, '2026-05-27 17:44:22'),
	(44, 'Antonio', '', 'Garcia', 'Muñoz', '', 1, 'admin@UNIVERSIDADMUNDOMAYA.edu.mx', '$2y$10$oVTWr1czIauM1rqdmdbNNey4iidi6u0dIyX1tq3f07nbFmcMEYMNe', NULL, NULL, 1, '2026-05-27 17:49:39'),
	(46, 'Jesus', 'Antonio', 'Martinez', 'Martinez', 'dsg2231001', 1, 'dsg2231001@UNIVERSIDADMUNDOMAYA.edu.mx', '$2y$10$GMLz5ol/vnGBksPuKP8vt.EfNwBZ62xatudDffr7qmPZMSu3G9QpC', 9, 16, 3, '2026-05-28 12:39:23'),
	(47, 'Joana', 'Jazmin', 'Reyes', 'De la Cruz', 'ols2231002', 1, 'ols2231002@UNIVERSIDADMUNDOMAYA.edu.mx', '$2y$10$mPPt4B7or7khRyu8wyVnIO.IRFGEtMPHEwZRfli9tlf0a4wi6KP0a', 1, 15, 3, '2026-05-31 11:09:40');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
