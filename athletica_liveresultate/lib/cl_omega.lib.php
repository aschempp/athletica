<?php

if (!defined('AA_CL_OMEGA_LIB_INCLUDED'))
{
	define('AA_CL_OMEGA_LIB_INCLUDED', 1);
}else{
	return;
}

/************************************
 *
 * Omega time measurement
 *
 * Handles file import/export with the omega scan-o-vision software
 *
/************************************/

require("./lib/cl_ftp_data.lib.php");

class omega{
	
	var $connection;
	var $path;
	var $host;
	var $user;
	var $pass;
	var $ftppath;
	var $sponsor;
	
	var $ftp;
	
	var $si; // silent, no errors
	
	function omega($noerror = false){
		$this->si = $noerror;
		$this->get_configuration($_COOKIE['meeting_id']);
		
		$this->ftp = new ftp_data();
	}
	
	function set_allFiles(){
		$meeting = $_COOKIE['meeting_id'];
		
		$this->set_lstconc($meeting);
		$this->set_lstcat();
		$this->set_lstnat();
		$this->set_lstlong($meeting);
		$this->set_lststyle();
		$this->set_lstrace($meeting);
		$this->set_lsttitpr($meeting);
		$this->set_lststart($meeting);
		$this->set_lststatu();
	}
	
	function set_configuration($meeting){
		global $strOmegaPathWriteFailed, $strOmegaNoPath;
		
		//if(@is_dir($_POST['path'])){
			// test local path for writing
			$fp = fopen($_POST['path']."/test.txt",'w');
			if(!$fp){
				$GLOBALS['ERROR'] = $strOmegaPathWriteFailed;
			}else{
				fclose($fp);
				unlink($_POST['path']."/test.txt");
			}
			
			mysql_query("LOCK TABLES zeitmessung WRITE");
		
			$res = mysql_query("SELECT * FROM athletica.zeitmessung WHERE xMeeting = $meeting");
			if(mysql_errno() > 0){
				AA_printErrorMsg(mysql_errno().": ".mysql_error());
			}else{
				if(!get_magic_quotes_gpc()){
					$_POST['path'] = addslashes($_POST['path']);
					$_POST['ftppath'] = addslashes($_POST['ftppath']);
					$_POST['sponsor'] = addslashes($_POST['sponsor']);
				}
				if(mysql_num_rows($res) == 0){
					mysql_query("
						INSERT INTO zeitmessung
						SET	OMEGA_Verbindung = '".$_POST['connection']."'
							, OMEGA_Pfad = '".$_POST['path']."'
							, OMEGA_Server = '".$_POST['host']."'
							, OMEGA_Benutzer = '".$_POST['user']."'
							, OMEGA_Passwort = '".$_POST['pass']."'
							, OMEGA_Ftppfad = '".$_POST['ftppath']."'
							, OMEGA_Sponsor = '".$_POST['sponsor']."'
							, xMeeting = $meeting
						");
				}else{
					mysql_query("
						UPDATE athletica.zeitmessung
						SET	OMEGA_Verbindung = '".$_POST['connection']."'
							, OMEGA_Pfad = '".$_POST['path']."'
							, OMEGA_Server = '".$_POST['host']."'
							, OMEGA_Benutzer = '".$_POST['user']."'
							, OMEGA_Passwort = '".$_POST['pass']."'
							, OMEGA_Ftppfad = '".$_POST['ftppath']."'
							, OMEGA_Sponsor = '".$_POST['sponsor']."'
						WHERE	xMeeting = $meeting
						");
				}
			}
			
			mysql_query("UNLOCK TABLES");
		/*} else {
			$GLOBALS['ERROR'] = $strOmegaNoPath;
		}*/
	}
	
	/**
	* outputs the competitiors table
	* idBib; "Bib"; "Forename"; "Name"; "AbrevNat"; "AbrevCat"; "BirthDate"; "Licence"
	*
	* - $meeting		meeting id, gets the registered athletes for the current meeting only
	*
	* --> relays: add 999 to BIB because the start numbers are not unique over relays and persons
	*/
	function set_lstconc($meeting){
		$tmp = 'idBib; "Bib"; "Forename"; "Name"; "AbrevNat"; "AbrevCat"; "BirthDate"; "Licence"';
		
		mysql_query("LOCK TABLES anmeldung READ, athlet READ, kategorie READ, staffel READ");
		
		// get all athletes
		/*$sql = "SELECT anmeldung.Startnummer, athlet.Vorname, athlet.Name, athlet.xVerein, kategorie.Code, athlet.Lizenznummer, athlet.Geburtstag FROM 
				anmeldung
				LEFT JOIN athlet USING(xAthlet)
				LEFT JOIN kategorie ON anmeldung.xKategorie = kategorie.xKategorie
			WHERE
				anmeldung.xMeeting = ".$meeting;*/
		$sql = "SELECT anmeldung.Startnummer, athlet.Vorname, athlet.Name, athlet.xVerein, kategorie.Kurzname, athlet.Lizenznummer, athlet.Geburtstag FROM 
				athletica.anmeldung
				LEFT JOIN athletica.athlet USING(xAthlet)
				LEFT JOIN athletica.kategorie ON anmeldung.xKategorie = kategorie.xKategorie
			WHERE
				anmeldung.xMeeting = ".$meeting; // changed from category code to category short name
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				
				if($row['xVerein'] == 999999){
					$row['xVerein'] = $row['Land'];
				}
				
				if($row['Geburtstag'] == "0000-00-00"){
					$date = $row['Jahr'];
				}else{
					//$date = @date('j. M Y', strtotime($row['Geburtstag']));
					// the datefunction can not be used, because of the limitation under windows: no dates before 1970!
					$date = substr($row['Geburtstag'], 8,2).".".substr($row['Geburtstag'], 5,2).".".substr($row['Geburtstag'], 0,4);
				}
				
				$tmp .= "\r\n".$row['Startnummer'].";\"".$row['Startnummer']."\";\"".
					trim($row['Vorname'])."\";\"".trim($row['Name'])."\";\"".$row['xVerein']."\";\"".
					$row['Kurzname']."\";\"".$date."\";\"".$row['Lizenznummer']."\"";
				
			}
			
			$this->send_file($tmp, "LSTCONC.TXT");
		}
		
		// get all relays
		$sql = "SELECT staffel.Startnummer, staffel.Name, staffel.xVerein, kategorie.Kurzname FROM 
				athletica.staffel
				LEFT JOIN athletica.kategorie ON staffel.xKategorie = kategorie.xKategorie
			WHERE
				staffel.xMeeting = ".$meeting; // changed from category code to category short name
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				
				$tmp .= "\r\n".$row['Startnummer'].";\"".$row['Startnummer']."\";\"".
					trim($row['Name'])."\";\"-\";\"".$row['xVerein']."\";\"".
					$row['Kurzname']."\";\"-\";\"0\"";
				
			}
			
			$this->send_file($tmp, "LSTCONC.TXT");
		}
		
		mysql_querY("UNLOCK TABLES");
	}
	
	/**
	* outputs the category table
	* "Category"; "AbrevCat"
	*/
	function set_lstcat(){
		$tmp = '"Category"; "AbrevCat"';
		
		mysql_query("LOCK TABLES kategorie READ");
		
		$sql = "SELECT * FROM kategorie";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				
				//$tmp .= "\r\n\"".trim($row['Name'])."\";\"".$row['Code']."\"";
				$tmp .= "\r\n\"".trim($row['Name'])."\";\"".$row['Kurzname']."\""; // changed from category code to category short name
				
			}
			
			$this->send_file($tmp, "LSTCAT.TXT");
		}
		
		mysql_querY("UNLOCK TABLES");
	}
	
	/**
	* outputs the club/nation table
	* "Nation/Club"; "AbrevNat"; "Flag"
	*/
	function set_lstnat(){
		$tmp = '"Nation/Club"; "AbrevNat"; "Flag"';
		
		mysql_query("LOCK TABLES verein READ, land READ");
		
		$sql = "SELECT * FROM athletica.verein";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				
				$tmp .= "\r\n\"".trim($row['Name'])."\";\"".$row['xVerein']."\";\"\"";
				
			}
		}
		$sql = "SELECT * FROM athletica.land";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				
				$tmp .= "\r\n\"".$row['Name']."\";\"".$row['xCode']."\";\"\"";
				
			}
		}
		
		$this->send_file($tmp, "LSTNAT.TXT");
		mysql_querY("UNLOCK TABLES");
	}
	
	/**
	* outputs the race lengths (disciplines)
	* idLong; "Length"; Mlength; Relay
	*/
	function set_lstlong($meeting){
		global $cfgRoundStatus;
		
		$tmp = 'idLong; "Length"; Mlength; Relay';
		
		mysql_query("LOCK TABLES d READ, w READ");
		
		//$sql = "SELECT * FROM disziplin_" . $_COOKIE['language'] . "
		$sql = "SELECT DISTINCT d.xDisziplin, d.* FROM
				athletica.runde as r 
				LEFT JOIN athletica.wettkampf as w USING(xWettkampf)
				LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " as d USING(xDisziplin)
			WHERE
				w.xMeeting = $meeting
			AND	d.Strecke > 0 
			AND r.Status != ".$cfgRoundStatus['results_done']."
			ORDER BY
				d.Anzeige";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				
				$runners = $row['Staffellaeufer'];
				if($runners == 0){ $runners = 1; }
				$tmp .= "\r\n".$row['xDisziplin'].";\"".trim($row['Strecke'])."m\";".$row['Strecke']
					.";".$runners;
				
			}
			
			$this->send_file($tmp, "LSTLONG.TXT");
		}
		
		mysql_querY("UNLOCK TABLES");
	}
	
	/**
	* ouputs race styles (omega specific)
	* idStyle; "Style"; "AbrevStyle"
	*/
	function set_lststyle(){
		$tmp = 'idStyle; "Style"; "AbrevStyle"';
		
		mysql_query("LOCK TABLES omega_typ READ");
		
		$sql = "SELECT * FROM athletica.omega_typ";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				if(empty($row['OMEGA_Name'])){
					$row['OMEGA_Name'] = " ";
				}
				$tmp .= "\r\n".$row['xOMEGA_Typ'].";\"".$row['OMEGA_Name']."\";\"".$row['OMEGA_Kurzname']."\"";
				
			}
			
			$this->send_file($tmp, "LSTSTYLE.TXT");
		}
		
		mysql_querY("UNLOCK TABLES");
	}
	
	/**
	* outputs races
	* Event; Round; NbHeat; idLong; idStyle; "AbrevCat"; "Date"; "Time"; idJuge1; idJuge2[; raceID]
	*
	* - $meeting		meeting id
	*/
	function set_lstrace($meeting){
		global $cfgRoundStatus;
		
		$tmp = 'Event; Round; NbHeat; idLong; idStyle; "AbrevCat"; "Date"; "Time"; idJuge1; idJuge2';
		
		mysql_query("LOCK TABLES serie as s READ, runde as r READ, wettkampf as w READ, disziplin_de as d READ, disziplin_fr as d READ, disziplin_it as d READ, omega_typ as o READ, kategorie as k READ");
		
		$sql = "SELECT s.Film, w.xWettkampf, r.xRunde, s.xSerie, d.xDisziplin, d.xOMEGA_Typ, k.Kurzname, r.Datum, r.Startzeit FROM
				athletica.serie as s
				LEFT JOIN athletica.runde as r USING(xRunde)
				LEFT JOIN athletica.wettkampf as w USING(xWettkampf)
				LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " as d USING(xDisziplin)
				LEFT JOIN athletica.omega_typ as o USING(xOMEGA_Typ)
				LEFT JOIN athletica.kategorie as k ON w.xKategorie = k.xKategorie
			WHERE
				w.Zeitmessung = 1
			AND	w.xMeeting = ".$meeting." 
			AND r.Status != ".$cfgRoundStatus['results_done']."
			ORDER BY s.Film ASC
			"; // changed from category code to category short name
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				
				$date = date('j. M Y', strtotime($row['Datum']));
				
				$tmp .= "\r\n".$row['Film'].";1;1;"
					.$row['xDisziplin'].";".$row['xOMEGA_Typ'].";\"".$row['Kurzname']."\";\""
					.$date."\";\"".$row['Startzeit']."\";0;0";
				
			}
			
			$this->send_file($tmp, "LSTRACE.TXT");
		}
		
		mysql_querY("UNLOCK TABLES");
	}
	
	/**
	* outputs race titles (printed from scan-o-vision)
	* Event; Round; "Title"; "Sponsor"
	*
	* - $meeting		meeting id
	*/
	function set_lsttitpr($meeting){
		global $cfgRoundStatus;
		
		$tmp = 'Event; Round; "Title"; "Sponsor"';
		
		mysql_query("LOCK TABLES runde as r READ, wettkampf as w READ, serie as s, rundentyp_de as rt READ, rundentyp_fr as rt READ, rundentyp_it as rt READ");
		
		$sql = "SELECT s.Film, s.Bezeichnung, rt.Name, rt.Typ FROM
				athletica.serie as s
				LEFT JOIN athletica.runde as r USING(xRunde)
				LEFT JOIN athletica.wettkampf as w USING(xWettkampf)
				LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " as rt ON rt.xRundentyp = r.xRundentyp
			WHERE
				w.Zeitmessung = 1
			AND	w.xMeeting = ".$meeting." 
			AND r.Status != ".$cfgRoundStatus['results_done']."";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			$sponsor = "OMEGA timing";
			
			$sql_sponsor = "SELECT OMEGA_Sponsor 
							  FROM athletica.zeitmessung 
							 WHERE xMeeting = ".$meeting.";";
			$query_sponsor = mysql_query($sql_sponsor);
			
			if($query_sponsor && mysql_num_rows($query_sponsor)==1){
				$sponsor = stripslashes(mysql_result($query_sponsor, 0, 'OMEGA_Sponsor'));
			}
			
			while($row = mysql_fetch_assoc($res)){
				
				if($row['Typ'] == '0'){
					$row['Name'] = "Serie";
				}
				
				$tmp .= "\r\n".$row['Film'].";1;\"".$row['Name']." ".$row['Bezeichnung']."\";\""
					.$sponsor."\"";
				
			}
			
			$this->send_file($tmp, "LSTTITPR.TXT");
		}
		
		mysql_querY("UNLOCK TABLES");
	}
	
	/**
	* outputs starts
	* Event; Round; Heat; Lane; NRelay; idBib
	*
	* - $meeting		meeting id
	*/
	function set_lststart($meeting){
		global $cfgRoundStatus;
		
		$tmp = 'Event; Round; Heat; Lane; NRelay; idBib';
		
		mysql_query("LOCK TABLES serienstart as sst READ, start as st READ, serie as s READ, wettkampf as w READ, anmeldung as a READ
					, staffel as sf READ, runde AS r READ");
		
		$sql = "SELECT DISTINCT s.Film, w.xWettkampf, s.xRunde, s.xSerie, sst.Position, st.xStaffel, a.Startnummer, sf.Startnummer as Staffelnummer FROM
				athletica.serienstart as sst
				LEFT JOIN athletica.start as st using(xStart)
				LEFT JOIN athletica.serie as s on sst.xSerie = s.xSerie
				LEFT JOIN athletica.wettkampf as w on w.xWettkampf = st.xWettkampf
				LEFT JOIN athletica.anmeldung as a on st.xAnmeldung = a.xAnmeldung
				LEFT JOIN athletica.staffel as sf on st.xStaffel = sf.xStaffel
				LEFT JOIN athletica.runde as r on r.xWettkampf = w.xWettkampf
			WHERE
				w.Zeitmessung = 1
			AND	w.xMeeting = ".$meeting." 
			AND r.Status != ".$cfgRoundStatus['results_done']."";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			while($row = mysql_fetch_assoc($res)){
				
				if($row['xStaffel'] > 0){
					$tmp .= "\r\n".$row['Film'].";1;1"
						.";".$row['Position'].";".$row['xStaffel'].";".$row['Staffelnummer'];
				}else{
					$tmp .= "\r\n".$row['Film'].";1;1"
						.";".$row['Position'].";".$row['xStaffel'].";".$row['Startnummer'];
				}
				
			}
			
			$this->send_file($tmp, "LSTSTART.TXT");
		}
		
		mysql_querY("UNLOCK TABLES");
	}
	
	/**
	* should output made records (e.g. world records)
	* idStatus; "Status"; "AbrevStatus"
	* 
	* static, generated by athletica
	*/
	function set_lststatu(){
		
		$tmp = 'idStatus; "Status"; "AbrevStatus"';
		$tmp .= "\r\n1;;\"OK\"";
		$tmp .= "\r\n2;\"-2.0\";\"DNS\"";
		$tmp .= "\r\n3;\"-3.0\";\"DNF\"";
		$tmp .= "\r\n4;\"-1.0\";\"DQ\"";
		$tmp .= "\r\n5;;\"USE\"";
		
		$this->send_file($tmp, "LSTSTATU.TXT");
	}
	
	/**
	* should output made records (e.g. world records)
	*/
	/*function set_lstrec(){
		
	}*/
	
	/**
	* should output a competitor list for a scan-o-vision feature
	*/
	/*function set_concat(){
		
	}*/
	
	function get_configuration($meeting){
		global $strOmegaNoConf;
		
		mysql_query("LOCK TABLES zeitmessung READ");
		
		$res = mysql_query("SELECT * FROM athletica.zeitmessung WHERE xMeeting = $meeting");
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			
			if(mysql_num_rows($res) == 0 && $_POST['arg'] != 'change'){
				$this->connection = "noconf";
				if($this->si==false){ AA_printErrorMsg($strOmegaNoConf); }
			}else{
			
				$row = mysql_fetch_assoc($res);
				
				$this->connection = $row['OMEGA_Verbindung'];
				$this->path = $row['OMEGA_Pfad'];
				$this->host = $row['OMEGA_Server'];
				$this->user = $row['OMEGA_Benutzer'];
				$this->pass = $row['OMEGA_Passwort'];
				$this->sponsor = $row['OMEGA_Sponsor'];
				$this->ftppath = $row['OMEGA_Ftppfad'];
			}
		}
		
		mysql_query("UNLOCK TABLES");
	}
	
	function get_lststatu(){
		$cont = $this->get_file("LSTSTATU.TXT");
		
		if(!$cont){ return false; }
		
		$new = array();
		foreach($cont as $val){
			$new[trim($val[0])] = $val;
		}
		
		return $new;
		//print_r($cont);
	}
	
	function get_lstrslt(){
		$cont = $this->get_file("LSTRSLT.TXT");
		return $cont;
		//print_r($cont);
	}
	
	function get_lstrrslt(){
		$cont = $this->get_file("LSTRRSLT.TXT");
		
		if(!$cont){ return false; }
		
		$new = array();
		foreach($cont as $val){
			$new[trim($val[0])] = $val;
		}
		
		return $new;
		//print_r($cont);
	}
	
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
	
	function get_file($filename){
		global $strErrFileOpenFailed;
		// get file
		
		// skip if the file is allredy read on script-runtime 
		if (isset($GLOBALS['omega_lstfiles'][$filename])){
			return $GLOBALS['omega_lstfiles'][$filename];
		}
		$GLOBALS['omega_lstfiles'][$filename] = false;
		
		
		if($this->connection == "local"){

			if (!file_exists($this->path."/".$filename)){
				return false;
			}
			
			$fp = @fopen($this->path."/".$filename, 'r');
			
		}elseif($this->connection == "ftp"){
			
			$this->ftp->open_connection($this->host, $this->user, $this->pass);
			
			$this->ftp->get_file("./tmp/".$filename, $this->ftppath."/".$filename);
			
			$fp = @fopen("./tmp/".$filename, 'r');
		}
		
		
		if(!$fp){
			return false;
			AA_printErrorMsg($strErrFileOpenFailed);
		}else{
			$content = array();
			while(!feof($fp)){
				$buffer = fgets($fp, 4096);
				$t = false;
				$field = 0;
				$tmp = array();
				for($i =0; $i<strlen($buffer); $i++){
					if($buffer[$i] == '"'){
						if($buffer[$i+1] == '"' && $t){
							$tmp[$field] .= $buffer[$i];
							continue 2;
						}elseif($t){
							$t = false;
						}elseif(!$t){
							$t = true;
						}
					}
					elseif($buffer[$i] == ';' && !$t){
						$tmp[$field] = trim($tmp[$field]);
						$field++;
					}
					else{
						$tmp[$field] .= $buffer[$i];
					}
				}
				$tmp[$field] = trim($tmp[$field]);
				$content[] = $tmp;
			}
		}
		
		$GLOBALS['omega_lstfiles'][$filename] = $content;
		
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