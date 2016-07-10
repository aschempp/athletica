<?php

if (!defined('AA_CL_PRINT_TEAMPAGE_PDF_LIB_INCLUDED'))
{
	define('AA_CL_PRINT_TEAMPAGE_PDF_LIB_INCLUDED', 1);


 	include('./lib/cl_print_relaypage_pdf.lib.php');

 /********************************************
 *
 * PRINT_TeamsPage
 *
 *    Class to print team lists
 *
 *******************************************/


class PRINT_TeamsPage_pdf extends PRINT_RelayPage_pdf
{
	var $width;
    function printHeaderLine()
    {	// page break check
        if($this->lp < 4)        
        {
            $this->insertPageBreak();
        }
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(509,array($GLOBALS['strStartnumber'],25,0,0),
		array($GLOBALS['strName'],234,1,0),array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strDisciplines'],234,1,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDisciplines'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
    }


    function printDiscHeaderLine() //seems unsused
    {
        if(($this->lpp - $this->linecnt) < 4)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table>");
        }
?>
    <tr>
        <th class='team_disc'><?php echo $GLOBALS['strDiscipline']; ?></th>
        <th class='team_disc_name'><?php echo $GLOBALS['strName']; ?></th>
        <th class='team_disc_year'><?php echo $GLOBALS['strYearShort']; ?></th>
    </tr>
<?php
        $this->linecnt++;
    }


    function printLine($nbr, $name, $year, $disc)
    {	// page break check
        if($this->lp < 2)        
        {
            $this->insertPageBreak();
            $this->printHeaderLine();
        }
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],'left',-10,0,$lnqnty);
		$this->lp-=12*$lnqnty;
    }

    function printDiscLine($disc, $name, $year)//seems unsused
    {
        if(($this->lpp - $this->linecnt) < 2)        // page break check
        {
            printf("</table>");
            $this->insertPageBreak();
            printf("<table class='sheet'>");
        }
        $this->linecnt++;            // increment line count
?>
    <tr>
        <td class='team_disc'><?php echo $disc; ?></td>
        <td class='team_disc_name'><?php echo $name; ?></td>
        <td class='team_disc_year'><?php echo $year; ?></td>
    </tr>
<?php
    }


} // end PRINT_TeamsPage
    
/********************************************
 *
 * PRINT_TeamPage
 *
 *	Class to print team lists
 *
 *******************************************/


class PRINT_TeamPage_pdf extends PRINT_RelayPage_pdf
{
  var $event;
  var $cat;
  var $time;
  var $timeinfo;
	var $width;     
  
  function printTitle($title)
  {   

    // page break check (at least one further line left)
		if($this->lp < 179)		//This, header, 6 lines (used often in 100m) and footer should fit on page=56+15+6*12+36=179
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",16,40,$this->lp,257,30,'left bottom',40,0);   //23
		$this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,256,30,'right bottom',0,2);
		
		$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,515,30,'right bottom',12,2);
  }
  
	function printHeaderLine($enrolSheet=false)
	{	// page break check
		if($this->lp < 138)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+24=138
		{
			$this->insertPageBreak();
		}
		$x=0;
		if  ($enrolSheet) {
			$x=16;
    }
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(423-$x,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],156,1,0),
		array($GLOBALS['strCategoryShort'],30,0,0),array($GLOBALS['strClub'],132,1,0),array($GLOBALS['strDiscipline'],80,0,0));  //fixed length of 40 for the last two columns (with space:42)
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40+$x,$this->lp,$this->width[0],30,'left bottom',17,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCategoryShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyValue'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',-17,0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyRank'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',0,0,$lnqnty);
    $this->lp-=$lnqnty*12;
    
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	} 

	function printLine($name, $cat, $club, $disc, $perf, $nbr, $enrolSheet, $quali, $teamPerf)
	{	//page breack check
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$x=0;
		$this->lp-=10;
		if  ($enrolSheet) {
			$x=16;
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,16,30,'left bottom',0,0);
    }
    if ($quali==0) {$quali=''; }
    if ($teamPerf==0) {$teamPerf=''; }
		$this->printTextLineBox($nbr,$this->font,"",10,40+$x,$this->lp,$this->width[0],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($cat,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],'left',-10,0,$lnqnty);
		$this->printTextLineBox($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',10,0);
    $this->printTextLineBox($quali,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,0); 
    $this->printTextLineBox($teamPerf,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;		
	}     

} // end PRINT_TeamPage

 /********************************************
 *
 * PRINT_ClubTeamPage
 *
 *    Class to print team lists
 *
 *******************************************/


class PRINT_ClubTeamPage_pdf extends PRINT_RelayPage_pdf
{
  var $event;
  var $cat;
  var $time;
  var $timeinfo;
	var $width;     
  
  function printTitle($title)
  {   

    // page break check (at least one further line left)
		if($this->lp < 179)		//This, header, 6 lines (used often in 100m) and footer should fit on page=56+15+6*12+36=179
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",16,40,$this->lp,257,30,'left bottom',40,0);   //23
		$this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,256,30,'right bottom',0,2);
		
		$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,515,30,'right bottom',12,2);
  }
  
    function printHeaderLine($enrolSheet=false)
    {	// page break check
		if($this->lp < 138)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+24=138
		{
			$this->insertPageBreak();
		}
		$x=0;
		if  ($enrolSheet) {
			$x=16;
        }
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(425-$x,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],200,1,0),      //fixed length of 40 for the last two columns
		array($GLOBALS['strCategoryShort'],30,0,0),array($GLOBALS['strDiscipline'],80,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40+$x,$this->lp,$this->width[0],30,'left bottom',17,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCategoryShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyValue'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',-17,0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyRank'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',0,0,$lnqnty);
    $this->lp-=$lnqnty*12;
    
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
    }     

    function printLine($name, $cat, $disc, $perf, $nbr,$enrolSheet, $quali, $teamPerf)
    {	// page break check
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$x=0;
		$this->lp-=10;
		if  ($enrolSheet) {
			$x=16;
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,16,30,'left bottom',0,0);
    }
    if ($quali==0) {$quali=''; }
    if ($teamPerf==0) {$teamPerf=''; }
		$this->printTextLineBox($nbr,$this->font,"",10,40+$x,$this->lp,$this->width[0],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($cat,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
    $this->printTextLineBox($quali,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,0); 
    $this->printTextLineBox($teamPerf,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;	
    } 

} // end PRINT_ClubTeamPage

/********************************************
 *
 * PRINT_CatTeamPage
 *
 *    Class to print team lists
 *
 *******************************************/


class PRINT_CatTeamPage_pdf extends PRINT_RelayPage_pdf
{
  var $event;
  var $cat;
  var $time;
  var $timeinfo;
	var $width;     
  
  function printTitle($title)
  {   

    // page break check (at least one further line left)
		if($this->lp < 179)		//This, header, 6 lines (used often in 100m) and footer should fit on page=56+15+6*12+36=179
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",16,40,$this->lp,257,30,'left bottom',40,0);   //23
		$this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,256,30,'right bottom',0,2);
		
		$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,515,30,'right bottom',12,2);
  }
  
    function printHeaderLine($enrolSheet=false)
    {	//page break check
		if($this->lp < 138)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+24=138
		{
			$this->insertPageBreak();
		}
		$x=0;
		if  ($enrolSheet) {
			$x=16;
    }
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(425-$x,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],180,1,0),
		array($GLOBALS['strClub'],140,1,0),array($GLOBALS['strDiscipline'],80,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40+$x,$this->lp,$this->width[0],30,'left bottom',17,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyValue'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',-17,0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyRank'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',0,0,$lnqnty);
    $this->lp-=$lnqnty*12;
    
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
    }     

    function printLine($name, $club, $disc, $perf, $nbr,$enrolSheet, $quali, $teamPerf)
    {
		if($this->lp < 62)		// Footer + line + athleteLine=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$x=0;
		$this->lp-=10;
		if  ($enrolSheet) {
			$x=16;
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,16,30,'left bottom',0,0);
    }
    if ($quali==0) {$quali=''; }
    if ($teamPerf==0) {$teamPerf=''; }
		$this->printTextLineBox($nbr,$this->font,"",10,40+$x,$this->lp,$this->width[0],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],'left',0,0,$lnqnty);
		$this->printTextLineBox($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',10,0);
    $this->printTextLineBox($quali,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,0); 
    $this->printTextLineBox($teamPerf,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;		
    }     
                
} // end PRINT_CatTeamPage

/********************************************
 *
 * PRINT_ClubCatTeamPage
 *
 *    Class to print team lists
 *
 *******************************************/


class PRINT_ClubCatTeamPage_pdf extends PRINT_RelayPage_pdf
{	
  var $event;
  var $cat;
  var $time;
  var $timeinfo;
	var $width;     
  
  function printTitle($title)
  {   

    // page break check (at least one further line left)
		if($this->lp < 179)		//This, header, 6 lines (used often in 100m) and footer should fit on page=56+15+6*12+36=179
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",16,40,$this->lp,257,30,'left bottom',40,0);   //23
		$this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,256,30,'right bottom',0,2);
		
		$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,515,30,'right bottom',12,2);
  }
  
    function printHeaderLine($enrolSheet=false)
    {
		if($this->lp < 138)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+24=138
		{
			$this->insertPageBreak();
		}
		$x=0;
		if  ($enrolSheet) {
			$x=16;
        }
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(427-$x,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],322,1,0),
		array($GLOBALS['strDiscipline'],80,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40+$x,$this->lp,$this->width[0],30,'left bottom',17,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyValue'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',-17,0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyRank'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',0,0,$lnqnty);
    $this->lp-=$lnqnty*12;
    
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
    }     

    function printLine($name, $disc, $perf, $nbr, $enrolSheet, $quali, $teamPerf)
    {
		if($this->lp < 62)		// Footer + line + athleteLine=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$x=0;
		$this->lp-=10;
		if  ($enrolSheet) {
			$x=16;
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,16,30,'left bottom',0,0);
    }
    if ($quali==0) {$quali=''; }
    if ($teamPerf==0) {$teamPerf=''; }
		$this->printTextLineBox($nbr,$this->font,"",10,40+$x,$this->lp,$this->width[0],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
    $this->printTextLineBox($quali,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,0); 
    $this->printTextLineBox($teamPerf,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;	
    }     

} // end PRINT_ClubCatTeamPage

/********************************************
 *
 * PRINT_CatDiscTeamPage
 *
 *    Class to print team lists
 *
 *******************************************/


class PRINT_CatDiscTeamPage_pdf extends PRINT_RelayPage_pdf
{
var $event;
  var $cat;
  var $time;
  var $timeinfo;
	var $width;     
  
  function printTitle($title)
  {   

    // page break check (at least one further line left)
		if($this->lp < 179)		//This, header, 6 lines (used often in 100m) and footer should fit on page=56+15+6*12+36=179
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",16,40,$this->lp,257,30,'left bottom',40,0);   //23
		$this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,256,30,'right bottom',0,2);
		
		$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,515,30,'right bottom',12,2);
  }
  
    function printHeaderLine($enrolSheet=false)
    {
		if($this->lp < 138)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+24=138
		{
			$this->insertPageBreak();
		}
		$x=0;
		if  ($enrolSheet) {
			$x=16;
        }
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(427-$x,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],220,1,0),
		array($GLOBALS['strClub'],182,1,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40+$x,$this->lp,$this->width[0],30,'left bottom',17,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyValue'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',-17,0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyRank'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',0,0,$lnqnty);
    $this->lp-=$lnqnty*12;       
    
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
    }      
         
    function printLine($name, $club, $perf, $nbr,$enrolSheet, $quali, $teamPerf)
    {
		if($this->lp < 62)		// Footer + line + athleteLine=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$x=0;
		$this->lp-=10;
		if  ($enrolSheet) {
			$x=16;
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,16,30,'left bottom',0,0);
    }
    if ($quali==0) {$quali=''; }
    if ($teamPerf==0) {$teamPerf=''; }
		$this->printTextLineBox($nbr,$this->font,"",10,40+$x,$this->lp,$this->width[0],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],'left',0,0,$lnqnty);
    $this->printTextLineBox($quali,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',10,0); 
    $this->printTextLineBox($teamPerf,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;	
    }   

} // end PRINT_CatDiscTeamPage

/********************************************
 *
 * PRINT_ClubDiscTeamPage
 *
 *    Class to print team lists
 *
 *******************************************/


class PRINT_ClubDiscTeamPage_pdf extends PRINT_RelayPage_pdf
{
  var $event;
  var $cat;
  var $time;
  var $timeinfo;
	var $width;     
  
  function printTitle($title)
  {   

    // page break check (at least one further line left)
		if($this->lp < 179)		//This, header, 6 lines (used often in 100m) and footer should fit on page=56+15+6*12+36=179
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",16,40,$this->lp,257,30,'left bottom',40,0);   //23
		$this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,256,30,'right bottom',0,2);
		
		$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,515,30,'right bottom',12,2);
  }     
  
    function printHeaderLine($enrolSheet=false)
    {
		if($this->lp < 138)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+24=138
		{
			$this->insertPageBreak();
		}
		$x=0;
		if  ($enrolSheet) {
			$x=16;
        }
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(427-$x,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],372,1,0),
		array($GLOBALS['strCategoryShort'],30,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40+$x,$this->lp,$this->width[0],30,'left bottom',17,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCategoryShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyValue'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',-17,0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyRank'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',0,0,$lnqnty);
    $this->lp-=$lnqnty*12;
    
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
    }
                                   
    function printLine($name, $cat, $perf, $nbr,$enrolSheet, $quali, $teamPerf)
    {
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$x=0;
		$this->lp-=10;
		if  ($enrolSheet) {
			$x=16;
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,16,30,'left bottom',0,0);
    }
    if ($quali==0) {$quali=''; }
    if ($teamPerf==0) {$teamPerf=''; }
		$this->printTextLineBox($nbr,$this->font,"",10,40+$x,$this->lp,$this->width[0],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($cat,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
    $this->printTextLineBox($quali,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,0); 
    $this->printTextLineBox($teamPerf,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;	
    }       

} // end PRINT_ClubDiscTeamPage

/********************************************
 *
 * PRINT_ClubCatDiscTeamPage
 *
 *    Class to print team lists
 *
 *******************************************/


class PRINT_ClubCatDiscTeamPage_pdf extends PRINT_RelayPage_pdf
{
  var $event;
  var $cat;
  var $time;
  var $timeinfo;
	var $width;     
  
  function printTitle($title)
  {   

    // page break check (at least one further line left)
		if($this->lp < 179)		//This, header, 6 lines (used often in 100m) and footer should fit on page=56+15+6*12+36=179
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",16,40,$this->lp,257,30,'left bottom',40,0);   //23
		$this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,256,30,'right bottom',0,2);
		
		$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,515,30,'right bottom',12,2);
  }
  
    function printHeaderLine($enrolSheet=false)
    {
		if($this->lp < 138)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+24=138
		{
			$this->insertPageBreak();
		}
		$x=0;
		if  ($enrolSheet) {
			$x=16;
        }
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(429-$x,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],404,1,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40+$x,$this->lp,$this->width[0],30,'left bottom',17,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyValue'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',-17,0,0);
    $lnqnty = $this->printTextFlowSimp($GLOBALS['strQualifyRank'],$this->font,"B",10,$this->posx+2,$this->lp,40,'left',0,0,$lnqnty);
    $this->lp-=$lnqnty*12;  
    
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
    }    

    function printLine($name, $perf, $nbr,$enrolSheet, $quali, $teamPerf)
    {
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$x=0;
		$this->lp-=10;
		if  ($enrolSheet) {
			$x=16;
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,16,30,'left bottom',0,0);
    }
    if ($quali==0) {$quali=''; }
    if ($teamPerf==0) {$teamPerf=''; }
		$this->printTextLineBox($nbr,$this->font,"",10,40+$x,$this->lp,$this->width[0],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
    $this->printTextLineBox($quali,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',10,0); 
    $this->printTextLineBox($teamPerf,$this->font,"",10,$this->posx+2,$this->lp,40,30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;	
    }     

} // end PRINT_ClubCatDiscTeamPage

/********************************************
 *
 * PRINT_TeamDiscPage
 *
 *	Class to print discipline lists per team
 *
 *******************************************/


class PRINT_TeamDiscPage_pdf extends PRINT_RelayPage_pdf
{
	var $width;
	function printHeaderLine()
	{	// page break check
		if($this->lp < 87)		//Footer + 3 lines + header = 36 + 3*12 + 15 = 87
		{
			$this->insertPageBreak();
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(509,array($GLOBALS['strDiscipline'],80,0,0),array($GLOBALS['strStartnumber'],25,0,0),
		array($GLOBALS['strName'],388,1,0),array($GLOBALS['strYearShort'],16,0,0));
	
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($disc, $nbr, $name, $year)
	{	// page break check
		if($this->lp < 48)		//Footer + line=36+12=48
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($disc,$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
		$this->printTextLineBox($nbr,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,2);
	}


} // end PRINT_TeamDiscPage

} // end AA_CL_PRINT_TEAMPAGE_LIB_INCLUDED
?>
