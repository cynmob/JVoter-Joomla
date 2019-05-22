SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `jvoter`
--

-- --------------------------------------------------------

--
-- Table structure for table `#__jvoter_contests`
--

CREATE TABLE IF NOT EXISTS `#__jvoter_contests` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `alias` VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '', 
  `description` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `moderated` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` VARCHAR(255) NOT NULL DEFAULT 'pending' COMMENT 'pending, denied, active, completed', 
  `type` ENUM('photo','video','simple') NOT NULL DEFAULT 'photo',
  `catid` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `plan_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_by_alias` VARCHAR(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `attribs` varchar(5120) NOT NULL,  
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `access` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `hits` int(10) UNSIGNED NOT NULL DEFAULT '0',  
  `featured` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Set if contest is featured.',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),  
  KEY `idx_alias` (`alias`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__jvoter_entries`
--

CREATE TABLE IF NOT EXISTS `#__jvoter_entries` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `title` varchar(250) NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `state` tinyint(3) NOT NULL DEFAULT '0',  
  `status` varchar(255) NOT NULL DEFAULT 'pending' COMMENT 'pending, denied, active, completed', 
  `moderated` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `images` text NOT NULL,
  `description` text NOT NULL, 
  `vote` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,  
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `access` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),  
  KEY `idx_contest_id` (`contest_id`),
  KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__jvoter_media`
--

CREATE TABLE IF NOT EXISTS `#__jvoter_media` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `title` VARCHAR(255) NOT NULL DEFAULT '', 
  `description` text NOT NULL,
  `params` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',  
  `primary` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `thumb` VARCHAR(255) DEFAULT NULL,
  `path` VARCHAR(255) NOT NULL,
  `mimetype` VARCHAR(64) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,  
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`), 
  KEY `idx_entry_id` (`entry_id`),
  KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__jvoter_features`
--

CREATE TABLE IF NOT EXISTS `#__jvoter_features` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(250) NOT NULL DEFAULT '',
  `namekey` varchar(50) NOT NULL,  
  `value` text NOT NULL,
  `description` text NOT NULL COMMENT 'Used as tooltip or field description.',  
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `type` varchar(50) DEFAULT NULL COMMENT 'can be varchar, boolean, select, etc',  
  `core` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `translate` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,  
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `note` varchar(255) NOT NULL DEFAULT '',
  `access` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `namekey` (`namekey`),
  KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__jvoter_plans`
--

CREATE TABLE IF NOT EXISTS `#__jvoter_plans` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `price` DECIMAL(15,2) NOT NULL DEFAULT '0.00' ,
  `features` text NOT NULL,
  `description` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0', 
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,  
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`), 
  KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__jvoter_rating`
--

CREATE TABLE IF NOT EXISTS `#__jvoter_rating` (
  `entry_id` int(11) NOT NULL DEFAULT 0,
  `rating_sum` int(10) unsigned NOT NULL DEFAULT 0,
  `rating_count` int(10) unsigned NOT NULL DEFAULT 0,
  `lastip` varchar(50) NOT NULL DEFAULT '', 
  PRIMARY KEY (`entry_id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__jvoter_date_orders`
--

CREATE TABLE IF NOT EXISTS `#__jvoter_date_orders` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contest_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL DEFAULT '',  
  `images` text NOT NULL,
  `description` text NOT NULL,  
  `checked_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,  
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'approve or not',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_contest_id` (`contest_id`),
  KEY `idx_status` (`status`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__jvoter_orders`
--

CREATE TABLE IF NOT EXISTS `#__jvoter_orders` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,	
	`user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
	`status` varchar(255) NOT NULL DEFAULT '',
	`type` varchar(50) NOT NULL DEFAULT 'entry' COMMENT 'contest, entry, buydate',	
	`item_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'contest ID, entry ID, date order ID',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,  
  	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,	
	`invoice_number` varchar(255) NOT NULL DEFAULT '' COMMENT 'The Invoice Number that Can be Set by Admins or auto-generated',	
	`currency` VARCHAR(5) NOT NULL ,	
	`total` DECIMAL(15,5) NOT NULL DEFAULT '0.00' ,    
	`tax_info` text NOT NULL DEFAULT '',
	`discount_code` varchar(255) NOT NULL DEFAULT '',
	`discount_price` decimal(15,2) NOT NULL DEFAULT '0.00',
	`discount_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
	`payment_id` varchar(255) NOT NULL DEFAULT '',
	`payment_method` varchar(255) NOT NULL DEFAULT '',
	`payment_price` decimal(15,2) NOT NULL DEFAULT '0.00',
	`payment_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
	`payment_params` text NOT NULL DEFAULT '',	
	`ip` varchar(255) NOT NULL DEFAULT '',	
	PRIMARY KEY (`id`),
	KEY `idx_createdby` (`created_by`),
	KEY `idx_status` (`status`),
	KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;