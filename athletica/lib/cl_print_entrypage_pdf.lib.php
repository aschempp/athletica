<?php

if (!defined('AA_CL_PRINT_ENTRYPAGE_PDF_LIB_INCLUDED'))
{
	define('AA_CL_PRINT_ENTRYPAGE_PDF_LIB_INCLUDED', 1);


     include('./lib/cl_print_page_pdf.lib.php');   

/********************************************
 *
 * PRINT_EntryPage
 *
 *	Class to print basic entry lists
 *
 *******************************************/


class PRINT_EntryPage_pdf extends PRINT_Page_pdf
{	
    
	var $width;
	
	function printHeaderLine()
	{  	// page break check
		if($this->lp < $this->footerheight + 111)		//Footer + 8 Lines + Header = 36 + 8*12 + 15 = 147
		{
			$this->insertPageBreak();
		}
		$this->width = $this->getColWidth(503,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],120,2,0),
		array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strCountry'],22,0,0),array($GLOBALS['strCategoryShort'],30,0,0),
		array($GLOBALS['strClub'],130,2,0),array($GLOBALS['strDisciplines'],160,1,0));
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCountry'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCategoryShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[5],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDisciplines'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[6],30,'left bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($nbr, $name, $year, $cat, $club, $disc, $ioc, $paid='')
	{ 	
		$lnqnty=1; //linequantity
		// page break check
		if($this->lp < $this->footerheight + 48)	//Footer + 4 Lines = 36 + 4*12 =84
		{
			$this->insertPageBreak();
			$this->printHeaderLine();
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,$lnqnty);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($cat,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[5],'left',-10,0,$lnqnty);
		
		if(isset($_GET['payment']) && isset($_GET['discgroup'])){
			$lnqnty=$this->printTextFlowSimp($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[6]-18,'left',0,0,$lnqnty);
			$this->printTextLineBox($paid,$this->font,"",10,$this->posx+2,$this->lp,16,30,'left bottom',10,-10);
		}else{
			$lnqnty=$this->printTextFlowSimp($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[6],'left',0,0,$lnqnty);
		}
		
		$this->lp-=$lnqnty*12;
		
	}
 
	function printSubTitle($title)
	{	// page break check
		if($this->lp < $this->footerheight + 123)		//Footer + 3 line + 3 athlete-line + header+ Subtitle=36+3*12+3*14+15+30=159
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",11,40,$this->lp,515,30,'left bottom',26,4);
	}

} // end PRINT_EntryPage


/********************************************
 *
 * PRINT_CatEntryPage
 *
 *	Class to print entry lists per category
 *
 *******************************************/


class PRINT_CatEntryPage_pdf extends PRINT_EntryPage_pdf
{
	var $width;
	
	function printHeaderLine()
	{	// page break check
		if($this->lp < $this->footerheight + 111)		//Footer + 8 Lines + Header = 36 + 8*12 + 15 = 147
		{
			$this->insertPageBreak();  
		}
		$this->width = $this->getColWidth(505,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],130,2,0),
		array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strCountry'],22,0,0),array($GLOBALS['strClub'],120,1,0),array($GLOBALS['strDisciplines'],192,1,0));
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCountry'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDisciplines'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[5],30,'left bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($nbr, $name, $year, $club, $disc, $ioc)
	{	
		$lnqnty=1;
		// page break check
		if($this->lp < $this->footerheight + 36)		//Footer + 3 Lines = 36 + 3*12 = 72
		{
			$this->insertPageBreak();
			$this->printHeaderLine();
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,$lnqnty);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],'left',-10,0,$lnqnty);
		$lnqnty=$this->printTextFlowSimp($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[5],'left',0,0,$lnqnty);
		
		$this->lp-=$lnqnty*12;
		
	}

} // end PRINT_CatEntryPage



/********************************************
 *
 * PRINT_ClubEntryPage
 *
 *	Class to print entry lists per club
 *
 *******************************************/


class PRINT_ClubEntryPage_pdf extends PRINT_EntryPage_pdf
{
	var $width;
	
	function printHeaderLine()
	{	// page break check
		if($this->lp < $this->footerheight + 111)		//Footer + 8 Lines + Header = 36+8*12+15=147
		{
			$this->insertPageBreak();
		}
		$this->width = $this->getColWidth(505,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],160,1,0),
		array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strCountry'],22,0,0),array($GLOBALS['strCategoryShort'],30,0,0),array($GLOBALS['strDisciplines'],252,1,0));
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCountry'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCategoryShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDisciplines'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[5],30,'left bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($nbr, $name, $year, $cat, $disc, $ioc)
	{	// page break check
		if($this->lp < $this->footerheight + 36)		//Footer + 3 Lines = 36 + 3*12 = 72
		{
			$this->insertPageBreak();
			$this->printHeaderLine();
		}
		$lnqnty=1;
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,$lnqnty);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($cat,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[5],'left',-10,0,$lnqnty);
		
		$this->lp-=$lnqnty*12;
	}
    
    
    

} // end PRINT_ClubEntryPage


/********************************************
 *
 * PRINT_CatDiscEntryPage
 *
 *	Class to print entry lists per category and discipline
 *
 *******************************************/


class PRINT_CatDiscEntryPage_pdf extends PRINT_EntryPage_pdf
{
	var $width;
	
	function printHeaderLine()
	{	// page break check
		if($this->lp < $this->footerheight + 63)		//Footer + 4 Lines + Header = 36 + 4*12 + 15 = 99
		{
			$this->insertPageBreak();
		}
		$this->width = $this->getColWidth(505,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],200,1,0),
		array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strCountry'],22,0,0),array($GLOBALS['strClub'],177,1,0),array($GLOBALS['strTopPerformance'],65,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCountry'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[5],30,'left bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($nbr, $name, $year, $club, $perf, $ioc)
	{	// page break check
		if($this->lp < $this->footerheight + 12)		//Footer + Line = 36 + 12 = 48
		{
			$this->insertPageBreak();
			$this->printHeaderLine();
		}
		$lnqnty=1;
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,$lnqnty);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],'left',-10,0,$lnqnty);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[5],30,'left bottom',10,0);
		
		$this->lp-=($lnqnty*12-10);   
	}

} // end PRINT_CatDiscEntryPage


/********************************************
 *
 * PRINT_ClubCatEntryPage
 *
 *	Class to print entry lists per club and category
 *
 *******************************************/


class PRINT_ClubCatEntryPage_pdf extends PRINT_EntryPage_pdf
{
	var $width;
	
	function printHeaderLine()
	{  	// page break check
		if($this->lp < $this->footerheight + 111)		//Footer + 8 Lines + Header = 36+8*12+15=147
		{
			$this->insertPageBreak();
		}
		$this->width = $this->getColWidth(507,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],190,1,0),
		array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strCountry'],22,0,0),array($GLOBALS['strDisciplines'],254,1,0));
		
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCountry'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDisciplines'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($nbr, $name, $year, $disc, $ioc)
	{	// page break check
		if($this->lp < $this->footerheight + 36)		//Footer + 3 Lines = 36 + 3*12 = 72
		{
			$this->insertPageBreak();
			$this->printHeaderLine();
		}
		$lnqnty = 1;
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,$lnqnty);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);

		$lnqnty=$this->printTextFlowSimp($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],'left',-10,0,$lnqnty);
		
		$this->lp-=$lnqnty*12;
	}

} // end PRINT_ClubCatEntryPage



/********************************************
 *
 * PRINT_ClubCatDiscEntryPage
 *
 *	Class to print entry lists per club, category and discipline
 *
 *******************************************/


class PRINT_ClubCatDiscEntryPage_pdf extends PRINT_EntryPage_pdf
{
	function printHeaderLine()
	{  	// page break check
		if($this->lp < $this->footerheight + 63)		// Footer + 4 lines + Header = 36+4*12+15 =99
		{
			$this->insertPageBreak();
		}
		$this->width = $this->getColWidth(507,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],379,1,0),
		array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strCountry'],22,0,0),array($GLOBALS['strTopPerformance'],65,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCountry'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($nbr, $name, $year, $perf, $ioc)
	{ 	
		$lnqnty=1;
		// page break check
		if($this->lp < $this->footerheight + 12)		//Footer + line = 36 + 12 = 48
		{
			$this->insertPageBreak();
			$this->printHeaderLine();
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=max($this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0),$lnqnty);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;
	}

} // end PRINT_PRINT_ClubCatDiscEntryPage



/********************************************
 *
 * PRINT_EnrolementPage
 *
 *	Class to print enrolement lists
 *
 *******************************************/


class PRINT_EnrolementPage_pdf extends PRINT_EntryPage_pdf
{

	var $event;
	var $cat;
	var $time;
	var $bRelay;
	var $timeinfo;
	var $comb_disc;
	var $bsvm;
	var $width;
	
	function printTitle()
	{   
		// page break check (at least one further line left)
		if($this->lp < $this->footerheight + 164)		//This, header, 8 lines (used often in 100m) and footer should fit on page=54+14+8*12+36=200
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($this->event,$this->font,"B",16,40,$this->lp,223,30,'left bottom',23,0);   //23
		$this->printTextLineBox($this->cat,$this->font,"B",16,$this->posx+2,$this->lp,65,30,'left bottom',0,0);
		$this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,223,30,'right bottom',0,2);
		
		$this->printTextLineBox($this->comb_disc,$this->font,"B",10,40,$this->lp,223,30,'left bottom',12,0);
		$this->printTextLineBox($this->timeinfo,$this->font,"",10,$this->posx+2,$this->lp,290,30,'right bottom',0,2);
	}
    
    function printTitleCont()
    {  
        // page break check (at least one further line left)
        if($this->lp < $this->footerheight + 87)      //This, title, 4 lines and footer should fit on page=25+14+4*12+36=123  
        {
            $this->insertPageBreak();
        }
        $this->printTextLineBox($this->event,$this->font,"B",16,40,$this->lp,223,30,'left bottom',23,0);
        $this->printTextLineBox($this->cat,$this->font,"B",16,$this->posx+2,$this->lp,65,30,'left bottom',0,0);
        $this->printTextLineBox($GLOBALS['strCont'],$this->font,"B",16,$this->posx+2,$this->lp,223,30,'left bottom',0,2);
    }

	function printHeaderLine($relay, $svm)
	{   
		if($this->lp < $this->footerheight + 26)		// should not be necessary, cause restrictive check already done printTitle, 36+12+14=62
		{
			$this->insertPageBreak();
		}
		
		$this->bRelay = $relay;
		
		if ($svm) {
			$txt1 = $GLOBALS['strTeam'];
		} else {
			$txt1 = $GLOBALS['strClub'];
		}
		//array(HeaderText, Minimum Width, Weight for fillup (0 when no changeO), additional space (in pt))
		if($relay == FALSE)
		{	$this->pdf->SetFont($this->font,"B",10); 
			$this->width = $this->getColWidth(489,array("",15,0,0),array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],148,1,0),
				array($GLOBALS['strYearShort'],30,0,0),array($GLOBALS['strCountry'],40,0,0),array($txt1,185,1,0),array($GLOBALS['strTopPerformance'],50,0,0));
            $this->printTextLineBox("",$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
			$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'right bottom',10,0);
			$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+5,$this->lp,$this->width[2],30,'left bottom',0,0);
			$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
			$this->printTextLineBox($GLOBALS['strCountry'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
			$this->printTextLineBox($txt1,$this->font,"B",10,$this->posx+2,$this->lp,$this->width[5],30,'left bottom',0,0);
			$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[6],30,'right bottom',0,2);
		}else{ 
			$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,56,$this->lp,25,30,'left bottom',10,0);
			$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,235,30,'left bottom',0,0);
			$this->printTextLineBox($txt1,$this->font,"B",10,$this->posx+2,$this->lp,235,30,'left bottom',0,2);
		}
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}	

    function printLineAthlete($athleteLine)
    {   // page break check
		if($this->lp < $this->footerheight + 12)    //36+12=48    
        {
            $this->insertPageBreak();
            $this->printHeaderLine($this->bRelay);
        }   
		$this->printTextLineBox($athleteLine,$this->font,"I",10,83,$this->lp,472,30,'left bottom',10,2);    
    }
    
	function printLine($nbr,  $name, $year, $club, $ioc, $top, $club2='', $pos)
	{   // page break check         
		if($this->lp < $this->footerheight + 24)		//36+12*2=60 (*2 because there may be an athlete-line
		{
			$this->insertPageBreak(); 			
			$this->printTitleCont(); 
			$this->printHeaderLine($this->bRelay,false);
             
		}
    
		$lnqnty=1;

		if(!$this->bRelay)		// athlete
		{
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
			$this->printTextLineBox($nbr,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],30,'right bottom',0,0);
			$lnqnty=max($this->printTextFlowSimp($name,$this->font,"",10,$this->posx+5,$this->lp,$this->width[2],'left',-10,0),$lnqnty);
			$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',10,0);
			$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
			$lnqnty=max($this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[5],'left',-10,0),$lnqnty);
			$this->printTextLineBox($top,$this->font,"",10,$this->posx+2,$this->lp,$this->width[6],30,'right bottom',10,-10);
		}
		else		// relay
		{  
			$this->printTextLineBox('[   ]',$this->font,"",10,40,$this->lp,14,30,'right bottom',10,0);
			$this->printTextLineBox($nbr,$this->font,"",10,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
			$lnqnty=max($this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,235,'left',-10,0),$lnqnty);
			$lnqnty=max($this->printTextFlowSimp($club2,$this->font,"",10,$this->posx+2,$this->lp,235,'left',0,0),$lnqnty);
		}
		$this->lp-=$lnqnty*12;
	}

} // end PRINT_EnrolementPage

/********************************************
 *
 * PRINT_ReceiptEntryPage
 *
 *    Class to print receipt entry lists
 *
 *******************************************/


class PRINT_ReceiptEntryPage_pdf extends PRINT_Page_pdf
{
    function printHeader($mname,$mDateFrom,$mDateTo,$stadion,$organisator)
    {   
		$this->printTextLineBox($GLOBALS['strReceipt'],$this->font,"B",24,20,$this->lp,515,40,'left bottom',24,2);
		
		$this->printTextLineBox($GLOBALS['strMeetingTitle']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',26,0);
		$this->printTextLineBox($mname,$this->font,"",13,$this->posx+2,$this->lp,451,30,'left bottom',0,5);
		$this->printTextLineBox($GLOBALS['strDate']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',13,0);
        if($mDateFrom == $mDateTo) {
            $this->printTextLineBox($mDateFrom,$this->font,"",13,$this->posx+2,$this->lp,242,30,'left bottom',0,5);    
        } else {
            $this->printTextLineBox($mDateFrom ." - ". $mDateTo,$this->font,"",13,$this->posx+2,$this->lp,242,30,'left bottom',0,5);
        }
		
		
		$this->printTextLineBox($GLOBALS['strOrganizer']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',13,0);
		$this->printTextLineBox($organisator,$this->font,"",13,$this->posx+2,$this->lp,222,30,'left bottom',0,2);
        $this->printTextLineBox($GLOBALS['strStadium']. ":  ",$this->font,"B",13,$this->posx,$this->lp,90,30,'left bottom',0,0);
        $this->printTextLineBox($stadion,$this->font,"",13,$this->posx+2,$this->lp,221,30,'left bottom',0,2);
    }   
    
    function printHeaderLineCont()//seems unused
    {   
        if($this->lp < $this->footerheight + 66)        // page break check
        {   
            $this->insertPageBreak();  
        }
        $this->printTextLineBox($GLOBALS['strParticipant']. " " . $GLOBALS['strCont'],$this->font,"B",13,20,$this->lp,515,30,'left bottom',13,2);
    }       

    function printLine1($nbr, $name, $year)           // page per athlet (print athlet name and age)
    {  
        $this->printTextLineBox($GLOBALS['strName']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',13,0);
        $this->printTextLineBox($name,$this->font,"",13,$this->posx+2,$this->lp,220,30,'left bottom',0,0);
        $this->printTextLineBox($GLOBALS['strYear']. ":  ",$this->font,"B",13,$this->posx+2,$this->lp,90,30,'left bottom',0,0);
        $this->printTextLineBox($year,$this->font,"",13,$this->posx+2,$this->lp,139,30,'left bottom',0,5);
    }
    
    function printLine2($club,$cat)                    // page per athlet (print athlet club and cat)    
    {  
        $this->printTextLineBox($GLOBALS['strClub']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',13,0);
        $this->printTextLineBox($club,$this->font,"",13,$this->posx+2,$this->lp,220,30,'left bottom',0,0);
        $this->printTextLineBox($GLOBALS['strCategory']. ":  ",$this->font,"B",13,$this->posx+2,$this->lp,90,30,'left bottom',0,0);
        $this->printTextLineBox($cat,$this->font,"",13,$this->posx+2,$this->lp,139,30,'left bottom',0,2);
    }  
    
    function printLine3($disc)                          // page per athlet (print disciplines)    
    {      
        $this->printTextLineBox($GLOBALS['strDisciplines']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',40,0);
  
         // print seperate lines per discipline 
         
         $disc_arr = split(",",$disc);   
         $i=0; 
         
         foreach ($disc_arr as $key)
            { 
              if($this->lp < $this->footerheight + 66)        // page break check
                {
                $this->insertPageBreak();
                $this->printHeader();
                }  
            if ($i==0) {
            $this->lp+=13; //first time print on same line
            }
            $this->printTextLineBox(trim($key),$this->font,"",13,132,$this->lp,403,30,'left bottom',13,2);
            $i++;
         }  
    }  
    //seems unused
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
    {  // page break check
        if($this->lp < $this->footerheight + 84)        // Footer + LineFooter  = 36 + 84=120
            {
            $this->insertPageBreak();
            $this->printHeader();
        }   
        $this->lp-=20;

        if ($list) {
			$this->printTextLineBox($GLOBALS['strTotal'] . " " . $GLOBALS['strFee']. ":  ",$this->font,"B",13,20,$this->lp,374,30,'left bottom',13,0);
			$this->printTextLineBox($GLOBALS['strCHF'] . "   " . $fee. ".00",$this->font,"B",13,$this->posx+2,$this->lp,179,30,'right bottom',0,2);
        }
        else {
			$this->printTextLineBox($GLOBALS['strTotal'] . " " . $GLOBALS['strFee']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',13,0);
			$this->printTextLineBox($GLOBALS['strCHF'] . "   " . $fee. ".00",$this->font,"B",13,$this->posx+2,$this->lp,423,30,'left bottom',0,2);
        }
        $this->lp-=13;
        $this->printTextLineBox($GLOBALS['strDate']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',25,0);
        $this->printTextLineBox($date,$this->font,"",13,$this->posx+2,$this->lp,220,30,'left bottom',0,0);
        $this->printTextLineBox($GLOBALS['strPlace']. ":  ",$this->font,"B",13,$this->posx+2,$this->lp,90,30,'left bottom',0,0);
        $this->printTextLineBox($place,$this->font,"",13,$this->posx+2,$this->lp,139,30,'left bottom',0,2);
        
        $this->lp-=13;
        
        $this->printTextLineBox($GLOBALS['strSubscribe']. ":  ",$this->font,"B",13,20,$this->lp,515,30,'left bottom',13,2); 
    }

   
    
    function printLine4($first, $name, $year, $cat ,$disc, $fee)
    {   // page break check
        if($this->lp < $this->footerheight)        //Footer+FooterLine+ 1 line=36+84+13=133
        {         
			$this->insertPageBreak(); 
			$this->printHeaderLineCont(); 
        }  
        $this->printTextLineBox($name,$this->font,"",11,20,$this->lp,130,30,'left bottom',11,0);
        $this->printTextLineBox($year,$this->font,"",11,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
        $this->printTextLineBox($cat,$this->font,"",11,$this->posx+2,$this->lp,35,30,'left bottom',0,0);
        $this->printTextLineBox($disc,$this->font,"",11,$this->posx+2,$this->lp,282,30,'left bottom',0,0);
        $this->printTextLineBox($GLOBALS['strCHF'] . "   " . $fee . ".00",$this->font,"",11,$this->posx+2,$this->lp,70,30,'right bottom',0,2);
    }
   
	function printLineClub($club)
    {  
        $this->printTextLineBox($GLOBALS['strClub']. ":  ",$this->font,"B",13,20,$this->lp,110,30,'left bottom',13,0);
        $this->printTextLineBox($club,$this->font,"",13,$this->posx+2,$this->lp,423,30,'left bottom',0,2);
        $this->lp-=13;
        $this->printTextLineBox($GLOBALS['strParticipant']. ":  ",$this->font,"B",13,20,$this->lp,515,30,'left bottom',13,2);
    }   
   
    function  printLineBreak($count)
    {                                  
		$this->lp-=13*$count;
    }
        

} // end PRINT_ReceiptEntryPage
	
/********************************************
 *
 * PRINT_ClubEntryPayedPage
 *
 *    Class to print entry lists per club
 *
 *******************************************/


class PRINT_ClubEntryPayedPage_pdf extends PRINT_EntryPage_pdf  //TODO
{ 	
	//strange but true: printHeaderLine is called before printSubtitle-->that's why so strange lp change in this functions
	
	var $width;
	
    function printHeaderLine($max_count = 2)
    {	// page break check
        if($this->lp < $this->footerheight + 51)  //Footer+3 Lines+Header=36 + 3*12 + 15=87
        {
            $this->insertPageBreak();
        }
		//echo('header');
		$this->width = $this->getColWidth(507,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strName'],150,1,0),
			array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strCategoryShort'],30,0,0),array('',300,0,0)); //300 is fix for 5th col!
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCategoryShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strPayedShort'] . " / " . $GLOBALS['strDisciplines'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,2);
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
    }


    function printLine($nbr, $name, $year, $cat, $disc, $len)
    {   
		$len = $len -1;
		// page break check
        if($this->lp < $this->footerheight + 36) //Footer+3 Lines=36+3*12=72
        {
            $this->insertPageBreak();
            $this->printHeaderLine();
        }
   
        $i = 0;
        $c = 0;
        // print more lines if disciplines more than 5 (5 disciplines per line)
         while ($len > 0){
             $pos = strpos($disc, "</td>");
             if ($i % 10 == 0){                
               $c++;
			   $c2 = 0;
             }
             $pos = $pos+5;
			 $txt=substr($disc,0,$pos);
			 if (stripos($txt,'input')===false) {
				$arr[$c][$c2][1] = strip_tags($txt);
				$c2++;
			} else {
				if (stripos($txt,'"y"')===false) {
					$arr[$c][$c2][0]='None';
				} else {
					$arr[$c][$c2][0]='Cross';
				}
			}
             $disc = substr($disc, $pos);
             $i++;
             $len -= 1;
         }
		
        foreach ($arr as $key => $val){
            
            if ($key == 1){
				$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'right bottom',10,0);
				$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
				$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
				$this->printTextLineBox($cat,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
				
				foreach ($val as $key2 => $val2) {
					$this->printCheckBox($this->posx+2,$this->lp,8,$val2[0]);
					$this->printTextLineBox($val2[1],$this->font,"",10,$this->posx+2,$this->lp,48,30,'left bottom',0,0);
				}
				$this->lp-=2;
			} else {
				$this->posx=253;
				$this->lp-=10;
				foreach ($val as $key2 => $val2) {
					$this->printCheckBox($this->posx+2,$this->lp,8,$val2[0]);
					$this->printTextLineBox($val2[1],$this->font,"",10,$this->posx+2,$this->lp,48,30,'left bottom',0,0);
				}
				$this->lp-=2;
			}
        }
		if ($lnqnty>count($arr)){
			$this->lp-=12*($lnqnty-count($arr));
		}
    }
    
    function printSubTitle($title)
    {	// page break check
        if($this->lp < $this->footerheight + 81) //87 (from header) + 30=117
        {
            $this->insertPageBreak();
        }
		//echo('subtitle');
		$this->printTextLineBox($title,$this->font,"B",11,40,$this->lp,515,30,'left bottom',26,4);
    }
    
    
    

} // end PRINT_ClubEntryPage

} // end AA_CL_PRINT_ENTRYPAGE_LIB_INCLUDED

?>
