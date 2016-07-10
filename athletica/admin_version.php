<?php

/********************
 *
 *	admin.php
 *	---------
 *	
 *******************/

$noMeetingCheck = true;
 
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
include('./lib/cl_gui_select.lib.php');
include('./lib/cl_protect.lib.php');
require('./lib/cl_http_data.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}

if($_POST['arg'] == "set_password"){
	
	$p = new Protect();
	
	if(!empty($_POST['password'])){
		$p->secureMeeting($_COOKIE['meeting_id'], $_POST['password']);
	}
	
}

if($_POST['arg'] == "del_password"){
	
	$p = new Protect();
	
	$p->unsecureMeeting($_COOKIE['meeting_id']);
	
}

//
//	Display administration
//

$page = new GUI_Page('admin');
$page->startPage();
$page->printPageTitle($strAdministration.' - '.$strVersionCheck);

$menu = new GUI_Menulist();
$menu->addButton('admin.php', $strBack, '_self');
$menu->printMenu();
?>
<p/>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="475" style="vertical-align: top;">
			<?php
			$newer = false;
			
			$http = new HTTP_data();
			$webserverDomain = $cfgSLVhost; // domain of swiss-athletics webserver
			
			$result = $http->send_post($webserverDomain, '/meetings/athletica/version.php', '', 'ini');
			$version = $result['version'];
			$datum = $result['datum'];
			
			$act_version = $cfgApplicationVersion;
			
			$version_short = substr($version, 0, 3);
			$act_version_short = substr($cfgApplicationVersion, 0, 3);
			$version_sub = (strlen($version)>=5) ? substr($version, 4) : 0;
			$act_version_sub = (strlen($act_version)>=5) ? substr($act_version, 4) : 0;
			
			if($version_short>$act_version_short){
				$newer = true;
			} elseif($version_short==$act_version_short){
				if($version_sub>$act_version_sub){
					$newer = true;
				}
			}
			
			if($newer){
				$athletica_de = 'http://www.swiss-athletics.ch/de/athletica/athletica.html';
				$athletica_fr = 'http://www.swiss-athletics.ch/fr/athletica/athletica.html';
				$link = ($lang=='fr') ? $athletica_fr : $athletica_de;
				?>			
				<table class='dialog' width="475" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th width="26" class='updatebox'><img src="img/update.gif" width="22" height="22"/></th>
						<th class='updatebox'><?php echo $strOldVersion; ?></th>
					</tr>
					<tr>
						<td colspan="2">
							<table class='admin'>
								<tr class='odd'>
									<td>
										<?php echo $strNewVersionDownload; ?><br/><br/>
										
										<?php echo $strYourVersion; ?>: <?php echo $act_version; ?><br/>
										<b><?php echo $strNewestVersion; ?>: <?php echo $version; ?> (<?php echo $datum; ?>)</b><br/><br/>
									</td>
								</tr>
								<tr class='even'>
									<td><input type="button" value="<?php echo $strUpdateDownload; ?> &raquo;" class="uploadbutton" onclick="window.open('<?php echo $link; ?>');"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table><br/>
				<?php
			} else {
				?>
				<table class='dialog' width="475" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th width="26" class='updateboxok'><img src="img/update_ok.gif" width="22" height="22"/></th>
						<th class='updateboxok'><?php echo $strVersionOK; ?></th>
					</tr>
					<tr>
						<td colspan="2">
							<table class='admin'>
								<tr class='odd'>
									<td>
										<?php echo $strVersionOK; ?><br/><br/>
										
										<?php echo $strYourVersion; ?>: <?php echo $act_version; ?><br/>
										<b><?php echo $strNewestVersion; ?>: <?php echo $version; ?> (<?php echo $datum; ?>)</b><br/><br/>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table><br/>
				<?php
			}
			?>
		</td>
	</tr>
</table>

<?php
$page->endPage();
?>
