-- MySQL dump 10.13  Distrib 5.6.33, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ottwatch
-- ------------------------------------------------------
-- Server version	5.6.33-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `answer`
--

DROP TABLE IF EXISTS `answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answer` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `questionid` mediumint(9) NOT NULL,
  `personid` mediumint(9) NOT NULL,
  `body` varchar(2048) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `in1` (`questionid`,`personid`),
  CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`questionid`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_candidate_donation`
--

DROP TABLE IF EXISTS `archive_candidate_donation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_candidate_donation` (
  `id` mediumint(9) NOT NULL DEFAULT '0',
  `returnid` mediumint(9) NOT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `prov` varchar(100) DEFAULT NULL,
  `postal` varchar(15) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `page` smallint(5) unsigned DEFAULT NULL,
  `x` smallint(5) unsigned DEFAULT NULL,
  `y` smallint(5) unsigned DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `location` point DEFAULT NULL,
  `peopleid` mediumint(9) DEFAULT NULL,
  `archid` mediumint(9) NOT NULL AUTO_INCREMENT,
  `archedit` datetime DEFAULT CURRENT_TIMESTAMP,
  `donorid` mediumint(8) unsigned DEFAULT NULL,
  `donor_gender` varchar(1) DEFAULT NULL,
  `comment` varchar(1024) DEFAULT NULL,
  `donation_date` date DEFAULT NULL,
  PRIMARY KEY (`archid`)
) ENGINE=InnoDB AUTO_INCREMENT=14317 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_candidate_donation2`
--

DROP TABLE IF EXISTS `archive_candidate_donation2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_candidate_donation2` (
  `id` mediumint(9) NOT NULL DEFAULT '0',
  `returnid` mediumint(9) NOT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `prov` varchar(100) DEFAULT NULL,
  `postal` varchar(15) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `page` smallint(5) unsigned DEFAULT NULL,
  `x` smallint(5) unsigned DEFAULT NULL,
  `y` smallint(5) unsigned DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `location` point DEFAULT NULL,
  `peopleid` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `budget_capital`
--

DROP TABLE IF EXISTS `budget_capital`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_capital` (
  `adopted` tinyint(1) DEFAULT NULL,
  `year` mediumint(9) DEFAULT NULL,
  `code` mediumint(8) unsigned DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `committee` varchar(32) DEFAULT NULL,
  `dept` varchar(32) DEFAULT NULL,
  `amount` mediumint(9) DEFAULT NULL,
  `service_area` varchar(32) DEFAULT NULL,
  `category` varchar(32) DEFAULT NULL,
  `ward` varchar(16) DEFAULT NULL,
  `completion` mediumint(9) DEFAULT NULL,
  `program` varchar(1024) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `listing` varchar(4096) DEFAULT NULL,
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1994 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `budget_draft_2015`
--

DROP TABLE IF EXISTS `budget_draft_2015`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_draft_2015` (
  `name` varchar(64) DEFAULT NULL,
  `committee` varchar(32) DEFAULT NULL,
  `dept` varchar(32) DEFAULT NULL,
  `service_area` varchar(32) DEFAULT NULL,
  `category` varchar(32) DEFAULT NULL,
  `ward` varchar(16) DEFAULT NULL,
  `completion` mediumint(9) DEFAULT NULL,
  `program` varchar(1024) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `listing` varchar(32) DEFAULT NULL,
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `amount` mediumint(9) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `buildingPermits_2015`
--

DROP TABLE IF EXISTS `buildingPermits_2015`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buildingPermits_2015` (
  `ottwatchid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `OBJECTID` bigint(20) DEFAULT NULL,
  `Status` varchar(1) DEFAULT NULL,
  `Score` float DEFAULT NULL,
  `Match_type` varchar(2) DEFAULT NULL,
  `Match_addr` varchar(120) DEFAULT NULL,
  `Postal` varchar(10) DEFAULT NULL,
  `Ref_ID` bigint(20) DEFAULT NULL,
  `X` float DEFAULT NULL,
  `Y` float DEFAULT NULL,
  `User_fld` varchar(120) DEFAULT NULL,
  `Addr_type` varchar(20) DEFAULT NULL,
  `ARC_Single` varchar(100) DEFAULT NULL,
  `Full_Addre` varchar(254) DEFAULT NULL,
  `PC` varchar(254) DEFAULT NULL,
  `WARD` varchar(254) DEFAULT NULL,
  `PLAN_` varchar(254) DEFAULT NULL,
  `LOT` varchar(254) DEFAULT NULL,
  `CONTRACTOR` varchar(254) DEFAULT NULL,
  `BLG_TYPE` varchar(254) DEFAULT NULL,
  `MUNICIPALI` varchar(254) DEFAULT NULL,
  `DESCRIPTIO` varchar(254) DEFAULT NULL,
  `D_U_` float DEFAULT NULL,
  `VALUE` float DEFAULT NULL,
  `FT2` float DEFAULT NULL,
  `PERMIT_` varchar(254) DEFAULT NULL,
  `APPL__TYPE` varchar(254) DEFAULT NULL,
  `ISSUED_DAT` datetime DEFAULT NULL,
  `M2` float DEFAULT NULL,
  `BLG_TYPE_F` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ottwatchid`)
) ENGINE=MyISAM AUTO_INCREMENT=8751 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bylaw`
--

DROP TABLE IF EXISTS `bylaw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bylaw` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `bylawnum` varchar(10) DEFAULT NULL,
  `summary` varchar(2048) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `meetingid` mediumint(9) DEFAULT NULL,
  `url` varchar(512) DEFAULT NULL,
  `enacted` date DEFAULT NULL,
  `bn_year` mediumint(9) DEFAULT NULL,
  `bn_num` mediumint(9) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=975 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidate`
--

DROP TABLE IF EXISTS `candidate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `year` smallint(6) DEFAULT NULL,
  `ward` tinyint(4) DEFAULT NULL,
  `first` varchar(50) DEFAULT NULL,
  `middle` varchar(50) DEFAULT NULL,
  `last` varchar(50) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `twitter` varchar(50) DEFAULT NULL,
  `facebook` varchar(100) DEFAULT NULL,
  `nominated` datetime DEFAULT NULL,
  `incumbent` tinyint(1) DEFAULT '0',
  `phone` varchar(30) DEFAULT NULL,
  `withdrew` datetime DEFAULT NULL,
  `personid` mediumint(9) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `retiring` tinyint(4) DEFAULT NULL,
  `winner` tinyint(4) DEFAULT NULL,
  `votes` mediumint(8) unsigned DEFAULT NULL,
  `electionid` mediumint(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `personid` (`personid`),
  CONSTRAINT `candidate_ibfk_1` FOREIGN KEY (`personid`) REFERENCES `people` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=939 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidate_donation`
--

DROP TABLE IF EXISTS `candidate_donation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_donation` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `returnid` mediumint(9) NOT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `prov` varchar(100) DEFAULT NULL,
  `postal` varchar(15) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `page` smallint(5) unsigned DEFAULT NULL,
  `x` smallint(5) unsigned DEFAULT NULL,
  `y` smallint(5) unsigned DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `location` point DEFAULT NULL,
  `peopleid` mediumint(9) DEFAULT NULL,
  `donorid` mediumint(8) unsigned DEFAULT NULL,
  `donor_gender` varchar(1) DEFAULT NULL,
  `donation_date` date DEFAULT NULL,
  `comment` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `returnid` (`returnid`),
  CONSTRAINT `candidate_donation_ibfk_1` FOREIGN KEY (`returnid`) REFERENCES `candidate_return` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14856 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidate_return`
--

DROP TABLE IF EXISTS `candidate_return`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_return` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `candidateid` mediumint(9) NOT NULL,
  `filename` varchar(512) DEFAULT NULL,
  `supplemental` tinyint(1) DEFAULT NULL,
  `done` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidateid` (`candidateid`),
  CONSTRAINT `candidate_return_ibfk_1` FOREIGN KEY (`candidateid`) REFERENCES `candidate` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1067 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `category` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultation`
--

DROP TABLE IF EXISTS `consultation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultation` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `category` varchar(300) DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `md5` varchar(50) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1170 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultationdoc`
--

DROP TABLE IF EXISTS `consultationdoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultationdoc` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `consultationid` mediumint(9) NOT NULL,
  `title` varchar(300) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `md5` varchar(50) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consultationid` (`consultationid`),
  CONSTRAINT `consultationdoc_ibfk_1` FOREIGN KEY (`consultationid`) REFERENCES `consultation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3008 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devapp`
--

DROP TABLE IF EXISTS `devapp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devapp` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `appid` varchar(10) DEFAULT NULL,
  `devid` varchar(32) DEFAULT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `apptype` varchar(100) DEFAULT NULL,
  `receiveddate` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `address` varchar(4096) DEFAULT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `coadesc` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5225 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devappfile`
--

DROP TABLE IF EXISTS `devappfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devappfile` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `devappid` mediumint(9) NOT NULL,
  `href` varchar(300) DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `devappid` (`devappid`),
  CONSTRAINT `devappfile_ibfk_1` FOREIGN KEY (`devappid`) REFERENCES `devapp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=99052 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devappstatus`
--

DROP TABLE IF EXISTS `devappstatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devappstatus` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `devappid` mediumint(9) NOT NULL,
  `statusdate` datetime DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `devappstatus_in1` (`devappid`,`statusdate`,`status`),
  CONSTRAINT `devappstatus_ibfk_1` FOREIGN KEY (`devappid`) REFERENCES `devapp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65796 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `electedofficials`
--

DROP TABLE IF EXISTS `electedofficials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `electedofficials` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `ward` varchar(100) DEFAULT NULL,
  `wardnum` varchar(5) DEFAULT NULL,
  `office` varchar(25) DEFAULT NULL,
  `first` varchar(50) DEFAULT NULL,
  `last` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `photourl` varchar(300) DEFAULT NULL,
  `phone` varchar(12) DEFAULT NULL,
  `twitter` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election`
--

DROP TABLE IF EXISTS `election`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `election` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `city` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_donor`
--

DROP TABLE IF EXISTS `election_donor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `election_donor` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7481 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_question`
--

DROP TABLE IF EXISTS `election_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `election_question` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `questionid` mediumint(9) NOT NULL,
  `ward` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questionid` (`questionid`),
  CONSTRAINT `election_question_ibfk_1` FOREIGN KEY (`questionid`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_vote`
--

DROP TABLE IF EXISTS `election_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `election_vote` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `electionid` mediumint(9) NOT NULL,
  `candidateid` mediumint(9) NOT NULL,
  `race` mediumint(9) DEFAULT NULL,
  `ward` mediumint(9) DEFAULT NULL,
  `poll` mediumint(9) DEFAULT NULL,
  `votes` mediumint(9) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `precinct` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `electionid` (`electionid`),
  KEY `candidateid` (`candidateid`),
  CONSTRAINT `election_vote_ibfk_1` FOREIGN KEY (`electionid`) REFERENCES `election` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_vote_ibfk_2` FOREIGN KEY (`candidateid`) REFERENCES `candidate` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8866 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `election_vote_candidate`
--

DROP TABLE IF EXISTS `election_vote_candidate`;
/*!50001 DROP VIEW IF EXISTS `election_vote_candidate`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `election_vote_candidate` AS SELECT 
 1 AS `electionid`,
 1 AS `candidateid`,
 1 AS `race`,
 1 AS `votes`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `election_vote_race`
--

DROP TABLE IF EXISTS `election_vote_race`;
/*!50001 DROP VIEW IF EXISTS `election_vote_race`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `election_vote_race` AS SELECT 
 1 AS `electionid`,
 1 AS `race`,
 1 AS `votes`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `feed`
--

DROP TABLE IF EXISTS `feed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feed` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `message` varchar(300) DEFAULT NULL,
  `path` varchar(300) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20835 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geometry_columns`
--

DROP TABLE IF EXISTS `geometry_columns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geometry_columns` (
  `F_TABLE_CATALOG` varchar(256) DEFAULT NULL,
  `F_TABLE_SCHEMA` varchar(256) DEFAULT NULL,
  `F_TABLE_NAME` varchar(256) NOT NULL,
  `F_GEOMETRY_COLUMN` varchar(256) NOT NULL,
  `COORD_DIMENSION` int(11) DEFAULT NULL,
  `SRID` int(11) DEFAULT NULL,
  `TYPE` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ifile`
--

DROP TABLE IF EXISTS `ifile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ifile` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `itemid` mediumint(9) NOT NULL,
  `fileid` mediumint(9) DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `md5` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ii2` (`itemid`,`id`),
  CONSTRAINT `ifile_ibfk_1` FOREIGN KEY (`itemid`) REFERENCES `item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6619677 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `meetingid` mediumint(9) NOT NULL,
  `itemid` mediumint(9) DEFAULT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meetingid` (`meetingid`),
  CONSTRAINT `item_ibfk_1` FOREIGN KEY (`meetingid`) REFERENCES `meeting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2274511 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemvote`
--

DROP TABLE IF EXISTS `itemvote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemvote` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `itemid` mediumint(9) NOT NULL,
  `motion` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `itemid` (`itemid`),
  CONSTRAINT `itemvote_ibfk_1` FOREIGN KEY (`itemid`) REFERENCES `item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=238806 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemvote_20161013`
--

DROP TABLE IF EXISTS `itemvote_20161013`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemvote_20161013` (
  `id` mediumint(9) NOT NULL DEFAULT '0',
  `itemid` mediumint(9) NOT NULL,
  `motion` varchar(60000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemvotecast`
--

DROP TABLE IF EXISTS `itemvotecast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemvotecast` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `itemvoteid` mediumint(9) NOT NULL,
  `vote` varchar(1) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `itemvoteid` (`itemvoteid`),
  CONSTRAINT `itemvotecast_ibfk_1` FOREIGN KEY (`itemvoteid`) REFERENCES `itemvote` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3858638 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `itemvotecast_summary1`
--

DROP TABLE IF EXISTS `itemvotecast_summary1`;
/*!50001 DROP VIEW IF EXISTS `itemvotecast_summary1`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `itemvotecast_summary1` AS SELECT 
 1 AS `itemvoteid`,
 1 AS `y_votes`,
 1 AS `n_votes`,
 1 AS `yn_votes`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `itemvotecast_summary2`
--

DROP TABLE IF EXISTS `itemvotecast_summary2`;
/*!50001 DROP VIEW IF EXISTS `itemvotecast_summary2`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `itemvotecast_summary2` AS SELECT 
 1 AS `itemvoteid`,
 1 AS `vote`,
 1 AS `asVote`,
 1 AS `yn_votes`,
 1 AS `majority`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `itemvotetab`
--

DROP TABLE IF EXISTS `itemvotetab`;
/*!50001 DROP VIEW IF EXISTS `itemvotetab`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `itemvotetab` AS SELECT 
 1 AS `itemvoteid`,
 1 AS `passed`,
 1 AS `y`,
 1 AS `n`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `latelobbying`
--

DROP TABLE IF EXISTS `latelobbying`;
/*!50001 DROP VIEW IF EXISTS `latelobbying`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latelobbying` AS SELECT 
 1 AS `id`,
 1 AS `lobbydate`,
 1 AS `created`,
 1 AS `diff`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `lobbyfile`
--

DROP TABLE IF EXISTS `lobbyfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lobbyfile` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `lobbyist` varchar(100) DEFAULT NULL,
  `client` varchar(100) DEFAULT NULL,
  `issue` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2319 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lobbying`
--

DROP TABLE IF EXISTS `lobbying`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lobbying` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lobbyfileid` mediumint(9) NOT NULL,
  `lobbydate` datetime DEFAULT NULL,
  `activity` varchar(100) DEFAULT NULL,
  `lobbied` varchar(200) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `electedofficialid` mediumint(9) DEFAULT NULL,
  `lobbiednorm` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lobbying_in1` (`lobbyfileid`,`lobbydate`,`activity`,`lobbied`),
  KEY `electedofficialid` (`electedofficialid`),
  CONSTRAINT `lobbying_ibfk_1` FOREIGN KEY (`lobbyfileid`) REFERENCES `lobbyfile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lobbying_ibfk_2` FOREIGN KEY (`electedofficialid`) REFERENCES `electedofficials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33805300 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `md5hist`
--

DROP TABLE IF EXISTS `md5hist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `md5hist` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `curmd5` varchar(50) DEFAULT NULL,
  `prevmd5` varchar(50) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `url` varchar(512) DEFAULT NULL,
  `s3url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1532 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meeting`
--

DROP TABLE IF EXISTS `meeting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meeting` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `rssguid` varchar(200) DEFAULT NULL,
  `meetid` mediumint(9) DEFAULT NULL,
  `starttime` datetime DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `contactName` varchar(100) DEFAULT NULL,
  `contactEmail` varchar(100) DEFAULT NULL,
  `contactPhone` varchar(30) DEFAULT NULL,
  `members` varchar(300) DEFAULT NULL,
  `minutes` tinyint(1) DEFAULT '0',
  `youtube` varchar(100) DEFAULT NULL,
  `youtubeset` datetime DEFAULT NULL,
  `youtubestart` smallint(5) unsigned DEFAULT NULL,
  `youtubestate` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2018 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mfippa`
--

DROP TABLE IF EXISTS `mfippa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mfippa` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `tag` varchar(12) DEFAULT NULL,
  `closed` datetime DEFAULT NULL,
  `source` varchar(12) DEFAULT NULL,
  `page` smallint(5) unsigned NOT NULL,
  `x` smallint(5) unsigned NOT NULL,
  `y` smallint(5) unsigned NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `summary` varchar(2000) DEFAULT NULL,
  `published` tinyint(1) DEFAULT '0',
  `disclosed` tinyint(1) DEFAULT NULL,
  `disposition` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mfippa_in1` (`tag`)
) ENGINE=InnoDB AUTO_INCREMENT=2237 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `opendata`
--

DROP TABLE IF EXISTS `opendata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opendata` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL,
  `name` varchar(300) DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1441 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `opendatafile`
--

DROP TABLE IF EXISTS `opendatafile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opendatafile` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `dataid` mediumint(9) NOT NULL,
  `guid` varchar(100) NOT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `format` varchar(32) DEFAULT NULL,
  `name` varchar(300) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP,
  `hash` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dataid` (`dataid`),
  CONSTRAINT `opendatafile_ibfk_1` FOREIGN KEY (`dataid`) REFERENCES `opendata` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5322 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ott311tweet`
--

DROP TABLE IF EXISTS `ott311tweet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ott311tweet` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `twid` varchar(20) NOT NULL,
  `in_reply_to_twid` varchar(20) DEFAULT NULL,
  `user_id` varchar(20) NOT NULL,
  `user_name` varchar(20) NOT NULL,
  `tweet` varchar(160) NOT NULL,
  `location` geometry DEFAULT NULL,
  `tweeted` datetime DEFAULT CURRENT_TIMESTAMP,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ott311_1` (`twid`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `twitter` varchar(32) DEFAULT NULL,
  `facebookid` bigint(20) unsigned DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `emailverified` tinyint(1) DEFAULT '0',
  `admin` tinyint(1) DEFAULT '0',
  `author` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `people_in1` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=397 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permit`
--

DROP TABLE IF EXISTS `permit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permit` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `st_num` varchar(20) DEFAULT NULL,
  `st_name` varchar(100) DEFAULT NULL,
  `postal` varchar(7) DEFAULT NULL,
  `ward` tinyint(4) DEFAULT NULL,
  `plan_num` varchar(30) DEFAULT NULL,
  `lot_num` varchar(30) DEFAULT NULL,
  `contractor` varchar(200) DEFAULT NULL,
  `building_type` varchar(200) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `du` mediumint(9) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  `area` mediumint(9) DEFAULT NULL,
  `permit_number` mediumint(9) DEFAULT NULL,
  `app_type` varchar(100) DEFAULT NULL,
  `issued_date` date DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=958 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `places`
--

DROP TABLE IF EXISTS `places`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `places` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `roadid` int(11) NOT NULL,
  `rd_num` mediumint(9) NOT NULL,
  `personid` mediumint(9) DEFAULT NULL,
  `shape` geometry DEFAULT NULL,
  `itemid` mediumint(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `places_in1` (`roadid`,`rd_num`,`personid`),
  KEY `personid` (`personid`),
  KEY `itemid` (`itemid`),
  CONSTRAINT `places_ibfk_1` FOREIGN KEY (`personid`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `places_ibfk_3` FOREIGN KEY (`itemid`) REFERENCES `item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=194477 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `polls_2010`
--

DROP TABLE IF EXISTS `polls_2010`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls_2010` (
  `OGR_FID` int(11) NOT NULL AUTO_INCREMENT,
  `SHAPE` geometry NOT NULL,
  `vot_subd` varchar(254) DEFAULT NULL,
  `ward` varchar(254) DEFAULT NULL,
  `ward_en` varchar(254) DEFAULT NULL,
  `ward_fr` varchar(254) DEFAULT NULL,
  `voteareaid` varchar(15) DEFAULT NULL,
  UNIQUE KEY `OGR_FID` (`OGR_FID`)
) ENGINE=InnoDB AUTO_INCREMENT=1070 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `polls_2014`
--

DROP TABLE IF EXISTS `polls_2014`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls_2014` (
  `OGR_FID` int(11) NOT NULL AUTO_INCREMENT,
  `SHAPE` geometry NOT NULL,
  `objectid` decimal(10,0) DEFAULT NULL,
  `vot_subd` varchar(254) DEFAULT NULL,
  `ward` varchar(254) DEFAULT NULL,
  `ward_en` varchar(254) DEFAULT NULL,
  `ward_fr` varchar(254) DEFAULT NULL,
  `shape_area` double(19,11) DEFAULT NULL,
  `shape_len` double(19,11) DEFAULT NULL,
  UNIQUE KEY `OGR_FID` (`OGR_FID`)
) ENGINE=InnoDB AUTO_INCREMENT=1094 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `polls_2018`
--

DROP TABLE IF EXISTS `polls_2018`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls_2018` (
  `OGR_FID` int(11) NOT NULL AUTO_INCREMENT,
  `SHAPE` geometry NOT NULL,
  `vot_subd` varchar(254) DEFAULT NULL,
  `ward` varchar(254) DEFAULT NULL,
  `ward_en` varchar(254) DEFAULT NULL,
  `ward_fr` varchar(254) DEFAULT NULL,
  `shape_area` double(19,11) DEFAULT NULL,
  `shape_len` double(19,11) DEFAULT NULL,
  UNIQUE KEY `OGR_FID` (`OGR_FID`)
) ENGINE=InnoDB AUTO_INCREMENT=291 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `publicevent`
--

DROP TABLE IF EXISTS `publicevent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `publicevent` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `href` varchar(512) DEFAULT NULL,
  `starttime` datetime DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `body` varchar(500) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP,
  `published` tinyint(1) DEFAULT '0',
  `personid` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `personid` (`personid`),
  CONSTRAINT `question_ibfk_1` FOREIGN KEY (`personid`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_vote`
--

DROP TABLE IF EXISTS `question_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_vote` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `questionid` mediumint(9) NOT NULL,
  `personid` mediumint(9) NOT NULL,
  `vote` tinyint(4) NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_vote_in1` (`questionid`,`personid`),
  KEY `personid` (`personid`),
  CONSTRAINT `question_vote_ibfk_1` FOREIGN KEY (`questionid`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `question_vote_ibfk_2` FOREIGN KEY (`personid`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spatial_ref_sys`
--

DROP TABLE IF EXISTS `spatial_ref_sys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spatial_ref_sys` (
  `SRID` int(11) NOT NULL,
  `AUTH_NAME` varchar(256) DEFAULT NULL,
  `AUTH_SRID` int(11) DEFAULT NULL,
  `SRTEXT` varchar(2048) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sr`
--

DROP TABLE IF EXISTS `sr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sr` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `sr_id` varchar(12) NOT NULL,
  `status` varchar(6) DEFAULT NULL,
  `service_code` varchar(9) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `requested` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `expected` datetime DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `scanned` datetime DEFAULT CURRENT_TIMESTAMP,
  `close_detected` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sr_in1` (`sr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=138358 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sr_status`
--

DROP TABLE IF EXISTS `sr_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sr_status` (
  `id` mediumint(9) NOT NULL DEFAULT '0',
  `status` varchar(6) DEFAULT NULL,
  `statusdate` datetime DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `story`
--

DROP TABLE IF EXISTS `story`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `story` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `personid` mediumint(9) NOT NULL,
  `title` varchar(300) DEFAULT NULL,
  `body` text,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL,
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `personid` (`personid`),
  CONSTRAINT `story_ibfk_1` FOREIGN KEY (`personid`) REFERENCES `people` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `story54`
--

DROP TABLE IF EXISTS `story54`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `story54` (
  `snapshot` varchar(8) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `zone_code` varchar(50) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `c` bigint(21) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `variable`
--

DROP TABLE IF EXISTS `variable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variable` (
  `name` varchar(64) NOT NULL,
  `value` longtext,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `votinglocations_2010`
--

DROP TABLE IF EXISTS `votinglocations_2010`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votinglocations_2010` (
  `OGR_FID` int(11) NOT NULL AUTO_INCREMENT,
  `SHAPE` geometry NOT NULL,
  `loc_en` varchar(254) DEFAULT NULL,
  `loc_fr` varchar(254) DEFAULT NULL,
  `addr_en` varchar(254) DEFAULT NULL,
  `addr_fr` varchar(254) DEFAULT NULL,
  `ward_num` tinyint(4) DEFAULT NULL,
  `vot_area` varchar(254) DEFAULT NULL,
  `day_en` varchar(254) DEFAULT NULL,
  `day_fr` varchar(254) DEFAULT NULL,
  `ward_en` varchar(50) DEFAULT NULL,
  `ward_fr` varchar(50) DEFAULT NULL,
  `voteareaid` varchar(15) DEFAULT NULL,
  UNIQUE KEY `OGR_FID` (`OGR_FID`)
) ENGINE=InnoDB AUTO_INCREMENT=555 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wards_2010`
--

DROP TABLE IF EXISTS `wards_2010`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wards_2010` (
  `OGR_FID` int(11) NOT NULL AUTO_INCREMENT,
  `shape` geometry NOT NULL,
  `ward_num` varchar(3) DEFAULT NULL,
  `ward_en` varchar(50) DEFAULT NULL,
  `ward_fr` varchar(50) DEFAULT NULL,
  UNIQUE KEY `OGR_FID` (`OGR_FID`),
  SPATIAL KEY `in_shape` (`shape`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `election_vote_candidate`
--

/*!50001 DROP VIEW IF EXISTS `election_vote_candidate`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`ottwatch`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `election_vote_candidate` AS select `election_vote`.`electionid` AS `electionid`,`election_vote`.`candidateid` AS `candidateid`,`election_vote`.`race` AS `race`,sum(`election_vote`.`votes`) AS `votes` from `election_vote` group by `election_vote`.`electionid`,`election_vote`.`candidateid`,`election_vote`.`race` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `election_vote_race`
--

/*!50001 DROP VIEW IF EXISTS `election_vote_race`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`ottwatch`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `election_vote_race` AS select `election_vote`.`electionid` AS `electionid`,`election_vote`.`race` AS `race`,sum(`election_vote`.`votes`) AS `votes` from `election_vote` group by `election_vote`.`electionid`,`election_vote`.`race` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `itemvotecast_summary1`
--

/*!50001 DROP VIEW IF EXISTS `itemvotecast_summary1`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`ottwatch`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `itemvotecast_summary1` AS select `itemvotecast`.`itemvoteid` AS `itemvoteid`,sum((case when (`itemvotecast`.`vote` = 'y') then 1 else 0 end)) AS `y_votes`,sum((case when (`itemvotecast`.`vote` = 'n') then 1 else 0 end)) AS `n_votes`,sum((case when (`itemvotecast`.`vote` = 'y') then 1 when (`itemvotecast`.`vote` = 'n') then 1 else 0 end)) AS `yn_votes` from `itemvotecast` group by `itemvotecast`.`itemvoteid` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `itemvotecast_summary2`
--

/*!50001 DROP VIEW IF EXISTS `itemvotecast_summary2`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`ottwatch`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `itemvotecast_summary2` AS select `ivc1`.`itemvoteid` AS `itemvoteid`,`ivc1`.`vote` AS `vote`,count(1) AS `asVote`,`c`.`yn_votes` AS `yn_votes`,(case when ((count(1) / `c`.`yn_votes`) >= 0.50) then 1 else 0 end) AS `majority` from (`itemvotecast` `ivc1` join `itemvotecast_summary1` `c` on((`c`.`itemvoteid` = `ivc1`.`itemvoteid`))) group by `ivc1`.`itemvoteid`,`ivc1`.`vote` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `itemvotetab`
--

/*!50001 DROP VIEW IF EXISTS `itemvotetab`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`ottwatch`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `itemvotetab` AS select `itemvotecast`.`itemvoteid` AS `itemvoteid`,(case when (sum((case when (`itemvotecast`.`vote` = 'y') then 1 else 0 end)) > sum((case when (`itemvotecast`.`vote` = 'n') then 1 else 0 end))) then 1 else 0 end) AS `passed`,sum((case when (`itemvotecast`.`vote` = 'y') then 1 else 0 end)) AS `y`,sum((case when (`itemvotecast`.`vote` = 'n') then 1 else 0 end)) AS `n` from `itemvotecast` group by `itemvotecast`.`itemvoteid` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latelobbying`
--

/*!50001 DROP VIEW IF EXISTS `latelobbying`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`ottwatch`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `latelobbying` AS select `lobbying`.`id` AS `id`,`lobbying`.`lobbydate` AS `lobbydate`,`lobbying`.`created` AS `created`,(to_days(`lobbying`.`created`) - to_days(`lobbying`.`lobbydate`)) AS `diff` from `lobbying` where ((to_days(`lobbying`.`created`) - to_days(`lobbying`.`lobbydate`)) >= 24) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-04-10 11:21:42
