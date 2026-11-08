-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: srm_residence
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `flat_no` varchar(255) DEFAULT NULL,
  `area_name` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `address_type` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `lat` decimal(10,7) DEFAULT NULL,
  `long` decimal(10,7) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
INSERT INTO `applications` VALUES (1,'+91 7010663823','Contact','contact_phone',NULL,'2026-11-07 07:53:18','2026-11-07 07:55:45'),(2,'ronaldojagan46@gmail.com','Contact','contact_email',NULL,'2026-11-07 07:53:18','2026-11-07 07:55:45'),(3,'No.42 Ambethkar street, North sanjai gandhi nagar, Zamin Pallavaram Chennai 600043','Contact','contact_address',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45');
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_details`
--

DROP TABLE IF EXISTS `bank_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_number` varchar(255) NOT NULL,
  `ifsc_code` varchar(255) NOT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `holder_name` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_details_user_id_foreign` (`user_id`),
  CONSTRAINT `bank_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_details`
--

LOCK TABLES `bank_details` WRITE;
/*!40000 ALTER TABLE `bank_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (1,'SRM Fest Banner Updated','1794038523_test_banner.jpg',1,'2026-11-07 08:02:04','2026-11-07 08:02:04','2026-11-07 08:02:04');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_payments`
--

DROP TABLE IF EXISTS `bill_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(255) NOT NULL,
  `bill_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'Cash',
  `transaction_id` varchar(255) DEFAULT NULL,
  `receipt` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_payments_payment_number_unique` (`payment_number`),
  KEY `bill_payments_bill_id_foreign` (`bill_id`),
  KEY `bill_payments_tenant_id_foreign` (`tenant_id`),
  KEY `bill_payments_received_by_foreign` (`received_by`),
  CONSTRAINT `bill_payments_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bill_payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bill_payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_payments`
--

LOCK TABLES `bill_payments` WRITE;
/*!40000 ALTER TABLE `bill_payments` DISABLE KEYS */;
INSERT INTO `bill_payments` VALUES (1,'RCP-202611-0001',1,1,3000.00,'2026-11-05','UPI / GPay','UPI1234567890',NULL,'Advance partial payment',1,'2026-11-05 15:44:00','2026-11-05 17:10:14','2026-11-05 17:10:14'),(2,'RCP-202611-0002',1,1,2000.00,'2026-11-05','Cash',NULL,NULL,'Remaining balance cleared',1,'2026-11-05 15:44:01','2026-11-05 17:10:14','2026-11-05 17:10:14'),(3,'RCP-202611-0003',3,1,6300.00,'2026-11-05','Cash',NULL,NULL,NULL,1,'2026-11-05 17:23:43','2026-11-05 17:33:30','2026-11-05 17:33:30'),(4,'RCP-202611-0004',4,1,2000.00,'2026-11-05','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-05 17:26:26','2026-11-05 17:26:26','2026-11-05 17:26:26'),(5,'RCP-202611-0005',4,1,1000.00,'2026-11-05','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-05 17:26:26','2026-11-05 17:26:26','2026-11-05 17:26:26'),(6,'RCP-202611-0006',5,1,2000.00,'2026-11-05','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-05 17:28:30','2026-11-05 17:28:30','2026-11-05 17:28:30'),(7,'RCP-202611-0007',5,1,1000.00,'2026-11-05','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-05 17:28:30','2026-11-05 17:28:30','2026-11-05 17:28:30'),(8,'RCP-202611-0008',6,1,2000.00,'2026-11-05','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-05 17:31:56','2026-11-05 17:31:57','2026-11-05 17:31:57'),(9,'RCP-202611-0009',6,1,1000.00,'2026-11-05','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-05 17:31:57','2026-11-05 17:31:57','2026-11-05 17:31:57'),(10,'RCP-202611-0010',8,1,2000.00,'2026-11-05','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-05 17:39:16','2026-11-05 17:39:16','2026-11-05 17:39:16'),(11,'RCP-202611-0011',8,1,1000.00,'2026-11-05','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-05 17:39:16','2026-11-05 17:39:16','2026-11-05 17:39:16'),(12,'RCP-202611-0012',9,1,2000.00,'2026-11-05','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-05 17:39:42','2026-11-05 17:39:42','2026-11-05 17:39:42'),(13,'RCP-202611-0013',9,1,1000.00,'2026-11-05','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-05 17:39:42','2026-11-05 17:39:42','2026-11-05 17:39:42'),(14,'RCP-202611-0014',10,1,2000.00,'2026-11-05','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-05 17:40:09','2026-11-05 17:40:09','2026-11-05 17:40:09'),(15,'RCP-202611-0015',10,1,1000.00,'2026-11-05','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-05 17:40:09','2026-11-05 17:40:09','2026-11-05 17:40:09'),(16,'RCP-202611-0016',11,1,5500.00,'2026-11-05','Cash',NULL,NULL,NULL,1,'2026-11-05 17:41:16','2026-11-07 06:46:38','2026-11-07 06:46:38'),(17,'RCP-202611-0017',13,1,2000.00,'2026-11-05','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-05 17:55:20','2026-11-05 17:55:20','2026-11-05 17:55:20'),(18,'RCP-202611-0018',13,1,1000.00,'2026-11-05','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-05 17:55:20','2026-11-05 17:55:20','2026-11-05 17:55:20'),(19,'RCP-202611-0019',14,1,2000.00,'2026-11-05','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-05 18:06:46','2026-11-05 18:06:46','2026-11-05 18:06:46'),(20,'RCP-202611-0020',14,1,1000.00,'2026-11-05','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-05 18:06:46','2026-11-05 18:06:46','2026-11-05 18:06:46'),(21,'RCP-202611-0021',15,1,2000.00,'2026-11-06','UPI / GPay','UPI-TEST-998877',NULL,'Initial payment upon invoice creation.',1,'2026-11-06 13:51:53','2026-11-06 13:51:53','2026-11-06 13:51:53'),(22,'RCP-202611-0022',15,1,1000.00,'2026-11-06','Cash',NULL,NULL,'Second partial payment test',1,'2026-11-06 13:51:53','2026-11-06 13:51:53','2026-11-06 13:51:53'),(23,'RCP-202611-0023',16,2,5000.00,'2026-11-07','UPI','UPI1234567890',NULL,NULL,1,'2026-11-07 08:13:38','2026-11-07 08:44:42','2026-11-07 08:44:42'),(24,'RCP-202611-0024',19,4,3000.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 09:18:28','2026-11-07 09:18:39','2026-11-07 09:18:39'),(25,'RCP-202611-0025',23,7,4500.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 10:43:03','2026-11-07 10:43:03',NULL),(26,'RCP-202611-0026',25,8,6000.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 10:44:51','2026-11-07 10:44:51',NULL),(27,'RCP-202611-0027',16,2,13500.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 10:45:12','2026-11-07 10:45:12',NULL),(28,'RCP-202611-0028',17,1,5000.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 10:45:24','2026-11-07 10:45:53','2026-11-07 10:45:53'),(29,'RCP-202611-0029',17,1,10000.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 10:54:15','2026-11-07 10:54:15',NULL),(30,'RCP-202611-0030',18,3,5000.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 17:15:23','2026-11-07 17:15:37','2026-11-07 17:15:37'),(31,'RCP-202611-0031',30,17,5000.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 17:15:53','2026-11-07 17:16:32','2026-11-07 17:16:32'),(32,'RCP-202611-0032',30,17,5000.00,'2026-11-07','Cash',NULL,NULL,NULL,1,'2026-11-07 17:24:27','2026-11-07 17:24:38','2026-11-07 17:24:38');
/*!40000 ALTER TABLE `bill_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bills`
--

DROP TABLE IF EXISTS `bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bill_number` varchar(255) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `bill_type` varchar(255) NOT NULL DEFAULT 'Monthly Rent',
  `billing_month` varchar(255) NOT NULL,
  `bill_date` date NOT NULL,
  `due_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `eb_previous_reading` decimal(10,2) DEFAULT NULL,
  `eb_current_reading` decimal(10,2) DEFAULT NULL,
  `eb_consumed_units` decimal(10,2) DEFAULT NULL,
  `eb_unit_rate` decimal(8,2) DEFAULT NULL,
  `eb_amount` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('unpaid','partially_paid','paid','overdue') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items`)),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bills_bill_number_unique` (`bill_number`),
  KEY `bills_tenant_id_foreign` (`tenant_id`),
  KEY `bills_room_id_foreign` (`room_id`),
  KEY `bills_created_by_foreign` (`created_by`),
  KEY `bills_status_index` (`status`),
  CONSTRAINT `bills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bills_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bills_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bills`
--

LOCK TABLES `bills` WRITE;
/*!40000 ALTER TABLE `bills` DISABLE KEYS */;
INSERT INTO `bills` VALUES (1,'INV-202611-0001',1,2,'Monthly Rent','November 2026','2026-11-05','2026-11-10',5000.00,NULL,NULL,NULL,NULL,NULL,5000.00,0.00,'paid','Test rent bill',NULL,1,'2026-11-05 15:44:00','2026-11-05 17:10:14','2026-11-05 17:10:14'),(3,'INV-202611-0002',1,2,'Monthly Rent & Utilities','November 2026','2026-11-05','2026-11-10',6300.00,0.00,100.00,100.00,8.00,800.00,6300.00,0.00,'paid',NULL,'[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":800,\"details\":\"100 units @ \\u20b98.00\\/unit (Meter: 0 \\u2794 100)\"}]',1,'2026-11-05 17:23:16','2026-11-05 17:33:30','2026-11-05 17:33:30'),(4,'INV-202611-0003',1,2,'Monthly Rent & Utilities','December 2026','2026-11-05','2026-11-10',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-05 17:26:26','2026-11-05 17:26:26','2026-11-05 17:26:26'),(5,'INV-202611-0004',1,2,'Monthly Rent & Utilities','December 2026','2026-11-05','2026-11-10',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-05 17:28:30','2026-11-05 17:28:30','2026-11-05 17:28:30'),(6,'INV-202611-0005',1,2,'Monthly Rent & Utilities','December 2026','2026-11-05','2026-11-10',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-05 17:31:56','2026-11-05 17:31:57','2026-11-05 17:31:57'),(7,'INV-202611-0006',1,2,'Monthly Rent & Utilities','November 2026','2026-11-05','2026-11-10',5500.00,0.00,NULL,0.00,8.00,0.00,0.00,5500.00,'unpaid',NULL,'[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":0,\"details\":null}]',1,'2026-11-05 17:33:59','2026-11-05 17:41:02','2026-11-05 17:41:02'),(8,'INV-202611-0007',1,2,'Monthly Rent & Utilities','December 2026','2026-11-05','2026-11-10',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-05 17:39:16','2026-11-05 17:39:16','2026-11-05 17:39:16'),(9,'INV-202611-0008',1,2,'Monthly Rent & Utilities','December 2026','2026-11-05','2026-11-10',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-05 17:39:42','2026-11-05 17:39:42','2026-11-05 17:39:42'),(10,'INV-202611-0009',1,2,'Monthly Rent & Utilities','December 2026','2026-11-05','2026-11-10',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-05 17:40:09','2026-11-05 17:40:09','2026-11-05 17:40:09'),(11,'INV-202611-0010',1,2,'Monthly Rent & Utilities','November 2026','2026-11-05','2026-11-10',5500.00,0.00,NULL,0.00,8.00,0.00,5500.00,0.00,'paid',NULL,'[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":0,\"details\":null}]',1,'2026-11-05 17:41:11','2026-11-07 06:46:38','2026-11-07 06:46:38'),(12,'INV-202611-0011',1,2,'Monthly Rent','November 2026','2026-11-05','2026-11-06',5500.00,0.00,NULL,0.00,8.00,0.00,0.00,5500.00,'overdue',NULL,'[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":0,\"details\":null}]',1,'2026-11-05 17:41:35','2026-11-07 06:46:34','2026-11-07 06:46:34'),(13,'INV-202611-0012',1,2,'Monthly Rent & Utilities','December 2026','2026-11-05','2026-11-10',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-05 17:55:20','2026-11-05 17:55:20','2026-11-05 17:55:20'),(14,'INV-202611-0013',1,2,'Monthly Rent & Utilities','December 2026','2026-11-05','2026-11-10',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-05 18:06:46','2026-11-05 18:06:46','2026-11-05 18:06:46'),(15,'INV-202611-0014',1,2,'Monthly Rent & Utilities','December 2026','2026-11-06','2026-11-11',7000.00,0.00,125.00,125.00,8.00,1000.00,2000.00,5000.00,'partially_paid','Automated test invoice for full module verification','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]',1,'2026-11-06 13:51:53','2026-11-06 13:51:53','2026-11-06 13:51:53'),(16,'INV-202611-0015',2,3,'Monthly Rent','November 2026','2026-11-07','2026-11-10',13500.00,NULL,NULL,NULL,NULL,NULL,13500.00,0.00,'paid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":13500}]',1,'2026-11-07 07:43:02','2026-11-07 10:45:12',NULL),(17,'INV-202611-0016',1,2,'Monthly Rent','November 2026','2026-11-07','2026-11-11',10000.00,NULL,NULL,NULL,NULL,NULL,10000.00,0.00,'paid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]',1,'2026-11-07 07:43:02','2026-11-07 10:54:15',NULL),(18,'INV-202611-0017',3,4,'Monthly Rent','November 2026','2026-11-07','2026-11-12',5000.00,NULL,NULL,NULL,NULL,NULL,0.00,5000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]',1,'2026-11-07 07:43:02','2026-11-07 17:15:37',NULL),(19,'INV-202611-0018',4,5,'Monthly Rent','November 2026','2026-11-07','2026-11-12',6000.00,NULL,NULL,NULL,NULL,NULL,0.00,6000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]',1,'2026-11-07 07:43:02','2026-11-07 09:18:39',NULL),(20,'INV-202611-0019',5,6,'Monthly Rent','November 2026','2026-11-07','2026-11-12',17000.00,NULL,NULL,NULL,NULL,NULL,0.00,17000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":17000}]',1,'2026-11-07 07:43:02','2026-11-07 07:43:02',NULL),(21,'INV-202611-0020',14,10,'Monthly Rent','November 2026','2026-11-07','2026-11-12',4000.00,NULL,NULL,NULL,NULL,NULL,0.00,4000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":4000}]',1,'2026-11-07 07:43:02','2026-11-07 07:43:02',NULL),(22,'INV-202611-0021',6,11,'Monthly Rent','November 2026','2026-11-07','2026-11-12',5000.00,NULL,NULL,NULL,NULL,NULL,0.00,5000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]',1,'2026-11-07 07:43:02','2026-11-07 07:43:02',NULL),(23,'INV-202611-0022',7,12,'Monthly Rent','November 2026','2026-11-07','2026-11-12',4500.00,NULL,NULL,NULL,NULL,NULL,4500.00,0.00,'paid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":4500}]',1,'2026-11-07 07:43:02','2026-11-07 10:43:03',NULL),(24,'INV-202611-0023',15,18,'Monthly Rent','November 2026','2026-11-07','2026-11-12',5000.00,NULL,NULL,NULL,NULL,NULL,0.00,5000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]',1,'2026-11-07 07:43:02','2026-11-07 07:43:02',NULL),(25,'INV-202611-0024',8,19,'Monthly Rent','November 2026','2026-11-07','2026-11-12',6000.00,NULL,NULL,NULL,NULL,NULL,6000.00,0.00,'paid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]',1,'2026-11-07 07:43:02','2026-11-07 10:44:51',NULL),(26,'INV-202611-0025',9,20,'Monthly Rent','November 2026','2026-11-07','2026-11-12',6000.00,NULL,NULL,NULL,NULL,NULL,0.00,6000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]',1,'2026-11-07 07:43:02','2026-11-07 07:43:02',NULL),(27,'INV-202611-0026',10,21,'Monthly Rent','November 2026','2026-11-07','2026-11-12',6000.00,NULL,NULL,NULL,NULL,NULL,0.00,6000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]',1,'2026-11-07 07:43:02','2026-11-07 07:43:02',NULL),(28,'INV-202611-0027',13,26,'Monthly Rent','November 2026','2026-11-07','2026-11-12',6000.00,NULL,NULL,NULL,NULL,NULL,0.00,6000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]',1,'2026-11-07 07:43:02','2026-11-07 07:43:02',NULL),(29,'INV-202611-0028',16,27,'Monthly Rent','November 2026','2026-11-07','2026-11-12',6000.00,NULL,NULL,NULL,NULL,NULL,0.00,6000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]',1,'2026-11-07 09:46:54','2026-11-07 09:46:54',NULL),(30,'INV-202611-0029',17,28,'Monthly Rent','November 2026','2026-11-07','2026-11-12',5000.00,NULL,NULL,NULL,NULL,NULL,0.00,5000.00,'unpaid','Automated monthly rent invoice for November 2026','[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]',1,'2026-11-07 10:53:21','2026-11-07 17:24:38',NULL);
/*!40000 ALTER TABLE `bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cancellation_reasons`
--

DROP TABLE IF EXISTS `cancellation_reasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cancellation_reasons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reason` varchar(255) NOT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cancellation_reasons`
--

LOCK TABLES `cancellation_reasons` WRITE;
/*!40000 ALTER TABLE `cancellation_reasons` DISABLE KEYS */;
/*!40000 ALTER TABLE `cancellation_reasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `delivery_boy_id` bigint(20) unsigned DEFAULT NULL,
  `delivery_address_id` bigint(20) unsigned NOT NULL,
  `delivery_date` date NOT NULL,
  `status` enum('pending','assigned','out_for_delivery','delivered','skipped','on_leave','holiday') NOT NULL DEFAULT 'pending',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `delivery_note` varchar(255) DEFAULT NULL,
  `skip_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deliveries_delivery_address_id_foreign` (`delivery_address_id`),
  KEY `deliveries_delivery_boy_id_delivery_date_index` (`delivery_boy_id`,`delivery_date`),
  KEY `deliveries_delivery_date_status_index` (`delivery_date`,`status`),
  KEY `deliveries_user_id_delivery_date_index` (`user_id`,`delivery_date`),
  CONSTRAINT `deliveries_delivery_address_id_foreign` FOREIGN KEY (`delivery_address_id`) REFERENCES `delivery_addresses` (`id`),
  CONSTRAINT `deliveries_delivery_boy_id_foreign` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`id`),
  CONSTRAINT `deliveries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_addresses`
--

DROP TABLE IF EXISTS `delivery_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT 'Home',
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `added_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_addresses_user_id_is_default_index` (`user_id`,`is_default`),
  KEY `delivery_addresses_user_id_is_active_index` (`user_id`,`is_active`),
  CONSTRAINT `delivery_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_addresses`
--

LOCK TABLES `delivery_addresses` WRITE;
/*!40000 ALTER TABLE `delivery_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_assignments`
--

DROP TABLE IF EXISTS `delivery_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_boy_id` bigint(20) unsigned NOT NULL,
  `assignment_date` date NOT NULL,
  `total_assigned` int(11) NOT NULL DEFAULT 0,
  `total_delivered` int(11) NOT NULL DEFAULT 0,
  `total_skipped` int(11) NOT NULL DEFAULT 0,
  `total_on_leave` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_assignments_delivery_boy_id_assignment_date_unique` (`delivery_boy_id`,`assignment_date`),
  KEY `delivery_assignments_assignment_date_index` (`assignment_date`),
  CONSTRAINT `delivery_assignments_delivery_boy_id_foreign` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_assignments`
--

LOCK TABLES `delivery_assignments` WRITE;
/*!40000 ALTER TABLE `delivery_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_mans`
--

DROP TABLE IF EXISTS `delivery_mans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_mans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_mans`
--

LOCK TABLES `delivery_mans` WRITE;
/*!40000 ALTER TABLE `delivery_mans` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_mans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_points`
--

DROP TABLE IF EXISTS `delivery_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location` text DEFAULT NULL,
  `sub_location` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_points`
--

LOCK TABLES `delivery_points` WRITE;
/*!40000 ALTER TABLE `delivery_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_type` varchar(255) NOT NULL,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `expense_month` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `paid_to` varchar(255) DEFAULT NULL,
  `receipt` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items`)),
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_room_id_foreign` (`room_id`),
  KEY `expenses_created_by_foreign` (`created_by`),
  KEY `expenses_expense_type_index` (`expense_type`),
  KEY `expenses_expense_month_index` (`expense_month`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,'personal',NULL,'Groceries & Food','Test Monthly Supplies',1550.00,'2026-11-07','November 2026','UPI',NULL,NULL,'Total grocery test','[{\"title\":\"Vegetables & Fruits\",\"amount\":450,\"notes\":\"Weekly market\"},{\"title\":\"Cooking Oil & Spices\",\"amount\":600,\"notes\":\"Store purchase\"},{\"title\":\"Dairy & Milk\",\"amount\":500,\"notes\":\"Morning delivery\"}]',1,NULL,'2026-11-07 09:42:36','2026-11-07 09:42:36','2026-11-07 09:42:36'),(2,'personal',NULL,'Personal','Weekly Groceries Test',900.00,'2026-11-07','November 2026',NULL,'Reliance Smart Point',NULL,'Weekend grocery shopping','[{\"title\":\"Basmati Rice\",\"quantity\":2,\"per_rate\":120,\"amount\":240,\"notes\":\"5kg bags\"},{\"title\":\"Sunflower Oil\",\"quantity\":3,\"per_rate\":140,\"amount\":420,\"notes\":\"1L pouches\"},{\"title\":\"Toor Dal\",\"quantity\":1.5,\"per_rate\":160,\"amount\":240,\"notes\":\"Loose dal\"}]',1,NULL,'2026-11-07 10:03:27','2026-11-07 10:05:04','2026-11-07 10:05:04'),(3,'personal',NULL,'Personal','Weekly Groceries Test',900.00,'2026-11-07','November 2026',NULL,'Reliance Smart Point',NULL,'Weekend grocery shopping','[{\"title\":\"Basmati Rice\",\"quantity\":2,\"per_rate\":120,\"amount\":240,\"notes\":\"5kg bags\"},{\"title\":\"Sunflower Oil\",\"quantity\":3,\"per_rate\":140,\"amount\":420,\"notes\":\"1L pouches\"},{\"title\":\"Toor Dal\",\"quantity\":1.5,\"per_rate\":160,\"amount\":240,\"notes\":\"Loose dal\"}]',1,NULL,'2026-11-07 10:03:40','2026-11-07 10:03:40','2026-11-07 10:03:40'),(4,'personal',NULL,'Personal','Updated Personal Expense',1000.00,'2026-11-08','November 2026',NULL,NULL,NULL,NULL,'[{\"title\":\"A4 Paper Bundle\",\"quantity\":4,\"per_rate\":250,\"amount\":1000,\"notes\":\"75 GSM\"}]',1,NULL,'2026-11-07 10:03:54','2026-11-07 10:03:54','2026-11-07 10:03:54'),(5,'personal',NULL,'Personal','GROCERY EXPENSES',15869.00,'2026-11-07','November 2026',NULL,NULL,NULL,NULL,'[{\"title\":\"Rice\",\"quantity\":1,\"per_rate\":2150,\"amount\":2150,\"notes\":\"\"},{\"title\":\"gold vinner\",\"quantity\":5,\"per_rate\":200,\"amount\":1000,\"notes\":\"\"},{\"title\":\"Milk\",\"quantity\":1,\"per_rate\":3000,\"amount\":3000,\"notes\":\"\"},{\"title\":\"Egg\",\"quantity\":217,\"per_rate\":7,\"amount\":1519,\"notes\":\"\"},{\"title\":\"Vegitables\",\"quantity\":1,\"per_rate\":200,\"amount\":200,\"notes\":\"\"},{\"title\":\"gas\",\"quantity\":1,\"per_rate\":1000,\"amount\":1000,\"notes\":\"\"},{\"title\":\"other items\",\"quantity\":1,\"per_rate\":7000,\"amount\":7000,\"notes\":\"\"}]',1,1,'2026-11-07 10:11:37','2026-11-07 10:11:37',NULL),(6,'personal',NULL,'Personal','Loans and creditors',40870.00,'2026-11-07','November 2026',NULL,NULL,NULL,NULL,'[{\"title\":\"narayanan cable\",\"quantity\":1,\"per_rate\":5000,\"amount\":5000,\"notes\":\"\"},{\"title\":\"sunderajan nagalkeni\",\"quantity\":1,\"per_rate\":3000,\"amount\":3000,\"notes\":\"\"},{\"title\":\"tambaram dharmalingam\",\"quantity\":1,\"per_rate\":2000,\"amount\":2000,\"notes\":\"\"},{\"title\":\"Sathish auto\",\"quantity\":1,\"per_rate\":5000,\"amount\":5000,\"notes\":\"\"},{\"title\":\"Gowri\",\"quantity\":1,\"per_rate\":5600,\"amount\":5600,\"notes\":\"\"},{\"title\":\"samsung tv  Emi\",\"quantity\":1,\"per_rate\":3300,\"amount\":3300,\"notes\":\"\"},{\"title\":\"Ragavan Loan\",\"quantity\":1,\"per_rate\":7400,\"amount\":7400,\"notes\":\"\"},{\"title\":\"pragathi loan\",\"quantity\":1,\"per_rate\":2720,\"amount\":2720,\"notes\":\"\"},{\"title\":\"gramam vidial loan\",\"quantity\":5,\"per_rate\":890,\"amount\":4450,\"notes\":\"\"},{\"title\":\"grow win loan\",\"quantity\":1,\"per_rate\":2400,\"amount\":2400,\"notes\":\"\"}]',1,1,'2026-11-07 10:30:15','2026-11-07 10:30:15',NULL),(7,'personal',NULL,'Personal','Recharge and phone bills',13350.00,'2026-11-06','November 2026',NULL,NULL,NULL,NULL,'[{\"title\":\"jagan\",\"quantity\":1,\"per_rate\":350,\"amount\":350,\"notes\":\"\"},{\"title\":\"ragavan\",\"quantity\":1,\"per_rate\":300,\"amount\":300,\"notes\":\"\"},{\"title\":\"saritha\",\"quantity\":1,\"per_rate\":300,\"amount\":300,\"notes\":\"\"},{\"title\":\"act fibernet\",\"quantity\":1,\"per_rate\":2000,\"amount\":2000,\"notes\":\"\"},{\"title\":\"news paper\",\"quantity\":1,\"per_rate\":400,\"amount\":400,\"notes\":\"\"},{\"title\":\"Eb\",\"quantity\":1,\"per_rate\":10000,\"amount\":10000,\"notes\":\"\"}]',1,1,'2026-11-07 10:32:18','2026-11-07 10:35:42',NULL),(8,'personal',NULL,'Personal','Others Expenses',14710.00,'2026-11-05','November 2026',NULL,NULL,NULL,NULL,'[{\"title\":\"Vignesh marriage Expenses\",\"quantity\":1,\"per_rate\":8000,\"amount\":8000,\"notes\":\"\"},{\"title\":\"Diwali sweets\",\"quantity\":1,\"per_rate\":640,\"amount\":640,\"notes\":\"\"},{\"title\":\"flower\",\"quantity\":1,\"per_rate\":200,\"amount\":200,\"notes\":\"\"},{\"title\":\"pooja items\",\"quantity\":1,\"per_rate\":120,\"amount\":120,\"notes\":\"\"},{\"title\":\"Advance return room no 2\",\"quantity\":1,\"per_rate\":5750,\"amount\":5750,\"notes\":\"\"}]',1,1,'2026-11-07 10:34:32','2026-11-07 12:37:45',NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `review` longtext DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feedback_user_id_foreign` (`user_id`),
  KEY `feedback_order_id_foreign` (`order_id`),
  CONSTRAINT `feedback_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `feedback_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holiday_settings`
--

DROP TABLE IF EXISTS `holiday_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('weekly','custom') NOT NULL DEFAULT 'weekly',
  `day_of_week` tinyint(4) DEFAULT NULL,
  `holiday_date` date DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_protected` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `holiday_settings_type_is_active_index` (`type`,`is_active`),
  KEY `holiday_settings_holiday_date_index` (`holiday_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holiday_settings`
--

LOCK TABLES `holiday_settings` WRITE;
/*!40000 ALTER TABLE `holiday_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `holiday_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaves`
--

DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leaves` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `leave_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','cancelled') NOT NULL DEFAULT 'approved',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leaves_subscription_id_leave_date_unique` (`subscription_id`,`leave_date`),
  KEY `leaves_approved_by_foreign` (`approved_by`),
  KEY `leaves_user_id_status_index` (`user_id`,`status`),
  KEY `leaves_leave_date_index` (`leave_date`),
  CONSTRAINT `leaves_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `leaves_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`),
  CONSTRAINT `leaves_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaves`
--

LOCK TABLES `leaves` WRITE;
/*!40000 ALTER TABLE `leaves` DISABLE KEYS */;
/*!40000 ALTER TABLE `leaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2019_05_11_000000_create_otps_table',1),(5,'2025_05_19_071846_create_bank_details_table',1),(6,'2025_05_19_082731_create_delivery_addresses_table',1),(7,'2025_05_19_082750_create_precheckouts_table',1),(8,'2025_05_19_082835_create_orders_table',1),(9,'2025_05_19_083113_create_addresses_table',1),(10,'2025_05_27_054402_create_order_items_table',1),(11,'2025_05_31_013125_create_feedback_table',1),(12,'2025_06_03_121505_create_temp_entries_table',1),(13,'2025_07_04_112222_create_saved_filters_table',1),(14,'2025_07_18_180721_create_payouts_table',1),(15,'2025_07_23_092943_create_order_status_histories_table',1),(16,'2025_07_30_092218_create_cancellation_reasons_table',1),(17,'2025_12_06_054453_create_personal_access_tokens_table',1),(18,'2025_12_06_060543_create_activity_log_table',1),(19,'2025_12_06_060544_add_event_column_to_activity_log_table',1),(20,'2025_12_06_060545_add_batch_uuid_column_to_activity_log_table',1),(21,'2025_12_17_095557_create_push_notifications_table',1),(22,'2025_12_17_160648_create_notifications_table',1),(23,'2026_02_27_152625_create_deliveries_table',1),(24,'2026_02_27_152717_create_delivery_assignments_table',1),(25,'2026_02_27_152909_create_settings_table',1),(26,'2026_02_27_153333_create_payments_table',1),(27,'2026_02_27_170915_create_packages_table',1),(28,'2026_02_27_171300_create_delivery_points_table',1),(29,'2026_02_27_171648_create_delivery_mans_table',1),(30,'2026_02_27_172735_create_missing_bottles_table',1),(31,'2026_03_02_095007_create_holiday_settings_table',1),(32,'2026_03_02_095110_create_subscriptions_table',1),(33,'2026_03_02_095153_create_subscriptions_billings_table',1),(34,'2026_03_02_095430_create_leaves_table',1),(35,'2026_03_04_130957_add_delivery_point_id_to_users_table',1),(36,'2026_03_04_170047_add_delivery_man_id_to_orders_table',1),(37,'2026_03_04_175445_add_order_id_to_payments_table',1),(38,'2026_03_10_154512_add_state_country',1),(39,'2026_03_10_162149_banner',1),(40,'2026_03_10_163335_application',1),(41,'2026_11_04_224637_create_tenants_table',2),(42,'2026_11_04_230053_create_rooms_table',3),(43,'2026_11_04_230852_add_room_id_to_tenants_table',4),(44,'2026_11_04_233002_create_expenses_table',5),(45,'2026_11_05_210655_create_bills_table',6),(46,'2026_11_05_210711_create_bill_payments_table',6),(47,'2026_11_05_214424_add_charges_to_rooms_and_tenants_table',7),(48,'2026_11_05_215841_add_eb_unit_rate_to_rooms_and_tenants_table',8),(49,'2026_11_05_222000_add_eb_readings_and_items_to_bills_and_tenants',9),(50,'2026_11_07_094900_add_is_self_to_rooms_table',10),(51,'2026_11_07_130000_create_tenant_rooms_table',11),(52,'2026_11_07_150500_add_expense_month_and_items_to_expenses_table',12),(53,'2026_11_07_152855_make_category_and_payment_method_nullable_in_expenses_table',13),(54,'2026_11_07_162501_sync_tenants_rent_from_rooms_table',14);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `missing_bottles`
--

DROP TABLE IF EXISTS `missing_bottles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missing_bottles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) DEFAULT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `delivery_man_id` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missing_bottles`
--

LOCK TABLES `missing_bottles` WRITE;
/*!40000 ALTER TABLE `missing_bottles` DISABLE KEYS */;
/*!40000 ALTER TABLE `missing_bottles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `type` enum('delivery','leave','payment','billing','general') NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  KEY `notifications_user_id_type_index` (`user_id`,`type`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `replacement_order_id` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `quantity_unit` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `cancellation_reason` text DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `refund_status` varchar(50) DEFAULT 'pending',
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_id` varchar(100) DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `return_image` varchar(255) DEFAULT NULL,
  `return_requested_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `courier_name` varchar(100) DEFAULT NULL,
  `shipment_id` varchar(255) DEFAULT NULL,
  `return_verified_by_admin` tinyint(1) DEFAULT NULL,
  `admin_verified_at` timestamp NULL DEFAULT NULL,
  `admin_verification_notes` text DEFAULT NULL,
  `shop_verified` tinyint(1) DEFAULT NULL,
  `shop_verified_at` timestamp NULL DEFAULT NULL,
  `shop_verification_notes` text DEFAULT NULL,
  `product_condition` varchar(50) DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `replace_reason` text DEFAULT NULL,
  `replace_image` varchar(255) DEFAULT NULL,
  `replace_requested_at` timestamp NULL DEFAULT NULL,
  `replace_verified_by_admin` tinyint(1) DEFAULT NULL,
  `replace_admin_verified_at` timestamp NULL DEFAULT NULL,
  `replace_admin_verification_notes` text DEFAULT NULL,
  `original_order_item_id` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_user_id_foreign` (`user_id`),
  KEY `order_items_cancelled_by_foreign` (`cancelled_by`),
  KEY `order_items_verified_by_foreign` (`verified_by`),
  KEY `order_items_status_index` (`status`),
  KEY `order_items_refund_status_index` (`refund_status`),
  KEY `order_items_cancelled_at_index` (`cancelled_at`),
  KEY `order_items_shipment_id_index` (`shipment_id`),
  CONSTRAINT `order_items_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_histories`
--

DROP TABLE IF EXISTS `order_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_histories_order_id_foreign` (`order_id`),
  CONSTRAINT `order_status_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_histories`
--

LOCK TABLES `order_status_histories` WRITE;
/*!40000 ALTER TABLE `order_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `delivery_address_id` bigint(20) unsigned NOT NULL,
  `delivery_man_id` varchar(255) DEFAULT NULL,
  `precheckout_id` bigint(20) unsigned DEFAULT NULL,
  `order_no` varchar(255) NOT NULL,
  `order_type` varchar(255) DEFAULT NULL,
  `pay_type` varchar(255) DEFAULT 'Online',
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `is_replacement_order` tinyint(1) NOT NULL DEFAULT 0,
  `original_order_id` bigint(20) unsigned DEFAULT NULL,
  `original_order_item_id` bigint(20) unsigned DEFAULT NULL,
  `replacement_notes` text DEFAULT NULL,
  `has_shipments` tinyint(1) NOT NULL DEFAULT 0,
  `all_items_delivered` tinyint(1) NOT NULL DEFAULT 0,
  `delivery_completed_at` timestamp NULL DEFAULT NULL,
  `shipments_count` int(11) NOT NULL DEFAULT 0,
  `delivered_shipments_count` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL,
  `completion_code` varchar(255) DEFAULT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_no_unique` (`order_no`),
  UNIQUE KEY `orders_invoice_no_unique` (`invoice_no`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_delivery_address_id_foreign` (`delivery_address_id`),
  KEY `orders_precheckout_id_foreign` (`precheckout_id`),
  KEY `orders_has_shipments_index` (`has_shipments`),
  KEY `orders_all_items_delivered_index` (`all_items_delivered`),
  KEY `orders_delivery_completed_at_index` (`delivery_completed_at`),
  KEY `orders_is_replacement_order_index` (`is_replacement_order`),
  CONSTRAINT `orders_delivery_address_id_foreign` FOREIGN KEY (`delivery_address_id`) REFERENCES `delivery_addresses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_precheckout_id_foreign` FOREIGN KEY (`precheckout_id`) REFERENCES `precheckouts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otps`
--

DROP TABLE IF EXISTS `otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `validity` int(11) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otps_id_index` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otps`
--

LOCK TABLES `otps` WRITE;
/*!40000 ALTER TABLE `otps` DISABLE KEYS */;
/*!40000 ALTER TABLE `otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `days` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','upi','card','online') NOT NULL DEFAULT 'cash',
  `transaction_id` varchar(255) DEFAULT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'success',
  `remarks` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_recorded_by_foreign` (`recorded_by`),
  KEY `payments_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payouts`
--

DROP TABLE IF EXISTS `payouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('generated','processing','paid','failed') NOT NULL DEFAULT 'generated',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payouts`
--

LOCK TABLES `payouts` WRITE;
/*!40000 ALTER TABLE `payouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `payouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `precheckouts`
--

DROP TABLE IF EXISTS `precheckouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `precheckouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `delivery_address_id` bigint(20) unsigned DEFAULT NULL,
  `preckout_type` varchar(255) DEFAULT 'Normal',
  `original_sub_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sub_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `original_delivery_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `platform_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `coupon_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_order_placed` tinyint(1) NOT NULL DEFAULT 0,
  `added_carts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`added_carts`)),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `precheckouts_user_id_foreign` (`user_id`),
  KEY `precheckouts_delivery_address_id_foreign` (`delivery_address_id`),
  CONSTRAINT `precheckouts_delivery_address_id_foreign` FOREIGN KEY (`delivery_address_id`) REFERENCES `delivery_addresses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `precheckouts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `precheckouts`
--

LOCK TABLES `precheckouts` WRITE;
/*!40000 ALTER TABLE `precheckouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `precheckouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `push_notifications`
--

DROP TABLE IF EXISTS `push_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `push_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `push_notifications`
--

LOCK TABLES `push_notifications` WRITE;
/*!40000 ALTER TABLE `push_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_number` varchar(255) NOT NULL,
  `floor` varchar(255) NOT NULL DEFAULT '1st Floor',
  `room_type` varchar(255) NOT NULL DEFAULT 'Single Deluxe',
  `capacity` int(11) NOT NULL DEFAULT 1,
  `monthly_rent` decimal(10,2) NOT NULL DEFAULT 8000.00,
  `eb_unit_rate` decimal(8,2) NOT NULL DEFAULT 10.00,
  `maintenance_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `security_deposit` decimal(10,2) NOT NULL DEFAULT 10000.00,
  `occupancy_status` enum('available','occupied','maintenance','self') NOT NULL DEFAULT 'available',
  `is_self` tinyint(1) NOT NULL DEFAULT 0,
  `amenities` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_room_number_unique` (`room_number`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,'101-A','1st Floor','Executive Suite',2,14000.00,10.00,0.00,0.00,18000.00,'available',0,'AC, Wi-Fi, Balcony, Smart TV',1,'2026-11-04 17:47:04','2026-11-04 17:47:04','2026-11-04 17:47:04'),(2,'2','Ground Floor','Single Deluxe',8,10000.00,0.00,0.00,0.00,5000.00,'occupied',0,NULL,1,'2026-11-04 17:53:03','2026-11-07 10:46:45',NULL),(3,'1','Ground Floor','Single Deluxe',6,6500.00,0.00,0.00,0.00,20000.00,'occupied',0,NULL,1,'2026-11-04 17:56:54','2026-11-07 07:20:53',NULL),(4,'3','Ground Floor','Single Deluxe',1,5000.00,10.00,0.00,250.00,10000.00,'occupied',0,NULL,1,'2026-11-05 18:02:05','2026-11-07 07:35:27',NULL),(5,'4','Ground Floor','Single Deluxe',7,6000.00,0.00,0.00,0.00,15000.00,'occupied',0,NULL,1,'2026-11-06 13:34:54','2026-11-07 05:52:21',NULL),(6,'5','Ground Floor','Single Deluxe',7,8500.00,0.00,0.00,0.00,12500.00,'occupied',0,NULL,1,'2026-11-06 13:40:31','2026-11-07 07:21:16',NULL),(9,'6','Ground Floor','Single Deluxe',8,7000.00,10.00,0.00,0.00,20000.00,'occupied',0,NULL,1,'2026-11-06 18:35:21','2026-11-07 09:28:40',NULL),(10,'7','Ground Floor','Single Deluxe',2,4000.00,0.00,0.00,0.00,10000.00,'occupied',0,NULL,1,'2026-11-07 04:07:33','2026-11-07 07:27:47',NULL),(11,'8','1st Floor','Single Deluxe',1,0.00,0.00,0.00,0.00,0.00,'self',1,NULL,1,'2026-11-07 04:08:26','2026-11-07 11:33:29',NULL),(12,'9','Ground Floor','Single Deluxe',4,4500.00,10.00,0.00,0.00,10000.00,'occupied',0,NULL,1,'2026-11-07 04:08:48','2026-11-07 06:10:56',NULL),(13,'10','Ground Floor','Single Deluxe',8,5000.00,10.00,0.00,0.00,10000.00,'occupied',0,NULL,1,'2026-11-07 04:09:30','2026-11-07 11:07:01',NULL),(14,'11','Ground Floor','Single Deluxe',7,8500.00,10.00,0.00,0.00,12500.00,'occupied',0,NULL,1,'2026-11-07 04:10:08','2026-11-07 07:21:16',NULL),(17,'12','Ground Floor','Single Deluxe',4,0.00,0.00,0.00,0.00,0.00,'self',1,NULL,1,'2026-11-07 04:29:44','2026-11-07 04:29:44',NULL),(18,'13','Ground Floor','Single Deluxe',2,5000.00,0.00,0.00,0.00,20000.00,'occupied',0,NULL,1,'2026-11-07 04:43:47','2026-11-07 07:29:21',NULL),(19,'14','Ground Floor','Single Deluxe',5,6000.00,10.00,0.00,0.00,15000.00,'occupied',0,NULL,1,'2026-11-07 04:44:20','2026-11-07 10:44:27',NULL),(20,'15','1st Floor','Single Deluxe',8,6000.00,10.00,0.00,0.00,10000.00,'occupied',0,NULL,1,'2026-11-07 04:44:55','2026-11-07 06:14:40',NULL),(21,'16','1st Floor','Single Deluxe',6,6000.00,10.00,0.00,0.00,10000.00,'occupied',0,NULL,1,'2026-11-07 04:45:22','2026-11-07 06:17:18',NULL),(22,'17','1st Floor','Single Deluxe',6,6000.00,10.00,0.00,0.00,10000.00,'available',0,NULL,1,'2026-11-07 04:45:36','2026-11-07 04:45:36',NULL),(23,'18','Ground Floor','Single Deluxe',1,0.00,0.00,0.00,0.00,0.00,'self',1,NULL,1,'2026-11-07 04:45:58','2026-11-07 04:45:58',NULL),(24,'19','Ground Floor','Single Deluxe',1,0.00,0.00,0.00,0.00,0.00,'self',1,NULL,1,'2026-11-07 04:46:14','2026-11-07 04:46:14',NULL),(25,'20','Ground Floor','Single Deluxe',1,0.00,0.00,0.00,0.00,0.00,'self',1,NULL,1,'2026-11-07 04:46:25','2026-11-07 04:46:25',NULL),(26,'21','1st Floor','Single Deluxe',6,6000.00,10.00,0.00,0.00,10000.00,'occupied',0,NULL,1,'2026-11-07 04:46:49','2026-11-07 07:23:56',NULL),(27,'22','1st Floor','Single Deluxe',1,0.00,0.00,0.00,0.00,0.00,'self',1,NULL,1,'2026-11-07 04:46:59','2026-11-07 11:34:16',NULL),(28,'23','1st Floor','Single Deluxe',4,5000.00,10.00,0.00,0.00,10000.00,'occupied',0,NULL,1,'2026-11-07 04:47:21','2026-11-07 10:52:12',NULL);
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_filters`
--

DROP TABLE IF EXISTS `saved_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saved_filters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `page` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `table_type` varchar(255) NOT NULL,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`filters`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saved_filters_user_id_foreign` (`user_id`),
  CONSTRAINT `saved_filters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_filters`
--

LOCK TABLES `saved_filters` WRITE;
/*!40000 ALTER TABLE `saved_filters` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('0kJQewuXSMwqFFzVyCyfeo3y7jdz7DVCYetuv2tV',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUk15R1Rvb3A4UWd3akNPQkJiOEZUNXdLd3lwYnpOWU1ZRVpIVHZVOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1794075071),('2rdyJY2c85ngIcn8JAvqHWdsI2OoHuOto1dByLyp',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNHRwSllLNGFjOWdGSEdHaDZPQjhtM3dIdVB0UFlKWWdoU01WQUVyNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794072453),('bE6KrMLDza9P6m0MHKoEkMnb0BFLnKfrh7HktTrJ',1,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWUNJNFQ3ZjRBbE5ycXZGdE1ocDhGUGtRTFR1TU9UMXRtNFQ0WG9ITCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL3RlbmFudHMiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1794075860),('d2bjXBCC1nDVHWaWTfegA1Zow8VRv14EC1ffpJBO',1,'2406:7400:bb:b115:2285:f177:f3c5:147a','Mozilla/5.0 (Linux; Android 16; CPH2491 Build/BP2A.250605.015; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/156.0.8078.50 Mobile Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoic3BLdWJnU0prd0NkR2p2THFHZHBRR3ZZbHJod2NnRGNFYXNPbzNyOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzE6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2FkbWluL3NldHRpbmdzIjtzOjU6InJvdXRlIjtzOjE0OiJzZXR0aW5ncy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1794075442),('DnZiOiXfF9IxkUeOctXCC981SooqCaNefZSruX2g',NULL,'2406:7400:bb:b115:c5f3:c7f3:c38f:cb1e','WhatsApp/2.23.20.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRHVDa05rMVoxZjk4V2J0SzVuMU5pbEVxRWRadm43M0JoWEFNcFdxRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1794072792),('fT6XAQU37AEb47pE8qpOzuRtALciTU27y7a1LXKa',1,'183.82.206.148','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Mobile Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoic1E0NWRJbHE3VElUUlBicmxuRXNuQlc4UHpGSTd0YXJNTG5uaEFYUyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo3MjoiaHR0cHM6Ly9hbnN3ZXJzLW1haW5seS1mdXR1cmVzLWZvbGxvd3MudHJ5Y2xvdWRmbGFyZS5jb20vYWRtaW4vZGFzaGJvYXJkIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2FkbWluL2Rhc2hib2FyZCI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1794074773),('G7wm9CsKqB5mqQ7OYgOxgpF3oLlFve4CNfVFY1dQ',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY3pCQlphaHRFVG9zYXlzOUtXSFoxcU9QdEdzSGluWHEya1JSaU9NRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794074103),('hz6Vnwc4q56dFSRWX9w1rVnDpXmiwsnKIudFJbz8',1,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTHNiZ0tuazdaa0VWZkIwMmM5M2dzcVI2ZGpQaU45aE1iMUxmbGViRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njg6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2FkbWluL3VzZXJzIjtzOjU6InJvdXRlIjtzOjExOiJ1c2Vycy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1794074836),('iBgs0sUuA7PzSpin9lXc5TX0bpcf77yJQo5yl6o8',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoid2c1YlhKN3lISjVHUmJwWXcwd001RkZaSFdwQUtxc2k0MUJqWGF2NiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794075043),('jqrMmNwgtR7lPZTghhVYAs7r9B9NRu8ZxFFO6kE6',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMzR6aG14MGVjYUxnSmJDdE14aTNXeEdlZ0NqdFJQUU8wTVUzY0tGbiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794075911),('jwjtWvKWYVrggN6JN6WXe8Tw4DWG3E01kJM1AWlG',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUzVFQWN5M0o0WEVsbEY0OHFubVZGZlQ3TjRpZDd4RWg5MzJlTVR4MSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1794073052),('L5SnOkPrRF3JrCIUq0AIQMFwXId2EhvvQNefAX0g',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM1ZLSGZaZ0R5OTlaM29ZV0MyUW84TXFDcjV5TlFvYVY1cG5oYUxMVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1794072642),('lhKM3xLAD03OiPGglxG7Ny9jCU5LpGPaER65biXt',1,'2406:7400:bb:b115:2285:f177:f3c5:147a','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Mobile Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVHBzRjhveFJjSTNuQUQ1c2x4Wjl6eDQzSlI5bTZ3ck43bG5xVVR4SCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo3MjoiaHR0cHM6Ly9hbnN3ZXJzLW1haW5seS1mdXR1cmVzLWZvbGxvd3MudHJ5Y2xvdWRmbGFyZS5jb20vYWRtaW4vZGFzaGJvYXJkIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2FkbWluL2Rhc2hib2FyZCI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1794075566),('oMpyggLO9AdNzPXIKauf3iljXoExJqjHkHotfL8N',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMU9GakZvNlVHS1JEenQ5OTFTajlaakNXc1p0c2JwZm0zd1Q4UHRheCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794072459),('QcnyHaQxLlydHodUojzJ4XyryysaRO2A52jIroTP',NULL,'2406:7400:bb:b115:c5f3:c7f3:c38f:cb1e','Mozilla/5.0 (Linux; Android 16; CPH2491 Build/BP2A.250605.015; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/156.0.8078.50 Mobile Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZlFPSGtUVDlYYzc5cUJRVVpKcEI0dDlaZXZobVgzOVJkMTZvanl6SCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1794074403),('qGI4rVbj4DSDUVtNz4MnWvwhvV3yRXbZjIgQcD4F',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVRmWEh5QVRWVVdWZE1sN0h6b21YNDZ2NDg2a3NNUU93YlBXbDJyciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzE6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL3Bhc3N3b3JkL3Jlc2V0IjtzOjU6InJvdXRlIjtzOjE2OiJwYXNzd29yZC5yZXF1ZXN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794075044),('saIl4oY5dSTd8zgdsTNYrSJmf70oPW275RwghYJ8',1,'2406:7400:bb:b115:2285:f177:f3c5:147a','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Mobile Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoidkplVjFpUHZxYUJmcFZhbXdIazc2ZG9hTjBjTFFlNk82azBRNmh0UiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo3MjoiaHR0cHM6Ly9hbnN3ZXJzLW1haW5seS1mdXR1cmVzLWZvbGxvd3MudHJ5Y2xvdWRmbGFyZS5jb20vYWRtaW4vZGFzaGJvYXJkIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2FkbWluL2Rhc2hib2FyZCI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1794075910),('tEhQfkdnqQ0gwOGS5s8AvXpN473b7OsrYxV8z2Rd',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoibUdCZTdPdXA2UUlTTVNWWjROZ3FDMTBYOTFzWE5KYzh5SHA2Q2NyRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1794072991),('uuqIpxang6IUPVj82NN9SNjwfBhJS05GtU0WFh1m',NULL,'2607:f8b0:400c:c20::20','Mozilla/5.0 (Linux; Android 10; SM-G570F; Build/QP1A.190129.147) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.3240.25 Mobile Safari/537.36 EdgA/136.0.3240.25','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRFV3TG1Hc2FOelhUaFhPeXZYUzV5dTBsYmF2VHYxeDF3Sm04MklBTyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo3MjoiaHR0cHM6Ly9hbnN3ZXJzLW1haW5seS1mdXR1cmVzLWZvbGxvd3MudHJ5Y2xvdWRmbGFyZS5jb20vYWRtaW4vZGFzaGJvYXJkIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794074454),('XAqUd4F3oFT665PzmXQbnpM1JxBJ2anZ42dumV8z',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSTNmUlVUTDR2bndSZW9jdms3YnBYbDB4ajkweENaeTVheXNSRE9XbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794074055),('yE3K9GrA3DhTCYpuOw6K1WAvI3FSZvQPJvH9GUep',NULL,'2406:7400:bb:b115:4518:fb66:6a8a:909','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9818','YTozOntzOjY6Il90b2tlbiI7czo0MDoiemhqdUhyZEhvVkExU0k1UlhqbEFLWFBTQldBQzMzNzZCOUVRMDhnaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1794072709),('YRqouxStG7BjBRqQmfVOStEOA6LTgSMnVF25Y8AR',1,'2406:7400:bb:b115:c5f3:c7f3:c38f:cb1e','Mozilla/5.0 (Linux; Android 16; CPH2491 Build/BP2A.250605.015; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/156.0.8078.50 Mobile Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWEdBc052WmRRSGNxTHoxRmV2U2NvcXl1bEVLcENOOHNvUnZFaGRrNiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzI6Imh0dHBzOi8vYW5zd2Vycy1tYWlubHktZnV0dXJlcy1mb2xsb3dzLnRyeWNsb3VkZmxhcmUuY29tL2FkbWluL2Rhc2hib2FyZCI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1794074384);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'residence_name','SRM Residence',NULL,'2026-11-07 07:53:18','2026-11-07 08:40:45'),(2,'contact_phone','+91 7010663823',NULL,'2026-11-07 07:53:18','2026-11-07 07:55:45'),(3,'contact_email','ronaldojagan46@gmail.com',NULL,'2026-11-07 07:53:18','2026-11-07 07:55:45'),(4,'razorpay_enabled','1',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(5,'razorpay_mode','test',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(6,'razorpay_key_id','rzp_test_AbCdEf123456',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(7,'razorpay_key_secret','vicky1998m',NULL,'2026-11-07 07:53:18','2026-11-07 08:56:39'),(8,'upi_enabled','1',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(9,'upi_id','ronaldojagan@okhdfcbank',NULL,'2026-11-07 07:53:18','2026-11-07 08:05:02'),(10,'upi_merchant_name','SRM RESIDENCE',NULL,'2026-11-07 07:53:18','2026-11-07 08:05:02'),(11,'messaging_enabled','1',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(12,'messaging_provider','whatsapp_cloud',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(13,'notify_bill_generated','1',NULL,'2026-11-07 07:53:18','2026-11-07 17:12:02'),(14,'notify_payment_received','1',NULL,'2026-11-07 07:53:18','2026-11-07 17:12:02'),(15,'razorpay_auto_reconcile','1',NULL,'2026-11-07 07:53:18','2026-11-07 08:05:02'),(16,'notify_due_reminder','1',NULL,'2026-11-07 07:53:18','2026-11-07 17:12:02'),(17,'notify_overdue_alert','1',NULL,'2026-11-07 07:53:18','2026-11-07 17:12:03'),(18,'default_eb_rate','12.50',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(19,'default_maintenance','600.00',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(20,'bill_generation_day','1',NULL,'2026-11-07 07:53:18','2026-11-07 07:53:18'),(21,'bill_due_day','10',NULL,'2026-11-07 07:53:18','2026-11-07 17:13:19'),(22,'late_fee_amount','0',NULL,'2026-11-07 07:53:18','2026-11-07 17:13:19'),(23,'residence_tagline','Executive PG & Co-Living Residence',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45'),(24,'contact_person','Property Manager',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45'),(25,'contact_designation','Resident Warden',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45'),(26,'contact_whatsapp','+91 7010663823',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45'),(27,'emergency_phone','+91 9025760270',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45'),(28,'contact_address','No.42 Ambethkar street, North sanjai gandhi nagar, Zamin Pallavaram Chennai 600043',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45'),(29,'gate_closing_time','22:30',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45'),(30,'office_hours','09:00 AM - 08:00 PM',NULL,'2026-11-07 07:55:45','2026-11-07 07:55:45'),(31,'admin_alert_email','ronaldojagan46@gmail.com',NULL,'2026-11-07 07:57:05','2026-11-07 07:57:05'),(32,'reminder_days_before_due','3',NULL,'2026-11-07 07:57:05','2026-11-07 07:57:05'),(33,'bill_sms_template','Dear {resident_name}, your room rent bill for {month} of Rs.{amount} has been generated. Due date: {due_date}. - SRM Residence',NULL,'2026-11-07 07:57:05','2026-11-07 07:57:05'),(34,'payment_receipt_template','Dear {resident_name}, we received your payment of Rs.{amount} on {payment_date}. Receipt #{receipt_no}. Thank you! - SRM Residence',NULL,'2026-11-07 07:57:05','2026-11-07 07:57:05'),(35,'upi_qr_code','settings/upi_qr_1794038702.jpeg','UPI QR Code Image','2026-11-07 08:05:02','2026-11-07 08:05:02'),(36,'razorpay_webhook_secret','',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(37,'bank_name','SBI',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(38,'bank_account_name','SRM RESIDENCE',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(39,'bank_account_number','33988707975',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(40,'bank_ifsc_code','sbin0002248',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(41,'bank_branch','Pallavaram Branch',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(42,'messaging_sender_id','SRMRES',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(43,'messaging_api_key','',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(44,'whatsapp_phone_number_id','',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(45,'mail_mailer','smtp',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(46,'mail_host','smtp.gmail.com',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(47,'mail_port','587',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(48,'mail_username','ronaldojagan46@gmail.com',NULL,'2026-11-07 08:05:02','2026-11-07 08:56:39'),(49,'mail_password','muta abxo vtxb gzhl',NULL,'2026-11-07 08:05:02','2026-11-07 08:56:39'),(50,'mail_encryption','tls',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(51,'mail_from_address','ronaldojagan46@gmail.com',NULL,'2026-11-07 08:05:02','2026-11-07 08:56:39'),(52,'mail_from_name','SRM Residence',NULL,'2026-11-07 08:05:02','2026-11-07 08:05:02'),(53,'default_other_charges','0.00',NULL,'2026-11-07 17:13:19','2026-11-07 17:13:19'),(54,'default_security_deposit','10000.00',NULL,'2026-11-07 17:13:19','2026-11-07 17:13:19'),(55,'default_rent_type','custom',NULL,'2026-11-07 17:13:19','2026-11-07 17:13:19'),(56,'grace_period_days','3',NULL,'2026-11-07 17:13:19','2026-11-07 17:13:19'),(57,'late_fee_type','fixed',NULL,'2026-11-07 17:13:19','2026-11-07 17:13:19'),(58,'currency_symbol','₹',NULL,'2026-11-07 17:13:19','2026-11-07 17:13:19');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `delivery_address_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','paused','cancelled') NOT NULL DEFAULT 'active',
  `carried_forward_days` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_delivery_address_id_foreign` (`delivery_address_id`),
  KEY `subscriptions_user_id_status_index` (`user_id`,`status`),
  KEY `subscriptions_status_index` (`status`),
  CONSTRAINT `subscriptions_delivery_address_id_foreign` FOREIGN KEY (`delivery_address_id`) REFERENCES `delivery_addresses` (`id`),
  CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions_billings`
--

DROP TABLE IF EXISTS `subscriptions_billings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions_billings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `billing_month` varchar(7) NOT NULL,
  `base_days` int(11) NOT NULL,
  `leaves_taken` int(11) NOT NULL DEFAULT 0,
  `carried_forward_days` int(11) NOT NULL DEFAULT 0,
  `total_billable_days` int(11) NOT NULL,
  `price_per_day` decimal(8,2) NOT NULL DEFAULT 40.00,
  `amount_due` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriptions_billings_subscription_id_billing_month_unique` (`subscription_id`,`billing_month`),
  KEY `subscriptions_billings_user_id_payment_status_index` (`user_id`,`payment_status`),
  KEY `subscriptions_billings_billing_month_index` (`billing_month`),
  CONSTRAINT `subscriptions_billings_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`),
  CONSTRAINT `subscriptions_billings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions_billings`
--

LOCK TABLES `subscriptions_billings` WRITE;
/*!40000 ALTER TABLE `subscriptions_billings` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions_billings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temp_entries`
--

DROP TABLE IF EXISTS `temp_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`notes`)),
  `status` varchar(255) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `temp_entries_order_id_unique` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temp_entries`
--

LOCK TABLES `temp_entries` WRITE;
/*!40000 ALTER TABLE `temp_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `temp_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_rooms`
--

DROP TABLE IF EXISTS `tenant_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `room_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_rooms_tenant_id_room_id_unique` (`tenant_id`,`room_id`),
  KEY `tenant_rooms_room_id_foreign` (`room_id`),
  CONSTRAINT `tenant_rooms_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tenant_rooms_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_rooms`
--

LOCK TABLES `tenant_rooms` WRITE;
/*!40000 ALTER TABLE `tenant_rooms` DISABLE KEYS */;
INSERT INTO `tenant_rooms` VALUES (1,1,2,'2026-11-04 17:55:20','2026-11-07 05:56:37'),(2,2,3,'2026-11-05 17:59:00','2026-11-05 17:59:00'),(3,3,4,'2026-11-05 18:02:58','2026-11-07 05:58:21'),(4,4,5,'2026-11-07 05:52:21','2026-11-07 05:58:50'),(5,5,6,'2026-11-07 06:06:20','2026-11-07 06:06:20'),(7,7,12,'2026-11-07 06:10:56','2026-11-07 06:10:56'),(8,8,19,'2026-11-07 06:13:30','2026-11-07 06:13:30'),(9,9,20,'2026-11-07 06:14:40','2026-11-07 06:14:40'),(10,10,21,'2026-11-07 06:17:18','2026-11-07 06:17:18'),(13,2,9,'2026-11-07 07:20:53','2026-11-07 07:20:53'),(16,5,14,'2026-11-07 07:21:16','2026-11-07 07:21:16'),(17,13,26,'2026-11-07 07:23:56','2026-11-07 07:23:56'),(18,14,10,'2026-11-07 07:27:47','2026-11-07 07:27:47'),(19,15,18,'2026-11-07 07:29:21','2026-11-07 07:29:21'),(21,17,28,'2026-11-07 10:52:12','2026-11-07 10:52:12'),(23,19,13,'2026-11-07 11:07:01','2026-11-07 11:07:01');
/*!40000 ALTER TABLE `tenant_rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `room_number` varchar(255) NOT NULL,
  `room_type` varchar(255) NOT NULL DEFAULT 'Single Deluxe',
  `monthly_rent` decimal(10,2) NOT NULL DEFAULT 0.00,
  `eb_unit_rate` decimal(8,2) NOT NULL DEFAULT 10.00,
  `eb_start_reading` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maintenance_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `security_deposit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commence_date` date DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenants_room_id_foreign` (`room_id`),
  CONSTRAINT `tenants_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES (1,2,'ATIULLAH KHAN',NULL,'9839035286','2','Single Deluxe',10000.00,0.00,0.00,0.00,0.00,5000.00,'2026-11-06',NULL,NULL,1,'2026-11-04 17:55:20','2026-11-07 10:46:45',NULL),(2,3,'Abid',NULL,'9840065836','1, 6','Single Deluxe',13500.00,0.00,0.00,0.00,0.00,40000.00,'2026-08-31',NULL,NULL,1,'2026-11-05 17:59:00','2026-11-07 07:20:53',NULL),(3,4,'Ravi',NULL,'7010663823','3','Single Deluxe',5000.00,10.00,0.00,0.00,250.00,10000.00,'2026-11-03',NULL,NULL,1,'2026-11-05 18:02:58','2026-11-07 07:35:27',NULL),(4,5,'Vimal Raj',NULL,'7092549999','4','Single Deluxe',6000.00,0.00,0.00,0.00,0.00,15000.00,'2026-11-06',NULL,NULL,1,'2026-11-07 05:52:21','2026-11-07 05:58:50',NULL),(5,6,'Surendhar',NULL,'9594170969','5, 11','Single Deluxe',17000.00,0.00,0.00,0.00,0.00,25000.00,'2026-11-11',NULL,NULL,1,'2026-11-07 06:06:20','2026-11-07 07:21:16',NULL),(6,11,'Abishek',NULL,'7349482713','8','Single Deluxe',5000.00,0.00,0.00,0.00,0.00,10000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 06:09:29','2026-11-07 11:30:08','2026-11-07 11:30:08'),(7,12,'Malar',NULL,'8056135985','9','Single Deluxe',4500.00,10.00,0.00,0.00,0.00,10000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 06:10:56','2026-11-07 06:10:56',NULL),(8,19,'Anil mehandi',NULL,'6377652410','14','Single Deluxe',6000.00,10.00,0.00,0.00,0.00,15000.00,'2026-11-06',NULL,NULL,1,'2026-11-07 06:13:30','2026-11-07 10:44:27',NULL),(9,20,'Ramjii',NULL,'9798294011','15','Single Deluxe',6000.00,10.00,0.00,0.00,0.00,10000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 06:14:40','2026-11-07 06:14:40',NULL),(10,21,'najrul khan',NULL,'8001381204','16','Single Deluxe',6000.00,10.00,0.00,0.00,0.00,10000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 06:17:18','2026-11-07 06:17:18',NULL),(11,9,'Multi Room Tenant Test','multiroom@test.com','9876543210','6, 7','Single Deluxe, 2-Sharing AC',7000.00,10.00,0.00,0.00,0.00,20000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 07:20:44','2026-11-07 07:26:45','2026-11-07 07:26:45'),(13,26,'Golden Kumar',NULL,'6287505002','21','Single Deluxe',6000.00,10.00,0.00,0.00,0.00,10000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 07:23:56','2026-11-07 07:23:56',NULL),(14,10,'Murugasen  sivagami',NULL,'7010663823','7','Single Deluxe',4000.00,0.00,0.00,0.00,0.00,10000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 07:27:47','2026-11-07 07:27:47',NULL),(15,18,'Merry',NULL,'7010663823','13','Single Deluxe',5000.00,0.00,0.00,0.00,0.00,20000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 07:29:21','2026-11-07 07:29:21',NULL),(16,27,'boopathi',NULL,'7339452586','22','Single Deluxe',6000.00,0.00,0.00,0.00,0.00,0.00,'2026-11-07',NULL,NULL,1,'2026-11-07 09:44:11','2026-11-07 11:34:02','2026-11-07 11:34:02'),(17,28,'Satheesh',NULL,'6380180859','23','Single Deluxe',5000.00,10.00,0.00,0.00,0.00,10000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 10:52:12','2026-11-07 10:52:12',NULL),(19,13,'Amirul islam',NULL,'9395264989','10','Single Deluxe',5000.00,10.00,0.00,0.00,0.00,10000.00,'2026-11-07',NULL,NULL,1,'2026-11-07 11:07:01','2026-11-07 11:07:01',NULL);
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `type` enum('Admin','customer','delivery') NOT NULL DEFAULT 'customer',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `delivery_point_id` varchar(255) DEFAULT NULL,
  `full_address` text DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `fcm_token` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Jagan','7010663823','ronaldojagan46@gmail.com','avatar.jpg','$2y$12$H/2Hi1WE4MP.tGs8Mk14MeU9UY.E9DFWSHEhSi2bEjEE.tmEOmO8u','Admin',1,NULL,NULL,NULL,NULL,'OwcUCoY8Nv','2026-11-03 18:46:07','2026-11-07 17:45:22',NULL),(16,'SRM Admin','9876543210','admin@srmresidence.in',NULL,'$2y$12$ChYRfyCbI8Bmo4cGb3B0M./538KI5nKd9Rap6nR8MWcUsthCX8WLO','Admin',1,NULL,NULL,NULL,NULL,NULL,'2026-11-07 17:36:25','2026-11-07 17:39:40',NULL),(17,'Ragavan','6382016520','ragavanrocky93@gmail.com','avatar.jpg','$2y$12$uTHAUAtT5o0sM8/U26GJceKQt7flu6NyeywMjBlls.PyoaOJWWV92','Admin',1,NULL,NULL,NULL,NULL,NULL,'2026-11-07 18:22:25','2026-11-07 18:22:25',NULL);
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

-- Dump completed on 2026-11-08  9:57:06
