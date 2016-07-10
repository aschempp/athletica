<?php

/**********
 *
 *	meeting_relay_add.php
 *	---------------------
 *	
 */           
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');
require('./lib/cl_performance.lib.php');  

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}
   
// initialize variables
$category = 0;
if(!empty($_POST['category'])) {
	$category = $_POST['category'];	// store selected category
}
else if(!empty($_GET['cat'])) {
	$category = $_GET['cat'];	// store selected category
}

$club = 0;
if(!empty($_POST['club'])) {
	$club = $_POST['club'];	// store selected category
}

$event = 0;
if(!empty($_POST['event'])) {
	$event = $_POST['event'];	// store selected event
}


//
// process data
//
$xStaffel=0;
$xStaffelSQL = "";

if ($_POST['arg']=="add")
{
	// Error: Empty fields
	if(empty($_POST['name']) || empty($_POST['club']) || empty($event))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK: try to add item
	else
	{
		// get the eventnumber of this meeting for generating a relay id in the form eventnumber999 (xxxxxx999)
		$res = mysql_query("SELECT xControl FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			return;
		}else{
			$row = mysql_fetch_array($res);
			$eventnr = $row[0];
			if(empty($eventnr)){
				$idcounter = "";
			}else{
				mysql_free_result($res);
				$arrid = array();
				$res = mysql_query("select max(xStaffel) from staffel where xStaffel like '$eventnr%'");
				$row = mysql_fetch_array($res);
				$arrid[] = $row[0];
				$res = mysql_query("select max(xTeam) from team where xTeam like '$eventnr%'");
				$row = mysql_fetch_array($res);
				$arrid[] = $row[0];
				$res = mysql_query("select max(id_relay) from base_relay where id_relay like '$eventnr%'");
				$row = mysql_fetch_array($res);
				$arrid[] = $row[0];
				$res = mysql_query("select max(id_svm) from base_svm where id_svm like '$eventnr%'");
				$row = mysql_fetch_array($res);
				$arrid[] = $row[0];
				
				rsort($arrid);
				$biggestId = $arrid[0];
				
				if($biggestId == 0 || strlen($biggestId) != 9){
					$idcounter = "001";
				}else{
					$idcounter = substr($biggestId,6,3);
					$idcounter++;
					$idcounter = sprintf("%03d", $idcounter);
				}
				
				$xStaffelSQL = ", xStaffel = ".$eventnr.$idcounter.", Athleticagen ='y' ";
			}
		}
		
		mysql_query("	LOCK TABLES
								disziplin_de READ
                                , disziplin_fr READ
                                , disziplin_it READ
                                , disziplin_de AS d READ   
                                , disziplin_fr AS d READ   
                                , disziplin_it AS d READ   
								, kategorie READ
								, meeting READ
								, team READ
								, runde READ 							
								, verein READ
								, wettkampf READ
								, wettkampf AS w READ 
								, staffel WRITE
								, staffel AS st READ  
								, staffelathlet WRITE
								, start WRITE
								, start AS s READ  
								, anmeldung READ
                                , teamsm READ   
							");

		if(AA_checkReference("kategorie", "xKategorie", $category) == 0)	// Category does not exist (anymore)
		{
			AA_printErrorMsg($strCategory . $strErrNotValid);
		}
		else
		{
			if(AA_checkReference("meeting", "xMeeting", $_COOKIE['meeting_id']) == 0)	// Meeting does not exist (anymore)
			{
				AA_printErrorMsg($strMeeting . $strErrNotValid);
			}
			else
			{
				if(AA_checkReference("verein", "xVerein", $_POST['club']) == 0)	// Club does not exist (anymore)
				{
					AA_printErrorMsg($strClub . $strErrNotValid);
				}
				else
				{
					// Team selected
					if((!empty($_POST['team']))
						&& (AA_checkReference("team", "xTeam", $_POST['team']) == 0))
					{
						AA_printErrorMsg($strTeam . $strErrNotValid);
					}
					else
					{

						if(AA_checkReference("wettkampf", "xWettkampf", $_POST['event']) == 0)	// Event does not exist (anymore)
						{
							AA_printErrorMsg($strEvent . $strErrNotValid);
						}
						else
						{
							$startcheck = TRUE;
							$c = 0;
							if($_POST['starts'] > 0)	// any athletes
							{
								foreach($_POST['starts'] as $start)
								{
									// position or athlete not valid
									if(($_POST['positions'][$c] > 0) &&
										(AA_checkReference("start", "xStart", $start) == 0))
									{
										$startcheck = FALSE;
									}
									$c++;
								}
							}	// ET any athletes
							if($startcheck == FALSE)	// At least one start entry does not exist (anymore)
							{
								AA_printErrorMsg($strAthlete . $strErrNotValid);
							}
							else	// add relay data
							{
								
								// check startnumber
								$lastnbr = AA_getLastStartnbrRelay();
								$nbr = $_POST['startnumber'];
								if($nbr > 0){
									$res = mysql_query("SELECT * FROM staffel 
												WHERE Startnummer = $nbr 
												AND xMeeting = ".$_COOKIE['meeting_id']);
									if(mysql_num_rows($res) > 0){
										$nbr = $lastnbr;
										$nbr++;
									}
									mysql_free_result($res);
									//
									// check if startnumber is used for athletes
									$res = mysql_query("SELECT * FROM anmeldung 
												WHERE Startnummer = $nbr 
												AND xMeeting = ".$_COOKIE['meeting_id']);
                                   
									if(mysql_num_rows($res) > 0){ 
										$nbr = AA_getNextStartnbr($nbr);
									}
								}else{
									if($lastnbr > 0){
										$nbr = $lastnbr+1;
										// check if startnumber is used for athletes
										$res = mysql_query("SELECT * FROM anmeldung 
													WHERE Startnummer = $nbr 
													AND xMeeting = ".$_COOKIE['meeting_id']);
										if(mysql_num_rows($res) > 0){
											$nbr = AA_getNextStartnbr($nbr);
										}
									}
								}
								
								if(!empty($_POST['id'])){
									// a relay is added from base data
									
									$xStaffelSQL = ", xStaffel = ".$_POST['id'].", Athleticagen ='n' ";
								}
								// check if relay name exist    
								
								$checkRelayName=AA_checkRelayName($category,$event,$_POST['name']); 									   							
								if (!$checkRelayName) {   // relay name doesn't exist       
									$sql = "INSERT INTO staffel 
											   SET Name = '".$_POST['name']."', 
												   xVerein = ".$_POST['club'].", 
												   xTeam = ".$_POST['team'].", 
												   xMeeting = ".$_COOKIE['meeting_id'].", 
												   xKategorie = ".$category.", 
												   Startnummer = '".$nbr."'".$xStaffelSQL.";";
                                   
									mysql_query($sql);
									if(mysql_errno() == 0) 		// no error
										{
										$xStaffel = mysql_insert_id();	// get new ID
										// check if event already started   
                                         $sql = "SELECT
                                                d.Name
                                            FROM
                                                disziplin_" . $_COOKIE['language'] . " AS d
                                                LEFT JOIN wettkampf AS w ON (d.xDisziplin = w.xDisziplin)
                                                LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf)    
                                            WHERE 
                                                r.Status > 0
                                                AND w.xWettkampf = ". $_POST['event'];    
                                       
                                        $res = mysql_query($sql);
                                       
										if(mysql_errno() > 0) {
											AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
										}
										// OK to add
										else {
											if (mysql_num_rows($res) > 0) {
												$row = mysql_fetch_row($res);
												AA_printErrorMsg($strWarningEventInProgress . $row[0]);
											}

											$perf = 0;
											if(!empty($_POST['topperf']))
												{
												$pt = new PerformanceTime($_POST['topperf']);
												$perf = $pt->getPerformance();
											}
											if($perf == NULL) {	// invalid performance
												$perf = 0;
											}

											mysql_query("
												INSERT INTO start SET 
													xWettkampf = " . $_POST['event'] . "
													, xStaffel = $xStaffel
													, Bestleistung = $perf
												");

											if(mysql_errno() > 0) {
												AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
											}
											// Add athletes
											else
												{
												// get each round for this event
												$xRounds = array();
												$res_rnd = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = ".$_POST['event']);
												if(mysql_errno() > 0) {
													AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
												}else{
													while($row_rnd = mysql_fetch_Array($res_rnd)){
														$xRounds[] = $row_rnd[0];
													}
												}
												if(count($xRounds) == 0){
													AA_printErrorMsg($strRelayDefineRounds);
												}
											
												$xStart = mysql_insert_id();	// get new ID
												$c = 0;
												if($_POST['starts'] > 0)	// any athletes
													{
													foreach($_POST['starts'] as $start)
														{
														foreach($xRounds as $xRound){
															if($_POST['positions'][$c] > 0)
																{
																mysql_query("
																	INSERT INTO staffelathlet
																	SET
																		xStaffelstart = $xStart
																		, xAthletenstart = $start
																		, Position='". $_POST['positions'][$c] . "'
																		, xRunde = '$xRound'
																	");
															}
														}
														$c++;		// next athlete
													}
												}		// ET any athletes
											}		// ET DB error add start
										}		// ET event already started	
									}		 // Duplicate entry
									else if(mysql_errno() == $cfgDBerrorDuplicate) {
										AA_printErrorMsg($strErrDuplicateRelay);
									}
									else {	// general DB error   									
										AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
									}		// ET DB error add relay
								}
								else
									{  
									AA_printErrorMsg($strErrDuplicateRelay);   										 
								}
								   //	}		// ET Entries valid


							}		// ET Entries valid
						}		// ET Event valid
					}		// ET Team valid
				}		// ET Club valid
			}		// ET Meeting valid
		}		// ET Category valid
		// Check if any error returned from DB
		
		mysql_query("UNLOCK TABLES");
	}
}

//
// display data 
//
$page = new GUI_Page('meeting_relay_add');
$page->startPage();
$page->printPageTitle("$strNewEntry $strRelays");

if ($_POST['arg']=="add")
{
	?>
<script>
	window.open("meeting_relaylist.php?item="
		+ <?php echo $xStaffel; ?> + "#" + <?php echo $xStaffel; ?>,
		"list");
</script>
	<?php
}

if(!empty($_POST['arg']) & $_POST['arg'] == 'sperren') {
       $sql="SELECT 
                        m.Online
                    FROM 
                        meeting AS m                          
                    WHERE                        
                         m.xMeeting = " . $_COOKIE['meeting_id'] ; 
                    
                    
            $res=mysql_query($sql);
            
            if(mysql_errno() > 0){
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }else{
                 if (mysql_num_rows($res) > 0){                      
                 
                    $sql = "UPDATE meeting 
                                    SET Online = 'n'  
                             WHERE                                                            
                                         xMeeting = " . $_COOKIE['meeting_id'] ; 
                                         
                    mysql_query($sql);
                    
                    if(mysql_errno() > 0) {
                           $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                    }  
                 } 
            }
} 
else {       
    if(AA_checkControl() == 0){
	    echo "<p>$strErrNoControl1</p>";
        echo "<form action='./meeting_relay_add.php' method='post'>    
            <p><input name='' value='' checked='checked' onclick='submit()' type='checkbox'>
            <input name='arg' value='sperren' type='hidden'> 
                $strMeetingWithUpload  &nbsp; ($strErrNoControl2)</p>
            </form>"; 
	    return;
    }
}

?>
<table>
<tr>
	<td class='forms'>
		<?php AA_printClubSelection("meeting_relay_add.php", $club, $category, 0, true); ?>
	</td>
	<td class='forms'>
		<?php AA_printCategoryEntries("meeting_relay_add.php", $category, $club); ?>
	</td>
	<td class='forms'>
		<?php AA_printRelaySelection("meeting_relay_add.php", $category, $event, $club); ?>
	</td>
</tr>
</table>

<?php

if(($category != 0) && ($event != 0) && ($club != 0))		// category and event selected
{            
	?>
<?php
		 //
			// check timetable 
			// 
			$query="SELECT 
						ru.Startzeit 
					FROM 
						wettkampf AS w
						INNER JOIN runde AS ru ON (ru.xWettkampf = w.xWettkampf)
					WHERE 
						w.xWettkampf = $event 
						AND w.xMeeting = " . $_COOKIE['meeting_id'] . " 
					ORDER BY ru.Startzeit";
					
			$res_ru=mysql_query($query);
			
			if(mysql_errno() > 0){
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}else{
				 $c=mysql_num_rows($res_ru);
				 if ( (strcmp($row_ru[0],'00:00:00'))==0  || $c==0) {   
						AA_printErrorMsg($strEnrolmErr);                           // timetable not fill out
				 }
				 else {
					
					  // timetable ok
			
			?>	
			
			 <br>
			 <table class="dialog">
				<tr>

	<th class='dialog' colspan="6"><?php echo $strRelayFromBase ?></th>
</tr>
	<?php
	
	//
	// get relay from base data for current selection
	//
	$res = mysql_query("SELECT xKategorie, xDisziplin FROM wettkampf WHERE xWettkampf = $event");
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}else{
		$row = mysql_fetch_array($res);
		
		$res = mysql_query("
			SELECT * FROM
				base_relay as b
				LEFT JOIN kategorie as k ON k.Code = b.category
				LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d ON d.Code = b.discipline
			WHERE
				b.account_code = $club
			AND	k.xKategorie = $row[0]
			AND	d.xDisziplin = $row[1]");
		
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			
			while($row = mysql_fetch_assoc($res)){ 
				?>
<tr>
	<form action='meeting_relay_add.php' method='post' name='entry'>
	<td class='dialog'>
		<input name='arg' type='hidden' value='add' />
		<input name='category' type='hidden' value='<?php echo $category; ?>' />
		<input name='event' type='hidden' value='<?php echo $event; ?>' />
		<input name='club' type='hidden' value='<?php echo $club; ?>' />
		<input name='id' type='hidden' value='<?php echo $row['id_relay']; ?>' />
		<?php echo $strName ?>: <input class='text' name='name' type='text'
			maxlength='40' value='<?php echo $row['relay_name'] ?>' />
	</td>
	<td class='dialog'><?php echo $strStartnumber ?></td>
	<td class='forms'><input class='nbr' type='text' maxlength='6' name="startnumber" value=""></td>
	<td class='dialog'><?php echo $strTeam ?></td>
	<?php $dd = new GUI_TeamDropDown($category, $club); ?>
	<td class='dialog'><input type="submit" value="<?php echo $strEnter ?>"></td>
	</form>
</tr>
				<?php
			}
			
			if(mysql_num_rows($res) == 0) {
				?>
<tr>
	<td class='dialog' colspan="6"><?php echo $strRelayBaseNotFound ?></td>
</tr>
				<?php
			}
		}
	}
	?>
</table>
<br>
<form action='meeting_relay_add.php' method='post' name='entry'>
<table class='dialog' width="50%">
<tr>
	<th class='dialog'><?php echo $strStartnumberLong; ?></th>
	<td class='forms'>
		<?php
		$lastnbr = AA_getLastStartnbrRelay();
		$nbr = 0;
		if($lastnbr > 0){
			$nbr = $lastnbr+1;
		}
		?>
		<input class='nbr' type='text' maxlength='6' name="startnumber" value="<?php echo $nbr ?>">
		<?php echo $strNextNr.": ".($lastnbr+1); ?>
	</td>
</tr>
<tr>
	<th class='dialog'><?php echo $strRelayname; ?></th>
	<td class='forms'>
		<input name='arg' type='hidden' value='add' />
		<input name='category' type='hidden' value='<?php echo $category; ?>' />
		<input name='event' type='hidden' value='<?php echo $event; ?>' />
		<input name='club' type='hidden' value='<?php echo $club; ?>' />
		<input class='text' name='name' type='text'
			maxlength='40' value='<?php echo $_POST['name']; ?>' />
	</td>
</tr>
<tr>
	<td colspan="2" class='dialog'><?php echo $strTeamNameRemark; ?><br/><br/><?php echo $strRelayNrInfo; ?></td>
</tr>
<tr>
	<th class='dialog'><?php echo $strTeam; ?></th>
	<?php
	$dd = new GUI_TeamDropDown($category, $club);
	?>
</tr>

<tr>
	<th class='dialog'><?php echo $strTopPerformance; ?></th>
	<td class='forms'><input class='perftime' name='topperf' type='text'
		maxlength='12' value='' /></td>
</tr>
</table>
<p/>
<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strName; ?></th>
	<th class='dialog'><?php echo $strFirstname; ?></th>
	<th class='dialog'><?php echo $strYear; ?></th>
	<th class='dialog'><?php echo $strPosition; ?></th>
</tr>

	<?php
	// list athletes who are not yet assigned to any relay     
      $sql = "SELECT
            a.xAnmeldung
            , at.Name
            , at.Vorname
            , at.Jahrgang
            , st.xStart
        FROM
            anmeldung AS a
            LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
            LEFT JOIN start AS st ON (a.xAnmeldung = st.xAnmeldung)
            LEFT JOIN staffelathlet AS sa ON st.xStart = sa.xAthletenstart
        WHERE 
            sa.xAthletenstart IS NULL          
            AND st.xWettkampf = $event
            AND at.xVerein = $club
        ORDER BY
            at.Name
            , at.Vorname";
    
    $result = mysql_query($sql);

	if(mysql_errno() > 0)
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}

	$i=0;
	// display list of athletes
	while ($row = mysql_fetch_row($result))
	{
		?>
<tr>
	<td><?php echo $row[1]; ?></td>
	<td><?php echo $row[2]; ?></td>
	<td class='forms_ctr'><?php echo AA_formatYearOfBirth($row[3]); ?></td>
	<td class='forms_ctr'>
		<input name='starts[]' type='hidden' value='<?php echo $row[4]; ?>'>
		<input class='nbr' name='positions[]' type='text' maxsize='2'>
	</td>
</tr>
		<?php
	}
	mysql_free_result($result);
?>
</table>

<p />

<table>
	<tr>
		<td class='forms'>
			<button type='submit'>
				<?php echo $strSave; ?>
			</button>
		</td>
	</tr>
</table>
</form>	

<?php
			}       // end timetable ok
	 }        // 

}			// ET category selected
?>

<script type="text/javascript">
<!--
	if(document.entry) {
		document.entry.name.focus();
	}
//-->
</script>

<?php


$page->endPage();

