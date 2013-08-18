-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.12-log - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             8.0.0.4396
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table skeleton.oauth_identities
DROP TABLE IF EXISTS `oauth_identities`;
CREATE TABLE IF NOT EXISTS `oauth_identities` (
  `identity_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned DEFAULT NULL,
  `oauth_id` varchar(64) DEFAULT NULL,
  `oauth_provider` enum('facebook','google') DEFAULT NULL,
  PRIMARY KEY (`identity_id`),
  UNIQUE KEY `oauth_id_oauth_provider` (`oauth_id`,`oauth_provider`),
  KEY `FK_oauth__user_id` (`user_id`),
  CONSTRAINT `FK_oauth_identities__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table skeleton.password_recovery_links
DROP TABLE IF EXISTS `password_recovery_links`;
CREATE TABLE IF NOT EXISTS `password_recovery_links` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `secure_key` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `expires_on` datetime NOT NULL,
  PRIMARY KEY (`link_id`),
  UNIQUE KEY `url` (`secure_key`),
  KEY `FK_password_recovery_links__email` (`email`),
  CONSTRAINT `FK_password_recovery_links__email` FOREIGN KEY (`email`) REFERENCES `user_identities` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table skeleton.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `timezone` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table skeleton.user_cookies
DROP TABLE IF EXISTS `user_cookies`;
CREATE TABLE IF NOT EXISTS `user_cookies` (
  `cookie_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `random_key` varchar(64) NOT NULL,
  `expires_on` datetime NOT NULL,
  PRIMARY KEY (`cookie_id`),
  KEY `random_key` (`random_key`(5)),
  KEY `FK_user_cookies__user_id` (`user_id`),
  KEY `valid_until` (`expires_on`),
  CONSTRAINT `FK_user_cookies__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table skeleton.user_identities
DROP TABLE IF EXISTS `user_identities`;
CREATE TABLE IF NOT EXISTS `user_identities` (
  `identity_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `username` varchar(64) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `email` varchar(64) NOT NULL,
  `status` enum('active','pending','inactive') NOT NULL,
  PRIMARY KEY (`identity_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `FK_user_identities__user_id` (`user_id`),
  CONSTRAINT `FK_user_identitites__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
