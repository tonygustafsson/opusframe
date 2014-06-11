-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 11 jun 2014 kl 15:16
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=102 ;

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

CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(30) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `real_name` varchar(150) NOT NULL,
  `activated` int(1) NOT NULL,
  `token_reset_password` varchar(50) DEFAULT NULL,
  `token_activation` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
