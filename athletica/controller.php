<?php

/*******************
 *
 * controller.php
 * --------------
 *
 * The Athletica controller component does not produce any output.
 * It glues together views (HTML pages) and models (functions) and steers
 * the program flow.
 * This component is mainly used to provide background processing without
 * redisplaying the HTML-page.
 *
 * All HTML-code produced here is "displayed" in a hidden controller-frame
 * and only serves the purpose to issue Javascript calls to update other
 * frames, mainly the status and the main frame.
 *
 * Actions:
 *		Every action to be handled must be defined in an individual class.
 *		(see cl_action_default.lib.php for details).
 *
 *	Error handling:
 *		Action classes must catch all errors and store an appropriate message
 *    in a global error variable "AA_ERROR". The controller evaluates this
 *    variable and triggers the error response.
 *
 * To add a new action class, copy an existing one, implement the required
 * functionality, add its library file to this script (don't forget to add
 * it also to the errorExit-function).
 *
 */

require('./lib/utils.lib.php');

require('./lib/cl_action_default.lib.php');
require('./lib/cl_action_saveFilm.lib.php');
require('./lib/cl_action_saveResult.lib.php');
require('./lib/cl_action_saveHeatStatus.lib.php');
require('./lib/cl_action_saveSpeakerStatus.lib.php');
require('./lib/cl_action_saveWind.lib.php');
require('./lib/cl_action_saveHandStopped.lib.php');

require('./lib/cl_gui_faq.lib.php');  

$AA_ERROR = '';

//
// connect to DB
//
AA_utils_connectToDB();
if(!empty($GLOBALS['AA_ERROR'])) {
	errorExit($action, $errParams);
}


//
//	Load action class
//

if($_POST['act'] == 'saveFilm')	// requested action class exists
{
	$action = new Action_saveFilm();
}
else if($_POST['act'] == 'saveHeatStatus')	// requested action class exists
{
	$action = new Action_saveHeatStatus();
}
else if($_POST['act'] == 'saveResult')		// requested action class exists
{
	$action = new Action_saveResult();
}
else if($_POST['act'] == 'saveSpeakerStatus')	// requested action class exists
{
	$action = new Action_saveSpeakerStatus();
}
else if($_POST['act'] == 'saveWind')	// requested action class exists
{
	$action = new Action_saveWind();
}
else if($_POST['act'] == 'saveHandStopped')	// requested action class exists
{
	$action = new Action_saveHandStopped();
}
elseif($_GET['act'] == "deactivateFaq"){
	
	$faq = new GUI_Faq();
	$faq->deactivateFaq($_GET['id']);
	?>
	<script type="text/javascript">document.location.href='status.php'</script>
	<?php
	return;
}
else				// send default system error if action not implemented
{
	$action = new Action_default();;
	$GLOBALS['AA_ERROR'] = "System error: Action '"
		. $_POST['act'] . "' not defined in controller.php";
	errorExit($action);
}


//
//	process action
//
$action->process();			// process requested action
if(!empty($GLOBALS['AA_ERROR'])) {
	errorExit($action);
}
 
//
// HTML Header
//
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title>controller</title>
</head>

<body>
[ OK ]
<form action="<?php echo $action->getOKAction(); ?>" method="post" name="next"
	target="<?php echo $action->getOKTarget(); ?>">
<?php

	$params = $action->getOKParameters();
	if(count($params) > 0)
	{
		foreach($params as $key => $value) {
			printf("<input type='hidden' name='$key' value='$value' />");
		}
	}
    
    $prog_mode = AA_results_getProgramMode();           
  
	?>
</form>



<script type="text/javascript">
<!--
    var prog_mode = "<?php echo $prog_mode; ?>";
    var type = "<?php echo $_POST['type']; ?>";  
     
	document.next.submit();   
    if (prog_mode == 2){
        if (type == 4 || type ==5 || type > 7) { 
            parent.frames[1].document.location.href = "http://<?php echo $_SERVER['HTTP_HOST']; ?>/athletica/event_results.php?arg=event&round=<?php echo $_POST['round']; ?>";  
        }
    }
  
//-->
</script>

<?php


//
// Javascript updates to requester page
//
$action->update();


//
// HTML Footer
//
?>

</body>
</html>

<?php



/**
 *		Error exit 
 *
 *		- print error msg in $GLOBAL['AA_ERROR']
 * 	- call error target with provided parameters	
 * 	- terminate this script	
 *
 */
function errorExit($action)
{
	require('./config.inc.php');
	require('./lib/cl_action_default.lib.php');
	require('./lib/cl_action_saveResult.lib.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title>controller</title>
</head>

<body>
[ ERROR ]
<script type="text/javascript">
<!--
	parent.frames[2].location.href= "status.php?msg=<?php echo $strError
		. ": " . $GLOBALS['AA_ERROR']; ?>";
	alert("<?php echo $GLOBALS['AA_ERROR']; ?>");
//-->
</script>


<form action="<?php echo $action->getErrorAction(); ?>" method="post"
	name="next" target="<?php echo $action->getErrorTarget(); ?>">
<?php
	$params = $action->getErrorParameters();
	if(count($params) > 0)
	{
		foreach($params as $key => $value) {
			printf("<input type='hidden' name='$key' value='$value' />");
		}
	}
?>
</form>

<script type="text/javascript">
<!--
	document.next.submit();
//-->
</script>

</body>
</html>

<?php
	exit('athletica_error');
}	// end function errorExit

?>
