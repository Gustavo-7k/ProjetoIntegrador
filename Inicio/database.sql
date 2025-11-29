-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: anthems-db:3306
-- Generation Time: Nov 29, 2025 at 08:21 PM
-- Server version: 8.0.43
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anthems_db`
--
CREATE DATABASE IF NOT EXISTS `anthems_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `anthems_db`;

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

DROP TABLE IF EXISTS `albums`;
CREATE TABLE `albums` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `artist` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_year` year DEFAULT NULL,
  `genre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spotify_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apple_music_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_comments` int DEFAULT '0',
  `average_rating` decimal(2,1) DEFAULT '0.0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `title`, `artist`, `cover_image`, `release_year`, `genre`, `spotify_url`, `apple_music_url`, `total_comments`, `average_rating`, `created_at`, `updated_at`) VALUES
(1, 'In Rainbows', 'Radiohead', 'InRainbows.jpeg', '2007', 'Alternative Rock', NULL, NULL, 2, 0.0, '2025-11-29 18:51:47', '2025-11-29 19:29:26'),
(2, 'Kid A', 'Radiohead', 'kida.jpeg', '2000', 'Electronic Rock', NULL, NULL, 0, 0.0, '2025-11-29 18:51:47', '2025-11-29 19:29:41'),
(3, 'OK Computer', 'Radiohead', 'okcomputer.jpeg', '1997', 'Alternative Rock', NULL, NULL, 0, 0.0, '2025-11-29 18:51:47', '2025-11-29 19:29:55'),
(4, 'Bury Me At Makeout Creek', 'Mitski', 'burymeatmakeoutcreek.jpg', '2014', 'Indie Rock', NULL, NULL, 0, 0.0, '2025-11-29 18:51:47', '2025-11-29 19:38:20'),
(5, 'Blonde', 'Frank Ocean', 'blond.jpg', '2016', 'R&B', NULL, NULL, 0, 0.0, '2025-11-29 18:51:47', '2025-11-29 19:39:23'),
(6, 'This Old Dog', 'Mac DeMarco', 'thisolddog.jpg', '2017', 'Indie Pop', NULL, NULL, 0, 0.0, '2025-11-29 18:51:47', '2025-11-29 19:39:55'),
(7, 'Lush', 'Mitski', 'Lush.jpeg', '2018', 'Indie Rock', NULL, NULL, 1, 4.0, '2025-11-29 18:51:47', '2025-11-29 19:47:30'),
(8, 'Ultraviolence', 'Lana Del Rey', 'Ultraviolence.jpeg', '2014', 'Dream Pop', NULL, NULL, 0, 0.0, '2025-11-29 18:51:47', '2025-11-29 19:31:02'),
(10, 'histórias de kebrada para pessoas mal criadas', 'Link do Zap', 'HQCM', '2023', 'Hip Hop', NULL, NULL, 1, 4.0, '2025-11-29 18:51:47', '2025-11-29 19:35:00'),
(11, '(What\'s the Story) Morning Glory?', 'Oasis', 'album_692b4ebd9de6e_1764445885.jpg', '1990', 'Britpop', NULL, NULL, 1, 4.0, '2025-11-29 16:51:25', '2025-11-29 17:05:51'),
(12, 'Puberty 2', 'Mitski', 'album_692b4f544ff00_1764446036.jpg', '2016', 'Indie Rock', NULL, NULL, 0, 0.0, '2025-11-29 16:53:56', '2025-11-29 16:53:56'),
(13, 'Sgt. Pepper\'s Lonely Hearts Club Band', 'Beatles', 'album_692b501d5508f_1764446237.jpg', '1967', 'Rock Psicodélico', NULL, NULL, 0, 0.0, '2025-11-29 16:57:17', '2025-11-29 16:57:17');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `album_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `rating` tinyint DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'approved',
  `likes_count` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `album_id`, `parent_id`, `rating`, `comment`, `status`, `likes_count`, `created_at`, `updated_at`) VALUES
(8, 2, 7, NULL, 4, 'Snail Mail representa muito bem o indie rock atual. Guitarras cristalinas e letras honestas.', 'approved', 0, '2025-11-29 18:51:47', '2025-11-29 18:51:47'),
(10, 3, 10, NULL, 4, 'Link do Zap traz uma perspectiva interessante para o hip hop nacional. Letras inteligentes.', 'approved', 0, '2025-11-29 18:51:47', '2025-11-29 18:51:47'),
(13, 2, 11, NULL, 4, 'Um album bom mas muito longo. Ele tem bonehead\'s bank holiday então compensa ouvir', 'approved', 0, '2025-11-29 17:05:51', '2025-11-29 17:05:55');

--
-- Triggers `comments`
--
DROP TRIGGER IF EXISTS `update_album_stats_delete`;
DELIMITER $$
CREATE TRIGGER `update_album_stats_delete` AFTER DELETE ON `comments` FOR EACH ROW BEGIN
    UPDATE `albums` SET 
        `total_comments` = GREATEST(`total_comments` - 1, 0),
        `average_rating` = COALESCE((
            SELECT ROUND(AVG(rating), 1) 
            FROM `comments` 
            WHERE album_id = OLD.album_id AND status = 'approved'
        ), 0)
    WHERE id = OLD.album_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_album_stats_insert`;
DELIMITER $$
CREATE TRIGGER `update_album_stats_insert` AFTER INSERT ON `comments` FOR EACH ROW BEGIN
    UPDATE `albums` SET 
        `total_comments` = `total_comments` + 1,
        `average_rating` = (
            SELECT ROUND(AVG(rating), 1) 
            FROM `comments` 
            WHERE album_id = NEW.album_id AND status = 'approved'
        )
    WHERE id = NEW.album_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `comments_with_user`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `comments_with_user`;
CREATE TABLE `comments_with_user` (
`id` int
,`user_id` int
,`album_id` int
,`parent_id` int
,`rating` tinyint
,`comment` text
,`status` enum('pending','approved','rejected')
,`likes_count` int
,`created_at` datetime
,`updated_at` datetime
,`username` varchar(30)
,`full_name` varchar(100)
,`profile_image` varchar(255)
,`album_title` varchar(255)
,`album_artist` varchar(255)
,`album_cover` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

DROP TABLE IF EXISTS `comment_likes`;
CREATE TABLE `comment_likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `comment_likes`
--
DROP TRIGGER IF EXISTS `update_comment_likes_delete`;
DELIMITER $$
CREATE TRIGGER `update_comment_likes_delete` AFTER DELETE ON `comment_likes` FOR EACH ROW BEGIN
    UPDATE `comments` SET 
        `likes_count` = GREATEST(`likes_count` - 1, 0)
    WHERE id = OLD.comment_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_comment_likes_insert`;
DELIMITER $$
CREATE TRIGGER `update_comment_likes_insert` AFTER INSERT ON `comment_likes` FOR EACH ROW BEGIN
    UPDATE `comments` SET 
        `likes_count` = `likes_count` + 1
    WHERE id = NEW.comment_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

DROP TABLE IF EXISTS `connections`;
CREATE TABLE `connections` (
  `id` int NOT NULL,
  `follower_id` int NOT NULL,
  `following_id` int NOT NULL,
  `status` enum('pending','accepted','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'accepted',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`id`, `follower_id`, `following_id`, `status`, `created_at`) VALUES
(1, 2, 3, 'accepted', '2025-11-29 18:51:47'),
(2, 2, 4, 'accepted', '2025-11-29 18:51:47'),
(3, 2, 5, 'accepted', '2025-11-29 18:51:47'),
(4, 3, 2, 'accepted', '2025-11-29 18:51:47'),
(5, 3, 4, 'accepted', '2025-11-29 18:51:47'),
(6, 4, 2, 'accepted', '2025-11-29 18:51:47'),
(7, 4, 3, 'accepted', '2025-11-29 18:51:47'),
(8, 4, 5, 'accepted', '2025-11-29 18:51:47'),
(9, 5, 2, 'accepted', '2025-11-29 18:51:47'),
(10, 5, 3, 'accepted', '2025-11-29 18:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('comment','follow','like','report','system') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_id` int DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `related_id`, `is_read`, `created_at`) VALUES
(1, 2, 'like', 'Novo like', 'Maria Silva curtiu seu comentÃ¡rio sobre In Rainbows', 1, 1, '2025-11-29 18:51:47'),
(2, 2, 'follow', 'Novo seguidor', 'Carlos Souza comeÃ§ou a te seguir', 4, 1, '2025-11-29 18:51:47'),
(3, 3, 'comment', 'Novo comentÃ¡rio', 'Gustavo comentou no Ã¡lbum In Rainbows', 1, 1, '2025-11-29 18:51:47'),
(4, 1, 'system', 'Bem-vindo!', 'Bem-vindo ao Anthems! Explore e compartilhe sua paixão pela música.', NULL, 1, '2025-11-29 18:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` int NOT NULL,
  `reporter_id` int NOT NULL,
  `comment_id` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','reviewed','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `newsletter` tinyint(1) DEFAULT '0',
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `terms_accepted_at` datetime NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `email_token`, `email_verified_at`, `is_admin`, `active`, `newsletter`, `profile_image`, `cover_image`, `bio`, `terms_accepted_at`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrador Sistema', 'admin@anthems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-11-29 18:51:47', 1, 1, 0, NULL, NULL, 'Administrador do sistema Anthems', '2025-11-29 18:51:47', '2025-11-29 16:14:32', '2025-11-29 18:51:47', '2025-11-29 16:35:20'),
(2, 'gustavo_schenkel', 'Gustavo Schenkel', 'gustavo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-11-29 18:51:47', 0, 1, 0, 'avatar_2_1764447149.jpg', 'cover_2_1764447149.jpg', 'Gosto muito de ouvir música', '2025-11-29 18:51:47', '2025-11-29 17:04:25', '2025-11-29 18:51:47', '2025-11-29 17:12:46'),
(3, 'maria_silva', 'Maria Silva', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-11-29 18:51:47', 0, 1, 0, 'avatar_3_1764443327.jpg', 'cover_3_1764443545.jpg', 'Apaixonada por mÃºsica indie e rock alternativo', '2025-11-29 18:51:47', '2025-11-29 16:08:07', '2025-11-29 18:51:47', '2025-11-29 16:12:25'),
(4, 'carlos_souza', 'Carlos Souza', 'carlos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-11-29 18:51:47', 0, 1, 0, NULL, NULL, 'FÃ£ de mÃºsica eletrÃ´nica e hip-hop', '2025-11-29 18:51:47', NULL, '2025-11-29 18:51:47', '2025-11-29 18:51:47'),
(5, 'ana_beatriz', 'Ana Beatriz', 'ana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-11-29 18:51:47', 0, 1, 0, NULL, NULL, 'MPB e jazz sÃ£o minha paixÃ£o', '2025-11-29 18:51:47', NULL, '2025-11-29 18:51:47', '2025-11-29 18:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_favorite_albums`
--

DROP TABLE IF EXISTS `user_favorite_albums`;
CREATE TABLE `user_favorite_albums` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `album_id` int NOT NULL,
  `position` tinyint NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_favorite_albums`
--

INSERT INTO `user_favorite_albums` (`id`, `user_id`, `album_id`, `position`, `created_at`) VALUES
(4, 3, 4, 0, '2025-11-29 16:12:25'),
(5, 1, 10, 0, '2025-11-29 16:35:20'),
(12, 2, 4, 0, '2025-11-29 17:12:46'),
(13, 2, 7, 1, '2025-11-29 17:12:46'),
(14, 2, 12, 2, '2025-11-29 17:12:46'),
(15, 2, 13, 3, '2025-11-29 17:12:46'),
(16, 2, 5, 4, '2025-11-29 17:12:46'),
(17, 2, 6, 5, '2025-11-29 17:12:46');

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_stats`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_stats`;
CREATE TABLE `user_stats` (
`id` int
,`username` varchar(30)
,`full_name` varchar(100)
,`total_comments` bigint
,`following_count` bigint
,`followers_count` bigint
,`average_rating_given` decimal(7,4)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_artist` (`artist`),
  ADD KEY `idx_genre` (`genre`),
  ADD KEY `idx_rating` (`average_rating`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_album` (`user_id`,`album_id`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_comments_album_status` (`album_id`,`status`),
  ADD KEY `idx_comments_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_connection` (`follower_id`,`following_id`),
  ADD KEY `idx_follower` (`follower_id`),
  ADD KEY `idx_following` (`following_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_notifications_user_read_created` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_active` (`active`);

--
-- Indexes for table `user_favorite_albums`
--
ALTER TABLE `user_favorite_albums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_album` (`user_id`,`album_id`),
  ADD KEY `album_id` (`album_id`),
  ADD KEY `idx_user_position` (`user_id`,`position`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_favorite_albums`
--
ALTER TABLE `user_favorite_albums`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

-- --------------------------------------------------------

--
-- Structure for view `comments_with_user`
--
DROP TABLE IF EXISTS `comments_with_user`;

DROP VIEW IF EXISTS `comments_with_user`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `comments_with_user`  AS SELECT `c`.`id` AS `id`, `c`.`user_id` AS `user_id`, `c`.`album_id` AS `album_id`, `c`.`parent_id` AS `parent_id`, `c`.`rating` AS `rating`, `c`.`comment` AS `comment`, `c`.`status` AS `status`, `c`.`likes_count` AS `likes_count`, `c`.`created_at` AS `created_at`, `c`.`updated_at` AS `updated_at`, `u`.`username` AS `username`, `u`.`full_name` AS `full_name`, `u`.`profile_image` AS `profile_image`, `a`.`title` AS `album_title`, `a`.`artist` AS `album_artist`, `a`.`cover_image` AS `album_cover` FROM ((`comments` `c` join `users` `u` on((`c`.`user_id` = `u`.`id`))) join `albums` `a` on((`c`.`album_id` = `a`.`id`))) WHERE (`c`.`status` = 'approved') ;

-- --------------------------------------------------------

--
-- Structure for view `user_stats`
--
DROP TABLE IF EXISTS `user_stats`;

DROP VIEW IF EXISTS `user_stats`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_stats`  AS SELECT `u`.`id` AS `id`, `u`.`username` AS `username`, `u`.`full_name` AS `full_name`, count(distinct `c`.`id`) AS `total_comments`, count(distinct `f1`.`following_id`) AS `following_count`, count(distinct `f2`.`follower_id`) AS `followers_count`, avg(`c`.`rating`) AS `average_rating_given` FROM (((`users` `u` left join `comments` `c` on((`u`.`id` = `c`.`user_id`))) left join `connections` `f1` on((`u`.`id` = `f1`.`follower_id`))) left join `connections` `f2` on((`u`.`id` = `f2`.`following_id`))) WHERE (`u`.`active` = true) GROUP BY `u`.`id`, `u`.`username`, `u`.`full_name` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `connections`
--
ALTER TABLE `connections`
  ADD CONSTRAINT `connections_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `connections_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_favorite_albums`
--
ALTER TABLE `user_favorite_albums`
  ADD CONSTRAINT `user_favorite_albums_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorite_albums_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
