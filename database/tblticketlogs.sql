-- Database: itc127-cs2a-2026
-- Table: tblticketlogs
-- Purpose: Track all changes and actions performed on support tickets
-- Date: 2026-02-17

USE `itc127-cs2a-2026`;

-- ===================================
-- Table: tblticketlogs
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
