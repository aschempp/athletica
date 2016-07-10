/*
SQLyog Ultimate v12.16 (64 bit)
MySQL - 5.1.50-community : Database - athletica_technical
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`athletica_technical` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `athletica_technical`;

/*Table structure for table `t_config` */

DROP TABLE IF EXISTS `t_config`;

CREATE TABLE `t_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(50) DEFAULT NULL,
  `config_group` varchar(30) DEFAULT NULL,
  `config_value` text,
  `config_show` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`config_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `t_config` */

insert  into `t_config`(`config_id`,`config_key`,`config_group`,`config_value`,`config_show`) values 
(1,'server_host','server','127.0.0.1',1),
(2,'server_db','server','athletica',1),
(3,'server_username','server','athletica',1),
(4,'server_password','server','athletica',1),
(5,'server_port','server','3306',1),
(6,'server_engine','server','mysql',0);

/*Table structure for table `t_heights` */

DROP TABLE IF EXISTS `t_heights`;

CREATE TABLE `t_heights` (
  `xHeight` int(11) NOT NULL,
  `serie` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `height` (`height`,`serie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `t_heights` */

/*Table structure for table `t_high_settings` */

DROP TABLE IF EXISTS `t_high_settings`;

CREATE TABLE `t_high_settings` (
  `xSerie` int(11) NOT NULL,
  `diff_1_until` int(11) NOT NULL DEFAULT '160',
  `diff_2_until` int(11) NOT NULL DEFAULT '180',
  `diff_1_value` int(11) NOT NULL DEFAULT '5',
  `diff_2_value` int(11) NOT NULL DEFAULT '3',
  `diff_3_value` int(11) NOT NULL DEFAULT '2',
  PRIMARY KEY (`xSerie`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `t_high_settings` */

/*Table structure for table `t_language` */

DROP TABLE IF EXISTS `t_language`;

CREATE TABLE `t_language` (
  `language_code` char(2) NOT NULL DEFAULT 'de',
  `language_name` varchar(30) NOT NULL,
  `language_position` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`language_code`),
  UNIQUE KEY `language_code` (`language_code`),
  UNIQUE KEY `language_name` (`language_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `t_language` */

insert  into `t_language`(`language_code`,`language_name`,`language_position`) values 
('de','Deutsch',1),
('fr','Français',2),
('it','Italiano',3);

/*Table structure for table `t_log_error` */

DROP TABLE IF EXISTS `t_log_error`;

CREATE TABLE `t_log_error` (
  `log_error_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_error_date` datetime DEFAULT NULL,
  `log_error_level` int(11) NOT NULL DEFAULT '2',
  `log_error_message` text NOT NULL,
  `log_error_file` varchar(255) NOT NULL,
  `log_error_line` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_error_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `t_log_error` */

/*Table structure for table `t_translation` */

DROP TABLE IF EXISTS `t_translation`;

CREATE TABLE `t_translation` (
  `translation_key` varchar(50) NOT NULL DEFAULT '',
  `language_code` char(2) NOT NULL,
  `translation_text` text NOT NULL,
  UNIQUE KEY `translation_key_language_code` (`translation_key`,`language_code`),
  KEY `language_code` (`language_code`),
  CONSTRAINT `fk_translation_language_code` FOREIGN KEY (`language_code`) REFERENCES `t_language` (`language_code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `t_translation` */

insert  into `t_translation`(`translation_key`,`language_code`,`translation_text`) values 
('AFTERWARDS','de','danach'),
('AFTER_ATTEMPT','de','nach %n% Versuch'),
('AFTER_ATTEMPTS','de','nach %n% Versuchen'),
('AND','de','und'),
('APPLICATION_NAME','de','Athletica - Technical Client'),
('ATHLETICA_SERVER','de','Athletica Server'),
('ATTEMPT','de','Versuch'),
('ATTEMPTS','de','Versuche'),
('CENTIMETER_SHORT','de','cm'),
('CHOOSE','de','bitte wählen'),
('CLOSE','de','Schliessen'),
('CONFIGURATION','de','Konfiguration'),
('DEFAULT','de','Standard'),
('DELETING_RESULT','de','Resultat wird gelöscht...'),
('DROP_POSITION','de','Reihenfolge drehen'),
('ERROR_DB','de','Datenbankfehler'),
('ERROR_DB_TEXT','de','Die Verbindung zur lokalen Datenbank ist fehlgeschlagen. Bitte überprüfen Sie die Einstellungen in der Datei \'inc.settings.php\' im Ordner \'athletica_technical\'.'),
('ERROR_DB_TITLE','de','Verbindung fehlgeschlagen'),
('ERROR_FUNCTION','de','Funktionsfehler'),
('ERROR_INPUT','de','ungültige Eingabe'),
('ERROR_INPUT_REQUIRED','de','Pflichtfeld!'),
('ERROR_INPUT_RESULT','de','ungültiges Resultat'),
('ERROR_INPUT_STARTHEIGHT','de','ungültige Anfangshöhe'),
('ERROR_INPUT_VALUE','de','ungültiger Wert'),
('ERROR_SERVER_TEXT','de','Bitte überprüfen Sie die Einstellungen für den Athletica Server.'),
('ERROR_SERVER_TITLE','de','Verbindung zum Athletica Server fehlgeschlagen'),
('EVENT','de','Wettkampf'),
('EVENTS_EMPTY','de','keine Wettkämpfe gefunden'),
('EVENT_FINISHED','de','Wettkampf abgeschlossen'),
('EVENT_QUIT','de','Wettkampf verlassen'),
('FINAL','de','Vor-/Endkampf'),
('FINAL_AFTER','de','Final nach Versuch'),
('FINAL_ATHLETES','de','Anz. Athleten im Endkampf'),
('HEIGHTS','de','Sprunghöhen'),
('HEIGHTS_DIFF','de','Höhensteigerung'),
('HEIGHTS_START','de','Anfangshöhen'),
('HEIGHTS_START_DEFINE','de','Anfangshöhen definieren!'),
('HOME','de','Startseite'),
('LANGUAGE','de','Sprache'),
('LOADING_DATA','de','Daten werden geladen...'),
('LOADING_HEIGHTS','de','Sprunghöhen werden geladen...'),
('LOADING_RESULT','de','Resultat wird geladen...'),
('LOADING_RESULTLIST','de','Rangliste wird geladen...'),
('LOADING_RESULTS','de','Resultate werden geladen...'),
('LOADING_SETTINGS','de','Einstellungen werden geladen...'),
('LOADING_STARTHEIGHTS','de','Anfangshöhen werden geladen...'),
('LOADING_STARTLIST','de','Startliste wird geladen...'),
('MEETING','de','Meeting'),
('METER_SHORT','de','m'),
('MODE','de','Modus'),
('MODE_LIVE','de','Live'),
('MODE_LOCAL','de','Lokal'),
('NEXT','de','Next'),
('NO','de','Nein'),
('OK','de','OK'),
('PERFORMANCE','de','Leistung'),
('PLEASE_WAIT','de','Bitte warten'),
('POSITION_RESET','de','Reihenfolge zurücksetzen'),
('QUIT','de','Verlassen'),
('REFRESH','de','Aktualisieren'),
('REMARK','de','Bemerkung'),
('RESETING_POSITIONS','de','Startliste wird wiederhergestellt...'),
('RESULTLIST','de','Rangliste'),
('RESULTS','de','Resultate'),
('RESULTS_CHANGE','de','Resultate ändern'),
('RESULT_HIGH_FAILED_BUTTON','de','X'),
('RESULT_HIGH_PASSED_BUTTON','de','O'),
('RESULT_HIGH_WAIVED_BUTTON_LONG','de','verz.'),
('RESULT_HIGH_WAIVED_BUTTON_SHORT','de','-'),
('RESULT_INVALID_DNS_BUTTON','de','n. a.'),
('RESULT_INVALID_DNS_DB','de','-1'),
('RESULT_INVALID_DNS_RANKING','de','n. a.'),
('RESULT_INVALID_DNS_SHORT','de','n. a.'),
('RESULT_INVALID_DSQ_BUTTON','de','disq.'),
('RESULT_INVALID_DSQ_DB','de','-3'),
('RESULT_INVALID_DSQ_RANKING','de','disq.'),
('RESULT_INVALID_DSQ_SHORT','de','disq.'),
('RESULT_INVALID_NAA_BUTTON','de','X'),
('RESULT_INVALID_NAA_DB','de','X'),
('RESULT_INVALID_NAA_RANKING','de','k. Res.'),
('RESULT_INVALID_NAA_SHORT','de','X'),
('RESULT_INVALID_WAI_BUTTON','de','-'),
('RESULT_INVALID_WAI_DB','de','-'),
('RESULT_INVALID_WAI_RANKING','de','verz.'),
('RESULT_INVALID_WAI_SHORT','de','-'),
('SAVE','de','Speichern'),
('SAVING_HEIGHTS','de','Sprunghöhen werden gespeichert...'),
('SAVING_RESULT','de','Resultat wird gespeichert...'),
('SAVING_STARTHEIGHTS','de','Anfangshöhen werden gespeichert...'),
('SERVER_DATABASE','de','Datenbank'),
('SERVER_HOST','de','IP Adresse'),
('SERVER_PASSWORD','de','Passwort'),
('SERVER_PORT','de','Port'),
('SERVER_USERNAME','de','Benutzername'),
('SETTINGS','de','Einstellungen'),
('STARTLIST','de','Startliste'),
('SUCCESS_SERVER_TEXT','de','Die Verbindung zum Athletica Server wurde erfolgreich hergestellt.'),
('SUCCESS_SERVER_TITLE','de','Verbindung erfolgreich'),
('TIME_CALL','de','Stellzeit'),
('TIME_START','de','Startzeit'),
('TO','de','bis'),
('TO_TOP','de','nach oben'),
('WIND','de','Wind'),
('YES','de','Ja');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
