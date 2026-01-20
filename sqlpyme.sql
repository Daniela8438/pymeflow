-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: pymeflow_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) NOT NULL,
  `cuit` varchar(20) NOT NULL,
  `condicion_iva` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuit` (`cuit`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (2,'Agustin Gongora','27162807744','responsable inscripto','ag@hotmail.com.ar','01123383311','Culpina 171 pb torre b'),(16,'Arcor','30-50279317-5','no inscripto','arcor@arcor','08001110072','Arroyito, Córdoba'),(17,'lucas dore','2016788432','responsable inscripto','fatimagmartiarena@gmail.com','12546456','Culpina 171'),(18,'julia','2035324443','responsable inscripto','j@hotmail.com','01123383311','bs as'),(19,'menta','3050248724','no inscripto','menta@menta','081022456','buenos aires'),(20,'ifts','20178100','no inscripto','ifts12@gmail.com','43456676','Av. Belgrano 637, C1092 AAG, Buenos Aires, Argentina'),(21,'ronall','201564794','no es responsable inscripto','ronall@ronall','0810888333','velez 166'),(22,'carlos gomez','20167664214','responsable inscripto','carlos@gmail.com','112546497','flores'),(23,'RAMIRO','20353365478','responsable inscripto','RAMI@HOTMAIL.COM.AR','46372356','GOL 231'),(24,'MARIA','353211764','responsable inscripto','M@HOTMAIL.COM','01123383312','culpina 171 3c'),(25,'jose donato','16598746','responsable inscripto','kk@hotmail.com','46372356','bs as'),(26,'marcos man','20303545554','responsable inscripto','marxo@hotmail','1111111444','cabildo'),(27,'jonathan','20317348239','responsable inscripto','jk5j@hotmail.com','458880000','av elcano 3755'),(28,'MARIA','27353211434','responsable inscripto','oro@gmail.com','4582696','orazabal 630'),(29,'ernesto fernandez','2042504687','responsable inscripto','ernesto@hotmail.com','48521289','la matanza'),(30,'jose raul','2015649874','Consumidor Final','joya@google.com','458','av siempre viva 772');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `tipo_factura` enum('A','B','C') NOT NULL,
  `numero_factura` varchar(100) NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `archivo_pdf_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_factura` (`numero_factura`),
  KEY `id_pedido` (`id_pedido`),
  CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `medio_pago` enum('efectivo','transferencia','mercadopago','tarjeta_credito','tarjeta_debito') NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `comprobante_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pedido` (`id_pedido`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado_pedido` enum('en_preparacion','completado','cancelado') NOT NULL,
  `estado_pago` enum('pendiente','parcial','pagado','cancelado') NOT NULL,
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_vendedor` (`id_vendedor`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (1,16,1,'2025-09-28 12:07:38','completado','pagado',8000.00),(2,16,3,'2025-09-28 12:07:53','completado','pagado',4000.00),(3,2,3,'2025-09-28 12:09:44','completado','pagado',8000.00),(4,2,3,'2025-09-28 12:12:20','completado','pagado',4500.00),(5,16,2,'2025-09-28 12:12:45','completado','pagado',3000.00),(6,2,2,'2025-09-28 19:15:37','completado','pagado',4000.00),(7,16,2,'2025-09-28 19:19:19','completado','pagado',2000.00),(8,18,3,'2025-09-28 19:42:15','completado','pagado',10000.00),(9,17,2,'2025-09-28 19:44:00','completado','pagado',1000.00),(10,16,2,'2025-09-28 20:15:47','completado','parcial',1000.00),(11,24,3,'2025-09-28 20:19:11','completado','pagado',3500.00),(12,25,3,'2025-09-28 20:28:17','completado','pagado',4000.00),(13,26,3,'2025-08-25 20:27:59','completado','pagado',10000.00),(14,27,2,'2025-10-08 12:26:03','completado','pagado',6000.00),(15,2,2,'2025-10-20 20:29:21','completado','pagado',1000.00),(16,2,9,'2025-10-20 20:59:52','completado','pagado',6000.00),(17,21,9,'2025-10-20 21:00:52','completado','pagado',7900.00),(18,18,9,'2025-10-20 21:01:40','completado','pagado',2000.00);
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos_items`
--

DROP TABLE IF EXISTS `pedidos_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `pedidos_items_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pedidos_items_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos_items`
--

LOCK TABLES `pedidos_items` WRITE;
/*!40000 ALTER TABLE `pedidos_items` DISABLE KEYS */;
INSERT INTO `pedidos_items` VALUES (1,1,3,1,1000.00),(2,1,1,2,3500.00),(3,2,3,4,1000.00),(4,3,3,1,1000.00),(5,3,1,2,3500.00),(6,4,3,1,1000.00),(7,4,1,1,3500.00),(8,5,3,3,1000.00),(9,6,5,2,2000.00),(10,7,5,1,2000.00),(11,8,3,10,1000.00),(12,9,6,1,1000.00),(13,10,6,1,1000.00),(14,11,1,1,3500.00),(15,12,5,2,2000.00),(16,13,6,2,1000.00),(17,13,1,2,3500.00),(18,13,4,1,1000.00),(19,14,6,2,1000.00),(20,14,5,2,2000.00),(21,15,6,1,1000.00),(22,16,5,3,2000.00),(23,17,1,1,3500.00),(24,17,3,1,1000.00),(25,17,6,1,1000.00),(26,17,9,2,1200.00),(27,18,3,1,1000.00),(28,18,4,1,1000.00);
/*!40000 ALTER TABLE `pedidos_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `precio_costo` decimal(10,2) DEFAULT NULL,
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 0,
  `foto_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'coca','de 3 l',3500.00,2000.00,7,2,NULL),(3,'chocolate','blanco medio',1000.00,500.00,20,2,NULL),(4,'papas fritas','bolsa chica',1000.00,100.00,5,2,NULL),(5,'agua manaos','1.5l',2000.00,1000.00,3,2,NULL),(6,'manzanas','rojas',1000.00,500.00,4,1,NULL),(7,'tortas','de ricota',5000.00,2700.00,2,1,NULL),(8,'cocos','coco fruta',1000.00,500.00,2,6,NULL),(9,'frijoles','frijoles acidos',1200.00,500.00,8,2,NULL);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(255) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('administrador','vendedor') NOT NULL,
  `fecha_alta` datetime NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador Principal','12345678','admin@pymeflow.com','1122334455','admin','$2y$10$SZcDDUOfEii64kL/xi63fOyXpMmYypLfpwitmAgt0qRsrmZLEmBhO','administrador','2025-09-27 22:24:32','activo',1),(2,'Marcos Matalobos',NULL,'marcosmatalobos@gmail.com',NULL,'Marcos','$2y$10$sNX5dtm2QW7zKCy2hqQmNODeYsLSSgNII4h6JNmRtO2eeYPGO5//m','vendedor','2025-09-28 13:52:12','activo',1),(3,'Daniela Roberts',NULL,'dn@gmail.com',NULL,'Daniela','$2y$10$NEMsBKulX0H3sjdJdIvHs.b7gdRdxZo002HmiBuMGHicwaGTE8wd.','vendedor','2025-09-28 13:52:51','activo',1),(4,'maria borges',NULL,'m@m',NULL,'maria','$2y$10$WqNXFFbTAiEx2CZpp/YUFuOYU3G4Nbplc26ig0d/Z3N/QCA1UK6YS','vendedor','2025-09-28 19:21:21','inactivo',1),(5,'lucia',NULL,'lu@hotmail.com',NULL,'lu','$2y$10$VGPyJVto0ajZ32IHxJxhZOID1xBOYbU/b49DqghWb4lDL5lZYGU2u','vendedor','2025-09-28 20:21:10','activo',0),(7,'jose',NULL,'jose@gg',NULL,'joses','$2y$10$VGVo.5SYR6Uzg4vHPVIpbuoRvsMRIPLyzxh4i5cM3V1VuCtLV3tku','administrador','2025-09-30 20:30:13','inactivo',1),(8,'fatima gongora',NULL,'fatimagmartiarena@gmail.com',NULL,'fatima','$2y$10$QlRUbOudA058DyAkE0FY0./q2Wtlhohz3QAfYwoO521w0iEp95Qre','vendedor','2025-10-19 23:17:44','activo',1),(9,'gabriela',NULL,'g@google.com',NULL,'gabriela','$2y$10$YCYuW.YMyXzPHYkuL7TdJu/8mVcdLTI5PYVQ5s6U.MtW9VpphyQyS','vendedor','2025-10-20 20:58:32','activo',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-20 21:13:57
