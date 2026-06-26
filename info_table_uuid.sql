-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `first_api`;
USE `first_api`;

-- Drop table if exists to avoid conflicts
DROP TABLE IF EXISTS `info`;

-- Create 'info' table with UUID primary key
CREATE TABLE `info` (
    `uuid` CHAR(36) NOT NULL PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email_address` VARCHAR(255) NOT NULL UNIQUE,
    `phone_no` VARCHAR(20) NOT NULL,
    `gender` VARCHAR(10) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `password_reset_token` VARCHAR(255) NULL,
    `password_reset_expires_at` DATETIME NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 