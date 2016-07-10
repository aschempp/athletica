<?php

/**********
 *
 *	meeting_relays.php
 *	------------------
 *	
 */

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_select.lib.php');

require('./lib/meeting.lib.php'); 
require('./lib/common.lib.php');
require('./lib/cl_performance.lib.php');


if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$item = $_POST['item'];

//
// add new athlete directly
//
if ($_POST['arg']=="add_new" || $_POST['arg']=="add_new_stnr")
{
	// Error: Empty fields
   
	if ($_POST['arg']=="add_new" && (empty($_POST['item']) || empty($_POST['event'])
		|| empty($_POST['athlete'])) 
        || ($_POST['arg']=="add_new_stnr" && (empty($_POST['item']) || empty($_POST['event'])
        || empty($_POST['startnr'])) ) )
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK
	else
	{
       $msgError = 0;    
       if ($_POST['arg']=="add_new_stnr"){
           $sql = " SELECT xAnmeldung FROM anmeldung WHERE Startnummer = ".$_POST['startnr'];
           $res = mysql_query($sql);
           if(mysql_errno() > 0)    // check DB error
                {  $msgError = 1;   
                   AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
           }
           if (mysql_num_rows($res) > 0){
              $row = mysql_fetch_row($res); 
              $_POST['athlete'] = $row[0];  
           }
           else {
               $msgError = 1; 
               AA_printErrorMsg($strStrNrNotExist);  
           }
       } 
        
        if ($msgError == 0){
		    mysql_query("LOCK TABLES start WRITE");

		    if($_POST['position'] > 0)		// change
		        {
			    mysql_query("
				    INSERT INTO start SET
					    xWettkampf = " . $_POST['event'] . "
					    , xAnmeldung = " . $_POST['athlete']
			    );
            
			    if(mysql_errno() == $cfgDBerrorDuplicate)
			        {
				    $result = mysql_query("
					    SELECT
						    xStart
					    FROM
						    start
					    WHERE xWettkampf = " . $_POST['event'] . "
					        AND xAnmeldung = " . $_POST['athlete']
				    );

				    if(mysql_errno() > 0)	// check DB error
				        {
					    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				    }
				    else	// no DB error
				        {
					    $row = mysql_fetch_row($result);
					    $_POST['athlete'] = $row[0];	// get ID
					    $_POST['arg']= "add_pos";		// continue by adding new position
				    }	// ET team test
				    mysql_free_result($result);
			    }
			    else if(mysql_errno() > 0) {
				    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			    }
			    else {
				    $_POST['athlete'] = mysql_insert_id();	// get new ID
				    $_POST['arg']= "add_pos";		// continue by adding new position
			    }
		    }
		    mysql_query("UNLOCK TABLES");
        }
	}	// ET empty fields
}



//
// change relay name
//
if ($_POST['arg']=="change_name")
{
	// Error: Empty fields
	if(empty($_POST['item']) || empty($_POST['name']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK
	else
	{
		mysql_query("
			LOCK TABLES
				staffel WRITE");

		// relay data
		mysql_query("
			UPDATE staffel SET
				Name=\"" . $_POST['name'] . "\"
			WHERE xStaffel= " . $_POST['item']
		);

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		mysql_query("UNLOCK TABLES");
	}
}

//
// change relay performance
//
else if ($_POST['arg']=="change_perf")
{
	mysql_query("
		LOCK TABLES
			start WRITE
	");

	if(AA_checkReference("start", "xStart", $_POST['start']) == 0)
	{
		AA_printErrorMsg($strEntry . $strErrNotValid);
	}
	else
	{
		$perf = 0;
		if(!empty($_POST['top'])) {
			$pt = new PerformanceTime($_POST['top']);
			$perf = $pt->getPerformance();
		}

		if($perf == NULL) {	// invalid performance
			$perf = 0;
		}

		mysql_query("
			UPDATE start SET
				Bestleistung = $perf
		 WHERE xStart = " . $_POST['start']
		);

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
	}
	mysql_query("UNLOCK TABLES");
}


//
// change relay team
//
else if ($_POST['arg']=="change_team")
{
	mysql_query("
		LOCK TABLES
			staffelathlet AS statREAD
			, team READ
			, start AS s WRITE
			, staffel WRITE");     
	
      $sql= "SELECT
                    stat.xAthletenstart
                FROM
                    staffelathlet AS stat
                    LEFT JOIN start AS s ON (stat.xStaffelstart = s.xStart)
                WHERE
                    s.xStaffel = " . $_POST['item'];     
    
    $res = mysql_query($sql);

	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	//else if(mysql_num_rows($res) > 0) {	// Athletes assigned
	//	AA_printErrorMsg($strErrTeamChange);
	//}
	else if((!empty($_POST['team'])) && (AA_checkReference("team", "xTeam", $_POST['team']) == 0))
	{
		AA_printErrorMsg($strTeam . $strErrNotValid);
	}
	else
	{
		// relay data
		mysql_query("
			UPDATE staffel SET
				xTeam = " . $_POST['team'] . "
			 WHERE xStaffel = " . $_POST['item']
		);

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
	}
	mysql_query("UNLOCK TABLES");
}


//
// change athlete's position
//
else if ($_POST['arg']=="change_pos")
{
	// Error: Empty fields
	if(empty($_POST['item']) || empty($_POST['relay'])
		|| empty($_POST['athlete']) || empty($_POST['round']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK
	else
	{
		mysql_query("LOCK TABLES staffelathlet WRITE");
		
		// if an additional round has been created, check first if there is already an entry in staffalathlet
		$resE = mysql_query("SELECT * FROM staffelathlet
					WHERE	xStaffelstart=" . $_POST['relay']."
					AND	xAthletenstart=" . $_POST['athlete']."
					AND	xRunde = ".$_POST['round']);
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			
			if(mysql_num_rows($resE) == 0){ // create staffelathlet
				mysql_query("INSERT INTO staffelathlet SET "
					 . "Position='" . ($_POST['position'])
					 . "' ,xStaffelstart=" . $_POST['relay']
					 . " , xAthletenstart=" . $_POST['athlete']
					 . " , xRunde = ".$_POST['round']);
			}else{	// change position
				mysql_query("UPDATE staffelathlet SET "
					 . "Position='" . ($_POST['position'])
					 . "' WHERE xStaffelstart=" . $_POST['relay']
					 . " AND xAthletenstart=" . $_POST['athlete']
					 . " AND xRunde = ".$_POST['round']);
			}
		
		}
		
		// do not simply delete entry if the entered position is 0
		// --> set the position to 0
		// --> only delete staffelathlet if the position for each round is 0 now
		$res = mysql_query("	SELECT SUM(Position) FROM
						staffelathlet
					WHERE xStaffelstart=" . $_POST['relay']."
					AND xAthletenstart=" . $_POST['athlete']);
		
		$row = mysql_fetch_array($res);
		if($row[0] == 0){
			mysql_query("DELETE FROM staffelathlet"
						 . " WHERE xStaffelstart='" . $_POST['relay']
						 . "' AND xAthletenstart='" . $_POST['athlete'] . "'");
		}
		
		if(mysql_affected_rows() == 0) {
			AA_printErrorMsg($strAthlete . $strErrNotValid);
		}
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		mysql_query("UNLOCK TABLES");
	}	// ET empty fields
}

//
// add athlete
//
else if ($_POST['arg']=="add_pos")
{
	// Error: Empty fields
	if(empty($_POST['item']) || empty($_POST['relay'])
		|| empty($_POST['athlete']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK
	else
	{
		mysql_query("
			LOCK TABLES
				anmeldung READ
				, start READ
				, runde READ
                , runde AS r READ 
				, rundentyp_de READ
                , rundentyp_fr READ
                , rundentyp_it READ
                , rundentyp_de AS rt READ
                , rundentyp_fr AS rt READ
                , rundentyp_it AS rt READ
				, staffelathlet WRITE
		");
		
		//
		// add position for each round
		//
		$result = mysql_query("
				SELECT 
                    r.xRunde, rt.Typ 
                FROM
					runde AS r
					LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt  ON (r.xRundentyp = rt.xRundentyp)
				WHERE
					r.xWettkampf = ".$_POST['event']);   
				
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			while($rowRound = mysql_fetch_array($result)){
				if($_POST['position'] > 0)		// change
				{
					mysql_query("
						INSERT INTO staffelathlet SET
							xStaffelstart=" . $_POST['relay'] . "
							, xAthletenstart=" . $_POST['athlete'] . "
							, Position='" . ($_POST['position']) . "'
							, xRunde='" . ($rowRound[0]) . "'
					");
				
					if(mysql_affected_rows() == 0) {
						AA_printErrorMsg($strPosition . $strErrNotValid);
					}
					if(mysql_errno() > 0) {
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}
				}
			}
		}
		
		mysql_query("UNLOCK TABLES");
	}	// ET empty fields
}
//
// remove position
//
elseif($_POST['arg'] == "remove_pos"){
	
	
	mysql_query("LOCK TABLES staffelathlet WRITE, start WRITE");
	
	mysql_query("DELETE FROM staffelathlet"
			. " WHERE xStaffelstart='" . $_POST['relay']
			. "' AND xAthletenstart='" . $_POST['athlete'] . "'");
            
	$sql = "SELECT * FROM staffelathlet WHERE xAthletenstart ='" . $_POST['athlete'] . "'";	
    $result = mysql_query($sql);	
    if(mysql_errno() > 0) {
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    } 
    if (mysql_num_rows($result) == 0){
            mysql_query("DELETE FROM start"
                        . " WHERE xStart ='" . $_POST['athlete'] . "'");
            
            if(mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
    }
	
	
	mysql_query("UNLOCK TABLES");
}
//
// remove position
//
elseif($_POST['arg'] == "change_startnbr"){
	
	mysql_query("LOCK TABLES staffel WRITE, anmeldung READ");
	
	$n = $_POST['startnumber'];
	
	// check if nbr already exists
	$res = mysql_query("SELECT * FROM staffel WHERE Startnummer = $n AND xMeeting = ".$_COOKIE['meeting_id']);
	
	if(mysql_num_rows($res) == 0){
		
		// check if start number exists in athlete registration
		$res = mysql_query("SELECT * FROM anmeldung 
					WHERE Startnummer = $n 
					AND xMeeting = ".$_COOKIE['meeting_id']);
		if(mysql_num_rows($res) == 0){
			
			mysql_query("UPDATE staffel SET Startnummer = $n WHERE xStaffel = ".$_POST['item']);
			if(mysql_errno() > 0) {
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			
		}else{
			AA_printErrorMsg($strStartnumberLong . $strErrNotValid);
		}
	}else{
		AA_printErrorMsg($strStartnumberLong . $strErrNotValid);
	}
	
	mysql_query("UNLOCK TABLES");
	
}

//
// Process del-request
//
if ($_GET['arg']=="del")
{
	mysql_query("LOCK TABLES serienstart READ,serienstart AS ss READ, start WRITE, start as s WRITE ,staffel WRITE"
				. ", staffelathlet WRITE");

	// get start ID for relay
	$sql = "SELECT 
                ss.xStart
	        FROM 
                serienstart AS ss
				LEFT JOIN start AS s ON (s.xStart = ss.xStart)
			WHERE s.xStaffel = " . $_GET['item'];        
                            
    $result = mysql_query($sql);
    
	if(mysql_errno() > 0)	// check DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());     
	}
	else	// no DB error
	{
		$rc = mysql_num_rows($result);
		mysql_free_result($result);

		if($rc == 0)		// not used anymore
		{
			$result = mysql_query("SELECT xStart"
										. " FROM start"
										. " WHERE xStaffel = " . $_GET['item']); 		
			if(mysql_errno() > 0)	// check DB error
			{
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else	// no DB error
			{
				$row = mysql_fetch_row($result);
				// delete relay athletes
				mysql_query("DELETE FROM staffelathlet WHERE xStaffelstart = "
							. $row[0]);
				if(mysql_errno() > 0)	// check DB error
				{
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else	// no DB error
				{
					// delete relay start
					mysql_query("DELETE FROM start WHERE xStart = "
							. $row[0]);
					if(mysql_errno() > 0)	// check DB error
					{
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}
					else	// no DB error
					{
						// delete relay
						mysql_query("DELETE FROM staffel WHERE xStaffel = "
									. $_GET['item']);
						if(mysql_errno() > 0)	// check DB error
						{
							AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}
					}		// ET DB error
				}		// ET DB error
			}		// ET DB error
			mysql_free_result($result);
		}
		else	// still in use
		{
			 AA_printErrorMsg($strRelay . $strErrStillUsed);
		}
	}			// ET DB error
	mysql_query("UNLOCK TABLES");

	$_POST['item'] = $_GET['item'];	// show empty form after delete
}

//
// display data
//
$page = new GUI_Page('meeting_relay');
$page->startPage();
$page->printPageTitle($strRelay);

if($_GET['arg'] == 'del')	// refresh list
{
	?>
<script type="text/javascript">
	window.open("meeting_relaylist.php", "list");
</script>
	<?php
}
else 
{
	?>
<script>
	window.open("meeting_relaylist.php?item="
		+ <?php echo $_POST['item']; ?>, "list");
</script>
	<?php
}

?>
<script type="text/javascript">
<!--
	function changePos(position, relayID, athleteID, roundID) {
		document.change_pos.position.value = position;
		document.change_pos.relay.value = relayID;
		document.change_pos.athlete.value = athleteID;
		document.change_pos.round.value = roundID;
		document.change_pos.submit();
	}
	
	function removePos(relayID, athleteID){
		document.change_pos.arg.value = "remove_pos";
		document.change_pos.relay.value = relayID;
		document.change_pos.athlete.value = athleteID;
		document.change_pos.submit();
	}
//-->
</script>

<?php

// get relay
// (remark: order of tables in FROM-clause is important for SQL performance)

  $sql = "SELECT
        s.xStaffel
        , s.Name
        , d.Kurzname
        , k.Kurzname
        , k.xKategorie
        , st.xStart
        , v.Name
        , v.xVerein
        , w.xWettkampf
        , t.Name
        , IFNULL(t.xTeam, 0)
        , st.Bestleistung
        , s.Startnummer        
    FROM
        start AS st
        LEFT JOIN staffel AS s ON (s.xStaffel = st.xStaffel)
        LEFT JOIN wettkampf AS w ON (st.xWettkampf = w.xWettkampf) 
        LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin)
        LEFT JOIN kategorie AS k ON (s.xKategorie = k.xKategorie )
        LEFT JOIN verein AS v ON (s.xVerein = v.xVerein)   
        LEFT JOIN team AS t ON (s.xTeam = t.xTeam)
    WHERE s.xStaffel = " . $_POST['item'];       

$result = mysql_query($sql);

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if (mysql_num_rows($result) > 0)
{
	$row = mysql_fetch_row($result);
	?>
<table class='dialog'>
<tr>
	<th class='dialog' colspan='2'><?php echo "$row[6], $row[3], $row[2]"; ?></th>
</tr>

<tr>
	<form action='meeting_relay.php' method='post' name='change_startnbr'>
	<td class='dialog'><?php echo $strStartnumberLong; ?></td>
	<td class='forms'>
		<input name='arg' type='hidden' value='change_startnbr' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input name='start' type='hidden' value='<?php echo $row[5]; ?>' />
		<input class='nbr' name='startnumber' type='text'
			maxlength='5' value="<?php echo $row[12]; ?>"
			onchange='document.change_startnbr.submit()' />
	</td>
	</form>
</tr>

<tr>
	<form action='meeting_relay.php' method='post' name='change_name'>
	<td class='dialog'><?php echo $strRelayname; ?></td>
	<td class='forms'>
		<input name='arg' type='hidden' value='change_name' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input name='start' type='hidden' value='<?php echo $row[5]; ?>' />
		<input class='name' name='name' type='text'
			maxlength='40' value="<?php echo $row[1]; ?>"
			onchange='document.change_name.submit()' />
	</td>
	</form>
</tr>

<tr>
	<form action='meeting_relay.php' method='post' name='change_perf'>
	<td class='dialog'><?php echo $strTopPerformance; ?></td>
	<td class='forms'>
		<input name='arg' type='hidden' value='change_perf' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input name='start' type='hidden' value='<?php echo $row[5]; ?>' />
		<input class='perftime' name='top' type='text'
			maxlength='12' value='<?php echo AA_formatResultTime($row[11]); ?>'
			onchange='document.change_perf.submit()' />
	</td>
	</form>
</tr>

<tr>
	<form action='meeting_relay.php' method='post' name='change_team'>
	<td class='dialog'><?php echo $strTeam; ?></td>
		<input name='arg' type='hidden' value='change_team' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input name='start' type='hidden' value='<?php echo $row[5]; ?>' />
	<?php
	$dd = new GUI_TeamDropDown($row[4], $row[7], $row[10], 'document.change_team.submit()');
	?>
	</form>
</tr>
</table>
<p><?php echo $strRelayAddAthlete ?></p>
	<?php
	// get relay athletes        
	  $sql = "SELECT
            DISTINCT(sa.xAthletenstart)
            , sa.Position
            , at.Name
            , at.Vorname
            , at.Jahrgang
            , a.Startnummer
        FROM
            staffelathlet AS sa
            LEFT JOIN start AS st ON (sa.xAthletenstart = st.xStart) 
            LEFT JOIN anmeldung AS a ON (st.xAnmeldung = a.xAnmeldung)        
            LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)    
        WHERE 
            sa.xStaffelstart = $row[5]   
        GROUP BY
            sa.xAthletenstart
        ORDER BY
            sa.Position";    
     
    $res = mysql_query($sql);

	if(mysql_errno() > 0)
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		?>
<p/>
<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strNbr; ?></th>
	<th class='dialog'><?php echo $strName; ?></th>
	<th class='dialog'><?php echo $strFirstname; ?></th>
	<th class='dialog'><?php echo $strYear; ?></th>
	<th class='dialog'><?php echo $strPosition; ?></th>
</tr>
<tr>
	<td class='dialog' colspan='4'></td>
	<?php
	//
	// show names of rounds
	//
	$arrRounds = array();
	$resPos = mysql_query("
			SELECT 
				rt.Name
				, r.xRunde
			FROM
				runde as r
				LEFT JOIN rundentyp_" . $_COOKIE['language'] . " as rt ON (r.xRundentyp = rt.xRundentyp)
			WHERE	r.xWettkampf = ".$row[8]."  
			ORDER BY	r.xRunde");
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}else{
		while($rowPos = mysql_fetch_array($resPos)){
			$arrRounds[] = $rowPos[1];
			?>
			<th class='dialog'><?php echo $rowPos[0]; ?></td>
			<?php
		}
	}
	?>
</tr>
<form action='meeting_relay.php' method='post' name='change_pos'>
	<input name='arg' type='hidden' value='change_pos' />
	<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
	<input name='relay' type='hidden' value='' />
	<input name='athlete' type='hidden' value='' />
	<input name='round' type='hidden' value='' />
	<input name='position' type='hidden' value='' />
		<?php	
		// display list of athletes
		while ($ath_row = mysql_fetch_row($res))
		{
?>
<tr>
	<td><?php echo $ath_row[5]; ?></td>
	<td><?php echo $ath_row[2]; ?></td>
	<td><?php echo $ath_row[3]; ?></td>
	<td class='forms_ctr'><?php echo AA_formatYearOfBirth($ath_row[4]); ?></td>
			<?php
			//
			// get different positions for each round
			//
			foreach($arrRounds as $r){
				$resPos = mysql_query("
						SELECT sa.Position, rt.Typ, sa.xRunde FROM
							staffelathlet as sa
							LEFT JOIN runde as r ON (sa.xRunde = r.xRunde)
							LEFT JOIN rundentyp_" . $_COOKIE['language'] . " as rt ON (r.xRundentyp = rt.xRundentyp) 
						WHERE	sa.xAthletenstart = ".$ath_row[0]."
						AND	sa.xStaffelstart = ".$row[5]." 						
						AND	r.xRunde = $r
						ORDER BY	r.xRunde");
				if(mysql_errno() > 0){
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}else{
					$rowPos = mysql_fetch_array($resPos)
					?>
		<!--<td class='forms_ctr'><input class='nbr' type='text'
			maxlength='2' value='<?php echo $ath_row[1]; ?>'
						onchange='changePos(value, <?php echo $row[5].", ".$ath_row[0]; ?>)'/>
		</td>-->
		<td class='forms_ctr'><input class='nbr' type='text'
			maxlength='2' value='<?php echo $rowPos[0]; ?>'
						onchange='changePos(value, <?php echo $row[5].", ".$ath_row[0].", ".$r; ?>)'/>
		</td>
				<?php
					
					mysql_free_result($resPos);
				}
			}
			?>
	<td class='forms_ctr'>
		<input type='button' value='<?php echo $strDelete ?>' name='delete' onclick='removePos(<?php echo $row[5].", ".$ath_row[0] ?>)'>
	</td>
</tr>
			<?php
		}
		mysql_free_result($res);
		?>
</form>	
<tr><td colspan='10'><hr /></td></tr>
<tr>
	<form action='meeting_relay.php' method='post' name='add_pos'>
	<td class='forms' colspan='4'>
		<input name='arg' type='hidden' value='add_pos' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input name='team' type='hidden' value='<?php echo $row[10]; ?>' />
		<input name='relay' type='hidden' value='<?php echo $row[5]; ?>' />
		<input name='event' type='hidden' value='<?php echo $row[8]; ?>' />
		<?php   
        
        $sqlClubLG=" = " .$row[7];
        $arrClub=AA_meeting_getLG_Club($row[7]);      // get all clubs with same LG
       
        if (count($arrClub) > 0) {
            $sqlClubLG=" IN (";
            foreach ($arrClub as $key => $val) {
                $sqlClubLG.=$val .",";              
           }
           $sqlClubLG=substr($sqlClubLG,0,-1);
           $sqlClubLG.=")";
        } 
        
		$dropdown = new GUI_Select("athlete", 1);
		$dropdown->addOption($strUnassignedAthletes, 0);
        
        $dropdown->addOptionsFromDB("  
            SELECT
                st.xStart
                , CONCAT( at.Name,' ', at.Vorname)   
                , a.Startnummer
                , at.Jahrgang
                
            FROM
                anmeldung AS a
                LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet  )
                LEFT JOIN start AS st ON (a.xAnmeldung = st.xAnmeldung)
                LEFT JOIN staffelathlet AS sa ON st.xStart = sa.xAthletenstart
            WHERE 
                sa.xAthletenstart IS NULL              
            AND st.xWettkampf = $row[8]
            AND at.xVerein $sqlClubLG
            AND a.xTeam = $row[10]
            ORDER BY
                at.Name
                , at.Vorname",true);   
      
        if(!empty($GLOBALS['AA_ERROR']))
        {
            AA_printErrorMsg($GLOBALS['AA_ERROR']);
        }           
       
        $dropdown->printList(false);
        
       ?>
      
	</td>
	<td class='forms'><input name='position' class='nbr' type='text'
		maxlength='2' value='' onchange='document.add_pos.submit()'/>
	</td>
	</form>	
		<?php			
		// extended athlete list (other categories, no relay entry, ...)
        $sqlClubLG=" = " .$row[7];
        $arrClub=AA_meeting_getLG_Club($row[7]);      // get all clubs with same LG
       
        if (count($arrClub) > 0) {
            $sqlClubLG=" IN (";
            foreach ($arrClub as $key => $val) {
                $sqlClubLG.=$val .",";              
           }
           $sqlClubLG=substr($sqlClubLG,0,-1);
           $sqlClubLG.=")";
        } 
               
		$res = mysql_query("
			SELECT
				a.xAnmeldung,
				a.Startnummer,
                at.Name,   
                at.Vorname,  
                at.Jahrgang,                 
				a.xTeam
			FROM
				anmeldung AS a
				LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
			WHERE  
			  a.xMeeting = " . $_COOKIE['meeting_id'] . "
			AND at.xVerein $sqlClubLG 
			ORDER BY at.Name, at.Vorname
		");
        
		if(mysql_errno() > 0)
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else	// OK
		{
			?>
	<form action='meeting_relay.php' method='post'>
	<td class='forms' colspan='2'>
		<input name='arg' type='hidden' value='add_new' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input name='event' type='hidden' value='<?php echo $row[8]; ?>' />
		<input name='team' type='hidden' value='<?php echo $row[10]; ?>' />
		<input name='relay' type='hidden' value='<?php echo $row[5]; ?>' />
			<?php	
			// Drop down list of all athletes who are not in this
			// event yet
			$dropdown = new GUI_Select("athlete", 1);
			$dropdown->addOption($strOtherAthletes, 0);

			while ($ath_row = mysql_fetch_row($res))
			{   
				$r = mysql_query("
					SELECT
						st.xStart
					FROM
						start AS st
					WHERE st.xWettkampf = $row[8]
					AND st.xAnmeldung = $ath_row[0]
				 ");                    
                 
				 if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				 }
				 else		// OK
				 {
					// Athlete not in even or team       
					if((mysql_num_rows($r) == 0) 	
						|| ($ath_row[5] != $row[10]))
					{
						$ath_concat = $ath_row[1] ."." . $ath_row[2] . " " . $ath_row[3] . " ( " . $ath_row[4]. " )";    
                        $dropdown->addOption($ath_concat, $ath_row[0]);                        
					}
                   
				}	// ET DB Error
				mysql_free_result($r);
			}
			mysql_free_result($res);
			//$dropdown->printList('forms', 2);
			$dropdown->printList();
			?>
	</td>
	<td class='forms'><input name='position' class='nbr' type='text'
		maxlength='2' value='' onchange='this.form.submit()'/> 
	</td>
	</form>	
			<?php
		}	// ET DB Error (unassigned athletes)
		?>
</tr>
<tr>
	<td colspan="8"><hr></td>
</tr>
<tr>
<?php		//
		// extended athlete list2 (all registered athletes)
		//
		
			?>
	<form action='meeting_relay.php' method='post' >
	<td class='forms' colspan='4'>
		<input name='arg' type='hidden' value='add_new' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input name='event' type='hidden' value='<?php echo $row[8]; ?>' />
		<input name='team' type='hidden' value='<?php echo $row[10]; ?>' />
		<input name='relay' type='hidden' value='<?php echo $row[5]; ?>' />
			<?php	
			// Drop down list of all athletes 
			$dropdown = new GUI_Select("athlete", 1);
			$dropdown->addOption($strAllRegisteredAthletes, 0);
            
             $dropdown->addOptionsFromDB("SELECT
                a.xAnmeldung
                , CONCAT( at.Name,' ', at.Vorname)   
                , a.Startnummer
                , at.Jahrgang  
            FROM
                anmeldung AS a
                LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
            WHERE  
              a.xMeeting = " . $_COOKIE['meeting_id'] . "             
            ORDER BY at.Name, at.Vorname", true);

			
             if(!empty($GLOBALS['AA_ERROR']))
                {
                    AA_printErrorMsg($GLOBALS['AA_ERROR']);
             }           
			$dropdown->printList(false);
			?>
	</td>
	<td class='forms'><input name='position' class='nbr' type='text'
		maxlength='2' value='' onchange='this.form.submit()'/> 
	</td>
	</form>	
			<?php
		
		 
         //
        // extended athlete list (team)
        //
        
            ?>
    <form action='meeting_relay.php' method='post' >
    <td class='forms' colspan='2'>
        <input name='arg' type='hidden' value='add_new' />
        <input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
        <input name='event' type='hidden' value='<?php echo $row[8]; ?>' />
        <input name='team' type='hidden' value='<?php echo $row[10]; ?>' />
        <input name='relay' type='hidden' value='<?php echo $row[5]; ?>' /> 
            <?php    
            // Drop down list of all athletes in team 
            $dropdown = new GUI_Select("athlete", 1);
            $dropdown->addOption($strAllRegisteredRelayTeam, 0);
            
            $dropdown->addOptionsFromDB(" SELECT
                 a.xAnmeldung
                , CONCAT( at.Name,' ', at.Vorname)   
                , a.Startnummer
                , at.Jahrgang  
            FROM
                anmeldung AS a
                LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
            WHERE  
                a.xMeeting = " . $_COOKIE['meeting_id'] . 
                " AND a.xTeam = " .$row[10] ." 
                 AND a.xTeam > 0   
            ORDER BY at.Name, at.Vorname", true);  

             if(!empty($GLOBALS['AA_ERROR']))
                {
                    AA_printErrorMsg($GLOBALS['AA_ERROR']);
             }           
            $dropdown->printList(false);
            ?>
    </td>
    <td class='forms'><input name='position' class='nbr' type='text'
        maxlength='2' value='' onchange='this.form.submit()'/> 
    </td>
    </form>    
            <?php
        
        ?>  
         
</tr>
<tr><form action='meeting_relay.php' method='post' >
    <td><?php echo $strStartnumberShort; ?>
    </td>
    <td class='forms' colspan='3'>
        <input name='arg' type='hidden' value='add_new_stnr' />
        <input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
        <input name='event' type='hidden' value='<?php echo $row[8]; ?>' />
        <input name='team' type='hidden' value='<?php echo $row[10]; ?>' />
        <input name='relay' type='hidden' value='<?php echo $row[5]; ?>' />
        
       <input name='startnr' type='text' value='' size="4" />
    </td>
    
    <td class='forms'>
        <input name='position' class='nbr' type='text'
        maxlength='2' value='' onchange='this.form.submit()'/>
    </td>
    </form>
 </tr>
</table>
		<?php
	}	// ET DB error relay athletes
}
mysql_free_result($result);
?>
<p/>
<?php
$btn = new GUI_Button("meeting_relay.php?arg=del&item=$row[0]", $strDelete);
$btn->printButton();

$page->endPage();

?>
