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

('AFTERWARDS','fr','ensuite'),

('AFTERWARDS','it','poi'),

('AFTER_ATTEMPT','de','nach %n% Versuch'),

('AFTER_ATTEMPT','fr','après %n% essai'),

('AFTER_ATTEMPT','it','dopo %n%  tentativo'),

('AFTER_ATTEMPTS','de','nach %n% Versuchen'),

('AFTER_ATTEMPTS','fr','après %n% essais'),

('AFTER_ATTEMPTS','it','dopo %n%  tentativi'),

('AND','de','und'),

('AND','fr','et'),

('AND','it','e'),

('APPLICATION_NAME','de','Athletica - Technical Client'),

('APPLICATION_NAME','fr','Athletica - Technical Client'),

('APPLICATION_NAME','it','Athletica - Technical Client'),

('ATHLETICA_SERVER','de','Athletica Server'),

('ATHLETICA_SERVER','fr','Serveur Athletica'),

('ATHLETICA_SERVER','it','Server Athletica '),

('ATTEMPT','de','Versuch'),

('ATTEMPT','fr','essai'),

('ATTEMPT','it','tentativo'),

('ATTEMPTS','de','Versuche'),

('ATTEMPTS','fr','essais'),

('ATTEMPTS','it','tentativi'),

('CENTIMETER_SHORT','de','cm'),

('CENTIMETER_SHORT','fr','cm'),

('CENTIMETER_SHORT','it','cm'),

('CHOOSE','de','bitte wählen'),

('CHOOSE','fr','veuillez choisir'),

('CHOOSE','it','Scegliere'),

('CLOSE','de','Schliessen'),

('CLOSE','fr','fermer'),

('CLOSE','it','Chiudere'),

('CONFIGURATION','de','Konfiguration'),

('CONFIGURATION','fr','configuration'),

('CONFIGURATION','it','Configurazione'),

('DEFAULT','de','Standard'),

('DEFAULT','fr','par défaut'),

('DEFAULT','it','Default'),

('DELETING_RESULT','de','Resultat wird gelöscht...'),

('DELETING_RESULT','fr','le résultat est effacé'),

('DELETING_RESULT','it','Il risultato viene cancellato...'),

('DROP_POSITION','de','Reihenfolge drehen'),

('DROP_POSITION','fr','inverser l\'ordre'),

('DROP_POSITION','it','Invertire l\'ordine'),

('ERROR_DB','de','Datenbankfehler'),

('ERROR_DB','fr','erreur de base de données'),

('ERROR_DB','it','Errore del database'),

('ERROR_DB_TEXT','de','Die Verbindung zur lokalen Datenbank ist fehlgeschlagen. Bitte überprüfen Sie die Einstellungen in der Datei \'inc.settings.php\' im Ordner \'athletica_technical\'.'),

('ERROR_DB_TEXT','fr','La connexion vers la base de données locale a échouée. Veuillez contrôler les paramètres du fichier \'inc.settings.php\' dans le classeur \'athletica_technical\'.'),

('ERROR_DB_TEXT','it','Il collegamento con il database locale è fallito. PF verificare le impostazioni del file \'inc.settings.php\' nella cartella \'athletica_technical\'.'),

('ERROR_DB_TITLE','de','Verbindung fehlgeschlagen'),

('ERROR_DB_TITLE','fr','connexion échouée'),

('ERROR_DB_TITLE','it','Collegamento fallito'),

('ERROR_FUNCTION','de','Funktionsfehler'),

('ERROR_FUNCTION','fr','erreur de fonctionnement'),

('ERROR_FUNCTION','it','Errore di funzione'),

('ERROR_INPUT','de','ungültige Eingabe'),

('ERROR_INPUT','fr','saisie non valable'),

('ERROR_INPUT','it','Immissione non valida'),

('ERROR_INPUT_REQUIRED','de','Pflichtfeld!'),

('ERROR_INPUT_REQUIRED','fr','donnée obligatoire'),

('ERROR_INPUT_REQUIRED','it','Campo obbligatorio!'),

('ERROR_INPUT_RESULT','de','ungültiges Resultat'),

('ERROR_INPUT_RESULT','fr','résultat non valable'),

('ERROR_INPUT_RESULT','it','Risultato non valido'),

('ERROR_INPUT_STARTHEIGHT','de','ungültige Anfangshöhe'),

('ERROR_INPUT_STARTHEIGHT','fr','hauteur de départ non valable'),

('ERROR_INPUT_STARTHEIGHT','it','Altezza di partenza non valida'),

('ERROR_INPUT_VALUE','de','ungültiger Wert'),

('ERROR_INPUT_VALUE','fr','valeur non valable'),

('ERROR_INPUT_VALUE','it','Valore non valido'),

('ERROR_SERVER_TEXT','de','Bitte überprüfen Sie die Einstellungen für den Athletica Server.'),

('ERROR_SERVER_TEXT','fr','Veuillez contrôler les paramètres pour le serveur Athletica'),

('ERROR_SERVER_TEXT','it','PF verificare le impostazioni del server Athletica'),

('ERROR_SERVER_TITLE','de','Verbindung zum Athletica Server fehlgeschlagen'),

('ERROR_SERVER_TITLE','fr','connexion échouée vers le serveur Athletica'),

('ERROR_SERVER_TITLE','it','Collegamento col server Athletica fallito'),

('EVENT','de','Wettkampf'),

('EVENT','fr','concours'),

('EVENT','it','Gara'),

('EVENTS_EMPTY','de','keine Wettkämpfe gefunden'),

('EVENTS_EMPTY','fr','aucun concours a été trouvé'),

('EVENTS_EMPTY','it','Nessuna gara trovata'),

('EVENT_FINISHED','de','Wettkampf abgeschlossen'),

('EVENT_FINISHED','fr','concours terminé'),

('EVENT_FINISHED','it','Gara terminata'),

('EVENT_QUIT','de','Wettkampf verlassen'),

('EVENT_QUIT','fr','quitter le concours'),

('EVENT_QUIT','it','Abbandono'),

('FINAL','de','Vor-/Endkampf'),

('FINAL','fr','èréliminaires/finale'),

('FINAL','it','Finale'),

('FINAL_AFTER','de','Final nach Versuch'),

('FINAL_AFTER','fr','finale après essai'),

('FINAL_AFTER','it','Finale dopo'),

('FINAL_ATHLETES','de','Anz. Athleten im Endkampf'),

('FINAL_ATHLETES','fr','nombre d\'athlètes en finale'),

('FINAL_ATHLETES','it','Numero di atleti in finale'),

('HEIGHTS','de','Sprunghöhen'),

('HEIGHTS','fr','hauteurs de saut'),

('HEIGHTS','it','Altezze'),

('HEIGHTS_DIFF','de','Höhensteigerung'),

('HEIGHTS_DIFF','fr','progression des hauteurs'),

('HEIGHTS_DIFF','it','Incremento altezze'),

('HEIGHTS_START','de','Anfangshöhen'),

('HEIGHTS_START','fr','hauteurs de départ'),

('HEIGHTS_START','it','Altezze di partenza'),

('HEIGHTS_START_DEFINE','de','Anfangshöhen definieren!'),

('HEIGHTS_START_DEFINE','fr','définition des hauteur de départ'),

('HEIGHTS_START_DEFINE','it','Definire le altezze di partenza!'),

('HOME','de','Startseite'),

('HOME','fr','page de démarrage'),

('HOME','it','Pagina iniziale'),

('LANGUAGE','de','Sprache'),

('LANGUAGE','fr','langue'),

('LANGUAGE','it','Lingua'),

('LOADING_DATA','de','Daten werden geladen...'),

('LOADING_DATA','fr','les données sont chargées'),

('LOADING_DATA','it','I dati vengono caricati...'),

('LOADING_HEIGHTS','de','Sprunghöhen werden geladen...'),

('LOADING_HEIGHTS','fr','les hauteurs de saut sont chargées'),

('LOADING_HEIGHTS','it','Le altezze vengono caricate...'),

('LOADING_RESULT','de','Resultat wird geladen...'),

('LOADING_RESULT','fr','le résultat est chargé'),

('LOADING_RESULT','it','Il risultato viene caricato...'),

('LOADING_RESULTLIST','de','Rangliste wird geladen...'),

('LOADING_RESULTLIST','fr','le classement est chargé'),

('LOADING_RESULTLIST','it','La classifica viene caricata...'),

('LOADING_RESULTS','de','Resultate werden geladen...'),

('LOADING_RESULTS','fr','les résultats sont chargés'),

('LOADING_RESULTS','it','I risultati vengono caricati...'),

('LOADING_SETTINGS','de','Einstellungen werden geladen...'),

('LOADING_SETTINGS','fr','les paramètres sont chargés'),

('LOADING_SETTINGS','it','Le impostazioni vengono caricate...'),

('LOADING_STARTHEIGHTS','de','Anfangshöhen werden geladen...'),

('LOADING_STARTHEIGHTS','fr','les hauteurs de départ sont chargées'),

('LOADING_STARTHEIGHTS','it','Le altezze di partenza vengono caricate...'),

('LOADING_STARTLIST','de','Startliste wird geladen...'),

('LOADING_STARTLIST','fr','la liste de départ est chargée'),

('LOADING_STARTLIST','it','La lista di partenza viene caricata...'),

('MEETING','de','Meeting'),

('MEETING','fr','meeting'),

('MEETING','it','Meeting'),

('METER_SHORT','de','m'),

('METER_SHORT','fr','m'),

('METER_SHORT','it','m'),

('MODE','de','Modus'),

('MODE','fr','mode'),

('MODE','it','Modo'),

('MODE_LIVE','de','Live'),

('MODE_LIVE','fr','en direct'),

('MODE_LIVE','it','Live'),

('MODE_LOCAL','de','Lokal'),

('MODE_LOCAL','fr','locale'),

('MODE_LOCAL','it','locale'),

('NEXT','de','Next'),

('NEXT','fr','suivant'),

('NEXT','it','prossimo'),

('NO','de','Nein'),

('NO','fr','Non'),

('NO','it','No'),

('OK','de','OK'),

('OK','fr','OK'),

('OK','it','OK'),

('PERFORMANCE','de','Leistung'),

('PERFORMANCE','fr','performance'),

('PERFORMANCE','it','Prestazione'),

('PLEASE_WAIT','de','Bitte warten'),

('PLEASE_WAIT','fr','veuillez attendre'),

('PLEASE_WAIT','it','Attendere prego'),

('POSITION_RESET','de','Reihenfolge zurücksetzen'),

('POSITION_RESET','fr','reinitialiser l\'ordre'),

('POSITION_RESET','it','Resettare'),

('QUIT','de','Verlassen'),

('QUIT','fr','quitter'),

('QUIT','it','Abbandonare'),

('REFRESH','de','Aktualisieren'),

('REFRESH','fr','actualisation'),

('REFRESH','it','Aggiornare'),

('REMARK','de','Bemerkung'),

('REMARK','fr','remarque'),

('REMARK','it','Annotazione'),

('RESETING_POSITIONS','de','Startliste wird wiederhergestellt...'),

('RESETING_POSITIONS','fr','la liste de départ est restaurée'),

('RESETING_POSITIONS','it','La lista di partenza viene resettata...'),

('RESULTLIST','de','Rangliste'),

('RESULTLIST','fr','classement'),

('RESULTLIST','it','Classifica'),

('RESULTS','de','Resultate'),

('RESULTS','fr','modification du classement'),

('RESULTS','it','Risultati'),

('RESULTS_CHANGE','de','Resultate ändern'),

('RESULTS_CHANGE','fr','modification des résultat'),

('RESULTS_CHANGE','it','Modifica risultati'),

('RESULT_HIGH_FAILED_BUTTON','de','X'),

('RESULT_HIGH_FAILED_BUTTON','fr','X'),

('RESULT_HIGH_FAILED_BUTTON','it','X'),

('RESULT_HIGH_PASSED_BUTTON','de','O'),

('RESULT_HIGH_PASSED_BUTTON','fr','O'),

('RESULT_HIGH_PASSED_BUTTON','it','O'),

('RESULT_HIGH_WAIVED_BUTTON_LONG','de','verz.'),

('RESULT_HIGH_WAIVED_BUTTON_LONG','fr','renonce'),

('RESULT_HIGH_WAIVED_BUTTON_LONG','it','rinunciato'),

('RESULT_HIGH_WAIVED_BUTTON_SHORT','de','-'),

('RESULT_HIGH_WAIVED_BUTTON_SHORT','fr','-'),

('RESULT_HIGH_WAIVED_BUTTON_SHORT','it','-'),

('RESULT_INVALID_DNS_BUTTON','de','n. a.'),

('RESULT_INVALID_DNS_BUTTON','fr','pas venu'),

('RESULT_INVALID_DNS_BUTTON','it','non part.'),

('RESULT_INVALID_DNS_DB','de','-1'),

('RESULT_INVALID_DNS_DB','fr','-1'),

('RESULT_INVALID_DNS_DB','it','-1'),

('RESULT_INVALID_DNS_RANKING','de','n. a.'),

('RESULT_INVALID_DNS_RANKING','fr','pas venu'),

('RESULT_INVALID_DNS_RANKING','it','non part.'),

('RESULT_INVALID_DNS_SHORT','de','n. a.'),

('RESULT_INVALID_DNS_SHORT','fr','pas venu'),

('RESULT_INVALID_DNS_SHORT','it','non part.'),

('RESULT_INVALID_DSQ_BUTTON','de','disq.'),

('RESULT_INVALID_DSQ_BUTTON','fr','disq.'),

('RESULT_INVALID_DSQ_BUTTON','it','squal.'),

('RESULT_INVALID_DSQ_DB','de','-3'),

('RESULT_INVALID_DSQ_DB','fr','-3'),

('RESULT_INVALID_DSQ_DB','it','-3'),

('RESULT_INVALID_DSQ_RANKING','de','disq.'),

('RESULT_INVALID_DSQ_RANKING','fr','disq.'),

('RESULT_INVALID_DSQ_RANKING','it','squal.'),

('RESULT_INVALID_DSQ_SHORT','de','disq.'),

('RESULT_INVALID_DSQ_SHORT','fr','disq.'),

('RESULT_INVALID_DSQ_SHORT','it','squal.'),

('RESULT_INVALID_NAA_BUTTON','de','X'),

('RESULT_INVALID_NAA_BUTTON','fr','X'),

('RESULT_INVALID_NAA_BUTTON','it','X'),

('RESULT_INVALID_NAA_DB','de','X'),

('RESULT_INVALID_NAA_DB','fr','X'),

('RESULT_INVALID_NAA_DB','it','X'),

('RESULT_INVALID_NAA_RANKING','de','k. Res.'),

('RESULT_INVALID_NAA_RANKING','fr','aucun rés.'),

('RESULT_INVALID_NAA_RANKING','it','nessun risultato'),

('RESULT_INVALID_NAA_SHORT','de','X'),

('RESULT_INVALID_NAA_SHORT','fr','X'),

('RESULT_INVALID_NAA_SHORT','it','X'),

('RESULT_INVALID_WAI_BUTTON','de','-'),

('RESULT_INVALID_WAI_BUTTON','fr','-'),

('RESULT_INVALID_WAI_BUTTON','it','-'),

('RESULT_INVALID_WAI_DB','de','-'),

('RESULT_INVALID_WAI_DB','fr','-'),

('RESULT_INVALID_WAI_DB','it','-'),

('RESULT_INVALID_WAI_RANKING','de','verz.'),

('RESULT_INVALID_WAI_RANKING','fr','renonce'),

('RESULT_INVALID_WAI_RANKING','it','rinunciato'),

('RESULT_INVALID_WAI_SHORT','de','-'),

('RESULT_INVALID_WAI_SHORT','fr','-'),

('RESULT_INVALID_WAI_SHORT','it','-'),

('SAVE','de','Speichern'),

('SAVE','fr','enregistrement'),

('SAVE','it','Salva'),

('SAVING_HEIGHTS','de','Sprunghöhen werden gespeichert...'),

('SAVING_HEIGHTS','fr','enregistrement des hauteurs de saut'),

('SAVING_HEIGHTS','it','Le altezze vengono salvate...'),

('SAVING_RESULT','de','Resultat wird gespeichert...'),

('SAVING_RESULT','fr','enregistrement du résultat'),

('SAVING_RESULT','it','Il risultato viene salvato...'),

('SAVING_STARTHEIGHTS','de','Anfangshöhen werden gespeichert...'),

('SAVING_STARTHEIGHTS','fr','enregistrement des hauteurs de départ'),

('SAVING_STARTHEIGHTS','it','Le altezze di partenza vengono salvate...'),

('SERVER_DATABASE','de','Datenbank'),

('SERVER_DATABASE','fr','base de données'),

('SERVER_DATABASE','it','Banca dati'),

('SERVER_HOST','de','IP Adresse'),

('SERVER_HOST','fr','adresse IP'),

('SERVER_HOST','it','Indirizzo IP'),

('SERVER_PASSWORD','de','Passwort'),

('SERVER_PASSWORD','fr','mot de passe'),

('SERVER_PASSWORD','it','Password'),

('SERVER_PORT','de','Port'),

('SERVER_PORT','fr','port'),

('SERVER_PORT','it','Porta'),

('SERVER_USERNAME','de','Benutzername'),

('SERVER_USERNAME','fr','nom d\'utilisateur'),

('SERVER_USERNAME','it','Nome utente'),

('SETTINGS','de','Einstellungen'),

('SETTINGS','fr','paramètres'),

('SETTINGS','it','Impostazioni'),

('STARTLIST','de','Startliste'),

('STARTLIST','fr','liste de départ'),

('STARTLIST','it','Lista di partenza'),

('SUCCESS_SERVER_TEXT','de','Die Verbindung zum Athletica Server wurde erfolgreich hergestellt.'),

('SUCCESS_SERVER_TEXT','fr','la connexion vers le serveur Athletica a été établie avec succès'),

('SUCCESS_SERVER_TEXT','it','Il collegamento al server Athletica è stato effettuato in modo corretto.'),

('SUCCESS_SERVER_TITLE','de','Verbindung erfolgreich'),

('SUCCESS_SERVER_TITLE','fr','connexion avec succès'),

('SUCCESS_SERVER_TITLE','it','Collegamento effettuato'),

('TIME_CALL','de','Stellzeit'),

('TIME_CALL','fr','heure de rassemblement'),

('TIME_CALL','it','Orario di appello'),

('TIME_START','de','Startzeit'),

('TIME_START','fr','heure de départ'),

('TIME_START','it','Orario di partenza'),

('TO','de','bis'),

('TO','fr','jusqu\'à'),

('TO','it','Fino a'),

('TO_TOP','de','nach oben'),

('TO_TOP','fr','vers le haut'),

('TO_TOP','it','Su'),

('WIND','de','Wind'),

('WIND','fr','vent'),

('WIND','it','Vento'),

('YES','de','Ja'),

('YES','fr','Oui'),

('YES','it','Sì');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
