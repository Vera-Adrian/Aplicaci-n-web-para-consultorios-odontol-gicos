-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-11-2026 a las 19:53:32
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
-- Base de datos: `dentahistoria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `antecedentes`
--

CREATE TABLE `antecedentes` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `alergias` text DEFAULT NULL,
  `enfermedades` text DEFAULT NULL,
  `medicamentos` text DEFAULT NULL,
  `embarazo` tinyint(1) NOT NULL DEFAULT 0,
  `habitos` text DEFAULT NULL,
  `antecedentes_odontologicos` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atenciones`
--

CREATE TABLE `atenciones` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `procedimiento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `pieza_dental` tinyint(3) UNSIGNED DEFAULT NULL,
  `diagnostico` text NOT NULL,
  `tratamiento` text DEFAULT NULL,
  `materiales` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `indicaciones` text DEFAULT NULL,
  `proxima_cita` date DEFAULT NULL,
  `costo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `anulada` tinyint(1) NOT NULL DEFAULT 0,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(80) NOT NULL,
  `apellidos` varchar(80) NOT NULL,
  `fecha_nac` date NOT NULL,
  `sexo` enum('F','M') NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `contacto_emergencia` varchar(150) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procedimientos`
--

CREATE TABLE `procedimientos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion_min` smallint(5) UNSIGNED NOT NULL DEFAULT 30,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `requiere_pieza` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `procedimientos`
--

INSERT INTO `procedimientos` (`id`, `nombre`, `categoria`, `descripcion`, `duracion_min`, `precio`, `requiere_pieza`, `activo`) VALUES
(1, 'Limpieza dental', 'Prevención', 'Profilaxis y eliminación de sarro y placa bacteriana.', 45, 30.00, 0, 1),
(2, 'Blanqueamiento dental', 'Estética', 'Aclarado del color del esmalte con agente blanqueador.', 60, 120.00, 0, 1),
(3, 'Extracción simple', 'Cirugía', 'Extracción de pieza dental erupcionada.', 30, 25.00, 1, 1),
(4, 'Extracción de cordal', 'Cirugía', 'Extracción de tercer molar (muela del juicio).', 60, 60.00, 1, 1),
(5, 'Tratamiento de conducto', 'Endodoncia', 'Eliminación de la pulpa dental y sellado de conductos.', 90, 150.00, 1, 1),
(6, 'Calza (obturación)', 'Restauración', 'Restauración de caries con resina o amalgama.', 40, 35.00, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `rol` enum('odontologo','asistente') NOT NULL DEFAULT 'asistente',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `antecedentes`
--
ALTER TABLE `antecedentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `paciente_id` (`paciente_id`);

--
-- Indices de la tabla `atenciones`
--
ALTER TABLE `atenciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `procedimiento_id` (`procedimiento_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_paciente_fecha` (`paciente_id`,`fecha`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `procedimientos`
--
ALTER TABLE `procedimientos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `antecedentes`
--
ALTER TABLE `antecedentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `atenciones`
--
ALTER TABLE `atenciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `procedimientos`
--
ALTER TABLE `procedimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Volcado de datos para la tabla `usuarios`
-- Usuario: admin / Contraseña: admin123
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `clave`, `rol`, `activo`) VALUES
(1, 'Dr. Administrador', 'admin', '$2y$10$qNQPfZc9e838ZB1j0cN0WOCs0KvYEczKN07I.0cFGfdnpGAVIZ2Ai', 'odontologo', 1);

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `antecedentes`
--
ALTER TABLE `antecedentes`
  ADD CONSTRAINT `antecedentes_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `atenciones`
--
ALTER TABLE `atenciones`
  ADD CONSTRAINT `atenciones_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  ADD CONSTRAINT `atenciones_ibfk_2` FOREIGN KEY (`procedimiento_id`) REFERENCES `procedimientos` (`id`),
  ADD CONSTRAINT `atenciones_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
