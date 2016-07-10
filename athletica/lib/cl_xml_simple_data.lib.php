<?php

if (!defined('AA_CL_XML_DATA_LIB_INCLUDED'))
{
	define('AA_CL_XML_DATA_LIB_INCLUDED', 1);
}else{
	return;
}

/************************************
 *
 * XML_data
 *
 * Loads xml data from Alabus Verband and the online registration
 * system into Athletica. Also generates the xml result feed for
 * uploading to Alabus Verband.
 * (can open gz compressed xml files)
 *
/************************************/

//require("common.lib.php");
//require('cl_performance.lib.php');
//require('cl_timetable.lib.php');
require('meeting.lib.php');
 include('./config.inc.php');      
 
if(AA_connectToDB() == FALSE){ // invalid db connection
	return;
}

if(AA_checkMeetingID() == FALSE){		// no meeting selected
	return;		// abort
}

// global definition      
$discode = "";
$catcode = "";
$xDis = array();
$distype = "";   

//$athlete = array();  
//$account = array(); 
$arr_noCat = array();


class XML_simple_data {
	var $opentags = array();
	var $gzfp;
   	
	function load_xml_simple($file, $type, $mode='', $ukc_meeting){          
		global $arr_noCat ;      
        
        
		if($type != "regUKC"){ // unknown type of data
			return false;
		}
        
        $xml_simple = simplexml_load_file($file);       
		
        mysql_query("LOCK TABLES  
                        verein READ, verein WRITE, athlet WRITE, athlet READ, meeting READ,
                        wettkampf AS w READ,
                        disziplin_de AS d READ, disziplin_fr AS d READ, disziplin_it AS d READ, anmeldung READ, anmeldung WRITE,
                        kategorie AS k READ, start WRITE,  start READ ");    
             
        XML_regUKC($xml_simple, $ukc_meeting);     		
		
		mysql_query("UNLOCK TABLES");
		
		return $arr_noCat;
	}
	
	}



/* handling online registration data (UKC) ****************************************************************************************************/
//function XML_regUKC($xml_simple){

function XML_regUKC($xml_simple, $ukc_meeting){     
    
    global $arr_noCat, $discode,  $catcode, $xDis , $distype, $cfgUKC_disc;  
     
    $kidID = 0;
    $license = 0;
    $licensePaid = '';
    $announcement = '';
    $club = '';
    $firstname = '';
    $lastname = '';      

    
    // check if clubs are in table verein 
    foreach ($xml_simple->accounts->account as $account) {
        
        $club =  $account->accountCode; 
        $clubName =  mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($account->accountName))));               
        $clubNameShort =  mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($account->accountShort))));   
        
        if (empty($clubNameShort)){
               $clubNameShort = $clubName;
        }   
        
        $laenge = strlen($clubName);
        $laengeShort = strlen($clubNameShort); 
               
        $result = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club."'");
        if (mysql_errno() > 0){
                    AA_printErrorMsg("xml-19-".mysql_errno() . ": " . mysql_error());
        }else{
               if (mysql_num_rows($result) == 0) {     
               
                     $sql = "INSERT INTO verein SET
                                        Name = '" .  $clubName ."',   
                                        Sortierwert =  '" .  $clubNameShort ."',   
                                        xCode =  '" .  $club ."'";  
                     $result = mysql_query($sql);
                     if (mysql_errno() > 0){
                            AA_printErrorMsg("xml-19-".mysql_errno() . ": " . mysql_error());
                     }
                    
               }
        }  
    }        
    // announcement for each athlete
    foreach ($xml_simple->athletes->athlete as $athlete) {
          
            $kidID = 0;
            $license = 0;
            $licensePaid = '';     
            $announcement = '';
            $club = ''; 
            $clubnr = '';  
            $xDis = array(); 
            $xAthlete = 0;
            $xReg = 0;
            
            $street = '';     
            $zipCode = 0;     
            $city = '';     
            $clubName = '';     
            $email = '';     
            $canton ='';  
            $nationality = '';   
        
            foreach ($athlete->attributes() as $attr=>$value) {
                   
                    switch ($attr){
                           case 'license':      if ($value > 0) { 
                                                    $license = $value; 
                                                }
                                                break;
                           case 'licensePaid':  if ($value == 1){
                                                     $licensePaid = 'y';
                                                }
                                                else {
                                                      $licensePaid = 'n'; 
                                                }
                                                     
                                                break;
                           case 'kidID':        if ($value > 0) {
                                                    $kidID = $value; 
                                                }
                                                break;
                           case 'announcement': $announcement = $value; 
                                                break;
                    }    
            }  
            $meetingDate = ''; 
            $discode = 0;      
            
            $lastName =  mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($athlete->lastName))));               
            $firstName =  mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($athlete->firstName))));   
                                                                                                                        
            $birthDate =  $athlete->birthDate;
            $arr_birth = explode(".",$birthDate);
            $birthdate = $arr_birth[2] . "-" .  $arr_birth[0] . "-" . $arr_birth[1]; 
            $birthYear = $arr_birth[2];
            if (!empty($athlete->accountCode) ) {
                  $club = $athlete->accountCode;   
            }     
                               
            $sex = (str_replace("\r", "\n", trim(utf8_decode($athlete->sex))));               
            $nationality = (str_replace("\r", "\n", trim(utf8_decode($athlete->nationality))));                   
            
            $street = mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($athlete->personalData->street))));    
            $zipCode = (str_replace("\r", "\n", trim(utf8_decode($athlete->personalData->zipCode))));               
            $city = mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($athlete->personalData->city))));   
            $clubName = mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($athlete->personalData->club))));   
            $email = mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($athlete->personalData->email))));   
            $canton = (str_replace("\r", "\n", trim(utf8_decode($athlete->personalData->canton))));      
                                                                                                                    
            // get region
            $region = 0; 
            if (!empty($canton)) {                
                $sql_reg = "SELECT xRegion FROM region WHERE Anzeige = '" . $canton ."'";
                $res_reg = mysql_query($sql_reg);             
                if(mysql_errno() > 0) {   
                            AA_printErrorMsg("xml-1-".mysql_errno() . ": " . mysql_error());  
                    }
                else {
                    if (mysql_num_rows($res_reg) == 1) {
                        $row = mysql_fetch_row($res_reg);
                        $region = $row[0]; 
                    }
                }
            }
            else {
                 $canton = 0;
            }
           
            
            if (empty($zipCode)){
                $zipCode = 0;
            }
            
            // get category
            $currYear = date('Y');
            $age = $currYear - $birthYear;               
            
           
            if ($ukc_meeting == 'y'){         // normal UBS Kids Cup Meeting (only UBS Kids Cup)
                if ($age < 7){
                    $age = 7;        //  minimum age
                }
                $sql_cat = "SELECT xKategorie, Code FROM kategorie AS k WHERE k.alterslimite = " . $age . " AND k.Geschlecht = '" .$sex . "' AND k.UKC = 'y' AND k.aktiv = 'y'"; 
              }
            else {   //normal Meetring with UBS Kids Cup integration
                 if ($age <= 7){
                    $age = 9;        //  minimum age
                 }     
                 if ($age % 2 == 0){
                     $age++;
                 }              
                 $sql_cat = "SELECT xKategorie, Code FROM kategorie AS k WHERE k.alterslimite <= " . $age . " AND k.Geschlecht = '" .$sex . "' AND k.UKC = 'n' AND k.aktiv = 'y'";    
            }
            
            $res_cat = mysql_query($sql_cat);
            
            if(mysql_errno() > 0) {   
                        AA_printErrorMsg("xml-1-".mysql_errno() . ": " . mysql_error());  
                }
            else{
                 if (mysql_num_rows($res_cat) > 0)  {
                        $row_cat =  mysql_fetch_row($res_cat);  
                        $cat = $row_cat[1];
                        $xCat = $row_cat[0];  
                        
                        $selection = "";
                        if ($ukc_meeting == 'n') {
                            $selection = " AND (d.Code = " . $cfgUKC_disc[0] ." || d.Code = " . $cfgUKC_disc[1]  . " || d.Code = " . $cfgUKC_disc[2] . ") ";
                        } 
            
                        $sql = "SELECT 
                                    w.xWettkampf,
                                    w.Mehrkampfcode,
                                    k.xKategorie,
                                    m.DatumVon   
                                FROM
                                    wettkampf as w
                                    LEFT JOIN kategorie as k on (w.xKategorie = k.xKategorie )
                                    LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d on (w.xDisziplin = d.xDisziplin)
                                    LEFT JOIN meeting AS m ON (m.xMeeting = w.xMeeting)  
                                WHERE 
                                    w.xMeeting = ".$_COOKIE['meeting_id']."
                                    $selection
                                    AND k.Kurzname = '" .$cat ."'";
                       
                        $res = mysql_query($sql);
                        if ((mysql_num_rows($res) >= 1 && $ukc_meeting == 'y') || (mysql_num_rows($res) == 3 && $ukc_meeting == 'n')) {
                            while($row_dis = mysql_fetch_assoc($res)){
                                    $xDis[] = $row_dis['xWettkampf'];
                                    if  ($row_dis['Mehrkampfcode'] > 0) {
                                        $discode =  $row_dis['Mehrkampfcode'];     // same for all combined disciplines  
                                    }                       
                                    $catnr =   $row_dis['xKategorie']; 
                                    $meetingDate =  $row_dis['DatumVon'];    
                            } 
                        }  
                        else {                                    
                              
                            if ($ukc_meeting == 'y'){
                                 $_POST['combinedtype'] = 408;
                                 $_POST['cat'] = $xCat;   
                                AA_meeting_addCombinedEvent($_SESSION['meeting_infos']['Startgeld']/100,$_SESSION['meeting_infos']['Haftgeld']/100); 
                            } 
                            else {
                                 
                                AA_meeting_addUkcEvent($xCat, $_SESSION['meeting_infos']['Startgeld']/100,$_SESSION['meeting_infos']['Haftgeld']/100);      
                            }
                                                    
                            $res = mysql_query($sql);
                            if(mysql_errno() > 0) {   
                                    AA_printErrorMsg("xml-1-".mysql_errno() . ": " . mysql_error());  
                            }
                            if (mysql_num_rows($res) > 0) {
                                while($row_dis = mysql_fetch_assoc($res)){
                                    $xDis[] = $row_dis['xWettkampf'];
                                    if  ($row_dis['Mehrkampfcode'] > 0) {
                                        $discode =  $row_dis['Mehrkampfcode'];     // same for all combined disciplines  
                                    }                       
                                    $meetingDate =  $row_dis['DatumVon'];    
                                } 
                            }
                        }       
                        
                        if (!empty($club)) {
                            $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club."'");
                            if (mysql_errno() > 0){
                                AA_printErrorMsg("xml-19-".mysql_errno() . ": " . mysql_error());
                            }else{
                                  if (mysql_num_rows($result2) > 0) {
                                        $rowClub1 = mysql_fetch_array($result2);
                                        $clubnr = $rowClub1[0]; 
                                  }                                   
                            }   
                        }
                        elseif (!empty($clubName)) {                                                          // selfmade club
                                $result2 = mysql_query("SELECT xVerein FROM verein WHERE Name = '" . $clubName ."'");
                                if (mysql_errno() > 0){
                                    AA_printErrorMsg("xml-19-".mysql_errno() . ": " . mysql_error());
                                }else{
                                      if (mysql_num_rows($result2) > 0) {
                                            $rowClub1 = mysql_fetch_array($result2);
                                            $clubnr = $rowClub1[0]; 
                                      }                                                                              // insert club
                                      else {
                                            $sql = "INSERT into verein SET
                                                            Name = '" .  $clubName ."',
                                                            Sortierwert = '" .  $clubName ."'";
                                            mysql_query($sql);                 
                                            $clubnr = mysql_insert_id();               
                                      }                                   
                                }   
                        }
                        
                        if (empty($club) && empty($clubName) && $license == 0) {
                            $clubnr = 999999;
                        }
                      
                        // if club is valid, insert athlete  
                        if(is_numeric($clubnr)) { 
                                                                                   
                            // if athlet exist
                            if ($license > 0){
                                      $sql = "SELECT * FROM athlet WHERE Lizenznummer= " .  $license; 
                            }
                            else {                                   
                                   $sql = "SELECT * FROM athlet WHERE Name= '" .  $lastName ."' AND Vorname = '" .  $firstName ."' AND Geburtstag = '" . $birthdate ."'";  
                            }
                            
                            $res = mysql_query($sql);  
                           
                            if(mysql_errno() > 0){  
                                AA_printErrorMsg("xml-20-".mysql_errno().": ".mysql_error());
                            }else{     
                                 $row = mysql_fetch_array($res);                             
                                if (mysql_num_rows($res) == 0) {
                                    
                                    if ($license > 0){
                                         $licenseType = 1;
                                    }
                                    else {
                                         $licenseType = 3;   
                                    }                                      
                                   
                                    $sql = "INSERT IGNORE INTO athlet SET
                                                                        Name = '" .  $lastName ."',
                                                                        Vorname = '" .  $firstName ."',  
                                                                        Jahrgang = '" .  $birthYear ."', 
                                                                        Lizenznummer = " . $license ." , 
                                                                        Geschlecht = '" .  $sex ."', 
                                                                        Land = '" .  $nationality ."', 
                                                                        Geburtstag = '" .  $birthdate ."',
                                                                        Athleticagen = 'n',   
                                                                        Bezahlt = '" .  $licensePaid ."', 
                                                                        xVerein = '" .  $clubnr ."',                                                                  
                                                                        Lizenztyp = " . $licenseType . ",
                                                                        xRegion = " . $region . ",   
                                                                        Adresse = '" . $street . "',  
                                                                        Plz = " . $zipCode . ",  
                                                                        Ort = '" . $city . "',  
                                                                        Email = '" . $email ."'"; 
                                                                        
                                   
                                    mysql_query($sql);
                                   
                                    if(mysql_errno() > 0){                                                                            
                                        AA_printErrorMsg("xml-21-".mysql_errno().": ".mysql_error());
                                    }else{
                                            $xAthlete = mysql_insert_id();   
                                    }
                                }
                                else {
                                       if ($license > 0){                                       
                                    
                                                $sql = "UPDATE athlet SET                                                             
                                                            Land = '" .  $nationality ."',                                                                
                                                            Bezahlt = '" .  $licensePaid ."',      
                                                            xRegion = " . $region . ",   
                                                            Adresse = '" . $street . "',  
                                                            Plz = " . $zipCode . ",  
                                                            Ort = '" . $city . "',  
                                                            Email = '" . $email ."'
                                                            WHERE Lizenznummer= " .  $license;  
                                       }
                                       else {
                                              $sql = "UPDATE athlet SET  
                                                            Land = '" .  $nationality ."',                                                              
                                                            Bezahlt = '" .  $licensePaid ."',  
                                                            xRegion = " . $region . ",   
                                                            Adresse = '" . $street . "',  
                                                            Plz = " . $zipCode . ",  
                                                            Ort = '" . $city . "',  
                                                            Email = '" . $email ."'    
                                                            WHERE Name= '" .  $lastName ."' AND Vorname = '" .  $firstName ."' AND Geburtstag = '" . $birthdate ."'" ;  
                                                            
                                       }   
                                      
                                        mysql_query($sql);
                                        if(mysql_errno() > 0){                                              
                                            AA_printErrorMsg("xml-22-".mysql_errno().": ".mysql_error());
                                        }else{
                                                $xAthlete = $row[0];   
                                        }
                                }    
                            }
                        }
                        else {
                                // club not found
                                if (empty($club)) {
                                     if ($license > 0){
                                         if (!in_array($license,$arr_noCat)){   
                                            $arr_noCat['athClub'][] = $license;    
                                         }
                                     } 
                                     else {
                                          if (!in_array($kidID,$arr_noCat)){   
                                            $arr_noCat['athClub'][] = $kidID;   
                                          } 
                                     }
                                     
                                }
                                else {
                                    if ($license > 0) {
                                        if (!in_array($license,$arr_noCat)){   
                                            $arr_noCat['club'][] = $club;   
                                        }   
                                    }
                                    else {
                                          if (!in_array($kidID,$arr_noCat)){ 
                                             $arr_noCat['club'][] = $club;   
                                        }   
                                    }
                                }
                        }       
           
                        if($xAthlete > 0){
                            // check if already registered
                            $result = mysql_query("SELECT xAnmeldung FROM anmeldung WHERE xAthlet = $xAthlete AND xMeeting = ".$_COOKIE['meeting_id']."");
                            if(mysql_errno() > 0){
                                AA_printErrorMsg("xml-23-".mysql_errno().": ".mysql_error());
                            }else{
                                    if ($announcement == 1) {
                                        if(mysql_num_rows($result) == 0){ // not yet registered  
                                            if($xCat!=''){
                                                mysql_query("INSERT INTO anmeldung SET
                                                                                Startnummer = 0
                                                                                , xAthlet = $xAthlete
                                                                                , xMeeting = ".$_COOKIE['meeting_id']."
                                                                                , xKategorie = $xCat
                                                                                , KidID = $kidID 
                                                                                , Bezahlt = '" . $licensePaid ."'   
                                                                                , Angemeldet = '$announcement'");     
                                               
                                                if(mysql_errno() > 0){ 
                                                    AA_printErrorMsg("xml-24-".mysql_errno().": ".mysql_error());
                                                }else{
                                                        $xReg = mysql_insert_id();
                                                }
                                                                
                                            } 
                                        }else{ // registered
                                                    $row = mysql_fetch_array($result);
                                                    mysql_query("Update anmeldung SET
                                                                                Startnummer = 0    
                                                                                , xAthlet = $xAthlete
                                                                                , xMeeting = ".$_COOKIE['meeting_id']."
                                                                                , xKategorie = $xCat
                                                                                , KidID = $kidID 
                                                                                , Bezahlt = '" . $licensePaid ."'   
                                                                                , Angemeldet = '$announcement'   
                                                                                WHERE xAnmeldung = $row[0]");
                                                    if(mysql_errno() > 0){ 
                                                        AA_printErrorMsg("xml-25-".mysql_errno().": ".mysql_error());
                                                    }else{
                                                            $xReg = $row[0];
                                                    }   
                                        }  
                                    }
                                    if($xReg > 0){
                                        // check if athlete alredy starts for this discipline(s)
                                        foreach($xDis as $xDis1){
                                                // because we can get multiple disciplines (combined event),
                                                // it is nessesary to determinate distype and discode for each discipline
                                                // (catcode won't change)  
                                                $res_distype = mysql_query("
                                                                SELECT d.Typ, d.Code, d.Appellzeit, d.Stellzeit FROM 
                                                                        disziplin_" . $_COOKIE['language'] . " as d
                                                                        LEFT JOIN wettkampf as w  ON (w.xDisziplin = d.xDisziplin)
                                                                WHERE w.xWettkampf = $xDis1");
                                                if(mysql_errno() > 0){
                                                                AA_printErrorMsg("xml-26-".mysql_errno().": ".mysql_error());
                                                }else{
                                                        $row_distype = mysql_fetch_Array($res_distype);
                                                        $distype = $row_distype[0];
                                                        $temp_discode = $row_distype[1];  
                                                }
                                        
                                                $result = mysql_query("SELECT xStart FROM start WHERE xAnmeldung = $xReg and xWettkampf = $xDis1");
                                                if(mysql_errno() > 0){
                                                                AA_printErrorMsg("xml-27-".mysql_errno().": ".mysql_error());
                                                }else{
                                                        if(mysql_num_rows($result) == 0){ // not yet starting, add start     
                                                            $saison = $_SESSION['meeting_infos']['Saison'];
                                                            if ($saison == ''){
                                                                $saison = "O"; //if no saison is set take outdoor
                                                            } 
                                                            
                                                            mysql_query("INSERT INTO start SET
                                                                                        xWettkampf = $xDis1                                                                               
                                                                                        , xAnmeldung = $xReg
                                                                                        ");
                                                            if(mysql_errno() > 0){
                                                                            AA_printErrorMsg("xml-28-".mysql_errno().": ".mysql_error());
                                                            }
                                                        } 
                                                }  
                                        }   
                                    } // end xReg > 0   
                            }   
                        }  
           } 
           else
            {  // not category UBS kids cup 
               
               if (!in_array($birthYear,$arr_noCat)){
                    $arr_noCat['cat'][] = $birthYear;   
               }   
           } 
          
        }   // end foreach athlete   
    
    }   // end foreach athlete    
                      
} 








