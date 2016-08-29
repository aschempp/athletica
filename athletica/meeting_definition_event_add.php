<?php
/**********
 *
 *	meeting_definition_eventadd.php
 *	-------------------------------
 *	
 */

require('./convtables.inc.php');

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_select.lib.php');

require('./lib/meeting.lib.php');
require('./lib/common.lib.php');


if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$category = 0;
if(!empty($_POST['cat'])) {
	$category = $_POST['cat'];
}
else if(!empty($_GET['cat'])) {
	$category = $_GET['cat'];
}

$date_keep = ''; 
if(!empty($_POST['date_1'] )){
    $date_keep = $_POST['date_1'];
}


$ukc_meeting = AA_checkMeeting_UKC() ;
  
// add a new event
if ($_POST['arg']=="add_event")
{
	AA_meeting_addEvent();

	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	
}

/***************************
 *
 *		General meeting data
 *
 ***************************/

$page = new GUI_Page('meeting_definition_event_add');
$page->startPage();
$page->printPageTitle($strNewEvent);

if ($_POST['arg']=="add_event")
{
	?>
<script type="text/javascript">
	window.open("meeting_definition_eventlist.php?updateCat="
		+ <?php echo $category; ?>,
		"list");
</script>
	<?php
}

//get default fee and deposit
$sql= "
	SELECT
		Startgeld
		, Haftgeld
	FROM
		meeting
	WHERE
		xMeeting = " . $_COOKIE['meeting_id'];
$result_meeting = mysql_query($sql);

if(mysql_errno() > 0) {		// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else		// no DB error
{

	if(mysql_num_rows($result_meeting) == 1)	
	{
		$row_meeting = mysql_fetch_array($result_meeting);
		$_POST['deposit'] = $row_meeting['Haftgeld']/100; //amounts ar stored in cents
		$_POST['fee'] =$row_meeting['Startgeld']/100;	  //amounts ar stored in cents
	} else {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());	
	}
	
}
?>



<script type="text/javascript">
<!--
	var disz = new Array();
	var timingrow;

	<?php
	// get disciplines with type for checking if wind can be enabled or not
	$res = mysql_query("select xDisziplin, Typ from disziplin_" . $_COOKIE['language']);
	$i=0;
	while($row_dis = mysql_fetch_array($res)){
		?>
		//disz[<?php echo $i ?>] = new Array(2);
		//disz[<?php echo $i ?>][0] = "<?php echo $row_dis[0] ?>";
		//disz[<?php echo $i ?>][1] = '<?php echo $row_dis[1] ?>';
		disz[<?php echo $row_dis[0] ?>] = '<?php echo $row_dis[1] ?>';
		<?php
		$i++;
	}
	mysql_Free_result($res);
	?>
	
	function check(item)	// state has changed; check what to do
	{
		if((item=='discipline')
			&& (document.add_event.discipline.value=='new'))	// new discipline
		{
			window.open("admin_disciplines.php", "_self");
		}
		else if((item=='category')
			&& (document.add_event.cat.value=='new'))	// new category
		{
			window.open("admin_categories.php", "_self");
		}
	}
	
	function check_discipline(){
		val = document.getElementById("disciplineselectbox").value;
		if(disz[val] == <?php echo $cfgDisciplineType[$strDiscTypeTrack] ?> || disz[val] == <?php echo $cfgDisciplineType[$strDiscTypeJump] ?>){
			// wind may be measured
			document.getElementById('wind').style.visibility = "visible";
			document.getElementById('nowind').style.visibility = "hidden";
		}else{
			document.getElementById('wind').style.visibility = "hidden";
			document.getElementById('nowind').style.visibility = "visible";
		}
		
		if(disz[val] == <?php echo $cfgDisciplineType[$strDiscTypeNone] ?> 
			|| disz[val] == <?php echo $cfgDisciplineType[$strDiscTypeTrack] ?> 
			|| disz[val] == <?php echo $cfgDisciplineType[$strDiscTypeTrackNoWind] ?> 
			|| disz[val] == <?php echo $cfgDisciplineType[$strDiscTypeDistance] ?> 
			|| disz[val] == <?php echo $cfgDisciplineType[$strDiscTypeRelay] ?> ){
			document.getElementById('timing').style.visibility = "visible";
		}else{
			document.getElementById('timing').style.visibility = "hidden";
		}
	}
	
	function jump_time(curr, next){
		var e = window.event;
		//alert (e);
		//if(e.keyCode > 31){
		if(curr.value.length == 2){
			var tmp = document.getElementsByName(next);
			tmp[0].focus();
			tmp[0].select();
		}
		//}
	}
	
	function create_combined(){
		
		if(document.add_event.cat.value != 'new' && document.add_event.cat.value != 0){
			
			// redirect for adding a combined event in the current category
			var val = document.add_event.cat.value;
			document.location.href = "meeting_definition_category.php?arg=create_combined&cat="+val;
			
		}else{
			
			alert("<?php echo $strErrCreateCombined ?>");
			
		}
	}
    
    function create_svm(){
        
        if(document.add_event.cat.value != 'new' && document.add_event.cat.value != 0){
            
            // redirect for adding a combined event in the current category
            var val = document.add_event.cat.value;
            document.location.href = "meeting_definition_category.php?arg=create_svm&cat="+val;
            
        }else{
            
            alert("<?php echo $strErrCreateSVM ?>");
            
        }
    }
    
    function change_date( id, selIndex){ 
       
        if (id != 'date_1selectbox') {
            document.getElementById('date_1selectbox').options[selIndex].selected=true;  
        }
         if (id != 'date_2selectbox') {
            document.getElementById('date_2selectbox').options[selIndex].selected=true;  
        }
         if (id != 'date_3selectbox') {
            document.getElementById('date_3selectbox').options[selIndex].selected=true;  
        }  
    }
    
//-->
</script>

<?php
/*****************************************
 *
 *	Form to add new category/discipline
 *
 *****************************************/

if ($ukc_meeting){
    $sql = "SELECT
                xKategorie
                , Kurzname
            FROM
                kategorie AS k                     
            WHERE aktiv = 'y'                       
            ORDER BY
                Anzeige";
}
else {
       $sql = "SELECT
                xKategorie
                , Kurzname
            FROM
                kategorie AS k     
                LEFT JOIN meeting AS m ON (m.UKC = k.UKC)  
            WHERE aktiv = 'y'
                AND m.xMeeting = " . $_COOKIE['meeting_id'] . "   
            ORDER BY
                Anzeige";
}

$result = mysql_query($sql);  	

if(mysql_errno() > 0) {		// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else		// no DB error
{

	if(mysql_num_rows($result) >= 1)	// any more categories
	{
?>
<form action='meeting_definition_event_add.php' method='post' name='add_event'>   

<table class='dialog' id="properties">
<tr>
	<th class='dialog'><?php echo $strCategory; ?></th>
	<td class='forms'>
		<input name='arg' type='hidden' value='add_event' />
<?php
		$dropdown = new GUI_Select('cat', 1, "check(\"category\")");
		$dropdown->addOptionNone();
		while ($row = mysql_fetch_row($result))
		{
			$dropdown->addOption($row[1], $row[0]);
		}
		$dropdown->addOptionNew();
		$dropdown->selectOption($category);
		$dropdown->printList();
?>
    </td>
    <td style="display:none">
	</td>
</tr>
     <?php
  if ($ukc_meeting == 'n') {
      ?>
<tr>
	<th class='dialog'><?php echo $strDiscipline; ?></th>
<?php
		$dd = new GUI_DisciplineDropDown(0, true, false, $keys, 'check_discipline()', '' ,$ukc_meeting);
?>
<td style="display:none">

   <input type="button" onclick="create_svm()" value="<?php echo $strCreateSVM ?>">  
  
   </td>
</tr>
 <?php
  }
  ?>

<tr style="display:none">
	<th class='dialog'><?php echo $strWind; ?></th>
	<td class='forms'>
		<input type="checkbox" name="wind" id="wind" value="yes"><span id="nowind"><?php echo $strNoWind ?></span>
	</td>
	<script language="javascript">
		document.getElementById('wind').style.visibility = "hidden";
	</script>
</tr>


<tr style="display:none">
	<th class='dialog'><?php echo $strDeposit; ?></th>
	<td class='forms'>
		<input class='nbr' name='deposit' type='text' maxlength='10'
			value='<?php echo $_POST['deposit']; ?>' />
	</td>
</tr>

<tr style="display:none">
	<th class='dialog'><?php echo $strFee; ?></th>
	<td class='forms'>
		<input class='nbr' name='fee' type='text' maxlength='10'
			value='<?php echo $_POST['fee']; ?>' />
	</td>
</tr>

<tr style="display:none">
	<th class='dialog'><?php echo $strInfo; ?></th>
	<td class='forms' colspan='3'>
		<input class='text' name='info' type='text' maxlength='15'
			value='' />
	</td>
</tr>

<tr id="timing" style="display:none">
    <th class='dialog'><?php echo $strTiming; ?></th>
    <td class='forms' colspan='3'>
        <input type="checkbox" name="timing" value="yes"> <?php echo $strOn ?> /
        <input type="checkbox" name="timingAuto" value="yes"> <?php echo $strAutomatic ?>
    </td>
    <script language="javascript">
        document.getElementById('timing').style.visibility = "hidden";
        /*timingrow = document.getElementById('timing');
         var tmp = timingrow.parentNode;
         tmp.removeChild(timingrow);*/
    </script>
</tr>

</table>

<p/>

<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strType; ?></th>
	<th class='dialog'><?php echo $strDate; ?></th>
	<th class='dialog'><?php echo $strTimeFormat; ?></th>
</tr>
<tr>
	<?php
	$dd = new GUI_RoundtypeDropDown(0, 1);  
    if ($date_keep == '' ) {   
	    $dd = new GUI_DateDropDown(0, 1,'change_date(this.id, this.selectedIndex)'); 
    }
    else {
         $dd = new GUI_DateDropDown($date_keep, 1,'change_date(this.id, this.selectedIndex)'); 
        
    }
	?>
	<td class='forms'>
		<input size="4" type='text' name='time_1' maxlength='5'
			value='' />
	</td>

</tr>

</table>

<button type='submit'>
	<?php echo $strAdd; ?>
</button>
</form>   
<?php
	}	// any category found
}	// ET DB error
mysql_free_result($result);

if($category > 0)
{
	?>
<script type="text/javascript">
<!--
	if(document.add_event) {
		document.add_event.discipline.focus();
	}
//-->
</script>
	<?php
}
else
{
	?>
<script type="text/javascript">
<!--
	if(document.add_event) {
		document.add_event.cat.focus();
	}
//-->
</script>
	<?php
}

$page->endPage();
