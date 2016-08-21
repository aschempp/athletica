<?php

/**********
 *
 *	meeting_entry_add.php
 *	---------------------
 *	
 */   
     
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_dropdown.lib.php');

require('./lib/common.lib.php');
require('./lib/cl_performance.lib.php');
require('./lib/meeting.lib.php');  
          
if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

if (!empty($_POST['hidden_searchfield'])){
    $focus_license = true;
}   

// initialize variables
$category = 0;
if(!empty($_POST['category'])) {  
	$category = $_POST['category'];	// store selected category
}
else if(!empty($_GET['cat'])) {   
	$category = $_GET['cat'];	// store selected category
}

$club = 0;
if(!empty($_POST['club'])) {
	$club = $_POST['club'];	// store selected club
}
$club2 = 0;
if(!empty($_POST['club2'])) {
	$club2 = $_POST['club2'];	// store selected club2
}
$clubSearch = 0;
if(!empty($_POST['clubSearch'])) {
	$clubSearch = $_POST['clubSearch'];    // store selected club from search 
}
$clubtext = '';
if(!empty($_POST['clubtext'])) {
	$clubtext = $_POST['clubtext'];	// store entered club name
}
$region = 0;
if(!empty($_POST['region'])) {
	$region = $_POST['region'];	// store selected region
}

$athlete_id = 0;
if(!empty($_POST['athlete_id'])){
	$athlete_id = $_POST['athlete_id']; // store athleteid from search request
}
elseif(!empty($_POST['athleteId'])){
    $athlete_id = $_POST['athleteId']; // store athleteid from search request
}
elseif(!empty($_GET['athleteId'])){
    $athlete_id = $_GET['athleteId']; // store athleteid from search request
}  

$ukc_meeting = AA_checkMeeting_UKC() ;           

$AthReg = false;

if ($athlete_id > 0){
      
    // check if athlete already registered and had checked disciplines     
    $sql_ba = "SELECT
                     ba.license
               FROM
                    base_athlete AS ba
               WHERE
                    ba.id_athlete = $athlete_id";        
                                        
    $result_ba = mysql_query($sql_ba);
    if(!$result_ba){
                AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
    }else{
          if(mysql_num_rows($result_ba) > 0){
               $row_ba = mysql_fetch_row($result_ba);  
          }   
    }   
    
    $sql_at = "SELECT 
                    s.xStart ,
                    a.xAnmeldung ,
                    w.xWettkampf  ,
                    w.mehrkampfcode
                FROM 
                    athlet AS at 
                    INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet) 
                    INNER JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung) 
                    LEFT JOIN wettkampf as w ON (w.xWettkampf = s.xWettkampf)    
                WHERE Lizenznummer = $row_ba[0] AND a.xMeeting = " . $_COOKIE['meeting_id'];
    
    $result_at = mysql_query($sql_at);
    if(!$result_at){
                AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
    }else{
                if(mysql_num_rows($result_at) > 0){ // no athlete was found
                    $arr_checkedDisc = array();
                    $arr_checkedCombs = array(); 
                    $i=0;
                    $j=0; 
                    while ($row_at = mysql_fetch_row($result_at)){
                          if ($row_at[3] > 0){
                              $arr_checkedCombs[$j] = $row_at[2]; 
                              $j++;   
                          }
                          else {
                            $arr_checkedDisc[$i] = $row_at[2];
                            $i++; 
                          }    
                    }     
                }
    }  
    
     $sql_at = "SELECT                      
                    a.xAnmeldung 
                FROM 
                    athlet AS at 
                    INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet)  
                WHERE Lizenznummer = $row_ba[0] AND a.xMeeting = " . $_COOKIE['meeting_id'];
   
    $result_at = mysql_query($sql_at);
    if(!$result_at){
                AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
    }else{
                if(mysql_num_rows($result_at) > 0){ // no athlete was found
                      $AthReg = true; 
                }
    }
       
} 
 		 
// check if search from base is activated
$allow_search_from_base = "true";
if(isset($_COOKIE['asfb'])){
	$allow_search_from_base = $_COOKIE['asfb'];  
}
 
$discs_def = array();
$combs_def = array();
$catcode = '';
$birth_date = '';
$athletesex = '';

if ($_GET['argument'] == 'change_sex'){
        // hold entered data
        $athleteId = $_GET['athleteId'];
        $first = $_GET['firstname'];
        $name = $_GET['name'];
        $day = $_GET['day'];
        $month = $_GET['month'];
        $year = $_GET['year'];         
        $sex = $_GET['sex'];
        $athletesex = $_GET['sex']; 
        $country = $_GET['country'];
        $region = $_GET['region'];
        $clubtext = $_GET['clubtext'];    
        $club=$_GET['clubnr'];     
        $clubinfotext = $_GET['clubinfo'];
        $clubtext2 = $_GET['club2'];
        $startnbr = $_GET['startnbr'];
        $category = $_GET['category'];
        $catcode = $category;
        $birth_date = $_GET['year'];
        $team = $_GET['team'];
        $combined = $_GET['combined'];
        $licensenr =  $_GET['licNr'];   
        $licensePrinted =  $_GET['licPrinted'];
        $licenseType = $_GET['licType'];
        
        if(isset($_GET['combs'])){
            $combs = explode(';-;', $_GET['combs']);
            foreach($combs as $comb){
                $comb = trim($comb);
                $combn = explode(';', $comb);
                
                $combid = trim($combn[0]);
                $combbest = (isset($combn[1])) ? trim($combn[1]) : '';
                
                $combs_def[$combid] = $combbest;
            }
        }
        
        if(isset($_GET['discs'])){
            $discs = explode(';-;', $_GET['discs']);
            foreach($discs as $disc){
                $disc = trim($disc);
                $discn = explode(';', $disc);
                
                $discid = trim($discn[0]);
                $discbest = (isset($discn[1])) ? trim($discn[1]) : '';
                
                $discs_def[$discid] = $discbest;
            }
        }            
     }  


if(!empty($_GET['asfb'])) {  
	$allow_search_from_base = $_GET['asfb'];
	setcookie('asfb',$_GET['asfb'],time()+100000);
	
	// hold entered data
	$athleteId = $_GET['athleteId'];
    $first = $_GET['firstname'];  
	$name = $_GET['name'];
	$day = $_GET['day'];
	$month = $_GET['month'];
	$year = $_GET['year'];
	$sex = $_GET['sex'];
    $athletesex = $_GET['sex'];  
	$country = $_GET['country'];
	$region = $_GET['region'];
	$clubtext = $_GET['club'];
	$clubinfotext = $_GET['clubinfo'];
	$startnbr = $_GET['startnbr'];
	$category = $_GET['category'];
    $catcode = $_GET['category'];  
    $birth_date = $_GET['year'];   
	$team = $_GET['team'];
	$combined = $_GET['combined'];
	
	if(isset($_GET['combs'])){
		$combs = explode(';-;', $_GET['combs']);
		foreach($combs as $comb){
			$comb = trim($comb);
			$combn = explode(';', $comb);
			
			$combid = trim($combn[0]);
			$combbest = (isset($combn[1])) ? trim($combn[1]) : '';
			
			$combs_def[$combid] = $combbest;
		}
	}
	
	if(isset($_GET['discs'])){
		$discs = explode(';-;', $_GET['discs']);
		foreach($discs as $disc){
			$disc = trim($disc);
			$discn = explode(';', $disc);
			
			$discid = trim($discn[0]);
			$discbest = (isset($discn[1])) ? trim($discn[1]) : '';
			
			$discs_def[$discid] = $discbest;
		}
	}
}
       
// reload disciplines after POST
if(isset($_POST['events'])){    
	foreach($_POST['events'] as $e_id){
		$discs_def[$e_id] = (isset($_POST['topperf_'.$e_id])) ? $_POST['topperf_'.$e_id] : '';
	}
	foreach($_POST['eventscombtemp'] as $e_id){
		$discs_def[$e_id] = (isset($_POST['topperf_'.$e_id])) ? $_POST['topperf_'.$e_id] : '';
	}
}

if(isset($_POST['combined'])){
	foreach($_POST['combined'] as $c_id){
		$combs_def[$c_id] = (isset($_POST['topcomb_'.$c_id])) ? $_POST['topcomb_'.$c_id] : '';
	}
}

// check license type according to asfb
$licenseType = 0;
if($allow_search_from_base == "true"){
	// only normal licenses are allowed
	$licenseType = 1;
	setcookie('licType',$licenseType,time()+100000);
}else{
	/*if(isset($_COOKIE['licType'])){
		$licenseType = $_COOKIE['licType'];
	}else{
		$licenseType = 2; // day license
		setcookie('licType',$licenseType,time()+100000);
	}*/
	$licenseType = 3;
	setcookie('licType',$licenseType,time()+100000);
}

// if license type has been chosen manually
if(isset($_GET['licType'])){
	$licenseType = $_GET['licType'];
	setcookie('licType',$_GET['licType'],time()+100000);
	
	if($licenseType != 1){ // set asfb false if this is not a normal license
		$allow_search_from_base = "false";
		setcookie('asfb',"false",time()+100000);
	}
	
	$first = $_GET['firstname'];
	$name = $_GET['name'];
	$day = $_GET['day'];
	$month = $_GET['month'];
	$year = $_GET['year'];
	$sex = $_GET['sex'];
	$country = $_GET['country'];
	$region = $_GET['region'];
	$clubtext = $_GET['club'];
	$clubinfotext = $_GET['clubinfo'];
	$startnbr = $_GET['startnbr'];
	$category = $_GET['category'];
	$team = $_GET['team'];
	$combined = $_GET['combined'];
	
	if(isset($_GET['combs'])){
		$combs = explode(';-;', $_GET['combs']);
		foreach($combs as $comb){
			$comb = trim($comb);
			$combn = explode(';', $comb);
			
			$combid = trim($combn[0]);
			$combbest = (isset($combn[1])) ? trim($combn[1]) : '';
			
			$combs_def[$combid] = $combbest;
		}
	}
	
	if(isset($_GET['discs'])){
		$discs = explode(';-;', $_GET['discs']);
		foreach($discs as $disc){
			$disc = trim($disc);
			$discn = explode(';', $disc);
			
			$discid = trim($discn[0]);
			$discbest = (isset($discn[1])) ? trim($discn[1]) : '';
			
			$discs_def[$discid] = $discbest;
		}
	}
}

$nbrcheck = TRUE;
$nbr = 0;

// search request for license number
if(isset($_POST['searchfield'])){
	    if(is_numeric($_POST['searchfield'])) {    // search request for license number
        
		//remove trailing 0 and check-digit (barcode-reader will transmit this chars)
		$licensenr = ltrim(substr($_POST['searchfield'],0,11),'0');
		$search_occurred = true;  
		$searchparam = " WHERE license = " . $licensenr;     
	
		$sql = "SELECT * FROM base_athlete " . $searchparam; 
			//WHERE license = $licensenr";

		$result = mysql_query($sql);
		if(!$result){
				AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
		}else{
				if(mysql_num_rows($result) == 0){ // no athlete was found
					$search_match = false;
				}else{ // search match
					  if(mysql_num_rows($result) == 1) { 
						$row_athlete = mysql_fetch_assoc($result);
						$search_match = true;
						$lastname = $row_athlete['lastname'];
						$firstname = $row_athlete['firstname'];
						$athlete_id = $row_athlete['id_athlete'];
						$birth_date = $row_athlete['birth_date'];
						$nationality = $row_athlete['nationality'];
						$club = $row_athlete['account_code'];
						$club_name = '';
						$club2 = $row_athlete['second_account_code'];
						$club2_name = '';
						$clubinfo = $row_athlete['account_info'];
						$athletesex = $row_athlete['sex'];                          
                        $catcode = $row_athlete['license_cat']; 
                        if ($row_athlete['license_paid'] == 'n'){
                             $licensePrinted = $strNo;
                        }
                        else {
                            $licensePrinted =$strYes;
                        }
                       
                         
                        // get age of athlete  
                        $age = 0;
                        $currYear = date('Y');  
                        $age = $currYear -  substr($birth_date,0,4);   
                        
                                                
						//
						// get club id from club code
						//
						$result = mysql_query("select xVerein, Name from verein where xCode = '".$club."'");
						if(mysql_errno() > 0){
							AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
						}else{
							$rowClub1 = mysql_fetch_array($result);
							$club = $rowClub1[0];
							$club_name = $rowClub1[1];
							if(!empty($club2)){
								$result = mysql_query("select xVerein, Name from verein where xCode = '".$club2."'");
								 if(mysql_errno() > 0){
								 AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
								 }else{
									$rowClub2 = mysql_fetch_array($result);
									$club2 = $rowClub2[0];
									$club2_name = $rowClub2[1];
								 }
							}
						}
						mysql_free_result($result);
			
						//
						// get category id
						//
						$result = mysql_query("select k.xKategorie from kategorie as k where k.Code = '".$row_athlete['license_cat']."' AND k.UKC = 'n' AND k.aktiv = 'y'");
                       
						if(mysql_errno() > 0){
						AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
						}else{
                            if (mysql_num_rows($result) == 0){
                                $res = mysql_query("SELECT 
                                                        k.xKategorie,
                                                        k.Code 
                                                    FROM 
                                                        kategorie as k 
                                                    WHERE
                                                        k.Alterslimite >= ".$age ."
                                                        AND k.aktiv = 'y'
                                                        AND k.Geschlecht = '" . $athletesex ."' 
                                                    ORDER BY k.Alterslimite, k.Anzeige");
                                
                                if(mysql_errno() > 0){
                                    AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
                                }else{
                                    $rowCat = mysql_fetch_array($res); 
                                    $category = $rowCat[0]; 
                                    $catcode = $rowCat[1]; 
                                } 
                            }
                            else {
							    $rowCat = mysql_fetch_array($result);
							    $category = $rowCat[0];
                            }
						}
						mysql_free_result($result);
					  }
				}
		}
	
                                 
    // check if athlete already registered and has checked disciplines 
    $sql_at = "SELECT 
                    s.xStart ,
                    a.xAnmeldung ,
                    w.xWettkampf ,
                    w.mehrkampfcode
                FROM 
                    athlet AS at 
                    INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet) 
                    INNER JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung) 
                    LEFT JOIN wettkampf as w ON (w.xWettkampf = s.xWettkampf)    
                WHERE Lizenznummer = $licensenr AND a.xMeeting=" . $_COOKIE['meeting_id'];  
    
    $result_at = mysql_query($sql_at);
    if(!$result_at){
                AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
    }else{
                if(mysql_num_rows($result_at) > 0){ // no athlete was found
                    $arr_checkedDisc = array();
                    $arr_checkedCombs = array(); 
                    $i=0;
                    $j=0; 
                    while ($row_at = mysql_fetch_row($result_at)){
                          if ($row_at[3] > 0){
                              $arr_checkedCombs[$j] = $row_at[2]; 
                              $j++;   
                          }
                          else {
                            $arr_checkedDisc[$i] = $row_at[2];
                            $i++; 
                          }
                        
                    }     
                }
    }
    
     $sql_at = "SELECT                      
                    a.xAnmeldung 
                FROM 
                    athlet AS at 
                    INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet)  
                WHERE Lizenznummer = $licensenr AND a.xMeeting=" . $_COOKIE['meeting_id'];
   
    $result_at = mysql_query($sql_at);
    if(!$result_at){
                AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
    }else{
                if(mysql_num_rows($result_at) > 0){ // no athlete was found
                      $AthReg = true; 
                }
    } 
    }  // end not numeric   
    
    
}

 
// search request for club
if($_POST['arg']=="change_athlete"){  
		$licensenr = $_POST['athleteSearch'];
		$search_occurred = true;
	
		$sql = "SELECT * FROM base_athlete 
			WHERE license = $licensenr";
			$result = mysql_query($sql);
			if(!$result){
				AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error() . urlencode($sql));
			}else{
				if(mysql_num_rows($result) == 0){ // no athlete was found
					$search_match = false;
				}else{ // search match
						$row_athlete = mysql_fetch_assoc($result);
						$search_match = true;
						$lastname = $row_athlete['lastname'];
						$firstname = $row_athlete['firstname'];
						$athlete_id = $row_athlete['id_athlete'];
						$birth_date = $row_athlete['birth_date'];
						$nationality = $row_athlete['nationality'];
						$club = $row_athlete['account_code'];
						$club_name = '';
						$club2 = $row_athlete['second_account_code'];
						$club2_name = '';
						$clubinfo = $row_athlete['account_info'];
						$athletesex = $row_athlete['sex'];
                        $catcode = $row_athlete['license_cat'];  
                        if ($row_athlete['license_paid'] == 'n') {
                             $licensePrinted = $strNo;
                        }
                        else {
                              $licensePrinted = $strYes; 
                        }
                        
                        
                        // get age of athlete  
                        $age = 0;
                        $currYear = date('Y');  
                        $age = $currYear -  substr($birth_date,0,4);  
                       
						//
						// get club id from club code
						//
						$result = mysql_query("select xVerein, Name from verein where xCode = '".$club."'");
						if(mysql_errno() > 0){
							AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
						}else{
							$rowClub1 = mysql_fetch_array($result);
							$club = $rowClub1[0];
							$club_name = $rowClub1[1];
							if(!empty($club2)){
								$result = mysql_query("select xVerein, Name from verein where xCode = '".$club2."'");
								if(mysql_errno() > 0){
									AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
								}else{
									$rowClub2 = mysql_fetch_array($result);
									$club2 = $rowClub2[0];
									$club2_name = $rowClub2[1];
								}
							}
						}
						mysql_free_result($result);
			
						//
						// get category id
						//  
						$result = mysql_query("select k.xKategorie from kategorie as k where k.Code = '".$row_athlete['license_cat']."' AND k.UKC = 'n' AND k.aktiv = 'y'"); 
						if(mysql_errno() > 0){
							AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
						}else{
                             if (mysql_num_rows($result) == 0){
                                $res = mysql_query("SELECT 
                                                        k.xKategorie,
                                                        k.Code  
                                                    FROM 
                                                        kategorie as k 
                                                    WHERE
                                                        k.Alterslimite >= ".$age ."
                                                        AND k.aktiv = 'y'
                                                        AND k.Geschlecht = '" . $athletesex ."' 
                                                    ORDER BY k.Alterslimite, k.Anzeige");
                                
                                if(mysql_errno() > 0){
                                    AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
                                }else{
                                    $rowCat = mysql_fetch_array($res); 
                                    $category = $rowCat[0]; 
                                    $catcode = $rowCat[1]; 
                                }
                            }
                            else {
							    $rowCat = mysql_fetch_array($result);
							    $category = $rowCat[0];
                            }
						}
						mysql_free_result($result);
				}
			} 
            
      // check if athlete already registered and has checked disciplines 
    $sql_at = "SELECT 
                    s.xStart ,
                    a.xAnmeldung ,
                    w.xWettkampf ,
                    w.mehrkampfcode   
                FROM 
                    athlet AS at 
                    INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet) 
                    LEFT JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung) 
                    LEFT JOIN wettkampf as w ON (w.xWettkampf = s.xWettkampf)    
                WHERE Lizenznummer = $licensenr AND a.xMeeting=" . $_COOKIE['meeting_id'];
    
    $result_at = mysql_query($sql_at);
    if(!$result_at){
                AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
    }else{
                if(mysql_num_rows($result_at) > 0){ // no athlete was found
                    $arr_checkedDisc = array();
                    $arr_checkedCombs = array(); 
                    $i=0;
                    $j=0; 
                    while ($row_at = mysql_fetch_row($result_at)){
                          if ($row_at[3] > 0){
                              $arr_checkedCombs[$j] = $row_at[2]; 
                              $j++;   
                          }
                          else {
                            $arr_checkedDisc[$i] = $row_at[2];
                            $i++; 
                          } 
                    } 
                }
    }       
            
     $sql_at = "SELECT                      
                    a.xAnmeldung 
                    
                FROM 
                    athlet AS at 
                    INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet)                     
                    
                WHERE Lizenznummer = $licensenr AND a.xMeeting=" . $_COOKIE['meeting_id']; 
    
    $result_at = mysql_query($sql_at);
    if(!$result_at){
                AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
    }else{
                if(mysql_num_rows($result_at) > 0){ // no athlete was found
                      $AthReg = true; 
                }  
    }     
}

//
// clear form
//
if ($_POST['arg']=="cancel")
{   
	$club = 0;
	$clubtext = "";
	$category = 0;
	$region = 0;
	$_POST['licensenr'] = '';
	$athlete_id = 0;
	$_POST['day'] = "";
	$_POST['month'] = "";
	$_POST['sex'] = "";
}

//
// add athlete
//
if ($_POST['arg']=="add")
{   
    $_POST['name'] = (isset($_POST['name'])) ? $_POST['name'] : $_POST['name_hidden'];
    $_POST['first'] = (isset($_POST['first'])) ? $_POST['first'] : $_POST['firstname_hidden'];
    $_POST['year'] = (isset($_POST['year'])) ? $_POST['year'] : $_POST['year_hidden'];
    $_POST['day'] = (isset($_POST['day'])) ? $_POST['day'] : $_POST['day_hidden'];
    $_POST['month'] = (isset($_POST['month'])) ? $_POST['month'] : $_POST['month_hidden'];     
   
    $gebDate = sprintf("%04d-%02d-%02d", $_POST['year'], $_POST['month'], $_POST['day']);     
   
    if ( empty($_POST['hidden_clubathletesearch']) ){
         if (isset($_POST['hidden_asfb']) &&  $_POST['hidden_asfb'] == false || $_POST['asfb'] == false) {
            
            $sql_at = "SELECT 
                            s.xStart ,
                            a.xAnmeldung ,
                            w.xWettkampf ,
                            w.mehrkampfcode,
                            at.Lizenznummer  
                       FROM 
                            athlet AS at 
                            INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet) 
                            INNER JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung) 
                            LEFT JOIN wettkampf as w ON (w.xWettkampf = s.xWettkampf)    
                       WHERE at.Name = '".$_POST['name'] .
                            "' AND  at.Vorname = '".$_POST['first'] .
                            "' AND  at.Geburtstag = '".$gebDate."' AND a.xMeeting = ". $_COOKIE['meeting_id'];    
           
            $result_at = mysql_query($sql_at);
            if(!$result_at){
                AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
            }else{
                $AthNoDisc = true; 
                if(mysql_num_rows($result_at) > 0){ // no athlete was found
                
                    $arr_checkedDisc = array();
                    $arr_checkedCombs = array(); 
                    $i=0;
                    $j=0;    
                    while ($row_at = mysql_fetch_row($result_at)){
                          $AthNoDisc = false;   
                          if ($row_at[3] > 0){
                              $arr_checkedCombs[$j] = $row_at[2]; 
                              $j++;   
                          }
                          else {
                            $arr_checkedDisc[$i] = $row_at[2];
                            $i++; 
                          }  
                    }    
                }
                if( (isset($_POST['events']) && !empty($_POST['events']))
                            || (isset($_POST['eventscombtemp']) && !empty($_POST['eventscombtemp']))
                            || (isset($_POST['combined']) && !empty($_POST['combined'])) ){
                    $AthNoDisc = false;   
                }
                
                $sql_at = "SELECT                      
                                a.xAnmeldung 
                           FROM 
                                athlet AS at 
                                INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet)   
                           WHERE at.Name = '".$_POST['name'] .
                                "' AND  at.Vorname = '".$_POST['first'] .
                                "' AND  at.Geburtstag = '".$gebDate."' AND a.xMeeting = ". $_COOKIE['meeting_id'];  
    
                $result_at = mysql_query($sql_at);
                if(!$result_at){
                        AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
                }else{
                        if(mysql_num_rows($result_at) > 0){ // no athlete was found
                            $AthReg = true; 
                        }   
                }  
            }  
        }
        else { 
          
              $AthReg = false;    
              // check if athlete already registered  
             
              $sql_ba = "SELECT
                                ba.license
                         FROM
                                base_athlete AS ba
                         WHERE
                                ba.id_athlete = $athlete_id ";
              
              $result_ba = mysql_query($sql_ba);
              if(!$result_ba){
                    AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
              }else{
                    if(mysql_num_rows($result_ba) > 0){
                        $row_ba = mysql_fetch_row($result_ba);  
                    }   
              }   
    
              $sql_at = "SELECT 
                                s.xStart ,
                                a.xAnmeldung ,
                                w.xWettkampf ,
                                w.mehrkampfcode  
                         FROM 
                                athlet AS at 
                                INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet) 
                                INNER JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung) 
                                LEFT JOIN wettkampf as w ON (w.xWettkampf = s.xWettkampf)    
                         WHERE Lizenznummer = $row_ba[0] AND a.xMeeting = ". $_COOKIE['meeting_id'];
              
              $result_at = mysql_query($sql_at);
              if(!$result_at){
                    AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
              }else{
                    $AthNoDisc = true;  
                    if(mysql_num_rows($result_at) > 0){ // athlete was found
                        $arr_checkedDisc = array();
                        $arr_checkedCombs = array(); 
                        $i=0;
                        $j=0; 
                        while ($row_at = mysql_fetch_row($result_at)){
                                $AthNoDisc = false;   
                                if ($row_at[3] > 0){
                                    $arr_checkedCombs[$j] = $row_at[2]; 
                                    $j++;   
                                }
                                else {
                                    $arr_checkedDisc[$i] = $row_at[2];
                                    $i++; 
                                }  
                        } 
                    }
              } 
              
              if( (isset($_POST['events']) && !empty($_POST['events']))
                            || (isset($_POST['eventscombtemp']) && !empty($_POST['eventscombtemp']))
                            || (isset($_POST['combined']) && !empty($_POST['combined'])) ){
                    $AthNoDisc = false;   
                }  
    
              $sql_at = "SELECT                      
                            a.xAnmeldung 
                         FROM 
                            athlet AS at 
                            INNER JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet)  
                         WHERE Lizenznummer = $row_ba[0] AND a.xMeeting = ". $_COOKIE['meeting_id'];
               
              $result_at = mysql_query($sql_at);
              if(!$result_at){
                    AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
              }else{
                    if(mysql_num_rows($result_at) > 0){ // athlete was found
                        $AthReg = true; 
                    }   
              } 
              
         }  
      
       ?>
        <script type="text/javascript">  
            var athReg = "<?php echo $AthReg; ?>";
            var $AthNoDisc = "<?php echo $AthNoDisc; ?>";  
           
             if ($AthNoDisc) {
                check = alert("<?php echo $strAthleteNoDisciplin; ?>"); 
                
                if (check == false){      
                    window.open('meeting_entry_add.php', 'detail');   
                   }
            }
      
            if (athReg) {
                check = window.confirm("<?php echo $strAthleteRegistered; ?>"); 
                
                if (check == false){     
                    window.open('meeting_entry_add.php', 'detail');  
                   }
            }
            
    </script>   
    
      
   <?php  
       }     
	
	$_POST['clubtext'] = (isset($_POST['clubtext'])) ? $_POST['clubtext'] : $_POST['clubtext_hidden'];
	$_POST['countryselectbox'] = (isset($_POST['countryselectbox'])) ? $_POST['countryselectbox'] : $_POST['countryselectbox_hidden'];
	$_POST['categoryselectbox'] = (isset($_POST['categoryselectbox'])) ? $_POST['categoryselectbox'] : $_POST['categoryselectbox_hidden'];
	$_POST['sex'] = (isset($_POST['sex'])) ? $_POST['sex'] : (($_POST['sexm_hidden']==1 || empty($_POST['sexm_hidden']) ) ? 'm' : 'w');
	
    // check if club exist
   if($club == 0){
       $sqlClub="SELECT 
                        xVerein
                 FROM
                        verein
                 WHERE
                        Name='".$_POST['clubtext']."'";
     
       $resClub=mysql_query($sqlClub); 
       if(mysql_errno() > 0) {
               AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());                 
               $_POST['club'] = $club;
       }else{
            if (mysql_num_rows($resClub) > 0) {
                $rowClub=mysql_fetch_row($resClub);
                $club=$rowClub[0];
            }
       }
   }
    
	// check if user wants to create a new club
	if($club == 0){
		if(empty($_POST['clubtext'])){
			AA_printErrorMsg($strErrEmptyFields);
		}else{
			$_POST['clubtext'] = addslashes($_POST['clubtext']);
			mysql_query("INSERT INTO verein SET Name = '".$_POST['clubtext']."' 
					, Sortierwert = '".$_POST['clubtext']."'");
			if(mysql_errno() > 0) {
				AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
				$club = 0;
				$_POST['club'] = $club;
			}else{
				$club = mysql_insert_id();
				$_POST['club'] = $club;
			}
		}
	}else{
		$_POST['club'] = $club;
	}
	
	     
	$name = $_POST['name'];
	$first = $_POST['first'];
	$year = $_POST['year'];
    
    $athletesex = $_POST['sex'];         
    $category = $_POST['category'];
    $catcode = $category;
    $birth_date = $_POST['year'];
    
    $clubtext = $_POST['clubtext'];  
	
	// check if license number is valid when entering a new athlete with license
	$licInvalid = false;
	if($athlete_id == 0 && $_POST['licensetype'] == 1){
		if(empty($_POST['licensenr']) || !is_numeric($_POST['licensenr'])){
			$licInvalid = true;
		}
	}
	
	// Error: Empty fields
	if((empty($_POST['name']) || empty($_POST['first']) || empty($_POST['club']) || $licInvalid)
		&& $athlete_id == 0) // if athlete id is given, athlete exists in base data
	{
		if($licInvalid){
			AA_printErrorMsg($strErrLicenseNotValid);
		}else{
			AA_printErrorMsg($strErrEmptyFields);
		}
	}
	// OK: try to add item
	else
	{   
		// check on completly entered birthday
		$birthday = "0000-00-00";
		if(!empty($_POST['day']) && !empty($_POST['month']) && !empty($_POST['year'])){
			$_POST['year'] = AA_setYearOfBirth($_POST['year']);
			//$_POST['day'] = printf("[%02d]",  $_POST['day']);
			//$_POST['month'] = printf("[%02d]",  $_POST['month']);
			$birthday = $_POST['year']."-".$_POST['month']."-".$_POST['day'];
		}else{
			// correct two-digit year
			if(!empty($_POST['year'])) {
				$_POST['year'] = AA_setYearOfBirth($_POST['year']);
			}
		}
		// determine sex        
		//if(empty($_POST['sex'])){
		
			//  set gender belong to category 
			$res_sex = mysql_query("SELECT if(Code = 'MAN_' OR substring(Code,4,1) = 'M', 'm', 'w') as sex, Code
							FROM `kategorie` WHERE xKategorie = $category"); 
			$row_sex = mysql_fetch_array($res_sex);
			if(mysql_num_rows($res_sex) != 0 && $row_sex[1] != ""){
					$sex = $row_sex[0];
			}
			else { 
				$sex = $_POST['sex']; 
			}
		
	/*		    
			$res_c = mysql_query("select Code from kategorie where xKategorie = $category");
			$row_c = mysql_fetch_array($res_c);                    
			if(substr($row_c[0],0,1) == 'M' || substr($row_c[0],3,1) == 'M'){
				$sex = "m";
			}else{
				$sex = "w";
			}
	*/        
		
		//}else{
		//	$sex = $_POST['sex'];
		//}

		mysql_query("LOCK TABLES disziplin_de READ, disziplin_fr READ, disziplin_it READ, kategorie READ, meeting READ"
					. ", runde READ, team READ, verein READ, wettkampf READ"
					. ", anmeldung WRITE, athlet WRITE start WRITE, staffel READ");

		if(AA_checkReference("kategorie", "xKategorie", $category) == 0)	// Category does not exist (anymore)
		{
			AA_printErrorMsg($strCategory . $strErrNotValid);
		}
		else
		{
			if(AA_checkReference("meeting", "xMeeting", $_COOKIE['meeting_id']) == 0)	// Meeting does not exist (anymore)
			{
				AA_printErrorMsg($strMeeting . $strErrNotValid);
			}
			else
			{
				if(AA_checkReference("verein", "xVerein", $_POST['club']) == 0)	// Club does not exist (anymore)
				{
					AA_printErrorMsg($strClub . $strErrNotValid);
				}
				else
				{
					// Team selected
					if((!empty($_POST['team']))
						&& (AA_checkReference("team", "xTeam", $_POST['team']) == 0))
					{
						AA_printErrorMsg($strTeam . $strErrNotValid);
					}
					else
					{   
						$eventcheck = TRUE;       
						if(count($_POST['events']) > 0){  
							foreach($_POST['events'] as $event)
							{
								if(AA_checkReference("wettkampf", "xWettkampf", $event) == 0)	// Event does not exist (anymore)
								{
									$eventcheck = FALSE;
								}
							}
						}
						if($eventcheck == FALSE)	// At least one event does not exist (anymore)
						{
							AA_printErrorMsg($strEvent . $strErrNotValid);
						}
						else	// check startnbr
						{
							$startnbr = AA_getLastStartnbr();
							// startnbrs already assigned, none provided
							if(($startnbr > 0) && (empty($_POST['startnbr'])))
							{
								$startnbr++;		// use next available nbr
								// check if this nbr is used for a relay
								$result = mysql_query("SELECT xStaffel "
											. " FROM staffel"
											. " WHERE xMeeting=" . $_COOKIE['meeting_id']
											. " AND Startnummer=" . $startnbr);
								if(mysql_errno() > 0) {
									AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
								}
								else {
									if(mysql_num_rows($result) > 0) {
										$startnbr = AA_getNextStartnbr($startnbr);
									}
								}
							}
							// startnbrs already assigned, nbr provided
							else if(($startnbr > 0) && (!empty($_POST['startnbr'])))
							{
								$startnbr = $_POST['startnbr'];
								$nReg = false;
								$nRelay = false;
								
								$result = mysql_query("SELECT xAnmeldung "
											. " FROM anmeldung"
											. " WHERE xMeeting=" . $_COOKIE['meeting_id']
											. " AND Startnummer=" . $startnbr);
								if(mysql_errno() > 0) {
									AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
								}else{
									if(mysql_num_rows($result) > 0) { $nReg = true; }
								}
								mysql_free_result($result);
								
								// check if this nbr is used for a relay
								$result = mysql_query("SELECT xStaffel "
											. " FROM staffel"
											. " WHERE xMeeting=" . $_COOKIE['meeting_id']
											. " AND Startnummer=" . $startnbr);
								if(mysql_errno() > 0) {
									AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
								}else{
									if(mysql_num_rows($result) > 0) { $nRelay = true; }
								}
								mysql_free_result($result);
								
								if($nReg || $nRelay){
									$startnbr = AA_getNextStartnbr($startnbr);
									AA_printErrorMsg($strStartnumberLong . $strErrNotValid);
									$nbrcheck = FALSE;
									
								}	// ET DB error startnbr check
							}
							if($nbrcheck == TRUE)	// Valid startnbr available
							{
								//if(AA_checkAge($category, $_POST['year']) == FALSE)
								//{
								//	AA_printErrorMsg($strErrTooOld);
								//}
								//else
								//{
									$xAthlet = 0;
									$licnr = 0;
                                    
                                    
									//
									// Check if athlete's basic data already available
									//
									if($athlete_id > 0){ // athlete from base data
										$result = mysql_query("select * from base_athlete where id_athlete = $athlete_id");
										if(mysql_errno() > 0) {
											AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
										}else{
											$row = mysql_fetch_assoc($result);
											mysql_free_result($result);
											
											$licnr = trim($row['license']);
											
											// trimming name and firstname beacause of old entries
											// 		with ending newlines in text fields
											/*$result = mysql_query("
												SELECT
													xAthlet
												FROM
													athlet
												WHERE TRIM(Name) = \"" . ($row['lastname']) . "\"
												AND TRIM(Vorname) =\"" . $row['firstname'] . "\"
												AND Jahrgang='" . substr($row['birth_date'],0,4) . "'
												AND xVerein='" . $club . "'
												AND Lizenznummer = ".$row['license']."
												");*/
											$result = mysql_query("
												SELECT
													xAthlet 
												FROM
													athlet
												WHERE TRIM(Name) = \"" . ($row['lastname']) . "\"
												AND TRIM(Vorname) =\"" . $row['firstname'] . "\"
												AND Jahrgang='" . substr($row['birth_date'],0,4) . "'
												AND xVerein='" . $club . "'
												");
										}
									}else{ // athlete entered manually
										$result = mysql_query("
											SELECT
												xAthlet
											FROM
												athlet
											WHERE Name = \"" . ($_POST['name']) . "\"
											AND Vorname=\"" . $_POST['first'] . "\"
											AND Jahrgang='" . $_POST['year'] . "'
											AND xVerein='" . $_POST['club'] . "'
											");
									}
									
									if(mysql_errno() > 0) {
										$msg = mysql_errno() . ": " . mysql_error();
									}
									else if(mysql_num_rows($result) > 0)	// Athlete found
									{                                           
										$row = mysql_fetch_row($result);
										$xAthlet = $row[0];
										
										if($licnr>0){     
											$sql = "UPDATE athlet 
													   SET Lizenznummer = ".$licnr." 
													   , Geschlecht = '" .$sex."'                                                        
													 WHERE xAthlet = ".$xAthlet."";
											$query = mysql_query($sql);
										   
										}
										
										// update available athlete (athlete could be entered during an old meeting with other data)
										
										
										mysql_free_result($result);
									}
									//
									// Athlete not found: Add basic data
									//
									else
									{   
										if($athlete_id > 0){ // add from base data
											// transfer athlete data from base to table athlet   
											mysql_query("	INSERT IGNORE INTO athlet 
														(Name, Vorname, Jahrgang, 
														Lizenznummer, Geschlecht, Land, 
														Geburtstag, xVerein, xVerein2,
														xRegion, Lizenztyp)
													SELECT 
														lastname, firstname, substring(birth_date, 1,4), 
														license, if(sex='','$sex',sex), nationality, 
														birth_date, $club, $club2,
														'".$_POST['region']."', 1
													FROM
														base_athlete
													WHERE
														id_athlete = $athlete_id");  
										}else{ // add from manually entered data
											// check athleticagen
                                            if(!isset($_POST['licensenr'])){
                                                 $_POST['licensenr'] = 0;
                                            }
											$agen = 'y';
											if($_POST['licensetype'] == 1){ // normal license with number
												$agen = 'n';
											} 
											mysql_query("
												INSERT INTO athlet SET 
													Name=\"" . ($_POST['name']) . "\"
													, Athleticagen = '$agen'
													, Vorname=\"" . $_POST['first'] . "\"
													, Geburtstag=\"" . $birthday . "\"
													, Jahrgang='" . $_POST['year'] . "'
													, xVerein=" . $_POST['club']."
													, Land = '".$_POST['country']."'
													, Geschlecht = '".$sex."'
													, xRegion = ".$_POST['region']."
													, Lizenztyp = ".$_POST['licensetype']."
													, Lizenznummer = '".$_POST['licensenr']."'
												"); 
										}

										if(mysql_errno() > 0) {
											$msg = mysql_errno() . ": " . mysql_error();
										}
										else {
											$xAthlet = mysql_insert_id();	// get new ID
										}
									}
																			
									if($xAthlet > 0)	// valid athlete ID
									{
										$xAnmeldung = 0;
										//
										// Check if athlete already registered for this meeting
										//
										$result = mysql_query("
											SELECT
												xAnmeldung
											FROM
												anmeldung
											WHERE xAthlet='$xAthlet'
											AND xMeeting='" . $_COOKIE['meeting_id'] . "'
											");

										if(mysql_errno() > 0) {
											$msg = mysql_errno() . ": " . mysql_error();
										}
										else if(mysql_num_rows($result) > 0)	// Athlete found
										{
											$row = mysql_fetch_row($result);
											$xAnmeldung = $row[0];   
											// update available registration
											// don't update start number
											mysql_query("
												UPDATE anmeldung SET 
													Gruppe = '".strtoupper($_POST['combinedgroup'])."'
													, xKategorie = $category
													, xTeam = ". $_POST['team']."
													, Vereinsinfo = '".$_POST['clubinfotext']."'
												WHERE
													xAnmeldung = $xAnmeldung
												LIMIT 1
											");

											if(mysql_errno() > 0) {
												$msg = mysql_errno() . ": " . mysql_error();
											}
											
											mysql_free_result($result);
										}
										//
										// Entry not found: Add basic data
										//
										else
										{   	  
											mysql_query("
												INSERT INTO anmeldung SET 
													xAthlet = $xAthlet
													, Startnummer='" . $startnbr . "'
													, Gruppe = '".strtoupper($_POST['combinedgroup'])."'
													, xMeeting=". $_COOKIE['meeting_id'] ."
													, xKategorie = $category
													, xTeam = ". $_POST['team']."
													, Vereinsinfo = '".$_POST['clubinfotext']."'
											");

											if(mysql_errno() > 0) {
												$msg = mysql_errno() . ": " . mysql_error();
											}
											else {
												$xAnmeldung = mysql_insert_id();	// get new ID
											}
										}
										
										//
										// Add events
										//
										if($xAnmeldung > 0)
										{
											$eventlist = "";
											$sep = "";
											
											// check on combined events, add to post events
											if(count($_POST['combined']) > 0){
												foreach($_POST['combined'] as $c){
													
													list($cCat, $cCode) = split('_', $c);
													$res_comb = mysql_query("SELECT xWettkampf FROM
																	wettkampf
																WHERE Mehrkampfcode = $cCode
																AND xKategorie = $cCat
																AND xMeeting = ".$_COOKIE['meeting_id']);
													if(mysql_errno() > 0){
														AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
													}else{
														while($row_comb = mysql_fetch_array($res_comb)){
															
															// dont start here if x is top perf
															if($_POST['topperf_'.$row_comb[0]] == "x"){
																continue;
															}
															
															$_POST['events'][] = $row_comb[0];
															
														}
													}
													
													// save combined top performance
													if(!empty($_POST["topcomb_$cCat"."_".$cCode])){													
														mysql_query("UPDATE anmeldung SET
																BestleistungMK = '".$_POST["topcomb_$cCat"."_".$cCode]."'
                                                                , VorjahrLeistungMK = '".$_POST["hidden_topcomb_$cCat"."_".$cCode]."'
															WHERE	xAnmeldung = $xAnmeldung");
														
													}
												}
											} 
											
											if(count($_POST['events']) > 0){  
												foreach($_POST['events'] as $event)
												{
													// validate top performance (if any)
													$perf = 0;
                                                    $perfSeason = 0;
													$p = 'topperf_' . $event;
													$t = 'type_' . $event;													
													
													//
													// check for performance in base data
													//
													if($athlete_id > 0){
														// get cat and dis codes for this event
														// only if not entered manually
													//	if(empty($_POST[$p])){
                                                            
                                                            $saison = $_SESSION['meeting_infos']['Saison'];
                                                            if ($saison == ''){
                                                                $saison = "O"; //if no saison is set take outdoor
                                                            }
                                                            
															$res_code = mysql_query("
																SELECT d.Code, k.Code FROM
																	wettkampf as w
																	LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d USING(xDisziplin)
																	LEFT JOIN kategorie as k ON w.xKategorie = k.xKategorie
																WHERE
																	w.xWettkampf = $event");
															if(mysql_errno() > 0){
																AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
															}else{
																$row_code = mysql_fetch_array($res_code);
																
																$res_p = mysql_query("
																	SELECT season_effort, notification_effort FROM
																		base_performance
																	WHERE	id_athlete = $athlete_id
																	AND	discipline = ".$row_code[0] ."
                                                                    AND season = '$saison'");
																	//AND	category = '".$row_code[1]."'");
                                                                 
																if(mysql_errno() > 0){
																	AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
																}else{
																	$row_p = mysql_fetch_array($res_p);
																	if (mysql_num_rows($res_p) > 0){
                                                                        if(empty($_POST[$p])){
                                                                        
                                                                            $_POST[$p] = $row_p[1];         // best effort current or previous year (Indoor: best of both / Outdoor: best of outdoor)
                                                                           // $previousSeasonBest = $row_p[0];   // base_performance feld wird von alabus noch defniniert im 2012
                                                                             $previousSeasonBest = 0;
                                                                        } 
                                                                        else {
                                                                             //$previousSeasonBest = $row_p[0];  // base_performance feld wird von alabus noch defniniert im 2012
                                                                              $previousSeasonBest = 0;
                                                                        }   
                                                                    } 
                                                                    else {
                                                                        $previousSeasonBest = 0;
                                                                        $perfSeason = 0;
                                                                    } 
																}
															}
														//}
													}
													
													if(!empty($_POST[$p]))
													{
														if($_POST[$t] == 'time') {
															$secflag = false;
															if(substr($_POST[$p],0,2) >= 60){
																$secflag = true;
															}
															$pt = new PerformanceTime($_POST[$p], $secflag);
															$perf = $pt->getPerformance();
                                                            
                                                            $ps = new PerformanceTime($previousSeasonBest, $secflag);
                                                            $perfSeason = $ps->getPerformance();
														}
														else {
															$pa = new PerformanceAttempt($_POST[$p]);
															$perf = $pa->getPerformance();
                                                            
                                                            $ps = new PerformanceAttempt($previousSeasonBest);
                                                            $perfSeason = $ps->getPerformance();
														}
														if($perf == NULL) {	// invalid performance
															$perf = 0;
														}
                                                        if($perfSeason == NULL) {    // invalid performance
                                                            $perfSeason = 0;
                                                        }
													} 
                                                   
                                                    
													if(isset($_POST['start_'.$event])){   
														// add every event (no duplicates)
														mysql_query("
															INSERT INTO start SET
																xWettkampf = $event
																, xAnmeldung = $xAnmeldung
																, Bestleistung = $perf
                                                                , VorjahrLeistung = $perfSeason
														");
		
														if(mysql_errno() == 0) 		// no error
														{
															// check if event already started   
                                                            $sql = "SELECT 
                                                                            d.Name
                                                                    FROM 
                                                                            disziplin_" . $_COOKIE['language'] . " AS d   
                                                                            LEFT JOIN wettkampf AS w  ON (d.xDisziplin = w.xDisziplin)
                                                                            LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf)
                                                                    WHERE 
                                                                         (r.Status = 1 OR r.Status = 2 OR r.Status = 3 OR r.Status = 4 OR r.Status = 6) 
                                                                         AND  w.xWettkampf= " . $event;     
                                                           
                                                            $res = mysql_query($sql);       
		
															if(mysql_errno() > 0) {
																AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
															}
															// add warning message
															else {
																if (mysql_num_rows($res) > 0) {
																	$row = mysql_fetch_row($res);
																	$eventlist = $eventlist . $sep . $row[0];   
																	$sep = ", ";
																}
		
															}
															mysql_free_result($res);
														}
														// DB Error (but not duplicate entry
														else if(mysql_errno() != $cfgDBerrorDuplicate) {
															AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
														}	// ET DB error insert
													}
												}
											} // end if some events are selected
											// print event warning if required
											if(strlen($eventlist) > 0 ) {
												AA_printErrorMsg($strWarningEventInProgress . $eventlist);
											}
										}				// xAnmeldung not OK
										else {
											AA_printErrorMsg($msg);
										}				// ET xAnmeldung OK
										
										// clean data wich will be inserted in forms again
										$name='';
										$first='';
										$year='';
										$_POST['day'] = "";
										$_POST['month'] = "";
										$country='';        									  
										$category='';
										$athlete_id=0;
										$_POST['licensenr'] = '';
										$_POST['sex'] = "";
										$startnbr = "";  
										
										//if($allow_search_from_base == "true"){ // clean only if searched from base
											$club=0;
											$clubtext="";
                                            $clubtext2=""; 
											$region = 0;
											$_POST['clubinfotext'] = "";
                                            $_POST['clubtext'] = "";  
										//}
										
									}				// xAthlet not OK
									else {
										AA_printErrorMsg($msg);
									}		// ET xAthlet OK
								//}		// ET valid age for category
							}		// ET Startnbr valid; add or change
						}		// ET Event valid; add or change
					}		// ET Team valid; add or change
				}		// ET Club valid; add or change
			}		// ET Meeting valid
		}		// ET Category valid
		mysql_query("UNLOCK TABLES");
	} 
  
  
}



//
// function: outputs disciplines for choosing
//
function meeting_get_disciplines(){
	global $athlete_id, $cfgDisciplineType, $cfgEventType, $strEventTypeSingleCombined, 
		$strEventTypeClubCombined, $strDiscTypeTrack, $strDiscTypeTrackNoWind, 
		$strDiscTypeRelay, $strDiscTypeDistance, $discs_def, $combs_def, $first,$catcode,$birth_date,$athletesex;
	 
  
     
	$combs_def = array();	  // delete the selected disziplines from previous enrolement
	
    $order = "ASC";
    if ($athletesex == 'w'){
         $order = "DESC";  
    }                           
	
     $sql = "SELECT
            d.Kurzname as DiszKurzname
            , d.Typ as DiszTyp
            , w.xWettkampf
            , w.Typ
            , k.Kurzname as KatKurzname
            , k.Name
            , d.Code as DiszCode
            , k.Code as KatCode
            , k.xKategorie
            , w.Info
            , w.Mehrkampfcode
            , k.Geschlecht
            , k.Alterslimite
        FROM
            disziplin_" . $_COOKIE['language'] . " AS d
            LEFT JOIN wettkampf as w ON (w.xDisziplin = d.xDisziplin)
            LEFT JOIN kategorie as k ON (w.xKategorie = k.xKategorie)
        WHERE 
            w.xMeeting = " . $_COOKIE['meeting_id'] ."         
        ORDER BY
             k.Geschlecht $order, k.Alterslimite, k.Kurzname, w.Mehrkampfcode, d.Anzeige";                 
   
   $result = mysql_query($sql);       
   
	if(mysql_errno() > 0)
	{
		AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
	}

	$i=0;
	$d=0;
	$k=0;
	$last_cat = "";
	$comb = 0;
	$combCat = 0;
	$combDiv = "";
    
                             // fetch all rows and put the disciplines of the entry category on top
    $event_rows = array();
    $temp_rows = array();   
     
    // get age of athlete
    $age = 0;
    $currYear = date('Y');  
    $age = $currYear -  substr($birth_date,0,4); 
    $first = true;  
  
    while ($event_row = mysql_fetch_row($result)){   
        
         $first=false;       
         if(($catcode != '' && $event_row[8] == $catcode) || ($event_row[12] == $age && $event_row[11] == $athletesex)){  
                  $event_rows[] = $event_row;  
         }
         elseif ($event_row[12] > $age && $event_row[11] == $athletesex ) {  
                    if (sizeof($event_rows) == 0){
                        $event_rows[] = $event_row;      
                    } 
                    else {  
                            if (in_array($event_row[7] ,$event_rows[0]) ) {
                               $event_rows[] = $event_row;  
                            }   
                    }   
         }  
    }         
   
    $kName= $event_rows[0][4];         
   
       $sql ="SELECT  DISTINCT
            d.Kurzname as DiszKurzname
            , d.Typ as DiszTyp
            , w.xWettkampf
            , w.Typ
            , k.Kurzname as KatKurzname
            , k.Name
            , d.Code as DiszCode
            , k.Code as KatCode
            , k.xKategorie
            , w.Info
            , w.Mehrkampfcode
            , k.Geschlecht
            , k.Alterslimite   
        FROM
            disziplin_" . $_COOKIE['language'] . " AS d 
            LEFT JOIN wettkampf as w  ON (w.xDisziplin = d.xDisziplin)    
            LEFT JOIN kategorie as k ON (w.xKategorie = k.xKategorie)
        WHERE 
            w.xMeeting = " . $_COOKIE['meeting_id'] ."  
        ORDER BY
            k.Geschlecht ".$order.", k.Alterslimite DESC,  k.Kurzname, w.Mehrkampfcode, d.Anzeige";     
    
    $result = mysql_query($sql);    
   
    if(mysql_errno() > 0)
    {
        AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
    }
     while ($event_row = mysql_fetch_row($result)){ 
             if ($event_row[4] != $kName){
                  $event_rows[] = $event_row;
             }
         
     }   
   
    // display list of events
    // while ($event_row = mysql_fetch_row($result))  
    foreach($event_rows as $event_row) 
	{   
		if($last_cat != $event_row[4]){	// new row with title for separating categories
			$k++; // cat counter
			if($comb > 0){
				echo ( "</table></div></td>");
				$comb = 0;
			}
			if($last_cat != ""){
				printf("</tr>");
				echo "</table></div>\n";
			}
			echo "<div id='place$k'><table id='cat$event_row[8]'>\n";
			printf("<tr><td colspan=6 class='cat'>$event_row[5]</td></tr><tr>");
			$last_cat = $event_row[4];
			$d=0;
		}else
		
		if( $d % 3 == 0 ) {		// new row after three events
			if ( $i != 0 ) {
				printf("</tr>");	// terminate previous row
			}
			$i++;
			printf("<tr>\n");
		}
		
		$class = 'meter';
		if(($event_row[1] == $cfgDisciplineType[$strDiscTypeTrack])
			|| ($event_row[1] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
			|| ($event_row[1] == $cfgDisciplineType[$strDiscTypeRelay])
			|| ($event_row[1] == $cfgDisciplineType[$strDiscTypeDistance]))
		{
			$class = 'time';
		}
		
		//
		// get performance from base data if searched for an athlete
		//
		$effort = 0;
		if($athlete_id > 0){
			$saison = $_SESSION['meeting_infos']['Saison'];
			if ($saison == ''){
				$saison = "O"; //if no saison is set take outdoor
			}
		
			$sql = "
				SELECT 
					season_effort, notification_effort 
				FROM
					base_performance
				WHERE	id_athlete = $athlete_id
				AND	discipline = ".$event_row[6] . "
				AND season = '$saison'";
			$res = mysql_query($sql);
             
			if(mysql_errno() > 0){
				AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
				echo $sql;
			}else{
				$rowPerf = mysql_fetch_array($res);
                
                $effort = $rowPerf['notification_effort'];      // best effort current or previous year (Indoor: best of both / Outdoor: best of outdoor)       
                    
		}
		}
		$effort = ltrim($effort, "0:");
		//
		// merge the disciplines for a combined event
		//
		if($event_row[3] == $cfgEventType[$strEventTypeSingleCombined]){
			$d=1;
			if($comb != $event_row[10]){
				if($comb > 0){
					echo ("</table></div>");
					echo "</td></tr><tr>";
				}
				$comb = $event_row[10];
				$combCat = $event_row[8];
				$comb_res = mysql_query("SELECT Name FROM disziplin_" . $_COOKIE['language'] . " WHERE Code = $comb");
				$comb_row = mysql_Fetch_array($comb_res);
				
               global $AthReg;
               global $arr_checkedCombs;
                
                if ($AthReg) {
                    if (in_array($event_row[2], $arr_checkedCombs)) {
                        $checked = ' checked="checked"';    
                    }
                    else {
                        $checked = ' ';  
                    }  
                }
                else {
				    $checked = (isset($combs_def[$event_row[8]."_".$comb])) ? ' checked="checked"' : '';
                }
               
				$val = (isset($combs_def[$event_row[8]."_".$comb])) ? $combs_def[$event_row[8]."_".$comb] : '';
				
				//get performance from base -----------------------------------------------------------------------
				if ($val == '') { //dont overwrite value ... no guess what can be stored in $val before.. ***SBA***
					$sql = "
						SELECT 
							season_effort, notification_effort 
						FROM
							base_performance
						WHERE	id_athlete = $athlete_id
						AND	discipline = ".$comb . "
						AND season = '$saison'";
					$res_perf_comb = mysql_query($sql);
		            
					if(mysql_errno() > 0){
						AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
						echo $sql;
					}else{
						$row_perf_comb = mysql_fetch_array($res_perf_comb);   
                        
                        $val = $row_perf_comb['notification_effort'];        // best effort current or previous year (Indoor: best of both / Outdoor: best of outdoor)
                      //  $seasonval = $row_perf_comb['season_effort'];     // base_performance feld wird von alabus noch defniniert im 2012   
                        $seasonval = 0;     
                                                						
						$val = ltrim($val,"0:");
						$val = (substr($val,-2)==".0")?substr($val,0,-2):$val;
                        
                        $seasonval = ltrim($seasonval,"0:");
                        $seasonval = (substr($seasonval,-2)==".0")?substr($seasonval,0,-2):$seasonval;
					}	
				}
				//--------------------------------------------------------------------------------------------------
				   
				?>
				<td class='dialog-top' nowrap="nowrap" id="topperftd<?php echo $combCat.$comb ?>">
					<input type="checkbox" value="<?php echo $event_row[8]."_".$comb ?>" name="combined[]" id="combinedCheck<?php echo $event_row[8]; ?>_<?php echo $comb; ?>" 
						onclick="check_combined('<?php echo $event_row[8]."_".$comb ?>', this);
							validate_discipline(<?php echo "'".$combCat.$comb."', '".$event_row[11]."', ".$event_row[12] ?>,this.checked);"<?php echo $checked; ?>>
					<?php echo $comb_row['Name']; ?>
				</td>
				<td class='dialog-top' nowrap="nowrap">
					<input type="text" name="topcomb_<?php echo $event_row[8]."_".$comb ?>" id="topcomb<?php echo $event_row[8]."_".$comb ?>" size="5" value="<?php echo $val; ?>">
                    <input type="hidden" name="hidden_topcomb_<?php echo $event_row[8]."_".$comb ?>" id="hidden_topcomb<?php echo $event_row[8]."_".$comb ?>"  value="<?php echo $seasonval; ?>">
				</td>
				<td class='dialog' nowrap="nowrap" colspan="4" id='td_<?php echo $event_row[8]."_".$comb ?>'>
					<div id="div_<?php echo $event_row[8]; ?>_<?php echo $comb; ?>" style="position: relative; display: none;">
						<?php
						if($checked==' checked="checked"'){
							?>
							<script type="text/javascript">
								check_combined('<?php echo $event_row[8]; ?>_<?php echo $comb; ?>', document.getElementById('combinedCheck<?php echo $event_row[8]; ?>_<?php echo $comb; ?>'));
								validate_discipline('<?php echo $combCat; ?><?php echo $comb; ?>', '<?php echo $event_row[11]; ?>', <?php echo $event_row[12]; ?>,this.checked);
							</script>
							<?php
						}
						?>
						<table>
						<?php
			}
			
			$effort = (isset($discs_def[$event_row[2]]) && $effort=='') ? $discs_def[$event_row[2]] : $effort;
			
			// create text nodes for adding with javascript
			echo ("<tr><td class='dialog'>
					<input name='start_$event_row[2]' type='checkbox' id='start$event_row[2]'
								value='start_$event_row[0]' checked/>
								$event_row[0]
				</td>
				<td class='forms'  nowrap=\"nowrap\">
					<input type=\"hidden\" name=\"eventscombtemp[]\" value=\"$event_row[2]\">
					<input class='perf$class' type=\"text\" name=\"topperf_$event_row[2]\" value='$effort'
						id='topperf$event_row[2]' >
					<input name='type_$event_row[2]' type='hidden' value='$class' />
				</td></tr>\n");
			
		}else{  
			if($comb > 0){
				echo ( "</table></div>");
				$comb = 0;
			}
			global $AthReg;
            global $arr_checkedDisc;
			
            if ($AthReg) {
                if (in_array($event_row[2], $arr_checkedDisc)) {
                    $checked = ' checked="checked"';    
                }
                else {
                    $checked = ' ';  
                }  
            }
            else {
                $checked = (isset($discs_def[$event_row[2]]) && $first!='') ? ' checked="checked"' : '';   
            }
            
			
			
           
            $info = (strlen($event_row[9])==0)?"":"(".$event_row[9].")";
           
            ?>
            <td class="dialog-top" nowrap="nowrap" id='topperftd<?php echo $event_row[2]?>'>
                <input name='start_<?php echo $event_row[2]?>' type='hidden' id='start<?php echo $event_row[2]?>'
                                value='start_<?php echo $event_row[0]?>'/>
                <input name='events[]' type='checkbox' value='<?php echo $event_row['2'] ?>'
                 onclick="validate_discipline(<?php echo $event_row[2] .',\'' . $event_row[11].'\',' . $event_row[12].','?>this.checked)" <?php echo $checked; ?>/><?php echo $event_row[0] .$info?></td>
            <?php
			  			
			$effort = (isset($discs_def[$event_row[2]]) && $effort=='' && $first!='') ? $discs_def[$event_row[2]] : $effort;
			
			printf("<td class=\"dialog-top\" nowrap=\"nowrap\" style=\"display:none\">
				<input name='type_$event_row[2]' type='hidden' value='$class' />
				<input class='perf$class' name='topperf_$event_row[2]' type='text'
				value='$effort' maxlength='12' id='topperf$event_row[2]' /></td>");
	
			$d++;
		}// end if not combiend
	}// end loop
	
	if($comb > 0){
		echo( "</table></div></td>");
	}
	
	echo "</tr></table></div>\n";
	echo $combDiv;

	mysql_free_result($result);
   
}

//
// display page 
//

$page = new GUI_Page('meeting_entry_add',false,'stylesheet_small-fonts.css');
$page->startPage();

?>

	<script language="javascript">
	var allowSearch = <?php echo $allow_search_from_base ?>;
	var categories = new Array();
	var clubs = new Array();
	var clubsearch = "";
	
	var combined = new Array();
	
	<?php
	// generate an array with the club names for the IE workaround (located at bottom of the script part)
	/*$res = mysql_query("select Sortierwert, xVerein from Verein order by Sortierwert");
	$i=0;
	while($row_club = mysql_fetch_array($res)){
		$row_club[0] = strtr($row_club[0], "\n"," ");
		?>
		clubs[<?php echo $i ?>] = new Array(2);
		clubs[<?php echo $i ?>][0] = "<?php echo $row_club[1] ?>";
		clubs[<?php echo $i ?>][1] = '<?php echo addslashes($row_club[0]) ?>';
		<?php
		$i++;
	}
	mysql_Free_result($res);
	
	// generate a category array for selecting per year
	//$res = mysql_query("select xKategorie, Alterslimite from kategorie where Code != '' order by Alterslimite ASC, Code");
	$res = mysql_query("select xKategorie, Alterslimite from kategorie order by Alterslimite ASC, Code");
	$i=0;
	while($row_cat = mysql_fetch_array($res)){
		
		?>
		categories[<?php echo $i ?>] = new Array(2);
		categories[<?php echo $i ?>][0] = "<?php echo $row_cat[0] ?>";
		categories[<?php echo $i ?>][1] = '<?php echo $row_cat[1] ?>';
		<?php
		$i++;
	}
	mysql_Free_result($res);*/
	
	$sql = "SELECT xKategorie, 
				   Alterslimite, 
				   Geschlecht
			  FROM kategorie AS k
          WHERE k.ukc = 'n'
		  ORDER BY Geschlecht ASC, 
				   Alterslimite ASC;";
	$query = mysql_query($sql);
  
	
	$cats = array();
	while($row_cat = mysql_fetch_assoc($query)){
		$geschlecht = (strtoupper($row_cat['Geschlecht'])=='M') ? 1 : 0;
		$cats[$geschlecht][] = array(
			$row_cat['Alterslimite'], 
			$row_cat['xKategorie'], 
		);
	}
	
	$last = -1;
	foreach($cats as $geschlecht => $cat){
		if($geschlecht!=$last){
			?>
			categories[<?php echo $geschlecht; ?>] = new Array(<?php echo count($cat); ?>);  			
			<?php
			$last = $geschlecht;
		}
		
		foreach($cat as $index => $c){
			?>
			categories[<?php echo $geschlecht; ?>][<?php echo $index; ?>] = new Array();
			categories[<?php echo $geschlecht; ?>][<?php echo $index; ?>][0] = <?php echo $c[0]; ?>;
			categories[<?php echo $geschlecht; ?>][<?php echo $index; ?>][1] = <?php echo $c[1]; ?>;
			
			<?php
		}
        
	}
	?>
	
	//
	// ajax functions:
	//
	function createXMLHttpRequest() {   
		try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {}
		try { return new XMLHttpRequest(); } catch(e) {}
		alert("XMLHttpRequest not supported");
		return null;
	}
	var xhReq = createXMLHttpRequest();
	var requestTimer = null;
	var foundAthletes = 0;
	var foundAthlete = false;
	var foundClubs = 0;
	var foundClub = false;
	var sId = 0;
	
	function request_timeout(){
		xhReq.abort();
		set_status("Timeout beim Laden");
	}
	
	function valid_request(){
		if (xhReq.readyState != 4)  { return false; }
		clearTimeout(requestTimer);
		if (xhReq.status != 200)  {
			
			set_status("HTTP Fehler");
			return false;
		}
		res = xhReq.responseXML;
		state = res.getElementsByTagName("state")[0].firstChild.nodeValue;
		if(state == "error"){
			set_status("Error returned");
			return false;
		}else if(state == "ok"){
			set_status("OK");
			return true;
		}
	}
	
	function set_status(msg){ 
		top.frames[2].location.href = "status.php?msg="+msg;
	}
	
	// unescape and replace '+' with ' '
	function uncode(text){
		s = new String(unescape(text));
		text = s.replace(/\+/g, " ");
		return text;
	}
	
	//
	// base search functions using ajax
	//
	function base_search(){  
        if (document.getElementById('newyear_hidden').value != '' ) {
            return; 
        }
       
		if(allowSearch == false){ return; }
		
		if(xhReq.readyState > 0){
			xhReq.abort();
		}
		
		sName = document.getElementById('newname').value;
		sFirstname = document.getElementById('newfirstname').value;
		sYear = document.getElementById('newyear').value;
		
        document.getElementById('newyear_hidden').value=sYear;
      
		xhReq.open("get", "meeting_entry_base_search.php?name="+sName+"&firstname="+sFirstname+"&year="+sYear+"&id="+sId, true);
		requestTimer = setTimeout(request_timeout, 10000); //10sec        
		xhReq.onreadystatechange = base_search_result;
		xhReq.send(null);
		
		sId = 0;
        
	}
	
	function base_select(id){
		sId = id.value;
		base_search();
	}
	
	function base_search_result(){  
		if(!valid_request()){ return; }
		num = res.getElementsByTagName("num")[0].firstChild.nodeValue;
		base_search_show(num);
		
		if(foundAthlete && num <= 1){
			return;
		}else{
			foundAthlete = false;
		}
		
		if(num == 1){
			// found a match
           
			foundAthlete = true;
			n = new String(unescape(res.getElementsByTagName("name")[0].firstChild.nodeValue));
			f = new String(unescape(res.getElementsByTagName("firstname")[0].firstChild.nodeValue));
			s = new String(unescape(res.getElementsByTagName("clubname")[0].firstChild.nodeValue));
            s2 = new String(unescape(res.getElementsByTagName("clubname2")[0].firstChild.nodeValue));           
			ci = new String(unescape(res.getElementsByTagName("clubinfo")[0].firstChild.nodeValue));
			
			 document.getElementById('athleteId').value = res.getElementsByTagName("athleteId")[0].firstChild.nodeValue;         
            document.getElementById('newlicensenr').value = res.getElementsByTagName("license")[0].firstChild.nodeValue;   
            document.getElementById('newlicensePrinted').value = res.getElementsByTagName("licensePrinted")[0].firstChild.nodeValue;  
			document.getElementById('newname').value = unescape(res.getElementsByTagName("name")[0].firstChild.nodeValue);
			document.getElementById('newname').value = n.replace(/\+/g, " ");
			document.getElementById('newfirstname').value = unescape(res.getElementsByTagName("firstname")[0].firstChild.nodeValue);
			document.getElementById('newfirstname').value = f.replace(/\+/g, " ");
			document.getElementById('newyear').value = res.getElementsByTagName("year")[0].firstChild.nodeValue;
			document.getElementById('newday').value = res.getElementsByTagName("day")[0].firstChild.nodeValue;
			document.getElementById('newmonth').value = res.getElementsByTagName("month")[0].firstChild.nodeValue;
			document.getElementById('newathleteid').value = res.getElementsByTagName("id")[0].firstChild.nodeValue;
			document.getElementById('newclub2').value = res.getElementsByTagName("club2")[0].firstChild.nodeValue;
			document.getElementById('newclub').value = res.getElementsByTagName("club")[0].firstChild.nodeValue;
			document.getElementById('clubtext').value = s.replace(/\+/g, " ");
            document.getElementById('clubtext2').value = s2.replace(/\+/g, " ").substring(0,s2.length-1); // last char is always a 0   
			document.getElementById('clubinfotext').value = ci.replace(/\+/g, " ").substring(0,ci.length-1); // last char is always a 0
			document.getElementById('countryselectbox').value = res.getElementsByTagName("country")[0].firstChild.nodeValue;             
			document.getElementById('categoryselectbox').value = res.getElementsByTagName("category")[0].firstChild.nodeValue; 
                         
			if(res.getElementsByTagName("sex")[0].firstChild.nodeValue == 'm'){
				document.getElementById('sexm').checked = true;
				document.getElementById('sexm_hidden').value = 1;
				document.getElementById('sexw_hidden').value = 0;                  
			}else if(res.getElementsByTagName("sex")[0].firstChild.nodeValue == 'w'){
				document.getElementById('sexw').checked = true;
				document.getElementById('sexm_hidden').value = 0;
				document.getElementById('sexw_hidden').value = 1;                     
			}
			
			// try to insert top performances
			var tp = res.getElementsByTagName("performance")[0];
			
			for(i = 0; i<tp.childNodes.length; i++){
				disc = tp.childNodes[i].getAttribute('id');
				perf = tp.childNodes[i].firstChild.nodeValue;
				document.getElementById("topperf"+disc).value = perf;
			}
			
			document.entry.startnbr.focus();
			document.entry.startnbr.select();
          
			check_category();
		}else if(num == 0){
			top.frames[2].location.href = "status.php?msg=No athlete found";
		}else{
			top.frames[2].location.href = "status.php?msg="+num+" athletes found";
		}
                     
	}
	
	function base_search_show(num){
		
		var tbl = document.getElementById("regtable");
		for(var i = foundAthletes; i>0; i--){
			tbl.deleteRow(2);
		}
		foundAthletes = 0;
		
		if(num <= 10 && num > 1){			
			for(var i = 0; i<num; i++){
				var text = unescape(res.getElementsByTagName("name")[i].firstChild.nodeValue) +
					" " + unescape(res.getElementsByTagName("firstname")[i].firstChild.nodeValue) +
					" " + res.getElementsByTagName("day")[i].firstChild.nodeValue +
					"." + res.getElementsByTagName("month")[i].firstChild.nodeValue +
					"." + res.getElementsByTagName("year")[i].firstChild.nodeValue +
					" (" + unescape(res.getElementsByTagName("club")[i].firstChild.nodeValue) + ")";
				
				s = new String(text);
				text = s.replace(/\+/g, " ");
				
				
				var tr = tbl.insertRow(2);
				var TD1 = document.createElement("td");
				var TD2 = document.createElement("td");
				var TD2text = document.createTextNode(text);
				//var check = document.createElement("input");
				var onClickAtt = document.createAttribute("onclick");
				var colspanAtt = document.createAttribute("colspan");
				
				var check = document.getElementById("checkorig").cloneNode(true);
				check.style.visibility = "visible";
				check.value = res.getElementsByTagName("id")[i].firstChild.nodeValue;
				
				colspanAtt.nodeValue = "3";
				onClickAtt.nodeValue = "base_select("+res.getElementsByTagName("id")[i].firstChild.nodeValue+")";
				
				//check.type = "checkbox";
				//check.setAttributeNode(onClickAtt);
				
				TD1.appendChild(check);
				TD1.className = "forms_right";
				
				TD2.setAttributeNode(colspanAtt);
				TD2.appendChild(TD2text);
				TD2.className = "forms";
				
				tr.appendChild(TD1);
				tr.appendChild(TD2);
				
			}
			foundAthletes = num;
		}
	}
	
	//
	// club search functions using ajax
	//
	function club_search(){
		if(xhReq.readyState > 0){
			xhReq.abort();
		}
		
		sClub = document.getElementById('clubtext').value;
		
		xhReq.open("get", "meeting_entry_club_search.php?club="+sClub+"&id="+sId, true);
		requestTimer = setTimeout(request_timeout, 10000); //10sec
		xhReq.onreadystatechange = club_search_result;
		xhReq.send(null);
		
		sId = 0;
	}
	
	function club_search_result(){  
		if(!valid_request()){ 
			if(res){ // if result returned but error
				document.getElementById('clubtext').className="highlight_orange";
				document.getElementById('newclub').value = 0;
			}
			return; 
		}
		num = res.getElementsByTagName("num")[0].firstChild.nodeValue;
		club_search_show(num);
		
		// prevent from searching if already found one
		if(foundClub == true && num == 1){
			// if entered text does not match, set club for creating a new one
			s = new String(unescape(res.getElementsByTagName("sortvalue")[0].firstChild.nodeValue));
			if(document.getElementById('clubtext').value != s.replace(/\+/g, " ")){
				document.getElementById('clubtext').className="highlight_orange";
				document.getElementById('newclub').value = 0;
				return;
			}
		}else if(foundClub == true && num == 0){
			document.getElementById('clubtext').className="highlight_orange";
			document.getElementById('newclub').value = 0;
			return;
		}else if(num > 1){
			foundClub = false;
			document.getElementById('newclub').value = 0;
		}
		
		if(num == 1){
			// found club
			foundClub = true;
			s = new String(unescape(res.getElementsByTagName("sortvalue")[0].firstChild.nodeValue));
			document.getElementById('newclub').value = res.getElementsByTagName("id")[0].firstChild.nodeValue;
			document.getElementById('clubtext').value = s.replace(/\+/g, " ");
			document.getElementById('clubtext').className="highlight_green";
		}else if(num == 0){
			top.frames[2].location.href = "status.php?msg=No club found";
		}else{
			top.frames[2].location.href = "status.php?msg="+num+" clubs found";
		}
	}
	
	function club_select(id){ 
		sId = id.value;
		club_search();
	}
	
	function club_search_show(num){
		var tbl = document.getElementById("regtable");
		for(var i = foundClubs; i>0; i--){
			tbl.deleteRow(6);
		}
		foundClubs = 0;
		
		if(num <= 10 && num > 1){
			for(var i = 0; i<num; i++){
				
				var text = unescape(res.getElementsByTagName("sortvalue")[i].firstChild.nodeValue) +
					" (" + unescape(res.getElementsByTagName("name")[i].firstChild.nodeValue) + ")";
				
				s = new String(text);
				text = s.replace(/\+/g, " ");
				
				var tr = tbl.insertRow(6);
				var TD1 = document.createElement("td");
				var TD2 = document.createElement("td");
				var TD2text = document.createTextNode(text);
				
				var colspanAtt = document.createAttribute("colspan");
				
				var check = document.getElementById("checkorig2").cloneNode(true);
				check.style.visibility = "visible";
				check.value = res.getElementsByTagName("id")[i].firstChild.nodeValue;
				
				colspanAtt.nodeValue = "3";
				
				TD1.appendChild(check);
				TD1.className = "forms_right";
				
				TD2.setAttributeNode(colspanAtt);
				TD2.appendChild(TD2text);
				TD2.className = "forms";
				
				tr.appendChild(TD1);
				tr.appendChild(TD2);
				
			}
			foundClubs = num;
			
			// disable club info text field to skip with tab
			document.entry.clubinfotext.disabled = true;
		}else{
			document.entry.clubinfotext.disabled = false;
		}
	}
	
	//
	// club info search functions using ajax
	//
	function clubinfo_search(){
		if(xhReq.readyState > 0){
			xhReq.abort();
		}
		
		sClub = document.getElementById('clubinfotext').value;
		
		xhReq.open("get", "meeting_entry_clubinfo_search.php?clubinfo="+sClub+"&id="+sId, true);
		requestTimer = setTimeout(request_timeout, 10000); //10sec
		xhReq.onreadystatechange = clubinfo_search_result;
		xhReq.send(null);
		
		sId = 0;
	}
	
	function clubinfo_search_result(){
		if(!valid_request()){ 
			if(res){ // if result returned but error
				document.getElementById('clubinfotext').className="highlight_orange";
			}
			return; 
		}
		num = res.getElementsByTagName("num")[0].firstChild.nodeValue;
		clubinfo_search_show(num);
		
		// prevent from searching if already found one
		if(foundClub == true && num == 1){
			// if entered text does not match, set club for creating a new one
			s = new String(unescape(res.getElementsByTagName("name")[0].firstChild.nodeValue));
			if(document.getElementById('clubinfotext').value != s.replace(/\+/g, " ")){
				document.getElementById('clubinfotext').className="highlight_orange";
				return;
			}
		}else if(foundClub == true && num == 0){
			document.getElementById('clubinfotext').className="highlight_orange";
			return;
		}else if(num > 1){
			foundClub = false;
		}
		
		if(num == 1){
			// found club
			foundClub = true;
			s = new String(unescape(res.getElementsByTagName("name")[0].firstChild.nodeValue));
			document.getElementById('clubinfotext').value = s.replace(/\+/g, " ");
			document.getElementById('clubinfotext').className="highlight_green";
		}else if(num == 0){
			top.frames[2].location.href = "status.php?msg=No club info found";
		}else{
			top.frames[2].location.href = "status.php?msg="+num+" club infos found";
		}
	}
	
	function clubinfo_select(id){
		sId = id.value;
		clubinfo_search();
	}
	
	function clubinfo_search_show(num){
		var tbl = document.getElementById("regtable");
		for(var i = foundClubs; i>0; i--){
			tbl.deleteRow(6);
		}
		foundClubs = 0;
		
		if(num <= 10 && num > 1){
			for(var i = 0; i<num; i++){
				
				var text = unescape(res.getElementsByTagName("name")[i].firstChild.nodeValue);
				
				s = new String(text);
				text = s.replace(/\+/g, " ");
				
				var tr = tbl.insertRow(6);
				var TD1 = document.createElement("td");
				var TD2 = document.createElement("td");
				var TD2text = document.createTextNode(text);
				
				var colspanAtt = document.createAttribute("colspan");
				
				var check = document.getElementById("checkorig3").cloneNode(true);
				check.style.visibility = "visible";
				check.value = res.getElementsByTagName("id")[i].firstChild.nodeValue;
				
				colspanAtt.nodeValue = "3";
				
				TD1.appendChild(check);
				TD1.className = "forms_right";
				
				TD2.setAttributeNode(colspanAtt);
				TD2.appendChild(TD2text);
				TD2.className = "forms";
				
				tr.appendChild(TD1);
				tr.appendChild(TD2);
				
			}
			foundClubs = num;
		}
	}
	
	//
	// other functions
	//
	function check_category(asfb){ 
		// on change of category, set disciplines of current category on top  
        document.getElementById('argument').value='change_sex';  
        
		var cat = document.getElementById("categoryselectbox").value;
        
        var gLicNr = '';
        var gLicPrinted = '';
        var gAthleteId = 0;   
        /*
        if (document.getElementById("cat"+cat)) {
		    var tcat = document.getElementById("cat"+cat);
           
            var otherplace = '';
            if (tcat.parentNode) {
		        otherplace = tcat.parentNode;
            }              
		   
            // if cat is not already on top place            
            if(otherplace.id != "place1"){ 
		        otherplace.removeChild(tcat);
		        
		        //remove cat on first place
		        var firstcat = document.getElementById("place1").firstChild;
		        document.getElementById("place1").removeChild(firstcat);
		    
		        //add selected cat on top
		        document.getElementById("place1").appendChild(tcat);
		        otherplace.appendChild(firstcat); 
            }
        }    
        */
        if (document.getElementById('athleteId')){
            gAthleteId = document.getElementById('athleteId').value;
        } 
        
        gName = document.getElementById('newname').value;
         
        gFirstname = document.getElementById('newfirstname').value;
        gDay = document.getElementById('newday').value;
        
        gMonth = document.getElementById('newmonth').value;
        gYear = document.getElementById('newyear').value;
         
        gSex = (document.getElementById('sexm').checked) ? 'm' : ((document.getElementById('sexw').checked) ? 'w' : '');
        
        gCountry = document.getElementById('countryselectbox').value;
        gRegion = document.getElementById('regionselectbox').value;
         
        gClubText = document.getElementById('clubtext').value;
        gClub = document.getElementById('clubtext').value;   
        gClubNr= document.getElementById('newclub').value;   
       
        gClubInfo = document.getElementById('clubinfotext').value;
        gClub2 = document.getElementById('clubtext2').value; 
        gStartnbr = document.getElementById('startnbr').value;
        gCategory = document.getElementById('categoryselectbox').value;        
        gTeam = document.getElementById('teamselectbox').value;        
        gCombined = document.getElementById('combinedgroup').value;  
       
        if (document.getElementById('newlicensenr')){          
            gLicNr = document.getElementById('newlicensenr').value;  
        }
         
        if (document.getElementById('newlicensePrinted')) {
            gLicPrinted = document.getElementById('newlicensePrinted').value;   
        }       
        
        gComb = '';
        for(var a=0; a<document.getElementsByName('combined[]').length; a++){
            var tmp = document.getElementsByName('combined[]')[a];
            if(tmp.checked){
                var best = document.getElementById('topcomb'+tmp.value).value;
                gComb += ((gComb!='') ? ';-;' : '')+tmp.value+';'+best;
            }
        }
        gComb = (gComb!='') ? '&combs='+gComb : '';
       
        gDisc = '';
        for(var a=0; a<document.getElementsByName('events[]').length; a++){
            var tmp = document.getElementsByName('events[]')[a];
            if(tmp.checked){
                var best = document.getElementById('topperf'+tmp.value).value;
                gDisc += ((gDisc!='') ? ';-;' : '')+tmp.value+';'+best;
            }
        }
       
        for(var a=0; a<document.getElementsByName('eventscombtemp[]').length; a++){
            var tmp1 = document.getElementsByName('eventscombtemp[]')[a];
            var tmp = document.getElementById('start'+tmp1.value);
            if(tmp.checked){
                var best = document.getElementById('topperf'+tmp1.value).value;
                gDisc += ((gDisc!='') ? ';-;' : '')+tmp1.value+';'+best;
            }
        }
        gDisc = (gDisc!='') ? '&discs='+gDisc : '';   
        if (gSex == ''){
            gSex = 'm';
        } 
        
        gLizType = document.getElementById('licensetypeselectbox').value;  
                 
        if (typeof(asfb) == 'boolean') {
           document.location.href='meeting_entry_add.php?argument=change_sex&asfb='+asfb+'&name='+gName+'&firstname='+gFirstname+'&day='+gDay+'&month='+gMonth+'&year='+gYear+'&sex='+gSex+'&country='+gCountry+'&region='+gRegion+'&clubtext='+gClubText+'&club='+gClub+'&clubnr='+gClubNr+'&clubinfo='+gClubInfo+'&club2='+gClub2+'&startnbr='+gStartnbr+'&category='+gCategory+'&team='+gTeam+'&combined='+gCombined+gDisc+gComb+'&licNr='+gLicNr+'&licPrinted='+gLicPrinted+'&athleteId='+gAthleteId+'&licType='+gLizType;                                                                                                                                                                                
        }  
        else {
            document.location.href='meeting_entry_add.php?argument=change_sex&name='+gName+'&firstname='+gFirstname+'&day='+gDay+'&month='+gMonth+'&year='+gYear+'&sex='+gSex+'&country='+gCountry+'&region='+gRegion+'&clubtext='+gClubText+'&club='+gClub+'&clubnr='+gClubNr+'&clubinfo='+gClubInfo+'&club2='+gClub2+'&startnbr='+gStartnbr+'&category='+gCategory+'&team='+gTeam+'&combined='+gCombined+gDisc+gComb+'&licNr='+gLicNr+'&licPrinted='+gLicPrinted+'&athleteId='+gAthleteId+'&licType='+gLizType;                                                                                                                                                                                                                                                                         
        }
        
       	}
	
	function check_year(asfb){
        
		now = new Date();
		age = 0;
		year = document.getElementById("newyear").value;
		curr = now.getYear();
		if(curr < 999){
			curr += 1900;
		}
		if(year.length == 2){
			if(year > 30){
				year = "19"+year;
			}else{
				year = "20"+year;
			}
		}
		/*age = curr - year;
		for(var i=0; i<categories.length; i++){
			if(age <= categories[i][1]){
				if(document.getElementById("sexw").checked){
					document.getElementById("categoryselectbox").value = categories[i+1][0];
				}else{
					document.getElementById("categoryselectbox").value = categories[i][0];
				}
				break;
			}
		}*/
		if (document.getElementById("sexw").checked || document.getElementById("sexm").checked) {
            
		    age = curr - year;
		    var sex = (document.getElementById("sexw").checked) ? 0 : 1;
		    for(var a=0; a<categories[sex].length; a++){            
			    if(age<=categories[sex][a][0]){
				    document.getElementById("categoryselectbox").value = categories[sex][a][1];
				    break;
			    }
		    }   
            
		    check_category(asfb);   
        } 
	}
	
	function check_sex(asfb){
		 
		check_year(asfb);
		
	}
    	
	// show disciplines for selected combined event
	function check_combined(str, o){  
		var d = document.getElementById("div_"+str);
		var t = document.getElementById("td_"+str);
		if(o.checked){
			if(navigator.appName == "Microsoft Internet Explorer"){
				//t.appendChild(t);
			}
			d.style.display = '';
			//d.style.position = "relative";
		}else{
			d.style.display = 'none';
			//d.style.position = "absolute";
		}     
	}
	
	function change_asfb(asfb){
		
		gName = document.getElementById('newname').value;
		gFirstname = document.getElementById('newfirstname').value;
		gDay = document.getElementById('newday').value;
		gMonth = document.getElementById('newmonth').value;
		gYear = document.getElementById('newyear').value;
		gSex = (document.getElementById('sexm').checked) ? 'm' : ((document.getElementById('sexw').checked) ? 'w' : '');
		gCountry = document.getElementById('countryselectbox').value;
		gRegion = document.getElementById('regionselectbox').value;
		gClub = document.getElementById('clubtext').value;
		gClubInfo = document.getElementById('clubinfotext').value;
        gClub2 = document.getElementById('clubtext2').value;  
		gStartnbr = document.getElementById('startnbr').value;
		gCategory = document.getElementById('categoryselectbox').value;		
		gTeam = document.getElementById('teamselectbox').value;		
		gCombined = document.getElementById('combinedgroup').value;	
		
		gComb = '';
		for(var a=0; a<document.getElementsByName('combined[]').length; a++){
			var tmp = document.getElementsByName('combined[]')[a];
			if(tmp.checked){
				var best = document.getElementById('topcomb'+tmp.value).value;
				gComb += ((gComb!='') ? ';-;' : '')+tmp.value+';'+best;
			}
		}
		gComb = (gComb!='') ? '&combs='+gComb : '';
		
		gDisc = '';
		for(var a=0; a<document.getElementsByName('events[]').length; a++){
			var tmp = document.getElementsByName('events[]')[a];
			if(tmp.checked){
				var best = document.getElementById('topperf'+tmp.value).value;
				gDisc += ((gDisc!='') ? ';-;' : '')+tmp.value+';'+best;
			}
		}
		for(var a=0; a<document.getElementsByName('eventscombtemp[]').length; a++){
			var tmp1 = document.getElementsByName('eventscombtemp[]')[a];
			var tmp = document.getElementById('start'+tmp1.value);
			if(tmp.checked){
				var best = document.getElementById('topperf'+tmp1.value).value;
				gDisc += ((gDisc!='') ? ';-;' : '')+tmp1.value+';'+best;
			}
		}
		gDisc = (gDisc!='') ? '&discs='+gDisc : '';
		
		document.location.href='meeting_entry_add.php?asfb='+asfb+'&name='+gName+'&firstname='+gFirstname+'&day='+gDay+'&month='+gMonth+'&year='+gYear+'&sex='+gSex+'&country='+gCountry+'&region='+gRegion+'&club='+gClub+'&clubinfo='+gClubInfo+'&club2='+gClub2+'&startnbr='+gStartnbr+'&category='+gCategory+'&team='+gTeam+'&combined='+gCombined+gDisc+gComb;
		
	}
	
	function change_licType(o){
		
		gName = document.getElementById('newname').value;
		gFirstname = document.getElementById('newfirstname').value;
		gDay = document.getElementById('newday').value;
		gMonth = document.getElementById('newmonth').value;
		gYear = document.getElementById('newyear').value;
		gSex = (document.getElementById('sexm').checked) ? 'm' : ((document.getElementById('sexw').checked) ? 'w' : '');
		gCountry = document.getElementById('countryselectbox').value;
		gRegion = document.getElementById('regionselectbox').value;
		gClub = document.getElementById('clubtext').value;
		gClubInfo = document.getElementById('clubinfotext').value;
        gClub2 = document.getElementById('clubtext2').value;  
		gStartnbr = document.getElementById('startnbr').value;
		gCategory = document.getElementById('categoryselectbox').value;		
		gTeam = document.getElementById('teamselectbox').value;		
		gCombined = document.getElementById('combinedgroup').value;		
		
		gComb = '';
		for(var a=0; a<document.getElementsByName('combined[]').length; a++){
			var tmp = document.getElementsByName('combined[]')[a];
			if(tmp.checked){
				var best = document.getElementById('topcomb'+tmp.value).value;
				gComb += ((gComb!='') ? ';-;' : '')+tmp.value+';'+best;
			}
		}
		gComb = (gComb!='') ? '&combs='+gComb : '';
		
		gDisc = '';
		for(var a=0; a<document.getElementsByName('events[]').length; a++){
			var tmp = document.getElementsByName('events[]')[a];
			if(tmp.checked){
				var best = document.getElementById('topperf'+tmp.value).value;
				gDisc += ((gDisc!='') ? ';-;' : '')+tmp.value+';'+best;
			}
		}
		for(var a=0; a<document.getElementsByName('eventscombtemp[]').length; a++){
			var tmp1 = document.getElementsByName('eventscombtemp[]')[a];
			var tmp = document.getElementById('start'+tmp1.value);
			if(tmp.checked){
				var best = document.getElementById('topperf'+tmp1.value).value;
				gDisc += ((gDisc!='') ? ';-;' : '')+tmp1.value+';'+best;
			}
		}
		gDisc = (gDisc!='') ? '&discs='+gDisc : '';
		
		document.location.href = 'meeting_entry_add.php?licType='+o.value+'&name='+gName+'&firstname='+gFirstname+'&day='+gDay+'&month='+gMonth+'&year='+gYear+'&sex='+gSex+'&country='+gCountry+'&region='+gRegion+'&club='+gClub+'&clubinfo='+gClubInfo+'&club2='+gClub2+'&startnbr='+gStartnbr+'&category='+gCategory+'&team='+gTeam+'&combined='+gCombined+gDisc+gComb;
		
	}
	
	function check_birth_date(cur, num, next){
		var s = String(cur.value);
		if(s.length == num){
			next.focus();
		}
	}
	
	function validate_discipline(disc, sex, limit, o){   
       
		year = <?php echo date('Y') ?>;
		if(document.getElementById("newyear")){              
			athleteAge = year - document.getElementById("newyear").value;             
		}else{
			athleteAge = year - document.entry.year.value; // if searched for license number              
		}
		athleteSex = "";
		if(document.getElementById("sexw")){
			if(document.getElementById("sexw").checked){
				athleteSex = "w";
			}
			if(document.getElementById("sexm").checked){
				athleteSex = "m";
			}
		}else{
			athleteSex = document.entry.sex.value; // if searched for license number
		}
	         
       
        		
		if(o){  
           
			if(athleteSex != sex && athleteSex != ""){ // invalid gender
				document.getElementById("topperftd"+disc).className="highlight_red";
				return;
				}
		     
			if(athleteAge > limit && athleteAge != year){ // invalid age
				document.getElementById("topperftd"+disc).className="highlight_red";
		   			return;
				}
            document.getElementById("topperftd"+disc).className="dialog-top";       
		}
		else {  
            
		     if(athleteSex != sex && athleteSex != ""){ // invalid gender
                if (sex == 'w'){
                    if (document.getElementById("sexw").checked){ 
                          document.getElementById("topperftd"+disc).className="highlight_red";
                          return;
                    } 
                }
                 if (sex == 'm'){ 
                    if (document.getElementById("c").checked){  
                         document.getElementById("topperftd"+disc).className="highlight_red";
                         return;
                    }
                   
                } 
                
                }
             
            if(athleteAge > limit && athleteAge != year){ // invalid age
               document.getElementById("topperftd"+disc).className="highlight_red";
                       return;
                }
            document.getElementById("topperftd"+disc).className="dialog-top";       
		}
       
		
	} 
	
	//
	// internet explorer workaround for typing in a select box
	//
	function IE_selectsearch(e){
		if (!e)
			e = window.event;
		
		if(e.keyCode > 31){
			clubsearch = clubsearch+String.fromCharCode(e.keyCode);
		}
		
		for(var i=0; i<clubs.length ;i++){
			if(clubs[i][1].substr(0,clubsearch.length) == clubsearch && clubsearch.length != 0){
				//document.getElementById('newclub').value = clubs[i][0];
				
				window.setTimeout("IE_selectset("+clubs[i][0]+")", 50);
				break;
			}
		}
	}
	
	function IE_selectset(val){
		document.getElementById('newclub').value = val;
	}
	
	function IE_selectclear(){
		clubsearch = "";
	}  
    
    function check_athlete_disc(){           // check if athlete has checked disziplines
      
      var flag_checked = false;   
      
       for(var a=0; a<document.getElementsByName('events[]').length; a++){
            var tmp = document.getElementsByName('events[]')[a];
            if(tmp.checked){
                 flag_checked = true;
            }
        }  
      
       for(var a=0; a<document.getElementsByName('combined[]').length; a++){
            var tmp = document.getElementsByName('combined[]')[a];   
            if(tmp.checked){
               flag_checked = true;  
            }
        }  
       
       if (flag_checked) {
           return true;
       }
       else {
            check = confirm("<?php echo $strAthleteNoDisciplin; ?>");
            return check;     
       }     
} 
   
		
	</script>

<?php

if ($_POST['arg']=="add")
{   $arr_checkedDisc = array();
    $arr_checkedCombs = array(); 
	?>
<script>
	window.open("meeting_entrylist.php?item="
		+ <?php echo $xAnmeldung; ?> + "#" + <?php echo $xAnmeldung; ?>,
		"list");
</script>
	<?php
}

?> 
<?php   

if($search_occurred){ 
	if($search_match){ // output information and ask to add athlet    
       
        ?>
     <script type="text/javascript">  
            var athReg = "<?php echo $AthReg; ?>";
      
            if (athReg) {
                check = window.confirm("<?php echo $strAthleteRegistered; ?>"); 
                
                if (check == false){    
                    window.open('meeting_entry_add.php', 'detail'); 
                   }
            }
            
    </script>     
      
		
<table>
<form action='meeting_entry_add.php' method='post' name='selectcat'>
<input name='search' type='hidden' value='<?php echo $_POST['searchfield']; ?>' />  
</form>
<form action='meeting_entry_add.php' method='post' name='entry'>
	<tr>
		<td class='forms'>
			<button type='submit' onclick="return check_athlete_disc();">
				<?php echo $strEnter; ?>
			</button>
             <input name='hidden_searchfield' type='hidden' value='<?php echo $_POST['searchfield']; ?>' />  
             <input name='hidden_clubathletesearch' type='hidden' value='<?php echo $_POST['arg']; ?>' /> 
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	<tr>
</table>
<table class='dialog'>
	<th class='dialog'>  
		<?php echo $strName ?>
	</th>
	<td class='forms'>
		<?php echo $lastname ?>
	</td>
	<th class='dialog'>
		<?php echo $strFirstname ?>
	</th>
	<td class='forms'>
		<?php echo $firstname ?>
	</td>
</tr>
<tr>
	<th class='dialog'><?php echo $strBirthday; ?> (TT/MM/YYYY)</th>
	<td class='forms' colspan="1">
	<?php echo substr($birth_date,8) ?>/<?php echo substr($birth_date,5,2) ?>/<?php echo substr($birth_date,0,4) ?>
	</td>
	<?php
	if($_POST['sex'] == "m"){ $sexm = "checked"; }
	if($_POST['sex'] == "w"){ $sexw = "checked"; }
	?>
	<th class='dialog' ><?php echo $strSex ?></th>
	<td class='forms' >
		<?php
		if($athletesex=='m' || $athletesex=='M'){
			echo $strSexMShort;
		} else {
			echo $strSexWShort;
		}
		?>
	</td>
</tr>
<tr>
	<th class='dialog'><?php echo $strCountry; ?></th>
	<td class="forms"><?php echo $nationality ?></td>
	<th class='dialog'><?php echo $strRegion ?></td>
	<?php $dd = new GUI_RegionDropDown($region, ""); ?>
</tr>
<?php
if($nbr == 0) {			// start number not selected yet
	$nbr = AA_getLastStartnbr();
	if($nbr > 0) {		// startnumbers set	(otherwise use zero)
		$nbr++;			// get next higher nbr
	}
}
?>
<tr>
	<th class='dialog'><?php echo $strClub ?></th>
	<td class='forms'><?php echo $club_name ?></td>
	<th class='dialog'><?php echo $strClubInfo ?></th>
	<td class='forms'><?php echo ($clubinfo!='') ? $clubinfo : '-' ?></td>
</tr>  

<?php
 
    $lg = AA_meeting_getLG($club);   
   
   if  ( $club2_name != '') {            // second club exist
 ?>   
 
<tr>
    <th class='dialog'><?php echo $strClub2; ?></th>
    <td class='forms'><?php echo $club2_name; ?></td>
     

 <?php
    }
    elseif ($lg != '') {
      ?>   
 
<tr>
    <th class='dialog'><?php echo $strClub2; ?></th>
    <td class='forms'><?php echo $lg; ?></td>
    

 <?php 
    
    }
 ?>

    <th class='dialog'><?php echo $strLicensePrinted; ?></th>
    <td class='forms'><?php echo $licensePrinted; ?></td> 
    </tr> 

<tr>
	<th class='dialog'><?php echo $strStartnumberLong; ?></th>
	<td class='forms'>
		<input name='arg' type='hidden' value='add' />
		<input name='frombase' type='hidden' value='checked' />
		<input name='license' type='hidden' value='<?php echo $licensenr; ?>' />
		<input name='athlete_id' type='hidden' value='<?php echo $athlete_id; ?>' />
		<input name='category' id="cat_hidden" type='hidden' value='<?php echo $category; ?>' />
		<input name='club' type='hidden' value='<?php echo $club; ?>' />
		<input name='club2' type='hidden' value='<?php echo $club2; ?>' />
		<input name='year' type='hidden' value='<?php echo substr($birth_date,0,4) ?>' />
		<input name="sex" type="hidden" value="<?php echo $athletesex ?>">
		<input class='nbr' name='startnbr' id='startnbr' type='text'
			maxlength='5' value='0' /> 
		<?php echo $strNextNr.": ".$nbr; ?>
	</td>
	<th class='dialog'>
		<?php echo $strCategory ?>
	</th>  	
		<?php 
		$dd = new GUI_CategoryDropDown($category, "check_category($allow_search_from_base)", true); 
		?>  
</tr>
<tr>
	<th class='dialog'><?php echo $strTeam; ?></th>
	<?php
		$dd = new GUI_TeamDropDown($category, $club);
	?>
</tr>
<?php
if(!empty($club2) && false){ // not yet in use
	?>
	<tr>
	<th class='dialog'><?php echo $strTeam; ?> 2</th>
	<?php
		$dd = new GUI_TeamDropDown($category, $club2);
	?>
	</tr>
	<?php
}
?>
<tr>
	<th class='dialog' colspan='4'><?php echo $strDisciplines . " / "
			. " $strTopPerformance"; ?></th>
</tr>
<tr>
	<td class='forms' colspan='4'>
		<!--<table>-->
<?php 
   
	meeting_get_disciplines();
   
   
    
    
?>
			<!--</table>-->
		</td>
	</tr>
</table>
<br>
<table>
	<tr>
		<td class='forms'>
			<button type='submit'>
				<?php echo $strEnter; ?>
			</button>
             <input name='hidden_searchfield' type='hidden' value='<?php echo $_POST['searchfield']; ?>' />     
		</td>
	</tr>
</table>
</form>

		<?php
	}else{ // output message
		?>
		<p><?php if ($_POST['arg']=='change_clubSearch') 
					echo $strNoAthletesToClub; 
				 else 
					echo $strNoSuchLicense; 
		?></p>
		<script type="text/javascript">
		document.forms[0].search.focus();
		</script>
		<?php
	}
}else{ // else if search
	  if ( ($_POST['arg']!="change_clubSearch")  
			&& !( (isset($_POST['searchfield']) && (!is_numeric($_POST['searchfield']) ) )  ) ) {  
?>
     

<?php $page->printPageTitle($strNewEntry); ?>

<?php
//
// display data
//

	if($nbr == 0) {			// start number not selected yet
		$nbr = AA_getLastStartnbr();
		if($nbr > 0) {		// startnumbers set	(otherwise use zero)
			$nbr++;			// get next higher nbr
		}
	}
    
   
   
?>

<form action='meeting_entry_add.php' method='post' name='entry'>
<table>
	<tr>
		<td class='forms'>
			<button type='submit'>
				<?php echo $strSave; ?>
			</button>
             <input name='hidden_asfb' type='hidden' value='<?php echo $_GET['asfb']; ?>' />  
		</td>
		<td class='forms'>
			<button type='submit' onclick='document.forms[2].arg.value="cancel"'>
				<?php echo $strCancel; ?>
			</button>
		</td>
	</tr>
</table>
<br>
<table class='dialog' id="regtable">

<tr>
	<th class='dialog'><?php echo $strBaseFromBase; ?></th>
	<td class='forms'>
		<?php
		if($allow_search_from_base == "true"){
			$asfbCheck = "onclick=\"change_asfb('false')\" checked";
		}else{
			$asfbCheck = "onclick=\"change_asfb('true')\"";
		}
		?>
		<input type="checkbox" name="asfb" value="true" <?php echo $asfbCheck ?>></td>
	<th class='dialog'><?php echo $strLicenseType ?></th>
	<?php
	$dd = new GUI_ConfigDropDown('licensetype', 'cfgLicenseType', $licenseType, "change_licType(this)");
	?>
</tr>
<tr>
	<th class='dialog'><?php echo $strName; ?></th>
	<td class='forms'><input class='text' name='name' type='text'
		maxlength='25' value='<?php echo $name; ?>'
		id="newname" onkeyup="base_search()" /></td>
	<?php
	if($licenseType == 1){
		$licenseNrDisabled = "";
		if($allow_search_from_base == "true"){
			$licenseNrDisabled = "disabled";
		}
	?>
	<th class='dialog'><?php echo $strLicenseNr; ?></th>
	<td class='forms'><input name='licensenr' type='text' size='12'
		id="newlicensenr" value='<?php echo isset($_POST['licensenr']) ?  $_POST['licensenr'] : $licensenr ?>'
		<?php echo $licenseNrDisabled ?>/></td>
    <input type="hidden" id="newlicnr" name="newlicensenr" value="<?php echo $licNr ?>"> 
    <input type="hidden" id="athleteId" name="athleteId" value="<?php echo $athleteId ?>">         
	<?php
	}
	?>
</tr>
<tr>
	<th class='dialog'><?php echo $strFirstname; ?></th>
	<td class='forms' ><input class='text' name='first' type='text'
		maxlength='25' value='<?php echo $first; ?>'
		id="newfirstname" onkeyup="base_search()" /></td>
    <?php
    if($licenseType == 1){
        $licenseNrDisabled = "";
        if($allow_search_from_base == "true"){
            $licenseNrDisabled = "disabled";
        }
    ?>    
    <th class='dialog'><?php echo $strLicensePrinted; ?></th>
    <td class='forms'><input name='licensePrinted' type='text' size='4'
        id="newlicensePrinted" value='<?php echo $licensePrinted; ?>'
        <?php echo $licenseNrDisabled ?>/></td>
     <input type="hidden" id="newlicPrinted" name="newlicensePrinted" value="<?php echo $licPrinted ?>">     
   <?php
    }
    ?>
</tr>
<tr>
	<?php
	$day = (isset($_POST['day'])) ? $_POST['day'] : $day;
	$month = (isset($_POST['month'])) ? $_POST['month'] : $month;
	?>
	<th class='dialog'><?php echo $strBirthday; ?> (TT/MM/YYYY)</th>
	<td class='forms' colspan="1">
	<input type="hidden" name="name_hidden" id="newname_hidden" value="">
	<input type="hidden" name="firstname_hidden" id="newfirstname_hidden" value="">
	<input type="hidden" name="year_hidden" id="newyear_hidden" value="<?php echo $year; ?>">
	<input type="hidden" name="day_hidden" id="newday_hidden" value="">
	<input type="hidden" name="month_hidden" id="newmonth_hidden" value="">
	<input type="hidden" name="clubtext_hidden" id="clubtext_hidden" value="">
	<input type="hidden" name="countryselectbox_hidden" id="countryselectbox_hidden" value="">
	<input type="hidden" name="categoryselectbox_hidden" id="categoryselectbox_hidden" value="">
	<input type="hidden" name="sexm_hidden" id="sexm_hidden" value="">
	<input type="hidden" name="sexw_hidden" id="sexw_hidden" value="">
    
    
	
	<input class='nbr' name='day' type='text'
		maxlength='2' value='<?php echo $day; ?>'
		id="newday" onkeyup="check_birth_date(document.entry.day,2,document.entry.month)">
	<input class='nbr' name='month' type='text'
		maxlength='2' value='<?php echo $month;?>'
		id="newmonth" onkeyup="check_birth_date(document.entry.month,2,document.entry.year)" >
	<input name='year' type='text'
		maxlength='4' value='<?php echo $year; ?>'
		id="newyear" onkeyup="base_search()" size="4" onchange="check_year()">
	<input type="hidden" name="club2" id="newclub2" value="">
	<input type="hidden" name="athlete_id" id="newathleteid" value="<?php echo $athlete_id ?>">
	</td>
	<?php
	$sexm = (isset($_POST['sex'])) ? (($_POST['sex']=='m') ? 'checked' : '') : (($sex=='m') ? 'checked' : '');
	$sexw = (isset($_POST['sex'])) ? (($_POST['sex']=='w') ? 'checked' : '') : (($sex=='w') ? 'checked' : '');
	?>
	<th class='dialog'><?php echo $strSex ?></th>
	<td class='forms'>
		<input type="radio" name="sex" id="sexm" value="m" <?php echo $sexm ?> onclick='check_sex(<?php echo $allow_search_from_base; ?>)' ><?php echo $strSexMShort ?>
		<input type="radio" name="sex" id="sexw" value="w" <?php echo $sexw ?> onclick='check_sex(<?php echo $allow_search_from_base; ?>)' ><?php echo $strSexWShort ?>
        <input name='argument' id='argument' type='hidden' value='' /> 
	</td>
</tr>
<tr style="display:none">
	<th class='dialog'><?php echo $strCountry; ?></th>
	<?php $dd = new GUI_CountryDropDown($country, ""); ?>
	<th class='dialog'><?php echo $strRegion ?></td>
	<?php $dd = new GUI_RegionDropDown($region, ""); ?>
</tr>
<tr>
	<?php
	
    $clubtext = (isset($_POST['clubtext'])) ? $_POST['clubtext'] : (isset($_GET['club']) ? $_GET['club'] : '');  
	$clubinfotext = (isset($_POST['clubinfotext'])) ? $_POST['clubinfotext'] : $clubinfotext;
    
	?>
	<th class='dialog'><?php echo $strClub ?></th>
	<td class='forms'><input type="text" id="clubtext" name="clubtext"
		onkeyup="club_search()" size="30" value="<?php echo $clubtext ?>"></td>
	<input type="hidden" id="newclub" name="club" value="<?php echo $club ?>">
	<th class='dialog'><?php echo $strClubInfo ?></th>
	<td class='forms'><input type="text" id="clubinfotext" name="clubinfotext"
		onkeyup="clubinfo_search()" size="30" value="<?php echo $clubinfotext;?>"></td>
</tr>

<tr>
<th class='dialog'><?php echo $strClub2 ?></th>
    <td class='forms'><input type="text" id="clubtext2" name="clubtext2"
         size="30" value="<?php echo $clubtext2; ?>"></td>
    <input type="hidden" id="newclub2" name="club2" value="<?php echo $club2 ?>">
</tr>

<tr>
	<th class='dialog'><?php echo $strStartnumberLong; ?></th>
	<td class='forms'>
		<input name='arg' type='hidden' value='add' />
		<input class='nbr' name='startnbr' id='startnbr' type='text'
			maxlength='5' value='<?php echo $startnbr ?>' /> <?php echo $strNextNr.": ".$nbr; ?>
	</td>
	<th class='dialog'><?php echo $strCategory ?></th>
	<?php $dd = new GUI_CategoryDropDown($category, "check_category($allow_search_from_base)", true); ?>
</tr>
<tr style="display:none">
	<th class='dialog'><?php echo $strTeam; ?>Test</th>
	<?php
		$dd = new GUI_TeamDropDown($category, $club, $team);
	?>
	<th class='dialog'><?php echo $strCombinedGroup; ?></th>
	<td class='forms'><input type="text" size="2" maxlength="2" name="combinedgroup" id="combinedgroup" value="<?php echo $combined; ?>"></td>
</tr>

<tr>
	<th class='dialog' colspan='4'><?php echo $strDisciplines . " / "
			. " $strTopPerformance"; ?></th>
</tr>
<tr>
	<td class='forms' colspan='4'>
		<!--<table>-->
<?php  
	meeting_get_disciplines(); // show disciplines
?>
			<!--</table>-->
		</td>
	</tr>
</table>

<p />
<table>
	<tr>
		<td class='forms'>
			<button type='submit'>
				<?php echo $strSave; ?>
			</button>
		</td>
		<td class='forms'>
			<button type='submit' onclick='document.forms[2].arg.value="cancel"'>
				<?php echo $strCancel; ?>
			</button>
		</td>
	</tr>
</table>
</form> 	

<!-- this select box is used for a IE trick ( in function base_search_show() ) -->
<input type="checkbox" onclick="base_select(this)"
	onfocus="this.parentNode.parentNode.className='active'"
	onblur="this.parentNode.parentNode.className=''"
	value="" id="checkorig" style="visibility:hidden">
<!-- this select box is used for a IE trick ( in function club_search_show() ) -->
<input type="checkbox" onclick="club_select(this)"
	onfocus="this.parentNode.parentNode.className='active'"
	onblur="this.parentNode.parentNode.className=''"
	value="" id="checkorig2" style="visibility:hidden">
<!-- this select box is used for a IE trick ( in function clubinfo_search_show() ) -->
<input type="checkbox" onclick="clubinfo_select(this)"
	onfocus="this.parentNode.parentNode.className='active'"
	onblur="this.parentNode.parentNode.className=''"
	value="" id="checkorig3" style="visibility:hidden">

<script type="text/javascript">
<!--
	var focus_license='<?php echo $focus_license; ?>';   
   
    if (focus_license==1){ 
        if (document.lookup){  
            if  (document.lookup.searchfield.value == '') {  
                document.lookup.searchfield.focus();  
            }  
        }           
    }
    else {
         if(document.entry ) {  
            if  (document.entry.name.value == '') {  
                document.entry.name.focus();               
            }           
            else if (document.entry.first.value=='') {  
                document.entry.first.focus();
            } 
            else if (document.entry.year.value=='') {  
                document.entry.year.focus();
            } 
            else if (document.entry.clubtext.value=='') {  
                document.entry.clubtext.focus();
            } 
            else if (document.entry.startnbr.value=='') {  
                document.entry.startnbr.focus();
            }  
        }  
	}
    
    

//-->
</script>

<?php
}// endif search occurred
} // end if club searched

if($athlete_id > 0){
	?>
	<script type="text/javascript">
	//document.forms[0].search.focus();
	document.entry.startnbr.focus();
	document.entry.startnbr.select();
	</script>
	<?php
}

$page->endPage();
?>
