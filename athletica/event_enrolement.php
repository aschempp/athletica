<?php
/**********
 *
 *	event_enrolement.php
 *	--------------------
 *	
 */      
           
require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');
                                        
if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}


 
 if (!empty($_POST['arg'] )) {
	if ($_POST['arg'] =='change_catFrom')   
		$catFrom=$_POST['category'];
		$catTo=$_POST['category'];     
}          
 
if (!empty($_POST['arg'] )) {
	 if ($_POST['arg'] =='change_catTo') 
		$catTo=$_POST['category'];    
} 

if (!empty($_POST['arg'] )) {
	 if ($_POST['arg'] =='change_discFrom')
		$discFrom=$_POST['discipline'];
		$discTo=$_POST['discipline'];   
}
if (!empty($_POST['arg'] )) {
	 if ($_POST['arg'] =='change_discTo')
		$discTo=$_POST['discipline'];  
} 
if (!empty($_POST['arg'] )) {
	if ($_POST['arg'] =='change_mDate')  
		$mDate=$_POST['date']; 
}                                                          

if  (!empty($_POST['catFrom']) && $_POST['arg'] !='change_catFrom'){
	 $catFrom=$_POST['catFrom']; 
	
}
if  (!empty($_POST['catTo']) && $_POST['arg'] !='change_catTo'){
	 $catTo=$_POST['catTo'];      
}
if  (!empty($_POST['discFrom']) && $_POST['arg'] !='change_discFrom') {
	 $discFrom=$_POST['discFrom']; 

}
if  (!empty($_POST['discTo']) && $_POST['arg'] !='change_discTo'){
	 $discTo=$_POST['discTo']; 

}
if  (!empty($_POST['mDate']) && $_POST['arg'] !='change_mDate'){
	 $mDate=$_POST['mDate']; 

}

if  (!empty($_GET['catFrom'])) {
	 $catFrom=$_GET['catFrom'];  
}
if  (!empty($_GET['catTo'])) {
	 $catTo=$_GET['catTo'];  
}
if  (!empty($_GET['discFrom'])) {
	 $discFrom=$_GET['discFrom'];  
}
if  (!empty($_GET['discTo'])) {
	 $discTo=$_GET['discTo'];  
}
 
if  (!empty($_GET['mDate'])) {
	 $mDate=$_GET['mDate'];  
}                           

 
// get presets
if(!empty($_GET['category'])) {
	$category = $_GET['category'];
}
else {
	$category = 0;
}

if(!empty($_GET['event'])) {
	$event = $_GET['event'];
}
else {
	$event = 0;
}

if(!empty($_GET['round'])) {
	$round = $_GET['round'];  
}
else {
	$round = 0;
}    
 
if(!empty($_GET['comb'])) {
	$comb = $_GET['comb'];
	list($cCat, $cCode, $cDisz) = explode("_", $comb);
}
else {
	$comb = 0;
	$cCat = 0;
	$cCode = 0;
    $cDisz = 0;
}
$teamsm = false;
if (isset($_GET['teamsm'])){
    $teamsm = $_GET['teamsm'];
}
else {
    $teamsm = AA_checkTeamSM($event); 
}


if(isset($_GET['present'])) {		// athlete absent
	$present = 0;
} else {
	$present = 1;
}


$mk_group = '';
$tm_group = ''; 
if(!empty($_GET['group'])) {
    if ($teamsm) {
         $tm_group = $_GET['group']; 
    }
    else {
         $mk_group = $_GET['group']; 
    } 
}

//
//	Check if relay event
//
$relay = AA_checkRelay($event);
$combined = AA_checkCombined($event, $round);

$svm = AA_checkSVM(0, $round); // decide whether to show club or team name  


//
// Update absent status
//
if($_GET['arg'] == 'change')
{
	mysql_query("LOCK TABLES serienstart READ, staffel as st READ ,  start as s READ,start as s2 READ, verein as v READ, staffelathlet as stat READ, anmeldung as a READ,athlet as at READ,wettkampf as w READ, disziplin_de as d READ, disziplin_fr as d READ , disziplin_it as d READ, wettkampf WRITE, start WRITE");
	if($comb > 0){ // if combined set present for all starts
		/*$res = mysql_query("SELECT * FROM
				serienstart
				, start
				, wettkampf
			WHERE
				serienstart.xStart = start.xStart
			AND	start.xWettkampf = wettkampf.xWettkampf
			AND	wettkampf.xKategorie = $cCat
			AND	wettkampf.Mehrkampfcode = $cCode
			AND	wettkampf.xMeeting = ".$_COOKIE['meeting_id']."
			AND	start.xAnmeldung = ". $_GET['entry']);*/
		$sql = "SELECT
					*
				FROM
					serienstart
				LEFT JOIN 
					start USING(xStart)
				LEFT JOIN
					wettkampf USING(xWettkampf)
				WHERE
					wettkampf.xKategorie = ".$cCat."
				AND
					wettkampf.Mehrkampfcode = ".$cCode."
				AND
					wettkampf.xMeeting = ".$_COOKIE['meeting_id']."
				AND
					start.xAnmeldung = ".$_GET['entry'].";";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			if(mysql_num_rows($res) > 0){
				AA_printErrorMsg($strErrAthleteSeeded);
			}else{
				

				/*mysql_query("UPDATE start, wettkampf SET
						start.Anwesend='$present'
					WHERE	start.xWettkampf = wettkampf.xWettkampf
					AND	wettkampf.xKategorie = $cCat
					AND	wettkampf.Mehrkampfcode = $cCode
					AND	wettkampf.xMeeting = ".$_COOKIE['meeting_id']."
					AND	start.xAnmeldung='" . $_GET['entry'] . "'
					");*/
				$sql = "UPDATE
							start
						LEFT JOIN 
							wettkampf USING(xWettkampf)
						SET 
							start.Anwesend = '".$present."' 							
						WHERE
							wettkampf.xKategorie = ".$cCat."
						AND	
							wettkampf.Mehrkampfcode = ".$cCode."
						AND	
							wettkampf.xMeeting = ".$_COOKIE['meeting_id']."
						AND	
							start.xAnmeldung='" . $_GET['entry']."';";
				mysql_query($sql);
				
				if(mysql_errno() > 0){
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				
			}
		}
	}else{ // single normal event
		if(AA_checkReference("serienstart", "xStart", $_GET['item']) != 0) // seeded!
		{
			AA_printErrorMsg($strErrAthleteSeeded);
		}
		else
		{    			
			$sql = "UPDATE start SET 
						Anwesend='$present'						
						WHERE xStart='" . $_GET['item'] . "'";
			
			mysql_query($sql);   
		   /*                    
		   // relay: set present to start record from relay                        
		   if ($relay){
		        $sql = "SELECT DISTINCT  					
					 		s.xStaffel
				        FROM
							staffel AS st
							LEFT JOIN start AS s USING(xStaffel)
							LEFT JOIN verein AS v ON(st.xVerein = v.xVerein)
							LEFT JOIN staffelathlet AS stat ON(stat.xStaffelstart = s.xStart)
							LEFT JOIN start AS s2 ON(s2.xStart = stat.xAthletenstart)
							LEFT JOIN anmeldung AS a USING(xAnmeldung)
							LEFT JOIN athlet AS at USING(xAthlet)
							LEFT JOIN wettkampf AS w ON(s.xWettkampf = w.xWettkampf)
							LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON(w.xDisziplin = d.xDisziplin)
						WHERE        					
							s2.xStart='" . $_GET['item'] . "'";  
			    
				$res = mysql_query($sql);  
				
				if(mysql_errno() > 0){  
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else {    
			   			if (mysql_num_rows($res) > 0){ 
				      		$row=mysql_fetch_array($res);    
				       
				       		$sql = "SELECT DISTINCT 
				       						s.xStart, 				       						
				       						min(s2.Anwesend)  
					 					FROM
											staffel AS st
											LEFT JOIN start AS s USING(xStaffel)
											LEFT JOIN verein AS v ON(st.xVerein = v.xVerein)
											LEFT JOIN staffelathlet AS stat ON(stat.xStaffelstart = s.xStart)
											LEFT JOIN start AS s2 ON(s2.xStart = stat.xAthletenstart)
											LEFT JOIN anmeldung AS a USING(xAnmeldung)
											LEFT JOIN athlet AS at USING(xAthlet)
											LEFT JOIN wettkampf AS w ON(s.xWettkampf = w.xWettkampf)
											LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON(w.xDisziplin = d.xDisziplin)
										WHERE        					
											s.xStaffel='" . $row[0]. "' GROUP BY s.xStart";     
				    
							$result = mysql_query($sql); 
					
							if(mysql_errno() > 0){  
								AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
							}
				   			else {   
			   						if (mysql_num_rows($result) > 0){ 
				      					   $row_rel=mysql_fetch_array($result);  
				      					   if ($row_rel[1]==1)	{  
				      		                                      //  not any athlete are present --> set 1 to present in the start record from relay   
				      		    				$sql = "UPDATE start SET 
														Anwesend='$row_rel[1]'   
														WHERE xStart='" . $row_rel[0] . "'";
								
												mysql_query($sql); 	  
				      						}	
				      						else {                //  one or more athletes are present   
				      										      		                        
				      		      				$sql = "UPDATE start SET 
														Anwesend='$row_rel[1]'     
														WHERE xStart='" . $row_rel[0] . "'";
								
												mysql_query($sql);  
				      		               }  
							        }
							}
			   		    } 
				} 
		   } 
           */
		}
		if(mysql_errno() > 0)
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
	}
	
	mysql_query("UNLOCK TABLES");
}

//
// Update round status at termination
//
else if($_GET['arg'] == 'terminate')
{
	mysql_query("LOCK TABLES rundenset READ, runde AS r WRITE, runde WRITE, wettkampf AS w READ");
    
    $mergedEvents=AA_getMergedEvents($round); 
    if ($mergedEvents!='')
        $SqlEvents=" IN " .$mergedEvents;
    else
        $SqlEvents=" = " .$event;   
    	
	// get rounds which enrolement is pending for termination
	if($comb > 0 || $teamsm){	// combined event -> get all rounds
		/*$result = mysql_query("
			SELECT
				runde.xRunde
			FROM
				runde
				, wettkampf
			WHERE runde.xWettkampf = wettkampf.xWettkampf
			AND wettkampf.xKategorie = $cCat
			AND wettkampf.xMeeting = ".$_COOKIE['meeting_id']."
			AND wettkampf.Mehrkampfcode = $cCode
			AND (runde.Status = " . $cfgRoundStatus['enrolement_pending'] . "
			OR runde.Status = " . $cfgRoundStatus['open'] . ")
			ORDER BY
				runde.Datum ASC
				, runde.Startzeit ASC
		");*/
        
        $sql_group = "";
        if  (!empty($mk_group)) {
              $sql_group = " AND r.Gruppe =  " .$mk_group; 
        }
        elseif (!empty($tm_group)){
                $sql_group =" AND r.Gruppe =  " .$tm_group; 
        }
		if ($teamsm){
              $sql = "SELECT
                    r.xRunde                   
                FROM
                    runde AS r
                LEFT JOIN
                    wettkampf AS w USING(xWettkampf)
                WHERE  
                    w.xMeeting = ".$_COOKIE['meeting_id']."                  
                AND
                    r.xWettkampf  $SqlEvents  
                AND
                    (r.Status = ".$cfgRoundStatus['enrolement_pending']." 
                OR    r.Status = ".$cfgRoundStatus['open'].")
                " .$sql_group ."
                ORDER BY
                      r.Datum ASC
                    , r.Startzeit ASC;";   
        }
        else {
              $sql = "SELECT
                    r.xRunde                   
                FROM
                    runde AS r
                LEFT JOIN
                    wettkampf AS w USING(xWettkampf)
                WHERE  
                    w.xKategorie = ".$cCat."
                AND
                    w.xMeeting = ".$_COOKIE['meeting_id']."
                AND
                    w.Mehrkampfcode = ".$cCode."
                AND
                    (r.Status = ".$cfgRoundStatus['enrolement_pending']." 
                OR    r.Status = ".$cfgRoundStatus['open'].")
                " .$sql_group ."
                ORDER BY
                      r.Datum ASC
                    , r.Startzeit ASC;";   
        }
		  
		$result = mysql_query($sql); 
        
	}else{		// normal single event
		$result = mysql_query("
			SELECT
				xRunde
			FROM
				runde AS r
			WHERE r.xWettkampf  $SqlEvents
			AND (r.Status = " . $cfgRoundStatus['enrolement_pending'] . "
			OR r.Status = " . $cfgRoundStatus['open'] . ")              
			ORDER BY
				r.Datum ASC
				, r.Startzeit ASC
		");  
	}
	if(mysql_errno() > 0)		// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}

	while($row = mysql_fetch_array($result)){
       
		mysql_query("
			UPDATE runde SET
				Status = " . $cfgRoundStatus['enrolement_done'] . "
			WHERE xRunde = ".$row[0]."
		");
		if(mysql_errno() > 0)
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
	}
	
	mysql_query("UNLOCK TABLES");
}

$arg = (isset($_GET['arg'])) ? $_GET['arg'] : ((isset($_COOKIE['sort_enrolement'])) ? $_COOKIE['sort_enrolement'] : 'nbr');
setcookie('sort_enrolement', $arg, time()+2419200);

//
//	Display enrolement list
//

$page = new GUI_Page('event_enrolement', TRUE);
$page->startPage();
$page->printPageTitle($strEnrolement . ": " . $_COOKIE['meeting']);
              
$menu = new GUI_Menulist();
if ($teamsm){
      $menu->addButton("dlg_print_event_enrolement.php?category=$category&event=$event&comb=$comb&catFrom=$catFrom&catTo=$catTo&discFrom=$discFrom&discTo=$discTo&mDate=$mDate&teamsm=$teamsm&group=$tm_group", $strPrint." ...", '_self'); 
}
else {
     $menu->addButton("dlg_print_event_enrolement.php?category=$category&event=$event&comb=$comb&catFrom=$catFrom&catTo=$catTo&discFrom=$discFrom&discTo=$discTo&mDate=$mDate&group=$mk_group", $strPrint." ...", '_self'); 
}

$menu->addButton($cfgURLDocumentation . 'help/event/enrolement.html', $strHelp, '_blank');
$menu->printMenu();

// sort argument
$img_nbr="img/sort_inact.gif";
$img_name="img/sort_inact.gif";
$img_club="img/sort_inact.gif";
$img_team="img/sort_inact.gif"; 

if ($arg=="nbr") {
	$argument="a.Startnummer";
	$img_nbr="img/sort_act.gif";
} else if ($arg=="name") {
	$argument="at.Name, at.Vorname";
	$img_name="img/sort_act.gif";
} else if ($arg=="club") {
	$argument="v.Sortierwert, a.Startnummer, d.Anzeige";
	$img_club="img/sort_act.gif";
} else if ($arg=="team") {
    $argument="t.Name, a.Startnummer, d.Anzeige";
    $img_team="img/sort_act.gif";
} else if ($arg=="relay") {
	$argument="st.Name";
	$img_name="img/sort_act.gif";
} else if ($arg=="relay_club") {
	$argument="v.Sortierwert, st.Name, d.Anzeige";
	$img_club="img/sort_act.gif";
} else if($relay == FALSE) {		// single event
	$argument="at.Name, at.Vorname";
	$img_name="img/sort_act.gif";
} else {							// relay event
	$argument="st.Name";
	$img_name="img/sort_act.gif";
}

 
?>
<p />

<table><tr>
	<td class='forms'>
		<?php	AA_printCategorySelection('event_enrolement.php', $category, 'get'); ?>
	</td>
	<td class='forms'>
		<?php	AA_printEventSelection('event_enrolement.php', $category, $event); ?>
	</td>
	<td class='forms'>
		<?php	AA_printEventCombinedSelection('event_enrolement.php', $category, $comb, 'get'); ?>
	</td>
</tr></table>
 <br>
<table>   

	<tr>       
				<th class='dialog'><?php echo $strCategory . " "; echo $strOf2;?></th>
				 <form action='event_enrolement.php' method='post' name='catFrom' > 
					<input name='arg' type='hidden' value='change_catFrom' /> 
					 <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
					  <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' /> 
					  <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' /> 
					   <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' /> 
					   <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />       
<?php
				$dd = new GUI_CategoryDropDown($catFrom,'document.catFrom.submit()', false);
				?>
				</form>
				 <th class='dialog'><?php echo $strCategory. " "; echo $strTo2; ?></th>
				 <form action='event_enrolement.php' method='post' name='catTo' > 
				 <input name='arg' type='hidden' value='change_catTo' /> 
				 <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' />  
				 <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
				  <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' />  
				   <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' />   
				   <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />    
				<?php
				$dd = new GUI_CategoryDropDown($catTo,'document.catTo.submit()', false);
				?>
				 </form>   
			</tr>
	<tr>

				<th class='dialog'><?php echo $strDiscipline. " "; echo $strOf2;?></th>
				 <form action='event_enrolement.php' method='post' name='discFrom' > 
					<input name='arg' type='hidden' value='change_discFrom' /> 
					 <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' /> 
					 <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' />  
					  <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
					   <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' /> 
					   <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />       
				<?php     
				$dd = new GUI_DisciplineDropDown($discFrom,'','','','document.discFrom.submit()');
				?>
				 </form> 
				 <th class='dialog'><?php echo $strDiscipline. " "; echo $strTo2; ?></th> 
				  <form action='event_enrolement.php' method='post' name='discTo' > 
					<input name='arg' type='hidden' value='change_discTo' /> 
					 <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
					  <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' />    
					 <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' />   
					  <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' /> 
					  <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />     
				
				<?php
				$dd = new GUI_DisciplineDropDown($discTo,'','','','document.discTo.submit()');   
				?>
				 </form>   
	</tr>
	<tr>
				<form action='event_enrolement.php' method='post' name='mDate' > 
					<input name='arg' type='hidden' value='change_mDate' /> 
					 <input name='discFrom' type='hidden' value='<?php echo $discFrom; ?>' /> 
					 <input name='discTo' type='hidden' value='<?php echo $discTo; ?>' />  
					  <input name='catFrom' type='hidden' value='<?php echo $catFrom; ?>' /> 
					   <input name='catTo' type='hidden' value='<?php echo $catTo; ?>' />  
					   <input name='mDate' type='hidden' value='<?php echo $mDate; ?>' />   
				<?php 
				
				$tage = 1;
				$sql_day = "SELECT 
							DISTINCT(Datum) AS Datum 
						FROM 
							runde 
						LEFT JOIN wettkampf USING(xWettkampf) 
						WHERE xMeeting = ".$_COOKIE['meeting_id']." 
						ORDER BY Datum ASC;";
						
				$query_day = mysql_query($sql_day);
			   
				$tage = mysql_num_rows($query_day);
				
				if($tage>1){
					?> 
				 
					<th class='dialog'>
					<?php echo $strDay; ?></input>
					</th>
				 
					<td class='forms'>
						<select name='date' onchange='document.mDate.submit()'>
						<option value="%">- <?php echo $strAll; ?> -</option>
					<?php
						while($row = mysql_fetch_assoc($query_day)){     
							
							if ($row['Datum'] == $mDate) {                                  
								?>
								<option selected="<?php $mDate ?>" value="<?php echo $row['Datum']; ?>"><?php echo date('d.m.Y', strtotime($row['Datum'])); ?> </option>
								<?php
							}
							else {
							?>
							<option value="<?php echo $row['Datum']; ?>"><?php echo date('d.m.Y', strtotime($row['Datum'])); ?> </option>
							<?php
							}
					   }
					?>
							</select>
				
					 <?php
					   }
					?>
				
					</form> 
					</td>
				 </tr>

</table>

<?php
  
if($event > 0 || $comb > 0 || $catFrom > 0 || $discFrom > 0 || $mDate > 0)
{                                      
	// check if enrolement pending for this event
	if($comb > 0 || $event > 0){ // combined event selected
		/*$result = mysql_query("
			SELECT
				xRunde
			FROM
				runde as r
				, wettkampf as w
			WHERE w.xWettkampf = r.xWettkampf
			AND w.xKategorie = $cCat
			AND w.Mehrkampfcode = $cCode
			AND w.xMeeting = ".$_COOKIE['meeting_id']."
			AND (r.Status = " . $cfgRoundStatus['enrolement_pending'] . "
			OR r.Status = " . $cfgRoundStatus['open'] . ")
			ORDER BY
				r.Datum ASC
				, r.Startzeit ASC
		");*/
	              		 
		if ($event > 0){          // only one disciplin of combined event
			$sqlEventComb=" w.xWettkampf = $event";   
			$sqlCat = '';
			$sqlMk = ''; 
		}
		else {   
			$sqlEventComb = '';        // the whole combined event
			$sqlCat = " w.xKategorie = " .$cCat ." AND ";    
            $sqlMk = " w.Mehrkampfcode = ".$cCode;   
		}   
        
        $sql_group = "";
        if  (!empty($mk_group)) {
              $sql_group = " AND r.Gruppe =  " .$mk_group; 
        }    
        elseif  (!empty($tm_group)) {
              $sql_group = " AND r.Gruppe =  " .$tm_group;
        }             		 
	    if ($teamsm){
                $sql = "SELECT
                    xRunde
                FROM
                    runde AS r
                LEFT JOIN
                    wettkampf AS w USING(xWettkampf)
                WHERE "
                    . $sqlEventComb
                       . $sqlCat     
                       . $sqlMk 
                    . $sql_group ."                          
                AND
                    w.xMeeting = ".$_COOKIE['meeting_id']."
                AND
                    (r.Status = ".$cfgRoundStatus['enrolement_pending']."
                OR
                    r.Status = ".$cfgRoundStatus['open'].")  
                ORDER BY
                      r.Datum ASC
                    , r.Startzeit ASC;";    
                    
               
        }
        else {
            
              $sql = "SELECT
                    xRunde
                FROM
                    runde AS r
                LEFT JOIN
                    wettkampf AS w USING(xWettkampf)
                WHERE "
                    . $sqlEventComb
                       . $sqlCat     
                       . $sqlMk 
                    . $sql_group ."                          
                AND
                    w.xMeeting = ".$_COOKIE['meeting_id']."
                AND
                    (r.Status = ".$cfgRoundStatus['enrolement_pending']."
                OR
                    r.Status = ".$cfgRoundStatus['open'].")  
                ORDER BY
                      r.Datum ASC
                    , r.Startzeit ASC;";
        }
		
	    
		$result = mysql_query($sql);
	   
	}else{ // normal single event
		  
		$result = mysql_query("
			SELECT
				xRunde
			FROM
				runde
			WHERE xWettkampf = $event
			AND (Status = " . $cfgRoundStatus['enrolement_pending'] . "
			OR Status = " . $cfgRoundStatus['open'] . ")
			ORDER BY
				Datum ASC
				, Startzeit ASC
		");  
        
	}    
    
    $mainEvent=AA_getMainRoundEvent($event,false);    
    if ($mainEvent!=$event & $mainEvent!=''){
        $flagMain=false;
    }
    else {
          $flagMain=true;      
       
	if(mysql_errno() > 0)		// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else if(mysql_num_rows($result) > 0)  // data found
	{    
		$row = mysql_fetch_row($result);
		$round=$row[0];
        $group=$row[1];
        if ($teamsm) {
              $btn = new GUI_Button("event_enrolement.php?arg=terminate&round=$row[0]&category=$category&event=$event&teamsm=$teamsm&group=$tm_group", $strTerminateEnrolement);   
        }
        else {
             $btn = new GUI_Button("event_enrolement.php?arg=terminate&round=$row[0]&category=$category&event=$event&comb=$comb&group=$mk_group", $strTerminateEnrolement);   
        }
		
		$btn->printButton();
?>
<p/>
<?php
		mysql_free_result($result);
	}
	if($_GET['arg'] == 'terminate' && $comb == 0){
		$btn = new GUI_Button("dlg_heat_seeding.php?round=$round", $strHeatSeeding);
		$btn->printButton();
	}

    
    
?>
<p/>
<table class='dialog'>
	<tr>
		<th class='dialog'>
			<?php echo $strPresent; ?>
		</th>
<?php
	if($relay == FALSE)		// single event
	{
?>
		<th class='dialog'>
			<a href='event_enrolement.php?arg=nbr&category=<?php echo $category; ?>&event=<?php echo $event; ?>&comb=<?php echo $comb; ?>&catFrom=<?php echo $catFrom; ?>&catTo=<?php echo $catTo; ?>&discFrom=<?php echo $discFrom; ?>&discTo=<?php echo $discTo; ?>&mDate=<?php echo $mDate; ?>&round=<?php echo $round;?>'><?php echo $strStartnumber; ?>
				<img src='<?php echo $img_nbr; ?>' />
			</a>
		</th>
		
		<th class='dialog'>
			<a href='event_enrolement.php?arg=name&category=<?php echo $category; ?>&event=<?php echo $event; ?>&comb=<?php echo $comb; ?>&catFrom=<?php echo $catFrom; ?>&catTo=<?php echo $catTo; ?>&discFrom=<?php echo $discFrom; ?>&discTo=<?php echo $discTo; ?>&mDate=<?php echo $mDate; ?>&round=<?php echo $round;?>'><?php echo $strName; ?>

				<img src='<?php echo $img_name; ?>' />
			</a>
		</th>

		<th class='dialog'>
		<?php echo $strYear; ?>
		</th>
		<th class='dialog'>
            <?php 
            if($svm){ 
                ?>
               <a href='event_enrolement.php?arg=team&category=<?php echo $category; ?>&event=<?php echo $event; ?>&comb=<?php echo $comb; ?>&catFrom=<?php echo $catFrom; ?>&catTo=<?php echo $catTo; ?>&discFrom=<?php echo $discFrom; ?>&discTo=<?php echo $discTo; ?>&mDate=<?php echo $mDate; ?>&round=<?php echo $round;?>'><?php  echo $strTeam; ?> 
                <img src='<?php echo $img_team; ?>' />
                </a>
                <?php
            }
                elseif ($teamsm){
                    ?>
                     <a href='event_enrolement.php?arg=team&category=<?php echo $category; ?>&event=<?php echo $event; ?>&comb=<?php echo $comb; ?>&catFrom=<?php echo $catFrom; ?>&catTo=<?php echo $catTo; ?>&discFrom=<?php echo $discFrom; ?>&discTo=<?php echo $discTo; ?>&mDate=<?php echo $mDate; ?>&round=<?php echo $round;?>'><?php  echo $strTeamsm; ?> 
                <img src='<?php echo $img_team; ?>' />
                </a>
                <?php
                }
                else { 
                  ?> 
			<a href='event_enrolement.php?arg=club&category=<?php echo $category; ?>&event=<?php echo $event; ?>&comb=<?php echo $comb; ?>&catFrom=<?php echo $catFrom; ?>&catTo=<?php echo $catTo; ?>&discFrom=<?php echo $discFrom; ?>&discTo=<?php echo $discTo; ?>&mDate=<?php echo $mDate; ?>&round=<?php echo $round;?>'><?php  echo $strClub; ?>  
				<img src='<?php echo $img_club; ?>' />
			</a>
            <?php
                }
            ?>
		</th> 
		<?php  
							
		if ($comb==0) { 
			 if ($event==0){  
			 ?>
			  <th class='dialog'>
			  <?php echo $strDiscipline; ?>
			  </th>
			  <?php 
			  if ($mDate==''  || $mDate=='%' ){ 
					if ($tage>1) {
					?>    
					<th class='dialog'>
					<?php echo $strDate; ?>    
					</th>
					<?php
					}      
			  }
			  }
		}
		?>
	   
<?php
	}
	else		// relay event
		{
?>
		<th class='dialog'>
			<a href='event_enrolement.php?arg=nbr&category=<?php echo $category; ?>&event=<?php echo $event; ?>'><?php echo $strStartnumber; ?>
				<img src='<?php echo $img_nbr; ?>' />
			</a>
		</th>
		
        <th class='dialog'>
            <a href='event_enrolement.php?arg=relay&category=<?php echo $category; ?>&event=<?php echo $event; ?>'><?php echo $strRelays; ?>
                <img src='<?php echo $img_name; ?>' />
            </a>
        </th>		
		<th class='dialog'>
			<a href='event_enrolement.php?arg=relay_club&category=<?php echo $category; ?>&event=<?php echo $event; ?>'><?php if ($svm) { echo $strTeam; } else { echo $strClub; }?>
				<img src='<?php echo $img_club; ?>' />
			</a>
		</th>
<?php
	}
?>
	</tr>
   

<?php  
	
	//
	// read merged rounds and select all events
	//  
    
    

    $sqlEvents=AA_getMergedEvents($round);
    
    if  ($sqlEvents=='' && $round==0){   
    	$sqlEvents=AA_getMergedEventsFromEvent($event);        	
	}
    if ($event > 0 && $sqlEvents!=''){
       $sqlEventComb=" w.xWettkampf IN ". $sqlEvents;        
    }
    
    
    if ($sqlEvents=='' ) {
        if ($event > 0){
            $sqlEvents = " s.xWettkampf = ".$event." ";  
            $sqlEvents.=" AND w.xMeeting = ". $_COOKIE['meeting_id'];    
        }
        else {
            $sqlEvents.=" w.xMeeting = ". $_COOKIE['meeting_id'];    
        } 
	}
    else {
        $sqlEvents = " s.xWettkampf IN ".$sqlEvents." "; 
        $sqlEvents.=" AND w.xMeeting = ". $_COOKIE['meeting_id'];   
	}
   
    $sqlDate = '';       
   
   if ($catFrom > 0 && $discFrom > 0){ 
   			 $sqlDate = ", r.Datum ";    
			 $getSortDisc = AA_getSortDisc($discFrom,$discTo);            // sort display from category
			 $getSortCat = AA_getSortCat($catFrom,$catTo);                // sort display from dicipline
			 if ($getSortCat[0] && $getSortDisc[0]) {  
				if ($catTo > 0)     
					$sqlEvents = " k.Anzeige >= ".$getSortCat[$catFrom] ." AND k.Anzeige <= ".$getSortCat[$catTo]." ";
				else
					$sqlEvents = " k.Anzeige = ".$getSortCat[$catFrom]." "; 
				if ($discTo > 0)                              
						$sqlEvents .= " AND d.Anzeige >= ".$getSortDisc[$discFrom] ." AND d.Anzeige <= " . $getSortDisc[$discTo] ." "; 
					else
					   $sqlEvents .= " AND d.Anzeige = " . $getSortDisc[$discFrom]." "; 
			  $sqlEvents.=" AND w.xMeeting = ". $_COOKIE['meeting_id']; 
			 }
			 else
			  $sqlEvents.=" w.xMeeting = ". $_COOKIE['meeting_id'];   
	
			$sqlGroupBy = " GROUP BY at.Name, at.Vorname, d.xDisziplin ";   
	}
	elseif ($catFrom > 0){   $sqlDate = ", r.Datum ";    
				  $getSortCat = AA_getSortCat($catFrom,$catTo);          // sort display from category  
				  if ($getSortCat[0]) {
					if ($catTo > 0)     
						$sqlEvents = " k.Anzeige >= ".$getSortCat[$catFrom]." AND k.Anzeige <= ".$getSortCat[$catTo]." ";
					else
						$sqlEvents = " k.Anzeige = ".$getSortCat[$catFrom]." ";  
				  	$sqlEvents.=" AND w.xMeeting = ". $_COOKIE['meeting_id'];  
				  } 
				  else                    
				 	$sqlEvents.=" w.xMeeting = ". $_COOKIE['meeting_id']; 
					 
				  $sqlGroupBy = " GROUP BY at.Name, at.Vorname, d.xDisziplin ";  
	}
	elseif ($discFrom > 0) {    $sqlDate = ", r.Datum ";    
					$getSortDisc = AA_getSortDisc($discFrom,$discTo);         // sort display from dicipline              
					 if ($getSortDisc[0]){
						if ($discTo > 0)                              
							$sqlEvents = " d.Anzeige >= ".$getSortDisc[$discFrom]." AND d.Anzeige <= ".$getSortDisc[$discTo] ." "; 
						else
							$sqlEvents = " d.Anzeige = ".$$getSortDisc[$discFrom]." "; 
					 $sqlEvents.=" AND w.xMeeting = ". $_COOKIE['meeting_id'];  
					 }
					 else 
						$sqlEvents.=" w.xMeeting = ". $_COOKIE['meeting_id'];  
			
					$sqlGroupBy = " GROUP BY at.Name, at.Vorname, d.xDisziplin "; 
	}
	if ($mDate > 0){    $sqlDate = ", r.Datum ";   
		   	 if ($sqlEvents!='')  {
				$sqlEvents.=" AND r.Datum = '" . $mDate . "' ";
			 }
			 else  {
				$sqlEvents.=" w.xMeeting = ". $_COOKIE['meeting_id'] ." AND r.Datum = '" . $mDate . "' ";    
			 }
	}  
	
   }
   
    if ($sqlEvents==''){
         $sqlEvents=" w.xMeeting = ". $_COOKIE['meeting_id'];    
	}           
   
    if ($flagMain){
	
	if($relay == FALSE) {          
			// single event
           
		if($comb > 0 || $event > 0){ // combined, select entries over each discipline
			
            $sql_event = "SELECT 
                                r.xWettkampf
                          FROM
                                runde as r
                          WHERE
                                r.xRunde = ".$round;
            $res_event = mysql_query($sql_event);   
            if(mysql_errno() > 0)        // DB error
                {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else if(mysql_num_rows($res_event) > 0) {   // data found   
                      $row = mysql_fetch_row($res_event);
                      $event = $row[0];
            } 
            
             $mainEvent=AA_getMainRoundEvent($event,false);    
             if ($mainEvent!=$event & $mainEvent!=''){
                //$flagMain=false;
                AA_printErrorMsg($strErrMergedRound);  
                return;
             }
            // else {
            //    $flagMain=true;  
            // }    
            
            $sqlCats= AA_mergedCatEvent($cCat,$event); 
            if (!empty($sqlCats)) {
                if (empty($sqlEventComb)){
                 $sqlCat = " w.xKategorie IN $sqlCats AND ";  
                }
                else {
                     $sqlCat = " AND w.xKategorie IN $sqlCats ";  
                } 
            }
             if  (!empty($mk_group) || (!empty($tm_group))) {   
                 if ($teamsm) {
                     $sql = "SELECT
                              s.xStart
                            , s.Anwesend
                            , a.Startnummer
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang                          
                            , t.Name   
                            , a.xAnmeldung
                            , s.Bezahlt 
                            , s.Gruppe                          
                        FROM
                            anmeldung AS a
                        LEFT JOIN
                            athlet AS at USING(xAthlet)
                        LEFT JOIN 
                            start AS s ON(s.xAnmeldung = a.xAnmeldung)                         
                        LEFT JOIN 
                            verein AS v ON(at.xVerein = v.xVerein)                        
                       INNER JOIN
                            teamsmathlet AS tat ON(a.xAnmeldung = tat.xAnmeldung)    
                        LEFT JOIN teamsm as t ON (tat.xTeamsm = t.xTeamsm)                      
                        WHERE s.Gruppe = " .$tm_group ."
                              AND s.xWettkampf = " . $event  ."
                        GROUP BY a.xAnmeldung                          
                        ORDER BY
                            ".$argument.", a.xAnmeldung;";
                            
                       
                        
                 }
                 else {
                       $sql = "SELECT
                              s.xStart
                            , s.Anwesend
                            , a.Startnummer
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang                          
                            , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))    
                            , a.xAnmeldung
                            , s.Bezahlt 
                            , a.Gruppe                          
                        FROM
                            anmeldung AS a
                        LEFT JOIN
                            athlet AS at USING(xAthlet)
                        LEFT JOIN 
                            start AS s ON(s.xAnmeldung = a.xAnmeldung)
                        LEFT JOIN 
                            verein AS v ON(at.xVerein = v.xVerein)
                        LEFT JOIN
                            team AS t ON(a.xTeam = t.xTeam)  
                        WHERE a.Gruppe = " .$mk_group ."
                        GROUP BY a.xAnmeldung                          
                        ORDER BY
                            ".$argument.", a.xAnmeldung;";
                 }
                
             }
             else {
                   if ($teamsm){
                        $sql = "SELECT
                              s.xStart
                            , s.Anwesend
                            , a.Startnummer
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang                          
                            , t.Name 
                            , a.xAnmeldung
                            , s.Bezahlt 
                            , s.Gruppe                          
                        FROM
                            anmeldung AS a
                        LEFT JOIN
                            athlet AS at USING(xAthlet)
                        LEFT JOIN 
                            start AS s ON(s.xAnmeldung = a.xAnmeldung)                         
                        LEFT JOIN 
                            verein AS v ON(at.xVerein = v.xVerein)                        
                        INNER JOIN
                            teamsmathlet AS tat ON(a.xAnmeldung = tat.xAnmeldung)    
                        LEFT JOIN teamsm as t ON (tat.xTeamsm = t.xTeamsm)     
                        LEFT JOIN
                            wettkampf AS w ON(s.xWettkampf = w.xWettkampf) 
                        LEFT JOIN
                            disziplin_" . $_COOKIE['language'] . " AS d ON(w.xDisziplin   = d.xDisziplin)                  
                        WHERE s.Gruppe = '" .$tm_group ."'
                              AND s.xWettkampf = " . $event  ."
                        GROUP BY a.xAnmeldung                          
                        ORDER BY
                            ".$argument.", a.xAnmeldung;";  
                            
                  
                        
                   }  
                   else {
                        $sql = "SELECT
                              s.xStart
                            , s.Anwesend
                            , a.Startnummer
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang                          
                            , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))    
                            , a.xAnmeldung
                            , s.Bezahlt                           
                        FROM
                            anmeldung AS a
                        LEFT JOIN
                            athlet AS at USING(xAthlet)
                        LEFT JOIN 
                            start AS s ON(s.xAnmeldung = a.xAnmeldung)
                        LEFT JOIN 
                            verein AS v ON(at.xVerein = v.xVerein)
                        LEFT JOIN
                            team AS t ON(a.xTeam = t.xTeam)  
                        LEFT JOIN
                            wettkampf AS w ON(s.xWettkampf = w.xWettkampf) 
                        LEFT JOIN
                            disziplin_" . $_COOKIE['language'] . " AS d ON(w.xDisziplin   = d.xDisziplin) "
                        . $sqlTable ."                                       
                        WHERE        "
                            . $sqlEventComb                           
                            . $sqlCat                          
                            . $sqlMk ."
                             AND w.xMeeting = ".$_COOKIE['meeting_id'] 
                        . $sqlGroupBy ."  
                        ORDER BY
                            ".$argument.", a.xAnmeldung;";
                   } 
			   
			    } 
			$query = $sql;                      
           
		}else{  
			// no combined
									   		   
			$sql = "(SELECT DISTINCT 
						  s.xStart
						, s.Anwesend
						, a.Startnummer
						, at.Name
						, at.Vorname
						, at.Jahrgang
                         , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))  
						, a.xAnmeldung  
						, d.Name
					    ".$sqlDate."
						, s.Bezahlt    						  
					FROM
						anmeldung AS a
					LEFT JOIN
						athlet AS at USING(xAthlet)
					LEFT JOIN 
						start AS s ON(s.xAnmeldung = a.xAnmeldung)
					LEFT JOIN 
						verein AS v ON(at.xVerein = v.xVerein)
                    LEFT JOIN
                        team AS t ON(a.xTeam = t.xTeam)
					LEFT JOIN
						wettkampf AS w ON(s.xWettkampf = w.xWettkampf)
					LEFT JOIN
						disziplin_" . $_COOKIE['language'] . " AS d ON(w.xDisziplin   = d.xDisziplin)
					LEFT JOIN runde AS r ON(r.xWettkampf = w.xWettkampf) 
					LEFT JOIN kategorie AS k ON(w.xKategorie = k.xKategorie)      
					WHERE   					
						".$sqlEvents." AND d.Staffellaeufer = 0 AND w.Mehrkampfcode = 0 
					".$sqlGroupBy ." 
					ORDER BY
						".$argument.")
						
					UNION    
						
					(SELECT DISTINCT 
						s.xStart     
						, s.Anwesend
						, a.Startnummer
						, at.Name
						, at.Vorname
						, at.Jahrgang
						  , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))   
						, a.xAnmeldung  
						, d.Name
					    ".$sqlDate."  
						, s.Bezahlt    						
					FROM
						  
						anmeldung AS a
					LEFT JOIN
						athlet AS at USING(xAthlet)
					LEFT JOIN 
						start AS s ON(s.xAnmeldung = a.xAnmeldung)
					LEFT JOIN 
						verein AS v ON(at.xVerein = v.xVerein)
                    LEFT JOIN
                        team AS t ON(a.xTeam = t.xTeam)
					LEFT JOIN
						wettkampf AS w ON(s.xWettkampf = w.xWettkampf)
					LEFT JOIN
						disziplin_" . $_COOKIE['language'] . " AS d ON(w.xDisziplin   = d.xDisziplin)
					LEFT JOIN 
						staffel AS staf On (staf.xStaffel = s.xStaffel ) 
                         
					 LEFT JOIN
						staffelathlet AS stat ON(stat.xStaffelstart = s.xStart) 
					 LEFT JOIN runde AS r ON(r.xWettkampf = w.xWettkampf)
					 LEFT JOIN kategorie AS k ON(w.xKategorie = k.xKategorie)     
					WHERE      					
						".$sqlEvents." AND d.Staffellaeufer > 0  
					".$sqlGroupBy ."  
					ORDER BY
						".$argument.")";    
			$query = $sql;      
         
		}
	}
	else {							// relay event
		//
		// get each athlete from all registered relays
		//
		
        $sql = "SELECT 
                        s.xStart 
                        , s.Anwesend 
                        , st.Name 
                        , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))   
                        , a.Startnummer 
                        , at.Name 
                        , at.Vorname 
                        , at.Jahrgang  
                        , s.Bezahlt 
                        , d.Name 
                        , st.Startnummer                      
                FROM 
                    staffel AS st 
                    LEFT JOIN start AS s USING(xStaffel) 
                    LEFT JOIN verein AS v ON(st.xVerein = v.xVerein)
                   
                    LEFT JOIN staffelathlet AS stat ON(stat.xStaffelstart = s.xStart) 
                    LEFT JOIN start AS s2 ON(s2.xStart = stat.xAthletenstart)
                    LEFT JOIN anmeldung AS a ON (s2.xAnmeldung=a.xAnmeldung) 
                     
                    LEFT JOIN athlet AS at USING(xAthlet) 
                    LEFT JOIN wettkampf AS w ON(s.xWettkampf = w.xWettkampf) 
                    LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON(w.xDisziplin = d.xDisziplin) 
                    LEFT JOIN team AS t ON (st.xTeam = t.xTeam) 
                WHERE                            
                        ".$sqlEvents." 
                GROUP BY 
                         st.Startnummer
                ORDER BY
                        ".$argument.";";
                        
		    $query = $sql;   
          
	}                   
	
	$result = mysql_query($query);
	
	if(mysql_errno() > 0)		// DB error
	{   
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else if(mysql_num_rows($result) > 0)  // data found
	{
		$i=0;
		$rowclass = "odd";
		$xEntry = 0;
		
			
		while ($row = mysql_fetch_array($result))
		{  
			if($comb > 0 && $xEntry == $row[7]){ // combined, merge starts
				continue;
			}
			$xEntry = $row[7];
			
			if($a != 0) {				// not first row
				printf("</tr>\n");
			}
			
			$i++;
			if( $i % 2 == 0 ) {		// even row number
				$rowclass = "even";
			}
			else {	// odd row number
				$rowclass = "odd";
			}
			printf("<tr class='$rowclass'>\n");
			printf("<form action='event_enrolement.php#$row[0]' method='get'"
					. " name='change_present_$i'>");
			
			if($row[1] == 0) {	// present (zero)
				$present = 0;
				$checked = "checked";
			}
			else {					// absent (not zero)
				$present = 1;
				$checked = "";
			}
		
			printf("<td class='forms_ctr'>");
			printf("<input name='arg' type='hidden' value='change' />");
			printf("<input name='item' type='hidden' value='$row[0]' />");
			printf("<input name='entry' type='hidden' value='$xEntry' />");
			printf("<input name='category' type='hidden' value='$category' />");
			printf("<input name='event' type='hidden' value='$event' />");
			printf("<input name='comb' type='hidden' value='$comb' />");
			printf("<input name='catFrom' type='hidden' value='$catFrom' />"); 
			printf("<input name='catTo' type='hidden' value='$catTo' />");   
			printf("<input name='discFrom' type='hidden' value='$discFrom' />"); 
			printf("<input name='discTo' type='hidden' value='$discTo' />"); 
			printf("<input name='mDate' type='hidden' value='$mDate' />");    
			printf("<input type='checkbox' name='present' value='$present' $checked"
					. " onClick='document.change_present_$i.submit()' />\n");
			printf("</td>\n");
			
			
		
			 
			if($relay == FALSE)			// single event
			{
				printf("<td class='forms_right'><a name='$row[0]'></a>$row[2]</td>");		// startnumber
				
				printf("<td>$row[3] $row[4]</td>");		// name
				printf("<td class='forms_ctr'>" . $row[5] . "</td>");	// year
				printf("<td>$row[6]</td>");		// club name  
			   
				if ($comb==0 )  {               // show disziplines and date only if they are different
					if ($event==0){
						printf("<td>");
						printf($row['Name']);        // discipline  
						printf("</td>\n");        
					
						if ($mDate=='' || $mDate=='%'){
							if ($tage>1) {
								 printf("<td>");   
								 printf( date('d.m.Y', strtotime($row['Datum'])) );      // date   
								 printf("</td>\n");       
			}
						}   
					}
				}
				
			}
			else							// relay event
			{
				printf("<td class='forms_right'><a name='$row[0]'></a>$row[10]</td>");		// startnumber
				
                printf("<td>$row[2]</td>");        // relay 
				printf("<td>$row[3]</td>\n");		// club name
				 
			}

			printf("</form>\n");
			printf("</tr>\n");
		}
	}
	printf("</table>\n");
	mysql_free_result($result);   
    }
    else {
        AA_printErrorMsg($strErrMergedRound); 
    } 
    
}
?>

<script type="text/javascript">
<!--
	scrollDown();
//-->
</script>
<?php

$page->endPage();
?>
