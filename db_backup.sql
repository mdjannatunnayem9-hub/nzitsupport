-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: nzitsupport
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
INSERT INTO `devices` VALUES (17,'Biometric'),(20,'Camera'),(16,'CCTV'),(1,'Desktop'),(25,'Discussion'),(22,'Headset'),(18,'Hub'),(12,'Keyboard'),(2,'Laptop'),(26,'Meeting'),(19,'Modem'),(11,'Monitor'),(13,'Mouse'),(24,'Other'),(14,'Phone'),(4,'Photocopy'),(3,'Printer'),(27,'Product QC'),(28,'Product Receive'),(10,'Projector'),(6,'Router'),(5,'Scanner'),(8,'Server'),(23,'Smartboard'),(7,'Switch'),(15,'Tablet'),(9,'UPS');
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nzsupportlist`
--

DROP TABLE IF EXISTS `nzsupportlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nzsupportlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sup_id` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `user_name` varchar(100) NOT NULL,
  `device` varchar(200) DEFAULT NULL,
  `support_person` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `remaining_days` int(11) DEFAULT 0,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nzsupportlist`
--

LOCK TABLES `nzsupportlist` WRITE;
/*!40000 ALTER TABLE `nzsupportlist` DISABLE KEYS */;
INSERT INTO `nzsupportlist` VALUES (1,'101','Complete','Jony Sir','Others','All IT Person','2026-04-04','To Sell dump product of HO. Bappi looking for vendor.','Many Vendor doesn\'t want to buy dump product. We have 3 vendors, 1 is Maymuna Enterprise and other 2 is Alamin vai\'s vendor.','2026-04-08',36,'0000-00-00','2026-05-14 17:17:11'),(2,'102','Complete','Mr. Imran, ACC','Software','Nayem','2026-04-07','Tally Login Problem. REF: 1','On Tally’s default page, the list of companies is showing a problem. But if click on ‘Select Tally Data Server’ then the login works properly','2026-04-07',37,'2026-04-07','2026-05-14 17:17:11'),(3,'103','Complete','Mostak Sir, MKT','Software','Nayem','2026-04-07','Anti Virus. It shows that another device(192.168.251.130) is trying to access Mr. Mostak’s device and attempting to insert suspicious data','I run the deep scan. Need to analyze more why is this happening.','0000-00-00',740147,'0000-00-00','2026-05-14 17:17:11'),(4,'104','SOP','All Photocopy','Photocopy','Bappi','2026-04-07','Page Count and Checking.','Page Count and Checking.','2026-04-07',37,'2026-04-07','2026-05-14 17:17:11'),(5,'105','Complete','Factory','Monitor','Bappi','2026-04-07','Panel Problem.','Need checking and repairing.','2026-04-11',33,'0000-00-00','2026-05-14 17:17:11'),(6,'106','Complete','Rajim fakir, Com','CPU','Bappi','2026-04-07','ram problem','ram temperature 80% to 90%','2026-04-11',33,'2026-04-11','2026-05-14 17:17:11'),(7,'107','Complete','Mr. Ahsan','CPU','Bappi','2026-07-04','CPU is slow.','There is several problem. No use of upgrading this PC. Recommending New CPU.','2026-04-20',24,'0000-00-00','2026-05-14 17:17:11'),(8,'108','Complete','Manirul Sir, Acc','Accessories','Nayem','2026-04-07','Need multiple Device connection.','Need Multihub for connecting, Scanner, Printer, Mouse, Keyboard and Ethernet.','2026-04-15',29,'0000-00-00','2026-05-14 17:17:11'),(9,'109','Complete','Jamal Hossain','CPU','Bappi','2026-04-07','ram temperature high','Ram temperature high add ram','2026-04-15',29,'2026-04-15','2026-05-14 17:17:11'),(10,'110','Complete','Mohibul Vai, VAT','Network','Nayem','2026-04-08','Cannot access website even showing network connection.','Default Search Engine Problem. User warn not to use unknown site and extension.','2026-04-08',36,'2026-04-08','2026-05-14 17:17:11'),(11,'111','SOP','All Photocopy','Photocopy','Bappi','2026-04-08','Page Count and Checking.','Page Count and Checking.','2026-04-08',36,'0000-00-00','2026-05-14 17:17:11'),(12,'112','Complete','Badiuzzaman','CPU','Bappi','2026-04-08','network ip setting','network problem','2026-04-08',36,'0000-00-00','2026-05-14 17:17:11'),(13,'113','Complete','Turin, ACC','Software','Nayem','2026-04-08','Tally Login Problem.','Solved by Tally support Bahar vai.','2026-04-08',36,'2026-04-08','2026-05-14 17:17:11'),(14,'114','Complete','6th flor north','Network','Bappi','2026-04-08','networking','file Network','2026-04-08',36,'0000-00-00','2026-05-14 17:17:11'),(15,'115','Complete','Ripon Sheikh','Laptop','Bappi','2026-04-08','google chrome problem','browsing problem','2026-04-08',36,'2026-04-08','2026-05-14 17:17:11'),(16,'116','Complete','factory','CPU','Bappi','2026-04-08','display not showing','board or processor problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(17,'117','Complete','Rina Akter, MKT-1','Software','Nayem','2026-04-08','Web accessing problem.','One of the option from inditex cannot be accessed. Mailed Zara\'s IT support to solve this issue. \r\nIssue is solved now.','0000-00-00',740147,'0000-00-00','2026-05-14 17:17:11'),(18,'118','SOP','All Photocopy','Photocopy','Bappi','2026-04-09','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(19,'119','Complete','Tutul Sir, MKT','Network','Bappi','2026-04-09','Network Connection Problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(20,'120','Complete','Kazi Russell','CPU','Bappi','2026-04-09','automated ok','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(21,'121','SOP','IP Camera','Others','Nayem','2026-04-09','MD sir IP Camera checking.','SOP Work','0000-00-00',0,'2026-04-09','2026-05-14 17:17:11'),(22,'122','Complete','CFO sir','Accessories','Bappi','2026-04-09','portable SSD kinar jonno','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(23,'123','Complete','Uzzal Sir, MKT','Printer','Nayem','2026-04-09','Printer Connection Problem.','Printer Driver not working with his Laptop. Installing new driver solved the issue.','0000-00-00',0,'2026-04-09','2026-05-14 17:17:11'),(24,'124','Complete','Rasel Vai, MKT','Software','Nayem','2026-04-09','PDF Reader not opening.','Uninstall and reinstallation fixed the issue.','0000-00-00',0,'2026-04-09','2026-05-14 17:17:11'),(25,'125','Complete','Eernena Ltd.','Others','Nayem','2026-04-09','Product Recieved.','Sr. GM Multihub and Don sir\'s combo keyboard.','0000-00-00',0,'2026-04-09','2026-05-14 17:17:11'),(26,'126','Complete','Maniruzzaman sir','Laptop','Bappi','2026-04-09','Type C to multihub setup','Naw ok','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(27,'127','Complete','MR Sulaiman','CPU','Bappi','2026-04-09','\"I\'ve moved the two computers to each other\'s places.\"','Naw all ok','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(28,'128','SOP','all photocopy','Photocopy','Bappi','2026-04-11','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(29,'129','Complete','Sulayman hossen','CPU','Bappi','2026-04-11','Computer not open','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(30,'130','Complete','Tariq sir','Monitor','Bappi','2026-04-11','Monitor Convertor change','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(31,'131','Complete','Siddik sir','Network','Bappi','2026-04-11','Network not connected','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(32,'132','Complete','4th flor photocopy','Photocopy','Bappi','2026-04-11','Scanner print problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(33,'133','Complete','Monir sir, MKT','Laptop','Nayem','2026-04-11','Slow System','Need SSD.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(34,'134','Analyzing','Rafi vai, MKT','Software','Nayem','2026-04-11','Excel Hang Problem.','The excel sent by Mr. Shafi, Dyeing store, Factory, always hangs. Talked with Emran,  he will check it.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(35,'135','Complete','Linkon Sir','Printer','Bappi','2026-04-11','Printer not responding','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(36,'136','Complete','Eernena Ltd','Others','Bappi','2026-04-11','Product Recieved.','Factory and Ho sinha sir printer','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(37,'137','Complete','Rimon hossain','CPU','Bappi','2026-04-11','Computer not open','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(38,'138','Complete','Jannatun Nayem','Printer','Bappi','2026-04-12','Printer Head problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(39,'139','Complete','Abu Sinha sir','Printer','Bappi','2026-04-12','New printer setup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(40,'140','Complete','DGM Monir Sir','Laptop','Bappi','2026-04-12','Laptop SSD/ NVME Setup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(41,'141','SOP','All Photocopy','Photocopy','Bappi','2026-04-12','Page Count and Checking.','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(42,'142','Complete','Simu, MKT','Others','Nayem','2026-04-12','Printing problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(43,'143','Complete','All Accounts','Software','All IT Person','2026-04-12','Tally Uninstall.','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(44,'144','Abandon','Director Monir Sir','Software','Nayem','2026-04-12','365 download','Only excel was downloaded','0000-00-00',740147,'0000-00-00','2026-05-14 17:17:11'),(45,'145','Complete','Azam Sir, Procurements','Printer','Bappi','2026-04-13','Printer Paper stuck.','Naw its fine','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(46,'146','Complete','AB Computer','Printer','Bappi','2026-04-12','Products Receive','Products Receive','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(47,'147','Complete','Bina medam','Software','Bappi','2026-04-12','Printer cannot connect to this printer','Network problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(48,'148','SOP','All Photocopy','Photocopy','Bappi','2026-04-13','Page Count and Checking.','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(49,'149','Complete','Mahabubul alam','Network','Bappi','2026-04-13','DSCP IP','Ip change','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(50,'150','Complete','Azam sir','Printer','Bappi','2026-04-13','Printer Roller paper Problem','Roller Changed.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(51,'151','Complete','Factory Printer','Printer','Bappi','2026-04-13','','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(52,'152','Complete','Md Alhaz uddin','Network','Bappi','2026-04-13','Computer network not working','We redirect his LAN in other switch.','0000-00-00',0,'2026-04-13','2026-05-14 17:17:11'),(53,'153','Complete','Md Zakariya','Network','Bappi','2026-04-15','DHCP IP','Static IP Problem','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(54,'154','Complete','Nipun','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(55,'155','Complete','Mr Sihib','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(56,'156','Complete','Mr Naymul islam Firoj','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(57,'157','Complete','mr. kanchon sir','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(58,'158','Complete','Mr shakil ahamed','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(59,'159','Complete','Mr Soyeb','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(60,'160','Complete','Mr Jamal hossain','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(61,'161','Complete','All Photocopy','Photocopy','Bappi','2026-04-15','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(62,'162','Complete','Mr. Johirul sir acc','Network','Bappi','2026-04-15','CFO sir share network file','','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(63,'163','Complete','Mr Arafat , MKT','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(64,'164','Complete','mr mustakim , Pro','Network','Bappi','2026-04-15','Ip not set DHCP and cable change','Port problem','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(65,'165','Complete','Mr Akash Comm','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(66,'166','Complete','Mr Mizan Insantive','Network','Bappi','2026-04-15','DHCP IP','Ip change','0000-00-00',0,'2026-04-15','2026-05-14 17:17:11'),(67,'167','Complete','Mr Foysal sir audit','Laptop','Bappi','2026-04-16','Laptop not open','','0000-00-00',0,'2026-04-16','2026-05-14 17:17:11'),(68,'168','Complete','Mr Delowar Bond','Monitor','Bappi','2026-04-16','Monitor Display Signal not connect','','0000-00-00',0,'2026-04-16','2026-05-14 17:17:11'),(69,'169','Complete','Mampi Kundo','CPU','Bappi','2026-04-16','PC does not opening.','','0000-00-00',0,'2026-04-16','2026-05-14 17:17:11'),(70,'170','Complete','Sanjay Sir, Acc','Printer','Bappi','2026-04-16','Need Toner Refill.','','0000-00-00',0,'2026-04-16','2026-05-14 17:17:11'),(71,'171','Complete','Mr Mannan sir','Network','Bappi','2026-04-16','Ip Problem','The same IP is given in 2 places.','0000-00-00',0,'2026-04-16','2026-05-14 17:17:11'),(72,'172','SOP','All photocopy','Photocopy','Bappi','2026-04-16','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'2026-04-16','2026-05-14 17:17:11'),(73,'173','Complete','Rasel vai, Acc','Software','Nayem','2026-04-16','EsimSol Problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(74,'174','Complete','DGM Shahiduzzaman sir','Network','Bappi','2026-04-16','DHCP IP','Ip change','0000-00-00',0,'2026-04-16','2026-05-14 17:17:11'),(75,'175','Complete','Kanchan Sir, MKT','CPU','Bappi','2026-04-16','User Lock in Windows.','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(76,'176','Complete','Factory  HDD Data recovery','CPU','All IT Person','2026-04-18','HDD Dynamic','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(77,'177','Complete','Mr Alamin','CPU','Bappi','2026-04-18','Computer windows setup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(78,'178','Complete','Mr Roki','CPU','Bappi','2026-04-18','SSD and windows software setup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(79,'179','Complete','Mr Rakib','Network','Bappi','2026-04-18','DSCP IP','Ip change','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(80,'180','Complete','Taly Server','CPU','Bappi','2026-04-18','Server ram change','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(81,'181','Complete','1000 fix','Printer','Bappi','2026-04-19','Card printer er jonno Motijheel 1000 fix a jawa hoiselo','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(82,'182','SOP','All photocopy','Photocopy','Bappi','2026-04-20','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(83,'183','Complete','Mr Emran Bond','CPU','Bappi','2026-04-20','CPU side change','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(84,'184','Complete','Cristal Vision','Others','Bappi','2026-04-20','Products revived','Products revived','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(85,'185','Complete','Mr Partho','Printer','Bappi','2026-04-20','Toner Change 76A','Replace the tonar cartridge','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(86,'186','Complete','Mr Partho Marketing','Printer','Bappi','2026-04-20','Toner change for 76A','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(87,'187','Complete','Mr Alhaz Land','Network','Bappi','2026-04-20','Switch hang','Restart','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(88,'188','Complete','GM Sir Bond','Laptop','Bappi','2026-04-20','Scanner Problem','Change the laptop scaner port','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(89,'189','Complete','Factory','Printer','Bappi','2026-04-20','Printer couldn’t print','Fixing problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(90,'190','Complete','Land','Network','Bappi','2026-04-21','Computer and telephone set Enternet problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(91,'191','Complete','Apparels Monitor','Monitor','Bappi','2026-04-21','Power Bottom problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(92,'192','SOP','All photocopy','Photocopy','Bappi','2026-04-21','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(93,'193','Complete','Mr Dev','Laptop','Bappi','2026-04-21','New Laptop Ram installing','New Ram Problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(94,'194','Complete','Global Brand','Others','Bappi','2026-04-21','Products receive','Products receive','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(95,'195','Complete','Virdi Device','Others','Bappi','2026-04-21','Setup 7 flor','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(96,'196','Complete','Advance ICT','Others','Bappi','2026-04-21','Products receive','Products receive','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(97,'197','SOP','All photocopy','Photocopy','Bappi','2026-04-22','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(98,'198','Complete','Factory','Printer','Bappi','2026-04-22','Gear sound problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(99,'199','Complete','Factory','Monitor','Bappi','2026-04-22','No Power Problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(100,'200','Complete','Mr Shoikot','Network','Bappi','2026-04-22','Internet cable change','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(101,'201','Complete','5th Commercial 404 printer','Printer','Bappi','2026-04-23','Paper jam','Fixing roller and flim problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(102,'202','Complete','Mr Tareq sir','Network','Bappi','2026-04-23','Telephone conflict','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(103,'203','SOP','all photocopy','Photocopy','Bappi','2026-04-23','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(104,'204','Complete','CFO sir','Others','Bappi','2026-04-23','Type B Charger cable','Pulice plaza','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(105,'205','Complete','Tanim. ACC','UPS','All IT Person','2026-04-25','UPS Connection Problem. After monitor power off 5 min it become normal.','','2026-04-27',17,'0000-00-00','2026-05-14 17:17:11'),(106,'206','Complete','Harun, Pro','Printer','Nayem','2026-04-25','Paper Jam even after paper was out.','After cleaning the front end rollers the printer started to function again.','0000-00-00',0,'2026-04-25','2026-05-14 17:17:11'),(107,'207','Complete','Mr Tanim Accounts','UPS','Bappi','2026-04-26','Ups no backup','Bettary problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(108,'208','SOP','all photocopy','Photocopy','Bappi','2026-04-26','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(109,'209','Complete','Insantive','Printer','Bappi','2026-04-26','Papere choto choto dag ashe','Fixing Or roller a problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(110,'210','Complete','Mr Emran Accounts','UPS','Bappi','2026-04-26','No backup','Bettary replace','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(111,'211','Complete','Factory Printer','Printer','Bappi','2026-04-26','','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(112,'212','Complete','Mr Yeasin','UPS','Bappi','2026-04-26','No backup','Bettary replace','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(113,'213','Complete','Mr Abu sinha sir','Software','Bappi','2026-04-27','6030 printer error problem','reinstalle the driver software','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(114,'214','SOP','all photocopy','Photocopy','Bappi','2026-04-27','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(115,'215','Complete','Mr Evan','Network','Bappi','2026-04-27','No internet','restart the computer naw ok','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(116,'216','Complete','Mr Shiddik sir','Network','Bappi','2026-04-27','No internet','Ethernet adepter problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(117,'217','Complete','Mr Sadiqul islam import','UPS','Bappi','2026-04-27','No backup','Bettary replace','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(118,'218','Complete','Mr Mahbub Alam accounts','UPS','Bappi','2026-04-27','No backup','Bettary replace','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(119,'219','Complete','1000 fix er kaje Motijheel jawa hoisa','Printer','Bappi','2026-04-28','Card printer','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(120,'220','Complete','Siddik sir','Network','Bappi','2026-04-28','Network Adepter change','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(121,'221','Complete','Joni sir A3 Paper er jonno ghulshan 1 jawa','Others','Bappi','2026-04-28','','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(122,'222','Complete','Mr Shuvo','Laptop','Bappi','2026-04-30','New Keyboard installed','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(123,'223','Complete','6th photocopy toner','Photocopy','Bappi','2026-04-30','Add new toner','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(124,'224','Complete','Mr Feroz','Others','Bappi','2026-04-30','Computer not connect to printer','Add printer','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(125,'225','Complete','Gm Alauddin sir','Laptop','Bappi','2026-04-30','WhatsApp not opening the file','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(126,'226','Complete','Factory Printer','Printer','Bappi','2026-04-30','Gear set Problem','Gear sound','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(127,'227','Complete','CEO sir room check','Others','All IT Person','2026-05-02','Wifi,TV,telephone,camera checku','Need to check Wire or Headset.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(128,'228','SOP','All photocopy','Photocopy','Bappi','2026-05-02','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(129,'229','Complete','Factory','CPU','Bappi','2026-05-02','Display output not showing','Whatsapp Beta was downloaded.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(130,'230','Complete','Kawser, Incentive','Printer','All IT Person','2026-05-02','Printer output problem.','Because of Empty toner.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(131,'231','Complete','Tofazzel Vai, Incentive','Software','Bappi','2026-05-03','Need activation and Monitor check','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(132,'232','Complete','Mozammel sir, MKT','Others','Nayem','2026-05-03','Virdi Punch Problem','New finger added on the software.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(133,'233','Pending','Simu Apu, MKT','Software','Nayem','2026-05-03','Font problem in mail.','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(134,'234','Complete','Rafik Vai, MKT','Software','Nayem','2026-05-03','Buyer website not opening in Browser','Unsolved. Most probably serverside problem. need to follow up.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(135,'235','Complete','Mother board repairing 1000fix','CPU','Bappi','2026-05-03','dell brand mother board repairing issue','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(136,'236','Complete','siddik sir laptop','Network','Bappi','2026-05-03','Laptop port problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(137,'237','Complete','Add toner for 5th mr, bashar side','Accessories','Bappi','2026-05-03','6 pcs toner deliver','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(138,'238','Complete','Land mr, joni','Accessories','Bappi','2026-05-03','Mouse installed','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(139,'239','Complete','Mr, Abu sinha sir','Network','Bappi','2026-05-03','Shaire file and folder shareing for 2 PC','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(140,'240','SOP','All photocopy','Photocopy','Bappi','2026-05-04','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(141,'241','Complete','All photocopy Cleaning','Photocopy','Bappi','2026-05-04','Photocopy Clean and maintenance','Photocopy Clean and maintenance','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(142,'242','Complete','Factory Computer','CPU','Bappi','2026-05-04','Computer Processor setup and service','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(143,'243','On Hold','Factory Zebra card printer','Printer','All IT Person','2026-05-04','Chaking and testing','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(144,'244','Complete','7th new sir room','Network','Bappi','2026-05-04','Sir room network and moduller setup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(145,'245','Complete','7th flor cctv','Others','Bappi','2026-05-04','cctv camera has been change other side','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(146,'246','Complete','8th photocopy','Photocopy','','0000-00-00','','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(147,'247','SOP','All photocopy','Photocopy','Bappi','2026-05-05','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(148,'248','Complete','Lipu sir','Laptop','Bappi','2026-05-05','New laptop add and networking','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(149,'249','Complete','Susten Ruhul sir','Laptop','Bappi','2026-05-05','New laptop add and networking','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(150,'250','Complete','Mr hasib','Laptop','Bappi','2026-05-05','Laptop new ram add and new windows installed','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(151,'251','Complete','5th photocopy B','Photocopy','Bappi','2026-05-05','Paper sige not matching','Naw ok','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(152,'252','SOP','All photocopy','Photocopy','Bappi','2026-05-06','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(153,'253','Complete','Mr Roky','UPS','Bappi','2026-05-06','No backup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(154,'254','Complete','Mr Lipu','Laptop','Bappi','2026-05-06','Windows virus problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(155,'255','','Visit to factory','Photocopy','Bappi','2026-05-07','','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(156,'256','Complete','Mr Harun or roshid','Scanner','Bappi','2026-05-09','Scaneer setup','Scaneer setup','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(157,'257','SOP','All photocopy','Photocopy','Bappi','2026-05-09','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(158,'258','Complete','Mr Ruhul sir','Network','Bappi','2026-05-09','DSCP ip','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(159,'259','Complete','Factory monitor token 2033','Monitor','Bappi','2026-05-10','Switch problem','Exchange the other monitor','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(160,'260','Complete','Factory monitor token 2036','Monitor','Bappi','2026-05-10','Display ribon problem','Change the ribbon','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(161,'261','SOP','All photocopy','Photocopy','Bappi','2026-05-10','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(162,'262','Complete','Ruhul sir','Software','Bappi','2026-05-10','WhatsApp installed','and login','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(163,'263','Complete','5th photocopy toner','Photocopy','Bappi','2026-05-10','Change the toner unit','Change the toner unit','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(164,'264','Complete','Ehsan sir','Network','Bappi','2026-05-12','DSCP IP','Add New IP','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(165,'265','Complete','4th photocopy toner','Accessories','Bappi','2026-05-12','Add new toner cartridge','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(166,'266','SOP','All photocopy','Software','Bappi','2026-05-12','Page Count and Checking.','Page Count and Checking.','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(167,'267','Complete','Advance ICT','Others','Bappi','2026-05-11','Products receive','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(168,'268','Running','1000fix printer','Printer','Bappi','2026-05-11','Ho to Motijheel work','','0000-00-00',740147,'0000-00-00','2026-05-14 17:17:11'),(169,'269','Complete','7th flor susten','Network','Bappi','2026-05-12','Susten room new employe internet connection setup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(170,'270','Running','Mr Mokshedul','CPU','Bappi','2026-05-12','Mother board connection setup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(171,'271','Complete','Mr Akib 3rd flor','Laptop','Bappi','2026-05-12','Laptop keyboard problem.','Replace the new keyboard jalai.keyboard','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(172,'272','Complete','Factory monitor','Monitor','Bappi','2026-05-12','Samsung old monitor panel problem','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(173,'273','Complete','2nd flor','CPU','Bappi','2026-05-13','3user eset setup','Virus problem','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(174,'274','Complete','Mr Roki','Software','Bappi','2026-05-13','Bangla typing problem','Bijoy 16 setup','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(175,'275','Complete','Advance ICT','Others','Bappi','2026-05-13','Products recive','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(176,'276','Complete','Mr Ahsan','CPU','Bappi','2026-05-14','New pc setup and HDD file Transfer','','0000-00-00',740147,'0000-00-00','2026-05-14 17:17:11'),(177,'277','Complete','Factory Audit','Network','Bappi','2026-05-14','New ip setup','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(178,'278','Complete','5th Insantive','Photocopy','Bappi','2026-05-14','New toner replace','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(179,'279','Complete','Mr Sarwar sir','Software','Bappi','2026-05-14','Printer cannot connect to share printer','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(180,'280','Pending','Factory','CPU','Bappi','2026-05-14','Computer not open / no power / Nothing to see','','0000-00-00',0,'0000-00-00','2026-05-14 17:17:11'),(181,'NZ-0001','Complete','Nayem','Laptop','Nayem','2026-05-14','Space Problem','Clearing old file ','2026-05-15',0,'2026-05-14','2026-05-14 19:01:56'),(182,'NZ-0002','Complete','Nayem','Keyboard','Nayem','2026-05-14','It was not laptop it was keyboard.','Test','2026-05-17',2,'2026-05-14','2026-05-14 19:08:57'),(183,'NZ-0003','Running','Jannatun Nayem','Desktop','Nayem','2026-05-15','Test','Test','2026-05-16',0,NULL,'2026-05-15 07:21:17');
/*!40000 ALTER TABLE `nzsupportlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_permissions`
--

DROP TABLE IF EXISTS `page_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) NOT NULL,
  `page_title` varchar(200) NOT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_name` (`page_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_permissions`
--

LOCK TABLES `page_permissions` WRITE;
/*!40000 ALTER TABLE `page_permissions` DISABLE KEYS */;
INSERT INTO `page_permissions` VALUES (1,'index','Home / Index',1),(2,'nzsupportlist','NZ Support List',1),(3,'admin','Admin Panel',0);
/*!40000 ALTER TABLE `page_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remarks`
--

DROP TABLE IF EXISTS `remarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `support_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `remark` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `support_id` (`support_id`),
  CONSTRAINT `remarks_ibfk_1` FOREIGN KEY (`support_id`) REFERENCES `nzsupportlist` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remarks`
--

LOCK TABLES `remarks` WRITE;
/*!40000 ALTER TABLE `remarks` DISABLE KEYS */;
INSERT INTO `remarks` VALUES (1,182,'admin','Need more time','2026-05-15 01:26:01'),(2,182,'Nayem','C drive need to format, and need to give windows.','2026-05-15 01:26:42'),(3,182,'admin','Test for the 3rd time','2026-05-15 01:35:04'),(4,182,'admin','Status: Analyzing -> Running','2026-05-15 01:37:27'),(5,182,'admin','Deadline: 2026-05-16 -> 2026-05-17','2026-05-15 01:37:49'),(6,182,'admin','Update: Device: Laptop -> Keyboard | Desc: Test -> It was not laptop it was keyboard.','2026-05-15 01:39:42'),(7,182,'admin','Status: Running -> Complete','2026-05-15 01:48:17'),(8,182,'Nayem','Update: Status: Complete -> SOP','2026-05-15 02:14:06'),(9,182,'Nayem','Status: SOP -> Complete','2026-05-15 02:14:36'),(10,183,'admin','Test','2026-05-15 13:23:59'),(11,183,'admin','Test','2026-05-15 21:48:55'),(12,183,'Jannatun Nayem','Test','2026-05-15 21:59:18'),(13,183,'Nayem','Test','2026-05-15 22:06:22'),(14,183,'admin','Test','2026-05-15 22:33:20'),(15,183,'admin','Update: Desc: Teat -> Test','2026-05-15 23:33:34');
/*!40000 ALTER TABLE `remarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#6c757d',
  `text_color` varchar(7) DEFAULT '#fff',
  `sort_order` int(11) DEFAULT 0,
  `abbreviation` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statuses`
--

LOCK TABLES `statuses` WRITE;
/*!40000 ALTER TABLE `statuses` DISABLE KEYS */;
INSERT INTO `statuses` VALUES (1,'Pending','#fd7e14','#000',2,'P'),(2,'Running','#ffc107','#000',1,'R'),(3,'Analyzing','#0dcaf0','#fff',3,'An'),(4,'On Hold','#dc3545','#fff',4,'OH'),(5,'SOP','#6f42c1','#fff',6,'S'),(6,'Abandon','#212529','#fff',7,'Ab'),(7,'Complete','#198754','#fff',5,'C');
/*!40000 ALTER TABLE `statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_page_permissions`
--

DROP TABLE IF EXISTS `user_page_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_page_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `can_view` tinyint(1) DEFAULT 0,
  `can_edit` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0,
  `can_update` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_page` (`user_id`,`page_name`),
  CONSTRAINT `user_page_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_page_permissions`
--

LOCK TABLES `user_page_permissions` WRITE;
/*!40000 ALTER TABLE `user_page_permissions` DISABLE KEYS */;
INSERT INTO `user_page_permissions` VALUES (1,2,'admin',0,0,0,0),(2,3,'admin',1,0,0,0),(3,2,'index',1,0,0,0),(4,3,'index',1,0,0,0),(5,2,'nzsupportlist',1,1,0,1),(6,3,'nzsupportlist',1,1,0,1),(8,1,'admin',1,1,1,1),(9,1,'index',1,1,1,1),(10,1,'nzsupportlist',1,1,1,1);
/*!40000 ALTER TABLE `user_page_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_registry`
--

DROP TABLE IF EXISTS `user_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `oid` (`oid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_registry`
--

LOCK TABLES `user_registry` WRITE;
/*!40000 ALTER TABLE `user_registry` DISABLE KEYS */;
INSERT INTO `user_registry` VALUES (2,'50369','Jannatun Nayem','Jr. Support Engineer','IT','01521206960','2026-05-15 07:17:13'),(3,'50517','Ismail Prodhan','Asst. Support Engineer','IT','01521206960','2026-05-15 17:45:21');
/*!40000 ALTER TABLE `user_registry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_requests`
--

DROP TABLE IF EXISTS `user_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `support_person` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_requests`
--

LOCK TABLES `user_requests` WRITE;
/*!40000 ALTER TABLE `user_requests` DISABLE KEYS */;
INSERT INTO `user_requests` VALUES (1,'50517','Ismail Prodhan','Asst. Support Engineer','IT','01521206960','','approved','admin','2026-05-15 17:39:26');
/*!40000 ALTER TABLE `user_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `user_key` varchar(50) DEFAULT NULL,
  `can_view` tinyint(1) DEFAULT 1,
  `can_edit` tinyint(1) DEFAULT 1,
  `can_delete` tinyint(1) DEFAULT 1,
  `can_update` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_key` (`user_key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$avHnnd3jei9h/vHqPl98dO8rgxiBftYxDgQIluULxbStO8/xH1dbq','admin',NULL,1,1,1,1,'2026-05-14 16:33:46'),(2,'Bappy','\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user','bappy',1,1,0,1,'2026-05-14 16:33:46'),(3,'Nayem','\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user','nayem',1,1,0,1,'2026-05-14 16:33:46');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-16 21:18:59
