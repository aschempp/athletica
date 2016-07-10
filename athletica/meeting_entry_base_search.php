<?php

header("Content-Type: text/xml");

/**********
 *
 *	meeting_entry_base_search.php
 *	---------------------
 *	
 */

require('./lib/common.lib.php');
require('./lib/cl_performance.lib.php');
require('./lib/meeting.lib.php');  

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	echo "<state>error</state>";
	return;		// abort
}


$sqlName = "";
$sqlFirstname = "";
$sqlYear = "";
$sqlId = "";

$clubName2 = "";

if(!empty($_GET['name'])){
	$sqlName = " lastname LIKE '".$_GET['name']."%' ";
}else{
	echo "<state>error</state>";
	return;
}

if(!empty($_GET['firstname'])){
	$sqlFirstname = " AND firstname LIKE '".$_GET['firstname']."%' ";
}

if(!empty($_GET['year'])){
	if(strlen($_GET['year']) == 2 && $_GET['year'] < 30){
		$sqlYear = " AND SUBSTRING(birth_date, 1,2) LIKE '".$_GET['year']."%' ";
	}elseif(strlen($_GET['year']) == 2 && $_GET['year'] >= 30){
		$sqlYear = " AND SUBSTRING(birth_date, 3,2) LIKE '".$_GET['year']."%' ";
	}elseif(strlen($_GET['year']) == 3){
		$sqlYear = " AND SUBSTRING(birth_date, 1,3) LIKE '".$_GET['year']."%' ";
	}elseif(strlen($_GET['year']) == 4){
		$sqlYear = " AND SUBSTRING(birth_date, 1,4) LIKE '".$_GET['year']."%' ";
	}
}

if($_GET['id'] != 0){
	$sqlId = " id_athlete = ".$_GET['id'];
	$sqlYear = "";
	$sqlName = "";
	$sqlFirstname = "";
}
/*
$sql = "SELECT b.*, 
			   k.xKategorie 
		FROM 
               base_athlete AS b 
	    LEFT JOIN kategorie AS k ON(b.license_cat = k.Code) 
		WHERE ".$sqlName." ".$sqlFirstname." ".$sqlYear." ".$sqlId." 
	    ORDER BY lastname DESC, 
		        firstname DESC;";  
*/               
$today=date(Y);               
$sql = "SELECT 
                b.*, 
                MIN(k.Alterslimite) as min_agelimit, 
                b.sex                
          FROM 
                base_athlete AS b 
                LEFT JOIN kategorie AS k ON ( b.license_cat = k.Code OR ( $today  - substring( b.birth_date, 1, 4 ) ) <= k.Alterslimite ) 
          WHERE ".$sqlName." ".$sqlFirstname." ".$sqlYear." ".$sqlId."  
                AND b.sex = k.Geschlecht and k.aktiv = 'y' AND k.UKC = 'n' 
                
          GROUP BY b.id_athlete
          ORDER BY lastname DESC, 
                   firstname DESC, k.Alterslimite ASC";               

$res = mysql_query($sql);    

if(mysql_errno > 0){
	echo "<state>error</state>";
}else{
	echo "<result>\n";
	echo "<state>ok</state>\n";
	$num = mysql_num_rows($res);      
    
	$row = mysql_fetch_assoc($res);
	
	// special char translation table
	$trans = get_html_translation_table(HTML_ENTITIES);
	//$encoded = strtr($str, $trans);
	
	echo "<num>$num</num>\n";
                                               
	if($num == 1){         
        $sql_k = "SELECT 
                        k.xKategorie
                    FROM 
                        kategorie AS k
                    WHERE
                        k.Alterslimite = ". $row['min_agelimit'].
                        " AND k.Geschlecht ='". $row['sex']."' ".
                        "AND k.aktiv = 'y' and k.UKC = 'n'";
        
         $res_k = mysql_query($sql_k);  
         if(mysql_errno > 0){
            echo "<state>error</state>";
         }else{
              $row_k = mysql_fetch_assoc($res_k);     
              echo "<category>".$row_k['xKategorie']."</category>\n";    
         }
                         
		$club = $row['account_code'];
		$club2 = $row['second_account_code'];
		//
		// get club id from club code
		//
		$result = mysql_query("select xVerein, Sortierwert from verein where xCode = '".$club."'");
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			$rowClub1 = mysql_fetch_array($result);
			$club = $rowClub1[0];
			$clubName = $rowClub1[1];
			if(!empty($club2)){
				$result = mysql_query("select xVerein, Sortierwert from verein where xCode = '".$club2."'");
				if(mysql_errno() > 0){
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}else{
					$rowClub2 = mysql_fetch_array($result);
					$club2 = $rowClub2[0];
                    $clubName2 = $rowClub2[1];   
				}
			}
		}
		mysql_free_result($result);
		
        if (empty($club2)){
           $clubName2 = AA_meeting_getLG($club);    
        }
        
        
		if(empty($club2)){ $club2 = 0; }
		
		echo "<athleteId>".urlencode(trim($row['id_athlete']))."</athleteId>\n";   
        echo "<name>".urlencode(trim($row['lastname']))."</name>\n";
		echo "<firstname>".urlencode(trim($row['firstname']))."</firstname>\n";
		echo "<clubname>".urlencode(trim($clubName))."</clubname>\n";
        echo "<clubname2>".urlencode(trim($clubName2))."0</clubname2>\n";  
		echo "<year>".substr($row['birth_date'],0,4)."</year>\n";
		echo "<day>".substr($row['birth_date'],8,2)."</day>\n";
		echo "<month>".substr($row['birth_date'],5,2)."</month>\n";
		echo "<license>".$row['license']."</license>\n";
        if ($row['license_paid'] == 'n'){
            $licensePrinted = $strNo;
        }
        else {
              $licensePrinted = $strYes; 
        }
        echo "<licensePrinted>".$licensePrinted."</licensePrinted>\n"; 
		echo "<id>".$row['id_athlete']."</id>\n";
		echo "<club>".$club."</club>\n";
		echo "<club2>".$club2."</club2>\n";
		echo "<country>".$row['nationality']."</country>\n";        
		echo "<sex>".$row['sex']."</sex>\n";
		echo "<clubinfo>".urlencode(trim($row['account_info']))."0</clubinfo>\n";
									// '0' is needed by xml result node if club info is empty
		//
		// get top performance for found athlete
		//
		$resdisc = mysql_query("
			SELECT
				w.xWettkampf
				, d.Code
				, k.Code
				, k.xKategorie
			FROM
				disziplin_" . $_COOKIE['language'] . " AS d
				INNER JOIN wettkampf as w ON ( w.xDisziplin = d.xDisziplin)
                LEFT JOIN kategorie as k ON (w.xKategorie = k.xKategorie)
			WHERE w.xMeeting = " . $_COOKIE['meeting_id'] ."  			
			ORDER BY
				k.Kurzname, w.Mehrkampfcode, d.Anzeige
		");
		
		echo "<performance>";
        
		$saison = $_SESSION['meeting_infos']['Saison'];
        if ($saison == ''){
            $saison = "O"; //if no saison is set take outdoor
        }
        
		while($rowdisc = mysql_fetch_array($resdisc)){	// get performance for each discipline of meeting
			$resperf = mysql_query("
				SELECT season_effort, notification_effort
                FROM
					base_performance
				WHERE	id_athlete = ".$row['id_athlete']."
				AND	discipline = ".$rowdisc[1] ."
                AND season = '$saison'");
			
			$effort = "";
			$rowperf = mysql_fetch_array($resperf);
			
			$effort = $rowperf[1];                   // best effort current or previous year (Indoor: best of both / Outdoor: best of outdoor)
			
			$effort = ltrim($effort, "0:");
			
			if(!empty($effort)){
				echo "<perf id=\"$rowdisc[0]\">$effort</perf>";
			}
		}
		
		echo "</performance>\n";
		
	}elseif($num <= 10){
		
		do{
           
			$club = $row['account_code'];
			//
			// get club id from club code
			//
			$result = mysql_query("select Name from verein where xCode = '".$club."'");
			if(mysql_errno() > 0){
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}else{
				$rowClub1 = mysql_fetch_array($result);
				$club = $rowClub1[0];
			}
			echo "<name>".urlencode(trim($row['lastname']))."</name>\n";
			echo "<firstname>".urlencode(trim($row['firstname']))."</firstname>\n";
			echo "<year>".substr($row['birth_date'],0,4)."</year>\n";
			echo "<day>".substr($row['birth_date'],8,2)."</day>\n";
			echo "<month>".substr($row['birth_date'],5,2)."</month>\n";
			echo "<id>".$row['id_athlete']."</id>\n";
			echo "<club>".urlencode(trim($club))."</club>\n"; 
                                                                 
            
            
			
		}while($row = mysql_fetch_assoc($res));
		
	}
	echo "</result>";
}
?>
