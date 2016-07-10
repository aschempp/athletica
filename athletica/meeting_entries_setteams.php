<?php

/**********
 *
 *	meeting_entries_startnumbers.php
 *	--------------------------------
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

$page = new GUI_Page('meeting_entries_setteams');
$page->startPage();
$page->printPageTitle($strAssignTeams);



if($_GET['arg'] == 'assign')
{
?>

<table class='dialog'>
	<?php
	
	
	$error = "";
	$pxTeam = 0;
	
	foreach($_POST as $pkey => $prow){
		
		if($prow != 1){
			continue;
		}
		
		list($xTeam, $xKategorie) = explode("_", $pkey);
		
		$res_team = mysql_query("SELECT team.xTeam, team.Name, team.xVerein
					, kategorie.xKategorie, kategorie.Kurzname
				FROM 
					team
					LEFT JOIN kategorie USING(xKategorie)
				WHERE	xTeam = $xTeam
				AND xMeeting = ".$_COOKIE['meeting_id']);
				if(mysql_errno() > 0){
					echo mysql_error();
				}
		$row_team = mysql_fetch_assoc($res_team);
		
		if($pxTeam != $xTeam){
	?>
<tr>
	<th class='dialog'><?php echo $row_team['Name'].", ".$row_team['Kurzname']; ?></th>
	
</tr>
	<?php
		$pxTeam = $xTeam;
		}
	?>
<tr>
	<td class='dialog'>
			<?php
			
			$res_at = mysql_query("SELECT * FROM
							athlet as at
							LEFT JOIN anmeldung as a USING(xAthlet)
						WHERE
							a.xMeeting = ".$_COOKIE['meeting_id']."
						AND	a.xKategorie = ".$xKategorie."
						AND	at.xVerein = ".$row_team['xVerein']."");
			if(mysql_errno() > 0){
				AA_printErrorMsg(mysql_errno().": ".mysql_error());
			}else{
				
				while($row_at = mysql_fetch_assoc($res_at)){
					echo $row_at['Name']." ".$row_at['Vorname']."<br>";
					mysql_query("update anmeldung set xTeam = $xTeam where xAnmeldung = ".$row_at['xAnmeldung']);
				}
				
			}
			
			/*$sql = "UPDATE
						athlet as at
						LEFT JOIN anmeldung as a USING(xAthlet)
					SET	a.xTeam = ".$xTeam."
					WHERE
						a.xMeeting = ".$_COOKIE['meeting_id']."
					AND	a.xKategorie = ".$xKategorie."
					AND	at.xVerein = ".$row_team['xVerein']."";
			echo $sql;
			mysql_query($sql);
			if(mysql_errno() > 0){
				$error = "Error: ".mysql_error();
			}*/
			?>
	</td>
</tr>
	<?php
	
	
	}
	
	if(!empty($error)){
		?>
<tr>
	<th class='dialog'><?php echo $error ?></th>
</tr>
		<?php
	}else{
		?>
<tr>
	<th class='dialog'><?php echo $strFinished ?>!</th>
</tr>
		<?php
	}
	
	?>
</table>

<?php

}else{
	?>
<form action="meeting_entries_setteams.php?arg=assign" name="form10" method="post">
<table class='dialog'>
<tr>
	<th class='dialog'></th>
	<?php
	
	// generate checkboxes with * category
	$res_cat = mysql_query("SELECT * from kategorie WHERE Code != '' order by anzeige");
	while($row_cat = mysql_fetch_assoc($res_cat)){
		?>
		<th class='dialog'><?php echo $row_cat['Kurzname']; ?></th>
		<?php
	}
	
	?>
</tr>
	<?php
	
	$res_team = mysql_query("SELECT team.xTeam, team.Name, team.xVerein
					, kategorie.xKategorie, kategorie.Kurzname
				FROM 
					team
					LEFT JOIN kategorie USING(xKategorie)
				WHERE xMeeting = ".$_COOKIE['meeting_id']);
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		
		while($row_team = mysql_fetch_assoc($res_team)){
	?>
	<tr>
	<th class='dialog'><?php echo $row_team['Name'].", ".$row_team['Kurzname']; ?></th>
			<?php
			
			// generate checkboxes with * category
			$res_cat = mysql_query("SELECT * from kategorie WHERE Code != '' order by Anzeige");
			while($row_cat = mysql_fetch_assoc($res_cat)){
				$sel = "";
				if($row_cat['xKategorie'] == $row_team['xKategorie']){
					$sel = "checked";
				}
				?>
				<td><input type="checkbox" name="<?php echo $row_team['xTeam']."_".$row_cat['xKategorie'] ?>" value="1" <?php echo $sel ?>></td>
				<?php
			}
			
			?>
	</tr>
	<?php
		}
	}
	?>

<tr>
	<td class='form' colspan='10'><input type="submit" name="asdf" value="<?php echo $strAssignTeams ?>"></td>
</tr>
</table>
</form>
	<?php
}

$page->endPage();
?>

