-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 03-05-2020 a las 12:41:31
-- Versión del servidor: 5.7.29-0ubuntu0.18.04.1
-- Versión de PHP: 7.2.24-0ubuntu0.18.04.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyTorneosDS`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deporte`
--

CREATE TABLE `deporte` (
  `id` int(11) NOT NULL,
  `nombre` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `puntos_pganado` int(11) NOT NULL,
  `puntos_pempetado` int(11) DEFAULT NULL,
  `puntos_pperdido` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `deporte_id` int(11) DEFAULT NULL,
  `nombre` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_integrantes` int(11) NOT NULL,
  `duracion_default` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `juez`
--

CREATE TABLE `juez` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pais`
--

CREATE TABLE `pais` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudad`
--

CREATE TABLE `ciudad` (
  `id` int(11) NOT NULL,
  `pais_id` int(11) DEFAULT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripcion`
--

CREATE TABLE `inscripcion` (
  `id` int(11) NOT NULL,
  `fecha_ini` date NOT NULL,
  `fecha_cierre` date NOT NULL,
  `monto` int(11) DEFAULT NULL,
  `requisitos` varchar(700) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `seguidor` tinyint(1) NOT NULL DEFAULT '1',
  `competidor` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `predio`
--

CREATE TABLE `predio` (
  `id` int(11) NOT NULL,
  `ciudad_id` int(11) DEFAULT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campo`
--

CREATE TABLE `campo` (
  `id` int(11) NOT NULL,
  `predio_id` int(11) DEFAULT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `dimensiones` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int(11) NOT NULL,
  `nombre` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_organizacion`
--

CREATE TABLE `tipo_organizacion` (
  `id` int(11) NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `minimo` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `notifications_id` int(11) DEFAULT NULL,
  `nombre_usuario` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pass` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `competencia`
--

CREATE TABLE `competencia` (
  `id` int(11) NOT NULL,
  `ciudad_id` int(11) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `organizacion_id` int(11) DEFAULT NULL,
  `inscripcion_id` int(11) DEFAULT NULL,
  `nombre` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_ini` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `genero` enum('MASCULINO','FEMENINO','MIXTO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_competidores` int(11) DEFAULT NULL,
  `cant_grupos` int(11) DEFAULT NULL,
  `fase` int(11) DEFAULT NULL,
  `min_competidores` int(11) DEFAULT NULL,
  `fase_actual` int(11) NOT NULL,
  `frec_dias` int(11) NOT NULL,
  `estado` enum('COMPETENCIA_SIN_INSCRIPCION','COMPETENCIA_CON_INSCRIPCION','COMPETENCIA_INSCRIPCION_ABIERTA','COMPETENCIA_INSCRIPCION_CERRADA','COMPETENCIA_INICIADA','COMPETENCIA_PAUSADA','COMPETENCIA_SUSPENDIDA','COMPETENCIA_FINALIZADA') COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jornada`
--

CREATE TABLE `jornada` (
  `id` int(11) NOT NULL,
  `competencia_id` int(11) DEFAULT NULL,
  `numero` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `fase` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `juez_competencia`
--

CREATE TABLE `juez_competencia` (
  `id` int(11) NOT NULL,
  `id_juez` int(11) NOT NULL,
  `id_competencia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `predio_competencia`
--

CREATE TABLE `predio_competencia` (
  `id` int(11) NOT NULL,
  `id_predio` int(11) NOT NULL,
  `id_competencia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turno`
--

CREATE TABLE `turno` (
  `id` int(11) NOT NULL,
  `competencia_id` int(11) DEFAULT NULL,
  `hora_desde` time NOT NULL,
  `hora_hasta` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_competencia`
--

CREATE TABLE `usuario_competencia` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_competencia` int(11) NOT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `alias` varchar(127) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuentro`
--

CREATE TABLE `encuentro` (
  `id` int(11) NOT NULL,
  `competencia_id` int(11) NOT NULL,
  `compuser1_id` int(11) NOT NULL,
  `compuser2_id` int(11) NOT NULL,
  `jornada_id` int(11) NOT NULL,
  `juez_id` int(11) DEFAULT NULL,
  `campo_id` int(11) DEFAULT NULL,
  `turno_id` int(11) DEFAULT NULL,
  `grupo` int(11) DEFAULT NULL,
  `rdo_comp1` int(11) DEFAULT NULL,
  `rdo_comp2` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edicion`
--

CREATE TABLE `edicion` (
  `id` int(11) NOT NULL,
  `encuentro_id` int(11) DEFAULT NULL,
  `operacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `editor` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invitacion`
--

CREATE TABLE `invitacion` (
  `id` int(11) NOT NULL,
  `uorganizador_id` int(11) NOT NULL,
  `udestino_id` int(11) NOT NULL,
  `estado` enum('ALTA','BAJA','N/D') COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultado`
--

CREATE TABLE `resultado` (
  `id` int(11) NOT NULL,
  `competidor_id` int(11) DEFAULT NULL,
  `jugados` int(11) DEFAULT NULL,
  `ganados` int(11) DEFAULT NULL,
  `empatados` int(11) DEFAULT NULL,
  `perdidos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- --------------------------------------------------------
--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `campo`
--
ALTER TABLE `campo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_291737AADC5381D3` (`predio_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_4E10122D3A909126` (`nombre`),
  ADD KEY `IDX_4E10122D239C54DD` (`deporte_id`);

--
-- Indices de la tabla `ciudad`
--
ALTER TABLE `ciudad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8E86059EC604D5C6` (`pais_id`);

--
-- Indices de la tabla `competencia`
--
ALTER TABLE `competencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_842C498A3A909126` (`nombre`),
  ADD UNIQUE KEY `UNIQ_842C498AFFD5FBD3` (`inscripcion_id`),
  ADD KEY `IDX_842C498AE8608214` (`ciudad_id`),
  ADD KEY `IDX_842C498A3397707A` (`categoria_id`),
  ADD KEY `IDX_842C498A90B1019E` (`organizacion_id`);

--
-- Indices de la tabla `deporte`
--
ALTER TABLE `deporte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_1C5BBE03A909126` (`nombre`);

--
-- Indices de la tabla `edicion`
--
ALTER TABLE `edicion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_655F7739E304C7C8` (`encuentro_id`);

--
-- Indices de la tabla `encuentro`
--
ALTER TABLE `encuentro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CDFA77FA9980C34D` (`competencia_id`),
  ADD KEY `IDX_CDFA77FA3AD9E` (`compuser1_id`),
  ADD KEY `IDX_CDFA77FA12B60270` (`compuser2_id`),
  ADD KEY `IDX_CDFA77FA26E992D9` (`jornada_id`),
  ADD KEY `IDX_CDFA77FA2515F440` (`juez_id`),
  ADD KEY `IDX_CDFA77FAA17A385C` (`campo_id`),
  ADD KEY `IDX_CDFA77FA69C5211E` (`turno_id`);

--
-- Indices de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `invitacion`
--
ALTER TABLE `invitacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3CD30E84D0F7497F` (`uorganizador_id`),
  ADD KEY `IDX_3CD30E84E34E213E` (`udestino_id`);

--
-- Indices de la tabla `jornada`
--
ALTER TABLE `jornada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_61D21CBF9980C34D` (`competencia_id`);

--
-- Indices de la tabla `juez`
--
ALTER TABLE `juez`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8FBF65007F8F253B` (`dni`);

--
-- Indices de la tabla `juez_competencia`
--
ALTER TABLE `juez_competencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3D03DE2449F7902` (`id_juez`),
  ADD KEY `IDX_3D03DE249C3E847D` (`id_competencia`);

--
-- Indices de la tabla `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pais`
--
ALTER TABLE `pais`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `predio`
--
ALTER TABLE `predio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_13E6D727E8608214` (`ciudad_id`);

--
-- Indices de la tabla `predio_competencia`
--
ALTER TABLE `predio_competencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3FED37B935D162CA` (`id_predio`),
  ADD KEY `IDX_3FED37B99C3E847D` (`id_competencia`);

--
-- Indices de la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B2ED91C7B73D69E` (`competidor_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_E553F373A909126` (`nombre`);

--
-- Indices de la tabla `tipo_organizacion`
--
ALTER TABLE `tipo_organizacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_BF73525A20332D99` (`codigo`),
  ADD UNIQUE KEY `UNIQ_BF73525A3A909126` (`nombre`);

--
-- Indices de la tabla `turno`
--
ALTER TABLE `turno`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E79767629980C34D` (`competencia_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_2265B05DD67CF11D` (`nombre_usuario`),
  ADD UNIQUE KEY `UNIQ_2265B05DD4BE081` (`notifications_id`);

--
-- Indices de la tabla `usuario_competencia`
--
ALTER TABLE `usuario_competencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BC07BB04FCF8192D` (`id_usuario`),
  ADD KEY `IDX_BC07BB049C3E847D` (`id_competencia`),
  ADD KEY `IDX_BC07BB044BAB96C` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `campo`
--
ALTER TABLE `campo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT de la tabla `ciudad`
--
ALTER TABLE `ciudad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4006;
--
-- AUTO_INCREMENT de la tabla `competencia`
--
ALTER TABLE `competencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT de la tabla `deporte`
--
ALTER TABLE `deporte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT de la tabla `edicion`
--
ALTER TABLE `edicion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `encuentro`
--
ALTER TABLE `encuentro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;
--
-- AUTO_INCREMENT de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT de la tabla `invitacion`
--
ALTER TABLE `invitacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `jornada`
--
ALTER TABLE `jornada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;
--
-- AUTO_INCREMENT de la tabla `juez`
--
ALTER TABLE `juez`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT de la tabla `juez_competencia`
--
ALTER TABLE `juez_competencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT de la tabla `pais`
--
ALTER TABLE `pais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT de la tabla `predio`
--
ALTER TABLE `predio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT de la tabla `predio_competencia`
--
ALTER TABLE `predio_competencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `resultado`
--
ALTER TABLE `resultado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT de la tabla `tipo_organizacion`
--
ALTER TABLE `tipo_organizacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT de la tabla `turno`
--
ALTER TABLE `turno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT de la tabla `usuario_competencia`
--
ALTER TABLE `usuario_competencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `campo`
--
ALTER TABLE `campo`
  ADD CONSTRAINT `FK_291737AADC5381D3` FOREIGN KEY (`predio_id`) REFERENCES `predio` (`id`);

--
-- Filtros para la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `FK_4E10122D239C54DD` FOREIGN KEY (`deporte_id`) REFERENCES `deporte` (`id`);

--
-- Filtros para la tabla `ciudad`
--
ALTER TABLE `ciudad`
  ADD CONSTRAINT `FK_8E86059EC604D5C6` FOREIGN KEY (`pais_id`) REFERENCES `pais` (`id`);

--
-- Filtros para la tabla `competencia`
--
ALTER TABLE `competencia`
  ADD CONSTRAINT `FK_842C498A3397707A` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`),
  ADD CONSTRAINT `FK_842C498A90B1019E` FOREIGN KEY (`organizacion_id`) REFERENCES `tipo_organizacion` (`id`),
  ADD CONSTRAINT `FK_842C498AE8608214` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudad` (`id`),
  ADD CONSTRAINT `FK_842C498AFFD5FBD3` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripcion` (`id`);

--
-- Filtros para la tabla `edicion`
--
ALTER TABLE `edicion`
  ADD CONSTRAINT `FK_655F7739E304C7C8` FOREIGN KEY (`encuentro_id`) REFERENCES `encuentro` (`id`);

--
-- Filtros para la tabla `encuentro`
--
ALTER TABLE `encuentro`
  ADD CONSTRAINT `FK_CDFA77FA12B60270` FOREIGN KEY (`compuser2_id`) REFERENCES `usuario_competencia` (`id`),
  ADD CONSTRAINT `FK_CDFA77FA2515F440` FOREIGN KEY (`juez_id`) REFERENCES `juez` (`id`),
  ADD CONSTRAINT `FK_CDFA77FA26E992D9` FOREIGN KEY (`jornada_id`) REFERENCES `jornada` (`id`),
  ADD CONSTRAINT `FK_CDFA77FA3AD9E` FOREIGN KEY (`compuser1_id`) REFERENCES `usuario_competencia` (`id`),
  ADD CONSTRAINT `FK_CDFA77FA69C5211E` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id`),
  ADD CONSTRAINT `FK_CDFA77FA9980C34D` FOREIGN KEY (`competencia_id`) REFERENCES `competencia` (`id`),
  ADD CONSTRAINT `FK_CDFA77FAA17A385C` FOREIGN KEY (`campo_id`) REFERENCES `campo` (`id`);

--
-- Filtros para la tabla `invitacion`
--
ALTER TABLE `invitacion`
  ADD CONSTRAINT `FK_3CD30E84D0F7497F` FOREIGN KEY (`uorganizador_id`) REFERENCES `usuario_competencia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_3CD30E84E34E213E` FOREIGN KEY (`udestino_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `jornada`
--
ALTER TABLE `jornada`
  ADD CONSTRAINT `FK_61D21CBF9980C34D` FOREIGN KEY (`competencia_id`) REFERENCES `competencia` (`id`);

--
-- Filtros para la tabla `juez_competencia`
--
ALTER TABLE `juez_competencia`
  ADD CONSTRAINT `FK_3D03DE2449F7902` FOREIGN KEY (`id_juez`) REFERENCES `juez` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_3D03DE249C3E847D` FOREIGN KEY (`id_competencia`) REFERENCES `competencia` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `predio`
--
ALTER TABLE `predio`
  ADD CONSTRAINT `FK_13E6D727E8608214` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudad` (`id`);

--
-- Filtros para la tabla `predio_competencia`
--
ALTER TABLE `predio_competencia`
  ADD CONSTRAINT `FK_3FED37B935D162CA` FOREIGN KEY (`id_predio`) REFERENCES `predio` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_3FED37B99C3E847D` FOREIGN KEY (`id_competencia`) REFERENCES `competencia` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD CONSTRAINT `FK_B2ED91C7B73D69E` FOREIGN KEY (`competidor_id`) REFERENCES `usuario_competencia` (`id`);

--
-- Filtros para la tabla `turno`
--
ALTER TABLE `turno`
  ADD CONSTRAINT `FK_E79767629980C34D` FOREIGN KEY (`competencia_id`) REFERENCES `competencia` (`id`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_2265B05DD4BE081` FOREIGN KEY (`notifications_id`) REFERENCES `notification` (`id`);

--
-- Filtros para la tabla `usuario_competencia`
--
ALTER TABLE `usuario_competencia`
  ADD CONSTRAINT `FK_BC07BB044BAB96C` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`),
  ADD CONSTRAINT `FK_BC07BB049C3E847D` FOREIGN KEY (`id_competencia`) REFERENCES `competencia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_BC07BB04FCF8192D` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
