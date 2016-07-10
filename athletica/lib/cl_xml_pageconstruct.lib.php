<?php

//
// this class definition is used by the xml result generator.
// for team events it will instance a team rankinglist class with this
// construct as output-$list. so we can get the team results without
// writing a second function for calculating
//


if (!defined('AA_CL_XML_PAGECONSTRUCT_LIB_INCLUDED'))
{
	define('AA_CL_XML_PAGECONSTRUCT_LIB_INCLUDED', 1);


/********************************************
 *
 * CLASS XML_Page
 *
 *******************************************/

class XML_Page
{
	var $xml;

	function GUI_Page($title, $scroll=FALSE)
	{
	
	}

	function printHTMLHeader()
	{

	}

	function printCover($type, $timing=true)
	{
		
	}


	function endPage()
	{

	}

	function printPageTitle($title)
	{

	}


	function printSubTitle($subtitle)
	{

	}

} // END CLASS Gui_Page


/********************************************
 *
 * CLASS XML_ListPage
 *
 *******************************************/

class XML_ListPage extends XML_Page
{
	
	function GUI_ListPage($title='Defaulttitle')
	{
		
	}

	function startPage()
	{
		
	}

	function startList()
	{
		
	}

	function endList()
	{
		
	}

	function switchRowClass()
	{
		
	}
} // end GUI_ListPage



/********************************************
 * XML_TeamRankingList: ranking list for team events
 *******************************************/
 
class XML_TeamRankingList extends XML_ListPage
{
	
	function XML_TeamRankingList(&$parser){
		$this->xml = $parser;
	}

	function printHeaderLine()
	{
		
	}


	function printLine($rank, $name, $club, $points, $id)
	{
		if($points>0){
			$this->xml->write_xml_open("team", array('teamCode'=>'M'));
			$this->xml->write_xml_finished("svmId",$id);
			
			$this->xml->write_xml_open("efforts");
			$this->xml->write_xml_open("effort");
			
			$this->xml->write_xml_finished("DateOfEffort", $GLOBALS['doe']);
			$this->xml->write_xml_finished("scoreResult",AA_alabusScore($points));
			$this->xml->write_xml_finished("wind"," ");
			$this->xml->write_xml_finished("kindOfLap"," ");	// round type
			$this->xml->write_xml_finished("lap"," ");		// heat name (A_, B_, 01, 02 ..)
			$this->xml->write_xml_finished("place",$rank);
			$this->xml->write_xml_finished("placeAddon"," ".$GLOBALS['rankadd']);
			$this->xml->write_xml_finished("relevant","1");
		}
	}


	function printAthleteLine($name, $year, $points)
	{
		
	}



	function printInfo($info)
	{
		$this->xml->write_xml_finished("effortDetails",$info);
		$this->xml->close_open_tags("teams");
	}

} // end GUI_TeamRankingList


} // end AA_CL_XML_PAGECONSTRUCT_LIB_INCLUDED

?>
