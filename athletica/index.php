<?php
/****************
 *
 * index.php
 * ---------
 *
 * @arg	Menu name
 */

require('lib/common.lib.php');
require('lib/meeting.lib.php');
require('lib/cl_protect.lib.php');

if(!empty($_GET['arg'])) {
	$arg = $_GET['arg'];
}
else {		// default menu
	$arg = 'meeting';
}

$arg2 = ''; 
if(!empty($_GET['arg2'])) {
    $arg2 = $_GET['arg2'];
}


$meetingID = '';
$arg_m = '';
if(!empty($_POST['item'])) {
    $meetingID = $_POST['item'];
    $arg_m = "?meetingId=".$meetingID; 
}


// change language
if ($_GET['language']=="change")
{
	// store cookies on browser
	setcookie("language_trans", $cfgLanguage[$_GET['lang']]['file']
		, time()+$cfgCookieExpires);
	setcookie("language_doc", $cfgLanguage[$_GET['lang']]['doc']
		, time()+$cfgCookieExpires);
	setcookie("language", $cfgLanguage[$_GET['lang']]['short']
		, time()+$cfgCookieExpires);
	// update current cookies
	$_COOKIE['language_trans'] = $cfgLanguage[$_GET['lang']]['file'];
	$_COOKIE['language_doc'] = $cfgLanguage[$_GET['lang']]['doc'];
	$_COOKIE['language'] = $cfgLanguage[$_GET['lang']]['short'];
	// load new language files
	include ($_COOKIE['language_trans']);
	$cfgURLDocumentation = $_COOKIE['language_doc'];

	$arg = 'admin';
}

// check on meeting password
$redirect = "";
$redirect2 = "";
$pass = new Protect();
if($pass->isRestricted($_COOKIE['meeting_id'])){
	
	if(!$pass->isLoggedIn($_COOKIE['meeting_id'])){ // user not logged in -> only speaker access
		if(!in_array($arg, $cfgOpenPages)){
			
			$redirect = "?redirect=$arg";
			$redirect2 = "&redirect=$arg";
			$arg = "login";
			
		}
		
	}
	
}

$ukc_meeting = AA_checkMeeting_UKC() ; 

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title><?php echo $cfgApplicationName . " (Version ". $cfgApplicationVersion
							. ")"; ?></title>
</head>

<frameset  rows="49,*,20,0" frameborder="NO" border="0" framespacing="0">
	<frame name="menu" src="menu.php?arg=<?php echo $arg; ?>&ukc=<?php echo $ukc_meeting; ?>&meetingID=<?php echo $meetingID; ?>&arg2=<?php echo $arg2; ?>&<?php echo $redirect2 ?>" marginwidth="0"
			marginheight="0" scrolling="no" frameborder="0" noresize>
	<frame name="main" src="<?php echo $arg; ?>.php<?php echo $arg_m; ?><?php echo $redirect ?>" marginwidth="0"
			marginheight="0" scrolling="auto" frameborder="0" noresize>
	<frame name="status" src="status.php" marginwidth="0"
			marginheight="0" scrolling="no" frameborder="0" noresize>
	<frame name="controller" src="UntitledFrame-2" marginwidth="0"
			marginheight="0" scrolling="no" frameborder="0" noresize>
</frameset>

<noframes>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" />
</noframes>

</html>
