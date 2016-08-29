/*
SQLyog Ultimate v12.16 (64 bit)
MySQL - 5.1.50-community : Database - athletica_liveresultate
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`athletica_liveresultate` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `athletica_liveresultate`;

/*Table structure for table `config` */

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `xConfig` int(11) NOT NULL DEFAULT '0',
  `ftpHost` varchar(100) NOT NULL DEFAULT '',
  `ftpUser` varchar(30) NOT NULL DEFAULT '',
  `ftpPwd` varchar(30) NOT NULL DEFAULT '',
  `url` varchar(100) NOT NULL DEFAULT '',
  `dir` varchar(100) DEFAULT '',
  PRIMARY KEY (`xConfig`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `config` */

insert  into `config`(`xConfig`,`ftpHost`,`ftpUser`,`ftpPwd`,`url`) values 
(0,'','','','');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
