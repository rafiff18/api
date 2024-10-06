-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2024 at 06:11 AM
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
-- Database: `event_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'webinar'),
(2, 'lomba'),
(3, 'talkshow');

-- --------------------------------------------------------

--
-- Table structure for table `comment_event`
--

CREATE TABLE `comment_event` (
  `comment_id` int(11) NOT NULL,
  `users_id` int(50) NOT NULL,
  `event_id` int(11) NOT NULL,
  `content_comment` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_event`
--

INSERT INTO `comment_event` (`comment_id`, `users_id`, `event_id`, `content_comment`) VALUES
(1, 3, 1, 'wow'),
(2, 1, 1, 'ya'),
(3, 1, 2, 'menarik');

-- --------------------------------------------------------

--
-- Table structure for table `event_main`
--

CREATE TABLE `event_main` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date_add` datetime NOT NULL,
  `category_id` varchar(10) NOT NULL,
  `desc_event` text NOT NULL,
  `poster` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `quota` int(255) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_main`
--

INSERT INTO `event_main` (`event_id`, `title`, `date_add`, `category_id`, `desc_event`, `poster`, `location`, `quota`, `date_start`, `date_end`) VALUES
(1, 'ai', '2024-10-03 11:22:06', '2', 'cek', 'cek.jpg', 'polines', 125, '2024-10-05 04:22:06', '2024-10-16 06:22:06'),
(3, 'BOT', '2024-10-03 11:22:06', '1', 'cek', 'cek.jpg', 'semarang', 15, '2024-10-05 04:22:06', '2024-10-16 06:22:06'),
(5, 'Webinar piton', '2024-10-06 12:00:00', '3', 'Webinar tentang penggunaan python update.', 'poster_webinar_piton.png', 'Zoom', 100, '2024-10-15 09:00:00', '2024-10-15 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `like_event`
--

CREATE TABLE `like_event` (
  `like_id` int(50) NOT NULL,
  `event_id` int(11) NOT NULL,
  `users_id` int(50) NOT NULL,
  `status_like` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regist_event`
--

CREATE TABLE `regist_event` (
  `regist_id` int(11) NOT NULL,
  `users_id` int(50) NOT NULL,
  `event_id` int(11) NOT NULL,
  `regist_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `regist_event`
--

INSERT INTO `regist_event` (`regist_id`, `users_id`, `event_id`, `regist_date`) VALUES
(1, 3, 2, '2024-10-06 04:05:54');

-- --------------------------------------------------------

--
-- Table structure for table `replay_comment`
--

CREATE TABLE `replay_comment` (
  `replay_id` int(11) NOT NULL,
  `users_id` int(50) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `content_replay` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `replay_comment`
--

INSERT INTO `replay_comment` (`replay_id`, `users_id`, `comment_id`, `content_replay`) VALUES
(2, 3, 1, 'sadad');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_event`
--

CREATE TABLE `ticket_event` (
  `ticket_id` int(11) NOT NULL,
  `users_id` int(50) NOT NULL,
  `barcode_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_event`
--

INSERT INTO `ticket_event` (`ticket_id`, `users_id`, `barcode_value`) VALUES
(1, 3, '1234567890');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `users_id` int(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('porpose','admin','superadmin','member') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`users_id`, `username`, `email`, `password`, `role`) VALUES
(2, 'rafif update', 'b@a', '', 'porpose'),
(3, 'rafif', '@a', '12', 'member'),
(4, 'ceg', 'daaa2@', '12', 'porpose'),
(5, 'cek', 'aaa@a', '$2y$10$yVRoCKkEVeGm5YWO50H.O.h2lrqKrKhZMh.PuMWn3vT99.h0FniO.', 'member');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `comment_event`
--
ALTER TABLE `comment_event`
  ADD PRIMARY KEY (`comment_id`),
  ADD UNIQUE KEY `users_id` (`users_id`,`event_id`);

--
-- Indexes for table `event_main`
--
ALTER TABLE `event_main`
  ADD PRIMARY KEY (`event_id`),
  ADD UNIQUE KEY `category_id` (`category_id`);

--
-- Indexes for table `like_event`
--
ALTER TABLE `like_event`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `event_id` (`event_id`),
  ADD UNIQUE KEY `users_id` (`users_id`);

--
-- Indexes for table `regist_event`
--
ALTER TABLE `regist_event`
  ADD PRIMARY KEY (`regist_id`),
  ADD UNIQUE KEY `event_id` (`event_id`),
  ADD UNIQUE KEY `users_id` (`users_id`);

--
-- Indexes for table `replay_comment`
--
ALTER TABLE `replay_comment`
  ADD PRIMARY KEY (`replay_id`),
  ADD UNIQUE KEY `users_id` (`users_id`),
  ADD UNIQUE KEY `comment_id` (`comment_id`);

--
-- Indexes for table `ticket_event`
--
ALTER TABLE `ticket_event`
  ADD PRIMARY KEY (`ticket_id`),
  ADD UNIQUE KEY `users_id` (`users_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comment_event`
--
ALTER TABLE `comment_event`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_main`
--
ALTER TABLE `event_main`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `regist_event`
--
ALTER TABLE `regist_event`
  MODIFY `regist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `replay_comment`
--
ALTER TABLE `replay_comment`
  MODIFY `replay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ticket_event`
--
ALTER TABLE `ticket_event`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
