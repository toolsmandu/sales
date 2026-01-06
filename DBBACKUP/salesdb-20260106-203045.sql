-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: 127.0.0.1    Database: salesdb
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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
-- Table structure for table `attendance_logs`
--

DROP TABLE IF EXISTS `attendance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `work_date` date NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `total_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_logs_user_id_foreign` (`user_id`),
  KEY `attendance_logs_work_date_index` (`work_date`),
  CONSTRAINT `attendance_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_logs`
--

LOCK TABLES `attendance_logs` WRITE;
/*!40000 ALTER TABLE `attendance_logs` DISABLE KEYS */;
INSERT INTO `attendance_logs` VALUES (1,6,'2025-12-02','2025-12-02 09:13:46','2025-12-02 09:25:55',12,'2025-12-02 09:13:46','2025-12-02 09:25:55');
/*!40000 ALTER TABLE `attendance_logs` ENABLE KEYS */;
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
INSERT INTO `cache` VALUES ('sales-record-cache-site_setting_login_content','O:22:\"App\\Models\\SiteSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"site_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:5:{s:2:\"id\";i:5;s:3:\"key\";s:13:\"login_content\";s:5:\"value\";s:724:\"{\"badge\":\"Trusted By 25,000+ Customers Worldwide.\",\"brand_accent\":\"LOGIN\",\"headline_prefix\":\"#1 Tools Provider\",\"headline_accent\":\"in Nepal\",\"headline_suffix\":\"with affordable pricing for all.\",\"lead\":\"Toolsmandu is a Nepal-based digital platform focused on providing digital tools, technology, and software licensing services more accessible to individuals and businesses at affordable pricing.\\r\\n<br><br>\\r\\nToolsmandu Provides Genuine Digital Subscriptions At Best Price. Serving Nepalese Market Since 2021 - Trusted By 25,000+ Customers Worldwide.\",\"perks\":[\"Instant Access\",\"Top Customer Support\",\"Affordable Price\"],\"card_title\":\"Sign in to continue\",\"logo_path\":\"login\\/treKKCI6Yu34rZkgRABbYoMMoVe3aJMtZSm5zUyY.png\"}\";s:10:\"created_at\";s:19:\"2025-12-12 06:39:27\";s:10:\"updated_at\";s:19:\"2025-12-12 16:57:23\";}s:11:\"\0*\0original\";a:5:{s:2:\"id\";i:5;s:3:\"key\";s:13:\"login_content\";s:5:\"value\";s:724:\"{\"badge\":\"Trusted By 25,000+ Customers Worldwide.\",\"brand_accent\":\"LOGIN\",\"headline_prefix\":\"#1 Tools Provider\",\"headline_accent\":\"in Nepal\",\"headline_suffix\":\"with affordable pricing for all.\",\"lead\":\"Toolsmandu is a Nepal-based digital platform focused on providing digital tools, technology, and software licensing services more accessible to individuals and businesses at affordable pricing.\\r\\n<br><br>\\r\\nToolsmandu Provides Genuine Digital Subscriptions At Best Price. Serving Nepalese Market Since 2021 - Trusted By 25,000+ Customers Worldwide.\",\"perks\":[\"Instant Access\",\"Top Customer Support\",\"Affordable Price\"],\"card_title\":\"Sign in to continue\",\"logo_path\":\"login\\/treKKCI6Yu34rZkgRABbYoMMoVe3aJMtZSm5zUyY.png\"}\";s:10:\"created_at\";s:19:\"2025-12-12 06:39:27\";s:10:\"updated_at\";s:19:\"2025-12-12 16:57:23\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:2:{i:0;s:3:\"key\";i:1;s:5:\"value\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2080918643),('sales-record-cache-site_setting_registration_enabled','N;',2080919163),('sales-record-cache-site_setting_work_schedule_rules','O:22:\"App\\Models\\SiteSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"site_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:5:{s:2:\"id\";i:4;s:3:\"key\";s:19:\"work_schedule_rules\";s:5:\"value\";s:15:\"[\"1.ww\",\"2.ww\"]\";s:10:\"created_at\";s:19:\"2025-12-02 15:47:10\";s:10:\"updated_at\";s:19:\"2025-12-02 15:47:10\";}s:11:\"\0*\0original\";a:5:{s:2:\"id\";i:4;s:3:\"key\";s:19:\"work_schedule_rules\";s:5:\"value\";s:15:\"[\"1.ww\",\"2.ww\"]\";s:10:\"created_at\";s:19:\"2025-12-02 15:47:10\";s:10:\"updated_at\";s:19:\"2025-12-02 15:47:10\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:2:{i:0;s:3:\"key\";i:1;s:5:\"value\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2080058018),('sales-record-cache-site_setting_work_schedule_table','O:22:\"App\\Models\\SiteSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"site_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:5:{s:2:\"id\";i:3;s:3:\"key\";s:19:\"work_schedule_table\";s:5:\"value\";s:267:\"[[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"]]\";s:10:\"created_at\";s:19:\"2025-12-02 15:31:42\";s:10:\"updated_at\";s:19:\"2025-12-02 17:53:38\";}s:11:\"\0*\0original\";a:5:{s:2:\"id\";i:3;s:3:\"key\";s:19:\"work_schedule_table\";s:5:\"value\";s:267:\"[[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"]]\";s:10:\"created_at\";s:19:\"2025-12-02 15:31:42\";s:10:\"updated_at\";s:19:\"2025-12-02 17:53:38\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:2:{i:0;s:3:\"key\";i:1;s:5:\"value\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2080058018),('sales-record-cache-track_otp_12546549162','a:4:{s:4:\"code\";s:6:\"410557\";s:5:\"email\";s:19:\"xpradiplc@gmail.com\";s:12:\"masked_email\";s:19:\"x*******c@gmail.com\";s:5:\"phone\";s:11:\"12546549162\";}',1765538889),('sales-record-cache-track_otp_9779809254104','a:4:{s:4:\"code\";s:6:\"180605\";s:5:\"email\";s:20:\"xpradiplc@gmail.coms\";s:12:\"masked_email\";s:20:\"xp*******@gmail.coms\";s:5:\"phone\";s:13:\"9779809254104\";}',1765537820),('sales-record-cache-track_otp_9809254104','a:4:{s:4:\"code\";s:6:\"151574\";s:5:\"email\";s:19:\"xpradiplc@gmail.com\";s:12:\"masked_email\";s:19:\"xpr***plc@gmail.com\";s:5:\"phone\";s:10:\"9809254104\";}',1765539162),('sales-record-cache-track_otp_9864484274','a:4:{s:4:\"code\";s:6:\"267547\";s:5:\"email\";s:22:\"kcsunita0111@gmail.com\";s:12:\"masked_email\";s:22:\"kc**********@gmail.com\";s:5:\"phone\";s:10:\"9864484274\";}',1765532236),('salesrecord-cache-site_setting_registration_enabled','O:22:\"App\\Models\\SiteSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"site_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:5:{s:2:\"id\";i:1;s:3:\"key\";s:20:\"registration_enabled\";s:5:\"value\";s:1:\"0\";s:10:\"created_at\";s:19:\"2025-11-11 21:42:26\";s:10:\"updated_at\";s:19:\"2025-11-11 21:42:41\";}s:11:\"\0*\0original\";a:5:{s:2:\"id\";i:1;s:3:\"key\";s:20:\"registration_enabled\";s:5:\"value\";s:1:\"0\";s:10:\"created_at\";s:19:\"2025-11-11 21:42:26\";s:10:\"updated_at\";s:19:\"2025-11-11 21:42:41\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:2:{i:0;s:3:\"key\";i:1;s:5:\"value\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2079924043),('salesrecord-cache-site_setting_work_schedule_table','O:22:\"App\\Models\\SiteSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"site_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:5:{s:2:\"id\";i:2;s:3:\"key\";s:19:\"work_schedule_table\";s:5:\"value\";s:234:\"[[\"Working Days \\/ Employees\",\"Keshab\",\"Prakash\",\"Prabesh\"],[\"Sunday\",\"Holiday\",\"9:30 AM - 5:30 PM\",\"7 AM - 9:30 PM\"],[\"Monday-Friday\",\"7 AM - 3 PM\",\"10:30 AM - 6:30 PM\",\"6 PM - 10 PM\"],[\"Saturday\",\"10 AM - 5 PM\",\"Holiday\",\"Holiday\"]]\";s:10:\"created_at\";s:19:\"2025-11-22 02:13:52\";s:10:\"updated_at\";s:19:\"2025-11-22 02:16:06\";}s:11:\"\0*\0original\";a:5:{s:2:\"id\";i:2;s:3:\"key\";s:19:\"work_schedule_table\";s:5:\"value\";s:234:\"[[\"Working Days \\/ Employees\",\"Keshab\",\"Prakash\",\"Prabesh\"],[\"Sunday\",\"Holiday\",\"9:30 AM - 5:30 PM\",\"7 AM - 9:30 PM\"],[\"Monday-Friday\",\"7 AM - 3 PM\",\"10:30 AM - 6:30 PM\",\"6 PM - 10 PM\"],[\"Saturday\",\"10 AM - 5 PM\",\"Holiday\",\"Holiday\"]]\";s:10:\"created_at\";s:19:\"2025-11-22 02:13:52\";s:10:\"updated_at\";s:19:\"2025-11-22 02:16:06\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:2:{i:0;s:3:\"key\";i:1;s:5:\"value\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2079924154),('toolsmandu-cache-site_setting_login_content','O:22:\"App\\Models\\SiteSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"site_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:5:{s:2:\"id\";i:5;s:3:\"key\";s:13:\"login_content\";s:5:\"value\";s:724:\"{\"badge\":\"Trusted By 25,000+ Customers Worldwide.\",\"brand_accent\":\"LOGIN\",\"headline_prefix\":\"#1 Tools Provider\",\"headline_accent\":\"in Nepal\",\"headline_suffix\":\"with affordable pricing for all.\",\"lead\":\"Toolsmandu is a Nepal-based digital platform focused on providing digital tools, technology, and software licensing services more accessible to individuals and businesses at affordable pricing.\\r\\n<br><br>\\r\\nToolsmandu Provides Genuine Digital Subscriptions At Best Price. Serving Nepalese Market Since 2021 - Trusted By 25,000+ Customers Worldwide.\",\"perks\":[\"Instant Access\",\"Top Customer Support\",\"Affordable Price\"],\"card_title\":\"Sign in to continue\",\"logo_path\":\"login\\/treKKCI6Yu34rZkgRABbYoMMoVe3aJMtZSm5zUyY.png\"}\";s:10:\"created_at\";s:19:\"2025-12-12 06:39:27\";s:10:\"updated_at\";s:19:\"2025-12-12 16:57:23\";}s:11:\"\0*\0original\";a:5:{s:2:\"id\";i:5;s:3:\"key\";s:13:\"login_content\";s:5:\"value\";s:724:\"{\"badge\":\"Trusted By 25,000+ Customers Worldwide.\",\"brand_accent\":\"LOGIN\",\"headline_prefix\":\"#1 Tools Provider\",\"headline_accent\":\"in Nepal\",\"headline_suffix\":\"with affordable pricing for all.\",\"lead\":\"Toolsmandu is a Nepal-based digital platform focused on providing digital tools, technology, and software licensing services more accessible to individuals and businesses at affordable pricing.\\r\\n<br><br>\\r\\nToolsmandu Provides Genuine Digital Subscriptions At Best Price. Serving Nepalese Market Since 2021 - Trusted By 25,000+ Customers Worldwide.\",\"perks\":[\"Instant Access\",\"Top Customer Support\",\"Affordable Price\"],\"card_title\":\"Sign in to continue\",\"logo_path\":\"login\\/treKKCI6Yu34rZkgRABbYoMMoVe3aJMtZSm5zUyY.png\"}\";s:10:\"created_at\";s:19:\"2025-12-12 06:39:27\";s:10:\"updated_at\";s:19:\"2025-12-12 16:57:23\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:2:{i:0;s:3:\"key\";i:1;s:5:\"value\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2080919400),('toolsmandu-cache-site_setting_registration_enabled','N;',2083066256),('toolsmandu-cache-track_otp_9779809254104','a:4:{s:4:\"code\";s:6:\"700406\";s:5:\"email\";s:20:\"xpradiplc@gmail.coms\";s:12:\"masked_email\";s:20:\"xpr***plc@gmail.coms\";s:5:\"phone\";s:13:\"9779809254104\";}',1765560850),('toolsmandu-cache-track_otp_9809254104','a:4:{s:4:\"code\";s:6:\"275731\";s:5:\"email\";s:19:\"xpradiplc@gmail.com\";s:12:\"masked_email\";s:19:\"xpr***plc@gmail.com\";s:5:\"phone\";s:10:\"9809254104\";}',1767035985),('toolsmandu-cache-track_otp_9845721817','a:4:{s:4:\"code\";s:6:\"888787\";s:5:\"email\";s:20:\"toolsmandu@gmail.com\";s:12:\"masked_email\";s:20:\"too***andu@gmail.com\";s:5:\"phone\";s:10:\"9845721817\";}',1765620069);
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
-- Table structure for table `chatbot_entries`
--

DROP TABLE IF EXISTS `chatbot_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatbot_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `question` longtext DEFAULT NULL,
  `answer` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chatbot_entries_product_id_foreign` (`product_id`),
  CONSTRAINT `chatbot_entries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_entries`
--

LOCK TABLES `chatbot_entries` WRITE;
/*!40000 ALTER TABLE `chatbot_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatbot_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chats_sender_id_foreign` (`sender_id`),
  KEY `chats_receiver_id_is_read_index` (`receiver_id`,`is_read`),
  CONSTRAINT `chats_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chats_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chats`
--

LOCK TABLES `chats` WRITE;
/*!40000 ALTER TABLE `chats` DISABLE KEYS */;
/*!40000 ALTER TABLE `chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_codes`
--

DROP TABLE IF EXISTS `coupon_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `code` varchar(255) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupon_codes_code_unique` (`code`),
  KEY `coupon_codes_product_id_foreign` (`product_id`),
  CONSTRAINT `coupon_codes_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_codes`
--

LOCK TABLES `coupon_codes` WRITE;
/*!40000 ALTER TABLE `coupon_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboard_counters`
--

DROP TABLE IF EXISTS `dashboard_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashboard_counters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dashboard_counters_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboard_counters`
--

LOCK TABLES `dashboard_counters` WRITE;
/*!40000 ALTER TABLE `dashboard_counters` DISABLE KEYS */;
INSERT INTO `dashboard_counters` VALUES (1,'sales',161,'2025-12-01 09:32:06','2026-01-06 07:45:56');
/*!40000 ALTER TABLE `dashboard_counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_settings`
--

DROP TABLE IF EXISTS `employee_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `monthly_hours_quota` int(10) unsigned NOT NULL DEFAULT 0,
  `daily_hours_quota` int(10) unsigned NOT NULL DEFAULT 0,
  `holiday_weekdays` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`holiday_weekdays`)),
  `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_settings_user_id_unique` (`user_id`),
  CONSTRAINT `employee_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_settings`
--

LOCK TABLES `employee_settings` WRITE;
/*!40000 ALTER TABLE `employee_settings` DISABLE KEYS */;
INSERT INTO `employee_settings` VALUES (1,6,208,8,'[\"sunday\"]',144.23,'2025-11-11 05:43:55','2025-11-22 02:26:41'),(2,7,104,4,'[\"saturday\"]',144.23,'2025-11-11 05:44:04','2025-11-22 02:26:54'),(3,8,208,8,'[\"saturday\"]',120.19,'2025-11-11 05:44:17','2025-11-12 05:37:44');
/*!40000 ALTER TABLE `employee_settings` ENABLE KEYS */;
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
-- Table structure for table `family_accounts`
--

DROP TABLE IF EXISTS `family_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `family_product_id` bigint(20) unsigned NOT NULL,
  `family_product_name` varchar(191) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `account_index` int(11) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_accounts_family_product_id_foreign` (`family_product_id`),
  CONSTRAINT `family_accounts_family_product_id_foreign` FOREIGN KEY (`family_product_id`) REFERENCES `family_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_accounts`
--

LOCK TABLES `family_accounts` WRITE;
/*!40000 ALTER TABLE `family_accounts` DISABLE KEYS */;
INSERT INTO `family_accounts` VALUES (17,7,'Microsoft 365','ms1@toolsmandu.com',1,10,NULL,'2025-12-17 14:26:43','2025-12-17 14:26:43'),(18,8,'Autodesk','toolsmandu1@gmail.com',1,100,NULL,'2025-12-17 14:27:15','2025-12-17 14:27:15'),(19,8,'Autodesk','toolsmandu2@gmail.com',2,5,NULL,'2025-12-17 15:00:47','2025-12-17 15:00:47');
/*!40000 ALTER TABLE `family_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_members`
--

DROP TABLE IF EXISTS `family_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `family_product_id` bigint(20) unsigned NOT NULL,
  `family_account_id` bigint(20) unsigned NOT NULL,
  `family_name` varchar(255) DEFAULT NULL,
  `family_product_name` varchar(191) DEFAULT NULL,
  `account_name` varchar(191) DEFAULT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_members_family_product_id_foreign` (`family_product_id`),
  KEY `family_members_family_account_id_foreign` (`family_account_id`),
  CONSTRAINT `family_members_family_account_id_foreign` FOREIGN KEY (`family_account_id`) REFERENCES `family_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `family_members_family_product_id_foreign` FOREIGN KEY (`family_product_id`) REFERENCES `family_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_members`
--

LOCK TABLES `family_members` WRITE;
/*!40000 ALTER TABLE `family_members` DISABLE KEYS */;
INSERT INTO `family_members` VALUES (38,8,18,NULL,'Autodesk','toolsmandu1@gmail.com','TM140','xpradiplc@gmail.com',NULL,'9809254104','Autodesk - 1 Month',22,'2025-12-18',30,NULL,NULL,NULL,NULL,NULL,'2025-12-17 14:27:38','2025-12-17 14:27:38'),(39,7,17,NULL,'Microsoft 365','ms1@toolsmandu.com','TM141','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Month Individual',22,'2025-12-18',30,NULL,NULL,NULL,NULL,NULL,'2025-12-17 14:27:51','2025-12-17 14:27:51'),(40,8,18,NULL,'Autodesk','toolsmandu1@gmail.com','TM142','xpradiplc@gmail.com',NULL,'9809254104','Autodesk - 1 Year',22,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 14:28:10','2025-12-17 14:28:10'),(41,8,18,NULL,'Autodesk','toolsmandu1@gmail.com','TM143','xpradiplc@gmail.com',NULL,'9809254104','Autodesk - 1 Year',22,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 14:28:21','2025-12-17 14:28:21'),(42,7,17,NULL,'Microsoft 365','ms1@toolsmandu.com','TM144','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',66,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 14:29:22','2025-12-17 14:29:22'),(43,7,17,NULL,'Microsoft 365','ms1@toolsmandu.com','TM145','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',559,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 14:36:54','2025-12-17 14:36:54'),(44,7,17,NULL,'Microsoft 365','ms1@toolsmandu.com','TM146','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',44,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 14:43:57','2025-12-17 14:43:57'),(45,7,17,NULL,'Microsoft 365','ms1@toolsmandu.com','TM147','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',2222,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 14:47:17','2025-12-17 14:47:17'),(46,8,19,'toolsmandu2@gmail.com','Autodesk',NULL,'TM151','xpradiplc@gmail.com',NULL,'9809254104','Autodesk - 1 Month',22,'2025-12-18',30,NULL,NULL,NULL,NULL,NULL,'2025-12-17 15:19:36','2025-12-17 15:19:36'),(47,8,19,'toolsmandu2@gmail.com','Autodesk',NULL,'TM152','xpradiplc@gmail.com',NULL,'9809254104','Autodesk - 1 Year',22,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 15:20:01','2025-12-17 15:20:01'),(48,8,19,'toolsmandu2@gmail.com','Autodesk',NULL,'TM153','xpradiplc@gmail.com',NULL,'9809254104','Autodesk - 1 Year',2222,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 15:20:25','2025-12-17 15:20:25'),(49,7,17,'ms1@toolsmandu.com','Microsoft 365',NULL,'TM154','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Month Individual',22,'2025-12-18',30,NULL,NULL,NULL,NULL,NULL,'2025-12-17 15:35:49','2025-12-17 15:35:49'),(50,7,17,'ms1@toolsmandu.com','Microsoft 365',NULL,'TM155','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',2,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 15:36:08','2025-12-17 15:36:08'),(51,8,19,'toolsmandu2@gmail.com','Autodesk',NULL,'TM156','xpradiplc@gmail.com',NULL,'9809254104','Autodesk - 1 Month',55,'2025-12-18',30,NULL,NULL,NULL,NULL,NULL,'2025-12-17 15:36:30','2025-12-17 15:36:30'),(52,8,19,'toolsmandu2@gmail.com','Autodesk',NULL,'TM157','xpradiplc@gmail.com',NULL,'9809254104','Autodesk - 1 Year',6,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 15:36:43','2025-12-17 15:36:43'),(53,7,17,'ms1@toolsmandu.com','Microsoft 365',NULL,'TM158','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',66,'2025-12-18',365,NULL,NULL,NULL,NULL,NULL,'2025-12-17 15:38:59','2025-12-17 15:38:59');
/*!40000 ALTER TABLE `family_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_products`
--

DROP TABLE IF EXISTS `family_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `default_capacity` int(11) DEFAULT NULL,
  `linked_product_id` bigint(20) unsigned DEFAULT NULL,
  `linked_variation_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`linked_variation_ids`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `family_products_slug_unique` (`slug`),
  KEY `family_products_linked_product_id_foreign` (`linked_product_id`),
  CONSTRAINT `family_products_linked_product_id_foreign` FOREIGN KEY (`linked_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_products`
--

LOCK TABLES `family_products` WRITE;
/*!40000 ALTER TABLE `family_products` DISABLE KEYS */;
INSERT INTO `family_products` VALUES (7,'Microsoft 365','microsoft-365',NULL,8,'[37]','2025-12-17 14:23:44','2025-12-17 15:38:37'),(8,'Autodesk','autodesk',NULL,18,'[\"38\"]','2025-12-17 14:23:58','2025-12-17 14:26:00');
/*!40000 ALTER TABLE `family_products` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2024_06_01_000150_create_dashboard_counters_table',1),(2,'2024_10_10_000200_create_sale_edit_notifications_table',2),(3,'0001_01_01_000000_create_users_table',3),(4,'0001_01_01_000001_create_cache_table',4),(5,'0001_01_01_000002_create_jobs_table',4),(6,'2024_06_01_000100_create_products_table',5),(7,'2024_06_01_000110_create_product_variations_table',6),(8,'2024_06_01_000120_create_payment_methods_table',7),(9,'2024_06_01_000130_create_sales_table',8),(10,'2024_06_01_000140_create_payment_transactions_table',9),(11,'2024_07_11_000000_create_record_products_table',10),(12,'2024_07_11_000001_add_links_to_record_products_table',10),(13,'2025_01_27_000000_add_expiry_fields_to_products_and_sales',11),(14,'2025_02_28_000001_add_status_to_sales_table',11),(15,'2025_10_29_143052_create_stock_keys_table',12),(16,'2025_10_29_145209_add_stock_pin_and_viewer_columns',13),(17,'2025_10_29_150414_add_stock_pin_name_to_users_table',14),(18,'2025_10_29_152133_create_stock_pins_table',14),(19,'2025_10_29_152324_add_viewed_pin_name_to_stock_keys_table',15),(20,'2025_10_29_162012_create_chatbot_entries_table',16),(21,'2025_11_03_014810_add_remarks_to_sales_table',16),(22,'2025_11_03_015812_add_role_to_users_table',16),(23,'2025_11_03_050958_add_viewed_remarks_to_stock_keys_table',16),(24,'2025_11_03_170000_update_chatbot_entry_text_columns',16),(25,'2025_11_03_171500_add_created_by_to_sales_table',16),(26,'2025_11_10_190113_create_qr_codes_table',17),(27,'2025_11_11_042019_create_coupon_codes_table',18),(28,'2025_11_15_000100_create_employee_settings_table',19),(29,'2025_11_15_000110_create_attendance_logs_table',20),(30,'2025_11_15_000120_create_tasks_table',21),(31,'2025_11_15_000130_create_task_completions_table',21),(32,'2025_11_15_045016_create_chats_table',21),(33,'2025_11_16_000000_create_site_settings_table',21),(34,'2025_11_16_020000_add_daily_quota_and_holidays_to_employee_settings',21),(35,'2025_11_16_030000_add_custom_weekdays_to_tasks',21),(36,'2025_11_16_040000_add_custom_interval_to_tasks',21),(37,'2025_11_16_050000_add_description_to_qr_codes',21),(38,'2025_11_16_060000_add_unique_number_and_monthly_limit_to_payment_methods',22),(39,'2025_11_16_060100_add_payment_method_number_to_qr_codes',22),(40,'2025_11_16_070000_add_stock_flags_to_products_and_variations',22),(41,'2025_11_16_070200_add_visible_to_qr_codes',22),(42,'2025_12_01_000200_make_sales_fields_nullable',22),(43,'2025_12_01_120000_change_sales_remarks_to_text',22);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('07271c1a-21de-43bc-a423-a822f8829099','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',8,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"22@gm.com\"}',NULL,'2025-12-15 11:15:44','2025-12-15 11:15:44'),('1640784d-0e97-4cf2-a401-e2c03128a9a0','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',6,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"22@gm.com\"}',NULL,'2025-12-15 11:15:44','2025-12-15 11:15:44'),('1f7b6a19-deab-43cb-8e2d-a5884c8fe4a2','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',8,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"3@gmail.com\"}',NULL,'2025-12-15 11:23:07','2025-12-15 11:23:07'),('4657af70-65f0-4f76-97ad-dbd1948c6754','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',7,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"22@gm.com\"}',NULL,'2025-12-15 11:15:44','2025-12-15 11:15:44'),('85f0d4be-123c-4379-b89d-3d65fb6e0f07','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',6,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"3@gmail.com\"}',NULL,'2025-12-15 11:23:07','2025-12-15 11:23:07'),('93852cb7-940b-49c7-a0c2-c6c6fdbf6588','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',7,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"2@GMA.COM\"}',NULL,'2025-12-15 11:23:12','2025-12-15 11:23:12'),('a23bb353-6caf-435d-abe6-9d5dc32142ce','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',5,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"22@gm.com\"}',NULL,'2025-12-15 11:15:44','2025-12-15 11:15:44'),('bff0fa66-18eb-4d5c-8044-6fb498c71172','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',7,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"3@gmail.com\"}',NULL,'2025-12-15 11:23:07','2025-12-15 11:23:07'),('cbc35ff8-d1e9-4925-9cf3-9194a0f58eec','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',8,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"2@GMA.COM\"}',NULL,'2025-12-15 11:23:12','2025-12-15 11:23:12'),('d4b28f64-b30f-460c-8b87-d06085805147','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',5,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"3@gmail.com\"}',NULL,'2025-12-15 11:23:07','2025-12-15 11:23:07'),('d9972f9f-a35c-421e-b6ca-d5810268ae17','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',5,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"2@GMA.COM\"}',NULL,'2025-12-15 11:23:12','2025-12-15 11:23:12'),('f0e50b28-bd8e-4a87-8c1c-176f81b24b9e','App\\Notifications\\FamilyAccountCapacityFullNotification','App\\Models\\User',6,'{\"title\":\"Family Limit is full\",\"message\":\"Family Limit is full for Canva. Please manage a new account in time.\",\"type\":\"family_account_full\",\"product\":\"Canva\",\"account\":\"2@GMA.COM\"}',NULL,'2025-12-15 11:23:12','2025-12-15 11:23:12');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
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
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `unique_number` varchar(255) NOT NULL,
  `monthly_limit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_label_unique` (`label`),
  UNIQUE KEY `payment_methods_slug_unique` (`slug`),
  UNIQUE KEY `payment_methods_unique_number_unique` (`unique_number`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (12,'Cash','cash','1',100000.00,18111.00,'2025-12-01 09:35:08','2025-12-06 08:40:33');
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_method_id` bigint(20) unsigned NOT NULL,
  `sale_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_transactions_sale_id_unique` (`sale_id`),
  KEY `payment_transactions_payment_method_id_foreign` (`payment_method_id`),
  CONSTRAINT `payment_transactions_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_transactions_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
INSERT INTO `payment_transactions` VALUES (131,12,122,'income',100.00,'+9779809254104','2025-12-02 17:06:46','2025-12-02 09:21:25','2025-12-02 11:21:46'),(132,12,124,'income',9000.00,'9864484274','2025-12-02 17:04:20','2025-12-02 10:55:53','2025-12-02 11:19:20'),(133,12,121,'income',9000.00,'+9779809254104','2025-12-02 17:10:41','2025-12-02 11:25:11','2025-12-02 11:25:41'),(134,12,125,'income',11.00,'989898','2025-12-06 14:25:33','2025-12-06 08:40:33','2025-12-06 08:40:33');
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variations`
--

DROP TABLE IF EXISTS `product_variations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `expiry_days` int(10) unsigned DEFAULT NULL,
  `is_in_stock` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variations_product_id_name_unique` (`product_id`,`name`),
  CONSTRAINT `product_variations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variations`
--

LOCK TABLES `product_variations` WRITE;
/*!40000 ALTER TABLE `product_variations` DISABLE KEYS */;
INSERT INTO `product_variations` VALUES (21,6,'2 Year',365,1,'2025-12-14 02:40:17','2025-12-14 02:40:30'),(22,7,'1 Year',365,1,'2025-12-14 05:22:44','2025-12-14 05:22:44'),(23,8,'1 Year Individual',365,1,'2025-12-15 11:54:52','2025-12-17 14:25:14'),(24,6,'1 Year',365,1,'2025-12-15 12:19:15','2025-12-15 12:19:15'),(25,9,'1 Year',30,1,'2025-12-16 05:27:12','2025-12-16 05:27:12'),(27,10,'1 Month',30,1,'2025-12-16 05:41:49','2025-12-16 05:41:49'),(28,11,'1 Year',365,1,'2025-12-16 05:50:25','2025-12-16 05:50:25'),(29,11,'Lifetime',36500,1,'2025-12-16 05:50:25','2025-12-16 05:50:25'),(30,12,'1 year',365,1,'2025-12-16 06:39:48','2025-12-16 06:39:48'),(31,13,'1 yeAR',365,1,'2025-12-16 08:26:40','2025-12-16 08:26:40'),(32,14,'1 year',365,1,'2025-12-16 08:53:30','2025-12-16 08:53:30'),(33,15,'Private',30,1,'2025-12-17 02:31:00','2025-12-17 02:31:00'),(34,10,'1 Year',365,1,'2025-12-17 02:44:16','2025-12-17 02:44:16'),(35,16,'1 year',222,1,'2025-12-17 08:30:21','2025-12-17 08:30:21'),(36,17,'ss',1,1,'2025-12-17 11:15:45','2025-12-17 11:15:45'),(37,8,'1 Month Individual',30,1,'2025-12-17 14:25:14','2025-12-17 14:25:14'),(38,18,'1 Month',30,1,'2025-12-17 14:25:36','2025-12-17 14:25:36'),(39,18,'1 Year',365,1,'2025-12-17 14:25:36','2025-12-17 14:25:36'),(40,19,'1 Year',365,1,'2025-12-17 14:51:00','2025-12-17 14:51:00'),(41,19,'2 Year',700,1,'2025-12-17 14:51:00','2025-12-17 14:51:00'),(42,20,'1 Year',365,1,'2026-01-05 09:23:49','2026-01-05 09:23:49'),(43,20,'2 Year',850,1,'2026-01-05 09:23:49','2026-01-05 09:23:49');
/*!40000 ALTER TABLE `product_variations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_in_stock` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (6,'Youtube',1,'2025-12-14 02:39:09','2025-12-14 02:39:09'),(7,'Perplexity AI',1,'2025-12-14 05:22:44','2025-12-14 05:22:44'),(8,'Microsoft 365 Personal',1,'2025-12-15 11:54:52','2025-12-15 11:54:52'),(9,'Canva',1,'2025-12-16 05:27:12','2025-12-16 05:27:12'),(10,'iCloud',1,'2025-12-16 05:41:49','2025-12-16 05:41:49'),(11,'Internet Download Manager (IDM)',1,'2025-12-16 05:50:25','2025-12-16 05:50:25'),(12,'Workspace',1,'2025-12-16 06:39:48','2025-12-16 06:39:48'),(13,'Tools product',1,'2025-12-16 08:26:40','2025-12-16 08:26:40'),(14,'Onedrive',1,'2025-12-16 08:53:30','2025-12-16 08:53:30'),(15,'Cloudflare',1,'2025-12-17 02:31:00','2025-12-17 02:31:00'),(16,'product ko product',1,'2025-12-17 08:30:21','2025-12-17 08:30:21'),(17,'shetsp',1,'2025-12-17 11:15:45','2025-12-17 11:15:45'),(18,'Autodesk',1,'2025-12-17 14:25:36','2025-12-17 14:25:36'),(19,'Zip',1,'2025-12-17 14:51:00','2025-12-17 14:51:00'),(20,'Surfshark VPN',1,'2026-01-05 09:23:49','2026-01-05 09:23:49');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qr_codes`
--

DROP TABLE IF EXISTS `qr_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qr_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `payment_method_number` varchar(255) DEFAULT NULL,
  `visible_to_employees` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_codes`
--

LOCK TABLES `qr_codes` WRITE;
/*!40000 ALTER TABLE `qr_codes` DISABLE KEYS */;
INSERT INTO `qr_codes` VALUES (3,'tr','qr-codes/OMZcxmSaYWqz657lUvHtIS3dbnApSjbq1yXZFnXA.jpg','tr',NULL,1,'2025-12-07 22:11:39','2025-12-07 22:11:39');
/*!40000 ALTER TABLE `qr_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_365_1yr`
--

DROP TABLE IF EXISTS `record_365_1yr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_365_1yr` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_365_1yr`
--

LOCK TABLES `record_365_1yr` WRITE;
/*!40000 ALTER TABLE `record_365_1yr` DISABLE KEYS */;
/*!40000 ALTER TABLE `record_365_1yr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_canva`
--

DROP TABLE IF EXISTS `record_canva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_canva` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_canva`
--

LOCK TABLES `record_canva` WRITE;
/*!40000 ALTER TABLE `record_canva` DISABLE KEYS */;
INSERT INTO `record_canva` VALUES (38,NULL,'email','eyJpdiI6Ik9QeC91b0FtWkQ4K3NkRXRpWlJLM1E9PSIsInZhbHVlIjoiZk1qZ1FsbE81UVVWclc2QzIyVVF1QT09IiwibWFjIjoiMDU1YjE1MjNkMzU4YjY3NDIyNDBmOTI1NGIyZjk4N2Q1ZWM2YWMzZmRhYzQ0NWU3ODg1NWU4MTcwZjlkNzBlOCIsInRhZyI6IiJ9','988','Canva',NULL,'2025-12-12',30,NULL,NULL,'2fa','e2','eyJpdiI6IndDQ1NTNE9HS2VMc2FueGlkREJ0U1E9PSIsInZhbHVlIjoiUzN0MXZxNFNCL05ndnVhM2ZvRjBmdz09IiwibWFjIjoiNzAyMmJhOTU3N2ZhMmUxMmE3MzExMGY3MWQwMzMwZTYyNjZmMGNlYzRlOWUxZDEwNDUxMTJlYTVhMzI4YThjNSIsInRhZyI6IiJ9','2025-12-13 07:57:27','2025-12-13 07:57:27'),(39,NULL,'ss@gmail.com','eyJpdiI6IkhIbGZUTFJZY09KclR5RmxMRkZXMGc9PSIsInZhbHVlIjoiVkdzMkZhaGV6cjllcjhheUdBQzRMQT09IiwibWFjIjoiM2VkNzk5MTBkMjBkM2EyMjBjOGQxYzU3ZGYwMDcxZGFhNWU0MzFlMjhmYTdiOTNhOGU3M2I1Njk3ODdlMzFkOSIsInRhZyI6IiJ9','p2','Canva',NULL,'2025-12-12',NULL,NULL,NULL,'22f22','ss','eyJpdiI6IlZxL09zeEdXTE1CUjdHd1BrYi8vNUE9PSIsInZhbHVlIjoiSklmVEdnaWh2M3RvMmhWVEp2Z2ZJZz09IiwibWFjIjoiNDgzNTk3NDkwODE2NWFkZGM0ZDdhODQ3NzkzMjkwNWU1NDhlNDZjNmNhOGQ4NzdjYTIwZmMzNzEwNjMwZjgzZiIsInRhZyI6IiJ9','2025-12-13 07:58:24','2025-12-13 07:58:24');
/*!40000 ALTER TABLE `record_canva` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_canva_teams`
--

DROP TABLE IF EXISTS `record_canva_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_canva_teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_canva_teams`
--

LOCK TABLES `record_canva_teams` WRITE;
/*!40000 ALTER TABLE `record_canva_teams` DISABLE KEYS */;
INSERT INTO `record_canva_teams` VALUES (1,NULL,'pabsonitdepartment@gmail.com',NULL,'9861890152','Canva Teams',NULL,'2025-12-23',365,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:25:42'),(2,NULL,'tej_subedi@hotmail.com',NULL,'981-3171720','Canva Teams',NULL,'2025-12-24',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(3,NULL,'ajayakchhetri@gmail.com',NULL,'986-1116421','Canva Teams',NULL,'2025-12-25',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(4,NULL,'admin@alphacapitalnepal.com',NULL,'986-1200475','Canva Teams',NULL,'2025-12-27',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(5,NULL,'paperfliesnotebook@gmail.com',NULL,'980-2925613','Canva Teams',NULL,'2025-12-30',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(6,NULL,'info.nischalshrestha@gmail.com',NULL,'982-0777777','Canva Teams',NULL,'2025-12-30',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(8,NULL,'sherchanrishikesh@gmail.com',NULL,'9803285723','Canva Teams',NULL,'2024-01-03',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(9,NULL,'pranayashakya.official@gmail.com',NULL,'9801057885','Canva Teams',NULL,'2024-01-03',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(10,NULL,'harshshahani@gmail.com',NULL,'985-1029120','Canva Teams',NULL,'2025-01-07',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(11,NULL,'aipodcast143@gmail.com',NULL,'1 (405) 261-9666','Canva Teams',NULL,'2025-01-07',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(12,NULL,'yubraz.p@gmail.com',NULL,'984-3743925','Canva Teams',NULL,'2025-01-08',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(13,NULL,'poudelshreya001@gmail.com',NULL,'986-1843884','Canva Teams',NULL,'2025-01-10',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(14,NULL,'tandukar2002@gmail.com',NULL,'976-6485764','Canva Teams',NULL,'2025-01-16',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(15,NULL,'musu.chau@gmail.com',NULL,'44 7926 672768','Canva Teams',NULL,'2025-01-13',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(16,NULL,'suzaxs@gmail.com',NULL,'9851068589','Canva Teams',NULL,'2025-01-13',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(17,NULL,'chagutheeproduction@gmail.com',NULL,'9849949957','Canva Teams',NULL,'2025-01-20',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(18,NULL,'ekahub2023@gmail.com',NULL,'974-8377777','Canva Teams',NULL,'2025-01-21',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(19,NULL,'bimalnepali72@gmail.com',NULL,'Bimal Nepali FB','Canva Teams',NULL,'2025-01-28',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(20,NULL,'mohanbk859@gmail.com',NULL,'Bimal Nepali FB','Canva Teams',NULL,'2025-02-09',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(21,NULL,'nepalcopy01@gmail.com',NULL,'984-7178597','Canva Teams',NULL,'2025-02-09',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(22,NULL,'laxmanbaral891@gmail.com',NULL,'982-8493993','Canva Teams',NULL,'2025-02-15',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(23,NULL,'workhard.malla153@gmail.com',NULL,'17203831470','Canva Teams',NULL,'2025-02-19',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(24,NULL,'ayzean@gmail.com',NULL,'984-9909961','Canva Teams',NULL,'2025-02-26',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(25,NULL,'anishdhakalashan1212@gmail.com',NULL,'fb friend','Canva Teams',NULL,'2025-03-13',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(26,NULL,'acexemailmarketing@gmail.com',NULL,'984-1236531','Canva Teams',NULL,'2025-03-13',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(27,NULL,'contact.brihat@gmail.com',NULL,'9744234987','Canva Teams',NULL,'2025-03-15',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(28,NULL,'ai.vixaal@gmail.com',NULL,'980-1182111','Canva Teams',NULL,'2025-04-01',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(29,NULL,'Charlesbabe37@gmail.com',NULL,'tumo','Canva Teams',NULL,'2025-04-06',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(30,NULL,'navin.siddhi@gmail.com',NULL,'985-1010962','Canva Teams',NULL,'2025-04-08',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(31,NULL,'somai.dambar28@gmail.com',NULL,'985-7052566','Canva Teams',NULL,'2025-04-24',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(32,NULL,'ceo@amgroup.com.np',NULL,'985-1180182','Canva Teams',NULL,'2025-04-22',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(33,NULL,'dhanrajdeuwa9@gmail.com',NULL,'985-1347446','Canva Teams',NULL,'2025-04-26',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(34,NULL,'maharzansanzeev9@gmail.com',NULL,'980-8033416','Canva Teams',NULL,'2025-05-04',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(35,NULL,'roshansah224@gmail.com',NULL,'982-3468731','Canva Teams',NULL,'2025-05-07',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(36,NULL,'kiransharma@msn.com',NULL,'985-6061873','Canva Teams',NULL,'2025-05-07',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(37,NULL,'hellowork280@gmail.com',NULL,'964 750 992 0932','Canva Teams',NULL,'2025-05-12',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(38,NULL,'Subarnadhakal00@gmail.com',NULL,'970-6010616','Canva Teams',NULL,'2025-05-20',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(39,NULL,'lee.steven.elson@gmail.com',NULL,'4.47429E+11','Canva Teams',NULL,'2025-05-25',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(40,NULL,'askdbgurung@gmail.com',NULL,'985-1198998','Canva Teams',NULL,'2025-05-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(41,NULL,'suniz9310@gmail.com',NULL,'_9800921305_','Canva Teams',NULL,'2025-06-01',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(42,NULL,'neptechstore@gmail.com',NULL,'985-1048211','Canva Teams',NULL,'2025-06-01',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(43,NULL,'Pdpro711@gmail.com',NULL,'8.17039E+11','Canva Teams',NULL,'2025-06-03',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(44,NULL,'ganesh@sdmiracle.com.np',NULL,'985-1168627','Canva Teams',NULL,'2025-06-05',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(45,NULL,'Letsgonepalcountry@gmail.com',NULL,'984-3652262','Canva Teams',NULL,'2025-06-11',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(46,NULL,'sabatrithapa154@gmail.com',NULL,'ram babu','Canva Teams',NULL,'2025-06-14',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(47,NULL,'chakradharanish@gmail.com',NULL,'9849559584','Canva Teams',NULL,'2025-06-15',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(48,NULL,'younglifenepal2009@gmail.com',NULL,'984-6044307','Canva Teams',NULL,'2025-06-07',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(49,NULL,'atlanticholidaysnepal@gmail.com',NULL,'984-3199096','Canva Teams',NULL,'2025-07-11',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(50,NULL,'prashantgiri111.pg@gmail.com',NULL,'986-4531602','Canva Teams',NULL,'2025-07-15',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(51,NULL,'kcsarthak55555@gmail.com',NULL,'976-7649561','Canva Teams',NULL,'2025-07-17',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(52,NULL,'Bishalsunar955@gmail.com',NULL,'986-6896325','Canva Teams',NULL,'2025-07-20',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(53,NULL,'Bijaygurung1522@gmail.com',NULL,'MAN GURUNG','Canva Teams',NULL,'2025-07-21',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(54,NULL,'sumanislearning@gmail.com',NULL,'985-6081265','Canva Teams',NULL,'2025-07-30',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(55,NULL,'mercurekathmandu@gmail.com',NULL,'970-7093119','Canva Teams',NULL,'2025-08-01',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(56,NULL,'dhirajmudvari3@gmail.com',NULL,'966 51 046 7850','Canva Teams',NULL,'2025-08-02',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(57,NULL,'sanjeetastha5@gmail.com',NULL,'mukul','Canva Teams',NULL,'2025-08-03',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(58,NULL,'femailmarketing17@gmail.com',NULL,'984-1236531','Canva Teams',NULL,'2025-08-04',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(59,NULL,'imsuju@gmail.com',NULL,'9849098039','Canva Teams',NULL,'2025-09-11',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(60,NULL,'manokrantihelpdesk2@gmail.com',NULL,'986-6210005','Canva Teams',NULL,'2025-09-18',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(61,NULL,'ntsherpa@gmail.com',NULL,'985-1106271','Canva Teams',NULL,'2025-09-22',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(62,NULL,'yrajuheart55@gmail.com',NULL,'980-1663670','Canva Teams',NULL,'2025-10-05',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(63,NULL,'kathasaigrace@gmail.com',NULL,'985-1133312','Canva Teams',NULL,'2025-10-10',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(64,NULL,'technicalsahilofficial@gmail.com',NULL,'982-3187537','Canva Teams',NULL,'2025-10-12',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(65,NULL,'berlinrobert29@gmail.com',NULL,'970-2189302','Canva Teams',NULL,'2025-10-14',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(66,NULL,'graphicspeachy@gmail.com',NULL,'984-0091379','Canva Teams',NULL,'2025-10-16',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(67,NULL,'paharivinod742@gmail.com',NULL,'985-6041617','Canva Teams',NULL,'2025-10-19',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(68,NULL,'Trekstargethimalaya@gmail.com',NULL,'985-1168229','Canva Teams',NULL,'2025-10-22',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(69,NULL,'smah.advancement@gmail.com',NULL,'986-0146265','Canva Teams',NULL,'2025-10-27',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(70,NULL,'webhubs.nepal@gmail.com',NULL,'986-0256887','Canva Teams',NULL,'2025-10-27',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(71,NULL,'computingnerdnepal@gmail.com',NULL,'986-0512016','Canva Teams',NULL,'2025-11-03',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(72,NULL,'Palinatdhar@gmail.com',NULL,'981-3782831','Canva Teams',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(73,NULL,'nirvanacanva2025@gmail.com',NULL,'985-1198248','Canva Teams',NULL,'2025-11-09',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(74,NULL,'advertising.parichay@gmail.com',NULL,'982-7150889','Canva Teams',NULL,'2025-11-10',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08'),(75,NULL,'romandhital731@gmail.com',NULL,'44 7491 628852','Canva Teams',NULL,'2025-11-14',NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-10 02:24:08','2025-12-10 02:24:08');
/*!40000 ALTER TABLE `record_canva_teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_cloudflare`
--

DROP TABLE IF EXISTS `record_cloudflare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_cloudflare` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_cloudflare`
--

LOCK TABLES `record_cloudflare` WRITE;
/*!40000 ALTER TABLE `record_cloudflare` DISABLE KEYS */;
INSERT INTO `record_cloudflare` VALUES (1,'TM95','xpradiplc@gmail.com',NULL,'9809254104','Cloudflare - Private',45,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 02:31:21','2025-12-17 02:31:21'),(2,'TM97','xpradiplc@gmail.com',NULL,'9809254104','Cloudflare - Private',28,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 02:33:51','2025-12-17 02:33:51'),(3,'TM128','xpradiplc@gmail.com',NULL,'9809254104','Cloudflare - Private',55,'2025-12-17',30,30,'remarks',NULL,NULL,NULL,'2025-12-17 05:34:24','2025-12-17 05:34:24'),(4,'TM130','xpradiplc@gmail.com',NULL,'9809254104','Cloudflare - Private',999999,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 08:21:35','2025-12-17 08:21:35');
/*!40000 ALTER TABLE `record_cloudflare` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_fck`
--

DROP TABLE IF EXISTS `record_fck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_fck` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_fck`
--

LOCK TABLES `record_fck` WRITE;
/*!40000 ALTER TABLE `record_fck` DISABLE KEYS */;
INSERT INTO `record_fck` VALUES (1,'TM126','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',34,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 04:34:12','2025-12-17 04:34:12');
/*!40000 ALTER TABLE `record_fck` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_icloud`
--

DROP TABLE IF EXISTS `record_icloud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_icloud` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_icloud`
--

LOCK TABLES `record_icloud` WRITE;
/*!40000 ALTER TABLE `record_icloud` DISABLE KEYS */;
INSERT INTO `record_icloud` VALUES (1,NULL,'xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',222,'2025-12-16',30,30,NULL,NULL,NULL,NULL,'2025-12-16 05:53:17','2025-12-16 05:53:17'),(2,'TM71','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',22,'2025-12-16',30,30,NULL,NULL,NULL,NULL,'2025-12-16 06:29:45','2025-12-16 06:32:11'),(3,'TM98','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',55,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 02:34:53','2025-12-17 02:34:53'),(4,'TM100','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',99,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 02:44:42','2025-12-17 02:44:42'),(5,'TM101','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',9999,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 02:44:51','2025-12-17 02:44:51'),(6,'TM102','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',22,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 02:45:55','2025-12-17 02:45:55'),(7,'TM103','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',23,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 02:51:20','2025-12-17 02:51:20'),(8,'TM104','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',22,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 02:52:46','2025-12-17 02:52:46'),(9,'TM105','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',12,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 02:53:13','2025-12-17 02:53:13'),(10,'TM106','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',12,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 02:53:20','2025-12-17 02:53:20'),(11,'TM107','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',232,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 02:59:44','2025-12-17 02:59:44'),(12,'TM108','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',23,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 03:12:10','2025-12-17 03:12:10'),(13,'TM110','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',223,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 03:13:15','2025-12-17 03:13:15'),(14,'TM111','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',34,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 03:13:25','2025-12-17 03:13:25'),(15,'TM112','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',45,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 03:26:18','2025-12-17 03:26:18'),(16,'TM113','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',23,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 03:26:51','2025-12-17 03:26:51'),(17,'TM114','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',244,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 03:27:10','2025-12-17 03:27:10'),(18,'TM115','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',24,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 03:31:41','2025-12-17 03:31:41'),(19,'TM116','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',45,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 03:36:22','2025-12-17 03:36:22'),(20,'TM117','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',67,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 03:53:43','2025-12-17 03:53:43'),(21,'TM118','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',11,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 03:55:02','2025-12-17 03:55:02'),(22,'TM119','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',45,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 03:55:38','2025-12-17 03:55:38'),(23,'TM120','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',34,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 04:26:55','2025-12-17 04:26:55'),(24,'TM121','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',45,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 04:27:38','2025-12-17 04:27:38'),(25,'TM123','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',66,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 04:32:49','2025-12-17 04:32:49'),(26,'TM125','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Year',66,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 04:33:41','2025-12-17 04:33:41'),(27,'TM127','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',45,'2025-12-17',30,30,NULL,NULL,NULL,NULL,'2025-12-17 04:34:33','2025-12-17 04:44:17'),(28,'TM161','xpradiplc@gmail.com',NULL,'9809254104','iCloud - 1 Month',33,'2026-01-06',30,30,'When changing the column, it checks SHOW INDEX for payment_methods_unique_number_unique; if already present, it just makes the column NOT NULL, otherwise it adds the unique constraint.',NULL,NULL,NULL,'2026-01-06 07:45:56','2026-01-06 07:45:56');
/*!40000 ALTER TABLE `record_icloud` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_idm`
--

DROP TABLE IF EXISTS `record_idm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_idm` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_idm`
--

LOCK TABLES `record_idm` WRITE;
/*!40000 ALTER TABLE `record_idm` DISABLE KEYS */;
INSERT INTO `record_idm` VALUES (1,'TM133','xpradiplc@gmail.com',NULL,'9809254104','Internet Download Manager (IDM) - 1 Year',44,'2025-12-17',365,365,'IDM1YR',NULL,NULL,NULL,'2025-12-17 08:28:07','2025-12-17 08:28:07');
/*!40000 ALTER TABLE `record_idm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_microsoft_365`
--

DROP TABLE IF EXISTS `record_microsoft_365`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_microsoft_365` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_microsoft_365`
--

LOCK TABLES `record_microsoft_365` WRITE;
/*!40000 ALTER TABLE `record_microsoft_365` DISABLE KEYS */;
INSERT INTO `record_microsoft_365` VALUES (1,NULL,'ms1@mailmandu.com',NULL,NULL,'Microsoft 365',NULL,'2025-12-13',365,NULL,'ADMIN ACCOUNT',NULL,NULL,NULL,'2025-12-14 04:57:27','2025-12-14 04:57:27'),(2,NULL,'A2@GMAIL.COM',NULL,'9898','Microsoft 365',NULL,'2025-12-13',365,NULL,NULL,NULL,NULL,NULL,'2025-12-14 04:57:46','2025-12-14 04:57:46'),(3,NULL,'A2@GMAIL.COM',NULL,'9999','Microsoft 365',NULL,'2025-12-12',365,NULL,NULL,NULL,NULL,NULL,'2025-12-14 04:58:01','2025-12-14 04:58:01'),(4,'TM144','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',66,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 14:29:22','2025-12-17 14:29:22'),(5,'TM145','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',559,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 14:36:54','2025-12-17 14:36:54'),(6,'TM146','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',44,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 14:43:57','2025-12-17 14:43:57'),(7,'TM147','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',2222,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 14:47:17','2025-12-17 14:47:17'),(8,'TM155','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',2,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 15:36:08','2025-12-17 15:36:08'),(9,'TM158','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',66,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 15:38:59','2025-12-17 15:38:59'),(10,'TM159','xpradiplc@gmail.com',NULL,'9809254104','Microsoft 365 Personal - 1 Year Individual',555,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 15:46:58','2025-12-17 15:46:58');
/*!40000 ALTER TABLE `record_microsoft_365` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_new2`
--

DROP TABLE IF EXISTS `record_new2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_new2` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_new2`
--

LOCK TABLES `record_new2` WRITE;
/*!40000 ALTER TABLE `record_new2` DISABLE KEYS */;
INSERT INTO `record_new2` VALUES (1,'toolsmandu@gmail.com','eyJpdiI6Ik81TDFObkFvR3ZMdVRuRTVGNW02UGc9PSIsInZhbHVlIjoidEhJeXRqRERVU1NpeEJFaXd1UnlDUT09IiwibWFjIjoiZDI1MGQ0OTE0YjY2MTg3MjkyNDM0ZTc2MjlkZTcyZjQzMmNjMGQ5ZmI1MTJkMzViYTdhODZlNmQ5YjNjYmM4ZSIsInRhZyI6IiJ9','9864484274','new2',5000,'2024-12-01',365,NULL,'remarks','t1','email2','eyJpdiI6IldrN3BNYk5SNy9jTlduVGp1ZmQ4b2c9PSIsInZhbHVlIjoiQ3B3NCtwT3J3a1VvY2cvOVdnczN4Zz09IiwibWFjIjoiOGQ2OTkxM2YwM2M0MjZhYmIyNWRhMWRhN2U1NzRiZGU4MTQwY2VhY2UyNjA0ZTU0ZmU5Y2Y5ZTE3YzllNTNkOSIsInRhZyI6IiJ9','2025-12-10 05:44:26','2025-12-10 05:44:26'),(2,'toolsmandu@gmail.com','eyJpdiI6ImdEZkxBZTRCckpDdGQ0UkFSZ2l4OFE9PSIsInZhbHVlIjoiWm9DTU1qcENsd0l0M3BSY1N5SjN5dz09IiwibWFjIjoiYWZmMzk2Mzc4YTRlYWY4ZmZlZDQ5NmU0ZWI5MTNjMzgzMjU5NDE5YWFhYmMyMzA1MzgwMDQxZGI2MDg1NThhNiIsInRhZyI6IiJ9','9864484274','new2',5000,'2024-12-02',365,NULL,'remarks','t2','email2','eyJpdiI6Ik10Y21qZmN3SUhnS0hiRTZuVzVRenc9PSIsInZhbHVlIjoiMkZrcEFGUXBnNkFrWXEzeDY5azY4Zz09IiwibWFjIjoiN2MyODVkNDZiMjliY2NmMDc1MDVmZDQwODI5MjBlMzIyODdlNTA2NTBiNWM4MTAwNTY1NzIxOGJhNTdkMmYxMyIsInRhZyI6IiJ9','2025-12-10 05:44:26','2025-12-10 05:44:26'),(3,'toolsmandu@gmail.com','eyJpdiI6Imd2dVpzZzAyWWZsdC9JTGFwc3RkYkE9PSIsInZhbHVlIjoiN3U1aEpRcTY4SUxBM1FvMW95L2FTQT09IiwibWFjIjoiZjIyOTEwYWIwMTM0YjJjMjI3MWU0MzIwZjc2NWZjYTg5MjBhMmE4M2VlOWIyNTAxZDc0NDk1YzhiYzJlNjAyMCIsInRhZyI6IiJ9','9864484274','new2',5000,'2024-12-03',365,NULL,'remarks','t3','email2','eyJpdiI6Illvb21zNnh3VklJY3F5WlZITDN0MFE9PSIsInZhbHVlIjoiTFBvUU5qbkJHQU1kMDhwcnZEdGZHUT09IiwibWFjIjoiYjNlZjc3ZmU1Mjg2OTlmNmI0YzBjMzNkZGJmNDU5MGE3NzdkNjJhZDVhY2NkN2FkOGQ4MjYyMGNhZDM3ZDQ5MCIsInRhZyI6IiJ9','2025-12-10 05:44:26','2025-12-10 05:44:26'),(4,'toolsmandu@gmail.com','eyJpdiI6IkJiWHl6UEVxZnIrWU1JUnRlMzlCVmc9PSIsInZhbHVlIjoiQmZmKzZUQUFLOWpvakFwZmdORXM1Zz09IiwibWFjIjoiYjlmNWZiYjAzOTQwNTIzYjNkZjgzOTQ4N2MzNzQ1OWUzMWRhYmI0NGVjMDk5M2MxYzlmZWFhNjIwODRiNzAyYiIsInRhZyI6IiJ9','9864484274','new2',5000,'2024-12-03',365,NULL,'remarks','t4','email2','eyJpdiI6IkNLU3N1ZW14cmNud0QrTllTNFcydUE9PSIsInZhbHVlIjoidExyWmdidGlxbTRkM25OR0JuM05yUT09IiwibWFjIjoiYmJiMzdkOTc5YmVmNWM4MGYxYjVlYzFiNTRlNjQyZjQyYWU3MGY4MTljNTdjZGRhNmViNDI3NGExZTdkOWNmZCIsInRhZyI6IiJ9','2025-12-10 05:44:26','2025-12-10 05:44:26');
/*!40000 ALTER TABLE `record_new2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_onedrive`
--

DROP TABLE IF EXISTS `record_onedrive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_onedrive` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_onedrive`
--

LOCK TABLES `record_onedrive` WRITE;
/*!40000 ALTER TABLE `record_onedrive` DISABLE KEYS */;
/*!40000 ALTER TABLE `record_onedrive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_products`
--

DROP TABLE IF EXISTS `record_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `linked_product_id` bigint(20) unsigned DEFAULT NULL,
  `linked_variation_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`linked_variation_ids`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_products_slug_unique` (`slug`),
  UNIQUE KEY `record_products_table_name_unique` (`table_name`),
  KEY `record_products_linked_product_id_foreign` (`linked_product_id`),
  CONSTRAINT `record_products_linked_product_id_foreign` FOREIGN KEY (`linked_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_products`
--

LOCK TABLES `record_products` WRITE;
/*!40000 ALTER TABLE `record_products` DISABLE KEYS */;
INSERT INTO `record_products` VALUES (11,'Canva Teams','canva-teams','record_canva_teams',NULL,NULL,'2025-12-10 02:23:59','2025-12-10 02:23:59'),(12,'Canva','canva','record_canva',9,'[\"25\"]','2025-12-10 04:31:17','2025-12-16 05:27:19'),(13,'test','test','record_test',NULL,NULL,'2025-12-10 05:31:05','2025-12-10 05:31:05'),(14,'new2','new2','record_new2',NULL,NULL,'2025-12-10 05:44:21','2025-12-10 05:44:21'),(15,'Microsoft 365','microsoft-365','record_microsoft_365',8,'[\"23\"]','2025-12-14 04:56:41','2025-12-16 05:07:22'),(16,'Youtube','youtube','record_youtube',6,'[\"24\",\"21\"]','2025-12-16 05:07:47','2025-12-16 05:07:53'),(17,'iCloud','icloud','record_icloud',10,'[\"27\"]','2025-12-16 05:41:59','2025-12-17 04:34:23'),(18,'Workspace','workspace','record_workspace',12,'[\"30\"]','2025-12-16 06:39:35','2025-12-16 06:39:57'),(19,'TOOLS PRODUCT','tools-product','record_tools_product',13,'[\"31\"]','2025-12-16 08:26:54','2025-12-16 08:27:00'),(20,'Onedrive','onedrive','record_onedrive',14,'[\"32\"]','2025-12-16 08:53:38','2025-12-16 08:53:42'),(21,'Cloudflare','cloudflare','record_cloudflare',15,'[\"33\"]','2025-12-17 02:30:49','2025-12-17 02:31:07'),(22,'fck','fck','record_fck',10,'[\"27\"]','2025-12-17 03:54:44','2025-12-17 03:54:50'),(23,'IDM','idm','record_idm',11,'[\"28\"]','2025-12-17 08:27:45','2025-12-17 08:27:50'),(24,'sheet ko product','sheet-ko-product','record_sheet_ko_product',17,'[\"36\"]','2025-12-17 08:30:48','2025-12-17 11:15:57'),(25,'365 1yr','365-1yr','record_365_1yr',6,'[\"21\"]','2025-12-17 14:49:42','2025-12-17 14:49:58'),(26,'Zip','zip','record_zip',19,'[\"40\"]','2025-12-17 14:51:08','2025-12-17 14:51:12');
/*!40000 ALTER TABLE `record_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_sheet_ko_product`
--

DROP TABLE IF EXISTS `record_sheet_ko_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_sheet_ko_product` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_sheet_ko_product`
--

LOCK TABLES `record_sheet_ko_product` WRITE;
/*!40000 ALTER TABLE `record_sheet_ko_product` DISABLE KEYS */;
INSERT INTO `record_sheet_ko_product` VALUES (1,'TM139','xpradiplc@gmail.com',NULL,'9809254104','shetsp - ss',3,'2025-12-17',1,1,NULL,NULL,NULL,NULL,'2025-12-17 11:29:57','2025-12-17 11:29:57');
/*!40000 ALTER TABLE `record_sheet_ko_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_test`
--

DROP TABLE IF EXISTS `record_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_test` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_test`
--

LOCK TABLES `record_test` WRITE;
/*!40000 ALTER TABLE `record_test` DISABLE KEYS */;
INSERT INTO `record_test` VALUES (1,'toolsmandu@gmail.com','eyJpdiI6IkpQR2FtK25IQzZHbk1xYTVGWUFFK2c9PSIsInZhbHVlIjoiWFJrNTZReWFHdmlPYUd4Qmtockpwdz09IiwibWFjIjoiM2ZkNTc2ZGU3ZTFkMDAzZWVkNjVlYzY5NWNkNTAzNjNiYmYxYjdlMDFiZWRhNjU3OTQ0Y2QxOTNiZThkMGUyZCIsInRhZyI6IiJ9','9864484274','test','2024-12-01',365,NULL,'remarks','t1','email2','eyJpdiI6IkhoSUk3VnFJVVJiWS8yYkoxMFduQUE9PSIsInZhbHVlIjoienZOUmdreFVzVUdiVUpnRzdsYlJNUT09IiwibWFjIjoiMmU2YzU4YmFmOWU3ZmRhNmYxZDVhYWY0NjI2NjI4MzIwNjU2ZWEzODU5MmE0NmY4YzM2MjAwYmU1YTI3OWQxOCIsInRhZyI6IiJ9','2025-12-10 05:31:25','2025-12-10 05:31:25'),(2,'toolsmandu@gmail.com','eyJpdiI6IkV1NldUNDJjOERCemE4TkVQQ2lpR0E9PSIsInZhbHVlIjoiM2xpeVpNaEhCY3hUU1NxcmNvbkw4dz09IiwibWFjIjoiYjczODdjYmEyNTYzZGU0ZTRkZjdhZWJiM2U3ZTUzZjdmYjE1MGZhNGEzMmE0ODg2NjBiZmE2OTdhM2EwMWEwMiIsInRhZyI6IiJ9','9864484274','test','2024-12-02',365,NULL,'remarks','t2','email2','eyJpdiI6Ikk3NmJSWXovdkkrUkRKY3ZaU29hMFE9PSIsInZhbHVlIjoiVTJicWl0VVV3eHU0UEVHNjhUUStQQT09IiwibWFjIjoiNWY0MWMzNWI1YTc3Y2E4MDEwYjA4NzZjNjgxODY4OThjNWQ0NGQ0NjZmODcwMzcxYTU0NzY4MTc3MmU0NWQ1NSIsInRhZyI6IiJ9','2025-12-10 05:31:25','2025-12-10 05:31:25'),(3,'toolsmandu@gmail.com','eyJpdiI6Im9vdzFDWFduY29uWnFOM0FRdVNnSFE9PSIsInZhbHVlIjoiaW9CMDZZOGNERi8vWGdMTGZML21zUT09IiwibWFjIjoiZGRhMGViN2E5ZTk2OTc3YjE1ZmUwNTc2MWY1YjdkNTZhNWZmYzk4NTdmMTY3NTk0ZTU3ZWE0MDcyZTBkNGI4MSIsInRhZyI6IiJ9','9864484274','test','2024-12-03',365,NULL,'remarks','t3','email2','eyJpdiI6ImdyR3ROTWFuQzhwbi85cDNtYjQ3UGc9PSIsInZhbHVlIjoiVE5TSCt3ZktCellLL2VtZk04M29zQT09IiwibWFjIjoiNDQwOWY4YzEwNTM1NGVmNzFiODI5MDkwNGZlODI1Njc1MDBmMjk1MTNmNDhmZTM1ZjE0MWE2ZjU3YTM5NjkzMCIsInRhZyI6IiJ9','2025-12-10 05:31:25','2025-12-10 05:31:25'),(4,'toolsmandu@gmail.com','eyJpdiI6IkZMbzhsVXJOdS9wK2pKK3Rkem5FSWc9PSIsInZhbHVlIjoiMXZGeXQ5Rmk5N0VrbHBvdDl0WlFCQT09IiwibWFjIjoiNGJjZTE5ZGJlY2U5YzA5YTBjNWVjNDdjODg5YjJjOWEzZjZmNGU3ZGJmOTQzYjBkM2U1Yzc0OWJmNzI1YzA0YiIsInRhZyI6IiJ9','9864484274','test','2024-12-03',365,NULL,'remarks','t4','email2','eyJpdiI6IlY0NzN0cXVMZDgxdzBjODVSb1pKekE9PSIsInZhbHVlIjoiNEtLVnNhVkNWT2lkbHZqeVN5dmJOZz09IiwibWFjIjoiNzUxODA2NTRlYTNjMmQxNjBjMmIwZDIzOGUzZjM4MzhmYjg1ZGM4NWY2NWU2MTRiZDk2YmI1YTYyNDE2YmEzOCIsInRhZyI6IiJ9','2025-12-10 05:31:25','2025-12-10 05:31:25'),(5,'','','','test','2025-12-10',NULL,NULL,'','','','','2025-12-10 05:33:55','2025-12-10 05:33:55');
/*!40000 ALTER TABLE `record_test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_tools_product`
--

DROP TABLE IF EXISTS `record_tools_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_tools_product` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_tools_product`
--

LOCK TABLES `record_tools_product` WRITE;
/*!40000 ALTER TABLE `record_tools_product` DISABLE KEYS */;
INSERT INTO `record_tools_product` VALUES (1,'TM99','xpradiplc@gmail.com',NULL,'9809254104','Tools product - 1 yeAR',233,'2025-12-17',365,365,NULL,NULL,NULL,NULL,'2025-12-17 02:38:17','2025-12-17 02:38:17'),(2,'TM137','xpradiplc@gmail.com',NULL,'9809254104','Tools product - 1 yeAR',223,'2025-12-17',365,365,'333',NULL,NULL,NULL,'2025-12-17 11:15:00','2025-12-17 11:15:00');
/*!40000 ALTER TABLE `record_tools_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_workspace`
--

DROP TABLE IF EXISTS `record_workspace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_workspace` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_workspace`
--

LOCK TABLES `record_workspace` WRITE;
/*!40000 ALTER TABLE `record_workspace` DISABLE KEYS */;
INSERT INTO `record_workspace` VALUES (1,'TM77','xpradiplc@gmail.com',NULL,'9809254104','Workspace - 1 year',22,'2025-12-16',365,365,NULL,NULL,NULL,NULL,'2025-12-16 06:40:07','2025-12-16 06:40:07');
/*!40000 ALTER TABLE `record_workspace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_youtube`
--

DROP TABLE IF EXISTS `record_youtube`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_youtube` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_youtube`
--

LOCK TABLES `record_youtube` WRITE;
/*!40000 ALTER TABLE `record_youtube` DISABLE KEYS */;
INSERT INTO `record_youtube` VALUES (1,'TM148','xpradiplc@gmail.com',NULL,'9809254104','Youtube - 2 Year',22,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 14:50:12','2025-12-17 14:50:12');
/*!40000 ALTER TABLE `record_youtube` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_zip`
--

DROP TABLE IF EXISTS `record_zip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_zip` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `sales_amount` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry` int(11) DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `two_factor` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_zip`
--

LOCK TABLES `record_zip` WRITE;
/*!40000 ALTER TABLE `record_zip` DISABLE KEYS */;
INSERT INTO `record_zip` VALUES (1,'TM149','xpradiplc@gmail.com',NULL,'9809254104','Zip - 1 Year',1,'2025-12-18',365,366,NULL,NULL,NULL,NULL,'2025-12-17 14:51:23','2025-12-17 14:51:23');
/*!40000 ALTER TABLE `record_zip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_edit_notifications`
--

DROP TABLE IF EXISTS `sale_edit_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_edit_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_edit_notifications_sale_id_foreign` (`sale_id`),
  KEY `sale_edit_notifications_actor_id_foreign` (`actor_id`),
  CONSTRAINT `sale_edit_notifications_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_edit_notifications_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_edit_notifications`
--

LOCK TABLES `sale_edit_notifications` WRITE;
/*!40000 ALTER TABLE `sale_edit_notifications` DISABLE KEYS */;
INSERT INTO `sale_edit_notifications` VALUES (1,124,7,'Prabesh edited the TM20: Changed Amount from 200.00 to 500.00','2025-12-02 10:57:53','2025-12-02 10:57:53'),(2,124,7,'Prabesh edited the TM20: Changed Email from kcsunita011@gmail.com to kcsunita0111@gmail.com','2025-12-02 10:58:10','2025-12-02 10:58:10'),(3,124,7,'Prabesh edited the TM20: Changed Status from completed to refunded','2025-12-02 10:58:24','2025-12-02 10:58:24'),(4,124,7,'Prabesh edited the TM20: Changed Amount from 6,000.00 to 9,000.00','2025-12-02 11:19:20','2025-12-02 11:19:20'),(5,122,7,'Prabesh edited the TM18: Changed Status from completed to refunded','2025-12-02 11:21:33','2025-12-02 11:21:33'),(6,121,7,'Prabesh edited the TM17: Changed Amount from N/A to 500.00; Changed Email from N/A to xpradiplc5@gmail.com; Changed Status from pending to completed; Changed Payment Method from N/A to Cash','2025-12-02 11:25:11','2025-12-02 11:25:11'),(7,121,7,'Prabesh edited the TM17: Changed Status from completed to pending','2025-12-02 11:25:36','2025-12-02 11:25:36'),(8,121,7,'Prabesh edited the TM17: Changed Amount from 500.00 to 9,000.00; Changed Status from pending to completed','2025-12-02 11:25:41','2025-12-02 11:25:41'),(9,125,5,'Pradip edited the TM21: Changed Status from completed to refunded','2025-12-09 23:57:55','2025-12-09 23:57:55'),(10,129,5,'Pradip edited the TM25: Changed Amount from N/A to 111.00; Changed Email from N/A to s@g.com; Changed Status from pending to completed','2025-12-10 01:45:43','2025-12-10 01:45:43'),(11,129,5,'Pradip edited the TM25: Changed Product from N/A to Google Storage Plan - 100GB Plan Yearly Genuine Plan','2025-12-10 01:48:12','2025-12-10 01:48:12'),(12,147,5,'Pradip edited the TM43: Changed Product from Google Storage Plan - 2TB 1 Year to Youtube - 1 Year','2025-12-14 02:39:16','2025-12-14 02:39:16');
/*!40000 ALTER TABLE `sale_edit_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) NOT NULL,
  `purchase_date` date NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_expiry_days` int(10) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `sales_amount` decimal(14,2) DEFAULT NULL,
  `payment_method_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_serial_number_unique` (`serial_number`),
  KEY `sales_payment_method_id_foreign` (`payment_method_id`),
  KEY `sales_created_by_foreign` (`created_by`),
  CONSTRAINT `sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (119,'TM15','2025-12-01','Google Storage Plan - 100GB 1 Year',365,NULL,'+9779809254104',NULL,NULL,NULL,'pending',5,'2025-12-01 10:50:51','2025-12-06 08:38:53'),(121,'TM17','2025-12-02','Google Storage Plan - 100GB 1 Year',365,NULL,'+9779809254104','xpradiplc5@gmail.com',9000.00,12,'completed',5,'2025-12-02 04:30:05','2025-12-06 08:38:53'),(122,'TM18','2025-12-02','Google Storage Plan - 100GB 1 Year',365,NULL,'+9779809254104','xpradiplc5@gmail.com',100.00,12,'completed',5,'2025-12-02 09:13:35','2025-12-06 08:38:53'),(123,'TM19','2025-12-02','Google Storage Plan - 100GB 1 Year',365,NULL,'+9779809254104','xpradiplc@gmail.coms',NULL,NULL,'completed',5,'2025-12-02 09:22:02','2025-12-06 08:38:53'),(124,'TM20','2025-12-02','Google Storage Plan - 100GB 1 Year',365,NULL,'9864484274','kcsunita0111@gmail.com',9000.00,12,'completed',7,'2025-12-02 10:55:53','2025-12-06 08:38:53'),(125,'TM21','2025-12-06','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,'11','989898','s@gm.com',11.00,12,'refunded',5,'2025-12-06 08:40:33','2025-12-09 23:57:55'),(126,'TM22','2025-12-10','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'5555',NULL,4333.00,NULL,'completed',5,'2025-12-10 00:30:17','2025-12-10 00:30:17'),(127,'TM23','2025-12-10','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'222',NULL,222.00,NULL,'completed',5,'2025-12-10 00:57:04','2025-12-10 00:57:04'),(128,'TM24','2025-12-10','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'95555',NULL,NULL,NULL,'pending',5,'2025-12-10 00:59:41','2025-12-10 00:59:41'),(129,'TM25','2025-12-10','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'988888','s@g.com',111.00,NULL,'completed',5,'2025-12-10 01:12:35','2025-12-10 01:48:12'),(130,'TM26','2025-12-12','Google Storage Plan - 200GB Plan',365,NULL,'9809254104','xpradiplc@gmail.com',5000.00,NULL,'completed',5,'2025-12-12 03:21:47','2025-12-12 03:21:47'),(131,'TM27','2025-12-12','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'+1 (254) 654-9162','xpradiplc@gmail.com',500.00,NULL,'completed',5,'2025-12-12 04:32:20','2025-12-12 04:32:20'),(132,'TM28','2025-12-13','Google Storage Plan - 200GB Plan',365,NULL,'+12546549162','xpradiplc@gmail.com',500.00,NULL,'completed',5,'2025-12-13 01:17:33','2025-12-13 01:17:33'),(133,'TM29','2025-12-02','Google Storage Plan - 200GB Plan',365,NULL,'+12546549162','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-13 01:19:37','2025-12-13 01:19:37'),(134,'TM30','2025-12-13','Google Storage Plan - 200GB Plan',365,NULL,'+12546549162','xpradiplc@gmail.com',2222.00,NULL,'completed',5,'2025-12-13 01:20:13','2025-12-13 01:20:13'),(135,'TM31','2025-12-13','Google Storage Plan - 2TB 1 Year',365,NULL,'+12546549162','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-13 03:31:35','2025-12-13 03:31:35'),(136,'TM32','2025-12-13','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'+9778888','xw@gmail.com',111.00,NULL,'completed',5,'2025-12-13 03:33:01','2025-12-13 03:33:01'),(137,'TM33','2025-12-13','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'+12546549162','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-13 03:34:12','2025-12-13 03:34:12'),(138,'TM34','2025-12-13','Google Storage Plan - 200GB Plan',365,NULL,'+12546549122','xpradiplc22@gmail.com',111.00,NULL,'completed',5,'2025-12-13 03:34:40','2025-12-13 03:34:40'),(139,'TM35','2025-12-13','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'+1254654922','ds@gmial.com',22.00,NULL,'completed',5,'2025-12-13 03:36:02','2025-12-13 03:36:02'),(140,'TM36','2025-12-13','Google Storage Plan - 200GB Plan',365,NULL,'9899','ws@gmail.com',222.00,NULL,'completed',5,'2025-12-13 03:38:10','2025-12-13 03:38:10'),(141,'TM37','2025-12-13','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'9888','sdfd@gmi.com',55.00,NULL,'completed',5,'2025-12-13 03:53:02','2025-12-13 03:53:02'),(142,'TM38','2025-12-13','Google Storage Plan - 200GB Plan',365,NULL,'6999','ss@gmail.com',222.00,NULL,'completed',5,'2025-12-13 03:55:12','2025-12-13 03:55:12'),(143,'TM39','2025-12-13','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'9809585555','sdss@gmail.com',NULL,NULL,'pending',5,'2025-12-13 03:56:30','2025-12-13 03:56:30'),(144,'TM40','2025-12-13','Google Storage Plan - 200GB Plan',365,NULL,'9845721817','toolsmandu@gmail.com',100.00,NULL,'completed',5,'2025-12-13 04:00:23','2025-12-13 04:00:23'),(145,'TM41','2025-12-09','Google Storage Plan - 200GB Plan',365,NULL,'9845721817','toolsmandu2@gmail.com',55.00,NULL,'completed',5,'2025-12-13 04:00:35','2025-12-13 04:00:35'),(146,'TM42','2025-12-13','Google Storage Plan - 100GB Plan Yearly Genuine Plan',365,NULL,'9809254105','xpradiplc@gmail.com',111.00,NULL,'completed',5,'2025-12-13 04:11:04','2025-12-13 04:11:04'),(147,'TM43','2025-12-13','Youtube - 1 Year',NULL,NULL,'9809254105','xpradiplc@gmail.com',333.00,NULL,'completed',5,'2025-12-13 04:12:23','2025-12-14 02:39:16'),(148,'TM44','2025-12-01','Perplexity AI - 1 Year',365,NULL,'9809254105','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-14 05:26:30','2025-12-14 05:26:30'),(149,'TM45','2025-12-02','Perplexity AI - 1 Year',365,NULL,'6666','ss@gmai.com',2222.00,NULL,'completed',5,'2025-12-14 06:01:31','2025-12-14 06:01:31'),(150,'TM46','2025-12-03','Perplexity AI - 1 Year',365,NULL,'9809254105','xpradiplc@gmail.com',44444.00,NULL,'completed',5,'2025-12-14 06:03:20','2025-12-14 06:03:20'),(151,'TM47','2025-10-01','Perplexity AI - 1 Year',365,NULL,'9809254105','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-14 06:17:18','2025-12-14 06:17:18'),(152,'TM48','2025-12-15','Perplexity AI - 1 Year',365,'1yr','9809254104','xpradiplc@gmail.com',4000.00,NULL,'completed',5,'2025-12-15 12:09:01','2025-12-15 12:09:01'),(153,'TM49','2025-12-15','Perplexity AI - 1 Year',365,'rj','9809254104','xpradiplc@gmail.com',4000.00,NULL,'completed',5,'2025-12-15 12:10:35','2025-12-15 12:10:35'),(154,'TM50','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',4000.00,NULL,'completed',5,'2025-12-15 13:19:32','2025-12-15 13:19:32'),(155,'TM51','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',4000.00,NULL,'completed',5,'2025-12-15 13:25:01','2025-12-15 13:25:01'),(156,'TM52','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',5000.00,NULL,'completed',5,'2025-12-15 13:32:20','2025-12-15 13:32:20'),(157,'TM53','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',50000.00,NULL,'completed',5,'2025-12-15 13:33:04','2025-12-15 13:33:04'),(158,'TM54','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',400.00,NULL,'completed',5,'2025-12-15 13:36:30','2025-12-15 13:36:30'),(159,'TM55','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-15 13:45:36','2025-12-15 13:45:36'),(160,'TM56','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',4000.00,NULL,'completed',5,'2025-12-15 13:51:07','2025-12-15 13:51:07'),(161,'TM57','2025-12-16','Perplexity AI - 1 Year',365,'remarks','9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-15 13:51:33','2025-12-15 13:51:33'),(162,'TM58','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',55.00,NULL,'completed',5,'2025-12-15 13:51:43','2025-12-15 13:51:43'),(163,'TM59','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',988.00,NULL,'completed',5,'2025-12-15 13:51:51','2025-12-15 13:51:51'),(164,'TM60','2025-12-16','Youtube - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-15 13:53:54','2025-12-15 13:53:54'),(165,'TM61','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-15 15:18:52','2025-12-15 15:18:52'),(166,'TM62','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',555.00,NULL,'completed',5,'2025-12-15 15:22:33','2025-12-15 15:22:33'),(167,'TM63','2025-12-16','Perplexity AI - 1 Year',365,'this is remarks','9809254104','xpradiplc@gmail.com',500.00,NULL,'completed',5,'2025-12-16 01:57:35','2025-12-16 01:57:35'),(168,'TM64','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 03:19:36','2025-12-16 03:19:36'),(169,'TM65','2025-12-16','Canva - 1 Year',30,'remarksmain','9809254104','xpradiplc@gmail.com',2000.00,NULL,'completed',5,'2025-12-16 05:27:39','2025-12-16 05:27:39'),(170,'TM66','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',500.00,NULL,'completed',5,'2025-12-16 05:42:16','2025-12-16 05:42:16'),(171,'TM67','2025-12-16','iCloud - 1 Year',30,NULL,'9809254104','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-16 05:46:03','2025-12-16 05:46:03'),(172,'TM68','2025-12-16','iCloud - 1 Year',30,NULL,'9809254104','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-16 05:53:17','2025-12-16 05:53:17'),(173,'TM69','2025-12-16','iCloud - 1 Year',30,'rem','9809254104','xpradiplc@gmail.com',500.00,NULL,'completed',5,'2025-12-16 06:27:48','2025-12-16 06:27:48'),(174,'TM70','2025-12-16','iCloud - 1 Year',30,NULL,'9809254104','xpradiplc@gmail.com',22222.00,NULL,'completed',5,'2025-12-16 06:29:12','2025-12-16 06:29:12'),(175,'TM71','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 06:29:44','2025-12-16 06:29:44'),(176,'TM72','2025-12-16','iCloud - 1 Year',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 06:30:04','2025-12-16 06:30:04'),(177,'TM73','2025-12-16','iCloud - 1 Month',30,'r','9809254104','xpradiplc@gmail.com',100.00,NULL,'completed',5,'2025-12-16 06:34:06','2025-12-16 06:34:06'),(178,'TM74','2025-12-16','iCloud - 1 Year',30,NULL,'9809254104','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-16 06:34:16','2025-12-16 06:34:16'),(179,'TM75','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',55.00,NULL,'completed',5,'2025-12-16 06:38:09','2025-12-16 06:38:09'),(180,'TM76','2025-12-16','iCloud - 1 Year',30,NULL,'9809254104','xpradiplc@gmail.com',55.00,NULL,'completed',5,'2025-12-16 06:38:17','2025-12-16 06:38:17'),(181,'TM77','2025-12-16','Workspace - 1 year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 06:40:07','2025-12-16 06:40:07'),(182,'TM78','2025-12-16','iCloud - 1 Year',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 06:40:36','2025-12-16 06:40:36'),(183,'TM79','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',2.00,NULL,'completed',5,'2025-12-16 06:40:43','2025-12-16 06:40:43'),(184,'TM80','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 06:41:18','2025-12-16 06:41:18'),(185,'TM81','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 06:41:37','2025-12-16 06:41:37'),(186,'TM82','2025-12-16','Workspace - 1 year',365,NULL,'9809254104','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-16 06:43:29','2025-12-16 06:43:29'),(187,'TM83','2025-12-16','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 07:16:02','2025-12-16 07:16:02'),(188,'TM84','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-16 07:27:13','2025-12-16 07:27:13'),(189,'TM85','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22233.00,NULL,'completed',5,'2025-12-16 08:15:18','2025-12-16 08:15:18'),(190,'TM86','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-16 08:20:44','2025-12-16 08:20:44'),(191,'TM87','2025-12-16','Workspace - 1 year',365,NULL,'9809254104','xpradiplc@gmail.com',25.00,NULL,'completed',5,'2025-12-16 08:21:25','2025-12-16 08:21:25'),(192,'TM88','2025-12-16','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',34.00,NULL,'completed',5,'2025-12-16 08:25:34','2025-12-16 08:25:34'),(193,'TM89','2025-12-16','Tools product - 1 yeAR',365,NULL,'9809254104','xpradiplc@gmail.com',345.00,NULL,'completed',5,'2025-12-16 08:27:16','2025-12-16 08:27:16'),(194,'TM90','2025-12-16','Workspace - 1 year',365,NULL,'9809254104','xpradiplc@gmail.com',23.00,NULL,'completed',5,'2025-12-16 08:35:35','2025-12-16 08:35:35'),(195,'TM91','2025-12-16','Microsoft 365 Personal - 1 Year Individual',NULL,NULL,'9809254104','xpradiplc@gmail.com',999.00,NULL,'completed',5,'2025-12-16 08:37:35','2025-12-16 08:37:35'),(196,'TM92','2025-12-16','Microsoft 365 Personal - 1 Year Individual',NULL,NULL,'9809254104','xpradiplc@gmail.com',666.00,NULL,'completed',5,'2025-12-16 08:38:44','2025-12-16 08:38:44'),(197,'TM93','2025-12-16','Microsoft 365 Personal - 1 Year Individual',NULL,NULL,'9809254104','xpradiplc@gmail.com',345.00,NULL,'completed',5,'2025-12-16 08:40:54','2025-12-16 08:40:54'),(198,'TM94','2025-12-16','Onedrive - 1 year',365,NULL,'98655','88@g.co',3.00,NULL,'completed',5,'2025-12-16 08:54:02','2025-12-16 08:54:02'),(199,'TM95','2025-12-17','Cloudflare - Private',30,NULL,'9809254104','xpradiplc@gmail.com',45.00,NULL,'completed',5,'2025-12-17 02:31:21','2025-12-17 02:31:21'),(200,'TM96','2025-12-17','Cloudflare - Private',30,NULL,'9809254104','xpradiplc@gmail.com',222.00,NULL,'completed',5,'2025-12-17 02:31:38','2025-12-17 02:31:38'),(201,'TM97','2025-12-17','Cloudflare - Private',30,NULL,'9809254104','xpradiplc@gmail.com',28.00,NULL,'completed',5,'2025-12-17 02:33:51','2025-12-17 02:33:51'),(202,'TM98','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',55.00,NULL,'completed',5,'2025-12-17 02:34:53','2025-12-17 02:34:53'),(203,'TM99','2025-12-17','Tools product - 1 yeAR',365,NULL,'9809254104','xpradiplc@gmail.com',233.00,NULL,'completed',5,'2025-12-17 02:38:17','2025-12-17 02:38:17'),(204,'TM100','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',99.00,NULL,'completed',5,'2025-12-17 02:44:42','2025-12-17 02:44:42'),(205,'TM101','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',9999.00,NULL,'completed',5,'2025-12-17 02:44:51','2025-12-17 02:44:51'),(206,'TM102','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 02:45:55','2025-12-17 02:45:55'),(207,'TM103','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',23.00,NULL,'completed',5,'2025-12-17 02:51:20','2025-12-17 02:51:20'),(208,'TM104','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 02:52:46','2025-12-17 02:52:46'),(209,'TM105','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',12.00,NULL,'completed',5,'2025-12-17 02:53:13','2025-12-17 02:53:13'),(210,'TM106','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',12.00,NULL,'completed',5,'2025-12-17 02:53:19','2025-12-17 02:53:19'),(211,'TM107','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',232.00,NULL,'completed',5,'2025-12-17 02:59:44','2025-12-17 02:59:44'),(212,'TM108','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',23.00,NULL,'completed',5,'2025-12-17 03:12:10','2025-12-17 03:12:10'),(213,'TM109','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',23.00,NULL,'completed',5,'2025-12-17 03:12:46','2025-12-17 03:12:46'),(214,'TM110','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',223.00,NULL,'completed',5,'2025-12-17 03:13:15','2025-12-17 03:13:15'),(215,'TM111','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',34.00,NULL,'completed',5,'2025-12-17 03:13:25','2025-12-17 03:13:25'),(216,'TM112','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',45.00,NULL,'completed',5,'2025-12-17 03:26:18','2025-12-17 03:26:18'),(217,'TM113','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',23.00,NULL,'completed',5,'2025-12-17 03:26:51','2025-12-17 03:26:51'),(218,'TM114','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',244.00,NULL,'completed',5,'2025-12-17 03:27:10','2025-12-17 03:27:10'),(219,'TM115','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',24.00,NULL,'completed',5,'2025-12-17 03:31:41','2025-12-17 03:31:41'),(220,'TM116','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',45.00,NULL,'completed',5,'2025-12-17 03:36:22','2025-12-17 03:36:22'),(221,'TM117','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',67.00,NULL,'completed',5,'2025-12-17 03:53:43','2025-12-17 03:53:43'),(222,'TM118','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',11.00,NULL,'completed',5,'2025-12-17 03:55:02','2025-12-17 03:55:02'),(223,'TM119','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',45.00,NULL,'completed',5,'2025-12-17 03:55:38','2025-12-17 03:55:38'),(224,'TM120','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',34.00,NULL,'completed',5,'2025-12-17 04:26:55','2025-12-17 04:26:55'),(225,'TM121','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',45.00,NULL,'completed',5,'2025-12-17 04:27:38','2025-12-17 04:27:38'),(226,'TM122','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',45.00,NULL,'completed',5,'2025-12-17 04:32:19','2025-12-17 04:32:19'),(227,'TM123','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',66.00,NULL,'completed',5,'2025-12-17 04:32:49','2025-12-17 04:32:49'),(228,'TM124','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',20.00,NULL,'completed',5,'2025-12-17 04:33:14','2025-12-17 04:33:14'),(229,'TM125','2025-12-17','iCloud - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',66.00,NULL,'completed',5,'2025-12-17 04:33:41','2025-12-17 04:33:41'),(230,'TM126','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',34.00,NULL,'completed',5,'2025-12-17 04:34:12','2025-12-17 04:34:12'),(231,'TM127','2025-12-17','iCloud - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',45.00,NULL,'completed',5,'2025-12-17 04:34:33','2025-12-17 04:34:33'),(232,'TM128','2025-12-17','Cloudflare - Private',30,'remarks','9809254104','xpradiplc@gmail.com',55.00,NULL,'completed',5,'2025-12-17 05:34:24','2025-12-17 05:34:24'),(233,'TM129','2025-12-17','Perplexity AI - 1 Year',365,NULL,'9809241104',NULL,22.00,NULL,'completed',5,'2025-12-17 05:37:36','2025-12-17 05:37:36'),(234,'TM130','2025-12-17','Cloudflare - Private',30,NULL,'9809254104','xpradiplc@gmail.com',999999.00,NULL,'completed',5,'2025-12-17 08:21:35','2025-12-17 08:21:35'),(235,'TM131','2025-12-17','Internet Download Manager (IDM) - 1 Year',365,'thisisrem','9809254104','xpradiplc@gmail.com',55.00,NULL,'completed',5,'2025-12-17 08:22:35','2025-12-17 08:22:35'),(236,'TM132','2025-12-17','Internet Download Manager (IDM) - 1 Year',365,'eyJpdiI6IjFrNnoxSmNZTEZaUmJXMXBVVkxIdlE9PSIsInZhbHVlIjoiU2ZCN3k5ZjloMUx1enp2bHNRaUJrZz09IiwibWFjIjoiMDg3ODlhMGQ0NzQ2MjVkYmY2MGJmNDkwOWVjNDdlN2YzMjRmODQ1MWE5OGU2OWEzODRlY2ExMjgwMWZmZWQ5OCIsInRhZyI6IiJ9','9809254104','xpradiplc@gmail.com',29.00,NULL,'completed',5,'2025-12-17 08:26:53','2025-12-17 08:26:53'),(237,'TM133','2025-12-17','Internet Download Manager (IDM) - 1 Year',365,'eyJpdiI6IlZmeDFlbEt5NFFMTDk1QXVzQTE3Mnc9PSIsInZhbHVlIjoiU1p3MFd3N1BIRTBNRGtCdWpONkNldz09IiwibWFjIjoiN2VkYjhkNTZhYjEwYjFiMmYwODI2OTMzZjUxYWQ3ZDZkM2VhYjY0NGY5MTc1MzJiNzcxNzBmYThjZDcwMGQ1NSIsInRhZyI6IiJ9','9809254104','xpradiplc@gmail.com',44.00,NULL,'completed',5,'2025-12-17 08:28:07','2025-12-17 08:28:07'),(238,'TM134','2025-12-17','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',344.00,NULL,'completed',5,'2025-12-17 10:17:51','2025-12-17 10:17:51'),(239,'TM135','2025-12-17','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',44.00,NULL,'completed',5,'2025-12-17 10:35:55','2025-12-17 10:35:55'),(240,'TM136','2025-12-17','Perplexity AI - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',66.00,NULL,'completed',5,'2025-12-17 10:50:55','2025-12-17 10:50:55'),(241,'TM137','2025-12-17','Tools product - 1 yeAR',365,'eyJpdiI6Im1OMU8vT2xET1NSeFcvd1JzMFRYRFE9PSIsInZhbHVlIjoiM1NKbEsyZWhLTDRxWHR3ZGZ4dTJpdz09IiwibWFjIjoiYjkyY2Y5OTU5NjEyYzM1NDMxNTcwZjFhMDE0MjUyNjYzY2MxZDg5NDIwZTZkMWM0MDliNmQzMDg5NzAxOWI1YSIsInRhZyI6IiJ9','9809254104','xpradiplc@gmail.com',223.00,NULL,'completed',5,'2025-12-17 11:15:00','2025-12-17 11:15:00'),(242,'TM138','2025-12-17','shetsp - ss2',NULL,NULL,'9809254104','xpradiplc@gmail.com',3.00,NULL,'completed',5,'2025-12-17 11:16:13','2025-12-17 11:16:13'),(243,'TM139','2025-12-17','shetsp - ss',1,NULL,'9809254104','xpradiplc@gmail.com',3.00,NULL,'completed',5,'2025-12-17 11:29:57','2025-12-17 11:29:57'),(244,'TM140','2025-12-18','Autodesk - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 14:27:38','2025-12-17 14:27:38'),(245,'TM141','2025-12-18','Microsoft 365 Personal - 1 Month Individual',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 14:27:51','2025-12-17 14:27:51'),(246,'TM142','2025-12-18','Autodesk - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 14:28:10','2025-12-17 14:28:10'),(247,'TM143','2025-12-18','Autodesk - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 14:28:21','2025-12-17 14:28:21'),(248,'TM144','2025-12-18','Microsoft 365 Personal - 1 Year Individual',365,NULL,'9809254104','xpradiplc@gmail.com',66.00,NULL,'completed',5,'2025-12-17 14:29:22','2025-12-17 14:29:22'),(249,'TM145','2025-12-18','Microsoft 365 Personal - 1 Year Individual',365,NULL,'9809254104','xpradiplc@gmail.com',559.00,NULL,'completed',5,'2025-12-17 14:36:54','2025-12-17 14:36:54'),(250,'TM146','2025-12-18','Microsoft 365 Personal - 1 Year Individual',365,NULL,'9809254104','xpradiplc@gmail.com',44.00,NULL,'completed',5,'2025-12-17 14:43:57','2025-12-17 14:43:57'),(251,'TM147','2025-12-18','Microsoft 365 Personal - 1 Year Individual',365,NULL,'9809254104','xpradiplc@gmail.com',2222.00,NULL,'completed',5,'2025-12-17 14:47:17','2025-12-17 14:47:17'),(252,'TM148','2025-12-18','Youtube - 2 Year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 14:50:12','2025-12-17 14:50:12'),(253,'TM149','2025-12-18','Zip - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',1.00,NULL,'completed',5,'2025-12-17 14:51:23','2025-12-17 14:51:23'),(254,'TM150','2025-12-18','Zip - 2 Year',700,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 14:51:39','2025-12-17 14:51:39'),(255,'TM151','2025-12-18','Autodesk - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 15:19:36','2025-12-17 15:19:36'),(256,'TM152','2025-12-18','Autodesk - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 15:20:01','2025-12-17 15:20:01'),(257,'TM153','2025-12-18','Autodesk - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',2222.00,NULL,'completed',5,'2025-12-17 15:20:25','2025-12-17 15:20:25'),(258,'TM154','2025-12-18','Microsoft 365 Personal - 1 Month Individual',30,NULL,'9809254104','xpradiplc@gmail.com',22.00,NULL,'completed',5,'2025-12-17 15:35:49','2025-12-17 15:35:49'),(259,'TM155','2025-12-18','Microsoft 365 Personal - 1 Year Individual',365,NULL,'9809254104','xpradiplc@gmail.com',2.00,NULL,'completed',5,'2025-12-17 15:36:08','2025-12-17 15:36:08'),(260,'TM156','2025-12-18','Autodesk - 1 Month',30,NULL,'9809254104','xpradiplc@gmail.com',55.00,NULL,'completed',5,'2025-12-17 15:36:30','2025-12-17 15:36:30'),(261,'TM157','2025-12-18','Autodesk - 1 Year',365,NULL,'9809254104','xpradiplc@gmail.com',6.00,NULL,'completed',5,'2025-12-17 15:36:43','2025-12-17 15:36:43'),(262,'TM158','2025-12-18','Microsoft 365 Personal - 1 Year Individual',365,NULL,'9809254104','xpradiplc@gmail.com',66.00,NULL,'completed',5,'2025-12-17 15:38:59','2025-12-17 15:38:59'),(263,'TM159','2025-12-18','Microsoft 365 Personal - 1 Year Individual',365,NULL,'9809254104','xpradiplc@gmail.com',555.00,NULL,'completed',5,'2025-12-17 15:46:58','2025-12-17 15:46:58'),(264,'TM160','2026-01-05','Surfshark VPN - 2 Year',850,NULL,'+977 981-8977436','abhayshah19980803@gmail.com',9500.00,NULL,'completed',5,'2026-01-05 09:24:05','2026-01-05 09:24:05'),(265,'TM161','2026-01-06','iCloud - 1 Month',30,'eyJpdiI6IkYyOVJsam9zMzdJUkpkNzN6Nk94Y1E9PSIsInZhbHVlIjoiME1YVWtZbEhZRmhqSGZsV1Y3MjlqTnovWGhMRHlOalh5cFdCNGFnVXFZR2xLNVVjQTlJN1RIUU9LdEczTXNwd1JGYVk0emxSRkxKTjJRK1ZXeU5rcSs1RlJ3Z1I1ak0wdkl6N3h2T1F3ZmJmMGpuaThQMFRXZ3ZjUmdZT1JhVVNzK1RhZ05TMDl6WkQ1RlpkQVNJTEhGdzNpSnlZSXVQVmx5RGRVQzA5VVRZTC9HcjdCc21WZmlXeEpNclJuOEl2ZkVqMlhjT0hnS3RBZlh6WmgyL0Fpbno5NSthdFZlY2VTaHNHTTBCNk1NSU9DQTNkaGhUZE9Vak9sWUkyeUtGbiIsIm1hYyI6IjFjZjI5OWI4MGYzMzUyOTA2MzRmOGM2YjY4NmU0ODBmYmE1YjQ1MGM3MTVhMDY0YWI1Mjc5M2Y3NzlmNGM4NjYiLCJ0YWciOiIifQ==','9809254104','xpradiplc@gmail.com',33.00,NULL,'completed',5,'2026-01-06 07:45:56','2026-01-06 07:45:56');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('60dRYclPYZXar5IQJpST6hWzo4aOmc1skqicIHqP',5,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoibTF6MjdvSm9STW1tYTE4Ym5jcThOQjNlZmloNldIQTM2eDRVcnpRZiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvc2hlZXQvcHJvZHVjdHMvMjEvZW50cmllcyI7czo1OiJyb3V0ZSI7czoxOToic2hlZXQuZW50cmllcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjU7fQ==',1766007662),('ljHsviv7vwoNq6A9ORNyAxgjlV9DZr4QAGBWoKYL',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYVZRNlQzWWYxTEFjUExmd1FNNkRUbzVQaFFYU1pOa3Y4cVlrZ3E1cSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=',1767027128);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (3,'work_schedule_table','[[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"],[\"Sunday\",\"Sunday\",\"Sunday\",\"Sunday\"]]','2025-12-02 09:46:42','2025-12-02 12:08:38'),(4,'work_schedule_rules','[\"1.ww\",\"2.ww\"]','2025-12-02 10:02:10','2025-12-02 10:02:10'),(5,'login_content','{\"badge\":\"Trusted By 25,000+ Customers Worldwide.\",\"brand_accent\":\"LOGIN\",\"headline_prefix\":\"#1 Tools Provider\",\"headline_accent\":\"in Nepal\",\"headline_suffix\":\"with affordable pricing for all.\",\"lead\":\"Toolsmandu is a Nepal-based digital platform focused on providing digital tools, technology, and software licensing services more accessible to individuals and businesses at affordable pricing.\\r\\n<br><br>\\r\\nToolsmandu Provides Genuine Digital Subscriptions At Best Price. Serving Nepalese Market Since 2021 - Trusted By 25,000+ Customers Worldwide.\",\"perks\":[\"Instant Access\",\"Top Customer Support\",\"Affordable Price\"],\"card_title\":\"Sign in to continue\",\"logo_path\":\"login\\/treKKCI6Yu34rZkgRABbYoMMoVe3aJMtZSm5zUyY.png\"}','2025-12-12 00:54:27','2025-12-12 11:12:23');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_keys`
--

DROP TABLE IF EXISTS `stock_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `activation_key` varchar(255) NOT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `viewed_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `viewed_by_pin_name` varchar(255) DEFAULT NULL,
  `viewed_remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_keys_activation_key_unique` (`activation_key`),
  KEY `stock_keys_product_id_foreign` (`product_id`),
  KEY `stock_keys_viewed_by_user_id_foreign` (`viewed_by_user_id`),
  CONSTRAINT `stock_keys_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_keys_viewed_by_user_id_foreign` FOREIGN KEY (`viewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_keys`
--

LOCK TABLES `stock_keys` WRITE;
/*!40000 ALTER TABLE `stock_keys` DISABLE KEYS */;
INSERT INTO `stock_keys` VALUES (17,11,'eyJpdiI6IjBVekFtN3NrREtLakZKdU1pWDYvenc9PSIsInZhbHVlIjoibm9UdWYxeHJ1cW90NXh5bHRZZ2kwZmFsZTB3eTlqMXVzVzgzTm10WU5xbz0iLCJtYWMiOiJhZDcyYWFhMjA3ZTIxZDc5YWYyMDFkMThhYzM4NmI4YjIyY2YwM2E3YzY3MjExMjc2NzdjMTIzYWQ3ODEyODVmIiwidGFnIjoiIn0=','2025-12-16 05:52:06',5,NULL,'ww','2025-12-16 05:50:38','2025-12-16 05:52:06');
/*!40000 ALTER TABLE `stock_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_pins`
--

DROP TABLE IF EXISTS `stock_pins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_pins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `pin_encrypted` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_pins_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_pins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_pins`
--

LOCK TABLES `stock_pins` WRITE;
/*!40000 ALTER TABLE `stock_pins` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_pins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_completions`
--

DROP TABLE IF EXISTS `task_completions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_completions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `completed_on` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_completions_task_id_user_id_completed_on_unique` (`task_id`,`user_id`,`completed_on`),
  KEY `task_completions_user_id_foreign` (`user_id`),
  CONSTRAINT `task_completions_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_completions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_completions`
--

LOCK TABLES `task_completions` WRITE;
/*!40000 ALTER TABLE `task_completions` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_completions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_user_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `recurrence_type` varchar(255) NOT NULL DEFAULT 'once',
  `custom_weekdays` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_weekdays`)),
  `custom_interval_days` int(10) unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_created_by_foreign` (`created_by`),
  KEY `tasks_assigned_user_id_recurrence_type_index` (`assigned_user_id`,`recurrence_type`),
  CONSTRAINT `tasks_assigned_user_id_foreign` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
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
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(32) NOT NULL DEFAULT 'employee',
  `remember_token` varchar(100) DEFAULT NULL,
  `stock_pin_hash` varchar(255) DEFAULT NULL,
  `stock_pin_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (5,'Pradip','toolsmandu@gmail.com',NULL,'$2y$12$8dF6Lk4ReuCHwxzWZrRIgeTKmM1qSKw/QC4qnUJofwyQ60nJJ2THq','admin',NULL,NULL,NULL,'2025-11-02 20:29:12','2025-11-20 21:01:30'),(6,'Keshab','keshab@toolsmandu.com',NULL,'$2y$12$aUBccglj6nJka/5S/JN.s.SBE3DkDdpXiVJ2egxpTQLC4k8gbyEtm','employee',NULL,NULL,NULL,'2025-11-02 20:59:45','2025-11-02 21:19:08'),(7,'Prabesh','prabesh@toolsmandu.com',NULL,'$2y$12$MyZLyH7s9vzxWqw2wr6.hu66rtRRgzN6LQKTq/ts6ufsNkzykdGGC','employee',NULL,NULL,NULL,'2025-11-02 21:00:10','2025-11-02 21:00:10'),(8,'Prakash','prakash@toolsmandu.com',NULL,'$2y$12$pPvxOfCiUJUrjuGdnEc6Aeeic5gsrBy8h5oWFAMb7ReiofnWPOKVq','employee',NULL,NULL,NULL,'2025-11-02 21:00:32','2025-11-02 21:00:32');
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

-- Dump completed on 2026-01-06 20:30:45
