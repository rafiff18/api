-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Okt 2024 pada 04.01
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

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
-- Struktur dari tabel `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `comment_event`
--

CREATE TABLE `comment_event` (
  `comment_id` int(11) NOT NULL,
  `users_id` int(50) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `content_comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `event_main`
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `like_event`
--

CREATE TABLE `like_event` (
  `like_id` int(50) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `users_id` int(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `regist_event`
--

CREATE TABLE `regist_event` (
  `regist_id` int(11) NOT NULL,
  `users_id` int(50) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `is_present` tinyint(1) NOT NULL,
  `registration_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `replay_comment`
--

CREATE TABLE `replay_comment` (
  `replay_id` int(11) NOT NULL,
  `users_id` int(50) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `content_replay` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_event`
--

CREATE TABLE `ticket_event` (
  `ticket_id` int(11) NOT NULL,
  `users_id` int(50) DEFAULT NULL,
  `barcode_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `users_id` int(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('porpose','admin','superadmin','member') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`users_id`, `username`, `email`, `password`, `role`) VALUES
(1, 'faishal', 'fs@fss', '12322', 'admin'),
(2, 'tarisa', 'tra@tra', '12322', 'member');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indeks untuk tabel `comment_event`
--
ALTER TABLE `comment_event`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indeks untuk tabel `event_main`
--
ALTER TABLE `event_main`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `like_event`
--
ALTER TABLE `like_event`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indeks untuk tabel `regist_event`
--
ALTER TABLE `regist_event`
  ADD PRIMARY KEY (`regist_id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indeks untuk tabel `replay_comment`
--
ALTER TABLE `replay_comment`
  ADD PRIMARY KEY (`replay_id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indeks untuk tabel `ticket_event`
--
ALTER TABLE `ticket_event`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `comment_event`
--
ALTER TABLE `comment_event`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `event_main`
--
ALTER TABLE `event_main`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `like_event`
--
ALTER TABLE `like_event`
  MODIFY `like_id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `regist_event`
--
ALTER TABLE `regist_event`
  MODIFY `regist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `replay_comment`
--
ALTER TABLE `replay_comment`
  MODIFY `replay_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticket_event`
--
ALTER TABLE `ticket_event`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `comment_event`
--
ALTER TABLE `comment_event`
  ADD CONSTRAINT `comment_event_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`),
  ADD CONSTRAINT `comment_event_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event_main` (`event_id`);

--
-- Ketidakleluasaan untuk tabel `event_main`
--
ALTER TABLE `event_main`
  ADD CONSTRAINT `event_main_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Ketidakleluasaan untuk tabel `like_event`
--
ALTER TABLE `like_event`
  ADD CONSTRAINT `like_event_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event_main` (`event_id`),
  ADD CONSTRAINT `like_event_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`);

--
-- Ketidakleluasaan untuk tabel `regist_event`
--
ALTER TABLE `regist_event`
  ADD CONSTRAINT `regist_event_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`),
  ADD CONSTRAINT `regist_event_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event_main` (`event_id`);

--
-- Ketidakleluasaan untuk tabel `replay_comment`
--
ALTER TABLE `replay_comment`
  ADD CONSTRAINT `replay_comment_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`),
  ADD CONSTRAINT `replay_comment_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comment_event` (`comment_id`);

--
-- Ketidakleluasaan untuk tabel `ticket_event`
--
ALTER TABLE `ticket_event`
  ADD CONSTRAINT `ticket_event_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
