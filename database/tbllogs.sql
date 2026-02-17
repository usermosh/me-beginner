-- Database: itc127-cs2a-2026
-- Table: tbllogs
-- Purpose: Store system activity logs for audit trail
-- Date: 2026-02-17

USE `itc127-cs2a-2026`;

-- ===================================
-- Table: tbllogs
-- ===================================
CREATE TABLE IF NOT EXISTS `tbllogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `datelog` VARCHAR(20) NOT NULL,
  `timelog` VARCHAR(20) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(100) NOT NULL,
  `performedby` VARCHAR(100) NOT NULL,
  `performedto` VARCHAR(100),
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Additional Indexes for tbllogs
-- ===================================
ALTER TABLE `tbllogs` ADD INDEX `idx_action` (`action`);
ALTER TABLE `tbllogs` ADD INDEX `idx_module` (`module`);
ALTER TABLE `tbllogs` ADD INDEX `idx_performedby` (`performedby`);
ALTER TABLE `tbllogs` ADD INDEX `idx_datelog` (`datelog`);
