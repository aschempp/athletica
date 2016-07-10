<?php

if (!defined('AA_CL_PRINT_ENTRYPAGE_LIB_INCLUDED'))
{
	define('AA_CL_PRINT_ENTRYPAGE_LIB_INCLUDED', 1);


 	include('./lib/cl_print_page.lib.php');   


/********************************************
 *
 * PRINT_EntryPage
 *
 *	Class to print basic entry lists
 *
 *******************************************/


class PRINT_EntryPage extends PRINT_Page
{
	function printHeaderLine()
	{  
		if(($this->lpp - $this->linecnt) < 2)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='entry_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='entry_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='entry_year'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='entry_ioc'><?php echo $GLOBALS['strCountry']; ?></th>
		<th class='entry_cat'><?php echo $GLOBALS['strCategoryShort']; ?></th>
		<th class='entry_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='entry_disc'><?php echo $GLOBALS['strDisciplines']; ?></th> 
		<?php
		if(isset($_GET['payment']) && isset($_GET['discgroup'])){
			?>
		<th class='entry_year'>&nbsp;</th>
			<?php
		}
		?>
	</tr>
<?php
		$this->linecnt+=2;
	}


	function printLine($nbr, $name, $year, $cat, $club, $disc, $ioc, $paid='')
	{  
        // count more lines if string is to long (club string)  
        $t1 = 0;
        $w = AA_getStringWidth($club, 12);
        $t1 = ceil(($w / 157));          
        
        // count more lines if string is to long (discipline string)
        $w = AA_getStringWidth($disc, 12);
        $t = ceil(($w / 180));      
        if ($t >= $t1){
            $this->linecnt += $t;   
        }
        else {
             $this->linecnt += $t1;     
        }
        
        
		if(($this->lpp - $this->linecnt) < -2)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
		
?>
	<tr>
		<td class='entry_nbr'><?php echo $nbr; ?></td>
		<td class='entry_name'><?php echo $name; ?></td>
		<td class='entry_year'><?php echo $year; ?></td>
		<td class='entry_ioc'><?php echo $ioc; ?></td>
		<td class='entry_cat'><?php echo $cat; ?></td>
		<td class='entry_club'><?php echo $club; ?></td>
		<td class='entry_disc'><?php echo $disc; ?></td> 
		<?php
		if(isset($_GET['payment']) && isset($_GET['discgroup'])){
			?>
		<td class='entry_year'><?php echo $paid; ?></td>
			<?php
		}
		?>
	</tr>
<?php
		
		
	}
 
	function printSubTitle($title)
	{
		if(($this->lpp - $this->linecnt) < 9)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
		$this->linecnt = $this->linecnt + 2;	// needs 2 lines (see style sheet)
?>
		<div class='hdr2'><?php echo $title; ?></div>
        
<?php
	}

} // end PRINT_EntryPage


/********************************************
 *
 * PRINT_CatEntryPage
 *
 *	Class to print entry lists per category
 *
 *******************************************/


class PRINT_CatEntryPage extends PRINT_EntryPage
{
	function printHeaderLine()
	{
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak(); 
			printf("<table>");  
		}
?>
	<tr>
		<th class='entry_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='entry_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='entry_year'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='entry_ioc'><?php echo $GLOBALS['strCountry']; ?></th>
		<th class='entry_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='entry_disc'><?php echo $GLOBALS['strDisciplines']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($nbr, $name, $year, $club, $disc, $ioc)
	{
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='entry_nbr'><?php echo $nbr; ?></td>
		<td class='entry_name'><?php echo $name; ?></td>
		<td class='entry_year'><?php echo $year; ?></td>
		<td class='entry_ioc'><?php echo $ioc; ?></td>
		<td class='entry_club'><?php echo $club; ?></td>
		<td class='entry_disc'><?php echo $disc; ?></td>
	</tr>
<?php
		// count more lines if string is to long (club string)  
        $t1 = 0;
        $w = AA_getStringWidth($club, 12);
        $t1 = ceil(($w / 157));          
        
        // count more lines if string is to long (discipline string)
        $w = AA_getStringWidth($disc, 12);
        $t = ceil(($w / 180));      
        if ($t >= $t1){
            $this->linecnt += $t;   
        }
        else {
             $this->linecnt += $t1;     
        }
		
		//$this->linecnt++;
	}

} // end PRINT_CatEntryPage



/********************************************
 *
 * PRINT_ClubEntryPage
 *
 *	Class to print entry lists per club
 *
 *******************************************/


class PRINT_ClubEntryPage extends PRINT_EntryPage
{
	function printHeaderLine()
	{
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='entry_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='entry_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='entry_year'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='entry_ioc'><?php echo $GLOBALS['strCountry']; ?></th>
		<th class='entry_cat'><?php echo $GLOBALS['strCategoryShort']; ?></th>
		<th class='entry_disc'><?php echo $GLOBALS['strDisciplines']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($nbr, $name, $year, $cat, $disc, $ioc)
	{
		if(($this->lpp - $this->linecnt) < 4)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='entry_nbr'><?php echo $nbr; ?></td>
		<td class='entry_name'><?php echo $name; ?></td>
		<td class='entry_year'><?php echo $year; ?></td>
		<td class='entry_ioc'><?php echo $ioc; ?></td>
		<td class='entry_cat'><?php echo $cat; ?></td>
		<td class='entry_disc'><?php echo $disc; ?></td>
	</tr>
<?php
		// count more lines if string is to long (discipline string)
		$w = AA_getStringWidth($disc, 12);
		$t = ceil(($w / 180));
		if($w > 180){
			$this->linecnt += $t;
		}else{
			$this->linecnt++;
		}
		//$this->linecnt++;
	}
    
    
    

} // end PRINT_ClubEntryPage


/********************************************
 *
 * PRINT_CatDiscEntryPage
 *
 *	Class to print entry lists per category and discipline
 *
 *******************************************/


class PRINT_CatDiscEntryPage extends PRINT_EntryPage
{
	function printHeaderLine()
	{
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='entry_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='entry_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='entry_year'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='entry_ioc'><?php echo $GLOBALS['strCountry']; ?></th>
		<th class='entry_club'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='entry_perf'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($nbr, $name, $year, $club, $perf, $ioc)
	{
		if(($this->lpp - $this->linecnt) < 4)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='entry_nbr'><?php echo $nbr; ?></td>
		<td class='entry_name'><?php echo $name; ?></td>
		<td class='entry_year'><?php echo $year; ?></td>
		<td class='entry_ioc'><?php echo $ioc; ?></td>
		<td class='entry_club'><?php echo $club; ?></td>
		<td class='entry_perf'><?php echo $perf; ?></td>
	</tr>
<?php
        // count more lines if string is to long (club string)  
        $t = 0;
        $w = AA_getStringWidth($club, 12);
        $t = ceil(($w / 157));  
		$this->linecnt+=$t;
       
	}

} // end PRINT_CatDiscEntryPage


/********************************************
 *
 * PRINT_ClubCatEntryPage
 *
 *	Class to print entry lists per club and category
 *
 *******************************************/


class PRINT_ClubCatEntryPage extends PRINT_EntryPage
{
	function printHeaderLine()
	{  
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='entry_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='entry_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='entry_year'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='entry_ioc'><?php echo $GLOBALS['strCountry']; ?></th>
		<th class='entry_disc'><?php echo $GLOBALS['strDisciplines']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($nbr, $name, $year, $disc, $ioc)
	{
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='entry_nbr'><?php echo $nbr; ?></td>
		<td class='entry_name'><?php echo $name; ?></td>
		<td class='entry_year'><?php echo $year; ?></td>
		<td class='entry_ioc'><?php echo $ioc; ?></td>
		<td class='entry_disc'><?php echo $disc; ?></td>
	</tr>
<?php
		// count more lines if string is to long (discipline string)
		$w = AA_getStringWidth($disc, 12);
		$t = ceil(($w / 180));
		if($w > 180){
			$this->linecnt += $t;
		}else{
			$this->linecnt++;
		}
		//$this->linecnt++;
	}

} // end PRINT_ClubCatEntryPage



/********************************************
 *
 * PRINT_ClubCatDiscEntryPage
 *
 *	Class to print entry lists per club, category and discipline
 *
 *******************************************/


class PRINT_ClubCatDiscEntryPage extends PRINT_EntryPage
{
	function printHeaderLine()
	{  
		if(($this->lpp - $this->linecnt) < 7)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
?>
	<tr>
		<th class='entry_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='entry_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='entry_year'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='entry_ioc'><?php echo $GLOBALS['strCountry']; ?></th>
		<th class='entry_perf'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
		$this->linecnt++;
	}


	function printLine($nbr, $name, $year, $perf, $ioc)
	{
		if(($this->lpp - $this->linecnt) < 4)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
			$this->printHeaderLine();
		}
?>
	<tr>
		<td class='entry_nbr'><?php echo $nbr; ?></td>
		<td class='entry_name'><?php echo $name; ?></td>
		<td class='entry_year'><?php echo $year; ?></td>
		<td class='entry_ioc'><?php echo $ioc; ?></td>
		<td class='entry_perf'><?php echo $perf; ?></td>
	</tr>
<?php
		$this->linecnt++;
	}

} // end PRINT_PRINT_ClubCatDiscEntryPage



/********************************************
 *
 * PRINT_EnrolementPage
 *
 *	Class to print enrolement lists
 *
 *******************************************/


class PRINT_EnrolementPage extends PRINT_EntryPage
{

	var $event;
	var $cat;
	var $time;
	var $bRelay;
	var $timeinfo;
	var $comb_disc;
	
	function printTitle()
	{   
		// page break check (at least one further line left)
		if(($this->lpp - $this->linecnt) < 7)		
		{
			$this->insertPageBreak();
		}
		$this->linecnt = $this->linecnt + 3;	// needs four lines (see style sheet)
?>
		<table class="enrolmt_disc"><tr>
			<th class='enrolmt_event'><?php echo $this->event; ?></th>
			<th class='enrolmt_cat'><?php echo $this->cat; ?></th>
			<th class='enrolmt_time'><?php echo $this->time; ?></th>
		</tr>
		<tr>
		<th class='enrolmt_comb_disc'><?php echo $this->comb_disc; ?></th> 
		<th class='enrolmt_timeinfo' colspan="2"> 
			<?php echo $this->timeinfo; ?>
		</td></tr>
		</table>
<?php
	}
    
    function printTitleCont()
    {  
        // page break check (at least one further line left)
        if(($this->lpp - $this->linecnt) < 7)        
        {
            $this->insertPageBreak();
        }
        $this->linecnt = $this->linecnt + 2;    // needs four lines (see style sheet)
?>
        <table class="enrolmt_disc"><tr>
            <th class='enrolmt_event'><?php echo $this->event; ?></th>
            <th class='enrolmt_cat'><?php echo $this->cat; ?></th>
            <th class='enrolmt_cont'><?php echo $GLOBALS['strCont'];  ?></th> 
        </tr>
        
        </table>
<?php
    }

	function printHeaderLine($relay, $svm)
	{   
		if(($this->lpp - $this->linecnt) < 4)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			printf("<table>");
		}
		
		$this->bRelay = $relay;
		
		if($relay == FALSE)
		{
?>
	<tr>
		<th class='enrolmt_tic' />
		<th class='enrolmt_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='enrolmt_name'><?php echo $GLOBALS['strName']; ?></th>
		<th class='enrolmt_year'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='enrolmt_year'><?php echo $GLOBALS['strCountry']; ?></th>
		<?php 
		if($svm){
			?>
		<th class='enrolmt_club'><?php echo $GLOBALS['strTeam']; ?></th>
			<?php
		}else{
			?>
		<th class='enrolmt_club'><?php echo $GLOBALS['strClub']; ?></th>
			<?php
		}
		?>
		<th class='enrolmt_top'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php 
		}
		else
		{  
?>
	<tr>
		<th class='enrolmt_tic' />
		<th class='enrolmt_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='enrolmt_name' ><?php echo $GLOBALS['strName']; ?></th>  
        <th class='enrolmt_empty'>&nbsp;</th>
       
		<?php 
		if($svm){
			?>
            <th class='enrolmt_club'><?php echo $GLOBALS['strTeam']; ?></th>   
			<?php
		}else{
			?>
		<th class='enrolmt_club'><?php echo $GLOBALS['strClub']; ?></th>   
			<?php
		}
		?>
        <th class='enrolmt_top'>&nbsp;</th>      
	</tr>
<?php
		}            
		$this->linecnt++;
	}

    function printLineAthlete($athleteLine)
    {       
      if(($this->lpp - $this->linecnt) < 2)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
            $this->printHeaderLine($this->bRelay);
        }   
       
     // relay  
?>
    <tr>
        <td class='enrolmt_tic'>&nbsp;&nbsp;&nbsp;</td>
        <td class='enrolmt_nbr'>&nbsp;</td>    
        <td class='enrolmt_atLine' colspan='6'><?php echo $athleteLine; ?></td>  
    </tr>
<?php
        
        $this->linecnt++;
    
    }
    
	function printLine($nbr,  $name, $year, $club, $ioc, $top, $club2='', $pos)
	{             
		if(($this->lpp - $this->linecnt) < 0)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak(); 			
            $this->printTitleCont(); 
            printf("<table>"); 
			$this->printHeaderLine($this->bRelay);
             
		}

		if(!$this->bRelay)		// athlete
		{      
?>
	<tr>
		<td class='enrolmt_tic'>[&nbsp;&nbsp;&nbsp;]</td>
		<td class='enrolmt_nbr'><?php echo $nbr; ?></td> 
		<td class='enrolmt_name'><?php echo $name; ?></td>
		<td class='enrolmt_year'><?php echo $year; ?></td>
		<td class='enrolmt_year'><?php echo $ioc; ?></td>
		<td class='enrolmt_club'><?php echo $club; ?></td>
		<td class='enrolmt_top'><?php echo $top; ?></td>
	</tr>
<?php

		}
		else		// relay
		{  
?>
	<tr>
		<td class='enrolmt_tic'>[&nbsp;&nbsp;&nbsp;]</td>
		<td class='enrolmt_nbr'><?php echo $nbr; ?></td>
        <td class='enrolmt_name'><?php echo $name; ?></td>  
        <td class='enrolmt_empty'>&nbsp;</td>  
		<td class='enrolmt_club'><?php echo $club2; ?></td> 
        <td class='enrolmt_top'>&nbsp;</td>     
	</tr>
<?php
		}
		$this->linecnt++;
	}

} // end PRINT_EnrolementPage

/********************************************
 *
 * PRINT_ReceiptEntryPage
 *
 *    Class to print receipt entry lists
 *
 *******************************************/


class PRINT_ReceiptEntryPage extends PRINT_Page
{
    function printHeader($mname,$mDateFrom,$mDateTo,$stadion,$organisator)
    {   
        if(($this->lpp - $this->linecnt) < 6)        // page break check
        {    
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
        }
        
    printf("<table class='dialog'>"); 
?>
    <table>
    <tr>
        <th class='receipt'><?php echo $GLOBALS['strReceipt']; ?></th>  
    </tr>
    <tr>
        <td>&nbsp; 
        </td>
    </tr>
    </table>
    <table>
    
    <tr>
        <th class='receipt_hd_meeting'><?php echo $GLOBALS['strMeetingTitle']. ":  " ?></th>
        <td class='receipt_hd_meeting'><?php echo $mname; ?></td>
          
         <th class='receipt_hd_date'><?php echo $GLOBALS['strDate']. ":  " ?></th>  
        <td class='receipt_hd_date'><?php echo $mDateFrom ." / ". $mDateTo?></td> 
    </tr>
    <tr>
        <th class='receipt_hd_stadion'><?php echo $GLOBALS['strStadium']. ":  " ?></th>
        <td class='receipt_hd_stadion'><?php echo $stadion; ?></td>
        
         <th class='receipt_hd_organizer'><?php echo $GLOBALS['strOrganizer']. ":  " ?></th>  
        <td class='receipt_hd_organizer'><?php echo $organisator; ?></td> 
    </tr>   
    
<?php
        $this->linecnt+=17;  
    }   
    
    function printHeaderLineCont()
    {   
        if(($this->lpp - $this->linecnt) < 6)        // page break check
        {   
            printf("</table>");
            $this->insertPageBreak();  
            printf("<table>");
        }    
        
?>
       <table width="100%">   
            <tr>        
                <th class='receipt_cl_name' colspan='4'><?php echo $GLOBALS['strParticipant']. " " . $GLOBALS['strCont'];   ?></th>  
            </tr> 
            <tr>
                <td colspan='4'> 
<?php
        $this->linecnt=4;  
    }       

    function printLine1($nbr, $name, $year)           // page per athlet (print athlet name and age)
    {  
        if(($this->lpp - $this->linecnt) < 6)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
            $this->printHeader();
        }  
?>
    <tr>
        <th class='receipt_name'><?php echo $GLOBALS['strName']. ":  " ?></th>
        <td class='receipt_name'><?php echo $name; ?></td>           
         <th class='receipt_year'><?php echo $GLOBALS['strYear']. ":  " ?></th>  
        <td class='receipt_year'><?php echo $year; ?></td> 
    </tr>
<?php
       
      $this->linecnt++;  
    }
    
    function printLine2($club,$cat)                    // page per athlet (print athlet club and cat)    
    {  
        if(($this->lpp - $this->linecnt) < 6)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
            $this->printHeader();
        }
          ?>
    <tr>
        <th class='receipt_club'><?php echo $GLOBALS['strClub']. ":  " ?></th>
        <td class='receipt_club'><?php echo $club; ?></td>           
         <th class='receipt_cat'><?php echo $GLOBALS['strCategory']. ":  " ?></th>  
        <td class='receipt_cat'><?php echo $cat; ?></td> 
    </tr>
<?php
       $this->linecnt++;   
    }  
    
     function printLine3($disc)                          // page per athlet (print disciplines)    
    {      
?>
        <tr>
        <th class='receipt_disc'><?php echo $GLOBALS['strDisciplines']. ":  " ?></th>
        <?php    
         // print seperate lines per discipline 
         
         $disc_arr = split(",",$disc);  
         $i=0;    
         
         foreach ($disc_arr as $key)
            { 
              if(($this->lpp - $this->linecnt) < 6)        // page break check
                {
                printf("</table>");
                $this->insertPageBreak();
                printf("<table>");
                $this->printHeader();
            }  
             $this->linecnt++;
            
             if ($i==0) {     
             ?>                  
                    <td colspan='3' class='receipt_disc'><?php echo $key; ?></td>  
                    </tr>   
             <?php 
             }
             else
                {
               ?>   
                    <tr>
                    <td>&nbsp;</td>  
                    <td colspan='3' class='receipt_disc'><?php echo $key; ?></td>  
                    </tr>  
             <?php 
             } 
             $i++;
         }  
    }  
    
   function printLineFee($fee='')                      // page per athlet (print fee)    
    {   
        if(($this->lpp - $this->linecnt) < 6)        // page break check
            {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
            $this->printHeader();
        }  
?>
     <tr>
        <td>&nbsp;
        </td>
        <tr>  
    <tr>
     
        <th class='receipt_fee' ><?php  echo  $GLOBALS['strTotal'] . " " . $GLOBALS['strFee']. ":  " ?></th> 
        
        <?php
      //  if ($fee>0){
      ?>
            <td class='receipt_fee'><?php echo $GLOBALS['strCHF'] . " &nbsp;&nbsp;" . $fee. ".00" ?></td>    
        <?php
       // }
        ?>
    </tr>
<?php            
        $this->linecnt++;  
    } 
    
  
    
     function printLineFooter($fee='',$date, $place, $list)
    {  
        if(($this->lpp - $this->linecnt) < 6)        // page break check
            {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
            $this->printHeader();
        }   
?>


     <tr>
        <td>&nbsp;
        </td>
     </tr>  
  <?php   
     if ($list) {
         ?>
          <tr>  
        <th class='receipt_cl_fee' ><?php  echo  $GLOBALS['strTotal'] . " " . $GLOBALS['strFee']. ":  " ?></th>   
        <td class='receipt_cl_fee'> &nbsp;</td>      
        <td class='receipt_cl_fee'> &nbsp;</td>    
        <td class='receipt_cl_fee_bold'><?php echo $GLOBALS['strCHF'] . " &nbsp;&nbsp;" . $fee. ".00" ?></td>   
        </tr> 
        <?php 
     }
     else {
      ?>
        <tr>  
        <th class='receipt_cl_fee' ><?php  echo  $GLOBALS['strTotal'] . " " . $GLOBALS['strFee']. ":  " ?></th>   
        <td class='receipt_fee_bold' ><?php echo $GLOBALS['strCHF'] . " &nbsp;&nbsp;" . $fee. ".00" ?></td>  
        <td class='receipt_cl_fee' colspan='2'> &nbsp;</td> 
        </tr>
     <?php
    }
    ?>
  
    <tr>
        <td>&nbsp;
        </td>
     </tr>  
    <tr>       
        <th class='receipt_date' ><?php  echo  $GLOBALS['strDate']. ":  "  ?></th> 
        <td class='receipt_date'><?php  echo $date; ?></td> 
        <th class='receipt_place' ><?php  echo  $GLOBALS['strPlace']. ":  "  ?></th> 
        <td class='receipt_place'><?php  echo $place; ?></td>    
    </tr>
     <tr>
        <td>&nbsp;
        </td>
     </tr>  
      
    <table>    
        <tr>   
            <th  class='receipt_subscribe' ><?php  echo  $GLOBALS['strSubscribe']. ":  "  ?></th>         
        </tr>
    </table>    
     
<?php    
      printf("</table>");       
      $this->linecnt++;  
    }

   
    
    function printLine4($first, $name, $year, $cat ,$disc, $fee)
    {   
        if(($this->lpp - $this->linecnt) < 6)        // page break check
        {   
           printf("</table>");                               
           $this->insertPageBreak(); 
           $this->printHeaderLineCont(); 
        }  
?>  

    <table>
    <tr>  
        <td class='receipt_cl_name'><?php echo $name; ?></td>  
        <td class='receipt_cl_year'><?php echo $year ?></td>   
        <td class='receipt_cl_cat'><?php echo $cat; ?></td>   
        <td class='receipt_cl_disc'><?php echo $disc; ?></td>  
        <td class='receipt_cl_fee'><?php echo $GLOBALS['strCHF'] . " &nbsp;&nbsp;" . $fee . ".00" ?></td> 
    </tr>
    </table> 
<?php
      $textWidth=AA_getStringWidth($disc,12); 
      $countLine=ceil(($textWidth/240));           // calculate lines of disziplines   
      $this->linecnt+=$countLine;                
    }
   
   function printLineClub($club)
    {  
        if(($this->lpp - $this->linecnt) < 6)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
            $this->printHeader();
        }
          ?>
    <tr>
        <th class='receipt_cl_club'><?php echo $GLOBALS['strClub']. ":  " ?></th>
        <td class='receipt_cl_club' colspan='3'><?php echo $club; ?></td>   
    </tr>
    <tr>
        <td>&nbsp;
        </td>
     </tr>  
    <tr>  
         <th class='receipt_cl_part' colspan='4'><?php echo $GLOBALS['strParticipant']. "  " ?></th>  
    </tr>  
<?php
       $this->linecnt++;   
    }   
   
     function  printLineBreak($count)
    {                                  
        if(($this->lpp - $this->linecnt) < 6)        // page break check
            {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
        }  
         
       for ($i=0;$i<$count;$i++){ 
            $this->linecnt++;  
?>    
        <tr>
        <td>&nbsp;
        </td>
        </tr>  
<?php
       }
    }
        

} // end PRINT_EntryPage

/********************************************
 *
 * PRINT_ClubEntryPayedPage
 *
 *    Class to print entry lists per club
 *
 *******************************************/


class PRINT_ClubEntryPayedPage extends PRINT_EntryPage
{
    function printHeaderLine($max_count = 2)
    {
        if(($this->lpp - $this->linecnt) < 16)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
        }
?>
    <tr>
        <th class='entry_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
        <th class='entry_name'><?php echo $GLOBALS['strName']; ?></th>
        <th class='entry_year'><?php echo $GLOBALS['strYearShort']; ?></th>        
        <th class='entry_cat'><?php echo $GLOBALS['strCategoryShort']; ?></th>        
        <th class='entry_disc' colspan="<?php echo $max_count; ?>"><?php echo $GLOBALS['strPayedShort'] . " / " . $GLOBALS['strDisciplines']; ?></th>
    </tr>
<?php
        $this->linecnt++;
    }


    function printLine($nbr, $name, $year, $cat, $disc, $len)
    {   $len = $len -1;
    
        if(($this->lpp - $this->linecnt) < 15)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
            $this->printHeaderLine();
        }        
   
        $i = 0;
        $c = 0;
        
        // print more lines if disciplines more than 5 (5 disciplines per line)
         while ($len > 0){
             $pos = strpos($disc, "</td>");
             if ($i % 10 == 0){                
               $c++;
             }
             $pos = $pos+5;
             $arr_disc[$c] .= substr($disc, 0, $pos);
             $disc = substr($disc, $pos);
             $i++;
             $len -= 1;
         }
         
        foreach ($arr_disc as $key => $val){
            
            if ($key == 1){
             ?>
            <tr>
                <td class='entry_nbr'><?php echo $nbr; ?></td>
                <td class='entry_name'><?php echo $name; ?></td>
                <td class='entry_year'><?php echo $year; ?></td>       
                <td class='entry_cat'><?php echo $cat; ?></td>
                <?php echo $val; ?>
            </tr>
            <?php
            }
            else {
                 ?>
                <tr>  
                    <td class='entry_nbr' colspan="4"></td>             
                    <?php echo $val; ?>
                </tr>
                <?php
            }
        }
  
        // count more lines if more than 4 disziplines
        
        if($len > 10){
            $this->linecnt += 2;
        }else{
            $this->linecnt++;
        }        
    }
    
    function printSubTitle($title)
    {
        if(($this->lpp - $this->linecnt) < 9)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
        }
        $this->linecnt = $this->linecnt + 2;    // needs 2 lines (see style sheet)
?>
        <div class='hdr2'><?php echo $title; ?></div>
        
<?php
    }
    
    
    

} // end PRINT_ClubEntryPage


} // end AA_CL_PRINT_ENTRYPAGE_LIB_INCLUDED
?>
