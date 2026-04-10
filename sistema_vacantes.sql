-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-04-2026 a las 17:54:32
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
-- Base de datos: `sistema_vacantes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `criterios_evaluacion`
--

CREATE TABLE `criterios_evaluacion` (
  `id` int(11) NOT NULL,
  `proceso_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `peso` decimal(3,1) DEFAULT 1.0,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones_postulantes`
--

CREATE TABLE `evaluaciones_postulantes` (
  `id` int(11) NOT NULL,
  `postulacion_id` int(11) NOT NULL,
  `criterio_id` int(11) NOT NULL,
  `puntaje` decimal(3,1) NOT NULL,
  `comentario` text DEFAULT NULL,
  `evaluador_id` int(11) NOT NULL,
  `fecha_evaluacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulaciones`
--

CREATE TABLE `postulaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `vacante_id` int(11) NOT NULL,
  `fecha_postulacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aprobado','rechazado','aceptada','rechazada') DEFAULT 'pendiente',
  `es_ganador` tinyint(1) DEFAULT 0,
  `fecha_seleccion` timestamp NULL DEFAULT NULL,
  `comentario_seleccion` text DEFAULT NULL,
  `puntaje` decimal(3,1) DEFAULT 0.0,
  `archivo` varchar(255) NOT NULL,
  `fecha_evaluacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulaciones`
--

INSERT INTO `postulaciones` (`id`, `usuario_id`, `vacante_id`, `fecha_postulacion`, `estado`, `es_ganador`, `fecha_seleccion`, `comentario_seleccion`, `puntaje`, `archivo`, `fecha_evaluacion`) VALUES
(1, 1, 2, '2025-11-27 21:45:14', 'rechazada', 0, NULL, 'Poca experiencia', 8.0, 'uploads/cv_6928c669e405b_CV._Carla_M_Espinosa_A..pdf', '2026-04-08 23:47:51'),
(2, 1, 2, '2026-04-09 02:16:55', 'rechazada', 0, NULL, NULL, 0.0, 'uploads/cv_69d70c1788e49_CV_KAREN_ESPINOSA_.pdf', NULL),
(3, 1, 1, '2026-04-09 02:17:03', 'aprobado', 0, NULL, NULL, 0.0, 'uploads/cv_69d70c1feab5a_Hoja_de_Vida_Miguel_Cardenas_v2.pdf', NULL),
(4, 5, 1, '2026-04-09 02:37:22', 'aprobado', 0, NULL, NULL, 0.0, 'uploads/cv_69d710e2a2bae_CEDULA_Y_PAPELETA_CARLA_ESPINOSA.docx', NULL),
(5, 5, 1, '2026-04-09 02:37:39', 'rechazado', 0, NULL, NULL, 0.0, 'uploads/cv_69d710f3296d8_Hoja_de_Vida_Miguel_Cardenas_v2.pdf', NULL),
(6, 6, 2, '2026-04-09 02:40:09', 'aceptada', 1, '2026-04-09 04:30:57', 'Cumple todos requisitos', 9.5, 'uploads/cv_69d7118980189_CV_KAREN_ESPINOSA_.pdf', '2026-04-08 23:47:51'),
(7, 8, 2, '2026-04-09 03:21:15', 'rechazada', 0, NULL, '                        Muy joven', 7.0, 'uploads/cv_69d71b2b81149_HOJA_DE_VIDA_Micaella_Espinosa.pdf', '2026-04-08 23:47:51'),
(8, 1, 3, '2026-04-09 04:05:08', 'rechazada', 0, NULL, '', 8.5, 'uploads/cv_69d72574b2007_HOJA_DE_VIDA_Micaella_Espinosa.pdf', '2026-04-09 23:28:34'),
(9, 1, 3, '2026-04-09 04:05:16', 'aceptada', 1, '2026-04-10 04:28:48', '', 8.6, 'uploads/cv_69d7257c5d146_CV_KAREN_ESPINOSA_.pdf', '2026-04-09 23:28:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proceso_seleccion`
--

CREATE TABLE `proceso_seleccion` (
  `id` int(11) NOT NULL,
  `vacante_id` int(11) NOT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `estado` enum('abierto','cerrado','finalizado') DEFAULT 'abierto',
  `numero_ganadores` int(11) DEFAULT 1,
  `criterios_seleccion` text DEFAULT NULL,
  `admin_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `proceso_seleccion`
--

INSERT INTO `proceso_seleccion` (`id`, `vacante_id`, `fecha_inicio`, `fecha_cierre`, `estado`, `numero_ganadores`, `criterios_seleccion`, `admin_id`) VALUES
(1, 2, '2026-04-09 03:40:03', '2026-04-09 04:30:57', 'finalizado', 1, 'Evaluación por puntaje y entrevista previa.  ', 1),
(2, 3, '2026-04-10 04:28:14', '2026-04-10 04:28:48', 'finalizado', 1, 'Nota más alta', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `apellido` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('empleado','admin') DEFAULT 'empleado',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'Admin', 'General', 'admin@xyz.com', '$2y$10$pL6ENfZ1M9dL9FVsQkqyhuDkCOVJPoUz04VeCsylcN0pg1h39F1H2', 'admin', '2025-11-17 04:46:41'),
(3, 'Admin', 'XYZ', 'admin123@xyz.com', '$2y$10$9gSSQdVJBoYk4ZgUqIBGAuGvYd64SgY1gGD7W82dManIeZDV4SSy2', 'admin', '2025-11-18 03:04:50'),
(5, 'Miguel', 'Cárdenas', 'mcardenas01@gmail.com', '$2y$10$gF0Cb4lGzBzVT0sbcZ0amuCKTJiW1Xn3jD8zzX4n5mpJ92zy96mDe', 'empleado', '2025-11-19 21:01:07'),
(6, 'Carla', 'Espinosa', 'cami_pin11@hotmail.com', '$2y$10$zbPWZMmT0hUwa0Di2H95lO2hJITsrMy.ios4yT5uyCu3GOTXvZtfq', 'empleado', '2026-01-20 20:13:45'),
(7, 'Karen', 'Espinosa', 'karensita_1@hotmail.com', '$2y$10$TKmYUx0dflkRvT8gMLrG.OlFp1m1Qx1PWMDO.LaLz.pEKaf4ZtGrW', 'empleado', '2026-04-09 02:43:38'),
(8, 'Rocio', 'Guerrero', 'rocio.60@gmail.com', '$2y$10$EgVFCPKPwbJn85HDVv7O9Oj/O64Jh3RGeB6lckZHFvXDmLtmqojbq', 'empleado', '2026-04-09 03:20:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacantes`
--

CREATE TABLE `vacantes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `requisitos` text NOT NULL,
  `departamento` varchar(150) NOT NULL,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('abierta','cerrada') DEFAULT 'abierta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vacantes`
--

INSERT INTO `vacantes` (`id`, `titulo`, `descripcion`, `requisitos`, `departamento`, `fecha_publicacion`, `estado`) VALUES
(1, 'Analista de Datos', 'Responsable de recopilar, procesar y analizar datos internos.', 'Experiencia en SQL, Excel avanzado, PowerBI.', 'Tecnología', '2025-11-17 04:46:41', 'abierta'),
(2, 'Asistente Administrativa', 'Buscamos una Asistente Administrativa organizada, proactiva y orientada al detalle para brindar apoyo operativo y administrativo al equipo. La persona seleccionada será responsable de gestionar documentos, coordinar actividades internas, atender clientes y asegurar el buen funcionamiento de las tareas diarias de la oficina.', 'Bachillerato o estudios técnicos/administrativos (deseable).\r\nExperiencia mínima de 1 a 2 años en roles administrativos.\r\nManejo de herramientas ofimáticas (Word, Excel, PowerPoint).\r\nExcelente ortografía, redacción y habilidades de comunicación.\r\nOrganización, puntualidad y capacidad para trabajar bajo supervisión mínima.', 'Administrativo', '2025-11-27 21:25:00', 'abierta'),
(3, 'Analista de Recursos Humanos', 'Responsable de apoyar en la gestión de procesos de selección, capacitación y evaluación del personal. Colaborará en la implementación de políticas de desarrollo organizacional y en el seguimiento de indicadores de desempeño. Deberá garantizar una comunicación efectiva entre los diferentes departamentos y mantener actualizada la base de datos de empleados.', 'Licenciatura en Psicología, Administración o carreras afines.\r\nExperiencia mínima de 2 años en posiciones similares.\r\nConocimientos en gestión de talento, reclutamiento y selección.\r\nHabilidades comunicativas y trabajo en equipo.\r\nDominio de herramientas ofimáticas y sistemas de gestión de Recursos Humanos.', 'Recursos Humanos', '2026-02-11 17:14:45', 'abierta');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `criterios_evaluacion`
--
ALTER TABLE `criterios_evaluacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proceso_id` (`proceso_id`);

--
-- Indices de la tabla `evaluaciones_postulantes`
--
ALTER TABLE `evaluaciones_postulantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evaluacion` (`postulacion_id`,`criterio_id`),
  ADD KEY `criterio_id` (`criterio_id`),
  ADD KEY `evaluador_id` (`evaluador_id`);

--
-- Indices de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `vacante_id` (`vacante_id`);

--
-- Indices de la tabla `proceso_seleccion`
--
ALTER TABLE `proceso_seleccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vacante_id` (`vacante_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `criterios_evaluacion`
--
ALTER TABLE `criterios_evaluacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `evaluaciones_postulantes`
--
ALTER TABLE `evaluaciones_postulantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `proceso_seleccion`
--
ALTER TABLE `proceso_seleccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `criterios_evaluacion`
--
ALTER TABLE `criterios_evaluacion`
  ADD CONSTRAINT `criterios_evaluacion_ibfk_1` FOREIGN KEY (`proceso_id`) REFERENCES `proceso_seleccion` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `evaluaciones_postulantes`
--
ALTER TABLE `evaluaciones_postulantes`
  ADD CONSTRAINT `evaluaciones_postulantes_ibfk_1` FOREIGN KEY (`postulacion_id`) REFERENCES `postulaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluaciones_postulantes_ibfk_2` FOREIGN KEY (`criterio_id`) REFERENCES `criterios_evaluacion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluaciones_postulantes_ibfk_3` FOREIGN KEY (`evaluador_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD CONSTRAINT `postulaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `postulaciones_ibfk_2` FOREIGN KEY (`vacante_id`) REFERENCES `vacantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proceso_seleccion`
--
ALTER TABLE `proceso_seleccion`
  ADD CONSTRAINT `proceso_seleccion_ibfk_1` FOREIGN KEY (`vacante_id`) REFERENCES `vacantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proceso_seleccion_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
