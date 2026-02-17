-- Database: itc127-cs2a-2026
-- Table: tbltickets
-- Purpose: Store support tickets for equipment/system issues
-- Date: 2026-02-17

USE `itc127-cs2a-2026`;

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
