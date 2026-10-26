-- ==============================================================================
-- SIDIS Publication Tables Schema
-- Tables: indpublication, journalpub, confrncepub
-- Linked with faculty table by faculty_id / id
-- ==============================================================================

-- 1. General Publications Table
CREATE TABLE IF NOT EXISTS `indpublication` (
  `pub_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` int(10) UNSIGNED NOT NULL,
  `faculty_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'publication',
  `title` text NOT NULL,
  `author` text NOT NULL,
  `journal` varchar(500) DEFAULT '',
  `volume` varchar(100) DEFAULT '',
  `page` varchar(100) DEFAULT '',
  `doi` varchar(255) DEFAULT '',
  `doilink` varchar(500) DEFAULT '',
  `year` varchar(50) DEFAULT '',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`pub_id`),
  KEY `idx_id` (`id`),
  KEY `idx_faculty_id` (`faculty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Journal Publications Table
CREATE TABLE IF NOT EXISTS `journalpub` (
  `pub_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` int(10) UNSIGNED NOT NULL,
  `faculty_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'journal',
  `title` text NOT NULL,
  `author` text NOT NULL,
  `journal` varchar(500) DEFAULT '',
  `volume` varchar(100) DEFAULT '',
  `page` varchar(100) DEFAULT '',
  `doi` varchar(255) DEFAULT '',
  `doilink` varchar(500) DEFAULT '',
  `year` varchar(50) DEFAULT '',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`pub_id`),
  KEY `idx_id` (`id`),
  KEY `idx_faculty_id` (`faculty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Conference Proceedings Table
CREATE TABLE IF NOT EXISTS `confrncepub` (
  `pub_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` int(10) UNSIGNED NOT NULL,
  `faculty_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'conference',
  `title` text NOT NULL,
  `author` text NOT NULL,
  `conference` varchar(500) DEFAULT '',
  `volume` varchar(100) DEFAULT '',
  `page` varchar(100) DEFAULT '',
  `doi` varchar(255) DEFAULT '',
  `doilink` varchar(500) DEFAULT '',
  `year` varchar(50) DEFAULT '',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`pub_id`),
  KEY `idx_id` (`id`),
  KEY `idx_faculty_id` (`faculty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
