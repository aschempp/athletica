<?php

if (!defined('AA_CL_PRINT_PAGE_LIB_INCLUDED'))
{
	define('AA_CL_PRINT_PAGE_LIB_INCLUDED', 1);           


	require('./lib/cl_gui_page.lib.php');


/********************************************
 *
 * PRINT_Page
 *
 *	Base class for printing documents
 *
 *******************************************/

class PRINT_Page extends GUI_Page
{
	var $lpp;		// Lines per page
	var $linecnt;	// Line counter
	var $pagenbr;	// current page number
	// vars for header and footer standard
	var $stdHeaderRight, $stdHeaderLeft, $stdHeaderCenter;
	var $stdFooterLeft, $stdFooterCenter, $stdFooterRight;
	var $picHeaderLeft, $picHeaderCenter, $picHeaderRight;
	var $picFooterLeft, $picFooterCenter, $picFooterRight;
	var $orga; // organiser

	// public functions
	// ----------------

	function PRINT_Page($title='Defaulttitle')
	{            
		$this->title = $title;
		$this->stylesheet = "printing.css";
		$this->lpp = 0;			// lines per page
		$this->linecnt = 0;		// actual nbr of lines per page
		$this->setHeaderAndFooter();
		$this->printHTMLHeader();
		$this->startPage();
	}

	/**
	 * startPage
	 * ---------
	 * Sets up the basic HTML-page frame for printing.
	 */
	function startPage()
	{
		global $cfgPageContentHeight;    
		
		$this->lpp = $GLOBALS['cfgPrtLinesPerPage'];		// lines per page
		?>
<body onLoad="printPage()">

<script type="text/javascript">
<!--
	function printPage()
	{
		window.print();
		//window.close();
	}
// -->
</script>

<?php $this->printPageHeader() ?>
              
<div style="height:<?php echo $cfgPageContentHeight ?>mm;"> <!-- content div will position the footer on bottom -->
<table class='frame'>
<tr class='frame'>
	<td class='frame'>
		<?php
	}


	/**
	 * printCover 
	 * ----------
	 * Sets up a cover page with basic meeting data.
	 */

	function printCover($type, $timing=true)
	{
		$sql = "SELECT m.Name, 
					   m.Ort, 
					   m.DatumVon, 
					   m.DatumBis, 
					   m.Nummer, 
					   s.Name As StadionName, 
					   DATE_FORMAT(m.DatumVon, '". $GLOBALS['cfgDBdateFormat'] . "') AS von, 
					   DATE_FORMAT(m.DatumBis, '". $GLOBALS['cfgDBdateFormat'] . "') AS bis, 
					   m.Organisator, 
					   m.Zeitmessung, 
					   z.OMEGA_Sponsor, 
					   z.ALGE_Typ
				  FROM meeting AS m 
			 LEFT JOIN stadion AS s USING(xStadion) 
			 LEFT JOIN zeitmessung AS z ON(m.xMeeting = z.xMeeting) 
				 WHERE m.xMeeting = ".$_COOKIE['meeting_id'].";";
		$result = mysql_query($sql);

		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
			$row = mysql_fetch_assoc($result);
			$date = $row['von'];
			if($row['DatumVon'] != $row['DatumBis']) {		// more than one day
				$date = $date . " " . $GLOBALS['strDateTo'] . " ". $row['bis'];
			}
?>
	<table>
		<tr>
			<td class='cover_title'><?php echo $row['Name']; ?></td>
		</tr>
		<tr>
			<td class='cover_title'><?php echo $type; ?></td>
		</tr>

	</table>
	<table>
		<tr>
			<td class='cover_param'><?php echo $GLOBALS['strStadium']; ?></td>
			<td class='cover_value'><?php echo $row['StadionName'].", ".$row['Ort']; ?></td>
		</tr>
		<tr>
			<td class='cover_param'><?php echo $GLOBALS['strOrganizer']; ?></td>
			<td class='cover_value'><?php echo $row['Organisator']; ?></td>
		</tr>
		<tr>
			<td class='cover_param'><?php echo $GLOBALS['strDate']; ?></td>
			<td class='cover_value'><?php echo $date; ?></td>
		</tr>
		<tr>
			<td class='cover_param'><?php echo $GLOBALS['strMeetingNbr']; ?></td>
			<td class='cover_value'><?php echo $row['Nummer']; ?></td>
		</tr>
		<?php
		if($timing){
			$str = "";        
			if($row['Zeitmessung']=='alge'){
				$str = 'ALGE '.$row['ALGE_Typ'];
			} 
            elseif ($row['Zeitmessung']=='omega'){
				$str = 'OMEGA ('.$row['OMEGA_Sponsor'].')';
			}
             else {
                   $str = $GLOBALS['strNoTiming'];   
             }
			?>
			<tr>
				<td class='cover_param'><?php echo $GLOBALS['strTiming']; ?></td>
				<td class='cover_value'><?php echo $str;?></td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td class='cover_slogan' colspan='2'>
				<?php echo $GLOBALS['strSlogan'] . " "
				. $GLOBALS['cfgApplicationName'] . " "
				. $GLOBALS['cfgApplicationVersion']; ?>
			</td>
		</tr>
	</table>
<?php
			mysql_free_result($result);
			$this->insertPageBreak();
		}
	}


	/**
	 * endPage
	 * -------
	 * Terminates the basic HTML-page frame.
	 */
	function endPage()
	{  
?>
	</td>
</tr>
</table>
</div>

<?php $this->printPageFooter() ?>

<!-- script for setting the number of total pages -->
<script type="text/javascript">

	var pages = <?php echo $this->pagenbr ?>;
	
	for(var i = 1; i<=pages; i++){
		var e = document.getElementById("totalpages_"+i);
		var t = e.firstChild;
		t.nodeValue = pages;
	}

</script>
</body>
</html>
<?php
	}
	
	
	/**
	 * insertPageBreak
	 * ---------------
	 * Terminates layout table and inserts a page break (printing).
	 */
	function insertPageBreak()
	{  
		global $cfgPageContentHeight;           
?>
	</td>
</tr>
</table>
</div>

<?php $this->printPageFooter() ?>

<br style='page-break-after:always' />

<?php $this->printPageHeader() ?>
		
<div style="height:<?php echo $cfgPageContentHeight ?>mm;">
<table class='frame'>
<tr class='frame'>
	<td class='frame'>
<?php
		$this->linecnt = 0;      		
	}

	/**
	 * printDocTitle
	 * -------------
	 * Print the document title.
	 */
	function printPageTitle($title)
	{   
		$this->linecnt = $this->linecnt + 2;	// needs 2 lines (see style sheet)
?>
		<div class='hdr1'><?php echo $title; ?></div>
<?php
	}


	function printSubTitle($title)
	{
		// page break check (at least one further line left)
		if(($this->lpp - $this->linecnt) < 3)		
		{
			$this->insertPageBreak();
		}
		$this->linecnt = $this->linecnt + 2;	// needs two lines (see style sheet)
?>
		<div class='hdr2'><?php echo $title; ?></div>
<?php
	}

	function startList()
	{   
		printf("<table>");   
	}


	function endList()
	{
		printf("</table>");
	}


	function printHeaderLine($text)
	{
		if(($this->lpp - $this->linecnt) < 1)		// page break check
		{
			$this->insertPageBreak();

		}
		$this->linecnt++;			// increment line count
?>
		<tr>
			<th><?php echo $text; ?></th>
		</tr>
		<tr>
			<td><hr /></td>
		</tr>
<?php
	}

	function printLine($text)
	{
		if(($this->lpp - $this->linecnt) < 1)		// page break check
		{
			$this->insertPageBreak();

		}
		$this->linecnt++;			// increment line count

		printf("<tr>\n");
		printf("<td>$text</td>");
		printf("</tr>\n");
	}
	
	function setHeaderAndFooter(){
		global $cfgApplicationName, $cfgApplicationVersion;
		global $strPage, $strOf;
		
		// get organiser
		$res = mysql_query("SELECT Organisator FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			$row = mysql_fetch_array($res);
			$this->orga = $row[0];
		}
		mysql_free_result($res);
		
		// get settings for pagelayout
		$res = mysql_query("SELECT * FROM layout WHERE xMeeting = ".$_COOKIE['meeting_id']);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			if(mysql_num_rows($res) == 0){		// no page layout defined, standard definitions
				
				$this->stdHeaderLeft = $_COOKIE['meeting'];
				$this->stdHeaderRight = date('d.m.Y H:i');
				$this->stdFooterLeft = "created by $cfgApplicationName $cfgApplicationVersion";
				$this->stdFooterCenter = $this->orga;
				$this->stdFooterRight = "$strPage !pn! ".strtolower($strOf)." <span id=\"totalpages_!pn!\">XY</span>";
				$this->picHeaderCenter = "<img src=\"layout/athletica-logo.png\" alt=\"\" height=\"30\" >";
				
			}else{					// layout defined
				
				$row = mysql_fetch_assoc($res);
				$this->defineHeaderAndFooter($this->stdHeaderLeft, $this->picHeaderLeft, $row['TypTL'], $row['TextTL'], $row['BildTL']);
				$this->defineHeaderAndFooter($this->stdHeaderCenter, $this->picHeaderCenter, $row['TypTC'], $row['TextTC'], $row['BildTC']);
				$this->defineHeaderAndFooter($this->stdHeaderRight, $this->picHeaderRight, $row['TypTR'], $row['TextTR'], $row['BildTR']);
				$this->defineHeaderAndFooter($this->stdFooterLeft, $this->picFooterLeft, $row['TypBL'], $row['TextBL'], $row['BildBL']);
				$this->defineHeaderAndFooter($this->stdFooterCenter, $this->picFooterCenter, $row['TypBC'], $row['TextBC'], $row['BildBC']);
				$this->defineHeaderAndFooter($this->stdFooterRight, $this->picFooterRight, $row['TypBR'], $row['TextBR'], $row['BildBR']);
				
			}
		}
	}
	
	function defineHeaderAndFooter(&$txt, &$pic, $type, $text, $picture){
		global $cfgApplicationName, $cfgApplicationVersion;
		global $strPage, $strOf;
		
		switch($type){
			case 0:
				$txt = "$strPage !pn! ".strtolower($strOf)." <span id=\"totalpages_!pn!\">XY</span>";
				// !pn! will be replaces by the current page number
				break;
			case 1:
				$txt = $_COOKIE['meeting'];
				break;
			case 2:
				$txt = $this->orga;
				break;
			case 3:
				$txt = date('d.m.Y H:i');
				break;
			case 4:
				$txt = "created by $cfgApplicationName $cfgApplicationVersion";
				break;
			case 5:
				$txt = $text;
				break;
			case 6:
				$txt = "";
				break;
		}
		if(!empty($picture)){
			$pic = "<img src=\"layout/$picture\" alt=\"\" height=\"30\" >";
		}
	}
	
	function printPageHeader(){
		$this->pagenbr++;            
		
		?>
<div style="position:relative; top:0mm; left:15px;">
<div style="position:absolute; top:0px; left:0px;">
<table class='page_header'>
	<tr>
		<td class="page_header_left">
			<?php echo $this->picHeaderLeft ?>
		</td>
		<td class="page_header_center">
			<?php echo $this->picHeaderCenter ?>
		</td>
		<td class="page_header_right">
			<?php echo $this->picHeaderRight ?>
		</td>
	</tr>
</table>
</div>
<div style="position:relative; top:0px; left:0px;">
<table class='page_header'>
	<tr>
		<td class="page_header_left">
			<?php echo str_replace('!pn!', $this->pagenbr, $this->stdHeaderLeft) ?>
		</td>
		<td class="page_header_center">
			<?php echo str_replace('!pn!', $this->pagenbr, $this->stdHeaderCenter) ?>
		</td>
		<td class="page_header_right">
			<?php echo str_replace('!pn!', $this->pagenbr, $this->stdHeaderRight) ?>
		</td>
	</tr>
</table>
</div>
</div>
		<?php
	}
	
	function printPageFooter(){     
       
		?>
<div style="position:relative; top:0mm; left:15px;">
<div style="position:absolute; top:0px; left:0px;">
<table class='page_footer'>
	<tr>
		<td class="page_footer_left">
			<?php echo $this->picFooterLeft ?>
		</td>
		<td class="page_footer_center">
			<?php echo $this->picFooterCenter ?>
		</td>
		<td class="page_footer_right">
			<?php echo $this->picFooterRight ?>
		</td>
	</tr>
</table>
</div>
<div style="position:absolute; top:0px; left:0px;">
<table class='page_footer'>
	<tr>
		<td class="page_footer_left">
			<?php echo str_replace('!pn!', $this->pagenbr, $this->stdFooterLeft) ?>
		</td>
		<td class="page_footer_center">
			<?php echo str_replace('!pn!', $this->pagenbr, $this->stdFooterCenter) ?>
		</td>
		<td class="page_footer_right">
			<?php echo str_replace('!pn!', $this->pagenbr, $this->stdFooterRight) ?>
		</td>
	</tr>
</table>
</div>
</div>
		<?php
	}
} // end PRINT_Page



/********************************************
 * Print_Definitions: printing meeting definitions
 *******************************************/

class PRINT_Definitions extends PRINT_Page
{
	function printLine($disc)
	{
		if(($this->lpp - $this->linecnt) < 1)		// page break check
		{
			$this->insertPageBreak();

		}
		$this->linecnt++;			// increment line count

		printf("<table><tr>\n");
		printf("<td>$disc</td>");
		printf("</tr></table>\n");
	}

} // end PRINT_Definitions




/********************************************
 * PRINT_RankingList: printing ranking list for single events
 *******************************************/

class PRINT_RankingList extends PRINT_Page
{
	var $cat;			// current category
	var $disc;			// current discipline
	var $relay;			// current relay status
	var $round;			// current round
	var $points;		// current point info
	var $wind;			// current wind info
	var $title;			// heat title
	var $heatwind;		// wind per heat


	function printSubTitle($category='', $discipline='', $round='', $info)
	{  
		if(!empty($category)) {
			$this->cat = $category;
		}
		if(!empty($discipline)) {
			$this->disc = $discipline;
		}
		if(!empty($round)) {
			$this->round = $round;
		}
		else {
			$this->round = '';
		}
	
		if(empty($category)) { 
			$this->round = $this->round . " " . $GLOBALS['strCont'];         
		}
        
         if(!empty($info)) {  
            $info = "($info)";
        }

		if(($this->lpp - $this->linecnt) < 12)		// page break check
		{   
		   	printf("</table>");      
			$this->insertPageBreak();
		   	printf("<table class='rank'>");       
			
		}
		$this->linecnt = $this->linecnt + 3;	// needs 3 lines (see style sheet)
?>
		<table><tr>   
                <th class='rank_cat'><?php echo $this->cat; ?></th>  
                <th class='rank_disc'><?php echo $this->disc ." " . $info; ?></th>  
			    <th class='rank_round'><?php echo $this->round; ?></th>
		</tr></table>
<?php 
	}


	function printInfoLine($info)
	{ 
		if(!empty($info))
		{   
			$this->linecnt = $this->linecnt++;
?>
		<table><tr>
			<td><?php echo $info; ?></td>
		</tr></table>
<?php
		}
	}


	function printHeaderLine($title, $relay=FALSE, $points=FALSE
		, $wind=FALSE, $heatwind='', $time="", $svm = false, $base_perf = false, $qual_mode = false , $eval, $withStartnr, $teamsm = false)
	{   
		$this->relay = $relay;
		$this->points = $points;
		$this->wind = $wind;
		$this->heatwind = $heatwind;
		$this->title = $title;
	
		$lines = 0;
		if(!empty($this->title)) {
			$lines = 2;	
		}        
        
		if(($this->lpp - $this->linecnt) < ($lines+7))		// page break check
		{  
		   	printf("</table>");
			$this->insertPageBreak();
		   	printf("<table class='rank'>");
		}

		if(!empty($this->title))
		{  
?>
	<tr>
		<th class='rank_heat' colspan='2'><?php echo $this->title; ?></td>
<?php
			if(!empty($this->heatwind)) {
?>
		<th class='rank_heatwind' colspan='2'>
			<?php echo $GLOBALS['strWind'] . ": " .$this->heatwind; ?>
		</th>
<?php
			}else{
?>
		<th class='rank_heatwind' colspan='2'>
		</th>
<?php
			}
			
			if(!empty($time)){
?>
		<th class='rank_datetime' colspan='6'>
			<?php echo $time; ?>
		</th>
	</tr>
<?php
            $this->linecnt = $this->linecnt + $lines + 1;    // increment line count
			}
		}
        elseif ($eval == $cfgEvalType[$strEvalTypeDiscDefault]){
                if(!empty($time)){
?>
                    <tr>  
                        <th class='rank_datetime' colspan='10'>
                        <?php echo $time; ?>
                        </th>
                    </tr>
<?php
            $this->linecnt = $this->linecnt + $lines + 1;    // increment line count
            }
        }

		

		$year = '';
		$ioc = '';
		if($this->relay == FALSE) {
			$year = $GLOBALS['strYearShort'];
			$ioc = $GLOBALS['strIocShort'];
		}

		$points = '';
		if($this->points == TRUE) {
			$points = $GLOBALS['strPoints'];
		}
		
		$wind = '';
		if($this->wind == TRUE) {
			$wind = $GLOBALS['strWind'];
		}
?>
	<tr>
		<th class='rank_rank'><?php echo $GLOBALS['strRank']; ?></th>
         <?php
        if ($withStartnr && $this->relay == FALSE){
            ?>
           <th class='rank_stnr'><?php echo $GLOBALS['strStNr']; ?></th> 
            <?php
        }
        ?>
		<th class='rank_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='rank_year'><?php echo $year; ?></th>
		<?php
		if($svm){
			?>
		<th class='rank_club'><?php echo $GLOBALS['strTeam']; ?></th>
			<?php
        }elseif ($teamsm){
            ?>
        <th class='rank_club'><?php echo $GLOBALS['strTeamsm']; ?></th>
            <?php        
		}else{
			?>
		<th class='rank_club'><?php echo $GLOBALS['strClub']; ?></th>
			<?php
		}
		?>
		<th class='rank_year'><?php echo $ioc; ?></th>
		<th class='rank_perf'><?php echo $GLOBALS['strPerformance']; ?></th>
		<th class='rank_qual' />
		<th class='rank_wind'><?php echo $wind; ?></th>
		<th class='rank_points'><?php echo $points; ?></th>
        <th class='rank_remark'><?php echo $GLOBALS['strResultRemarkShort']; ?></th>
	</tr>
<?php
	}


	function startList()
	{   
		printf("<table class='rank'>");   
	}


	function printLine($rank, $name, $year, $club, $perf
		, $wind, $points, $qual, $ioc, $sb="", $pb="", $qual_mode=false, $athleteCat='', $remark, $secondResult=false, $withStartnr, $startnr)
	{    
        if (!$secondResult){
        if(($this->lpp - $this->linecnt) < 5)		// page break check
		{   
			printf("</table>");			   			
			$this->insertPageBreak(); 
			$this->printSubTitle();  			 	
			printf("<table class='rank'>");  
			$this->printHeaderLine('', $this->relay, $this->points, $this->wind, '', '', false, false, false, '', $withStartnr);          
		}   
       
        // count more lines if string is to long (club string) 
       
        $t1 = 0;
        $w = AA_getStringWidth($club, 12);  
        $t1 = ceil(($w / 157));         
		$this->linecnt = $this->linecnt + $t1;			// increment line count
        }
     
      if ($secondResult){
          $this->linecnt++;  
        } 
	  	
?>
	<tr>
		<td class='rank_rank'><?php echo $rank; ?></td>
         <?php
        if ($withStartnr && $this->relay == FALSE){
            ?>
           <td class='rank_stnr'><?php echo $startnr; ?></td>  
            <?php
        }
        ?>
		<td class='rank_name'><?php echo $name; ?></td>
		<td class='rank_year'><?php echo $year; ?></td>
		<td class='rank_club'><?php echo $club; ?></td>
		<td class='rank_year'><?php echo $ioc; ?></td>
		<td class='rank_perf'><?php echo $perf; ?></td>
		<td class='rank_qual'><?php echo $qual; ?></td>
		<td class='rank_wind'><?php echo $wind; ?></td>
        <?php            
        if ($this->points == TRUE) {
            ?>
		    <td class='rank_points'><?php echo $points; ?></td>
            <?php
       }
        ?>
        <td class='rank_remark'><?php echo $remark; ?></td>   
	</tr>

<?php
	}
	
	function printAthletesLine($text){ 
           $textWidth=AA_getStringWidth($text,12); 
           $countLine=ceil(($textWidth/620));           // calculate lines of disziplines   
           $this->linecnt+=$countLine;    
?>
	<tr>
		<td class='rank_attempts'></td>
		<td class='rank_attempts' rank_name colspan="8"><?php echo $text; ?></td>
	</tr>
<?php
	}

} // end PRINT_RankingList

/********************************************
 * PRINT_CombingedRankingList: printing ranking list for combined events
 *******************************************/

class PRINT_CombinedRankingList extends PRINT_RankingList
{

	function printHeaderLine($time = "")
	{ 
		$lines = 0;
        if(!empty($time)){ 
           $lines = 2;  
        }
		if(($this->lpp - $this->linecnt) < ($lines+4))		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='rank'>");
		}
		if(!empty($time)){
            
?>
	<tr>
		<th class='comb_datetime' colspan='6'>
			<?php echo $time; ?>
		</th>
	</tr>
<?php
		}
		$this->linecnt = $this->linecnt + $lines + 1;	// increment line count
?>
	<tr>
		<th class='comb_rank'><?php echo $GLOBALS['strRank']; ?></th>
		<th class='comb_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='comb_year'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='comb_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='comb_year'><?php echo $GLOBALS['strIocShort']; ?></th>
		<th class='comb_points'><?php echo $GLOBALS['strPoints']; ?></th>
        <th class='comb_remark'><?php echo $GLOBALS['strResultRemarkShort']; ?></th> 
	</tr>
<?php
	}
  
    function printSubTitle($category='')
    {  
        if(!empty($category)) {
            $this->cat = $category;
        }   

        if(($this->lpp - $this->linecnt) < 8)        // page break check
        {   
               printf("</table>");      
            $this->insertPageBreak();
               printf("<table class='rank'>");       
            
        }
        $this->linecnt = $this->linecnt + 2;    // needs 2 lines (see style sheet)
?>
        <table width="100%"><tr>
            <th class='rank_cat'><?php echo $this->cat; ?></th>
            
        </tr></table>
<?php 
    }
    
   
	function printLine($rank, $name, $year, $club, $points, $ioc, $remark)
	{   
		if(($this->lpp - $this->linecnt) < 4)		// page break check
		{   
			printf("</table>");
			$this->insertPageBreak();
			$this->printSubTitle();
			printf("<table class='rank'>");
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		$this->linecnt++;			// increment line count
		$pt = '';
		if(!empty($rank)) {
			$pt = ".";
		}
?>
	<tr>
		<td class='comb_rank'><?php echo $rank . $pt; ?></td>
		<td class='comb_name'><?php echo $name; ?></td>
		<td class='comb_year'><?php echo $year; ?></td>
		<td class='comb_club'><?php echo $club; ?></td>
		<td class='comb_year'><?php echo $ioc; ?></td>
		<td class='comb_points'><?php echo $points; ?></td>
        <td class='comb_remark'><?php echo $remark; ?></td>  
	</tr>

<?php
	}


	function printInfo($info)
	{   $textWidth=AA_getStringWidth($info,12);          
        $countLine=ceil(($textWidth/754));             // calculate lines of disziplines    
        $this->linecnt+=$countLine;  
          
?>
	<tr>
		<td />
		<td class='comb_info' colspan='3'><?php echo $info; ?><br/></td>
		<td />
	</tr>

<?php
	}

} // end PRINT_CombinedRankingList


/********************************************
 * PRINT_TeamRankingList: printing ranking list for svm team events
 *******************************************/

class PRINT_TeamRankingList extends PRINT_RankingList
{

	function printHeaderLine($time = "")
	{
		$lines = 0;
        if(!empty($time)){
           $lines = 1;   
        }
		if(($this->lpp - $this->linecnt) < ($lines+4))		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='rank'>");
		}
		if(!empty($time)){
?>
	<tr>
		<th class='comb_datetime' colspan='4'>
			<?php echo $time; ?>
		</th>
	</tr>
<?php
		}
		$this->linecnt = $this->linecnt + $lines + 2;	// increment line count
?>
	<tr>
		<th class='team_rank'><?php echo $GLOBALS['strRank']; ?></th>
		<th class='team_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='team_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='team_points'><?php echo $GLOBALS['strPoints']; ?></th>
	</tr>
<?php
	}


	function printLine($rank, $name, $club, $points)
	{
		if(($this->lpp - $this->linecnt) < 5)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			$this->printSubTitle();
			printf("<table class='rank'>");
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		$this->linecnt++;			// increment line count
		$pt = '';
		if(!empty($rank)) {
			$pt = ".";
		}
?>
	<tr>
		<td class='team_rank'><?php echo $rank . $pt; ?></td>
		<td class='team_name'><?php echo $name; ?></td>
		<td class='team_club'><?php echo $club; ?></td>
		<td class='team_points'><?php echo $points; ?></td>
	</tr>

<?php
	}


	function printAthleteLine($name, $year, $points, $country, $club, $rank, $type)
	{
		if(($this->lpp - $this->linecnt) < 4)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			$this->printSubTitle();
			printf("<table class='rank'>");
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		$this->linecnt++;			// increment line count
    if ($type == 'teamP'){
             ?>
        <tr>
            <td class='team_rank'><?php echo $rank; ?></td>
            <td class='team_name'><?php echo "$name, $year, $country"; ?></td>
            <td class='team_club'><?php echo $club; ?></td>
            <td class='team_points' ><?php echo $points; ?></td>
        </tr>
             <?php
    }
    else {
       ?>
       <tr>
        <td class='team_rank' />
        <td class='team_name'><?php echo "$name, $year, $country"; ?></td>
        <td class='team_club'><?php echo $points; ?></td>
        <td class='team_points' />
    </tr>
    <?php 
    }

	}


	function printInfo($info)
	{
		$this->linecnt = $this->linecnt + 1;	// increment line count
?>
	<tr>
		<td />
		<td class='team_info' colspan='2'><?php echo $info; ?><br/></td>
		<td />
	</tr>

<?php
	}

} // end PRINT_TeamRankingList



/********************************************
 * PRINT_TeamSheet: printing team sheets
 *******************************************/

class PRINT_TeamSheet extends PRINT_Page
{

	function printHeader($team, $category, $competitors)
	{
		$this->linecnt = $this->linecnt + 7;
?>
<table class='sheet'>
	<tr>
		<td class='sheet_title' colspan='6'><?php echo $GLOBALS['strTeamRankingTitle']; ?></td>
	</tr>
<?php

		$result = mysql_query("
			SELECT m.Ort
				, s.Name
				, DATE_FORMAT(m.DatumBis, '" . $GLOBALS['cfgDBdateFormat'] . "')
			FROM
				meeting AS m
				LEFT JOIN stadion AS s ON (m.xStadion = s.xStadion)
			WHERE 
                m.xMeeting = ". $_COOKIE['meeting_id']);

		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
			$row = mysql_fetch_row($result);
			$place = $row[0];
			$stadium = $row[1];
			$date = $row[2];
			mysql_free_result($result);
		}
?>
	<tr>
		<td class='sheet_col1'><?php echo $GLOBALS['strPlace'] . ", " . $GLOBALS['strStadium']; ?></td>
		<td class='sheet_col2'><?php echo $place . ", " . $stadium; ?></td>
		<td class='sheet_col3'><?php echo $GLOBALS['strDate']; ?></td>
		<td class='sheet_col4' colspan='4'><?php echo $date; ?></td>
	</tr>
	<tr>
		<td class='sheet_col1'><?php echo $GLOBALS['strClub']; ?></td>
		<td class='sheet_team'><?php echo $team; ?></td>
		<td class='sheet_col3'><?php echo $GLOBALS['strCategory']; ?></td>
		<td class='sheet_cat' colspan='4'><?php echo $category; ?></td>
	</tr>
	<tr>
		<td class='sheet_col1'><?php echo $GLOBALS['strCompetition']; ?></td>
		<td class='sheet_col2' colspan='6'><?php echo $competitors; ?></td>
	</tr>
	<tr>
		<td colspan='7'><hr class='sep'/></td>
	</tr>
<?php
		$this->linecnt = $this->linecnt + 3;			// increment line count
	}


	function printSubHeader($title)
	{
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='sheet'>");
		}
		$this->linecnt++;			// increment line count
?>
	<tr>
		<td class='sheet_subheader' colspan='6'><?php echo $title ?></td>
	</tr>
	<tr>
		<td colspan='6'><hr class='line'/></td>
	</tr>
<?php
	}



	function printLine($disc, $name, $perf, $wind, $points, $total, $remark)
	{
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='sheet'>");
		}
		$this->linecnt++;			// increment line count
?>
	<tr>
		<td class='sheet_disc'><?php echo $disc; ?></td>
		<td class='sheet_name'><?php echo $name; ?></td>
		<td class='sheet_perf'><?php echo $perf; ?></td>
		<td class='sheet_wind'><?php echo $wind; ?></td>
       <td class='sheet_pts'><?php echo $points; ?></td>
		<td class='sheet_remark'><?php echo $remark; ?></td>
		<td class='sheet_tot'><?php echo $total; ?></td>
	</tr>
<?php
	}


	function printLineCombined($name, $year, $points, $country)
	{
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='sheet'>");
		}
		$this->linecnt++;			// increment line count
?>
	<tr>
		<td class='sheet_athlete'><?php echo $name; ?></td>
		<td class='sheet_year'><?php echo $year; ?></td>
        <td class='sheet_country'><?php echo $country; ?></td> 
		<td colspan='2' />
		<td class='sheet_points'><?php echo $points; ?></td>
	</tr>
<?php
	}


	function printDisciplinesCombined($disciplines)
	{
		$this->linecnt++;			// increment line count
?>
	<tr>
		<td class='sheet_info' colspan='3'><?php echo $disciplines; ?></td>
	</tr>
<?php
	}


	function printRelayAthlete($name)
	{
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='sheet'>");
		}
		$this->linecnt++;			// increment line count
?>
	<tr>
		<td class='sheet_relay_athlete' colspan='6'><?php echo $name; ?></td>
	</tr>
<?php
	}


	function printSubTotal($total)
	{
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='sheet'>");
		}
?>
	<tr>
		<td colspan='4' />
		<td class='sheet_sep' colspan='2'><hr class='line'/></td>
	</tr>
	<tr>
		<td colspan='4' />
		<td class='sheet_total'><?php echo $GLOBALS['strSubTotal']; ?></td>
		<td class='sheet_totalpts'><?php echo $total; ?></td>
	</tr>
<?php
		$this->linecnt = $this->linecnt + 3;			// increment line count
	}


	function printTotal($total)
	{
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='sheet'>");
		}
?>
	<tr>
		<td colspan='4' />
		<td class='sheet_sep' colspan='2'><hr class='line'/></td>
	</tr>
	<tr>
		<td colspan='4' />
		<td class='sheet_total'><?php echo $GLOBALS['strTotal']; ?></td>
		<td class='sheet_totalpts'><?php echo $total; ?></td>
	</tr>
	<tr>
		<td colspan='4' />
		<td class='sheet_sep' colspan='2'><hr class='sep'/></td>
	</tr>
<?php
		$this->linecnt = $this->linecnt + 3;			// increment line count
	}


	function printFooter()
	{   
		$this->linecnt = $this->linecnt + 12;
?>
	<tr>
		<td colspan='6'><hr class='sep'/></td>
	</tr>
	<tr>
		<td colspan='6'><?php echo $GLOBALS['strTeamRankingDeclaration']; ?><br /></td>
	</tr>
	<tr>
		<td class='sheet_disc' colspan='2'>
			<?php echo $GLOBALS['strJudges'] . ":"; ?></td>
		<td class='sheet_perf' colspan='4'>
			<?php echo $GLOBALS['strTeamLeaders'] . ":"; ?></td>
	</tr>
	<tr>
		<td colspan='6'><br/><br/><hr class='line'/></td>
	</tr>
	<tr>
		<td colspan='6'><br/><br/><hr class='line'/></td>
	</tr>
	<tr>
		<td colspan='6'><br/><br/><hr class='line'/></td>
	</tr>
</table>
<?php
	}
} // end PRINT_TeamSheet



/********************************************
 * PRINT_TeamSMRankingList: printing ranking list for team sm events
 *******************************************/

class PRINT_TeamSMRankingList extends PRINT_RankingList
{

	function printHeaderLine($time = "")
	{
		$lines = 0;
		if(($this->lpp - $this->linecnt) < ($lines+3))		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table class='rank'>");
		}
		if(!empty($time)){
?>
	<tr>
		<th class='comb_datetime' colspan='4'>
			<?php echo $time; ?>
		</th>
	</tr>
<?php
		}
		$this->linecnt = $this->linecnt + $lines + 2;	// increment line count
?>
	<tr>
		<th class='team_rank'><?php echo $GLOBALS['strRank']; ?></th>
		<th class='team_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='team_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='team_points'><?php echo $GLOBALS['strResult']; ?></th>
	</tr>
<?php
	}


	function printLine($rank, $name, $club, $perf)
	{
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			$this->printSubTitle();
			printf("<table class='rank'>");
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		$this->linecnt++;			// increment line count
		$pt = '';
		if(!empty($rank)) {
			$pt = ".";
		}
?>
	<tr>
		<td class='team_rank'><?php echo $rank . $pt; ?></td>
		<td class='team_name'><?php echo $name; ?></td>
		<td class='team_club'><?php echo $club; ?></td>
		<td class='team_points'><?php echo $perf; ?></td>
	</tr>

<?php
	}


	function printAthleteLine($name, $year, $perf)
	{
		if(($this->lpp - $this->linecnt) < 2)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			$this->printSubTitle();
			printf("<table class='rank'>");
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		$this->linecnt++;			// increment line count
?>
	<tr>
		<td class='team_rank' />
		<td class='team_name'><?php echo "$name, $year"; ?></td>
		<td class='team_club'><?php echo $perf; ?></td>
		<td class='team_points' />
	</tr>

<?php
	}


	function printInfo($info)
	{
		$this->linecnt = $this->linecnt + 2;	// increment line count
?>
	<tr>
		<td />
		<td class='team_info' colspan='2'><?php echo $info; ?><br/></td>
		<td />
	</tr>

<?php
	}

} // end PRINT_TeamSMRankingList



/********************************************
 * PRINT_Timetable: printing meeting timetable
 *******************************************/

class PRINT_Timetable extends PRINT_Page
{

	function printHeaderLine($headerline)
	{
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			$this->insertPageBreak();
		}
		$this->linecnt++;	// needs two lines (see style sheet)
?>
	<tr><?php echo $headerline; ?></tr>
<?php
	}


	function printLine($row)
	{
		if(($this->lpp - $this->linecnt) < 1)		// page break check
		{
			$this->insertPageBreak();

		}
		$this->linecnt++;			// increment line count
?>
	<tr><?php echo $row; ?></tr>
<?php
	}

} // end PRINT_Timetable



/***************************************************************
 * PRINT_TimetableComp: printing meeting timetable (competition)
 ***************************************************************/

class PRINT_TimetableComp extends PRINT_Page
{
	var $dateFormat = '';
	
	function startHeaderComp(){
		printf("<table class='timetableComp_header'>");
	}
	
	function endHeaderComp(){
		printf("</table>");
	}

	function printHeaderLine($dateFormat='')
	{
		global $strTimetableCompEnrolement, $strCategoryShort, $strDisciplineShort, $strTimetableCompHeatType, $strTimetableCompGrp, 
			   $strTimetableCompManipulationTime, $strTimetableCompTime, $strTimetableCompHeatNum, $strTimetableCompLauf, 
			   $strTimetableCompRemarks;
			   
		$date = ($dateFormat!='') ? $dateFormat : $this->dateFormat;
		
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			$this->endHeaderComp();
			$this->insertPageBreak();
			$this->startHeaderComp();
		}
		$this->linecnt += 3;	// needs 3 lines
?>
	<tr>
		<th width="50"><?php echo $strTimetableCompEnrolement; ?></th>
		<th width="50"><?php echo $strCategoryShort; ?></th>
		<th width="130"><?php echo $strDisciplineShort; ?></th>
		<th width="45"><?php echo $strTimetableCompHeatType; ?></th>
		<th width="35"><?php echo $strTimetableCompGrp; ?></th>
		<th width="50"><?php echo $strTimetableCompManipulationTime; ?></th>
		<th width="50"><?php echo $strTimetableCompTime; ?></th>
		<th width="45"><?php echo $strTimetableCompHeatNum;?></th>
		<th width="70"><?php echo $strTimetableCompLauf; ?></th>
		<th><?php echo $strTimetableCompRemarks; ?></th>
	</tr>
	<tr>
		<td colspan="10">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="10"><span class="timetableComp_date"><?php echo $date; ?></span></td>
	</tr>
<?php
		$this->dateFormat = $date;
	}


	function startTableComp(){
		printf("<table class='timetableComp'>");
	}
	
	function endTableComp(){
		printf("</table>");
	}
	
	function printLine($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col10)
	{
		if(($this->lpp - $this->linecnt) < 4)	// page break check
		{
			$this->endTableComp();
			$this->insertPageBreak();
			$this->startHeaderComp();
			$this->printHeaderLine($this->dateFormat);
			$this->endHeaderComp();
			$this->startTableComp();
		}
		$this->linecnt += 2;			// increment line count
?>
	<tr>
		<td width='50'><?php echo $col1; ?></td>
		<td width='50'><?php echo $col2; ?></td>
		<td width='130'><?php echo $col3; ?></td>
		<td width='45'><?php echo $col4; ?></td>
		<td width='35'><?php echo $col5; ?></td>
		<td width='50'><?php echo $col6; ?></td>
		<td width='50' class='rightline'><?php echo $col7; ?></td>
		<td width='45' class='rightlineBold'>&nbsp;</td>
		<td width='70' class='rightline'>&nbsp;</td>
		<td class='remarks'><?php echo $col10; ?></td>
	</tr>
<?php
	}

} // end PRINT_Timetable


/********************************************
 * PRINT_Statistics:	printing meeting statistics
 *******************************************/

class PRINT_Statistics extends PRINT_Page
{
	var $headerCol1;
	var $headerCol2;
	var $headerCol3;
	var $headerCol4;
	var $headerCol5; 
    var $headerCol6;     
	
	function printHeaderLine($col1, $col2, $col3, $col4="", $col5="", $col6="" )
	{   
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
		$this->linecnt++;	// needs two lines (see style sheet)
?>
	<tr>
		<th class='stats_col1'><?php echo $col1; ?></th>
		<th class='stats_col2'><?php echo $col2; ?></th>
		<th class='stats_col3'><?php echo $col3; ?></th>
		<?php
		if(!empty($col4) || $col4 == '0'){
			?>
		<th class='stats_col3'><?php echo $col4; ?></th>
			<?php
		}
		if(!empty($col5) || $col5 == '0'){
			?>
		<th class='stats_col3'><?php echo $col5; ?></th>
			<?php
		}
        if(!empty($col6) ){  
        ?>
        <th class='stats_col3'><?php echo $col6; ?></th> 
        <?php
        }        
        ?>
		
	</tr>
<?php
		$this->headerCol1 = $col1;
		$this->headerCol2 = $col2;
		$this->headerCol3 = $col3;
		$this->headerCol4 = $col4;
		$this->headerCol5 = $col5;    
        $this->headerCol6 = $col6;        
	}


	function printLine($col1, $col2, $col3, $col4="", $col5="")
	{
		if(($this->lpp - $this->linecnt) < 4)	// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine($this->headerCol1, $this->headerCol2
						, $this->headerCol3, $this->headerCol4, $this->headerCol5);
		}
		$this->linecnt++;			// increment line count
?>
	<tr>
		<td class='stats_col1'><?php echo $col1; ?></th>
		<td class='stats_col2'><?php echo $col2; ?></th>
		<td class='stats_col3'><?php echo $col3; ?></th>
		<?php
		if(!empty($col4) || $col4 == '0'){
			?>
		<td class='stats_col3'><?php echo $col4; ?></td>
			<?php
		}
		if(!empty($col5) || $col5 == '0'){
			?>
		<td class='stats_col3'><?php echo $col5; ?></td>
			<?php
		}
		?>
	</tr>
<?php
	}
    
    function printLineTax($col1, $col2, $col3, $col4="", $col5="", $assTax, $span=1)
    {
        if(($this->lpp - $this->linecnt) < 5)    // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
            $this->printHeaderLine($this->headerCol1, $this->headerCol2
                        , $this->headerCol3, $this->headerCol4, $this->headerCol5 ,  $this->headerCol6);
        }
        $this->linecnt++;            // increment line count
?>
    <tr>
     <?php if (!empty($assTax) || $assTax == '0'){
               ?>
               <td class='stats_col1_intend'><?php echo $col1; ?></td>   
               <?php
           }
           else {
                ?> 
                <td class='stats_col1'><strong><?php echo $col1; ?></strong></th>
           <?php
           }
           if ($span == 2){
               ?>
                  <td class='stats_col2'><?php echo $col2; ?></th>
               <?php
           }
           else {
               ?>
                 <td class='stats_col2_bold'><?php echo $col2; ?></th>
                 <?php
           }
     ?>
        
        <td class='stats_col3_bold'><?php echo $col3; ?></th>
        <?php
        if(!empty($col4) || $col4 == '0' || !empty($assTax) || $assTax == '0'){
            ?>
        <td class='stats_col3_bold'><?php echo $col4; ?></td>
            <?php
        }
        if (!empty($assTax) || $assTax == '0'){  
            if(!empty($col5) || $col5 == '0' ){
            ?>
            <td class='stats_col3'><?php echo $col5; ?></td>
            <?php
            }
        }
        elseif  (!empty($col5) || $col5 == '0' ){
           ?>
            <td class='stats_col3_bold'><?php echo $col5; ?></td>
            <?php
           }
           
         if(!empty($assTax) || $assTax == '0' ){
            ?>
            <td class='stats_col3' colspan='<?php echo $span; ?>'><?php echo $assTax .".00"; ?></td>
            <?php
        }
        else {
            ?>
            <td class='stats_col3'>&nbsp;</td>
            <?php
        }
        ?>
        
       
    </tr>
<?php
    }

    
    function printTotalLine($col1, $col2, $col3, $col4="", $col5="")
    {
		$this->linecnt = $this->linecnt + 2;		// increment line count
?>
	<tr>
		<td class='stats_tot1'><?php echo $col1; ?></th>
		<td class='stats_tot2'><?php echo $col2; ?></th>
		<td class='stats_tot3'><?php echo $col3; ?></th>
		<?php
		if(!empty($col4) || $col4 == '0'){
			?>
		<td class='stats_tot3'><?php echo $col4; ?></td>
			<?php
		}
		if(!empty($col5) || $col5 == '0'){
			?>
		<td class='stats_tot3' ><?php echo $col5; ?></td>
			<?php
		}
		?>
        </tr>
<?php
    }
    
    function printTotalLineTax($col1, $col2, $col3, $col4="", $col5="",$span=1)
    {
        $this->linecnt = $this->linecnt + 2;        // increment line count
?>
    <tr>
        <td class='stats_tot1'><?php echo $col1; ?></th>
        <td class='stats_tot2'><?php echo $col2; ?></th>
        <td class='stats_tot3'><?php echo $col3; ?></th>
        <?php
        if(!empty($col4) || $col4 == '0'){
            ?>
        <td class='stats_tot3'><?php echo $col4; ?></td>
            <?php
        }
        if(!empty($col5) || $col5 == '0'){
            ?>
        <td class='stats_tot3' colspan='<?php echo $span; ?>'><?php echo $col5.".00"; ?></td>
            <?php
        }
         if ($span==1){
        ?>                   
        <td class='stats_tot3'>&nbsp;</td> 
        <?php
        }
        ?>
	</tr>
<?php
	}
} // end PRINT_Statistics



} // end AA_CL_PRINT_PAGE_LIB_INCLUDED
?>
