<?php

/**********
 *
 *	event_heats.php
 *	---------------
 *	
 */
            
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');
require('./lib/heats.lib.php');
require('./lib/results.lib.php');
require('./lib/utils.lib.php');
require('./lib/cl_omega.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$round = 0;
if(!empty($_GET['round'])){
	$round = $_GET['round'];
}
else if(!empty($_POST['round'])) {
	$round = $_POST['round'];
}
if(!empty($_GET['heat'])) {
	$heat = $_GET['heat'];
}

$teamsm = false;
if (isset($_POST['teamsm'])){
    $teamsm = $_POST['teamsm'];
}

$presets = AA_results_getPresets($round);


$relay = AA_checkRelay($presets['event']);	// check, if this is a relay event

$disctype = AA_getDisciplineType($round);	// get discipline type


if($_POST['arg'] == 'seed') {			// heat seeding     
       AA_heats_seedEntries($presets['event']);  
}

else if($_POST['arg'] == 'seed_qual') {		// seed qualified athletes
	AA_heats_seedQualifiedAthletes($presets['event']);
}

else if($_GET['arg'] == 'heats_done') {	// heat seeding/qualification done
	
	AA_utils_changeRoundStatus($round, $cfgRoundStatus['heats_done']);
	if(!empty($GLOBALS['AA_ERROR'])) {
		AA_printErrorMsg($GLOBALS['AA_ERROR']);
	}
}

else if($_GET['arg'] == 'del_heats') {		// delete heats   
	AA_heats_delete($round);      
}

else if($_POST['arg'] == 'add_start') {	// add new athlete/relay
	AA_heats_addStart($round);
}

else if($_GET['arg'] == 'del_start') {	// delete athlete/relay
	AA_heats_deleteStart();
}

// change installations
else if(($_POST['arg'] == 'change_inst') || ($_POST['arg'] == 'change_all_inst')) {
	AA_heats_changeInstallation($round);
}

// change heat ID
else if($_POST['arg'] == 'change_heat_name') {		// change heat id
	AA_heats_changeHeatName($round);
}

// change position
else if($_POST['arg'] == 'change_pos') {
	AA_heats_changePosition($round);
}

// change film number
else if($_POST['arg'] == 'change_film') {
	AA_heats_changeFilm();
}

// seed heat randomly
else if($_GET['arg'] == 'seed_heat') {
	AA_heats_seedHeat($heat);
}

//
//	Display heats
//
 
$page = new GUI_Page('event_heats');
$page->startPage();
$page->printPageTitle($strHeatSeeding . ": " . $_COOKIE['meeting']);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/event/heats.html', $strHelp, '_blank');
$menu->printMenu();

?>
<script type="text/javascript">
<!--
	var selected = 0;
	var oldClass = 0;
	var focus = "";
	var obj = 0;
	var value1 = "";
	var value2 = "";


	function clickTrack(o, item, heatID, heatName, position)
	{   // Select Athlete
		if ((selected == 0) && (item !=0))
		{
			// select athlete and keep the data
			selected = 1;
			oldClass = o.className;
			o.className = 'active';
			document.athlete.item.value=item;
			document.athlete.heat.value=heatID;
			document.athlete.pos.value=position;

			// create and append delete-button
			var link = document.createElement('a');
			var text = document.createTextNode('<?php echo $strDelete; ?>');	
			link.appendChild(text);	
			link.href = "event_heats.php?arg=del_start&item=" + item
				+ "&round=" + document.athlete.round.value;   
			var TD = o.insertCell(o.cells.length);
			TD.className = 'nav';	
			TD.appendChild(link);	
		}
		// Drop athlete to new track/heat
		else if (selected == 1)
		{
			if((heatID != document.athlete.heat.value)
				|| (position != document.athlete.pos.value))
			{
				// activate current line and submit update-form
				o.className = 'active';
				document.athlete.action = document.athlete.action + heatName;
				document.athlete.heat.value = heatID;
				document.athlete.pos.value = position;
				document.athlete.heatname.value = heatName;
				document.athlete.submit();
			}
			else
			{
				// deselect athlete and remove delete-button
				selected = 0;
				o.className = oldClass;
				o.deleteCell(o.cells.length-1);
			}
		}
	}
	
	/*function changePos(o, position, heatName, item, heatID){
		// set attributes for changing position manually
		bchangePos = true;
		if(position != ""){ document.athlete.pos.value = position; }
		document.athlete.item.value = item;
		if(heatName != ""){
			document.athlete.heatname.value = heatName;
			document.athlete.heat.value = '';
		}else{
			document.athlete.heat.value = heatID;
		}
	}*/
	
	function check_key(e){
		if (!e)
			e = window.event;
		
		// change position when pressed enter and a value has been changed
		if(e.keyCode == 13 || e.keyCode == 9){
			switch(focus){
				case "heat":
				/*document.athlete.heatname.value = document.getElementById('newheat').value;
				document.athlete.track.value = document.getElementById('newtrack').value;
				document.athlete.heat.value = '';
				document.athlete.item.value = value2;
				document.athlete.submit();*/
				break;
				case "track":
				//document.athlete.heatname.value = document.getElementById('newheat').value;
				//document.athlete.track.value = document.getElementById("newtrack"+value2).value;
				document.athlete.track.value = obj.value;
				document.athlete.heat.value = value1;
				document.athlete.item.value = value2;
				document.athlete.submit();
				break;
			}
		}
	}
	
	function focus_on(field, val1, val2, o){
		value1 = val1;
		value2 = val2;
		focus = field;
		obj = o;
	}
	function focus_off(filed){
		focus = "";
	}
	
	document.onkeydown = check_key
//-->
</script>

<form action='event_heats.php#heat_' method='post' name='athlete'>
	<input type='hidden' name='arg' value='change_pos'/>
	<input type='hidden' name='round' value='<?php echo $round; ?>' />   
	<input type='hidden' name='item' value=''/>
	<input type='hidden' name='heat' value=''/>
	<input type='hidden' name='pos' value=''/>
	<input type='hidden' name='heatname' value=''/>
	<input type='hidden' name='track' value=''/>
</form>

<table><tr>
	<td class='forms'>
		<?php	AA_printCategorySelection("event_heats.php", $presets['category']); ?>
	</td>
	<td class='forms'>
		<?php	AA_printEventSelection("event_heats.php", $presets['category'], $presets['event'], "post"); ?>
	</td>
<?php
if($presets['event'] > 0) {		// event selected
	printf("<td class='forms'>\n");
	AA_printRoundSelection("event_heats.php", $presets['category'], $presets['event'], $round);
	printf("</td>\n");
}
if($round > 0) {		// round selected
	printf("<td class='forms'>\n");
	AA_printHeatSelection($round);
	printf("</td>\n");
}
?>
</tr></table>

<?php
           
$mergedMain=AA_checkMainRound($round);
if ($mergedMain!=1){                    // main round or not merged round
        
// read round data
if($round > 0)
{   $mergedRounds=AA_getMergedRounds($round);    // get merged rounds
    if ($mergedRounds!=''){
       $SqlRound=" IN " . $mergedRounds;
    }
    else {
         $SqlRound=" = " . $round; 
    }   
	$status = AA_getRoundStatus($round);
	$combined = AA_checkCombined(0, $round);
	$svm = AA_checkSVM(0, $round); // decide whether to show club or team name
	
	$cLast = 0;
	if($combined){
		// determine if this is the last discipline -> take combined top performance of previously made events
		/*$res_c = mysql_query("SELECT w.Mehrkampfende FROM
						wettkampf as w
						, runde as r
					WHERE	r.xRunde = $round
					AND	r.xWettkampf = w.xWettkampf");*/
		$sql = "SELECT
					w.Mehrkampfende
				FROM
					wettkampf AS w
				LEFT JOIN 
					runde AS r USING(xWettkampf)
				WHERE r.xRunde ".$SqlRound.";";
        
		$res_c = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			$row_c = mysql_Fetch_array($res_c);
			$cLast = $row_c[0];
		}
	}
	
	// No action yet
	if(($status == $cfgRoundStatus['open'])
		|| ($status == $cfgRoundStatus['enrolement_done']))
	{
		$btn = new GUI_Button("dlg_heat_seeding.php?round=$round", $strHeatSeeding);
		$btn->printButton();
	}
	// No action yet
	else if($status == $cfgRoundStatus['enrolement_pending'])
	{
		$btn = new GUI_Button("event_enrolement.php?category=" . $presets['category'] . "&event=" . $presets['event'], $strEnrolement);
		$btn->printButton();
	}
	// Some heats defined
	else if(($status == $cfgRoundStatus['heats_in_progress'])
		|| ($status == $cfgRoundStatus['heats_done']))
	{

		// add form to add new athletes/relays
		AA_heats_printNewStart($presets['event'], $round, "event_heats.php");

		if($status == $cfgRoundStatus['heats_in_progress']) {
			AA_printWarningMsg($strHeatsInWork);
		}
        
         // check if round is final
        $sql_r="SELECT 
                    rt.Typ
                FROM
                    runde as r
                    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " as rt USING (xRundentyp)
                WHERE
                    r.xRunde=" .$round;
        $res_r = mysql_query($sql_r);
       
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        
        $order="ASC";   
        if (mysql_num_rows($res_r) == 1) {
            $row_r=mysql_fetch_row($res_r);  
            if ($row_r[0]=='F'){
                $order="DESC";  
            }
        }
 
//
// display all heats
//

		// (Remark: LPAD(s.Bezeichnung,5,'0') is used to order heats by their
		// name. This trick is necessary as 'Bezeichnung' may be alpha-numeric.)
		if($relay == FALSE) {		// single event
			if ($teamsm){
                  $sql = "SELECT DISTINCT
                          r.Bahnen
                        , rt.Name
                        , rt.Typ
                        , s.xSerie
                        , s.Bezeichnung
                        , s.xAnlage
                        , ss.xSerienstart
                        , ss.Position
                        , st.Bestleistung
                        , a.Startnummer
                        , at.Name
                        , at.Vorname
                        , at.Jahrgang
                        , t.Name
                        , LPAD(s.Bezeichnung, 5, '0') as heatid
                        , ss.Bahn
                        , s.Film
                        , a.BestleistungMK
                        , IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land
                        , r.xRunde  
                        , st.VorjahrLeistung
                        , a.VorjahrLeistungMK
                    FROM
                        runde AS r
                    LEFT JOIN 
                        rundentyp_" . $_COOKIE['language'] . " AS rt USING(xRundentyp)
                    LEFT JOIN 
                        serie AS s ON(r.xRunde = s.xRunde)
                    LEFT JOIN 
                        serienstart AS ss USING(xSerie)
                    LEFT JOIN
                        start AS st USING(xStart)
                    LEFT JOIN
                        anmeldung AS a USING(xAnmeldung)
                    LEFT JOIN
                        athlet AS at USING(xAthlet)
                    LEFT JOIN 
                        verein AS v USING(xVerein)                         
                    INNER JOIN
                        teamsmathlet AS tat ON(st.xAnmeldung = tat.xAnmeldung)
                    LEFT JOIN teamsm as t ON (tat.xTeamsm = t.xTeamsm AND t.xWettkampf = st.xWettkampf)                      
                    LEFT JOIN 
                        region AS re ON(at.xRegion = re.xRegion) 
                    WHERE t.Name is not NULL AND 
                        r.xRunde ".$SqlRound."
                    ORDER BY                            
                          heatid  ".$order ."
                        , ss.Position ASC;";
            $query = $sql;
          
          
            }
            else {
               $sql = "SELECT
                          r.Bahnen
                        , rt.Name
                        , rt.Typ
                        , s.xSerie
                        , s.Bezeichnung
                        , s.xAnlage
                        , ss.xSerienstart
                        , ss.Position
                        , st.Bestleistung
                        , a.Startnummer
                        , at.Name
                        , at.Vorname
                        , at.Jahrgang
                        , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))
                        , LPAD(s.Bezeichnung, 5, '0') as heatid
                        , ss.Bahn
                        , s.Film
                        , a.BestleistungMK
                        , IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land
                        , r.xRunde  
                        , st.VorjahrLeistung
                        , a.VorjahrLeistungMK
                    FROM
                        runde AS r
                    LEFT JOIN 
                        rundentyp_" . $_COOKIE['language'] . " AS rt USING(xRundentyp)
                    LEFT JOIN 
                        serie AS s ON(r.xRunde = s.xRunde)
                    LEFT JOIN 
                        serienstart AS ss USING(xSerie)
                    LEFT JOIN
                        start AS st USING(xStart)
                    LEFT JOIN
                        anmeldung AS a USING(xAnmeldung)
                    LEFT JOIN
                        athlet AS at USING(xAthlet)
                    LEFT JOIN 
                        verein AS v USING(xVerein)
                    LEFT JOIN
                        team AS t ON(a.xTeam = t.xTeam)
                    LEFT JOIN 
                        region AS re ON(at.xRegion = re.xRegion) 
                    WHERE 
                        r.xRunde ".$SqlRound."
                    ORDER BY                            
                          heatid  ".$order ."
                        , ss.Position ASC;";
            $query = $sql;
            
            }
			
		}
		else {								// relay event
			
			$sql = "SELECT
						  r.Bahnen
						, rt.Name
						, rt.Typ
						, s.xSerie
						, s.Bezeichnung
						, s.xAnlage
						, ss.xSerienstart
						, ss.Position
						, st.Bestleistung
						, sf.Name
						, if('".$svm."', t.Name, v.Name)
						, LPAD(s.Bezeichnung, 5, '0') as heatid
						, ss.Bahn
						, s.Film
						, sf.Startnummer 
                        , st.VorjahrLeistung						
					FROM
						runde AS r
					LEFT JOIN 
						rundentyp_" . $_COOKIE['language'] . " AS rt USING(xRundentyp)
					LEFT JOIN 
						serie AS s ON(r.xRunde = s.xRunde)
					LEFT JOIN 
						serienstart AS ss USING(xSerie)
					LEFT JOIN
						start AS st USING(xStart)
					LEFT JOIN
						staffel AS sf USING(xStaffel)
					LEFT JOIN 
						verein AS v USING(xVerein)
					LEFT JOIN
						team AS t ON(sf.xTeam = t.xTeam)
					WHERE 
						r.xRunde ".$SqlRound." AND st.Anwesend=0  
					ORDER BY                             
						  heatid ".$order ."  
						, ss.Position ASC;";
			$query = $sql;   
            
		}
       
		$result = mysql_query($query);

		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{
?>
<table>
	<tr>
		<th class='dialog'><?php echo $strAssignInstallationToEveryHeat; ?></th>
		<form action='event_heats.php' method='post' name='inst_selection'>
		<input type='hidden' name='arg' value='change_all_inst' />
		<input type='hidden' name='round' value='<?php echo $round; ?>' />
		<input type='hidden' name='item' value='' />
			<?php
				$dd = new GUI_InstallationDropDown('document.inst_selection.submit()');
			?>
		</form>
		<td>
			<?php
				$btn = new GUI_Button("event_heats.php?arg=del_heats&round=$round", $strDeleteHeats);
				$btn->printButton();
				if($status == $cfgRoundStatus['heats_in_progress'])
				{
					$btn->set("dlg_print_contest.php?round=$round", $strTerminateSeeding . " ...");
				}
				else {
					$btn->set("dlg_print_contest.php?round=$round&print=yes", $strPrint . " ...");
				}
				$btn->printButton();
			?>
		</td>
	</tr>
</table>
<table class='dialog'>
<?php
			$h = 0;
			$p = 0;
			$rowclass = 'odd';
			$tracks = 0;
			
			
			while($row = mysql_fetch_row($result))
			{ 
				$tracknumber = $relay ? $row[12] : $row[15];
				
				$p++;						// increment position counter
				if($h != $row[3])		// new heat
				{
					$tracks = $row[0];		// keep nbr of planned tracks
					// fill previous heat with empty tracks
					if($p > 1) {
						AA_heats_printEmptyTracks($p, $tracks, $h, $hn);
					}

					$h = $row[3];				// keep heat ID
					$hn = $row[4];				// heat name
					$p = 1;						// start with track one
					if($relay){ $filmnr = $row[13]; }else{ $filmnr = $row[16]; }

					// Heat info line
					// --------------
					printf("<tr>\n");
					if(is_null($row[1]))		// only one round
					{
?>
		<th class='dialog' colspan='2'><?php echo $strFinalround; ?></th>
<?php
					}
					else
					{
?>
		<th class='dialog'><?php echo $row[1]; ?></th>
		<th class='dialog'><?php echo $row[2]; ?></th>
<?php
					}
?>
		<form action='event_heats.php#heat_<?php echo $row[4]; ?>' method='post'
			name='heat_id_<?php echo $h; ?>'>

		<th class='dialog'>

			<input type='hidden' name='arg' value='change_heat_name' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />  
			<input type='hidden' name='item' value='<?php echo $row[3]; ?>' />
			<input class='nbr' type='text' name='id' maxlength='2'
				value='<?php echo $row[4]; ?>'
				onChange='document.heat_id_<?php echo $h;?>.submit()' />
				<a name='heat_<?php echo $row[4]; ?>'></a>
		</th>
		</form>

		<th class='dialog' />
		<th class='dialog'><?php echo $strInstallation; ?></th>

		<form action='event_heats.php#heat_<?php echo $row[4]; ?>' method='post'
			name='inst_selection_<?php echo $h; ?>'>
		<input type='hidden' name='arg' value='change_inst' />
		<input type='hidden' name='round' value='<?php echo $round; ?>' />    
		<input type='hidden' name='item' value='<?php echo $row[3]; ?>' />
			<?php
				$dd = new GUI_InstallationDropDown("document.inst_selection_$h.submit()", $row[5]);
			?>

		</form>
		<?php
		if(($disctype == $cfgDisciplineType[$strDiscTypeTrack])
			|| ($disctype == $cfgDisciplineType[$strDiscTypeTrackNoWind])
			|| ($disctype == $cfgDisciplineType[$strDiscTypeRelay])
			|| ($disctype == $cfgDisciplineType[$strDiscTypeDistance])) {
		?>
		<th><?php echo $strFilm ?></th>
		<th>
		
		<form action='event_heats.php#heat_<?php echo $row[4]; ?>' method='post'
			name='film_number_<?php echo $h; ?>'>
		<input type='hidden' name='arg' value='change_film' />
		<input type='hidden' name='round' value='<?php echo $round; ?>' />    
		<input type='hidden' name='item' value='<?php echo $row[3]; ?>' />
		<input type="text" name="film" value="<?php echo $filmnr ?>" size=3 onchange="document.film_number_<?php echo $h;?>.submit()">
		</form>
		</th>
		<?php
		}else{
			?>
		<th colspan=2></th>
			<?php
		}
		?>
		<th class='dialog'>
			<?php
				$btn = new GUI_Button("event_heats.php?arg=seed_heat&round=".$round."&heat=".$row[3], $strSeedHeat);
				$btn->printButton();
			?>
		</th>
	</tr>
<?php
					// Column header line
					// ------------------
					if($relay == FALSE) {
?>
	<tr>
		<th class='dialog'><?php echo $strPositionShort; ?></th>
		<th class='dialog'><?php echo $strNbr; ?></th>
		<th class='dialog'><?php echo $strAthlete; ?></th>
		<th class='dialog'><?php echo $strYearShort; ?></th>
		<th class='dialog'><?php echo $strCountry; ?></th>
		<th class='dialog' colspan='2'><?php if($svm){ echo $strTeam; }elseif ($teamsm){echo $strTeamsm;} else { echo $strClub;} ?></th>
        <!--<th class='dialog'><?php //echo $strPreviousSeasonBest; ?></th>-->
		<th class='dialog'><?php echo $strTopPerformance; ?></th>
		<?php
						if(($disctype == $cfgDisciplineType[$strDiscTypeTrack])
								|| ($disctype == $cfgDisciplineType[$strDiscTypeTrackNoWind])
								|| ($disctype == $cfgDisciplineType[$strDiscTypeRelay])
								|| ($disctype == $cfgDisciplineType[$strDiscTypeDistance])) {
		?>
		<th class='dialog' colspan='2'><?php echo $strTrack; ?></th>
		<?php
						}else{
		?>
		<th class='dialog' colspan='2'></th>
		<?php
						}
		?>
		<!--<th class='dialog' colspan="2"><?php echo $strHeat; ?></th>-->
	</tr>
<?php
					}
					else {
?>
	<tr>
		<th class='dialog'><?php echo $strPositionShort; ?></th>
		<th class='dialog'><?php echo $strNbr; ?></th>
		<th class='dialog' colspan='2'><?php echo $strRelay; ?></th>
        <th class='dialog'><?php if($svm){ echo $strTeam; } else { echo $strClub;} ?></th>
        <!--<th class='dialog'><?php //echo $strPreviousSeasonBest; ?></th>-->
		<th class='dialog'><?php echo $strTopPerformance; ?></th>
		<th class='dialog' colspan='2'><?php echo $strTrack; ?></th>
		<!--<th class='dialog' colspan="2"><?php echo $strHeat; ?></th>-->
	</tr>
<?php
					}
				}

				// Empty tracks
	
				// ------------
				// show empty track if current track and athlete's position
				// are not identical

				if($p < $row[7]) {
					$p = AA_heats_printEmptyTracks($p, ($row[7]-1), $h, $hn);
				}

				// Athlete/relay-data line
				// -----------------------
				$p = $row[7];				// keep position
				if($p % 2 == 0) {			// even position
					$rowclass='even';
				}
				else {						// odd position
					$rowclass='odd';
				}	
?>
	<tr class='<?php echo $rowclass; ?>'
		onClick='clickTrack(this, <?php echo $row[6].", ".$h.", \"".$hn."\", ".$row[7];?>)' style="cursor: pointer;">
		<td>
			<?php echo $row[7]; ?>
		</td>
		</form>

<?php
				if($relay == FALSE) {
?>
		<td><?php echo $row[9]; ?></td>
		<td>			<?php echo $row[10] . " " . $row[11]; ?></td>
		<td><?php echo AA_formatYearOfBirth($row[12]); ?></td>
		<td><?php echo (($row[18]!='' && $row[18]!='-') ? $row[18] : '&nbsp;');?></td>
		<td><?php echo $row[13]; ?></td>
		
<?php
				}
				else {
?>
		<td><?php echo $row[14]; ?></td>
		<td colspan='2'><?php echo $row[9]; ?></td>
		<td><?php echo $row[10]; ?></td>
        
        <?php
                }
?>
        <td>

   <?php             
                // show combined topperf if last combined discipline
                
                if($combined && $cLast == 1){
                    $row[20] = $row[21];
                    echo $row[20];
                }
                else{
                     /*
                    if($relay == TRUE) {
                        $previousSeasonBest = $row[15];
                    }
                    else {
                         $previousSeasonBest = $row[20];
                    }
                  
                   
                    if($previousSeasonBest == 0) {    // no season best set
                        echo "-";
                    }
                    else if(($disctype == $cfgDisciplineType[$strDiscTypeJump])
                            || ($disctype == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                            || ($disctype == $cfgDisciplineType[$strDiscTypeThrow])
                            || ($disctype == $cfgDisciplineType[$strDiscTypeHigh])) {
                        echo AA_formatResultMeter($previousSeasonBest);
                       
                    }
                    else {
                         echo AA_formatResultTime($previousSeasonBest);
                       
                    }
                    */
                }
?>
        </td>
        
        
		<td>
<?php
				
				
				// show combined topperf if last combined discipline
				if($combined && $cLast == 1){
					$row[8] = $row[17];
					echo $row[8];
				}else{
					if($row[8] == 0) {	// no top performance set
						echo "-";
					}
					else if(($disctype == $cfgDisciplineType[$strDiscTypeJump])
							|| ($disctype == $cfgDisciplineType[$strDiscTypeJumpNoWind])
							|| ($disctype == $cfgDisciplineType[$strDiscTypeThrow])
							|| ($disctype == $cfgDisciplineType[$strDiscTypeHigh])) {                        
						echo AA_formatResultMeter($row[8]);
					}
					else {                        
						echo AA_formatResultTime($row[8]);
					}
				}
?>
		</td>
        
        
        
        
		<?php
						if(($disctype == $cfgDisciplineType[$strDiscTypeTrack])
								|| ($disctype == $cfgDisciplineType[$strDiscTypeTrackNoWind])
								|| ($disctype == $cfgDisciplineType[$strDiscTypeRelay])
								|| ($disctype == $cfgDisciplineType[$strDiscTypeDistance])) {
		?>
		<td colspan='2'><input type="text" id="newtrack" size="3" value="<?php echo $tracknumber; ?>" name="newtrack<?php echo $row[6]; ?>"
			onfocus="focus_on('track', '<?php echo $h; ?>', '<?php echo $row[6]; ?>', this)"
			onblur="focus_off()"></td>
		<?php
						}else{
		?>
		<td colspan='2'></td>
		<?php
						}
		?>
		<!--<td><input type="text" id="newheat" size="3" value="<?php echo $hn; ?>" name="newheat"
			onfocus="focus_on('heat', '', '<?php echo $row[6]; ?>')"
			onblur="focus_off()"></td>-->
	</tr>
<?php
			}

			if($p > 0) {		// heats set up
				$p++;
				AA_heats_printEmptyTracks($p, $tracks, $h, $hn);
			}
?>
	<tr>
		<th class='dialog' colspan='9'><?php echo $strNewHeat; ?></th>
	</tr>
<?php
			// set a new heat name
			$newhn = chr((ord($hn)+1));
			AA_heats_printEmptyTracks(1, 1, 0, $newhn);
			printf("</table>");
			mysql_free_result($result);
		}		// ET DB error
	}
	// Some results entered
	else
	{
		if($status == $cfgRoundStatus['results_in_progress']) {
			AA_printWarningMsg($strResultsInWork);
		}
		// All results entered
		else if($status == $cfgRoundStatus['results_done']) {
			AA_printWarningMsg($strErrResultsEntered);
		}
?>
	<br/>
<?php

		$btn = new GUI_Button("event_results.php?round=$round", $strOpenResultView);
        $btn->printButton();
		
        $btn->set("dlg_print_contest.php?round=$round&print=yes", $strPrint . " ...");
        $btn->printButton();
	}
}		// ET round selected
}
else {
     AA_printErrorMsg($strErrMergedRound);     
}


$page->endPage();
?>
