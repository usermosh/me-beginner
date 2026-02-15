-- Database: itc127-cs2a-2026
-- Table: tblequipment
-- Purpose: Store equipment/asset information
-- Date: 2026-02-07

USE `itc127-cs2a-2026`;

-- ===================================
-- Table: tblequipment
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
  `status` ENUM('WORKING', 'ON-REPAIR', 'RETIRED') NOT NULL DEFAULT 'WORKING',
  `createdby` VARCHAR(100),
  `datecreated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `datemodified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modifiedby` VARCHAR(100),
  `dateDeleted` VARCHAR(20),
  `deletedby` VARCHAR(100),
  INDEX `idx_status` (`status`),
  INDEX `idx_branch` (`branch`),
  INDEX `idx_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
