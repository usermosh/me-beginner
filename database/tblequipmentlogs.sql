-- Database: itc127-cs2a-2026
-- Table: tblequipmentlogs
-- Purpose: Track all changes and actions performed on equipment
-- Date: 2026-02-07

USE `itc127-cs2a-2026`;

-- ===================================
-- Table: tblequipmentlogs
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
