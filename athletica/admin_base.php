<?php

/********************
 *
 *	admin_base.php
 *	---------
 *	login form for getting the base data from the slv web system
 *
 *******************/

$noMeetingCheck = true;
 
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

require('./lib/cl_xml_data.lib.php');
require('./lib/cl_http_data.lib.php');

if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}

/*if(AA_checkMeetingID() == FALSE){		// no meeting selected
	return;		// abort
}*/

//
//	Display enrolement list
//

$page = new GUI_Page('admin_base');
$page->startPage();
$page->printPageTitle($strBaseUpdate);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/base.html', $strHelp, '_blank');
$menu->printMenu();
?>
<p/>

<?php

// handle reset request

if($_GET['arg'] == "reset"){
	?>
	<p><?php echo $strResetDo ?></p>
	<input type="button" value="<?php echo $strYes ?>" onclick="location.href='admin_base.php?arg=reset_do'">
	<input type="button" value="<?php echo $strNo ?>" onclick="location.href='admin.php'">
	<?php
	
	$page->endPage();
	die();
}elseif($_GET['arg'] == "reset_do"){
	
	// reset base_log
	mysql_query("TRUNCATE TABLE base_log");
	if(mysql_errno > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		?>
		<p><?php echo $strResetDone ?></p>
		<?php
	}
} elseif($_GET['arg']=='empty'){
	?>
	<p><?php echo $strEmptyCacheDo ?></p>
	<input type="button" value="<?php echo $strYes ?>" onclick="location.href='admin_base.php?arg=empty_do'">
	<input type="button" value="<?php echo $strNo ?>" onclick="location.href='admin.php'">
	<?php
	
	$page->endPage();
	die();
} elseif($_GET['arg']=='empty_do'){
	$errors = false;
	
	$sql = "SELECT xAthlet 
			  FROM athlet;";
	$query = mysql_query($sql);
	
	while($row = mysql_fetch_assoc($query)){
		$num = AA_checkReference('anmeldung', 'xAthlet', $row['xAthlet']);
		
		if($num>0){
			$errors = true;
			break;
		}
	}
	
	if(!$errors){
		$sql = "SELECT xVerein 
				  FROM verein;";
		$query = mysql_query($sql);
		
		while($row = mysql_fetch_assoc($query)){
			$num = AA_checkReference('team', 'xVerein', $row['xVerein']);
			$num2 = AA_checkReference('teamsm', 'xVerein', $row['xVerein']);
			$num3 = AA_checkReference('staffel', 'xVerein', $row['xVerein']);
			
			if($num>0 || $num2>0 || $num3>0){
				$errors = true;
				break;
			}
		}
	}
	
	if($errors){
		echo $strEmptyCacheReference;
		die();
	} else {
		// reset athletes
		mysql_query("TRUNCATE TABLE athlet");
		if(mysql_errno > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			// reset accounts
			mysql_query("TRUNCATE TABLE verein");
			if(mysql_errno > 0){
				AA_printErrorMsg(mysql_errno().": ".mysql_error());
			}else{
				// reset accounts
				mysql_query("TRUNCATE TABLE staffel");
				if(mysql_errno > 0){
					AA_printErrorMsg(mysql_errno().": ".mysql_error());
				}else{
					// reset accounts
					mysql_query("TRUNCATE TABLE base_account");
					mysql_query("TRUNCATE TABLE base_log");
					mysql_query("TRUNCATE TABLE base_athlete");
					mysql_query("TRUNCATE TABLE base_log");
					mysql_query("TRUNCATE TABLE base_performance");
					mysql_query("TRUNCATE TABLE base_relay");
					mysql_query("TRUNCATE TABLE base_svm");
					if(mysql_errno > 0){
						AA_printErrorMsg(mysql_errno().": ".mysql_error());
					}else{
						?>
						<p><?php echo $strEmptyCacheDone ?></p>
						<?php
					}
				}
			}
		}
	}
} elseif($_GET['arg']=='empty_entries'){
    ?>
    <p><?php echo $strEmptyEntriesDo ?></p>
    <input type="button" value="<?php echo $strYes ?>" onclick="location.href='admin_base.php?arg=empty_entries_do'">
    <input type="button" value="<?php echo $strNo ?>" onclick="location.href='admin.php'">
    <?php
    
    $page->endPage();
    die();
} elseif($_GET['arg']=='empty_entries_do'){
    $errors = false;
    
    $sql = "SELECT xSerie 
              FROM serie
                LEFT JOIN runde USING(xRunde)
                LEFT JOIN wettkampf USING(xWettkampf)
              WHERE xMeeting = ". $_COOKIE['meeting_id'] .";";
    $query = mysql_query($sql);
    
    while($row = mysql_fetch_assoc($query)){
        $errors = true;
        break;
    }
    
    if($errors){
        echo $strEmptyEntriesReference;
        die();
    } else {
        // reset athletes
        mysql_query("DELETE start FROM start LEFT JOIN anmeldung USING(xAnmeldung) WHERE xMeeting = ". $_COOKIE['meeting_id'] ."");
        if(mysql_errno > 0){
            AA_printErrorMsg(mysql_errno().": ".mysql_error());
        }else{
            // reset entries
            mysql_query("DELETE FROM anmeldung WHERE xMeeting = ". $_COOKIE['meeting_id'] ."");
                if(mysql_errno > 0){
                    AA_printErrorMsg(mysql_errno().": ".mysql_error());
                }else{
                    ?>
                    <p><?php echo $strEmptyEntriesDone ?></p>
                    <?php
                    die();
                }
            }
    }
}


$http = new HTTP_data();
$webserverDomain = $cfgSLVhost; // domain of swiss-athletics webserver

// handle arguments
$login = false;
$slvsid = 0;
$basefiles = array();
$filetype = "";
$newglc = "";
// login attempt on the slv server
if($_POST['arg'] == "login"){
	
	// sending login information and global last change date of the base_data
	$result = mysql_query("SELECT global_last_change FROM base_log where type like 'base_%' ORDER BY id_log DESC LIMIT 1");
	if(mysql_errno > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		// the following information is used to determinate wich base file we need to download
		if(mysql_num_rows($result) == 0){
			$glc = "";
			$type = "complete"; // for a complete base data download
		}else{
			$row = mysql_fetch_array($result);
			$glc = $row[0];
			$type = "update"; // if there is already a log entry, a complete download was made once
		}
		
		mysql_free_result($result);
		
		//$result = $http->send_get('slv.exigo.ch', '/downloads/verbandstagung_201104.ppt' , 'file', 'test.ppt', true);
		$post = "clubnr=".urlencode($_POST['clubnr'])."&pass=".urlencode($_POST['pass'])
			."&glc=".urlencode($glc)."&type=".urlencode($type);
		
		$result = $http->send_post($webserverDomain, '/meetings/athletica/login.php', $post, 'ini');		
		//TEST mit XML aus Ordner basdata_test
		//$result = $http->send_post($webserverDomain, '/meetings/athletica/login_test.php', $post, 'ini');
		
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
				$slvsid = $result['sid']; // remember session id from slv server
				$basefiles = explode(":",$result['files']); // get files to download
				$filetype = $result['filetype']; // return complete or update, 
								//maybe the glc-date is too old, so the login script returned a complete set
				$newglc = substr($result["newglc"],0,4)."-".substr($result["newglc"],4,2)."-".substr($result["newglc"],6,2);
				break;
				
				case "denied":
				$login = false;
				echo "<p>$strLoginFalse</p>";
				break;
			}
		}
	}
}

// start download of files
if($login){ // show download process
	// start download
	if(count($basefiles) > 0 && !empty($basefiles[0])){
		?>

		<p><?php echo $strBaseDownload ?></p>
		<table class="dialog">
		<tr height="1">
			<td></td>
			<td height="1">
				<img src="img/spacer.jpg" width="150" height="1" >
			</td>
			<td></td>
		</tr>
		<tr>
			<td>0%</td>
			<th>
				<img src="img/progress.jpg" width="1" height="15" id="progress" >
			</th>
			<td>100%</td>
		</tr>
		<tr>
			<td></td>
			<td height="1">
				<img src="img/spacer.jpg" width="150" height="1" >
			</td>
			<td></td>
		</tr>
		</table>

		<?php
		/*
		 * update process ----------------------------------------------------------------------------------
		*/
		set_time_limit(3600); // the script will break if this is not set
		
		$i = 0;
		$xml = new XML_data();
		// show count of files
		echo "<p>$strCountFiles: ".count($basefiles)."</p>\n";
		foreach($basefiles as $file){ // get files
			
			$result = $http->send_get($webserverDomain, $file , 'file', "update$i.gz", true); // returns local filename
			
			if(!$result){ // error in http class
				AA_printErrorMsg($strErrDownload);
				break;
			}else{
				
				// first file, type = complete --> truncate base tables
				if($i == 0 && $filetype == "complete"){
					
					// initialize global for storing clubs
					$GLOBALS['clubstore'] = array();
					
					$temppath = addslashes(dirname($_SERVER['PATH_TRANSLATED'])."\\tmp\\");
					
					if(mysql_errno() > 0){
						AA_printErrorMsg(mysql_errno().": ".mysql_error());
						break; // important
					}else{
						mysql_query("TRUNCATE TABLE base_athlete");
					}
					
					if(mysql_errno() > 0){
						AA_printErrorMsg(mysql_errno().": ".mysql_error());
						break; // important
					}else{
						mysql_query("TRUNCATE TABLE base_account");
					}
					
					if(mysql_errno() > 0){
						AA_printErrorMsg(mysql_errno().": ".mysql_error());
						break; // important
					}else{
						mysql_query("TRUNCATE TABLE base_performance");
					}
					
					if(mysql_errno() > 0){
						AA_printErrorMsg(mysql_errno().": ".mysql_error());
						break; // important
					}else{
						mysql_query("TRUNCATE TABLE base_relay");
					}
					
					if(mysql_errno() > 0){
						AA_printErrorMsg(mysql_errno().": ".mysql_error());
						break; // important
					}else{
						mysql_query("TRUNCATE TABLE base_svm");
					}
					
					//mysql_query("DELETE FROM verein WHERE xCode != ''");
					
					//mysql_query("UNLOCK TABLES");
				}
				
				//echo "<p>".date("H:i:s")."</p>";
				// start parsing xml file
				echo "<p>$strFile ".($i+1)." $strBaseProcessing ... <b>$strPleaseWait</b> ";
				ob_flush();
				flush();
				
				$xml->load_xml($result, "base", $_POST['mode']);
				echo " OK!</p>\n";
				//echo "<p>".date("H:i:s")."</p>";
			}
			$i++;
		}
		
		
		//
		// if a complete update was made, check for missing (deleted) clubs
		//
		if($filetype == "complete"){
			
			$res_club = mysql_query("SELECT * FROM verein WHERE TRIM(xCode) != ''"); // control only those with an account code
			
			while($row = mysql_fetch_assoc($res_club)){
				
				if(!in_array(trim($row['xCode']), $GLOBALS['clubstore'])){ // if not in clubstore
											// set flag 'deleted'
					mysql_query("UPDATE verein SET Geloescht = 1 
							WHERE xVerein = ".$row['xVerein']);
					
				}
				
			}
			
			mysql_free_result($res_club);
			
		}
		
		?>
<script type="text/javascript">
document.getElementById("progress").width="150";
</script>
		<?php
		
		// if no error: insert global last change date into base_log
		$time = date('Y-m-d h:i:s');
		mysql_query("INSERT INTO base_log (type, update_time, global_last_change) VALUES ('base_$filetype','$time','$newglc')");     
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
                                                                     		}
		
	}else{ // base data is up to date
		echo "<p>$strBaseUptodate</p>\n";
	}
	
	//
	// output form for next step: getting registrations
	//
	?>

<table class='dialog'>
<tr><td>
<?php echo $strBaseUpdated ?>

<?php
if(isset($_SESSION['meeting_infos']) && count($_SESSION['meeting_infos'])>0){
	?>
	<br/><br/>
	<?php echo $strBaseUpdatedSync; 
	
}
?>
</td></tr>
<?php
if(isset($_SESSION['meeting_infos']) && count($_SESSION['meeting_infos'])>0){
	?>
	<form name="export" action="admin_registration.php" target="_self" method="post">
	<input type="hidden" name="slvsid" value="<?php echo $slvsid; ?>">
	<input type="hidden" name="arg" value="list">
	<tr>
		<td><input type="submit" value="<?php echo $strNextSync ?>" class="syncbutton"></td>
	</tr>
	</form>
	<?php
}
?>
</table>

	<?php
	
}else{ // show login form

?>
<form action='admin_base.php' name='base' method='post' target='_self'>
 <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
            <td style="vertical-align: top;" width="260">
                <table class="dialog" width="260">
                    <tbody><tr>

                        <th><?php echo $strConfiguration; ?></th>
                    </tr>
                    <tr>
                        <td>
                          <p><?php echo $strEffortsUpdateInfo4; ?></p>
                          <p>
                            <label>
                              <input type="radio" name="mode" value="overwrite" id="mode_0" checked="checked" />
                              <?php echo $strOverwrite;?></label>
                            <br />
                            <label>
                              <input type="radio" name="mode" value="skip" id="mode_1" />
                              <?php echo$strLeaveBehind ;?></label>
                            <br />
                          </p></td>
                    </tr>
                </tbody>
        </table>
 <br />
<table class='dialog'>
<tr>
	<th><?php echo $strLoginSlv; ?></th>
</tr>
<tr>
	<td>
   
    
    
		<table class='admin'>
		
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
} // end if login

$page->endPage();
?>
