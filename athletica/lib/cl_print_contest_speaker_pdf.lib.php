<?php

if (!defined('AA_CL_PRINT_CONTEST_SPEAKER_PDF_LIB_INCLUDED'))
{
    define('AA_CL_PRINT_CONTEST_SPEAKER_PDF_LIB_INCLUDED', 1);


    require('./lib/cl_print_page_pdf.lib.php');

/********************************************
 *
 * PRINT_Contest
 *
 *    Class to print contest sheets
 *
 *******************************************/


class PRINT_Contest_speaker_pdf extends PRINT_Page_pdf
{
    var $cat;
    var $event;
    var $info;
    var $time;
    var $freetxt;
    var $nextRounds;
    var $resultinfo;
    var $timeinfo;
    var $freetext_bool;

    function printHeaderLine(){ 
        if($this->landscape) {
            $this->printTextLineBox($this->event,$this->font,"B",16,40,$this->lp,350,30,'left bottom',21,0);
            $this->printTextLineBox($this->cat,$this->font,"B",16,$this->posx+2,$this->lp,300,30,'left bottom',0,0);
            $this->printTextLineBox($this->time,$this->font,"B",16,$this->posx+2,$this->lp,112,30,'right bottom',0,20);
            
            
            
            //$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,762,30,'right bottom',10,5);
            
        }   else {
            $this->printTextLineBox($this->event,$this->font,"B",13,20,$this->lp,250,30,'left bottom',21,0);
            $this->printTextLineBox($this->cat,$this->font,"B",13,$this->posx+2,$this->lp,100,30,'left bottom',0,0);
            $this->printTextLineBox($this->time,$this->font,"B",13,$this->posx+2,$this->lp,201,30,'right bottom',0,15);
            
            
            
            //$this->printTextLineBox($this->timeinfo,$this->font,"",10,40,$this->lp,762,30,'right bottom',10,5);
            
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
        if($this->freetext_bool) {
            if($this->landscape) {   
                $this->printTextLineBox($this->freetxt,$this->font,"",11,40,$this->lp,762,30,'left bottom',0,15);   
            } else {
                $this->printTextLineBox($this->freetxt,$this->font,"",9,20,$this->lp,555,30,'left bottom',0,10);   
            }
        }
    }
    
    function setFollowing($info)
    {
        $this->nextRounds = $info;
    }
    
    function printFollowing()
    {
        if($this->nextRounds != "")
        {
            if($this->landscape) {
                $this->printTextLineBox($this->nextRounds,$this->font,"",13,40,$this->lp,764,30,'right bottom',0,15);    
            } else {
                $this->printTextLineBox($this->nextRounds,$this->font,"",9,20,$this->lp,555,30,'right bottom',0,15);    
            }
        }
    }

    function printRecords($sr_result="", $sr_name="", $sl_result="", $sl_name="", $category="", $sl_cat="")
    {       
        if($this->landscape) {                                                    
            if($sr_result<>"") {
                $this->printTextLineBox("SR " . $category,$this->font,"B",13,457,$this->lp,50,30,'right bottom',5,0);
                $this->printTextLineBox($sr_result,$this->font,"",13,$this->posx+10,$this->lp,60,30,'left bottom',0,0);
                $this->printTextLineBox($sr_name,$this->font,"",13,$this->posx+2,$this->lp,189,30,'left bottom',0,15);
            }
            if($sl_result<>"")
            {
                $this->printTextLineBox("SL " . $sl_cat,$this->font,"B",13,457,$this->lp,50,30,'right bottom',5,0);
                $this->printTextLineBox($sl_result,$this->font,"",13,$this->posx+10,$this->lp,60,30,'left bottom',0,0);
                $this->printTextLineBox($sl_name,$this->font,"",13,$this->posx+2,$this->lp,189,30,'left bottom',0,15);
            }       
            $this->pdf->Line(40,$this->lp,802,$this->lp);
        } else {
            if($sr_result<>"") {
                $this->printTextLineBox("SR " . $category,$this->font,"B",10,270,$this->lp,50,30,'right bottom',5,0);
                $this->printTextLineBox($sr_result,$this->font,"",10,$this->posx+10,$this->lp,50,30,'left bottom',0,0);
                $this->printTextLineBox($sr_name,$this->font,"",10,$this->posx+2,$this->lp,249,30,'left bottom',0,10);
            }
            if($sl_result<>"")
            {
                $this->printTextLineBox("SL " . $sl_cat,$this->font,"B",10,270,$this->lp,50,30,'right bottom',5,0);
                $this->printTextLineBox($sl_result,$this->font,"",10,$this->posx+10,$this->lp,50,30,'left bottom',0,0);
                $this->printTextLineBox($sl_name,$this->font,"",10,$this->posx+2,$this->lp,249,30,'left bottom',0,10);
            }       
            $this->pdf->Line(20,$this->lp,575,$this->lp);    
        }
    }
    
    function printRecordSR($sr_result="", $sr_name="", $category="", $type="")
    {       
        if($this->landscape) {                                                    
            if($sr_result<>"") {
                $this->printTextLineBox($type . " " . $category,$this->font,"B",13,457,$this->lp,50,30,'right bottom',5,0);
                $this->printTextLineBox($sr_result,$this->font,"",13,$this->posx+10,$this->lp,60,30,'left bottom',0,0);
                $this->printTextLineBox($sr_name,$this->font,"",13,$this->posx+2,$this->lp,189,30,'left bottom',0,15);
            }
        } else {
            if($sr_result<>"") {
                $this->printTextLineBox($type . " " . $category,$this->font,"B",10,270,$this->lp,50,30,'right bottom',5,0);
                $this->printTextLineBox($sr_result,$this->font,"",10,$this->posx+10,$this->lp,50,30,'left bottom',0,0);
                $this->printTextLineBox($sr_name,$this->font,"",10,$this->posx+2,$this->lp,249,30,'left bottom',0,10);
            }
        }
    }
    
    function printRecordSL($sl_result="", $sl_name="", $sl_cat="")
    {       
        if($this->landscape) {                                                    
            if($sl_result<>"")
            {
                $this->printTextLineBox("SL " . $sl_cat,$this->font,"B",13,457,$this->lp,50,30,'right bottom',5,0);
                $this->printTextLineBox($sl_result,$this->font,"",13,$this->posx+10,$this->lp,60,30,'left bottom',0,0);
                $this->printTextLineBox($sl_name,$this->font,"",13,$this->posx+2,$this->lp,189,30,'left bottom',0,15);
            }       
        } else {
            if($sl_result<>"")
            {
                $this->printTextLineBox("SL " . $sl_cat,$this->font,"B",10,270,$this->lp,50,30,'right bottom',5,0);
                $this->printTextLineBox($sl_result,$this->font,"",10,$this->posx+10,$this->lp,50,30,'left bottom',0,0);
                $this->printTextLineBox($sl_name,$this->font,"",10,$this->posx+2,$this->lp,249,30,'left bottom',0,10);
            }       
        }
    }

    function printHeatTitle($heat, $installation, $film='', $prev_rnd_name='', $wind='')
    {
        if($this->landscape) {   
            $this->pdf->Line(40,$this->lp,802,$this->lp);    
            $this->printTextLineBox($heat,$this->font,"B",13,40,$this->lp,150,21,'left center',20,0);
            $this->printTextLineBox($this->info,$this->font,"B",13,$this->posx,$this->lp,148,21,'left center',0,0);
            $this->printTextLineBox(($film=="") ? "" : $GLOBALS['strFilm'].": ".$film,$this->font,"I",12,$this->posx,$this->lp,221,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",12,$this->posx,$this->lp,83,21,'left center',0,0);   
            //$this->printTextLineBox(date('Y')-1,$this->font,"B",12,$this->posx,$this->lp,62,21,'left center',0,0); 'Bestenliste Vorjahr  
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",12,$this->posx,$this->lp,103,21,'left center',0,0); 
            $this->printTextLineBox($prev_rnd_name,$this->font,"B",12,$this->posx,$this->lp,80,21,'left center',0,2); 
            $this->pdf->Line(40,$this->lp,802,$this->lp);  
        } else {
            $this->pdf->Line(20,$this->lp,575,$this->lp);
            $this->printTextLineBox($heat,$this->font,"B",10,20,$this->lp,130,21,'left center',20,0);
            $this->printTextLineBox($this->info,$this->font,"B",10,$this->posx,$this->lp,175,21,'left center',0,0);
            $this->printTextLineBox(($film=="") ? "" : $GLOBALS['strFilm'].": ".$film,$this->font,"I",10,$this->posx,$this->lp,80,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,0);   
            //$this->printTextLineBox(date('Y')-1,$this->font,"B",10,$this->posx,$this->lp,62,21,'left center',0,0); 'Bestenliste Vorjahr  
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,0); 
            $this->printTextLineBox($prev_rnd_name,$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,2);
            if($wind != "") {
                $this->printTextLineBox($GLOBALS['strWind']." ".$wind,$this->font,"",9,20,$this->lp,130,21,'left center',12,0);    
            } elseif ($installation != '') {
                $this->printTextLineBox("(".$installation.")",$this->font,"",9,20,$this->lp,130,21,'left center',12,0);
            }
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
    }
    
    function printHeatTitleCombined($heat, $installation, $film='', $prev_rnd_name='')
    {
        if($this->landscape) {  
            $this->pdf->Line(40,$this->lp,802,$this->lp);     
            $this->printTextLineBox($heat,$this->font,"B",13,40,$this->lp,150,21,'left center',20,0);
            $this->printTextLineBox($this->info,$this->font,"B",13,$this->posx,$this->lp,148,21,'left center',0,0);
            $this->printTextLineBox(($film=="") ? "" : $GLOBALS['strFilm'].": ".$film,$this->font,"I",12,$this->posx,$this->lp,221,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",12,$this->posx,$this->lp,83,21,'left center',0,0);   
            //$this->printTextLineBox(date('Y')-1,$this->font,"B",12,$this->posx,$this->lp,62,21,'left center',0,0); 'Bestenliste Vorjahr  
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",12,$this->posx,$this->lp,103,21,'left center',0,0); 
            $this->printTextLineBox($prev_rnd_name,$this->font,"B",12,$this->posx,$this->lp,80,21,'left center',0,0); 
            $this->printTextLineBox($GLOBALS['strPointsShort'],$this->font,"B",12,$this->posx,$this->lp,80,21,'left center',0,2); 
            $this->pdf->Line(40,$this->lp,802,$this->lp);  
        } else {
            $this->pdf->Line(20,$this->lp,575,$this->lp);
            $this->printTextLineBox($heat,$this->font,"B",10,20,$this->lp,140,21,'left center',20,0);
            $this->printTextLineBox($this->info,$this->font,"B",10,$this->posx,$this->lp,135,21,'left center',0,0);
            $this->printTextLineBox(($film=="") ? "" : $GLOBALS['strFilm'].": ".$film,$this->font,"I",10,$this->posx,$this->lp,80,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,0);   
            //$this->printTextLineBox(date('Y')-1,$this->font,"B",10,$this->posx,$this->lp,62,21,'left center',0,0); 'Bestenliste Vorjahr  
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,0); 
            $this->printTextLineBox($prev_rnd_name,$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,0);
            $this->printTextLineBox($GLOBALS['strPointsShort'],$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,2);
            if($installation != "") {
                $this->printTextLineBox("(".$installation.")",$this->font,"",9,20,$this->lp,130,21,'left center',12,0);    
            }
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
    }
    
    function printHeatTitle_athletes($heat, $prev_rnd_name='')
    {
        if($this->landscape) {
            $this->pdf->Line(40,$this->lp,802,$this->lp);       
            $this->printTextLineBox($heat,$this->font,"B",13,40,$this->lp,150,21,'left center',20,0);
            $this->printTextLineBox($this->info,$this->font,"B",13,$this->posx,$this->lp,348,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",12,$this->posx,$this->lp,83,21,'left center',0,0);   
            //$this->printTextLineBox(date('Y')-1,$this->font,"B",12,$this->posx,$this->lp,62,21,'left center',0,0); 'Bestenliste Vorjahr  
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",12,$this->posx,$this->lp,103,21,'left center',0,0); 
            $this->printTextLineBox($prev_rnd_name,$this->font,"B",12,$this->posx,$this->lp,80,21,'left center',0,2); 
            $this->pdf->Line(40,$this->lp,802,$this->lp);  
        } else {
            $this->pdf->Line(20,$this->lp,575,$this->lp);
            $this->printTextLineBox($heat,$this->font,"B",10,20,$this->lp,130,21,'left center',20,0);
            $this->printTextLineBox($this->info,$this->font,"B",10,$this->posx,$this->lp,234,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,0);   
            //$this->printTextLineBox(date('Y')-1,$this->font,"B",10,$this->posx,$this->lp,62,21,'left center',0,0); 'Bestenliste Vorjahr  
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,0); 
            $this->printTextLineBox($prev_rnd_name,$this->font,"B",10,$this->posx+2,$this->lp,50,21,'left center',0,2);
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
    }

    function insertPageBreak()
    { 
        $this->printPageFooter();
        $this->pdf->AddPage();
        $this->printPageHeader();
        $this->printHeaderLine();
        $this->printFollowing();
        $this->printFreeTxt();
    }   

    function printEndHeat()
    {
        return;
    } 

} // end Contest



class PRINT_ContestTrack_speaker_pdf extends PRINT_Contest_speaker_pdf
{
    function printStartHeat($svm = false, $teamsm = false)
    {
              
    }


    function printHeatLine($track=0, $nbr="", $name="", $year="", $club="", $pos=0, $country="", $season_effort="", $best_effort="", $palmares="", $previousRes='')
    {   
        if($this->landscape) {
            
            $this->printTextLineBox($track,$this->font,"B",12,40,$this->lp,15,30,'left bottom',15,0);
            //$this->printTextLineBox("(".$pos.")",$this->font,"",12,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($nbr,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",12,$this->posx+2,$this->lp,190,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",12,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",12,$this->posx+2,$this->lp,180,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",12,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,0);    
            $this->printTextLineBox($previousRes,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,5);    
            
            
            $this->pdf->SetXY(67,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",10);
            $start_y = $this->pdf->GetY();
            $this->pdf->MultiCell(735,15,$palmares);  
            $end_y = $this->pdf->GetY();
            $height = $end_y - $start_y;
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(40,$this->lp,802,$this->lp);
        } else {
    
            $this->printTextLineBox($track,$this->font,"B",9,20,$this->lp,20,30,'left bottom',15,0);
            //$this->printTextLineBox("(".$pos.")",$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($nbr,$this->font,"",8,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",9,$this->posx+2,$this->lp,125,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",9,$this->posx+2,$this->lp,30,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",9,$this->posx+2,$this->lp,150,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0); 
            $this->printTextLineBox($previousRes,$this->font,"b",8,$this->posx+2,$this->lp,50,30,'left bottom',0,5);   
            $this->pdf->SetXY(38,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",7);
            $height = $this->GetMultiCellHeight(455,12,$palmares);          
            $this->pdf->MultiCell(455,12,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
    }

}

class PRINT_ContestTrackCombined_speaker_pdf extends PRINT_Contest_speaker_pdf
{
    function printStartHeat($svm = false, $teamsm = false)
    {
              
    }


    function printHeatLine($track=0, $nbr="", $name="", $year="", $club="", $pos=0, $country="", $season_effort="", $best_effort="", $palmares="", $previousRes='', $points='', $wind='')
    {   
        if($this->landscape) {
            
            $this->printTextLineBox($track,$this->font,"B",12,40,$this->lp,15,30,'left bottom',15,0);
            //$this->printTextLineBox("(".$pos.")",$this->font,"",12,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($nbr,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",12,$this->posx+2,$this->lp,190,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",12,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",12,$this->posx+2,$this->lp,180,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",12,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,0);    
            $this->printTextLineBox($previousRes,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,0);    
            $this->printTextLineBox($points,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,5);    
            
            
            $this->pdf->SetXY(67,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",10);
            $start_y = $this->pdf->GetY();
            $this->pdf->MultiCell(735,15,$palmares);  
            $end_y = $this->pdf->GetY();
            $height = $end_y - $start_y;
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(40,$this->lp,802,$this->lp);
        } else {
    
            $this->printTextLineBox($track,$this->font,"B",9,20,$this->lp,20,30,'left bottom',15,0);
            //$this->printTextLineBox("(".$pos.")",$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($nbr,$this->font,"",8,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",9,$this->posx+2,$this->lp,125,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",9,$this->posx+2,$this->lp,30,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",9,$this->posx+2,$this->lp,120,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0); 
            $this->printTextLineBox($previousRes,$this->font,"b",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);   
            $this->printTextLineBox($points,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,5);

            if ($wind != '') {
                $this->printTextLineBox("(".$wind.")",$this->font,"i",7,$this->posx-50-50-2,$this->lp,50,30,'left bottom',5,5);
                $this->pdf->SetXY(38,$this->pageheight-$this->lp-10);     
            } else {
                $this->pdf->SetXY(38,$this->pageheight-$this->lp); 
            }
            
            $this->pdf->SetFont($this->font,"I",7);
            $height = $this->GetMultiCellHeight(455,12,$palmares);          
            $this->pdf->MultiCell(455,12,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
    }

}

class PRINT_ContestTrack_athletes_speaker_pdf extends PRINT_Contest_speaker_pdf
{
    function printStartHeat($svm = false, $teamsm = false)
    {
              
    }


    function printHeatLine($nbr="", $name="", $year="", $club="", $country="", $season_effort="", $best_effort="", $palmares="", $previousRes='')
    {   
        if($this->landscape) {
            
            $this->printTextLineBox($nbr,$this->font,"",12,40,$this->lp,46,30,'center bottom',15,0);
            $this->printTextLineBox($name,$this->font,"B",12,$this->posx+2,$this->lp,190,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",12,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",12,$this->posx+2,$this->lp,180,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",12,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,0);    
            $this->printTextLineBox($previousRes,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,5);    
            
            
            $this->pdf->SetXY(67,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",10);
            $start_y = $this->pdf->GetY();
            $this->pdf->MultiCell(735,15,$palmares);  
            $end_y = $this->pdf->GetY();
            $height = $end_y - $start_y;
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(40,$this->lp,802,$this->lp);
        } else {
            $this->printTextLineBox($nbr,$this->font,"",8,20,$this->lp,25,30,'left bottom',15,0);
            $this->printTextLineBox($name,$this->font,"B",9,$this->posx+2,$this->lp,125,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",9,$this->posx+2,$this->lp,30,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",9,$this->posx+2,$this->lp,150,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0); 
            $this->printTextLineBox($previousRes,$this->font,"b",8,$this->posx+2,$this->lp,50,30,'left bottom',0,5);   
            $this->pdf->SetXY(38,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",7);
            $height = $this->GetMultiCellHeight(455,12,$palmares);          
            $this->pdf->MultiCell(455,12,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
    }

}


/********************************************
 *
 * PRINT_ContestRelay
 *
 *******************************************/


class PRINT_ContestRelay_speaker_pdf extends PRINT_Contest_speaker_pdf 
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


class PRINT_ContestTech_speaker_pdf extends PRINT_Contest_speaker_pdf
{

    function printHeatTitle_old($heat, $installation)
    {
        if($this->landscape) {
            $this->printTextLineBox($heat,$this->font,"B",13,40,$this->lp,114,21,'left center',20,0);
            $this->printTextLineBox($installation,$this->font,"B",13,$this->posx,$this->lp,114,21,'left center',0,0);
            $this->printTextLineBox($this->info,$this->font,"B",13,$this->posx,$this->lp,221,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",12,$this->posx,$this->lp,82,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",12,$this->posx,$this->lp,80,21,'left center',0,2);
            $this->pdf->Line(40,$this->lp,802,$this->lp);   
        } else {
            $this->printTextLineBox($heat,$this->font,"B",13,40,$this->lp,114,21,'left center',20,0);
            $this->printTextLineBox($installation,$this->font,"B",13,$this->posx,$this->lp,114,21,'left center',0,0);
            $this->printTextLineBox($this->info,$this->font,"B",13,$this->posx,$this->lp,221,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",12,$this->posx,$this->lp,82,21,'left center',0,0);    
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",12,$this->posx,$this->lp,80,21,'left center',0,2);
            $this->pdf->Line(40,$this->lp,802,$this->lp);    
        }
    }


    function printHeatLine($nbr=0, $name="", $year="", $club="", $country="", $season_effort="", $best_effort="", $position="", $palmares="", $previousRes="", $wind='')
    {
        if($this->landscape) {
            $this->printTextLineBox($position,$this->font,"",12,40,$this->lp,15,30,'left bottom',15,0);
            $this->printTextLineBox($nbr,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",12,$this->posx+2,$this->lp,130,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",12,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",12,$this->posx+2,$this->lp,170,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",12,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,5);
            $this->pdf->SetXY(67,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",10);
            $height = $this->GetMultiCellHeight(735,15,$palmares);          
            $this->pdf->MultiCell(735,15,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(40,$this->lp,802,$this->lp);                
        } else {
            $this->printTextLineBox($position,$this->font,"B",9,20,$this->lp,20,30,'left bottom',15,0);
            //$this->printTextLineBox("(".$pos.")",$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($nbr,$this->font,"",8,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",9,$this->posx+2,$this->lp,125,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",9,$this->posx+2,$this->lp,30,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",9,$this->posx+2,$this->lp,150,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0); 
            $this->printTextLineBox($previousRes,$this->font,"b",8,$this->posx+2,$this->lp,50,30,'left bottom',0,5);   
            if ($wind != '') {
                $this->printTextLineBox("(".$wind.")",$this->font,"i",7,$this->posx-50,$this->lp,50,30,'left bottom',5,5);
                $this->pdf->SetXY(38,$this->pageheight-$this->lp-10);     
            } else {
                $this->pdf->SetXY(38,$this->pageheight-$this->lp); 
            }
            
            $this->pdf->SetFont($this->font,"I",7);
            $height = $this->GetMultiCellHeight(455,12,$palmares);          
            $this->pdf->MultiCell(455,12,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
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

class PRINT_ContestTechCombined_speaker_pdf extends PRINT_Contest_speaker_pdf
{

    function printHeatTitle_old($heat, $installation)
    {
        if($this->landscape) {
            $this->printTextLineBox($heat,$this->font,"B",13,40,$this->lp,114,21,'left center',20,0);
            $this->printTextLineBox($installation,$this->font,"B",13,$this->posx,$this->lp,114,21,'left center',0,0);
            $this->printTextLineBox($this->info,$this->font,"B",13,$this->posx,$this->lp,221,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",12,$this->posx,$this->lp,82,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",12,$this->posx,$this->lp,80,21,'left center',0,2);
            $this->pdf->Line(40,$this->lp,802,$this->lp);   
        } else {
            $this->printTextLineBox($heat,$this->font,"B",13,40,$this->lp,114,21,'left center',20,0);
            $this->printTextLineBox($installation,$this->font,"B",13,$this->posx,$this->lp,114,21,'left center',0,0);
            $this->printTextLineBox($this->info,$this->font,"B",13,$this->posx,$this->lp,221,21,'left center',0,0);   
            $this->printTextLineBox($GLOBALS['strSB'],$this->font,"B",12,$this->posx,$this->lp,82,21,'left center',0,0);    
            $this->printTextLineBox($GLOBALS['strPB'],$this->font,"B",12,$this->posx,$this->lp,80,21,'left center',0,2);
            $this->pdf->Line(40,$this->lp,802,$this->lp);    
        }
    }


    function printHeatLine($nbr=0, $name="", $year="", $club="", $country="", $season_effort="", $best_effort="", $position="", $palmares="", $previousRes="", $points='', $wind='')
    {
        if($this->landscape) {
            $this->printTextLineBox($position,$this->font,"",12,40,$this->lp,15,30,'left bottom',15,0);
            $this->printTextLineBox($nbr,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",12,$this->posx+2,$this->lp,130,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",12,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",12,$this->posx+2,$this->lp,170,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",12,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,0);
            $this->printTextLineBox($points,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,5);
            
            $this->pdf->SetXY(67,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",10);
            $height = $this->GetMultiCellHeight(735,15,$palmares);          
            $this->pdf->MultiCell(735,15,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(40,$this->lp,802,$this->lp);                
        } else {
            $this->printTextLineBox($position,$this->font,"B",9,20,$this->lp,20,30,'left bottom',15,0);
            //$this->printTextLineBox("(".$pos.")",$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($nbr,$this->font,"",8,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",9,$this->posx+2,$this->lp,125,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",9,$this->posx+2,$this->lp,30,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",9,$this->posx+2,$this->lp,120,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0); 
            $this->printTextLineBox($previousRes,$this->font,"b",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);   
            $this->printTextLineBox($points,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,5);   

            if ($wind != '') {
                $this->printTextLineBox("(".$wind.")",$this->font,"i",7,$this->posx-50-2-50,$this->lp,50,30,'left bottom',5,5);
                $this->pdf->SetXY(38,$this->pageheight-$this->lp-10);     
            } else {
                $this->pdf->SetXY(38,$this->pageheight-$this->lp); 
            }
            
            $this->pdf->SetFont($this->font,"I",7);
            $height = $this->GetMultiCellHeight(455,12,$palmares);          
            $this->pdf->MultiCell(455,12,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
    }
    
    function printHeatLineWithoutPoints($nbr=0, $name="", $year="", $club="", $country="", $season_effort="", $best_effort="", $position="", $palmares="", $previousRes="")
    {
        if($this->landscape) {
            $this->printTextLineBox($position,$this->font,"",12,40,$this->lp,15,30,'left bottom',15,0);
            $this->printTextLineBox($nbr,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",12,$this->posx+2,$this->lp,130,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",12,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",12,$this->posx+2,$this->lp,170,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",12,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,0);
            
            $this->pdf->SetXY(67,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",10);
            $height = $this->GetMultiCellHeight(735,15,$palmares);          
            $this->pdf->MultiCell(735,15,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(40,$this->lp,802,$this->lp);                
        } else {
            $this->printTextLineBox($position,$this->font,"B",9,20,$this->lp,20,30,'left bottom',15,0);
            //$this->printTextLineBox("(".$pos.")",$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($nbr,$this->font,"",8,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($name,$this->font,"B",9,$this->posx+2,$this->lp,125,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",9,$this->posx+2,$this->lp,30,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",9,$this->posx+2,$this->lp,150,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0); 
            $this->printTextLineBox($previousRes,$this->font,"b",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);     
            
            $this->pdf->SetXY(38,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",7);
            $height = $this->GetMultiCellHeight(455,12,$palmares);          
            $this->pdf->MultiCell(455,12,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
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

}

class PRINT_ContestTech_athletes_speaker_pdf extends PRINT_Contest_speaker_pdf
{

    function printHeatLine($nbr=0, $name="", $year="", $club="", $country="", $season_effort="", $best_effort="", $palmares="", $previousRes="")
    {
        if($this->landscape) {
            $this->printTextLineBox($nbr,$this->font,"",12,40,$this->lp,46,30,'center bottom',15,0);
            $this->printTextLineBox($name,$this->font,"B",12,$this->posx+2,$this->lp,130,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",12,$this->posx+2,$this->lp,46,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",12,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",12,$this->posx+2,$this->lp,170,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",12,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",12,$this->posx+2,$this->lp,100,30,'left bottom',0,5);
            $this->pdf->SetXY(67,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",10);
            $height = $this->GetMultiCellHeight(735,15,$palmares);          
            $this->pdf->MultiCell(735,15,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(40,$this->lp,802,$this->lp);                
        } else {
            $this->printTextLineBox($nbr,$this->font,"",8,20,$this->lp,25,30,'left bottom',15,0);
            $this->printTextLineBox($name,$this->font,"B",9,$this->posx+2,$this->lp,125,30,'left bottom',0,0);
            $this->printTextLineBox($year,$this->font,"",9,$this->posx+2,$this->lp,30,30,'center bottom',0,0);
            $this->printTextLineBox((($country!='' && $country!='-') ? $country : ''),$this->font,"",9,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
            $this->printTextLineBox($club,$this->font,"",9,$this->posx+2,$this->lp,150,30,'left bottom',0,0);
            $this->printTextLineBox($season_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0);
            $this->printTextLineBox($best_effort,$this->font,"",8,$this->posx+2,$this->lp,50,30,'left bottom',0,0); 
            $this->printTextLineBox($previousRes,$this->font,"b",8,$this->posx+2,$this->lp,50,30,'left bottom',0,5);   
            $this->pdf->SetXY(38,$this->pageheight-$this->lp);
            $this->pdf->SetFont($this->font,"I",7);
            $height = $this->GetMultiCellHeight(455,12,$palmares);          
            $this->pdf->MultiCell(455,12,$palmares);  
            $this->lp = $this->lp - ($height);
            $this->pdf->Line(20,$this->lp,575,$this->lp);
        }
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

}

    
} // end AA_CL_PRINT_CONTEST_SPEAKER_LIB_INCLUDED
?>
