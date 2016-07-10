<?php
/**********
 *
 *	meeting_definition.php
 *	----------------------
 *	
 */

require('./convtables.inc.php');

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_select.lib.php');
require('./lib/cl_timetable.lib.php');

require('./lib/meeting.lib.php');
require('./lib/common.lib.php');


if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$event = 0;
if(!empty($_POST['item'])) {
	$event = $_POST['item'];
}
else if(!empty($_GET['item'])) {
	$event = $_GET['item'];
}

$category = 0;
if(!empty($_POST['cat'])) {
	$category = $_POST['cat'];
}
else if(!empty($_GET['cat'])) {
	$category = $_GET['cat'];
}

$xDiscipline = 0;   
$date_keep = ''; 

//
// Process changes to meeting data
//

$GLOBALS['AA_ERROR'] = '';


// change event data
if ($_POST['arg']=="change_event")
{
   
	    AA_meeting_changeEvent();
    
    
}
// delete a single event
else if ($_POST['arg']=="del_event")
{
	AA_meeting_deleteEvent();
}
else if ($_POST['arg']=="add_round")
{   
	list($_POST['hr'], $_POST['min']) = AA_formatEnteredTime($_POST['time']);
	
	$result = mysql_query("
		SELECT
			Typ
			, Appellzeit
			, Stellzeit
		FROM 
			disziplin_" . $_COOKIE['language'] . "
		WHERE xDisziplin = " . $_POST['xDis']
	);
	$row = mysql_fetch_row($result);
	$stdEtime = strtotime($row[1]); // hold standard delay for enrolement time
	$stdMtime = strtotime($row[2]); // and manipulation time
	
	if(empty($_POST['etime'])){ // if enrolement time is empty, calculate with discipline standard (1h before)
		$tmp = strtotime($_POST['hr'].":".$_POST['min'].":00");
		$tmp = $tmp - $stdEtime;
		$_POST['etime'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
	}
	if(empty($_POST['mtime'])){ // if manipulation time is empty, calculate with discipline standard (15min before)
		$tmp = strtotime($_POST['hr'].":".$_POST['min'].":00");
		$tmp = $tmp - $stdMtime;
		$_POST['mtime'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
	}
	
	// the other times are parsed in the timetable constructor
	$tt = new Timetable();
	$tt->add();
}
else if ($_POST['arg']=="del_round")
{
	$tt = new Timetable();
	$tt->delete();
}
else if ($_POST['arg']=='change_round')
{
	list($_POST['hr'], $_POST['min']) = AA_formatEnteredTime($_POST['time']);
   
    // auto configure enrolement and manipulation time
    $result = mysql_query("
        SELECT
            d.Typ
            , d.Appellzeit
            , d.Stellzeit
        FROM
            wettkampf as w
            LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d USING(xDisziplin)
        WHERE w.xWettkampf = " . $_POST['item']
    );
    $row = mysql_fetch_row($result);
    $stdEtime = strtotime($row[1]); // hold standard delay for enrolement time
    $stdMtime = strtotime($row[2]); // and manipulation time
    
    $tmp = strtotime($_POST['hr'].":".$_POST['min'].":00");
    $tmp = $tmp - $stdEtime;
    $_POST['etime'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
    
    $tmp = strtotime($_POST['hr'].":".$_POST['min'].":00");
    $tmp = $tmp - $stdMtime;
    $_POST['mtime'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
    
    
	// the other times are parsed in the timetable constructor
	$tt = new Timetable();
	$tt->change();
}

//
// process round merge requests
//
elseif($_POST['arg'] == "merge_add"){
	         
	if(!empty($_POST['mainRound']) && !empty($_POST['round'])){
		
		//mysql_query("LOCK TABLES rundenset WRITE, serie READ");
        mysql_query("LOCK TABLES rundenset WRITE, rundenset as rs READ ,serie READ, runde AS r Read, wettkampf AS w READ, meeting READ ");
   	
		$mr = $_POST['mainRound'];
		$r = $_POST['round'];
		$rs = $_POST['roundSet'];     
		
		if(AA_checkReference("serie", "xRunde", $mr) != 0
			|| AA_checkReference("serie", "xRunde", $r) != 0)
		{
			$GLOBALS['AA_ERROR'] = $strErrHeatsAlreadySeeded;
		}else{
			// check group  (rounds with groups can not be merged)
             $res = mysql_query("SELECT r.Gruppe FROM runde As r WHERE r.xRunde = ".$mr. " OR r.xRunde = ".$r); 
             if(mysql_errno() > 0){
                        $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
             }else{
                 if(mysql_num_rows($res) > 0){
                        $row = mysql_fetch_array($res);
                        if (!empty($row[0])){
                             $GLOBALS['AA_ERROR'] = $strErrMergeGroup;
                        }
                 }
             }
            if (empty($GLOBALS['AA_ERROR'])) { 
                $c1=AA_countRound($r);
                $c2=AA_countRound($mr); 
                if ( $c1==$c2)  {
            
			        // round set not yet created
			        if(empty($rs)){
				        // get next roundset number
				        $res = mysql_query("SELECT MAX(xRundenset) FROM rundenset");
				        $max = 0;
				        if(mysql_num_rows($res) > 0){
					        $row = mysql_fetch_array($res);
					        $max = $row[0];
				        }
				        $max++;
				
				        mysql_query("INSERT INTO rundenset SET
						    xRundenset = $max
						    , Hauptrunde = 1
						    , xRunde = $mr
						    , xMeeting = ".$_COOKIE['meeting_id']);
				        if(mysql_errno() > 0){
					        $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
				        }else{
					        $rs = $max;
				        }
			        }
			      
			        // insert new round  
			        if($rs > 0){ 
				        mysql_query("INSERT INTO rundenset SET
						    xRundenset = $rs
						    , Hauptrunde = 0
						    , xRunde = $r
						    , xMeeting = ".$_COOKIE['meeting_id']);
				        if(mysql_errno() > 0){
					        $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
				        }
			        } 
                    AA_getAllRoundsforChecked($event,$action='add',$r);     // set checked automatic
                }
                else {   
                    AA_printErrorMsg($strMergeRoundsErr);    
                }  
            } 
		}   
		mysql_query("UNLOCK TABLES");  
	}
	
}elseif($_POST['arg'] == "merge_del"){
	               
	if(!empty($_POST['mainRound']) && !empty($_POST['round']) && !empty($_POST['roundSet'])){
		
		//mysql_query("LOCK TABLES rundenset WRITE, serie READ");
		mysql_query("LOCK TABLES rundenset WRITE, rundenset as rs READ,serie READ, runde AS r Read, wettkampf AS w READ, meeting READ ");
        
		$mr = $_POST['mainRound'];
		$r = $_POST['round'];
		$rs = $_POST['roundSet'];
		
		if(AA_checkReference("serie", "xRunde", $mr) != 0
			|| AA_checkReference("serie", "xRunde", $r) != 0)
		{
			$GLOBALS['AA_ERROR'] = $strErrHeatsAlreadySeeded;
		}else{
			
			// remove round from set
			mysql_query("DELETE FROM rundenset WHERE
					xRundenset = $rs
					AND xMeeting = ".$_COOKIE['meeting_id']."
					AND xRunde = $r");
			if(mysql_errno() > 0){
				$GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
			}else{
				
				// check if there are no more rounds in set
				$res = mysql_query("SELECT * FROM rundenset WHERE
							xRundenset = $rs");
				if(mysql_errno() > 0){
					$GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
				}else{
					
					if(mysql_num_rows($res) == 1){ // mainround only  
						mysql_query("DELETE FROM rundenset WHERE
							xRundenset = $rs
							AND xMeeting = ".$_COOKIE['meeting_id']);
						if(mysql_errno() > 0){
							$GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
						}
					}  
				}  
			} 
            AA_getAllRoundsforChecked($event,$action='del',$r);      // delete corresponding rounds checked    
		} 
		mysql_query("UNLOCK TABLES");  
	}    
}
         
// Check if any error returned
if(!empty($GLOBALS['AA_ERROR'])) {  
		AA_printErrorMsg($GLOBALS['AA_ERROR']);
}
else if(mysql_errno() > 0) {
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}


$page = new GUI_Page('meeting_definitions');
$page->startPage();
?>

<script type="text/javascript">
<!--
	window.open("meeting_definition_eventlist.php?item="
		+ <?php echo $event; ?> + "&updateCat=" + <?php echo $category; ?>,
		"list");

	function setArgument(arg, form)
	{
		form.arg.value = arg;
	}

	function check(item)
	{
		if((item == 'roundtype')
			&& (document.round.roundtype.value == 'new'))
		{
			window.open("admin_roundtypes.php", "main");
		}
	}
	
	
	// functions for merging rounds
	function add_mergedRound(mr, round){
		
		document.forms["mergeRounds"+mr].arg.value="merge_add";
		document.forms["mergeRounds"+mr].round.value=round;
		document.forms["mergeRounds"+mr].submit();
		
	}
	
	function del_mergedRound(mr, round){
		
		document.forms["mergeRounds"+mr].arg.value="merge_del";
		document.forms["mergeRounds"+mr].round.value=round;
		document.forms["mergeRounds"+mr].submit();
		
	}
    
    
    function info_warning() {
        var box=confirm("<?php echo $GLOBALS['strChangeInfo']; ?>");
       
        if(box==false){               
               document.forms['event'].info.value = document.forms['event'].info_keep.value;  
        } 
           
        document.event.submit();   
    }
       
    
//-->
</script>

<?php
/*****************************************
 *
 *	 Show Event	
 *
 *****************************************/
                                   
$sql = "SELECT
        w.xWettkampf
        , w.xKategorie
        , w.Typ
        , w.Haftgeld
        , w.Startgeld
        , w.Punktetabelle
        , w.Punkteformel
        , k.Name
        , d.Name
        , d.xDisziplin
        , d.Typ
        , w.Windmessung
        , w.Info
        , w.Zeitmessung
        , w.ZeitmessungAuto
    FROM
        wettkampf AS w
        LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
        LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d  ON (w.xDisziplin = d.xDisziplin)
    WHERE w.xWettkampf = $event
    ORDER BY
        k.Anzeige
        , d.Anzeige";
 
$result = mysql_query($sql);  

if(mysql_errno() > 0) {	// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if(mysql_num_rows($result) > 0)  // data found
{
	$row = mysql_fetch_row($result);
	$xDiscipline = $row[9];      
	
	$page->printPageTitle("$row[7], $row[8]");
?>
<table class='dialog'>
<tr>
	<form action='meeting_definition_event.php' method='post' name='event'>
	<th class='dialog'><?php echo $strInfo; ?></th>
	<td class='forms'>
		<input name='arg' type='hidden' value='change_event' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
		<input name='act_disc' type='hidden' value='<?php echo $i; ?>' />

		<input class='text' name='info' id='info' type='text' maxlength='15'
			value="<?php echo $row[12]; ?>" 
			onChange='info_warning()' />
        <input name='info_keep' id='info_keep' type='hidden' value='<?php echo $row[12]; ?>' />    
	</td>
</tr>
<?php       

	// disciplines, where wind may be measured
	if(($row[10] == $cfgDisciplineType[$strDiscTypeTrack])
		|| ($row[10] == $cfgDisciplineType[$strDiscTypeJump]))
	{
		if($row[11] == 0) {	// wind not measured (zero)
			$wind = 1;
			$checked = "";
		}
		else {					// wind measured (one)
			$wind = 0;
			$checked = "checked";
		}
?>
<tr>
	<th class='dialog'><?php echo $strWind; ?></th>
	<td class='forms'>
		<input type='checkbox' name='wind' value='yes'
			<?php echo $checked; ?>
			onClick='document.event.submit()' />
	</td>

<?php
	}
?>
<tr>
	<th class='dialog'><?php echo $strDeposit; ?></th>
	<td class='forms'>
		<input class='nbr' name='deposit' type='text' maxlength='10'
			value='<?php echo $row[3]; ?>'
			onChange='document.event.submit()' />
	</td>
</tr>

<tr>
	<th class='dialog'><?php echo $strFee; ?></th>
	<td class='forms'>
		<input class='nbr' name='fee' type='text' maxlength='10'
			value='<?php echo $row[4]; ?>'
			onChange='document.event.submit()' />
	</td>
</tr>
<?php
	// if time can be measured
	if(($row[10] == $cfgDisciplineType[$strDiscTypeNone])
		|| ($row[10] == $cfgDisciplineType[$strDiscTypeTrack])
		|| ($row[10] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
		|| ($row[10] == $cfgDisciplineType[$strDiscTypeDistance])
		|| ($row[10] == $cfgDisciplineType[$strDiscTypeRelay]))
	{
		if($row[13] == 1){
			$t1 = "checked";
		}else{
			$t1 = "";
		}
		if($row[14] == 1){
			$t2 = "checked";
		}else{
			$t2 = "";
		}
?>
<tr>
	<th class='dialog'><?php echo $strTiming; ?></th>
	<td class='forms'>
		<input type="checkbox" name="timing" value="yes" onChange='document.event.submit()' <?php echo $t1 ?>>
		<?php echo $strAutomatic ?>:<input type="checkbox" name="timingAuto" value="yes"
		onChange='document.event.submit()' <?php echo $t2 ?>>
	</td>
</tr>

<?php
	}

	if($row[2] > 0) {		// not a single event
?>
<tr>
	<th class='dialog'><?php echo $strConversionFormula; ?></th>
	<td class='forms'>
<?php
		if($row[5]==$cvtTable[$strConvtableRankingPoints] || $row[5]==$cvtTable[$strConvtableRankingPointsU20]){
			?>
			<input type="text" name="formula" value="<?php echo $row[6]; ?>" style="width: 45px;" onchange="document.event.submit()"/>
			<?php
		} else {
			$dropdown = new GUI_Select('formula', 1, "document.event.submit()");
			foreach($cvtFormulas[$row[5]] as $key=>$value)
			{
				$dropdown->addOption($key, $key);
				if($row[6] == $key) {
					$dropdown->selectOption($key);
				}
			}
			$dropdown->printList();
		}
	}

	$show = true;
?>
	</td>
</tr>
</form>
</table>

<?php
	mysql_free_result($result);
}		// ET DB error
 
$roundsArr = array(); // initialize round array

// show category selection
$result = mysql_query("
	SELECT
		r.xRunde
		, r.Datum
		, TIME_FORMAT(r.Startzeit, '%H') 
		, TIME_FORMAT(r.Startzeit, '%i') 
		, r.xWettkampf
		, r.xRundentyp
		, TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
		, TIME_FORMAT(r.Appellzeit, '$cfgDBtimeFormat')
		, TIME_FORMAT(r.Stellzeit, '$cfgDBtimeFormat')
		, rs.Hauptrunde
		, rt.Name
		, rs.xRundenset
        , r.Gruppe
	FROM
		runde AS r
		LEFT JOIN rundenset AS rs ON (rs.xRunde = r.xRunde AND rs.xMeeting = ". $_COOKIE['meeting_id'] .")  
		LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
	WHERE r.xWettkampf = $event
	ORDER BY
		r.Datum
		, r.Startzeit
");
     
if(mysql_errno() > 0) {	// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else			// no DB error
{
	?>
<p/>

<table class='dialog'>
	<?php
	$i=0;

  	while($row = mysql_fetch_row($result))
  	{  
		$roundsArr[] = array($row[0], $row[9], $row[10], $row[6], $row[11], $row[9]);
		
		if($i==0)	// first row: show category headerline
		{
			//	Headerline category
			?>
<tr>
	<th class='dialog'><?php echo $strType; ?></th>
	<th class='dialog'><?php echo $strDate; ?></th>
	<th class='dialog'><?php echo $strTimeFormat; ?></th>
	<th class='dialog'><?php echo $strEnrolementTime; ?></th>
	<th class='dialog'><?php echo $strManipulationTime; ?></th>
</tr>
			<?php
		}

		$i++;
		?>
<tr>
<form action='meeting_definition_event.php' method='post' name='rnd_<?php echo $row[0]; ?>'>
	<input name='arg' type='hidden' value=''>
	<input name='round' type='hidden' value='<?php echo $row[0]; ?>'>
	<input name='item' type='hidden' value='<?php echo $event; ?>'>
	<input name='cat' type='hidden' value='<?php echo $category; ?>' />
	<input name='xDis' type='hidden' value='<?php echo $xDiscipline; ?>' />     
    <input name='g' type='hidden' value='<?php echo $row[12]; ?>' />  
		<?php
		$dd = new GUI_RoundtypeDropDown($row[5]);
		$dd = new GUI_DateDropDown($row[1]);
		?>
	<!--<td class='forms'>
		<input class='nbr' type='text' name='hr' maxlength='2'
			value='<?php echo $row[2]; ?>' />
	</td>
	<td class='forms'>
		<input class='nbr' type='text' name='min' maxlength='2'
			value='<?php echo $row[3]; ?>' />
	</td>-->
	<td class='forms'>
		<input size="4" type='text' name='time' maxlength='5'
			value='<?php echo $row[6]; ?>' />
	</td>
	<td class='forms'>
		<input size="4" type='text' name='etime' maxlength='5'
			value='<?php echo $row[7]; ?>' />
	</td>
	<td class='forms'>
		<input size="4" type='text' name='mtime' maxlength='5'
			value='<?php echo $row[8]; ?>' />
	</td>
	<td class='forms'>
		<button name='change' type='submit' onClick='setArgument("change_round", document.rnd_<?php echo $row[0]; ?>)'>
			<?php echo $strChange; ?></button>
	</td>
	<td class='forms'>
		<button name='delete' type='submit' onClick='setArgument("del_round", document.rnd_<?php echo $row[0]; ?>)'>
			<?php echo $strDelete; ?></button>
	</td>
</form>
</tr>
		<?php
        
    $date_keep = $row[1]; 
       
	}

	if($show == true)		// any event
	{   
		?>
<tr>
	<th class='dialog' colspan='5'><?php echo $strNew; ?></th>
</tr>
<tr>
<form action='meeting_definition_event.php' method='post' name='rnd_new'>
	<input name='arg' type='hidden' value='add_round'>
	<input name='round' type='hidden' value='<?php echo $row[0]; ?>'>
	<input name='item' type='hidden' value='<?php echo $event; ?>'>
	<input name='cat' type='hidden' value='<?php echo $category; ?>' />
	<input name='xDis' type='hidden' value='<?php echo $xDiscipline; ?>' />
		<?php
		$dd = new GUI_RoundtypeDropDown(0);  
       if ($date_keep == '' ) {   
	        $dd = new GUI_DateDropDown(0); 
        }
        else {
             $dd = new GUI_DateDropDown($date_keep); 
        }                           
		?>
	<!--<td class='forms'>
		<input class='nbr' type='text' name='hr' maxlength='2'
			value='' />
	</td>
	<td class='forms'>
		<input class='nbr' type='text' name='min' maxlength='2'
			value='' />
	</td>-->
	<td class='forms'>
		<input size="4" type='text' name='time' maxlength='5'
			value='' />
	</td>
	<td class='forms'>
		<input size="4" type='text' name='etime' maxlength='5'
			value='' />
	</td>
	<td class='forms'>
		<input size="4" type='text' name='mtime' maxlength='5'
			value='' />
	</td>
	<td class='forms' colspan='2'>
		<button name='add' type='submit'>
			<?php echo $strAdd; ?></button>
	</td>
</tr>
</form>
		<?php
	}

	?>
</table>
	<?php

	if(($i == 0) && ($show == true))	// no rounds -> show delete event button
	{
		?>
<p/>

<form action='meeting_definition_event.php' method='post' name='del_event'>
	<input name='arg' type='hidden' value='del_event' />
	<input name='item' type='hidden' value='<?php echo $event;?>' />
	<input name='cat' type='hidden' value='<?php echo $category; ?>' />
	<button type='submit'
		onChange='document.del_event.submit()' />
		<?php echo $strDelete; ?>
	</button>
</form>
		<?php
	}
	mysql_free_result($result);
}		// ET DB error


// show dialog for mergeing rounds together

?>
<br>
<table class="dialog">
<tr><th class="dialog"><?php echo $strMergeRounds ?></th></tr>
<?php

if($i == 0){	// no rounds here
	?>
<tr><td class="dialog"><?php echo $strMergeRoundsNoRound ?></td></tr>
	<?php
}else{
	// select any other rounds in same discipline but not current event
	$mRounds = array();	
			
	$sql = "SELECT  k.Name
				  , rt.Name
				  , rs.xRundenset
				  , rs.Hauptrunde
				  , ru.xRunde
				  , TIME_FORMAT(ru.Startzeit, '".$cfgDBtimeFormat."') 
                  , w.Info
			  FROM 
			  	   wettkampf AS w 
		 LEFT JOIN runde AS ru USING(xWettkampf) 
		 LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON(rt.xRundentyp = ru.xRundentyp) 
		 LEFT JOIN kategorie AS k ON(k.xKategorie = w.xKategorie) 
		 LEFT JOIN rundenset AS rs ON(rs.xRunde = ru.xRunde AND rs.xMeeting = " .$_COOKIE['meeting_id'].") 
		 	 WHERE w.xMeeting = ".$_COOKIE['meeting_id']." 
		 	   AND w.xWettkampf != ".$event."
		 	   AND w.xDisziplin = ".$xDiscipline." 
		  ORDER BY k.Anzeige
				 ";
	$res = mysql_query($sql);
	
	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	elseif(mysql_num_rows($res) > 0){ // rounds for mergeing found
		
		$notMainRound = false;
		while($row = mysql_fetch_array($res)){
			$mRounds[] = $row;
			if($row[3] > 0){
				$notMainRound = true;
			}
		}
		
		
		// setup form for merging
		foreach($roundsArr as $round){
			?>
			<form action="meeting_definition_event.php" method="POST" name="mergeRounds<?php echo $round[0] ?>">
			<input type="hidden" name="arg" value="">
			<input type="hidden" name="mainRound" value="<?php echo $round[0] ?>">
			<input type="hidden" name="roundSet" value="<?php echo $round[4] ?>">
			<input type="hidden" name="item" value="<?php echo $event ?>">
			<input type="hidden" name="cat" value="<?php echo $category ?>">
			<input type="hidden" name="round" value="">
			</form>
			<tr>
				<th class="dialog"><?php echo $round[2]." (".$round[3].")" ?></th>
				<th class="dialog"></th>
				<th class="dialog"><?php echo $strMergeHere ?></th>
			</tr>
			<?php
			foreach($mRounds as $mr){
				/*
				$round[4] = rundenSet SelRound
				$mr[2] = rundenSet ActRound
				$round[5] = main round
				*/
				if((intval($mr[2]) == 0 || (intval($mr[2]) == intval($round[4]))) && (intval($round[5]) == 1 || (intval($round[5]) == 0 && intval($round[4]) == 0))){ // the actual round is a main round or is not merged
					?>
					<tr>
						<td class="dialog"><?php echo $mr[1]." (".$mr[5].")" ?></td>
						<td class="dialog"><?php echo $mr[0] ." (" . $mr[6] . ") "?></td>
						<td class="forms">
							<?php
							if(intval($mr[2]) == intval($round[4]) && intval($round[4]) > 0){
								?>
								<input type="checkbox" name="merge<?php echo $mr[4] ?>" checked
									onclick="del_mergedRound(<?php echo $round[0] ?>, <?php echo $mr[4] ?>)">
								<?php
							}else{
								?>
								<input type="checkbox" name="merge<?php echo $mr[4] ?>"
									onclick="add_mergedRound(<?php echo $round[0] ?>, <?php echo $mr[4] ?>)">
								<?php
							}
							?>
						</td>
					</tr>
					<?php
				} else { // rounds are merged and the selected round isn't the main round
					if(intval($round[5]) == 0 && intval($mr[2]) == intval($round[4])){
						?>
						<tr>
							<td class="dialog"><?php echo $mr[1]." (".$mr[5].")" ?></td>
							<td class="dialog"><?php echo $mr[0] ." (" . $mr[6] . ") "?></td>
							<td class="dialog"><?php
							if($mr[3] > 0){
								echo $strMergeMain;
							}
							?></td>
						</tr>
						<?php
					}
				}

				/*if($notMainRound || ($mr[2] > 0 && $mr[2] != $round[4])){ // rounds are merged on another round
											// either one of them is main round or round set is not the same

					if($mr[2] == $round[4]){	// show only rounds that are merged with the selected one
						?>
						<tr>
							<td class="dialog"><?php echo $mr[1]." (".$mr[5].")" ?></td>
							<td class="dialog"><?php echo $mr[0] ?></td>
							<td class="dialog"><?php
							if($mr[3] > 0){
								echo $strMergeMain;
							}
							?></td>
						</tr>
						<?php
					}
				}else{	// rounds can be merged here -> admin functionality
					?>
					<tr>
						<td class="dialog"><?php echo $mr[1]." (".$mr[5].")" ?></td>
						<td class="dialog"><?php echo $mr[0] ?></td>
						<td class="forms">
							<?php
							if($mr[2] > 0){
								?>
								<input type="checkbox" name="merge<?php echo $mr[4] ?>" checked
									onclick="del_mergedRound(<?php echo $round[0] ?>, <?php echo $mr[4] ?>)">
								<?php
							}else{
								?>
								<input type="checkbox" name="merge<?php echo $mr[4] ?>"
									onclick="add_mergedRound(<?php echo $round[0] ?>, <?php echo $mr[4] ?>)">
								<?php
							}
							?>
						</td>
					</tr>
					<?php
				}*/
				
			}
			
			?>
			<tr>
				<td class="dialog"> </td>
			</tr>
			<?php
			
		}
		
	}else{ // no rounds found
		?>
<tr><td class="dialog"><?php echo $strMergeRoundsNoDisc ?></td></tr>
		<?php
	}
}

?>
</table>
<?php

$page->endPage();
?>
