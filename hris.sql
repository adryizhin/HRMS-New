-- HRMS database snapshot: 2026-10-16 12:26:31 UTC
-- Import into an EMPTY database selected in phpMyAdmin, or: mysql -u root -p hris < hris.sql
-- Includes private system records. Copy uploads/ separately for attachments and photos.
-- InnoDB, utf8mb4_general_ci; no DROP TABLE, CREATE DATABASE, USE or DEFINER directives.


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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `shift` enum('morning','night') NOT NULL,
  `login_time` datetime NOT NULL,
  `status` enum('present','late','absent') NOT NULL,
  `late_minutes` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_shift_date` (`user_id`,`attendance_date`,`shift`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `attendance_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24610 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (1,11,'2026-07-02','morning','2026-07-02 03:27:38','present',0,'2026-07-01 19:27:38');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (3,11,'2026-08-06','morning','2026-08-06 14:51:24','absent',0,'2026-08-06 06:51:24');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4,11,'2026-08-07','morning','2026-08-07 01:42:19','present',0,'2026-08-06 17:42:19');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (5,11,'2026-08-21','morning','2026-08-21 01:45:22','present',0,'2026-08-20 17:45:22');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (6,12,'2026-08-21','morning','2026-08-21 01:46:19','present',0,'2026-08-20 17:46:19');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (7,11,'2026-09-26','morning','2026-09-26 00:09:11','present',0,'2026-09-25 16:09:11');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (12,12,'2026-09-26','morning','2026-09-26 14:17:49','late',0,'2026-09-26 06:17:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (21,13,'2026-09-26','morning','2026-09-26 23:15:58','absent',0,'2026-09-26 15:15:58');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (29,12,'2026-09-27','morning','2026-09-27 00:00:23','present',0,'2026-09-26 16:00:23');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (30,11,'2026-09-27','morning','2026-09-27 00:02:09','present',0,'2026-09-26 16:02:09');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (31,13,'2026-09-27','morning','2026-09-27 00:07:39','present',0,'2026-09-26 16:07:39');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (33,1,'2026-04-23','morning','2026-04-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (34,1,'2026-04-24','morning','2026-04-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (35,1,'2026-04-27','morning','2026-04-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (36,1,'2026-04-28','morning','2026-04-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (37,1,'2026-04-29','morning','2026-04-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (38,1,'2026-04-30','morning','2026-04-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (40,1,'2026-05-04','morning','2026-05-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (41,1,'2026-05-05','morning','2026-05-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (42,1,'2026-05-06','morning','2026-05-06 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (43,1,'2026-05-07','morning','2026-05-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (44,1,'2026-05-08','morning','2026-05-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (45,1,'2026-05-11','morning','2026-05-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (46,1,'2026-05-12','morning','2026-05-12 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (47,1,'2026-05-13','morning','2026-05-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (48,1,'2026-05-14','morning','2026-05-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (49,1,'2026-05-15','morning','2026-05-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (50,1,'2026-05-18','morning','2026-05-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (51,1,'2026-05-19','morning','2026-05-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (52,1,'2026-05-20','morning','2026-05-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (53,1,'2026-05-21','morning','2026-05-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (54,1,'2026-05-22','morning','2026-05-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (55,1,'2026-05-25','morning','2026-05-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (56,1,'2026-05-26','morning','2026-05-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (58,1,'2026-05-28','morning','2026-05-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (59,1,'2026-05-29','morning','2026-05-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (60,1,'2026-06-01','morning','2026-06-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (61,1,'2026-06-02','morning','2026-06-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (62,1,'2026-06-03','morning','2026-06-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (63,1,'2026-06-04','morning','2026-06-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (64,1,'2026-06-05','morning','2026-06-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (65,1,'2026-06-08','morning','2026-06-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (66,1,'2026-06-09','morning','2026-06-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (67,1,'2026-06-10','morning','2026-06-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (68,1,'2026-06-11','morning','2026-06-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (70,1,'2026-06-15','morning','2026-06-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (71,1,'2026-06-16','morning','2026-06-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (72,1,'2026-06-17','morning','2026-06-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (73,1,'2026-06-18','morning','2026-06-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (74,1,'2026-06-19','morning','2026-06-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (75,1,'2026-06-22','morning','2026-06-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (76,1,'2026-06-23','morning','2026-06-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (77,1,'2026-06-24','morning','2026-06-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (78,1,'2026-06-25','morning','2026-06-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (79,1,'2026-06-26','morning','2026-06-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (80,1,'2026-06-29','morning','2026-06-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (81,1,'2026-06-30','morning','2026-06-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (82,1,'2026-07-01','morning','2026-07-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (83,1,'2026-07-02','morning','2026-07-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (84,1,'2026-07-03','morning','2026-07-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (85,1,'2026-07-06','morning','2026-07-06 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (86,1,'2026-07-07','morning','2026-07-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (87,1,'2026-07-08','morning','2026-07-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (88,1,'2026-07-09','morning','2026-07-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (89,1,'2026-07-10','morning','2026-07-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (90,1,'2026-07-13','morning','2026-07-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (91,1,'2026-07-14','morning','2026-07-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (92,1,'2026-07-15','morning','2026-07-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (93,1,'2026-07-16','morning','2026-07-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (94,1,'2026-07-17','morning','2026-07-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (95,1,'2026-07-20','morning','2026-07-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (96,1,'2026-07-21','morning','2026-07-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (97,1,'2026-07-22','morning','2026-07-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (98,1,'2026-07-23','morning','2026-07-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (99,1,'2026-07-24','morning','2026-07-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (100,1,'2026-07-27','morning','2026-07-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (101,1,'2026-07-28','morning','2026-07-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (102,1,'2026-07-29','morning','2026-07-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (103,1,'2026-07-30','morning','2026-07-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (104,1,'2026-07-31','morning','2026-07-31 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (105,1,'2026-08-03','morning','2026-08-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (106,1,'2026-08-04','morning','2026-08-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (107,1,'2026-08-05','morning','2026-08-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (108,1,'2026-08-06','morning','2026-08-06 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (109,1,'2026-08-07','morning','2026-08-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (110,1,'2026-08-10','morning','2026-08-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (111,1,'2026-08-11','morning','2026-08-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (112,1,'2026-08-12','morning','2026-08-12 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (113,1,'2026-08-13','morning','2026-08-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (114,1,'2026-08-14','morning','2026-08-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (115,1,'2026-08-17','morning','2026-08-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (116,1,'2026-08-18','morning','2026-08-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (117,1,'2026-08-19','morning','2026-08-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (118,1,'2026-08-20','morning','2026-08-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (120,1,'2026-08-24','morning','2026-08-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (121,1,'2026-08-25','morning','2026-08-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (122,1,'2026-08-26','morning','2026-08-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (123,1,'2026-08-27','morning','2026-08-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (124,1,'2026-08-28','morning','2026-08-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (126,1,'2026-09-01','morning','2026-09-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (127,1,'2026-09-02','morning','2026-09-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (128,1,'2026-09-03','morning','2026-09-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (129,1,'2026-09-04','morning','2026-09-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (130,1,'2026-09-07','morning','2026-09-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (131,1,'2026-09-08','morning','2026-09-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (132,1,'2026-09-09','morning','2026-09-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (133,1,'2026-09-10','morning','2026-09-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (134,1,'2026-09-11','morning','2026-09-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (135,1,'2026-09-14','morning','2026-09-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (136,1,'2026-09-15','morning','2026-09-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (137,1,'2026-09-16','morning','2026-09-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (138,1,'2026-09-17','morning','2026-09-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (139,1,'2026-09-18','morning','2026-09-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (140,1,'2026-09-21','morning','2026-09-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (141,1,'2026-09-22','morning','2026-09-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (142,1,'2026-09-23','morning','2026-09-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (143,1,'2026-09-24','morning','2026-09-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (144,1,'2026-09-25','morning','2026-09-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (145,11,'2026-06-01','morning','2026-06-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (146,11,'2026-06-02','morning','2026-06-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (147,11,'2026-06-03','morning','2026-06-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (148,11,'2026-06-04','morning','2026-06-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (149,11,'2026-06-05','morning','2026-06-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (150,11,'2026-06-08','morning','2026-06-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (151,11,'2026-06-09','morning','2026-06-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (152,11,'2026-06-10','morning','2026-06-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (153,11,'2026-06-11','morning','2026-06-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (155,11,'2026-06-15','morning','2026-06-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (156,11,'2026-06-16','morning','2026-06-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (157,11,'2026-06-17','morning','2026-06-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (158,11,'2026-06-18','morning','2026-06-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (159,11,'2026-06-19','morning','2026-06-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (160,11,'2026-06-22','morning','2026-06-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (161,11,'2026-06-23','morning','2026-06-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (162,11,'2026-06-24','morning','2026-06-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (163,11,'2026-06-25','morning','2026-06-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (164,11,'2026-06-26','morning','2026-06-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (165,11,'2026-06-29','morning','2026-06-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (166,11,'2026-06-30','morning','2026-06-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (167,11,'2026-07-01','morning','2026-07-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (168,11,'2026-07-03','morning','2026-07-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (169,11,'2026-07-06','morning','2026-07-06 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (170,11,'2026-07-07','morning','2026-07-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (171,11,'2026-07-08','morning','2026-07-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (172,11,'2026-07-09','morning','2026-07-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (173,11,'2026-07-10','morning','2026-07-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (174,11,'2026-07-13','morning','2026-07-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (175,11,'2026-07-14','morning','2026-07-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (176,11,'2026-07-15','morning','2026-07-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (177,11,'2026-07-16','morning','2026-07-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (178,11,'2026-07-17','morning','2026-07-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (179,11,'2026-07-20','morning','2026-07-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (180,11,'2026-07-21','morning','2026-07-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (181,11,'2026-07-22','morning','2026-07-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (182,11,'2026-07-23','morning','2026-07-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (183,11,'2026-07-24','morning','2026-07-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (184,11,'2026-07-27','morning','2026-07-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (185,11,'2026-07-28','morning','2026-07-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (186,11,'2026-07-29','morning','2026-07-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (187,11,'2026-07-30','morning','2026-07-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (188,11,'2026-07-31','morning','2026-07-31 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (189,11,'2026-08-03','morning','2026-08-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (190,11,'2026-08-04','morning','2026-08-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (191,11,'2026-08-05','morning','2026-08-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (192,11,'2026-08-10','morning','2026-08-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (193,11,'2026-08-11','morning','2026-08-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (194,11,'2026-08-12','morning','2026-08-12 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (195,11,'2026-08-13','morning','2026-08-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (196,11,'2026-08-14','morning','2026-08-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (197,11,'2026-08-17','morning','2026-08-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (198,11,'2026-08-18','morning','2026-08-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (199,11,'2026-08-19','morning','2026-08-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (200,11,'2026-08-20','morning','2026-08-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (201,11,'2026-08-24','morning','2026-08-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (202,11,'2026-08-25','morning','2026-08-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (203,11,'2026-08-26','morning','2026-08-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (204,11,'2026-08-27','morning','2026-08-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (205,11,'2026-08-28','morning','2026-08-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (207,11,'2026-09-01','morning','2026-09-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (208,11,'2026-09-02','morning','2026-09-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (209,11,'2026-09-03','morning','2026-09-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (210,11,'2026-09-04','morning','2026-09-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (211,11,'2026-09-07','morning','2026-09-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (212,11,'2026-09-08','morning','2026-09-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (213,11,'2026-09-09','morning','2026-09-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (214,11,'2026-09-10','morning','2026-09-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (215,11,'2026-09-11','morning','2026-09-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (216,11,'2026-09-14','morning','2026-09-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (217,11,'2026-09-15','morning','2026-09-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (218,11,'2026-09-16','morning','2026-09-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (219,11,'2026-09-17','morning','2026-09-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (220,11,'2026-09-18','morning','2026-09-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (221,11,'2026-09-21','morning','2026-09-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (222,11,'2026-09-22','morning','2026-09-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (223,11,'2026-09-23','morning','2026-09-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (224,11,'2026-09-24','morning','2026-09-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (225,11,'2026-09-25','morning','2026-09-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (226,12,'2026-06-01','morning','2026-06-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (227,12,'2026-06-02','morning','2026-06-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (228,12,'2026-06-03','morning','2026-06-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (229,12,'2026-06-04','morning','2026-06-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (230,12,'2026-06-05','morning','2026-06-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (231,12,'2026-06-08','morning','2026-06-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (232,12,'2026-06-09','morning','2026-06-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (233,12,'2026-06-10','morning','2026-06-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (234,12,'2026-06-11','morning','2026-06-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (236,12,'2026-06-15','morning','2026-06-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (237,12,'2026-06-16','morning','2026-06-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (238,12,'2026-06-17','morning','2026-06-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (239,12,'2026-06-18','morning','2026-06-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (240,12,'2026-06-19','morning','2026-06-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (241,12,'2026-06-22','morning','2026-06-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (242,12,'2026-06-23','morning','2026-06-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (243,12,'2026-06-24','morning','2026-06-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (244,12,'2026-06-25','morning','2026-06-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (245,12,'2026-06-26','morning','2026-06-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (246,12,'2026-06-29','morning','2026-06-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (247,12,'2026-06-30','morning','2026-06-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (248,12,'2026-07-01','morning','2026-07-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (249,12,'2026-07-02','morning','2026-07-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (250,12,'2026-07-03','morning','2026-07-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (251,12,'2026-07-06','morning','2026-07-06 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (252,12,'2026-07-07','morning','2026-07-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (253,12,'2026-07-08','morning','2026-07-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (254,12,'2026-07-09','morning','2026-07-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (255,12,'2026-07-10','morning','2026-07-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (256,12,'2026-07-13','morning','2026-07-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (257,12,'2026-07-14','morning','2026-07-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (258,12,'2026-07-15','morning','2026-07-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (259,12,'2026-07-16','morning','2026-07-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (260,12,'2026-07-17','morning','2026-07-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (261,12,'2026-07-20','morning','2026-07-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (262,12,'2026-07-21','morning','2026-07-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (263,12,'2026-07-22','morning','2026-07-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (264,12,'2026-07-23','morning','2026-07-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (265,12,'2026-07-24','morning','2026-07-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (266,12,'2026-07-27','morning','2026-07-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (267,12,'2026-07-28','morning','2026-07-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (268,12,'2026-07-29','morning','2026-07-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (269,12,'2026-07-30','morning','2026-07-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (270,12,'2026-07-31','morning','2026-07-31 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (271,12,'2026-08-03','morning','2026-08-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (272,12,'2026-08-04','morning','2026-08-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (273,12,'2026-08-05','morning','2026-08-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (274,12,'2026-08-06','morning','2026-08-06 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (275,12,'2026-08-07','morning','2026-08-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (276,12,'2026-08-10','morning','2026-08-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (277,12,'2026-08-11','morning','2026-08-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (278,12,'2026-08-12','morning','2026-08-12 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (279,12,'2026-08-13','morning','2026-08-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (280,12,'2026-08-14','morning','2026-08-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (281,12,'2026-08-17','morning','2026-08-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (282,12,'2026-08-18','morning','2026-08-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (283,12,'2026-08-19','morning','2026-08-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (284,12,'2026-08-20','morning','2026-08-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (285,12,'2026-08-24','morning','2026-08-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (286,12,'2026-08-25','morning','2026-08-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (287,12,'2026-08-26','morning','2026-08-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (288,12,'2026-08-27','morning','2026-08-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (289,12,'2026-08-28','morning','2026-08-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (291,12,'2026-09-01','morning','2026-09-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (292,12,'2026-09-02','morning','2026-09-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (293,12,'2026-09-03','morning','2026-09-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (294,12,'2026-09-04','morning','2026-09-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (295,12,'2026-09-07','morning','2026-09-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (296,12,'2026-09-08','morning','2026-09-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (297,12,'2026-09-09','morning','2026-09-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (298,12,'2026-09-10','morning','2026-09-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (299,12,'2026-09-11','morning','2026-09-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (300,12,'2026-09-14','morning','2026-09-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (301,12,'2026-09-15','morning','2026-09-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (302,12,'2026-09-16','morning','2026-09-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (303,12,'2026-09-17','morning','2026-09-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (304,12,'2026-09-18','morning','2026-09-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (305,12,'2026-09-21','morning','2026-09-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (306,12,'2026-09-22','morning','2026-09-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (307,12,'2026-09-23','morning','2026-09-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (308,12,'2026-09-24','morning','2026-09-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (309,12,'2026-09-25','morning','2026-09-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (310,13,'2026-06-01','morning','2026-06-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (311,13,'2026-06-02','morning','2026-06-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (312,13,'2026-06-03','morning','2026-06-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (313,13,'2026-06-04','morning','2026-06-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (314,13,'2026-06-05','morning','2026-06-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (315,13,'2026-06-08','morning','2026-06-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (316,13,'2026-06-09','morning','2026-06-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (317,13,'2026-06-10','morning','2026-06-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (318,13,'2026-06-11','morning','2026-06-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (320,13,'2026-06-15','morning','2026-06-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (321,13,'2026-06-16','morning','2026-06-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (322,13,'2026-06-17','morning','2026-06-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (323,13,'2026-06-18','morning','2026-06-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (324,13,'2026-06-19','morning','2026-06-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (325,13,'2026-06-22','morning','2026-06-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (326,13,'2026-06-23','morning','2026-06-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (327,13,'2026-06-24','morning','2026-06-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (328,13,'2026-06-25','morning','2026-06-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (329,13,'2026-06-26','morning','2026-06-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (330,13,'2026-06-29','morning','2026-06-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (331,13,'2026-06-30','morning','2026-06-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (332,13,'2026-07-01','morning','2026-07-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (333,13,'2026-07-02','morning','2026-07-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (334,13,'2026-07-03','morning','2026-07-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (335,13,'2026-07-06','morning','2026-07-06 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (336,13,'2026-07-07','morning','2026-07-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (337,13,'2026-07-08','morning','2026-07-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (338,13,'2026-07-09','morning','2026-07-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (339,13,'2026-07-10','morning','2026-07-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (340,13,'2026-07-13','morning','2026-07-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (341,13,'2026-07-14','morning','2026-07-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (342,13,'2026-07-15','morning','2026-07-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (343,13,'2026-07-16','morning','2026-07-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (344,13,'2026-07-17','morning','2026-07-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (345,13,'2026-07-20','morning','2026-07-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (346,13,'2026-07-21','morning','2026-07-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (347,13,'2026-07-22','morning','2026-07-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (348,13,'2026-07-23','morning','2026-07-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (349,13,'2026-07-24','morning','2026-07-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (350,13,'2026-07-27','morning','2026-07-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (351,13,'2026-07-28','morning','2026-07-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (352,13,'2026-07-29','morning','2026-07-29 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (353,13,'2026-07-30','morning','2026-07-30 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (354,13,'2026-07-31','morning','2026-07-31 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (355,13,'2026-08-03','morning','2026-08-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (356,13,'2026-08-04','morning','2026-08-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (357,13,'2026-08-05','morning','2026-08-05 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (358,13,'2026-08-06','morning','2026-08-06 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (359,13,'2026-08-07','morning','2026-08-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (360,13,'2026-08-10','morning','2026-08-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (361,13,'2026-08-11','morning','2026-08-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (362,13,'2026-08-12','morning','2026-08-12 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (363,13,'2026-08-13','morning','2026-08-13 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (364,13,'2026-08-14','morning','2026-08-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (365,13,'2026-08-17','morning','2026-08-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (366,13,'2026-08-18','morning','2026-08-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (367,13,'2026-08-19','morning','2026-08-19 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (368,13,'2026-08-20','morning','2026-08-20 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (370,13,'2026-08-24','morning','2026-08-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (371,13,'2026-08-25','morning','2026-08-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (372,13,'2026-08-26','morning','2026-08-26 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (373,13,'2026-08-27','morning','2026-08-27 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (374,13,'2026-08-28','morning','2026-08-28 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (376,13,'2026-09-01','morning','2026-09-01 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (377,13,'2026-09-02','morning','2026-09-02 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (378,13,'2026-09-03','morning','2026-09-03 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (379,13,'2026-09-04','morning','2026-09-04 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (380,13,'2026-09-07','morning','2026-09-07 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (381,13,'2026-09-08','morning','2026-09-08 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (382,13,'2026-09-09','morning','2026-09-09 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (383,13,'2026-09-10','morning','2026-09-10 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (384,13,'2026-09-11','morning','2026-09-11 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (385,13,'2026-09-14','morning','2026-09-14 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (386,13,'2026-09-15','morning','2026-09-15 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (387,13,'2026-09-16','morning','2026-09-16 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (388,13,'2026-09-17','morning','2026-09-17 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (389,13,'2026-09-18','morning','2026-09-18 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (390,13,'2026-09-21','morning','2026-09-21 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (391,13,'2026-09-22','morning','2026-09-22 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (392,13,'2026-09-23','morning','2026-09-23 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (393,13,'2026-09-24','morning','2026-09-24 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (394,13,'2026-09-25','morning','2026-09-25 17:00:00','absent',0,'2026-09-26 16:17:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4447,12,'2026-09-28','morning','2026-09-28 08:00:52','present',0,'2026-09-28 00:00:52');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4816,11,'2026-09-28','morning','2026-09-28 15:31:38','late',421,'2026-09-28 07:31:38');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4817,11,'2026-10-08','morning','2026-10-08 21:35:35','absent',0,'2026-10-08 13:35:35');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4819,1,'2026-09-28','morning','2026-09-28 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4820,1,'2026-09-29','morning','2026-09-29 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4821,1,'2026-09-30','morning','2026-09-30 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4822,1,'2026-10-01','morning','2026-10-01 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4823,1,'2026-10-02','morning','2026-10-02 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4824,1,'2026-10-05','morning','2026-10-05 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4825,1,'2026-10-06','morning','2026-10-06 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4826,1,'2026-10-07','morning','2026-10-07 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4827,1,'2026-10-08','morning','2026-10-08 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4828,11,'2026-09-29','morning','2026-09-29 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4829,11,'2026-09-30','morning','2026-09-30 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4830,11,'2026-10-01','morning','2026-10-01 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4831,11,'2026-10-02','morning','2026-10-02 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4832,11,'2026-10-05','morning','2026-10-05 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4833,11,'2026-10-06','morning','2026-10-06 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4834,11,'2026-10-07','morning','2026-10-07 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4835,12,'2026-09-29','morning','2026-09-29 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4836,12,'2026-09-30','morning','2026-09-30 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4837,12,'2026-10-01','morning','2026-10-01 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4838,12,'2026-10-02','morning','2026-10-02 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4839,12,'2026-10-05','morning','2026-10-05 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4840,12,'2026-10-06','morning','2026-10-06 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4841,12,'2026-10-07','morning','2026-10-07 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4842,12,'2026-10-08','morning','2026-10-08 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4843,13,'2026-09-28','morning','2026-09-28 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4844,13,'2026-09-29','morning','2026-09-29 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4845,13,'2026-09-30','morning','2026-09-30 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4846,13,'2026-10-01','morning','2026-10-01 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4847,13,'2026-10-02','morning','2026-10-02 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4848,13,'2026-10-05','morning','2026-10-05 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4849,13,'2026-10-06','morning','2026-10-06 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4850,13,'2026-10-07','morning','2026-10-07 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (4851,13,'2026-10-08','morning','2026-10-08 17:00:00','absent',0,'2026-10-08 13:39:49');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (6841,14,'2026-10-08','morning','2026-10-08 22:24:20','absent',0,'2026-10-08 14:24:20');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (14256,11,'2026-10-09','morning','2026-10-09 20:10:06','absent',0,'2026-10-09 12:10:06');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (14258,1,'2026-10-09','morning','2026-10-09 17:00:00','absent',0,'2026-10-09 12:14:54');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (14259,12,'2026-10-09','morning','2026-10-09 17:00:00','absent',0,'2026-10-09 12:14:54');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (14260,13,'2026-10-09','morning','2026-10-09 17:00:00','absent',0,'2026-10-09 12:14:54');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (14261,14,'2026-10-09','morning','2026-10-09 17:00:00','absent',0,'2026-10-09 12:14:54');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (15053,11,'2026-10-10','morning','2026-10-10 22:40:37','absent',0,'2026-10-10 14:40:37');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (15054,12,'2026-10-10','morning','2026-10-10 22:41:16','absent',0,'2026-10-10 14:41:16');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (15455,1,'2026-10-10','morning','2026-10-10 23:06:01','absent',0,'2026-10-10 15:06:01');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (18234,12,'2026-10-11','morning','2026-10-11 00:13:33','present',0,'2026-10-10 16:13:33');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (18235,1,'2026-10-11','morning','2026-10-11 00:16:18','present',0,'2026-10-10 16:16:18');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (20215,11,'2026-10-11','morning','2026-10-11 00:51:18','present',0,'2026-10-10 16:51:18');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (20220,13,'2026-10-11','morning','2026-10-11 01:22:58','present',0,'2026-10-10 17:22:58');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (21805,11,'2026-10-12','morning','2026-10-12 09:41:26','late',71,'2026-10-12 01:41:26');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (21806,1,'2026-10-12','morning','2026-10-12 09:42:12','late',72,'2026-10-12 01:42:12');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (22597,12,'2026-10-12','morning','2026-10-12 09:48:59','late',78,'2026-10-12 01:48:59');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (23794,13,'2026-10-12','morning','2026-10-12 17:00:00','absent',0,'2026-10-12 09:13:51');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (23795,14,'2026-10-12','morning','2026-10-12 17:00:00','absent',0,'2026-10-12 09:13:51');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24598,11,'2026-10-14','morning','2026-10-14 19:50:41','absent',0,'2026-10-14 11:50:41');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24599,12,'2026-10-14','morning','2026-10-14 19:51:29','absent',0,'2026-10-14 11:51:29');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24600,1,'2026-10-13','morning','2026-10-13 17:00:00','absent',0,'2026-10-14 11:51:40');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24601,1,'2026-10-14','morning','2026-10-14 17:00:00','absent',0,'2026-10-14 11:51:40');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24602,11,'2026-10-13','morning','2026-10-13 17:00:00','absent',0,'2026-10-14 11:51:40');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24603,12,'2026-10-13','morning','2026-10-13 17:00:00','absent',0,'2026-10-14 11:51:40');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24604,13,'2026-10-13','morning','2026-10-13 17:00:00','absent',0,'2026-10-14 11:51:40');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24605,13,'2026-10-14','morning','2026-10-14 17:00:00','absent',0,'2026-10-14 11:51:40');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24606,14,'2026-10-13','morning','2026-10-13 17:00:00','absent',0,'2026-10-14 11:51:40');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24607,14,'2026-10-14','morning','2026-10-14 17:00:00','absent',0,'2026-10-14 11:51:40');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24608,11,'2026-10-16','morning','2026-10-16 13:00:14','late',270,'2026-10-16 05:00:14');
INSERT INTO `attendance` (`id`, `user_id`, `attendance_date`, `shift`, `login_time`, `status`, `late_minutes`, `created_at`) VALUES (24609,12,'2026-10-16','morning','2026-10-16 13:05:02','late',275,'2026-10-16 05:05:02');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','delivered','seen') DEFAULT 'sent',
  `seen_at` datetime DEFAULT NULL,
  `is_seen` tinyint(1) DEFAULT 0,
  `is_delivered` tinyint(1) DEFAULT 0,
  `is_typing` tinyint(1) DEFAULT 0,
  `attachment_path` varchar(80) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_size` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (1,1,0,'hi','2026-05-06 06:32:34','delivered',NULL,0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (2,1,0,'hi','2026-05-06 06:32:35','delivered',NULL,0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (3,1,8,'hi','2026-05-06 06:38:46','seen','2026-05-06 15:44:05',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (4,1,8,'hi','2026-05-06 06:45:32','seen','2026-05-06 15:44:05',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (5,1,8,'hellooo','2026-05-06 06:46:27','seen','2026-05-06 15:44:05',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (6,1,8,'yooohooo','2026-05-06 06:46:33','seen','2026-05-06 15:44:05',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (7,8,1,'Hello Brother!','2026-05-06 06:54:40','seen','2026-05-06 15:32:57',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (8,1,10,'Hello','2026-05-06 07:05:56','seen','2026-05-06 15:35:02',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (9,10,1,'hi','2026-05-06 07:07:35','seen','2026-05-06 15:31:58',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (10,1,10,'hi brother','2026-05-06 07:11:30','seen','2026-05-06 15:35:02',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (11,1,8,'okay','2026-05-06 07:15:14','seen','2026-05-06 15:44:05',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (12,10,1,'yo brother!','2026-05-06 07:22:04','seen','2026-05-06 15:31:58',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (13,1,8,'sent?','2026-05-06 07:33:07','seen','2026-05-06 15:44:05',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (14,10,1,'diwow','2026-05-06 07:35:14','seen','2026-05-06 15:35:25',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (15,1,10,'AHAHAHAHAHA','2026-05-06 07:35:34','seen','2026-05-06 15:35:35',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (16,10,1,'ngek','2026-05-06 07:35:47','seen','2026-05-06 15:35:48',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (17,10,1,'wala?','2026-05-06 07:41:43','seen','2026-05-06 15:41:44',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (18,1,10,'HAHAHAHAHA','2026-05-06 07:41:53','seen','2026-05-06 15:41:54',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (19,8,1,'HAHAHAHAHA','2026-05-07 18:30:08','seen','2026-05-08 02:30:26',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (20,1,8,'mlmllk','2026-05-07 18:30:33','seen','2026-05-08 02:30:34',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (21,11,12,'hoy','2026-06-13 22:24:54','seen','2026-06-14 06:26:52',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (22,12,11,'okay','2026-06-13 22:27:02','seen','2026-07-02 03:29:45',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (23,11,12,'HAHAHAHAHA','2026-10-09 12:15:12','seen','2026-10-09 20:15:26',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (24,12,11,'ok','2026-10-09 12:15:42','seen','2026-10-09 21:10:19',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (25,11,12,'hoy','2026-10-10 14:40:56','seen','2026-10-10 22:41:17',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (26,12,11,'OKAY','2026-10-10 14:48:12','seen','2026-10-10 22:48:15',0,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (27,1,12,'hello brother','2026-10-10 16:10:04','seen','2026-10-11 00:14:14',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (28,12,1,'yo yo yow','2026-10-10 16:14:18','seen','2026-10-11 00:16:21',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (29,1,11,'shet shet nakita ako!','2026-10-10 16:25:26','seen','2026-10-11 00:58:57',1,0,0,'7c061ba56bc7f8e5ab563be024697287.txt','to do HRIS done.txt',1256);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (30,11,1,'goods na boi','2026-10-10 16:59:03','seen','2026-10-11 00:59:12',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (31,1,11,'edi goods na tayo pre HAHAHAHAHA','2026-10-10 16:59:19','seen','2026-10-11 00:59:50',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (32,1,12,'ok','2026-10-10 17:32:34','seen','2026-10-11 01:43:14',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (33,12,1,'yooohooo dito ang tingin1','2026-10-10 17:47:00','seen','2026-10-11 01:49:58',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (34,12,1,'HAHAHAHAHA','2026-10-10 17:47:06','seen','2026-10-11 01:49:58',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (35,1,12,'HAHAHAHAHA NAPAKA ANGAS!','2026-10-10 17:50:07','seen','2026-10-11 01:50:09',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (36,12,1,'sarap palo HAHAHAHA','2026-10-10 17:55:25','seen','2026-10-11 02:00:13',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (37,1,11,'hoy hoy hoy','2026-10-12 05:23:17','seen','2026-10-12 13:25:16',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (38,1,11,'hellooo','2026-10-12 05:23:24','seen','2026-10-12 13:25:16',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (39,1,11,'HAHAHAHAHAHA','2026-10-12 05:23:26','seen','2026-10-12 13:25:16',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (40,1,11,'lala','2026-10-12 05:23:28','seen','2026-10-12 13:25:16',1,0,0,NULL,NULL,NULL);
INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `status`, `seen_at`, `is_seen`, `is_delivered`, `is_typing`, `attachment_path`, `attachment_name`, `attachment_size`) VALUES (41,11,1,'','2026-10-12 12:57:25','sent',NULL,0,0,0,'732d23e1cb7c7a60e1525f4f3f255ee2.jpg','adrian1.jpg',178530);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `departments` (`id`, `name`) VALUES (4,'Human Resource');
INSERT INTO `departments` (`id`, `name`) VALUES (5,'Information Technology');
INSERT INTO `departments` (`id`, `name`) VALUES (6,'Finance');
INSERT INTO `departments` (`id`, `name`) VALUES (7,'Sales');
INSERT INTO `departments` (`id`, `name`) VALUES (8,'Production');
INSERT INTO `departments` (`id`, `name`) VALUES (9,'Intern');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `title` varchar(160) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(120) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('submitted','verified','rejected') NOT NULL DEFAULT 'submitted',
  `hr_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `employee_documents_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_documents_reviewer_fk` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday_imports` (
  `calendar_year` int(11) NOT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`calendar_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `holiday_imports` (`calendar_year`, `imported_at`) VALUES (2026,'2026-10-08 14:20:55');
INSERT INTO `holiday_imports` (`calendar_year`, `imported_at`) VALUES (2027,'2026-10-08 14:20:55');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `holiday_date` date NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`holiday_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-01-01','New Year\'s Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-02-17','Chinese New Year','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-03-20','Eid\'l Fitr','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-04-02','Maundy Thursday','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-04-03','Good Friday','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-04-04','Black Saturday','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-04-09','Araw ng Kagitingan','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-05-01','Labor Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-05-27','Eid\'l Adha','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-06-12','Independence Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-08-21','Ninoy Aquino Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-08-31','National Heroes Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-11-01','All Saints\' Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-11-02','All Souls\' Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-11-30','Bonifacio Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-12-08','Feast of the Immaculate Conception of Mary','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-12-24','Christmas Eve','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-12-25','Christmas Day','2026-09-28 07:32:28','2026-09-28 07:32:28');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-12-30','Rizal Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2026-12-31','Last Day of the Year','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-01-01','New Year\'s Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-02-06','Chinese New Year','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-03-25','Maundy Thursday','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-03-26','Good Friday','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-03-27','Black Saturday','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-04-09','Araw ng Kagitingan','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-05-01','Labor Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-06-12','Independence Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-08-21','Ninoy Aquino Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-08-30','National Heroes Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-11-01','All Saints\' Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-11-02','All Souls\' Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-11-30','Bonifacio Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-12-08','Feast of the Immaculate Conception of Mary','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-12-24','Christmas Eve','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-12-25','Christmas Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-12-30','Rizal Day','2026-10-08 14:20:55','2026-10-08 14:20:55');
INSERT INTO `holidays` (`holiday_date`, `name`, `created_at`, `updated_at`) VALUES ('2027-12-31','New Year\'s Eve','2026-10-08 14:20:55','2026-10-08 14:21:57');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hr_task_inbox` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `hr_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `employee_id` (`employee_id`),
  KEY `hr_id` (`hr_id`),
  CONSTRAINT `hr_task_inbox_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hr_task_inbox_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hr_task_inbox_ibfk_3` FOREIGN KEY (`hr_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_request_days` (
  `request_id` int(11) NOT NULL,
  `leave_date` date NOT NULL,
  PRIMARY KEY (`request_id`,`leave_date`),
  CONSTRAINT `leave_request_days_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `leave_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `hr_id` int(11) NOT NULL,
  `leave_type` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_key` char(64) DEFAULT NULL,
  `days_initialized` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_leave_request_key` (`request_key`),
  KEY `employee_id` (`employee_id`),
  KEY `hr_id` (`hr_id`),
  CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`hr_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `logout_reason` varchar(30) DEFAULT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `last_ping` datetime DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=235 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (1,1,'System Administrator','admin','2026-04-23 06:00:24','2026-04-23 06:01:27',NULL,'f16e602f692129bb517a3f7992fa1f1d32b84037ae4de42d124a29f363f8eb54','2026-04-23 06:00:24',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (3,1,'System Administrator','admin','2026-04-23 06:11:59','2026-04-23 06:13:55',NULL,'e0401f5e0390ea41e4b1885681b8ab0b4ed4f76564e5477eaf3b9b8ef301045b','2026-04-23 06:11:59',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (4,1,'System Administrator','admin','2026-04-23 14:58:26','2026-04-23 15:07:59',NULL,'80e41da44a884081b88d328375c88fba96a9e1dad287743e33c82d3be1509e61','2026-04-23 14:58:26',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (6,1,'System Administrator','admin','2026-04-23 15:24:35','2026-04-23 15:54:09',NULL,'ea9ea463542a01c884665a4484f9514ec3cc15c0fad6c0c6a154185cbcd14e9c','2026-04-23 15:24:35',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (7,1,'System Administrator','admin','2026-04-25 00:57:57','2026-04-25 00:58:40',NULL,'0fa723a437bc09f6728990f322104e04bd78146203801c02d5afec9acf7ac971','2026-04-25 00:57:57',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (9,1,'System Administrator','admin','2026-04-25 01:04:09','2026-04-25 01:04:16',NULL,'a562bda7f7a409b232293fd07f0da58ae696f6503a01bd7c89e5da0cec946af0','2026-04-25 01:04:09',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (11,1,'System Administrator','admin','2026-04-25 01:17:24','2026-04-25 01:19:00',NULL,'8ffc924807463d50a87d9f830af259d46def1316773630a91bffbc684141d622','2026-04-25 01:17:24',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (13,1,'System Administrator','admin','2026-04-25 01:21:24','2026-04-25 01:21:58',NULL,'b2ea2a1dc3f39cd1649c22a0245077aea3fabe9edb7076db0882fdbb1bccff07','2026-04-25 01:21:24',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (14,1,'System Administrator','admin','2026-04-25 01:22:11','2026-04-25 01:44:59',NULL,'8bc720d801d2ab97bb3a8c350d12065aba0d4914d1df16f4ac9c5b359e22766f','2026-04-25 01:22:11',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (20,1,'System Administrator','admin','2026-04-25 01:55:35','2026-04-25 01:56:00',NULL,'6463911351db1de354db62356473804827fafea6f534a70fa8beccbcc9ece7c0','2026-04-25 01:55:35',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (22,1,'System Administrator','admin','2026-04-25 02:02:56','2026-04-25 02:07:14',NULL,'2d0744ba9c6bee4c7a022b2364a6457e367a603f46bd1a4979a0506abdfb99f2','2026-04-25 02:02:56',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (25,1,'System Administrator','admin','2026-04-26 22:12:26','2026-04-26 22:18:52',NULL,'185288f12f377c01d53a8a43dc77e86508c2503524a1539c0abc83f7885ef8ec','2026-04-26 22:12:26',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (30,1,'System Administrator','admin','2026-04-26 22:26:44','2026-04-26 22:26:44',NULL,'288e48a5b71bcf16623e42d67842b391732325f461513dc6637e2e2dd2a3ac3a','2026-04-26 22:26:44',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (32,1,'System Administrator','admin','2026-04-26 22:28:42','2026-04-26 22:28:43',NULL,'a51022fd9be898244c830368d586456e63aaa3559f51cda6ebd3f092394174b6','2026-04-26 22:28:42',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (62,1,'System Administrator','admin','2026-04-26 23:22:47','2026-04-27 00:27:18',NULL,'cb4053a37c03b77e19089e5ff18779fd9f27b54f2fb7be1fe98f75052ef5c39c','2026-04-27 00:27:09',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (65,1,'System Administrator','admin','2026-04-27 01:40:10','2026-04-27 01:40:27',NULL,'d9fcd7939e0a35857a0adf68334d888e340024b54c38010261770a416feabf07','2026-04-27 01:40:26',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (66,1,'System Administrator','admin','2026-04-28 23:19:20','2026-04-28 23:19:55',NULL,'917dc6d40dd047e3302c80987a85e5a7a5cd0b916501018c578b965022d5b244','2026-04-28 23:19:50',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (70,1,'System Administrator','admin','2026-04-29 14:05:13','2026-04-29 14:29:13',NULL,'f8fb68947b36bba18fafd7f91bf1f04b2796a2c1818bc491e1a3d5849cc5a3d3','2026-04-29 14:29:08',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (75,1,'System Administrator','admin','2026-04-29 15:22:31','2026-04-29 15:22:53',NULL,'0f9ca4b87c38d0fa6944e51d9aa44d4d087048af853234b6f2b82b0ad770cae7','2026-04-29 15:22:32',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (76,1,'System Administrator','admin','2026-04-29 15:23:14','2026-04-29 15:31:25',NULL,'932566a7217fab336f8d3e7eeb963ede5dfaba506cf69a3fd2598c08fa4c1100','2026-04-29 15:31:23',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (77,1,'System Administrator','admin','2026-04-29 15:31:42','2026-04-29 15:37:03',NULL,'231e8500e05d4d2ce4bdd289569daceca77c2fb4d73b07ff39018918c2c42b37','2026-04-29 15:36:56',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (80,1,'System Administrator','admin','2026-04-29 15:37:32','2026-04-29 15:39:39',NULL,'4e3eaadb43d70132af029999657e8267f4036b8ebac9286b16e8cf86524a02d3','2026-04-29 15:39:36',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (81,1,'System Administrator','admin','2026-04-29 15:39:54','2026-04-29 15:45:36',NULL,'4808d39ddf542fea0b2cb1ff4d2a1284905215d4e8001057031546ae4f83424b','2026-04-29 15:45:32',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (83,1,'System Administrator','admin','2026-04-29 15:45:57','2026-04-29 15:55:44',NULL,'d9d05beb5754c0a009f49ae33aa0e6b072b3d08ed63065a72f2fb17e6086112b','2026-04-29 15:55:42',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (86,1,'System Administrator','admin','2026-04-30 21:43:59','2026-04-30 22:03:01',NULL,'79990b95645c7ebca2f38383ba1879631bfd569436cfb5fc5683257330a5bf0a','2026-04-30 21:44:21',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (87,1,'System Administrator','admin','2026-04-30 23:06:28','2026-04-30 23:06:56',NULL,'7b17bb6f4442d435fcca3e01db04b46d0eec356c4ccf3a96f6bd3ca0f9a41b18','2026-04-30 23:06:54',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (91,1,'System Administrator','admin','2026-05-01 23:38:35','2026-05-01 23:39:58',NULL,'006cc8d1f434c55c0b057f8e0ce019a8fbebcf842f1ccc879518acd12055abdb','2026-05-01 23:39:57',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (93,1,'System Administrator','admin','2026-05-04 00:20:16','2026-05-04 00:34:21',NULL,'d254cd4cb3224aa9cea90f32aeb8fc1a064cc99928b3bef487b555a0a59ff60d','2026-05-04 00:33:49',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (94,1,'System Administrator','admin','2026-05-04 00:40:02','2026-05-04 01:02:15',NULL,'e6f9d4a46ffa23fd4ff7ae0cbde9b9b6caf8a8418906e5c5edce6a51e18dcd79','2026-05-04 00:40:48',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (95,1,'System Administrator','admin','2026-05-06 13:50:21','2026-05-06 14:37:51',NULL,'29fd85125b402758280a07a9d97df5398d71b2f1cb91f8824ff939c32919266d','2026-05-06 14:37:38',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (96,1,'System Administrator','admin','2026-05-06 14:38:09','2026-05-06 14:47:39',NULL,'170b78ba350923536eb1415cea69d66dbdf73eade0596d698bbf819baf2c15a5','2026-05-06 14:45:26',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (98,1,'System Administrator','admin','2026-05-06 14:55:02','2026-05-06 15:06:04',NULL,'5c04f04b7ea17f79d1a7d89e23a8ce2021cff9d7473f2dc92999369aaf96b074','2026-05-06 15:05:47',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (100,1,'System Administrator','admin','2026-05-06 15:07:46','2026-05-06 15:20:37',NULL,'7cbf86256731b3ac4dcc5c7d90cd1fc0cf6c5f07650f28eb9af6094fccb2060d','2026-05-06 15:18:58',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (102,1,'System Administrator','admin','2026-05-06 15:20:46','2026-05-06 15:57:56',NULL,'fde38d0bf5537d8f30184d74f23bedaf2234b84263e11b68094feb943ee43a75','2026-05-06 15:42:46',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (105,1,'System Administrator','admin','2026-05-08 02:27:40','2026-05-08 02:43:24','timeout','6ffa8b728a8ffdcf993a40c879ba1c64daf931b5863bfde8c46165e6fb2db21e','2026-05-08 02:28:24',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (107,1,'System Administrator','admin','2026-05-31 02:17:39','2026-05-31 02:22:07','logout','ec1eeb94d165223d7199737b5a99032545c75bcc9b6870e668091a115c5303df','2026-05-31 02:22:03',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (108,13,'Pauleen Mae Lugasing','employee','2026-05-31 02:19:45','2026-05-31 02:51:17','timeout','e7ffe52da1262be9f4974bd2b30059a9c90d706c90a490afc01b164048dfb8e7','2026-05-31 02:36:17',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (110,11,'Adrian Consolacion','admin','2026-06-14 05:58:33','2026-06-14 06:19:18','timeout','e3240664a9015663f0ebfb9c49fa64a74b18e2f208f31d72b0bf541b514e3b46','2026-06-14 05:58:33',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (111,11,'Adrian Consolacion','admin','2026-06-14 06:22:11','2026-06-14 06:25:08','logout','aa0987d71413d178f44be4ba28b9cd0210278c5e0abdede2f5ac8dbffce514d8','2026-06-14 06:24:48',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (112,12,'Anthony Hernandez','hr','2026-06-14 06:26:38','2026-06-14 06:30:12','logout','c8d4f8009ce9a29395c57b0b77de64506e3366a115d96e8025b18f357a4d98ff','2026-06-14 06:30:05',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (113,11,'Adrian Consolacion','admin','2026-06-14 06:30:42','2026-06-14 06:31:21','logout','21e5fd422a3e615caca305e2fa73fdf8b9e2f4b14bcb8cd06d7f267aa5f9427e','2026-06-14 06:30:48',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (114,13,'Pauleen Mae Lugasing','employee','2026-06-14 06:31:45','2026-06-14 06:33:05','logout','b8cf6cf4310f1029f059263dd74f5da0ee892b3d5c610042b075b45f54ebe642','2026-06-14 06:32:50',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (119,11,'Adrian Consolacion','admin','2026-08-21 01:45:21','2026-08-21 01:46:09','logout','1b5751fdf147db33427a46c3e07af9449d6f4d86c5043554e7488c822d9e156a','2026-08-21 01:46:04',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (120,12,'Anthony Hernandez','hr','2026-08-21 01:46:19','2026-08-21 02:07:10','timeout','52826018f42d9be7c19df555405b262d70674b045e93b987c0fc2b0d2af0384b','2026-08-21 01:51:54',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (121,11,'Adrian Consolacion','admin','2026-09-26 00:09:11','2026-09-26 00:33:56','timeout','1189c9aeb5c48fe545e2e4d02ea11b407838be389b7a8ea605f6a1cb9cbbfe3e','2026-09-26 00:18:54',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (122,11,'Adrian Consolacion','admin','2026-09-26 12:35:29','2026-09-26 12:51:01','logout','4fbc5c738ba2dcb9dbf11988541ce34f801968a7b87f60ab56d1508710f24b8d','2026-09-26 12:50:57',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (123,11,'Adrian Consolacion','admin','2026-09-26 12:51:15','2026-09-26 13:06:24','timeout','64ef60ae0ad5d72c3b07740333a2486bd1a241da1a0e3b5ac73e4eea0f360a05','2026-09-26 12:51:15',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (124,11,'Adrian Consolacion','admin','2026-09-26 13:38:29','2026-09-26 13:54:00','timeout','97b48ab1e5a66c53fd05325210823a294e561cd3521dbb7d58d1c106fefab1ea','2026-09-26 13:38:50',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (125,11,'Adrian Consolacion','admin','2026-09-26 14:16:55','2026-09-26 14:17:39','logout','7883a0705231c174cbf79e979209fe4c84e76f8408972825716beec7dc83c35f','2026-09-26 14:17:28',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (126,12,'Anthony Hernandez','hr','2026-09-26 14:17:49','2026-09-26 14:33:38','timeout','b510144a51e185270455d8bd3896c0858c1b942beea24fae672184ba9f76994a','2026-09-26 14:18:31',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (127,11,'Adrian Consolacion','admin','2026-09-26 14:50:16','2026-09-26 14:58:33','logout','554e54a7bea36ab65382f8dd28cce592617dba48ffdfee8c40c35f4a4cf21680','2026-09-26 14:58:29',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (128,12,'Anthony Hernandez','hr','2026-09-26 14:58:43','2026-09-26 15:14:18','timeout','fb415e9cf0decb5188b6a37ac86b69b67bab43dce026e732f46c4d718928c147','2026-09-26 14:58:50',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (129,11,'Adrian Consolacion','admin','2026-09-26 15:32:54','2026-09-26 15:41:03','logout','df9264fcf9db13979d8550dda819882b88ab8875d1bdffc4c67b60c799f5ee6f','2026-09-26 15:41:01',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (130,12,'Anthony Hernandez','hr','2026-09-26 15:41:14','2026-09-26 16:05:38','logout','af28352bc74e7d90078c5e9fabb7ca5214d70b05d05bfff73ba1f587a0074c0d','2026-09-26 16:05:32',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (131,11,'Adrian Consolacion','admin','2026-09-26 16:15:46','2026-09-26 16:31:34','timeout','8ae1f2540424d0f8e115aa672f027a1d817ac7b22f0d8b2191c4230ca1599e1e','2026-09-26 16:15:58',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (132,11,'Adrian Consolacion','admin','2026-09-26 16:33:41','2026-09-26 16:33:46','logout','f2019679ab06fc4aa559e79199e804594aac48933dbc10f2afb62db1e9fa0879','2026-09-26 16:33:44',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (134,11,'Adrian Consolacion','admin','2026-09-26 23:07:08','2026-09-26 23:15:46','logout','064044723860a1debfc46b9a8d04953d77cb809a7d75dc0719ed71b66ee18ce7','2026-09-26 23:15:38',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (135,13,'Pauleen Mae Lugasing','employee','2026-09-26 23:15:58','2026-09-26 23:25:06','logout','bc2f30689e6cdc9f3f4b2193f8e35c7c2b345c6e71dd53022384135857d25cd0','2026-09-26 23:24:40',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (136,12,'Anthony Hernandez','hr','2026-09-26 23:25:17','2026-09-26 23:28:16','logout','a67fd6d377df526816b46371bf73a99337a0dbe5cf323572a6357b2131395180','2026-09-26 23:28:07',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (137,13,'Pauleen Mae Lugasing','employee','2026-09-26 23:28:33','2026-09-26 23:36:10','logout','4b12420778f2664a30a853c8f49f478fc5af472ece8917cf0a267cbb70e86965','2026-09-26 23:36:08',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (138,12,'Anthony Hernandez','hr','2026-09-26 23:36:25','2026-09-26 23:39:22','logout','50c7a3b11505e10b5624069e5e372ec253bb3e003dabd65b4d8b2ac8c825ef5e','2026-09-26 23:39:06',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (139,13,'Pauleen Mae Lugasing','employee','2026-09-26 23:39:30','2026-09-26 23:52:54','logout','0d80c8c0bd02c506332d21307576b5ba481bbe7124645e04f98ca4efa36f404b','2026-09-26 23:52:49',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (140,11,'Adrian Consolacion','admin','2026-09-26 23:40:27','2026-09-26 23:51:40','logout','8b6a23b463779d74ab6624aa9437c6a7e77af40b4b6a1d0023c8db5919ac97cd','2026-09-26 23:51:31',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (141,11,'Adrian Consolacion','admin','2026-09-26 23:53:14','2026-09-26 23:54:45','logout','0e0972ffd6f662be592435a22603c5df678f4bfc6a5598f0030832e60cef4e4d','2026-09-26 23:54:43',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (142,12,'Anthony Hernandez','hr','2026-09-26 23:54:52','2026-09-27 00:00:13','logout','497a4f46d27bc1c6403b59ded61bf9f8f3f52300661791dde9a0d9c415ee1d55','2026-09-27 00:00:00',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (143,12,'Anthony Hernandez','hr','2026-09-27 00:00:23','2026-09-27 00:02:02','logout','00e1b61ff3427b76b97c416752e542bc6b7bec1d68de94aa85204a5e1d593620','2026-09-27 00:01:30',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (144,11,'Adrian Consolacion','admin','2026-09-27 00:02:09','2026-09-27 00:02:28','logout','e9ef9e03ee2b47343a5803fabfe8a8b594e4e8494f345aec0d771317878c4ca8','2026-09-27 00:02:27',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (145,13,'Pauleen Mae Lugasing','employee','2026-09-27 00:07:38','2026-09-27 00:07:57','logout','2ec5a7b977b0b41a0ddd572d5761d80fe71d9cd194d83b3718c59e370add3053','2026-09-27 00:07:54',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (146,11,'Adrian Consolacion','admin','2026-09-27 00:15:13','2026-09-27 00:19:44','logout','95ea80c8e14d67fbcfde01afb1ec6631f8b1018505dc360e70c50202b60e1fc5','2026-09-27 00:19:32',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (148,11,'Adrian Consolacion','admin','2026-09-27 13:16:38','2026-09-27 13:17:07','logout','f488c8513155fcd7f1b7fe0ddc1b7e15692f6de7b18485f5946f545769eab86e','2026-09-27 13:16:39',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (149,12,'Anthony Hernandez','hr','2026-09-27 13:17:13','2026-09-27 13:17:29','logout','51ac2228d7867eec49c94a56c87b57b258ade99679560f94d70783402ef68616','2026-09-27 13:17:13',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (150,13,'Pauleen Mae Lugasing','employee','2026-09-27 13:17:37','2026-09-27 13:21:41','logout','b24da88ef460f46c82d1b04da081558a0e28b1d9085e7c4a0bcb5ecd01949a71','2026-09-27 13:21:38',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (151,12,'Anthony Hernandez','hr','2026-09-27 13:21:49','2026-09-27 13:23:00','logout','481914bf98ef382c97d7f3316f926c44c7653fa4b97575bc2988c1ee0d86d759','2026-09-27 13:22:49',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (152,11,'Adrian Consolacion','admin','2026-09-27 13:23:12','2026-09-27 13:29:54','logout','933fb035e4492f11f8f406ffc1ed3ba7b0469d229d3257086a7c29e1c5c02150','2026-09-27 13:29:49',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (153,13,'Pauleen Mae Lugasing','employee','2026-09-27 13:30:03','2026-09-27 13:30:39','logout','6ef79bbb6eefef3669a84db1cdd0c0da9e4c321884c37ed2e8985a46712a8045','2026-09-27 13:30:37',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (155,12,'Anthony Hernandez','hr','2026-09-28 08:00:52','2026-09-28 08:29:52','timeout','58d0925e89a9c87c245f8dfb9ec974c908e110d29575ca29c5b2a732830022ac','2026-09-28 08:16:56',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (158,11,'Adrian Consolacion','admin','2026-10-08 21:35:35','2026-10-08 21:39:56','logout','b3c5cd03b6382e9e367fd781817855fefe1aa23b2efe759a0945fe731ac68f1a','2026-10-08 21:39:54',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (159,12,'Anthony Hernandez','hr','2026-10-08 21:40:06','2026-10-08 21:44:24','logout','82a8804278e30a0d6acd55ed27a926c02c1d3b20236cae5b0a335022dbb3d5a2','2026-10-08 21:44:17',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (160,11,'Adrian Consolacion','admin','2026-10-08 21:44:30','2026-10-08 21:46:10','logout','91174471fa82d8c5a1fed620b849d2995c696c6e17758cebb3d43bdfec04eb46','2026-10-08 21:45:39',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (161,13,'Pauleen Mae Lugasing','employee','2026-10-08 21:46:19','2026-10-08 22:07:16','timeout','c5cbb60d9d4efb5fae88f38540614de1356fdcabb2dd3fb8ea34b694682b70c6','2026-10-08 21:51:30',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (162,11,'Adrian Consolacion','admin','2026-10-08 22:11:11','2026-10-08 22:12:07','logout','017218259ae4a639da6bddeae2e86c21e6a0c6bb0d26f8015ada92d996638055','2026-10-08 22:12:04',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (163,13,'Pauleen Mae Lugasing','employee','2026-10-08 22:12:21','2026-10-08 22:19:42','logout','9914385eff895025ba6725cd29fab8c249c30aa5583fa8d8afb9ba6ff8009162','2026-10-08 22:19:16',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (164,12,'Anthony Hernandez','hr','2026-10-08 22:19:48','2026-10-08 22:21:16','logout','099dfbe816a0405dd113aecf827be044cf7d353920ba6805202a2373ad84ea15','2026-10-08 22:21:09',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (165,11,'Adrian Consolacion','admin','2026-10-08 22:21:24','2026-10-08 22:24:11','logout','134dd24ba9f021fd63cdb428259954345be4104fc74a3598f2b2c7f9ea5fece9','2026-10-08 22:24:05',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (166,14,'Gilberto Bajar','hr','2026-10-08 22:24:20','2026-10-08 22:27:52','logout','a57e09db229aea029f49ea8b44a4bebeab5774e7f7178d8301ef30cbbb75609c','2026-10-08 22:27:39',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (167,14,'Gilberto Bajar','hr','2026-10-08 22:28:03','2026-10-08 22:37:49','logout','64872c19c421abb2c00dceff3c1aac01ab2bf3c6e188e85e81290fc23fd777b3','2026-10-08 22:37:08',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (168,12,'Anthony Hernandez','hr','2026-10-08 22:37:55','2026-10-08 22:54:59','timeout','c1fa79c475a1eabb8170dd1431f1ce65be673c87fda4e721c0d4650835c92468','2026-10-08 22:39:59',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (171,11,'Adrian Consolacion','admin','2026-10-09 20:10:06','2026-10-09 20:10:44','logout','9a5f35bacfed0c01afa25d001c7d707b1cb35d3755df3de212cb828dcbc90de8','2026-10-09 20:10:27',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (172,11,'Adrian Consolacion','admin','2026-10-09 20:11:51','2026-10-09 20:15:16','logout','b283ffc3b4873e00d3668c10bea554e5b0bf301ce9210889aa6c3ddc8710695c','2026-10-09 20:14:59',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (173,12,'Anthony Hernandez','hr','2026-10-09 20:15:25','2026-10-09 20:45:17','timeout','631268ce564eaa2a10ebd29097b3ae8ad9f2c25ed56d88095a38af3d2284c020','2026-10-09 20:15:25',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (174,11,'Adrian Consolacion','admin','2026-10-09 20:56:49','2026-10-09 20:57:48','logout','8b64fd83d3ab339ffe5dc0c7f7d2ef2a4dc7bc81881c42b5bc3d82cf2c1ab55c','2026-10-09 20:57:46',NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (175,11,'Adrian Consolacion','admin','2026-10-09 20:58:32','2026-10-09 21:28:51','logout','65f5f16d8efd37016e08af8ef34356f243409ef9d19748ac747e22fe7b15750c','2026-10-09 21:28:47','2026-10-09 21:28:47');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (176,11,'Adrian Consolacion','admin','2026-10-09 21:28:56','2026-10-09 21:44:49','timeout','d0a97d3fa21f65ac8ab78fbb9deaf94c35f77ea8ec7cebf7415841e83b992dcb','2026-10-09 21:29:49','2026-10-09 21:29:49');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (177,11,'Adrian Consolacion','admin','2026-10-09 21:29:25','2026-10-09 22:18:28','timeout','3d4b5afa66b54780b7080bd7f4b392ac4ed28b749f4d704457d3c049416b23ca','2026-10-09 22:03:20','2026-10-09 22:18:10');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (178,11,'Adrian Consolacion','admin','2026-10-10 22:40:37','2026-10-10 22:55:34','logout','bb838dd21ebd6e7cbc000ae4fa6b8ae902e7804c6279a09a4b4f9cc875af2398','2026-10-10 22:55:24','2026-10-10 22:55:24');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (179,12,'Anthony Hernandez','hr','2026-10-10 22:41:16','2026-10-10 23:15:10','timeout','78635e3f8a7696f62fbdb6547af3d18eaf822321eca5ffb7823f012d0510bafb','2026-10-10 23:00:10','2026-10-10 23:14:42');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (180,11,'Adrian Consolacion','admin','2026-10-10 22:56:16','2026-10-10 22:57:58','logout','03f6f67fe20619522c29cc8004e5d2199d06004a7f515dba530be8afc8444c8e','2026-10-10 22:57:56','2026-10-10 22:57:56');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (181,11,'Adrian Consolacion','admin','2026-10-10 23:00:37','2026-10-10 23:03:22','logout','d5004df320a13c85b8f35a868f39ecda5b7c0758abf998f7fff92be1cdaadb0d','2026-10-10 23:03:19','2026-10-10 23:03:19');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (182,11,'Adrian Consolacion','admin','2026-10-10 23:03:28','2026-10-10 23:03:41','logout','3c235d9e90d1e936faa9f9ed73eb82101912fa72dbe1f35323e7c4cff5cc45b8','2026-10-10 23:03:35','2026-10-10 23:03:35');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (183,11,'Adrian Consolacion','admin','2026-10-10 23:03:58','2026-10-10 23:04:10','logout','1bc4e0328d4de961023191ba8b4ea987592170722de979e0064281400ed16d51','2026-10-10 23:04:09','2026-10-10 23:04:09');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (184,11,'Adrian Consolacion','admin','2026-10-10 23:04:45','2026-10-10 23:05:49','logout','d133254f6acf230cb408877e8b64d6f184c9deb76115ebc448d904aec3a5570e','2026-10-10 23:05:46','2026-10-10 23:05:46');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (185,1,'System Administrator','admin','2026-10-10 23:06:01','2026-10-10 23:06:17','logout','c68d50d5276e5b8df3d2d06403eeea489301d5e7c28c6797f6beae53045501e2','2026-10-10 23:06:01','2026-10-10 23:06:01');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (186,12,'Anthony Hernandez','hr','2026-10-10 23:13:06','2026-10-10 23:15:35','logout','889124176c435db52984efb902f6181b7bacc9df2a0f12ee4c69f9a27154c18c','2026-10-10 23:15:33','2026-10-10 23:15:33');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (187,11,'Adrian Consolacion','admin','2026-10-10 23:15:29','2026-10-10 23:16:02','logout','f5fcb495bd155d9d29bde39228e7d2829a620ff6bbace54bb8c8260e82668409','2026-10-10 23:15:29','2026-10-10 23:15:59');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (188,1,'System Administrator','admin','2026-10-10 23:15:47','2026-10-10 23:17:28','logout','725b7e18eb487411ddb29665bd29e23aba3a3d74e477fcd9226b173e1a58c939','2026-10-10 23:17:26','2026-10-10 23:17:26');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (189,12,'Anthony Hernandez','hr','2026-10-10 23:16:08','2026-10-10 23:17:20','logout','43c9f125124f81f36eedba6b2e48e96a32a863f3ef98094b8640b57a7c984ee7','2026-10-10 23:17:17','2026-10-10 23:17:17');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (190,12,'Anthony Hernandez','hr','2026-10-10 23:17:33','2026-10-10 23:20:11','logout','7a5acb0534dc3436a0837e2e0f113ea0db238fd77f6ca5bfc78c479e74bf7e57','2026-10-10 23:20:10','2026-10-10 23:20:10');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (191,1,'System Administrator','admin','2026-10-10 23:20:26','2026-10-10 23:21:55','logout','5460ef68b33115223700a1a77179145b2c54868b1b1bfa15ed94bf7584986a8d','2026-10-10 23:21:26','2026-10-10 23:21:26');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (192,12,'Anthony Hernandez','hr','2026-10-10 23:22:02','2026-10-10 23:26:19','logout','e9cdc4d0d76c38d5f6adfd47136c2abee9cefb6649252858ac443845a9b0ce3f','2026-10-10 23:26:12','2026-10-10 23:26:19');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (193,12,'Anthony Hernandez','hr','2026-10-10 23:22:27','2026-10-10 23:42:15','timeout','270a22a54d15f8e3898b6d250e7df11635ae20922a091580e724eb5c1c7e6eae','2026-10-10 23:27:15','2026-10-10 23:42:12');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (194,1,'System Administrator','admin','2026-10-10 23:26:26','2026-10-10 23:41:58','timeout','3e85113e3871a883744d83fc05cd985fa2cb4fecf1c1ce833577d93a0def5653','2026-10-10 23:26:58','2026-10-10 23:41:28');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (195,1,'System Administrator','admin','2026-10-10 23:48:18','2026-10-10 23:57:36','logout','1bbcb26cca45fafd067962cdad6e64475f6b1445485ba121012b26598b898630','2026-10-10 23:56:39','2026-10-10 23:57:09');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (196,12,'Anthony Hernandez','hr','2026-10-10 23:57:53','2026-10-11 00:22:13','timeout','2667cabe0121fc4c7b7fd0b81a4940974ac6e19831ec07a9f3e7dd4e13795037','2026-10-11 00:07:13','2026-10-11 00:07:13');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (197,12,'Anthony Hernandez','hr','2026-10-10 23:58:09','2026-10-10 23:58:59','logout','2834c6c6e27dca3a478633e6c4298d525b2d7d3bcad89d213042021d79496c45','2026-10-10 23:58:58','2026-10-10 23:58:58');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (198,1,'System Administrator','admin','2026-10-10 23:59:20','2026-10-11 00:13:15','logout','793874144b34a4dd250782f99ca0ad435498acbc5288843b2689029efa4d5d7e','2026-10-11 00:12:56','2026-10-11 00:13:10');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (199,12,'Anthony Hernandez','hr','2026-10-11 00:13:33','2026-10-11 00:15:05','logout','0c218dcd8d0d91d79749e4fcf6a2196780b52678884dbb6c6dabe46025bf46ad','2026-10-11 00:15:03','2026-10-11 00:15:03');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (200,1,'System Administrator','admin','2026-10-11 00:16:18','2026-10-11 00:18:21','logout','f7d8c62438ef254d4f2f9b2e1c73ff82f145c6ded4b3d663821e73d39ced9b1d','2026-10-11 00:18:18','2026-10-11 00:18:18');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (201,1,'System Administrator','admin','2026-10-11 00:22:12','2026-10-11 00:47:00','logout','ac025ce4ea3caf64115cf874d282c92563afa968b5a5b4a2b7e5a53fafdd363f','2026-10-11 00:46:58','2026-10-11 00:46:58');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (202,1,'System Administrator','admin','2026-10-11 00:47:22','2026-10-11 00:50:08','logout','2403f01526fe0911bd6be396f2bb7042881ba3bad3b2b9720a69a3e4dfe87c80','2026-10-11 00:50:06','2026-10-11 00:50:06');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (203,1,'System Administrator','admin','2026-10-11 00:50:20','2026-10-11 01:06:32','timeout','773b62dd103392a8fa86cc832bff39ed9e8336ade532f60a35d6d08bdebb0839','2026-10-11 00:51:32','2026-10-11 00:51:34');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (204,12,'Anthony Hernandez','hr','2026-10-11 00:50:39','2026-10-11 00:51:01','logout','1c2e021728e64e9f9cfe6e0eef7a2bd735734fb137666eba48b774cc9642801a','2026-10-11 00:50:45','2026-10-11 00:50:54');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (205,11,'Adrian Consolacion','admin','2026-10-11 00:51:18','2026-10-11 01:06:42','logout','43d3ab6346ca58b541ffd2d6355299076d436991d06ddcf3bbf47669adab9ba3','2026-10-11 01:05:55','2026-10-11 01:06:41');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (206,1,'System Administrator','admin','2026-10-11 00:52:15','2026-10-11 01:30:29','logout','cf64d9d46bc6c74577c3d3dec3e7c6a814f340e8fa29a90d62a4e8d6dd9b742c','2026-10-11 01:30:28','2026-10-11 01:30:28');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (207,11,'Adrian Consolacion','admin','2026-10-11 00:58:53','2026-10-11 01:18:55','timeout','e4e433a368aa17ed4e4f3698ff68546f61efc9a7c8fd0959b6b6c1bfd63de21e','2026-10-11 01:03:55','2026-10-11 01:03:55');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (208,12,'Anthony Hernandez','hr','2026-10-11 01:04:10','2026-10-11 01:05:32','logout','ee4e933f7d5dbf4131fefa3cfae94c64a8906e69992e5972fd3002a5e4588a9a','2026-10-11 01:05:22','2026-10-11 01:05:27');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (209,12,'Anthony Hernandez','hr','2026-10-11 01:06:46','2026-10-11 01:20:46','logout','9dcb85d8b90d401cd300cb6ed441e2ca577b2028ae1e7858b9508c7f85392461','2026-10-11 01:20:32','2026-10-11 01:20:42');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (210,13,'Pauleen Mae Lugasing','employee','2026-10-11 01:22:58','2026-10-11 01:30:55','logout','1a1883d3c197b2d24ff92584ed57b1ae944bf98f6b9009e34464869575c97982','2026-10-11 01:30:50','2026-10-11 01:30:53');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (211,1,'System Administrator','admin','2026-10-11 01:30:36','2026-10-11 01:43:00','logout','8c2e5bc0ccabbfba941a7d6053d03e9e292803d8c8e8340d325fce3180ddab34','2026-10-11 01:42:08','2026-10-11 01:42:58');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (212,12,'Anthony Hernandez','hr','2026-10-11 01:43:09','2026-10-11 02:12:20','logout','1d6f40f838a97d8c09329ed42d205cf9a0aaf83aa1860ea2ff684b8aed891979','2026-10-11 02:12:18','2026-10-11 02:12:18');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (213,1,'System Administrator','admin','2026-10-11 01:49:53','2026-10-11 02:12:05','logout','55a248f270551450fc88c7f0400d538e6e783da992156b541246aaf12fbfdb7f','2026-10-11 02:11:35','2026-10-11 02:12:01');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (214,1,'System Administrator','admin','2026-10-11 02:12:28','2026-10-11 02:25:22','logout','60ad9bb5768ab1f5a453c43d1c00f3fb111e23d37d6a04984e08d1b79fcdf1a8','2026-10-11 02:25:04','2026-10-11 02:25:19');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (215,11,'Adrian Consolacion','admin','2026-10-12 09:41:26','2026-10-12 09:41:42','logout','851f39322653862adbce440e47e406a4288b3951e8f91244a65cad6476d19fb6','2026-10-12 09:41:40','2026-10-12 09:41:40');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (216,1,'System Administrator','admin','2026-10-12 09:42:12','2026-10-12 09:44:52','logout','e18e173673ad991408107e6c7b6363d28c82db693d06bce63e0f7757c7e8abd9','2026-10-12 09:44:49','2026-10-12 09:44:49');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (217,12,'Anthony Hernandez','hr','2026-10-12 09:48:59','2026-10-12 10:04:59','logout','9dae9abd9336073f738cadd3ca909ea0cf4fbbb0b0f4f0b1b226b372152e3382','2026-10-12 10:04:57','2026-10-12 10:04:57');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (218,11,'Adrian Consolacion','admin','2026-10-12 10:07:22','2026-10-12 10:18:25','logout','ad9fd6524a2c6af4f2f8c2e8c3f20a3e500847c55f3b5d8eccd59d05a7de5497','2026-10-12 10:18:24','2026-10-12 10:18:24');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (219,1,'System Administrator','admin','2026-10-12 10:19:43','2026-10-12 10:30:57','logout','43125cc2e63988fe947c1585368aecff539a68fc7b68c0d41e201edc9e86def5','2026-10-12 10:30:51','2026-10-12 10:30:56');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (220,11,'Adrian Consolacion','admin','2026-10-12 10:31:09','2026-10-12 10:32:41','logout','a4cc30452eb22c750b0107fa180679b47406f8530906bf7e828e735d8e2b73cd','2026-10-12 10:32:24','2026-10-12 10:32:39');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (221,11,'Adrian Consolacion','admin','2026-10-12 10:32:48','2026-10-12 11:20:00','timeout','e62d0b4b8c074132dccce1fd65c2138b91383092562746999494b4702abcca1e','2026-10-12 11:04:58','2026-10-12 11:19:03');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (222,11,'Adrian Consolacion','admin','2026-10-12 11:23:43','2026-10-12 11:24:06','logout','3c369ca5fd11d865bb894d11479c6033eca8f3ac2c318e457ac0068ec74ea727','2026-10-12 11:23:44','2026-10-12 11:24:04');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (223,1,'System Administrator','admin','2026-10-12 11:24:14','2026-10-12 11:37:25','logout','8b6e71c8070ca86c173e569169c852403eb80b30c1158ee41d5a1d448e3ebfcc','2026-10-12 11:37:12','2026-10-12 11:37:24');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (224,12,'Anthony Hernandez','hr','2026-10-12 11:37:40','2026-10-12 11:39:23','logout','80109ec6c4613f86f51e86bb654598cba1d6e9570cb8d4e735d659f3e0488ece','2026-10-12 11:38:55','2026-10-12 11:39:20');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (225,11,'Adrian Consolacion','admin','2026-10-12 13:19:13','2026-10-12 13:49:04','timeout','9a360de31feeaa0e1df8ec8b09403013cff3ef983ecd2aa84583fb2c8037a6e6','2026-10-12 13:34:04','2026-10-12 13:34:09');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (226,1,'System Administrator','admin','2026-10-12 13:22:32','2026-10-12 13:51:02','timeout','b23ae3a99e3080ca382116ba634fbfedda22288856d885eff75647bb00c410ec','2026-10-12 13:36:02','2026-10-12 13:36:22');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (227,11,'Adrian Consolacion','admin','2026-10-12 17:13:23','2026-10-12 17:17:38','logout','781c3c5034c51c52feb2eac90784e47d6222aa70a8bfeb5c6c33727c054baba9','2026-10-12 17:17:36','2026-10-12 17:17:36');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (228,12,'Anthony Hernandez','hr','2026-10-12 17:17:44','2026-10-12 17:19:24','logout','446931d46ef38850b236911a1031247ceb46ee30127bffd9764d37407b6ad73a','2026-10-12 17:19:17','2026-10-12 17:19:20');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (229,11,'Adrian Consolacion','admin','2026-10-12 17:19:38','2026-10-12 17:34:58','timeout','4514ea0ca69aa17b74bbf2398ea48ef8b58f1739bd0668182e5f6994e0f36bfe','2026-10-12 17:19:58','2026-10-12 17:20:48');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (230,11,'Adrian Consolacion','admin','2026-10-12 20:56:41','2026-10-12 21:24:24','timeout','45ebf3af997fffcc0545508f7f1a00955b8f6a703fa7c4c6bca74b841f55baf3','2026-10-12 21:09:24','2026-10-12 21:10:24');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (231,11,'Adrian Consolacion','admin','2026-10-14 19:50:41','2026-10-14 19:51:18','logout','52bee99eb3d7d04d5ecb42b35ceb99e6ede93341d237c4b727bd76c50461dc3f','2026-10-14 19:51:16','2026-10-14 19:51:16');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (232,12,'Anthony Hernandez','hr','2026-10-14 19:51:29','2026-10-14 20:13:09','timeout','c74686d97dd24b5937d66016f7ed09f8f984c77987cdc56d68a3f9f1b084187f','2026-10-14 19:58:09','2026-10-14 19:58:24');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (233,11,'Adrian Consolacion','admin','2026-10-16 13:00:14','2026-10-16 13:19:49','timeout','6a2cae4a6ce792e98c5cde3a55acc8e59b99b438da84aa6c84526e232e86e352','2026-10-16 13:04:49','2026-10-16 13:04:49');
INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`, `logout_time`, `logout_reason`, `session_token`, `last_ping`, `last_seen`) VALUES (234,12,'Anthony Hernandez','hr','2026-10-16 13:05:02',NULL,NULL,'072e27eae038eea70e60067daa42792d1f878a9b4d050e866c7f034babd57182','2026-10-16 13:20:02','2026-10-16 13:20:02');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `activity_type` varchar(20) NOT NULL DEFAULT 'leave',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `activity_type`) VALUES (1,7,'❌ Your task \'Techno\' has been REJECTED.',1,'2026-04-29 05:56:45','leave');
INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `activity_type`) VALUES (2,7,'✅ Your Sick Leave leave (May 02 - May 04) has been APPROVED.',1,'2026-04-29 06:56:59','leave');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_throttle` (
  `source_hash` char(64) NOT NULL,
  `window_start` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`source_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `password_reset_throttle` (`source_hash`, `window_start`, `attempts`) VALUES ('eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3','2026-10-12 10:05:47',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `employee_id` varchar(50) NOT NULL,
  `request_message` text NOT NULL,
  `status` enum('pending','in_progress','resolved','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `handled_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `requested_email` varchar(191) DEFAULT NULL,
  `system_note` varchar(255) DEFAULT NULL,
  `notification_email` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`status`),
  KEY `status` (`status`,`created_at`),
  KEY `handled_by` (`handled_by`),
  CONSTRAINT `password_reset_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `password_reset_tickets_ibfk_2` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `password_reset_tickets` (`id`, `user_id`, `employee_id`, `request_message`, `status`, `admin_notes`, `handled_by`, `created_at`, `updated_at`, `requested_email`, `system_note`, `notification_email`) VALUES (1,13,'','I forgot my password, please change new password','rejected','Ewan HAHAHHAHAHA',11,'2026-10-09 12:11:35','2026-10-10 14:55:14',NULL,NULL,NULL);
INSERT INTO `password_reset_tickets` (`id`, `user_id`, `employee_id`, `request_message`, `status`, `admin_notes`, `handled_by`, `created_at`, `updated_at`, `requested_email`, `system_note`, `notification_email`) VALUES (2,12,'','Changing account password','rejected','Ewan HAHAHAHAHA',11,'2026-10-09 12:58:13','2026-10-10 14:55:24',NULL,NULL,NULL);
INSERT INTO `password_reset_tickets` (`id`, `user_id`, `employee_id`, `request_message`, `status`, `admin_notes`, `handled_by`, `created_at`, `updated_at`, `requested_email`, `system_note`, `notification_email`) VALUES (3,1,'','Nakalimutan ko password HAHAHAHAHA','resolved','Done change password bossing ko',11,'2026-10-10 14:56:04','2026-10-10 14:57:43',NULL,NULL,NULL);
INSERT INTO `password_reset_tickets` (`id`, `user_id`, `employee_id`, `request_message`, `status`, `admin_notes`, `handled_by`, `created_at`, `updated_at`, `requested_email`, `system_note`, `notification_email`) VALUES (4,1,'','nakalimutan pass bossing','resolved','try sample for changing password',11,'2026-10-10 15:04:27','2026-10-10 15:05:41',NULL,NULL,NULL);
INSERT INTO `password_reset_tickets` (`id`, `user_id`, `employee_id`, `request_message`, `status`, `admin_notes`, `handled_by`, `created_at`, `updated_at`, `requested_email`, `system_note`, `notification_email`) VALUES (5,1,'','sample notification for ticketing updates','rejected','Open ticket for now',11,'2026-10-12 02:05:47','2026-10-12 02:09:57','admin@hris.com',NULL,'adrianconsolacion.2903@gmail.com');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_ticket_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `recipient` varchar(191) NOT NULL,
  `ticket_status` varchar(20) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `delivery_status` enum('queued','sending','sent','failed') NOT NULL DEFAULT 'queued',
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_error` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL,
  `claimed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ticket_status_email` (`ticket_id`,`ticket_status`),
  KEY `delivery_status` (`delivery_status`),
  CONSTRAINT `password_ticket_emails_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `password_reset_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `password_ticket_emails` (`id`, `ticket_id`, `recipient`, `ticket_status`, `subject`, `body`, `delivery_status`, `attempts`, `last_error`, `created_at`, `sent_at`, `claimed_at`) VALUES (1,5,'adrianconsolacion.2903@gmail.com','pending','Pagecom HRIS - Password reset ticket #5 - Open / Pending','Password reset ticket #5\nStatus: Open / Pending\n\nYour request has been received and is waiting for an administrator.\n\nAn administrator must verify your identity before resetting an account. This email never contains a password. If you did not request this, please ignore it.\n\nPagecom HRIS','sent',1,NULL,'2026-10-12 02:05:47','2026-10-12 10:05:52','2026-10-12 10:05:47');
INSERT INTO `password_ticket_emails` (`id`, `ticket_id`, `recipient`, `ticket_status`, `subject`, `body`, `delivery_status`, `attempts`, `last_error`, `created_at`, `sent_at`, `claimed_at`) VALUES (2,5,'adrianconsolacion.2903@gmail.com','in_progress','Pagecom HRIS - Password reset ticket #5 - Open / In progress','Password reset ticket #5\nStatus: Open / In progress\n\nAn administrator is reviewing your request.\n\nAn administrator must verify your identity before resetting an account. This email never contains a password. If you did not request this, please ignore it.\n\nPagecom HRIS','sent',1,NULL,'2026-10-12 02:07:41','2026-10-12 10:07:46','2026-10-12 10:07:41');
INSERT INTO `password_ticket_emails` (`id`, `ticket_id`, `recipient`, `ticket_status`, `subject`, `body`, `delivery_status`, `attempts`, `last_error`, `created_at`, `sent_at`, `claimed_at`) VALUES (3,5,'adrianconsolacion.2903@gmail.com','rejected','Pagecom HRIS - Password reset ticket #5 - Rejected','Password reset ticket #5\nStatus: Rejected\n\nYour request has been closed without a password reset. Contact your administrator for further help.\n\nAn administrator must verify your identity before resetting an account. This email never contains a password. If you did not request this, please ignore it.\n\nPagecom HRIS','sent',1,NULL,'2026-10-12 02:09:57','2026-10-12 10:10:07','2026-10-12 10:09:57');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payslips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cutoff_start` date NOT NULL,
  `cutoff_end` date NOT NULL,
  `payout_date` date NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL DEFAULT 755.00,
  `paid_days` int(11) NOT NULL DEFAULT 0,
  `present_days` int(11) NOT NULL DEFAULT 0,
  `late_days` int(11) NOT NULL DEFAULT 0,
  `absent_days` int(11) NOT NULL DEFAULT 0,
  `unrecorded_days` int(11) NOT NULL DEFAULT 0,
  `allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `generated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_cutoff` (`user_id`,`cutoff_start`,`cutoff_end`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `payslips` (`id`, `user_id`, `cutoff_start`, `cutoff_end`, `payout_date`, `daily_rate`, `paid_days`, `present_days`, `late_days`, `absent_days`, `unrecorded_days`, `allowance`, `deduction`, `tax`, `gross_pay`, `total_deductions`, `net_pay`, `generated_by`, `created_at`, `updated_at`) VALUES (1,13,'2026-09-16','2026-09-30','2026-09-30',695.00,0,0,0,0,11,0.00,0.00,0.00,0.00,0.00,0.00,12,'2026-09-26 08:35:00','2026-09-26 08:35:00');
INSERT INTO `payslips` (`id`, `user_id`, `cutoff_start`, `cutoff_end`, `payout_date`, `daily_rate`, `paid_days`, `present_days`, `late_days`, `absent_days`, `unrecorded_days`, `allowance`, `deduction`, `tax`, `gross_pay`, `total_deductions`, `net_pay`, `generated_by`, `created_at`, `updated_at`) VALUES (2,12,'2026-09-16','2026-09-30','2026-09-30',695.00,3,2,1,8,0,0.00,0.00,0.00,2185.00,166.80,2018.20,12,'2026-09-26 08:35:58','2026-09-28 00:07:41');
INSERT INTO `payslips` (`id`, `user_id`, `cutoff_start`, `cutoff_end`, `payout_date`, `daily_rate`, `paid_days`, `present_days`, `late_days`, `absent_days`, `unrecorded_days`, `allowance`, `deduction`, `tax`, `gross_pay`, `total_deductions`, `net_pay`, `generated_by`, `created_at`, `updated_at`) VALUES (3,11,'2026-09-16','2026-09-30','2026-09-30',695.00,2,2,0,8,1,0.00,0.00,0.00,1390.00,111.20,1278.80,12,'2026-09-28 00:03:12','2026-09-28 00:03:12');
INSERT INTO `payslips` (`id`, `user_id`, `cutoff_start`, `cutoff_end`, `payout_date`, `daily_rate`, `paid_days`, `present_days`, `late_days`, `absent_days`, `unrecorded_days`, `allowance`, `deduction`, `tax`, `gross_pay`, `total_deductions`, `net_pay`, `generated_by`, `created_at`, `updated_at`) VALUES (6,14,'2026-10-01','2026-10-15','2026-10-15',695.00,0,0,0,1,10,0.00,0.00,100.00,0.00,100.00,0.00,14,'2026-10-08 14:29:49','2026-10-08 14:30:38');
INSERT INTO `payslips` (`id`, `user_id`, `cutoff_start`, `cutoff_end`, `payout_date`, `daily_rate`, `paid_days`, `present_days`, `late_days`, `absent_days`, `unrecorded_days`, `allowance`, `deduction`, `tax`, `gross_pay`, `total_deductions`, `net_pay`, `generated_by`, `created_at`, `updated_at`) VALUES (9,11,'2026-09-15','2026-10-15','2026-10-15',695.00,5,3,2,19,0,0.00,0.00,0.00,3675.00,278.00,3397.00,12,'2026-10-12 09:18:15','2026-10-12 09:18:15');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sent_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `sent_tasks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sent_tasks_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sent_tasks_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_cleanup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `cleaned_at` datetime DEFAULT current_timestamp(),
  `affected_sessions` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','read','approved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL,
  `hr_id` int(11) DEFAULT NULL,
  `assignment_batch` varchar(64) DEFAULT NULL,
  `assignment_key` char(64) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `file_attachment` varchar(255) DEFAULT NULL,
  `submission_file` varchar(255) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `submission_request_key` char(64) DEFAULT NULL,
  `status` enum('pending','submitted','reviewed') NOT NULL DEFAULT 'pending',
  `ai_score` int(11) DEFAULT NULL,
  `grammar_score` int(11) DEFAULT NULL,
  `spelling_score` int(11) DEFAULT NULL,
  `clarity_score` int(11) DEFAULT NULL,
  `relevance_score` int(11) DEFAULT NULL,
  `ai_feedback` text DEFAULT NULL,
  `hr_score` int(11) DEFAULT NULL,
  `hr_feedback` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` text DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_task_assignment_key` (`assignment_key`),
  UNIQUE KEY `unique_submission_request_key` (`submission_request_key`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `tasks` (`id`, `employee_id`, `hr_id`, `assignment_batch`, `assignment_key`, `title`, `description`, `deadline`, `file_attachment`, `submission_file`, `submitted_at`, `submission_request_key`, `status`, `ai_score`, `grammar_score`, `spelling_score`, `clarity_score`, `relevance_score`, `ai_feedback`, `hr_score`, `hr_feedback`, `file_name`, `file_path`, `file_type`, `uploaded_by`, `created_at`) VALUES (4,NULL,NULL,'batch_6a1b2af2e0e3b',NULL,'sample task',NULL,NULL,NULL,NULL,NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1780165362_Data Communication.pdf','../uploads/tasks/1780165362_Data Communication.pdf','application/pdf',12,'2026-05-30 18:22:42');
INSERT INTO `tasks` (`id`, `employee_id`, `hr_id`, `assignment_batch`, `assignment_key`, `title`, `description`, `deadline`, `file_attachment`, `submission_file`, `submitted_at`, `submission_request_key`, `status`, `ai_score`, `grammar_score`, `spelling_score`, `clarity_score`, `relevance_score`, `ai_feedback`, `hr_score`, `hr_feedback`, `file_name`, `file_path`, `file_type`, `uploaded_by`, `created_at`) VALUES (5,13,12,'batch_6a2dd9eca0cd6',NULL,'sample task','sample task for experiment','2026-06-16','1781389804_Data_Communication.pdf','1781389970_13_Data_Communication.pdf','2026-06-14 06:32:50',NULL,'reviewed',50,0,0,0,0,'Text extraction failed. The file may be image-based or an unsupported format.',100,'wahahahaha','1781389804_Data_Communication.pdf','uploads/tasks/1781389804_Data_Communication.pdf','application/pdf',12,'2026-06-13 22:30:04');
INSERT INTO `tasks` (`id`, `employee_id`, `hr_id`, `assignment_batch`, `assignment_key`, `title`, `description`, `deadline`, `file_attachment`, `submission_file`, `submitted_at`, `submission_request_key`, `status`, `ai_score`, `grammar_score`, `spelling_score`, `clarity_score`, `relevance_score`, `ai_feedback`, `hr_score`, `hr_feedback`, `file_name`, `file_path`, `file_type`, `uploaded_by`, `created_at`) VALUES (6,13,12,'batch_6ab7e487435cd',NULL,'Evaluation Sample 1','Sample for experimentation','2026-09-18','1790436487_1781389804_Data_Communication.pdf','964e7cd8208c3b7e91b7e6475ea54b04_13_1781389804_Data_Communication.pdf','2026-09-26 23:36:08','7313ba9d18f84d5e8a229100fce2274ae633ce59c1557e91129d8afd73848712','reviewed',50,0,0,0,0,'Text extraction failed. The file may be image-based or an unsupported format.',100,'HAHAHAHAHA SAMPLE','1790436487_1781389804_Data_Communication.pdf','uploads/tasks/1790436487_1781389804_Data_Communication.pdf','application/pdf',12,'2026-09-26 15:28:07');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','hr','employee') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `work_shift` enum('morning','night') NOT NULL DEFAULT 'morning',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(80) DEFAULT NULL,
  `phone_number` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `email` (`email`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `users` (`id`, `employee_id`, `full_name`, `email`, `password`, `role`, `department_id`, `work_shift`, `status`, `created_at`, `profile_picture`, `phone_number`) VALUES (1,'ADMIN001','System Administrator','admin@hris.com','$2y$10$HTQaV97T0DZWItxg3rAJMOVzw7h1.riwmF/dJMtChsVwn6vEYXJ2e','admin',5,'morning','active','2026-04-22 22:00:07','d903d57295567b3fb884f60afd83d738.png','09308730162');
INSERT INTO `users` (`id`, `employee_id`, `full_name`, `email`, `password`, `role`, `department_id`, `work_shift`, `status`, `created_at`, `profile_picture`, `phone_number`) VALUES (11,'EMP-00002','Adrian Consolacion','admin.adrian@pagecom.com','$2y$10$R.ivAj8BX/V19Ayk.wusT.SsllnIXtHmMX0/FQiDZUXB5jAwR6R8m','admin',5,'morning','active','2026-05-30 18:18:24','56ab2974b2668e74e11a6ece302ca66c.png','09308730162');
INSERT INTO `users` (`id`, `employee_id`, `full_name`, `email`, `password`, `role`, `department_id`, `work_shift`, `status`, `created_at`, `profile_picture`, `phone_number`) VALUES (12,'EMP-00012','Anthony Hernandez','hr.anthony@pagecom.com','$2y$10$2sOnCqVjgsDegsWWJ7Vzq.DVxCWSPV2fiz6kl44Qu9Vi1tMLtxYBq','hr',4,'morning','active','2026-05-30 18:19:04','0591d015649b30c8216edb778cf3f211.png','');
INSERT INTO `users` (`id`, `employee_id`, `full_name`, `email`, `password`, `role`, `department_id`, `work_shift`, `status`, `created_at`, `profile_picture`, `phone_number`) VALUES (13,'EMP-00013','Pauleen Mae Lugasing','pml@gmail.com','$2y$10$yo5TsYM8KVTB91/vMtdyB.SAdrM4FNwKFchWX.haSlel/v3p1jB0K','employee',8,'morning','active','2026-05-30 18:19:27',NULL,NULL);
INSERT INTO `users` (`id`, `employee_id`, `full_name`, `email`, `password`, `role`, `department_id`, `work_shift`, `status`, `created_at`, `profile_picture`, `phone_number`) VALUES (14,'EMP-00014','Gilberto Bajar','hr.bajar@pagecom.com','$2y$10$xRV33WF1Kns9G6vuz6scDeEoFZajipyNg.XYzj7AifchBr7ilTI9W','hr',4,'morning','active','2026-10-08 14:24:05',NULL,NULL);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

