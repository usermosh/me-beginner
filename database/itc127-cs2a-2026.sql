-- Database: itc127-cs2a-2026
-- Generated SQL file for phpAdmin import
-- Date: 2026-02-07

-- Create Database
CREATE DATABASE IF NOT EXISTS `itc127-cs2a-2026`;
USE `itc127-cs2a-2026`;

-- ===================================
-- Table: tblaccounts
-- Purpose: Store user accounts for authentication
-- ===================================
CREATE TABLE IF NOT EXISTS `tblaccounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `usertype` ENUM('ADMINISTRATOR', 'TECHNICAL', 'USER') NOT NULL,
  `status` ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `createdby` VARCHAR(100),
  `datecreated` VARCHAR(20),
  `datemodified` VARCHAR(20),
  `modifiedby` VARCHAR(100),
  `dateDeleted` VARCHAR(20),
  `deletedby` VARCHAR(100),
  CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  MODIFIED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Table: tblequipment
-- Purpose: Store equipment/asset information
-- ===================================
CREATE TABLE IF NOT EXISTS `tblequipment` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assetNumber` VARCHAR(100) NOT NULL UNIQUE,
  `serialNumber` VARCHAR(100) NOT NULL UNIQUE,
  `type` VARCHAR(100) NOT NULL,
  `manufacturer` VARCHAR(100),
  `yearModel` INT,
  `description` TEXT,
  `branch` VARCHAR(255),
  `department` VARCHAR(255),
  `status` ENUM('WORKING', 'DEFECTIVE', 'LOST', 'DECOMMISSIONED') NOT NULL DEFAULT 'WORKING',
  `createdby` VARCHAR(100),
  `datecreated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `datemodified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modifiedby` VARCHAR(100),
  `dateDeleted` VARCHAR(20),
  `deletedby` VARCHAR(100),
  INDEX `assetNumber` (`assetNumber`),
  INDEX `serialNumber` (`serialNumber`),
  INDEX `status` (`status`),
  INDEX `branch` (`branch`),
  INDEX `department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Table: tblequipmentlogs
-- Purpose: Track all changes and actions performed on equipment
-- ===================================
CREATE TABLE IF NOT EXISTS `tblequipmentlogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `datelog` VARCHAR(20),
  `timelog` VARCHAR(20),
  `action` VARCHAR(255),
  `module` VARCHAR(255),
  `performedby` VARCHAR(100),
  `equipmentId` INT,
  `assetNumber` VARCHAR(100),
  `details` TEXT,
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `equipmentId` (`equipmentId`),
  INDEX `assetNumber` (`assetNumber`),
  INDEX `datelog` (`datelog`),
  FOREIGN KEY (`equipmentId`) REFERENCES `tblequipment`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Sample Data: Administrator Account
-- ===================================
INSERT INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`) 
VALUES ('admin', 'admin123', 'ADMINISTRATOR', 'ACTIVE', 'System', '07/02/2026');

-- ===================================
-- Sample Data: Technical Support Account
-- ===================================
INSERT INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`) 
VALUES ('technical', 'tech123', 'TECHNICAL', 'ACTIVE', 'System', '07/02/2026');

-- ===================================
-- Sample Data: Regular User Account
-- ===================================
INSERT INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`) 
VALUES ('user', 'user123', 'USER', 'ACTIVE', 'System', '07/02/2026');

-- ===================================
-- Indexes and Constraints
-- ===================================
ALTER TABLE `tblaccounts` ADD INDEX `username` (`username`);
ALTER TABLE `tblaccounts` ADD INDEX `status` (`status`);
ALTER TABLE `tblaccounts` ADD INDEX `usertype` (`usertype`);

-- ===================================
-- Database Setup Complete
-- Database Name: itc127-cs2a-2026
-- Tables Created: 3
-- Sample Accounts: 3 (admin, technical, user)
-- ===================================
