<?php

/********************
 *
 *	admin.php
 *	---------
 *	
 *******************/

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
include('./lib/cl_gui_select.lib.php');
include('./lib/cl_gui_dropdown.lib.php');

require('./lib/timing.lib.php');

if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}
if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}


//
// save changes for configuration
//
if($_POST['arg'] == 'change'){
	
	if($_POST['connection'] == 'local'){
		if(empty($_POST['path'])){
			AA_printErrorMsg($strErrEmptyFields);
		}else{
			$conf = AA_timing_saveConfiguration();
		}
	}else{
		if(empty($_POST['host']) || empty($_POST['user']) || empty($_POST['pass']) || empty($_POST['ftppath'])){
			AA_printErrorMsg($strErrEmptyFields);
		}else{
			$conf = AA_timing_saveConfiguration();
		}
	}
}
//
// save system if changed
//
elseif($_POST['arg'] == 'change_system'){
	
	AA_timing_setTiming($_POST['timing_system']);
	
}
elseif($_POST['arg'] == 'change_autorank'){

    AA_timing_setAutoRank($_POST['autorank']);
} 

$system = AA_timing_getTiming();
if($system != "no"){
	$conf = AA_timing_getConfiguration();                                  
}

/*if($omega->connection == "ftp"){
	$ftp = "checked";
}else{
	$local = "checked";
}*/
$optic = "checked";
$optic2 = "";
$local = "checked";

//
//	Display options for time measurement
//

$page = new GUI_Page('meeting_timing');
$page->startPage();
$page->printPageTitle($strTiming);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/meeting/timing.html', $strHelp, '_blank');
$menu->addButton($cfgURLDocumentation . 'help/meeting/athletica_netzwerk.pdf', $strOmegaManual, '_blank');
$menu->printMenu();

if(!empty($GLOBALS['ERROR'])){
	
	AA_printErrorMsg($GLOBALS['ERROR']);
	
}

?>
<p/>
<table class='dialog'>
<form name="system" action="meeting_timing.php" method="post">
<input type="hidden" name="arg" value="change_system">
<tr>
	<th class='dialog'><?php echo $strTiming ?></th>
	<?php $dd = new GUI_ConfigDropDown('timing_system', 'cfgTimingType', $system, "submit()"); ?>
</tr>
</form>
</table>
      <?php 
      $auto=AA_timing_getAutoRank();
      
      if ($auto=='y'){
         $auto='yes';
         $checked='checked="checked"';
      }
      else {
          $auto='no'; 
          $checked='';  
      }
      
      if ($system != 'no'){   
       ?>
<br>

<table class='dialog'>
<form name="auto" action="meeting_timing.php" method="post">
<input type="hidden" name="arg" value="change_autorank">
<tr>
    <th class='dialog'><?php echo $strAutoRank ?></th>
    <td><input type='checkbox' name='autorank' value='<?php echo $auto; ?>' <?php echo $checked; ?> onClick='document.auto.submit()'>
    </input>  
    </td>
</tr>
</form>
</table>

 <br>  
<?php
      }
      
// display configuration for choosen system
if($system == "omega"){
	?>
<form name="omega" action="meeting_timing.php" method="post">
<input type="hidden" name="arg" value="change">
<input type="hidden" name="connection" value="local">
<table class='dialog'>

<tr>
	<th class='dialog'><?php echo $strTimingOmega; ?></th>
</tr>
<tr>
	<td>
		<table class='admin'>
			<tr>
				<td><?php echo $strOmegaPath ?></td>
				<td>
					<input type="text" name="path" value="<?php echo $conf->path ?>" size="30">
				</td>
			</tr>
			<tr class='odd'>
				<td colspan="2">
					- C:\Programme\athletica\www\tmp<br/>
					- \\server\athletica\www\tmp
				</td>
			</tr>
			<tr>
				<td><?php echo $strOmegaSponsor ?></td>
				<td>
					<input type="text" name="sponsor" value="<?php echo $conf->sponsor ?>" size="30">
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr class='even'>
	<td>
		<input type="submit" name="save" value="<?php echo $strSave ?>">
	</td>
</tr>

</table>
</form>

	<?php
}elseif($system == "alge"){
	$optic = ($conf->typ=='OPTIc') ? "checked" : "";
	$optic2 = ($conf->typ=='OPTIc2') ? "checked" : "";
	$optic = ($optic=='' && $optic2=='') ? "checked" : $optic;
	?>

<form name="alge" action="meeting_timing.php" method="post">
<input type="hidden" name="arg" value="change">
<input type="hidden" name="connection" value="local">
<table class='dialog'>

<tr>
	<th class='dialog'><?php echo $strTimingAlge; ?></th>
</tr>
<tr>
	<td>
		<table class='admin'>
			<tr>
				<td colspan="2" height="5"></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="radio" name="typ" id="optic" value="OPTIc" <?php echo $optic ?>><label for="optic">OPTIc</label>
					<input type="radio" name="typ" id="optic2" value="OPTIc2" <?php echo $optic2 ?>><label for="optic2">OPTIc2</label>
				</td>
			</tr>
			<tr>
				<td colspan="2" height="5"></td>
			</tr>
			<!--<tr>
				<th colspan="2" class="sub">
					<input type="radio" name="connection" value="local" <?php echo $local ?>><?php echo $strOmegaLocal ?>
				</th>
				<th colspan="2" class="sub">
					<input type="radio" name="connection" value="ftp" <?php echo $ftp ?>><?php echo $strOmegaFtp ?>
				</th>
			</tr>-->
			<tr>
				<td><?php echo $strOmegaPath ?></td>
				<td>
					<input type="text" name="path" value="<?php echo $conf->path ?>" size="30">
				</td>
				<!--<td><?php echo $strOmegaHost ?></td>
				<td>
					<input type="text" name="host" value="<?php echo $omega->host ?>" size="15">
				</td>-->
			</tr>
			<tr class='odd'>
				<td colspan="2">
					- C:\Programme\athletica\www\tmp<br/>
					- \\server\athletica\www\tmp
				</td>
				<!--<td><?php echo $strOmegaUser ?></td>
				<td>
					<input type="text" name="user" value="<?php echo $omega->user ?>" size="10">
				</td>-->
			</tr>
			<!--<tr class='odd'>
				<td colspan="2">&nbsp;</td>
				<td><?php echo $strOmegaPass ?></td>
				<td>
					<input type="password" name="pass" value="<?php echo $omega->pass ?>" size="10">
				</td>
			</tr>
			<tr class='odd'>
				<td colspan="2">&nbsp;</td>
				<td><?php echo $strOmegaFtppath ?></td>
				<td>
					<input type="text" name="ftppath" value="<?php echo $omega->ftppath ?>" size="20">
				</td>
			</tr>-->
		</table>
	</td>
</tr>
<tr class='even'>
	<td>
		<input type="submit" name="save" value="<?php echo $strSave ?>">
	</td>
</tr>

</table>
</form>

	<?php
}
$page->endPage();
?>
