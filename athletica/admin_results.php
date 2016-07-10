<?php

/********************
 *
 *	admin_base.php
 *	---------
 *	login form for getting the base data from the slv web system
 *
 *******************/

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_xml_pageconstruct.lib.php');
require('./lib/rankinglist_team.lib.php');

require('./lib/common.lib.php');

require('./lib/cl_xml_data.lib.php');
require('./lib/cl_ftp_data.lib.php');
require('./lib/cl_http_data.lib.php');

if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}

if(AA_checkMeetingID() == FALSE){		// no meeting selected
	return;		// abort
}

//
//	Display enrolement list
//

$page = new GUI_Page('admin_base');
$page->startPage();
$page->printPageTitle($strResultUpload);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/results.html', $strHelp, '_blank');
$menu->printMenu();


$login = false;

if($_POST['arg'] == "login"){
	
	$http = new HTTP_data();
	$post = "clubnr=".urlencode($_POST['clubnr'])."&pass=".urlencode($_POST['pass']);
	$result = $http->send_post($cfgSLVhost, '/meetings/athletica/login.php', $post, 'ini');
	if(!$result){
		AA_printErrorMsg($strErrLogin);
	}else{
		switch($result['login']){
			case "error":
			AA_printErrorMsg($result['error']);
			break;
			
			case "ok":
			$login = true;
			echo "<p>$strLoginTrue</p>";
			
			break;
			
			case "denied":
			$login = false;
			echo "<p>$strLoginFalse</p>";
			break;
		}
	}
}

if($login){
	
	set_time_limit(300);
	
	$xml = new XML_data();
	$ftp = new FTP_data();
	
	//
	// set file names and generade result xml
	//
	$res = mysql_query("select xControl from meeting where xMeeting = ".$_COOKIE['meeting_id']);
	$row = mysql_fetch_Array($res);
	$eventnr = $row[0];
	$local = dirname($_SERVER['SCRIPT_FILENAME'])."/tmp/results.xml.gz";
	$remote = date("Ymd")."_".$eventnr.".gz";
	
	$nbr_effort = $xml->gen_result_xml($local);          
	
	// upload result file
	if($nbr_effort>0){ //upload only if file contains at least one results
		$ftp->open_connection($cfgSLVhost, $cfgSLVuser, $cfgSLVpass);
		$success = $ftp->put_file($local, $remote);
		$ftp->close_connection();
	} else {
		$success=true;
	} 
     
      
	if($success){
		// output message and set round status to results_sent
		
		foreach($GLOBALS['rounds'] as $xRunde){
			mysql_query("UPDATE runde SET StatusUpload = 1 WHERE xRunde = $xRunde");
			if(mysql_errno() > 0 ){
				AA_printErrorMsg(mysql_errno().": ".mysql_error());
			}
		}
		if($nbr_effort>0){
			echo "<p><b>$strResultsUploaded</b></p>";
			echo $strNumberEfforts .": " .$nbr_effort;
		} else {
			echo "<p><b>$strResultsUploadedNoResults</b></p>";
		}
		
		
		
	}else{
		
		echo "<p>$strErrResultUpload</p>";
		
	}
}else{
	$cControl = AA_checkControl();
	if($cControl == 0){
		echo "<p>$strErrNoControl</p>";
		return;
	}elseif($cControl == 2){
		?>
		<p><?php echo $strErrNoControl3; ?></p><br/>
		<!--<img src="img/nosync_<?php echo $_COOKIE['language']; ?>.gif" alt="" style="border: solid 1px #000000;"/>  -->
		<?php
		return;
	}
    
    // check if there are results in progress 
    $query = "SELECT 
                    xRunde                         
              FROM
                    runde as ru 
                    LEFT JOIN wettkampf as w ON (w.xWettkampf = ru.xWettkampf)                             
              WHERE ru.Status = ".$cfgRoundStatus['results_in_progress']."
                    AND ru.StatusUpload = 0 AND w.xMeeting = " . $_COOKIE['meeting_id'];     
               
              $res_results = mysql_query($query);
              if(mysql_errno() > 0){
                    echo mysql_Error();
              }else{
                    if(mysql_num_rows($res_results) > 0){
                        ?>
                        <p class="st_res_work" ><?php echo $strErrResultsInProgress; ?></p><br/> 
                       <?php  
                    return; 
                    } 
              }  
	
?>
<table class='dialog'>
<tr>
	<th><?php echo $strLoginSlv; ?></th>
</tr>
<tr>
	<td>
		<table class='admin'>
		<form action='admin_results.php' name='base' method='post' target='_self' >
		<input type="hidden" name="arg" value="login">
		<tr>
			<td>
				<?php echo $strClubNr ?>
			</td>
			<td>
				<input type="text" name="clubnr" value="">
			</td>
		</tr>
		<tr>
			<td>
				<?php echo $strPassword ?>
			</td>
			<td>
				<input type="password" name="pass" value="">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" name="submit" value="<?php echo $strLogin ?>">
			</td>
		</tr>
		</form>	
		</table>
	</td>
</tr>
</table>
<?php

}
$page->endPage();
?>
