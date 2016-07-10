<?php

/**********
 *
 *	event_rankinglists.php
 *	----------------------
 *	
 */         
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');
require('./lib/results.lib.php');

$disciplines=false;  

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

// get presets
$round = 0;
if(!empty($_GET['round'])){
	$round = $_GET['round'];
}
else if(!empty($_POST['round'])) {
	$round = $_POST['round'];
}
     
$presets = AA_results_getPresets($round);

$eventTypeCat = AA_getEventTypesCat();	

// check discipline type of event if selected
$dtype = "";
if(!empty($presets['event'])){
	$res = mysql_query("
		SELECT d.Typ FROM 
			wettkampf as w
			LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d USING(xDisziplin) 
		WHERE w.xWettkampf = ".$presets['event']
	);
	
	if(mysql_errno() > 0){
		
	}else{
		$row = mysql_fetch_array($res);
		$dtype = $row[0];
	}
}
   
if (!empty($_POST['arg'] )) {
	if ($_POST['arg'] =='change_catFrom') {  
		$catFrom=$_POST['category'];
		$catTo=$_POST['category']; 
		$presets['category']='';
	}    
	else if ($_POST['arg'] =='change_discFrom') {
		$discFrom=$_POST['discipline'];
		$discTo=$_POST['discipline'];  
	}  
	else if ($_POST['arg'] =='change_heatFrom') { 
			$heatFrom=$_POST['heatFrom'];
			$heatTo=$_POST['heatFrom'];  
	}     
}          
 
if (!empty($_POST['arg'] )) {
	 if ($_POST['arg'] =='change_catTo') { 	            
		$catTo=$_POST['category']; 
		 $presets['category']='';  
	}	
	else  if ($_POST['arg'] =='change_discTo') {
		$discTo=$_POST['discipline'];  
	}  
	else if ($_POST['arg'] =='change_heatTo') { 
			$heatTo=$_POST['heatTo'];
	}       
} 
                                   

if  (!empty($_POST['catFrom']) && $_POST['arg'] !='change_catFrom'){
	 $catFrom=$_POST['catFrom']; 	
}
if  (!empty($_POST['catTo']) && $_POST['arg'] !='change_catTo'){     
	 $catTo=$_POST['catTo'];      
}
if  (!empty($_POST['discFrom']) && $_POST['arg'] !='change_discFrom') {
	 $discFrom=$_POST['discFrom'];  
}
if  (!empty($_POST['discTo']) && $_POST['arg'] !='change_discTo'){
	 $discTo=$_POST['discTo'];   
} 
if  (!empty($_POST['heatFrom']) && $_POST['arg'] !='change_heatFrom') {
	 $heatFrom=$_POST['heatFrom'];   
}
if  (!empty($_POST['heatTo']) && $_POST['arg'] !='change_heatTo'){
	 $heatTo=$_POST['heatTo'];   
}


if  (!empty($_GET['catFrom'])) {
	 $catFrom=$_GET['catFrom'];  
	 
}
if  (!empty($_GET['catTo'])) {  
	 $catTo=$_GET['catTo']; 
	 
}
if  (!empty($_GET['discFrom'])) {
	 $discFrom=$_GET['discFrom'];  
}
if  (!empty($_GET['discTo'])) {
	 $discTo=$_GET['discTo'];  
} 

         
//
//	Display print form
//

$page = new GUI_Page('event_rankinglists');
$page->startPage();
$page->printPageTitle($strRankingLists . ": " . $_COOKIE['meeting']);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/event/rankinglists.html', $strHelp, '_blank');
$menu->printMenu();

?>
<script type="text/javascript">
<!--
	function setPrint()
	{
		document.printdialog.formaction.value = 'print';
		document.printdialog.target = '_blank';
	}
	
	function setView()
	{
		document.printdialog.formaction.value = 'view';
		document.printdialog.target = '';
	}
	
	function setExportPress()
	{
		document.printdialog.formaction.value = 'exportpress';
		document.printdialog.target = '';
	}
	
	function setExportDiplom()
	{
		document.printdialog.formaction.value = 'exportdiplom';
		document.printdialog.target = '';
	}
	
	function checkDisc() 
	{  
	   e = document.getElementById("combined"); 
	   e.checked=true; 
	}
           
   
//-->
</script>

<p/>

<table><tr>
	<td>
		<?php	AA_printCategorySelection('event_rankinglists.php'
			, $presets['category'], 'post'); ?>
	</td>
	<td>
		<?php	AA_printEventSelection('event_rankinglists.php'
			, $presets['category'], $presets['event'], 'post'); ?>
	</td>
<?php
if($presets['event'] > 0) {		// event selected        
?>
	<td>
		<?php AA_printRoundSelection('event_rankinglists.php'
			, $presets['category'] , $presets['event'], $round); ?>
	</td>
<?php
}

 if($round > 0) {		// round selected        
?>
	<td>
		<?php AA_printHeatSelectionDropDownFrom('event_rankinglists.php'
			, $presets['category'] , $presets['event'], $round, $heatFrom, $heatTo); ?>
	</td>   
<?php   
}

if($heatFrom > 0) {		// heat from selected   
?>
	<td>
		<?php AA_printHeatSelectionDropDownTo('event_rankinglists.php'
			, $presets['category'] , $presets['event'], $round, $heatFrom, $heatTo); ?>
	</td>  
<?php    
}
 
?>
 </tr>
</table>   
 
<br>
<table>   

	<tr>       
				<th class='dialog'><?php echo $strCategory . " "; echo $strOf2;?></th>
				 <form action='event_rankinglists.php' method='post' name='catFrom' > 
					<input name='arg' type='hidden' value='change_catFrom' /> 
					 <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
					  <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' /> 
					  <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' /> 
					   <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' /> 
					   <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />       
<?php
				$dd = new GUI_CategoryDropDown($catFrom,'document.catFrom.submit()', false);
				?>
				</form>
				 <th class='dialog'><?php echo $strCategory. " "; echo $strTo2; ?></th>
				 <form action='event_rankinglists.php' method='post' name='catTo' > 
				 <input name='arg' type='hidden' value='change_catTo' /> 
				 <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' />  
				 <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
				  <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' />  
				   <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' />   
				   <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />    
				<?php
				$dd = new GUI_CategoryDropDown($catTo,'document.catTo.submit()', false);
				?>
				 </form>   
			</tr>
	<tr>

				<th class='dialog'><?php echo $strDiscipline. " "; echo $strOf2;?></th>
				 <form action='event_rankinglists.php' method='post' name='discFrom' > 
					<input name='arg' type='hidden' value='change_discFrom' /> 
					 <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' /> 
					 <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' />  
					  <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
					   <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' /> 
					   <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />       
				<?php     
				$dd = new GUI_DisciplineDropDown($discFrom,'','','','document.discFrom.submit()');
				?>
				 </form> 
				 <th class='dialog'><?php echo $strDiscipline. " "; echo $strTo2; ?></th> 
				  <form action='event_rankinglists.php' method='post' name='discTo' > 
					<input name='arg' type='hidden' value='change_discTo' /> 
					 <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
					  <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' />    
					 <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' />   
					  <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' /> 
					  <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />     
				
				<?php
				$dd = new GUI_DisciplineDropDown($discTo,'','','','document.discTo.submit()');   
				?>
				 </form>   
	</tr>  	
	</table> 	
	
	<table>
		<tr>    

 
<form action='print_rankinglist.php' method='get' name='printdialog'>  
<input type='hidden' name='category' value='<?php echo $presets['category']; ?>'>
<input type='hidden' name='event' value='<?php echo $presets['event']; ?>'>
<input type='hidden' name='round' value='<?php echo $round; ?>'>
<input type='hidden' name='heatFrom' value='<?php echo $heatFrom; ?>'>
<input type='hidden' name='heatTo' value='<?php echo $heatTo ?>'>
<input type='hidden' name='formaction' value=''>   
<input type='hidden' name='catFrom' value='<?php echo $catFrom; ?>'> 
<input type='hidden' name='catTo' value='<?php echo $catTo; ?>'>  
<input type='hidden' name='discFrom' value='<?php echo $discFrom; ?>'>  
<input type='hidden' name='discTo' value='<?php echo $discTo; ?>'> 
 
<table class='dialog'>  
<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='single' id='type'  checked >
			<?php echo $strSingleEvent; ?></input>
	</th>
</tr>   
         
<?php
if(($dtype == $cfgDisciplineType[$strDiscTypeJump])
	|| ($dtype == $cfgDisciplineType[$strDiscTypeJumpNoWind])
	|| ($dtype == $cfgDisciplineType[$strDiscTypeThrow])
	|| ($dtype == $cfgDisciplineType[$strDiscTypeHigh])
	|| empty($presets['event'])) {
?>
<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='single_attempts' id='type' >
			<?php echo $strSingleEventAttempts; ?></input>
	</th>
</tr> 
<?php
}
  
if (isset($eventTypeCat['combined'])){?> 
   
<tr> 
    <td class='dialog'>
        &nbsp;&nbsp;  
        <input type='checkbox' name='heatSeparate' value='yes'>
            <?php echo $strHeatsSeparate ?></input>
    </td>
</tr>
<tr> 
    <td class='dialog'>
        &nbsp;&nbsp;  
        <input type='checkbox' name='withStartnr' value='yes'>
            <?php echo $strStartnumbers ?></input>
    </td>
</tr>
<tr> 
    <td class='dialog'>
        &nbsp;&nbsp;  
        <input type='checkbox' name='ranklistAll' value='yes'>
            <?php echo $strRanklistAll ?></input>
    </td>
</tr>

                          

<?php 
}
 
// Rankginglists for club and combined-events
//if(empty($presets['event'])){// no event selected

if (isset($eventTypeCat['combined'])){?>
<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='combined' id='combined' >
			<?php echo $strCombinedEvent; ?> </input>
		
	</th>
</tr>

<tr> 
	<td class='dialog'>
		&nbsp;&nbsp;  
		<input type='checkbox' name='sepu23' value='yes'>
			<?php echo $strSeparateU23; ?></input>
	</td>
</tr>
							
<tr> 
	<td class='dialog'>&nbsp;&nbsp;&nbsp;&nbsp;Disziplin: 1 bis   
			<select name='disc_nr' onchange='checkDisc()'>
				<option value="99">99</option>
				<?php
				for ($i=1;$i<=99;$i++){
					?>
					<option value="<?php echo $i?>"><?php echo $i?></option>
					<?php
				}
				?>
			</select>
		</td>
</tr><?php
} //END IF  isset($eventTypeCat['combined']))   

if (isset($eventTypeCat['club'])){?>
<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='team'>
			<?php echo $strClubRanking; ?></input>
	</td>
</tr>
 <tr>
    <th class='dialog'>
        <input type='radio' name='type' value='teamAll'>
            <?php echo  $strClubRanking . " (" . $strAll . ")";?> </input>
        
    </th>
</tr>
 <tr>
    <th class='dialog'>
        <input type='radio' name='type' value='teamP'>
            <?php echo  $strClubRanking . " " . $strTnMk;?> </input>
        
    </th>
</tr>
<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='sheets'>
			<?php echo $strClubSheets; ?></input>
	</td>
</tr>  

<?php
}
//}
if(empty($round) && isset($eventTypeCat['teamsm'])){	// team sm ranking minimum is discipline and at least one eventtype must be team-sm?>
<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='teamsm'>
			<?php echo $strTeamSMRanking; ?></input>
	</td>
</tr>
<?php
}
if (!isset($eventTypeCat['combined'])){?> 
   

<tr> 
    <td class='dialog'>
        &nbsp;&nbsp;  
        <input type='checkbox' name='heatSeparate' value='yes'>
            <?php echo $strHeatsSeparate ?></input>
    </td>
</tr>
<tr> 
    <td class='dialog'>
        &nbsp;&nbsp;  
        <input type='checkbox' name='withStartnr' value='yes'>
            <?php echo $strStartnumbers ?></input>
    </td>
</tr>
<tr> 
    <td class='dialog'>
        &nbsp;&nbsp;  
        <input type='checkbox' name='ranklistAll' value='yes'>
            <?php echo $strRanklistAll ?></input>
    </td>
</tr>


<?php
}  
?>
<tr>
	<th class='dialog'><?php echo $strSortBy; ?></th>

</tr>
<tr>
	<td class='dialog'>
	 &nbsp;&nbsp; 
		<input type='checkbox' name='athleteCat' value='yes'>
			<?php echo $strAthleteCat; ?></input>
	</td>
</tr>    
<?php 
if(empty($presets['event']))	// show page break only event not selected
{										
?>

<tr>
	<th class='dialog'>
		<?php echo $strPageBreak; ?>
	</th>
</tr>


<tr>
	<td class='dialog'>
		<input type='radio' name='break' value='none' checked>
			<?php echo $strNoPageBreak; ?></input>
	</td>
</tr>
<?php
	if(empty($presets['category']))	// show page break 'category' only if no
	{											// specific category selected
?>
<tr>
	<td class='dialog'>
		<input type='radio' name='break' value='category'>
			<?php echo $strCategory; ?></input>
	</td>
</tr>
<?php
	}		// ET page break category
?>
<tr>
	<td class='dialog'>
		<input type='radio' name='break' value='discipline'>
			<?php echo $strDiscipline; ?></input>
	</td>
</tr>
<?php
}		// ET page break

$tage = 1;
$sql = "SELECT DISTINCT(Datum) AS Datum 
		  FROM runde 
	 LEFT JOIN wettkampf USING(xWettkampf) 
		 WHERE xMeeting = ".$_COOKIE['meeting_id']." 
	  ORDER BY Datum ASC;";
$query = mysql_query($sql);

$tage = mysql_num_rows($query);
if($tage>1){
	?>
	<tr>
		<th class='dialog'>
			<?php echo $strDay; ?></input>
		</th>
	</tr>
	<tr>
		<td class='dialog'>
			<select name='date'>
				<option value="%">- <?php echo $strAll; ?> -</option>
				<?php
				while($row = mysql_fetch_assoc($query)){
					?>
					<option value="<?php echo $row['Datum']?>"><?php echo date('d.m.Y', strtotime($row['Datum']))?></option>
					<?php
				}
				?>
			</select>
		</td>
	</tr>
	<?php
}
?>
<tr>
	<th class='dialog'>
		<input type='checkbox' name='cover' value='cover'>
			<?php echo $strCover; ?></input>
	</th>
</tr>
<tr>
	<td class='dialog'>
		<input type='checkbox' name='cover_timing' value='1'>
			<?php echo $strTiming; ?></input>
	</td>
</tr>
<?php
//if($presets['event'] > 0) {	// event selected
//	$efforts_text = $strEfforts;
//} else {
	$efforts_text = "<a href=\"#\" class=\"info\">$strEfforts<span>$strEffortsWarning</span></a>"; //show anyway
//}?>	
<tr>
	<th class='dialog'>
		<input type='checkbox' name='show_efforts' value='sb_pb' checked="checked">
			<?php echo $efforts_text ; ?></input>
	</th>
</tr>
          
</table>
  <p />   
     <table>
<tr>
    <th class='dialog'>
        <input type='radio' name='type' value='ukc' id='combined' >
             <?php echo $strRankingList . " " . $strUKC; ?></input>     
        
    </th>
</tr>
</table>

<p />   

<table>
<tr>
	<td>
		<button name='view' type='submit' onClick='setView()'>
			<?php echo $strShow; ?>
		</button>
	</td>
	<td>
		<button name='print' type='submit' onClick='setPrint()'>
			<?php echo $strPrint; ?>
		</button>
	</td>         
</tr>
</table>
          


<br/>

<table class="dialog">
<tr>
	<th class="dialog"><?php echo $strExport ?></th>
</tr>
<tr>
	<td class="forms">
		<input type="radio" name="limitRank" value="yes" id="limitrank">
		<?php echo $strExportRanks ?> <input type="text" size="2" name="limitRankFrom" onfocus="o = document.getElementById('limitrank'); o.checked='checked'">
		<?php echo strtolower($strTo) ?> <input type="text" size="2" name="limitRankTo" onfocus="o = document.getElementById('limitrank'); o.checked='checked'">
	</td>
</tr>
<tr>
	<td class="forms">
		<input type="radio" name="limitRank" value="no" checked><?php echo $strExportAllRanks ?>
	</td>
</tr>
<tr>
	<td class="forms" align="right">
		<button name='print' type='submit' onClick='setExportPress()'>
			<?php echo $strExportPress; ?>
		</button>
		<button name='print' type='submit' onClick='setExportDiplom()'>
			<?php echo $strExportDiplom; ?>
		</button>
	</td>
</tr>
</table>

</form>  

<?php

$page->endPage();



?>
