<?php

if (!defined('AA_CL_ALGE_LIB_INCLUDED'))
{
	define('AA_CL_ALGE_LIB_INCLUDED', 1);
}else{
	return;
}

/************************************
 *
 * Alge time measurement
 *
 * Handles file import/export with the alge optic software
 *
/************************************/

require("./lib/cl_ftp_data.lib.php");

class alge{
	
	var $typ;
	var $connection;
	var $path;
	var $host;
	var $user;
	var $pass;
	var $ftppath;
	
	var $ftp;
	
	var $si; // silent, no errors
	
	function alge($noerror = false){    	
		$this->si = $noerror;
		$this->get_configuration($_COOKIE['meeting_id']);
		
		$this->ftp = new ftp_data();
	}
	
	function set_configuration($meeting){
		global $strOmegaPathWriteFailed, $strAlgeNoPath;
		
		//if(@is_dir($_POST['path'])){
			// test local path for writing
			$fp = @fopen($_POST['path']."/test.txt",'w');
			if(!$fp){
				$GLOBALS['ERROR'] = $strOmegaPathWriteFailed;
			}else{
				fclose($fp);
				unlink($_POST['path']."/test.txt");
			}
			
			mysql_query("LOCK TABLES zeitmessung WRITE");
		
			$res = mysql_query("SELECT * FROM zeitmessung WHERE xMeeting = $meeting");
			if(mysql_errno() > 0){
				AA_printErrorMsg(mysql_errno().": ".mysql_error());
			}else{
				if(!get_magic_quotes_gpc()){
					$_POST['path'] = addslashes($_POST['path']);
					$_POST['ftppath'] = addslashes($_POST['ftppath']);
				}
				if(mysql_num_rows($res) == 0){
					mysql_query("
						INSERT INTO zeitmessung
						SET	ALGE_Typ = '".$_POST['typ']."'
							, ALGE_Verbindung = '".$_POST['connection']."'
							, ALGE_Pfad = '".$_POST['path']."'
							, ALGE_Server = '".$_POST['host']."'
							, ALGE_Benutzer = '".$_POST['user']."'
							, ALGE_Passwort = '".$_POST['pass']."'
							, ALGE_Ftppfad = '".$_POST['ftppath']."'
							, xMeeting = $meeting
						");
				}else{
					mysql_query("
						UPDATE zeitmessung
						SET	ALGE_Typ = '".$_POST['typ']."'
							, ALGE_Verbindung = '".$_POST['connection']."'
							, ALGE_Pfad = '".$_POST['path']."'
							, ALGE_Server = '".$_POST['host']."'
							, ALGE_Benutzer = '".$_POST['user']."'
							, ALGE_Passwort = '".$_POST['pass']."'
							, ALGE_Ftppfad = '".$_POST['ftppath']."'
						WHERE	xMeeting = $meeting
						");
				}
			}
			
			mysql_query("UNLOCK TABLES");
		/*} else {
			$GLOBALS['ERROR'] = $strAlgeNoPath;
		}*/
	}
	
	function get_configuration($meeting){
		global $strOmegaNoConf;
		
		mysql_query("LOCK TABLES zeitmessung READ");
		
		$res = mysql_query("SELECT * FROM zeitmessung WHERE xMeeting = $meeting");
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			
			if(mysql_num_rows($res) == 0 && $_POST['arg'] != 'change'){
				$this->connection = "noconf";
				if($this->si==false){ AA_printErrorMsg($strOmegaNoConf); }
			}else{
			
				$row = mysql_fetch_assoc($res);
				
				$this->typ = $row['ALGE_Typ'];
				$this->connection = $row['ALGE_Verbindung'];
				$this->path = $row['ALGE_Pfad'];
				$this->host = $row['ALGE_Server'];
				$this->user = $row['ALGE_Benutzer'];
				$this->pass = $row['ALGE_Passwort'];
				$this->ftppath = $row['ALGE_Ftppfad'];
			}
		}
		
		mysql_query("UNLOCK TABLES");
	}
	
	/**
	 * generates the filename for a round or a heat
	 *
	 * file name is specified by "'filmnbr'_'discipline' 'categorie' - 'roundtype'_H'heat'"
	 * if round is given -> heat and film number will be added by the calling function
	**/
	function make_filename($round = 0, $heat = 0){
		
		if($round > 0){
			/*$res = mysql_query("SELECT 
						TIME_FORMAT(r.Startzeit, '%H%i')
						, d.Kurzname
						, k.Name
						, rt.Name
					FROM
						wettkampf as w
						, runde as r
						, disziplin as d
						, rundentyp as rt
						, kategorie as k
					WHERE	r.xRunde = $round
					AND	r.xWettkampf = w.xWettkampf
					AND	d.xDisziplin = w.xDisziplin
					AND	rt.xRundentyp = r.xRundentyp
					AND	k.xKategorie = w.xKategorie");*/
			$sql = "SELECT TIME_FORMAT(r.Startzeit, '%H%i'), 
						   d.Kurzname, 
						   k.Name, 
						   rt.Name 
					  FROM wettkampf AS w 
				 LEFT JOIN runde AS r USING(xWettkampf) 
				 LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON(w.xDisziplin = d.xDisziplin) 
				 LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON(r.xRundentyp = rt.xRundentyp) 
				 LEFT JOIN kategorie AS k ON(w.xKategorie = k.xKategorie) 
					 WHERE r.xRunde = ".$round.";";
			$res = mysql_query($sql);
			
			if(mysql_errno() > 0){
				AA_printErrorMsg(mysql_errno().": ".mysql_error());
			}else{
				
				$row = mysql_fetch_array($res);
                
                $replace = array( '/' => '_' ,  ':' => '_','*' => '_', '?' => '_', '<' => '_', '>' => '_', '|' => '_');
                $row[2] = strtr($row[2] , $replace );       
				return "_".$row[1]." ".$row[2]." - ".$row[3]."_H";
				
			}
		}elseif($heat > 0){
			/*$res = mysql_query("SELECT 
						TIME_FORMAT(r.Startzeit, '%H%i')
						, d.Kurzname
						, k.Name
						, rt.Name
						, s.Bezeichnung
						, s.Film
					FROM
						wettkampf as w
						, runde as r
						, serie as s
						, disziplin as d
						, rundentyp as rt
						, kategorie as k
					WHERE	s.xSerie = $heat
					AND	r.xWettkampf = w.xWettkampf
					AND	s.xRunde = r.xRunde
					AND	d.xDisziplin = w.xDisziplin
					AND	rt.xRundentyp = r.xRundentyp
					AND	k.xKategorie = w.xKategorie");*/
			$sql = "SELECT TIME_FORMAT(r.Startzeit, '%H%i'), 
						   d.Kurzname, 
						   k.Name, 
						   rt.Name, 
						   s.Bezeichnung, 
						   s.Film 
					  FROM wettkampf AS w 
				 LEFT JOIN runde AS r USING(xWettkampf) 
				 LEFT JOIN serie AS s USING(xRunde) 
				 LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON(w.xDisziplin = d.xDisziplin) 
				 LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON(r.xRundentyp = rt.xRundentyp) 
				 LEFT JOIN kategorie AS k ON(w.xKategorie = k.xKategorie) 
					 WHERE s.xSerie = ".$heat.";";
			$res = mysql_query($sql);

			if(mysql_errno() > 0){
				AA_printErrorMsg(mysql_errno().": ".mysql_error());
			}else{
				
				$row = mysql_fetch_array($res);
                $replace = array( '/' => '_' ,  ':' => '_','*' => '_', '?' => '_', '<' => '_', '>' => '_', '|' => '_');
                $row[2] = strtr($row[2] , $replace );       
				return sprintf("%03d",$row[5])."_".$row[1]." ".$row[2]." - ".$row[3]."_H".$row[4];
				
			}
		}
	}
	
	/**
	* export a round to alge
	* 
	* exportet are 2 files, .txt (competitiors) and .rac (race information)
	* - param round
	*/
	function export_round($round){
		
		$relay = AA_checkRelay(0, $round);
		
		mysql_query("LOCK TABLES wettkampf as w READ, runde as r READ
					, serie as s READ, disziplin_de as d READ , disziplin_fr as d READ  , disziplin_it as d READ  
					, rundentyp_de as rt READ, rundentyp_fr as rt READ, rundentyp_it as rt READ
                    , kategorie as k READ
					, anmeldung as a READ, athlet as at READ
					, start as st READ, serienstart as ss READ
					, verein as v READ, meeting as m READ
					, stadion as sta, staffel as sf READ");
		
		$file = $this->make_filename($round);  
		// get each heat with race informations
		/*$sql = "SELECT 
				s.xSerie
				, m.Name
				, m.Ort
				, d.Kurzname
				, k.Name
				, rt.Name
				, w.xWettkampf
				, d.Strecke
				, s.Film
				, s.Bezeichnung
				, sta.Name
			FROM
				meeting as m
				, wettkampf as w
				, disziplin as d
				, kategorie as k
				, runde as r
				, serie as s
				, rundentyp as rt
				, stadion as sta
			WHERE	r.xRunde = $round
			AND	w.xWettkampf = r.xWettkampf
			AND	m.xMeeting = w.xMeeting
			AND	sta.xStadion = m.xStadion
			AND	d.xDisziplin = w.xDisziplin
			AND	k.xKategorie = w.xKategorie
			AND	s.xRunde = r.xRunde
			AND	rt.xRundentyp = r.xRundentyp
			AND	w.Zeitmessung = 1
			";*/
			$sql = "SELECT s.xSerie, 
						   m.Name, 
						   m.Ort, 
						   d.Kurzname, 
						   k.Name, 
						   rt.Name, 
						   w.xWettkampf, 
						   d.Strecke, 
						   s.Film, 
						   s.Bezeichnung, 
						   sta.Name,
                           d.Code 
					  FROM meeting AS m 
				 LEFT JOIN wettkampf AS w USING(xMeeting) 
				 LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d USING(xDisziplin) 
				 LEFT JOIN kategorie AS k ON(w.xKategorie = k.xKategorie) 
				 LEFT JOIN runde AS r ON(w.xWettkampf = r.xWettkampf) 
				 LEFT JOIN serie AS s USING(xRunde) 
				 LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON(r.xRundentyp = rt.xRundentyp) 
				 LEFT JOIN stadion AS sta ON(m.xStadion = sta.xStadion) 
					 WHERE r.xRunde = ".$round." 
					   AND w.Zeitmessung = 1;";      		
		$resHeat = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			
			while($rowHeat = mysql_fetch_array($resHeat)){
				$fileHeat = sprintf("%03d",$rowHeat[8]).$file.$rowHeat[9];
                
				// race information (*.rac file)
				// "RaceNo" = Heat Number, no identification
                $windmode = 7;      // no measurement
                if  ($rowHeat[11] == 10 || $rowHeat[11] == 30 || ($rowHeat[11] >= 252 & $rowHeat[11] <= 256)) {
                    $windmode = 6;
                }
                elseif ($rowHeat[11] == 35 || $rowHeat[11] == 258 || $rowHeat[11] == 40) {
                    $windmode = 5;  
                }
                elseif ($rowHeat[11] >= 259 & $rowHeat[11] <= 271) {
                    $windmode = 4;  
                }
                 elseif ($rowHeat[11] == 50) {
                    $windmode = 3;  
                }
                else { 
                    $windmode = 7;  
                }  
               
				$tmp = "[RaceInfo]
Meeting=$rowHeat[1], $rowHeat[2]
Place=$rowHeat[10]
CompType=$rowHeat[3] $rowHeat[4] - $rowHeat[5]
CompNo=$rowHeat[6]
Distance=$rowHeat[7]m
RaceNo=$rowHeat[9]
Prepared=1
Windmode=$windmode
[Files]
Name=$fileHeat
";
				$this->send_file($tmp, "$fileHeat.rac");
				
				if($relay == false){
					
					// starts for each race (*.txt file)
					$tmp = "";
					/*$sql = "SELECT
							a.Startnummer
							, ss.Bahn
							, at.Name
							, at.Vorname
							, v.Name
							, at.Jahrgang
						FROM 
							runde as r
							, wettkampf as w
							, serie as s
							, serienstart as ss
							, start as st
							, anmeldung as a
							, athlet as at
							, verein as v
						WHERE	s.xSerie = $rowHeat[0]
						AND	r.xWettkampf = w.xWettkampf
						AND	s.xRunde = r.xRunde
						AND	ss.xSerie = s.xSerie
						AND	st.xStart = ss.xStart
						AND	a.xAnmeldung = st.xAnmeldung
						AND	at.xAthlet = a.xAthlet
						AND	v.xVerein = at.xVerein";*/
					$sql = "SELECT a.Startnummer, 
								   ss.Bahn, 
								   at.Name, 
								   at.Vorname, 
								   v.Name, 
								   at.Jahrgang 
							  FROM runde AS r 
						 LEFT JOIN wettkampf AS w USING(xWettkampf) 
						 LEFT JOIN serie AS s ON(r.xRunde = s.xRunde) 
						 LEFT JOIN serienstart AS ss USING(xSerie) 
						 LEFT JOIN start AS st USING(xStart) 
						 LEFT JOIN anmeldung AS a USING(xAnmeldung) 
						 LEFT JOIN athlet AS at USING(xAthlet) 
						 LEFT JOIN verein AS v USING(xVerein) 
							 WHERE s.xSerie = ".$rowHeat[0] . "
                               ORDER BY ss.Position";
					$res = mysql_query($sql);
                   
					if(mysql_errno() > 0){
						AA_printErrorMsg(mysql_errno().": ".mysql_error());
					}else{
						if(mysql_num_rows($res) == 0){
							
						}else{
							while($row = mysql_fetch_array($res)){
								$tmp .= "\t$row[0]\t$row[1]\t".trim($row[2])."\t".trim($row[3])."\t".trim($row[4])."\t$row[5]";
								$tmp .= "\t\t\t999999999\t1\t3";
								$tmp .= "\t\t\t\t\t999999999\t\t\t\t\r\n";
							}
							$this->send_file($tmp, "$fileHeat.txt");
						}
					}
					mysql_Free_result($res);
					
				}else{	// relay event
					
					// starts for each race (*.txt file)
					$tmp = "";
					/*$sql = "SELECT
							sf.Startnummer
							, ss.Bahn
							, sf.Name
							, '-'
							, v.Name
							, '-'
						FROM 
							runde as r
							, wettkampf as w
							, serie as s
							, serienstart as ss
							, start as st
							, staffel as sf
							, verein as v
						WHERE	s.xSerie = $rowHeat[0]
						AND	r.xWettkampf = w.xWettkampf
						AND	s.xRunde = r.xRunde
						AND	ss.xSerie = s.xSerie
						AND	st.xStart = ss.xStart
						AND	sf.xStaffel = st.xStaffel
						AND	v.xVerein = sf.xVerein";*/
					$sql = "SELECT sf.Startnummer, 
								   ss.Bahn, 
								   sf.Name, 
								   '-', 
								   v.Name, 
								   '-' 
							  FROM runde AS r 
						 LEFT JOIN wettkampf AS w USING(xWettkampf) 
						 LEFT JOIN serie AS s ON(r.xRunde = s.xRunde) 
						 LEFT JOIN serienstart AS ss USING(xSerie) 
						 LEFT JOIN start AS st USING(xStart) 
						 LEFT JOIN staffel AS sf USING(xStaffel) 
						 LEFT JOIN verein AS v USING(xVerein) 
							 WHERE s.xSerie = ".$rowHeat[0]."
                             ORDER BY ss.Position";
					$res = mysql_query($sql);
                     
					if(mysql_errno() > 0){
						AA_printErrorMsg(mysql_errno().": ".mysql_error());
					}else{
						if(mysql_num_rows($res) == 0){
							
						}else{
							while($row = mysql_fetch_array($res)){
								$tmp .= "\t$row[0]\t$row[1]\t".trim($row[2])."\t".trim($row[3])."\t".trim($row[4])."\t$row[5]";
								$tmp .= "\t\t\t999999999\t1\t3";
								$tmp .= "\t\t\t\t\t999999999\t\t\t\t\r\n";
							}
							$this->send_file($tmp, "$fileHeat.txt");
						}
					}
					mysql_Free_result($res);
					
				}
			}
			
		}
		
		mysql_query("UNLOCK TABLES");
	}
	
	/**
	* import results from alge
	* (*.txt file)
	* - param heat
	*/
	function import_heat_results($heat){
		$file = $this->make_filename(0, $heat);
		
		$cont = $this->get_file("$file.txt");
		
		if(!$cont){ return false; }
		return $cont;
	}
	
	/**
	* import contest information from alge
	* (*.rac file)
	* race terminator (flag official) is the manually exported file ['filmnbr'.off]
	* - param heat
	*/
	function import_heat_infos($heat){
		$file = $this->make_filename(0, $heat);
		
		$cont = $this->get_file("$file.rac", true); // load ini
		
		if(!$cont){ return false; }
		
		// check on finished results
		// if the manually exported file exists it is ok
		
		if(@is_file($this->path."/".substr($file,0,3).".off")){
			$cont['Official'] = true;
		}else{
			$cont['Official'] = false;
		}
		
		return $cont;
	}
	
	/**
	 * send file function
	 *
	 */
	function send_file($content, $filename){
		global $strErrFileOpenFailed;
		// save file
		
		if($this->connection == "local"){
			// copy file on a local disk or network share
			
			$fp = @fopen($this->path."/".$filename, 'w');
			if(!$fp){
				//AA_printErrorMsg($strErrFileOpenFailed.": ".$this->path."/".$filename);
				return false;
			}else{
				fputs($fp, $content);
				
				fclose($fp);
			}
			
		}elseif($this->connection == "ftp"){
			// send file per ftp on a server (e.g. on a scan-o-vision workstation)
			
			$fp = @fopen("./tmp/".$filename, 'w');
			if(!$fp){
				AA_printErrorMsg($strErrFileOpenFailed);
				return false;
			}else{
				fputs($fp, $content);
				
				fclose($fp);
			}
			
			$this->ftp->open_connection($this->host, $this->user, $this->pass);
			
			$this->ftp->put_file("./tmp/".$filename, $this->ftppath."/".$filename);
		}
	}
	
	/**
	 * get file function
	 *
	 * - race information in alge timing are stored in a ini format
	 */
	function get_file($filename, $ini = false){
		global $strErrFileOpenFailed;
		$tpath = "";
		// get file
		
		if($this->connection == "local"){
			
			$fp = @fopen($this->path."/".$filename, 'r');
			$tpath = $this->path."/";
			
		}elseif($this->connection == "ftp"){
			
			$this->ftp->open_connection($this->host, $this->user, $this->pass);
			
			$this->ftp->get_file("./tmp/".$filename, $this->ftppath."/".$filename);
			
			$fp = @fopen("./tmp/".$filename, 'r');
			$tpath = "./tmp/";
		}
		
		
		if(!$fp){
			if($this->si==false){ AA_printErrorMsg($strErrFileOpenFailed); }
			return false;
		}else{
			if($ini){ // parse ini
				@fclose($fp);
				return parse_ini_file($tpath.$filename, true);
			}
			$content = array();
			while(!feof($fp)){
				$buffer = fgets($fp, 4096);
				if(!empty($buffer)){
					$content[] = explode("\t", $buffer);
				}
			}
		}
		
		return $content;
	}
	
	function is_configured(){
		if($this->connection == "noconf"){
			return false;
		}else{
			return true;
		}
	}
}

?>
