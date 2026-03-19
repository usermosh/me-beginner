-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2026 at 03:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `itc127-cs2a-2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblticketlogs`
--

CREATE TABLE `tblticketlogs` (
  `logId` int(11) NOT NULL,
  `ticketNumber` varchar(20) NOT NULL,
  `action` varchar(50) NOT NULL,
  `performedBy` varchar(100) NOT NULL,
  `datePerformed` varchar(30) NOT NULL,
  `details` longtext DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblticketlogs`
--

INSERT INTO `tblticketlogs` (`logId`, `ticketNumber`, `action`, `performedBy`, `datePerformed`, `details`, `createdAt`) VALUES
(10, '20260319220406', 'created', 'user', '03/19/2026 10:04 PM', 'Problem: software, Details: sdasd', '2026-03-19 14:04:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblticketlogs`
--
ALTER TABLE `tblticketlogs`
  ADD PRIMARY KEY (`logId`),
  ADD KEY `idx_ticketNumber` (`ticketNumber`),
  ADD KEY `idx_performedBy` (`performedBy`),
  ADD KEY `idx_datePerformed` (`datePerformed`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblticketlogs`
--
ALTER TABLE `tblticketlogs`
  MODIFY `logId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblticketlogs`
--
ALTER TABLE `tblticketlogs`
  ADD CONSTRAINT `tblticketlogs_ibfk_1` FOREIGN KEY (`ticketNumber`) REFERENCES `tbltickets` (`ticketNumber`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
