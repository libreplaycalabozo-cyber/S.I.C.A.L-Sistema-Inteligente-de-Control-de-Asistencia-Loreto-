-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-10-2026 a las 15:50:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `control_asistencia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anio_escolar_activo`
--

CREATE TABLE `anio_escolar_activo` (
  `id` int(11) NOT NULL,
  `periodo` varchar(20) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `bloqueado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones_profesor`
--

CREATE TABLE `asignaciones_profesor` (
  `id` int(11) NOT NULL,
  `profesor1_id` int(11) NOT NULL,
  `profesor2_id` int(11) NOT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `seccion_id` int(11) DEFAULT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_estudiantes`
--

CREATE TABLE `asistencia_estudiantes` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) DEFAULT NULL,
  `asignacion_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_registro` time NOT NULL,
  `estatus` enum('presente','ausente','tarde') NOT NULL,
  `hora_marca` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_personal`
--

CREATE TABLE `asistencia_personal` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estatus_laboral` enum('presente','reposo','permiso_especial','evacuado_emergencia','justificado_decreto') DEFAULT 'presente',
  `salida_anticipada_autorizada` int(11) DEFAULT 0,
  `motivo_salida_anticipada` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carga_academica_docente`
--

CREATE TABLE `carga_academica_docente` (
  `id` int(11) NOT NULL,
  `profesor1_id` int(11) NOT NULL,
  `profesor2_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `seccion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL,
  `nombre_institucion` varchar(255) NOT NULL,
  `logo_institucion` varchar(255) NOT NULL,
  `hora_salida_legal` time DEFAULT '14:20:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id`, `nombre_institucion`, `logo_institucion`, `hora_salida_legal`) VALUES
(1, 'E.T.C Dr Rafael Loreto Loreto', 'img/logo_institucional.png', '14:20:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `decretos_feriados`
--

CREATE TABLE `decretos_feriados` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('feriado','decreto') NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `numero_decreto` varchar(100) DEFAULT NULL,
  `ente_emisor` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `seccion_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horas_administrativas_docente`
--

CREATE TABLE `horas_administrativas_docente` (
  `id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `horas_asignadas` int(11) NOT NULL DEFAULT 0,
  `motivo_cargo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `nombre_materia` varchar(100) NOT NULL,
  `horas_semanales` int(11) NOT NULL DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `nombre_materia`, `horas_semanales`) VALUES
(56, 'Lengua y Literatura (1° y 2° Año)', 3),
(57, 'Lengua y Literatura (3°, 4° y 5° Año)', 4),
(58, 'Matemáticas (1° a 5° Año)', 4),
(59, 'Educación Física (1°, 2° y 3er Año)', 2),
(60, 'Biología, Ambiente y Tecnología (1° y 2° Año)', 4),
(61, 'Biología, Ambiente y Tecnología (3°, 4° y 5° Año)', 8),
(62, 'Geografía, Historia, Ciudadanía (1° y 2° Año)', 4),
(63, 'Geografía, Historia, Ciudadanía (3°, 4° y 5° Año)', 2),
(64, 'Proyecto de Economía Socioproductiva (1° a 5° Año)', 8),
(65, 'Orientación y Vinculación Vocacional (1° y 2° Año)', 4),
(66, 'Orientación y Vinculación Vocacional (3°, 4° y 5° Año)', 2),
(67, 'Idiomas - Administración / Telemática (1° y 2° Año)', 3),
(68, 'Idiomas - Administración / Telemática (3°, 4° y 5° Año)', 4),
(69, 'Idiomas - Mención Turismo (4° y 5° Año)', 6),
(70, 'Gestión y Administración de Servicios Turísticos y Hotelería (1° a 5° Año)', 8),
(71, 'Diseño, Planificación y Org. de Sistemas Administrativos (1°, 2° y 3er Año)', 8),
(72, 'Diseño, Planificación y Org. de Sistemas Administrativos (4° y 5° Año)', 10),
(73, 'Programación y Diseño de Software y Redes (1°, 2° y 3er Año)', 8),
(74, 'Programación y Diseño de Software y Redes (4° y 5° Año)', 10),
(75, 'Práctica Profesional - Mención Turismo (6to Año)', 1440),
(76, 'Práctica Profesional - Mención Administración (6to Año)', 1440),
(77, 'Práctica Profesional - Mención Telemática (6to Año)', 1440);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones`
--

CREATE TABLE `secciones` (
  `id` int(11) NOT NULL,
  `nombre_seccion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `secciones`
--

INSERT INTO `secciones` (`id`, `nombre_seccion`) VALUES
(27, '1er Año - Sección Turismo'),
(28, '2do Año - Sección Turismo'),
(29, '3er Año - Sección Turismo'),
(30, '4to Año - Sección Turismo'),
(31, '5to Año - Sección Turismo'),
(32, '6to Año - Sección Turismo'),
(33, '1er Año - Sección Administración'),
(34, '2do Año - Sección Administración'),
(35, '3er Año - Sección Administración'),
(36, '4to Año - Sección Administración'),
(37, '5to Año - Sección Administración'),
(38, '6to Año - Sección Administración'),
(39, '1er Año - Sección Telemática'),
(40, '2do Año - Sección Telemática'),
(41, '3er Año - Sección Telemática'),
(42, '4to Año - Sección Telemática'),
(43, '5to Año - Sección Telemática'),
(44, '6to Año - Sección Telemática');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `rol` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anio_escolar_activo`
--
ALTER TABLE `anio_escolar_activo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaciones_profesor`
--
ALTER TABLE `asignaciones_profesor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profesor_id` (`profesor1_id`),
  ADD KEY `materia_id` (`materia_id`),
  ADD KEY `seccion_id` (`seccion_id`);

--
-- Indices de la tabla `asistencia_estudiantes`
--
ALTER TABLE `asistencia_estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `asignacion_id` (`asignacion_id`);

--
-- Indices de la tabla `asistencia_personal`
--
ALTER TABLE `asistencia_personal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `carga_academica_docente`
--
ALTER TABLE `carga_academica_docente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profesor_id` (`profesor1_id`),
  ADD KEY `materia_id` (`materia_id`),
  ADD KEY `seccion_id` (`seccion_id`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `decretos_feriados`
--
ALTER TABLE `decretos_feriados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fecha` (`fecha`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `seccion_id` (`seccion_id`);

--
-- Indices de la tabla `horas_administrativas_docente`
--
ALTER TABLE `horas_administrativas_docente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profesor_id` (`profesor_id`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anio_escolar_activo`
--
ALTER TABLE `anio_escolar_activo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaciones_profesor`
--
ALTER TABLE `asignaciones_profesor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_estudiantes`
--
ALTER TABLE `asistencia_estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_personal`
--
ALTER TABLE `asistencia_personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carga_academica_docente`
--
ALTER TABLE `carga_academica_docente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `decretos_feriados`
--
ALTER TABLE `decretos_feriados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horas_administrativas_docente`
--
ALTER TABLE `horas_administrativas_docente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignaciones_profesor`
--
ALTER TABLE `asignaciones_profesor`
  ADD CONSTRAINT `asignaciones_profesor_ibfk_1` FOREIGN KEY (`profesor1_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asignaciones_profesor_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asignaciones_profesor_ibfk_3` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencia_estudiantes`
--
ALTER TABLE `asistencia_estudiantes`
  ADD CONSTRAINT `asistencia_estudiantes_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asistencia_estudiantes_ibfk_2` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones_profesor` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencia_personal`
--
ALTER TABLE `asistencia_personal`
  ADD CONSTRAINT `asistencia_personal_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carga_academica_docente`
--
ALTER TABLE `carga_academica_docente`
  ADD CONSTRAINT `carga_academica_docente_ibfk_1` FOREIGN KEY (`profesor1_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carga_academica_docente_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carga_academica_docente_ibfk_3` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `horas_administrativas_docente`
--
ALTER TABLE `horas_administrativas_docente`
  ADD CONSTRAINT `horas_administrativas_docente_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
