SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loginkey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `addedby` int(11) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `capcode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `accounts` (`id`, `username`, `password`, `loginkey`, `addedby`, `level`, `capcode`) VALUES
(1, 'admin', '$2y$12$HZkijNLXqOWGa1Gyyp8ojuTUYfsFkvt6lZqpUEdfTQiuuYUEK1Huq', 'v18utqekrzcl5spqvu4thzyzlkgnzn1kez5xg98xza8utnbe85xfum46xakmnbws1rg7reuhft1', 0, 9999, '<b style=\'color:#f00\'>admin</b>');

CREATE TABLE `bans` (
  `id` int(11) NOT NULL,
  `ip` decimal(39,0) UNSIGNED NOT NULL DEFAULT '0' COLLATE utf8mb4_unicode_ci,
  `pubreason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `privreason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `bannedby` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `at` int(11) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mohel` (
  `id` int(11) NOT NULL,
  `mohel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `head` mediumtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `flood` (
  `ip` decimal(39,0) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `flood`
  ADD PRIMARY KEY (`ip`);

ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `loginkey` (`loginkey`);

ALTER TABLE `bans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip` (`ip`);

ALTER TABLE `mohel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mohel` (`mohel`);

ALTER TABLE `settings`
  ADD UNIQUE KEY `name` (`name`);