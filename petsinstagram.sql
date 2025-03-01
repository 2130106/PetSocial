-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-02-2025 a las 16:53:11
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
-- Base de datos: `petsinstagram`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `id_publicacion` int(11) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `id_respuesta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id`, `id_publicacion`, `usuario`, `comentario`, `id_respuesta`) VALUES
(1, 13, 'ALEJANDRA Martínez Peña', 'que mona con su paleta!', NULL),
(2, 13, 'ALEJANDRA Martínez Peña', 'Sii era de fresa :)', 1),
(4, 19, 'ALEJANDRA Martínez Peña', 'holii', NULL),
(5, 19, 'ALEJANDRA Martínez Peña', 'siiss', 4),
(6, 22, 'ALEJANDRA Martínez Peña', 'holaaa', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `id_publicacion` int(11) NOT NULL,
  `usuario` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `likes`
--

INSERT INTO `likes` (`id`, `id_publicacion`, `usuario`) VALUES
(17, 14, 'juanito perez'),
(19, 13, 'ALEJANDRA Martínez Peña'),
(23, 14, 'ALEJANDRA Martínez Peña'),
(24, 22, 'ALEJANDRA Martínez Peña');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id` int(11) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen_ruta` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id`, `usuario`, `descripcion`, `imagen_ruta`, `fecha`, `usuario_id`) VALUES
(12, 'ALEJANDRA Martínez Peña', 'Aqui en mi habitacion con lentes oscuros', 'uploads/img_67b6a16d5bf9c.jpeg', '2025-02-20 03:28:45', 1),
(13, 'ALEJANDRA Martínez Peña', 'Toda bonita', 'uploads/img_67b6a17f13ecc.jpeg', '2025-02-20 03:29:03', 1),
(14, 'juanito perez', 'dormidito', 'uploads/img_67b6a55365f39.jpeg', '2025-02-20 03:45:23', 4),
(19, 'ALEJANDRA Martínez Peña', 'l', 'uploads/img_67b952c546b47.jpg', '2025-02-22 04:29:57', 1),
(20, 'ALEJANDRA Martínez Peña', 'asdadas', 'uploads/img_67b95375009b7.jpeg', '2025-02-22 04:32:53', 1),
(21, 'ALEJANDRA Martínez Peña', 'oip', 'uploads/img_67b953d33620d.jpeg', '2025-02-22 04:34:27', 1),
(22, 'ALEJANDRA Martínez Peña', '-..ñ', 'uploads/img_67b957099ab37.jpeg', '2025-02-22 04:48:09', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguidores`
--

CREATE TABLE `seguidores` (
  `id` int(11) NOT NULL,
  `usuario_seguidor` int(11) NOT NULL,
  `usuario_seguido` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seguidores`
--

INSERT INTO `seguidores` (`id`, `usuario_seguidor`, `usuario_seguido`) VALUES
(6, 1, 4),
(5, 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_perfil` varchar(255) NOT NULL,
  `nombre_mascota` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `fecha_nacimiento`, `email`, `contrasena`, `created_at`, `foto_perfil`, `nombre_mascota`) VALUES
(1, 'ALEJANDRA', 'Martínez Peña', '2008-06-11', 'admin@123.com', '$2y$10$KkKBw47h4gVr7WPwZpZM0eu1SrU/Q.jp2ALhbBIBAav.dAFooPaUC', '2025-02-18 00:48:08', 'uploads/1740192537_R.jpeg', 'gatito1'),
(2, 'juanito', 'juanite', '2003-03-21', 'martinez11@gmail.com', '$2y$10$cXWWMKMSgf.K6Wx.RVToyeDcGuHmaG1A7pzMxp/.j2lBKA/Y4PcSi', '2025-02-18 03:01:25', 'uploads/1739847745_image.png', 'gatito2'),
(4, 'juanito', 'perez', '2003-02-11', 'abi@abi.com', '$2y$10$uFHSK/xOXMCVNCtI6FvFgeF.1rhyuIbj/RSd.Kwjstx0BHm/b6qy6', '2025-02-20 03:44:48', 'uploads/1740023088_327827697_ed2a4293a5.jpg', 'gatito3'),
(5, 'ALEJANDRA', 'Martínez Peña', '2012-10-21', 'locos@locos.com', '$2y$10$TJyTDpwR7K0kBiQ1tJ1sUOSL9QbsC/DMt1h/.k5mi9sDmK2lEWzCu', '2025-02-22 04:47:39', 'uploads/1740199659_R.jpeg', 'gatito13'),
(7, 'asdasdas', 'asdasdas', '2025-02-04', 'mellamojuanito@juanitomellamo.cum', '$2y$10$S3DgYEwVuQphR..s4HrLROrag9pXA5u/elvuLbffOITg9nbR7cfi2', '2025-02-28 01:26:16', 'uploads/1740705976_geno.jpeg', '                background-color:rgb(128, 70, 76);');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_publicacion` (`id_publicacion`),
  ADD KEY `id_respuesta` (`id_respuesta`);

--
-- Indices de la tabla `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_publicacion` (`id_publicacion`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_seguidor` (`usuario_seguidor`,`usuario_seguido`),
  ADD KEY `usuario_seguido` (`usuario_seguido`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `seguidores`
--
ALTER TABLE `seguidores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id`),
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`id_respuesta`) REFERENCES `comentarios` (`id`);

--
-- Filtros para la tabla `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD CONSTRAINT `seguidores_ibfk_1` FOREIGN KEY (`usuario_seguidor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguidores_ibfk_2` FOREIGN KEY (`usuario_seguido`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
