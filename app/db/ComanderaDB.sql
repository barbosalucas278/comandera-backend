-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 17-05-2021 a las 20:30:11
-- Versión del servidor: 8.0.13-4
-- Versión de PHP: 7.2.24-0ubuntu0.18.04.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `EQQ31Tr4y7`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Encuesta`
--

CREATE TABLE `Encuesta` (
  `Id` int(11) NOT NULL,
  `CodigoMesa` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `Restaurante` int(11) NOT NULL,
  `Mozo` int(11) NOT NULL,
  `Cocinero` int(11) NOT NULL,
  `Comentario` varchar(66) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `FechaCreacion` date NOT NULL,
  `HorarioCreacion` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `EncuestaMesa`
--

CREATE TABLE `EncuestaMesa` (
  `Id` int(11) NOT NULL,
  `MesaId` int(11) NOT NULL,
  `EncuestaId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `EstadoMesa`
--

CREATE TABLE `EstadoMesa` (
  `Id` int(11) NOT NULL,
  `Detalle` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `EstadoMesa`
--

INSERT INTO `EstadoMesa` (`Id`, `Detalle`) VALUES
(1, 'Cliente esperando pedido'),
(2, 'Cliente comiendo'),
(3, 'Cliente pagando'),
(4, 'Cerrada'),
(5, 'Abierta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `EstadoPedido`
--

CREATE TABLE `EstadoPedido` (
  `Id` int(11) NOT NULL,
  `Detalle` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `EstadoPedido`
--

INSERT INTO `EstadoPedido` (`Id`, `Detalle`) VALUES
(1, 'Pendiente'),
(2, 'En preparacion'),
(3, 'Listo para servir');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `EstadoUsuario`
--

CREATE TABLE `EstadoUsuario` (
  `Id` int(11) NOT NULL,
  `Detalle` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `EstadoUsuario`
--

INSERT INTO `EstadoUsuario` (`Id`, `Detalle`) VALUES
(1, 'Suspendido'),
(2, 'Activo'),
(3, 'Eliminado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Mesa`
--

CREATE TABLE `Mesa` (
  `Id` int(11) NOT NULL,
  `EstadoMesaId` int(11) NOT NULL DEFAULT '5',
  `Codigo` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `Mesa`
--

INSERT INTO `Mesa` (`Id`, `EstadoMesaId`, `Codigo`) VALUES
(1, 5, 'M0001'),
(2, 5, 'M0002');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Pedido`
--

CREATE TABLE `Pedido` (
  `Id` int(11) NOT NULL,
  `EstadoPedidoId` int(11) NOT NULL DEFAULT '1',
  `CodigoMesa` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `CodigoPedido` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `ProductoId` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Importe` float NOT NULL,
  `FechaCreacion` date NOT NULL,
  `HorarioCreacion` time NOT NULL,
  `HorarioInicio` time DEFAULT NULL,
  `TiempoEstipulado` int(11) DEFAULT NULL,
  `HorarioDeEntrega` time DEFAULT NULL,
  `NombreCliente` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `UrlFoto` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `Pedido`
--

INSERT INTO `Pedido` (`Id`, `EstadoPedidoId`, `CodigoMesa`, `CodigoPedido`, `ProductoId`, `Cantidad`, `Importe`, `FechaCreacion`, `HorarioCreacion`, `HorarioInicio`, `TiempoEstipulado`, `HorarioDeEntrega`, `NombreCliente`, `UrlFoto`) VALUES
(1, 1, '2', 'd2886', 2, 3, 151.5, '2021-05-17', '21:52:33', NULL, NULL, NULL, 'Victoria', ''),
(2, 1, '2', 'd1199', 1, 2, 21, '2021-05-17', '16:56:50', NULL, NULL, NULL, 'Victoria', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Producto`
--

CREATE TABLE `Producto` (
  `Id` int(11) NOT NULL,
  `Codigo` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `TipoProductoId` int(11) NOT NULL,
  `Nombre` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Stock` int(11) NOT NULL,
  `Precio` float NOT NULL,
  `FechaCreacion` date NOT NULL,
  `FechaUltimaModificacion` date DEFAULT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `Producto`
--

INSERT INTO `Producto` (`Id`, `Codigo`, `TipoProductoId`, `Nombre`, `Stock`, `Precio`, `FechaCreacion`, `FechaUltimaModificacion`, `Activo`) VALUES
(1, '12345', 2, 'Flan', 10, 10.5, '2021-05-17', NULL, 1),
(2, '12346', 1, 'Churrasco con pure', 12, 50.5, '2021-05-17', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ProductoPedidoUsuario`
--

CREATE TABLE `ProductoPedidoUsuario` (
  `Id` int(11) NOT NULL,
  `ProductoId` int(11) NOT NULL,
  `PedidoId` int(11) NOT NULL,
  `UsuarioId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Sector`
--

CREATE TABLE `Sector` (
  `Id` int(11) NOT NULL,
  `Detalle` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `Sector`
--

INSERT INTO `Sector` (`Id`, `Detalle`) VALUES
(1, 'Cocina'),
(2, 'Candy Bar'),
(3, 'Barra de cervezas'),
(4, 'Barra de tragos'),
(5, 'Salon');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `TipoProducto`
--

CREATE TABLE `TipoProducto` (
  `Id` int(11) NOT NULL,
  `SectorId` int(11) NOT NULL,
  `Detalle` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `TipoProducto`
--

INSERT INTO `TipoProducto` (`Id`, `SectorId`, `Detalle`) VALUES
(1, 1, 'Comida'),
(2, 2, 'Postre'),
(3, 3, 'Cerveza'),
(4, 4, 'Trago');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `TipoUsuario`
--

CREATE TABLE `TipoUsuario` (
  `Id` int(11) NOT NULL,
  `Detalle` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `TipoUsuario`
--

INSERT INTO `TipoUsuario` (`Id`, `Detalle`) VALUES
(1, 'Empleado'),
(2, 'Administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Usuario`
--

CREATE TABLE `Usuario` (
  `Id` int(11) NOT NULL,
  `SectorId` int(11) NOT NULL,
  `EstadoUsuarioId` int(11) NOT NULL DEFAULT '2',
  `Nombre` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Apellido` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Clave` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Mail` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `FechaCreacion` date NOT NULL,
  `FechaUltimaModificacion` date DEFAULT NULL,
  `FechaBaja` date DEFAULT NULL,
  `UsuarioModificacion` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `UsuarioAlta` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `TipoUsuarioId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `Usuario`
--

INSERT INTO `Usuario` (`Id`, `SectorId`, `EstadoUsuarioId`, `Nombre`, `Apellido`, `Clave`, `Mail`, `FechaCreacion`, `FechaUltimaModificacion`, `FechaBaja`, `UsuarioModificacion`, `UsuarioAlta`, `TipoUsuarioId`) VALUES
(1, 3, 2, 'Nahuel', 'Barbosa', '$2y$10$IyMwna5RSYtqQ56YBT/OF.fmO4TqMKdCWUuNzv5W69mOjhRlknpK6', 'nahuel@nahuel.com', '2021-05-17', NULL, NULL, NULL, 'Lucas', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `UsuarioLog`
--

CREATE TABLE `UsuarioLog` (
  `Id` int(11) NOT NULL,
  `UsuarioId` int(11) NOT NULL,
  `FechaDeIngreso` date NOT NULL,
  `HoraDeIngreso` time NOT NULL,
  `FechaDeEgreso` date NOT NULL,
  `HoraDeEgreso` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `Encuesta`
--
ALTER TABLE `Encuesta`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `EncuestaMesa`
--
ALTER TABLE `EncuestaMesa`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IXFK_EncuestaMesa_Encuenta` (`EncuestaId`),
  ADD KEY `IXFK_EncuestaMesa_Mesa` (`MesaId`);

--
-- Indices de la tabla `EstadoMesa`
--
ALTER TABLE `EstadoMesa`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `EstadoPedido`
--
ALTER TABLE `EstadoPedido`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `EstadoUsuario`
--
ALTER TABLE `EstadoUsuario`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `Mesa`
--
ALTER TABLE `Mesa`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IXFK_Mesa_EstadoMesa` (`EstadoMesaId`);

--
-- Indices de la tabla `Pedido`
--
ALTER TABLE `Pedido`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IXFK_Pedido_EstadoPedido` (`EstadoPedidoId`),
  ADD KEY `IXFK_Pedido_Producto` (`ProductoId`);

--
-- Indices de la tabla `Producto`
--
ALTER TABLE `Producto`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IXFK_Producto_TipoProducto` (`TipoProductoId`);

--
-- Indices de la tabla `ProductoPedidoUsuario`
--
ALTER TABLE `ProductoPedidoUsuario`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IXFK_ProductoPedidoUsuario_Pedido` (`PedidoId`),
  ADD KEY `IXFK_ProductoPedidoUsuario_Producto` (`ProductoId`),
  ADD KEY `IXFK_ProductoPedidoUsuario_Usuario` (`UsuarioId`);

--
-- Indices de la tabla `Sector`
--
ALTER TABLE `Sector`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `TipoProducto`
--
ALTER TABLE `TipoProducto`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IXFK_TipoProducto_Sector` (`SectorId`);

--
-- Indices de la tabla `TipoUsuario`
--
ALTER TABLE `TipoUsuario`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `Usuario`
--
ALTER TABLE `Usuario`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IXFK_Usuario_EstadoUsuario` (`EstadoUsuarioId`),
  ADD KEY `IXFK_Usuario_TipoUsuario_02` (`TipoUsuarioId`),
  ADD KEY `IXFK_Usuario_Sector` (`SectorId`) USING BTREE;

--
-- Indices de la tabla `UsuarioLog`
--
ALTER TABLE `UsuarioLog`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IXFK_UsuarioLog_Usuario` (`UsuarioId`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `Encuesta`
--
ALTER TABLE `Encuesta`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `EncuestaMesa`
--
ALTER TABLE `EncuestaMesa`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `EstadoMesa`
--
ALTER TABLE `EstadoMesa`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `EstadoPedido`
--
ALTER TABLE `EstadoPedido`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `EstadoUsuario`
--
ALTER TABLE `EstadoUsuario`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `Mesa`
--
ALTER TABLE `Mesa`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `Pedido`
--
ALTER TABLE `Pedido`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `Producto`
--
ALTER TABLE `Producto`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ProductoPedidoUsuario`
--
ALTER TABLE `ProductoPedidoUsuario`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Sector`
--
ALTER TABLE `Sector`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `TipoProducto`
--
ALTER TABLE `TipoProducto`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `TipoUsuario`
--
ALTER TABLE `TipoUsuario`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `Usuario`
--
ALTER TABLE `Usuario`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `UsuarioLog`
--
ALTER TABLE `UsuarioLog`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `EncuestaMesa`
--
ALTER TABLE `EncuestaMesa`
  ADD CONSTRAINT `FK_EncuestaMesa_Encuenta` FOREIGN KEY (`EncuestaId`) REFERENCES `Encuesta` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_EncuestaMesa_Mesa` FOREIGN KEY (`MesaId`) REFERENCES `Mesa` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `Mesa`
--
ALTER TABLE `Mesa`
  ADD CONSTRAINT `FK_Mesa_EstadoMesa` FOREIGN KEY (`EstadoMesaId`) REFERENCES `EstadoMesa` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `ProductoPedidoUsuario`
--
ALTER TABLE `ProductoPedidoUsuario`
  ADD CONSTRAINT `FK_ProductoPedidoUsuario_Pedido` FOREIGN KEY (`PedidoId`) REFERENCES `Pedido` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_ProductoPedidoUsuario_Producto` FOREIGN KEY (`ProductoId`) REFERENCES `Producto` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_ProductoPedidoUsuario_Usuario` FOREIGN KEY (`UsuarioId`) REFERENCES `Usuario` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `TipoProducto`
--
ALTER TABLE `TipoProducto`
  ADD CONSTRAINT `FK_TipoProducto_Sector` FOREIGN KEY (`SectorId`) REFERENCES `Sector` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `Usuario`
--
ALTER TABLE `Usuario`
  ADD CONSTRAINT `FK_Usuario_EstadoUsuario` FOREIGN KEY (`EstadoUsuarioId`) REFERENCES `EstadoUsuario` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
