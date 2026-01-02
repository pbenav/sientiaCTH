/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `event_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `observations` text DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `is_all_day` tinyint(1) NOT NULL DEFAULT 0,
  `is_workday_type` tinyint(1) NOT NULL DEFAULT 0,
  `is_break_type` tinyint(1) NOT NULL DEFAULT 0,
  `is_authorizable` tinyint(1) NOT NULL DEFAULT 0,
  `is_pause_type` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_types_team_id_foreign` (`team_id`),
  KEY `event_types_is_workday_type_index` (`is_workday_type`),
  CONSTRAINT `event_types_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `event_type_id` bigint(20) unsigned DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL,
  `is_authorized` tinyint(1) NOT NULL DEFAULT 0,
  `is_closed_automatically` tinyint(1) NOT NULL DEFAULT 0,
  `is_exceptional` tinyint(1) NOT NULL DEFAULT 0,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_extra_hours` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `work_center_id` bigint(20) unsigned DEFAULT NULL,
  `authorized_by_id` bigint(20) unsigned DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `nfc_tag_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_user_id_foreign` (`user_id`),
  KEY `events_event_type_id_foreign` (`event_type_id`),
  KEY `events_work_center_id_foreign` (`work_center_id`),
  KEY `events_authorized_by_id_foreign` (`authorized_by_id`),
  KEY `events_team_id_foreign` (`team_id`),
  CONSTRAINT `events_authorized_by_id_foreign` FOREIGN KEY (`authorized_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_event_type_id_foreign` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `events_work_center_id_foreign` FOREIGN KEY (`work_center_id`) REFERENCES `work_centers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tablename` varchar(255) NOT NULL,
  `original_event` varchar(2048) NOT NULL,
  `modified_event` varchar(2048) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exceptional_clock_in_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `exceptional_clock_in_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exceptional_clock_in_tokens_token_unique` (`token`),
  KEY `exceptional_clock_in_tokens_user_id_foreign` (`user_id`),
  KEY `exceptional_clock_in_tokens_team_id_foreign` (`team_id`),
  CONSTRAINT `exceptional_clock_in_tokens_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exceptional_clock_in_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
DROP TABLE IF EXISTS `failed_login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL,
  `lockout_time` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `holidays` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `holidays_date_team_id_unique` (`date`,`team_id`),
  KEY `holidays_team_id_foreign` (`team_id`),
  KEY `holidays_date_index` (`date`),
  KEY `holidays_type_index` (`type`),
  CONSTRAINT `holidays_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `impersonation_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `impersonation_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `admin_user_id` bigint(20) unsigned NOT NULL,
  `admin_session_id` varchar(255) NOT NULL,
  `target_user_id` bigint(20) unsigned NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `impersonation_tokens_token_unique` (`token`),
  KEY `impersonation_tokens_target_user_id_foreign` (`target_user_id`),
  KEY `impersonation_tokens_token_index` (`token`),
  KEY `impersonation_tokens_admin_user_id_expires_at_index` (`admin_user_id`,`expires_at`),
  CONSTRAINT `impersonation_tokens_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `impersonation_tokens_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `impersonations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `impersonations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `message_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_user_message_id_foreign` (`message_id`),
  KEY `message_user_user_id_foreign` (`user_id`),
  CONSTRAINT `message_user_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sender_deleted_at` timestamp NULL DEFAULT NULL,
  `sender_purged_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_sender_id_foreign` (`sender_id`),
  KEY `messages_parent_id_index` (`parent_id`),
  CONSTRAINT `messages_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permission_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_name` varchar(255) NOT NULL,
  `action` varchar(50) NOT NULL,
  `result` enum('allowed','denied') DEFAULT NULL,
  `performed_by` bigint(20) unsigned DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `resource_type` varchar(100) DEFAULT NULL,
  `resource_id` bigint(20) unsigned DEFAULT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `permission_audit_log_performed_by_foreign` (`performed_by`),
  KEY `permission_audit_log_team_id_foreign` (`team_id`),
  KEY `permission_audit_log_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `permission_audit_log_permission_name_index` (`permission_name`),
  KEY `permission_audit_log_action_index` (`action`),
  CONSTRAINT `permission_audit_log_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permission_audit_log_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_audit_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `granted_by` bigint(20) unsigned DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_role_permission_id_role_id_unique` (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  KEY `permission_role_granted_by_foreign` (`granted_by`),
  CONSTRAINT `permission_role_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `requires_context` tinyint(1) NOT NULL DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`),
  KEY `permissions_category_index` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_team_id_name_unique` (`team_id`,`name`),
  KEY `roles_created_by_foreign` (`created_by`),
  CONSTRAINT `roles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `roles_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `format` varchar(255) NOT NULL DEFAULT 'html',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_announcements_team_id_foreign` (`team_id`),
  KEY `team_announcements_created_by_foreign` (`created_by`),
  CONSTRAINT `team_announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_announcements_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_invitations_team_id_email_unique` (`team_id`,`email`),
  CONSTRAINT `team_invitations_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `custom_role_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_user_team_id_user_id_unique` (`team_id`,`user_id`),
  KEY `team_user_custom_role_id_foreign` (`custom_role_id`),
  CONSTRAINT `team_user_custom_role_id_foreign` FOREIGN KEY (`custom_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `pdf_engine` varchar(255) NOT NULL DEFAULT 'browsershot',
  `max_report_months` int(11) DEFAULT 3,
  `async_report_threshold_months` int(11) DEFAULT NULL,
  `personal_team` tinyint(1) NOT NULL,
  `event_retention_months` int(11) NOT NULL DEFAULT 60,
  `timezone` varchar(255) DEFAULT NULL,
  `event_expiration_days` int(10) unsigned DEFAULT NULL,
  `force_clock_in_delay` tinyint(1) NOT NULL DEFAULT 0,
  `clock_in_delay_minutes` int(10) unsigned DEFAULT NULL,
  `clock_in_grace_period_minutes` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `special_event_color` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teams_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_meta` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_meta_user_id_meta_key_unique` (`user_id`,`meta_key`),
  KEY `user_meta_user_id_index` (`user_id`),
  KEY `user_meta_meta_key_index` (`meta_key`),
  CONSTRAINT `user_meta_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `valid_from` timestamp NULL DEFAULT NULL,
  `valid_until` timestamp NULL DEFAULT NULL,
  `granted_by` bigint(20) unsigned DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `revoked_by` bigint(20) unsigned DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_permissions_permission_id_foreign` (`permission_id`),
  KEY `user_permissions_team_id_foreign` (`team_id`),
  KEY `user_permissions_granted_by_foreign` (`granted_by`),
  KEY `user_permissions_revoked_by_foreign` (`revoked_by`),
  KEY `user_permissions_user_id_valid_until_index` (`user_id`,`valid_until`),
  CONSTRAINT `user_permissions_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_permissions_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `family_name1` varchar(255) DEFAULT NULL,
  `family_name2` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `max_owned_teams` int(10) unsigned DEFAULT 5,
  `week_starts_on` tinyint(4) NOT NULL DEFAULT 1,
  `vacation_calculation_type` enum('natural','working') NOT NULL DEFAULT 'natural' COMMENT 'Type of vacation calculation: natural (calendar days) or working (excluding weekends/holidays)',
  `vacation_working_days` int(10) unsigned DEFAULT 22 COMMENT 'Number of working days for vacation calculation when type is "working"',
  `geolocation_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` bigint(20) unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `notify_new_messages` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `work_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_centers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `nfc_tag_id` varchar(64) DEFAULT NULL,
  `nfc_tag_description` text DEFAULT NULL,
  `nfc_payload` varchar(500) DEFAULT NULL,
  `nfc_tag_generated_at` timestamp NULL DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_centers_code_unique` (`code`),
  UNIQUE KEY `work_centers_nfc_tag_id_unique` (`nfc_tag_id`),
  KEY `work_centers_team_id_foreign` (`team_id`),
  KEY `work_centers_nfc_tag_id_index` (`nfc_tag_id`),
  KEY `work_centers_nfc_payload_index` (`nfc_payload`),
  CONSTRAINT `work_centers_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2014_10_12_200000_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2020_05_21_100000_create_teams_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2020_05_21_200000_create_team_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2020_05_21_300000_create_team_invitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2022_04_28_180745_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2022_05_17_180450_create_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2023_03_29_122114_add-obs-field',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2024_05_28_113545_events_history',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2024_11_25_170137_resize_events_history_events_column',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2021_09_04_200523_create_event_types_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_08_22_180127_create_user_metas_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_09_07_001317_add_consolidated_fields_to_tables',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_09_10_190112_migrate_event_descriptions_to_event_types',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_09_11_073900_add_observations_to_events_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_09_12_113303_create_messages_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_09_12_113348_create_message_user_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_09_12_113440_add_notify_new_messages_to_users_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_09_12_124404_create_notifications_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_09_12_140206_add_sender_deleted_at_to_messages_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_09_12_141036_add_sender_purged_at_to_messages_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_09_14_052546_create_failed_login_attempts_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_10_14_230531_create_work_centers_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_10_14_230711_add_work_center_id_to_events_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_10_15_183952_add_is_workday_type_to_event_types_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_10_15_195318_create_holidays_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_10_18_075444_add_clock_in_delay_settings_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_10_18_075445_create_exceptional_clock_in_tokens_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_10_18_091100_add_authorized_by_id_to_events_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_10_18_094630_add_is_extra_hours_to_events_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_10_18_102100_add_is_closed_automatically_to_events_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_10_20_164900_add_is_exceptional_to_events_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_10_24_014437_add_event_expiration_days_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_10_24_041825_add_timezone_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_10_26_162900_add_irregular_event_color_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_10_28_100000_add_team_id_to_events_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_10_28_200000_make_color_nullable_in_event_types_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_11_02_131253_add_is_authorizable_to_event_types_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_11_02_223812_add_week_start_preference_to_users_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_11_02_234231_create_team_announcements_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_11_06_162621_update_workday_types_for_all_teams',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_11_06_163036_update_existing_events_extra_hours_logic',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_11_06_163513_ensure_is_extra_hours_default_values',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_11_06_163626_fix_events_without_description',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_11_06_174952_add_pause_event_type_to_teams',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_11_06_185715_add_is_break_type_to_event_types',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_11_06_192042_add_exceptional_event_color_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_11_06_200219_unify_event_color_columns_in_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_11_07_012507_add_nfc_tag_id_to_work_centers_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_11_07_014031_add_nfc_payload_to_work_centers_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_11_08_134528_change_user_code_to_string_in_users_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_11_20_233508_normalize_work_schedule_days_in_user_meta',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_11_24_182605_add_pdf_engine_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_11_24_185028_add_chrome_path_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_11_25_185012_add_report_preferences_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_11_29_111917_remove_chrome_path_from_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_11_29_235028_fix_all_day_events_timezone',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_11_30_224718_fix_all_day_events_end_dates',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_12_01_133300_add_is_admin_to_users_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_12_01_140004_change_events_team_cascade_to_set_null',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_12_01_140021_add_event_retention_period_to_teams_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2025_12_01_140538_create_welcome_team',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_12_01_162833_add_max_owned_teams_to_users_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_12_01_200000_ensure_users_belong_to_single_team',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_12_01_201000_create_team_administrators_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_12_01_202438_assign_team_id_to_legacy_events',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_12_02_013312_drop_team_administrators_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2025_12_09_181436_add_format_to_team_announcements_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2025_12_10_192055_add_is_pause_type_to_event_types_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_12_12_125709_backfill_events_team_and_work_center',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_12_15_162730_fix_all_day_event_dates',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_12_15_185901_add_vacation_preferences_to_users_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2025_12_16_124104_add_geolocation_to_events_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2025_12_16_124125_add_geolocation_enabled_to_users_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2025_12_17_162628_add_ip_address_to_events_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2025_12_21_134849_add_parent_id_to_messages_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2025_12_08_094412_create_permissions_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2025_12_08_110000_create_roles_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2025_12_08_122330_add_custom_role_id_to_team_user_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2025_12_08_122330_create_permission_audit_log_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2025_12_08_122330_create_permission_role_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2025_12_08_122330_create_user_permissions_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2025_12_25_163858_make_user_code_nullable_in_users_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2025_12_28_164922_create_impersonations_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2025_12_28_171639_create_impersonation_tokens_table',16);
