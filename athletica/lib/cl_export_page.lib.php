<?php

if (!defined('AA_CL_EXPORT_PAGE_LIB_INCLUDED'))
{
	define('AA_CL_EXPORT_PAGE_LIB_INCLUDED', 1);



/********************************************
 *
 * EXPORT_Page
 *
 *	Base class for exporting lists
 *	exports in txt for press and csv for manual prints (eg. startnumbers or diploms)
 *
 *******************************************/

class EXPORT_Page
{
	
	// file parameters
	var $title;
	var $filetype;
	var $contenttype;
	var $lb; // line break
	var $fd; // field delimiter
	var $td; // text delimiter
	var $headerPrinted;
	
	// public functions
	// ----------------
	
	function EXPORT_Page($title='Defaulttitle', $filetype = "csv")
	{
		global $cfgContentTypes;
		$this->lb = $cfgContentTypes[$filetype]['lb'];
		$this->td = $cfgContentTypes[$filetype]['td'];
		$this->fd = $cfgContentTypes[$filetype]['fd'];
		
		$this->title = $title;
		$this->filetype = $filetype;
		$this->contenttype = $cfgContentTypes[$filetype]['mt'];
		$this->startPage();
	}
	
	/**
	 * startPage
	 * ---------
	 * set up headers for file output.
	 */
	function startPage()
	{
		header("Content-type: ".$this->contenttype."");
		header("Content-Disposition: attachment; filename=".$this->title.".".$this->filetype);
	}
	
	function printCsvLine($params){
		
		$out = "";
		
		if(count($params) > 0){
			
			foreach($params as $p){
				
				if(is_numeric($p)){
					$out .= $p . $this->fd;
				}else{
					$out .= $this->td . $p . $this->td . $this->fd;
				}
				
			}
			
			$out = substr($out, 0, -1);
		}
		
		echo $out;
		echo $this->lb;
		
	}
	
	/**
	 * printCover 
	 * ----------
	 * Sets up a cover page with basic meeting data.
	 */
	 
	function printCover($type, $cover_timing=true)
	{
		
	}
	
	/**
	 * endPage
	 * -------
	 * Terminates the basic HTML-page frame.
	 */
	function endPage()
	{
		ob_flush();
		flush();
	}
	
	/**
	 * insertPageBreak
	 * ---------------
	 * Terminates layout table and inserts a page break (printing).
	 */
	function insertPageBreak()
	{
		
	}
	
	/**
	 * printDocTitle
	 * -------------
	 * Print the document title.
	 */
	function printPageTitle($title)
	{
		
	}
	
	function printSubTitle($title)
	{
		
	}
	
	function startList()
	{
		
	}
	
	function endList()
	{
		
	}
	
	function printHeaderLine($text)
	{
		
	}
	
	function printLine($text)
	{
		
	}
	
	function setHeaderAndFooter(){
		
	}
	
	function defineHeaderAndFooter(&$txt, &$pic, $type, $text, $picture){
		
	}
	
	function printPageHeader(){
		
	}
	
	function printPageFooter(){
		
	}
} 


/* ********************************************************************************************************************************
EXPORT for press */

/********************************************
 * EXPORT_RankingListPress: exports ranking list for single events
 *******************************************/

class EXPORT_RankingListPress extends EXPORT_Page
{
	var $cat;			// current category
	var $disc;			// current discipline
	var $relay;			// current relay status
	var $round;			// current round
	var $points;			// current point info
	var $wind;			// current wind info
	var $title;			// heat title
	var $heatwind;			// wind per heat
	var $firstSubTitle = true;	// if first subtitle
	
	// overwrite constructor
	function EXPORT_RankingListPress($title='Defaulttitle', $filetype = "csv"){
		parent::EXPORT_page($title, $filetype);
		
		$res = mysql_query("SELECT * FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
		if(mysql_errno() > 0){
			
		}else{
			
			$row = mysql_fetch_assoc($res);
			
			//output location and title
			echo $row['Ort'].". ".$row['Name'].".";
			
		}
		
	}
	
	function printSubTitle($category='', $discipline='', $round='')
	{
		
		if($this->cat != $category){
			if(!$this->firstSubTitle){
				echo " -- ".$category.".";
			}else{
				echo " ".$category.".";
			}
			if($this->disc != $discipline){
				echo " ".$discipline.":";
			}
			
		}elseif($this->disc != $discipline){
			echo " -- ".$discipline.":";
		}
		
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
		
		/*if(!$this->firstSubTitle){
			echo $this->lb;
			echo $this->lb;
		}
		$this->printCsvLine(array($this->cat, $this->disc, $this->round));*/
		
		$this->firstSubTitle = false;
	}
	
	
	function printInfoLine($info)
	{
		if(!empty($info))
		{
			
		}
	}


	function printHeaderLine($title, $relay=FALSE, $points=FALSE
		, $wind=FALSE, $heatwind='', $time="", $svm = false)
	{
		$this->relay = $relay;
		$this->points = $points;
		$this->wind = $wind;
		$this->heatwind = $heatwind;
		$this->title = $title;
	}
	
	
	function startList()
	{
		
	}
	
	
	function printLine($rank, $name, $year, $club, $perf
		, $wind, $points, $qual, $ioc)
	{
		
		if(!empty($wind)){
			if($wind >= 0){
				$perf .= "+".$wind;
			}else{
				$perf .= $wind;
			}
		}
		if(!empty($points)){
			$perf .= "/".$points;
		}
		if(!empty($rank)){
			$rank .= ".";
		}
        list($name, $vorname) = split(' ', $name);
		$name = $vorname . ' ' . $name;
		echo " $rank $name ($club) $perf.";
		//$this->printCsvLine(array($rank, $name, $year, $perf, $points));
		
	}
	
	function printAthletesLine($text){
		
	}

}

/********************************************
 * EXPORT_CombinedRankingListPress: exports ranking list for combined events
 *******************************************/

class EXPORT_CombinedRankingListPress extends EXPORT_RankingListPress
{
	
	function printHeaderLine($time = "")
	{
		
		/*if(!empty($time)){
			$this->printCsvLine(array($time));
		}
		
		$params = array();
		$params[] = $GLOBALS['strRank'];
		$params[] = $GLOBALS['strName'];
		$params[] = $GLOBALS['strYearShort'];
		$params[] = $GLOBALS['strPoints'];
		
		$this->printCsvLine($params);*/
		
	}
	
	
	function printLine($rank, $name, $year, $club, $points, $ioc)
	{
		
		if(!empty($rank)){
			$rank .= ".";
		}
		
		echo " $rank $name ($club) $points.";
		
		//$this->printCsvLine(array($rank, $name, $year, $points));
		
	}
	
	
	function printInfo($info)
	{
		
	}

}


/********************************************
 * EXPORT_TeamRankingListPress: exports ranking list for svm team events
 *******************************************/

class EXPORT_TeamRankingListPress extends EXPORT_RankingListPress
{

	function printHeaderLine($time = "")
	{
		
		/*if(!empty($time)){
			$this->printCsvLine(array($time));
		}
		
		$params = array();
		$params[] = $GLOBALS['strRank'];
		$params[] = $GLOBALS['strName'];
		$params[] = $GLOBALS['strClub'];
		$params[] = $GLOBALS['strPoints'];
		
		$this->printCsvLine($params);*/
		
	}


	function printLine($rank, $name, $club, $points)
	{
		if(!empty($rank)){
			$rank .= ".";
		}
		
		echo " $rank $name ($club) $points.";
		
		//$this->printCsvLine(array($rank, $name, $club, $points));
		
	}


	function printAthleteLine($name, $year, $points)
	{
		/*
?>
	<tr>
		<td class='team_rank' />
		<td class='team_name'><?php echo "$name, $year"; ?></td>
		<td class='team_club'><?php echo $points; ?></td>
		<td class='team_points' />
	</tr>

<?php
		*/
	}


	function printInfo($info)
	{
		
	}

}


/********************************************
 * EXPORT_TeamSMRankingListPress: exports ranking list for team sm events
 *******************************************/

class EXPORT_TeamSMRankingListPress extends EXPORT_RankingListPress
{

	function printHeaderLine($time = "")
	{
		
		/*if(!empty($time)){
			$this->printCsvLine(array($time));
		}
		
		$params = array();
		$params[] = $GLOBALS['strRank'];
		$params[] = $GLOBALS['strName'];
		$params[] = $GLOBALS['strClub'];
		$params[] = $GLOBALS['strPoints'];
		
		$this->printCsvLine($params);*/
		
	}


	function printLine($rank, $name, $club, $perf)
	{
		if(!empty($rank)){
			$rank .= ".";
		}
		
		echo " $rank $name ($club) $perf.";
		
		//$this->printCsvLine(array($rank, $name, $club, $points));
		
	}


	function printAthleteLine($name, $year, $perf)
	{
		/*
?>
	<tr>
		<td class='team_rank' />
		<td class='team_name'><?php echo "$name, $year"; ?></td>
		<td class='team_club'><?php echo $perf; ?></td>
		<td class='team_points' />
	</tr>

<?php
		*/
	}


	function printInfo($info)
	{
		
	}

}


/* ********************************************************************************************************************************
EXPORT for diploms */

/********************************************
 * EXPORT_RankingListDiplom: exports ranking list for single events
 *******************************************/

class EXPORT_RankingListDiplom extends EXPORT_Page
{
	var $cat;			// current category
	var $disc;			// current discipline
	var $relay;			// current relay status
	var $round;			// current round
	var $points;		// current point info
	var $wind;			// current wind info
	var $title;			// heat title
	var $heatwind;		// wind per heat


	function printSubTitle($category='', $discipline='', $round='')
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
		
		/*echo $this->lb;
		echo $this->lb;
		$this->printCsvLine(array($this->cat, $this->disc, $this->round));*/
		
	}


	function printInfoLine($info)
	{
		if(!empty($info))
		{
			
		}
	}


	function printHeaderLine($title, $relay=FALSE, $points=FALSE
		, $wind=FALSE, $heatwind='', $time="", $svm = false)
	{
		if($this->headerPrinted){
			return;
		}
		$this->headerPrinted = true;
		
		$this->relay = $relay;
		$this->points = $points;
		$this->wind = $wind;
		$this->heatwind = $heatwind;
		$this->title = $title;
		
		$params = array();
		
		if(!empty($this->title))
		{
			$params[] = $this->title;
			if(!empty($this->heatwind)) {
				$params[] = $GLOBALS['strWind'] . ": " .$this->heatwind;
			}
			
			if(!empty($time)){
				$params[] = $time;
			}
		}
		
		//$this->printCsvLine($params);
		
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
		
		$params = array();
		$params[] = $GLOBALS['strRank'];
		$params[] = $GLOBALS['strName'];   
		$params[] = $year; 
        $params[] = $ioc;  
        if($svm){                      
            $params[] = $GLOBALS['strTeam'];  
        }
        else{ 
            $params[] = $GLOBALS['strClub'];   
        }
       
		$params[] = $GLOBALS['strPerformance'];
		$params[] = $GLOBALS['strPoints'];
		$params[] = $GLOBALS['strCategory'];
		$params[] = $GLOBALS['strDiscipline'];
		$params[] = $GLOBALS['strRound'];
		
		$this->printCsvLine($params);
	}
	
	
	function startList()
	{
		
	}
	
	
	function printLine($rank, $name, $year, $club, $perf
		, $wind, $points, $qual, $country)
	{
		
		if(!empty($wind)){
			if($wind >= 0){
				$perf .= "+".$wind;
			}else{
				$perf .= $wind;
			}
		}
		if(!empty($points)){
			//$perf .= "/".$points;
		}
		
		$this->printCsvLine(array($rank, $name, $year, $country, $club, $perf, $points, $this->cat, $this->disc, $this->round));
		
	}
	
	function printAthletesLine($text){
		
	}

}

/********************************************
 * EXPORT_CombinedRankingListDiplom: exports ranking list for combined events
 *******************************************/

class EXPORT_CombinedRankingListDiplom extends EXPORT_RankingListDiplom
{
	
	function printHeaderLine($time = "")
	{
		if($this->headerPrinted){
			return;
		}
		$this->headerPrinted = true;
		
		if(!empty($time)){
			//$this->printCsvLine(array($time));
		}
		
		$params = array();
		$params[] = $GLOBALS['strRank'];
		$params[] = $GLOBALS['strName'];
		$params[] = $GLOBALS['strYearShort'];
		$params[] = $GLOBALS['strPoints'];
		$params[] = $GLOBALS['strCategory'];
		$params[] = $GLOBALS['strDiscipline'];
		$params[] = $GLOBALS['strRound'];
		
		$this->printCsvLine($params);
		
	}
	
	
	function printLine($rank, $name, $year, $club, $points, $ioc)
	{
		
		$this->printCsvLine(array($rank, $name, $year, $points, $this->cat, $this->disc, $this->round));
		
	}
	
	
	function printInfo($info)
	{
		
	}

}


/********************************************
 * EXPORT_TeamRankingListDiplom: exports ranking list for combined events
 *******************************************/

class EXPORT_TeamRankingListDiplom extends EXPORT_RankingListDiplom
{

	function printHeaderLine($time = "")
	{
		if($this->headerPrinted){
			return;
		}
		$this->headerPrinted = true;
		
		if(!empty($time)){
			//$this->printCsvLine(array($time));
		}
		
		$params = array();
		$params[] = $GLOBALS['strRank'];
		$params[] = $GLOBALS['strName'];
		$params[] = $GLOBALS['strClub'];
		$params[] = $GLOBALS['strPoints'];
		$params[] = $GLOBALS['strCategory'];
		$params[] = $GLOBALS['strDiscipline'];
		$params[] = $GLOBALS['strRound'];
		
		$this->printCsvLine($params);
		
	}


	function printLine($rank, $name, $club, $points)
	{
		
		$this->printCsvLine(array($rank, $name, $club, $points, $this->cat, $this->disc, $this->round));
		
	}


	function printAthleteLine($name, $year, $points)
	{
		/*
?>
	<tr>
		<td class='team_rank' />
		<td class='team_name'><?php echo "$name, $year"; ?></td>
		<td class='team_club'><?php echo $points; ?></td>
		<td class='team_points' />
	</tr>

<?php
		*/
	}


	function printInfo($info)
	{
		
	}

} 

} // end AA_CL_PRINT_PAGE_LIB_INCLUDED
?>
