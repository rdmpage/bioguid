# Sequel Pro dump
# Version 663
# http://code.google.com/p/sequel-pro
#
# Host: localhost (MySQL 5.1.26-rc)
# Database: bioguid
# Generation Time: 2009-05-07 16:36:34 +0100
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table arc_g2t
# ------------------------------------------------------------

DROP TABLE IF EXISTS `arc_g2t`;

CREATE TABLE `arc_g2t` (
  `g` mediumint(8) unsigned NOT NULL,
  `t` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `gt` (`g`,`t`),
  KEY `tg` (`t`,`g`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci DELAY_KEY_WRITE=1;



# Dump of table arc_id2val
# ------------------------------------------------------------

DROP TABLE IF EXISTS `arc_id2val`;

CREATE TABLE `arc_id2val` (
  `id` mediumint(8) unsigned NOT NULL,
  `misc` tinyint(1) NOT NULL DEFAULT '0',
  `val` text COLLATE utf8_unicode_ci NOT NULL,
  `val_type` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`,`val_type`),
  KEY `v` (`val`(64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci DELAY_KEY_WRITE=1;



# Dump of table arc_o2val
# ------------------------------------------------------------

DROP TABLE IF EXISTS `arc_o2val`;

CREATE TABLE `arc_o2val` (
  `id` mediumint(8) unsigned NOT NULL,
  `cid` mediumint(8) unsigned NOT NULL,
  `misc` tinyint(1) NOT NULL DEFAULT '0',
  `val` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `cid` (`cid`),
  KEY `v` (`val`(64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci DELAY_KEY_WRITE=1;



# Dump of table arc_s2val
# ------------------------------------------------------------

DROP TABLE IF EXISTS `arc_s2val`;

CREATE TABLE `arc_s2val` (
  `id` mediumint(8) unsigned NOT NULL,
  `cid` mediumint(8) unsigned NOT NULL,
  `misc` tinyint(1) NOT NULL DEFAULT '0',
  `val` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `cid` (`cid`),
  KEY `v` (`val`(64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci DELAY_KEY_WRITE=1;



# Dump of table arc_setting
# ------------------------------------------------------------

DROP TABLE IF EXISTS `arc_setting`;

CREATE TABLE `arc_setting` (
  `k` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `val` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `k` (`k`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci DELAY_KEY_WRITE=1;



# Dump of table arc_triple
# ------------------------------------------------------------

DROP TABLE IF EXISTS `arc_triple`;

CREATE TABLE `arc_triple` (
  `t` mediumint(8) unsigned NOT NULL,
  `s` mediumint(8) unsigned NOT NULL,
  `p` mediumint(8) unsigned NOT NULL,
  `o` mediumint(8) unsigned NOT NULL,
  `o_lang_dt` mediumint(8) unsigned NOT NULL,
  `o_comp` char(35) COLLATE utf8_unicode_ci NOT NULL,
  `s_type` tinyint(1) NOT NULL DEFAULT '0',
  `o_type` tinyint(1) NOT NULL DEFAULT '0',
  `misc` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `t` (`t`),
  KEY `spo` (`s`,`p`,`o`),
  KEY `os` (`o`,`s`),
  KEY `po` (`p`,`o`),
  KEY `misc` (`misc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci DELAY_KEY_WRITE=1;



# Dump of table article_cache
# ------------------------------------------------------------

DROP TABLE IF EXISTS `article_cache`;

CREATE TABLE `article_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `atitle` varchar(255) DEFAULT NULL,
  `issn` char(9) DEFAULT NULL,
  `volume` char(16) DEFAULT NULL,
  `issue` char(16) DEFAULT NULL,
  `spage` varchar(16) DEFAULT NULL,
  `epage` varchar(16) DEFAULT NULL,
  `hard` int(11) DEFAULT '0',
  `doi` varchar(255) DEFAULT NULL,
  `hdl` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `year` char(4) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `abstract` text,
  `sici` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` varchar(255) DEFAULT NULL,
  `pmid` int(11) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `open_access` char(1) DEFAULT 'N',
  `modified` datetime DEFAULT '2100-00-00 00:00:00',
  `publisher_id` varchar(64) DEFAULT NULL,
  `swf` varchar(255) DEFAULT NULL,
  `eissn` char(9) DEFAULT NULL,
  `genre` varchar(32) DEFAULT 'article',
  `isbn` varchar(32) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publoc` varchar(255) DEFAULT NULL,
  `oclc` int(11) DEFAULT NULL,
  `xml_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `issn` (`issn`),
  KEY `volume` (`volume`),
  KEY `spage` (`spage`),
  KEY `epage` (`epage`),
  KEY `idx_issn_volume_spage` (`issn`,`volume`,`spage`)
) ENGINE=InnoDB AUTO_INCREMENT=117828 DEFAULT CHARSET=utf8;



# Dump of table author
# ------------------------------------------------------------

DROP TABLE IF EXISTS `author`;

CREATE TABLE `author` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `lastname` varchar(128) DEFAULT NULL,
  `forename` varchar(128) DEFAULT NULL,
  `suffix` varchar(32) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT '2100-00-00 00:00:00',
  PRIMARY KEY (`author_id`),
  KEY `lastname` (`lastname`),
  KEY `forename` (`forename`)
) ENGINE=InnoDB AUTO_INCREMENT=123109 DEFAULT CHARSET=utf8;



# Dump of table author_reference_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `author_reference_joiner`;

CREATE TABLE `author_reference_joiner` (
  `author_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `author_order` tinyint(4) DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT '2100-00-00 00:00:00',
  KEY `author_id` (`author_id`),
  KEY `reference_id` (`reference_id`),
  CONSTRAINT `author_fk` FOREIGN KEY (`author_id`) REFERENCES `author` (`author_id`) ON DELETE CASCADE,
  CONSTRAINT `reference_fk` FOREIGN KEY (`reference_id`) REFERENCES `article_cache` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table crossref
# ------------------------------------------------------------

DROP TABLE IF EXISTS `crossref`;

CREATE TABLE `crossref` (
  `title` varchar(255) DEFAULT NULL,
  `issn` char(9) DEFAULT NULL,
  `start_date` int(11) DEFAULT NULL,
  `start_volume` int(11) DEFAULT NULL,
  KEY `title` (`title`),
  KEY `start_date` (`start_date`),
  KEY `start_volume` (`start_volume`),
  KEY `issn` (`issn`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table darwin_core
# ------------------------------------------------------------

DROP TABLE IF EXISTS `darwin_core`;

CREATE TABLE `darwin_core` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institutionCode` varchar(128) DEFAULT NULL,
  `collectionCode` varchar(128) DEFAULT NULL,
  `catalogNumber` varchar(128) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `guid` varchar(128) DEFAULT NULL,
  `bci` varchar(128) DEFAULT NULL,
  `organism` varchar(128) DEFAULT NULL,
  `kingdom` varchar(128) DEFAULT NULL,
  `phylum` varchar(128) DEFAULT NULL,
  `class` varchar(128) DEFAULT NULL,
  `order` varchar(128) DEFAULT NULL,
  `family` varchar(128) DEFAULT NULL,
  `genus` varchar(128) DEFAULT NULL,
  `species` varchar(128) DEFAULT NULL,
  `subspecies` varchar(128) DEFAULT NULL,
  `continentOcean` varchar(128) DEFAULT NULL,
  `country` varchar(128) DEFAULT NULL,
  `stateProvince` varchar(128) DEFAULT NULL,
  `locality` varchar(255) DEFAULT NULL,
  `island` varchar(128) DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `collector` varchar(128) DEFAULT NULL,
  `collectorNumber` varchar(128) DEFAULT NULL,
  `fieldNumber` varchar(128) DEFAULT NULL,
  `typeStatus` varchar(128) DEFAULT NULL,
  `verbatimCollectingDate` varchar(128) DEFAULT NULL,
  `dateCollected` date DEFAULT '0000-00-00',
  `dateModified` date DEFAULT '0000-00-00',
  `verbatimLatitude` varchar(128) DEFAULT NULL,
  `verbatimLongitude` varchar(128) DEFAULT NULL,
  `elevation` int(11) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT '2100-00-00 00:00:00',
  `county` varchar(128) DEFAULT NULL,
  `islandGroup` varchar(128) DEFAULT NULL,
  `dateLastModified` varchar(128) DEFAULT NULL,
  `json` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12138 DEFAULT CHARSET=utf8;



# Dump of table darwin_core_ubio_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `darwin_core_ubio_joiner`;

CREATE TABLE `darwin_core_ubio_joiner` (
  `darwin_core_id` int(11) DEFAULT NULL,
  `namebankID` int(11) DEFAULT NULL,
  KEY `darwin_core_id` (`darwin_core_id`),
  KEY `namebankID` (`namebankID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table feed
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feed`;

CREATE TABLE `feed` (
  `url` varchar(255) DEFAULT NULL,
  `last_modified` varchar(64) DEFAULT NULL,
  `etag` varchar(64) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table feed_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feed_item`;

CREATE TABLE `feed_item` (
  `id` varchar(255) DEFAULT NULL,
  `feed_id` varchar(32) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `description` text,
  `updated` datetime DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `links` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table feed_source
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feed_source`;

CREATE TABLE `feed_source` (
  `id` varchar(32) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `last_accessed` datetime NOT NULL,
  `harvest_interval` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table genbank
# ------------------------------------------------------------

DROP TABLE IF EXISTS `genbank`;

CREATE TABLE `genbank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accession` varchar(16) DEFAULT NULL,
  `gi` int(11) DEFAULT NULL,
  `json` text,
  `taxon` int(11) DEFAULT NULL,
  `organism` varchar(255) DEFAULT NULL,
  `country` varchar(64) DEFAULT NULL,
  `locality` varchar(255) DEFAULT NULL,
  `specimen_voucher` varchar(255) DEFAULT NULL,
  `isolate` varchar(255) DEFAULT NULL,
  `specimen_code` varchar(32) DEFAULT NULL,
  `host` varchar(128) DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `taxonomic_group` varchar(64) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `pmid` int(11) DEFAULT NULL,
  `doi` varchar(255) DEFAULT NULL,
  `hdl` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `host_namebankID` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sequence` text,
  `lat_lon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lat_lon` (`lat_lon`),
  KEY `locality` (`locality`),
  KEY `organism` (`organism`)
) ENGINE=MyISAM AUTO_INCREMENT=217201 DEFAULT CHARSET=latin1;



# Dump of table issn
# ------------------------------------------------------------

DROP TABLE IF EXISTS `issn`;

CREATE TABLE `issn` (
  `title` varchar(255) DEFAULT NULL,
  `issn` varchar(9) CHARACTER SET latin1 DEFAULT NULL,
  `language_code` char(2) DEFAULT 'en',
  `comment` varchar(255) DEFAULT NULL,
  KEY `JournalTitle` (`title`),
  KEY `issn` (`issn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table jstor
# ------------------------------------------------------------

DROP TABLE IF EXISTS `jstor`;

CREATE TABLE `jstor` (
  `journal` varchar(255) NOT NULL DEFAULT '',
  `issn` varchar(9) NOT NULL DEFAULT '',
  `startDate` int(11) DEFAULT NULL,
  `endDate` int(11) DEFAULT NULL,
  `wall` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table secondary_author_reference_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `secondary_author_reference_joiner`;

CREATE TABLE `secondary_author_reference_joiner` (
  `author_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `author_order` tinyint(4) DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT '2100-00-00 00:00:00',
  KEY `author_id` (`author_id`),
  KEY `reference_id` (`reference_id`),
  CONSTRAINT `secondary_author_fk` FOREIGN KEY (`author_id`) REFERENCES `author` (`author_id`) ON DELETE CASCADE,
  CONSTRAINT `secondary_reference_fk` FOREIGN KEY (`reference_id`) REFERENCES `article_cache` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table services
# ------------------------------------------------------------

DROP TABLE IF EXISTS `services`;

CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `kind` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `kind` (`kind`)
) ENGINE=InnoDB AUTO_INCREMENT=166511 DEFAULT CHARSET=latin1;



# Dump of table status
# ------------------------------------------------------------

DROP TABLE IF EXISTS `status`;

CREATE TABLE `status` (
  `service_id` int(11) NOT NULL,
  `status` int(11) DEFAULT NULL,
  `tested` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `total_time` float DEFAULT NULL,
  `server` varchar(255) DEFAULT NULL,
  KEY `servicefk` (`service_id`),
  CONSTRAINT `servicefk` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table ubio_cache
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ubio_cache`;

CREATE TABLE `ubio_cache` (
  `namebankID` int(11) NOT NULL DEFAULT '0',
  `nameString` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `fullNameString` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `packageID` int(11) DEFAULT NULL,
  `packageName` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `basionymUnit` int(11) DEFAULT NULL,
  `rankID` int(11) DEFAULT NULL,
  `rankName` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`namebankID`),
  KEY `nameString` (`nameString`),
  KEY `fullNameString` (`fullNameString`),
  KEY `packageID` (`packageID`),
  KEY `packageName` (`packageName`),
  KEY `rankID` (`rankID`),
  KEY `rankName` (`rankName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;






/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
