-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 07:06 PM
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
-- Database: `mypetakomv2`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `AttendanceID` varchar(10) NOT NULL,
  `UserID` varchar(10) NOT NULL,
  `EventID` varchar(10) NOT NULL,
  `AttendanceTime` time DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `AttendanceStatus` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`AttendanceID`, `UserID`, `EventID`, `AttendanceTime`, `Location`) VALUES
('A001', 'CB23001', 'E001', '10:15:00', 'DK1'),
('A002', 'CB23002', 'E001', '10:20:00', 'DK1'),
('A003', 'CB23003', 'E002', '09:10:00', 'Hall A'),
('A004', 'CB23004', 'E002', '09:05:00', 'Hall A'),
('A005', 'CB23005', 'E003', '11:10:00', 'Expo Centre'),
('A006', 'CB23006', 'E004', '08:15:00', 'Lab 3'),
('A007', 'CB23007', 'E005', '14:10:00', 'DK2'),
('A008', 'CB23008', 'E006', '10:05:00', 'Room B2'),
('A009', 'CB23009', 'E007', '08:35:00', 'Stadium'),
('A010', 'CB23010', 'E008', '13:05:00', 'Field Area');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_slot`
--

CREATE TABLE `attendance_slot` (
  `AttendanceID` varchar(10) NOT NULL,
  `EventID` varchar(10) NOT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `AttendanceStartTime` time DEFAULT NULL,
  `AttendanceEndTime` time DEFAULT NULL,
  `QRCodeAttendance` varchar(255) DEFAULT NULL,
  `AttendanceDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_slot`
--

INSERT INTO `attendance_slot` (`AttendanceID`, `EventID`, `Location`, `AttendanceStartTime`, `AttendanceEndTime`, `QRCodeAttendance`, `AttendanceDate`) VALUES
('A001', 'E001', 'DK1', '10:00:00', '11:00:00', 'qr_slot_E001.png', '2024-06-10'),
('A002', 'E002', 'Hall A', '09:00:00', '10:00:00', 'qr_slot_E002.png', '2024-06-15'),
('A003', 'E003', 'Expo Centre', '11:00:00', '12:00:00', 'qr_slot_E003.png', '2024-06-20'),
('A004', 'E004', 'Lab 3', '08:00:00', '09:00:00', 'qr_slot_E004.png', '2024-06-25'),
('A005', 'E005', 'DK2', '14:00:00', '15:00:00', 'qr_slot_E005.png', '2024-07-01'),
('A006', 'E006', 'Room B2', '10:00:00', '11:00:00', 'qr_slot_E006.png', '2024-07-05'),
('A007', 'E007', 'Stadium', '08:30:00', '09:30:00', 'qr_slot_E007.png', '2024-07-10'),
('A008', 'E008', 'Field Area', '13:00:00', '14:00:00', 'qr_slot_E008.png', '2024-07-15'),
('A009', 'E009', 'Gallery', '12:00:00', '13:00:00', 'qr_slot_E009.png', '2024-07-20'),
('A010', 'E010', 'Auditorium', '09:00:00', '10:00:00', 'qr_slot_E010.png', '2024-07-25');

-- --------------------------------------------------------

--
-- Table structure for table `committee`
--

CREATE TABLE `committee` (
  `CommitteeID` varchar(10) NOT NULL,
  `EventID` varchar(10) NOT NULL,
  `C_RoleID` varchar(10) NOT NULL,
  `UserID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `committee`
--

INSERT INTO `committee` (`CommitteeID`, `EventID`, `C_RoleID`, `UserID`) VALUES
('C001', 'E001', 'EC001', 'CB23001'),
('C002', 'E001', 'EC002', 'CB23002'),
('C003', 'E001', 'EC003', 'CB23003'),
('C004', 'E001', 'EC004', 'CB23004'),
('C005', 'E001', 'EC005', 'CB23005'),
('C006', 'E002', 'EC001', 'CB23006'),
('C007', 'E002', 'EC002', 'CB23007'),
('C008', 'E002', 'EC003', 'CB23008'),
('C009', 'E002', 'EC004', 'CB23009'),
('C010', 'E002', 'EC005', 'CB23010'),
('C011', 'E003', 'EC001', 'CB23011'),
('C012', 'E003', 'EC002', 'CB23012'),
('C013', 'E003', 'EC003', 'CB23013'),
('C014', 'E004', 'EC001', 'CB23014'),
('C015', 'E004', 'EC002', 'CB23015'),
('C016', 'E005', 'EC001', 'CB23016'),
('C017', 'E005', 'EC002', 'CB23017'),
('C018', 'E005', 'EC003', 'CB23018'),
('C019', 'E006', 'EC001', 'CB23019'),
('C020', 'E006', 'EC002', 'CB23020'),
('C021', 'E007', 'EC001', 'CB23001'),
('C022', 'E007', 'EC003', 'CB23002'),
('C023', 'E008', 'EC001', 'CB23003'),
('C024', 'E008', 'EC002', 'CB23004'),
('C025', 'E009', 'EC001', 'CB23005'),
('C026', 'E010', 'EC001', 'CB23006'),
('C027', 'E010', 'EC002', 'CB23007'),
('C028', 'E010', 'EC003', 'CB23008');

-- --------------------------------------------------------

--
-- Table structure for table `committee_role`
--

CREATE TABLE `committee_role` (
  `C_RoleID` varchar(10) NOT NULL,
  `Description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `committee_role`
--

INSERT INTO `committee_role` (`C_RoleID`, `Description`) VALUES
('EC001', 'Leader'),
('EC002', 'Secretary'),
('EC003', 'Treasurer'),
('EC004', 'Logistics Coordinator'),
('EC005', 'Multimedia & Promotion');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `EventID` varchar(10) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Date` date NOT NULL,
  `Time` time NOT NULL,
  `Location` varchar(100) NOT NULL,
  `Status` enum('Completed','Cancelled','Upcoming') NOT NULL,
  `ApprovalLetter` varchar(255) NOT NULL,
  `QRCode` varchar(255) NOT NULL,
  `UserID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`EventID`, `Title`, `Description`, `Date`, `Time`, `Location`, `Status`, `ApprovalLetter`, `QRCode`, `UserID`) VALUES
('E001', 'Tech Talk 2024', 'A talk on latest technology trends', '2024-06-10', '10:00:00', 'DK1', 'Completed', 'approval_E001.pdf', 'qr_E001.png', 'EA001'),
('E002', 'Career Fair', 'Student career fair with multiple companies', '2024-06-15', '09:00:00', 'Hall A', 'Completed', 'approval_E002.pdf', 'qr_E002.png', 'EA001'),
('E003', 'Innovation Expo', 'Showcase of student innovation projects', '2024-06-20', '11:00:00', 'Expo Centre', 'Completed', 'approval_E003.pdf', 'qr_E003.png', 'EA002'),
('E004', 'Coding Competition', 'A day-long coding hackathon', '2024-06-25', '08:00:00', 'Lab 3', 'Upcoming', 'approval_E004.pdf', 'qr_E004.png', 'EA002'),
('E005', 'Entrepreneurship Talk', 'Talk from successful alumni', '2024-07-01', '14:00:00', 'DK2', 'Completed', 'approval_E005.pdf', 'qr_E005.png', 'EA003'),
('E006', 'Digital Marketing Workshop', 'Workshop on social media marketing', '2024-07-05', '10:00:00', 'Room B2', 'Upcoming', 'approval_E006.pdf', 'qr_E006.png', 'EA003'),
('E007', 'Sports Day', 'Annual university sports day', '2024-07-10', '08:30:00', 'Stadium', 'Cancelled', 'approval_E007.pdf', 'qr_E007.png', 'EA001'),
('E008', 'Green Campus Campaign', 'Environmental awareness campaign', '2024-07-15', '13:00:00', 'Field Area', 'Completed', 'approval_E008.pdf', 'qr_E008.png', 'EA002'),
('E009', 'Photography Contest', 'Photo contest for students', '2024-07-20', '12:00:00', 'Gallery', 'Upcoming', 'approval_E009.pdf', 'qr_E009.png', 'EA003'),
('E010', 'Debate Championship', 'Inter-university debate tournament', '2024-07-25', '09:00:00', 'Auditorium', 'Completed', 'approval_E010.pdf', 'qr_E010.png', 'EA002');

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `MembershipID` varchar(10) NOT NULL,
  `Status` enum('Pending','Approved','Rejected') NOT NULL,
  `ApprovalDate` date DEFAULT NULL,
  `StudentCard` varchar(255) NOT NULL,
  `ApprovedBy` varchar(10) NOT NULL,
  `UserID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership`
--

INSERT INTO `membership` (`MembershipID`, `Status`, `ApprovalDate`, `StudentCard`, `ApprovedBy`, `UserID`) VALUES
('MS001', 'Pending', NULL, 'card_CB23001.jpg', 'PA001', 'CB23001'),
('MS002', 'Approved', '2024-05-01', 'card_CB23002.jpg', 'PA001', 'CB23002'),
('MS003', 'Approved', '2024-05-02', 'card_CB23003.jpg', 'PA002', 'CB23003'),
('MS004', 'Rejected', '2024-05-03', 'card_CB23004.jpg', 'PA002', 'CB23004'),
('MS005', 'Pending', NULL, 'card_CB23005.jpg', 'PA001', 'CB23005'),
('MS006', 'Approved', '2024-05-05', 'card_CB23006.jpg', 'PA003', 'CB23006'),
('MS007', 'Approved', '2024-05-06', 'card_CB23007.jpg', 'PA003', 'CB23007'),
('MS008', 'Rejected', '2024-05-07', 'card_CB23008.jpg', 'PA002', 'CB23008'),
('MS009', 'Pending', NULL, 'card_CB23009.jpg', 'PA001', 'CB23009'),
('MS010', 'Approved', '2024-05-08', 'card_CB23010.jpg', 'PA003', 'CB23010');

-- --------------------------------------------------------

--
-- Table structure for table `merit_application`
--

CREATE TABLE `merit_application` (
  `MeritID` varchar(10) NOT NULL,
  `Status` enum('Pending','Approved','Rejected') NOT NULL,
  `SubmittedDate` date NOT NULL,
  `SubmittedBy` varchar(10) NOT NULL,
  `ApprovedBy` varchar(10) NOT NULL,
  `EventID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `merit_application`
--

INSERT INTO `merit_application` (`MeritID`, `Status`, `SubmittedDate`, `SubmittedBy`, `ApprovedBy`, `EventID`) VALUES
('M001', 'Approved', '2024-06-11', 'EA001', 'PA001', 'E001'),
('M002', 'Pending', '2024-06-16', 'EA001', 'PA002', 'E002'),
('M003', 'Rejected', '2024-06-21', 'EA002', 'PA002', 'E003'),
('M004', 'Approved', '2024-06-26', 'EA002', 'PA003', 'E004'),
('M005', 'Pending', '2024-07-02', 'EA003', 'PA003', 'E005'),
('M006', 'Approved', '2024-07-06', 'EA003', 'PA001', 'E006'),
('M007', 'Pending', '2024-07-11', 'EA001', 'PA002', 'E007'),
('M008', 'Approved', '2024-07-16', 'EA002', 'PA003', 'E008'),
('M009', 'Rejected', '2024-07-21', 'EA003', 'PA001', 'E009'),
('M010', 'Approved', '2024-07-26', 'EA002', 'PA002', 'E010');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `RoleID` varchar(10) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`RoleID`, `Description`) VALUES
('EA', 'Event Advisor'),
('PA', 'Petakom Administrator'),
('ST', 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` varchar(10) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Role` varchar(10) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Name`, `Role`, `Password`, `Email`, `PhoneNumber`) VALUES
('CB23001', 'Aiman Syafiq', 'ST', '$2y$10$stu01', 'aiman@student.ump.edu.my', '013500001'),
('CB23002', 'Nabila Huda', 'ST', '$2y$10$stu02', 'nabila@student.ump.edu.my', '013500002'),
('CB23003', 'Hakim Rosli', 'ST', '$2y$10$stu03', 'hakim@student.ump.edu.my', '013500003'),
('CB23004', 'Fatin Auni', 'ST', '$2y$10$stu04', 'fatin@student.ump.edu.my', '013500004'),
('CB23005', 'Zul Ariff', 'ST', '$2y$10$stu05', 'zul@student.ump.edu.my', '013500005'),
('CB23006', 'Syahirah Rahim', 'ST', '$2y$10$stu06', 'syahirah@student.ump.edu.my', '013500006'),
('CB23007', 'Danial Haikal', 'ST', '$2y$10$stu07', 'danial@student.ump.edu.my', '013500007'),
('CB23008', 'Balqis Zahrah', 'ST', '$2y$10$stu08', 'balqis@student.ump.edu.my', '013500008'),
('CB23009', 'Izzat Iman', 'ST', '$2y$10$stu09', 'izzat@student.ump.edu.my', '013500009'),
('CB23010', 'Maisarah Nadia', 'ST', '$2y$10$stu10', 'maisarah@student.ump.edu.my', '013500010'),
('CB23011', 'Haziq Danish', 'ST', '$2y$10$stu11', 'haziq@student.ump.edu.my', '013500011'),
('CB23012', 'Raihan Zulkifli', 'ST', '$2y$10$stu12', 'raihan@student.ump.edu.my', '013500012'),
('CB23013', 'Puteri Alya', 'ST', '$2y$10$stu13', 'puteri@student.ump.edu.my', '013500013'),
('CB23014', 'Shahrul Amir', 'ST', '$2y$10$stu14', 'shahrul@student.ump.edu.my', '013500014'),
('CB23015', 'Nur Amira', 'ST', '$2y$10$stu15', 'amira@student.ump.edu.my', '013500015'),
('CB23016', 'Faizal Ridzuan', 'ST', '$2y$10$stu16', 'faizal@student.ump.edu.my', '013500016'),
('CB23017', 'Hana Irdina', 'ST', '$2y$10$stu17', 'hana@student.ump.edu.my', '013500017'),
('CB23018', 'Rashid Zaki', 'ST', '$2y$10$stu18', 'rashid@student.ump.edu.my', '013500018'),
('CB23019', 'Yasmin Salwa', 'ST', '$2y$10$stu19', 'yasmin@student.ump.edu.my', '013500019'),
('CB23020', 'Hakimi Nasrul', 'ST', '$2y$10$stu20', 'hakimi@student.ump.edu.my', '013500020'),
('EA001', 'Encik Ahmad Farid', 'EA', '$2y$10$i6tHgj55HSsPDBHKGwUwKOI3OhHGGBK.YNr.8HfetamSQoWXGlo2i', 'farid.ea@ump.edu.my', '0185550011'),
('EA002', 'Puan Zaleha Osman', 'EA', '$2y$10$ea2', 'zaleha.ea@ump.edu.my', '0185550022'),
('EA003', 'Dr. Liyana Yusuf', 'EA', '$2y$10$ea3', 'liyana.ea@ump.edu.my', '0185550033'),
('PA001', 'Nur Aisyah Azman', 'PA', '$2y$10$admin1', 'aisyah.pa@ump.edu.my', '0195550011'),
('PA002', 'Mohd Faiz Reza', 'PA', '$2y$10$admin2', 'faiz.pa@ump.edu.my', '0195550022'),
('PA003', 'Siti Nurul Iman', 'PA', '$2y$10$admin3', 'nurul.pa@ump.edu.my', '0195550033');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`AttendanceID`,`UserID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `EventID` (`EventID`);

--
-- Indexes for table `attendance_slot`
--
ALTER TABLE `attendance_slot`
  ADD PRIMARY KEY (`AttendanceID`),
  ADD KEY `EventID` (`EventID`);

--
-- Indexes for table `committee`
--
ALTER TABLE `committee`
  ADD PRIMARY KEY (`CommitteeID`),
  ADD KEY `EventID` (`EventID`),
  ADD KEY `C_RoleID` (`C_RoleID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `committee_role`
--
ALTER TABLE `committee_role`
  ADD PRIMARY KEY (`C_RoleID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`EventID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`MembershipID`),
  ADD KEY `ApprovedBy` (`ApprovedBy`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `merit_application`
--
ALTER TABLE `merit_application`
  ADD PRIMARY KEY (`MeritID`),
  ADD KEY `SubmittedBy` (`SubmittedBy`),
  ADD KEY `ApprovedBy` (`ApprovedBy`),
  ADD KEY `EventID` (`EventID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD KEY `Role` (`Role`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`),
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`AttendanceID`) REFERENCES `attendance_slot` (`AttendanceID`);

--
-- Constraints for table `attendance_slot`
--
ALTER TABLE `attendance_slot`
  ADD CONSTRAINT `attendance_slot_ibfk_1` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`);

--
-- Constraints for table `committee`
--
ALTER TABLE `committee`
  ADD CONSTRAINT `committee_ibfk_1` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`),
  ADD CONSTRAINT `committee_ibfk_2` FOREIGN KEY (`C_RoleID`) REFERENCES `committee_role` (`C_RoleID`),
  ADD CONSTRAINT `committee_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `membership`
--
ALTER TABLE `membership`
  ADD CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`ApprovedBy`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `merit_application`
--
ALTER TABLE `merit_application`
  ADD CONSTRAINT `merit_application_ibfk_1` FOREIGN KEY (`SubmittedBy`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `merit_application_ibfk_2` FOREIGN KEY (`ApprovedBy`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `merit_application_ibfk_3` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`Role`) REFERENCES `role` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
