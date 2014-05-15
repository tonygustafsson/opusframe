-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 15 maj 2014 kl 14:40
-- Serverversion: 5.6.16
-- PHP-version: 5.5.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databas: `opusframe`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `movies`
--

CREATE TABLE IF NOT EXISTS `movies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `genre` varchar(50) NOT NULL,
  `rating` int(1) NOT NULL,
  `media_type` varchar(20) NOT NULL,
  `seen` datetime NOT NULL,
  `recommended` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=67 ;

--
-- Dumpning av Data i tabell `movies`
--

INSERT INTO `movies` (`id`, `name`, `genre`, `rating`, `media_type`, `seen`, `recommended`) VALUES
(18, 'The Hobbit', 'Adventure', 5, 'DVD', '2000-01-01 00:00:00', 0),
(21, 'Van Gogh', 'History', 5, '', '0000-00-00 00:00:00', 0),
(22, '300: The movie', 'Action', 4, 'DVD', '2000-01-01 00:00:00', 1),
(24, 'Herkules', 'History', 1, '', '0000-00-00 00:00:00', 0),
(27, 'The Obilisk', 'Adventure', 4, 'DVD', '2000-01-01 00:00:00', 1),
(32, 'The loveStory', 'Romantik', 5, '', '0000-00-00 00:00:00', 0),
(34, 'The Testing', 'Action', 4, '', '1970-01-01 00:00:00', 0),
(36, 'TonyMovie', 'Det', 5, '', '0000-00-00 00:00:00', 0),
(37, 'Kalle Anka', 'Kakke', 4, '', '2014-04-11 00:00:00', 0),
(40, 'The mourning', 'SciFi', 5, '', '2014-04-02 00:00:00', 0),
(50, 'Kakmovie', 'SciFi', 2, 'DVD', '2014-04-26 00:00:00', 1),
(51, 'KalleAnka &amp;amp; CO', 'Romance', 2, 'DVD', '2014-05-02 00:00:00', 0),
(65, 'The turna', 'Romance', 3, 'DVD', '2014-05-06 00:00:00', 0),
(66, 'The Grinding', 'Romance', 2, 'DVD', '2014-05-06 00:00:00', 0);

-- --------------------------------------------------------

--
-- Tabellstruktur `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(30) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `real_name` varchar(150) NOT NULL,
  `activated` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumpning av Data i tabell `users`
--

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
