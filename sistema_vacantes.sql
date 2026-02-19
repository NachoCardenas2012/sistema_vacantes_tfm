-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 12-01-2026 a las 20:31:07
-- Versión del servidor: 5.7.24
-- Versión de PHP: 8.3.1

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
-- Estructura de tabla para la tabla `postulaciones`
--

CREATE TABLE `postulaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `vacante_id` int(11) NOT NULL,
  `fecha_postulacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `archivo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `postulaciones`
--

INSERT INTO `postulaciones` (`id`, `usuario_id`, `vacante_id`, `fecha_postulacion`, `estado`, `archivo`) VALUES
(1, 1, 2, '2025-11-27 21:45:14', 'pendiente', 'uploads/cv_6928c669e405b_CV._Carla_M_Espinosa_A..pdf');

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
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'Admin', 'General', 'admin@xyz.com', '$2y$10$gF0Cb4lGzBzVT0sbcZ0amuCKTJiW1Xn3jD8zzX4n5mpJ92zy96mDe', 'admin', '2025-11-17 04:46:41'),
(3, 'Admin', 'XYZ', 'admin123@xyz.com', '$2y$10$9gSSQdVJBoYk4ZgUqIBGAuGvYd64SgY1gGD7W82dManIeZDV4SSy2', 'admin', '2025-11-18 03:04:50'),
(5, 'Miguel', 'Cárdenas', 'mcardenas01@gmail.com', '$2y$10$vrnqYf7TU1SA76isIJs90uig2g6ECLoQtyB/Rg7UbkUmtuTiHYmKG', 'empleado', '2025-11-19 21:01:07');

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
  `fecha_publicacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('abierta','cerrada') DEFAULT 'abierta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `vacantes`
--

INSERT INTO `vacantes` (`id`, `titulo`, `descripcion`, `requisitos`, `departamento`, `fecha_publicacion`, `estado`) VALUES
(1, 'Analista de Datos', 'Responsable de recopilar, procesar y analizar datos internos.', 'Experiencia en SQL, Excel avanzado, PowerBI.', 'Tecnología', '2025-11-17 04:46:41', 'abierta'),
(2, 'Asistente Administrativa', 'Buscamos una Asistente Administrativa organizada, proactiva y orientada al detalle para brindar apoyo operativo y administrativo al equipo. La persona seleccionada será responsable de gestionar documentos, coordinar actividades internas, atender clientes y asegurar el buen funcionamiento de las tareas diarias de la oficina.', 'Bachillerato o estudios técnicos/administrativos (deseable).\r\nExperiencia mínima de 1 a 2 años en roles administrativos.\r\nManejo de herramientas ofimáticas (Word, Excel, PowerPoint).\r\nExcelente ortografía, redacción y habilidades de comunicación.\r\nOrganización, puntualidad y capacidad para trabajar bajo supervisión mínima.', 'Administrativo', '2025-11-27 21:25:00', 'abierta');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `vacante_id` (`vacante_id`);

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
-- AUTO_INCREMENT de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD CONSTRAINT `postulaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `postulaciones_ibfk_2` FOREIGN KEY (`vacante_id`) REFERENCES `vacantes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
