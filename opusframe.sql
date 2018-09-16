-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 16 sep 2018 kl 18:58
-- Serverversion: 10.1.26-MariaDB
-- PHP-version: 7.1.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `opusframe`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `genre` varchar(50) NOT NULL,
  `rating` int(1) NOT NULL,
  `media_type` varchar(20) NOT NULL,
  `seen` datetime NOT NULL,
  `recommended` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumpning av Data i tabell `movies`
--

INSERT INTO `movies` (`id`, `name`, `genre`, `rating`, `media_type`, `seen`, `recommended`) VALUES
(92, 'X-Men: Days of Future Past', 'Action', 2, 'BluRay', '2014-06-04 00:00:00', 0),
(93, 'The Grand Budapest Hotel', 'Romance', 4, 'DVD', '2014-06-08 00:00:00', 1),
(94, 'Lego Movie', 'Romance', 1, 'BluRay', '2014-06-08 00:00:00', 0),
(95, 'The Internship', 'Comedy', 3, 'DVD', '2014-05-27 00:00:00', 1),
(96, 'Maleficent', 'Adventure', 5, 'DVD', '2014-06-11 00:00:00', 1),
(97, 'Mr Peabody &amp; Sherman', 'Comedy', 3, 'DVD', '2014-06-02 00:00:00', 0),
(98, 'Law Abiding Citizen', 'Action', 5, 'DVD', '2014-01-11 00:00:00', 1),
(99, 'Prisoners', 'Action', 3, 'BluRay', '2014-04-11 00:00:00', 1),
(100, 'Her', 'Romance', 4, 'DVD', '2014-05-08 00:00:00', 1),
(101, 'The Secret Life of Walter Mitty', 'Comedy', 5, 'BluRay', '2014-05-16 00:00:00', 1);

-- --------------------------------------------------------

--
-- Tabellstruktur `users`
--

CREATE TABLE `users` (
  `id` varchar(30) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `real_name` varchar(150) NOT NULL,
  `activated` int(1) NOT NULL,
  `token_reset_password` varchar(50) DEFAULT NULL,
  `token_activation` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumpning av Data i tabell `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `real_name`, `activated`, `token_reset_password`, `token_activation`) VALUES
('5b9e8a62c2dff', 'test', '$2y$10$uJpfuT1Jt7zM5d0huxH6WuSvFLi5GSnwNi0kZH6kWSwvlLN0SGVtG', 'Test User', 1, NULL, '');

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
