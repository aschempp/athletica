<?php

if (!defined('AA_CL_EXPORT_ENTRYPAGE_LIB_INCLUDED'))
{
	define('AA_CL_EXPORT_ENTRYPAGE_LIB_INCLUDED', 1);


 	include('./lib/cl_export_page.lib.php');

    /********************************************
     *
     * EXPORT_EntryPage
     *
     *	Class to export entry list (used as startnumber export)
     *
     *******************************************/

    class EXPORT_EntryPage extends EXPORT_Page
    {
	    function printHeaderLine()
	    {
		    if($this->headerPrinted){
			    return;
		    }
		    
		    $this->printCsvLine(array($GLOBALS['strStartnumber'], $GLOBALS['strName'], $GLOBALS['strYearShort']));
		    $this->headerPrinted = true;
	    }
	    
	    
	    function printLine($nbr, $name, $year, $cat, $club, $disc, $ioc, $paid='')
	    {
		    
		    $this->printCsvLine(array($nbr, $name, $year));
		    
	    }
	    
	    
	    function printSubTitle($title)
	    {
		    
	    }

    } // end PRINT_EntryPage

}

?>
