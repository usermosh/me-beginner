-- Database: itc127-cs2a-2026
-- Table: tblaccounts
-- Purpose: Store user accounts for authentication
-- Date: 2026-02-07

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
  MODIFIED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Additional Indexes for tblaccounts
-- ===================================
ALTER TABLE `tblaccounts` ADD INDEX `idx_status` (`status`);
ALTER TABLE `tblaccounts` ADD INDEX `idx_usertype` (`usertype`);

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
