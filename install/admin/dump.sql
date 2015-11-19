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
-- Table structure for table `base_group`
--

DROP TABLE IF EXISTS `base_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `base_group`
--

LOCK TABLES `base_group` WRITE;
/*!40000 ALTER TABLE `base_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `base_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `base_ip`
--

DROP TABLE IF EXISTS `base_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_ip` (
  `ip` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `base_ip`
--

LOCK TABLES `base_ip` WRITE;
/*!40000 ALTER TABLE `base_ip` DISABLE KEYS */;
/*!40000 ALTER TABLE `base_ip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `base_rule`
--

DROP TABLE IF EXISTS `base_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_group` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_page` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `base_rule`
--

LOCK TABLES `base_rule` WRITE;
/*!40000 ALTER TABLE `base_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `base_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `base_tree`
--

DROP TABLE IF EXISTS `base_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_tree` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `way` varchar(767) CHARACTER SET ascii DEFAULT NULL,
  `route` varchar(767) CHARACTER SET ascii DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  `actions` tinytext,
  `template` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `id_parent` int(11) NOT NULL DEFAULT '0',
  `id_prototype` int(11) NOT NULL DEFAULT '0',
  `is_prototype` int(11) NOT NULL DEFAULT '0',
  `from_prototype` varchar(4) CHARACTER SET ascii NOT NULL DEFAULT '0000',
  `mod` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_base_tree_way` (`is_prototype`,`way`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `base_tree`
--

LOCK TABLES `base_tree` WRITE;
/*!40000 ALTER TABLE `base_tree` DISABLE KEYS */;
INSERT INTO `base_tree` VALUES (1,',adm,,,',NULL,NULL,NULL,NULL,NULL,0,150,0,'0011','admin'),(2,',adm;access,,,',NULL,NULL,NULL,NULL,NULL,0,150,0,'0011','admin'),(3,',adm;access;group,,,',NULL,NULL,NULL,NULL,NULL,2,150,0,'0011','admin'),(4,',adm;access;group;new,,,',NULL,NULL,NULL,NULL,NULL,3,150,0,'0011','admin'),(5,',adm;access;user,,,',NULL,NULL,NULL,NULL,NULL,2,150,0,'0011','admin'),(6,',adm;access;user;new,,,',NULL,NULL,NULL,NULL,NULL,5,150,0,'0011','admin'),(8,',adm;logout,,,',NULL,NULL,'admin:logout',NULL,NULL,0,150,0,'0000','admin'),(9,',adm;mod,,,',NULL,NULL,NULL,NULL,NULL,0,150,0,'0011','admin'),(10,',adm;structure,,,',NULL,NULL,NULL,NULL,NULL,0,150,0,'0011','admin'),(11,',adm;structure;add,,,',NULL,NULL,NULL,NULL,NULL,10,150,0,'0011','admin'),(12,',adm;structure;branch,,id;type,',NULL,NULL,'user:auth;structure:branch',NULL,NULL,10,150,0,'0000','admin'),(13,',adm;structure;save;add,,,',NULL,NULL,'user:auth;structure:save',NULL,NULL,11,150,0,'0000','admin'),(14,',adm;structure;tree_main_state,,id;type,',NULL,NULL,'user:auth;structure:tree_main_state',NULL,NULL,10,150,0,'0000','admin'),(50,NULL,',adm;access;group;del;p:1:/\\d+/,,,',NULL,'user:auth;user:group_del',NULL,NULL,3,150,0,'0000','admin'),(51,NULL,',adm;access;group;edit;p:1:/^\\d+$/,,,',NULL,NULL,NULL,NULL,3,150,0,'0011','admin'),(52,NULL,',adm;access;group;p:1:/p\\d+/,,,',NULL,NULL,NULL,NULL,3,150,0,'0011','admin'),(53,NULL,',adm;access;group;save;p:1:/^\\w+$/,,,',NULL,'user:auth;user:user_group_save',NULL,NULL,3,150,0,'0000','admin'),(54,NULL,',adm;access;user;del;p:1:/\\d+/,,,',NULL,'user:auth;user:user_del',NULL,NULL,5,150,0,'0000','admin'),(55,NULL,',adm;access;user;edit;p:1:/^\\d+$/,,,',NULL,NULL,NULL,NULL,5,150,0,'0011','admin'),(56,NULL,',adm;access;user;p:1:/p\\d+/,,,',NULL,NULL,NULL,NULL,5,150,0,'0011','admin'),(57,NULL,',adm;access;user;save;p:1:/^\\w+$/,,,',NULL,'user:auth;user:user_group_save',NULL,NULL,5,150,0,'0000','admin'),(58,NULL,',adm;access;user_group;p:1:/\\d+/,,,',NULL,NULL,NULL,NULL,5,150,0,'0011','admin'),(59,NULL,',adm;access;user_group;p:1:/\\d+/;p:1:/p\\d+/,,,',NULL,NULL,NULL,NULL,5,150,0,'0011','admin'),(60,NULL,',adm;mod;install;p:1:/^\\w+$/,,,',NULL,'user:auth;admin:install',NULL,NULL,9,0,0,'0000','admin'),(61,NULL,',adm;mod;uninstall;p:1:/^\\w+$/,,,',NULL,'user:auth;admin:uninstall',NULL,NULL,9,0,0,'0000','admin'),(62,NULL,',adm;structure;del;p:1:/\\d+/,,,',NULL,'user:auth;structure:del',NULL,NULL,10,150,0,'0000','admin'),(63,NULL,',adm;structure;edit;p:1:/\\d+/,,,',NULL,NULL,NULL,NULL,10,150,0,'0011','admin'),(64,NULL,',adm;structure;edit;p:1:/\\d+/;p:1:/\\w+/,,,',NULL,NULL,NULL,NULL,10,150,0,'0011','admin'),(65,NULL,',adm;structure;param;p:1:/\\d+/;p:1:/[\\w\\:]+/,,,',NULL,NULL,NULL,NULL,10,150,0,'0011','admin'),(66,NULL,',adm;structure;param;p:1:/\\d+/;p:1:/[\\w\\:]+/;p:1:/[\\w\\:]+/,,,',NULL,NULL,NULL,NULL,10,150,0,'0011','admin'),(67,NULL,',adm;structure;save;p:1:/\\d+/,,,',NULL,'user:auth;structure:save',NULL,NULL,63,150,0,'0000','admin'),(68,NULL,',adm;structure;save;p:1:/\\d+/;p:1:/[^\\/]+/,,,',NULL,'user:auth;structure:save',NULL,NULL,64,150,0,'0000','admin'),(69,NULL,',adm;structure;save_param;p:1:/\\d+/;p:1:/[\\w\\:]+/,,,',NULL,'user:auth;structure:save_param',NULL,NULL,65,150,0,'0000','admin'),(70,NULL,',adm;structure;save_param;p:1:/\\d+/;p:1:/[\\w\\:]+/;p:1:/[\\w\\:]+/,,,',NULL,'user:auth;structure:save_param',NULL,NULL,66,150,0,'0000','admin'),(100,NULL,NULL,'content','user:user',NULL,NULL,5,0,0,'0000','admin'),(101,NULL,NULL,'content','user:group',NULL,NULL,3,0,0,'0000','admin'),(102,NULL,NULL,'content','user:user_group_edit',NULL,NULL,4,0,0,'0000','admin'),(103,NULL,NULL,'content','user:user_group_edit',NULL,NULL,51,0,0,'0000','admin'),(104,NULL,NULL,'content','user:user',NULL,NULL,58,0,0,'0000','admin'),(105,NULL,NULL,'content','user:user',NULL,NULL,59,0,0,'0000','admin'),(106,NULL,NULL,'content','user:user_group_edit',NULL,NULL,6,0,0,'0000','admin'),(107,NULL,NULL,'content','user:user',NULL,NULL,56,0,0,'0000','admin'),(108,NULL,NULL,'content','user:user_group_edit',NULL,NULL,55,0,0,'0000','admin'),(109,NULL,NULL,'content','structure:content',NULL,NULL,1,0,0,'0000','admin'),(110,NULL,NULL,'content','structure:add_edit',NULL,NULL,11,0,0,'0000','admin'),(111,NULL,NULL,'content','structure:add_edit',NULL,NULL,63,0,0,'0000','admin'),(112,NULL,NULL,'content','structure:add_edit',NULL,NULL,64,0,0,'0000','admin'),(113,NULL,NULL,'content','structure:param',NULL,NULL,65,0,0,'0000','admin'),(114,NULL,NULL,'content','structure:param',NULL,NULL,66,0,0,'0000','admin'),(115,NULL,NULL,'content',NULL,'view/admin/doc.php',NULL,7,0,0,'0000','admin'),(116,NULL,NULL,'content','admin:mod',NULL,NULL,9,0,0,'0000','admin'),(117,NULL,NULL,'nav','admin:nav',NULL,NULL,150,0,0,'0000','admin'),(118,NULL,NULL,'content','admin:update_check',NULL,NULL,15,0,0,'0000','admin'),(119,NULL,NULL,'content','structure:content',NULL,NULL,10,0,0,'0000','admin'),(150,',adm;base_proto,,,',NULL,NULL,'user:auth','view/admin/main.php',NULL,0,0,1,'0000','admin');
/*!40000 ALTER TABLE `base_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `base_user`
--

DROP TABLE IF EXISTS `base_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `pass` varchar(32) DEFAULT NULL,
  `id_group` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `base_user`
--

LOCK TABLES `base_user` WRITE;
/*!40000 ALTER TABLE `base_user` DISABLE KEYS */;
INSERT INTO `base_user` VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3',0,1);
/*!40000 ALTER TABLE `base_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-04-26 20:19:26
