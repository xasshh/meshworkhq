-- Meshwork HQ, MySQL schema and reference data.
--
-- Schema compiled by Laravel's own MySQL grammar, so it is identical to
-- what `php artisan migrate` produces on the server. Carries the skill
-- taxonomy, the credit bundles and the migrations ledger. No user data.
-- Generated 2026-08-20

SET NAMES utf8mb4;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `passkeys`;
DROP TABLE IF EXISTS `briefs`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `alerts`;
DROP TABLE IF EXISTS `credit_bundles`;
DROP TABLE IF EXISTS `credit_transactions`;
DROP TABLE IF EXISTS `unlocks`;
DROP TABLE IF EXISTS `conversations`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `payments`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table `users` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `email` varchar(255) not null, `email_verified_at` timestamp null, `password` varchar(255) not null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `users` add unique `users_email_unique`(`email`);
create table `password_reset_tokens` (`email` varchar(255) not null, `token` varchar(255) not null, `created_at` timestamp null, primary key (`email`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `sessions` (`id` varchar(255) not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` text null, `payload` longtext not null, `last_activity` int not null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `sessions` add index `sessions_user_id_index`(`user_id`);
alter table `sessions` add index `sessions_last_activity_index`(`last_activity`);
create table `cache` (`key` varchar(255) not null, `value` mediumtext not null, `expiration` bigint not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `cache` add index `cache_expiration_index`(`expiration`);
create table `cache_locks` (`key` varchar(255) not null, `owner` varchar(255) not null, `expiration` bigint not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `cache_locks` add index `cache_locks_expiration_index`(`expiration`);
create table `jobs` (`id` bigint unsigned not null auto_increment primary key, `queue` varchar(255) not null, `payload` longtext not null, `attempts` smallint unsigned not null, `reserved_at` int unsigned null, `available_at` int unsigned not null, `created_at` int unsigned not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `jobs` add index `jobs_queue_index`(`queue`);
create table `job_batches` (`id` varchar(255) not null, `name` varchar(255) not null, `total_jobs` int not null, `pending_jobs` int not null, `failed_jobs` int not null, `failed_job_ids` longtext not null, `options` mediumtext null, `cancelled_at` int null, `created_at` int not null, `finished_at` int null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(255) not null, `connection` varchar(255) not null, `queue` varchar(255) not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `failed_jobs` add index `failed_jobs_connection_queue_failed_at_index`(`connection`, `queue`, `failed_at`);
alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`);
create table `passkeys` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `name` varchar(255) not null, `credential_id` varchar(255) not null, `credential` json not null, `last_used_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `passkeys` add constraint `passkeys_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `passkeys` add index `passkeys_user_id_index`(`user_id`);
alter table `passkeys` add unique `passkeys_credential_id_unique`(`credential_id`);
alter table `users` add `two_factor_secret` text null after `password`;
alter table `users` add `two_factor_recovery_codes` text null after `two_factor_secret`;
alter table `users` add `two_factor_confirmed_at` timestamp null after `two_factor_recovery_codes`;
alter table `users` add `role` varchar(255) not null default 'client' after `email_verified_at`;
alter table `users` add `credits` int unsigned not null default '0' after `role`;
alter table `users` add `professional_title` varchar(255) null after `name`;
alter table `users` add `phone` varchar(50) null after `email_verified_at`;
alter table `users` add `bio` text null after `phone`;
alter table `users` add `portfolio_url` varchar(255) null after `bio`;
alter table `users` add `skill_tags` json null after `portfolio_url`;
alter table `users` add `company_name` varchar(255) null after `skill_tags`;
alter table `users` add `company_size` varchar(50) null after `company_name`;
alter table `users` add `company_role` varchar(255) null after `company_size`;
alter table `users` add `company_description` text null after `company_role`;
alter table `users` add `company_services` text null after `company_description`;
create table `briefs` (`id` bigint unsigned not null auto_increment primary key, `ulid` varchar(26) not null, `client_id` bigint unsigned not null, `title` varchar(255) not null, `description` text not null, `budget_min` int unsigned null, `budget_max` int unsigned null, `skill_tags` json null, `location` varchar(255) null, `is_remote` tinyint(1) not null default '0', `language` varchar(10) not null default 'en', `status` varchar(30) not null default 'draft', `published_at` timestamp null, `expires_at` timestamp null, `hired_professional_id` bigint unsigned null, `alert_wave` smallint unsigned not null default '0', `total_alerts_sent` int unsigned not null default '0', `total_unlocks` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `briefs` add constraint `briefs_client_id_foreign` foreign key (`client_id`) references `users` (`id`) on delete cascade;
alter table `briefs` add constraint `briefs_hired_professional_id_foreign` foreign key (`hired_professional_id`) references `users` (`id`) on delete set null;
alter table `briefs` add index `briefs_client_id_status_index`(`client_id`, `status`);
alter table `briefs` add index `briefs_status_expires_at_index`(`status`, `expires_at`);
alter table `briefs` add unique `briefs_ulid_unique`(`ulid`);
create table `skills` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `slug` varchar(255) not null, `category` varchar(100) not null, `is_active` tinyint(1) not null default '1', `sort_order` smallint unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `skills` add index `skills_category_is_active_index`(`category`, `is_active`);
alter table `skills` add unique `skills_slug_unique`(`slug`);
create table `alerts` (`id` bigint unsigned not null auto_increment primary key, `brief_id` bigint unsigned not null, `professional_id` bigint unsigned not null, `wave` tinyint unsigned not null default '1', `status` varchar(20) not null default 'notified', `ai_relevance_score` float(53) null, `notified_at` timestamp null, `viewed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `alerts` add constraint `alerts_brief_id_foreign` foreign key (`brief_id`) references `briefs` (`id`) on delete cascade;
alter table `alerts` add constraint `alerts_professional_id_foreign` foreign key (`professional_id`) references `users` (`id`) on delete cascade;
alter table `alerts` add unique `alerts_brief_id_professional_id_unique`(`brief_id`, `professional_id`);
alter table `alerts` add index `alerts_professional_id_status_index`(`professional_id`, `status`);
alter table `alerts` add index `alerts_brief_id_wave_index`(`brief_id`, `wave`);
create table `credit_bundles` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(100) not null, `credits` smallint unsigned not null, `price_kobo` int unsigned not null, `is_active` tinyint(1) not null default '1', `sort_order` smallint unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `credit_transactions` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `type` varchar(20) not null, `amount` int not null, `balance_after` int unsigned not null, `reference` varchar(255) not null, `description` varchar(255) null, `related_type` varchar(255) null, `related_id` bigint unsigned null, `expires_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `credit_transactions` add constraint `credit_transactions_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `credit_transactions` add index `credit_transactions_related_type_related_id_index`(`related_type`, `related_id`);
alter table `credit_transactions` add index `credit_transactions_user_id_type_index`(`user_id`, `type`);
alter table `credit_transactions` add index `credit_transactions_user_id_created_at_index`(`user_id`, `created_at`);
alter table `credit_transactions` add unique `credit_transactions_reference_unique`(`reference`);
create table `unlocks` (`id` bigint unsigned not null auto_increment primary key, `brief_id` bigint unsigned not null, `professional_id` bigint unsigned not null, `alert_id` bigint unsigned null, `credits_spent` tinyint unsigned not null default '1', `status` varchar(20) not null default 'active', `ai_fit_score` float(53) null, `unlocked_at` timestamp not null, `pitch_sent_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `unlocks` add constraint `unlocks_brief_id_foreign` foreign key (`brief_id`) references `briefs` (`id`) on delete cascade;
alter table `unlocks` add constraint `unlocks_professional_id_foreign` foreign key (`professional_id`) references `users` (`id`) on delete cascade;
alter table `unlocks` add constraint `unlocks_alert_id_foreign` foreign key (`alert_id`) references `alerts` (`id`) on delete set null;
alter table `unlocks` add unique `unlocks_brief_id_professional_id_unique`(`brief_id`, `professional_id`);
alter table `unlocks` add index `unlocks_professional_id_status_index`(`professional_id`, `status`);
create table `conversations` (`id` bigint unsigned not null auto_increment primary key, `brief_id` bigint unsigned not null, `client_id` bigint unsigned not null, `professional_id` bigint unsigned not null, `unlock_id` bigint unsigned null, `last_message_at` timestamp null, `archived_by_client_at` timestamp null, `archived_by_professional_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `conversations` add constraint `conversations_brief_id_foreign` foreign key (`brief_id`) references `briefs` (`id`) on delete cascade;
alter table `conversations` add constraint `conversations_client_id_foreign` foreign key (`client_id`) references `users` (`id`) on delete cascade;
alter table `conversations` add constraint `conversations_professional_id_foreign` foreign key (`professional_id`) references `users` (`id`) on delete cascade;
alter table `conversations` add constraint `conversations_unlock_id_foreign` foreign key (`unlock_id`) references `unlocks` (`id`) on delete set null;
alter table `conversations` add unique `conversations_brief_id_professional_id_unique`(`brief_id`, `professional_id`);
alter table `conversations` add index `conversations_client_id_last_message_at_index`(`client_id`, `last_message_at`);
alter table `conversations` add index `conversations_professional_id_last_message_at_index`(`professional_id`, `last_message_at`);
create table `messages` (`id` bigint unsigned not null auto_increment primary key, `conversation_id` bigint unsigned not null, `sender_id` bigint unsigned not null, `body` text not null, `attachment_path` varchar(255) null, `read_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `messages` add constraint `messages_conversation_id_foreign` foreign key (`conversation_id`) references `conversations` (`id`) on delete cascade;
alter table `messages` add constraint `messages_sender_id_foreign` foreign key (`sender_id`) references `users` (`id`) on delete cascade;
alter table `messages` add index `messages_conversation_id_created_at_index`(`conversation_id`, `created_at`);
create table `notifications` (`id` char(36) not null, `type` varchar(255) not null, `notifiable_type` varchar(255) not null, `notifiable_id` bigint unsigned not null, `data` text not null, `read_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `notifications` add index `notifications_notifiable_type_notifiable_id_index`(`notifiable_type`, `notifiable_id`);
alter table `users` add `avatar_path` varchar(255) null after `skill_tags`;
alter table `users` add `logo_path` varchar(255) null after `company_services`;
alter table `users` add `show_hire_count` tinyint(1) not null default '1' after `skill_tags`;
alter table `users` add `show_engagement_count` tinyint(1) not null default '1' after `show_hire_count`;
alter table `users` add `verification_status` varchar(255) not null default 'unverified';
alter table `users` add `verification_type` varchar(255) null;
alter table `users` add `verification_reference` varchar(255) null;
alter table `users` add `verification_document_path` varchar(255) null;
alter table `users` add `verification_legal_name` varchar(255) null;
alter table `users` add `verification_submitted_at` timestamp null;
alter table `users` add `verification_reviewed_at` timestamp null;
alter table `users` add `verification_notes` text null;
alter table `users` add index `users_verification_status_index`(`verification_status`);
create table `payments` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `credit_bundle_id` bigint unsigned null, `reference` varchar(255) not null, `gateway` varchar(255) not null default 'paystack', `gateway_reference` varchar(255) null, `amount_kobo` bigint unsigned not null, `credits` int unsigned not null, `status` varchar(255) not null default 'pending', `paid_at` timestamp null, `failure_reason` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `payments` add constraint `payments_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `payments` add constraint `payments_credit_bundle_id_foreign` foreign key (`credit_bundle_id`) references `credit_bundles` (`id`) on delete set null;
alter table `payments` add index `payments_user_id_status_index`(`user_id`, `status`);
alter table `payments` add unique `payments_reference_unique`(`reference`);
alter table `payments` add index `payments_status_index`(`status`);

-- skills: 40 rows
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Brand Strategy', 'brand-strategy', 'creative', 1, 1, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Graphic Design', 'graphic-design', 'creative', 1, 2, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Logo & Identity', 'logo-identity', 'creative', 1, 3, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 'Illustration', 'illustration', 'creative', 1, 4, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (5, 'Motion Graphics', 'motion-graphics', 'creative', 1, 5, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (6, 'Photography', 'photography', 'creative', 1, 6, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (7, 'Videography', 'videography', 'creative', 1, 7, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (8, 'Copywriting', 'copywriting', 'creative', 1, 8, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (9, 'Content Strategy', 'content-strategy', 'creative', 1, 9, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (10, 'UX/UI Design', 'uxui-design', 'creative', 1, 10, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (11, 'Web Development', 'web-development', 'digital', 1, 11, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (12, 'Mobile App Development', 'mobile-app-development', 'digital', 1, 12, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (13, 'Social Media Management', 'social-media-management', 'digital', 1, 13, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (14, 'SEO & SEM', 'seo-sem', 'digital', 1, 14, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (15, 'Paid Advertising', 'paid-advertising', 'digital', 1, 15, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (16, 'Email Marketing', 'email-marketing', 'digital', 1, 16, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (17, 'Data Analytics', 'data-analytics', 'digital', 1, 17, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (18, 'E-commerce', 'e-commerce', 'digital', 1, 18, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (19, 'Influencer Marketing', 'influencer-marketing', 'digital', 1, 19, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (20, 'Community Management', 'community-management', 'digital', 1, 20, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (21, 'Event Planning', 'event-planning', 'events', 1, 21, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (22, 'Event Production', 'event-production', 'events', 1, 22, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (23, 'MC / Host', 'mc-host', 'events', 1, 23, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (24, 'DJ / Music', 'dj-music', 'events', 1, 24, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (25, 'Catering & Food', 'catering-food', 'events', 1, 25, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (26, 'Decoration & Styling', 'decoration-styling', 'events', 1, 26, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (27, 'Live Entertainment', 'live-entertainment', 'events', 1, 27, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (28, 'Venue Management', 'venue-management', 'events', 1, 28, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (29, 'Public Relations', 'public-relations', 'pr_comms', 1, 29, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (30, 'Media Relations', 'media-relations', 'pr_comms', 1, 30, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (31, 'Crisis Communications', 'crisis-communications', 'pr_comms', 1, 31, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (32, 'Influencer Relations', 'influencer-relations', 'pr_comms', 1, 32, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (33, 'Press & Editorial', 'press-editorial', 'pr_comms', 1, 33, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (34, 'Business Development', 'business-development', 'business', 1, 34, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (35, 'Market Research', 'market-research', 'business', 1, 35, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (36, 'Strategy Consulting', 'strategy-consulting', 'business', 1, 36, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (37, 'Financial Modelling', 'financial-modelling', 'business', 1, 37, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (38, 'Legal & Compliance', 'legal-compliance', 'business', 1, 38, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (39, 'HR & Recruitment', 'hr-recruitment', 'business', 1, 39, '2026-06-11 18:14:42', '2026-06-11 18:14:42');
INSERT INTO `skills` (`id`, `name`, `slug`, `category`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (40, 'Drone Videography', 'drone-videography', 'Creative', 1, 0, '2026-08-19 10:16:01', '2026-08-19 10:16:01');

-- credit_bundles: 5 rows
INSERT INTO `credit_bundles` (`id`, `name`, `credits`, `price_kobo`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Trial Pack', 3, 150000, 1, 1, '2026-08-19 09:02:31', '2026-08-19 09:02:31');
INSERT INTO `credit_bundles` (`id`, `name`, `credits`, `price_kobo`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Starter Pack', 5, 250000, 1, 2, '2026-08-19 09:02:31', '2026-08-19 09:02:31');
INSERT INTO `credit_bundles` (`id`, `name`, `credits`, `price_kobo`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Growth Pack', 20, 800000, 1, 3, '2026-08-19 09:02:31', '2026-08-19 09:02:31');
INSERT INTO `credit_bundles` (`id`, `name`, `credits`, `price_kobo`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 'Pro Pack', 50, 1750000, 1, 4, '2026-08-19 09:02:31', '2026-08-19 09:02:31');
INSERT INTO `credit_bundles` (`id`, `name`, `credits`, `price_kobo`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (5, 'Power Pack', 150, 4500000, 1, 5, '2026-08-19 09:02:31', '2026-08-19 09:02:31');

-- migrations: 21 rows
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4, '2024_01_01_000000_create_passkeys_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6, '2026_06_06_205719_add_role_and_credits_to_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7, '2026_06_06_235914_add_profile_fields_to_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8, '2026_06_07_094140_create_briefs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9, '2026_06_07_094140_create_skills_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10, '2026_06_07_094140_z_create_alerts_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11, '2026_06_07_094141_create_credit_bundles_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12, '2026_06_07_094141_create_credit_transactions_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13, '2026_06_07_094141_create_unlocks_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14, '2026_06_07_094142_create_conversations_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15, '2026_06_07_094142_create_messages_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16, '2026_06_07_094142_create_notifications_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17, '2026_06_08_185425_add_avatar_and_logo_to_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18, '2026_08_19_131431_add_trust_signals_to_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19, '2026_08_19_132105_add_verification_to_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20, '2026_08_19_153650_create_payments_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21, '2026_08_19_163026_backfill_email_verified_at_for_existing_users', 1);

SET FOREIGN_KEY_CHECKS = 1;
