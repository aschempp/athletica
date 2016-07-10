<?php

/********************
 *
 *	admin_service.php
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

$res_srv_lg = '';
if(isset($_POST['arg']) && $_POST['arg']=='login_srv'){
	if(md5($_POST['username'])==$cfgSrvHashU && md5($_POST['password'])==$cfgSrvHashP){
		$_SESSION['login_srv'] = array(
			'username' => $cfgSrvHashU,
			'password' => $cfgSrvHashP,
		);
		
		header('Location: admin_service.php');
		exit();
	} else {
		$res_srv_lg = 'error';
	}
}

if(isset($_SESSION['login_srv']) && $_SESSION['login_srv']['username']==$cfgSrvHashU && $_SESSION['login_srv']['password']==$cfgSrvHashP){
	$res_srv_lg = 'ok';
}

if(isset($_GET['a']) && $_GET['a']=='logout'){
	if(isset($_SESSION['login_srv'])){
		unset($_SESSION['login_srv']);
	}
	
	header('Location: admin_service.php');
	exit();
}

if(isset($_GET['a']) && $_GET['a']=='pws'){
	$sql = "UPDATE meeting 
			   SET Passwort = '';";
	$query = mysql_query($sql);
	
	$_SESSION['temp'] = array(
		'action' => 'pws',
		'res' => $query,
	);
	
	header('Location: admin_service.php');
	exit();
}

if(isset($_GET['a']) && $_GET['a']=='statusupload'){
	$statusupload = (isset($_GET['v']) && intval($_GET['v'])==1) ? 1 : 0;
	
	$sql = "UPDATE runde 
			   SET StatusUpload = ".$statusupload
               . " ,  StatusUploadUKC = ".$statusupload;
	$query = mysql_query($sql);
	
	$_SESSION['temp'] = array(
		'action' => 'statusupload',
		'res' => $query,
	);
	
	header('Location: admin_service.php');
	exit();
}

if(isset($_GET['a']) && $_GET['a']=='disc'){
	if(isset($_GET['m'])){
		$_SESSION['temp'] = array(
			'action' => 'disc',
			'xMeeting' => intval($_GET['m']),
			'res' => 'select',
		);
	}
}

if(isset($_POST['arg']) && $_POST['arg']=='disc'){
	$xMeeting = $_POST['xMeeting'];
	
	$queries = '';
	
	foreach($_POST['delete'] as $xKategorie => $disziplinen){
		foreach($disziplinen as $xDisziplin){
			$sql = "SELECT xWettkampf 
					  FROM wettkampf 
					 WHERE xMeeting = ".$xMeeting." 
					   AND xKategorie = ".$xKategorie." 
					   AND xDisziplin = ".$xDisziplin.";";
			$query = mysql_query($sql);
			
			while($wettkampf = mysql_fetch_assoc($query)){
				$xWettkampf = $wettkampf['xWettkampf'];
				
				$sql2 = "SELECT xRunde 
						   FROM runde 
						  WHERE xWettkampf = ".$xWettkampf.";";
				$query2 = mysql_query($sql2);
				
				while($runde = mysql_fetch_assoc($query2)){
					$xRunde = $runde['xRunde'];
					
					$sql3 = "SELECT xSerie 
							   FROM serie 
							  WHERE xRunde = ".$xRunde.";";
					$query3 = mysql_query($sql3);
					
					while($serie = mysql_fetch_assoc($query3)){
						$xSerie = $serie['xSerie'];
						
						$sql4 = "SELECT xSerienstart 
								   FROM serienstart 
								  WHERE xSerie = ".$xSerie.";";
						$query4 = mysql_query($sql4);
						
						while($serienstart = mysql_fetch_assoc($query4)){
							$xSerienstart = $serienstart['xSerienstart'];
							
							$sql5 = "DELETE FROM resultat 
									  WHERE xSerienstart = ".$xSerienstart.";";
							$query5 = mysql_query($sql5);
							
							$queries .= (($queries!='') ? '<br/>' : '').$sql5;
						}
						
						$sql6 = "SELECT xStart 
								   FROM start 
								  WHERE xWettkampf = ".$xWettkampf.";";
						$query6 = mysql_query($sql6);
						
						$in_xStart = '';
						while($start = mysql_fetch_assoc($query6)){
							$in_start .= (($in_start!='') ? ',' : '').$start['xStart'];
						}
						
						$in = ($in_start!='') ? "OR xStart IN(".$in_start.")" : "" ;
						$sql7 = "DELETE FROM serienstart 
									   WHERE xSerie = ".$xSerie." 
										  ".$in.";";
						$query7 = mysql_query($sql7);
						
						$queries .= (($queries!='') ? '<br/>' : '').$sql7;
					}
					
					$sql8 = "DELETE FROM serie 
							   WHERE xRunde = ".$xRunde.";";
					$query8 = mysql_query($sql8);
					
					$queries .= (($queries!='') ? '<br/>' : '').$sql8;
				}
				
				$sql9 = "DELETE FROM runde 
							   WHERE xWettkampf = ".$xWettkampf.";";
				$query9 = mysql_query($sql9);
				
				$queries .= (($queries!='') ? '<br/>' : '').$sql9;
				
				$sql10 = "DELETE FROM start 
								WHERE xWettkampf = ".$xWettkampf.";";
				$query10 = mysql_query($sql10);
				
				$queries .= (($queries!='') ? '<br/>' : '').$sql10;
				
				$sql11 = "DELETE FROM wettkampf 
								WHERE xKategorie = ".$xKategorie." 
								  AND xDisziplin = ".$xDisziplin." 
								  AND xMeeting = ".$xMeeting.";";
				$query11 = mysql_query($sql11);
				
				$queries .= (($queries!='') ? '<br/>' : '').$sql11;
			}
		}
	}
	
	$_SESSION['temp'] = array(
		'action' => 'disc',
		'res' => 'ok',
		'queries' => $queries,
	);
	
	header('Location: admin_service.php');
	exit();
}

if(isset($_GET['a']) && $_GET['a']=='xControl'){
	$xMeeting = (isset($_GET['m'])) ? intval($_GET['m']) : 0;
	$xControl = (isset($_GET['c'])) ? intval($_GET['c']) : 0;
	
	$sql = "UPDATE meeting 
			   SET xControl = ".$xControl." 
			 WHERE xMeeting = ".$xMeeting.";";
	$query = mysql_query($sql);
	
	header('Location: admin_service.php');
	exit();
}

//
//	Display administration
//

$page = new GUI_Page('admin_service');
$page->startPage();
$page->printPageTitle($strServiceMenu);

$menu = new GUI_Menulist();
$menu->printMenu();
?>
<p/>

<?php
if($res_srv_lg=='ok'){
	if(isset($_SESSION['temp']) && $_SESSION['temp']['action']=='disc' && $_SESSION['temp']['res']=='select'){
		$menu = new GUI_Menulist();
		$menu->addButton('admin_service.php', $strBack);
		$menu->printMenu();
		?>
		<br/>
		<form name="frmDisc" action="admin_service.php" method="post">
			<input type="hidden" name="arg" value="disc"/>
			<input type="hidden" name="xMeeting" value="<?php echo $_SESSION['temp']['xMeeting']; ?>"/>
			
			<table width="430" border="0" cellpadding="0" cellspacing="0" class="dialog">
				<colgroup>
					<col width="100"/>
					<col width="330"/>
					<col width="80"/>
				</colgroup>
				<tr>
					<th class="dialog"><?php echo $strCategory; ?></th>
					<th class="dialog"><?php echo $strDiscipline; ?></th>
					<th class="dialog"><?php echo $strDelete; ?></th>
				</tr>
				<?php
				$sql = "SELECT DISTINCT(w.xKategorie), 
							   Name 
						  FROM wettkampf AS w 
					 LEFT JOIN kategorie USING(xKategorie) 
						 WHERE xMeeting = ".$_SESSION['temp']['xMeeting']." 
					  ORDER BY Anzeige ASC;";
				$query = mysql_query($sql);
				
				$overall_cls = 'even';
				while($kategorie = mysql_fetch_assoc($query)){
					?>
					<tr>
						<td style="vertical-align: top;"><b><?php echo stripslashes($kategorie['Name']); ?></b></td>
						<td colspan="2" style="vertical-align: top;">
							
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<colgroup>
									<col width="330"/>
									<col width="80"/>
								</colgroup>
								<?php
								$sql2 = "SELECT DISTINCT(w.xDisziplin), 
												Name, 
												Info, 
												xKategorie_svm 
										   FROM wettkampf AS w 
									  LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d USING(xDisziplin) 
										  WHERE xMeeting = ".$_SESSION['temp']['xMeeting']." 
											AND xKategorie = ".$kategorie['xKategorie']." 
									   ORDER BY Anzeige ASC;";
								$query2 = mysql_query($sql2);
								
								$cls = $overall_cls;
								while($disziplin = mysql_fetch_assoc($query2)){
									$info = ($disziplin['Info']!='') ? ' ('.stripslashes($disziplin['Info']).')' : '';
									$info = ($disziplin['xKategorie_svm']!=0) ? ' ('.$strClubEvent.')' : $info;
									
									$sel = (isset($_SESSION['temp']['checked'][$kategorie['xKategorie']][$disziplin['xDisziplin']])) ? ' checked="checked"' : '';
									
									$cls = ($cls=='even') ? 'odd' : 'even';
									?>
									<tr class="<?php echo $cls; ?>">
										<td><label for="delete_<?php echo $kategorie['xKategorie']; ?>_<?php echo $disziplin['xDisziplin']; ?>"><?php echo stripslashes($disziplin['Name']); ?><?php echo $info; ?></label></td>
										<td><input type="checkbox" name="delete[<?php echo $kategorie['xKategorie']; ?>][<?php echo $disziplin['xDisziplin']; ?>]" id="delete_<?php echo $kategorie['xKategorie']; ?>_<?php echo $disziplin['xDisziplin']; ?>" value="<?php echo $disziplin['xDisziplin']; ?>"<?php echo $sel; ?>/></td>
									</tr>
									<?php
								}
								
								$overall_cls = $cls;
								?>
							</table>
						</td>
					</tr>
					<?php
				}
				?>
			</table><br/>
			
			<table width="430" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td style="text-align: right;"><input type="submit" name="btnSubmit" class="uploadbutton" value="<?php echo $strDelete; ?>"/></td>
				</tr>
			</table>
		</form>
		<?php
		unset($_SESSION['temp']);
	} else {
		?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td width="475" style="vertical-align: top;">
					<table class='dialog' width="475">
						<tr>
							<th><?php echo $strServiceMenu; ?></th>
						</tr>
						<tr>
							<td>
								<input type="button" value="<?php echo $strLogout; ?>" onclick="document.location.href = 'admin_service.php?a=logout';"/>
							</td>
						</tr>
					</table><br/>
					<table class='dialog' width="475">
						<tr>
							<th class='insecure'><?php echo $strSrvRemoveMeetingPasswords; ?></th>
						</tr>
						<tr>
							<td>
								<table class='admin'>
									<tr class="odd">
										<td width="70" rowspan="2" style="text-align: center;"><img src="img/password.gif" border="0" alt="<?php echo $strProtectMeeting;?>" title="<?php echo $strProtectMeeting; ?>"/></td>
										<td><?php echo $strSrvRemoveMeetingPasswordsInfo ?></td>
									</tr>
									<tr class='even'>
										<td>
											<?php
											if(isset($_SESSION['temp']) && $_SESSION['temp']['action']=='pws'){
												$color = ($_SESSION['temp']['res']) ? '008000' : 'FF0000';
												$msg = ($_SESSION['temp']['res']) ? $strSrvRemoveMeetingPasswordsOK : $strSrvRemoveMeetingPasswordsError;
												?>
												<br/><span style="color: #<?php echo $color; ?>; font-weight: bold;"><?php echo $msg; ?></span><br/><br/>
												<?php
												
												unset($_SESSION['temp']);
											}
											?>
											<input type="button" value="<?php echo $strSrvRemoveMeetingPasswordsButton; ?>" onclick="document.location.href = 'admin_service.php?a=pws';">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table><br/><br/>
					
					<table class='dialog' width="475">
						<tr>
							<th><?php echo $strSrvRemoveDisciplines; ?></th>
						</tr>
						<tr>
							<td>
								<table class='admin'>
									<tr class="odd">
										<td>
											<form name="frmDisc">
												<?php echo $strSrvRemoveDisciplinesInfo ?><br/><br/>
												
												<?php
												if(isset($_SESSION['temp']) && $_SESSION['temp']['action']=='disc'){
													?>
													<script type="text/javascript">
														function checkDiv(){
															document.getElementById('div_queries').style.display = (document.getElementById('div_queries').style.display=='') ? 'none' : '';
														}
													</script>
													
													<span style="color: #008000; font-weight: bold;">
														<?php echo $strSrvRemoveDisciplinesOK; ?><br/>
														<a href="javascript: checkDiv();">Details (Queries)</a>
														<div id="div_queries" style="display: none;"><?php echo $_SESSION['temp']['queries']; ?></div>
													</span><br/><br/><br/>
													<?php
													
													unset($_SESSION['temp']);
												}
												?>
												
												<?php echo $strMeetingTitle; ?>: 
												<?php
												$dropdown = new GUI_Select('xMeeting', 1, '');
												$dropdown->addOptionsFromDB("select xMeeting, Name from meeting order by DatumVon, DatumBis");
												$dropdown->selectOption("-");
												$dropdown->printList();
												?>
											</form>
										</td>
									</tr>
									<tr class='even'>
										<td>
											
											
											<input type="button" value="<?php echo $strSrvRemoveDisciplinesChoose; ?>" onclick="document.location.href = 'admin_service.php?a=disc&m='+document.frmDisc.xMeeting.value;">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<td width="10"></td>
				<td style="vertical-align: top;">
					<table class='dialog' width="475">
						<tr>
							<th class='bestlistupdate'><?php echo $strSrvStatusUpload; ?></th>
						</tr>
						<tr class="odd">
							<td><?php echo $strSrvStatusUploadInfo ?></td>
						</tr>
						<tr class='even'>
							<td>
								<?php
								if(isset($_SESSION['temp']) && $_SESSION['temp']['action']=='statusupload'){
									$color = ($_SESSION['temp']['res']) ? '008000' : 'FF0000';
									$msg = ($_SESSION['temp']['res']) ? $strSrvStatusUploadOK : $strSrvStatusUploadError;
									?>
									<br/><span style="color: #<?php echo $color; ?>; font-weight: bold;"><?php echo $msg; ?></span><br/><br/>
									<?php
									
									unset($_SESSION['temp']);
								}
								?>
								<input type="button" value="<?php echo $strSrvStatusUpload0; ?>" onclick="document.location.href='admin_service.php?a=statusupload&v=0'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="button" value="<?php echo $strSrvStatusUpload1; ?>" onclick="document.location.href='admin_service.php?a=statusupload&v=1'">
							</td>
						</tr>
					</table><br/><br/>
					
					<table class='dialog' width="475">
						<tr>
							<th>Bewilligungsnummer zuordnen</th>
						</tr>
						<tr class="odd">
							<td>
								Meeting: 
								<?php
								$dropdown = new GUI_Select('xMeeting_xControl', 1, '');
								$dropdown->addOptionsFromDB("select xMeeting, Name from meeting order by DatumVon, DatumBis");
								$dropdown->selectOption("-");
								$dropdown->printList();
								?>
								<br/>
								Bewilligungsnummer: <input type="text" name="xControl" id="xControl" style="width: 50px;"/><br/>
								<input type="button" value="Los" onclick="document.location.href='admin_service.php?a=xControl&m='+document.getElementById('xMeeting_xControlselectbox').value+'&c='+document.getElementById('xControl').value">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php
	}
} else {
	?>
	<?php echo$strServiceMenuInfo; ?><br/><br/>
	
	<?php
	if($res_srv_lg=='error'){
		?>
		<br/><span style="color: #FF0000; font-weight: bold;"><?php echo $strLoginFalse; ?></span><br/><br/>
		<?php
	}
	
	$wert_username = (isset($_POST['username'])) ? stripslashes($_POST['username']) : '';
	$wert_password = (isset($_POST['password'])) ? stripslashes($_POST['password']) : '';
	?>
	<form action="admin_service.php" name="login" method="post">
		<input type="hidden" name="arg" value="login_srv">
		
		<table class="dialog">
			<tr>
				<th colspan="2" class="dialog"><?php echo $strLogin ?></th>
			</tr>
			<tr>
				<td class="dialog"><?php echo $strUserName; ?></td>
				<td class="dialog"><input type="text" name="username" value="<?php echo $wert_username; ?>"></td>
			</tr>
			<tr>
				<td class="dialog"><?php echo $strPassword; ?></td>
				<td class="dialog"><input type="password" name="password" value="<?php echo $wert_password; ?>"></td>
			</tr>
			<tr>
				<td colspan="2" class="forms" align="right"><input type="submit" value="<?php echo $strLogin ?>"></td>
			</tr>
		</table>
	</form>

	<script>
		document.login.username.focus();
	</script>
	<?php
}

$page->endPage();
?>