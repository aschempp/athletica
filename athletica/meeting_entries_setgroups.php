<?php

/**********
 *
 *	meeting_entries_setgroups.php
 *	--------------------------------
 *	
 *	assign groups for combined and team sm events
 *	
 */

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(empty($_COOKIE['meeting_id'])) {
	AA_printErrorMsg($GLOBALS['strNoMeetingSelected']);
}




//
// show dialog 
//

$page = new GUI_Page('meeting_entries_setgroups');
$page->startPage();


// sort argument
$img_nbr="img/sort_inact.gif";
$img_name="img/sort_inact.gif";
$img_comb="img/sort_inact.gif";
$img_pole="img/sort_inact.gif";
$img_high="img/sort_inact.gif";

$argument = "";
$direction = "asc";
if ($_GET['sort']=="nbr") {
	$argument="4";
	$img_nbr="img/sort_act.gif";
} else if ($_GET['sort']=="name") {
	$argument="1";
	$img_name="img/sort_act.gif";
} else if ($_GET['sort']=="comb") {
	$argument="8";
	$direction = "desc";
	$img_comb="img/sort_act.gif";
} else if ($_GET['sort']=="pole") {
	$argument="pole";
	$direction = "desc";
	$img_pole="img/sort_act.gif";
} else if ($_GET['sort']=="high") {
	$argument="high";
	$direction = "desc";
	$img_high="img/sort_act.gif";
} else {
	$argument="4";
	$img_nbr="img/sort_act.gif";
}

// sort function
function cmp($a, $b)
{
	global $argument, $direction;
	if($direction == "asc"){
		return strcmp($a[$argument], $b[$argument]);
	}else{
		return strcmp($b[$argument], $a[$argument]);
	}
}


// arguments
$category = 0;
if(isset($_GET['category'])){
	$category = $_GET['category'];
}

$disc = 0;
if(isset($_GET['disc'])){
	$disc = $_GET['disc'];
}

$comb = 0;
if(isset($_GET['comb'])){
	$comb = $_GET['comb'];
	if(strpos($comb, "_") !== false){
		list($category, $comb) = explode('_', $comb);
	}
}

$max = 0;

//
// save entered group numbers
if($_POST['arg'] == "save"){
	$flagHeats = false; 
	foreach($_POST['groups'] as $entry => $group){
		
        $sql="SELECT r.Status       
               FROM
                    anmeldung  AS a                      
                    LEFT JOIN start as s ON (a.xAnmeldung = s.xAnmeldung) 
                    LEFT JOIN runde as r On (r.xWettkampf=s.xWettkampf)
                    LEFT JOIN wettkampf as w On (w.xWettkampf=s.xWettkampf)    
               WHERE
                    a.xAnmeldung=" . $entry . "    
                    AND a.Gruppe = r.Gruppe                        
                    AND a.xMeeting=" .$_COOKIE['meeting_id']; 
        
        $res=mysql_query($sql);  
        if(mysql_errno() > 0)
        {
              AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else { 
              mysql_query("UPDATE anmeldung SET Gruppe = '$group' WHERE xAnmeldung = ".$entry);     
             
              if (mysql_num_rows($res) > 0){
                  $row= mysql_fetch_row($res);
                  if ($row[0] >= 1 && $row[0] <= 4 ) {
                        $flagHeats = true;    
                  }   
               }  
        }   
	}
    if ($flagHeats){                 
        AA_printErrorMsg($strErrAthletesSeeded);   
	}
}
//
// assign groups by sort argument and max people per group
elseif($_POST['arg'] == "assign"){
	
	$max = $_POST['max'];
	
}

//
// test on rounds if groups will be assigned or removed
//
if($_POST['arg'] == "save" || $_POST['arg'] == "assign"){
	
	mysql_query("LOCK TABLES runde as r WRITE, runde WRITE, wettkampf as w WRITE, anmeldung as a READ, start as s READ");
	
	// if there are already rounds with no group, set all of them to the lowest group given
	// (do not update rounds in last combined contest)
	$res = mysql_query("SELECT * FROM
				runde as r
				LEFT JOIN wettkampf as w ON (r.xWettkampf = w.xWettkampf)
			WHERE 				
				w.Mehrkampfende = 0
			AND	w.Mehrkampfcode = $comb
			AND	w.xKategorie = $category
			AND	w.xMeeting = ".$_COOKIE['meeting_id']);
   
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		if(mysql_num_rows($res) > 0){ // got rounds without group
			$g = "";
			if($_POST['arg'] == "save"){ // the lowest group will be in $_POST['groups']
				
				asort($_POST['groups']);
				foreach($_POST['groups'] as $gr){
					$g = $gr;
					if(!empty($g)){
						break;
					}
				}
				
			}else{ // auto assign, lowest group will be 1
				
				$g = 1;
				
			}     
		}
	}
	
	mysql_query("UNLOCK TABLES");
	
}elseif($_POST['arg'] == "remove"){
	
	mysql_query("LOCK TABLES runde as r WRITE, wettkampf as w READ, runde WRITE, serie READ");
	
	// if there are already rounds with groups, remove all but the one with the lowest group
	$res = mysql_query("SELECT r.xRunde, r.Gruppe FROM
				runde as r
				LEFT JOIN wettkampf as w ON (r.xWettkampf = w.xWettkampf)
			WHERE  
				w.Mehrkampfcode = $comb
			AND	w.xKategorie = $category
			AND	w.xMeeting = ".$_COOKIE['meeting_id']."
			ORDER BY
				r.Gruppe ASC");
           
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		if(mysql_num_rows($res) > 0){
			$g = 0;
			$del = false;
			
			while($row = mysql_Fetch_array($res)){
				if(AA_utils_checkReference("serie", "xRunde", $row[0]) != 0) // do not modify rounds if seeded
				{
					$error = $GLOBALS['strRound'] . $GLOBALS['strErrStillUsed'];
					AA_printErrorMsg($error);
					$_POST['arg'] = "";
					break;
				}
				
				if($g != 0 & $g != $row[1]){ // first group over
					$del = true;
				}
				if($del){
					mysql_query("DELETE FROM runde WHERE xRunde = $row[0]");
					
				}else{
					mysql_query("UPDATE runde as r SET Gruppe = '' WHERE xRunde = $row[0]");
				}
				if ($row[1] != '') {
				    $g = $row[1];
                }
			}
			
		}
	}
	
	mysql_query("UNLOCK TABLES");
}

/* part for team sm **************************************************************************************/
//
// save entered group numbers
if($_POST['arg'] == "teamsm_save"){
	
	foreach($_POST['groups'] as $entry => $group){
		
		mysql_query("UPDATE anmeldung SET Gruppe = $group WHERE xAnmeldung = ".$entry);
		
	}
	
}
//
// assign groups by sort argument and max people per group
elseif($_POST['arg'] == "teamsm_assign"){
	
	$max = $_POST['max'];
	
}

//
// test on rounds if groups will be assigned or removed
//
if($_POST['arg'] == "teamsm_save" || $_POST['arg'] == "teamsm_assign"){
	
	mysql_query("LOCK TABLES runde as r WRITE, wettkampf as w WRITE");
	
	// if there are already rounds with no group, set all of them to the lowest group given
	// (do not update rounds in last combined contest)
	$res = mysql_query("SELECT * FROM
				runde as r
				LEFT JOIN wettkampf as w  ON (r.xWettkampf = w.xWettkampf)
			WHERE  
				r.Gruppe = ''
			AND	w.xWettkampf = $disc");
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		if(mysql_num_rows($res) > 0){ // got rounds without group
			
			$g = "";
			if($_POST['arg'] == "teamsm_save"){ // the lowest group will be in $_POST['groups']
				
				asort($_POST['groups']);
				foreach($_POST['groups'] as $gr){
					$g = $gr;
					if(!empty($g)){
						break;
					}
				}
				
			}else{ // auto assign, lowest group will be 1
				
				$g = 1;
				
			}
			
			mysql_query("	UPDATE runde as r, wettkampf as w SET 
						r.Gruppe = $g
					WHERE
						r.xWettkampf = w.xWettkampf
					AND	r.Gruppe = ''
					AND	w.xWettkampf = $disc");
			if(mysql_errno() > 0){
				AA_printErrorMsg(mysql_errno().": ".mysql_error());
			}
		}
	}
	
	mysql_query("UNLOCK TABLES");
	
}elseif($_POST['arg'] == "teamsm_remove"){
	
	mysql_query("LOCK TABLES runde as r WRITE, wettkampf as w READ, runde WRITE, serie READ");
	
	// if there are already rounds with groups, remove all but the one with the lowest group
	$res = mysql_query("SELECT r.xRunde, r.Gruppe FROM
				runde as r
				LEFT JOIN wettkampf as w  ON (r.xWettkampf = w.xWettkampf)
			WHERE  
				w.xWettkampf = $disc
			ORDER BY
				r.Gruppe ASC");
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		if(mysql_num_rows($res) > 0){
			$g = 0;
			$del = false;
			
			while($row = mysql_Fetch_array($res)){
				if(AA_utils_checkReference("serie", "xRunde", $row[0]) != 0) // do not modify rounds if seeded
				{
					$error = $GLOBALS['strRound'] . $GLOBALS['strErrStillUsed'];
					AA_printErrorMsg($error);
					$_POST['arg'] = "";
					break;
				}
				
				if($g != 0 && $g != $row[1]){ // first group over
					$del = true;
				}
				if($del){
					mysql_query("DELETE FROM runde WHERE xRunde = $row[0]");
					
				}else{
					mysql_query("UPDATE runde as r SET Gruppe = '' WHERE xRunde = $row[0]");
				}
				
				$g = $row[1];
			}
			
		}
	}
	
	mysql_query("UNLOCK TABLES");
}

/*
 * set groups for combined event
 *
 **************************************************************************************/

AA_printCategorySelection('meeting_entries_setgroups.php', $category, 'get');
$page->printPageTitle($strCombinedGroupsAutoAssign.", ".$strEventTypeSingleCombined);

?>
<table><tr>
	<td class='forms'>
		
	</td>
	<td class='forms'>
		<?php	AA_printEventCombinedSelection('meeting_entries_setgroups.php', $category, $category."_".$comb, 'get'); ?>
	</td>
</tr></table>
<br>
<?php
//
// show entries for category and combined type
//
if($category > 0 && $comb > 0){
?>
<table class='dialog'>
	<tr>
		<form action="meeting_entries_setgroups.php?sort=<?php echo $_GET['sort'] ?>&category=<?php echo $category ?>&comb=<?php echo $comb ?>"
			method="POST" name="groups">
		<input type="hidden" name="arg" value="assign">
		<th class='dialog'><?php echo $strAutomatic ?>:</th>
		<td class='forms'> max <input type="text" name='max' value="10" size="2"> <?php echo $strAthletes ?></td>
		<td class='forms'><input type="submit" value="<?php echo $strAssign ?>"></td>
		<td class='dialog'><?php echo $strCombinedGroupsBySort ?></td>
		</form>
	</tr>
	<tr>
		<td class='forms' colspan='2'>
			<input type="button" value="<?php echo $strRemoveGroups ?>"
				onclick="document.groups.arg.value = 'remove'; document.groups.submit()">
		</td>
	</tr>
</table>
<br>
<table class='dialog'>
	<tr>
		<th class='dialog'><a href='meeting_entries_setgroups.php?sort=nbr&category=<?php echo $category ?>&comb=<?php echo $comb ?>'>
			<?php echo $strStartnumber ?><img src="<?php echo $img_nbr?>"></a></th>
		<th class='dialog'><a href='meeting_entries_setgroups.php?sort=name&category=<?php echo $category ?>&comb=<?php echo $comb ?>'>
			<?php echo $strName ?><img src="<?php echo $img_name?>"></a></th>
		<th class='dialog'><a href='meeting_entries_setgroups.php?sort=pole&category=<?php echo $category ?>&comb=<?php echo $comb ?>'>
			<?php echo $strPole ?><img src="<?php echo $img_pole?>"></a></th>
		<th class='dialog'><a href='meeting_entries_setgroups.php?sort=high&category=<?php echo $category ?>&comb=<?php echo $comb ?>'>
			<?php echo $strHigh ?><img src="<?php echo $img_high?>"></a></th>
		<th class='dialog'><a href='meeting_entries_setgroups.php?sort=comb&category=<?php echo $category ?>&comb=<?php echo $comb ?>'>
			<?php echo $strCombinedShort ?><img src="<?php echo $img_comb?>"></a></th>
		<th class='dialog'><?php echo $strCombinedGroup ?></th>
	</tr>
		<form action="meeting_entries_setgroups.php?sort=<?php echo $_GET['sort'] ?>&category=<?php echo $category ?>&comb=<?php echo $comb ?>" method="POST">
		<input type="hidden" name="arg" value="save">
	<?php
	
	// select * athlete for this event   
	                
     $sql = "SELECT
                a.xAnmeldung
                , at.Name
                , at.Vorname
                , a.Gruppe
                , a.Startnummer
                , st.Bestleistung
                , d.Code
                , w.Mehrkampfcode
                , a.BestleistungMK
            FROM
                wettkampf as w
                LEFT JOIN start as st ON (st.xWettkampf = w.xWettkampf)
                LEFT JOIN anmeldung as a ON (a.xAnmeldung = st.xAnmeldung)
                LEFT JOIN athlet as at ON (at.xAthlet = a.xAthlet )
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d  ON (d.xDisziplin = w.xDisziplin)
            WHERE
                w.Mehrkampfcode = $comb
            AND    w.xKategorie = $category
            AND    w.xMeeting = ".$_COOKIE['meeting_id']." 
            ORDER BY
                a.xAnmeldung";           
    
    $res = mysql_query($sql);    

	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		
		$xEntry = 0;
		$current = array();
		$i = 0;
		
		while($row = mysql_fetch_array($res)){
			if($xEntry != $row[0]){
				
				if($xEntry > 0){
					$i++;
				}
				
				$current[$i] = $row;
				$xEntry = $row[0];
				
			}
			
			// save top performace
			if($row[6] == 310){ // high
				$current[$i]['high'] = $row[5];
			}
			
			if($row[6] == 320){ // pole
				$current[$i]['pole'] = $row[5];
			}
			
		}
		
		if(!empty($argument)){
			usort($current, "cmp");
		}
		
		$group = 0;
		$i = 0;
		foreach($current as $curr){
			if($i % $max == 0){ // count for setting group (after sort)
				$group++;
			}
			$i++;
			if($_POST['arg'] == "assign"){ // if auto assign is choosen, set group for entry
				$curr[3] = $group;
				mysql_query("UPDATE anmeldung SET Gruppe = $group WHERE xAnmeldung = ".$curr[0]);
			}elseif($_POST['arg'] == "remove"){
				$curr[3] = "";
				mysql_query("UPDATE anmeldung SET Gruppe = '' WHERE xAnmeldung = ".$curr[0]);
			}
			?>
           <input type="hidden" name="hidden_groups[<?php echo $curr[0] ?>]" value="<?php echo $curr[3] ?>" > 
			<tr>
				<td class='dialog'><?php echo $curr[4] ?></td>
				<td class='dialog'><?php echo $curr[1]." ".$curr[2] ?></td>
				<td class='dialog'><?php echo AA_formatResultMeter($curr['pole']) ?></td>
				<td class='dialog'><?php echo AA_formatResultMeter($curr['high']) ?></td>
				<td class='dialog'><?php echo $curr[8] ?></td>
				<td class='forms'><input type="text" size="1" maxlength="2" name="groups[<?php echo $curr[0] ?>]" value="<?php echo $curr[3] ?>"></td>
			</tr>
			<?php
		}
	}
	
	?>
	<tr>
		<td class='forms' colspan="2">
			<input type="submit" value="<?php echo $strSave ?>">
		</td>
	</tr>
		</form>
</table>

<?php
}      
 
$page->endPage();
?>

