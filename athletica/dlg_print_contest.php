<?php

/**********
 *
 *	dlg_print_contest.php
 *	---------------------
 *	
 */
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_dropdown.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

// get presets
$round = $_GET['round'];
if(!empty($_POST['round'])) {
	$round = $_POST['round'];
}

$print = 'no';
if(!empty($_GET['print'])) {
    $print = $_GET['print'];
}

// get number of athletes/relays with valid result
/*$result = mysql_query("
	SELECT
		COUNT(*)
	FROM
		serienstart AS ss
		, serie AS s
	WHERE s.xSerie = ss.xSerie
	AND s.xRunde = $round
");*/
$sql = "SELECT
			COUNT(*)
		FROM
			serienstart AS ss
		LEFT JOIN 
			serie AS s USING(xSerie)
		WHERE
			s.xRunde = ".$round.";";
$result = mysql_query($sql);
 
if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else
{
	$row = mysql_fetch_row($result);
	$present = $row[0];
	mysql_free_result($result);
}


// get nbr of heats
$result = mysql_query("
	SELECT
		COUNT(*)
	FROM serie
	WHERE xRunde = $round
");

if(mysql_errno() > 0) {		// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else {
	$row = mysql_fetch_row($result);
	$tot_heats = $row[0];
}
mysql_free_result($result);


// read round information
/*$result = mysql_query("
	SELECT
		DATE_FORMAT(r.Datum, '$cfgDBdateFormat')
		, TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
		, r.Bahnen
		, rt.Name
		, w.xWettkampf
		, k.Name
		, d.Name
		, d.Typ
		, r.QualifikationSieger
		, r.QualifikationLeistung
	FROM
		runde AS r
		, wettkampf AS w
		, kategorie AS k
		, disziplin_" . $_COOKIE['language'] . " AS d
	LEFT JOIN rundentyp AS rt
	ON r.xRundentyp = rt.xRundentyp
	WHERE r.xRunde = $round
	AND w.xWettkampf = r.xWettkampf
	AND k.xKategorie = w.xKategorie
	AND d.xDisziplin = w.xDisziplin
");*/

$mRounds= AA_getMergedRounds($round);
$sqlRound = '';
if (empty($mRounds)){
   $sqlRound = "= ". $round;  
}
else {
     $sqlRound = "IN ". $mRounds;  
}

$sql = "SELECT
			  DATE_FORMAT(r.Datum, '".$cfgDBdateFormat."')
			, TIME_FORMAT(r.Startzeit, '".$cfgDBtimeFormat."')
			, r.Bahnen
			, rt.Name
			, w.xWettkampf
			, k.Name
			, d.Name
			, d.Typ
			, r.QualifikationSieger
			, r.QualifikationLeistung
            , rt.Typ
            , rt.Wertung
            , w.Typ
		FROM
			runde AS r
		LEFT JOIN
			rundentyp_" . $_COOKIE['language'] . " AS rt USING(xRundentyp)
		LEFT JOIN
			wettkampf AS w ON(w.xWettkampf = r.xWettkampf)
		LEFT JOIN
			kategorie AS k USING(xKategorie)
		LEFT JOIN 
			disziplin_" . $_COOKIE['language'] . " AS d ON(d.xDisziplin = w.xDisziplin)
		WHERE
			r.xRunde  ".$sqlRound.";";
$result = mysql_query($sql);

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else
{   
	while($row = mysql_fetch_row($result)) {
         $catMerged .= $row[5] ." / ";
        }   
    $titel = substr($catMerged,0,-2);
    
    $result = mysql_query($sql);
    $row = mysql_fetch_row($result);
	$event = $row[4];				// event ID
	$tracks = $row[2];			// nbr of tracks

	$relay = AA_checkRelay($event);
	if($relay == FALSE) {		// single event
		$XXXPresent = $strAthletesPresent;
	}
	else {							// relay event
		$XXXPresent = $strRelaysPresent;
	}

	$page = new GUI_Page('print_contest');
	$page->startPage();
	$page->printPageTitle($strPrintHeats);

	$menu = new GUI_Menulist();
	$menu->addButton($cfgURLDocumentation . 'help/event/print_heats.html', $strHelp, '_blank');
	$menu->printMenu();

	$page->printSubTitle("$row[6], $titel");
?>
<form action='print_contest.php' method='post' target='_blank' name='qual' >
<input name='round' type='hidden' value='<?php echo $round; ?>' />
<input name='present' type='hidden' value='<?php echo $present; ?>' />
<input name='print' type='hidden' value='<?php echo $print; ?>' />  
<input name='d_Typ' type='hidden' value='<?php echo $row[7]; ?>' />   
<input name='w_Typ' type='hidden' value='<?php echo $row[12]; ?>' />   
<table class='dialog' id="regtable">
	<tr>
		<th class='dialog' colspan='2'><?php echo $row[3]; ?></th>
	</tr>
	<tr>
		<td class='dialog'><?php echo $strTime; ?></td>
		<th class='dialog'><?php echo $row[0] . ", " . $row[1]; ?></th>
	</tr>
	<tr>
		<td class='dialog'><?php echo $strNbrOfHeats; ?></td>
		<th class='dialog'><?php echo $tot_heats; ?></th>
	</tr>
	<tr>
		<td class='dialog'><?php echo $strPageBreakHeat; ?></td>
		<th class='dialog'><input type="checkbox" name="heatpagebreak" value="yes"></th>
	</tr>
	<tr>
		<td class='dialog'><?php echo $XXXPresent; ?></td>
		<th class='dialog'><?php echo $present; ?></th>
	</tr>
	<?php
	//
	// display text field for entering the number of attempts to be printed
	//
	if($row[7] == $cfgDisciplineType[$strDiscTypeJump]
		|| $row[7] == $cfgDisciplineType[$strDiscTypeJumpNoWind]
		|| $row[7] == $cfgDisciplineType[$strDiscTypeThrow])
	{
		?>
	<tr>
		<td class='dialog'><?php echo $strCountAttempts; ?>:</td>
		<th class='dialog'><input type="text" value="<?php echo $cfgCountAttempts[$row[7]];?>" id="countattempts" name="countattempts" size="3" onchange="addSelects()"></th>
	</tr> 
    <tr> 
        <td class='dialog'><?php echo $strOnlyBestResult; ?>:</td>     
        <th class='dialog'><input type='checkbox' name='onlyBest' value='y'/> 
                 
         </th>  
     </tr>  
     <?php
      if ($row[11] != 1){                    //show checkbox endEvent only when rt.Wertung not 1
           ?>
           <tr> 
        <td class='dialog'><?php echo $strEndEvent; ?>:</td>     
        <th class='dialog'><input type='checkbox' name='endEvent' id='endEvent' value='y' onclick="addTxtField()"  /> 
                 
         </th>  
     </tr> 
      <tr>
    <td class='dialog'><?php echo $strChangePos; ?></td> 
      <th class='dialog'>   
                             <select name='changePos1'> 
                             <option value="-">-</option>
                             <?php 
                                for ($i=1;$i<$cfgCountAttempts[$row[7]];$i++){    
                                         ?>
                                         <option value="<?php echo $i;?>"><?php echo $i;?></option>   
                                        <?php    
                                }
                                ?>  
                             </select>
                            <?php 
                             echo $strAnd;
                            ?>
                             <select name='changePos2'> 
                                <option value="-">-</option>
                             <?php 
                                for ($i=1;$i<$cfgCountAttempts[$row[7]];$i++){                                      
                                         ?>
                                         <option value="<?php echo $i;?>"><?php echo $i;?></option>   
                                        <?php    
                                }
                                ?>  
                             </select>      
      </th>
    </tr>     
     <?php
      }
     ?>    
     
     
     
       
    
    
     
    
		<?php
	} 
	?>
    
<?php    
    if (($$row[7] == $cfgDisciplineType[$strDiscTypeTrack])
            || ($row[7] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
            || ($row[7] == $cfgDisciplineType[$strDiscTypeRelay])
            || ($row[7] == $cfgDisciplineType[$strDiscTypeDistance]))
        {
	        if($row[2] > 0) {		// discipline run in tracks
        ?>
	        <tr>
		        <td class='dialog'><?php echo $strNbrOfTracks; ?></td>
		        <?php
			        $dd = new GUI_ConfigDropDown('tracks', 'cfgTrackOrder', $tracks, '', true);
		        ?>
	        </tr>
        <?php
	        }
        }

	$qual_top = $row[8];
	$qual_perf = $row[9];
    
	mysql_free_result($result);
}

// show qualification form if another round follows
$nextRound = AA_getNextRound($event, $round);
$combined = AA_checkCombined($event);
$teamsm = AA_checkTeamSM($event);      

$quali = TRUE;
if ($row[10] == 'S' || $row[10] == 'O'){
    $quali = FALSE;                                     // double round "serie"" or "(ohne)"  --> no need of qualification 
}

if($nextRound > 0 && !$combined && !$teamsm && $quali)		// next round found
{
	/*$result = mysql_query("
		SELECT
			rt.Name
		FROM
			runde AS r
		LEFT JOIN rundentyp AS rt
		ON r.xRundentyp = rt.xRundentyp
		WHERE r.xRunde = $nextRound
	");*/
	$sql = "SELECT
				rt.Name
			FROM
				runde AS r
			LEFT JOIN
				rundentyp_" . $_COOKIE['language'] . " AS rt USING(xRundentyp)
			WHERE
				r.xRunde = ".$nextRound.";";
	$result = mysql_query($sql);

	if(mysql_errno() > 0)		// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		$row = mysql_fetch_row($result);
?>
	<tr>
		<td class='dialog' colspan='2'><hr />
		<script type="text/javascript">
<!--
	function calcTotal()
	{      
		if((isNaN(document.qual.qual_top.value) == true)
			|| ((parseInt(document.qual.qual_top.value)) < 0))
		{
			document.qual.qual_top.value = 0;
		}

		if((isNaN(document.qual.qual_perf.value) == true) 
			|| ((parseInt(document.qual.qual_perf.value)) < 0))
		{
			document.qual.qual_perf.value = 0;
		}

		document.getElementById("total").firstChild.nodeValue =
			(parseInt(<?php echo $tot_heats; ?>)
				* parseInt(document.qual.qual_top.value))
			+ parseInt(document.qual.qual_perf.value);
	}
//-->
		</script>
		</td>
	</tr>

	<tr>
		<th class='dialog' colspan='2'>
			<?php echo $strQualification . " " . $row[0]; ?></th>
	</tr>
	<tr>
		<td class='dialog'><?php echo $strQualifyTop; ?></td>
		<td class='forms'>
			<input name='next_round' type='hidden' value='<?php echo $nextRound; ?>' />
			<input class='nbr' name='qual_top' type='text' maxlength='4'
				value='<?php echo $qual_top; ?>' onChange='calcTotal()' />
		</td>
	</tr>
	<tr>
		<td class='dialog'><?php echo $strQualifyPerformance; ?></td>
		<td class='forms'>
			<input class='nbr' name='qual_perf' type='text' maxlength='4'
				value='<?php echo $qual_perf; ?>' onChange='calcTotal()' />
		</td>
	</tr>
	<tr>
		<td class='dialog'><?php echo $strTotal; ?></td>
		<th class='dialog' id='total'>
			<?php echo ($tot_heats * $qual_top) + $qual_perf; ?>
		</th>
	</tr>
    <tr>
    

<?php
		mysql_free_result($result);
	}		// ET DB error
}		// next round found
?>
</table>

<p />

<table>
	<tr>
		<td>
			<button type='submit'>
				<?php echo $strTerminateSeeding; ?>
		  	</button>
		</td>
		<td>
			<button name='reset' type='reset' onClick='window.open("event_heats.php?round=<?php echo $round; ?>", "main")'>
			<?php echo $strBack; ?>
			</button>
		</td>
	</tr>
</table>
</form>
<?php 
   $finalAfterAttempts =  ceil($cfgCountAttempts[$row[7]]/ 2);
   ?>

 <!-- this select box is used for a IE trick ( in function base_search_show() ) -->
    <span id="sp_CountFinalist" style="visibility:hidden"><?php echo $strCountFinalist; ?></span>
    <input type="text" value="<?php echo $cfgFinalist; ?>" id="orig_CountFinalist" style="visibility:hidden" name="orig_CountFinalist" size="3">     
           
     <span id="sp_finalAfterAttempts" style="visibility:hidden"><?php echo $strCountFinalAfterXEvents; ?></span>
    <input type="text" value="<?php echo $finalAfterAttempts; ?>" id="orig_finalAfterAttempts" style="visibility:hidden" name="countFinalAfter" size="3" onchange="return checkCount()" >           
            
                      
  <script type="text/javascript">
<!--
               //new Option() kennt vier Parameter von denen die drei letzten Parameter optional sind.
//1. text = angezeigter Text in der Liste
//2. value = zu übertragender Wert der Liste (optional)
//3. defaultSelected = true übergeben, wenn der Eintrag der defaultmäßig vorselektierte Eintrag sein soll, sonst false (optional)
//4. selected = true übergeben, wenn der Eintrag selektiert werden soll (optional)
     function addSelects () {
        
          var i = 0;
          
          var len1 = document.qual.changePos1.options.length;    
          var len2 = document.qual.changePos2.options.length;      
          for(i=0;i<len1;i++) {
                document.qual.changePos1.options[document.qual.changePos1.length - 1] = null;
          }
          for(i=0;i<len2;i++) {    
                document.qual.changePos2.options[document.qual.changePos2.length - 1] = null;
          }
          
          newOption = new Option('-', '-', false, false);
          document.qual.changePos1.options[document.qual.changePos1.length] = newOption;
          for(i=0;i<document.getElementById("countattempts").value;i++) {   
                 
                  if (i==Math.ceil(document.getElementById("countattempts").value/2))  {                           
                        newOption = new Option(i, i, false, false);
                        document.qual.changePos1.options[document.qual.changePos1.length] = newOption;   
                        if (document.getElementById("endEvent").checked) {
                           var s = i + 1;  
                        document.qual.changePos1[s].selected = true;      
                        }  
                  }  
                  else {
                        newOption = new Option(i, i, false, false);
                        document.qual.changePos1.options[document.qual.changePos1.length] = newOption;   
                  }             
                 
          }
          newOption = new Option('-', '-', false, false);
          document.qual.changePos2.options[document.qual.changePos2.length] = newOption;
          for(i=0;i<document.getElementById("countattempts").value;i++) {                      
                  newOption = new Option(i, i, false, false);
                  document.qual.changePos2.options[document.qual.changePos2.length] = newOption; 
          }  
          document.getElementById("orig_finalAfterAttempts").value = Math.ceil(document.getElementById("countattempts").value / 2);           
    }                 
    
    
    function addTxtField() {  
        
             var test =  document.getElementById("endEvent").checked;
             if  (test == true){
                 
                    var tbl = document.getElementById("regtable");  
                  
                    var tr = tbl.insertRow(8);   
                    var TD1 = document.createElement("td");     
                    var TD2 = document.createElement("th");   
                  
                    TD2.setAttribute('class','dialog');                   
                    var check1 = document.getElementById("sp_CountFinalist").cloneNode(true);      
                    var check2 = document.getElementById("orig_CountFinalist").cloneNode(true);  
                    
                    check1.style.visibility = "visible";
                    check2.style.visibility = "visible";  
                                       
                    TD1.appendChild(check1);                                             
                    tr.appendChild(TD1);   
                    TD2.appendChild(check2);
                    tr.appendChild(TD2);                        
               
                    var tr = tbl.insertRow(9);   
                    var TD1 = document.createElement("td");  
                    var TD2 = document.createElement("th"); 
                    
                    TD2.setAttribute('class','dialog');                        
                    var check1 = document.getElementById("sp_finalAfterAttempts").cloneNode(true);      
                    var check2 = document.getElementById("orig_finalAfterAttempts").cloneNode(true);                      
                    check1.style.visibility = "visible";
                    check2.style.visibility = "visible";   
                   
                    TD1.appendChild(check1);                     
                    tr.appendChild(TD1);                       
                    TD2.appendChild(check2);
                    tr.appendChild(TD2);    
                  
              }
              else {                          
                    var tbl = document.getElementById("regtable");                
                    var tr = tbl.deleteRow(8);  
                    var tr = tbl.deleteRow(8);  
              }
              
              addSelects();
                           
    }
    
    function checkCount() {   
               if (document.getElementById("orig_finalAfterAttempts").value < document.getElementById("countattempts").value){
                   return true;
               }
               alert("<?php echo $strWarnFinalAttempt; ?>");
               return false;
    }
    
    
   </script>
    

<?php
$page->endPage();



