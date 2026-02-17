-- ========================================================================
-- ITC127-CS2A-2025-Acobado Database Setup Script
-- ========================================================================
-- This script sets up all required tables for the Technical Management System
-- Last Updated: February 17, 2026
-- ========================================================================

-- Create database
CREATE DATABASE IF NOT EXISTS `itc127-cs2a-2026`;
USE `itc127-cs2a-2026`;

-- ===================================
-- Table: tblaccounts
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
  MODIFIED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_usertype` (`usertype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Table: tbltickets
-- ===================================
CREATE TABLE IF NOT EXISTS `tbltickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticketNumber` VARCHAR(50) NOT NULL UNIQUE,
  `problem` VARCHAR(100) NOT NULL,
  `details` TEXT NOT NULL,
  `status` ENUM('pending', 'inprogress', 'completed') NOT NULL DEFAULT 'pending',
  `createdBy` VARCHAR(100) NOT NULL,
  `dateCreated` VARCHAR(50) NOT NULL,
  `assignedTo` VARCHAR(100),
  `dateAssigned` VARCHAR(50),
  `dateCompleted` VARCHAR(50),
  `approvedBy` VARCHAR(100),
  `dateApproved` VARCHAR(50),
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `ticketNumber` (`ticketNumber`),
  INDEX `status` (`status`),
  INDEX `createdBy` (`createdBy`),
  INDEX `assignedTo` (`assignedTo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Table: tblequipment
-- ===================================
CREATE TABLE IF NOT EXISTS `tblequipment` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assetNumber` VARCHAR(100) NOT NULL UNIQUE,
  `serialNumber` VARCHAR(100),
  `type` VARCHAR(100),
  `branch` VARCHAR(100),
  `department` VARCHAR(100),
  `status` ENUM('ACTIVE', 'MAINTENANCE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `createdby` VARCHAR(100),
  `datecreated` VARCHAR(20),
  CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_assetNumber` (`assetNumber`),
  INDEX `idx_serialNumber` (`serialNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Table: tbllogs (System Activity Logs)
-- ===================================
CREATE TABLE IF NOT EXISTS `tbllogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `datelog` VARCHAR(20) NOT NULL,
  `timelog` VARCHAR(20) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(100) NOT NULL,
  `performedby` VARCHAR(100) NOT NULL,
  `performedto` VARCHAR(100),
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_action` (`action`),
  INDEX `idx_module` (`module`),
  INDEX `idx_performedby` (`performedby`),
  INDEX `idx_datelog` (`datelog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Table: tblticketlogs (Ticket Activity Logs)
-- ===================================
CREATE TABLE IF NOT EXISTS `tblticketlogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticketNumber` VARCHAR(50) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `performedBy` VARCHAR(100) NOT NULL,
  `datePerformed` VARCHAR(50),
  `details` TEXT,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `ticketNumber` (`ticketNumber`),
  INDEX `action` (`action`),
  INDEX `performedBy` (`performedBy`),
  FOREIGN KEY (`ticketNumber`) REFERENCES `tbltickets`(`ticketNumber`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Table: tblequipmentlogs (Equipment Activity Logs)
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
INSERT IGNORE INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`) 
VALUES ('admin', 'admin123', 'ADMINISTRATOR', 'ACTIVE', 'System', '07/02/2026');

-- ===================================
-- Sample Data: Technical Support Account
-- ===================================
INSERT IGNORE INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`) 
VALUES ('technical', 'tech123', 'TECHNICAL', 'ACTIVE', 'System', '07/02/2026');

-- ===================================
-- Sample Data: Regular User Account
-- ===================================
INSERT IGNORE INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`) 
VALUES ('user', 'user123', 'USER', 'ACTIVE', 'System', '07/02/2026');
