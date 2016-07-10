/*
SQLyog Ultimate v12.16 (64 bit)
MySQL - 5.1.50-community : Database - athletica
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`athletica` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `athletica`;

/*Table structure for table `anlage` */

DROP TABLE IF EXISTS `anlage`;

CREATE TABLE `anlage` (
  `xAnlage` int(11) NOT NULL AUTO_INCREMENT,
  `Bezeichnung` varchar(20) NOT NULL DEFAULT '',
  `Homologiert` enum('y','n') NOT NULL DEFAULT 'y',
  `xStadion` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xAnlage`),
  KEY `xStadion` (`xStadion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `anlage` */

/*Table structure for table `anmeldung` */

DROP TABLE IF EXISTS `anmeldung`;

CREATE TABLE `anmeldung` (
  `xAnmeldung` int(11) NOT NULL AUTO_INCREMENT,
  `Startnummer` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Erstserie` enum('y','n') NOT NULL DEFAULT 'n',
  `Bezahlt` enum('y','n') NOT NULL DEFAULT 'y',
  `Gruppe` char(2) NOT NULL DEFAULT '',
  `BestleistungMK` float NOT NULL DEFAULT '0',
  `Vereinsinfo` varchar(150) NOT NULL DEFAULT '',
  `xAthlet` int(11) NOT NULL DEFAULT '0',
  `xMeeting` int(11) NOT NULL DEFAULT '0',
  `xKategorie` int(11) DEFAULT NULL,
  `xTeam` int(11) NOT NULL DEFAULT '0',
  `BaseEffortMK` enum('y','n') NOT NULL DEFAULT 'n',
  `Anmeldenr_ZLV` int(11) DEFAULT '0',
  `KidID` int(11) DEFAULT '0',
  `Angemeldet` enum('y','n') DEFAULT 'n',
  `VorjahrLeistungMK` int(11) DEFAULT '0',
  PRIMARY KEY (`xAnmeldung`),
  UNIQUE KEY `AthleteMeetingKat` (`xAthlet`,`xMeeting`,`xKategorie`),
  KEY `xAthlet` (`xAthlet`),
  KEY `xMeeting` (`xMeeting`),
  KEY `xKategorie` (`xKategorie`),
  KEY `Startnummer` (`Startnummer`),
  KEY `xTeam` (`xTeam`),
  KEY `Vereinsinfo` (`Vereinsinfo`)
) ENGINE=MyISAM AUTO_INCREMENT=1674 DEFAULT CHARSET=utf8;

/*Data for the table `anmeldung` */


/*Table structure for table `athlet` */

DROP TABLE IF EXISTS `athlet`;

CREATE TABLE `athlet` (
  `xAthlet` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL DEFAULT '',
  `Vorname` varchar(50) NOT NULL DEFAULT '',
  `Jahrgang` year(4) DEFAULT NULL,
  `xVerein` int(11) NOT NULL DEFAULT '0',
  `xVerein2` int(11) NOT NULL DEFAULT '0',
  `Lizenznummer` int(11) NOT NULL DEFAULT '0',
  `Geschlecht` enum('m','w') NOT NULL DEFAULT 'm',
  `Land` char(3) NOT NULL DEFAULT '',
  `Geburtstag` date NOT NULL DEFAULT '0000-00-00',
  `Athleticagen` enum('y','n') NOT NULL DEFAULT 'n',
  `Bezahlt` enum('y','n') NOT NULL DEFAULT 'n',
  `xRegion` int(11) NOT NULL DEFAULT '0',
  `Lizenztyp` tinyint(2) NOT NULL DEFAULT '0',
  `Manuell` int(1) NOT NULL DEFAULT '0',
  `Adresse` varchar(50) DEFAULT NULL,
  `Plz` int(6) DEFAULT '0',
  `Ort` varchar(50) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`xAthlet`),
  UNIQUE KEY `Athlet` (`Name`,`Vorname`,`Geburtstag`,`xVerein`),
  KEY `Name` (`Name`),
  KEY `xVerein` (`xVerein`),
  KEY `Lizenznummer` (`Lizenznummer`)
) ENGINE=MyISAM AUTO_INCREMENT=27037 DEFAULT CHARSET=utf8;

/*Data for the table `athlet` */


/*Table structure for table `base_account` */

DROP TABLE IF EXISTS `base_account`;

CREATE TABLE `base_account` (
  `account_code` varchar(30) NOT NULL DEFAULT '',
  `account_name` varchar(255) NOT NULL DEFAULT '',
  `account_short` varchar(255) NOT NULL DEFAULT '',
  `account_type` varchar(100) NOT NULL DEFAULT '',
  `lg` varchar(100) NOT NULL DEFAULT '',
  KEY `account_code` (`account_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `base_account` */


/*Table structure for table `base_athlete` */

DROP TABLE IF EXISTS `base_athlete`;

CREATE TABLE `base_athlete` (
  `id_athlete` int(11) NOT NULL AUTO_INCREMENT,
  `license` int(11) NOT NULL DEFAULT '0',
  `license_paid` enum('y','n') NOT NULL DEFAULT 'y',
  `license_cat` varchar(4) NOT NULL DEFAULT '',
  `lastname` varchar(100) NOT NULL DEFAULT '',
  `firstname` varchar(100) NOT NULL DEFAULT '',
  `sex` enum('m','w') NOT NULL DEFAULT 'm',
  `nationality` char(3) NOT NULL DEFAULT '',
  `account_code` varchar(30) NOT NULL DEFAULT '',
  `second_account_code` varchar(30) NOT NULL DEFAULT '',
  `birth_date` date NOT NULL DEFAULT '0000-00-00',
  `account_info` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_athlete`),
  KEY `account_code` (`account_code`),
  KEY `second_account_code` (`second_account_code`),
  KEY `license` (`license`),
  KEY `lastname` (`lastname`),
  KEY `firstname` (`firstname`)
) ENGINE=MyISAM AUTO_INCREMENT=38007 DEFAULT CHARSET=utf8;

/*Data for the table `base_athlete` */


/*Table structure for table `base_log` */

DROP TABLE IF EXISTS `base_log`;

CREATE TABLE `base_log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `global_last_change` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id_log`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `base_log` */


/*Table structure for table `base_performance` */

DROP TABLE IF EXISTS `base_performance`;

CREATE TABLE `base_performance` (
  `id_performance` int(11) NOT NULL AUTO_INCREMENT,
  `id_athlete` int(11) NOT NULL DEFAULT '0',
  `discipline` smallint(6) NOT NULL DEFAULT '0',
  `category` varchar(10) NOT NULL DEFAULT '',
  `best_effort` varchar(15) NOT NULL DEFAULT '',
  `best_effort_date` date NOT NULL DEFAULT '0000-00-00',
  `best_effort_event` varchar(100) NOT NULL DEFAULT '',
  `season_effort` varchar(15) NOT NULL DEFAULT '',
  `season_effort_date` date NOT NULL DEFAULT '0000-00-00',
  `season_effort_event` varchar(100) NOT NULL DEFAULT '',
  `notification_effort` varchar(15) NOT NULL DEFAULT '',
  `notification_effort_date` date NOT NULL DEFAULT '0000-00-00',
  `notification_effort_event` varchar(100) NOT NULL DEFAULT '',
  `season` enum('I','O') NOT NULL DEFAULT 'O',
  PRIMARY KEY (`id_performance`),
  UNIQUE KEY `id_athlete_discipline_season` (`id_athlete`,`discipline`,`season`),
  KEY `id_athlete` (`id_athlete`),
  KEY `discipline` (`discipline`),
  KEY `season` (`season`)
) ENGINE=MyISAM AUTO_INCREMENT=327517 DEFAULT CHARSET=utf8;

/*Data for the table `base_performance` */


/*Table structure for table `base_relay` */

DROP TABLE IF EXISTS `base_relay`;

CREATE TABLE `base_relay` (
  `id_relay` int(11) NOT NULL DEFAULT '0',
  `is_athletica_gen` enum('y','n') NOT NULL DEFAULT 'y',
  `relay_name` varchar(255) NOT NULL DEFAULT '',
  `category` varchar(10) NOT NULL DEFAULT '',
  `discipline` varchar(10) NOT NULL DEFAULT '',
  `account_code` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_relay`),
  KEY `account_code` (`account_code`),
  KEY `discipline` (`discipline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `base_relay` */


/*Table structure for table `base_svm` */

DROP TABLE IF EXISTS `base_svm`;

CREATE TABLE `base_svm` (
  `id_svm` int(11) NOT NULL DEFAULT '0',
  `is_athletica_gen` enum('y','n') NOT NULL DEFAULT 'y',
  `svm_name` varchar(255) NOT NULL DEFAULT '',
  `svm_category` varchar(10) NOT NULL DEFAULT '',
  `account_code` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_svm`),
  KEY `account_code` (`account_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `base_svm` */


/*Table structure for table `disziplin_de` */

DROP TABLE IF EXISTS `disziplin_de`;

CREATE TABLE `disziplin_de` (
  `xDisziplin` int(11) NOT NULL AUTO_INCREMENT,
  `Kurzname` varchar(15) NOT NULL DEFAULT '',
  `Name` varchar(40) NOT NULL DEFAULT '',
  `Anzeige` int(11) NOT NULL DEFAULT '1',
  `Seriegroesse` int(4) NOT NULL DEFAULT '0',
  `Staffellaeufer` int(11) DEFAULT NULL,
  `Typ` int(11) NOT NULL DEFAULT '0',
  `Appellzeit` time NOT NULL DEFAULT '00:00:00',
  `Stellzeit` time NOT NULL DEFAULT '00:00:00',
  `Strecke` float NOT NULL DEFAULT '0',
  `Code` int(11) NOT NULL DEFAULT '0',
  `xOMEGA_Typ` int(11) NOT NULL DEFAULT '0',
  `aktiv` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`xDisziplin`),
  UNIQUE KEY `Kurzname` (`Kurzname`),
  KEY `Anzeige` (`Anzeige`),
  KEY `Staffel` (`Staffellaeufer`),
  KEY `Code` (`Code`)
) ENGINE=MyISAM AUTO_INCREMENT=206 DEFAULT CHARSET=utf8;

/*Data for the table `disziplin_de` */

insert  into `disziplin_de`(`xDisziplin`,`Kurzname`,`Name`,`Anzeige`,`Seriegroesse`,`Staffellaeufer`,`Typ`,`Appellzeit`,`Stellzeit`,`Strecke`,`Code`,`xOMEGA_Typ`,`aktiv`) values 
(38,'50','50 m',10,6,0,2,'01:00:00','00:15:00',50,10,1,'y'),
(39,'55','55 m',20,6,0,2,'01:00:00','00:15:00',55,20,1,'y'),
(40,'60','60 m',30,6,0,2,'01:00:00','00:15:00',60,30,1,'y'),
(41,'80','80 m',35,6,0,1,'01:00:00','00:15:00',80,35,1,'y'),
(42,'100','100 m',40,8,0,1,'01:00:00','00:20:00',100,40,1,'y'),
(43,'150','150 m',48,6,0,1,'01:00:00','00:15:00',150,48,1,'y'),
(44,'200','200 m',50,6,0,1,'01:00:00','00:15:00',200,50,1,'y'),
(45,'300','300 m',60,6,0,2,'01:00:00','00:15:00',300,60,1,'y'),
(46,'400','400 m',70,6,0,2,'01:00:00','00:20:00',400,70,1,'y'),
(47,'600','600 m',80,12,0,7,'01:00:00','00:15:00',600,80,1,'y'),
(48,'800','800 m',90,6,0,7,'01:00:00','00:20:00',800,90,1,'y'),
(49,'1000','1000 m',100,15,0,7,'01:00:00','00:15:00',1000,100,1,'y'),
(50,'1500','1500 m',110,13,0,7,'01:00:00','00:20:00',1500,110,1,'y'),
(51,'1MEILE','1 Meile',120,15,0,7,'01:00:00','00:15:00',1609,120,1,'y'),
(52,'2000','2000 m',130,15,0,7,'01:00:00','00:15:00',2000,130,1,'y'),
(53,'3000','3000 m',140,15,0,7,'01:00:00','00:15:00',3000,140,1,'y'),
(54,'5000','5000 m',160,15,0,7,'01:00:00','00:15:00',5000,160,1,'y'),
(55,'10000','10 000 m',170,20,0,7,'01:00:00','00:15:00',10000,170,1,'y'),
(56,'20000','20 000 m',180,20,0,7,'01:00:00','00:15:00',20000,180,1,'y'),
(57,'1STUNDE','1 Stunde',171,20,0,7,'01:00:00','00:15:00',1,182,1,'y'),
(58,'25000','25 000 m',181,20,0,7,'01:00:00','00:15:00',25000,181,1,'y'),
(59,'30000','30 000 m',182,20,0,7,'01:00:00','00:15:00',30000,195,1,'y'),
(61,'HALBMARATH','Halbmarathon',183,20,0,7,'01:00:00','00:15:00',0,190,1,'y'),
(62,'MARATHON','Marathon',184,20,0,7,'01:00:00','00:15:00',0,200,1,'y'),
(64,'50H106.7','50 m Hürden 106.7',232,6,0,1,'01:00:00','00:15:00',50,232,4,'y'),
(65,'50H99.1','50 m Hürden 99.1',233,6,0,2,'01:00:00','00:15:00',50,233,4,'y'),
(66,'50H91.4','50 m Hürden 91.4',234,6,0,2,'01:00:00','00:15:00',50,234,4,'y'),
(67,'50H84.0','50 m Hürden 84.0',235,6,0,2,'01:00:00','00:15:00',50,235,4,'y'),
(68,'50H76.2','50 m Hürden 76.2  U18 W',236,6,0,2,'01:00:00','00:15:00',50,236,4,'y'),
(69,'60H106.7','60 m Hürden 106.7',241,6,0,2,'01:00:00','00:15:00',60,252,4,'y'),
(70,'60H99.1','60 m Hürden 99.1',242,6,0,2,'01:00:00','00:15:00',60,253,4,'y'),
(71,'60H91.4','60 m Hürden 91.4',243,6,0,2,'01:00:00','00:15:00',60,254,4,'y'),
(72,'60H84.0','60 m Hürden 84.0',244,6,0,2,'01:00:00','00:15:00',60,255,4,'y'),
(73,'60H76.2','60 m Hürden 76.2  U18 W',245,6,0,2,'01:00:00','00:15:00',60,256,4,'y'),
(74,'80H76.2','80 m Hürden 76.2',264,6,0,1,'01:00:00','00:15:00',80,258,4,'y'),
(75,'100H84.0','100 m Hürden 84.0',266,6,0,1,'01:00:00','00:20:00',100,261,4,'y'),
(76,'100H76.2','100 m Hürden 76.2',267,6,0,1,'01:00:00','00:20:00',100,259,4,'y'),
(77,'110H106.7','110 m Hürden 106.7',268,6,0,1,'01:00:00','00:20:00',110,271,4,'y'),
(78,'110H99.1','110 m Hürden 99.1',269,6,0,1,'01:00:00','00:15:00',110,269,4,'y'),
(79,'110H91.4','110 m Hürden 91.4',270,6,0,1,'01:00:00','00:15:00',110,268,4,'y'),
(80,'200H','200 m Hürden',280,6,0,1,'01:00:00','00:15:00',200,280,4,'y'),
(81,'300H84.0','300 m Hürden 84.0',290,6,0,2,'01:00:00','00:15:00',300,290,4,'y'),
(82,'300H76.2','300 m Hürden 76.2',291,6,0,2,'01:00:00','00:15:00',300,291,4,'y'),
(83,'400H91.4','400 m Hürden 91.4',298,6,0,2,'01:00:00','00:20:00',400,301,4,'y'),
(84,'400H76.2','400 m Hürden 76.2',301,6,0,2,'01:00:00','00:20:00',400,298,4,'y'),
(85,'1500ST','1500 m Steeple',302,6,0,7,'01:00:00','00:15:00',1500,209,6,'y'),
(86,'2000ST','2000 m Steeple',303,6,0,7,'01:00:00','00:15:00',2000,210,6,'y'),
(87,'3000ST','3000 m Steeple',304,6,0,7,'01:00:00','00:15:00',3000,220,6,'y'),
(88,'5XFREI','5x frei',395,6,5,3,'01:00:00','00:15:00',5,497,1,'y'),
(89,'5X80','5x80 m',396,6,5,3,'01:00:00','00:15:00',400,498,1,'y'),
(90,'6XFREI','6x frei',394,6,6,3,'01:00:00','00:15:00',6,499,1,'y'),
(91,'4X100','4x100 m',397,6,4,3,'01:00:00','00:15:00',400,560,1,'y'),
(92,'4X200','4x200 m',398,6,4,3,'01:00:00','00:15:00',800,570,1,'y'),
(93,'4X400','4x400 m',399,6,4,3,'01:00:00','00:15:00',1600,580,1,'y'),
(94,'3X800','3x800 m',400,6,3,3,'01:00:00','00:15:00',2400,589,1,'y'),
(95,'4X800','4x800 m',401,6,4,3,'01:00:00','00:15:00',3200,590,1,'y'),
(96,'3X1000','3x1000 m',402,6,3,3,'01:00:00','00:15:00',3000,595,1,'y'),
(97,'4X1500','4x1500 m',403,6,4,3,'01:00:00','00:15:00',6000,600,1,'y'),
(98,'OLYMPISCHE','Olympische',404,12,4,3,'01:00:00','00:15:00',0,601,1,'y'),
(99,'AMERICAINE','Américaine',405,12,3,3,'01:00:00','00:15:00',0,602,1,'y'),
(100,'HOCH','Hoch',310,15,0,6,'01:00:00','00:30:00',0,310,1,'y'),
(101,'STAB','Stab',320,15,0,6,'01:00:00','00:30:00',0,320,1,'y'),
(102,'WEIT','Weit',330,15,0,4,'01:00:00','00:30:00',0,330,1,'y'),
(103,'DREI','Drei',340,15,0,4,'01:00:00','00:30:00',0,340,1,'y'),
(104,'KUGEL7.26','Kugel 7.26 kg',347,15,0,8,'01:00:00','00:30:00',0,351,1,'y'),
(105,'KUGEL6.00','Kugel 6.00 kg',348,15,0,8,'01:00:00','00:20:00',0,348,1,'y'),
(106,'KUGEL5.00','Kugel 5.00 kg',349,15,0,8,'01:00:00','00:30:00',0,347,1,'y'),
(107,'KUGEL4.00','Kugel 4.00 kg',350,15,0,8,'01:00:00','00:30:00',0,349,1,'y'),
(108,'KUGEL3.00','Kugel 3.00 kg',352,15,0,8,'01:00:00','00:20:00',0,352,1,'y'),
(109,'KUGEL2.50','Kugel 2.50 kg',353,15,0,8,'01:00:00','00:20:00',0,353,1,'y'),
(110,'DISKUS2.00','Diskus 2.00 kg',356,15,0,8,'01:00:00','00:20:00',0,361,1,'y'),
(111,'DISKUS1.75','Diskus 1.75 kg',357,15,0,8,'01:00:00','00:20:00',0,359,1,'y'),
(112,'DISKUS1.50','Diskus 1.50 kg',358,15,0,8,'01:00:00','00:20:00',0,358,1,'y'),
(113,'DISKUS1.00','Diskus 1.00 kg',359,15,0,8,'01:00:00','00:20:00',0,357,1,'y'),
(114,'DISKUS0.75','Diskus 0.75 kg',361,15,0,8,'01:00:00','00:20:00',0,356,1,'y'),
(115,'HAMMER7.26','Hammer 7.26 kg',375,15,0,8,'01:00:00','00:30:00',0,381,1,'y'),
(116,'HAMMER6.00','Hammer 6.00 kg',376,15,0,8,'01:00:00','00:20:00',0,378,1,'y'),
(117,'HAMMER5.00','Hammer 5.00 kg',377,15,0,8,'01:00:00','00:20:00',0,377,1,'y'),
(118,'HAMMER4.00','Hammer 4.00 kg',378,15,0,8,'01:00:00','00:30:00',0,376,1,'y'),
(119,'HAMMER3.00','Hammer 3.00 kg',381,15,0,8,'01:00:00','00:20:00',0,375,1,'y'),
(120,'SPEER800','Speer 800 gr',387,15,0,8,'01:00:00','00:20:00',0,391,1,'y'),
(121,'SPEER700','Speer 700 gr',388,15,0,8,'01:00:00','00:20:00',0,389,1,'y'),
(122,'SPEER600','Speer 600 gr',389,15,0,8,'01:00:00','00:20:00',0,388,1,'y'),
(123,'SPEER400','Speer 400 gr',391,15,0,8,'01:00:00','00:20:00',0,387,1,'y'),
(124,'BALL200','Ball 200 g',392,15,0,8,'01:00:00','00:20:00',0,386,1,'y'),
(125,'5KAMPF_W_U20W_I','Fünfkampf W / U20 W Indoor',408,6,0,9,'01:00:00','00:15:00',5,394,1,'y'),
(126,'5KAMPF_U18W_I','Fünfkampf U18 W Indoor',409,6,0,9,'01:00:00','00:15:00',5,395,1,'y'),
(127,'7KAMPF_M_I','Siebenkampf M Indoor',413,6,0,9,'01:00:00','00:15:00',7,396,1,'y'),
(128,'7KAMPF_U20M_I','Siebenkampf U20 M Indoor',414,6,0,9,'01:00:00','00:15:00',7,397,1,'y'),
(129,'7KAMPF_U18M_I','Siebenkampf U18 M Indoor',415,6,0,9,'01:00:00','00:15:00',7,398,1,'y'),
(130,'10KAMPF_M','Zehnkampf M',434,6,0,9,'01:00:00','00:15:00',10,410,1,'y'),
(131,'10KAMPF_U20M','Zehnkampf  U20 M',435,6,0,9,'01:00:00','00:15:00',10,411,1,'y'),
(132,'10KAMPF_U18M','Zehnkampf   U18 M',436,6,0,9,'01:00:00','00:15:00',10,412,1,'y'),
(133,'10KAMPF_W','Zehnkampf W',437,6,0,9,'01:00:00','00:15:00',10,413,1,'y'),
(134,'7KAMPF','Siebenkampf',430,6,0,9,'01:00:00','00:15:00',7,400,1,'y'),
(135,'7KAMPF_U18W','Siebenkampf   U18 W',431,6,0,9,'01:00:00','00:15:00',7,401,1,'y'),
(136,'6KAMPF_U16M','Sechskampf  U16 M',429,6,0,9,'01:00:00','00:15:00',6,402,1,'y'),
(137,'5KAMPF_U16W','Fünfkampf U16 W',426,6,0,9,'01:00:00','00:15:00',5,399,1,'y'),
(138,'UKC','UBS Kids Cup',439,6,0,9,'01:00:00','00:15:00',3,408,1,'y'),
(139,'MILEWALK','Mile walk',450,50,0,7,'01:00:00','00:15:00',1609,415,5,'y'),
(140,'3000WALK','3000 m walk',452,50,0,7,'01:00:00','00:15:00',3000,420,5,'y'),
(141,'5000WALK','5000 m walk',453,50,0,7,'01:00:00','00:15:00',5000,430,5,'y'),
(142,'10000WALK','10000 m walk',454,50,0,7,'01:00:00','00:15:00',10000,440,5,'y'),
(143,'20000WALK','20000 m walk',455,50,0,7,'01:00:00','00:15:00',20000,450,5,'y'),
(144,'50000WALK','50000 m walk',456,50,0,7,'01:00:00','00:15:00',50000,460,5,'y'),
(145,'3KMWALK','3 km walk',470,50,0,7,'01:00:00','00:15:00',3000,470,5,'y'),
(146,'5KMWALK','5 km walk',480,50,0,7,'01:00:00','00:15:00',5000,480,5,'y'),
(147,'10KMWALK','10 km walk',490,50,0,7,'01:00:00','00:15:00',10000,490,5,'y'),
(150,'20KMWALK','20 km walk',500,50,0,7,'01:00:00','00:15:00',20000,500,5,'y'),
(152,'35KMWALK','35 km walk',530,50,0,7,'01:00:00','00:15:00',35000,530,5,'y'),
(154,'50KMWALK','50 km walk',550,50,0,7,'01:00:00','00:15:00',50000,550,5,'y'),
(156,'10KM','10 km',440,50,0,7,'01:00:00','00:15:00',10000,491,1,'y'),
(157,'15KM','15 km',441,50,0,7,'01:00:00','00:15:00',15000,494,1,'y'),
(158,'20KM','20 km',442,50,0,7,'01:00:00','00:15:00',20000,501,1,'y'),
(159,'25KM','25 km',443,50,0,7,'01:00:00','00:15:00',25000,505,1,'y'),
(160,'30KM','30 km',444,50,0,7,'01:00:00','00:15:00',30000,511,1,'y'),
(162,'1HWALK','1 h  walk',555,50,0,7,'01:00:00','00:15:00',1,555,5,'y'),
(163,'2HWALK','2 h  walk',556,50,0,7,'01:00:00','00:15:00',2,556,5,'y'),
(164,'100KMWALK','100 km walk',457,50,0,7,'01:00:00','00:15:00',100000,559,5,'y'),
(165,'BALL80','Ball 80 g',393,15,0,8,'01:00:00','00:20:00',0,385,1,'y'),
(166,'300H91.4','300 m Hürden 91.4',289,6,0,2,'01:00:00','00:15:00',300,289,4,'y'),
(167,'...KAMPF','...kampf',799,6,0,9,'01:00:00','00:15:00',4,799,1,'y'),
(168,'75','75 m',31,6,0,1,'01:00:00','00:15:00',75,31,1,'y'),
(169,'50H68.6','50 m Hürden 68.6',240,6,0,2,'01:00:00','00:15:00',50,237,1,'y'),
(170,'60H68.6','60 m Hürden 68.6',252,6,0,2,'01:00:00','00:15:00',60,257,1,'y'),
(171,'80H84.0','80 m Hürden 84.0',263,6,0,1,'01:00:00','00:15:00',80,260,1,'y'),
(172,'80H68.6','80 m Hürden 68.6',265,6,0,1,'01:00:00','00:15:00',80,262,1,'y'),
(173,'300H68.6','300 m Hürden 68.6',292,6,0,2,'01:00:00','00:15:00',300,295,1,'y'),
(174,'SPEER500','Speer 500 gr',390,15,0,8,'01:00:00','00:20:00',0,390,1,'y'),
(175,'5KAMPF_M','Fünfkampf M',418,6,0,9,'01:00:00','00:15:00',5,392,1,'y'),
(176,'5KAMPF_U20M','Fünfkampf U20 M',420,6,0,9,'01:00:00','00:15:00',5,393,1,'y'),
(177,'5KAMPF_U18M','Fünfkampf U18 M',421,6,0,9,'01:00:00','00:15:00',5,405,1,'y'),
(178,'5KAMPF_W','Fünfkampf W',423,6,0,9,'01:00:00','00:15:00',5,416,1,'y'),
(180,'5KAMPF_U18W','Fünfkampf U18 W',425,6,0,9,'01:00:00','00:15:00',5,418,1,'y'),
(181,'10KAMPF_MASTER','Zehnkampf Master',438,6,0,9,'01:00:00','00:15:00',10,414,1,'y'),
(182,'2000WALK','2000 m walk',451,50,0,7,'01:00:00','00:15:00',2000,419,1,'y'),
(183,'...LAUF','...lauf',796,6,0,9,'01:00:00','00:15:00',4,796,1,'y'),
(184,'...SPRUNG','...sprung',797,6,0,9,'01:00:00','00:20:00',4,797,1,'y'),
(185,'...WURF','...wurf',798,6,0,9,'01:00:00','00:20:00',4,798,1,'y'),
(186,'WEIT Z','Weit (Zone)',331,15,0,5,'01:00:00','00:20:00',0,331,1,'y'),
(187,'50H76.2U16','50 m Hürden 76.2  U16W/U14M',237,6,0,2,'01:00:00','00:15:00',50,246,4,'y'),
(188,'50H76.2U14','50 m Hürden 76.2  U14 W (In)',238,6,0,2,'01:00:00','00:15:00',50,247,4,'y'),
(189,'50H60-76.2','50 m Hürden 60-76.2 U12 (In)',239,6,0,2,'01:00:00','00:15:00',50,248,4,'y'),
(190,'60H76.2U16','60 m Hürden 76.2  U16W/U14M',247,6,0,2,'01:00:00','00:15:00',60,275,4,'y'),
(191,'60H76.2U14I','60 m Hürden 76.2  U14W (In)',248,6,0,2,'01:00:00','00:15:00',60,276,4,'y'),
(192,'60H60-76.2','60 m Hürden 60-76.2  U12 (In)',250,6,0,2,'01:00:00','00:15:00',60,277,4,'y'),
(193,'60H76.2U14O','60 m Hürden 76.2  U14 W (Out)',251,6,0,2,'01:00:00','00:15:00',60,278,4,'y'),
(194,'60H60-76.2U12','60 m Hürden 60-76.2 U12',254,6,0,2,'01:00:00','00:15:00',60,279,4,'y'),
(195,'5KAMPF_U16M','Fünfkampf U16 M',422,6,0,9,'01:00:00','00:15:00',5,406,1,'y'),
(196,'5KAMPF_U18M_I','Fünfkampf U18 M Indoor',406,6,0,9,'01:00:00','00:15:00',5,424,1,'y'),
(197,'5KAMPF_U23M','Fünfkampf U23 M',419,6,0,9,'01:00:00','00:15:00',5,407,1,'y'),
(198,'5KAMPF_U20W','Fünfkampf U20 W',424,6,0,9,'01:00:00','00:15:00',5,417,1,'y'),
(199,'5KAMPF_U16M_I','Fünfkampf U16 M Indoor',407,6,0,9,'01:00:00','00:15:00',5,425,1,'y'),
(200,'5KAMPF_U16W_I','Fünfkampf U16 W Indoor',410,6,0,9,'01:00:00','00:15:00',5,426,1,'y'),
(201,'8KAMPF_U18M','Achtkampf U18 M',433,6,0,9,'01:00:00','00:15:00',5,427,1,'y'),
(202,'Schwedenstaffel','Schwedenstaffel',404,12,4,3,'01:00:00','00:15:00',0,603,1,'y'),
(203,'Stab-Weit','Stab - Weit',325,15,0,5,'01:00:00','00:20:00',0,332,1,'y'),
(204,'Drehwurf','Drehwerfen',365,15,0,8,'01:00:00','00:20:00',0,354,1,'y'),
(205,'400H84.0','400 m Hürden 84.0',800,8,0,2,'01:00:00','00:20:00',400,820,4,'y');

/*Table structure for table `disziplin_fr` */

DROP TABLE IF EXISTS `disziplin_fr`;

CREATE TABLE `disziplin_fr` (
  `xDisziplin` int(11) NOT NULL AUTO_INCREMENT,
  `Kurzname` varchar(15) NOT NULL DEFAULT '',
  `Name` varchar(40) NOT NULL DEFAULT '',
  `Anzeige` int(11) NOT NULL DEFAULT '1',
  `Seriegroesse` int(4) NOT NULL DEFAULT '0',
  `Staffellaeufer` int(11) DEFAULT NULL,
  `Typ` int(11) NOT NULL DEFAULT '0',
  `Appellzeit` time NOT NULL DEFAULT '00:00:00',
  `Stellzeit` time NOT NULL DEFAULT '00:00:00',
  `Strecke` float NOT NULL DEFAULT '0',
  `Code` int(11) NOT NULL DEFAULT '0',
  `xOMEGA_Typ` int(11) NOT NULL DEFAULT '0',
  `aktiv` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`xDisziplin`),
  UNIQUE KEY `Kurzname` (`Kurzname`),
  KEY `Anzeige` (`Anzeige`),
  KEY `Staffel` (`Staffellaeufer`),
  KEY `Code` (`Code`)
) ENGINE=MyISAM AUTO_INCREMENT=206 DEFAULT CHARSET=utf8;

/*Data for the table `disziplin_fr` */

insert  into `disziplin_fr`(`xDisziplin`,`Kurzname`,`Name`,`Anzeige`,`Seriegroesse`,`Staffellaeufer`,`Typ`,`Appellzeit`,`Stellzeit`,`Strecke`,`Code`,`xOMEGA_Typ`,`aktiv`) values 
(38,'50','50 m',10,6,0,2,'01:00:00','00:15:00',50,10,1,'y'),
(39,'55','55 m',20,6,0,2,'01:00:00','00:15:00',55,20,1,'y'),
(40,'60','60 m',30,6,0,2,'01:00:00','00:15:00',60,30,1,'y'),
(41,'80','80 m',35,6,0,1,'01:00:00','00:15:00',80,35,1,'y'),
(42,'100','100 m',40,8,0,1,'01:00:00','00:20:00',100,40,1,'y'),
(43,'150','150 m',48,6,0,1,'01:00:00','00:15:00',150,48,1,'y'),
(44,'200','200 m',50,6,0,1,'01:00:00','00:15:00',200,50,1,'y'),
(45,'300','300 m',60,6,0,2,'01:00:00','00:15:00',300,60,1,'y'),
(46,'400','400 m',70,6,0,2,'01:00:00','00:20:00',400,70,1,'y'),
(47,'600','600 m',80,12,0,7,'01:00:00','00:15:00',600,80,1,'y'),
(48,'800','800 m',90,6,0,7,'01:00:00','00:20:00',800,90,1,'y'),
(49,'1000','1000 m',100,15,0,7,'01:00:00','00:15:00',1000,100,1,'y'),
(50,'1500','1500 m',110,13,0,7,'01:00:00','00:20:00',1500,110,1,'y'),
(51,'1MILE','1 mile',120,15,0,7,'01:00:00','00:15:00',1609,120,1,'y'),
(52,'2000','2000 m',130,15,0,7,'01:00:00','00:15:00',2000,130,1,'y'),
(53,'3000','3000 m',140,15,0,7,'01:00:00','00:15:00',3000,140,1,'y'),
(54,'5000','5000 m',160,15,0,7,'01:00:00','00:15:00',5000,160,1,'y'),
(55,'10000','10 000 m',170,20,0,7,'01:00:00','00:15:00',10000,170,1,'y'),
(56,'20000','20 000 m',180,20,0,7,'01:00:00','00:15:00',20000,180,1,'y'),
(57,'1HEURE','1 heure',171,620,0,7,'01:00:00','00:15:00',1,182,1,'y'),
(58,'25000','25 000 m',181,20,0,7,'01:00:00','00:15:00',25000,181,1,'y'),
(59,'30000','30 000 m',182,20,0,7,'01:00:00','00:15:00',30000,195,1,'y'),
(61,'DEMIMARATHON','Demimarathon',183,20,0,7,'01:00:00','00:15:00',0,190,1,'y'),
(62,'MARATHON','Marathon',184,20,0,7,'01:00:00','00:15:00',0,200,1,'y'),
(64,'50H106.7','50 m haies 106.7',232,6,0,1,'01:00:00','00:15:00',50,232,4,'y'),
(65,'50H99.1','50 m haies 99.1',233,6,0,2,'01:00:00','00:15:00',50,233,4,'y'),
(66,'50H91.4','50 m haies 91.4',234,6,0,2,'01:00:00','00:15:00',50,234,4,'y'),
(67,'50H84.0','50 m haies 84.0',235,6,0,2,'01:00:00','00:15:00',50,235,4,'y'),
(68,'50H76.2','50 m haies 76.2  U18 W',236,6,0,2,'01:00:00','00:15:00',50,236,4,'y'),
(69,'60H106.7','60 m haies 106.7',241,6,0,2,'01:00:00','00:15:00',60,252,4,'y'),
(70,'60H99.1','60 m haies 99.1',242,6,0,2,'01:00:00','00:15:00',60,253,4,'y'),
(71,'60H91.4','60 m haies 91.4',243,6,0,2,'01:00:00','00:15:00',60,254,4,'y'),
(72,'60H84.0','60 m haies 84.0',244,6,0,2,'01:00:00','00:15:00',60,255,4,'y'),
(73,'60H76.2','60 m haies 76.2  U18 W',245,6,0,2,'01:00:00','00:15:00',60,256,4,'y'),
(74,'80H76.2','80 m haies 76.2',264,6,0,1,'01:00:00','00:15:00',80,258,4,'y'),
(75,'100H84.0','100 m haies 84.0',266,6,0,1,'01:00:00','00:20:00',100,261,4,'y'),
(76,'100H76.2','100 m haies 76.2',267,6,0,1,'01:00:00','00:20:00',100,259,4,'y'),
(77,'110H106.7','110 m haies 106.7',268,6,0,1,'01:00:00','00:20:00',110,271,4,'y'),
(78,'110H99.1','110 m haies 99.1',269,6,0,1,'01:00:00','00:15:00',110,269,4,'y'),
(79,'110H91.4','110 m haies 91.4',270,6,0,1,'01:00:00','00:15:00',110,268,4,'y'),
(80,'200H','200 m haies',280,6,0,1,'01:00:00','00:15:00',200,280,4,'y'),
(81,'300H84.0','300 m haies 84.0',290,6,0,2,'01:00:00','00:15:00',300,290,4,'y'),
(82,'300H76.2','300 m haies 76.2',291,6,0,2,'01:00:00','00:15:00',300,291,4,'y'),
(83,'400H91.4','400 m haies 91.4',298,6,0,2,'01:00:00','00:20:00',400,301,4,'y'),
(84,'400H76.2','400 m haies 76.2',301,6,0,2,'01:00:00','00:20:00',400,298,4,'y'),
(85,'1500ST','1500 m Steeple',302,6,0,7,'01:00:00','00:15:00',1500,209,6,'y'),
(86,'2000ST','2000 m Steeple',303,6,0,7,'01:00:00','00:15:00',2000,210,6,'y'),
(87,'3000ST','3000 m Steeple',304,6,0,7,'01:00:00','00:15:00',3000,220,6,'y'),
(88,'5XLIBRE','5x libre',395,6,5,3,'01:00:00','00:15:00',5,497,1,'y'),
(89,'5X80','5x80 m',396,6,5,3,'01:00:00','00:15:00',400,498,1,'y'),
(90,'6XLIBRE','6x libre',394,6,6,3,'01:00:00','00:15:00',6,499,1,'y'),
(91,'4X100','4x100 m',397,6,4,3,'01:00:00','00:15:00',400,560,1,'y'),
(92,'4X200','4x200 m',398,6,4,3,'01:00:00','00:15:00',800,570,1,'y'),
(93,'4X400','4x400 m',399,6,4,3,'01:00:00','00:15:00',1600,580,1,'y'),
(94,'3X800','3x800 m',400,6,3,3,'01:00:00','00:15:00',2400,589,1,'y'),
(95,'4X800','4x800 m',401,6,4,3,'01:00:00','00:15:00',3200,590,1,'y'),
(96,'3X1000','3x1000 m',402,6,3,3,'01:00:00','00:15:00',3000,595,1,'y'),
(97,'4X1500','4x1500 m',403,6,4,3,'01:00:00','00:15:00',6000,600,1,'y'),
(98,'OLYMPISCHE','Olympische',404,12,4,3,'01:00:00','00:15:00',0,601,1,'y'),
(99,'AMERICAINE','Américaine',405,12,3,3,'01:00:00','00:15:00',0,602,1,'y'),
(100,'HAUTEUR','Hauteur',310,15,0,6,'01:00:00','00:30:00',0,310,1,'y'),
(101,'PERCHE','Perche',320,15,0,6,'01:00:00','00:30:00',0,320,1,'y'),
(102,'LONGUEUR','Longueur',330,15,0,4,'01:00:00','00:30:00',0,330,1,'y'),
(103,'TRIPLE','Triple',340,15,0,4,'01:00:00','00:30:00',0,340,1,'y'),
(104,'POIDS7.26','Poids 7.26 kg',347,15,0,8,'01:00:00','00:30:00',0,351,1,'y'),
(105,'POIDS6.00','Poids 6.00 kg',348,15,0,8,'01:00:00','00:20:00',0,348,1,'y'),
(106,'POIDS5.00','Poids 5.00 kg',349,15,0,8,'01:00:00','00:30:00',0,347,1,'y'),
(107,'POIDS4.00','Poids 4.00 kg',350,15,0,8,'01:00:00','00:30:00',0,349,1,'y'),
(108,'POIDS3.00','Poids 3.00 kg',352,15,0,8,'01:00:00','00:20:00',0,352,1,'y'),
(109,'POIDS2.50','Poids 2.50 kg',353,15,0,8,'01:00:00','00:20:00',0,353,1,'y'),
(110,'DISQUE2.00','Disque 2.00 kg',356,615,0,8,'01:00:00','00:20:00',0,361,1,'y'),
(111,'DISQUE1.75','Disque 1.75 kg',357,15,0,8,'01:00:00','00:20:00',0,359,1,'y'),
(112,'DISQUE1.50','Disque 1.50 kg',358,15,0,8,'01:00:00','00:20:00',0,358,1,'y'),
(113,'DISQUE1.00','Disque 1.00 kg',359,15,0,8,'01:00:00','00:20:00',0,357,1,'y'),
(114,'DISQUE0.75','Disque 0.75 kg',361,15,0,8,'01:00:00','00:20:00',0,356,1,'y'),
(115,' MARTEAU7.26','Marteau 7.26 kg',375,15,0,8,'01:00:00','00:30:00',0,381,1,'y'),
(116,' MARTEAU6.00','Marteau 6.00 kg',376,15,0,8,'01:00:00','00:20:00',0,378,1,'y'),
(117,' MARTEAU5.00','Marteau 5.00 kg',377,15,0,8,'01:00:00','00:20:00',0,377,1,'y'),
(118,' MARTEAU4.00','Marteau 4.00 kg',378,15,0,8,'01:00:00','00:30:00',0,376,1,'y'),
(119,' MARTEAU3.00','Marteau 3.00 kg',381,15,0,8,'01:00:00','00:20:00',0,375,1,'y'),
(120,'JAVELOT800','Javelot 800 gr',387,15,0,8,'01:00:00','00:20:00',0,391,1,'y'),
(121,'JAVELOT700','Javelot 700 gr',388,15,0,8,'01:00:00','00:20:00',0,389,1,'y'),
(122,'JAVELOT600','Javelot 600 gr',389,15,0,8,'01:00:00','00:20:00',0,388,1,'y'),
(123,'JAVELOT400','Javelot 400 gr',391,15,0,8,'01:00:00','00:20:00',0,387,1,'y'),
(124,'BALLE200','Balle 200 gr',392,15,0,8,'01:00:00','00:20:00',0,386,1,'y'),
(125,'5ATHLON_W_U20WI','Pentathlon W / U20 W Indoor',408,6,0,9,'01:00:00','00:15:00',5,394,1,'y'),
(126,'5ATHLON_U18W_I','Pentathlon U18 W Indoor',409,6,0,9,'01:00:00','00:15:00',5,395,1,'y'),
(127,'7ATHLON_M_I','Heptathlon M Indoor',413,6,0,9,'01:00:00','00:15:00',7,396,1,'y'),
(128,'7ATHLON_U20M_I','Heptathlon U20 M Indoor',414,6,0,9,'01:00:00','00:15:00',7,397,1,'y'),
(129,'7ATHLON_U18M_I','Heptathlon U18 M Indoor',415,6,0,9,'01:00:00','00:15:00',7,398,1,'y'),
(130,'10ATHLON_M','Décathlon M',434,6,0,9,'01:00:00','00:15:00',10,410,1,'y'),
(131,'10ATHLON_U20M','Décathlon U20 M',435,6,0,9,'01:00:00','00:15:00',10,411,1,'y'),
(132,'10ATHLON_U18M','Décathlon U18 M',436,6,0,9,'01:00:00','00:15:00',10,412,1,'y'),
(133,'10ATHLON_W','Décathlon W',437,6,0,9,'01:00:00','00:15:00',10,413,1,'y'),
(134,'7ATHLON','Heptathlon',430,6,0,9,'01:00:00','00:15:00',7,400,1,'y'),
(135,'7ATHLON_U18W','Heptathlon U18 W',431,6,0,9,'01:00:00','00:15:00',7,401,1,'y'),
(136,'6ATHLON_U16M','Hexathlon U16 M',429,6,0,9,'01:00:00','00:15:00',6,402,1,'y'),
(137,'5ATHLON_U16W','Pentathlon U16 W',426,6,0,9,'01:00:00','00:15:00',5,399,1,'y'),
(138,'UKC','UBS Kids Cup',439,6,0,9,'01:00:00','00:15:00',3,408,1,'y'),
(139,'MILEWALK','Mile walk',450,50,0,7,'01:00:00','00:15:00',1609,415,5,'y'),
(140,'3000WALK','3000 m walk',452,50,0,7,'01:00:00','00:15:00',3000,420,5,'y'),
(141,'5000WALK','5000 m walk',453,50,0,7,'01:00:00','00:15:00',5000,430,5,'y'),
(142,'10000WALK','10000 m walk',454,50,0,7,'01:00:00','00:15:00',10000,440,5,'y'),
(143,'20000WALK','20000 m walk',455,50,0,7,'01:00:00','00:15:00',20000,450,5,'y'),
(144,'50000WALK','50000 m walk',456,50,0,7,'01:00:00','00:15:00',50000,460,5,'y'),
(145,'3KMWALK','3 km walk',470,50,0,7,'01:00:00','00:15:00',3000,470,5,'y'),
(146,'5KMWALK','5 km walk',480,50,0,7,'01:00:00','00:15:00',5000,480,5,'y'),
(147,'10KMWALK','10 km walk',490,50,0,7,'01:00:00','00:15:00',10000,490,5,'y'),
(150,'20KMWALK','20 km walk',500,50,0,7,'01:00:00','00:15:00',20000,500,5,'y'),
(152,'35KMWALK','35 km walk',530,50,0,7,'01:00:00','00:15:00',35000,530,5,'y'),
(154,'50KMWALK','50 km walk',550,50,0,7,'01:00:00','00:15:00',50000,550,5,'y'),
(156,'10KM','10 km',440,650,0,7,'01:00:00','00:15:00',10000,491,1,'y'),
(157,'15KM','15 km',441,50,0,7,'01:00:00','00:15:00',15000,494,1,'y'),
(158,'20KM','20 km',442,50,0,7,'01:00:00','00:15:00',20000,501,1,'y'),
(159,'25KM','25 km',443,50,0,7,'01:00:00','00:15:00',25000,505,1,'y'),
(160,'30KM','30 km',444,50,0,7,'01:00:00','00:15:00',30000,511,1,'y'),
(162,'1HWALK','1 h  walk',555,50,0,7,'01:00:00','00:15:00',1,555,5,'y'),
(163,'2HWALK','2 h  walk',556,50,0,7,'01:00:00','00:15:00',2,556,5,'y'),
(164,'100KMWALK','100 km walk',457,50,0,7,'01:00:00','00:15:00',100000,559,5,'y'),
(165,'BALLE80','Balle 80 gr',393,15,0,8,'01:00:00','00:20:00',0,385,1,'y'),
(166,'300H91.4','300 m haies 91.4',289,6,0,2,'01:00:00','00:15:00',300,289,4,'y'),
(167,'...ATHLON','...athlon',799,6,0,9,'01:00:00','00:15:00',4,799,1,'y'),
(168,'75','75 m',31,6,0,1,'01:00:00','00:15:00',75,31,1,'y'),
(169,'50H68.6','50 m haies 68.6',240,6,0,2,'01:00:00','00:15:00',50,237,1,'y'),
(170,'60H68.6','60 m haies 68.6',252,6,0,2,'01:00:00','00:15:00',60,257,1,'y'),
(171,'80H84.0','80 m haies 84.0',263,6,0,1,'01:00:00','00:15:00',80,260,1,'y'),
(172,'80H68.6','80 m haies 68.6',265,6,0,1,'01:00:00','00:15:00',80,262,1,'y'),
(173,'300H68.6','300 m haies 68.6',292,6,0,2,'01:00:00','00:15:00',300,295,1,'y'),
(174,'JAVELOT500','Javelot 500 gr',390,15,0,8,'01:00:00','00:20:00',0,390,1,'y'),
(175,'5ATHLON_M','Pentathlon M',418,6,0,9,'01:00:00','00:15:00',5,392,1,'y'),
(176,'5ATHLON_U20M','Pentathlon U20 M',420,6,0,9,'01:00:00','00:15:00',5,393,1,'y'),
(177,'5ATHLON_U18M','Pentathlon U18 M',421,6,0,9,'01:00:00','00:15:00',5,405,1,'y'),
(178,'5ATHLON_F','Pentathlon F',423,6,0,9,'01:00:00','00:15:00',5,416,1,'y'),
(180,'5ATHLON_U18F','Pentathlon U18 F',425,6,0,9,'01:00:00','00:15:00',5,418,1,'y'),
(181,'10ATHLON_MASTER','Décathlon Master',438,6,0,9,'01:00:00','00:15:00',10,414,1,'y'),
(182,'2000WALK','2000 m walk',451,50,0,7,'01:00:00','00:15:00',2000,419,1,'y'),
(183,'...COURS','...cours',796,6,0,9,'01:00:00','00:15:00',4,796,1,'y'),
(184,'...LONGUEUR','...longueur',797,6,0,9,'01:00:00','00:20:00',4,797,1,'y'),
(185,'...LANCER','...lancer',798,6,0,9,'01:00:00','00:20:00',4,798,1,'y'),
(186,'LONGUEUR Z','Longueur (zone)',331,15,0,5,'01:00:00','00:20:00',0,331,1,'y'),
(187,'50H76.2U16','50 m haies 76.2  U16W/U14M',237,6,0,2,'01:00:00','00:15:00',50,246,4,'y'),
(188,'50H76.2U14','50 m haies 76.2  U14 W (In)',238,6,0,2,'01:00:00','00:15:00',50,247,4,'y'),
(189,'50H60-76.2','50 m haies 60-76.2 U12 (In)',239,6,0,2,'01:00:00','00:15:00',50,248,4,'y'),
(190,'60H76.2U16','60 m haies 76.2  U16W/U14M',247,6,0,2,'01:00:00','00:15:00',60,275,4,'y'),
(191,'60H76.2U14I','60 m haies 76.2  U14W (In)',248,6,0,2,'01:00:00','00:15:00',60,276,4,'y'),
(192,'60H60-76.2','60 m haies 60-76.2  U12 (In)',250,6,0,2,'01:00:00','00:15:00',60,277,4,'y'),
(193,'60H76.2U14O','60 m haies 76.2  U14 W (Out)',251,6,0,2,'01:00:00','00:15:00',60,278,4,'y'),
(194,'60H60-76.2U12','60 m haies 60-76.2 U12',254,6,0,2,'01:00:00','00:15:00',60,279,4,'y'),
(195,'5ATHLON_U16M','Athlon U16 M',422,6,0,9,'01:00:00','00:15:00',5,406,1,'y'),
(196,'5ATHLON_U18M_I','Pentathlon U18 M Indoor',406,6,0,9,'01:00:00','00:15:00',5,424,1,'y'),
(197,'5ATHLON_U23M','Pentathlon U23 M',419,6,0,9,'01:00:00','00:15:00',5,407,1,'y'),
(198,'5ATHLON_U20W','Pentathlon U20 W',424,6,0,9,'01:00:00','00:15:00',5,417,1,'y'),
(199,'5ATHLON_U16M_I','Pentathlon U16 M Indoor',407,6,0,9,'01:00:00','00:15:00',5,425,1,'y'),
(200,'5ATHLON_U16W_I','Pentathlon U16 w Indoor',410,6,0,9,'01:00:00','00:15:00',5,426,1,'y'),
(201,'8ATHLON_U18M','Octathlon U18 M',433,6,0,9,'01:00:00','00:15:00',5,427,1,'y'),
(202,'Relais suédois','Relais suédois',404,12,4,3,'01:00:00','00:15:00',0,603,1,'y'),
(203,'perche-long','perche en longueur',325,15,0,5,'01:00:00','00:20:00',0,332,1,'y'),
(204,'lancer-rotation','lancer en rotation',365,15,0,8,'01:00:00','00:20:00',0,354,1,'y'),
(205,'400H84.0','400 m haies 84.0',800,8,0,2,'01:00:00','00:20:00',0,0,0,'y');

/*Table structure for table `disziplin_it` */

DROP TABLE IF EXISTS `disziplin_it`;

CREATE TABLE `disziplin_it` (
  `xDisziplin` int(11) NOT NULL AUTO_INCREMENT,
  `Kurzname` varchar(15) NOT NULL DEFAULT '',
  `Name` varchar(40) NOT NULL DEFAULT '',
  `Anzeige` int(11) NOT NULL DEFAULT '1',
  `Seriegroesse` int(4) NOT NULL DEFAULT '0',
  `Staffellaeufer` int(11) DEFAULT NULL,
  `Typ` int(11) NOT NULL DEFAULT '0',
  `Appellzeit` time NOT NULL DEFAULT '00:00:00',
  `Stellzeit` time NOT NULL DEFAULT '00:00:00',
  `Strecke` float NOT NULL DEFAULT '0',
  `Code` int(11) NOT NULL DEFAULT '0',
  `xOMEGA_Typ` int(11) NOT NULL DEFAULT '0',
  `aktiv` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`xDisziplin`),
  UNIQUE KEY `Kurzname` (`Kurzname`),
  KEY `Anzeige` (`Anzeige`),
  KEY `Staffel` (`Staffellaeufer`),
  KEY `Code` (`Code`)
) ENGINE=MyISAM AUTO_INCREMENT=206 DEFAULT CHARSET=utf8;

/*Data for the table `disziplin_it` */

insert  into `disziplin_it`(`xDisziplin`,`Kurzname`,`Name`,`Anzeige`,`Seriegroesse`,`Staffellaeufer`,`Typ`,`Appellzeit`,`Stellzeit`,`Strecke`,`Code`,`xOMEGA_Typ`,`aktiv`) values 
(38,'50','50 m',10,6,0,2,'01:00:00','00:15:00',50,10,1,'y'),
(39,'55','55 m',20,6,0,2,'01:00:00','00:15:00',55,20,1,'y'),
(40,'60','60 m',30,6,0,2,'01:00:00','00:15:00',60,30,1,'y'),
(41,'80','80 m',35,6,0,1,'01:00:00','00:15:00',80,35,1,'y'),
(42,'100','100 m',40,8,0,1,'01:00:00','00:20:00',100,40,1,'y'),
(43,'150','150 m',48,6,0,1,'01:00:00','00:15:00',150,48,1,'y'),
(44,'200','200 m',50,6,0,1,'01:00:00','00:15:00',200,50,1,'y'),
(45,'300','300 m',60,6,0,2,'01:00:00','00:15:00',300,60,1,'y'),
(46,'400','400 m',70,6,0,2,'01:00:00','00:20:00',400,70,1,'y'),
(47,'600','600 m',80,12,0,7,'01:00:00','00:15:00',600,80,1,'y'),
(48,'800','800 m',90,6,0,7,'01:00:00','00:20:00',800,90,1,'y'),
(49,'1000','1000 m',100,15,0,7,'01:00:00','00:15:00',1000,100,1,'y'),
(50,'1500','1500 m',110,13,0,7,'01:00:00','00:20:00',1500,110,1,'y'),
(51,'1MILE','1 mile',120,15,0,7,'01:00:00','00:15:00',1609,120,1,'y'),
(52,'2000','2000 m',130,15,0,7,'01:00:00','00:15:00',2000,130,1,'y'),
(53,'3000','3000 m',140,15,0,7,'01:00:00','00:15:00',3000,140,1,'y'),
(54,'5000','5000 m',160,15,0,7,'01:00:00','00:15:00',5000,160,1,'y'),
(55,'10000','10 000 m',170,20,0,7,'01:00:00','00:15:00',10000,170,1,'y'),
(56,'20000','20 000 m',180,20,0,7,'01:00:00','00:15:00',20000,180,1,'y'),
(57,'1ORA','1 ora',171,20,0,7,'01:00:00','00:15:00',1,182,1,'y'),
(58,'25000','25 000 m',181,20,0,7,'01:00:00','00:15:00',25000,181,1,'y'),
(59,'30000','30 000 m',182,20,0,7,'01:00:00','00:15:00',30000,195,1,'y'),
(61,'MEZZA MARA','Mezza maratona',183,20,0,7,'01:00:00','00:15:00',0,190,1,'y'),
(62,'MARATONA','Maratona',184,20,0,7,'01:00:00','00:15:00',0,200,1,'y'),
(64,'50H106.7','50 m ostacoli 106.7',232,6,0,1,'01:00:00','00:15:00',50,232,4,'y'),
(65,'50H99.1','50 m ostacoli 99.1',233,6,0,2,'01:00:00','00:15:00',50,233,4,'y'),
(66,'50H91.4','50 m ostacoli 91.4',234,6,0,2,'01:00:00','00:15:00',50,234,4,'y'),
(67,'50H84.0','50 m ostacoli 84.0',235,6,0,2,'01:00:00','00:15:00',50,235,4,'y'),
(68,'50H76.2','50 m ostacoli 76.2  U18 W',236,6,0,2,'01:00:00','00:15:00',50,236,4,'y'),
(69,'60H106.7','60 m ostacoli 106.7',241,6,0,2,'01:00:00','00:15:00',60,252,4,'y'),
(70,'60H99.1','60 m ostacoli 99.1',242,6,0,2,'01:00:00','00:15:00',60,253,4,'y'),
(71,'60H91.4','60 m ostacoli 91.4',243,6,0,2,'01:00:00','00:15:00',60,254,4,'y'),
(72,'60H84.0','60 m ostacoli 84.0',244,6,0,2,'01:00:00','00:15:00',60,255,4,'y'),
(73,'60H76.2','60 m ostacoli 76.2  U18 W',245,6,0,2,'01:00:00','00:15:00',60,256,4,'y'),
(74,'80H76.2','80 m ostacoli 76.2',264,6,0,1,'01:00:00','00:15:00',80,258,4,'y'),
(75,'100H84.0','100 m ostacoli 84.0',266,6,0,1,'01:00:00','00:20:00',100,261,4,'y'),
(76,'100H76.2','100 m ostacoli 76.2',267,6,0,1,'01:00:00','00:20:00',100,259,4,'y'),
(77,'110H106.7','110 m ostacoli 106.7',268,6,0,1,'01:00:00','00:20:00',110,271,4,'y'),
(78,'110H99.1','110 m ostacoli 99.1',269,6,0,1,'01:00:00','00:15:00',110,269,4,'y'),
(79,'110H91.4','110 m ostacoli 91.4',270,6,0,1,'01:00:00','00:15:00',110,268,4,'y'),
(80,'200H','200 m ostacoli',280,6,0,1,'01:00:00','00:15:00',200,280,4,'y'),
(81,'300H84.0','300 m ostacoli 84.0',290,6,0,2,'01:00:00','00:15:00',300,290,4,'y'),
(82,'300H76.2','300 m ostacoli 76.2',291,6,0,2,'01:00:00','00:15:00',300,291,4,'y'),
(83,'400H91.4','400 m ostacoli 91.4',298,6,0,2,'01:00:00','00:20:00',400,301,4,'y'),
(84,'400H76.2','400 m ostacoli 76.2',301,6,0,2,'01:00:00','00:20:00',400,298,4,'y'),
(85,'1500ST','1500 m Steeple',302,6,0,7,'01:00:00','00:15:00',1500,209,6,'y'),
(86,'2000ST','2000 m Steeple',303,6,0,7,'01:00:00','00:15:00',2000,210,6,'y'),
(87,'3000ST','3000 m Steeple',304,6,0,7,'01:00:00','00:15:00',3000,220,6,'y'),
(88,'5XLIBERO','5x libero',395,6,5,3,'01:00:00','00:15:00',5,497,1,'y'),
(89,'5X80','5x80 m',396,6,5,3,'01:00:00','00:15:00',400,498,1,'y'),
(90,'6XLIBERO','6x libero',394,6,6,3,'01:00:00','00:15:00',6,499,1,'y'),
(91,'4X100','4x100 m',397,6,4,3,'01:00:00','00:15:00',400,560,1,'y'),
(92,'4X200','4x200 m',398,6,4,3,'01:00:00','00:15:00',800,570,1,'y'),
(93,'4X400','4x400 m',399,6,4,3,'01:00:00','00:15:00',1600,580,1,'y'),
(94,'3X800','3x800 m',400,6,3,3,'01:00:00','00:15:00',2400,589,1,'y'),
(95,'4X800','4x800 m',401,6,4,3,'01:00:00','00:15:00',3200,590,1,'y'),
(96,'3X1000','3x1000 m',402,6,3,3,'01:00:00','00:15:00',3000,595,1,'y'),
(97,'4X1500','4x1500 m',403,6,4,3,'01:00:00','00:15:00',6000,600,1,'y'),
(98,'OLYMPISCHE','Olympische',404,12,4,3,'01:00:00','00:15:00',0,601,1,'y'),
(99,'AMERICAINE','Américaine',405,12,3,3,'01:00:00','00:15:00',0,602,1,'y'),
(100,'ALTO','Alto',310,15,0,6,'01:00:00','00:30:00',0,310,1,'y'),
(101,'ASTA','Asta',320,15,0,6,'01:00:00','00:30:00',0,320,1,'y'),
(102,'LUNGO','Lungo',330,15,0,4,'01:00:00','00:30:00',0,330,1,'y'),
(103,'TRIPLO','Triplo',340,15,0,4,'01:00:00','00:30:00',0,340,1,'y'),
(104,'PESO7.26','Peso 7.26 kg',347,15,0,8,'01:00:00','00:30:00',0,351,1,'y'),
(105,'PESO6.00','Peso 6.00 kg',348,15,0,8,'01:00:00','00:20:00',0,348,1,'y'),
(106,'PESO5.00','Peso 5.00 kg',349,15,0,8,'01:00:00','00:30:00',0,347,1,'y'),
(107,'PESO4.00','Peso 4.00 kg',350,15,0,8,'01:00:00','00:30:00',0,349,1,'y'),
(108,'PESO3.00','Peso 3.00 kg',352,15,0,8,'01:00:00','00:20:00',0,352,1,'y'),
(109,'PESO2.50','Peso 2.50 kg',353,15,0,8,'01:00:00','00:20:00',0,353,1,'y'),
(110,'DISCO2.00','Disco 2.00 kg',356,15,0,8,'01:00:00','00:20:00',0,361,1,'y'),
(111,'DISCO1.75','Disco 1.75 kg',357,15,0,8,'01:00:00','00:20:00',0,359,1,'y'),
(112,'DISCO1.50','Disco 1.50 kg',358,15,0,8,'01:00:00','00:20:00',0,358,1,'y'),
(113,'DISCO1.00','Disco 1.00 kg',359,15,0,8,'01:00:00','00:20:00',0,357,1,'y'),
(114,'DISCO0.75','Disco 0.75 kg',361,15,0,8,'01:00:00','00:20:00',0,356,1,'y'),
(115,'MARTELLO7.26','Martello 7.26 kg',375,15,0,8,'01:00:00','00:30:00',0,381,1,'y'),
(116,'MARTELLO6.00','Martello 6.00 kg',376,15,0,8,'01:00:00','00:20:00',0,378,1,'y'),
(117,'MARTELLO5.00','Martello 5.00 kg',377,15,0,8,'01:00:00','00:20:00',0,377,1,'y'),
(118,'MARTELLO4.00','Martello 4.00 kg',378,15,0,8,'01:00:00','00:30:00',0,376,1,'y'),
(119,'MARTELLO3.00','Martello 3.00 kg',381,15,0,8,'01:00:00','00:20:00',0,375,1,'y'),
(120,'GIAVELLOTTO800','Giavellotto 800 gr',387,15,0,8,'01:00:00','00:20:00',0,391,1,'y'),
(121,'GIAVELLOTTO700','Giavellotto 700 gr',388,15,0,8,'01:00:00','00:20:00',0,389,1,'y'),
(122,'GIAVELLOTTO600','Giavellotto 600 gr',389,15,0,8,'01:00:00','00:20:00',0,388,1,'y'),
(123,'GIAVELLOTTO400','Giavellotto 400 gr',391,15,0,8,'01:00:00','00:20:00',0,387,1,'y'),
(124,'PALLINA200','Pallina 200 gr',392,15,0,8,'01:00:00','00:20:00',0,386,1,'y'),
(125,'5ATHLON_W_U20WI','Pentathlon W / U20 W Indoor',408,6,0,9,'01:00:00','00:15:00',5,394,1,'y'),
(126,'5ATHLON_U18W_I','Pentathlon U18 W Indoor',409,6,0,9,'01:00:00','00:15:00',5,395,1,'y'),
(127,'7ATHLON_M_I','Heptathlon M Indoor',413,6,0,9,'01:00:00','00:15:00',7,396,1,'y'),
(128,'7ATHLON_U20M_I','Heptathlon U20 M Indoor',414,6,0,9,'01:00:00','00:15:00',7,397,1,'y'),
(129,'7ATHLON_U18M_I','Heptathlon U18 M Indoor',415,6,0,9,'01:00:00','00:15:00',7,398,1,'y'),
(130,'10ATHLON_M','Decathlon M',434,6,0,9,'01:00:00','00:15:00',10,410,1,'y'),
(131,'10ATHLON_U20M','Decathlon U20 M',435,6,0,9,'01:00:00','00:15:00',10,411,1,'y'),
(132,'10ATHLON_U18M','Decathlon U18 M',436,6,0,9,'01:00:00','00:15:00',10,412,1,'y'),
(133,'10ATHLON_W','Decathlon W',437,6,0,9,'01:00:00','00:15:00',10,413,1,'y'),
(134,'7ATHLON','Heptathlon',430,6,0,9,'01:00:00','00:15:00',7,400,1,'y'),
(135,'7ATHLON_U18W','Heptathlon U18 W',431,6,0,9,'01:00:00','00:15:00',7,401,1,'y'),
(136,'6ATHLON_U16M','Hexathlon U16 M',429,6,0,9,'01:00:00','00:15:00',6,402,1,'y'),
(137,'5ATHLON_U16W','Pentathlon U16 W',426,6,0,9,'01:00:00','00:15:00',5,399,1,'y'),
(138,'UKC','UBS Kids Cup',439,6,0,9,'01:00:00','00:15:00',3,408,1,'y'),
(139,'MILEWALK','Mile walk',450,20,0,7,'01:00:00','00:15:00',1609,415,5,'y'),
(140,'3000WALK','3000 m walk',452,20,0,7,'01:00:00','00:15:00',3000,420,5,'y'),
(141,'5000WALK','5000 m walk',453,20,0,7,'01:00:00','00:15:00',5000,430,5,'y'),
(142,'10000WALK','10000 m walk',454,20,0,7,'01:00:00','00:15:00',10000,440,5,'y'),
(143,'20000WALK','20000 m walk',455,20,0,7,'01:00:00','00:15:00',20000,450,5,'y'),
(144,'50000WALK','50000 m walk',456,20,0,7,'01:00:00','00:15:00',50000,460,5,'y'),
(145,'3KMWALK','3 km walk',470,20,0,7,'01:00:00','00:15:00',3000,470,5,'y'),
(146,'5KMWALK','5 km walk',480,20,0,7,'01:00:00','00:15:00',5000,480,5,'y'),
(147,'10KMWALK','10 km walk',490,20,0,7,'01:00:00','00:15:00',10000,490,5,'y'),
(150,'20KMWALK','20 km walk',500,20,0,7,'01:00:00','00:15:00',20000,500,5,'y'),
(152,'35KMWALK','35 km walk',530,20,0,7,'01:00:00','00:15:00',35000,530,5,'y'),
(154,'50KMWALK','50 km walk',550,20,0,7,'01:00:00','00:15:00',50000,550,5,'y'),
(156,'10KM','10 km',440,20,0,7,'01:00:00','00:15:00',10000,491,1,'y'),
(157,'15KM','15 km',441,20,0,7,'01:00:00','00:15:00',15000,494,1,'y'),
(158,'20KM','20 km',442,20,0,7,'01:00:00','00:15:00',20000,501,1,'y'),
(159,'25KM','25 km',443,20,0,7,'01:00:00','00:15:00',25000,505,1,'y'),
(160,'30KM','30 km',444,20,0,7,'01:00:00','00:15:00',30000,511,1,'y'),
(162,'1HWALK','1 h  walk',555,20,0,7,'01:00:00','00:15:00',1,555,5,'y'),
(163,'2HWALK','2 h  walk',556,20,0,7,'01:00:00','00:15:00',2,556,5,'y'),
(164,'100KMWALK','100 km walk',457,20,0,7,'01:00:00','00:15:00',100000,559,5,'y'),
(165,'PALLINA80','Pallina 80 gr',393,15,0,8,'01:00:00','00:20:00',0,385,1,'y'),
(166,'300H91.4','300 m ostacoli 91.4',289,6,0,2,'01:00:00','00:15:00',300,289,4,'y'),
(167,'...ATHLON','...athlon',799,6,0,9,'01:00:00','00:15:00',4,799,1,'y'),
(168,'75','75 m',31,6,0,1,'01:00:00','00:15:00',75,31,1,'y'),
(169,'50H68.6','50 m ostacoli 68.6',240,6,0,2,'01:00:00','00:15:00',50,237,1,'y'),
(170,'60H68.6','60 m ostacoli 68.6',252,6,0,2,'01:00:00','00:15:00',60,257,1,'y'),
(171,'80H84.0','80 m ostacoli 84.0',263,6,0,1,'01:00:00','00:15:00',80,260,1,'y'),
(172,'80H68.6','80 m ostacoli 68.6',265,6,0,1,'01:00:00','00:15:00',80,262,1,'y'),
(173,'300H68.6','300 m ostacoli 68.6',292,6,0,2,'01:00:00','00:15:00',300,295,1,'y'),
(174,'GIAVELLOTTO500','Giavellotto 500 gr',390,15,0,8,'01:00:00','00:20:00',0,390,1,'y'),
(175,'5ATHLON_M','Pentathlon M',418,6,0,9,'01:00:00','00:15:00',5,392,1,'y'),
(176,'5ATHLON_U20M','Pentathlon U20 M',420,6,0,9,'01:00:00','00:15:00',5,393,1,'y'),
(177,'5ATHLON_U18M','Pentathlon U18 M',421,6,0,9,'01:00:00','00:15:00',5,405,1,'y'),
(178,'5ATHLON_F','Pentathlon F',423,6,0,9,'01:00:00','00:15:00',5,416,1,'y'),
(180,'5ATHLON_U18F','Pentathlon U18 F',425,6,0,9,'01:00:00','00:15:00',5,418,1,'y'),
(181,'10ATHLON_MASTER','Decathlon Master',438,6,0,9,'01:00:00','00:15:00',10,414,1,'y'),
(182,'2000WALK','2000 m walk',451,50,0,7,'01:00:00','00:15:00',2000,419,1,'y'),
(183,'...COURS','...cours',796,6,0,9,'01:00:00','00:15:00',4,796,1,'y'),
(184,'...LUNGO','...lungo',797,6,0,9,'01:00:00','00:20:00',4,797,1,'y'),
(185,'...LANCER','...lancer',798,6,0,9,'01:00:00','00:00:00',4,798,1,'y'),
(186,'LUNGO Z','Lungo (zone)',331,15,0,5,'01:00:00','00:20:00',0,331,1,'y'),
(187,'50H76.2U16','50 m ostacoli 76.2  U16W/U14M',237,6,0,2,'01:00:00','00:15:00',50,246,4,'y'),
(188,'50H76.2U14','50 m ostacoli 76.2  U14 W (In)',238,6,0,2,'01:00:00','00:15:00',50,247,4,'y'),
(189,'50H60-76.2','50 m ostacoli 60-76.2 U12 (In)',239,6,0,2,'01:00:00','00:15:00',50,248,4,'y'),
(190,'60H76.2U16','60 m ostacoli 76.2  U16W/U14M',247,6,0,2,'01:00:00','00:15:00',60,275,4,'y'),
(191,'60H76.2U14I','60 m ostacoli 76.2  U14W (In)',248,6,0,2,'01:00:00','00:15:00',60,276,4,'y'),
(192,'60H60-76.2','60 m ostacoli 60-76.2  U12 (In)',250,6,0,2,'01:00:00','00:15:00',60,277,4,'y'),
(193,'60H76.2U14O','60 m ostacoli 76.2  U14 W (Out)',251,6,0,2,'01:00:00','00:15:00',60,278,4,'y'),
(194,'60H60-76.2U12','60 m ostacoli 60-76.2 U12',254,6,0,2,'01:00:00','00:15:00',60,279,4,'y'),
(195,'5ATHLON_U16M','Pentathlon U16 M',422,6,0,9,'01:00:00','00:15:00',5,406,1,'y'),
(196,'5ATHLON_U18M_I','Pentathlon U18 M Indoor',406,6,0,9,'01:00:00','00:15:00',5,424,1,'y'),
(197,'5ATHLON_U23M','Pentathlon U23 M',419,6,0,9,'01:00:00','00:15:00',5,407,1,'y'),
(198,'5ATHLON_U20W','Pentathlon U20 W',424,6,0,9,'01:00:00','00:15:00',5,417,1,'y'),
(199,'55ATHLON_U16M_I','Pentathlon U16 M Indoor',407,6,0,9,'01:00:00','00:15:00',5,425,1,'y'),
(200,'5ATHLON_U16W_I','Pentathlon U16 w Indoor',410,6,0,9,'01:00:00','00:15:00',5,426,1,'y'),
(201,'8ATHLON_U18M','Octathlon U18 M',433,6,0,9,'01:00:00','00:15:00',5,427,1,'y'),
(202,'staffetta sved.','staffetta svedese',404,12,4,3,'01:00:00','00:15:00',0,603,1,'y'),
(203,'asta-lungo','salto con l\'asta et lungo',325,15,0,5,'01:00:00','00:20:00',0,332,1,'y'),
(204,'lancio-rotativo','lancio di rotativo',365,15,0,8,'01:00:00','00:20:00',0,354,1,'y'),
(205,'400H84.0','400 m ostacoli 84.0',800,8,0,2,'01:00:00','00:20:00',0,0,0,'y');

/*Table structure for table `faq` */

DROP TABLE IF EXISTS `faq`;

CREATE TABLE `faq` (
  `xFaq` int(11) NOT NULL AUTO_INCREMENT,
  `Frage` varchar(255) NOT NULL DEFAULT '',
  `Antwort` text NOT NULL,
  `Zeigen` enum('y','n') NOT NULL DEFAULT 'y',
  `PosTop` int(11) NOT NULL DEFAULT '0',
  `PosLeft` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL DEFAULT '0',
  `Seite` varchar(255) NOT NULL DEFAULT '',
  `Sprache` char(2) NOT NULL DEFAULT '',
  `FarbeTitel` varchar(6) NOT NULL DEFAULT 'FFAA00',
  `FarbeHG` varchar(6) NOT NULL DEFAULT 'FFCC00',
  PRIMARY KEY (`xFaq`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `faq` */

/*Table structure for table `hoehe` */

DROP TABLE IF EXISTS `hoehe`;

CREATE TABLE `hoehe` (
  `xHoehe` int(11) NOT NULL AUTO_INCREMENT,
  `Hoehe` int(9) NOT NULL DEFAULT '0',
  `xRunde` int(11) NOT NULL DEFAULT '0',
  `xSerie` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xHoehe`),
  KEY `xRunde` (`xRunde`),
  KEY `xSerie` (`xSerie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `hoehe` */

/*Table structure for table `kategorie` */

DROP TABLE IF EXISTS `kategorie`;

CREATE TABLE `kategorie` (
  `xKategorie` int(11) NOT NULL AUTO_INCREMENT,
  `Kurzname` varchar(4) NOT NULL DEFAULT '',
  `Name` varchar(30) NOT NULL DEFAULT '',
  `Anzeige` int(11) NOT NULL DEFAULT '1',
  `Alterslimite` tinyint(4) NOT NULL DEFAULT '99',
  `Code` varchar(4) NOT NULL DEFAULT '',
  `Geschlecht` enum('m','w') NOT NULL DEFAULT 'm',
  `aktiv` enum('y','n') NOT NULL DEFAULT 'y',
  `UKC` enum('y','n') DEFAULT 'n',
  PRIMARY KEY (`xKategorie`),
  UNIQUE KEY `Kurzname` (`Kurzname`),
  KEY `Anzeige` (`Anzeige`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

/*Data for the table `kategorie` */

insert  into `kategorie`(`xKategorie`,`Kurzname`,`Name`,`Anzeige`,`Alterslimite`,`Code`,`Geschlecht`,`aktiv`,`UKC`) values 
(1,'MAN_','MAN',1,99,'MAN_','m','y','n'),
(2,'U20M','U20 M',4,19,'U20M','m','y','n'),
(3,'U18M','U18 M',5,17,'U18M','m','y','n'),
(4,'U16M','U16 M',6,15,'U16M','m','y','n'),
(5,'U14M','U14 M',7,13,'U14M','m','y','n'),
(6,'U12M','U12 M',8,11,'U12M','m','y','n'),
(7,'WOM_','WOM',10,99,'WOM_','w','y','n'),
(8,'U20W','U20 W',13,19,'U20W','w','y','n'),
(9,'U18W','U18 W',14,17,'U18W','w','y','n'),
(10,'U16W','U16 W',15,15,'U16W','w','y','n'),
(11,'U14W','U14 W',16,13,'U14W','w','y','n'),
(12,'U12W','U12 W',17,11,'U12W','w','y','n'),
(13,'U23M','U23 M',3,22,'U23M','m','y','n'),
(14,'U23W','U23 W',12,22,'U23W','w','y','n'),
(16,'U10M','U10 M',9,9,'U10M','m','y','n'),
(17,'U10W','U10 W',18,9,'U10W','w','y','n'),
(18,'MASM','MASTERS M',2,99,'MASM','m','y','n'),
(19,'MASW','MASTERS W',11,99,'MASW','w','y','n'),
(20,'M15','U16 M15',21,15,'M15','m','y','y'),
(21,'M14','U16 M14',22,14,'M14','m','y','y'),
(22,'M13','U14 M13',23,13,'M13','m','y','y'),
(23,'M12','U14 M12',24,12,'M12','m','y','y'),
(24,'M11','U12 M11',25,11,'M11','m','y','y'),
(25,'M10','U12 M10',26,10,'M10','m','y','y'),
(26,'M09','U10 M09',27,9,'M09','m','y','y'),
(27,'M08','U10 M08',28,8,'M08','m','y','y'),
(28,'M07','U10 M07',29,7,'M07','m','y','y'),
(29,'W15','U16 W15',31,15,'W15','w','y','y'),
(30,'W14','U16 W14',32,14,'W14','w','y','y'),
(31,'W13','U14 W13',33,13,'W13','w','y','y'),
(32,'W12','U14 W12',34,12,'W12','w','y','y'),
(33,'W11','U12 W11',35,11,'W11','w','y','y'),
(34,'W10','U12 W10',36,10,'W10','w','y','y'),
(35,'W09','U10 W09',37,9,'W09','w','y','y'),
(36,'W08','U10 W08',38,8,'W08','w','y','y'),
(37,'W07','U10 W07',39,7,'W07','w','y','y');

/*Table structure for table `kategorie_svm` */

DROP TABLE IF EXISTS `kategorie_svm`;

CREATE TABLE `kategorie_svm` (
  `xKategorie_svm` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL DEFAULT '',
  `Code` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`xKategorie_svm`),
  KEY `Code` (`Code`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

/*Data for the table `kategorie_svm` */

insert  into `kategorie_svm`(`xKategorie_svm`,`Name`,`Code`) values 
(1,'29.01 Nationalliga A Männer','29_01'),
(2,'29.02 Nationalliga A Frauen','29_02'),
(3,'30.01 Nationalliga B Männer','30_01'),
(4,'30.02 Nationalliga B Frauen','30_02'),
(5,'31.01 Nationalliga C Männer','31_01'),
(6,'31.02 Nationalliga C Frauen','31_02'),
(7,'32.01 Regionalliga A Männer','32_01'),
(9,'32.03 Regionalliga A Frauen','32_03'),
(11,'33.01 Junior Liga Männer','33_01'),
(13,'33.03 Junior Liga Frauen','33_03'),
(15,'35.01 M30 und älter Männer','35_01'),
(16,'35.02 U18 M','35_02'),
(17,'35.03 U18 M Mehrkampf','35_03'),
(18,'35.04 U16 M','35_04'),
(19,'35.05 U16 M Mehrkampf','35_05'),
(20,'35.06 U14 M','35_06'),
(21,'35.07 U14 M Mannschaftswettkampf','35_07'),
(22,'35.08 U12 M Mannschaftswettkampf','35_08'),
(23,'36.01 W30 und älter Frauen','36_01'),
(24,'36.02 U18 W','36_02'),
(25,'36.03 U18 W Mehrkampf','36_03'),
(26,'36.04 U16 W','36_04'),
(27,'36.05 U16 W Mehrkampf','36_05'),
(28,'36.06 U14 W','36_06'),
(29,'36.07 U14 W Mannschaftswettkampf','36_07'),
(30,'36.08 U12 W Mannschaftswettkampf','36_08'),
(31,'36.09 Mixed Team U12 M und U12 W','36_09'),
(36,'32.07 Regionalliga B Männer','32_07'),
(37,'32.08 Regionalliga B Frauen','32_08');

/*Table structure for table `land` */

DROP TABLE IF EXISTS `land`;

CREATE TABLE `land` (
  `xCode` char(3) NOT NULL DEFAULT '',
  `Name` varchar(100) NOT NULL DEFAULT '',
  `Sortierwert` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `land` */

insert  into `land`(`xCode`,`Name`,`Sortierwert`) values 
('SUI','Switzerland',1),
('AFG','Afghanistan',2),
('ALB','Albania',3),
('ALG','Algeria',4),
('ASA','American Samoa',5),
('AND','Andorra',6),
('ANG','Angola',7),
('AIA','Anguilla',8),
('ANT','Antigua & Barbuda',9),
('ARG','Argentina',10),
('ARM','Armenia',11),
('ARU','Aruba',12),
('AUS','Australia',13),
('AUT','Austria',14),
('AZE','Azerbaijan',15),
('BAH','Bahamas',16),
('BRN','Bahrain',17),
('BAN','Bangladesh',18),
('BAR','Barbados',19),
('BLR','Belarus',20),
('BEL','Belgium',21),
('BIZ','Belize',22),
('BEN','Benin',23),
('BER','Bermuda',24),
('BHU','Bhutan',25),
('BOL','Bolivia',26),
('BIH','Bosnia Herzegovina',27),
('BOT','Botswana',28),
('BRA','Brazil',29),
('BRU','Brunei',30),
('BUL','Bulgaria',31),
('BRK','Burkina Faso',32),
('BDI','Burundi',33),
('CAM','Cambodia',34),
('CMR','Cameroon',35),
('CAN','Canada',36),
('CPV','Cape Verde Islands',37),
('CAY','Cayman Islands',38),
('CAF','Central African Republic',39),
('CHA','Chad',40),
('CHI','Chile',41),
('CHN','China',42),
('COL','Colombia',43),
('COM','Comoros',44),
('CGO','Congo',45),
('COD','Congo [Zaire]',46),
('COK','Cook Islands',47),
('CRC','Costa Rica',48),
('CIV','Ivory Coast',49),
('CRO','Croatia',50),
('CUB','Cuba',51),
('CYP','Cyprus',52),
('CZE','Czech Republic',53),
('DEN','Denmark',54),
('DJI','Djibouti',55),
('DMA','Dominica',56),
('DOM','Dominican Republic',57),
('TLS','East Timor',58),
('ECU','Ecuador',59),
('EGY','Egypt',60),
('ESA','El Salvador',61),
('GEQ','Equatorial Guinea',62),
('ERI','Eritrea',63),
('EST','Estonia',64),
('ETH','Ethiopia',65),
('FIJ','Fiji',66),
('FIN','Finland',67),
('FRA','France',68),
('GAB','Gabon',69),
('GAM','Gambia',70),
('GEO','Georgia',71),
('GER','Germany',72),
('GHA','Ghana',73),
('GIB','Gibraltar',74),
('GBR','Great Britain & NI',75),
('GRE','Greece',76),
('GRN','Grenada',77),
('GUM','Guam',78),
('GUA','Guatemala',79),
('GUI','Guinea',80),
('GBS','Guinea-Bissau',81),
('GUY','Guyana',82),
('HAI','Haiti',83),
('HON','Honduras',84),
('HKG','Hong Kong',85),
('HUN','Hungary',86),
('ISL','Iceland',87),
('IND','India',88),
('INA','Indonesia',89),
('IRI','Iran',90),
('IRQ','Iraq',91),
('IRL','Ireland',92),
('ISR','Israel',93),
('ITA','Italy',94),
('JAM','Jamaica',95),
('JPN','Japan',96),
('JOR','Jordan',97),
('KAZ','Kazakhstan',98),
('KEN','Kenya',99),
('KIR','Kiribati',100),
('KOR','Korea',101),
('KUW','Kuwait',102),
('KGZ','Kirgizstan',103),
('LAO','Laos',104),
('LAT','Latvia',105),
('LIB','Lebanon',106),
('LES','Lesotho',107),
('LBR','Liberia',108),
('LIE','Liechtenstein',109),
('LTU','Lithuania',110),
('LUX','Luxembourg',111),
('LBA','Libya',112),
('MAC','Macao',113),
('MKD','Macedonia',114),
('MAD','Madagascar',115),
('MAW','Malawi',116),
('MAS','Malaysia',117),
('MDV','Maldives',118),
('MLI','Mali',119),
('MLT','Malta',120),
('MSH','Marshall Islands',121),
('MTN','Mauritania',122),
('MRI','Mauritius',123),
('MEX','Mexico',124),
('FSM','Micronesia',125),
('MDA','Moldova',126),
('MON','Monaco',127),
('MGL','Mongolia',128),
('MNE','Montenegro',129),
('MNT','Montserrat',130),
('MAR','Morocco',131),
('MOZ','Mozambique',132),
('MYA','Myanmar [Burma]',133),
('NAM','Namibia',134),
('NRU','Nauru',135),
('NEP','Nepal',136),
('NED','Netherlands',137),
('AHO','Netherlands Antilles',138),
('NZL','New Zealand',139),
('NCA','Nicaragua',140),
('NIG','Niger',141),
('NGR','Nigeria',142),
('NFI','Norfolk Islands',143),
('PRK','North Korea',144),
('NOR','Norway',145),
('OMN','Oman',146),
('PAK','Pakistan',147),
('PLW','Palau',148),
('PLE','Palestine',149),
('PAN','Panama',150),
('NGU','Papua New Guinea',151),
('PAR','Paraguay',152),
('PER','Peru',153),
('PHI','Philippines',154),
('POL','Poland',155),
('POR','Portugal',156),
('PUR','Puerto Rico',157),
('QAT','Qatar',158),
('ROM','Romania',159),
('RUS','Russia',160),
('RWA','Rwanda',161),
('SMR','San Marino',162),
('STP','São Tome & Principé',163),
('KSA','Saudi Arabia',164),
('SEN','Senegal',165),
('SRB','Serbia',166),
('SEY','Seychelles',167),
('SLE','Sierra Leone',168),
('SIN','Singapore',169),
('SVK','Slovakia',170),
('SLO','Slovenia',171),
('SOL','Solomon Islands',172),
('SOM','Somalia',173),
('RSA','South Africa',174),
('ESP','Spain',175),
('SKN','St. Kitts & Nevis',176),
('SRI','Sri Lanka',177),
('LCA','St. Lucia',178),
('VIN','St. Vincent & the Grenadines',179),
('SUD','Sudan',180),
('SUR','Surinam',181),
('SWZ','Swaziland',182),
('SWE','Sweden',183),
('SYR','Syria',185),
('TAH','Tahiti',186),
('TPE','Taiwan',187),
('TAD','Tadjikistan',188),
('TAN','Tanzania',189),
('THA','Thailand',190),
('TOG','Togo',191),
('TGA','Tonga',192),
('TRI','Trinidad & Tobago',193),
('TUN','Tunisia',194),
('TUR','Turkey',195),
('TKM','Turkmenistan',196),
('TKS','Turks & Caicos Islands',197),
('UGA','Uganda',198),
('UKR','Ukraine',199),
('UAE','United Arab Emirates',200),
('USA','United States',201),
('URU','Uruguay',202),
('UZB','Uzbekistan',203),
('VAN','Vanuatu',204),
('VEN','Venezuela',205),
('VIE','Vietnam',206),
('ISV','Virgin Islands',207),
('SAM','Western Samoa',208),
('YEM','Yemen',209),
('ZAM','Zambia',210),
('ZIM','Zimbabwe',211);

/*Table structure for table `layout` */

DROP TABLE IF EXISTS `layout`;

CREATE TABLE `layout` (
  `xLayout` int(11) NOT NULL AUTO_INCREMENT,
  `TypTL` int(11) NOT NULL DEFAULT '0',
  `TextTL` varchar(255) NOT NULL DEFAULT '',
  `BildTL` varchar(255) NOT NULL DEFAULT '',
  `TypTC` int(11) NOT NULL DEFAULT '0',
  `TextTC` varchar(255) NOT NULL DEFAULT '',
  `BildTC` varchar(255) NOT NULL DEFAULT '',
  `TypTR` int(11) NOT NULL DEFAULT '0',
  `TextTR` varchar(255) NOT NULL DEFAULT '',
  `BildTR` varchar(255) NOT NULL DEFAULT '',
  `TypBL` int(11) NOT NULL DEFAULT '0',
  `TextBL` varchar(255) NOT NULL DEFAULT '',
  `BildBL` varchar(255) NOT NULL DEFAULT '',
  `TypBC` int(11) NOT NULL DEFAULT '0',
  `TextBC` varchar(255) NOT NULL DEFAULT '',
  `BildBC` varchar(255) NOT NULL DEFAULT '',
  `TypBR` int(11) NOT NULL DEFAULT '0',
  `TextBR` varchar(255) NOT NULL DEFAULT '',
  `BildBR` varchar(255) NOT NULL DEFAULT '',
  `xMeeting` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xLayout`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `layout` */


/*Table structure for table `meeting` */

DROP TABLE IF EXISTS `meeting`;

CREATE TABLE `meeting` (
  `xMeeting` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(60) NOT NULL DEFAULT '',
  `Ort` varchar(20) NOT NULL DEFAULT '',
  `DatumVon` date NOT NULL DEFAULT '0000-00-00',
  `DatumBis` date DEFAULT NULL,
  `Nummer` varchar(20) NOT NULL DEFAULT '',
  `ProgrammModus` int(1) NOT NULL DEFAULT '0',
  `Online` enum('y','n') NOT NULL DEFAULT 'y',
  `Organisator` varchar(200) NOT NULL DEFAULT '',
  `Zeitmessung` enum('no','omega','alge') NOT NULL DEFAULT 'no',
  `Passwort` varchar(50) NOT NULL DEFAULT '',
  `xStadion` int(11) NOT NULL DEFAULT '0',
  `xControl` int(11) NOT NULL DEFAULT '0',
  `Startgeld` float NOT NULL DEFAULT '0',
  `StartgeldReduktion` float NOT NULL DEFAULT '0',
  `Haftgeld` float NOT NULL DEFAULT '0',
  `Saison` enum('','I','O') NOT NULL DEFAULT '',
  `AutoRangieren` enum('n','y') NOT NULL DEFAULT 'n',
  `UKC` enum('y','n') DEFAULT 'n',
  `StatusChanged` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`xMeeting`),
  KEY `Name` (`Name`),
  KEY `xStadion` (`xStadion`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `meeting` */


/*Table structure for table `omega_typ` */

DROP TABLE IF EXISTS `omega_typ`;

CREATE TABLE `omega_typ` (
  `xOMEGA_Typ` int(11) NOT NULL DEFAULT '0',
  `OMEGA_Name` varchar(15) NOT NULL DEFAULT '',
  `OMEGA_Kurzname` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`xOMEGA_Typ`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `omega_typ` */

insert  into `omega_typ`(`xOMEGA_Typ`,`OMEGA_Name`,`OMEGA_Kurzname`) values 
(1,'','0001'),
(2,'Handstoppung','Hnd'),
(3,'ohne Limite','o.Li'),
(4,'Hürden','Hü'),
(5,'Gehen','Geh'),
(6,'Steeple','Stpl');

/*Table structure for table `palmares` */

DROP TABLE IF EXISTS `palmares`;

CREATE TABLE `palmares` (
  `license` int(10) DEFAULT NULL,
  `palmares_international` longtext,
  `palmares_national` longtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `palmares` */


/*Table structure for table `region` */

DROP TABLE IF EXISTS `region`;

CREATE TABLE `region` (
  `xRegion` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL DEFAULT '',
  `Anzeige` varchar(6) NOT NULL DEFAULT '',
  `Sortierwert` int(11) NOT NULL DEFAULT '0',
  `UKC` enum('y','n') DEFAULT 'n',
  PRIMARY KEY (`xRegion`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

/*Data for the table `region` */

insert  into `region`(`xRegion`,`Name`,`Anzeige`,`Sortierwert`,`UKC`) values 
(1,'Aargau','AG',100,'n'),
(2,'Appenzell Ausserrhoden','AR',101,'n'),
(3,'Appenzell Innerrhoden','AI',102,'n'),
(4,'Basel-Landschaft','BL',103,'n'),
(5,'Basel-Stadt','BS',104,'n'),
(6,'Bern','BE',105,'n'),
(7,'Freiburg','FR',106,'n'),
(8,'Genf','GE',107,'n'),
(9,'Glarus','GL',108,'n'),
(10,'Graubünden','GR',109,'n'),
(11,'Jura','JU',110,'n'),
(12,'Luzern','LU',111,'n'),
(13,'Neuenburg','NE',112,'n'),
(14,'Nidwalden','NW',113,'n'),
(15,'Obwalden','OW',114,'n'),
(16,'Sankt Gallen','SG',115,'n'),
(17,'Schaffhausen','SH',116,'n'),
(18,'Schwyz','SZ',117,'n'),
(19,'Solothurn','SO',118,'n'),
(20,'Thurgau','TG',119,'n'),
(21,'Tessin','TI',120,'n'),
(22,'Uri','UR',121,'n'),
(23,'Wallis','VS',122,'n'),
(24,'Waadt','VD',123,'n'),
(25,'Zug','ZG',124,'n'),
(26,'Zürich','ZH',125,'n'),
(27,'Liechtenstein','FL',126,'y');

/*Table structure for table `rekorde` */

DROP TABLE IF EXISTS `rekorde`;

CREATE TABLE `rekorde` (
  `record_type` varchar(10) NOT NULL,
  `season` varchar(1) NOT NULL,
  `discipline` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `result` varchar(100) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `rekorde` */


/*Table structure for table `resultat` */

DROP TABLE IF EXISTS `resultat`;

CREATE TABLE `resultat` (
  `xResultat` int(11) NOT NULL AUTO_INCREMENT,
  `Leistung` int(9) NOT NULL DEFAULT '0',
  `Info` char(5) NOT NULL DEFAULT '-',
  `Punkte` float NOT NULL DEFAULT '0',
  `xSerienstart` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xResultat`),
  KEY `Leistung` (`Leistung`),
  KEY `Serienstart` (`xSerienstart`)
) ENGINE=MyISAM AUTO_INCREMENT=2168 DEFAULT CHARSET=utf8;

/*Data for the table `resultat` */


/*Table structure for table `runde` */

DROP TABLE IF EXISTS `runde`;

CREATE TABLE `runde` (
  `xRunde` int(11) NOT NULL AUTO_INCREMENT,
  `Datum` date NOT NULL DEFAULT '0000-00-00',
  `Startzeit` time NOT NULL DEFAULT '00:00:00',
  `Appellzeit` time NOT NULL DEFAULT '00:00:00',
  `Stellzeit` time NOT NULL DEFAULT '00:00:00',
  `Status` int(11) NOT NULL DEFAULT '0',
  `Speakerstatus` int(11) NOT NULL DEFAULT '0',
  `StatusZeitmessung` tinyint(4) NOT NULL DEFAULT '0',
  `StatusUpload` tinyint(4) NOT NULL DEFAULT '0',
  `QualifikationSieger` tinyint(4) NOT NULL DEFAULT '0',
  `QualifikationLeistung` tinyint(4) NOT NULL DEFAULT '0',
  `Bahnen` tinyint(4) NOT NULL DEFAULT '0',
  `Versuche` tinyint(4) NOT NULL DEFAULT '0',
  `Gruppe` char(2) NOT NULL DEFAULT '',
  `xRundentyp` int(11) DEFAULT NULL,
  `xWettkampf` int(11) NOT NULL DEFAULT '0',
  `nurBestesResultat` enum('y','n') NOT NULL DEFAULT 'n',
  `StatusChanged` enum('y','n') NOT NULL DEFAULT 'y',
  `Endkampf` enum('0','1') NOT NULL DEFAULT '0',
  `Finalisten` tinyint(4) DEFAULT '8',
  `FinalNach` tinyint(4) DEFAULT '3',
  `Drehen` varchar(20) DEFAULT '3',
  `StatusUploadUKC` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`xRunde`),
  KEY `xWettkampf` (`xWettkampf`),
  KEY `Zeit` (`Datum`,`Startzeit`),
  KEY `Status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=200 DEFAULT CHARSET=utf8;

/*Data for the table `runde` */


/*Table structure for table `rundenlog` */

DROP TABLE IF EXISTS `rundenlog`;

CREATE TABLE `rundenlog` (
  `xRundenlog` int(11) NOT NULL AUTO_INCREMENT,
  `Zeit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Ereignis` varchar(255) NOT NULL DEFAULT '',
  `xRunde` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xRundenlog`),
  KEY `Zeit` (`Zeit`),
  KEY `Runde` (`xRunde`)
) ENGINE=MyISAM AUTO_INCREMENT=574 DEFAULT CHARSET=utf8;

/*Data for the table `rundenlog` */


/*Table structure for table `rundenset` */

DROP TABLE IF EXISTS `rundenset`;

CREATE TABLE `rundenset` (
  `xRundenset` int(11) NOT NULL DEFAULT '0',
  `xMeeting` int(11) NOT NULL DEFAULT '0',
  `xRunde` int(11) NOT NULL DEFAULT '0',
  `Hauptrunde` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xRundenset`,`xMeeting`,`xRunde`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `rundenset` */


/*Table structure for table `rundentyp_de` */

DROP TABLE IF EXISTS `rundentyp_de`;

CREATE TABLE `rundentyp_de` (
  `xRundentyp` int(11) NOT NULL AUTO_INCREMENT,
  `Typ` char(2) NOT NULL DEFAULT '',
  `Name` varchar(20) NOT NULL DEFAULT '',
  `Wertung` tinyint(4) DEFAULT '0',
  `Code` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`xRundentyp`),
  UNIQUE KEY `Name` (`Name`),
  UNIQUE KEY `Typ` (`Typ`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `rundentyp_de` */

insert  into `rundentyp_de`(`xRundentyp`,`Typ`,`Name`,`Wertung`,`Code`) values 
(1,'V','Vorlauf',0,'V'),
(2,'F','Final',0,'F'),
(3,'Z','Zwischenlauf',0,'Z'),
(5,'Q','Qualifikation',1,'Q'),
(6,'S','Serie',0,'S'),
(7,'X','Halbfinal',0,'X'),
(8,'D','Mehrkampf',1,'D'),
(9,'0','(ohne)',2,'0'),
(10,'FZ','Zeitläufe',1,'FZ');

/*Table structure for table `rundentyp_fr` */

DROP TABLE IF EXISTS `rundentyp_fr`;

CREATE TABLE `rundentyp_fr` (
  `xRundentyp` int(11) NOT NULL AUTO_INCREMENT,
  `Typ` char(2) NOT NULL DEFAULT '',
  `Name` varchar(20) NOT NULL DEFAULT '',
  `Wertung` tinyint(4) DEFAULT '0',
  `Code` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`xRundentyp`),
  UNIQUE KEY `Name` (`Name`),
  UNIQUE KEY `Typ` (`Typ`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `rundentyp_fr` */

insert  into `rundentyp_fr`(`xRundentyp`,`Typ`,`Name`,`Wertung`,`Code`) values 
(1,'V','Eliminatoire',0,'V'),
(2,'F','Finale',0,'F'),
(3,'Z','Second Tour',0,'Z'),
(5,'Q','Qualification',1,'Q'),
(6,'S','Série',0,'S'),
(7,'X','Demi-finale',0,'X'),
(8,'D','Concour multiple',1,'D'),
(9,'0','(sans)',2,'0'),
(10,'FZ','Courses au temps',1,'FZ');

/*Table structure for table `rundentyp_it` */

DROP TABLE IF EXISTS `rundentyp_it`;

CREATE TABLE `rundentyp_it` (
  `xRundentyp` int(11) NOT NULL AUTO_INCREMENT,
  `Typ` char(2) NOT NULL DEFAULT '',
  `Name` varchar(20) NOT NULL DEFAULT '',
  `Wertung` tinyint(4) DEFAULT '0',
  `Code` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`xRundentyp`),
  UNIQUE KEY `Name` (`Name`),
  UNIQUE KEY `Typ` (`Typ`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `rundentyp_it` */

insert  into `rundentyp_it`(`xRundentyp`,`Typ`,`Name`,`Wertung`,`Code`) values 
(1,'V','Eliminatoria',0,'V'),
(2,'F','Finale',0,'F'),
(3,'Z','Secondo Tour',0,'Z'),
(5,'Q','Qualificazione',1,'Q'),
(6,'S','Serie',0,'S'),
(7,'X','Semifinale',0,'X'),
(8,'D','Gara multipla',1,'D'),
(9,'0','(senza)',2,'0'),
(10,'FZ','Zeitläufe',1,'FZ');

/*Table structure for table `serie` */

DROP TABLE IF EXISTS `serie`;

CREATE TABLE `serie` (
  `xSerie` int(11) NOT NULL AUTO_INCREMENT,
  `Bezeichnung` char(2) NOT NULL DEFAULT '',
  `Wind` varchar(5) DEFAULT '',
  `Film` int(11) DEFAULT '0',
  `Status` int(11) NOT NULL DEFAULT '0',
  `Handgestoppt` tinyint(4) NOT NULL DEFAULT '0',
  `xRunde` int(11) NOT NULL DEFAULT '0',
  `xAnlage` int(11) DEFAULT NULL,
  `TVName` varchar(70) DEFAULT NULL,
  `MaxAthlet` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xSerie`),
  UNIQUE KEY `Bezeichnung` (`xRunde`,`Bezeichnung`),
  KEY `Runde` (`xRunde`),
  KEY `Anlage` (`xAnlage`)
) ENGINE=MyISAM AUTO_INCREMENT=157 DEFAULT CHARSET=utf8;

/*Data for the table `serie` */


/*Table structure for table `serienstart` */

DROP TABLE IF EXISTS `serienstart`;

CREATE TABLE `serienstart` (
  `xSerienstart` int(11) NOT NULL AUTO_INCREMENT,
  `Position` int(11) NOT NULL DEFAULT '0',
  `Bahn` int(11) NOT NULL DEFAULT '0',
  `Rang` int(11) NOT NULL DEFAULT '0',
  `Qualifikation` tinyint(4) NOT NULL DEFAULT '0',
  `xSerie` int(11) NOT NULL DEFAULT '0',
  `xStart` int(11) NOT NULL DEFAULT '0',
  `RundeZusammen` int(11) NOT NULL DEFAULT '0',
  `Bemerkung` char(5) NOT NULL DEFAULT '',
  `Position2` int(11) NOT NULL DEFAULT '0',
  `Position3` int(11) NOT NULL DEFAULT '0',
  `AktivAthlet` enum('y','n') NOT NULL DEFAULT 'n',
  `Starthoehe` int(11) DEFAULT '0',
  PRIMARY KEY (`xSerienstart`),
  UNIQUE KEY `Serienstart` (`xSerie`,`xStart`),
  KEY `Rang` (`Rang`),
  KEY `Qualifikation` (`Qualifikation`),
  KEY `xSerie` (`xSerie`),
  KEY `xStart` (`xStart`)
) ENGINE=MyISAM AUTO_INCREMENT=1388 DEFAULT CHARSET=utf8;

/*Data for the table `serienstart` */


/*Table structure for table `stadion` */

DROP TABLE IF EXISTS `stadion`;

CREATE TABLE `stadion` (
  `xStadion` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL DEFAULT '',
  `Bahnen` tinyint(4) NOT NULL DEFAULT '6',
  `BahnenGerade` tinyint(4) NOT NULL DEFAULT '8',
  `Ueber1000m` enum('y','n') NOT NULL DEFAULT 'n',
  `Halle` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`xStadion`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `stadion` */


/*Table structure for table `staffel` */

DROP TABLE IF EXISTS `staffel`;

CREATE TABLE `staffel` (
  `xStaffel` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(40) NOT NULL DEFAULT '',
  `xVerein` int(11) NOT NULL DEFAULT '0',
  `xMeeting` int(11) NOT NULL DEFAULT '0',
  `xKategorie` int(11) NOT NULL DEFAULT '0',
  `xTeam` int(11) NOT NULL DEFAULT '0',
  `Athleticagen` enum('y','n') NOT NULL DEFAULT 'n',
  `Startnummer` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xStaffel`),
  KEY `xMeeting` (`xMeeting`),
  KEY `xVerein` (`xVerein`),
  KEY `Name` (`Name`(10)),
  KEY `xTeam` (`xTeam`),
  KEY `Startnummer` (`Startnummer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `staffel` */

/*Table structure for table `staffelathlet` */

DROP TABLE IF EXISTS `staffelathlet`;

CREATE TABLE `staffelathlet` (
  `xStaffelstart` int(11) NOT NULL DEFAULT '0',
  `xAthletenstart` int(11) NOT NULL DEFAULT '0',
  `xRunde` int(11) NOT NULL DEFAULT '0',
  `Position` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xStaffelstart`,`xAthletenstart`,`xRunde`),
  UNIQUE KEY `Reihenfolge` (`xStaffelstart`,`Position`,`xRunde`),
  KEY `Position` (`Position`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `staffelathlet` */

/*Table structure for table `start` */

DROP TABLE IF EXISTS `start`;

CREATE TABLE `start` (
  `xStart` int(11) NOT NULL AUTO_INCREMENT,
  `Anwesend` smallint(1) NOT NULL DEFAULT '0',
  `Bestleistung` int(11) NOT NULL DEFAULT '0',
  `Bezahlt` enum('y','n') NOT NULL DEFAULT 'n',
  `Erstserie` enum('y','n') NOT NULL DEFAULT 'n',
  `xWettkampf` int(11) NOT NULL DEFAULT '0',
  `xAnmeldung` int(11) NOT NULL DEFAULT '0',
  `xStaffel` int(11) NOT NULL DEFAULT '0',
  `BaseEffort` enum('y','n') NOT NULL DEFAULT 'y',
  `VorjahrLeistung` int(11) DEFAULT '0',
  `Gruppe` char(2) DEFAULT '',
  PRIMARY KEY (`xStart`),
  UNIQUE KEY `start` (`xWettkampf`,`xAnmeldung`,`xStaffel`),
  KEY `Staffel` (`xStaffel`),
  KEY `Anmeldung` (`xAnmeldung`),
  KEY `Wettkampf` (`xWettkampf`),
  KEY `WettkampfAnmeldung` (`xAnmeldung`,`xWettkampf`),
  KEY `WettkampfStaffel` (`xStaffel`,`xWettkampf`)
) ENGINE=MyISAM AUTO_INCREMENT=3414 DEFAULT CHARSET=utf8;

/*Data for the table `start` */


/*Table structure for table `sys_backuptabellen` */

DROP TABLE IF EXISTS `sys_backuptabellen`;

CREATE TABLE `sys_backuptabellen` (
  `xBackup` int(11) NOT NULL AUTO_INCREMENT,
  `Tabelle` varchar(50) DEFAULT NULL,
  `SelectSQL` text,
  PRIMARY KEY (`xBackup`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

/*Data for the table `sys_backuptabellen` */

insert  into `sys_backuptabellen`(`xBackup`,`Tabelle`,`SelectSQL`) values 
(1,'anlage','SELECT * FROM anlage'),
(2,'anmeldung','SELECT * FROM anmeldung WHERE xMeeting = \'%d\''),
(3,'athlet','SELECT * FROM athlet'),
(5,'base_account','SELECT * FROM base_account'),
(6,'base_athlete','SELECT * FROM base_athlete'),
(7,'base_log','SELECT * FROM base_log'),
(8,'base_performance','SELECT * FROM base_performance'),
(9,'base_relay','SELECT * FROM base_relay'),
(10,'base_svm','SELECT * FROM base_svm'),
(11,'disziplin_de','SELECT * FROM disziplin_de'),
(12,'disziplin_fr','SELECT * FROM disziplin_fr'),
(13,'disziplin_it','SELECT * FROM disziplin_it'),
(14,'kategorie','SELECT * FROM kategorie'),
(16,'layout','SELECT * FROM layout WHERE xMeeting = \'%d\''),
(17,'meeting','SELECT * FROM meeting WHERE xMeeting=\'%d\''),
(18,'omega_typ','SELECT * FROM omega_typ'),
(19,'region','SELECT * FROM region'),
(20,'resultat','SELECT\r\n    resultat.*\r\nFROM\r\n    athletica.resultat\r\n    LEFT JOIN athletica.serienstart \r\n        ON (resultat.xSerienstart = serienstart.xSerienstart)\r\n    LEFT JOIN athletica.start \r\n        ON (serienstart.xStart = start.xStart)\r\n    LEFT JOIN athletica.wettkampf \r\n        ON (start.xWettkampf = wettkampf.xWettkampf)\r\nWHERE (wettkampf.xMeeting =\'%d\') \r\nAND xResultat IS NOT NULL;'),
(21,'runde','SELECT\r\n    runde.*\r\nFROM\r\n    athletica.wettkampf\r\n    LEFT JOIN athletica.runde \r\n        ON (wettkampf.xWettkampf = runde.xWettkampf)\r\nWHERE (wettkampf.xMeeting =\'%d\') \r\nAND xRunde IS NOT NULL;'),
(22,'rundenlog','SELECT\r\n    rundenlog.*\r\nFROM\r\n    athletica.runde\r\n    JOIN athletica.rundenlog \r\n        ON (runde.xRunde = rundenlog.xRunde)\r\n    JOIN athletica.wettkampf \r\n        ON (wettkampf.xWettkampf = runde.xWettkampf)\r\nWHERE (wettkampf.xMeeting =\'%d\') \r\nAND xRundenlog IS NOT NULL;'),
(23,'rundenset','SELECT * FROM rundenset WHERE xMeeting = \'%d\''),
(24,'rundentyp_de','SELECT * FROM rundentyp_de'),
(25,'rundentyp_fr','SELECT * FROM rundentyp_de'),
(26,'rundentyp_it','SELECT * FROM rundentyp_de'),
(27,'serie','SELECT\r\n    serie.*\r\nFROM\r\n    athletica.wettkampf\r\n    LEFT JOIN athletica.runde \r\n        ON (wettkampf.xWettkampf = runde.xWettkampf)\r\n    LEFT JOIN athletica.serie \r\n        ON (runde.xRunde = serie.xRunde)\r\nWHERE (wettkampf.xMeeting =\'%d\') \r\nAND xSerie IS NOT NULL;'),
(28,'serienstart','SELECT\r\n    serienstart.*\r\nFROM\r\n    athletica.wettkampf\r\n    LEFT JOIN athletica.runde \r\n        ON (wettkampf.xWettkampf = runde.xWettkampf)\r\n    LEFT JOIN athletica.serie \r\n        ON (runde.xRunde = serie.xRunde)\r\n    LEFT JOIN athletica.serienstart \r\n        ON (serie.xSerie = serienstart.xSerie)\r\nWHERE (wettkampf.xMeeting =\'%d\') \r\nAND xSerienstart IS NOT NULL;'),
(29,'stadion','SELECT * FROM stadion'),
(30,'staffel','SELECT * FROM staffel WHERE xMeeting = \'%d\''),
(31,'staffelathlet','SELECT\r\n    staffelathlet.*\r\nFROM\r\n    athletica.staffelathlet\r\n    INNER JOIN athletica.runde \r\n        ON (staffelathlet.xRunde = runde.xRunde)\r\n    INNER JOIN athletica.wettkampf \r\n        ON (runde.xWettkampf = wettkampf.xWettkampf)\r\nWHERE (wettkampf.xMeeting =\'%d\') \r\nAND xStaffelstart IS NOT NULL;'),
(32,'start','SELECT\r\n    start.*\r\nFROM\r\n    athletica.wettkampf\r\n    LEFT JOIN athletica.start \r\n        ON (wettkampf.xWettkampf = start.xWettkampf)\r\nWHERE (wettkampf.xMeeting =\'%d\') \r\nAND xStart IS NOT NULL;'),
(33,'team','SELECT * FROM team WHERE xMeeting = \'%d\''),
(34,'teamsm','SELECT * FROM teamsm WHERE xMeeting = \'%d\''),
(35,'teamsmathlet','SELECT\r\n    teamsmathlet.*\r\nFROM\r\n    athletica.teamsmathlet\r\n    LEFT JOIN athletica.anmeldung \r\n        ON (teamsmathlet.xAnmeldung = anmeldung.xAnmeldung)\r\nWHERE (anmeldung.xMeeting =\'%d\') \r\nAND xTeamsm IS NOT NULL;'),
(36,'verein','SELECT * FROM verein'),
(37,'wertungstabelle','SELECT * FROM wertungstabelle'),
(38,'wertungstabelle_punkte','SELECT * FROM wertungstabelle_punkte'),
(39,'wettkampf','SELECT * FROM wettkampf WHERE xMeeting = \'%d\''),
(40,'zeitmessung','SELECT * FROM zeitmessung WHERE xMeeting = \'%d\''),

(41,'palmares','SELECT * FROM palmares'),

(42,'hoehe','SELECT * FROM hoehe'),

(43,'kategorie_svm','SELECT * FROM kategorie_svm'),

(44,'land','SELECT * FROM land'),

(45,'rekorde','SELECT * FROM rekorde');

/*Table structure for table `team` */

DROP TABLE IF EXISTS `team`;

CREATE TABLE `team` (
  `xTeam` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) NOT NULL DEFAULT '',
  `Athleticagen` enum('y','n') NOT NULL DEFAULT 'n',
  `xKategorie` int(11) NOT NULL DEFAULT '0',
  `xMeeting` int(11) NOT NULL DEFAULT '0',
  `xVerein` int(11) NOT NULL DEFAULT '0',
  `xKategorie_svm` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xTeam`),
  UNIQUE KEY `MeetingKatName` (`xMeeting`,`xKategorie`,`Name`,`xKategorie_svm`),
  KEY `Name` (`Name`),
  KEY `xKategorie` (`xKategorie`),
  KEY `xVerein` (`xVerein`),
  KEY `xMeeting` (`xMeeting`),
  KEY `xKategorie_svm` (`xKategorie_svm`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `team` */

/*Table structure for table `teamsm` */

DROP TABLE IF EXISTS `teamsm`;

CREATE TABLE `teamsm` (
  `xTeamsm` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL DEFAULT '',
  `xKategorie` int(11) NOT NULL DEFAULT '0',
  `xVerein` int(11) NOT NULL DEFAULT '0',
  `xWettkampf` int(11) NOT NULL DEFAULT '0',
  `xMeeting` int(11) NOT NULL DEFAULT '0',
  `Startnummer` int(11) NOT NULL DEFAULT '0',
  `Gruppe` char(2) DEFAULT '',
  `Quali` int(11) NOT NULL DEFAULT '0',
  `Leistung` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xTeamsm`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `teamsm` */

/*Table structure for table `teamsmathlet` */

DROP TABLE IF EXISTS `teamsmathlet`;

CREATE TABLE `teamsmathlet` (
  `xTeamsm` int(11) NOT NULL DEFAULT '0',
  `xAnmeldung` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xTeamsm`,`xAnmeldung`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `teamsmathlet` */

/*Table structure for table `verein` */

DROP TABLE IF EXISTS `verein`;

CREATE TABLE `verein` (
  `xVerein` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL DEFAULT '',
  `Sortierwert` varchar(100) NOT NULL DEFAULT '0',
  `xCode` varchar(30) NOT NULL DEFAULT '',
  `Geloescht` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xVerein`),
  UNIQUE KEY `Name` (`Name`),
  KEY `Sortierwert` (`Sortierwert`),
  KEY `xCode` (`xCode`)
) ENGINE=MyISAM AUTO_INCREMENT=634 DEFAULT CHARSET=utf8;

/*Data for the table `verein` */

insert  into `verein`(`xVerein`,`Name`,`Sortierwert`,`xCode`,`Geloescht`) values 
(1,'LG Graubünden','Graubünden LG','1.LG.0043',0),
(2,'AthleticaVeveyse','VeveyseAthletica','1.FR.0333',0),
(3,'Dreiländer Lauf Basel','Basel Drei Länder Lauf','1.BSBL.0244',0),
(4,'Run Fit Thurgau','Thurgau Run Fit','1.TG.1427',0),
(5,'Verein City-Athletics Langenthal','Langenthal Verein Ctiy-Athletics','1.BE.0197',0),
(6,'Turnverein Lützelflüh-Goldbach','Lützelflüh-Goldbach Turnverein','1.BE.0201',0),
(7,'Association Nendaz Trail','Nendaz Association Trail','1.VS.1732',0),
(8,'ATLET (Association Team Léman Espoirs Triathlon)','ATLET (Association Team Léman Espoirs Triathlon)','1.VD.1638',0),
(9,'Association \"Supertrail du Barlatay\" Mémorial Franziska Rochat-Moser','Association \"Supertrail du Barlatay\" Mémorial Franziska Rochat-Moser','1.VD.1640',0),
(10,'FSG St-Imier','St-Imier FSG','1.BE.0202',0),
(11,'Outdoor Unlimited Sarl','Sarl Outdoor Unlimited','1.GE.0425',0),
(12,'Ingold Rönners Laufträff','Ingold Rönners Laufträff','1.BE.0200',0),
(13,'Vikmotion Event GmbH','Vikmotion Event GmbH','1.ILV.0768',0),
(14,'Club Les Trotteurs','Club Les Trotteurs','1.VS.1731',0),
(15,'Team Tempo-Sport','Team Tempo-Sport','1.ZH.1878',0),
(16,'Lauftreff Limmattal','Limmattal Lauftreff','1.ZH.1881',0),
(17,'FSG Châtillon','Châtillon FSG','1.JU.0831',0),
(18,'Verein GurtenClassic','GurtenClassic','1.BE.0182',0),
(19,'STV Uznach','Uznach STV','1.SGALV.1059',0),
(20,'TV Bürglen','Bürglen TV','1.ILV.0760',0),
(21,'FSG Collonge-Bellerive','Collonge-Bellerive FSG','1.GE.0419',0),
(22,'OK Basler Stadtlauf','Basler Stadtlauf OK','1.BSBL.0239',0),
(23,'Trailers Mt Blanc Suisse','Mt Blanc Suisse Trailers','1.VS.1725',0),
(24,'Verein Emmental Walking','Emmental Verein Walking','1.BE.0175',0),
(25,'Laufträff Büren an der Aare','Büren an der Aare Laufträff','1.BE.0177',0),
(26,'Le Demi de Jussy','Jussy Le Demi','1.GE.0420',0),
(27,'Verein für Sportveranstaltungen','Verein für Sportveranstaltungen','1.ILV.0757',0),
(28,'TV Dägerlen','Dägerlen TV','1.ZH.1882',0),
(29,'LGV 2015 Zug','Zug LGV 2015','1.ILV.0770',0),
(30,'TVM Buchsi-Athletics','Buchsi-Athletics TVM','1.BE.0203',0),
(31,'Amriswil-Athletics','Amriswil-Athletics','1.TG.1425',0),
(32,'Unitas Malcantone','Malcantone Unitas','1.TI.1534',0),
(33,'LG Bern','Bern LG','1.LG.0041',0),
(34,'TSV St. Antoni','St. Antoni TSV','1.FR.0335',0),
(35,'Verein Running Grindelwald','Grindelwald Verein Running','1.BE.0194',0),
(36,'Associaton Défi International Val-de-Travers','Val-de-Travers','1.NE.0911',0),
(37,'FG Malcantone','Malcantone FG','1.TI.1536',0),
(38,'LA Seerücken','Seerücken LA','1.TG.1426',0),
(39,'Sportverein carpediem','Sportverein carpediem','1.ILV.0763',0),
(40,'Markus Ryffel\'s GmbH','Markus Ryffel\'s GmbH','1.SLV.0009',0),
(41,'Verein Altstätter Städtlilauf','Altstätter Städtlilauf','1.SGALV.1056',0),
(42,'KTV Edelweiss Kriessern Aktive','Kriessern Edelweiss KTV','1.SGALV.1055',0),
(43,'Verein / OK  Kerzerslauf','Kerzerslauf Verein / OK','1.FR.0331',0),
(44,'LGKE Küsnacht-Erlenbach','Küsnacht-Erlenbach LGKE','1.ZH.1869',0),
(45,'Running Team Prilly','Prilly Running Team','1.VD.1631',0),
(46,'Laufsportverein Basel','Basel Laufsportverein','1.BSBL.0242',0),
(47,'FitSport.ch','FitSport.ch','1.ILV.0761',0),
(48,'TV Aarberg Leichtathletik','Aarberg TV Leichtathletik','1.BE.0184',0),
(49,'Gym Rolle','Rolle Gym','1.VD.1632',0),
(50,'Team des Alpes Leysin','Leysin Team des Alpes','1.VD.1636',0),
(51,'Turnverein Untervaz','TV Untervaz','1.GR.0617',1),
(52,'TV Walenstadt','Walenstadt TV','1.SGALV.1064',0),
(53,'Association pour la promotion de la course à pied en ville de Lausanne','Association pour la promotion de la course à pied en ville de Lausanne','1.VD.1635',0),
(54,'Verein Langenthaler Stadtlauf','Langenthaler Verein Stadtlauf','1.BE.0198',0),
(55,'OK Bärner Bärgloufcup','Bärner Bärgloufcup OK','1.BE.0199',0),
(56,'Association du Tour Pédestre du Canton de Genève','Genève Association du Tour Pédestre du Canton','1.GE.0424',0),
(57,'Laufbewegung Regio Basel','Basel Regio Laufbewegung','1.BSBL.0243',0),
(58,'Bieler Lauftag','Bieler Lauftage','1.BE.0186',0),
(59,'Satus Burgdorf','Burgdorf Satus','1.BE.0187',0),
(60,'TV Blumenstein','Blumenstein TV','1.BE.0196',0),
(61,'STV St. Margrethen','St. Maragrethen STV','1.SGALV.1057',0),
(62,'Laufgruppe Staufberg Staufen','Staufen Laufgruppe Staufberg','1.AG.0052',0),
(63,'Mediasprint','Mediasprint','1.MS',0),
(64,'Nachwuchsprojekte','Pseudoverein','1.NWP',0),
(65,'Biel/Bienne Athletics','Biel/Bienne Athletics','1.BE.0188',0),
(66,'T-R-T Athlétisme Monthey','Monthey TRT Athlétisme','1.VS.1727',0),
(67,'CA Dents-du-Midi','dents du midi Club d\'athlétisme','1.VS.1728',0),
(68,'Gym St-Aubin','St-Aubin Gym','1.FR.0332',0),
(69,'LG Züri+','Züri+ LG','1.LG.0042',0),
(70,'TV Otelfingen','Otelfingen TV','1.ZH.1871',0),
(71,'Club Athlétique Montreux','Montreux CA','1.VD.1629',0),
(72,'LC Bad Zurzach','Zurzach LC Bad','1.AG.0051',0),
(73,'BEO Runners','BEO Runners','1.BE.0178',0),
(74,'Verein Zürich Marathon','Zürich Marathon Verein','1.ZH.1870',0),
(75,'Gerbersport','Gerbersport','1.BE.0185',0),
(76,'LA Lungern','Lungern LA','1.ILV.0759',0),
(77,'LGRL Rüegsauschachen-Lützelflüh','Rüegsauschachen-Lützelflüh LGRL','1.BE.0180',0),
(78,'Zermatt Marathon','Zermatt Marathon','1.VS.1726',0),
(79,'TV St. Gallen-Ost','St. Gallen-Ost TV','1.SGALV.1060',0),
(80,'24 Stundenlauf Aare Insel Brugg','Brugg 24 Stundenlauf Aare Insel','1.AG.0050',0),
(81,'Polysport Vully','Vully Polysport','1.FR.0334',1),
(82,'Sihltaler Sportclub','Sihltaler Sportclub','1.ZH.1876',0),
(83,'Croc-Kil...Leysin','Leysin Croc-Kil...','1.VD.1633',0),
(84,'Association Fyne Terra Marathon','Association Fyne Terra Marathon','1.VD.1634',0),
(85,'SSC Riehen','Riehen SSC','1.BSBL.0245',0),
(86,'Lauf-Treff Buchs SG','Lauf-Treff Buchs SG','1.SGALV.1062',0),
(87,'Laufträff Birsegg Aesch','Birsegg Aesch Laufträff','1.BSBL.0246',0),
(88,'AS Monteceneri','Monteceneri AS','1.TI.1537',0),
(89,'Verein Greifenseelauf','Greifenseelauf Verein','1.ZH.1877',0),
(90,'Verein Sempacherseelauf','Sempacherseelauf Verein','1.ILV.0766',0),
(91,'Club Sportiv d\'Engiadina Bassa','Engiadina Bassa Club Sportiv','1.GR.0615',0),
(92,'Schneesport Club Wiedlisbach','Wiedlisbach Schneesport Club','1.BE.0190',0),
(93,'runningtrainer.ch','runningtrainer.ch','1.BE.0192',0),
(94,'Sportgruppe UOV Wiedlisbach','Wiedlisbach Sportgruppe UOV','1.BE.0191',0),
(95,'Grand-Prix von Bern','Bern Grand-Prix','1.BE.0193',0),
(96,'TV Thusis','Thusis TV','1.GR.0616',0),
(97,'Società Podistica Locarnese','Locarnese Società Podistica','1.TI.1538',0),
(98,'LR Nottwil','Nottwil LR','1.ILV.0769',0),
(99,'LC Rafzerfeld','Rafzerfeld LC','1.ZH.1880',0),
(100,'PLUSPORT Behindertensport Schweiz','PLUSPORT Behindertensport Schweiz','1.SLV.0008',0),
(101,'TSV Engelburg','Engelburg TSV','1.SGALV.1054',0),
(102,'Verein Tüfelsschluchtlauf Hägendorf VTH','Hägendorf Verein Tüfelsschluchtlauf VTH','1.SO.1224',0),
(103,'FSG Jonquille','La Jonquille FSG','1.GE.0423',0),
(104,'STV Unterägeri Leichtathletik','Unterägeri STV','1.ILV.0765',0),
(105,'UBS Kids Cup Zentrale','UBS Kids Cup Zentrale','1.UBS',0),
(106,'UBS Kids Cup','UBS Kids Cup','1.UBSKIDSCUP',0),
(107,'Laufgruppe Cham','Cham Laufgruppe','1.ILV.0767',0),
(108,'Bewegungscoaching Laufgruppen','Bewegungscoaching Laufgruppen','1.BSBL.0249',0),
(109,'TV Schänis','Schänis TV','1.SGALV.1066',0),
(110,'VullyRun','VullyRun','1.VD.1637',0),
(111,'Verein Zürcher Silvesterlauf TV Unterstrass','SILA-TVU','1.ZH.1879',0),
(112,'Jungfrau-Marathon','Jungfrau-Marathon','1.BE.0195',0),
(113,'IMG (Schweiz) AG','IMG (Schweiz) AG','1.SLV.0010',1),
(114,'STV Ganterschwil','Ganterschwil STV','1.SGALV.1063',0),
(115,'Basel Dragons running club','Basel Dragons running club','1.BSBL.0247',0),
(116,'Verein Basel Marathon','Basel Marathon Verein','1.BSBL.0248',0),
(117,'Versoix Athlétisme','Versoix Athlétisme','1.GE.0418',0),
(118,'Sportplausch.ch','Sportplausch.ch','1.SZ.1324',0),
(119,'SATUS Biel-Stadt','Biel-Stadt SATUS','1.BE.0168',0),
(120,'LV Wettingen-Baden','LVWB','1.AG.0049',0),
(121,'TV Grosswangen','Grosswangen TV','1.ILV.0758',0),
(122,'Verein Lauftreff beider Basel','Basel beider Verein Lauftreff','1.BSBL.0241',0),
(123,'Comacina Atleti.eu','Comacina Atleti.eu','1.TI.1533',0),
(124,'Verein Glarner Stadtlauf','Glarner Stadtlauf Verein','1.GL.0502',0),
(125,'6WEEKS','6WEEKS','1.ILV.0764',0),
(126,'Ski- und Sportclub Eglisau','Eglisau Ski- und Sportclub','1.ZH.1875',0),
(127,'Comitato Stralugano','Stralugano Comitato','1.TI.1535',0),
(128,'Chêne Gymnastique Genève','Genève Chêne Gymnastique','1.GE.0422',0),
(129,'CoA Pierre-Pertuis','Pierre-Pertuis CoA','1.LG.0039',0),
(130,'IG Laufveranstaltungen Lenzerheide','Lenzerheide IG Laufveranstaltugen','1.GR.0613',1),
(131,'LG Basel Regio','Basel Regio LG','1.LG.0040',0),
(132,'TSV Fortitudo Gossau','Gossau Foritudo TSV','1.SGALV.1058',0),
(133,'Verein Steinhölzlilauf','Steinhölzlilauf Verein','1.BE.0181',0),
(134,'Appenzellischer Turnverband','ATV','1.KLV.ARAI',0),
(135,'LG LZ Oberaargau','Oberaargau LZ LG','1.LG.0038',0),
(136,'TEP Organisation','TEP Organisation','1.FR.0337',0),
(137,'TV Buchberg-Rüdlingen','Buchberg-Rüdlingen TV','1.SH.1109',0),
(138,'Triviera','Triviera','1.VD.1639',0),
(139,'Luzerner Stadtlauf','Luzerner Stadtlauf','1.ILV.0762',0),
(140,'kein Verein','kein Verein','101939',0),
(141,'Trilogie Running Team','Trilogie Running Team','1.FR.0336',0),
(142,'Association Les Trailers Verbier St-Bernard','Verbier St-Bernard Association Les Trailers','1.VS.1730',0),
(143,'FSG La Sarraz','La Sarraz SFG','1.VD.1628',0),
(144,'Fun and Run Thun','Thun Fun and Run','1.BE.0183',0),
(145,'LA Hüntwangen','Hüntwangen LAR TV','1.ZH.1873',0),
(146,'LG Liechtenstein','Liechtenstein LG','1.LG.0046',0),
(147,'LG Oberwallis','Oberwallis LG','1.LG.0045',0),
(148,'Swiss Athletics','Swiss Athletics','1',0),
(149,'Aargauischer Leichtathletikverband','ALV','1.KLV.AG',0),
(150,'TV Stein (AG)','Stein (AG) TV','1.AG.0002',0),
(151,'TV Zofingen LA','Zofingen LA TV','1.AG.0003',0),
(152,'SATUS Rothrist LA','Rothrist SATUS LA','1.AG.0004',0),
(153,'TV Rothrist','Rothrist TV','1.AG.0005',0),
(154,'BTV Aarau Athletics','Aarau Atheltics BTV','1.AG.0006',0),
(155,'TV Buchs AG','Buchs AG TV','1.AG.0007',0),
(156,'LAR Satus Oberentfelden','Oberentfelden SATUS','1.AG.0008',0),
(157,'LV Fricktal','Fricktal LV','1.AG.0009',0),
(158,'LAR TV Windisch','Windisch LAR-TV','1.AG.0012',0),
(159,'Laufsportgruppe Brugg','Brugg LSG','1.AG.0013',0),
(160,'Vom Stein Baden','Baden vom Stein','1.AG.0014',0),
(161,'Läufergruppe Horn, Gebenstorf-Turgi','Horn LG','1.AG.0015',0),
(162,'Sri Chinmoy Marathon Team','Sri Chinmoy Marathon Team','1.ZH.1864',0),
(163,'TV Lenzburg','Lenzburg TV','1.AG.0028',0),
(164,'LR Wohlen','Wohlen LR','1.AG.0029',0),
(165,'TV Wohlen AG','Wohlen (AG) TV','1.AG.0030',0),
(166,'LA Villmergen','Villmergen LA','1.AG.0031',0),
(167,'STV Büttikon','Büttikon STV','1.AG.0032',0),
(168,'STV Beinwil/Freiamt','Beinwil/Freiamt STV','1.AG.0033',0),
(169,'STV Auw','Auw LAG','1.AG.0034',0),
(170,'Schweiz. Leichtathletikver. der Behinderten','SLVB','1.SLV.0005',0),
(171,'Schulsport Seengen','Seengen Schulsport','1.AG.0036',0),
(172,'SATUS Gränichen','Gränichen SATUS','1.AG.0037',0),
(173,'STV Gränichen LA','Gränichen STV','1.AG.0038',0),
(174,'STV Mühlau','Mühlau STV','1.AG.0044',0),
(175,'Berner Leichtathletik-Verband','BLV','1.KLV.BE',0),
(176,'STV Biel','Biel STV','1.BE.0105',0),
(177,'FSG La Neuveville','Neuveville FSG','1.BE.0107',0),
(178,'CA Courtelary','Courtelary CA','1.BE.0109',0),
(179,'GS Malleray-Bévilard','Malleray-Bévilard GS','1.JU.0832',0),
(180,'CA Moutier','Moutier CA','1.BE.0112',0),
(181,'SGSV-FSSS Leichtathletik','SGSV-FSSS Leichtathletik','1.SLV.0006',0),
(182,'SFG \"Le Cornet\" Crémines','Crémines SFG \"Le Cornet\"','1.BE.0114',1),
(183,'GG Bern','Bern GGB','1.BE.0115',0),
(184,'STB Leichtathletik','Bern STB LA','1.BE.0116',0),
(185,'TV Länggasse Bern','Bern Länggasse TV','1.BE.0117',0),
(186,'Vereinigung Freunde der Leichtathletik des Berner Leichtathletik-Verbandes','Freunde der Leichtathletik','1.SLV.0012',0),
(187,'swiss masters athletics','swiss masters athletics','1.SLV.0002',0),
(188,'LAG TV Zollikofen','Zollikofen TV-LAG','1.BE.0121',0),
(189,'TV Bolligen','Bolligen TV','1.BE.0123',0),
(190,'TV Ostermundigen','Ostermundigen TV','1.BE.0125',0),
(191,'TV Münsingen','Münsingen TV','1.BE.0127',1),
(192,'TV Schwarzenburg','Schwarzenburg TV','1.BE.0128',0),
(193,'TV Oberwangen','Oberwangen TV','1.BE.0129',0),
(194,'TSV Frauenkappelen','Frauenkappelen TSV','1.BE.0130',0),
(195,'LAC Wohlen','Wohlen LAC','1.BE.0132',0),
(196,'TV Lyss','Lyss TV','1.BE.0133',0),
(197,'TV Fraubrunnen','Fraubrunnen TV','1.BE.0136',0),
(198,'TV Herzogenbuchsee','Herzogenbuchsee TV','1.BE.0137',0),
(199,'TV Rüegsauschachen','Rüegsauschachen TV','1.BE.0138',0),
(200,'LC Kirchberg','Kirchberg LC','1.BE.0142',0),
(201,'TV Sumiswald','Sumiswald TV','1.BE.0144',0),
(202,'TV Grosshöchstetten','Grosshöchstetten TV','1.BE.0145',0),
(203,'TV Konolfingen Athletics','Konolfingen TV','1.BE.0146',0),
(204,'SK Langnau','Langnau SK','1.BE.0147',0),
(205,'TV Trubschachen','Trubschachen TV','1.BE.0148',0),
(206,'All Blacks Thun','Thun All-Blacks','1.BE.0152',0),
(207,'LV Thun','Thun LV','1.BE.0154',0),
(208,'TV Spiez','Spiez TV','1.BE.0156',0),
(209,'LC Scharnachtal','Scharnachtal LC','1.BE.0157',0),
(210,'TV Saanen-Gstaad','Saanen-Gstaad TV','1.BE.0158',0),
(211,'TV Unterseen','Unterseen TV','1.BE.0159',0),
(212,'TV Meiringen','Meiringen TV','1.BE.0160',0),
(213,'LV Langenthal','Langenthal LV','1.BE.0161',0),
(214,'TV Aeschi','Aeschi TV','1.BE.0162',0),
(215,'LV Huttwil','Huttwil LV','1.BE.0163',0),
(216,'STV Attiswil (Leichtathletikriege)','Attiswil LA-STV','1.BE.0166',0),
(217,'UOV Burgdorf - Läufergruppe','Burgdorf UOV','1.BE.0167',0),
(218,'LAV beider Basel','LABB','1.KLV.BSBL',0),
(219,'LC Fortuna Oberbaselbiet','Oberbaselbiet LC Fortuna','1.BSBL.0201',0),
(220,'LAS Old Boys Basel','Basel OB','1.BSBL.0202',0),
(221,'LC Basel','Basel LC','1.BSBL.0203',0),
(222,'LAR Binningen','Binningen LAR','1.BSBL.0204',0),
(223,'TV Bottmingen','Bottmingen TV','1.BSBL.0205',0),
(224,'Sportclub Biel-Benken','Biel-Benken SC','1.BSBL.0206',0),
(225,'LC Therwil','Therwil LC','1.BSBL.0207',0),
(226,'TV Riehen','Riehen TV','1.BSBL.0209',0),
(227,'TV Muttenz athletics','Muttenz TV','1.BSBL.0211',0),
(228,'TV Arlesheim','Arlesheim TV','1.BSBL.0213',0),
(229,'TV Zwingen','Zwingen TV','1.BSBL.0214',0),
(230,'SC Liestal','Liestal SC','1.BSBL.0216',0),
(231,'TV Bubendorf','Bubendorf TV','1.BSBL.0217',0),
(232,'LV Frenke','Frenke LV','1.BSBL.0218',0),
(233,'TV Läufelfingen','Läufelfingen TV','1.BSBL.0219',0),
(234,'TV Aesch','Aesch TV','1.BSBL.0225',0),
(235,'Fédération Fribourgeoise d\'Athlétisme','FFA','1.KLV.FR',0),
(236,'FSG Estavayer-le-Lac','Estavayer-Lully FSG','1.FR.0301',0),
(237,'SA Bulle','Bulle SA','1.FR.0303',0),
(238,'CS Marsens','Marsens CS','1.FR.0304',0),
(239,'FSG Gym Hommes Broc','Broc FSG','1.FR.0305',0),
(240,'CS Neirivue','Neirivue CS','1.FR.0306',0),
(241,'CARC Romont','Romont-Condémina CA','1.FR.0307',0),
(242,'CA Fribourg','Fribourg CA','1.FR.0308',0),
(243,'LAT Sense (Lauf und Athletikteam)','Sense LAT','1.FR.0309',0),
(244,'TSV Rechthalten','Rechthalten TSV','1.FR.0310',0),
(245,'TSV Heitenried','Heitenried TSV','1.FR.0311',0),
(246,'TV Alterswil','Alterswil TV','1.FR.0312',0),
(247,'CA Marly','Marly CA','1.FR.0313',0),
(248,'CS Le Mouret','Mouret, Le CS','1.FR.0314',0),
(249,'CA Gibloux Farvagny','Farvagny CA Gibloux','1.FR.0315',0),
(250,'CA Belfaux','Belfaux CA','1.FR.0316',0),
(251,'CA Rosé','Rosé CA','1.FR.0317',0),
(252,'TV Bösingen','Bösingen TV','1.FR.0319',0),
(253,'TV Wünnewil','Wünnewil TV','1.FR.0320',0),
(254,'TSV Düdingen','Düdingen TSV','1.FR.0321',0),
(255,'TSV Gurmels','Gurmels TSV','1.FR.0322',0),
(256,'AC Murten','Murten AC','1.FR.0323',0),
(257,'TV Murten','Murten TV','1.FR.0324',0),
(258,'LA Plaffeien','Plaffeien LA','1.FR.0325',0),
(259,'COA Fribourg-Romand','Fribourg-Romand COA','1.LG.0009',0),
(260,'TSV Kerzers','Kerzers TSV','1.FR.0329',0),
(261,'Association Genevoise d`Athlétisme','AGA','1.KLV.GE',0),
(262,'Athlétisme Viseu-Genève','Genève Athlétisme Viseu','1.GE.0401',0),
(263,'CA Genève','Genève CA','1.GE.0402',0),
(264,'C.H. de Plainpalais','Plainpalais C.H.','1.GE.0403',0),
(265,'Stade Genève','Genève Stade','1.GE.0405',0),
(266,'UGS-Athlétisme','UGS-Athlétisme','1.GE.0406',1),
(267,'FSG Meyrin','Meyrin FSG','1.GE.0408',0),
(268,'C.H. Châtelaine','Châtelaine C.H.','1.GE.0411',0),
(269,'SATUS Athl. Genève','Genève SATUS Athl.','1.GE.0412',0),
(270,'FSG Bernex-Confignon','Bernex-Confignon FSG','1.GE.0414',0),
(271,'Société de gymnastique de Jussy','Jussy société de gymnastique','1.GE.0415',0),
(272,'FSG Versoix','Versoix FSG','1.GE.0416',0),
(273,'COA Petit-Léman','Petit-Léman COA','1.LG.0012',0),
(274,'Glarner Leichtathletikverband GLAV','GLAV','1.KLV.GL',0),
(275,'LAV Glarus','Glarus LAV','1.GL.0501',0),
(276,'KLV Graubünden','Graubünden KLV','1.KLV.GR',0),
(277,'BTV Chur Leichtathletik','Chur BTV','1.GR.0602',0),
(278,'Track Club Davos','Davos Track-Club','1.GR.0606',0),
(279,'AJ TV Landquart','Landquart AJ TV','1.GR.0607',0),
(280,'Innerschweizer Leichtathletik Verband','ILV','1.KLV.ILV',0),
(281,'LC Luzern','Luzern LC','1.ILV.0701',0),
(282,'TV Reussbühl LA','Reussbühl LA TV','1.ILV.0703',0),
(283,'STV Ruswil','Ruswil STV','1.ILV.0704',0),
(284,'LC Emmenstrand','Emmenstrand LC','1.ILV.0705',0),
(285,'TSV Rothenburg athletics','Rothenburg TSV','1.ILV.0707',0),
(286,'TV Inwil','Inwil TV','1.ILV.0708',0),
(287,'LR Ebikon','Ebikon LR','1.ILV.0709',0),
(288,'LV Horw','Horw LV','1.ILV.0710',0),
(289,'STV Alpnach, LAGr','Alpnach STV LAGr','1.ILV.0712',0),
(290,'TV Sarnen LA','Sarnen TV','1.ILV.0713',0),
(291,'STV Malters','Malters STV','1.ILV.0714',0),
(292,'TV Wolhusen','Wolhusen TV','1.ILV.0715',0),
(293,'STV Willisau','Willisau STV','1.ILV.0716',0),
(294,'LR Gettnau','Gettnau LR','1.ILV.0717',0),
(295,'STV Altbüron','Altbüron STV','1.ILV.0718',0),
(296,'KTV Neuenkirch LR','Neuenkirch KTV','1.ILV.0720',0),
(297,'TSV Oberkirch','Oberkirch TSV','1.ILV.0721',0),
(298,'TV Sursee','Sursee TV','1.ILV.0722',0),
(299,'STV Ettiswil','Ettiswil STV','1.ILV.0723',1),
(300,'STV Beromünster','Beromünster STV','1.ILV.0724',0),
(301,'STV-LA Roggliswil','Roggliswil STV-LA','1.ILV.0728',0),
(302,'STV Ballwil','Ballwil STV','1.ILV.0730',0),
(303,'AUDACIA Leichtathletik','AUDACIA Leichtathletik','1.ILV.0731',0),
(304,'LAR STV Hitzkirch','Hitzkirch STV','1.ILV.0732',0),
(305,'LK Zug','Zug LK','1.ILV.0733',0),
(306,'Hochwacht Zug','Zug Hochwacht','1.ILV.0734',0),
(307,'TV Cham 1884','Cham 1884 TV','1.ILV.0737',0),
(308,'STV Oberägeri','Oberägeri STV','1.ILV.0738',0),
(309,'TSV 2001 Rotkreuz','Rotkreuz TSV-2001','1.ILV.0740',0),
(310,'LA Nidwalden','Nidwalden LA','1.ILV.0741',0),
(311,'LC Altdorf','Altdorf LC','1.ILV.0743',0),
(312,'LA TV Erstfeld','Erstfeld LA-TV','1.ILV.0745',0),
(313,'LG Unterwalden','Unterwalden LG','1.LG.0016',0),
(314,'LG Uri','Uri LG','1.LG.0017',0),
(315,'LG Nordstar Luzern','Nordstar Luzern LG','1.LG.0018',0),
(316,'STV Nebikon','Nebikon STV','1.ILV.0750',0),
(317,'Leichtathletik Kerns','Kerns LA','1.ILV.0753',0),
(318,'Association Jurassienne d`Athlétisme','AJA','1.KLV.JU',0),
(319,'GS Franches-Montagnes','Franches-Montagnes GS','1.JU.0801',0),
(320,'FSG Reconvilier','Reconvilier FSG','1.JU.0805',0),
(321,'CA Delémont','Delémont CA','1.JU.0807',0),
(322,'FSG Delémont','Delémont FSG','1.JU.0808',0),
(323,'FSG Courroux','Courroux FSG','1.JU.0809',0),
(324,'Femina Vicques','Vicques Femina','1.JU.0810',0),
(325,'FSG Vicques','Vicques FSG','1.JU.0811',0),
(326,'FSG Bassecourt','Bassecourt FSG','1.JU.0814',0),
(327,'GS Tabeillon','Tabeillon GS','1.JU.0815',0),
(328,'FSG Alle','Alle FSG','1.JU.0817',0),
(329,'CA Fontenais','Fontenais CA','1.JU.0820',0),
(330,'FSG Courgenay','Courgenay FSG','1.JU.0822',0),
(331,'COA Ajoie','Ajoie COA','1.LG.0013',0),
(332,'COA Delémont','Delémont COA','1.LG.0014',0),
(333,'CA des Franches-Montagnes','Franches-Montagnes CA','1.JU.0830',0),
(334,'FSG Malleray-Bévilard','Malleray-Bévilard FSG','1.JU.0829',0),
(335,'Association Neuchâteloise d\'athlétisme','ANA','1.KLV.NE',0),
(336,'CEP Cortaillod','Cortaillod CEP','1.NE.0901',0),
(337,'FSG Bevaix','Bevaix FSG','1.NE.0902',0),
(338,'FSG Corcelles-Cormondrèche','Corcelles FSG','1.NE.0903',0),
(339,'FSG Couvet','Couvet FSG','1.NE.0904',0),
(340,'FSG Geneveys et Coffrane','Geneveys et Coffrane FSG','1.NE.0906',0),
(341,'SEP Olympic La Chaux-de-Fonds','SEP Olympic ChdF','1.NE.0907',0),
(342,'FSG Le Locle','Locle, Le FSG','1.NE.0908',0),
(343,'GA Neuchâtelois','Neuchâtelois GA','1.LG.0020',0),
(344,'Cressier-Chaumont','Cressier-Chaumont','1.NE.0910',0),
(345,'TV Bad Ragaz','Bad Ragaz TV','1.SGALV.1001',0),
(346,'SC Diemberg','Diemberg SC','1.SGALV.1002',0),
(347,'LAG Gossau','Gossau LAG','1.SGALV.1003',0),
(348,'LC Rapperswil-Jona','Rapperswil-Jona LC','1.SGALV.1004',0),
(349,'STV Eschenbach SG','Eschenbach SG STV','1.SGALV.1006',0),
(350,'Läuferriege Walenstadt','Walenstadt LR','1.SGALV.1007',0),
(351,'TV Mels','Mels TV','1.SGALV.1008',0),
(352,'LA Speicher','Speicher LA','1.ARAI.1009',0),
(353,'LR TV Appenzell','Appenzell TV','1.ARAI.1010',0),
(354,'TV Teufen Leichtathletik','Teufen TV','1.ARAI.1011',0),
(355,'TV Herisau','Herisau TV','1.ARAI.1013',0),
(356,'LGTV Flawil','Flawil TV Jugi','1.SGALV.1014',0),
(357,'STV Lütisburg','Lütisburg STV','1.SGALV.1015',0),
(358,'LC Uzwil','Uzwil LC','1.SGALV.1016',0),
(359,'LGB Bodensee','Bodensee LGB','1.SGALV.1018',0),
(360,'TV Thal','Thal TV','1.SGALV.1019',0),
(361,'STV Au SG','Au SG  STV','1.SGALV.1020',0),
(362,'STV Widnau','Widnau STV','1.SGALV.1021',0),
(363,'Athleticteam KTV Altstätten','Altstätten Athleticteam KTV','1.SGALV.1023',0),
(364,'STV Balgach','Balgach STV','1.SGALV.1024',0),
(365,'KTV Oberriet','Oberriet KTV','1.SGALV.1025',0),
(366,'STV Oberriet-Eichenwies','Oberriet-Eichenwies STV','1.SGALV.1026',0),
(367,'TV Rüthi','Rüthi TV','1.SGALV.1027',0),
(368,'TV Buchs SG','Buchs SG TV','1.SGALV.1028',0),
(369,'STV Gams','Gams STV','1.SGALV.1029',0),
(370,'Ski- Bergclub Gauschla','Gauschla Ski- Bergclub','1.SGALV.1030',0),
(371,'LC Vaduz','Vaduz LC','1.SGALV.1031',0),
(372,'TV Eschen-Mauren','Eschen-Mauren TV','1.SGALV.1032',0),
(373,'LC Schaan','Schaan LC','1.SGALV.1033',0),
(374,'TV Schaan / Leichtathletik','Schaan TV','1.SGALV.1034',0),
(375,'Turnverein Triesen','Triesen TV','1.SGALV.1035',0),
(376,'KTV Wil LA','Wil KTV','1.SGALV.1036',0),
(377,'KTV Bütschwil','Bütschwil KTV','1.SGALV.1040',0),
(378,'LC Brühl Leichtathletik','St.Gallen LC Brühl','1.SGALV.1041',0),
(379,'LG Fürstenland','Fürstenland LG','1.LG.0021',0),
(380,'LG Obersee','Obersee LG','1.LG.0023',0),
(381,'LGB Benken','Benken LGB','1.SGALV.1047',0),
(382,'LG Rheintal','Rheintal LG','1.LG.0024',0),
(383,'TV St. Peterzell','St. Peterzell TV','1.SGALV.1050',0),
(384,'Schaffhauser KLV','SKLV','1.KLV.SH',0),
(385,'LC Schaffhausen','Schaffhausen LC','1.SH.1101',0),
(386,'OK Staaner Stadtlauf','Staaner Stadtlauf OK','1.SH.1104',0),
(387,'Turne Schlaate','Schlaate Turne','1.SH.1108',0),
(388,'KLV Solothurn','Solothurn KLV','1.KLV.SO',0),
(389,'TV Grenchen','Grenchen TV','1.SO.1201',0),
(390,'Biberist aktiv! Leichtathletik','Biberist aktiv! Leichtathletik','1.SO.1202',0),
(391,'STV Bettlach','Bettlach STV','1.SO.1203',0),
(392,'STV Selzach','Selzach STV','1.SO.1204',0),
(393,'LZ Thierstein','Thierstein LZ','1.SO.1205',0),
(394,'TV Luterbach','Luterbach TV','1.SO.1207',0),
(395,'TV Biezwil','Biezwil TV','1.SO.1208',0),
(396,'TV Olten','Olten TV','1.SO.1209',0),
(397,'STV Gunzgen','Gunzgen STV','1.SO.1210',0),
(398,'LA TV Wolfwil','Wolfwil LA-TV','1.SO.1212',0),
(399,'LZ Lostorf','Lostorf LZ','1.SO.1213',0),
(400,'TSV Kestenholz','Kestenholz TSV','1.SO.1215',0),
(401,'TV Balsthal','Balsthal TV','1.SO.1216',0),
(402,'STV Welschenrohr','Welschenrohr STV','1.SO.1217',0),
(403,'TV Gretzenbach','Gretzenbach TV','1.SO.1218',0),
(404,'LG Solothurn WEST','Solothurn WEST LG','1.LG.0025',0),
(405,'LVS Schwyz','Schwyz LVS','1.KLV.SZ',0),
(406,'KTV Muotathal','Muotathal KTV','1.SZ.1301',0),
(407,'STV Küssnacht','Küssnacht STV','1.SZ.1302',0),
(408,'TSV Steinen','Steinen TSV','1.SZ.1303',0),
(409,'TV Ibach','Ibach TV','1.SZ.1305',0),
(410,'TV Brunnen','Brunnen TV','1.SZ.1306',0),
(411,'STV Gersau','Gersau STV','1.SZ.1307',0),
(412,'KTV Freienbach','Freienbach KTV','1.SZ.1308',0),
(413,'STV Pfäffikon-Freienbach','Pfäffikon-Freienbach STV','1.SZ.1309',0),
(414,'STV Wollerau-Bäch','Wollerau-Bäch STV','1.SZ.1310',0),
(415,'ETV Schindellegi','Schindellegi ETV','1.SZ.1311',0),
(416,'STV Einsiedeln','Einsiedeln STV','1.SZ.1312',0),
(417,'KTV Altendorf','Altendorf KTV','1.SZ.1313',0),
(418,'Turnverein Siebnen','Siebnen TV','1.SZ.1314',0),
(419,'TSV Galgenen','Galgenen TSV','1.SZ.1315',0),
(420,'STV Wangen SZ','Wangen SZ STV','1.SZ.1316',0),
(421,'STV Lachen','Lachen STV','1.SZ.1317',0),
(422,'STV Tuggen','Tuggen STV','1.SZ.1318',0),
(423,'STV Wägital','Wägital STV','1.SZ.1319',0),
(424,'TV Buttikon-Schübelbach','Buttikon-SchübelbachTV','1.SZ.1321',0),
(425,'STV Reichenburg','Reichenburg STV','1.SZ.1322',0),
(426,'LG Innerschwyz','Innerschwyz LG','1.LG.0026',0),
(427,'Thurgauer Leichtathletik-Verband','TLAV','1.KLV.TG',0),
(428,'LAR Tägerwilen-Kreuzlingen','Tägerwilen-Kreuzlingen LAR','1.TG.1401',0),
(429,'TV Aadorf','Aadorf TV','1.TG.1403',0),
(430,'TSV Guntershausen','Guntershausen TSV','1.TG.1404',0),
(431,'KTV Frauenfeld','Frauenfeld KTV','1.TG.1405',0),
(432,'TV Gachnang-Islikon','Gachnang-Islikon TV','1.TG.1407',0),
(433,'LAR TV Weinfelden','Weinfelden LAR-TV','1.TG.1409',0),
(434,'STV Illhart-Sonterswil','Illhart-Sonterswil STV','1.TG.1410',0),
(435,'STV Berg','Berg STV','1.TG.1412',0),
(436,'LG SBW-NET Oberthurgau','Oberthurgau LG','1.LG.0027',0),
(437,'TV Zihlschlacht','Zihlschlacht TV','1.TG.1414',0),
(438,'LAR Bischofszell','Bischofszell LAR','1.TG.1415',0),
(439,'STV Güttingen','Güttingen STV','1.TG.1417',0),
(440,'LC Bottighofen','Bottighofen LC','1.TG.1418',0),
(441,'STV Neukirch-Egnach','Neukirch-Egnach STV','1.TG.1419',0),
(442,'LC Frauenfeld','Frauenfeld LC','1.TG.1420',0),
(443,'LAR Matzingen','Matzingen LAR','1.TG.1421',0),
(444,'Federazione Ticinese di Atletica Leggera','FTAL','1.KLV.TI',0),
(445,'GAB Bellinzona','Bellinzona GAB','1.TI.1501',0),
(446,'SA Bellinzona','Bellinzona SA','1.TI.1502',0),
(447,'Atletica Tenero 90','Tenero Atletica 90','1.TI.1503',0),
(448,'Comunità atletica Tre Valli','Comunità atletica Tre Valli','1.LG.0028',0),
(449,'Vis Nova Agarone','Agarone Vis-Nova','1.TI.1506',0),
(450,'SAG Gordola','Gordola SAG','1.TI.1507',0),
(451,'VIRTUS Locarno','Locarno VIRTUS','1.TI.1508',0),
(452,'US Ascona','Ascona US','1.TI.1509',0),
(453,'SFG Brissago','Brissago FSG','1.TI.1510',0),
(454,'SFG Biasca','Biasca SFG','1.TI.1511',0),
(455,'SFG Airolo','Airolo SA SFG','1.TI.1512',0),
(456,'ASSPO Riva San Vitale','Riva San Vitale','1.TI.1514',0),
(457,'Atletica Mendrisiotto','Mendrisiotto Atletica','1.TI.1515',0),
(458,'SFG Chiasso','Chiasso SFG','1.TI.1516',0),
(459,'SAV Vacallo','Vacallo SAV','1.TI.1517',0),
(460,'SFG Morbio Inferiore','Morbio Inferiore SFG','1.TI.1518',0),
(461,'Società Sportiva Valle di Muggio','Muggio Società Sportiva Valle','1.TI.1519',0),
(462,'SFG Mendrisio','Mendrisio SFG','1.TI.1520',0),
(463,'VIGOR Ligornetto','Ligornetto VIGOR','1.TI.1521',0),
(464,'SA Massagno','Massagno SA','1.TI.1524',0),
(465,'SAL Lugano','Lugano SAL','1.TI.1526',0),
(466,'Fédération Suisse de Marche','Fédération Suisse de Marche','1.SLV.0004',0),
(467,'USC Capriaschese-Atletica','Capriachese USC','1.TI.1529',0),
(468,'SAL Lugano sezione Marcia','Lugano Marcia SA','1.TI.1530',0),
(469,'GAD Dongio','Dongio GAD','1.TI.1532',0),
(470,'swiss masters running','smrun','1.SLV.0001',0),
(471,'SUPPORTER Swiss Athletics','SUPPORTER Swiss Athletics','1.SLV.0003',0),
(472,'Association Cantonale Vaudoise d’Athlétisme','ACVA','1.KLV.VD',0),
(473,'Stade Lausanne Athlétisme','Lausanne Stade','1.VD.1601',0),
(474,'Lausanne-Sports Athlétisme','Lausanne-Sports','1.VD.1602',0),
(475,'CM Cour Lausanne','Lausanne CM-Cour','1.VD.1603',0),
(476,'FSG Renens','Renens FSG','1.VD.1604',0),
(477,'FSG St-Cierges','St-Cierges FSG','1.VD.1605',0),
(478,'FSG Epalinges','Epalinges FSG','1.VD.1606',0),
(479,'Footing-Club Lausanne','Lausanne FC','1.VD.1607',0),
(480,'COVA Nyon','Nyon COVA','1.VD.1611',0),
(481,'US Yverdon','Yverdon US','1.VD.1612',0),
(482,'CM Yverdon','Yverdon CM','1.VD.1613',0),
(483,'CA Broyard','Broyard CA','1.VD.1614',0),
(484,'FSG Avenches','Avenches FSG','1.VD.1615',0),
(485,'CA Riviera','Riviera CA','1.VD.1616',0),
(486,'FSG Chailly-Montreux','Chailly-Montreux F.S.G.','1.VD.1617',0),
(487,'CA Aiglon','Aiglon CA','1.VD.1618',0),
(488,'COA Lausanne-Riviera','Lausanne-Riviera COA','1.LG.0030',0),
(489,'COA Broye-Nord-Vaudois','Broye-Nord Vaudois COA','1.LG.0031',0),
(490,'FSG Morges','Morges FSG','1.VD.1622',0),
(491,'CM Ecureuils / La Poste','Tour-de-Peilz CM','1.VD.1623',0),
(492,'AthleticaOron','Oron Athletlica','1.VD.1624',0),
(493,'Ecole nouvelle de la Suisse Romande','ENSR','1.VD.1625',0),
(494,'Fédération Valaisanne d`Athlétisme','FVA','1.KLV.VS',0),
(495,'Club de Marche Monthey','Monthey CM','1.VS.1701',0),
(496,'SG St-Maurice','St-Maurice SG','1.VS.1702',0),
(497,'SFG Collombey-Muraz','Collombey-Muraz SFG','1.VS.1703',0),
(498,'CA Vouvry','Vouvry CA','1.VS.1704',0),
(499,'CABV Martigny','Martigny CABV','1.VS.1706',0),
(500,'CS 13 Etoiles','Sion 13 Etoiles','1.VS.1708',0),
(501,'SFG Conthey','Conthey SFG','1.VS.1709',0),
(502,'CA Sion','Sion CA','1.VS.1710',0),
(503,'CA Vétroz','Vétroz CA','1.VS.1713',0),
(504,'ES Ayent Anzère','Ayent Anzère ES','1.VS.1714',0),
(505,'LFT Oberwallis','Oberwallis LFT','1.VS.1716',0),
(506,'TV Naters','Naters TV','1.VS.1717',0),
(507,'LV Visp','Visp LV','1.VS.1719',0),
(508,'STV Gampel','Gampel STV','1.VS.1720',0),
(509,'CA Sierre DSG','Sierre DSG CA','1.VS.1721',0),
(510,'COA Valais Romand','Valais Romand COA','1.LG.0032',0),
(511,'Laufsportverband Oberwallis','Oberwallis LSV','1.SLV.0011',0),
(512,'Verein Gondo Marathon','Gondo Marathon Verein','1.VS.1724',0),
(513,'zürich athletics','ZLV','1.KLV.ZH',0),
(514,'TV Hausen a. A.','Hausen a. A. TV','1.ZH.1801',0),
(515,'LAC TV Unterstrass Zürich','Zürich TVU','1.ZH.1802',0),
(516,'STV Wiedikon','Wiedikon STV','1.ZH.1803',0),
(517,'Leichtathletik Club Zürich','Zürich Leichtathletik Club','1.ZH.1804',0),
(518,'TV Altstetten-Zürich','Altstetten-ZH  TV','1.ZH.1806',0),
(519,'LG Oerlikon','Oerlikon-Glattal LG','1.LG.0033',0),
(520,'SATUS Zürich-Oerlikon','Oerlikon SATUS','1.ZH.1808',0),
(521,'Akad. Sportverband Zürich','ASVZ','1.ZH.1809',0),
(522,'LC Regensdorf','Regensdorf LC','1.ZH.1811',0),
(523,'TV Egg','Egg TV','1.ZH.1813',0),
(524,'Adliswil Track Team','Adliswil Track Team','1.ZH.1814',0),
(525,'LC Turicum','Turicum LC','1.ZH.1815',0),
(526,'STV Dietikon','Dietikon STV','1.ZH.1817',0),
(527,'TV Kloten LA','Kloten TV LA','1.ZH.1819',0),
(528,'TV Dietlikon','Dietlikon TV','1.ZH.1820',0),
(529,'Winterthur Marathon','Winterthur Marathon','1.ZH.1821',0),
(530,'LV Winterthur','Winterthur LV','1.ZH.1822',0),
(531,'TV NS Winterthur','Winterthur TVNS','1.ZH.1823',0),
(532,'TV Kilchberg','Kilchberg TV','1.ZH.1824',0),
(533,'Leichtathletik-Club Dübendorf','Dübendorf Leichtathletik-Club','1.ZH.1827',0),
(534,'LC Uster','Uster LC','1.ZH.1828',0),
(535,'TV Uster','Uster TV','1.ZH.1830',0),
(536,'TV Oerlikon','Oerlikon TV','1.ZH.1831',0),
(537,'LV Zürcher Oberland','Zürcher Oberland LV','1.ZH.1832',0),
(538,'LAR TV Rüti','Rüti TV-LAR','1.ZH.1833',0),
(539,'TV Hombrechtikon','Hombrechtikon TV','1.ZH.1834',0),
(540,'LC Meilen','Meilen LC','1.ZH.1837',0),
(541,'TV Thalwil','Thalwil TV','1.ZH.1840',0),
(542,'TV Bülach','Bülach TV','1.ZH.1841',0),
(543,'TV Horgen','Horgen TV','1.ZH.1842',0),
(544,'STV Wädenswil','Wädenswil STV','1.ZH.1844',0),
(545,'LV Albis','Albis LV','1.ZH.1845',0),
(546,'LG ZH Oberland Athletics','ZH Oberland Athletics LG','1.LG.0035',0),
(547,'TV Maur','Maur TV','1.ZH.1858',0),
(548,'TV Mettmenstetten','Mettmenstetten TV','1.ZH.1861',0),
(549,'LA Wyland','Wyland LA','1.ZH.1862',0),
(550,'Ausland','Ausland','999999',0),
(551,'KTV Einsiedeln','Einsiedeln KTV','1.SZ.1323',0),
(552,'STV Grabs','Grabs STV','1.SGALV.1051',0),
(553,'Läuferriege TV Mauritius Emmen','Emmen TV Mauritius LR','1.ILV.0754',0),
(554,'Verein Lucerne Marathon','Lucerne Marathon Verein','1.ILV.0755',0),
(555,'Laufsportgruppe Olten','Olten LSG','1.SO.1222',0),
(556,'LA Sennwald','Sennwald LA','1.SGALV.1052',0),
(557,'TV Hubersdorf','Hubersdorf TV','1.SO.1223',0),
(558,'STV Kriessern','Kriessern STV','1.SGALV.1053',0),
(559,'cityrunning.ch','CityRunning','1.ZH.1868',0),
(560,'Liechtensteiner Leichtathletikverband','LLV','1.SLV.0007',0),
(561,'LG athletics.baselland','baselland LG athletics','1.LG.0007',0),
(562,'TV Eschlikon','Eschlikon TV','1.TG.1423',0),
(563,'TV Oberdiessbach','Oberdiessbach TV','1.BE.0170',0),
(564,'St.Galler Leichtathletik-Verband','SGLV','1.KLV.SG',0),
(565,'TV Grüsch','Grüsch TV','1.GR.0611',0),
(566,'TV Rümlang','Rümlang TV','1.ZH.1867',0),
(567,'TG Hütten','Hütten TG','1.ZH.1866',0),
(568,'STV Sempach','Sempach STV','1.ILV.0756',0),
(569,'Athletissima Lausanne','Athletissima Lausanne','1.VD.1626',0),
(570,'Weltklasse Zürich','VfG / LCZ','1.ZH.1865',0),
(571,'LA Bern','Bern LA','1.BE.0171',0),
(572,'RTZ Berner Oberland','RTZ BEO','1.BE.0172',1),
(573,'SC Diegten','Diegten SC','1.BSBL.0237',0),
(574,'perü timing','perü timing','1.BE.0173',0),
(575,'LGO Oberbaselbiet / BTV Sissach','Oberbaselbiet LGO / Sissach BTV','1.BSBL.0238',0),
(576,'Free Runners Grenchen','Grenchen Free Runners','1.SO.1220',0),
(577,'kids+athletics','kids+athletics','888888',0),
(578,'Association Genève Marathon','Genève Marathon Association','1.GE.0417',0),
(579,'St. Louis running club','St. Louis running club','',0),
(580,'CS Saint-Louis','CS Saint-Louis','',0),
(581,'LG Radolfzell','LG Radolfzell','',0),
(582,'TuS Lörrach-Stetten','TuS Lörrach-Stetten','',0),
(583,'FC Guebwiller','FC Guebwiller','',0),
(584,'CUS dei Laghi Varese','CUS dei Laghi Varese','',0),
(585,'Offenburg LG','Offenburg LG','',0),
(586,'Unitas Brumath','Unitas Brumath','',0),
(587,'Pays Colmar Athlétisme','Pays Colmar Athlétisme','',0),
(588,'hibschi gämsche','hibschi gämsche','1.VS.1733',0),
(589,'TV Wehr','TV Wehr','',0),
(590,'TV Winterthur','TV Winterthur','',0),
(591,'Associazione Gravesano Running','Gravesano Associazione Running','1.TI.1539',0),
(592,'OK Kreuzegg Classic/Skiclub Bütschwil','Bütschwil OK Kreuzegg Classic/Skiclub','1.SGALV.1067',0),
(593,'RPS Sportveranstaltungen','RPS Sportveranstaltungen','1.SGALV.1068',0),
(594,'Panathlon Club Chablais','Chablais Panathlon Club','1.VD.1641',0),
(595,'TSV Vechigen','Vechigen TSV','1.BE.0204',0),
(596,'WingTsun-Running Bern','Bern WingTsun-Running','1.BE.0205',0),
(597,'LSV Kloten-Basserdorf','Kloten-Bassersdorf LSV','1.ZH.1886',0),
(598,'The Wayve','The Wayve','1.ZH.1883',0),
(599,'Züri rännt','Züri rännt','1.ZH.1884',0),
(600,'Corrida Bulloise','Bulle Corrida Bulloise','1.FR.0339',0),
(601,'TV Mümliswil','Mümliswil TV','1.SO.1225',0),
(602,'Berglauf Steckborn','Steckborn Berglauf','1.TG.1428',0),
(603,'SSC Athletics (Schwamendinger Sportclub)','Schwamendinger Sportclub SSC Athletics','1.ZH.1885',0),
(604,'LSC Wil','Wil LSC','1.SGALV.1069',0),
(605,'Runner\'s Club Bellinzona','Bellinzona Runner\'s Club','1.TI.1540',0),
(606,'FSG Féminine Tavannes','Tavannes FSG Féminine','1.BE.0206',0),
(607,'Verein Aletsch Halbmarathon','Aletsch Verein Halbmarathon','1.VS.1734',0),
(608,'Reusslauf-Vereinigung','Bremgarten Reusslauf-Vereinigung','1.AG.0053',0),
(609,'Playmaker sport et event','(Morat-Fribourg) Playmaker sport et event','1.FR.0338',0),
(610,'Turnverein Wiesendangen','Wiesendangen Turnverein','1.ZH.1887',0),
(611,'Blind-Jogging','Blind-Jogging','1.BSBL.0250',0),
(627,'-','-','',0);

/*Table structure for table `videowand` */

DROP TABLE IF EXISTS `videowand`;

CREATE TABLE `videowand` (
  `xVideowand` int(11) NOT NULL AUTO_INCREMENT,
  `xMeeting` int(11) NOT NULL DEFAULT '0',
  `X` int(11) NOT NULL DEFAULT '0',
  `Y` int(11) NOT NULL DEFAULT '0',
  `InhaltArt` enum('dyn','stat') NOT NULL DEFAULT 'dyn',
  `InhaltStatisch` text NOT NULL,
  `InhaltDynamisch` text NOT NULL,
  `Aktualisierung` int(11) NOT NULL DEFAULT '0',
  `Status` enum('black','white','active') NOT NULL DEFAULT 'active',
  `Hintergrund` varchar(6) NOT NULL DEFAULT '',
  `Fordergrund` varchar(6) NOT NULL DEFAULT '',
  `Bildnr` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xVideowand`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `videowand` */

/*Table structure for table `wertungstabelle` */

DROP TABLE IF EXISTS `wertungstabelle`;

CREATE TABLE `wertungstabelle` (
  `xWertungstabelle` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`xWertungstabelle`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;

/*Data for the table `wertungstabelle` */

/*Table structure for table `wertungstabelle_punkte` */

DROP TABLE IF EXISTS `wertungstabelle_punkte`;

CREATE TABLE `wertungstabelle_punkte` (
  `xWertungstabelle_Punkte` int(11) NOT NULL AUTO_INCREMENT,
  `xWertungstabelle` int(11) NOT NULL DEFAULT '0',
  `xDisziplin` int(11) NOT NULL DEFAULT '0',
  `Geschlecht` enum('W','M') NOT NULL DEFAULT 'M',
  `Leistung` varchar(50) NOT NULL DEFAULT '',
  `Punkte` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`xWertungstabelle_Punkte`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `wertungstabelle_punkte` */

/*Table structure for table `wettkampf` */

DROP TABLE IF EXISTS `wettkampf`;

CREATE TABLE `wettkampf` (
  `xWettkampf` int(11) NOT NULL AUTO_INCREMENT,
  `Typ` tinyint(4) NOT NULL DEFAULT '0',
  `Haftgeld` float unsigned NOT NULL DEFAULT '0',
  `Startgeld` float unsigned NOT NULL DEFAULT '0',
  `Punktetabelle` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Punkteformel` varchar(20) NOT NULL DEFAULT '0',
  `Windmessung` tinyint(4) NOT NULL DEFAULT '0',
  `Info` varchar(50) DEFAULT NULL,
  `Zeitmessung` tinyint(4) NOT NULL DEFAULT '0',
  `ZeitmessungAuto` tinyint(4) NOT NULL DEFAULT '0',
  `xKategorie` int(11) NOT NULL DEFAULT '1',
  `xDisziplin` int(11) NOT NULL DEFAULT '1',
  `xMeeting` int(11) NOT NULL DEFAULT '1',
  `Mehrkampfcode` int(11) NOT NULL DEFAULT '0',
  `Mehrkampfende` tinyint(4) NOT NULL DEFAULT '0',
  `Mehrkampfreihenfolge` tinyint(4) NOT NULL DEFAULT '0',
  `xKategorie_svm` int(11) NOT NULL DEFAULT '0',
  `OnlineId` int(11) NOT NULL DEFAULT '0',
  `TypAenderung` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`xWettkampf`),
  KEY `xKategorie` (`xKategorie`),
  KEY `xDisziplin` (`xDisziplin`),
  KEY `xMeeting` (`xMeeting`),
  KEY `OnlineId` (`OnlineId`)
) ENGINE=MyISAM AUTO_INCREMENT=266 DEFAULT CHARSET=utf8;

/*Data for the table `wettkampf` */


/*Table structure for table `zeitmessung` */

DROP TABLE IF EXISTS `zeitmessung`;

CREATE TABLE `zeitmessung` (
  `xZeitmessung` int(11) NOT NULL AUTO_INCREMENT,
  `OMEGA_Verbindung` enum('local','ftp') NOT NULL DEFAULT 'local',
  `OMEGA_Pfad` varchar(255) NOT NULL DEFAULT '',
  `OMEGA_Server` varchar(255) NOT NULL DEFAULT '',
  `OMEGA_Benutzer` varchar(50) NOT NULL DEFAULT '',
  `OMEGA_Passwort` varchar(50) NOT NULL DEFAULT '',
  `OMEGA_Ftppfad` varchar(255) NOT NULL DEFAULT '',
  `OMEGA_Sponsor` varchar(255) NOT NULL DEFAULT '',
  `ALGE_Typ` varchar(20) NOT NULL DEFAULT '',
  `ALGE_Ftppfad` varchar(255) NOT NULL DEFAULT '',
  `ALGE_Passwort` varchar(50) NOT NULL DEFAULT '',
  `ALGE_Benutzer` varchar(50) NOT NULL DEFAULT '',
  `ALGE_Server` varchar(255) NOT NULL DEFAULT '',
  `ALGE_Pfad` varchar(255) NOT NULL DEFAULT '',
  `ALGE_Verbindung` enum('local','ftp') NOT NULL DEFAULT 'local',
  `xMeeting` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`xZeitmessung`),
  KEY `xMeeting` (`xMeeting`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `zeitmessung` */


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
