# Sequel Pro dump
# Version 1191
# http://code.google.com/p/sequel-pro
#
# Host: localhost (MySQL 5.1.34)
# Database: bhl
# Generation Time: 2009-12-21 13:28:53 +0000
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table rdmp_author
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_author`;

CREATE TABLE `rdmp_author` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_cluster_id` int(11) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `forename` varchar(255) DEFAULT NULL,
  `suffix` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_author_reference_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_author_reference_joiner`;

CREATE TABLE `rdmp_author_reference_joiner` (
  `author_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `author_order` int(11) DEFAULT NULL,
  KEY `author_id` (`author_id`),
  KEY `reference_id` (`reference_id`),
  CONSTRAINT `rdmp_author_reference_joiner_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `rdmp_author` (`author_id`) ON DELETE CASCADE,
  CONSTRAINT `rdmp_author_reference_joiner_ibfk_2` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_locality
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_locality`;

CREATE TABLE `rdmp_locality` (
  `locality_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `loc` point NOT NULL,
  `woeid` int(11) NOT NULL DEFAULT '0',
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  PRIMARY KEY (`locality_id`),
  SPATIAL KEY `loc` (`loc`),
  KEY `woeid` (`woeid`),
  KEY `latitude` (`latitude`,`longitude`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



# Dump of table rdmp_locality_page_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_locality_page_joiner`;

CREATE TABLE `rdmp_locality_page_joiner` (
  `PageID` int(11) NOT NULL,
  `locality_id` int(11) NOT NULL,
  KEY `locality_id` (`locality_id`),
  KEY `PageID` (`PageID`),
  KEY `PageID_2` (`PageID`,`locality_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_name
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_name`;

CREATE TABLE `rdmp_name` (
  `NameBankID` int(11) DEFAULT NULL,
  `NameString` varchar(255) DEFAULT NULL,
  FULLTEXT KEY `NameString` (`NameString`) /*!50100 WITH PARSER `bi_gram` */ 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference`;

CREATE TABLE `rdmp_reference` (
  `reference_id` int(11) NOT NULL AUTO_INCREMENT,
  `genre` enum('article','book','chapter') NOT NULL DEFAULT 'article',
  `title` varchar(255) DEFAULT NULL,
  `secondary_title` varchar(255) DEFAULT NULL,
  `volume` varchar(16) DEFAULT NULL,
  `series` varchar(16) DEFAULT NULL,
  `issue` varchar(16) DEFAULT NULL,
  `spage` varchar(16) DEFAULT NULL,
  `epage` varchar(16) DEFAULT NULL,
  `year` char(4) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `issn` char(9) DEFAULT NULL,
  `isbn` int(11) DEFAULT NULL,
  `isbn13` int(11) DEFAULT NULL,
  `oclc` int(11) DEFAULT NULL,
  `sici` varchar(255) DEFAULT NULL,
  `PageID` int(11) NOT NULL DEFAULT '0',
  `abstract` text,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`reference_id`),
  KEY `secondary_title` (`secondary_title`),
  KEY `volume` (`volume`),
  KEY `series` (`series`),
  KEY `issue` (`issue`),
  KEY `spage` (`spage`),
  KEY `year` (`year`),
  KEY `date` (`date`),
  KEY `issn` (`issn`),
  KEY `PageID` (`PageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference_page_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference_page_joiner`;

CREATE TABLE `rdmp_reference_page_joiner` (
  `reference_id` int(11) NOT NULL,
  `PageID` int(11) NOT NULL,
  `page_order` int(11) NOT NULL,
  KEY `reference_id` (`reference_id`),
  KEY `PageID` (`PageID`),
  CONSTRAINT `rdmp_reference_page_joiner_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference_version
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference_version`;

CREATE TABLE `rdmp_reference_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` int(10) unsigned DEFAULT NULL,
  `json` tinytext,
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`),
  CONSTRAINT `rdmp_reference_version_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



# Dump of table rdmp_secondary_author_reference_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_secondary_author_reference_joiner`;

CREATE TABLE `rdmp_secondary_author_reference_joiner` (
  `author_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `author_order` int(11) DEFAULT NULL,
  KEY `author_id` (`author_id`),
  KEY `reference_id` (`reference_id`),
  CONSTRAINT `rdmp_secondary_author_reference_joiner_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `rdmp_author` (`author_id`) ON DELETE CASCADE,
  CONSTRAINT `rdmp_secondary_author_reference_joiner_ibfk_2` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_text
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_text`;

CREATE TABLE `rdmp_text` (
  `PageID` int(11) NOT NULL,
  `ocr_text` text NOT NULL,
  PRIMARY KEY (`PageID`),
  FULLTEXT KEY `ocr_text` (`ocr_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_text_index
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_text_index`;

CREATE TABLE `rdmp_text_index` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_type` enum('citation','author','title') NOT NULL DEFAULT 'title',
  `object_id` int(11) DEFAULT NULL,
  `object_text` text NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `object_text` (`object_text`) /*!50100 WITH PARSER `bi_gram` */ 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;






/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
