<?php

if (!defined('AA_CL_PRINT_CONTEST_PDF_LIB_INCLUDED'))
{
    define('AA_CL_PRINT_CONTEST_PDF_LIB_INCLUDED', 1);


    require('./lib/cl_print_page_pdf.lib.php');

/********************************************
 *
 * PRINT_Contest
 *
 *    Class to print contest sheets
 *
 *******************************************/


class PRINT_Contest_pdf extends PRINT_Page_pdf
{
    var $cat;
    var $event;
    var $info;
    var $time;
    var $freetxt;
    var $resultinfo;
    var $timeinfo;

    function printHeaderLine(){
        $this->printTextLineBox($this->event,$this->font,"B",16,40,$this->lp,170,30,'left bottom',21,0);
        $this->printTextLineBox($this->cat,$this->font,"B",16,$this->posx+2,$this->lp,171,30,'left bottom',0,0);
        $this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,170,30,'right bottom',0,5);
        $this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,515,30,'right bottom',10,5);

        $txt = '';            
        $i = 0;
        // Info: signature for invalid results
        foreach($GLOBALS['cfgInvalidResult'] as $value) {
            if($i > 0 ) {
                $txt = $txt . ", ";    
            }
            $txt = $txt . $value['code'] . " = " . $value['long'];
            $i++;
        }
        $this->printTextLineBox($txt,$this->font,"",10,40,$this->lp,515,30,'left bottom',15,5);
    
        if($this->resultinfo != ""){
            $this->printTextLineBox($this->resultinfo,$this->font,"",10,40,$this->lp,515,30,'left bottom',15,5);
        }
    }
    

    function setFreeTxt($freetxt)
    {
        $this->freetxt = $freetxt;
    }


    function addFreeTxt($freetxt)
    {
        $this->freetxt = $this->freetxt . $freetxt;
    }


    function printFreeTxt()
    {
        $this->printTextLineBox($this->freetxt,$this->font,"",10,40,$this->lp,515,30,'left bottom',15,5);
    }


    function printHeatTitle($heat, $installation, $film='')
    {
        $this->printTextCell($heat,$this->font,"B",11,40,$this->lp,114,21,'left center',20,0);
        $this->printTextCell($installation,$this->font,"B",11,$this->posx,$this->lp,114,21,'left center',0,0);
        $this->printTextCell($this->info,$this->font,"B",11,$this->posx,$this->lp,150,21,'left center',0,0);
        $this->printTextCell($GLOBALS['strFilm'].": ".$film,$this->font,"B",11,$this->posx,$this->lp,75,21,'left center',0,0);
        $this->printTextCell($GLOBALS['strWind'].": ",$this->font,"B",11,$this->posx,$this->lp,62,21,'left center',0,21);
    }

    function insertPageBreak()
    { 
        $this->printPageFooter();
        $this->pdf->AddPage();
        $this->printPageHeader();
        $this->printHeaderLine();
        $this->printFreeTxt();
    }   

    function printEndHeat()
    {
        return;
    }

} // end Contest



/********************************************
 *
 * PRINT_ContestTrack
 *
 *******************************************/


class PRINT_ContestTrack_pdf extends PRINT_Contest_pdf
{
    function printStartHeat($svm = false, $teamsm = false)
    {
        $this->printTextCell($GLOBALS['strTrack'],$this->font,"B",10,40,$this->lp,25,20,'center center',0,0);//pos
        $this->printTextCell($GLOBALS['strPositionShort'],$this->font,"B",10,$this->posx,$this->lp,25,20,'center center',0,0);//track
        $this->printTextCell($GLOBALS['strStartnumber'],$this->font,"B",10,$this->posx,$this->lp,32,20,'center center',0,0);//nbr
        $this->printTextCell($GLOBALS['strName'],$this->font,"B",10,$this->posx,$this->lp,135,20,'left center',0,0);//name
        $this->printTextCell($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx,$this->lp,30,20,'center center',0,0);//year
        $this->printTextCell($GLOBALS['strCountry'],$this->font,"B",10,$this->posx,$this->lp,35,20,'center center',0,0);//country
        if ($svm){
            $this->printTextCell($GLOBALS['strTeam'],$this->font,"B",10,$this->posx,$this->lp,123,20,'left center',0,0);//team
        }elseif ($teamsm){
      $this->printTextCell($GLOBALS['strTeamsm'],$this->font,"B",10,$this->posx,$this->lp,123,20,'left center',0,0);//club
    }else{
            $this->printTextCell($GLOBALS['strClub'],$this->font,"B",10,$this->posx,$this->lp,123,20,'left center',0,0);//club
        }
        $this->printTextCell($GLOBALS['strResult'],$this->font,"B",10,$this->posx,$this->lp,65,20,'center center',0,0);//result
        $this->printTextCell($GLOBALS['strRank'],$this->font,"B",10,$this->posx,$this->lp,45,20,'center center',0,20);//rank        
    }


    function printHeatLine($track=0, $nbr="", $name="", $year="", $club="", $pos=0, $country="")
    {    //multiline necessary--> first print text, then cells without content
        $lines=1; 
        $padtop=5;
        $lines=max($lines,$this->printTextFlowSimp($pos,$this->font,"",10,41,$this->lp-$padtop,23,'center',0,0));
        $lines=max($lines,$this->printTextFlowSimp($track,$this->font,"",10,66,$this->lp-$padtop,23,'center',0,0));
        $lines=max($lines,$this->printTextFlowSimp($nbr,$this->font,"",10,91,$this->lp-$padtop,30,'center',0,0));
        $lines=max($lines,$this->printTextFlowSimp($name,$this->font,"",10,124,$this->lp-$padtop,131,'left',0,0));//126 127
        $lines=max($lines,$this->printTextFlowSimp($year,$this->font,"",10,261,$this->lp-$padtop,22,'center',0,0));
        $lines=max($lines,$this->printTextFlowSimp((($country!='' && $country!='-') ? $country : ''),$this->font,"",10,291,$this->lp-$padtop,27,'center',0,0));
        $lines=max($lines,$this->printTextFlowSimp($club,$this->font,"",10,324,$this->lp-$padtop,119,'left',0,0));
        $height=20+($lines-1)*12;
        $this->printTextCell('',$this->font,"",10,40,$this->lp,25,$height,'center',0,0);//pos
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,25,$height,'center',0,0);//track
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,32,$height,'center',0,0);//nbr
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,135,$height,'left',0,0);//name
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,30,$height,'center',0,0);//year
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,35,$height,'left',0,0);//country
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,123,$height,'left',0,0);//club
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,65,$height,'center',0,0);//result
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,45,$height,'center',0,$height);//rank
    }

} // end ContestTrack



/********************************************
 *
 * PRINT_ContestTrackNoWind
 *
 *******************************************/


class PRINT_ContestTrackNoWind_pdf extends PRINT_ContestTrack_pdf
{

    function printHeatTitle($heat, $installation, $film='')
    { 
        $this->printTextCell($heat,$this->font,"B",11,40,$this->lp,114,21,'left center',20,0);
        $this->printTextCell($installation,$this->font,"B",11,$this->posx,$this->lp,114,21,'left center',0,0);
        $this->printTextCell($this->info,$this->font,"B",11,$this->posx,$this->lp,150,21,'left center',0,0);
        $this->printTextCell('',$this->font,"B",11,$this->posx,$this->lp,62,21,'left center',0,0);
        $this->printTextCell($GLOBALS['strFilm'].": ".$film,$this->font,"B",11,$this->posx,$this->lp,75,21,'left center',0,21);        
    }

} // end ContestTrackNoWind



/********************************************
 *
 * PRINT_ContestRelay
 *
 *******************************************/


class PRINT_ContestRelay_pdf extends PRINT_Contest_pdf 
{
    var $width;
    
    function printHeatTitle($heat, $installation, $film='')
    {    
        $this->printTextCell($heat,$this->font,"B",11,40,$this->lp,114,21,'left center',20,0);
        $this->printTextCell($installation,$this->font,"B",11,$this->posx,$this->lp,114,21,'left center',0,0);
        $this->printTextCell($this->info,$this->font,"B",11,$this->posx,$this->lp,150,21,'left center',0,0);
        $this->printTextCell('',$this->font,"B",11,$this->posx,$this->lp,62,21,'left center',0,0);
        $this->printTextCell($GLOBALS['strFilm'].": ".$film,$this->font,"B",11,$this->posx,$this->lp,75,21,'left center',0,21);
    }


    function printStartHeat($svm = false)
    { 
        if ($svm) { 
            $txt3=$GLOBALS['strTeam'];
        } else {
            $txt3=$GLOBALS['strClub'];
        }
        $this->width = $this->getColWidth(515,array($GLOBALS['strTrack'],26,0,2),array($GLOBALS['strRelays'],235,1,2),array($txt3,152,2,2),array($GLOBALS['strResult'],60,0,2),array($GLOBALS['strRank'],42,0,2));
        $this->printTextCell($GLOBALS['strTrack'], $this->font,"B",10,40,$this->lp,$this->width[0],21,'center center',0,0);
        $this->printTextCell($GLOBALS['strRelays'], $this->font,"B",10,$this->posx,$this->lp,$this->width[1],21,'left center',0,0);
        $this->printTextCell($GLOBALS['strRelays'], $this->font,"B",10,$this->posx,$this->lp,$this->width[2],21,'left center',0,0);
        $this->printTextCell($GLOBALS['strResult'], $this->font,"B",10,$this->posx,$this->lp,$this->width[3],21,'center center',0,0);
        $this->printTextCell($GLOBALS['strRank'], $this->font,"B",10,$this->posx,$this->lp,$this->width[4],21,'center center',0,21);
    }


    function printHeatLine($track=0, $relay="", $club="", $country="")
    {   
        $arr=explode('<br />',$relay);
        $lines=1;
        if (count($arr)>1) {$lines=count($arr);}
        for ($i=0;$i<count($arr);$i++) {
            $this->printTextLineBox($arr[$i],$this->font,"",10,40+$this->width[0]+2,$this->lp-$i*12-21,$this->width[1]-4,21,'left center',0,0);
        }
        $this->printTextCell($track, $this->font,"",10,40,$this->lp,$this->width[0],21+($lines-1)*12,'center center',0,0);
        $this->printTextCell("", $this->font,"",10,$this->posx,$this->lp,$this->width[1],21+($lines-1)*12,'left center',0,0);
        $this->printTextCell($club, $this->font,"",10,$this->posx,$this->lp,$this->width[2],21+($lines-1)*12,'left center',0,0);
        $this->printTextCell("", $this->font,"",10,$this->posx,$this->lp,$this->width[3],21+($lines-1)*12,'center center',0,0);
        $this->printTextCell("", $this->font,"",10,$this->posx,$this->lp,$this->width[4],21+($lines-1)*12,'center center',0,21+($lines-1)*12);
    }

} // end ContestRelay




/********************************************
 *
 * PRINT_ContestTech
 *
 *******************************************/


class PRINT_ContestTech_pdf extends PRINT_Contest_pdf
{

    function printHeatTitle($heat, $installation)
    {
        $this->printTextCell($heat,$this->font,"B",11,40,$this->lp,114,21,'left center',20,0);
        $this->printTextCell($installation,$this->font,"B",11,$this->posx,$this->lp,114,21,'left center',0,0);
        $this->printTextCell($this->info,$this->font,"B",11,$this->posx,$this->lp,150,21,'left center',0,0);
        $this->printTextCell('',$this->font,"B",11,$this->posx,$this->lp,75,21,'left center',0,0);
        $this->printTextCell('',$this->font,"B",11,$this->posx,$this->lp,62,21,'left center',0,21);
    }


    function printHeatLine($nbr=0, $name="", $year="", $club="", $country="")
    {
        $this->printTextLineBox($nbr,$this->font,"B",10,40,$this->lp,46,30,'center bottom',15,0);
        $this->printTextLineBox($name,$this->font,"B",10,$this->posx+2,$this->lp,159,30,'left bottom',0,0);
        $this->printTextLineBox($year,$this->font,"B",10,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
        $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",10,$this->posx+2,$this->lp,46,30,'left bottom',0,0);
        $this->printTextLineBox($club,$this->font,"B",10,$this->posx+2,$this->lp,210,30,'left bottom',0,5);
        
        $this->printTextCell('',$this->font,"",10,40,$this->lp,58,20,'center center',0,0);
        $this->multiAttempt(true);
        $this->printTextCell($GLOBALS['strResult'],$this->font,"",10,$this->posx,$this->lp,58,20,'center center',0,0);
        $this->printTextCell($GLOBALS['strRank'],$this->font,"",10,$this->posx,$this->lp,45,20,'center center',0,20);
        
        $this->printTextCell($GLOBALS['strResult'],$this->font,"",10,40,$this->lp,58,20,'center center',0,0);
        $this->multiAttempt(false);
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,58,20,'center center',0,0);
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,45,20,'center center',0,20);
        
        $this->printTextCell($GLOBALS['strWind'],$this->font,"",10,40,$this->lp,58,20,'center center',0,0);
        $this->multiAttempt(false);
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,58,20,'center center',0,0);
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,45,20,'center center',0,20);
    }
    
    function multiAttempt($head = false)
    {
        $width=45;
        if ($_POST['countattempts']>3){
            $width=min($width,(515-3*58-2*45)/$_POST['countattempts']);
        }else{
            $width=min($width,(515-2*58-45)/$_POST['countattempts']);
        }
    
        for($i = 0; $i<$_POST['countattempts']; $i++){
            if($i == 3 && !$head && $_POST['countattempts'] != 4){
                $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,58,20,'center center',0,0);
                $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,45,20,'center center',0,0);
            }
            
            if($i == 3 && $head && $_POST['countattempts'] != 4){
                $this->printTextCell($GLOBALS['strResult'],$this->font,"",10,$this->posx,$this->lp,58,20,'center center',0,0);
                $this->printTextCell($GLOBALS['strRank'],$this->font,"",10,$this->posx,$this->lp,45,20,'center center',0,0);
            }
      
            if($head){
                $this->printTextCell(($i+1).".",$this->font,"",10,$this->posx,$this->lp,$width,20,'center center',0,0);
            }else{
                $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,$width,20,'center center',0,0);
            }
        }
    }

} // end ContestTech


/********************************************
 *
 * PRINT_ContestTechNoWind
 *
 *******************************************/


class PRINT_ContestTechNoWind_pdf extends PRINT_ContestTech_pdf
{

    function printHeatLine($nbr=0, $name="", $year="", $club="", $country="")
    {
        $this->printTextLineBox($nbr,$this->font,"B",10,40,$this->lp,46,30,'center bottom',15,0);
        $this->printTextLineBox($name,$this->font,"B",10,$this->posx+2,$this->lp,159,30,'left bottom',0,0);
        $this->printTextLineBox($year,$this->font,"B",10,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
        $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",10,$this->posx+2,$this->lp,46,30,'left bottom',0,0);
        $this->printTextLineBox($club,$this->font,"B",10,$this->posx+2,$this->lp,210,30,'left bottom',0,5);
        
        $this->printTextCell('',$this->font,"",10,40,$this->lp,58,20,'center center',0,0);
        $this->multiAttempt(true);
        $this->printTextCell($GLOBALS['strResult'],$this->font,"",10,$this->posx,$this->lp,58,20,'center center',0,0);
        $this->printTextCell($GLOBALS['strRank'],$this->font,"",10,$this->posx,$this->lp,45,20,'center center',0,20);
        
        $this->printTextCell($GLOBALS['strResult'],$this->font,"",10,40,$this->lp,58,20,'center center',0,0);
        $this->multiAttempt(false);
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,58,20,'center center',0,0);
        $this->printTextCell('',$this->font,"",10,$this->posx,$this->lp,45,20,'center center',0,20);
    }

} // end ContestTechNoWind




/********************************************
 *
 * PRINT_ContestHigh
 *
 *******************************************/

class PRINT_ContestHigh_pdf extends PRINT_ContestTech_pdf
{
    function printResultTable($text){
        $this->printTextCell($text,$this->font,"",10,40,$this->lp,59,20,'center center',0,0);
        for($i=0;$i<2;$i++){
            for($j=0;$j<12;$j++){
                $this->printTextCell('',$this->font,"",0,99+$j*38,$this->lp,38,20,'left center',0,0);
            }
            $this->lp-=20;
        }
    }
    
    function printHeatTitle($heat, $installation){
        $this->printTextCell($heat,$this->font,"B",11,40,$this->lp,114,21,'left center',20,0);
        $this->printTextCell($installation,$this->font,"B",11,$this->posx,$this->lp,114,21,'left center',0,0);
        $this->printTextCell($this->info,$this->font,"B",11,$this->posx,$this->lp,150,21,'left center',0,0);
        $this->printTextCell('',$this->font,"B",11,$this->posx,$this->lp,75,21,'left center',0,0);
        $this->printTextCell('',$this->font,"B",11,$this->posx,$this->lp,62,21,'left center',0,21);
        $this->printResultTable($GLOBALS['strHeight']);
    }

    function printHeatLine($nbr=0, $name="", $year="", $club="", $country=""){
        $this->printTextLineBox($nbr,$this->font,"B",10,40,$this->lp,46,30,'center bottom',15,0);
        $this->printTextLineBox($name,$this->font,"B",10,$this->posx+2,$this->lp,159,30,'left bottom',0,0);
        $this->printTextLineBox($year,$this->font,"B",10,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
        $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",10,$this->posx+2,$this->lp,46,30,'left bottom',0,0);
        $this->printTextLineBox($club,$this->font,"B",10,$this->posx+2,$this->lp,210,30,'left bottom',0,5);
        
        $this->printResultTable($GLOBALS['strResult']);
    }


} // end ContestHigh



} // end AA_CL_PRINT_CONTEST_LIB_INCLUDED
?>
