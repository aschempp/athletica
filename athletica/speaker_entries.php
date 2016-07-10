<?php

/**********
 *
 *	speaker_entries.php
 *	-------------------
 *	
 */

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_searchfield.lib.php');
require('./lib/cl_performance.lib.php');

require('./lib/common.lib.php');
require('./lib/results.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$arg = (isset($_GET['arg'])) ? $_GET['arg'] : ((isset($_COOKIE['sort_speakentries'])) ? $_COOKIE['sort_speakentries'] : 'nbr');
setcookie('sort_speakentries', $arg, time()+2419200);

$page = new GUI_Page('speaker_entries');
$page->startPage();
$page->printPageTitle($strEntries. ": " . $_COOKIE['meeting']);
$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/speaker/entries.html', $strHelp, '_blank');
$menu->addSearchfield('speaker_entry.php', '_self', 'post', 'speaker_entries.php');
$menu->printMenu();

$round = 0;
if(!empty($_GET['round'])){
	$round = $_GET['round'];
}
else if(!empty($_POST['round'])) {
	$round = $_POST['round'];
}

$teamsm = false;
if (isset($_GET['teamsm'])){
    $teamsm = $_GET['teamsm'];
} 

$mk_group = '';
$tm_group = ''; 
if(!empty($_GET['group'])) {
    if ($teamsm) {
         $tm_group = $_GET['group']; 
    }
    else {
         $mk_group = $_GET['group']; 
    } 
}  



$presets = AA_results_getPresets($round);

$relay = AA_checkRelay($presets['event']);	// check, if this is a relay event

//
//	Display data
// ------------
?>
<p />

<table>
	<tr>
		<td class='forms'>
		<?php	AA_printCategorySelection("speaker_entries.php", $presets['category']); ?>
		</td>
		<td class='forms'>
		<?php	AA_printEventSelection("speaker_entries.php", $presets['category'], $presets['event'], "post"); ?>
		</td>
<?php
if($presets['event'] > 0) {		// event selected
	printf("<td class='forms'>\n");
	AA_printRoundSelection("speaker_entries.php", $presets['category'], $presets['event'], $round);
	printf("</td>\n");
}
?>
	</tr>
</table>

<?php
if($round > 0)
{   
    ?>
    <button type="button" onclick="window.open('print_contest_speaker_athletes.php?round=<?=$round?>')"><?=$strStartlist?></button>
    <?php
	// sort argument
	$img_nbr="img/sort_inact.gif";
	$img_name="img/sort_inact.gif";
	$img_club="img/sort_inact.gif";
	$img_perf="img/sort_inact.gif";

	if ($arg=="nbr" && !$relay) {        
		$argument="a.Startnummer";
		$img_nbr="img/sort_act.gif";
	} else if ($arg=="name") {
		$argument="ss.Position";
		$img_name="img/sort_act.gif";
	} else if ($arg=="club") {
		$argument="v.Sortierwert, a.Startnummer";
		$img_club="img/sort_act.gif";
	} else if ($arg=="perf") {
		$argument="s.Bestleistung";
		$img_perf="img/sort_act.gif";
	} else if ($arg=="relay") {
		$argument="st.Name";
		$img_name="img/sort_act.gif";
	} else if ($arg=="relay_club") {
		$argument="v.Sortierwert, st.Name";
		$img_club="img/sort_act.gif";
	} else if($relay == FALSE) {		// single event
		$argument="at.Name, at.Vorname";
		$img_name="img/sort_act.gif";
	} else {									// relay event
		$argument="st.Name";
		$img_name="img/sort_act.gif";
	}  
    
    $mergedEvents=AA_getMergedEventsFromEvent($presets['event']);    
      
    if ($mergedEvents!=''){
       $sqlEvent=" IN ". $mergedEvents;        
    }
    else {
        $sqlEvent=" = ". $presets['event'];  
    }          
   
    $sqlGroup = "";
    if  (!empty($mk_group)) {
              $sqlGroup = " AND a.Gruppe =  " .$mk_group; 
    }
    elseif (!empty($tm_group)){
                $sqlGroup =" AND s.Gruppe =  " .$tm_group; 
    }
   
	if($relay == FALSE) 		// single event
	{             		
        $query = "
            SELECT
                a.xAnmeldung
                , a.Startnummer
                , at.Name
                , at.Vorname
                , at.Jahrgang
                , v.Name  
                , at.xAthlet
                , s.Bestleistung
                , w.xDisziplin
                , d.Typ
            FROM
                anmeldung AS a
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet  )
                LEFT JOIN start AS s ON (a.xAnmeldung = s.xAnmeldung)
                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                LEFT JOIN wettkampf AS w ON (w.xWettkampf = s.xWettkampf)
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
            WHERE s.xWettkampf " . $sqlEvent . "
                " . $sqlGroup . "                 
                AND s.Anwesend = 0                    
            ORDER BY " . $argument;       
           
	}
	else {							// relay event
		
        $query= "
            SELECT
                s.xStaffel
                , st.Name
                , v.Name
            FROM
                staffel AS st
                LEFT JOIN start AS s ON (st.xStaffel = s.xStaffel )
                LEFT JOIN verein AS v ON (v.xVerein = st.xVerein)
            WHERE s.xWettkampf " . $sqlEvent . "           
            AND s.Anwesend = 0   
            ORDER BY " . $argument;          
           
          
	}
    
    $result = mysql_query($query);      
	
	if(mysql_errno() > 0)		// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else if(mysql_num_rows($result) > 0)  // data found
	{
		?>
<table class='dialog'>
	<tr>
		<?php
		if($relay == FALSE)		// single event
		{
			?>
		<th class='dialog'>
			<a href='speaker_entries.php?arg=nbr&round=<?php echo $round; ?>'>
				<?php echo $strStartnumber; ?>
				<img src='<?php echo $img_nbr; ?>' />
			</a>
		</th>
		<th class='dialog'>
			<a href='speaker_entries.php?arg=name&round=<?php echo $round; ?>'>
				<?php echo $strName; ?></a>
				<img src='<?php echo $img_name; ?>' />
			</th> 
       
		<th class='dialog'>
		<?php echo $strYear; ?>
		</th>
		<th class='dialog'>
			<a href='speaker_entries.php?arg=club&round=<?php echo $round; ?>'>
				<?php echo $strClub; ?>
				<img src='<?php echo $img_club; ?>' />
			</a>
		</th>
		<th class='dialog'>
			<a href='speaker_entries.php?arg=perf&round=<?php echo $round; ?>'>
				<?php echo $strTopPerformance; ?>
				<img src='<?php echo $img_perf; ?>' />
			</a>
		</th>
			<?php
		}
		else		// relay event
		{
			?>
		<th class='dialog'>
			<a href='speaker_entries.php?arg=relay&round=<?php echo $round; ?>'>
				<?php echo $strRelays; ?>
				<img src='<?php echo $img_name; ?>' />
			</a>
		</th>
		</th>
		<th class='dialog'>
			<a href='speaker_entries.php?arg=relay_club&round=<?php echo $round; ?>'>
				<?php echo $strClub; ?>
				<img src='<?php echo $img_club; ?>' />
			</a>
		</th>
			<?php
		}
		?>
	</tr>
		<?php

		$i=0;
		$rowclass = "odd";

		while ($row = mysql_fetch_row($result))
		{
			$i++;
			if( $i % 2 == 0 ) {		// even row number
				$rowclass = "even";
			}
			else {	// odd row number
				$rowclass = "odd";
			}

			// print row: onClick show athlete- or relay-details
			?>
	<tr class='<?php echo $rowclass; ?>' onClick='window.open("speaker_entry.php?item=<?php echo $row[6]; ?>", "_self")' style="cursor: pointer;">
			<?php
			if($relay == FALSE)			// single event
			{
				
			if(($row[9] == $cfgDisciplineType[$strDiscTypeTrack])
			|| ($row[9] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
			|| ($row[9] == $cfgDisciplineType[$strDiscTypeRelay])
			|| ($row[9] == $cfgDisciplineType[$strDiscTypeDistance]))
		{
			$perf = AA_formatResultTime($row[7], true);
		}
		else {
			$perf = AA_formatResultMeter($row[7]);
		}
				
				?>
		<td class='forms_right'><?php echo $row[1]; ?></td>
		<td><?php echo "$row[2] $row[3]"; ?></td> 
         
		<td class='forms_ctr'><?php echo AA_formatYearOfBirth($row[4]); ?></td>
		<td><?php echo $row[5]; ?></td>
		<td><?php echo $perf; ?></td>
				<?php
			}
			else							// relay event
			{
				?>
		<td><?php echo $row[1]; ?></td>
		<td><?php echo $row[2]; ?></td>
				<?php
			}
			?>
	</tr>
			<?php
		}
		?>
</table>
		<?php
	}
	else
	{
		AA_printWarningMsg($strNoEntries);
	}
	mysql_free_result($result);
}

$page->endPage();


