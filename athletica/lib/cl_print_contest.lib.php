<?php

if (!defined('AA_CL_PRINT_CONTEST_LIB_INCLUDED'))
{
    define('AA_CL_PRINT_CONTEST_LIB_INCLUDED', 1);


    require('./lib/cl_print_page.lib.php');

/********************************************
 *
 * PRINT_Contest
 *
 *    Class to print contest sheets
 *
 *******************************************/


class PRINT_Contest extends PRINT_Page
{

    var $cat;
    var $event;
    var $info;
    var $time;
    var $freetxt;
    var $resultinfo;
    var $timeinfo;

    function printHeaderLine()
    {
        
?>
    <table>
    <!--<tr>
        <th class='contest_meeting' colspan='3'>
            <?php echo $_COOKIE['meeting']; ?></th>
    </tr>-->
    <tr>
        <th class='contest_event'><?php echo $this->event; ?></th>
        <th class='contest_cat'><?php echo $this->cat; ?></th>
        <th class='contest_time'><?php echo $this->time; ?></th>
    </tr>
    <tr>
        <th class='contest_timeinfo' colspan="3">
            <?php echo $this->timeinfo ?>
        </th>
    </tr>
    <tr>
        <td class='contest_free' colspan='3'>
<?php
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
        echo $txt;
?>
        </td>
    </tr>
<?php
        if($this->resultinfo != ""){
            ?>
    <tr>
        <td class='contest_free' colspan='3'>
            <?php echo $this->resultinfo ?>
        </td>
    </tr>
            <?php
        }
?>
    </table>
<?php
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
        
?>
    <table>
    <tr>
        <td class='contest_free'>
            <?php echo $this->freetxt; ?>
        </td>
    </tr>
    </table>
<?php
    }


    function printHeatTitle($heat, $installation, $film='')
    {
?>
    </p>
    <table>
    <tr>
        <th class='contest_heat'><?php echo $heat; ?></th>
        <th class='contest_installation'><?php echo $installation; ?></th>
        <th class='contest_info'><?php echo $this->info; ?></th>
        <th class='contest_film'><?php echo $GLOBALS['strFilm']; ?>: <?php echo $film ?></th>
        <th class='contest_wind'><?php echo $GLOBALS['strWind']; ?>:</th>
    </tr>
    </table>
<?php
    }

    function insertPageBreak()
    {     
        global $cfgPageContentHeight;             
?>
    </td>
</tr>
</table>
</div>

<?php $this->printPageFooter() ?>

<br style='page-break-after:always' />

<?php $this->printPageHeader() ?>
   
<div style="height:<?php echo $cfgPageContentHeight ?>mm;">
<table class='frame'>
<tr class='frame'>
    <td class='frame'>

<?php
        $this->linecnt = 0;
        $this->printHeaderLine();
        $this->printFreeTxt();
    }   

    function printEndHeat()
    {
?>
    </table>
<?php
    }

} // end Contest



/********************************************
 *
 * PRINT_ContestTrack
 *
 *******************************************/


class PRINT_ContestTrack extends PRINT_Contest
{
    function printStartHeat($svm = false, $teamsm = false)
    {
?>
    <table>
    <tr>
        <th class='contest_track_track'><?php echo $GLOBALS['strTrack']; ?></th>
        <th class='contest_track_pos'><?php echo $GLOBALS['strPositionShort']; ?></th>
        <th class='contest_track_nbr'><?php echo $GLOBALS['strStartnumber']; ?></th>
        <th class='contest_track_name'><?php echo $GLOBALS['strName']; ?></th>
        <th class='contest_track_year'><?php echo $GLOBALS['strYearShort']; ?></th>
        <th class='contest_track_country'><?php echo $GLOBALS['strCountry']; ?></th>
        <?php
        if($svm){
            ?>
        <th class='contest_track_club'><?php echo $GLOBALS['strTeam']; ?></th>
            <?php
        }elseif ($teamsm){
            ?>
        <th class='contest_track_club'><?php echo $GLOBALS['strTeamsm']; ?></th>
            <?php
        }
        else {
            ?>
        <th class='contest_track_club'><?php echo $GLOBALS['strClub']; ?></th>
            <?php
        }       
        ?>
        
        <th class='contest_track_result'><?php echo $GLOBALS['strResult']; ?></th>
        <th class='contest_track_rank'><?php echo $GLOBALS['strRank']; ?></th>
    </tr>
<?php
        
    }


    function printHeatLine($track=0, $nbr="", $name="", $year="", $club="", $pos=0, $country="")
    {
        
?>
    <tr>
        <td class='contest_track_track'><?php echo $pos; ?></td>
        <td class='contest_track_pos'><?php echo $track; ?></td>
        <td class='contest_track_nbr'><?php echo $nbr; ?></td>
        <td class='contest_track_name'><?php echo $name; ?></td>
        <td class='contest_track_year'><?php echo $year; ?></td>
        <td class='contest_track_country'><?php echo (($country!='' && $country!='-') ? $country : '&nbsp;'); ?></td>
        <td class='contest_track_club'><?php echo $club; ?></td>
        <td class='contest_track_result'></td>
        <td class='contest_track_rank'></td>
    </tr>
<?php
        
    }

} // end ContestTrack



/********************************************
 *
 * PRINT_ContestTrackNoWind
 *
 *******************************************/


class PRINT_ContestTrackNoWind extends PRINT_ContestTrack
{

    function printHeatTitle($heat, $installation, $film='')
    {
        
?>
    <p />
    <table>
    <tr>
        <th class='contest_heat'><?php echo $heat; ?></th>
        <th class='contest_installation'></th>
        <th class='contest_info'><?php echo $this->info; ?></th>
        <th class='contest_wind'></th>
        <th class='contest_film'><?php echo $GLOBALS['strFilm']; ?>: <?php echo $film ?></th>
    </tr>
    </table>
<?php
        
    }

} // end ContestTrackNoWind



/********************************************
 *
 * PRINT_ContestRelay
 *
 *******************************************/


class PRINT_ContestRelay extends PRINT_Contest
{

    function printHeatTitle($heat, $installation, $film='')
    {
?>
    <p />
    <table>
    <tr>
        <th class='contest_heat'><?php echo $heat; ?></th>
        <th class='contest_installation'></th>
        <th class='contest_info'><?php echo $this->info; ?></th>
        <th class='contest_wind'></th>
        <th class='contest_film'><?php echo $GLOBALS['strFilm']; ?>: <?php echo $film ?></th>
    </tr>
    </table>
<?php
    }


    function printStartHeat($svm = false)
    {
?>
    <table>
    <tr>
        <th class='contest_track_track'><?php echo $GLOBALS['strTrack']; ?></th>
        <th class='contest_track_relay'><?php echo $GLOBALS['strRelays']; ?></th>
        <?php
        if($svm){
            ?>
        <th class='contest_track_club'><?php echo $GLOBALS['strTeam']; ?></th>
            <?php
        }else{
            ?>
        <th class='contest_track_club'><?php echo $GLOBALS['strClub']; ?></th>
            <?php
        }
        ?>
        <th class='contest_track_result'><?php echo $GLOBALS['strResult']; ?></th>
        <th class='contest_track_rank'><?php echo $GLOBALS['strRank']; ?></th>
    </tr>
<?php
    }


    function printHeatLine($track=0, $relay="", $club="", $country="")
    {   
?>
    <tr>
        <td class='contest_track_track'><?php echo $track; ?></td>
        <td class='contest_track_relay'><?php echo $relay; ?></td>
        <td class='contest_track_country' text-valign='top'><?php echo $club; ?></td>
        <td class='contest_track_result' text-valign='top'></td> 
        <td class='contest_track_rank'></td>
    </tr>
<?php
    }

} // end ContestRelay




/********************************************
 *
 * PRINT_ContestTech
 *
 *******************************************/


class PRINT_ContestTech extends PRINT_Contest
{

    function printHeatTitle($heat, $installation)
    {
?>
    <p />
    <table>
    <tr>
        <th class='contest_heat'><?php echo $heat; ?></th>
        <th class='contest_installation'><?php echo $installation; ?></th>
        <th class='contest_info'><?php echo $this->info; ?></th>
        <th class='contest_wind'></th>
        <th class='contest_film'></th>
    </tr>
    </table>
<?php
    }


    function printHeatLine($nbr=0, $name="", $year="", $club="", $country="")
    {
?>
    <table>
    <tr>
        <th class='contest_tech_nbr'><?php echo $nbr; ?></th>
        <th class='contest_tech_name'><?php echo $name; ?></th>
        <th class='contest_tech_year'><?php echo $year; ?></th>
        <td class='contest_tech_country'><?php echo (($country!='' && $country!='-') ? $country : '&nbsp;'); ?></td>
        <th class='contest_tech_club'><?php echo $club; ?></th>
    </tr>
    </table>
    <table>
    <!--<tr>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'>1.</td>
        <td class='contest_tech_field'>2.</td>
        <td class='contest_tech_field'>3.</td>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'><?php echo $GLOBALS['strRank']; ?></td>
        <td class='contest_tech_field'>4.</td>
        <td class='contest_tech_field'>5.</td>
        <td class='contest_tech_field'>6.</td>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'><?php echo $GLOBALS['strRank']; ?></td>
    </tr>
    <tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
    </tr>
    <tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strWind']; ?></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
    </tr>-->
    <tr>
        <td class='contest_tech_result'></td>
        <?php $this->multiAttempt("<td class='contest_tech_field'>%s</td>", true); ?>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'><?php echo $GLOBALS['strRank']; ?></td>
    </tr>
    <tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <?php $this->multiAttempt("<td class='contest_tech_field'></td>"); ?>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
    </tr>
    <tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strWind']; ?></td>
        <?php $this->multiAttempt("<td class='contest_tech_field'></td>"); ?>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
    </tr>
    </table>
<?php
    }
    
    function multiAttempt($line, $head = false){
        for($i = 0; $i<$_POST['countattempts']; $i++){
            if($i == 3 && !$head && $_POST['countattempts'] != 4){
                ?>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
                <?php
            }
            
            if($i == 3 && $head && $_POST['countattempts'] != 4){
                ?>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'><?php echo $GLOBALS['strRank']; ?></td>
                <?php
            }
            
            ?>
        <?php echo sprintf($line, ($i+1)."."); ?>
            <?php
        }
    }

} // end ContestTech


/********************************************
 *
 * PRINT_ContestTechNoWind
 *
 *******************************************/


class PRINT_ContestTechNoWind extends PRINT_ContestTech
{

    function printHeatLine($nbr=0, $name="", $year="", $club="", $country="")
    {
?>
    <table>
    <tr>
        <th class='contest_tech_nbr'><?php echo $nbr; ?></th>
        <th class='contest_tech_name'><?php echo $name; ?></th>
        <th class='contest_tech_year'><?php echo $year; ?></th>
        <td class='contest_tech_country'><?php echo (($country!='' && $country!='-') ? $country : '&nbsp;'); ?></td>
        <th class='contest_tech_club'><?php echo $club; ?></th>
    </tr>
    </table>
    <table>
    <!--<tr>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'>1.</td>
        <td class='contest_tech_field'>2.</td>
        <td class='contest_tech_field'>3.</td>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'><?php echo $GLOBALS['strRank']; ?></td>
        <td class='contest_tech_field'>4.</td>
        <td class='contest_tech_field'>5.</td>
        <td class='contest_tech_field'>6.</td>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'><?php echo $GLOBALS['strRank']; ?></td>
    </tr>
    <tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_field'></td>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
    </tr>-->
    <tr>
        <td class='contest_tech_result'></td>
        <?php $this->multiAttempt("<td class='contest_tech_field'>%s</td>", true); ?>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <td class='contest_tech_field'><?php echo $GLOBALS['strRank']; ?></td>
    </tr>
    <tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <?php $this->multiAttempt("<td class='contest_tech_field'></td>"); ?>
        <td class='contest_tech_result'></td>
        <td class='contest_tech_field'></td>
    </tr>
    </table>
<?php
    }

} // end ContestTechNoWind




/********************************************
 *
 * PRINT_ContestHigh
 *
 *******************************************/

class PRINT_ContestHigh extends PRINT_ContestTech
{
    function printHeatTitle($heat, $installation)
    {
?>
    <p />
    <table>
    <tr>
        <th class='contest_heat'><?php echo $heat; ?></th>
        <th class='contest_installation'><?php echo $installation; ?></th>
        <th class='contest_info'><?php echo $this->info; ?></th>
        <th class='contest_wind'></th>
        <th class='contest_film'></th>
    </tr>
    </table>
    <table>
    <tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strHeight']; ?></td>
        <?php //$this->multiplyLine("<td class='contest_tech_high'></td>"); 
        ?>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
    </tr>
    <tr>
        <td></td>
        <?php //$this->multiplyLine("<td class='contest_tech_high_bottom'>&nbsp;</td>"); 
        ?>
        <td class='contest_tech_high_bottom'>&nbsp;</td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
    </tr>
    </table>
<?php
    }

    function printHeatLine($nbr=0, $name="", $year="", $club="", $country="")
    {
?>
    <table>
    <tr>
        <th class='contest_tech_nbr'><?php echo $nbr; ?></th>
        <th class='contest_tech_name'><?php echo $name; ?></th>
        <th class='contest_tech_year'><?php echo $year; ?></th>
        <td class='contest_tech_country'><?php echo (($country!='' && $country!='-') ? $country : '&nbsp;'); ?></td>
        <th class='contest_tech_club'><?php echo $club; ?></th>
    </tr>
    </table>
    <table>
    <!--<tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strHeight']; ?></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
    </tr>-->
    <tr>
        <td class='contest_tech_result'><?php echo $GLOBALS['strResult']; ?></td>
        <?php //$this->multiplyLine("<td class='contest_tech_high'></td>"); 
        ?>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
        <td class='contest_tech_high'></td>
    </tr>
    <tr>
        <td></td>
        <?php //$this->multiplyLine("<td class='contest_tech_high_bottom'>&nbsp;</td>"); 
        ?>
        <td class='contest_tech_high_bottom'>&nbsp;</td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
        <td class='contest_tech_high_bottom'></td>
    </tr>
    
    </table>
<?php
    }


} // end ContestHigh



} // end AA_CL_PRINT_CONTEST_LIB_INCLUDED
?>
