<?php
require('./lib/common.lib.php');

require('./lib/meeting.lib.php');
require('lib/cl_protect.lib.php');


if(empty($_GET['arg'])) {
	$arg='meeting';
}
else {
	$arg = $_GET['arg'];
}

if(empty($_GET['arg2'])) {
    $arg2='';
}
else {
    $arg2 = $_GET['arg2'];
}

if($arg == "login"){
	$arg = $_GET['redirect'];
}

$ukc_meeting = 'n';

if(!empty($_GET['meetingID'])) {
    $meeting = $_GET['meetingID'];
    $ukc_meeting = AA_checkMeeting_UKC($meeting) ; 
}
else {
    if(!empty($_GET['ukc'])) {
        $ukc_meeting=$_GET['ukc'];
    }
}


/**************
 *
 *	menu.php
 * --------
 * This script is used by index.php to dynamically setup the main menu bar.
 * Each value in "subitems" represents the php-script to be executed (script
 * name without .php) when an item is selected.
 *
 */


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title><?php echo $arg; ?></title>

<link rel="stylesheet" href="css/navigation.css" type="text/css">
</head>

<body>

<?php
	$meeting_class = 'main_inactive';
	$event_class = 'main_inactive';
	$speaker_class = 'main_inactive';
    $regie_class = 'main_inactive'; 
	$admin_class = 'main_inactive';
    

   if (!empty($arg2)){
        $arg_save = $arg;
       $arg = "regie";
   }

	switch($arg) {
		//
		// Main menu 'MEETING'
		//
		case 'meeting':
		case 'meeting_definition':
		case 'meeting_entries':
		case 'meeting_relays':
		case 'meeting_teams':
		case 'meeting_teamsms':
		case 'meeting_timing':
		case 'meeting_page_layout':

			$meeting_class = 'main';
            
            if ($ukc_meeting == 'n'){
			// submenus
			$subitems= array(0 => 'meeting_definition'
								, 1 => 'meeting_entries'
								, 2 => 'meeting_relays'
								, 3 => 'meeting_teams'
								, 4 => 'meeting_teamsms'
								, 5 => 'meeting_timing'
								, 6 => 'meeting_page_layout');
			// submenu titles
			$subtitles= array(0 => $strMeetingDefinition
								, 1 => $strEntries
								, 2 => $strRelays
								, 3 => $strTeams
								, 4 => $strTeamsTeamSM
								, 5 => $strTiming
								, 6 => $strPageLayoutShort);
			// submenu style
			$subitem_class= array(0 => 'subitem_inactive'
								, 1 => 'subitem_inactive'
								, 2 => 'subitem_inactive'
								, 3 => 'subitem_inactive'
								, 4 => 'subitem_inactive'
								, 5 => 'subitem_inactive'
								, 6 => 'subitem_inactive');
            }
             else {
                 $subitems= array(0 => 'meeting_definition'
                                , 1 => 'meeting_entries'
                               
                                , 2 => 'meeting_timing'
                                , 3 => 'meeting_page_layout');
            // submenu titles
            $subtitles= array(0 => $strMeetingDefinition
                                , 1 => $strEntries
                               
                                , 2 => $strTiming
                                , 3 => $strPageLayoutShort);
            // submenu style
            $subitem_class= array(0 => 'subitem_inactive'
                                , 1 => 'subitem_inactive'
                                
                                , 2 => 'subitem_inactive'
                                , 3 => 'subitem_inactive');
                 
                 
                 
             }
			// highlight current submenu
			switch($arg) {
				case 'meeting_definition':
					$subitem_class[0]='subitem';
					break;
				case 'meeting_entries':
					$subitem_class[1]='subitem';
					break;
				case 'meeting_relays':
					$subitem_class[2]='subitem';
					break;
				case 'meeting_teams':
					$subitem_class[3]='subitem';
					break;
				case 'meeting_teamsms':
					$subitem_class[4]='subitem';
					break;
				case 'meeting_timing':
					$subitem_class[5]='subitem';
					break;
				case 'meeting_page_layout':
					$subitem_class[6]='subitem';
					break;
			}
			break;

		//
		// Main menu 'EVENT'
		//
		case 'event':
		case 'event_enrolement':
		case 'event_heats':
		case 'event_results':
		case 'event_rankinglists': 
			$event_class = 'main';
			// submenus
			$subitems= array(0 => 'event_enrolement'
								, 1 => 'event_heats'
								, 2 => 'event_results'
								, 3 => 'event_rankinglists');
			// submenu titles
			$subtitles= array(0 => $strEnrolement
								, 1 => $strHeats
								, 2 => $strResults
								, 3 => $strRankingLists);
			// submenu style
			$subitem_class= array(0 => 'subitem_inactive'
								, 1 => 'subitem_inactive'
								, 2 => 'subitem_inactive'
								, 3 => 'subitem_inactive');
			// highlight current submenu
			switch($arg) {
				case 'event_enrolement':
					$subitem_class[0]='subitem';
					break;
				case 'event_heats':
					$subitem_class[1]='subitem';
					break;
				case 'event_results':
					$subitem_class[2]='subitem';
					break;
				case 'event_rankinglists':
					$subitem_class[3]='subitem';
					break;
			}

			break;

		//
		// Main menu 'SPEAKER'
		//	- reuses event rankinglists
		case 'speaker':
		case 'speaker_entries':
		case 'speaker_results':
		case 'speaker_rankinglists':  
            
			$speaker_class = 'main';
			// submenus
			$subitems= array(0 => 'speaker_entries'
								, 1 => 'speaker_results'
								, 2 => 'speaker_rankinglists');
			// submenu titles
			$subtitles= array(0 => $strEntries
								, 1 => "$strHeats & $strResults"
								, 2 => $strRankingLists);
			// submenu style
			$subitem_class= array(0 => 'subitem_inactive'
								, 1 => 'subitem_inactive'
								, 2 => 'subitem_inactive');
			// highlight current submenu
			switch($arg) {
				case 'speaker_entries':
					$subitem_class[0]='subitem';
					break;
				case 'speaker_results':
					$subitem_class[1]='subitem';
					break;
				case 'speaker_rankinglists':
					$subitem_class[2]='subitem';
					break;
			}
			break;

        //
        // Main menu 'REGIE'
        //    - reuses event rankinglists
        case 'regie':
              $arg2 = 'regie';
        case 'speaker_entries':
        case 'speaker_results&regie':
        case 'speaker_rankinglists':
            if (!empty($arg2)){
                $arg = $arg_save;
            }
            $regie_class = 'main';
            // submenus
            $subitems= array(0 => 'speaker_entries'
                                , 1 => 'speaker_results&regie'
                                , 2 => 'speaker_rankinglists');
            // submenu titles
            $subtitles= array(0 => $strEntries
                                , 1 => "$strHeats & $strResults"
                                , 2 => $strRankingLists);
            // submenu style
            $subitem_class= array(0 => 'subitem_inactive'
                                , 1 => 'subitem_inactive'
                                , 2 => 'subitem_inactive');
            // highlight current submenu
            switch($arg) {
                case 'speaker_entries':
                    $subitem_class[0]='subitem';
                    break;
                case 'speaker_results':
                    $subitem_class[1]='subitem';
                    break;
                case 'speaker_rankinglists':
                    $subitem_class[2]='subitem';
                    break;
            }
            break;
		//
		// Main menu 'ADMIN'
		//
		case 'admin':
		case 'admin_base':
		case 'admin_results':
		case 'admin_categories':
		case 'admin_disciplines':
		case 'admin_scoretables':
		case 'admin_clubs':
		case 'admin_athletes':
		case 'admin_stadiums':
		case 'admin_roundtypes':
		case 'admin_faq':
		case 'admin_region':
		case 'admin_service':   
			$admin_class = 'main';
            
             if ($ukc_meeting == 'n'){
			// submenus
			$subitems= array(0 => 'admin_categories'
								, 1 => 'admin_disciplines'
								, 2 => 'admin_scoretables'
								, 3 => 'admin_clubs'
								, 4 => 'admin_region'
								, 5 => 'admin_athletes'
								, 6 => 'admin_stadiums'
								, 7 => 'admin_roundtypes'
								, 8 => 'admin_faq'
								, 9 => 'admin_service');
			// submenu titles
			$subtitles= array(0 => $strCategories
								, 1 => $strDisciplines
								, 2 => $strScoreTables
								, 3 => $strClubs
								, 4 => $strRegion
								, 5 => $strAthletes
								, 6 => $strStadiums
								, 7 => $strRoundtypes
								, 8 => $strFaq
								, 9 => $strService);
			// submenu style
			$subitem_class= array(0 => 'subitem_inactive'
								, 1 => 'subitem_inactive'
								, 2 => 'subitem_inactive'
								, 3 => 'subitem_inactive'
								, 4 => 'subitem_inactive'
								, 5 => 'subitem_inactive'
								, 6 => 'subitem_inactive'
								, 7 => 'subitem_inactive'
								, 8 => 'subitem_inactive'
								, 9 => 'subitem_inactive');
             }
             else {
                 // submenus
            $subitems= array(0 => 'admin_categories'
                                , 1 => 'admin_disciplines'                               
                                , 2 => 'admin_clubs'
                                , 3 => 'admin_region'
                                , 4 => 'admin_athletes'
                                , 5 => 'admin_stadiums'
                                , 6 => 'admin_roundtypes'
                                , 7 => 'admin_faq'
                                , 8 => 'admin_service');
            // submenu titles
            $subtitles= array(0 => $strCategories
                                , 1 => $strDisciplines                                
                                , 2 => $strClubs
                                , 3 => $strRegion
                                , 4 => $strAthletes
                                , 5 => $strStadiums
                                , 6 => $strRoundtypes
                                , 7 => $strFaq
                                , 8 => $strService);
            // submenu style
            $subitem_class= array(0 => 'subitem_inactive'
                                , 1 => 'subitem_inactive'                               
                                , 2 => 'subitem_inactive'
                                , 3 => 'subitem_inactive'
                                , 4 => 'subitem_inactive'
                                , 5 => 'subitem_inactive'
                                , 6 => 'subitem_inactive'
                                , 7 => 'subitem_inactive'
                                , 8 => 'subitem_inactive');
             }
			// highlight current submenu
			switch($arg) {
				case 'admin_categories':
					$subitem_class[0]='subitem';
					break;
				case 'admin_disciplines':
					$subitem_class[1]='subitem';
					break;
				case 'admin_scoretables':
					$subitem_class[2]='subitem';
					break;
				case 'admin_clubs':
					$subitem_class[3]='subitem';
					break;
				case 'admin_region':
					$subitem_class[4]='subitem';
					break;
				case 'admin_athletes':
					$subitem_class[5]='subitem';
					break;
				case 'admin_stadiums':
					$subitem_class[6]='subitem';
					break;
				case 'admin_roundtypes':
					$subitem_class[7]='subitem';
					break;
				case 'admin_faq':
					$subitem_class[8]='subitem';
					break;
				case 'admin_service':
					$subitem_class[9]='subitem';
					break;
			}
			break;
	}

//
//	Set up main menu
//
?>
<table width="100%">
  <tr> 
	<td class="<?php echo $meeting_class; ?>" height="20" width="20%">
		<a href="index.php" target="_parent">
			<?php echo $strMeetingTitle; ?></a>
	</td>
	<td class="<?php echo $event_class; ?>" height="20" width="20%">
		<a href="index.php?arg=event" target="_parent">
			<?php echo $strEvent; ?></a>
	</td>
	<td class="<?php echo $speaker_class; ?>" height="20" width="20%">
		<a href="index.php?arg=speaker" target="_parent">
			<?php echo $strSpeaker; ?></a>
	</td>
    <td class="<?php echo $regie_class; ?>" height="20" width="20%">
        <a href="index.php?arg=regie" target="_parent">
            <?php echo $strRegie; ?></a>
    </td>
	<td class="<?php echo $admin_class; ?>" height="20" width="20%">
		<a href="index.php?arg=admin" target="_parent">
			<?php echo $strAdministration; ?></a>
	</td>
  </tr>
</table>

<?php
//
//	Set up sub-menu items
//
?>
<table width="100%">
  <tr> 
<?php
	$i=0;
	if(is_array($subitems))
	{
		$percent_width = 100 / count($subitems);
		foreach($subitems as $item)
		{         if ($item == 'speaker_results&regie'){
                      $item =   'speaker_results';
        }
			echo "<td nowrap=\"nowrap\" class=\"$subitem_class[$i]\" height=\"20\" width=\"$percent_width%\">\n";
			echo "<a href=\"index.php?arg=$item&arg2=$arg2\" target=\"_parent\">\n";
			echo $subtitles[$i] . "</a></td>\n";
			$i++;	
		}
	}
?>
  </tr> 
</table>

</body>
</html>
