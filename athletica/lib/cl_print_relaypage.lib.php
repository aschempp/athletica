<?php

if (!defined('AA_CL_PRINT_RELAYPAGE_LIB_INCLUDED'))
{
	define('AA_CL_PRINT_RELAYPAGE_LIB_INCLUDED', 1);


 	include('./lib/cl_print_page.lib.php');


/********************************************
 *
 * PRINT_RelayPage
 *
 *	Class to print relay lists
 *
 *******************************************/

class PRINT_RelayPage extends PRINT_Page
{
	function printHeaderLine()
	{          
		if(($this->lpp - $this->linecnt) < 12)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
        
?>
	<tr>
		<th class='relay_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='relay_name'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='relay_cat'><?php echo $GLOBALS['strCategoryShort']; ?></th>
		<th class='relay_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='relay_disc'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='relay_perf'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($name, $cat, $club, $disc, $perf, $nbr)
	{          
		if(($this->lpp - $this->linecnt) < 11)		// page break check
		{   
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
        
?>
	<tr>
		<td class='relay_nbr'><?php echo $nbr; ?></td>
		<td class='relay_name'><?php echo $name; ?></td>
		<td class='relay_cat'><?php echo $cat; ?></td>
		<td class='relay_club'><?php echo $club; ?></td>
		<td class='relay_disc'><?php echo $disc; ?></td>
		<td class='relay_perf'><?php echo $perf; ?></td>
	</tr>
<?php
		$this->linecnt++;
	}


	function printAthletes($athletes)
	{   		
        // count more lines if string is to long ($athletes string)  
        $t = 0;
        $w = AA_getStringWidth($athletes, 12);
        $t = ceil(($w / 600));        
        $this->linecnt+=$t;         
		      
        
        // different space of browsers by empty line
        if (empty($athletes) && (preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']))) {    
              $this->linecnt++; 
        }
        elseif (empty($athletes)) {                   
               $this->linecnt=$this->linecnt + 0.3; 
        }
      
       if (preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']) && empty($athletes)) {     // force IE to print empty line like firefox  
?>
	<tr>
		<td class='relay_athletes' colspan='6'><?php echo $athletes; ?>&nbsp;</td>     
	</tr>
<?php
       }
       else {
?>
    <tr>
        <td class='relay_athletes' colspan='6'><?php echo $athletes; ?></td>
    </tr>
<?php  
           
       }
	}



	function printSubTitle($title)
	{   
		if(($this->lpp - $this->linecnt) < 16)		// page break check
		{
			$this->insertPageBreak();
		}
		$this->linecnt = $this->linecnt + 2;	// needs two lines (see style sheet)
?>
		<div class='hdr2'><?php echo $title; ?></div>
<?php
	}

} // end PRINT_RelayPage


/********************************************
 *
 * PRINT_CatRelayPage
 *
 *	Class to print relay lists per categroy
 *
 *******************************************/

class PRINT_CatRelayPage extends PRINT_RelayPage
{
	function printHeaderLine()
	{
		if(($this->lpp - $this->linecnt) < 13)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='relay_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='relay_name'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='relay_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='relay_disc'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='relay_perf'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($name, $club, $disc, $perf, $nbr)
	{
		if(($this->lpp - $this->linecnt) < 12)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='relay_nbr'><?php echo $nbr; ?></td>
		<td class='relay_name'><?php echo $name; ?></td>
		<td class='relay_club'><?php echo $club; ?></td>
		<td class='relay_disc'><?php echo $disc; ?></td>
		<td class='relay_perf'><?php echo $perf; ?></td>
	</tr>
<?php
		$this->linecnt++;
	}

} // end PRINT_CatRelayPage



/********************************************
 *
 * PRINT_ClubRelayPage
 *
 *	Class to print relay lists per club
 *
 *******************************************/

class PRINT_ClubRelayPage extends PRINT_RelayPage
{
	function printHeaderLine()
	{
		if(($this->lpp - $this->linecnt) < 9)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='relay_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='relay_name'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='relay_cat'><?php echo $GLOBALS['strCategoryShort']; ?></th>
		<th class='relay_disc'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='relay_perf'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($name, $cat, $disc, $perf, $nbr)
	{  
		if(($this->lpp - $this->linecnt) < 8)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
        
?>
	<tr>
		<td class='relay_nbr'><?php echo $nbr; ?></td>
		<td class='relay_name'><?php echo $name; ?></td>
		<td class='relay_cat'><?php echo $cat; ?></td>
		<td class='relay_disc'><?php echo $disc; ?></td>
		<td class='relay_perf'><?php echo $perf; ?></td>
	</tr>
<?php
		$this->linecnt++;
	}

} // end PRINT_ClubRelayPage


/********************************************
 *
 * PRINT_CatDiscRelayPage
 *
 *	Class to print relay lists per categroy and discipline
 *
 *******************************************/

class PRINT_CatDiscRelayPage extends PRINT_RelayPage
{
	function printHeaderLine()
	{  
		if(($this->lpp - $this->linecnt) < 13)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='relay_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='relay_name'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='relay_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='relay_perf'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($name, $club, $perf, $nbr)
	{
		if(($this->lpp - $this->linecnt) < 12)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='relay_nbr'><?php echo $nbr; ?></td>
		<td class='relay_name'><?php echo $name; ?></td>
		<td class='relay_club'><?php echo $club; ?></td>
		<td class='relay_perf'><?php echo $perf; ?></td>
	</tr>
<?php
		$this->linecnt++;
	}

} // end PRINT_CatDiscRelayPage



/********************************************
 *
 * PRINT_ClubCatRelayPage
 *
 *	Class to print relay lists per categroy and discipline
 *
 *******************************************/

class PRINT_ClubCatRelayPage extends PRINT_RelayPage
{
	function printHeaderLine()
	{
		if(($this->lpp - $this->linecnt) < 13)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='relay_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='relay_name'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='relay_disc'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='relay_perf'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($name, $disc, $perf, $nbr)
	{   
		if(($this->lpp - $this->linecnt) < 12)		// page break check
		{   
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='relay_nbr'><?php echo $nbr; ?></td>
		<td class='relay_name'><?php echo $name; ?></td>
		<td class='relay_disc'><?php echo $disc; ?></td>
		<td class='relay_perf'><?php echo $perf; ?></td>
	</tr>
<?php
		$this->linecnt++;
	}

} // end PRINT_ClubCatRelayPage



/********************************************
 *
 * PRINT_ClubCatDiscRelayPage
 *
 *	Class to print relay lists per club, categroy and discipline
 *
 *******************************************/

class PRINT_ClubCatDiscRelayPage extends PRINT_RelayPage
{
	function printHeaderLine()
	{     
		if(($this->lpp - $this->linecnt) < 11)		// page break check
		{  
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='relay_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='relay_name'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='relay_perf'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($name, $perf, $nbr)
	{   //echo " pirnt line linecnt=$this->linecnt";
		if(($this->lpp - $this->linecnt) < 10)		// page break check
		{   
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='relay_nbr'><?php echo $nbr; ?></td>
		<td class='relay_name'><?php echo $name; ?></td>
		<td class='relay_perf'><?php echo $perf; ?></td>
	</tr>
<?php
		$this->linecnt++;
	}
    
    function printAthletes($athletes)
    {           
         // count more lines if string is to long ($athletes string)  
        $t = 0;
        $w = AA_getStringWidth($athletes, 12);           
        $t = ceil(($w / 350));   
        $this->linecnt+=$t;         
        
        // different space of browsers by empty line
        if (empty($athletes) && (preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']))) {    
                 $this->linecnt++;  
        }
        elseif (empty($athletes)) {
             $this->linecnt=$this->linecnt + 0.6; 
        }
      
        if (preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']) && empty($athletes)) {    // force IE to print empty line like firefox
?>
    <tr>
        <td class='relay_athletes' colspan='6'><?php echo $athletes; ?>&nbsp;</td>
    </tr>
<?php
        }
        else {
?>
    <tr>
        <td class='relay_athletes' colspan='6'><?php echo $athletes; ?></td>
    </tr>
<?php            
        }
    }
    
    function printSubTitle($title)
    {    
        if(($this->lpp - $this->linecnt) < 13)        // page break check
        {
            $this->insertPageBreak();
        }
        $this->linecnt = $this->linecnt + 2;    // needs two lines (see style sheet)
?>
        <div class='hdr2'><?php echo $title; ?></div>
<?php
    }


} // end PRINT_ClubCatDiscRelayPage



} // end AA_CL_PRINT_RELAYPAGE_LIB_INCLUDED
?>
