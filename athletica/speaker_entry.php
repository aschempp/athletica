<?php

/**********
 *
 *	speaker_entry.php
 *	-----------------
 *	
 */

require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_menulist.lib.php');

require('./lib/common.lib.php');
require('./lib/results.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$round = 0;
if(!empty($_GET['round'])){
	$round = $_GET['round'];
}


$page = new GUI_Page('speaker_entry');
$page->startPage();
$page->printPageTitle($strEntry. ": " . $_COOKIE['meeting']);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/speaker/entry.html', $strHelp, '_blank');
if(!empty($_POST['back'])) {
	$menu->addButton($_POST['back'], $strBack);
}
$menu->printMenu();

?>
<p />
<?php

$item = 0;
if(!empty($_GET['item'])){
	$item = $_GET['item'];
}

$relay = 0;
if(!empty($_GET['relay'])){
	$relay = $_GET['relay'];
}


//
// set up search parameters, if any 
//
unset($searchparam);

if($_POST['arg']=='search')
{
	$name = '';
	$nbr = '';
	if(is_numeric($_POST['searchfield'])) {
		$searchparam = " AND a.Startnummer = '" . $_POST['searchfield'] . "'";
	}	
	else {
		$searchparam = " AND at.Name = '" . $_POST['searchfield'] . "'";
	}       
	
     $sql = "SELECT
                    a.xAnmeldung
                    , a.Startnummer
                    , at.Name
                    , at.Vorname
                    , at.Jahrgang
                    , v.Name
                    , at.xAthlet
             FROM
                    anmeldung AS a
                    LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                    LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
             WHERE 
                a.xMeeting = " . $_COOKIE['meeting_id'] . "  
                $searchparam 
             ORDER BY
                at.Name
                ,at.Vorname";        
    
    $result = mysql_query($sql);     

	if(mysql_errno() > 0)		// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	// more than one hit: show selection list
	else if(mysql_num_rows($result) > 1)
	{

		$i=0;
		$rowclass = "odd";

		?>
<table class='dialog'>
	<tr>
		<th class='dialog'><?php echo $strStartnumber; ?></th>
		<th class='dialog'><?php echo $strName; ?></th>
		<th class='dialog'><?php echo $strYear; ?></th>
		<th class='dialog'><?php echo $strClub; ?></th>
	</tr>
		<?php

		while ($row2 = mysql_fetch_row($result))
		{
			$i++;
			if( $i % 2 == 0 ) {		// even row number
				$rowclass = "even";
			}
			else {	// odd row number
				$rowclass = "odd";
			}
			?>
	<tr class='<?php echo $rowclass; ?>' onClick='window.open("speaker_entry.php?item=<?php echo $row2[6]; ?>", "_self")' style="cursor: pointer;">
		<td class='forms_right'><?php echo $row2[1]; ?></td>
		<td><?php echo "$row2[2] $row2[3]"; ?></td>
		<td class='forms_ctr'><?php echo AA_formatYearOfBirth($row2[4]); ?></td>
		<td><?php echo $row2[5]; ?></td>
	</tr>
			<?php
		}

		?>
</table>
		<?php

		mysql_free_result($result);
	}

	// one hit: save item to display further down
	else if(mysql_num_rows($result) == 1)
	{
		$row2 = mysql_fetch_row($result);
		$item = $row2[6];
	}
	else
	{
		AA_printErrorMsg($strErrAthleteNotFound);
	}
}


// 
// show athlete's or relay's data
//
if ($item > 0)
{
	$xAthlet = $item;       
	
    $sql = "SELECT
                    a.xAnmeldung
                    , a.Startnummer
                    , at.Name
                    , at.Vorname
                    , at.Jahrgang
                    , v.Name
                    , at.xAthlet
                    , k.Name
                    , t.Name
            FROM
                    anmeldung AS a
                    LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
                    LEFT JOIN verein AS v  ON (v.xVerein = at.xVerein)
                    LEFT JOIN kategorie AS k ON (k.xKategorie = a.xKategorie)
                    LEFT  JOIN team AS t ON a.xTeam = t.xTeam
            WHERE 
                a.xMeeting = " . $_COOKIE['meeting_id'] . "    
                AND a.xAthlet = " . $item . "  
            ORDER BY
                at.Name
                , at.Vorname";      
    
    $result = mysql_query($sql);    

	$row2 = mysql_fetch_row($result); 
	
	?>
	
	<table class='dialog'>
	<tr>
		<th class='dialog'><?php echo $strName; ?></th>
		<th class='dialog'><?php echo "$row2[2] $row2[3]"; ?></th>
	</tr>
	<tr>
		<th class='dialog'><?php echo $strStartnumberLong; ?></th>
		<td class='dialog'><?php echo $row2[1]; ?></td>
	</tr>
	<tr>
		<th class='dialog'><?php echo $strCategory; ?></th>
		<td class='dialog'><?php echo $row2[7]; ?></td>
	</tr>
	<tr>
		<th class='dialog'><?php echo $strYear; ?></th>
		<td class='dialog'><?php echo AA_formatYearOfBirth($row2[4]); ?></td>
	</tr>
	<tr>
		<th class='dialog'><?php echo $strClub; ?></th>
		<td class='dialog'><?php echo $row2[5]; ?></td>
	</tr>
	<tr>
		<th class='dialog'><?php echo $strTeam; ?></th>
		<td class='dialog'><?php echo $row2[8]; ?></td>
	</tr>
</table>
<p/>
	<?php
	
	// athlet
	if ($relay == 0) {
		
        $query = "
            SELECT
                a.Startnummer
                , d.Name
                , d.Typ
                , at.Name
                , at.Vorname
                , at.Jahrgang
                , k.Name
                , v.Name
                , t.Name
                , r.Leistung
                , r.Info
                , rt.Typ
                , s.Wind
                , at.xAthlet
            FROM
                anmeldung AS a
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                LEFT JOIN start  AS st ON (st.xAnmeldung = a.xAnmeldung)
                LEFT JOIN wettkampf AS w ON (w.xWettkampf = st.xWettkampf )
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d  ON (d.xDisziplin = w.xDisziplin )
                LEFT JOIN kategorie AS k ON (k.xKategorie = a.xKategorie)
                LEFT JOIN serienstart AS ss ON (ss.xStart = st.xStart )
                INNER JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart)
                LEFT JOIN serie AS s ON (s.xSerie = ss.xSerie)   
                LEFT JOIN runde AS ru ON (ru.xRunde = s.xRunde)  
                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)                   
                LEFT JOIN team AS t ON a.xTeam = t.xTeam
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON ru.xRundentyp = rt.xRundentyp
            WHERE 
                a.xAthlet = $item            
            ORDER BY
                d.Anzeige
                , ru.Datum
                , ru.Startzeit
                , r.xResultat
        ";       
                
	}
	// relay
	else {
		
        $query = "SELECT
                        s.Name
                        , d.Name
                        , k.Name
                        , v.Name
                        , t.Name
                        , s.xStaffel
                        , r.Leistung
                        , rt.Typ
                  FROM
                        staffel AS s    
                        LEFT JOIN start AS st ON (st.xStaffel = s.xStaffel)     
                        LEFT JOIN wettkampf AS w ON (w.xWettkampf = st.xWettkampf)
                        LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                        LEFT JOIN kategorie AS k ON (k.xKategorie = s.xKategorie    )
                        LEFT JOIN serienstart AS ss ON (ss.xStart = st.xStart )
                        LEFT JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart)
                        LEFT JOIN serie AS se ON (se.xSerie = ss.xSerie )    
                        LEFT JOIN runde AS ru ON (ru.xRunde = se.xRunde  )  
                        LEFT JOIN verein AS v ON (v.xVerein = s.xVerein)                         
                        LEFT JOIN team AS t ON s.xTeam = t.xTeam
                        LEFT  JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON ru.xRundentyp = rt.xRundentyp
                  WHERE 
                        s.xStaffel = $item             
                  ORDER BY
                    d.Anzeige
                    , ru.Datum
                    , ru.Startzeit ";      
       
	}

	$result = mysql_query($query);      

	if(mysql_errno() > 0)		// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{               
		
     $sql = "SELECT 
                    DISTINCT a.xAnmeldung
                    , a.Startnummer
                    , at.Name
                    , at.Vorname
                    , at.Jahrgang
                    , k.Kurzname
                    , k.Name
                    , v.Name
                    , t.Name
                    , d.Kurzname
                    , d.Name
                    , d.Typ
                    , s.Bestleistung
                    , if(at.xRegion = 0, at.Land, re.Anzeige)
                    , ck.Kurzname
                    , ck.Name
                    , s.Bezahlt
                    , w.Info
                    , d2.Kurzname
                    , d2.Name
                    , v.Sortierwert
                    , k.Anzeige
                    , w.Startgeld  
                    , w.mehrkampfcode    
            FROM
                    anmeldung AS a        
                    LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet) 
                    LEFT JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung)
                    LEFT JOIN wettkampf AS w ON (w.xWettkampf = s.xWettkampf)
                    LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d  ON (d.xDisziplin = w.xDisziplin)
                    LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie)  
                    LEFT JOIN kategorie AS ck ON (ck.xKategorie = w.xKategorie)    
                    LEFT JOIN verein AS v ON (at.xVerein = v.xVerein )                      
                    LEFT JOIN runde AS r ON (s.xWettkampf = r.xWettkampf) 
                    LEFT JOIN team AS t ON a.xTeam = t.xTeam
                    LEFT JOIN region as re ON at.xRegion = re.xRegion
                    LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d2 ON (w.Typ = 1 AND w.Mehrkampfcode = d2.Code)
            WHERE 
                    a.xMeeting = " . $_COOKIE['meeting_id'] . "
                    AND a.xAthlet = $item   
            ORDER BY
                    d.Anzeige";         
    
    $result2 = mysql_query($sql);   
    
	$tempdisz = "";
          
		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		 
		else if(mysql_num_rows($result2) > 0)  // data found
		{
			?>

				<table class='dialog'>
				<tr>
				<th class='dialog' colspan='4'><?php echo $strStartsPerDisc; ?></th>
				</tr>
				<?php
			while($row2 = mysql_fetch_row($result2))
			{
				
				
				if($tempdisz != $row2[10]) 	// new discipline
				{
					?>
				<tr>
				<td class='dialog'><?php echo $row2[10]; ?></td>
					<?php
				
				
				if(($row2[11] == $cfgDisciplineType[$strDiscTypeNone])
					|| ($row2[11]== $cfgDisciplineType[$strDiscTypeTrack])
					|| ($row2[11]== $cfgDisciplineType[$strDiscTypeTrackNoWind])
					|| ($row2[11]== $cfgDisciplineType[$strDiscTypeDistance])
					|| ($row2[11]== $cfgDisciplineType[$strDiscTypeRelay]))
				{
					$perf = AA_formatResultTime($row2[12]);
				}
				// technical disciplines
				else 
				{
					$perf = AA_formatResultMeter($row2[12]);
				}
				?>
				<td class='dialog_right'><?php echo $perf; ?></td>
				<?php
				}
				$tempdisz = $row2[10];
				
			}
		}
	
	?>
	</table><br>			<?php
	
		$i = 0;
		$disc = '';
		while($row = mysql_fetch_row($result))
		{
			// athlete
			if($relay == 0)
			{
				if($i == 0)
				{
					// $xAthlet = $row[13];
					?>
	
<table class='dialog'>
	<tr>
		<th class='dialog' colspan='4'><?php echo $strResults; ?></th>
	</tr>
				<?php
				}

				if($disc != $row[1]) 	// new discipline
				{
					?>
	<tr>
		<td class='dialog'><?php echo $row[1]; ?></td>
		<td class='dialog'><?php echo $row[11]; ?></td>
					<?php
				}
				else
				{
					?>
	<tr>
		<td />
		<td />
					<?php
				}

				// track disciplines (timed)
				if(($row[2] == $cfgDisciplineType[$strDiscTypeNone])
					|| ($row[2]== $cfgDisciplineType[$strDiscTypeTrack])
					|| ($row[2]== $cfgDisciplineType[$strDiscTypeTrackNoWind])
					|| ($row[2]== $cfgDisciplineType[$strDiscTypeDistance])
					|| ($row[2]== $cfgDisciplineType[$strDiscTypeRelay]))
				{
					$perf = AA_formatResultTime($row[9]);
				}
				// technical disciplines
				else 
				{
					$perf = AA_formatResultMeter($row[9]);
				}
				// track discipline with wind
				if($row[2]== $cfgDisciplineType[$strDiscTypeTrack])
				{
					$info = $row[12];
				}
				// technical disciplines
				else 
				{
					$info = $row[10];
				}

				?>
		<td class='dialog_right'><?php echo $perf; ?></td>
		<td class='dialog_right'><?php echo $info; ?></td>
	</tr>
				<?php
			}
			// relay
			else
			{
				if($i == 0)		// only once
				{
					?>
<table class='dialog'>
	<tr>
		<th class='dialog'><?php echo $strRelay; ?></th>
		<th class='dialog'><?php echo $row[0]; ?></th>
	</tr>
	<tr>
		<th class='dialog'><?php echo $strCategory; ?></th>
		<td class='dialog'><?php echo $row[2]; ?></td>
	</tr>
	<tr>
		<th class='dialog'><?php echo $strClub; ?></th>
		<td class='dialog'><?php echo $row[3]; ?></td>
	</tr>
	<tr>
		<th class='dialog'><?php echo $strTeam; ?></th>
		<td class='dialog'><?php echo $row[4]; ?></td>
	</tr>
</table>
<p/>
<table class='dialog'>
	<tr>
		<th class='dialog' colspan='4'><?php echo $strDiscipline; ?></th>
	</tr>
					<?php
				}

				if($disc != $row[1]) 	// new discipline
				{
					?>
	<tr>
		<td class='dialog_right'><?php echo $row[1]; ?></td>
		<td class='dialog_right'><?php echo $row[7]; ?></td>
					<?php
				}
				else
				{
					?>
	<tr>
		<td />
		<td />
					<?php
				}
				?>
		<td class='dialog_right'><?php echo AA_formatResultTime($row[6]); ?></td>
	</tr>
				<?php
			}

			$i++;
			$disc = $row[1]; 		// keep discipline
		}

		mysql_free_result($result);
		// add relay athletes
		if($relay > 0)
		{         			
            $sql = "SELECT
                            a.xAnmeldung
                            , a.Startnummer
                            , at.Name
                            , at.Vorname
                    FROM
                            athlet AS at
                            LEFT JOIN anmeldung AS a ON (a.xAthlet = at.xAthlet)
                            LEFT JOIN start AS st ON (st.xAnmeldung = a.xAnmeldung)
                            LEFT JOIN staffelathlet AS sta ON (sta.xStaffelstart = ss.xStart)  
                            LEFT JOIN start AS ss ON (sta.xAthletenstart = st.xStart) 
                    WHERE 
                            ss.xStaffel = $item                 
                    ORDER BY
                            sta.Position";      
            
            $result = mysql_query($sql);     

			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else
			{
				?>
	<tr>
		<td/>
		<td class='dialog' colspan='2'>
			<table>
				<?php
				while ($row = mysql_fetch_row($result))
				{
					?>
					<tr>
					<td class='forms_right'><?php echo $row[1]; ?></td>
					<td><?php echo "$row[2] $row[3]"; ?></td>
					</tr>
					<?php
				}
				?>
			</table>
		</td>
	</tr>
				<?php
				mysql_free_result($result);
			}
		}
}
		?>
</table>
	<br><br>		
	<table class='dialog'>
	
	<?php 
	$sql = "SELECT
				d.Name as DiszName
				, d.Typ
				, best_effort
				, DATE_FORMAT(best_effort_date, '%d.%m.%Y') AS pb_date
				, best_effort_event
				, season_effort
				, DATE_FORMAT(season_effort_date, '%d.%m.%Y') AS sb_date
				, season_effort_event
				, season
			FROM
				athletica.base_athlete
				INNER JOIN athletica.base_performance 
					ON (base_athlete.id_athlete = base_performance.id_athlete)
				INNER JOIN athletica.athlet 
					ON (athlet.Lizenznummer = base_athlete.license)
				INNER JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d 
					ON (d.Code = base_performance.discipline)
			WHERE (athlet.xAthlet =$xAthlet)
			AND NOT (best_effort = '' AND season_effort = '')
			ORDER BY base_performance.season, d.Code";
		
		$res = mysql_query($sql);
		
		if(mysql_num_rows($res)==0){
			?>
			<tr>
				<td class='dialog' colspan = "7"><?php echo $strErrNoResults; ?></th>
			</tr>
			<?php
		}
		
		while($row_perf=mysql_fetch_array($res)){
			if ($row_perf[8]!=$lastseason){
				if ($row_perf[8]=="I"){
					$txtSaison = "Indoor";
				} else if ($row_perf[8]=="O") {
					$txtSaison = "Outdoor";					
				}
				$lastseason=$row_perf[8];
				?>
				<tr>
					<th class='dialog' colspan="7"><?php echo $txtSaison;?></th>
				</tr>
				<tr>
					<td class='dialog'>&nbsp;</td>
					<th class='dialog' colspan="3"><?php echo $strPB_long; ?></th>
					<th class='dialog' colspan="3"><?php echo $strSB_long; ?></th>
				</tr>

				<?php
			}
			
				
			if(($row_perf['Typ'] == $cfgDisciplineType[$strDiscTypeJump])
				|| ($row_perf['Typ'] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
				|| ($row_perf['Typ'] == $cfgDisciplineType[$strDiscTypeThrow])
				|| ($row_perf['Typ'] == $cfgDisciplineType[$strDiscTypeHigh])) {
				if (strlen($row_perf['season_effort'])>0){
					$sb_perf = AA_formatResultMeter(str_replace(".", "", $row_perf['season_effort']));
				}else {
					$sb_perf = '';
				}
				
				if (strlen($row_perf['best_effort'])>0){
					$pb_perf = AA_formatResultMeter(str_replace(".", "", $row_perf['best_effort']));
				}else {
					$pb_perf = '';
				}

			} else {
				//convert performance-time to milliseconds
				$timepices = explode(":", $row_perf['season_effort']);
				$season_effort = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) + ($timepices[2] *  1000) + ($timepices[3]);
				$timepices = explode(":", $row_perf['best_effort']);
				$best_effort = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) + ($timepices[2] *  1000) + ($timepices[3]);									
				if(($row_perf['Typ'] == $cfgDisciplineType[$strDiscTypeTrack])
				|| ($row_perf['Typ'] == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
					if($season_effort!=0){
						$sb_perf = AA_formatResultTime($season_effort, true, true);
					} else {
						$sb_perf = '';
					}
					if($best_effort!=0){
						$pb_perf = AA_formatResultTime($best_effort, true, true);
					} else {
						$pb_perf = '';
					}
				}else{
					if($season_effort!=0){
						$sb_perf = AA_formatResultTime($season_effort, true);
					} else {
						$sb_perf = '';
					}
					if($best_effort!=0){
						$pb_perf = AA_formatResultTime($best_effort, true);
					} else {
						$pb_perf = '';
					}
				}
			}	
				
			
			?>
			<tr>
				<th class='dialog'><?php echo $row_perf['DiszName']; ?></th>
				<td class='forms_right'><?php echo $pb_perf; ?></td>
				<td class='dialog'><?php echo ($row_perf['pb_date']=="00.00.0000")?"":$row_perf['pb_date']; ?></td>
				<td class='dialog'><?php echo $row_perf['best_effort_event']; ?></td>
				<td class='forms_right'><?php echo $sb_perf; ?></td>
				<td class='dialog'><?php echo ($row_perf['sb_date']=="00.00.0000")?"":$row_perf['sb_date'];  ?></td>
				<td class='dialog'><?php echo $row_perf['season_effort_event']; ?></td>
			</tr>
			<?php
		}
		?></table><?php
		
		
	

}

$page->endPage();


