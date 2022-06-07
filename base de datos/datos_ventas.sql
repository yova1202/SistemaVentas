-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-09-2021 a las 19:04:40
-- Versión del servidor: 10.4.18-MariaDB
-- Versión de PHP: 8.0.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `datos_ventas`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_precio_producto` (IN `n_cantidad` INT, IN `n_precio` DECIMAL(10,2), IN `codigo` INT)  BEGIN
    	DECLARE nueva_existencia int;
        DECLARE nuevo_total decimal(10,2);
        DECLARE nuevo_precio decimal(10,2);
        
        DECLARE cant_actual int;
        DECLARE pre_actual decimal(10,2);
        
        DECLARE actual_existencia int;
        DECLARE actual_precio decimal(10,2);
        
        SELECT precio,existencia INTO actual_precio,actual_existencia FROM producto WHERE codproducto = codigo;
        SET nueva_existencia = actual_existencia + n_cantidad;
        
        UPDATE producto SET existencia = nueva_existencia, precio = n_precio WHERE codproducto = codigo;
        
        SELECT nueva_existencia,nuevo_precio;
        
     END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_temp` (IN `codigo` INT, IN `cantidad` INT, IN `token_user` VARCHAR(50))  BEGIN
    
    	DECLARE precio_actual decimal(10,2);
        DECLARE existencia_actual int;
        DECLARE nueva_existencia int;
        SELECT precio INTO precio_actual FROM producto WHERE codproducto = codigo;
        
        INSERT INTO detalle_temp(token_user,codproducto,cantidad,precio_venta) VALUES(token_user,codigo,cantidad,precio_actual);
        
        SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = codigo;

                SET nueva_existencia = existencia_actual - cantidad;
                UPDATE producto SET existencia = nueva_existencia WHERE codproducto = codigo;
        
        SELECT tmp.correlativo, tmp.codproducto,p.descripcion,tmp.cantidad,tmp.precio_venta FROM detalle_temp tmp
        INNER JOIN producto p 
        ON tmp.codproducto = p.codproducto
        WHERE tmp.token_user = token_user;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `anular_venta` (`no_venta` INT)  BEGIN
    	DECLARE existe_venta int;
        DECLARE registros int;
        DECLARE a int;
        
        DECLARE cod_producto int;
        DECLARE cant_producto int;
        DECLARE existencia_actual int;
        DECLARE nueva_existencia int;
        
        SET existe_venta = (SELECT COUNT(*) FROM venta WHERE noventa = no_venta and status = 1);
        
        IF existe_venta > 0 THEN
        	CREATE TEMPORARY TABLE tbl_tmp (
                id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                cod_prod BIGINT,
                cant_prod int);
                
                SET a = 1;
                
                SET registros = (SELECT COUNT(*)FROM detalleventa WHERE noventa = no_venta);
                
                IF registros > 0 THEN
                	INSERT INTO tbl_tmp(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalleventa WHERE noventa = no_venta;
                    
                    WHILE a <= registros DO
                    	SELECT cod_prod,cant_prod INTO cod_producto,cant_producto FROM tbl_tmp WHERE id = a;
                        SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
                        SET nueva_existencia = existencia_actual + cant_producto;
                        UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto;
                        
                        SET a=a+1;
                    
                    END WHILE;
                    UPDATE venta SET status = 2 WHERE noventa = no_venta;
                    DROP TABLE tbl_tmp;
                    SELECT * FROM venta WHERE noventa = no_venta;
                
                END IF;
        
        ELSE
        	SELECT 0 venta;
        END IF;
    
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `cancelar_venta` (IN `token` INT)  BEGIN
    	DECLARE existe_venta int;
        DECLARE registros int;
        DECLARE a int;
        
        DECLARE cod_producto int; 
        DECLARE cant_producto int;
        DECLARE existencia_actual int;
        DECLARE nueva_existencia int;
        
        SET existe_venta = (SELECT COUNT(*) FROM detalle_temp WHERE token_user = token);
        
        IF existe_venta > 0 THEN
        	CREATE TEMPORARY TABLE tbl_tmp (
                id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                cod_prod BIGINT,
                cant_prod int);
                
                SET a = 1;
                
                SET registros = (SELECT COUNT(*)FROM detalle_temp WHERE token_user = token);
                
                IF registros > 0 THEN
                	INSERT INTO tbl_tmp(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalle_temp WHERE token_user = token;
                    
                    WHILE a <= registros DO
                    	SELECT cod_prod,cant_prod INTO cod_producto,cant_producto FROM tbl_tmp WHERE id = a;
                        SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
                        SET nueva_existencia = existencia_actual + cant_producto;
                        UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto;
                        
                        SET a=a+1;
                    
                    END WHILE;
       DELETE FROM detalle_temp WHERE token_user = token;
              DROP TABLE tbl_tmp;
       SELECT * FROM detalle_temp WHERE token_user = token;
                
                END IF;
        
        ELSE
        	SELECT 0 detalle_temp;
        END IF;
    
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dataDashboard` (IN `usuario_id` INT)  BEGIN
    	
        DECLARE usuarios int;
        DECLARE clientes int;
        DECLARE proveedores int;
        DECLARE productos int;
        DECLARE  ventas decimal(10,2);
        
        SELECT COUNT(*) INTO usuarios FROM usuario WHERE status !=10;
        SELECT COUNT(*) INTO clientes FROM cliente WHERE status !=10;
        SELECT COUNT(*) INTO proveedores FROM proveedor WHERE status !=10;
        SELECT COUNT(*) INTO productos FROM producto WHERE status !=10;
        SELECT SUM(totalventa) INTO ventas FROM venta WHERE usuario = usuario_id AND fecha > CURDATE() AND status =1;
        
        SELECT usuarios,clientes,proveedores,productos,ventas;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `del_detalle_temp` (IN `id_detalle` INT, IN `token` VARCHAR(50))  BEGIN    
		DECLARE existe_venta int;
        DECLARE registros int;
        DECLARE a int;
        
        DECLARE cod_producto int;
        DECLARE cant_producto int;
        DECLARE existencia_actual int;
        DECLARE nueva_existencia int;
        

        	CREATE TEMPORARY TABLE tbl_tmp (id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                cod_prod BIGINT,
                cant_prod int);
                
                SET a = 1;
                
                SET registros = (SELECT COUNT(*)FROM detalle_temp WHERE correlativo = id_detalle);
                

                	INSERT INTO tbl_tmp(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalle_temp WHERE correlativo = id_detalle;
                    
                    	SELECT cod_prod,cant_prod INTO cod_producto,cant_producto FROM tbl_tmp WHERE id = a;
                        SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
                        SET nueva_existencia = existencia_actual + cant_producto;
                        UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto;

            DELETE FROM detalle_temp WHERE correlativo = id_detalle;
  
            SELECT tmp.correlativo, tmp.codproducto,p.descripcion,tmp.cantidad,tmp.precio_venta FROM detalle_temp tmp
            INNER JOIN producto p 
            ON tmp.codproducto = p.codproducto
            WHERE tmp.token_user = token;
        END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `procesar_venta` (IN `cod_usuario` INT, IN `cod_cliente` INT, IN `token` VARCHAR(50))  BEGIN
    	DECLARE venta INT;
        
        DECLARE registros INT;
        DECLARE total DECIMAL(10,2);
        
        DECLARE nueva_existencia int;
        DECLARE existencia_actual int;
        
        DECLARE tmp_cod_producto int;
        DECLARE tmp_cant_producto int;
        DECLARE a INT;
        SET a = 1;
        
        CREATE TEMPORARY TABLE tbl_tmp_tokenuser(
        		id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        		cod_prod BIGINT,
        		cant_prod int);
                
        SET registros = (SELECT COUNT(*) FROM detalle_temp WHERE token_user = token);
        
        IF registros > 0 THEN
        	INSERT INTO tbl_tmp_tokenuser(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalle_temp WHERE token_user = token;
            
            INSERT INTO venta(usuario,codcliente) VALUES(cod_usuario,cod_cliente);
            SET venta = LAST_INSERT_ID();
            
            INSERT INTO detalleventa(noventa,codproducto,cantidad,precio_venta) SELECT(venta) as noventa, codproducto,cantidad,precio_venta FROM detalle_temp WHERE token_user = token;
            
            WHILE a <= registros DO
            	SELECT cod_prod,cant_prod INTO tmp_cod_producto,tmp_cant_producto FROM tbl_tmp_tokenuser WHERE id = a;
                
                SET a=a+1;
           
            END WHILE;
            
            SET total = (SELECT SUM(cantidad * precio_venta) FROM detalle_temp WHERE token_user = token);
            UPDATE venta SET totalventa = total WHERE noventa = venta;
            DELETE FROM detalle_temp WHERE token_user = token;
            TRUNCATE TABLE tbl_tmp_tokenuser;
            SELECT * FROM venta WHERE noventa = venta;
            
        ELSE
        	SELECT 0;
        END IF;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `nit` varchar(20) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `nit`, `nombre`, `telefono`, `direccion`, `date_add`, `usuario_id`, `status`) VALUES
(1, '0', 'ND', 0, 'ND', '0000-00-00 00:00:00', 1, 1),
(25, '6233558', 'Amparo Alonzo', 79025489, 'MONTERO', '2021-05-03 13:17:13', 1, 1),
(26, '8569887', 'Angel Dolores', 79856487, 'MONTERO', '2021-05-03 13:20:36', 1, 1),
(27, '6030701', 'Benjamin Bello', 63325697, 'MONTERO', '2021-05-03 13:21:48', 1, 1),
(28, '6030705', 'Domingo Carcamo', 79024589, 'MONTERO', '2021-05-03 13:29:56', 1, 1),
(29, '8625987', 'Estela Esmeralda', 65589745, 'MONTERO', '2021-05-03 14:06:31', 1, 1),
(30, '6641236', 'Jacinto Mendez', 70245724, 'MONTERO', '2021-05-03 14:12:07', 1, 1),
(31, '8699776', 'Julio León', 79854896, 'MONTERO', '2021-05-03 14:13:06', 1, 1),
(32, '6322554', 'Leticia Lima', 62215749, 'MONTERO', '2021-05-03 14:13:59', 1, 1),
(33, '8055236', 'Lidia Magdalena', 65598789, 'MONTERO', '2021-05-03 14:15:23', 1, 1),
(34, '8414758', 'Margarita Nieves', 69984754, 'MONTERO', '2021-05-03 14:17:39', 1, 1),
(35, '8066993', 'Narciso Perez', 66989547, 'MONTERO', '2021-05-03 14:18:25', 1, 1),
(36, '8522449', 'Oscar Paz', 65548791, 'MONTERO', '2021-05-03 14:20:38', 1, 1),
(37, '8088444', 'Rebeca Lira', 75412369, 'MONTERO', '2021-05-03 14:23:01', 1, 1),
(38, '8088441', 'Violeta Victoria', 62258749, 'MONTERO', '2021-05-03 14:25:12', 1, 1),
(51, '8088443', 'iban arias', 78956236, 'MONTERO', '2021-08-29 16:37:00', 1, 0),
(52, '603068', 'ARLIN', 85274367, 'Barrio san Isidro, las vegas de la barrera 200 metros al oeste', '2021-09-08 18:04:09', 15, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) NOT NULL,
  `nit` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `direccion` text NOT NULL,
  `iva` decimal(10,2) NOT NULL,
  `foto` varchar(200) NOT NULL,
  `moneda` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nit`, `nombre`, `razon_social`, `telefono`, `email`, `direccion`, `iva`, `foto`, `moneda`) VALUES
(1, '60306999855b', 'El Cumpa', 'Venta productos abarrotes', 70003249, 'elcumpa@gmail.com', 'Centro comercial ', '0.00', 'logo_1ecbba73a7d407977168efe6aa146e4c.jpg', 'Bs');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalleventa`
--

CREATE TABLE `detalleventa` (
  `correlativo` bigint(11) NOT NULL,
  `noventa` bigint(11) DEFAULT NULL,
  `codproducto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detalleventa`
--

INSERT INTO `detalleventa` (`correlativo`, `noventa`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(87, 40, 34, 1, '25.00'),
(88, 40, 31, 1, '20.00'),
(89, 40, 29, 1, '8.00'),
(90, 40, 21, 3, '25.00'),
(91, 40, 28, 2, '7.00'),
(94, 41, 24, 10, '45.00'),
(95, 42, 24, 1, '45.00'),
(96, 42, 27, 1, '7.00'),
(99, 44, 27, 1, '7.00'),
(100, 45, 33, 1, '25.00'),
(101, 46, 24, 1, '45.00'),
(103, 48, 37, 1, '25.00'),
(104, 49, 29, 1, '8.00'),
(105, 50, 25, 1, '25.00'),
(106, 51, 25, 1, '25.00'),
(107, 52, 27, 1, '7.00'),
(108, 53, 28, 1, '7.00'),
(109, 54, 30, 1, '25.00'),
(110, 55, 29, 1, '8.00'),
(111, 56, 33, 1, '25.00'),
(112, 57, 24, 1, '45.00'),
(113, 58, 25, 1, '25.00'),
(114, 59, 25, 1, '25.00'),
(115, 60, 34, 1, '25.00'),
(116, 61, 27, 1, '7.00'),
(117, 62, 28, 1, '7.00'),
(118, 63, 28, 1, '7.00'),
(119, 64, 27, 1, '7.00'),
(120, 65, 28, 1, '7.00'),
(121, 65, 21, 1, '25.00'),
(123, 66, 30, 1, '25.00'),
(124, 67, 27, 1, '7.00'),
(125, 68, 27, 1, '7.00'),
(126, 69, 27, 19, '7.00'),
(127, 69, 27, 1, '7.00'),
(129, 70, 27, 19, '7.00'),
(130, 70, 27, 1, '7.00'),
(132, 71, 27, 19, '7.00'),
(133, 72, 29, 1, '8.00'),
(134, 73, 28, 1, '7.00'),
(135, 74, 29, 1, '8.00'),
(136, 74, 29, 20, '8.00'),
(137, 74, 29, 20, '8.00'),
(138, 75, 29, 1, '8.00'),
(139, 76, 29, 18, '8.00'),
(140, 76, 29, 1, '8.00'),
(142, 77, 29, 18, '8.00'),
(143, 77, 29, 1, '8.00'),
(145, 78, 29, 18, '8.00'),
(146, 78, 29, 1, '8.00'),
(148, 79, 29, 18, '8.00'),
(149, 79, 29, 1, '8.00'),
(151, 80, 29, 18, '8.00'),
(152, 80, 29, 1, '8.00'),
(154, 81, 29, 1, '8.00'),
(155, 82, 29, 1, '8.00'),
(156, 83, 29, 1, '8.00'),
(157, 84, 30, 1, '25.00'),
(158, 85, 30, 21, '25.00'),
(159, 85, 30, 1, '25.00'),
(161, 86, 30, 1, '25.00'),
(162, 86, 30, 22, '25.00'),
(164, 87, 30, 1, '25.00'),
(165, 88, 30, 1, '25.00'),
(166, 88, 30, 1, '25.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_temp`
--

CREATE TABLE `detalle_temp` (
  `correlativo` int(11) NOT NULL,
  `token_user` varchar(50) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

CREATE TABLE `entradas` (
  `correlativo` int(11) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`correlativo`, `codproducto`, `fecha`, `cantidad`, `precio`, `usuario_id`) VALUES
(43, 27, '2021-05-03 15:52:57', 24, '7.00', 1),
(44, 28, '2021-05-03 16:03:45', 24, '7.00', 1),
(45, 29, '2021-05-03 16:11:26', 24, '8.00', 1),
(46, 30, '2021-05-03 16:16:21', 24, '25.00', 1),
(47, 31, '2021-05-03 16:16:53', 12, '20.00', 1),
(48, 32, '2021-05-03 16:25:02', 12, '20.00', 1),
(49, 33, '2021-05-03 16:25:47', 12, '25.00', 1),
(50, 34, '2021-05-03 16:26:27', 12, '25.00', 1),
(51, 24, '2021-05-03 17:54:50', 24, '45.00', 1),
(52, 34, '2021-08-24 16:23:55', 1, '25.00', 15),
(54, 37, '2021-08-27 11:48:35', 1, '25.00', 15),
(55, 37, '2021-08-29 16:45:42', 10, '25.00', 1),
(56, 37, '2021-08-29 16:46:07', 10, '25.05', 1),
(57, 37, '2021-08-29 16:46:51', 10, '25.05', 1),
(58, 37, '2021-08-29 16:50:19', 5, '26.50', 1),
(59, 38, '2021-09-08 09:08:09', 24, '25.50', 15),
(60, 38, '2021-09-08 11:39:44', 1, '25.50', 15),
(61, 38, '2021-09-08 11:40:10', 5, '25.75', 15),
(62, 33, '2021-09-08 11:40:52', 5, '25.50', 15),
(63, 27, '2021-09-08 11:41:42', 12, '7.30', 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `codproducto` int(20) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `proveedor` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `existencia` int(11) DEFAULT NULL,
  `foto` text DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `status` int(11) DEFAULT 1,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`codproducto`, `codigo`, `descripcion`, `proveedor`, `precio`, `existencia`, `foto`, `date_add`, `status`, `usuario_id`) VALUES
(21, '1004', 'Gaseosa 500 ml', 17, '25.00', 23, 'img_abb2ec20f7ce38fa8dfae387a1f160d6.jpg', '2021-04-18 19:32:15', 1, 1),
(24, '1001', 'Adrenalina jumbo', 18, '45.00', -1, 'img_f64716d161989d21bb38f56535e71e8f.jpg', '2021-04-18 21:06:33', 1, 1),
(25, '1003', 'Gatorade', 18, '25.00', 11, 'img_3840018ef365fd18ae9198f7a753ffb8.jpg', '2021-04-19 16:32:07', 1, 1),
(27, '1006', 'Ranchitas', 19, '7.30', 50, 'img_b2974c23d4ea6d87486606762f9edd9b.jpg', '2021-05-03 15:52:57', 1, 1),
(28, '1007', 'Taqueritos', 19, '7.00', 17, 'img_c98d82adaf60d0211d3c629b1e59bad3.jpg', '2021-05-03 16:03:45', 1, 1),
(29, '1008', 'Zambos', 19, '8.00', 98, 'img_82203c43da2e412c40fe4377857a6099.jpg', '2021-05-03 16:11:26', 1, 1),
(30, '1009', 'Pepsi cola', 18, '25.00', 10, 'img_9e6928346e3857791e499d903234011b.jpg', '2021-05-03 16:16:21', 1, 1),
(31, '1010', 'Pepsi lata', 18, '20.00', 11, 'img_be9766c91c2216dede74604696ce23bc.jpg', '2021-05-03 16:16:53', 1, 1),
(32, '1011', 'Liptop', 18, '20.00', 12, 'img_6d9eed50fd3330bfc1a347e5baacce3b.jpg', '2021-05-03 16:25:02', 1, 1),
(33, '1012', 'Chicharrones', 19, '25.50', 15, 'img_dbcd6e3c7056db0373cb82056059a07d.jpg', '2021-05-03 16:25:47', 1, 1),
(34, '1013', 'Yum mix', 19, '25.50', 11, 'img_f3c936207157d0bf1c7d4694d621e690.jpg', '2021-05-03 16:26:27', 1, 1),
(37, '1050', 'sillon', 17, '26.51', 35, 'img_producto.png', '2021-08-27 11:48:35', 1, 15),
(38, '1051', 'Power', 18, '25.75', 30, 'img_producto.png', '2021-09-08 09:08:09', 1, 15);

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `entradas_A_I` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
        	INSERT INTO entradas (codproducto,cantidad,precio,usuario_id)
            VALUES(new.codproducto,new.existencia,new.precio,new.usuario_id);
         END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `codproveedor` int(11) NOT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` bigint(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `status` int(11) DEFAULT 1,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`codproveedor`, `proveedor`, `contacto`, `telefono`, `direccion`, `date_add`, `status`, `usuario_id`) VALUES
(17, 'Coca cola', 'Andres Espinoza', 99999999, 'Calle central', '2021-04-18 19:29:16', 1, NULL),
(18, 'Pepsi cola', 'Adrian Gomez', 87878787, 'zona 22', '2021-04-18 21:05:28', 1, NULL),
(19, 'Snack Yummies', 'Paulo Rojas', 99999999, 'zona 1', '2021-05-03 14:59:34', 1, NULL),
(20, 'Del monte', 'Mauricio Soza', 99999999, 'zona 1', '2021-05-03 15:00:21', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idrol` int(11) NOT NULL,
  `rol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idrol`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `usuario` varchar(15) DEFAULT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `rol` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `correo`, `usuario`, `clave`, `rol`, `status`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1),
(14, 'soluciones', 'soluciones@gmail.com', 'soluciones', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(15, 'admin2', 'admin2@gmail.com', 'admin2', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(16, 'Jose Enrique', 'jose@hotmail.com', 'jose', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(17, 'Carlos Joaquin', 'joaquin@gmail.com', 'carlos', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(18, 'Adriana García', 'adriana@gmail.com', 'adriana', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(19, 'Maria Ester', 'maria@gmail.com', 'maria', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(20, 'Andres Zalazar', 'andres@gmail.com', 'andres', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(21, 'Esteban Blandon', 'esteban@hotmail.com', 'esteban', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(22, 'Pedro Henriquez', 'pedro@yahoo.es', 'pedro', 'd41d8cd98f00b204e9800998ecf8427e', 3, 1),
(23, 'Gonzalo Gonzalez', 'gonzalo@gmail.com', 'gonzalo', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(24, 'Hernaldo Lumbi', 'hernaldo@gmail.com', 'hernaldo', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(25, 'Griselda Hernandez', 'griselda@gmail.com', 'griselda', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(26, 'Josue Espinoza', 'josue@gmail.com', 'josue', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(27, 'Pablo Martínez', 'pablo1@gmail.com', 'pablo1', 'd41d8cd98f00b204e9800998ecf8427e', 3, 1),
(33, 'ricardo', 'ricotorrico22@gmail.com', 'rico', '81dc9bdb52d04dc20036dbd8313ed055', 3, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `noventa` bigint(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  `totalventa` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`noventa`, `fecha`, `usuario`, `codcliente`, `status`, `totalventa`) VALUES
(40, '2021-05-03 16:57:19', 1, 1, 1, '142.00'),
(41, '2021-05-03 17:08:09', 1, 1, 1, '450.00'),
(42, '2021-05-03 17:54:24', 1, 1, 1, '52.00'),
(43, '2021-08-24 16:25:13', 15, 1, 1, '500.00'),
(44, '2021-08-24 16:39:05', 15, 1, 2, '7.00'),
(45, '2021-08-25 11:02:01', 15, 1, 1, '25.00'),
(46, '2021-08-25 11:43:17', 15, 1, 1, '45.00'),
(47, '2021-08-25 11:55:32', 15, 1, 1, '385.00'),
(48, '2021-08-27 11:48:50', 15, 1, 1, '25.00'),
(49, '2021-08-27 13:48:13', 15, 1, 1, '8.00'),
(50, '2021-08-27 16:12:49', 15, 1, 1, '25.00'),
(51, '2021-08-28 13:14:53', 27, 1, 1, '25.00'),
(52, '2021-08-28 13:18:17', 27, 1, 1, '7.00'),
(53, '2021-08-28 13:43:28', 27, 26, 1, '7.00'),
(54, '2021-08-28 17:13:48', 15, 1, 1, '25.00'),
(55, '2021-08-28 17:24:53', 15, 1, 1, '8.00'),
(56, '2021-08-28 20:03:39', 1, 26, 1, '25.00'),
(57, '2021-08-28 20:05:36', 33, 25, 1, '45.00'),
(58, '2021-08-28 20:08:58', 19, 33, 1, '25.00'),
(59, '2021-08-29 16:53:58', 33, 27, 2, '25.00'),
(60, '2021-08-30 12:01:28', 1, 27, 1, '25.00'),
(61, '2021-08-30 14:54:26', 15, 1, 1, '7.00'),
(62, '2021-08-30 15:06:36', 15, 1, 1, '7.00'),
(63, '2021-08-30 15:09:36', 15, 1, 1, '7.00'),
(64, '2021-08-30 15:14:15', 15, 1, 2, '7.00'),
(65, '2021-08-30 16:25:42', 15, 1, 1, '32.00'),
(66, '2021-08-30 16:30:49', 15, 1, 1, '25.00'),
(67, '2021-08-30 16:39:59', 15, 1, 1, '7.00'),
(68, '2021-09-06 17:36:35', 33, 1, 1, '7.00'),
(69, '2021-09-06 17:58:30', 15, 1, 2, '140.00'),
(70, '2021-09-06 18:21:50', 15, 1, 2, NULL),
(71, '2021-09-06 18:22:04', 15, 1, 2, NULL),
(72, '2021-09-08 09:15:09', 15, 1, 1, '8.00'),
(73, '2021-09-08 09:17:37', 15, 25, 1, '7.00'),
(74, '2021-09-08 12:50:57', 15, 1, 1, NULL),
(75, '2021-09-08 12:51:44', 15, 1, 1, '8.00'),
(76, '2021-09-08 12:52:26', 15, 1, 2, NULL),
(77, '2021-09-08 14:22:51', 15, 1, 2, NULL),
(78, '2021-09-08 14:28:08', 15, 1, 2, NULL),
(79, '2021-09-08 14:28:17', 15, 1, 2, NULL),
(80, '2021-09-08 14:30:11', 15, 1, 2, NULL),
(81, '2021-09-08 15:22:29', 15, 1, 2, NULL),
(82, '2021-09-08 15:22:47', 15, 1, 2, NULL),
(83, '2021-09-08 15:31:06', 15, 1, 2, NULL),
(84, '2021-09-08 15:38:10', 15, 1, 1, '25.00'),
(85, '2021-09-08 15:43:37', 15, 1, 2, NULL),
(86, '2021-09-08 15:59:44', 15, 1, 2, '575.00'),
(87, '2021-09-08 17:55:35', 15, 1, 1, '25.00'),
(88, '2021-09-08 18:04:21', 15, 52, 1, '50.00');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalleventa`
--
ALTER TABLE `detalleventa`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `noventa` (`noventa`);

--
-- Indices de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `token_user` (`token_user`);

--
-- Indices de la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`codproducto`),
  ADD KEY `proveedor` (`proveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`codproveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idrol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `rol` (`rol`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`noventa`),
  ADD KEY `usuario` (`usuario`),
  ADD KEY `codcliente` (`codcliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalleventa`
--
ALTER TABLE `detalleventa`
  MODIFY `correlativo` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=398;

--
-- AUTO_INCREMENT de la tabla `entradas`
--
ALTER TABLE `entradas`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `codproducto` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `codproveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `noventa` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalleventa`
--
ALTER TABLE `detalleventa`
  ADD CONSTRAINT `detalleventa_ibfk_1` FOREIGN KEY (`noventa`) REFERENCES `venta` (`noventa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalleventa_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD CONSTRAINT `detalle_temp_ibfk_1` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `entradas_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`codproveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `proveedor_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`idrol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`codcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
