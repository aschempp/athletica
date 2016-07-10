<?php

if (!defined('AA_CL_PRINT_RELAYPAGE_PDF_LIB_INCLUDED'))
{
	define('AA_CL_PRINT_RELAYPAGE_PDF_LIB_INCLUDED', 1);


     include('./lib/cl_print_page_pdf.lib.php');

/********************************************
 *
 * PRINT_RelayPage
 *
 *	Class to print relay lists
 *
 *******************************************/

class PRINT_RelayPage_pdf extends PRINT_Page_pdf
{
	var $width;
	function printHeaderLine()
	{   
		if($this->lp < 129)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+15=129
		{
			$this->insertPageBreak();
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(505,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strRelay'],170,1,0),
		array($GLOBALS['strCategoryShort'],30,0,0),array($GLOBALS['strClub'],135,1,0),
		array($GLOBALS['strDiscipline'],80,0,0),array($GLOBALS['strTopPerformance'],65,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strRelay'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCategoryShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[5],30,'right bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($name, $cat, $club, $disc, $perf, $nbr)
	{   
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($cat,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],'left',-10,0,$lnqnty);
		$this->printTextLineBox($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',10,0);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[5],30,'right bottom',0,-10);
		
		$this->lp-=$lnqnty*12;
	}


	function printAthletes($athletes)
	{   
		$ln = $this->printTextFlowSimp($athletes,$this->font,'I',10,67,$this->lp,488,'left',0,0);
		$this->lp-=$ln*12+2;
	}



	function printSubTitle($title)
	{   //page break check
		if($this->lp < 177)		//Footer + 3Lines + Header + Subtitle = 36 + 8*12 + 15 + 30 = 177
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",11,40,$this->lp,515,30,'left bottom',26,4);
	}

} // end PRINT_RelayPage


/********************************************
 *
 * PRINT_CatRelayPage
 *
 *	Class to print relay lists per categroy
 *
 *******************************************/

class PRINT_CatRelayPage_pdf extends PRINT_RelayPage_pdf
{
	var $width;
	function printHeaderLine()
	{
		if($this->lp < 129)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+15=129
		{
			$this->insertPageBreak();
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(507,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strRelay'],187,1,0),
		array($GLOBALS['strClub'],150,1,0),array($GLOBALS['strDiscipline'],80,0,0),array($GLOBALS['strTopPerformance'],65,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strRelay'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'right bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($name, $club, $disc, $perf, $nbr)
	{
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],'left',0,0,$lnqnty);
		$this->printTextLineBox($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',10,0);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'right bottom',0,-10);
		
		$this->lp-=$lnqnty*12;
	}

} // end PRINT_CatRelayPage



/********************************************
 *
 * PRINT_ClubRelayPage
 *
 *	Class to print relay lists per club
 *
 *******************************************/

class PRINT_ClubRelayPage_pdf extends PRINT_RelayPage_pdf
{
	var $width;
	function printHeaderLine()
	{
		if($this->lp < 129)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+15=129
		{
			$this->insertPageBreak();
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(507,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strRelay'],307,1,0),
		array($GLOBALS['strCategoryShort'],30,0,0),array($GLOBALS['strDiscipline'],80,0,0),array($GLOBALS['strTopPerformance'],65,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strRelay'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strCategoryShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'right bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($name, $cat, $disc, $perf, $nbr)
	{  
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($cat,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'right bottom',0,-10);
		
		$this->lp-=$lnqnty*12;
	}

} // end PRINT_ClubRelayPage


/********************************************
 *
 * PRINT_CatDiscRelayPage
 *
 *	Class to print relay lists per categroy and discipline
 *
 *******************************************/

class PRINT_CatDiscRelayPage_pdf extends PRINT_RelayPage_pdf
{
	var $width;
	function printHeaderLine()
	{  
		if($this->lp < 129)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+15=129
		{
			$this->insertPageBreak();
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(509,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strRelay'],230,1,0),
		array($GLOBALS['strClub'],187,1,0),array($GLOBALS['strTopPerformance'],65,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strRelay'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($name, $club, $perf, $nbr)
	{
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],'left',0,0,$lnqnty);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',10,-10);
		
		$this->lp-=$lnqnty*12;
	}

} // end PRINT_CatDiscRelayPage



/********************************************
 *
 * PRINT_ClubCatRelayPage
 *
 *	Class to print relay lists per categroy and discipline
 *
 *******************************************/

class PRINT_ClubCatRelayPage_pdf extends PRINT_RelayPage_pdf
{
	var $width;
	function printHeaderLine()
	{
		if($this->lp < 129)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+15=129
		{
			$this->insertPageBreak();
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(509,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strRelay'],339,1,0),
		array($GLOBALS['strDiscipline'],80,0,0),array($GLOBALS['strTopPerformance'],65,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strRelay'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDiscipline'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($name, $disc, $perf, $nbr)
	{   
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($disc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',10,0);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',0,-10);
		
		$this->lp-=$lnqnty*12;
	}

} // end PRINT_ClubCatRelayPage



/********************************************
 *
 * PRINT_ClubCatDiscRelayPage
 *
 *	Class to print relay lists per club, categroy and discipline
 *
 *******************************************/

class PRINT_ClubCatDiscRelayPage_pdf extends PRINT_RelayPage_pdf
{
	var $width;
	function printHeaderLine()
	{	
		if($this->lp < 129)		// Footer + 3 line + 3 athlete-line + header=36+3*12+3*14+15=129
		{
			$this->insertPageBreak();
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(511,array($GLOBALS['strStartnumber'],25,0,0),array($GLOBALS['strRelay'],421,1,0),
		array($GLOBALS['strTopPerformance'],65,0,0));
	
		$this->printTextLineBox($GLOBALS['strStartnumber'],$this->font,"B",10,40,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strRelay'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strTopPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'right bottom',0,2);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($name, $perf, $nbr)
	{   
		if($this->lp < 62)		// Footer + line + athlete-line=36+12+14=62
		{
			$this->insertPageBreak();
			$this->printHeaderLine($enrolSheet);
		}
		$this->printTextLineBox($nbr,$this->font,"",10,40,$this->lp,$this->width[0],30,'left bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'right bottom',10,-10);
		
		$this->lp-=$lnqnty*12;
	}


} // end PRINT_ClubCatDiscRelayPage



} // end AA_CL_PRINT_RELAYPAGE_LIB_INCLUDED
?>
