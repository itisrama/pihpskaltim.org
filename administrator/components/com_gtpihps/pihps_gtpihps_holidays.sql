-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 23, 2015 at 11:41 AM
-- Server version: 5.5.43-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pihps_nasional`
--

-- --------------------------------------------------------

--
-- Table structure for table `pihps_gtpihps_holidays`
--

CREATE TABLE IF NOT EXISTS `pihps_gtpihps_holidays` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `published` tinyint(1) DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=5 ;

--
-- Dumping data for table `pihps_gtpihps_holidays`
--

INSERT INTO `pihps_gtpihps_holidays` (`id`, `name`, `start`, `end`, `published`, `ordering`, `created`, `created_by`, `modified`, `modified_by`) VALUES
(1, 'Test', '2015-07-24', '2015-07-24', 1, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0),
(3, 'Test 2', '2015-07-22', '2015-07-23', 1, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0),
(4, 'Test 3', '2015-07-30', '2015-07-30', 1, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
