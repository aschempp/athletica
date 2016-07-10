<?php

/**********
 *
 *	regie.php
 *	-----------
 *	
 */

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_searchfield.lib.php');

require('./lib/common.lib.php');
require('./lib/timetable.lib.php');

if(AA_connectToDB() == FALSE) {	// invalid DB connection
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$now = getdate();
$zero = '';
if($now['minutes'] < 10) {
	$zero = '0';
}

$timestamp = $now['mday']
				. "." . $now['mon']
				. "." . $now['year']
				. ", " . $now['hours']
				. "." . $zero. $now['minutes'];

$page = new GUI_Page('regie');
$page->startPage();
$page->printPageTitle($strRegieMonitor . " " . $_COOKIE['meeting'] . " ($timestamp Uhr)");

$hlpbtn = new GUI_Button($cfgURLDocumentation . 'help/regie/index.html', $strHelp, '_blank');
?>

 <p>
    <?php $hlpbtn->printButton(); ?>      

<?php
$search = new GUI_Searchfield('speaker_entry.php', '_self', 'post', 'regie.php');
$search->printSearchfield();    
?>
<p />
<?php AA_timetable_display_regie($timestamp); ?>

<script type="text/javascript">
<!--
	window.setTimeout("updatePage()", <?php echo $cfgMonitorReload * 1000; ?>);

	// scroll to put current time line approximately to the middle of the screen
	var now = new Date();
	//var now = new Date(2003,2,9,10,0,0);	// test case
	var year = now.getYear();
	var m = now.getMonth() + 1;
	var month = ((m < 10) ? ("0" + m) : m);
	var d = now.getDate();
	var day = ((d < 10) ? ("0" + d) : d);
	var h = now.getHours() - 2;
	var hour = ((h < 10) ? ("0" + h) : h);
	var date = year + "-" + month + "-" + day + hour;
	if(document.getElementById(date))
	{
		// scroll to current time (Internet Explorer only!!!)
		document.getElementById(date).scrollIntoView("false");
		// get Y-offset 
		if(document.documentElement && document.documentElement.scrollTop)
		{														//IE6 standards compliant mode
			var scroll = document.documentElement.scrollTop;
	  }
		// scroll to the left
		window.scrollTo(0, scroll);
	}

	function updatePage()
	{
		window.open("regie.php", "main");
	}

	//-->
</script>

<?php

$page->endPage();

