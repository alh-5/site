-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `if0_40960042_talent_coins`;
USE `if0_40960042_talent_coins`;

-- Table for storing talent coins
CREATE TABLE IF NOT EXISTS `talent_coins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `coin_id` INT(11) NOT NULL,
  `coin_name` VARCHAR(100) NOT NULL,
  `coin_count` INT(11) DEFAULT 0,
  `coin_note` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_coin` (`user_id`, `coin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for storing system state
CREATE TABLE IF NOT EXISTS `system_state` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `multiplier_active` TINYINT(1) DEFAULT 0,
  `multiplier_rate` INT(11) DEFAULT 1,
  `multiplier_seconds` INT(11) DEFAULT 0,
  `current_page` INT(11) DEFAULT 1,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_state` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;