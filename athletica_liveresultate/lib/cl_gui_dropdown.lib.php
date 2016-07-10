<?php

if (!defined('AA_CL_GUI_DROPDOWN_LIB_INCLUDED'))
{
	define('AA_CL_GUI_DROPDOWN_LIB_INCLUDED', 1);


	require('./lib/cl_gui_select.lib.php');

/********************************************

	CLASS-Collection: Select-Lists
 
	This is a temporary implementation of classes that a <TD>-wrapper around
	SELECT-classes.

 *******************************************/


/********************************************
 * CLASS GUI_CategoryDropDown
 *******************************************/

class GUI_CategoryDropDown
{
	/*
	 *	Constructor
	 *	-----------
     *    optNone:  set false if you want no option '-' to the <SELECT> list
	 */
	function GUI_CategoryDropDown($key, $action = '', $bAll = false, $bAthleteCat = false, $dis = false,$optNone=true)
	{    
		$select = new GUI_CategorySelect($action,$optNone);
		echo "<td class='forms'>";
		$select->printList($key, $bAll, $bAthleteCat, $dis);
		echo "</td>";
	}
} // END CLASS Gui_CategoryDropDown


/********************************************
 * CLASS GUI_ClubDropDown
 * Prints a club drop down.
 *******************************************/

class GUI_ClubDropDown
{
	/*	Constructor
	 *	-----------
	 *		key:		primary key of db table
	 *		all:		set to true if you want print all clubs, not only those in
	 *          	the current meeting.
	 *    action:	if 'all' is set to true, provide also an action item
     *    optNone:  set false if you want no option '-' to the <SELECT> list
     *              
	 */
	function GUI_ClubDropDown($key, $all = false, $action='', $dis = false,$optNone=true, $manual_club='')
	{  
		$select = new GUI_ClubSelect($all, $action, $optNone );
		echo "<td class='forms'>";
		$select->printList($key, $dis, $manual_club);
		echo "</td>";
	}

} // END CLASS Gui_ClubDropDown


/********************************************
 * CLASS GUI_ConfigDropDown
 * Base class to print different configuration drop down lists.
 *******************************************/

class GUI_ConfigDropDown
{
	/*	Constructor
	 *	-----------
	 * To preselect an item, provide its value
	 *		name:					tag name
	 *		configuration:		configuration type
	 *		selection:			preseleted configuration item
	 *		keycomp:				set to true if you want to compare selection to key

	 */
	function GUI_ConfigDropDown($name, $configuration, $selection, $action='', $keycomp=false, $dis = false)
	{
		$select = new GUI_ConfigSelect($name, $action);
		echo "<td class='forms'>";
		$select->printList($configuration, $selection, $keycomp, $dis);
		echo "</td>";

	}

} // END CLASS Gui_ConfigDropDown

/********************************************
 * CLASS GUI_CountryDropDown
 ********************************************/

class GUI_CountryDropDown
{
	/*	Constructor
		-----------
			selection:	preselect an item
			action:		javascript function to process on change
	*/
	function GUI_CountryDropDown($selection="-", $action="", $dis = false){
		$select = new GUI_CountrySelect($action);
		echo "<td class='forms'>";
		$select->printList($selection, $dis);
		echo "</td>";
	}
}

/********************************************
 * CLASS GUI_DateDropDown
 *******************************************/

class GUI_DateDropDown
{
	/*	Constructor
	 *	-----------
	 *		date:		day to preselect	
	 */
	function GUI_DateDropDown($date, $index=0, $action = '')
	{
		$select = new GUI_DateSelect($index, $action);
		echo "<td class='forms'>";
		$select->printList($date);
		echo "</td>";
	}
} // END CLASS Gui_DateFieldDropDown



/********************************************
 * CLASS GUI_DateFieldDropDown
 *******************************************/

class GUI_DateFieldDropDown
{
	/*	Constructor
	 *	-----------
	 *		date:		either day or month to preselect	
	 *		month:	set to true if you want to print month list; if false
	 *					day list is printed
	 */
	function GUI_DateFieldDropDown($name, $date, $month, $action='')
	{
		$select = new GUI_DateFieldSelect($name, $month, $action);
		echo "<td class='forms'>";
		$select->printList($date);
		echo "</td>";
	}
} // END CLASS Gui_DateFieldDropDown


/********************************************
 * CLASS GUI_DisciplineDropDown
 *******************************************/

class GUI_DisciplineDropDown
{
	/*	Constructor
	 *	-----------
	 *		key:		primary key of db table
	 *		new:		set to true if you want NEW as last option
	 *		relay:	set to true if you only want to see relay disciplines
	 *		keys:		list of disciplines not to be displayed
	 *		action:	javascript action to execute onChange
	 */
	function GUI_DisciplineDropDown($key= 0, $new = false, $relay=false, $keys='', $action='', $event=false)
	{
		$select = new GUI_DisciplineSelect($new, $action);
		echo "<td class='forms'>";
		$select->printList($key, $relay, $keys , $event);
		echo "</td>";
	}

} // END CLASS Gui_DisciplineDropDown


/********************************************
 * CLASS GUI_EventDropDown
 *******************************************/

class GUI_EventDropDown
{
	/*	Constructor
	 *	-----------
	 */
	function GUI_EventDropDown($category, $event, $action, $relay=false)
	{
		$select = new GUI_EventSelect($category, $action);
		echo "<td class='forms'>";
		$select->printList($event, $relay);
		echo "</td>";
	}

} // END CLASS GUI_EventCombinedDropDown


/********************************************
 * CLASS GUI_EventCombinedDropDown
 *******************************************/

class GUI_EventCombinedDropDown
{
	/*	Constructor
	 *	-----------
	 */
	function GUI_EventCombinedDropDown($category, $comb, $action)
	{
		$select = new GUI_EventCombinedSelect($category, $action);
		echo "<td class='forms'>";
		$select->printList($comb);
		echo "</td>";
	}

} // END CLASS GUI_EventCombinedDropDown


/********************************************
 * CLASS GUI_HeatDropDown
 *******************************************/

class GUI_HeatDropDown
{
	/*	Constructor
	 *	-----------
	 */
	function GUI_HeatDropDown($round)
	{   
		$select = new GUI_HeatSelect($round);
		echo "<td class='forms'>";
		$select->printList(0);
		echo "</td>";

	}

} // END CLASS Gui_HeatDropDown


/********************************************
 * CLASS GUI_HeatDropDownFrom
 *******************************************/

class GUI_HeatDropDownFrom
{
	/*	Constructor
	 *	-----------
	 */
	function GUI_HeatDropDownFrom($round, $heat=0, $optNew=true)
	{   
		$select = new GUI_HeatSelectFrom($round);
		echo "<td class='forms'>";
		$select->printList($heat,$optNew);
		echo "</td>";

	}

} // END CLASS Gui_HeatDropDownFrom


/********************************************
 * CLASS GUI_HeatDropDownTo
 *******************************************/

class GUI_HeatDropDownTo
{
	/*	Constructor
	 *	-----------
	 */
	function GUI_HeatDropDownTo($round, $heat=0, $optNew=true)
	{   
		$select = new GUI_HeatSelectTo($round);
		echo "<td class='forms'>";
		$select->printList($heat,$optNew);
		echo "</td>";

	}

} // END CLASS Gui_HeatDropDown


/********************************************
 * CLASS GUI_InstallationDropDown
 *******************************************/

class GUI_InstallationDropDown
{
	/*	Constructor
	 *	-----------
	 *    action:	CSS-class	
	 *    item:		item to preselect
	 */
	function GUI_InstallationDropDown($action, $item=0)
	{
		$select = new GUI_InstallationSelect($action);
		echo "<td class='forms'>";
		$select->printList($item);
		echo "</td>";
	}

} // END CLASS Gui_InstallationDropDown


/********************************************
 * CLASS GUI_RegionDropDown
 ********************************************/

class GUI_RegionDropDown
{
	/*	Constructor
		-----------
			selection:	preselect an item
			action:		javascript function to process on change
	*/
	function GUI_RegionDropDown($selection="-", $action="", $dis = false){
		$select = new GUI_RegionSelect($action);
		echo "<td class='forms'>";
		$select->printList($selection, $dis);
		echo "</td>";
	}
}


/********************************************
 * CLASS GUI_RoundDropDown
 *******************************************/

class GUI_RoundDropDown
{
	/*	Constructor
	 *	-----------
	 *		event:	event to be evaluated
	 *		round:	round to preselect
	 */
	function GUI_RoundDropDown($event, $round)
	{
		$select = new GUI_RoundSelect($event);
		echo "<td class='forms'>";
		$select->printList($round);
		echo "</td>";
	}

} // END CLASS Gui_RoundDropDown


/********************************************
 * CLASS GUI_RoundtypeDropDown
 *******************************************/

class GUI_RoundtypeDropDown
{
	/*	Constructor
	 *	-----------
	 *		type:	roundtype to preselect
	 */
	function GUI_RoundtypeDropDown($type, $index=0)
	{   
		$select = new GUI_RoundtypeSelect($index);
		echo "<td class='forms'>";
		$select->printList($type);
		echo "</td>";
	}

} // END CLASS Gui_RoundtypeDropDown


/********************************************
 * CLASS GUI_SeasonDropDown
 *******************************************/

class GUI_SeasonDropDown
{
	/*	Constructor
	 *	-----------
	 *		key:	primary key of db table
	 */
	function GUI_SeasonDropDown($key='')
	{
		$select = new GUI_SeasonSelect();
		echo "<td class='forms'>";
		$select->printList($key);
		echo "</td>";
	}

} // END CLASS Gui_SeasonDropDown



/********************************************
 * CLASS GUI_StadiumDropDown
 *******************************************/

class GUI_StadiumDropDown
{
	/*	Constructor
	 *	-----------
	 *		key:	primary key of db table
	 */
	function GUI_StadiumDropDown($key=0)
	{
		$select = new GUI_StadiumSelect();
		echo "<td class='forms'>";
		$select->printList($key);
		echo "</td>";
	}

} // END CLASS Gui_StadiumDropDown



/********************************************
 * CLASS GUI_TeamDropDown
 *******************************************/

class GUI_TeamDropDown 
{
	/*	Constructor
	 *	-----------
	 *		category:	current category
	 *		club:			current club
	 *		key:			team primary key to preselect
	 *		action:		javascript action to execute onChange
	 */
	function GUI_TeamDropDown($category, $club, $key=0, $action='')
	{
		$select = new GUI_TeamSelect($category, $club, $action);
		echo "<td class='forms'>";
		$select->printList($key);
		echo "</td>";
	}

} // END CLASS Gui_TeamDropDown


/********************************************
 * CLASS GUI_ScoreTableDropDown
 *******************************************/

class GUI_ScoreTableDropDown 
{
	/*	Constructor
	 *	-----------
	 *		key:			scoretable primary key to preselect
	 *		action:		javascript action to execute onChange
	 */
	function GUI_ScoreTableDropDown($key=0, $action='')
	{
		$select = new GUI_ScoreTableSelect($action);
		echo "<td class='forms'>";
		$select->printList($key);
		echo "</td>";
	}

} // END CLASS Gui_TeamDropDown


/********************************************
 * CLASS GUI_ScoreTableDisciplineDropDown
 *******************************************/

class GUI_ScoreTableDisciplineDropDown 
{
	/*	Constructor
	 *	-----------
	 *		scoretable:	scoretable primary key
	 * 		key:		discipline primary key to preselect
	 *		action:		javascript action to execute onChange
	 */
	function GUI_ScoreTableDisciplineDropDown($scoretable, $key=0, $action='')
	{
		$select = new GUI_ScoreTableDisciplineSelect($action);
		echo "<td class='forms'>";
		$select->printList($scoretable, $key);
		echo "</td>";
	}

} // END CLASS Gui_TeamDropDown


} // end AA_CL_GUI_DROPDOWN_LIB_INCLUDED

?>
