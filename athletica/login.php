<?php
/**********
 *
 *	meeting_copy.php
 *	------------------
 *	
 */

require('./lib/cl_gui_page.lib.php');
require('./lib/cl_protect.lib.php');
require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$arg = "";
if(isset($_POST['arg'])){
	$arg = $_POST['arg'];
}
$redirect = "";
if(isset($_GET['redirect'])){
	$redirect = $_GET['redirect'];
}elseif(isset($_POST['redirect'])){
	$redirect = $_POST['redirect'];
}
$error = false;


//
// do login
//

if($arg == "login"){
	$p = new Protect();
	$res = $p->login($_COOKIE['meeting_id'], $_POST['password']);
	
	if($res){
		$redirect = (isset($_POST['redirect']) && trim($_POST['redirect'])!='') ? $_POST['redirect'] : 'admin';
		header("Location: ".$redirect.".php");
		exit();
	}else{
		$error = true;
	}
	
}

$page = new GUI_Page('login');
$page->startPage();
$page->printPageTitle($strLoginToMeeting . ": " . $_COOKIE['meeting']);
?>

<br>
<p><?php echo $strRestrictedAccess ?></p>
<br>

<?php
if($error){
	echo "<p>$strErrAccessDenied</p>";
}
?>

<table class="dialog">
	<form action="login.php" name="login" method="post">
	<input type="hidden" name="arg" value="login">
	<input type="hidden" name="redirect" value="<?php echo $redirect ?>">
	<tr>
		<th class="dialog"><?php echo $strLogin ?></th>
	</tr>
	<tr>
		<td class="dialog"><?php echo $strPassword ?>:
			<input type="password" name="password">
		</td>
	</tr>
	<tr>
		<td class="forms" align="right">
			<input type="submit" value="<?php echo $strLogin ?>">
		</td>
	</tr>
	</form>
</table>

<script>
document.login.password.focus();
</script>

<?php


$page->endPage();
?>
