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
include('./lib/cl_gui_faq.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}

$arg = "";
if(isset($_POST['arg'])){
	$arg = $_POST['arg'];
}elseif(isset($_GET['arg'])){
	$arg = $_GET['arg'];
}

$faq = new GUI_Faq();

if($arg == "activateAll"){
	
	$faq->activateAll();
	
}else if($arg == "deactivateAll"){
	
	$faq->deactivateAll();
	
}elseif($arg == "activate"){
	
	$faq->activateFaq($_POST['id']);
	
}elseif($arg == "deactivate"){
	
	$faq->deactivateFaq($_POST['id']);
	
}


//
//	Display enrolement list
//

$page = new GUI_Page('admin_faq');
$page->startPage();
$page->printPageTitle($strFaq);

$menu = new GUI_Menulist();
$menu->addButton('admin_faq.php?arg=activateAll', $strFaqActivateAll, '_self');
$menu->addButton('admin_faq.php?arg=deactivateAll', $strFaqDeactivateAll, '_self');
$menu->addButton($cfgURLDocumentation . 'help/administration/index.html', $strHelp, '_blank');
$menu->printMenu();
?>
<p/>

<table class='dialog'>
<tr>
	<th class='dialog' width="300"><?php echo $strFaqQuestion; ?></th>
	<th class='dialog'><?php echo $strFaqActivatedShort; ?></th>
</tr>
<?php

// get all faq for the current language
$res = mysql_query("SELECT * FROM faq WHERE Sprache = '".$_COOKIE['language']."'");
if(mysql_errno() > 0){
	AA_printErrorMsg(mysql_errno().": ".mysql_error());
}else{
	$class = 'odd';
	while($row = mysql_fetch_assoc($res)){
		$check = "";
		$action = "activate";
		if($row['Zeigen'] == 'y'){
			$check = "checked";
			$action = "deactivate";
		}
		?>
<tr class='<?php echo $class ?>'>
	<td class='dialog'><?php echo $row['Frage'] ?></td>
	<form action="admin_faq.php" method="POST">
	<td class='forms'>
		
		<input type="checkbox" name="" value="" <?php echo $check ?> onclick="submit()">
		<input type="hidden" name="arg" value="<?php echo $action ?>">
		<input type="hidden" name="id" value="<?php echo $row['xFaq'] ?>">
		
	</td>
	</form>
</tr>
		<?php
		if($class == 'odd'){
			$class = 'even';
		}else{
			$class = 'odd';
		}
	}
	
}

?>

</table>

<?php
$page->endPage();
