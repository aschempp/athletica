<?php

if (!defined('AA_CL_GUI_SELECT_LIB_INCLUDED'))
{
	define('AA_CL_GUI_SELECT_LIB_INCLUDED', 1);



/********************************************

	CLASS-Collection: GUI_Select-Lists
 
	This collection contains classes which print <SELECT>-nodes
	(= drop down lists).
 
	1.) base classes
		 ------------
	GUI_Select				basic class, prints <SELECT> box				

	2.) implementation classes
		 ----------------------
	GUI_CategorySelect			category list
	GUI_ClubSelect					club list
	GUI_ConfigSelect				base class to print different config. lists	
	GUI_CountrySelect			ioc country code list
	GUI_DateSelect					meeting date list
	GUI_DateFieldSelect			fields for date items
	GUI_DisciplineSelect			discipline list
	GUI_EventSelect				event list
	GUI_InstallationSelect		installation list
	GUI_RelaySelect				relay list
	GUI_RoundSelect				round list
	GUI_RoundtypeSelect			roundtype list
	GUI_StadiumSelect				stadium list
	GUI_TeamSelect					team list

 *******************************************/



/********************************************
 * CLASS GUI_Select
 * Prints a <SELECT> box.
 *******************************************/

class GUI_Select
{
	var $action;
	var $checked;
	var $name;
	var $options;
	var $size;
    var $multiple;

	function GUI_Select($name, $size, $action='',$multiple)
	{     
		$this->action = $action;
		$this->checked = '';
		$this->name = $name;
		$this->size = $size;
		$this->options = array();
        $this->multiple = $multiple; 
	}

	/*	addOption()
	 *	---------------
	 * Add any option to the <SELECT> list
	 *		key:	item to disply (actually the value)
	 *		value:	the option value (actually the key)
	 */
	function addOption($key, $value)
	{
		//$this->options[$key] = $value;
		$this->options[$value] = $key;
	}

	/*	addOptionNone()
	 *	---------------
	 * Adds an option '-' to the <SELECT> list
	 */
	function addOptionNone()
	{
		//$this->options['-'] = 0;
		$this->options['0'] = '-';
	}

	/*	addOptionNew()
	 *	---------------
	 * Adds an option '[ New ... ]' to the <SELECT> list
	 */
	function addOptionNew()
	{
		$k = "[ " . $GLOBALS['strNew'] . " ... ]";
		//$this->options[$k] = 'new';
		$this->options['new'] = $k;
	}

	/*	addOptionsFromDB()
	 *	------------------
	 * Read item from the DB and adds them to the <SELECT> list
	 *		query:	provide SELECT query that returns item[0] as value
	 *					and item[1] as key.
	 *					(item[0] is usually the primary key, item[1] the display
	 *					name)
	 */
	function addOptionsFromDB($query)
	{
		require('./lib/utils.lib.php');
		$GLOBALS['AA_ERROR'] = '';

		$res = mysql_query($query);
		if(mysql_errno() > 0) {		// DB error
			$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
		}
		else {
			while ($row = mysql_fetch_row($res))
			{
				//$this->options[$row[1]] = $row[0];
				$this->options[$row[0]] = $row[1];
			}
			mysql_free_result($res);
		}
	}

	/*	selectOption()
	 *	--------------
	 * Set option to 'checked' in the <SELECT> list
	 *		key:		item to be checked
	 */
	function selectOption($key)
	{   
		$this->checked = $key;                   
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 */
	function printList($dis = false, $manual_club = '')
	{   
	   $dis = ($dis) ? ' disabled="disabled"' : '';
       if ($this->multiple == 'multiple'){
            ?>      
            <select class='<?php echo $manual_club; ?>'  name='<?php echo $this->name; ?>[]'  size='<?php echo $this->size; ?>' <?php echo $this->multiple; ?>       
            <?php if ($this->action != '') { ?> onChange='<?php echo $this->action; ?>'<?php }; ?> id='<?php echo $this->name; ?>selectbox'<?php echo $dis; ?>>
        
     
     <?php
           
       }
       else { 
?>      
	 <select class='<?php echo $manual_club; ?>'  name='<?php echo $this->name; ?>'  size='<?php echo $this->size; ?>' <?php echo $this->multiple; ?>       
		<?php if ($this->action != '') { ?> onChange='<?php echo $this->action; ?>'<?php }; ?> id='<?php echo $this->name; ?>selectbox'<?php echo $dis;?>>
        
     
<?php
        }
        
		foreach ($this->options as $key=>$value)
		{
			if($key == "$this->checked") {
				printf("\t<option selected value='$key'>$value</option>\n");
			}
			else {
				printf("\t<option value='$key'>$value</option>\n");
			}
		}
?>
	</select>
<?php
	}

} // END CLASS Gui_Select


/********************************************
 * CLASS GUI_CategorySelect
 * Prints a category drop down list by using the GUI_Select class.
 *******************************************/

class GUI_CategorySelect
{
	var $select;
	var $optNone;   
	
	/*
	 *	Constructor
	 *	-----------
	 */
	function GUI_CategorySelect($action = '',$optNone=true)
	{    
		$this->select = new GUI_Select('category', 1, $action);
		$this->optNone = $optNone;
		if ($this->optNone)
			$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its DB primary key
	 *		key:		primary key of db table
	 */
	function printList($key=0, $bAll = false, $bAthleteCat = false, $dis = false)
	{
		require('./config.inc.php');
		
		// change name for select box if $bAthleteCat is set
		if($bAthleteCat){
			$this->select->name = "athletecat";
		}
		
		// get items from DB
		if(!$bAll){
			if(!$bAthleteCat){
				$this->select->addOptionsFromDB("
					SELECT DISTINCT
						w.xKategorie
						, k.Kurzname
					FROM
						athletica.wettkampf AS w
						LEFT JOIN athletica.kategorie AS k ON (w.xKategorie = k.xKategorie)
					WHERE    
					   w.xMeeting = " . $_COOKIE['meeting_id'] . "
					ORDER BY
						k.Anzeige
				");
			}else{
				$this->select->addOptionsFromDB("
					SELECT DISTINCT
						a.xKategorie
						, k.Kurzname
					FROM
						athletica.anmeldung AS a
						LEFT JOIN athletica.kategorie AS k ON (a.xKategorie = k.xKategorie)  
					WHERE  
					    a.xMeeting = " . $_COOKIE['meeting_id'] . "
					ORDER BY
						k.Anzeige
				");
			}
		}else{
			$this->select->addOptionsFromDB("
				SELECT DISTINCT
					k.xKategorie
					, k.Kurzname
				FROM
					athletica.kategorie AS k
				WHERE aktiv = 'y' 
				ORDER BY
					k.Anzeige
			");
		}

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList($dis);
	}
} // END CLASS Gui_CategorySelect


/********************************************
 * CLASS GUI_ClubSelect
 * Prints a club drop down list by using the GUI_Select class.
 *******************************************/

class GUI_ClubSelect
{
	var $select;
	var $all;
	var $optNone; 
	
	/*	Constructor
	 *	-----------
	 *		all:	set to true if you want print all clubs, not only those in
	 *          	the current meeting.
	 *    action:	if 'all' is set to true, provide also an action item
	 */
	function GUI_ClubSelect($all = false, $action='',$optNone=true)
	{    
		$this->all = $all;
		$this->optNone = $optNone;
		$this->select = new GUI_Select('club', 1, $action);  
		 
		if ($this->optNone)  
			$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list together with a table cell containing
	 * its caption.
	 * To preselect an item, provide its DB primary key
	 *		key:		primary key of db table
	 *		relays:	set to true if you only want to see relay disciplines
	 */
	function printList($key = 0, $dis = false, $manual_club = '')
	{
		require('./config.inc.php');

		if($key == 0) {
			$key = '-';
		}

		// get items from DB
		// read all clubs
		$res = mysql_query("
			SELECT
				xVerein
				, Sortierwert
				, Name
			FROM
				athletica.verein
			ORDER BY
				Sortierwert
		"); 
		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else										// no DB error
		{
			$options = array();
			while ($row = mysql_fetch_row($res))
			{
				// select list of clubs
				if($this->all == false)
				{
					$ok = false;		// initialize
					// check if club has any athletes
					$r = mysql_query("
						SELECT
							at.xAthlet
						FROM
							athletica.anmeldung AS a
							LEFT JOIN athletica.athlet AS at ON (a.xAthlet = at.xAthlet)
						WHERE at.xVerein = $row[0]  						
						AND a.xMeeting = " . $_COOKIE['meeting_id']
					);
					if(mysql_errno() > 0) {		// DB error
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}
					else if(mysql_num_rows($r) > 0)		// club in this meeting
					{
						$ok = true;
					}
					else
					{
						// check if club has any teams
						mysql_free_result($r);
						$r = mysql_query("
							SELECT
								t.xTeam
							FROM
								athletica.team AS t
							WHERE t.xVerein = $row[0]
							AND t.xMeeting = " . $_COOKIE['meeting_id']
						);

						if(mysql_errno() > 0) {		// DB error
							AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}
						else if(mysql_num_rows($r) > 0)		// club in this meeting
						{
							$ok = true;
						}
						else
						{
							// check if club has any relays
							mysql_free_result($r);
							$r = mysql_query("
								SELECT
									s.xStaffel
								FROM
									athletica.staffel AS s
								WHERE s.xVerein = $row[0]
								AND s.xMeeting = " . $_COOKIE['meeting_id']
							);

							if(mysql_errno() > 0) {		// DB error
								AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
							}
							else if(mysql_num_rows($r) > 0)		// club in this meeting
							{
								$ok = true;
							}
						}
					}		// ET club with athletes
				}
				else {	// show all clubs
					$ok = true;
				}	// ET all clubs or only those at this meeting

				// club takes part in this meeting
				if($ok == true)		
				{    
					$str = ($row[1]!='' && $row[1]!="\n") ? $row[1] : $row[2];
					//$this->select->addOption($str, $row[0]);
					$options[] = array(
						'sort' => strtoupper($str), 
						'name' => $str, 
						'key' => $row[0]
					);
				}

				if($row[0] == $key) {
					$this->select->selectOption($key);
				}
			}	// END while
			
			$sort = array();
			$name = array();
			$key = array();
			foreach($options as $k => $row) {
				$sort[$k]  = $row['sort'];
				$name[$k]  = $row['name'];
				$key[$k] = $row['key'];
			}
			array_multisort($sort, SORT_ASC, $name, SORT_ASC, $key, SORT_ASC, $options);
			
			foreach($options as $option){
				$this->select->addOption($option['name'], $option['key']);
			}

			if($this->all == true) {				// item to add new club
				$this->select->addOptionNew();	// 'new' item
			}
			mysql_free_result($res);
		}						// ET DB error
		
		$this->select->printList($dis, $manual_club);
	}
} // END CLASS Gui_ClubSelect


/********************************************
 * CLASS GUI_ConfigSelect
 * Base class to print different configuration drop down lists.
 *******************************************/

class GUI_ConfigSelect
{
	var $select;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_ConfigSelect($name='', $action='')
	{
		$this->select = new GUI_Select($name, 1, $action);
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its value
	 *		eval:		ID of evaluation type
	 *		key:		set to true if you want to compare selection to key
	 */
	function printList($configuration, $selection=0, $keycomp=false, $dis = false)
	{
		require('./convtables.inc.php');

		foreach($GLOBALS[$configuration] as $key=>$value)
		{
			if($keycomp == false)
			{
				$this->select->addOption($key, $value);
				if($selection == $value) {
					$this->select->selectOption($value);
				}
			}
			else {
				$this->select->addOption($key, $key);
				if($selection == $key) {
					$this->select->selectOption($key);
				}
			}
		}
		
		// special: own score tables
		if($configuration=='cvtTable')
		{
			$sql = "SELECT DISTINCT(xWertungstabelle)
						   , Name 
					  FROM athletica.wertungstabelle 
				  ORDER BY Name ASC;";
			$query = mysql_query($sql);
			
			while($row = mysql_fetch_assoc($query)){
				$this->select->addOption($row['Name'], $row['xWertungstabelle']);
				if($selection == $row['xWertungstabelle']) {
					$this->select->selectOption($row['xWertungstabelle']);
				}
			}
		}
		
		$this->select->printList($dis);
	}

} // END CLASS Gui_ConfigSelect


/********************************************
 * CLASS GUI_CountrySelect
 * Prints a country drop down list by using the GUI_Select class.
 *******************************************/

class GUI_CountrySelect
{
	/*	Constructor
	 *	-----------
	 *		
	 */
	function GUI_CountrySelect($action='')
	{
		$this->select = new GUI_Select('country', 1, $action);
		//$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list together with a table cell containing
	 * its caption.
	 * To preselect an item, provide its DB primary key
	 */
	function printList($key = "-", $dis = false)
	{
		require('./config.inc.php');
		
		// read all clubs
		$res = mysql_query("
			SELECT
				xCode
				, Name
			FROM
				athletica.land
			ORDER BY
				Sortierwert
		"); 
		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else // no DB error
		{
			$this->select->addOption('-', '-');
			if($key == '-'){
				$this->select->selectOption('-');
			}
			while ($row = mysql_fetch_row($res))
			{
				$this->select->addOption($row[0], $row[0]);
				if($key == $row[0]) {
					$this->select->selectOption($row[0]);
				}
			}	// END while

			
			mysql_free_result($res);
		} // ET DB error

		$this->select->printList($dis);
	}
} // END CLASS gui_countryselect


/********************************************
 * CLASS GUI_DateSelect
 * Prints a drop down lists with date range of current meeting.
 *******************************************/

class GUI_DateSelect
{
	var $select;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_DateSelect($index=0, $action = '')
	{
		if($index > 0) {
			$this->select = new GUI_Select("date_".$index, 1,  $action);
		}
		else {
			$this->select = new GUI_Select('date', 1, $action);
		}
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list together with a table cell containing
	 * its caption.
	 * To preselect an item, provide its DB primary key
	 *		date:		either day or month to preselect	
	 */
	function printList($date = 0)
	{
		require('./config.inc.php');

		// assemble selection list for meeting dates
		$result = mysql_query("
			SELECT
				DatumVon
				, TO_DAYS(DatumBis) - TO_DAYS(DatumVon)
			FROM
				athletica.meeting
			WHERE xMeeting=" . $_COOKIE['meeting_id']
		);

		if(mysql_errno() > 0)	// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else			// no DB error
		{
			$row = mysql_fetch_row($result);

			for($i=0; $i<=$row[1]; $i++)
			{
				$res = mysql_query("
					SELECT
						DATE_ADD(DatumVon, INTERVAL $i DAY)
						, DATE_FORMAT(DATE_ADD(DatumVon, INTERVAL $i DAY), '%d.%m.%Y')
					FROM
						athletica.meeting
					WHERE xMeeting=" . $_COOKIE['meeting_id']
				);
				$date_row = mysql_fetch_row($res);
				$this->select->addOption($date_row[1], $date_row[0]);
				if($date_row[0] == $date) {
					$this->select->selectOption($date_row[0]);
				}
				mysql_free_result($res);
			}
			mysql_free_result($result);
		}
		$this->select->printList();
	}

} // END CLASS Gui_DateSelect



/********************************************
 * CLASS GUI_DateFieldSelect
 * Prints a drop down lists for day and months by using the GUI_Select class.
 *******************************************/

class GUI_DateFieldSelect
{
	var $month;
	var $select;
	
	/*	Constructor
	 *	-----------
	 *		month:	set to true if you want to print month list; if false
	 *					day list is printed
	 */
	function GUI_DateFieldSelect($name, $month, $action='')
	{
		$this->month = $month;
		if($month == true) {
			$nm = $name."_month";
		}
		else {
			$nm = $name."_day";
		}
		$this->select = new GUI_Select($nm, 1, $action);
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list together with a table cell containing
	 * its caption.
	 * To preselect an item, provide its DB primary key
	 *		date:		either day or month to preselect	
	 */
	function printList($date)
	{
		if($this->month == true) {
			$this->addOption($date, 12);
		}
		else {
			$this->addOption($date, 31);
		}
		$this->select->printList();
	}

	/*	private: addOption()
	 *	--------------------
	 *	Internal function to build drop down lists for days and months. 
	 */
	function addOption($date, $range)
	{
		for( $i=1; $i<10 && $i<=$range; $i++) {
			$d = '0'.$i;
			$this->select->addOption($d, $d);
			if($d == $date) {
				$this->select->selectOption($d);
			}
		}
		for( ; $i<=31 && $i<=$range; $i++) {
			$this->select->addOption($i, $i);
			if($i == $date) {
				$this->select->selectOption($i);
			}
		}
	}
} // END CLASS Gui_DateFieldSelect


/********************************************
 * CLASS GUI_DisciplineSelect
 * Prints a discipline drop down list by using the GUI_Select class.
 *******************************************/

class GUI_DisciplineSelect
{
	var $select;
	var $new;
	
	/*	Constructor
	 *	-----------
	 *		new:	set to true if you want NEW as last option
	 */
	function GUI_DisciplineSelect($new = false, $action='')
	{  
		$this->new = $new;
		if($new == true && empty($action)) {
			$action = "check(\"discipline\")";
		}

		$this->select = new GUI_Select('discipline', 1, $action);
		$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list together with a table cell containing
	 * its caption.
	 * To preselect an item, provide its DB primary key
	 *		key:		primary key of db table
	 *		relay:	set to true if you only want to see relay disciplines
	 *		keys:		list of disciplines not to be displayed
     *      event:  set to true if you only want to see disciplines from events       
	 */
	function printList($key=0, $relay=false, $keys='', $event=false)
	{
		require('./config.inc.php');

        $where = ''; 
		$table = '';
        $dist = ''; 
		if($relay == true) {
			$where = " AND Staffellaeufer > 0 ";
		}
		else if(!empty($keys)) {
			$where = " AND xDisziplin NOT IN ($keys) ";
		}
        elseif ($event == true) {
           $table = " INNER JOIN wettkampf as w ON (d.xDisziplin = w.xDisziplin) ";
           $dist = " DISTINCT ";
        }


		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT $dist 
				d.xDisziplin
				, Kurzname
			FROM
				disziplin_" . $_COOKIE['language'] . "  AS d
                $table  
			WHERE d.Typ != ".$cfgDisciplineType[$strDiscCombined]."
            AND aktiv = 'y' 
			$where
			ORDER BY
				Anzeige
		");
        
		if(!empty($GLOBALS['AA_ERROR']))
		{   
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($this->new == true) {
			$this->select->addOptionNew();				// 'new' item
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList();
	}
} // END CLASS Gui_DisciplineSelect


/********************************************
 * CLASS GUI_EventSelect
 * Prints an event drop down list by using the GUI_Select class.
 *******************************************/

class GUI_EventSelect
{
	var $select;
	var $category;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_EventSelect($category, $action='')
	{
		$this->category = $category;
		$this->select = new GUI_Select('event', 1, $action);
		$this->select->addOptionNone();				// empty item
	}


	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its DB primary key
	 *		key:		primary key of db table
	 *		relay:	set to true, if you only want to see relay events
	 */
	function printList($key=0, $relay=false)
	{
		require('./config.inc.php');

		if($this->category < 1)	{		// no selection
			$cat_argument = "";
			$displ = "IF(LENGTH(wettkampf.Info)>0, CONCAT(kategorie.Kurzname, ', ', d.Kurzname, ' (', wettkampf.Info, ')'), CONCAT(kategorie.Kurzname, ', ', d.Kurzname))";
		}
		else {
			$cat_argument = " AND wettkampf.xKategorie = " . $this->category;
			$displ = "IF(LENGTH(wettkampf.Info)>0,  CONCAT(d.Kurzname, ' (', wettkampf.Info, ')') , d.Kurzname) as DiszName";
		}

		$where = '';
		if($relay == true) {
			$where = 'AND d.Staffellaeufer > 0 ';
		}

		// get items from DB
		$sql = "SELECT 
					wettkampf.xWettkampf 
					, $displ 
				FROM 
					athletica.wettkampf 
				LEFT JOIN athletica.kategorie USING (xKategorie)
				LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d ON (wettkampf.xDisziplin = d.xDisziplin)
				WHERE 
					wettkampf.xMeeting = " . $_COOKIE['meeting_id'] . "
				$cat_argument  
				$where
				ORDER BY 
					kategorie.Anzeige 
					, d.Anzeige";
		
		
			/*SELECT
				wettkampf.xWettkampf
				, $displ
			FROM
				wettkampf
				, kategorie
				, disziplin
			WHERE wettkampf.xMeeting = " . $_COOKIE['meeting_id']  
			. $cat_argument. "
			AND wettkampf.xKategorie = kategorie.xKategorie
			AND wettkampf.xDisziplin = disziplin.xDisziplin
			$where
			ORDER BY
				kategorie.Anzeige
				, disziplin.Anzeige";*/
				
		$this->select->addOptionsFromDB($sql);

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList();
	}

} // END CLASS Gui_EventSelect


/********************************************
 * CLASS GUI_EventCombinedSelect
 * Prints an event drop down list but only combined events (merged disciplines by Mehrkampfcode).
 *******************************************/

class GUI_EventCombinedSelect
{
	var $select;
	var $category;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_EventCombinedSelect($category, $action='')
	{
		$this->category = $category;
		$this->select = new GUI_Select('comb', 1, $action);
		$this->select->addOptionNone();				// empty item
	}


	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its DB primary key
	 *		key:		primary key of db table
	 */
	function printList($key=0)
	{
		require('./config.inc.php');

		if($this->category < 1)	{		// no selection
			$cat_argument = "";
			$displ = "CONCAT(k.Kurzname, ', ', d.Name)";
		}
		else {
			$cat_argument = " AND w.xKategorie = " . $this->category;
			$displ = "d.Name";
		}
		
		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				CONCAT(w.xKategorie, '_', w.Mehrkampfcode)
				, $displ
			FROM
				athletica.wettkampf AS w
				LEFT JOIN athletica.kategorie AS k ON (w.xKategorie = k.xKategorie)
				LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d ON (w.Mehrkampfcode = d.Code)
			WHERE w.xMeeting = " . $_COOKIE['meeting_id']  
			. $cat_argument. "			
			AND w.Mehrkampfcode > 0
			GROUP BY
				w.xKategorie
				, w.Mehrkampfcode
			ORDER BY
				k.Anzeige
				, d.Anzeige
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList();
	}

} // END CLASS GUI_EventCombinedSelect


/********************************************
 * CLASS GUI_HeatSelect
 * Prints a heat drop down list by using the GUI_Select class.
 *******************************************/

class GUI_HeatSelect
{
	var $select;
	var $round;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_HeatSelect($round=0)
	{  
		$this->round = $round;    
		$this->select = new GUI_Select('heat', 1);   		
		$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key=0)
	{
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				s.xSerie
				, s.Bezeichnung
				, LPAD(s.Bezeichnung,5,'0') as heatid
			FROM
				athletica.runde AS r
				LEFT JOIN athletica.serie AS s ON (s.xRunde = r.xRunde)
			WHERE r.xRunde = " . $this->round . " 			
			ORDER BY
				heatid
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key); 
		$this->select->addOptionNew();  	  
		$this->select->printList();
	}
} // END CLASS Gui_HeatSelect

 /********************************************
 * CLASS GUI_HeatSelectFrom
 * Prints a heat drop down list by using the GUI_Select class.
 *******************************************/

class GUI_HeatSelectFrom
{
	var $select;
	var $round;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_HeatSelectFrom($round=0)
	{  
		$this->round = $round;   		
		$this->select = new GUI_Select('heatFrom', 1, 'document.heat_selectionFrom.submit()'); 
		$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key=0,$optNew=true)
	{
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				s.xSerie
				, s.Bezeichnung
				, LPAD(s.Bezeichnung,5,'0') as heatid
			FROM
				athletica.runde AS r
				LEFT JOIN athletica.serie AS s ON (s.xRunde = r.xRunde)
			WHERE r.xRunde = " . $this->round . " 			
			ORDER BY
				heatid
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		if ($optNew)
			$this->select->addOptionNew();
		$this->select->printList();
	}
} // END CLASS Gui_HeatSelectFrom

 /********************************************
 * CLASS GUI_HeatSelectTo
 * Prints a heat drop down list by using the GUI_Select class.
 *******************************************/

class GUI_HeatSelectTo
{
	var $select;
	var $round;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_HeatSelectTo($round=0)
	{  
		$this->round = $round;  		
		$this->select = new GUI_Select('heatTo', 1, 'document.heat_selectionTo.submit()'); 
		$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key=0,$optNew=true)
	{
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				s.xSerie
				, s.Bezeichnung
				, LPAD(s.Bezeichnung,5,'0') as heatid
			FROM
				athletica.runde AS r
				LEFT JOIN athletica.serie AS s ON (s.xRunde = r.xRunde)
			WHERE r.xRunde = " . $this->round . "			
			ORDER BY
				heatid
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		if ($optNew)
			$this->select->addOptionNew();
		$this->select->printList();
	}
} // END CLASS Gui_HeatSelectTo


/********************************************
 * CLASS GUI_InstallationSelect
 * Prints an installation drop down list by using the GUI_Select class.
 *******************************************/

class GUI_InstallationSelect
{
	var $select;
	
	/*	Constructor
	 *	-----------
	 *    action:	CSS-class	
	 */
	function GUI_InstallationSelect($action)
	{
		$this->select = new GUI_Select('installation', 1, $action);
		$this->select->addOptionNone();				// empty item
	}


	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key)
	{
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				a.xAnlage
				, a.Bezeichnung
			FROM
				athletica.anlage AS a
				LEFT JOIN athletica.meeting AS m ON (a.xStadion = m.xStadion)
			WHERE m.xMeeting = " . $_COOKIE['meeting_id'] . " 			
			ORDER BY
				a.Bezeichnung
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList();
	}

} // END CLASS Gui_InstallationSelect



/********************************************
 * CLASS GUI_RegionSelect
 * Prints a region drop down, distinct on RegionSpezial of anmeldung.
 *******************************************/

class GUI_RegionSelect
{
	/*	Constructor
	 *	-----------
	 *		
	 */
	function GUI_RegionSelect($action='')
	{
		$this->select = new GUI_Select('region', 1, $action);
		//$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list together with a table cell containing
	 * its caption.
	 * To preselect an item, provide its DB primary key
	 */
	function printList($key = "-", $dis = false)
	{
		require('./config.inc.php');
		
		// read all clubs
		$res = mysql_query("
			SELECT
				xRegion
				, Name
			FROM
				athletica.region
			ORDER BY
				Sortierwert
		"); 
		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else // no DB error
		{
			$this->select->addOption('-', '0');
			if($key == '-'){
				$this->select->selectOption('-');
			}
			while ($row = mysql_fetch_row($res))
			{
				$this->select->addOption($row[1], $row[0]);
				if($key == $row[0]) {
					$this->select->selectOption($row[0]);
				}
			}	// END while

			
			mysql_free_result($res);
		} // ET DB error

		$this->select->printList($dis);
	}
} // END CLASS GUI_RegionSelect



/********************************************
 * CLASS GUI_RoundSelect
 * Prints a round drop down list by using the GUI_Select class.
 *******************************************/

class GUI_RoundSelect
{
	var $select;
	var $event;
	
	/*	Constructor
	 *	-----------
	 *		event:	all rounds for this event
	 */
	function GUI_RoundSelect($event=0)
	{
		$this->event = $event;
		$this->select = new GUI_Select('round', 1 , 'document.round_selection.submit()');
		$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key=0)
	{   
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				runde.xRunde
				,  IF(rt.Typ IS NULL
					, '".$GLOBALS['strFinalround']."' 
					, IF(rt.Typ = 'D'
						, CONCAT(rt.Typ, ' g', runde.Gruppe)
						, rt.Typ
					)
				)
			FROM
				athletica.runde
			LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt
				ON runde.xRundentyp = rt.xRundentyp
			WHERE runde.xWettkampf = " . $this->event
		);

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList();
	}
} // END CLASS Gui_RoundSelect
		

/********************************************
 * CLASS GUI_RoundtypeSelect
 * Prints a roundtype drop down list by using the GUI_Select class.
 *******************************************/

class GUI_RoundtypeSelect
{
	var $select;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_RoundtypeSelect($index)
	{
		if($index > 0) {
			$this->select = new GUI_Select("roundtype_".$index, 1, "check(\"roundtype\")");
		}
		else {
			$this->select = new GUI_Select("roundtype", 1, "check(\"roundtype\")");
		}
		$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key=0)
	{
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				xRundentyp
				, Name
			FROM
				athletica.rundentyp_" . $_COOKIE['language'] . "
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->addOptionNew();
		$this->select->printList();
	}
} // END CLASS Gui_RoundtypeSelect



/********************************************
 * CLASS GUI_SeasonSelect
 * Prints a season drop down list by using the GUI_Select class.
 *******************************************/

class GUI_SeasonSelect
{
	var $select;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_SeasonSelect()
	{
		$this->select = new GUI_Select('saison', 1, "check(\"saison\")");
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 */
	function printList($key='')
	{
		require('./config.inc.php');

		$this->select->addOption('-', '');
		$this->select->addOption('Indoor', 'I');
		$this->select->addOption('Outdoor','O');

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}

		$this->select->selectOption($key);
		$this->select->printList();
	}
} // END CLASS Gui_StadiumSelect




/********************************************
 * CLASS GUI_StadiumSelect
 * Prints a stadium drop down list by using the GUI_Select class.
 *******************************************/

class GUI_StadiumSelect
{
	var $select;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_StadiumSelect()
	{
		$this->select = new GUI_Select('stadium', 1, "check(\"stadium\")");
		$this->select->addOptionNone();				// empty item
	}

	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key=0)
	{
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				xStadion
				, Name
			FROM
				athletica.stadion
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->addOptionNew();
		$this->select->printList();
	}
} // END CLASS Gui_StadiumSelect




/********************************************
 * CLASS GUI_TeamSelect
 * Prints a team drop down list by using the GUI_Select class.
 *******************************************/

class GUI_TeamSelect
{
	var $category;
	var $club;
	var $select;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_TeamSelect($category, $club, $action='')
	{
		$this->category = $category;
		$this->club = $club;
		$this->select = new GUI_Select('team', 1, $action);
		$this->select->addOptionNone();				// empty item
	}


	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key)
	{
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				xTeam
				, concat(team.Name, ' (', kategorie.Name, ')')
			FROM
				athletica.team
				LEFT JOIN athletica.kategorie USING(xKategorie)
			WHERE ".//xKategorie = " . $this->category . "
			//AND xVerein = " . $this->club . "
			" xMeeting = " . $_COOKIE['meeting_id'] . "
			ORDER BY
				team.Name
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList();
	}
} // END CLASS Gui_TeamSelect


/********************************************
 * CLASS GUI_ScoreTableSelect
 * Prints a score table drop down list by using the GUI_Select class.
 *******************************************/

class GUI_ScoreTableSelect
{
	var $select;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_ScoreTableSelect($action='')
	{
		$this->select = new GUI_Select('xWertungstabelle', 1, $action);
		$this->select->addOptionNone();				// empty item
		$this->select->addOptionNew();
	}


	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($key)
	{
		require('./config.inc.php');

		// get items from DB
		$this->select->addOptionsFromDB("
			SELECT
				xWertungstabelle
				, Name
			FROM
				wertungstabelle
			ORDER BY
				Name
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList();
	}
} // END CLASS GUI_ScoreTableDisciplineSelect

class GUI_ScoreTableDisciplineSelect
{
	var $select;
	
	/*	Constructor
	 *	-----------
	 */
	function GUI_ScoreTableDisciplineSelect($action='')
	{
		$this->select = new GUI_Select('xDisziplin', 1, $action);
		$this->select->addOptionNone();				// empty item
	}


	/*	printList()
	 *	-----------
	 * Finally, print the <SELECT> list
	 * To preselect an item, provide its ID
	 *		key:	primary key of db table
	 */
	function printList($scoretable, $key)
	{
		require('./config.inc.php');

		// get items from DB
		/*$this->select->addOptionsFromDB("
			SELECT
				DISTINCT(wertungstabelle_punkte.xDisziplin) 
				, Name
			FROM
				wertungstabelle_punkte 
			LEFT JOIN
				disziplin 
			USING(xDisziplin) 
			WHERE
				xWertungstabelle = ".$scoretable." 
			ORDER BY
				Name
		");*/
		
		$this->select->addOptionsFromDB("
			SELECT
				DISTINCT(xDisziplin) 
				, Name
			FROM
				disziplin_" . $_COOKIE['language'] . " 
			ORDER BY
				Anzeige
		");

		if(!empty($GLOBALS['AA_ERROR']))
		{
			AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
		if($key == 0) {
			$key = '-';
		}
		$this->select->selectOption($key);
		$this->select->printList();
	}
} // END CLASS GUI_ScoreTableDisciplineSelect


} // end AA_CL_GUI_SELECT_LIB_INCLUDED

?>
