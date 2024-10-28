-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1



SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `regist_id` int(11) NOT NULL,
  `is_present` tinyint(1) DEFAULT 0,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `regist_id`, `is_present`, `scanned_at`) VALUES
(1, 1, 0, '2024-10-16 04:19:57'),
(2, 2, 0, '2024-10-16 04:41:57'),
(3, 2, 1, '2024-10-16 04:42:18');

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
(1, 'lomba'),
(2, 'lomba'),
(4, 'seminar apdet');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `parent_id`, `users_id`, `content`, `created_at`, `event_id`) VALUES
(2, NULL, 5, 'This is a new root comment', '2024-10-18 09:54:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comment_event`
--

CREATE TABLE `comment_event` (
  `comment_id` int(11) NOT NULL,
  `users_id` int(50) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `content_comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_event`
--

INSERT INTO `comment_event` (`comment_id`, `users_id`, `event_id`, `content_comment`) VALUES
(3, 9, 2, 'yyyyy'),
(5, 3, 2, 'comment 1');

-- --------------------------------------------------------

--
-- Table structure for table `event_main`
--

CREATE TABLE `event_main` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date_add` datetime NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `desc_event` text DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `quota` int(255) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_main`
--

INSERT INTO `event_main` (`event_id`, `title`, `date_add`, `category_id`, `desc_event`, `poster`, `location`, `quota`, `date_start`, `date_end`) VALUES
(1, 'pomn', '2024-10-14 04:15:54', 2, 'p2md dasdad2024', 'iyaaah.jpg', 'polines', 100, '2024-10-14 09:15:54', '2024-10-14 09:15:54'),
(2, 'seminar ai', '2024-10-14 09:18:25', 2, 'msmsm', 'iyaaah.jpg', 'polines', 100, '2024-10-14 09:15:54', '2024-10-14 09:15:54');

-- --------------------------------------------------------

--
-- Table structure for table `like_event`
--

CREATE TABLE `like_event` (
  `like_id` int(50) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `users_id` int(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regist_event`
--

CREATE TABLE `regist_event` (
  `regist_id` int(11) NOT NULL,
  `users_id` int(50) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `is_present` tinyint(1) NOT NULL,
  `registration_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `regist_event`
--

INSERT INTO `regist_event` (`regist_id`, `users_id`, `event_id`, `qr_code`, `is_present`, `registration_time`) VALUES
(6, 3, 2, 'wwwwwww', 1, '2024-10-22 13:24:13');

-- --------------------------------------------------------

--
-- Table structure for table `replay_comment`
--

CREATE TABLE `replay_comment` (
  `replay_id` int(11) NOT NULL,
  `users_id` int(50) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `content_replay` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `replay_comment`
--

INSERT INTO `replay_comment` (`replay_id`, `users_id`, `comment_id`, `content_replay`) VALUES
(7, 9, 3, 'eeee'),
(8, 9, 3, 'wwww'),
(9, 9, 5, 'replay 1');

-- --------------------------------------------------------

--
-- Table structure for table `tag_user`
--

CREATE TABLE `tag_user` (
  `tag_id` int(11) NOT NULL,
  `replay_id` int(11) DEFAULT NULL,
  `content_taguser` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tag_user`
--

INSERT INTO `tag_user` (`tag_id`, `replay_id`, `content_taguser`) VALUES
(1, 7, 'wwwwwww'),
(2, 7, 'qqqqqw'),
(3, 8, 'tag user');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_event`
--

CREATE TABLE `ticket_event` (
  `ticket_id` int(11) NOT NULL,
  `users_id` int(50) DEFAULT NULL,
  `barcode_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 'tarisa', 'tra@tra.com', '12322', 'member'),
(4, 'rapip', 'rafif@gmail.com', '12345', 'admin'),
(5, 'wrzeno', 'seno@gmail.com', '12345', 'superadmin'),
(6, 'johndoe', 'john@example.com', 'password123', 'member'),
(7, 'johndoe', 'john@example.com', '123123', 'admin'),
(8, 'johndoe', 'john@example.com', '123123', 'admin'),
(9, 'pasha', 'pasha@gmail.com', '$2y$10$moqTnmxy.KzhZaxgAShyme2OgidKlBsQB5saieKIZQO3bR2rSWwfS', 'member');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `regist_id` (`regist_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `fk_parent_comment` (`parent_id`),
  ADD KEY `fk_users_id` (`users_id`),
  ADD KEY `fk_event_id` (`event_id`);

--
-- Indexes for table `comment_event`
--
ALTER TABLE `comment_event`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_main`
--
ALTER TABLE `event_main`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `like_event`
--
ALTER TABLE `like_event`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indexes for table `regist_event`
--
ALTER TABLE `regist_event`
  ADD PRIMARY KEY (`regist_id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `replay_comment`
--
ALTER TABLE `replay_comment`
  ADD PRIMARY KEY (`replay_id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indexes for table `tag_user`
--
ALTER TABLE `tag_user`
  ADD PRIMARY KEY (`tag_id`),
  ADD KEY `fk_replay_id` (`replay_id`);

--
-- Indexes for table `ticket_event`
--
ALTER TABLE `ticket_event`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comment_event`
--
ALTER TABLE `comment_event`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `event_main`
--
ALTER TABLE `event_main`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `like_event`
--
ALTER TABLE `like_event`
  MODIFY `like_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `regist_event`
--
ALTER TABLE `regist_event`
  MODIFY `regist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `replay_comment`
--
ALTER TABLE `replay_comment`
  MODIFY `replay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tag_user`
--
ALTER TABLE `tag_user`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ticket_event`
--
ALTER TABLE `ticket_event`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_event_id` FOREIGN KEY (`event_id`) REFERENCES `event_main` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comment_event`
--
ALTER TABLE `comment_event`
  ADD CONSTRAINT `comment_event_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`),
  ADD CONSTRAINT `comment_event_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event_main` (`event_id`);

--
-- Constraints for table `event_main`
--
ALTER TABLE `event_main`
  ADD CONSTRAINT `event_main_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `like_event`
--
ALTER TABLE `like_event`
  ADD CONSTRAINT `like_event_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event_main` (`event_id`),
  ADD CONSTRAINT `like_event_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `regist_event`
--
ALTER TABLE `regist_event`
  ADD CONSTRAINT `regist_event_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`),
  ADD CONSTRAINT `regist_event_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event_main` (`event_id`);

--
-- Constraints for table `replay_comment`
--
ALTER TABLE `replay_comment`
  ADD CONSTRAINT `replay_comment_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`),
  ADD CONSTRAINT `replay_comment_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comment_event` (`comment_id`);

--
-- Constraints for table `ticket_event`
--
ALTER TABLE `ticket_event`
  ADD CONSTRAINT `ticket_event_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
