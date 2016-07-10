<?php
/**********
 *
 *	meeting maintenance functions
 *	
 */

if (!defined('AA_MEETING'))
{
	define('AA_MEETING_LIB_INCLUDED', 1);

//
// change general meeting data
//
function AA_meeting_changeData()
{   
	include('./config.inc.php');
	require('./lib/common.lib.php');

	// Assemble date
	$fromdate = $_POST['from_year'] . $_POST['from_month'] . $_POST['from_day'];
	$todate = $_POST['to_year'] . $_POST['to_month'] . $_POST['to_day'];
	$fee =  strtr($_POST['fee'], ",", ".");
	$feereduction = strtr($_POST['feereduction'], ",", ".");
	$deposit = strtr($_POST['deposit'], ",", ".");
	$saison = $_POST['saison'];
		
	$online = "";
	if($_POST['online'] == 'yes'){
		$online = "y";
	}else{
		$online = "n";
	}
	
	// Error: Empty fields
	if(!is_numeric($fromdate) || !is_numeric($todate))
	{
		AA_printErrorMsg($strErrInvalidDate);
	}
	// Error: Empty fields
	else if(empty($_POST['name']) || empty($_POST['place']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	else if($fromdate > $todate)			// invalid order
	{
		AA_printErrorMsg($strErrTodateLowerFromdate);
	}
	else if(!is_numeric($fee))			// invalid amount
	{
		AA_printErrorMsg($strFee . $strErrNotValid);
	}
	else if(!is_numeric($feereduction))			// invalid amount
	{
		AA_printErrorMsg($strFeeReduction . $strErrNotValid);
	}
	else if(!is_numeric($deposit))			// invalid amount
	{
		AA_printErrorMsg($strDeposit . $strErrNotValid);
	}

	
	// OK: try to change item
	else
	{
		mysql_query("LOCK TABLES stadion READ, meeting WRITE");
		// check if stadium is valid
		if(AA_checkReference("stadion", "xStadion", $_POST['stadium']) == 0)
		{
			AA_printErrorMsg($strStadium . $strErrNotValid);
		}
		else
		{
			$sql = "UPDATE meeting SET 
					Name=\"" . $_POST['name'] . "\"
					, Ort=\"" . $_POST['place'] . "\"
					, DatumVon='" . $fromdate . "'
					, DatumBis='" . $todate . "'
					, Nummer=\"" . $_POST['nbr'] . "\"
					, ProgrammModus=" . $_POST['mode'] . "
					, Online='$online'
					, xStadion=" . $_POST['stadium'] . "
					, Organisator = '".$_POST['organisator']."'
					, Startgeld = '".($fee*100) ."'
					, StartgeldReduktion = '".($feereduction*100)."'
					, Haftgeld = '".($deposit*100)."'
					, Saison = '".$saison."'
				WHERE xMeeting=" . $_POST['item'];
			
			mysql_query($sql);
		}
		// Check if any error returned from DB
		if(mysql_errno() > 0)
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{
			$_COOKIE['meeting'] = $_POST['name'];	// keep new meeting name
		}
		mysql_query("UNLOCK TABLES");
	}

	return;
}

//
// add a new event
//
function AA_meeting_addEvent()
{  
	require('./lib/common.lib.php');
	require('./lib/cl_timetable.lib.php');
	
	$info = '';
	if(!empty($_POST['info'])) {
		$info = $_POST['info'];
	}

	$type = 0;
	if(!empty($_POST['type'])) {
		$type = $_POST['type'];
	}

	$deposit = 0;
	if(!empty($_POST['deposit'])) {
		$deposit = strtr($_POST['deposit'], ",", ".");
	}

	$fee = 0;
	if(!empty($_POST['fee'])) {
		$fee = strtr($_POST['fee'], ",", ".");
	}

	$conv = 0;
	$formula = 0;
	if(!empty($_POST['conv']))
	{
		$conv = $_POST['conv'];
		if(!empty($_POST['formula'])) {
			$formula = $_POST['formula'];
		}
	}
	
	$stdEtime = '';
	$stdMtime = '';

	mysql_query("LOCK TABLES kategorie READ, disziplin_de READ, disziplin_fr READ, disziplin_ite READ,  meeting READ"
					. ", wettkampf WRITE");
	// check if category ist still valid
	if(AA_checkReference("kategorie", "xKategorie", $_POST['cat']) == 0)
	{
		AA_printErrorMsg($GLOBALS['strCategory'] . $GLOBALS['strErrNotValid']);
	}
	else
	{
		// check if meeting is still valid
		if(AA_checkReference("meeting", "xMeeting", $_COOKIE['meeting_id']) == 0)
		{
			AA_printErrorMsg($GLOBALS['strMeeting'] . $GLOBALS['strErrNotValid']);
		}
		else
		{
			$result = mysql_query("
				SELECT
					Typ
					, Appellzeit
					, Stellzeit
				FROM 
					disziplin_" . $_COOKIE['language'] . "
				WHERE xDisziplin = " . $_POST['discipline']
			);

			if(mysql_errno() > 0)		// DB error
			{
				AA_printErrorMsg($GLOBALS['strDiscipline'] . $GLOBALS['strErrNotValid']);
			}
			else
			{
				// set default wind info, timing for this descipline
				$row = mysql_fetch_row($result);
				$wind = 0;
				$timing = 0;
				$tauto = 0;
				
				$stdEtime = strtotime($row[1]); // hold standard delay for enrolement time
				$stdMtime = strtotime($row[2]); // and manipulation time
				
				if(($row[0] == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrack']])
					|| ($row[0] == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJump']]))
				{
					if($_POST['wind'] == "yes"){
						$wind = 1;
					}else{
						$wind = 0;
					}
				}
				mysql_free_result($result);
				
				if(($row[0] == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeNone']])
					|| ($row[0] == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrack']])
					|| ($row[0] == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrackNoWind']])
					|| ($row[0] == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeDistance']])
					|| ($row[0] == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeRelay']])){
					
					if($_POST['timing'] == "yes"){
						$timing = 1;
					}else{
						$timing = 0;
					}
					if($_POST['timingAuto'] == "yes"){
						$tauto = 1;
					}else{
						$tauto = 0;
					}
				}
				
				// fetch parameters Typ, Mehrkampfcode, xKategorie_svm
				$res = mysql_query("
						SELECT 
							Typ
							, Mehrkampfcode
							, xKategorie_svm
						FROM
							wettkampf
						WHERE
							xKategorie = ".$_POST['cat']."
						AND	xMeeting = ".$_COOKIE['meeting_id']);
				if(mysql_num_rows($res) > 0){
					$rowT = mysql_fetch_array($res);
					
					if($type==0){
						if($rowT[0] != $GLOBALS['cfgEventType'][$GLOBALS['strEventTypeSingleCombined']]){
							$type = $rowT[0];
						}
					}
					//$mkcode = $rowT[1];
					$svmcat = $rowT[2];
				}else{
					$mkcode = 0;
					$svmcat = 0;
				}
				
				//OK, add event
				mysql_query("
					INSERT INTO wettkampf SET
						Typ='$type'
						, Mehrkampfcode='$mkcode'
						, xKategorie_svm='$svmcat'
						, Windmessung=$wind
						, Zeitmessung=$timing
						, ZeitmessungAuto=$tauto
						, Haftgeld='$deposit'
						, Startgeld='$fee'
						, Punktetabelle='$conv'
						, Punkteformel='$formula'
						, Info=\"$info\"
						, xKategorie = " . $_POST['cat'] . "
						, xDisziplin = " . $_POST['discipline'] . "
						, xMeeting = " . $_COOKIE['meeting_id']
				);
			}
		}
	}
	// Check if any error returned from DB
	$event = 0;						
	if(mysql_errno() > 0)
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		$event = mysql_insert_id();
	}
	mysql_query("UNLOCK TABLES");

	// add rounds if any
	if($event > 0)			// new event added
	{
		if(!empty($_POST['time_1'])) 	// 1st round set
		{
			list($hr, $min) = AA_formatEnteredTime($_POST['time_1']);
			
			if(empty($_POST['etime_1'])){ // if enrolement time is empty, calculate with discipline standard (1h bevor)
				$tmp = strtotime($hr.":".$min.":00");
				$tmp = $tmp - $stdEtime;
				$_POST['etime_1'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
			}
			if(empty($_POST['mtime_1'])){ // if manipulation time is empty, calculate with discipline standard (15min bevor)
				$tmp = strtotime($hr.":".$min.":00");
				$tmp = $tmp - $stdMtime;
				$_POST['mtime_1'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
			}
			
			$tt = new TimetableNew($_POST['date_1'], $event, 0,
				$_POST['roundtype_1'], $hr, $min, $_POST['etime_1'], $_POST['mtime_1']);
			$tt->add();
		}

		if(!empty($_POST['time_2'])) 	// 2nd round set
		{
			list($hr, $min) = AA_formatEnteredTime($_POST['time_2']);
			
			if(empty($_POST['etime_2'])){ // if enrolement time is empty, calculate with discipline standard (1h bevor)
				$tmp = strtotime($hr.":".$min.":00");
				$tmp = $tmp - $stdEtime;
				$_POST['etime_2'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
			}
			if(empty($_POST['mtime_2'])){ // if manipulation time is empty, calculate with discipline standard (15min bevor)
				$tmp = strtotime($hr.":".$min.":00");
				$tmp = $tmp - $stdMtime;
				$_POST['mtime_2'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
			}
			
			$tt = new TimetableNew($_POST['date_2'], $event, 0,
				$_POST['roundtype_2'], $hr, $min, $_POST['etime_2'], $_POST['mtime_2']);
			$tt->add();
		}

		if(!empty($_POST['time_3'])) 	// 3rd round set
		{
			list($hr, $min) = AA_formatEnteredTime($_POST['time_3']);
			
			if(empty($_POST['etime_3'])){ // if enrolement time is empty, calculate with discipline standard (1h bevor)
				$tmp = strtotime($hr.":".$min.":00");
				$tmp = $tmp - $stdEtime;
				$_POST['etime_3'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
			}
			if(empty($_POST['mtime_3'])){ // if manipulation time is empty, calculate with discipline standard (15min bevor)
				$tmp = strtotime($hr.":".$min.":00");
				$tmp = $tmp - $stdMtime;
				$_POST['mtime_3'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
			}
			
			$tt = new TimetableNew($_POST['date_3'], $event, 0,
				$_POST['roundtype_3'], $hr, $min, $_POST['etime_3'], $_POST['mtime_3']);
			$tt->add();
		}

		// Check if any error returned
		if(!empty($GLOBALS['AA_ERROR'])) {
				AA_printErrorMsg($GLOBALS['AA_ERROR']);
		}
	}

	return;
}

//
// add new combined event
//
function AA_meeting_addCombinedEvent($disfee, $penalty){
		
	include('./convtables.inc.php');
	require('./lib/common.lib.php');
	
	if(!empty($_POST['combinedtype'])){
		$t = $_POST['combinedtype'];
		
		// get short name
		$res = mysql_query("SELECT Kurzname FROM disziplin_" . $_COOKIE['language'] . " WHERE Code = $t");
		$row = mysql_fetch_array($res);
		$sName = $row[0];
		
		// check if combined type has predefined disciplines
		$sql_k = "SELECT Geschlecht, Code , Kurzname FROM kategorie WHERE xKategorie = ".$_POST['cat'].";";
		$query_k = mysql_query($sql_k);
		$row_k = mysql_fetch_assoc($query_k);
			
		$tmp = $t;			
		if($tmp==394 && ($row_k['Geschlecht']=='m' || $row_k['Geschlecht']=='M')){
			$tmp = 3942;
		}
		$tt = ''; 	
		if(isset($cfgCombinedDef[$tmp])){
			
            $tt = $cfgCombinedDef[$tmp];    
			
			$k = 0;
            if (isset($cfgCombinedWO[$tt])){
              
			foreach($cfgCombinedWO[$tt] as $val){
				
				$k++;
				$res = mysql_query("SELECT xDisziplin FROM disziplin_" . $_COOKIE['language'] . " WHERE Code = $val");
				$row = mysql_fetch_array($res);
				$d = $row[0];
				$combEnd = 0;
				
				if($k == count($cfgCombinedWO[$tt]) && $tmp != 403){
					$combEnd = 1;
				}
                if ($val == 100 && $tmp == 403) {         // code 100 = 1000m (a single event inside the UBS Kids Cup)
                         mysql_query("INSERT INTO wettkampf SET
                        Typ = ".$cfgEventType[$strEventTypeSingle]."
                        , Haftgeld = '$penalty'
                        , Startgeld = '$disfee' 
                        , Info = ''
                        , xKategorie = ".$_POST['cat']."
                        , xDisziplin = $d
                        , xMeeting = ".$_COOKIE['meeting_id']);
                }
                else {
                    
                
				mysql_query("INSERT INTO wettkampf SET
						Typ = ".$cfgEventType[$strEventTypeSingleCombined]."
						, Haftgeld = '$penalty'
						, Startgeld = '$disfee'
						, Info = '$sName'
						, xKategorie = ".$_POST['cat']."
						, xDisziplin = $d
						, xMeeting = ".$_COOKIE['meeting_id']."
						, Mehrkampfcode = $t
						, Mehrkampfende = $combEnd
						, Mehrkampfreihenfolge = $k");
				
	            }
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					break;
				}
				
			}
			
             if ($val != 100) { 
			    // set conversion table               
			    $_POST['type'] = $cfgEventType[$strEventTypeSingleCombined];
			    $_POST['conv'] = $cfgCombinedWO[$tt."_F"];             
			    $_POST['conv_changed'] = 'yes';
			    AA_meeting_changeCategory($t);
             }
			}
            else {
                // setup a placeholder disciplin (take first disciplin of combined definition 'MAN')
                mysql_query("INSERT INTO wettkampf SET
                    Typ = ".$cfgEventType[$strEventTypeSingleCombined]."
                    , Info = '$sName'
                    , xKategorie = ".$_POST['cat']."
                    , xDisziplin = ".$cfgCombinedWO['MAN'][0]."
                    , xMeeting = ".$_COOKIE['meeting_id']."
                    , Mehrkampfcode = $t");
                if(mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }  
            }
		}else{
			// setup a placeholder disciplin (take first disciplin of combined definition 'MAN')
			mysql_query("INSERT INTO wettkampf SET
					Typ = ".$cfgEventType[$strEventTypeSingleCombined]."
					, Info = '$sName'
					, xKategorie = ".$_POST['cat']."
					, xDisziplin = ".$cfgCombinedWO['MAN'][0]."
					, xMeeting = ".$_COOKIE['meeting_id']."
					, Mehrkampfcode = $t");
			if(mysql_errno() > 0) {
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
		} 
		 
	}
	
}


//
// change data per category
//
function AA_meeting_changeCategory($byCombtype = 0)
{
	include('./convtables.inc.php');
	require('./lib/common.lib.php');

	$setConv = ", Punktetabelle=";
	if(!empty($_POST['conv'])) {
		$setConv = $setConv . $_POST['conv'];
	}
	else {
		$setConv = $setConv . 0;
	}

	$type = 0;
	if(!empty($_POST['type'])) {
		$type = $_POST['type'];
	}
	
	$sqlCombtype = "";
	if($byCombtype > 0){
		$sqlCombtype = "AND w.Mehrkampfcode = $byCombtype";
	}
	
	mysql_query("
		LOCK TABLES
			disziplin_" . $_COOKIE['language'] . " READ
			, team READ
			, serienstart READ
			, start READ
			, resultat WRITE
			, wettkampf WRITE
            , wettkampf AS w WRITE
			, runde READ
			, r READ
			, w READ
	");

	$count = 0;
	// new event type is not a team-type

	/*if($type < $GLOBALS['cfgEventType'][$GLOBALS['strEventTypeClubMA']])
	{
		// check whether any teams defined for this category
		$res = mysql_query("
			SELECT
				xTeam
			FROM
				team
			WHERE xKategorie = " . $_POST['cat'] . "
			AND xMeeting=" . $_COOKIE['meeting_id']
		);

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
			$count = mysql_num_rows($res);
			mysql_free_result($res);
		}
	}*/
	//$count = 0;
	if($count == 0)		// update OK
	{
		// read all events for this category
		$result = mysql_query("
			SELECT
				w.xWettkampf
				, d.Kurzname
				, w.Mehrkampfcode
				, d.Typ
                , d.Code
			FROM
				wettkampf AS w
				LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
			WHERE w.xKategorie = " . $_POST['cat'] . "
			AND w.xMeeting = " . $_COOKIE['meeting_id'] . "  			
			$sqlCombtype
			$sqlSetCombinedOnly
		");     
        
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{   
			while ($row = mysql_fetch_row($result))
			{
				// check if any formula for new conversion table
				$setFormula = "";
				if($_POST['conv_changed'] == 'yes')
				{   
					if($_POST['conv'] == $cvtTable[$strConvtableRankingPoints] || $_POST['conv'] == $cvtTable[$strConvtableRankingPointsU20]){ // check ranking points
						$keysRP = array_keys($cvtFormulas[$_POST['conv']]);
						if($row[3] == $cfgDisciplineType[$strDiscTypeRelay]){ // if relay type
							$setFormula = ", Punkteformel='".$keysRP[1]."'";
							$formula = $keysRP[1];
						}else{ // if not
							$setFormula = ", Punkteformel='".$keysRP[0]."'";
							$formula = $keysRP[0];
						}
					}elseif(isset($cvtFormulas[$_POST['conv']][$row[1]])) {                         
						$setFormula = ", Punkteformel='$row[1]'";
						$formula = $row[1];                          
					}elseif(isset($cvtFormulas[$_POST['conv']][substr($row[1],0,2)."H"])){
						$setFormula = ", Punkteformel='".substr($row[1],0,2)."H"."'";
						$formula = substr($row[1],0,2)."H";
					}elseif(isset($cvtFormulas[$_POST['conv']][substr($row[1],0,3)."H"])){
						$setFormula = ", Punkteformel='".substr($row[1],0,3)."H"."'";
						$formula = substr($row[1],0,3)."H";
					}elseif(isset($cvtFormulas[$_POST['conv']][substr($row[1],0,4)])){
						$setFormula = ", Punkteformel='".substr($row[1],0,4)."'";
						$formula = substr($row[1],0,4);
					}elseif(isset($cvtFormulas[$_POST['conv']][substr($row[1],0,5)])){
						$setFormula = ", Punkteformel='".substr($row[1],0,5)."'";
						$formula = substr($row[1],0,5);
					}elseif(isset($cvtFormulas[$_POST['conv']][substr($row[1],0,6)])){
						$setFormula = ", Punkteformel='".substr($row[1],0,6)."'";
						$formula = substr($row[1],0,6);
					}
					else {
                        if ($row[4] >= 497 & $row[4] <= 499) {
                           $setFormula = ", Punkteformel='4X100'";
                           $formula = '4X100';
                        }
                        else {
						    $setFormula = ", Punkteformel='0'";
						    $formula = '0';
                        }
					}
				}
				
				// if the user wants to change into a combined event
				// set the disciplines without a Mehrkampfcode to type single (0)
				$setType = $type;
				if($type == $cfgEventType[$strEventTypeSingleCombined] && $row[2] == 0){
					$setType = $cfgEventType[$strEventTypeSingle];
				}
				
				mysql_query("
					UPDATE wettkampf SET
						Typ='$setType'
						$setConv
						$setFormula
						$setWeight
					WHERE xWettkampf = $row[0]
				");

				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else if($_POST['conv_changed'] == 'yes')	// conv. table changed
				{    
					AA_meeting_resetResults($row[0], $formula, $_POST['conv']);
				}
			}	// end while every event for this category

			mysql_free_result($result);
		}	// ET DB error
	}	// ET OK to update 
	else
	{
		AA_printErrorMsg($GLOBALS['strErrTypeChange']);
	}

	mysql_query("UNLOCK TABLES");
	return;
}


function AA_meeting_formatFormula($formula){
	 if (strpos($formula, '-') ){
        $formula2 = explode('-', str_replace(' ', '', $formula));
        $minus=true;
    }
    else {
         $formula2 = explode('+', str_replace(' ', '', $formula)); 
         $minus=false; 
    }

    $res = '16 -1';

    if(count($formula2)==2){         
            if ($minus){
                if(is_numeric($formula2[0]) && is_numeric($formula2[1]) && $formula2[0]>0 && $formula2[1]>0 && $formula2[0]>$formula2[1]){ 
                    $res = intval($formula2[0]).' -'.intval($formula2[1]);
                }
            }
            else {
                if(is_numeric($formula2[0]) && is_numeric($formula2[1]) && $formula2[0]>0 && $formula2[1]>0 ){     
                    $res = intval($formula2[0]).' +'.intval($formula2[1]); 
                }
            }
    }

	return $res;
	
}

function AA_meeting_changeFormula()
{
	include('./convtables.inc.php');
	require('./lib/common.lib.php');
	
	
	
	$formula = AA_meeting_formatFormula($_POST['formula']);
				
	mysql_query("
		UPDATE wettkampf SET
			Punkteformel = '".$formula."' 
		WHERE xWettkampf = ".$_POST['item'].";
	");

	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		mysql_free_result($result);
	}	// ET OK to update 
	
	return;
}


//
// change event data
//
function AA_meeting_changeEvent()
{
	include('./convtables.inc.php');
	require('./lib/common.lib.php');

	$info = '';
	if(!empty($_POST['info'])) {
		$info = $_POST['info'];
	}
	$wind = 0;
	if(!empty($_POST['wind'])) {
		$wind = 1;
	}
	$deposit = 0;
	if(!empty($_POST['deposit'])) {
		$deposit = strtr($_POST['deposit'], ",", ".");
	}
	$fee = 0;
	if(!empty($_POST['fee'])) {
		$fee = strtr($_POST['fee'], ",", ".");
	}
	$formula = 0;
	if(!empty($_POST['formula'])) {
		$formula = $_POST['formula'];
	}
	$timing = 0;
	if(!empty($_POST['timing'])) {
		$timing = 1;
	}
	$tauto = 0;
	if(!empty($_POST['timingAuto'])) {
		$tauto = 1;
	}

	mysql_query("
		LOCK TABLES
			disziplin_" . $_COOKIE['language'] . " READ
			, serienstart READ
			, start READ
			, resultat wRITE
			, wettkampf WRITE
			, runde READ
			, r READ
			, w READ
	");

	mysql_query("
		UPDATE wettkampf SET 
			Windmessung = $wind
			, Haftgeld='$deposit'
			, Startgeld='$fee'
			, Punkteformel='$formula'
			, Info=\"$info\"
			, Zeitmessung=$timing
			, ZeitmessungAuto=$tauto
		WHERE xWettkampf = " . $_POST['item']
	);

	// Check if any error returned from DB
	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else {
		AA_meeting_resetResults($_POST['item'], $formula);
	}
	mysql_query("UNLOCK TABLES");
	return;
}

// change event data
//
function AA_meeting_changeEventDiscipline()
{   
	include('./convtables.inc.php');
	require('./lib/common.lib.php');

	$info = '';
	if(!empty($_POST['info'])) {
		$info = $_POST['info'];
	}
	$wind = 0;
	if(!empty($_POST['wind'])) {
		$wind = 1;
	}
	$deposit = 0;
	if(!empty($_POST['deposit'])) {
		$deposit = strtr($_POST['deposit'], ",", ".");
	}
	$fee = 0;
	if(!empty($_POST['fee'])) {
		$fee = strtr($_POST['fee'], ",", ".");
	}
	$timing = 0;
	if(!empty($_POST['timing'])) {
		$timing = 1;
	}
	$tauto = 0;
	if(!empty($_POST['timingAuto'])) {
		$tauto = 1;
	}
	$cat=0;
	if(!empty($_POST['cat'])) {   
		$cat=$_POST['cat'];         
	}
	$discipline=0;
	$code=0;
	if(!empty($_POST['discipline_cmb'])) {
		$code=$_POST['discipline_cmb'];
	 }
	if(!empty($_POST['cmbtype'])) {
		$combined=$_POST['cmbtype'];           
										//   check if athletes announced
		$sql_a="SELECT
					w.xWettkampf
					, s.xAnmeldung 
				FROM 
					wettkampf AS w 
				INNER JOIN 
					start AS s USING (xWettkampf)
				WHERE 
					w.mehrkampfcode=".$combined."
					AND w.xMeeting=".$_COOKIE['meeting_id']."
					AND w.xKategorie=".$cat.";";              

		$result_a = mysql_query($sql_a);         
		 
		if (mysql_num_rows($result_a)!= 0) {
												//   athletes already announced
			?>
			<script type="text/javascript">
				alert("<?php echo $strErrDiscCombChange; ?>");                 
			</script>
			<?php
			return;
		}   

		$sql="SELECT
					xDisziplin
			  FROM 
					disziplin_" . $_COOKIE['language'] . "
			  WHERE
					code = ".$code.";";

		$result = mysql_query($sql);

		if(mysql_errno() > 0) {        // DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
				$row = mysql_fetch_row($result);
				$discipline=$row[0];
		}
		 mysql_free_result($result);
	}

	mysql_query("
		LOCK TABLES
			disziplin_" . $_COOKIE['language'] . " READ
			, serienstart READ
			, start READ
			, resultat wRITE
			, wettkampf WRITE
			, runde READ
			, r READ
			, w READ
	");

	mysql_query("
		UPDATE wettkampf SET
			Windmessung = $wind
			, Haftgeld='$deposit'
			, Startgeld='$fee'
			, xDisziplin='$discipline'
			, Info=\"$info\"
			, Zeitmessung=$timing
			, ZeitmessungAuto=$tauto
		WHERE xWettkampf = " . $_POST['item']
	);

	// Check if any error returned from DB
	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	
	mysql_query("UNLOCK TABLES");
	return;
}

//
// delete a single event
//
function AA_meeting_deleteEvent()
{
	require('./lib/common.lib.php');

	mysql_query("LOCK TABLES runde READ, start READ, wettkampf WRITE");

	// Still in use?
	$rows = AA_checkReference("runde", "xWettkampf", $_POST['item']);
	$rows = $rows + AA_checkReference("start", "xWettkampf", $_POST['item']);

	// OK: not used anymore
	if($rows == 0) {
		mysql_query("DELETE FROM wettkampf WHERE xWettkampf=" . $_POST['item']);
	}
	// Error: still in use
	else {
		AA_printErrorMsg($GLOBALS['strEvent'] . $GLOBALS['strErrStillUsed']);
	}
	mysql_query("UNLOCK TABLES");
	return;
}


//
// Drop down list date
//
function AA_meeting_printDate($name, $date, $submit=FALSE)
{
	require('./lib/common.lib.php');
	require('./lib/cl_gui_dropdown.lib.php');

	if(empty($date))		// set current date if none provided
	{
		$date = date("Y-m-d");
	}
	$day=substr($date, 8, 2);
	$month=substr($date, 5, 2);
	$year=substr($date, 0, 4);

	$sub = '';
	if($submit == TRUE) {
		$sub = "document.change_def.submit()";
	}

	$dd = new GUI_DateFieldDropDown($name, $day, false, $sub);
	$dd = new GUI_DateFieldDropDown($name, $month, true, $sub);
?>
	<td class='forms'>
	<input class='nbr' name='<?php echo $name; ?>_year' type='text'
		maxlength='4' style='width:9mm' value='<?php echo $year; ?>'
		onChange='<?php echo $sub; ?>' />
	</td>
<?php
}


//
// Reset result points
//
function AA_meeting_resetResults($event, $formula, $conv = '')
{
	global $strConvtableRankingPoints;
    global $strConvtableRankingPointsU20; 
	
	require('./lib/common.lib.php');
	require('./lib/utils.lib.php');      
	
    $sql = "
        SELECT
            re.xResultat
            , re.Leistung
            , re.Info
            , at.Geschlecht
        FROM
            resultat AS re
            LEFT JOIN serienstart AS ss ON (ss.xSerienstart = re.xSerienstart)
            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
            LEFT JOIN anmeldung AS a ON (st.xAnmeldung = a.xAnmeldung)
            LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
        WHERE        
            st.xWettkampf = $event 
            AND re.Info != 'XXX'";  
     
    $result = mysql_query($sql);       
		
	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		if($formula == '0')	// formula deleted
		{
			// reset all result points to zero
			while ($row = mysql_fetch_row($result))
			{
				mysql_query("
					UPDATE resultat SET
						Punkte=0
					WHERE xResultat = $row[0]
				");

				// Check if any error returned from DB
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
			}	// end while every result for this category
		}
		else	// new formula set
		{
			// if ranking points are set -> calc special
            
            if (empty($conv)){
                $rp = $GLOBALS['cvtFormulas'][$GLOBALS['cvtTable'][$strConvtableRankingPoints]]; // formulas for ranking points  
            }
            else {
                    if ($conv == $cvtTable[$strConvtableRankingPointsU20]) {
                         $rp = $GLOBALS['cvtFormulas'][$GLOBALS['cvtTable'][$strConvtableRankingPointsU20]]; // formulas for ranking points 
                    }
                    else {
                        $rp = $GLOBALS['cvtFormulas'][$GLOBALS['cvtTable'][$strConvtableRankingPoints]]; // formulas for ranking points 
                    }
            }
			
			if(array_key_exists($formula, $rp)){
				
				mysql_free_result($result);
				$result = mysql_query("SELECT xRunde FROM
								runde
							WHERE
								xWettkampf = $event");
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}else{
					// update each round
					while($row = mysql_fetch_array($result)){
						AA_utils_calcRankingPoints($row[0]);
					}
				}
				
			}else{ // normal point formula
				
				// recalculate result points
				while ($row = mysql_fetch_row($result))
				{
					$points = AA_utils_calcPoints($event, $row[1], 0, $row[3]);
					mysql_query("
						UPDATE resultat SET
							Punkte = $points
						WHERE xResultat = $row[0]
					");
	
					// Check if any error returned from DB
					if(mysql_errno() > 0) {
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}
				} // end while every result for this category
			} // end if ranking points
		} // ET formula change

		mysql_free_result($result);
	}	// ET DB error
	
}	// end function resetResults

//
// get all clubs with same LG 
//

function AA_meeting_getLG_Club($club){     
    $arrClub = array();
       
    $sql="SELECT 
                ba.lg,
                ba.account_name                
          FROM
                verein AS v
                LEFT JOIN base_account AS ba ON (v.xCode = ba.account_code)
          WHERE 
                v.xVerein = " .$club ."
                AND v.xCode != ''";
    
    $result=mysql_query($sql);  
    
    if(mysql_errno() > 0){
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
         if (mysql_num_rows($result) == 1 ){                
                $row = mysql_fetch_array($result);
                if ($row[0] != '') {
                      $sql="SELECT 
                            ba.account_code,
                            v.xVerein
                      FROM
                            base_account AS ba 
                            LEFT JOIN verein AS v ON (ba.account_code = v.xCode)
                      WHERE 
                            ba.lg = '" .$row[0] ."'";    
                      
                      $result=mysql_query($sql); 
                      
                      if(mysql_errno() > 0){
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                      }else{
                            $i=0;
                            while ($row = mysql_fetch_array($result)) {                               
                               $arrClub[$i]=$row[1]; 
                               $i++;                                
                            } 
                       }        
                }
                else {
                    // if ($row[1] != '') {
                         $sql="SELECT 
                                ba.account_code,
                                v.xVerein
                          FROM
                                base_account AS ba 
                                LEFT JOIN verein AS v ON (ba.account_code = v.xCode)
                          WHERE 
                                ba.lg = '" .$row[1] ."'";    
                          
                          $result=mysql_query($sql); 
                         
                          if(mysql_errno() > 0){
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                          }else{
                                $i=0;
                                while ($row = mysql_fetch_array($result)) {                                
                                   $arrClub[$i]=$row[1];
                                   $i++;  
                                }   
                          // } 
                       }
                }  
         }
    }
   return $arrClub; 
   
}   //end function AA_meeting_getLG_Club  

//
// get LG  from Club
//

function AA_meeting_getLG($club){     
    $lg = "";
    
    $sql="SELECT 
                ba.lg 
          FROM
                verein AS v
                LEFT JOIN base_account AS ba ON (v.xCode = ba.account_code)
          WHERE 
                v.xVerein = " .$club;
   
    $result=mysql_query($sql);  
   
    if(mysql_errno() > 0){
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
         if (mysql_num_rows($result) == 1 ){ 
                $row = mysql_fetch_array($result);
                $lg = $row[0];
         }
    }
   return $lg; 
   
}   //end function AA_meeting_getLG  

//
// add new svm event
//
function AA_meeting_addSVMEvent($disfee, $penalty){
    
    include('./convtables.inc.php');
    require('./lib/common.lib.php');   
                                            
    if(!empty($_POST['svmcategory'])){  
        $svm = $_POST['svmcategory'];
        
        // get short name
        $res = mysql_query("SELECT ks.code FROM kategorie_svm AS ks WHERE ks.xKategorie_svm = $svm");  
        $row = mysql_fetch_array($res);
        $svmCode = $row[0];  
        $_POST['svmCode']=$svmCode;               
          
         if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());                   
            }   
                 
        if(isset($cfgSVM[$svmCode])){
            $arrSVM = $cfgSVM[$svmCode];  
            $k = 0;
           
            foreach($arrSVM as $key => $val){      
                $k++;
                $res = mysql_query("SELECT xDisziplin, Typ FROM disziplin_" . $_COOKIE['language'] . " WHERE Code = $val");
                $row = mysql_fetch_array($res);
                $d = $row[0]; 
                $dTyp = $row[1]; 
                if (is_null($d)){
                   $GLOBALS['AA_ERROR'] = $GLOBALS['strErrNoSuchDisCode']." (code=".$val.")";
                   continue;
                }
                
                $wTyp=$_POST['wTyp'];    
                
                $sql="SELECT xWettkampf FROM wettkampf WHERE  xDisziplin = " . $d ." AND xKategorie_svm = " .$svm . " AND xMeeting = " .$_COOKIE['meeting_id'];
                $res = mysql_query($sql);
                $num = mysql_num_rows($res);
               
                if (mysql_num_rows($res) == 0) {    
                    
                     $sql="INSERT INTO wettkampf SET
                        Typ = ".$wTyp."
                        , Haftgeld = '$penalty'
                        , Startgeld = '$disfee' 
                        , Info = ''                         
                        , xKategorie = ".$_POST['cat']."
                        , xDisziplin = $d
                        , xMeeting = ".$_COOKIE['meeting_id']." 
                        , xKategorie_svm = $svm";                   
                
                  
                    mysql_query($sql);
                    
                    if(mysql_errno() > 0) {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        break;
                    }    
                   
                    $event=mysql_insert_id(); 
                    $_POST['item'] = $event; 
                     if (isset($cfgSVM[$svmCode."_T"][$k-1])) {           // fix timetable
                        $st = $cfgSVM[$svmCode."_T"][$k-1]; 
                        AA_meeting_addTime($st, $wTyp,$event, $dTyp);
                     } 
                     else {
                           if ($d >= 88 && $d <=99){                       // relays
                                  // dummy round for relays
                                  $st = '00:00:00';
                                  AA_meeting_addTime($st, $wTyp,$event, $dTyp);
                           }
                         
                     }
                }  
            }  
                               
            // set conversion table                                           
            $_POST['type'] = $wTyp;
            $_POST['conv'] = $cfgSVM[$svmCode."_F"];             
            $_POST['conv_changed'] = 'yes';
           
            AA_meeting_changeCategory('');    
            
        }else{    
           $GLOBALS['AA_ERROR'] = $GLOBALS['strErrDiscNotDefSVM'];    
        }    
    }            
}     // end function AA_meeting_addSVMEvent

//
// get type of event
//
function AA_meeting_getEventType(){
    
    include('./convtables.inc.php');
    require('./lib/common.lib.php');     
         
    if(!empty($_POST['svmcategory'])){
        $svm = $_POST['svmcategory'];
        
        // get short name
        $res = mysql_query("SELECT ks.code FROM kategorie_svm AS ks WHERE ks.xKategorie_svm = $svm");         
        $row = mysql_fetch_array($res);
        $svmCode = $row[0];              
          
         if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());                   
         }  
          
         $_POST['wTyp'] = $cfgSVM[$svmCode."_ET"];     
            
    }else{   
           $GLOBALS['AA_ERROR'] = $GLOBALS['strErrDiscNotDefSVM'];  
    } 
      
}      // end function AA_meeting_getEventType 
    
//
// add time
//
function AA_meeting_addTime($st , $wTyp, $item, $dTyp)  {
  // date, item, roundtype, hr, min    
  
    include('./convtables.inc.php');    
      
    if ($wTyp > $cfgEventType[$strEventTypeSingleCombined]
                && $row[2] != $cfgEventType[$strEventTypeTeamSM])         // not single event
            {      
                                                                 
              if ($dTyp == $cfgDisciplineType[$strDiscTypeTrack] ||
                        $dTyp == $cfgDisciplineType[$strDiscTypeTrackNoWind] ||  
                        $dTyp == $cfgDisciplineType[$strDiscTypeDistance] ||  
                        $dTyp == $cfgDisciplineType[$strDiscTypeRelay] )  
               {                                                                    // discipline type track
                    $_POST['roundtype'] = 6; // round type "Serie"  
              }   
              else {                                                                // discipline type tech
                  $_POST['roundtype'] = 9; // round type "ohne" 
              }  
    }
   
    
    if(preg_match("/[\.,;:]/",$st) == 0){
        $_POST['hr'] = substr($st,0,-2);
        if(strlen($st) == 3){
            $_POST['min'] = substr($st,1);
        }elseif(strlen($st) == 4){
            $_POST['min'] = substr($st,2);
        }
    }else{
        list($_POST['hr'], $_POST['min']) = preg_split("/[\.,;:]/", $st);
    }
    
    // auto configure enrolement and manipulation time
    $result = mysql_query("
        SELECT
            d.Typ
            , d.Appellzeit
            , d.Stellzeit
        FROM
            wettkampf as w
            LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d USING(xDisziplin)
        WHERE w.xWettkampf = " . $item
    );
    $row = mysql_fetch_row($result);
    $stdEtime = strtotime($row[1]); // hold standard delay for enrolement time
    $stdMtime = strtotime($row[2]); // and manipulation time
    
    $tmp = strtotime($_POST['hr'].":".$_POST['min'].":00");
    $tmp = $tmp - $stdEtime;
    $_POST['etime'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
    
    $tmp = strtotime($_POST['hr'].":".$_POST['min'].":00");
    $tmp = $tmp - $stdMtime;
    $_POST['mtime'] = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);
      
     
    if($_POST['round'] > 0 ){
        $tt = new Timetable();  
        $tt->change();
    }else{
        $tt = new Timetable();
        $tt->add();
    }  
    
}    // end function AA_meeting_addTime


}		// AA_MEETING_LIB_INCLUDED
?>
