<?php

/**********
 *
 *	meeting_entries_receipt.php
 *	-----------------------------
 *	
 */   
	 

require('./lib/cl_gui_page.lib.php');
require('./lib/common.lib.php');
require('./lib/cl_performance.lib.php');


if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}   


$page = new GUI_Page('meeting_entries_efforts');
$page->startPage();
$page->printPageTitle($strUpdateEfforts);



if (isset($_POST['updateEfforts'])){    
	if ($_POST['mode']=="overwrite"){	
		//echo "Overwrite";
		$sql_where = "";
	} else {
		//echo "Skip";
		$sql_where = " AND start.BaseEffort ='y'";
	}
	
	$event = $_COOKIE['meeting_id'];

	$saison = $_SESSION['meeting_infos']['Saison'];
	if ($saison == ''){
		$saison = "O"; //if no saison is set take outdoor
	}
	
	$sql = "	SELECT
		athlet.Lizenznummer as License
		, d.Code as DiszCode
		, d.Typ
		, xStart
        , wettkampf.Mehrkampfcode as MK
        , anmeldung.xAnmeldung as Enrolment
	FROM
		athletica.start
		INNER JOIN athletica.anmeldung 
			ON (start.xAnmeldung = anmeldung.xAnmeldung)
		INNER JOIN athletica.wettkampf 
			ON (start.xWettkampf = wettkampf.xWettkampf)
		INNER JOIN athletica.athlet 
			ON (anmeldung.xAthlet = athlet.xAthlet)
		INNER JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d 
			ON (wettkampf.xDisziplin = d.xDisziplin)
	WHERE (athlet.Lizenznummer != 0 AND 
		wettkampf.xMeeting =$event 
		$sql_where) ORDER BY License";     
	//echo $sql;  
	
	$res_start = mysql_query($sql);
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error() . $sql);
	} else { 
        $licnr_keep=0;
        $perfSeason = 0;
		while ($row_start = mysql_fetch_array($res_start)){
			// get performance from base data 
			$perf = 0;

			$sql = "SELECT season_effort, notification_effort 
					  FROM base_performance 
				 LEFT JOIN base_athlete USING(id_athlete) 
					 WHERE base_athlete.license = ".$row_start['License']." 
					   AND base_performance.discipline = ".$row_start['DiszCode'] ." 
					   AND season = '".$saison."';";
			$res = mysql_query($sql); 
			
			$rowPerf = mysql_fetch_array($res);  
            $perf = $rowPerf['notification_effort'];       // best effort current or previous year (Indoor: best of both / Outdoor: best of outdoor)
            $perfSeason = $rowPerf['season_effort'];        
										
			if(($row_start['Typ'] == $cfgDisciplineType[$strDiscTypeTrack])
				|| ($row_start['Typ'] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
				|| ($row_start['Typ'] == $cfgDisciplineType[$strDiscTypeRelay])
				|| ($row_start['Typ'] == $cfgDisciplineType[$strDiscTypeDistance])) {  // disciplines track
				$pt = new PerformanceTime(trim($perf));
				$perf = $pt->getPerformance();
                
                $ps = new PerformanceTime(trim($perfSeason));
                $perfSeason = $ps->getPerformance();
			}
		   	else {				
		   		$perf = (ltrim($perf,"0"))*100;  
                $perfSeason = (ltrim($perfSeason,"0"))*100;  
			}
            if  (empty($perf)){
                 $perf = 0.0; 
            }
            if  (empty($perfSeason)){
                 $perfSeason = 0; 
            }
            											  
			if($perf != NULL) {	// invalid performance
				$sql = "UPDATE start SET 
				  Bestleistung = $perf
                 , VorjahrLeistung = $perfSeason
				 , BaseEffort = 'y'
				 WHERE xStart = ". $row_start['xStart'];
				//echo " <br>$sql";
				mysql_query($sql);
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
	 
			}
            
            if ($licnr_keep != $row_start['License']){
                 // get performance for combined events from base data 
                 $perf = 0;

                 $sql = "SELECT season_effort, notification_effort 
                            FROM base_performance 
                         LEFT JOIN base_athlete USING(id_athlete) 
                         WHERE base_athlete.license = ".$row_start['License']." 
                         AND base_performance.discipline = ".$row_start['MK'] ." 
                         AND season = '".$saison."';";
                 $res = mysql_query($sql);   
                 
                 $rowPerf = mysql_fetch_array($res); 
                 
                 if (empty($rowPerf['notification_effort'])){
                     $perf = 0.0;
                 }
                 else {
                      $perf = $rowPerf['notification_effort'];       // best effort current or previous year (Indoor: best of both / Outdoor: best of outdoor)
                 }
                
                 if (empty($rowPerf['season_effort'])){
                     $seasonPerf = 0;
                 }
                 else {
                     $seasonPerf = $rowPerf['season_effort'];       
                 }
                 
                                                                                                                                       
                 if($perf != NULL) {    // invalid performance
                        $sql = "UPDATE anmeldung SET 
                                        BestleistungMK = $perf
                                        , VorjahrLeistungMK = $seasonPerf
                                        , BaseEffortMK = 'y'
                                WHERE xAnmeldung = ". $row_start['Enrolment'];
                 
                        mysql_query($sql);
                        if(mysql_errno() > 0) {
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }  
                 }   
            }   
           $licnr_keep=$row_start['License'];  
		}
		
		echo "<br>$strUpdateEffortsSuccess";
	}

} else {


//---------------- Show Configuration Screen ------------------	
	
//get base-date
$res = mysql_query("SELECT MAX(global_last_change) as datum FROM base_log");
if ($res){
	$row =mysql_fetch_array($res);
	$date = $row['datum'];
} else {
	$date = $strNoBaseData ;
}

?>
<form action='meeting_entries_efforts.php' method='post' name='Form_updateEfforts'>   

	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody><tr>
			<td style="vertical-align: top;" width="475">
				<table class="dialog" width="475">
					<tbody><tr>

						<th><?php echo $strConfiguration; ?></th>
					</tr>
					<tr>
						<td><p><?php echo $strEffortsUpdateInfo1; ?></p>
						  <p><?php echo $strEffortsUpdateInfo2; ?></p>
						  <p><?php echo $strBaseData; ?> <b><?php echo substr($date,8,2). '.'. substr($date,5,2).'.'.substr($date,0,4);?></b><br />
							<br />
						  </p>
						  <p><?php echo $strEffortsUpdateInfo3; ?></p>
						  <p>
							<label>
							  <input type="radio" name="mode" value="overwrite" id="mode_0" checked="checked" />
							  <?php echo $strOverwrite; ?></label>
							<br />
							<label>
							  <input type="radio" name="mode" value="skip" id="mode_1" />
							  <?php echo $strLeaveBehind ;?></label>
							<br />
						  </p></td>
					</tr>
				</tbody>
		</table>
	
	<br>
	<button name='updateEfforts' type='submit'>
	<?php echo $strUpdateEfforts; ?>
	</button>          
</form>    

<?php
}

$page->endPage();
?>
