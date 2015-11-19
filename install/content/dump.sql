-- MySQL dump 10.13  Distrib 5.5.47, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: solo
-- ------------------------------------------------------
-- Server version	5.5.47-0ubuntu0.12.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mod_content`
--

DROP TABLE IF EXISTS `mod_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_tree` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `varchar255` varchar(255) DEFAULT NULL,
  `text` text,
  `longtext` longtext,
  `date` int(11) NOT NULL DEFAULT '0',
  `sec` int(11) NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_mod_title_id_tree` (`id_tree`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mod_content`
--

LOCK TABLES `mod_content` WRITE;
/*!40000 ALTER TABLE `mod_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `mod_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mod_content_sec`
--

DROP TABLE IF EXISTS `mod_content_sec`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_content_sec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mod_content_sec`
--

LOCK TABLES `mod_content_sec` WRITE;
/*!40000 ALTER TABLE `mod_content_sec` DISABLE KEYS */;
INSERT INTO `mod_content_sec` VALUES (1,'Страница'),(2,'Новость');
/*!40000 ALTER TABLE `mod_content_sec` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `base_tree` WRITE, `base_tree` AS a READ;
/*!40000 ALTER TABLE `base_tree` DISABLE KEYS */;
INSERT INTO base_tree(way, route, tag, actions, template, file, id_parent, id_prototype, is_prototype, from_prototype, `mod`) VALUES
(',mod;content,,,', NULL, NULL, NULL, NULL, NULL, 0, 150, 0, '0011', 'content');
INSERT INTO base_tree(way, route, tag, actions, template, file, id_parent, id_prototype, is_prototype, from_prototype, `mod`) VALUES
(NULL, NULL, 'content', 'content:last_edit', NULL, NULL, (SELECT MAX(a.id) FROM base_tree AS a), 0, 0, '0000', 'content');
/*!40000 ALTER TABLE `base_tree` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-04-26 20:29:12
