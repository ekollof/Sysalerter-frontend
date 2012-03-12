-- phpMyAdmin SQL Dump
-- version 3.1.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 28, 2009 at 10:57 AM
-- Server version: 5.0.45
-- PHP Version: 5.2.4

--
-- Database: `sysalert`
--

-- --------------------------------------------------------

--
-- Table structure for table `machines`
--
-- Creation: Jan 23, 2009 at 02:38 PM
--

DROP TABLE IF EXISTS `machines`;
CREATE TABLE IF NOT EXISTS `machines` (
  `id` bigint(11) NOT NULL auto_increment,
  `name` varchar(255) binary NOT NULL,
  `key` varchar(255) binary NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--
-- Creation: Jan 23, 2009 at 02:37 PM
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `hostname` varchar(255) binary NOT NULL,
  `id` bigint(11) unsigned NOT NULL auto_increment,
  `status` varchar(255) binary default NULL,
  `value` text,
  `alertstatus` enum('0','1') NOT NULL,
  `lastalert` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `lastalert` (`lastalert`),
  KEY `status` (`status`),
  KEY `hostname` (`hostname`),
  KEY `alertstatus` (`alertstatus`)
) TYPE=InnoDB;