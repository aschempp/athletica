<?php

if (!defined('AA_CL_GUI_PAGE_LIB_INCLUDED'))
{
	define('AA_CL_GUI_PAGE_LIB_INCLUDED', 1);


/********************************************
 *
 * CLASS GUI_Page
 *
 * Prints an HTML page.
 *
 *******************************************/

include("cl_gui_faq.lib.php");
include("cl_protect.lib.php");

class GUI_Page
{
	var $stylesheet;
	var $additional_stylesheet;
	var $title;
	var $scroll;

	function GUI_Page($title, $scroll=FALSE, $additional_stylesheet="")
	{
		
		// check on meeting password
		$pass = new Protect();
		if($pass->isRestricted($_COOKIE['meeting_id'])){
			
			if(!$pass->isLoggedIn($_COOKIE['meeting_id'])){ // user not logged in -> only speaker access
				
				if(!in_array($title, $GLOBALS['cfgOpenPages'])){
					
					?>
					<script type="text/javascript">
						parent.location.href = 'index.php?arg=admin';
					</script>
					<?php
				}	
			}	
		}
		
		$this->title = $title;
		$this->scroll = $scroll;
		$this->stylesheet = "stylesheet.css";
		$this->additional_stylesheet = $additional_stylesheet;
		$this->printHTMLHeader();
	}

	function printHTMLHeader()
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $this->title; ?></title>
<link rel="stylesheet" href="css/<?php echo $this->stylesheet; ?>" type="text/css">
<?php
	if ($this->additional_stylesheet != "") {
	?><link rel="stylesheet" href="css/<?php echo $this->additional_stylesheet; ?>" type="text/css"><?php
	}
?>
</head>
<?php
	}



	function startPage()
	{
		$cl_gui_page_scroll_y = 0;
		if(!empty($_POST['cl_gui_page_scroll_y'])) {
			$cl_gui_page_scroll_y = $_POST['cl_gui_page_scroll_y'];
		}
?>
<body>
<script type="text/javascript">
<!--
	// preload status bar
	if(document.images){
		pre = new Image(100, 100);
		pre.src = 'img/progress.jpg';
	}
	// update status bar
	top.frames[2].location.href='status.php';
<?php
		if($this->scroll == TRUE)
		{
?>
	// add scroll point and submit form
	function submitForm(form)
	{
		var sy = document.createElement('input');
		sy.name = 'cl_gui_page_scroll_y';
		sy.type = 'hidden';
		if(typeof( window.pageYOffset) == 'number') 			//Netscape compliant
		{
			sy.value = window.pageYOffset;
		}
		else if(document.body && document.body.scrollTop) 	//DOM compliant
		{
			sy.value = document.body.scrollTop;
		}
		else if(document.documentElement && document.documentElement.scrollTop)
		{														//IE6 standards compliant mode
			sy.value = document.documentElement.scrollTop;
	  }
		form.appendChild(sy);
		form.submit();
	}

	// scroll if any scroll point set
	function scrollDown()
	{
		window.scrollBy(0,<?php echo $cl_gui_page_scroll_y; ?>);
	}
<?php
		}		// ET scroll-option
?>
	// close function for faq windows (hide)
	function closeFaq(id){      		
		if(document.getElementById("faq"+id).checked){
			top.frames[2].location.href='./controller.php?act=deactivateFaq&id='+id;
		}else{
			//top.frames[2].location.href='status.php';
		}
		document.getElementById("faqdiv"+id).style.visibility = "hidden";
		document.getElementById("faqifrm"+id).style.visibility = "hidden";
		//top.frames[2].location.href='status.php';
	}
//-->
</script>
<?php
	}


	/**
	 * printCover 
	 * ----------
	 * Sets up a cover page with basic meeting data.
	 */
	function printCover($type, $timing=true)
	{
		$result = mysql_query("
			SELECT m.Name
				, m.Ort
				, m.DatumVon
				, m.DatumBis
				, s.Name
				, DATE_FORMAT(m.DatumVon, '". $GLOBALS['cfgDBdateFormat'] . "')
				, DATE_FORMAT(m.DatumBis, '". $GLOBALS['cfgDBdateFormat'] . "')
			FROM
				meeting AS m
				LEFT JOIN stadion AS s ON (m.xStadion = s.xStadion)
			WHERE m.xMeeting = ". $_COOKIE['meeting_id']);

		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
			$row = mysql_fetch_row($result);
			$date = $row[5];
			if($row[2] != $row[3]) {		// more than one day
				$date = $date . " " . $GLOBALS['strDateTo'] . " ". $row[6];
			}

	$this->printSubtitle($type . " " . $row[0]);
?>
	<table class='dialog'>
		<tr>
			<th class='dialog'><?php echo $GLOBALS['strStadium']; ?></th>
			<td class='dialog'><?php echo $row[4].", ".$row[1]; ?></td>
		</tr>
		<tr>
			<th class='dialog'><?php echo $GLOBALS['strOrganizer']; ?></th>
			<td class='dialog'><?php echo $GLOBALS['cfgRankingOrganizer']; ?></td>
		</tr>
		<tr>
			<th class='dialog'><?php echo $GLOBALS['strDate']; ?></th>
			<td class='dialog'><?php echo $date; ?></td>
		</tr>
		<?php
		if($timing){
			?>
			<tr>
				<th class='dialog'><?php echo $GLOBALS['strTiming']; ?></th>
				<td class='dialog'><?php echo $GLOBALS['cfgRankingTiming']; ?></td>
			</tr>
			<?php
		}
		?>
	</table>
<?php
			mysql_free_result($result);
		}
	}


	function endPage()
	{
		$faq = new GUI_Faq();   
		$faq->showFaq($this->title);
        
?>
</body>
</html>
<?php
	}


	function printPageTitle($title)
	{
?>
<h1><?php echo $title; ?></h1>
<?php
	}


	function printSubTitle($subtitle, $discname)
	{
?>
<h2><?php echo $subtitle . " " . $discname; ?></h2>
<?php
	}

} // END CLASS Gui_Page


/********************************************
 *
 * CLASS GUI_ListPage
 *
 *	Base class to display simple HTML-lists
 *
 *******************************************/

class GUI_ListPage extends GUI_Page
{
	var $rowclass;

	// public functions
	// ----------------

	function GUI_ListPage($title='Defaulttitle')
	{
		$this->rowclass = array('even', 'odd');
		parent::GUI_Page($title);
	}

	/**
	 * startPage
	 * ---------
	 * Sets up the basic HTML-page frame for printing.
	 */
	function startPage()
	{
		?>
<body>
		<?php
	}


	/**
	 *	start a display list
	 */
	function startList()
	{
		?>
<table class='dialog'>
		<?php
	}

	/**
	 *	terminate a display list
	 */
	function endList()
	{
		?>
</table>
		<?php
	}


	/**
	 *	switch row's CSS-class 
	 */
	function switchRowClass()
	{
		$this->rowclass=array_reverse($this->rowclass);	// switch rowclass
	}
} // end GUI_ListPage



/********************************************
 * GUI_RankingList: simple ranking list
 *******************************************/
class GUI_RankingList extends GUI_ListPage
{
	var $points;
	var $relay;
	var $wind;


	function printSubTitle($category='', $discipline='', $round='', $info, $heatFrom = '', $heatTo = '', $roundType)
	{
		if(!empty($round)) {
			$round = ", $round";
		}
        if(!empty($info)) {  
            $info = "($info)";
        }
        $heat = ''; 
        if (!empty($heatFrom) && $roundType == 1){
            $heat = " , Serie $heatFrom bis $heatTo";
        }
?>
<h2><?php echo "$category $discipline $info $round $heat"; ?></h2>
<?php
	}


	function printCeremonyStatus($roundID, $status)
	{
		if($status == $GLOBALS['cfgSpeakerStatus']['ceremony_done']) {
			$checked = 'checked';
		}
		else {
			$checked = '';
		}
?>
<form action='controller.php' method='post'
	name='speakerstatus' target='controller'>
<table class='dialog'>
	<th class='dialog'>
		<?php echo $GLOBALS['strCeremonyDone']; ?>
		<input type='hidden' name='act' value='saveSpeakerStatus' />
		<input type='hidden' name='round' value='<?php echo $roundID; ?>' />
		<input type='hidden' name='status' value='<?php echo $GLOBALS['cfgSpeakerStatus']['ceremony_done'];?>' />
		<input type='checkbox' name='checked' <?php echo $checked;?>
			onClick="document.speakerstatus.submit()" />
	</th>
</table>
</form>
<?php
	}

	function printHeaderLine($title, $relay=FALSE, $points=FALSE
		, $wind=FALSE, $heatwind='', $time='', $svm = false, $base_perf = false, $qual_mode = false, $eval='', $withStartnr, $teamsm = false)
	{
		$this->relay = $relay;
		$this->wind = $wind;
		$this->points = $points;

		$span = 5;
		if($points == TRUE) {
			$span++;
		}
		if($relay == TRUE) {
			$span--;
		}
		if($wind == TRUE) {
			$span++;
		}
		
		if($base_perf == TRUE){
			$span++;
			$span++;
		}

		if($qual_mode == TRUE){
			$span++;
		}

		// print heat header if title set (results evaluated per heat)
		if(!empty($title))
		{
			if(empty($heatwind))
			{
				?>

	<tr>
		<th class='dialog' colspan='<?php echo 2 + $span; ?>'><?php echo $title; ?></th>
	</tr>
				<?php
			}
			else
			{
				?>
	<tr>
		<th class='dialog' colspan='<?php echo $span; ?>'><?php echo $title; ?></th>
		<th class='dialog' colspan='2'><?php echo $GLOBALS['strWind'] . ": $heatwind"; ?></th>
	</tr>
				<?php
			}
		}	// ET heat title set

		// print column headers
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strRank']; ?></th>   
        <?php
        if ($withStartnr && $this->relay == FALSE){
            ?>
           <th class='dialog'><?php echo $GLOBALS['strStNr']; ?></th> 
            <?php
        }
        ?>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<?php
		if($relay == FALSE)
		{
			?>
		<th class='dialog'><?php echo $GLOBALS['strYear']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strCountry']; ?></th>
			<?php
		}
		if($svm){
			?>
		<th class='dialog'><?php echo $GLOBALS['strTeam']; ?></th>
			<?php
		}
        elseif ($teamsm){
            ?>
        <th class='dialog'><?php echo $GLOBALS['strTeamsm']; ?></th>
            <?php
            
        }else{
			?>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
			<?php
		}
		?>
		<th class='dialog'><?php echo $GLOBALS['strPerformance']; ?></th>
		<?php
		if($wind == TRUE)
		{
			?>
		<th class='dialog'><?php echo $GLOBALS['strWind']; ?></th>
			<?php
		}
		if($points == TRUE)
		{
			?>
		<th class='dialog'><?php echo $GLOBALS['strPoints']; ?></th>
			<?php
		}
		
		if($qual_mode == TRUE){
			echo '<th class="dialog">&nbsp;</th>';
		}
		
		if($relay == FALSE && $base_perf == TRUE)
		{
			?>
		<th class='dialog'><?php echo $GLOBALS['strSB']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strPB']; ?></th>
			<?php
		}
		
		?>
        <th class='dialog'><?php echo $GLOBALS['strResultRemark']; ?></th>  
	</tr>
<?php
	}

	function printInfoLine($info,$athleteCat=false)
	{    
		if(!empty($info))
			{
			if ($athleteCat) {
?>
				<p><?php echo $info; ?></p> <p></p>
					
<?php
			}
			else {
?>
	  <table><tr>
			<td><?php echo $info; ?></td>
		</tr></table>
		<p/> 
		
<?php
			}
		}
	}

	function printLine($rank, $name, $year, $club, $perf
		, $wind, $points, $qual, $country, $sb="", $pb="", $qual_mode=false, $athleteCat='', $remark='', $secondResult = false, $withStartnr, $startnr )
	{              
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $rank; ?></td>
        <?php
        if ($withStartnr && $this->relay == FALSE){
            ?>
            <td class='forms_right'><?php echo $startnr; ?></td>  
            <?php
        }
        ?>
		<td><?php echo $name; ?></td>
		<?php
		if($this->relay == FALSE)
		{
			?>
		<td class='forms_ctr'><?php echo $year; ?></th>
		<td><?php echo $country; ?></td>
			<?php
		}
		?>
		<td><?php echo $club; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
		<?php
		if($this->wind == TRUE)
		{
			?>
		<td class='forms_right'><?php echo $wind; ?></th>
			<?php
		}
		if($this->points == TRUE)
		{
			?>
		<td class='forms_right'><?php echo $points; ?></th>
			<?php
		}

		if(!empty($qual)) {
			?>
		<td><?php echo $qual; ?></td>
			<?php
		} else {
			if ($qual_mode == TRUE){		
				echo "<td>&nbsp;</td>";
			}
		}
		
		if($this->relay == FALSE && !empty($sb)){
			?>
		<td class='forms_right'><?php echo $sb; ?></td>
			<?php	
		}
		
		if($this->relay == FALSE && !empty($pb)){
			?>
		<td class='forms_right'><?php echo $pb; ?></td>
			<?php	
		}		
		?>
        <td><?php echo $remark; ?></td>  
	</tr>
<?php
		//$this->switchRowClass();
	}
	
	function printAthletesLine($text){    
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'></td>
		<td colspan='8'><?php echo $text; ?></td>
	</tr>
<?php
		$this->switchRowClass();
	}
	
} // end GUI_RankingList



/********************************************
 * GUI_CombinedRankingList: ranking list for combined events 
 *******************************************/
class GUI_CombinedRankingList extends GUI_ListPage
{
	function printHeaderLine()
	{
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strRank']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strPoints']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strResultRemark']; ?></th> 
	</tr>
		<?php
	}


	function printLine($rank, $name, $year, $club, $points, $ioc='', $remark)
	{
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $rank; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_ctr'><?php echo $year; ?></td>
		<td><?php echo $club; ?></td>
		<td class='forms_right'><?php echo $points; ?></td>
        <td><?php echo $remark; ?></td> 
	</tr>
		<?php
	}


	function printInfo($info)
	{
		$this->linecnt = $this->linecnt + 2;	// increment line count
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td />
		<td class='disc' colspan='3'><?php echo $info; ?><br/></td>
		<td colspan='2'/>
	</tr>
		<?php
		$this->switchRowClass();
	}
} // end GUI_CombinedRankingList



/********************************************
 * GUI_TeamRankingList: ranking list for team events
 *******************************************/
 
class GUI_TeamRankingList extends GUI_ListPage
{

	function printHeaderLine()
	{
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strRank']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strPoints']; ?></th>             
	</tr>
		<?php
	}


	function printLine($rank, $name, $club, $points, $type)
	{   
		$this->switchRowClass();   
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $rank; ?></td>
		<td><?php echo $name; ?></td>
		<td><?php echo $club; ?></td>
		<td class='forms_right'><?php echo $points; ?></td>          
	</tr>
		<?php
	}


	function printAthleteLine($name, $year, $points, $country, $club, $rank, $type)
	{
		?>
	
		
        <?php
         if ($type == 'teamP'){
             ?>
             <tr class='<?php echo $this->rowclass[0]; ?>'>
             <td><?php echo $rank; ?></td>
             <td><?php echo "$name, $year"; ?></td> 
              <td><?php echo $club; ?></td>
              <td><?php echo $points; ?></td> 
              <?php
         }
         else {
             ?>
             <tr class='<?php echo $this->rowclass[0]; ?>'><td />
             <td><?php echo "$name, $year, $country"; ?></td>
             <td><?php echo $points; ?></td>
             <td />             
             <?php
         }
         ?>
		
	</tr>
		<?php
	}



	function printInfo($info)
	{
		$this->linecnt = $this->linecnt + 2;	// increment line count
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td />
		<td class='disc' colspan='2'><?php echo $info; ?><br/></td>
		<td />          
	</tr>
		<?php
	}

} // end GUI_TeamRankingList



/********************************************
 * GUI_TeamSheet: show team sheets
 *******************************************/

class GUI_TeamSheet extends GUI_ListPage
{

	function printHeader($team, $category)
	{
		parent::printSubTitle("$category: $team");
		parent::startList();
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strPerformance']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strWind']; ?></th>
		<th class='dialog' colspan='2'><?php echo $GLOBALS['strPoints']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strResultRemark']; ?></th>
	</tr>
<?php
	}


	function printHeaderCombined($team, $category)
	{
		parent::printSubTitle("$category: $team");
		parent::startList();
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strPoints']; ?></th> 
	</tr>
<?php
	}


	function printSubHeader($title)
	{
?>
	<tr>
		<th class='dialog' colspan='6'><?php echo $title; ?></th>
	</tr>
<?php
	}


	function printLine($disc, $name, $perf, $wind, $points, $total,$remark)
	{   
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td><?php echo $disc; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
		<td class='forms_right'><?php echo $wind; ?></td>
		<td class='forms_right'><?php echo $points; ?></td>
		<td class='forms_right'><?php echo $total; ?></td>
        <td><?php echo $remark; ?></td>  
	</tr>
<?php
		$this->switchRowClass();
	}


	function printLineCombined($name, $year, $points, $country)
	{
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td><?php echo "$name, $year, $country"; ?></td>
		<td class='forms_right'><?php echo $points; ?></td>
	</tr>
<?php
		//$this->switchRowClass();
	}


	function printDisciplinesCombined($disciplines)
	{
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='disc'><?php echo $disciplines; ?></td>
		<td />
	</tr>
<?php
		$this->switchRowClass();
	}


	function printRelayAthlete($name)
	{
		$this->switchRowClass();	// keep old style
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td />
		<td><?php echo $name; ?></td>
		<td/>
		<td/>
		<td/>
		<td/>
        <td/> 
	</tr>
<?php
		$this->switchRowClass();
	}


	function printSubTotal($total)
	{
?>
	<tr>
		<th colspan='4' />
		<th class='statistic_total'><?php echo $GLOBALS['strSubTotal']; ?></th>
		<th class='statistic_total'><?php echo $total; ?></th>
        <th/>  
	</tr>
<?php
	}



	function printTotal($total)
	{
?>
	<tr>
		<th colspan='4' />
		<th class='statistic_total'><?php echo $GLOBALS['strTotal']; ?></th>
		<th class='statistic_total'><?php echo $total; ?></th>
        <th/>   
	</tr>
<?php
		parent::endList();
	}


	function printTotalCombined($total)
	{
?>
	<tr>
		<th class='statistic_total'><?php echo $GLOBALS['strTotal']; ?></th>
		<th class='statistic_total'><?php echo $total; ?></th>
	</tr>
<?php
		parent::endList();
	}


} // end GUI_TeamSheet



/********************************************
 * GUI_TeamSMRankingList: ranking list for team sm events
 *******************************************/
 
class GUI_TeamSMRankingList extends GUI_ListPage
{

	function printHeaderLine()
	{
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strRank']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTeamsm']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strResult']; ?></th>
	</tr>
		<?php
	}


	function printLine($rank, $name, $club, $perf)
	{
		$this->switchRowClass();
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $rank; ?></td>
		<td><?php echo $name; ?></td>
		<td><?php echo $club; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
	</tr>
		<?php
	}


	function printAthleteLine($name, $year, $perf)
	{
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td />
		<td><?php echo "$name, $year"; ?></td>
		<td><?php echo $perf; ?></td>
		<td />
	</tr>
		<?php
	}



	function printInfo($info)
	{
		$this->linecnt = $this->linecnt + 2;	// increment line count
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td />
		<td class='disc' colspan='2'><?php echo $info; ?><br/></td>
		<td />
	</tr>
		<?php
	}

} // end GUI_TeamSMRankingList


/********************************************
 * GUI_Statistics:	meeting statistics
 *******************************************/

class GUI_Statistics extends GUI_ListPage
{
	
	function printHeaderLine($col1, $col2, $col3, $col4="", $col5="", $col6="")
	{ 
?>
	<tr>
		<th class='dialog'><?php echo $col1; ?></th>
		<th class='dialog'><?php echo $col2; ?></th>
		<th class='dialog'><?php echo $col3; ?></th>
		<?php
		if(!empty($col4) || $col4 == '0'){
			?>
		<th class='dialog'><?php echo $col4; ?></th>
			<?php
		}
		if(!empty($col5) || $col5 == '0'){
			?>
		<th class='dialog'><?php echo $col5; ?></th>
			<?php
		}
        if(!empty($col6) ){  
        ?>
        <th class='dialog'><?php echo $col6; ?></th> 
        <?php
        }
        ?>
	</tr>
<?php
		
	}


	function printLine($col1, $col2, $col3, $col4="", $col5="")
	{ 
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>    
        <td><?php echo $col1; ?></td> 
          
		<td class='forms_right'><?php echo $col2; ?></th>
		<td class='forms_right'><?php echo $col3; ?></th>
		<?php
		if(!empty($col4) || $col4 == '0' ){
			?>
		<td class='forms_right'><?php echo $col4; ?></td>
			<?php
		}      
		if(!empty($col5) || $col5 == '0' ){
			?>
		<td class='forms_right'><?php echo $col5; ?></td>
			<?php
		    }
        ?>
       
	</tr>
<?php
		$this->switchRowClass();
	}
       
function printLineTax($col1, $col2, $col3, $col4="", $col5="", $assTax='', $span=1)
    {
?>
    <tr class='<?php echo $this->rowclass[0]; ?>'>
     <?php 
     if (!empty($assTax) || $assTax == '0'){  
               ?>
               <td class='forms_intend'><?php echo $col1; ?></td>   
               <?php
           }
           else { 
              ?>
               <td ><strong><?php echo $col1; ?></strong></td>   
               <?php  
           }
           if ($span == 2){
               ?>
                <td class='forms' colspan='<?php echo $span; ?>'><?php echo $col2; ?></th>  
              <?php 
           }
           else {
        ?>
        <td class='forms_right_bold'><?php echo $col2; ?></th>
        <td class='forms_right_bold'><?php echo $col3; ?></th>
        <?php
           }
        if(!empty($col4) || $col4 == '0' || !empty($assTax) || $assTax == '0'){
            ?>
        <td class='forms_right_bold'><?php echo $col4; ?></td>
            <?php
        }
        if (!empty($assTax) || $assTax == '0'){
            if(!empty($col5) || $col5 == '0' ){
            ?>
        <td class='forms_right'><?php echo $col5; ?></td>
            <?php
            }
        }
        elseif  (!empty($col5) || $col5 == '0') {
                  ?>
                  <td class='forms_right_bold'><?php echo $col5; ?></td>
                  <?php  
        }
        
        if(!empty($assTax) || $assTax == '0'){
            ?>
            <td class='forms_right' colspan='<?php echo $span; ?>'><?php echo $assTax .".00"; ?></td>
            <?php
        }
        else {
            ?>
            <td class='forms_right'>&nbsp;</td>
            <?php
        }
        ?>
    </tr>
<?php
        $this->switchRowClass();
    }


    function printTotalLine($col1, $col2, $col3, $col4="", $col5="")
    {
?>
	<tr>
		<th class='statistic_total'><?php echo $col1; ?></th>
		<th class='statistic_total'><?php echo $col2; ?></th>
		<th class='statistic_total'><?php echo $col3; ?></th>
		<?php
		if(!empty($col4) || $col4 == '0'){
			?>
		<th class='statistic_total'><?php echo $col4; ?></th>
			<?php
		}
		if(!empty($col5) || $col5 == '0'){
			?>
		<th class='statistic_total' ><?php echo $col5; ?></th>
			<?php
		}
		?>
		 </tr>
<?php
    }
    
    function printTotalLineTax($col1, $col2, $col3, $col4="", $col5="",$span=1)
    {
?>
    <tr>
        <th class='statistic_total'><?php echo $col1; ?></th>
        <th class='statistic_total'><?php echo $col2; ?></th>
        <th class='statistic_total'><?php echo $col3; ?></th>
        <?php
        if(!empty($col4) || $col4 == '0'){
            ?>
        <th class='statistic_total'><?php echo $col4; ?></th>
            <?php
        }
        if(!empty($col5) || $col5 == '0'){
            ?>
        <th class='statistic_total' colspan='<?php echo $span; ?>'><?php if ($span == 1) {echo $col5;} else {echo $col5.".00";} ?></th>
            <?php
        }
        if ($span==1){
        ?>
        <th class='statistic_total' >&nbsp;</th>
           <?php
        }
        ?>
	</tr>
<?php
	}
} // end PRINT_Statistics


} // end AA_CL_GUI_PAGE_LIB_INCLUDED

?>
