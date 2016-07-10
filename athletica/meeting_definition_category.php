<?php
/**********
 *
 *	meeting_definition_category.php
 *	-------------------------------
 *	
 */   	 
require('./convtables.inc.php');

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_select.lib.php');
require('./lib/cl_timetable.lib.php');

require('./lib/meeting.lib.php');
require('./lib/common.lib.php');    

              
if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$cCategory = 0;
$category = 0;
if(!empty($_POST['cat'])) {
	$category = $_POST['cat'];
}
else if(!empty($_GET['cat'])) {
	$category = $_GET['cat'];
}  
  
 $svmcat= 0;
 if(!empty($_POST['svmcat'])){
    $svmcat = $_POST['svmcat'];   
 }  
 
 $ukc_meeting = AA_checkMeeting_UKC() ; 
 
 $nulltime_exist = false;     
//
// Process changes to meeting data
//         

// change all events of a category
if ($_POST['arg']=="change_cat")
{  
	AA_meeting_changeCategory();
     
}
// change event data
else if ($_POST['arg']=="change_event")
{
	AA_meeting_changeEvent();
}
// change event data
else if ($_POST['arg']=="change_event_discipline")
{
	AA_meeting_changeEventDiscipline();
}
        
// change type of combined event (needed for bestlist)
else if($_POST['arg']=="change_combtype"){
	
	if(!empty($_POST['combinedtype'])){
		
		if($_POST['combinedtype'] == "-"){ $_POST['combinedtype'] = 0; }
		
		// check if there is already such a combined event for this category
		$res = mysql_query("SELECT * FROM wettkampf
					WHERE xKategorie = ".$_POST['cat']."
					AND Mehrkampfcode = ".$_POST['combinedtype']."
					AND xMeeting = ".$_COOKIE['meeting_id']."
					");
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			if(mysql_num_rows($res) > 0){
				AA_printErrorMsg($strDoubleCombinedEvent);
			}else{  
			   
				  mysql_query("LOCK TABLES kategorie READ, disziplin_de READ, disziplin_de AS d READ, disziplin_fr READ, disziplin_fr AS d READ, disziplin_it READ, disziplin_it AS d READ"
					. ", wettkampf WRITE, wettkampf AS w WRITE");
			
				// delete combined event
				
				mysql_query("DELETE FROM wettkampf                                     
							 WHERE xKategorie = ".$_POST['cat']."
					AND Mehrkampfcode = ".$_POST['comb']."
					AND xMeeting = ".$_COOKIE['meeting_id']."
					");
			
			
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else {  
						 // add new combined event
						AA_meeting_addCombinedEvent($_SESSION['meeting_infos']['Startgeld']/100,$_SESSION['meeting_infos']['Haftgeld']/100);                                                                                                  
				}
				
			 mysql_query("UNLOCK TABLES");    
			}
		}
		
	}
	
}
// change type of combined event (needed for bestlist)
else if($_POST['arg']=="change_svmcat"){  
  
	if(!empty($_POST['svmcategory'])){
        
         // check if there is already such a svm event for this category
        $res = mysql_query("SELECT * FROM wettkampf
                    WHERE xKategorie = ".$_POST['cat']."
                    AND xKategorie_svm = ".$_POST['svmcategory']."
                    AND xMeeting = ".$_COOKIE['meeting_id']."
                    ");
        if(mysql_errno() > 0) {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }else{
            if(mysql_num_rows($res) > 0){
                AA_printErrorMsg($strDoubleCombinedEvent);
            }else{  
               
                  mysql_query("LOCK TABLES kategorie READ, disziplin_de READ, disziplin_fr READ, disziplin_it READ, kategorie_svm AS ks READ, wettkampf as w READ, runde as r READ, rundentyp_de READ, rundentyp_fr READ, rundentyp_it READ  "
                    . ", disziplin_de as d READ, disziplin_fr as d READ , disziplin_it as d READ , wettkampf WRITE, wettkampf AS w WRITE, runde WRITE");  
    
                  $sql="SELECT 
                            r.xRunde, w.xWettkampf  
                        FROM
                            wettkampf as w
                            LEFT JOIN runde as r On (w.xWettkampf = r.xWettkampf)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d ON (d.xDisziplin = w.xDisziplin)
                        WHERE
                            w.xKategorie = ". $category ."
                            AND r.xRunde IS NOT NULL
                            AND w.xKategorie_svm = " .$_POST['oldsvm']." 
                            AND xMeeting = ".$_COOKIE['meeting_id']." 
                        ORDER BY d.Anzeige";
                 
                  $result=mysql_query($sql);
            
                  if(mysql_errno() > 0) {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                  }
                  else {
            
                        while ($row=mysql_fetch_row($result)) {
                       
                            $qdel="DELETE FROM runde                                     
                                WHERE xRunde = " .$row[0];
                                
                            mysql_query($qdel);
                            if(mysql_errno() > 0) {
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }  
                       }   
                  }    
            
      
                // delete svm event
                
                mysql_query("DELETE FROM wettkampf                                     
                             WHERE xKategorie = " .$category ."
                                AND xKategorie_svm = ".$_POST['oldsvm']."                    
                    AND xMeeting = ".$_COOKIE['meeting_id']."
                    ");     
               
                if(mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else {  
                         // add new svm event   
                       
                        AA_Meeting_getEventType(); 
                        
                        $_POST['date']=$_SESSION['meeting_infos']['DatumVon'];  
                        $_POST['svm'] =  $_POST['svmcategory'];  
                        
                        AA_meeting_addSVMEvent($_SESSION['meeting_infos']['Startgeld']/100,$_SESSION['meeting_infos']['Haftgeld']/100); 
                        
                }
                
             mysql_query("UNLOCK TABLES");  
            
             if ($GLOBALS['AA_ERROR'] != '')
                {
                 AA_printErrorMsg($GLOBALS['AA_ERROR']);  
                }  
            }
        } 
	}  
}
// add a new combined contest
elseif($_POST['arg']=="add_combtype"){
			 
	AA_meeting_addCombinedEvent($_SESSION['meeting_infos']['Startgeld']/100,$_SESSION['meeting_infos']['Haftgeld']/100);
	
}

// add a new svm contest
elseif($_POST['arg']=="add_svmcat"){
     
    AA_Meeting_getEventType(); 
    
    $_POST['date']=$_SESSION['meeting_infos']['DatumVon']; 
    
    $_POST['svm']  =  $_POST['svmcategory'];
   
    AA_meeting_addSVMEvent($_SESSION['meeting_infos']['Startgeld']/100,$_SESSION['meeting_infos']['Haftgeld']/100);    
       
    if ($GLOBALS['AA_ERROR'] != '')
        {
        AA_printErrorMsg($GLOBALS['AA_ERROR']);  
    }
    
}
// change formula for a certain combtype
elseif($_POST['arg']=="change_formula"){ 
	if(isset($_POST['nocat'])){      
		AA_meeting_changeFormula();
	} else {     
		AA_meeting_changeCategory($_POST['item']); 
	}	
}
// add a disciplin for a combined contest
elseif($_POST['arg']=="new_discipline"){   
	
	if(!empty($_POST['combtype'])){  
		$t = $_POST['combtype'];
		
		// get short name
		$res = mysql_query("SELECT Kurzname, Name FROM disziplin_" . $_COOKIE['language'] . " WHERE Code = $t");
		$row = mysql_fetch_array($res);
		$sName = $row[0];
        if ($t == 408){                 // UBS Kids Cup
             $sName = $row[1];  
        }
		if (empty($_POST['punktetabelle'])){
		    $_POST['punktetabelle']=0; 
		}
		
		mysql_query("INSERT INTO wettkampf SET
				Typ = ".$cfgEventType[$strEventTypeSingleCombined]."
				, Info = '$sName'
				, xKategorie = ".$_POST['cat']."
				, xDisziplin = ".$_POST['discipline']."
				, xMeeting = ".$_COOKIE['meeting_id']."
				, Punktetabelle = ".$_POST['punktetabelle']." 
				, Mehrkampfcode = $t   
				, Mehrkampfreihenfolge = 127");  
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
        
        $_POST['conv_changed'] = 'yes';
        $_POST['conv']= $_POST['punktetabelle'];
        $_POST['type'] = $cfgEventType[$strEventTypeSingleCombined];
        AA_meeting_changeCategory($t);
	}
}
// remove a discipline
elseif($_POST['arg']=="delete_discipline"){
	
	AA_meeting_deleteEvent();
	
}
// configure rounds with start time
elseif($_POST['arg']=="change_starttime"){
       
	// date, item, roundtype, hr, min, g     
	if(empty($_POST['g'])){
		$st = $_POST['starttime'];
	}else{
		$st = $_POST['starttime'][$_POST['g']];
	}    
       
    if($_POST['wTyp'] > $cfgEventType[$strEventTypeSingleCombined]
                && $_POST['wTyp']!= $cfgEventType[$strEventTypeTeamSM])         // not single event
            {
              //$it=$_POST['item'];                                                      
              if ($_POST['dTyp'] == $cfgDisciplineType[$strDiscTypeTrack] ||
                        $_POST['dTyp'] == $cfgDisciplineType[$strDiscTypeTrackNoWind] ||  
                        $_POST['dTyp'] == $cfgDisciplineType[$strDiscTypeDistance] ||  
                        $_POST['dTyp'] == $cfgDisciplineType[$strDiscTypeRelay] )  
               {                                                                    // discipline type track
                    $_POST['roundtype'] = 6; // round type "Serie"  
              }   
              else {                                                                // discipline type tech
                  $_POST['roundtype'] = 9; // round type "ohne" 
              } 
	           
            }
    else {
         if ($_POST['wTyp'] ==  $cfgEventType[$strEventTypeTeamSM]){
               $_POST['roundtype'] = 9; // round type "(ohne)"  
         }
         else {
             $_POST['roundtype'] = 8; // round type "Mehrkampf"  
         }
         
    } 
    
     $svm='';
     if  (!empty($_POST['svmcat'])){
        $svm = $_POST['svmcat']; 
     }                
     elseif (!empty($_POST['svmcategory'])){
            $svm = $_POST['svmcategory']; 
     }  
                        
     if (!empty($svm)){  
        // get short name  
        $res = mysql_query("SELECT ks.code FROM kategorie_svm AS ks WHERE ks.xKategorie_svm = $svm"); 
        $row = mysql_fetch_array($res);
        $_POST['svmCode'] = $row[0];              
          
        if(mysql_errno() > 0) {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());                   
        }
        
        // svm code not defined
        if (!isset($cfgSVM[$_POST['svmCode']])){  
            $GLOBALS['AA_ERROR'] = $GLOBALS['strErrDiscNotDefSVM'];  
        }    
     }
    
   
    $st=trim($st);
    if (strlen($st) < 4 && $st == 0){
       $st=sprintf("%04d", $st); 
    } 
    elseif (strlen($st) == 1) {
            $st=sprintf("%02d", $st)."00";
    }
     elseif (strlen($st) == 2) {
            $st.="00";
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
			LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d USING(xDisziplin)
		WHERE w.xWettkampf = " . $_POST['item']
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
	
     
   	$groupexist=false;
 
    $res_grp = mysql_query("SELECT  
								r.xRunde
							FROM
								runde as r
							WHERE	xWettkampf = $_POST[eventgroup]
							AND r.Gruppe ='' ");   
	   
	if (mysql_num_rows($res_grp)>0){
		$row_grp=mysql_fetch_array($res_grp);
		$groupexist=true; 
	}   
  
	if($_POST['round'] > 0  || $groupexist){
		$tt = new Timetable();
	  	if  ($groupexist){
	  	    $tt->round=$row_grp[0];
	  	    $tt->grp=$_POST['eventgroup']; 
	   	}
		$tt->change();
	}else{ 
        if (!empty($_POST['min'])){ 
		    $tt = new Timetable();  
		    $tt->add();
        }
        
	}
    
  if (!empty($GLOBALS['AA_ERROR'])){                
        AA_printErrorMsg($GLOBALS['AA_ERROR']);    
  }
}
// request to change last event of combined
elseif($_POST['arg'] == "change_lastdisc"){
	
	// first check if there are seeded rounds in current and next 'last discipline'
	list($cCat,$cCode) = split("_",$_POST['lastround']);
	
	// fetch round from current last discipline (cCat, cCode) and rounds from selectet last discipline
	/*$res = mysql_query("SELECT 
					r.xRunde
				FROM
					runde as r
					, wettkampf as w
				WHERE
					r.xWettkampf = w.xWettkampf
				AND	(w.xWettkampf = ".$_POST['item']."
					OR	(w.Mehrkampfcode = $cCode
						AND w.xKategorie = $cCat
						AND w.Mehrkampfende = 1))
				");*/
	$sql = "SELECT
				r.xRunde
			FROM
				runde AS r
			LEFT JOIN
				wettkampf AS w USING(xWettkampf)
			WHERE
				w.xWettkampf = ".$_POST['item']."
			OR 
				(w.Mehrkampfcode = ".$cCode."
			AND
				w.xKategorie = ".$cCat."
			AND
				w.Mehrkampfende = 1);";
	$res = mysql_query($sql);
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		
		$rounds = "";
		while($row = mysql_fetch_array($res)){
			
			if(AA_utils_checkReference("serie", "xRunde", $row[0]) != 0)
			{
				$error = $GLOBALS['strRound'] . $GLOBALS['strErrStillUsed'];
				break;
			}
			
			$rounds .= $row[0].",";
		}
		
		if(empty($error)){
			
			// ok, no seeded rounds -> delete all
			if(!empty($rounds)){
				mysql_query("DELETE FROM runde WHERE xRunde IN (".substr($rounds,0,-1).")");
				if(mysql_errno() > 0){
					AA_printErrorMsg(mysql_errno().": ".mysql_error());
				}
			}
		
			// now set last discipline info
			if($_POST['last'] == 1){ // disable last discipline mode
			   	mysql_query("UPDATE wettkampf SET Mehrkampfende = 0 WHERE xWettkampf = ".$_POST['item']);
			}else{
				mysql_query("UPDATE wettkampf SET Mehrkampfende = 0 WHERE xKategorie = $cCat AND Mehrkampfcode = $cCode");
				mysql_query("UPDATE wettkampf SET Mehrkampfende = 1 WHERE xWettkampf = ".$_POST['item']);
			} 			
		}else{
			AA_printErrorMsg($error);
		}
		
	}
	
}
  
// wish to create a new combined contest (without creating a discipline first)
$cNewCombined = false;
if($_GET['arg']=="create_combined"){
	$cNewCombined = true;
	$cCategory = $category;
}

// wish to create a new svm contest (without creating a discipline first)
$cNewSVM = false;
if($_GET['arg'] == "create_svm" || $_POST['svm'] == "create_svm"){
    $cNewSVM = true;
    $cCategory = $category;
}
    
// Check if any error returned from DB
if(mysql_errno() > 0) {
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

 
/***************************
 *
 *		General meeting data
 *
 ***************************/

$page = new GUI_Page('meeting_definition_category');
$page->startPage();
?>

<script type="text/javascript">
<!--
	// preselect formula for new disciplines
	function selectFormula()
	{
		var oDisc = document.newdisc.elements[0];
		var oFormula = document.newdisc.elements[9];

		if(document.newdisc.discipline.value == 'new')
		{
			window.open("admin_disciplines.php", "_self");
		}
		else if(oFormula.name = 'formula')
		{
			var s = oDisc.selectedIndex;
			for(i = 0; i < oFormula.length; i++)
			{
				if(oFormula.options[i].text == oDisc.options[s].text)
				{
					oFormula.selectedIndex = i;
				}
			}
		}
	}    
	

//-->
</script>

<?php
/*****************************************
 *
 *	 Events: disciplines per categories	
 *
 *****************************************/

// order descendend by Mehrkampfcode else the selection of category type
// will show the wrong information
$sql = "SELECT
			  w.xWettkampf
			, w.xKategorie
			, w.Typ
			, w.Punktetabelle
			, w.Punkteformel
			, k.Name
			, d.Name
			, w.Mehrkampfcode
			, w.xKategorie_svm
			, d.Typ
			, w.Windmessung
			, w.Zeitmessung
			, w.ZeitmessungAuto
			, w.Info
			, w.Mehrkampfende
			, w.Mehrkampfreihenfolge
			, d.Code
			, k.Geschlecht             
		FROM
			wettkampf AS w
		LEFT JOIN
			kategorie AS k USING(xKategorie)
		LEFT JOIN
			disziplin_" . $_COOKIE['language'] . " AS d ON(w.xDisziplin = d.xDisziplin)
		WHERE
			w.xMeeting = ".$_COOKIE['meeting_id']."
		AND
			w.xKategorie = ".$category."
		ORDER BY
              w.Typ
            , w.xKategorie_svm
			 , w.Mehrkampfcode DESC
			, w.Mehrkampfreihenfolge
            
			, d.Anzeige;";
   
$result = mysql_query($sql); 

      
if(mysql_errno() > 0) {	// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else			// no DB error
{     
	// display list
	$i=0;
	$k=0;
	$c=0;           // count of combined disciplines
	$comb=0;		// combined code
	$cType = 0;
	$cGroups = array();	// combined groups
	$tsm = 0;	// count team sm disc
    $cfgSVM_arr = array();
    $keep_key = 0;         // keep the key for the svm disciplin with nulltime
                     	
	while ($row = mysql_fetch_row($result))
	{  
        if ($row[7] > 0){
            $cNewCombined = true;
        }   
        if ($i==0){
            if (!empty($row[8])){
                $svm=$row[8];  
        
                // get short name
                $res = mysql_query("SELECT ks.code FROM kategorie_svm AS ks WHERE ks.xKategorie_svm = $svm"); 
                $row_svm = mysql_fetch_array($res);
                $svmCode = $row_svm[0];              
          
                if(mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());                   
                }
                                
                if (isset($cfgSVM[$svmCode])) {  
                    $cfgSVM_arr = $cfgSVM[$svmCode];   
                                    
                    if (isset($cfgSVM[$svmCode."_NT"])){   
                        $cfgSVM_arr_NT = $cfgSVM[$svmCode."_NT"]; 
                    }          
                }else{  
                    $GLOBALS['AA_ERROR'] = $GLOBALS['strErrDiscNotDefSVM'];  
                }            
            }
        } 
        
        foreach ($cfgSVM_arr as $key => $val){   
            if ($val == $row[16]){
                $keep_key=$key;  
                break;
            }
        }     
        
		$punktetabelle = $row[3];
		
		// check on combined event order and set if unset
		if($row[15] == 0 && $row[2] == $cfgEventType[$strEventTypeSingleCombined] && $row[7] > 0){
			$tmp = $row[7];			
			if($tmp==394 && ($row[17]=='m' || $row[17]=='M')){
				$tmp = 3942;
			}
			
			$pos = $cfgCombinedWO[$cfgCombinedDef[$tmp]];
			$pos = array_keys($pos, $row[16]);
			if(count($pos) > 0){
				$pos = $pos[0]+1;
			}else{
				$pos = 127; // maximum
			}
			
			mysql_query("UPDATE wettkampf SET
					Mehrkampfreihenfolge = $pos
				WHERE
					xWettkampf = ".$row[0]);
		   
			
		}
		
		if($k!=$row[1])	            // first row: show category headerline
		{   
			//
			//	Headerline category
			//
            
            
			
			$cType = $row[2];
			$cCategory = $row[1];

			$page->printPageTitle($row[5]);
            
        }
        
        if($row[8] != $svmCat_keep){      
        
            if ($i > 0){                
                    ?>
                     </table></table><br/>
                    <?php
            } 
            
?>
<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strEventType; ?></th>
<?php
			if($row[2] > $cfgEventType[$strEventTypeSingleCombined]
				&& $row[2] != $cfgEventType[$strEventTypeTeamSM]) {		// not single event
				
?>
	<th class='dialog'><?php echo $strConversionTable; ?></th>
<?php
			}           
?>
</tr>  
<tr>
	<form action='meeting_definition_category.php' method='post' name='cat_<?php echo $row[8]; ?>'>
	<input name='arg' type='hidden' value='change_cat' />
	<input name='conv_changed' type='hidden' value='no' />
	<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
    <input name='wTyp' type='hidden' value='<?php echo $row[2]; ?>' />  
    <input name='svm' type='hidden' value='<?php echo $row[8]; ?>' />  
<?php
			// event type drop down
			$dd = new GUI_ConfigDropDown('type', 'cfgEventType', $row[2], "document.cat_$row[8].submit()");
         ?>
    </form> 
    <?php
    ?>
    <form action='meeting_definition_category.php' method='post' name='conv_<?php echo $row[8]; ?>'>  
        <input name='arg' type='hidden' value='change_cat' />
        <input name='conv_changed' type='hidden' value='yes' />
        <input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
        <input name='wTyp' type='hidden' value='<?php echo $row[2]; ?>' />  
        <input name='type' type='hidden' value='<?php echo $row[2]; ?>' />   
        <input name='svm' type='hidden' value='<?php echo $row[8]; ?>' />  
       <?php    
			// conversion table drop down
			if($row[2] > $cfgEventType[$strEventTypeSingleCombined]
				&& $row[2] != $cfgEventType[$strEventTypeTeamSM]) 		// not single event
			{
				
                $dd = new GUI_ConfigDropDown('conv', 'cvtTable', $row[3], "conv_$row[8].submit()"); 
			}
?>
	</form>
</tr>
</table>
<?php
			if($row[2] > $cfgEventType[$strEventTypeSingleCombined]
				&& $row[2] != $cfgEventType[$strEventTypeTeamSM]){
                    
				?>
<br>
<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strSvmCategory; ?></th>
</tr>
<tr>
	<td class='dialog'>
		<form action='meeting_definition_category.php' method='post' name='svmcat_<?php echo $row[8]; ?>'>
		<input name='arg' type='hidden' value='change_svmcat' />
		<input name='conv_changed' type='hidden' value='no' />
		<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
        <input name='oldsvm' type='hidden' value='<?php echo $row[8]; ?>' />   
        <input name='wTyp' type='hidden' value='<?php echo $row[2]; ?>' /> 
		<select name="svmcategory" onchange="document.svmcat_<?php echo $row[8]; ?>.submit()">
			<option value="-">-</option>
				<?php
				$res_comb = mysql_query("select xKategorie_SVM, Name from kategorie_svm ORDER BY Code");
				if(mysql_errno() > 0) {	// DB error
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}else{
					while($row_comb = mysql_fetch_array($res_comb)){ 
						if($row[8] == $row_comb[0]){
							$sel = "selected";
						}else{
							$sel = "";
						}
						?>
			<option value="<?php echo $row_comb[0] ?>" <?php echo $sel ?>><?php echo $row_comb[1] ?></option>
						<?php
					}
				}
                
                
				?>
		</select>
		</form>
	</td>
</tr>
</table>
          

				<?php
			}
			// conversion formula drop down
			if($row[2] > $cfgEventType[$strEventTypeSingleCombined]
				&& $row[2] != $cfgEventType[$strEventTypeTeamSM]) 		// not single event
			{
?>
<p/>

<table class='dialog'>  
<tr>   
	<td class='dialog'>
		<table >
            <tr>
               <th class='dialog'><?php echo "$strDiscipline"  ?></th>  
                <th class='dialog'><?php echo "$strConversionFormula"  ?></th>  
                <th class='dialog'><?php echo " $strDate "; ?></th>   
                <th class='dialog'><?php echo "$strTime"  ?></th> 
                <th class='dialog'><?php echo "$strDiscipline"  ?></th>  
                <th class='dialog'><?php echo "$strConversionFormula"  ?></th>  
                <th class='dialog'><?php echo " $strDate "; ?></th>   
                <th class='dialog'><?php echo "$strTime"  ?></th>   
            </tr>
            <tr>
<?php
			}	// ET single event
			$k=$row[1];
            $svmCat_keep = $row[8];                  
		}

		// conversion formula drop down
		if($row[2] > $cfgEventType[$strEventTypeSingleCombined] 
			&& $row[2] != $cfgEventType[$strEventTypeTeamSM]) 		// not single event
		{   
			//
			// Print disciplines
			//
			if($i % 2 == 0 ) {		// even row number
				$rowclass='even';
			}
			else {	// odd row number
				$rowclass='odd';
			}
            
			if($i % 2 == 0) {		// new row after three events
				if ($i != 0 ) {
					printf("</tr>");	// terminate previous row
				}
				printf("<tr class='$rowclass'>");
			}
			$i++;    
        
            $hclass='';  
            if  (!is_null($cfgSVM_arr_NT) ) {  
                if ($cfgSVM_arr_NT[$keep_key] == '0000'){   
                     $hclass='highlight_nulltime_red'; 
                     $nulltime_exist = true;  
                }
            } 
        
?>
		<form action='meeting_definition_category.php' method='post' name='event_<?php echo $row[0]; ?>'> 
			<td class='<?php echo $hclass; ?>'><?php echo $row[6]; ?></td>      
			<td class='forms'>
				<input name='arg' type='hidden' value='change_event' />
				<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
				<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
                <input name='dTyp' type='hidden' value='<?php echo $row[9]; ?>' /> 
                <input name='wTyp' type='hidden' value='<?php echo $row[2]; ?>' />  
				<input name="nocat" type="hidden" value="1"/>
			
			<?php
			if($row[3]==$cvtTable[$strConvtableRankingPoints] || $row[3]==$cvtTable[$strConvtableRankingPointsU20]){
				?>
				<input type="text" name="formula" value="<?php echo $row[4]?>" style="width: 45px;" onchange="document.event_<?php echo $row[0]; ?>.arg.value='change_formula'; 
					document.event_<?php echo $row[0]; ?>.submit()"/>     			  
				<?php
			} else {
				$dropdown = new GUI_Select('formula', 1, "document.event_$row[0].submit()");
				foreach($cvtFormulas[$row[3]] as $key=>$value)
				{
					$dropdown->addOption($key, $key);
					if($row[4] == $key) {
						$dropdown->selectOption($key);
					}
				}
				$dropdown->printList();
			}
?>
			</td>  
             </form> 
               
            <form action='meeting_definition_category.php' method='post' name='event_time_<?php echo $row[0]; ?>'> 
                 
<?php
       
            $res_c = mysql_query("SELECT
                                        TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
                                        , r.Datum
                                        , r.xRunde
                                  FROM
                                        runde as r
                                        LEFT JOIN wettkampf AS w ON (r.xWettkampf = w.xWettkampf)
                                  WHERE 
                                        r.xWettkampf = ".$row[0]." 
                                        AND w.xMeeting = ".$_COOKIE['meeting_id']);    
                   
            $time = mysql_fetch_array($res_c);                   
               
            $dd = new GUI_DateDropDown($time[1]);
?>              
            
            <input name='arg' type='hidden' value='change_starttime' /> 
            <input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
                <input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
                <input name='dTyp' type='hidden' value='<?php echo $row[9]; ?>' /> 
                <input name='wTyp' type='hidden' value='<?php echo $row[2]; ?>' />  
                <input name='svmcat' type='hidden' value='<?php echo $row[8]; ?>' />  
                 <input name='round' type='hidden' value='<?php echo $round; ?>' />   
                  <input name='dCode' type='hidden' value='<?php echo $row[16]; ?>' />  
                <input name="nocat" type="hidden" value="1"/>   
            <td class='forms' colspan="1">
                <input type="text" size="5" maxlength="5" value="<?php echo $time[0] ?>"
                onchange="document.event_time_<?php echo $row[0] ?>.arg.value='change_starttime'; 
                    document.event_time_<?php echo $row[0] ?>.round.value='<?php echo $time[2] ?>';
                    document.event_time_<?php echo $row[0] ?>.submit()" 
                name="starttime" id="starttime_<?php echo $oldx."_".$oldg ?>">
            </td>  
		</form>
<?php           
		}
         
        
        	// ET single event
		
		/*
		*
		*	Special combined events
		***********************************************************************************************************
		*/
		// print information for extendend combined contest
		// first print header for different comb-types
		if($row[2] == $cfgEventType[$strEventTypeSingleCombined]){     
			if($comb != $row[7]){ 
				$c=0;  
				
				if($comb != 0){ //show entry for add new disc and close disc table
							  
					?>
		<tr>
			<form action='meeting_definition_category.php' method='post' name='newdiscipline_<?php echo $comb ?>'>
			<input name='arg' type='hidden' value='new_discipline' />
			<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
			<input name='combtype' type='hidden' value='<?php echo $comb; ?>' />  
			<?php $dd = new GUI_DisciplineDropDown(0, true, false, $keys, "document.newdiscipline_$comb.submit()"); ?>
			<td class='dialog' colspan='6'></td>
			</form>
		</tr>
	</table>
					<?php
				}
				
				?>
	<br>
	<table class='dialog'>
	<tr>
	<th class='dialog'><?php echo $strCombinedDiscipline; ?></th>
	<th class='dialog'><?php echo $strConversionTable; ?></th>
	</tr>
	<tr>
	<td class='dialog'>
		<form action='meeting_definition_category.php' method='post' name='combtype_<?php echo $row[1]."_".$row[7] ?>'>
		<input name='arg' type='hidden' value='change_combtype' />
		<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
		<input name='comb' type='hidden' value='<?php echo $row[7]; ?>' />
		<select name="combinedtype" onchange="document.combtype_<?php echo $row[1]."_".$row[7] ?>.submit()">
			<!--<option value="-">-</option>-->
				<?php               
                if ($ukc_meeting == 'y')  {
                    $sql_ukc = " AND d.Code = 408 ";
                }
                else {
                      $sql_ukc = " AND d.Code != 408 ";   
                }
				$res_comb = mysql_query("select Code, Name from disziplin_" . $_COOKIE['language'] . " AS d where Typ = ".$cfgDisciplineType[$strDiscCombined] ." ". $sql_ukc ." ORDER BY Anzeige");
                $sql="select Code, Name from disziplin_" . $_COOKIE['language'] . " where Typ = ".$cfgDisciplineType[$strDiscCombined] ." ". $sql_ukc ." ORDER BY Anzeige";
               
				if(mysql_errno() > 0) {	// DB error
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}else{
					while($row_comb = mysql_fetch_array($res_comb)){
						if($row[7] == $row_comb[0]){
							$sel = "selected";
						}else{
							$sel = "";
						}
						?>
			<option value="<?php echo $row_comb[0] ?>" <?php echo $sel ?>><?php echo $row_comb[1] ?></option>
						<?php
					}
				}
				?>
		</select>
		
	</td>
	</form>  
	<form method="POST" action="meeting_definition_category.php" name="comb_<?php echo $row[7] ?>">
	<input name='conv_changed' type='hidden' value='yes' />
	<input type="hidden" name="arg" value="change_formula">
	<input type="hidden" name="item" value="<?php echo $row[7] ?>">
	<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
	<input name='type' type='hidden' value='<?php echo $row[2]; ?>' />
	<?php $dd = new GUI_ConfigDropDown('conv', 'cvtTable', $row[3], "comb_$row[7].submit()"); ?>
	</form>
	</table>
				<?php    
				
				// get count of groups of entrys for current combined event
				$cGroups = array();
				$sql = "SELECT
							DISTINCT(a.Gruppe) as g
						FROM
							wettkampf AS w
						LEFT JOIN
							start AS st USING(xWettkampf)
						LEFT JOIN
							anmeldung As a USING(xAnmeldung)
						WHERE
							w.Mehrkampfcode = ".$row[7]."
						AND
							w.xKategorie = ".$row[1]."
						AND
							w.xMeeting = ".$_COOKIE['meeting_id']."
						AND 
							a.Gruppe != ''
						ORDER BY
							g ASC;";  
			          
				$res_c = mysql_query($sql);
				if(mysql_errno() > 0){
					AA_printErrorMsg(mysql_errno().": ".mysql_error());
				}else{
					while($row_c = mysql_fetch_array($res_c)){
						$cGroups[] = $row_c[0]; 
					}
					mysql_free_result($res_c);
				}
				
				// print header for disc table
				?>
	<table class='dialog'>
		<tr>
			<th class='dialog'><?php echo $strDiscipline; ?></th>
			<th class='dialog'><?php echo $strPoints; ?></th>
			<th class='dialog' title="<?php echo $strWind; ?>">W</th>
			<th class='dialog' title="<?php echo $strTiming." ".$strOn; ?>">T</th>
			<th class='dialog' title="<?php echo $strTiming." ".$strAutomatic; ?>">TA</th>
			<?php
			if(count($cGroups) > 0){
				?>
			<th class='dialog'><?php echo $strDate ?></td>
				<?php
				foreach($cGroups as $g){
					?>
			<th class='dialog'><?php echo $strTime." (G $g)"; ?></th>
					<?php
				}
			}else{
				?>
			<th class='dialog'><?php echo $strDate ?></td>
			<th class='dialog'><?php echo $strTime; ?></th>
				<?php
			}
			?>
			<th class='dialog' title="<?php echo $strCombinedLastEvent ?>">L</th>
			<td class='dialog'></td>
		</tr>
				<?php
				
				$comb = $row[7];
				
			}
			
			//
			// print each discipline
			//
			?>
		<tr>
			<form method="POST" action="meeting_definition_category.php" name="event_disc_<?php echo $row[0] ?>">
			<input type="hidden" name="arg" value="change_event_discipline">
			<input type="hidden" name="g" value="">
			<input type="hidden" name="round" value="">
			<input type="hidden" name="item" value="<?php echo $row[0] ?>">
			<input type="hidden" name="info" value="<?php echo $row[13] ?>">
			<input type="hidden" name="last" value="<?php echo $row[14] ?>">
           <input name='wTyp' type='hidden' value='<?php echo $row[2]; ?>' />  
			<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
			<input type="hidden" name="nocat" value="1"/> 
			
			<?php
		 
			if(!empty($_POST['combinedtype']) || !empty($_POST['cmbtype']) || !empty($row[7])  ){
				if  (!empty($_POST['combinedtype'])) {
					$t = $_POST['combinedtype'];
				}
				elseif  (!empty($_POST['cmbtype'])) {
						  $t = $_POST['cmbtype'];
				}
				elseif  (!empty($row[7])) {
						  $t = $row[7];
				}                 
							   
				// check if combined type has predefined disciplines
							
				$sql_k = "SELECT 
								Geschlecht 
						  FROM 
								kategorie 
						  WHERE 
								xKategorie = ".$category.";";  
								
				$query_k = mysql_query($sql_k);                   
				$row_k = mysql_fetch_assoc($query_k);
													  
				$my_tmp = $t;
				if($my_tmp==394 && ($row_k['Geschlecht']=='m' || $row_k['Geschlecht']=='M')){
					$my_tmp = 3942;
				}
			   
				if(isset($cfgCombinedDef[$my_tmp])){
					 $tt = $cfgCombinedDef[$my_tmp];   
					 $fix_disc = false;   
                     if (isset($cfgCombinedWO[$tt])){
                         $fix_disc = true;
                     }
                    
					?>
					<td class='dialog'>            
					<input name='cmbtype' type='hidden' value='<?php echo $t; ?>' />
					<?php

					//$dropdown = new GUI_Select('discipline_cmb', 1, "document.event_disc_$row[0].submit()");  
                    if ($fix_disc){
                         $res_d = mysql_query("SELECT 
                                                    xDisziplin
                                                    , Name
                                                    , Code 
                                              FROM 
                                                    disziplin_" . $_COOKIE['language'] . "
                                              WHERE Code = ". $row[16]); 
                            
                         $row_d = mysql_fetch_array($res_d);
                        
                        echo " " . $row_d[1];
                         ?>
                         <!--<input name="discipline_cmb_<?php echo $c;?>" type="text" disabled="disabled" value="<?php echo $row_d[1];?>  "/>  -->
                         <?php
                    } 
                    else {                                    
					    $dropdown = new GUI_Select('discipline_cmb', 1, "");	 
					    $res_d = mysql_query("SELECT 
												    xDisziplin
												    , Name
												    , Code 
										      FROM 
												    disziplin_" . $_COOKIE['language']);  
					    $val=$cfgCombinedWO[$tt][$c];
					    
					    while ($row_d = mysql_fetch_array($res_d)){  
						    if(!empty($_POST['combinedtype'])){  
							     if($row_d[2] == $row[16]) {                                         
								    $dropdown->selectOption($row[16]);
							    }
						    }                            
						    else {
							     $dropdown->selectOption($row[16]);                                       
						    }                           
									    
					    $dropdown->addOption( $row_d[1],$row_d[2]);   
					    }                 
				    $dropdown->printList(); 
                   
                    } 
                    $c++;    
                  
				// count of combined disciplines  
				}
				 else {                    
					 ?>
					 <td class='dialog'><?php echo $row[6]; ?></td>
					 </td>
					 <?php                      
				 }                   
			   ?> 
			</td>
			</form>     
			 <?php       
			}             
            
			?>             
			<form method="POST" action="meeting_definition_category.php" name="event_<?php echo $row[0] ?>">
			<input type="hidden" name="arg" value="change_event">     
			<input type="hidden" name="g" value="">
			<input type="hidden" name="round" value="">
			<input type="hidden" name="item" value="<?php echo $row[0] ?>">
			<input type="hidden" name="info" value="<?php echo $row[13] ?>">
			<input type="hidden" name="last" value="<?php  echo $row[14] ?>">
            <input type="hidden" name="wTyp" value="<?php  echo $row[2] ?>"> 
			<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
			<input type="hidden" name="nocat" value="1"/>             
			
			<td class='forms'>
			<?php
			if($row[3]>=100){
				?>
				-
				<?php
			} elseif($row[3]==$cvtTable[$strConvtableRankingPoints] || $row[3]==$cvtTable[$strConvtableRankingPointsU20]){
				?>
				<input type="text" name="formula" value="<?php echo $row[4]; ?>" style="width: 45px;" onchange="document.event_<?php echo $row[0] ?>.arg.value='change_formula'; 
					document.event_<?php echo $row[0] ?>.submit()"/>
				<?php
			} else {
                 if ($fix_disc){
                     ?>
                     <input type="hidden" name="formula" value="<?php echo $row[4]; ?>"/>  
                     <?php           
                         echo  $row[4];
                 }
                 else {     
                 
				    $dropdown = new GUI_Select('formula', 1, "document.event_$row[0].submit()");
				    foreach($cvtFormulas[$row[3]] as $key=>$value)
				    {
					    $dropdown->addOption($key, $key);
					    if($row[4] == $key) {
						    $dropdown->selectOption($key);
					    }
				    }
				    $dropdown->printList(); 
                }  
			}
			?>
			</td>
			<?php
			$check = "";
			$check1 = "";
			$check2 = "";
			$checkLast = "";
			
			// measure wind
			if($row[9] == $cfgDisciplineType[$strDiscTypeTrack] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeJump]){
				if($row[10] == 1){ $check = "checked"; }
				?>
				<td class='forms'><input type="checkbox" name="wind" onclick="document.event_<?php echo $row[0] ?>.submit()" <?php echo $check ?>></td>
				<?php
			}else{
				?>
				<td class='dialog'>-</td>
				<?php
			}
			
			// time measurement
			if($row[9] == $cfgDisciplineType[$strDiscTypeNone] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeTrack] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeTrackNoWind] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeDistance] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeRelay] ){
				if($row[11] == 1){ $check1 = "checked"; }
				if($row[12] == 1){ $check2 = "checked"; }
				?>
				<td class='forms'><input type="checkbox" name="timing" onclick="document.event_<?php echo $row[0] ?>.submit()" <?php echo $check1 ?>></td>
				<td class='forms'><input type="checkbox" name="timingAuto" onclick="document.event_<?php echo $row[0] ?>.submit()" <?php echo $check2 ?>></td>
				<?php
			}else{
				?>
				<td class='dialog'>-</td>
				<td class='dialog'>-</td>
				<?php
			}
			
			// get round time for groups
			if(count($cGroups) > 0 && $row[14] == 0){
				$times = array();
				$date = 0;
				$res_c = mysql_query("SELECT
								TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
								, r.Datum
								, r.Gruppe
								, r.xRunde
							FROM
								runde as r
							WHERE	xWettkampf = $row[0]");  
				 
				while($row_c = mysql_fetch_array($res_c)){
					$times[$row_c[2]] = $row_c;
					$date = $row_c[1];
				}
				mysql_free_result($res_c);
				
				$dd = new GUI_DateDropDown($date);
				//$oldg = 0; // hold previous ids for setting focus on next time field after saving
				//$oldx = 0;
				foreach($cGroups as $g){
					?>
			<td class='forms'><input type="text" size="5" maxlength="5" value="<?php echo $times[$g][0] ?>"
				onchange="document.event_<?php echo $row[0] ?>.arg.value='change_starttime'; 
					document.event_<?php echo $row[0] ?>.g.value='<?php echo $g ?>'; 
					document.event_<?php echo $row[0] ?>.round.value='<?php echo $times[$g][3] ?>';
					document.event_<?php echo $row[0] ?>.submit()" 
				name="starttime[<?php echo $g ?>]" id="starttime_<?php echo $oldx."_".$oldg ?>">
	 	
				 	<input name='eventgroup' type='hidden' value='<?php echo $row[0]; ?>' /> 
				 	<input name='combGroup'  type='hidden' value='<?php echo $row[7]; ?>' />  	 
					 
			</td>
					<?php
					$oldg = $g;
					$oldx = $row[0];
				}
			}
			
			// if last round, show only one time field
			elseif(count($cGroups) == 0 || $row[14] == 1){
				
				$res_c = mysql_query("SELECT
							TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
							, r.Datum
							, r.xRunde
						FROM
							runde as r
						WHERE	xWettkampf = $row[0]");
				$time = mysql_fetch_array($res_c);
				
				$dd = new GUI_DateDropDown($time[1]);
				?>
			<td class='forms' colspan="<?php echo count($cGroups)==0?"1":count($cGroups); ?>">
				<input type="text" size="5" maxlength="5" value="<?php echo $time[0] ?>"
				onchange="document.event_<?php echo $row[0] ?>.arg.value='change_starttime'; 
					document.event_<?php echo $row[0] ?>.round.value='<?php echo $time[2] ?>';
					document.event_<?php echo $row[0] ?>.submit()" 
				name="starttime" id="starttime_<?php echo $oldx."_".$oldg ?>">
			</td>
				<?php
			}
			
			?>
			<td class='forms'>
				<?php
				if($row[14] == 1){
					$checkLast = "checked";
				}
				?>
				<input type="radio" name="lastround" value="<?php echo $row[1]."_".$row[7] ?>"
					onclick="document.event_<?php echo $row[0] ?>.arg.value='change_lastdisc'; 
						document.event_<?php echo $row[0] ?>.submit()" <?php echo $checkLast ?>>
			</td>
			<td class='dialog'>
				<input type="button" name="delete" value="<?php echo $strDelete ?>"
				onclick="document.event_<?php echo $row[0] ?>.arg.value='delete_discipline'; document.event_<?php echo $row[0] ?>.submit()">
			</td>
			</form>
		</tr>
			<?php
			
		}
		
		
		/*
		*
		*	Special Team SM events
		***********************************************************************************************************
		*/
		// print information for extendend team sm contest
		// first print header
		if($row[2] == $cfgEventType[$strEventTypeTeamSM]
			&& (	$row[9] == $cfgDisciplineType[$strDiscTypeJump]
				|| $row[9] == $cfgDisciplineType[$strDiscTypeJumpNoWind]
				|| $row[9] == $cfgDisciplineType[$strDiscTypeHigh]
				|| $row[9] == $cfgDisciplineType[$strDiscTypeThrow])){
			if($tsm == 0){
				
				
				?>
	<br>
				<?php
				
				// print header for disc table
				?>
	<table class='dialog'>
		<tr>
			<th class='dialog'><?php echo $strDiscipline; ?></th>
			<th class='dialog' title="<?php echo $strWind; ?>">W</th>
			<th class='dialog' title="<?php echo $strTiming." ".$strOn; ?>">T</th>
			<th class='dialog' title="<?php echo $strTiming." ".$strAutomatic; ?>">TA</th>
			<?php
			if(count($cGroups) > 0){
				?>
			<th class='dialog'><?php echo $strDate ?></td>
				<?php
				foreach($cGroups as $g){
					?>
			<th class='dialog'><?php echo $strTime." (G $g)"; ?></th>
					<?php
				}
			}else{
				?>
			<th class='dialog'><?php echo $strDate ?></td>
			<th class='dialog'><?php echo $strTime; ?></th>
				<?php
			}
            
			?>
			
		</tr>
				<?php
				
				$comb = $row[7];
				
			}
			$tsm++;
			
			//
			// print each discipline
			//
            $cGroups = array(); 
			if ($row[2] == $cfgEventType[$strEventTypeTeamSM]) {
                 $sql_c = "SELECT DISTINCT 
                                r.Gruppe
                                , r.xRunde
                                , r.xWettkampf
                            FROM
                                wettkampf AS w
                                LEFT JOIN start AS st USING(xWettkampf)      
                                INNER JOIN runde AS r ON (r.xWettkampf = w.xWettkampf)  
                            WHERE
                                w.xWettkampf = ".$row[0]."
                            AND
                                w.xMeeting = ".$_COOKIE['meeting_id'] ."
                            ORDER BY r.Gruppe";   
                  
                 $res_c = mysql_query($sql_c);                      
                 if (mysql_num_rows($res_c) == 0) {                               
                           
                           $sql = "INSERT INTO runde SET  
                                           Datum = '".$_SESSION['meeting_infos']['DatumVon']."', 
                                           Gruppe = '1' , 
                                           xRundentyp = 9 ,
                                           xWettkampf = ".$row[0];                       
                            mysql_query($sql);
                            if(mysql_errno() > 0){
                                AA_printErrorMsg(mysql_errno().": ".mysql_error());
                            }
                            $sql = "INSERT INTO runde SET  
                                           Datum = '".$_SESSION['meeting_infos']['DatumVon']."', 
                                           Gruppe = '2' , 
                                           xRundentyp = 9 ,
                                           xWettkampf = ".$row[0];                       
                            mysql_query($sql);
                            if(mysql_errno() > 0){
                              AA_printErrorMsg(mysql_errno().": ".mysql_error());
                            }
                    }
                    elseif (mysql_num_rows($res_c) == 1) {
                            $row_c = mysql_fetch_row($res_c);  
                            if ($row_c[0] == ''){
                                   $sql = "Update runde SET  
                                                    Gruppe = '1' 
                                           WHERE xRunde = " .$row_c[1];
                                    mysql_query($sql);
                                    if(mysql_errno() > 0){
                                        AA_printErrorMsg(mysql_errno().": ".mysql_error());
                                    }  
                            }
                            if  ($row_c[0] == 1 || $row_c[0] == ''){
                                  $group = 2;
                            }
                            else {
                                  $group = 1;
                            }
                            $sql = "INSERT INTO runde SET  
                                           Datum = '".$_SESSION['meeting_infos']['DatumVon']."', 
                                            Gruppe = '" . $group . "',  
                                           xRundentyp = 9 ,
                                           xWettkampf = ".$row[0];  
                    
                            mysql_query($sql);
                            if(mysql_errno() > 0){
                                AA_printErrorMsg(mysql_errno().": ".mysql_error());
                            } 
                    }    
                    $res_c = mysql_query($sql_c);
                    if (mysql_num_rows($res_c) > 0) {
                        while($row_c = mysql_fetch_array($res_c)){
                            $cGroups[] = $row_c[0];
                        }
                    }
            }
            else {
			        // get count of groups of entrys for current event
			     
			        $sql = "SELECT
						        DISTINCT(a.Gruppe) AS g
					        FROM
						        wettkampf AS w
					        LEFT JOIN 
						        start AS st USING(xWettkampf)
					        LEFT JOIN 
						        anmeldung AS a USING(xAnmeldung)
					        WHERE
						        w.xWettkampf = ".$row[0]."
					        AND
						        w.xMeeting = ".$_COOKIE['meeting_id']."
					        AND 
						        a.Gruppe != ''
					        ORDER BY
						        g ASC;";    
			        $res_c = mysql_query($sql);
                   
			        if(mysql_errno() > 0){
				        AA_printErrorMsg(mysql_errno().": ".mysql_error());
			        }else{
				        while($row_c = mysql_fetch_array($res_c)){
					        $cGroups[] = $row_c[0];
				        }
				        mysql_free_result($res_c);
			        }
            }
			?>
		<tr>
			<form method="POST" action="meeting_definition_category.php" name="event_<?php echo $row[0] ?>">
			<input type="hidden" name="arg" value="change_event">
			<input type="hidden" name="g" value="">
			<input type="hidden" name="round" value="">
			<input type="hidden" name="item" value="<?php echo $row[0] ?>">
			<input type="hidden" name="info" value="<?php echo $row[13] ?>">
			<input type="hidden" name="last" value="<?php echo $row[14] ?>">
             <input type="hidden" name="wTyp" value="<?php  echo $row[2] ?>">
			<input name='cat' type='hidden' value='<?php echo $row[1]; ?>' />
			<td class='dialog'><?php echo $row[6]; ?></td>
			
			<?php
			$check = "";
			$check1 = "";
			$check2 = "";
			$checkLast = "";
			
			// measure wind
			if($row[9] == $cfgDisciplineType[$strDiscTypeTrack] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeJump]){
				if($row[10] == 1){ $check = "checked"; }
				?>
				<td class='forms'><input type="checkbox" name="wind" onclick="document.event_<?php echo $row[0] ?>.submit()" <?php echo $check ?>></td>
				<?php
			}else{
				?>
				<td class='dialog'>-</td>
				<?php
			}
			
			// time measurement
			if($row[9] == $cfgDisciplineType[$strDiscTypeNone] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeTrack] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeTrackNoWind] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeDistance] 
				|| $row[9] == $cfgDisciplineType[$strDiscTypeRelay] ){
				if($row[11] == 1){ $check1 = "checked"; }
				if($row[12] == 1){ $check2 = "checked"; }
				?>
				<td class='forms'><input type="checkbox" name="timing" onclick="document.event_<?php echo $row[0] ?>.submit()" <?php echo $check1 ?>></td>
				<td class='forms'><input type="checkbox" name="timingAuto" onclick="document.event_<?php echo $row[0] ?>.submit()" <?php echo $check2 ?>></td>
				<?php
			}else{
				?>
				<td class='dialog'>-</td>
				<td class='dialog'>-</td>
				<?php
			}
			
			// get round time for groups
			if(count($cGroups) > 0){
				$times = array();
				$date = 0;
				$res_c = mysql_query("SELECT
								TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
								, r.Datum
								, r.Gruppe
								, r.xRunde
							FROM
								runde as r
							WHERE	xWettkampf = $row[0]");
				while($row_c = mysql_fetch_array($res_c)){
					$times[$row_c[2]] = $row_c;
					$date = $row_c[1];
				}
				mysql_free_result($res_c);
				
				$dd = new GUI_DateDropDown($date);
				//$oldg = 0; // hold previous ids for setting focus on next time field after saving
				//$oldx = 0;
				foreach($cGroups as $g){
					?>
			<td class='forms'>g<?php echo $g ?>
				<input type="text" size="5" maxlength="5" value="<?php echo $times[$g][0] ?>"
				onchange="document.event_<?php echo $row[0] ?>.arg.value='change_starttime'; 
					document.event_<?php echo $row[0] ?>.g.value='<?php echo $g ?>'; 
					document.event_<?php echo $row[0] ?>.round.value='<?php echo $times[$g][3] ?>';
					document.event_<?php echo $row[0] ?>.submit()" 
				name="starttime[<?php echo $g ?>]" id="starttime_<?php echo $oldx."_".$oldg ?>">
			</td>
					<?php
					$oldg = $g;
					$oldx = $row[0];
				}
			}
			
			// show only one time field
			elseif(count($cGroups) == 0){
				
				$res_c = mysql_query("SELECT
							TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
							, r.Datum
							, r.xRunde
						FROM
							runde as r
						WHERE	xWettkampf = $row[0]");
				$time = mysql_fetch_array($res_c);
				
				$dd = new GUI_DateDropDown($time[1]);
				?>
			<td class='forms' colspan="<?php echo count($cGroups)==0?"1":count($cGroups); ?>">
				<input type="text" size="5" maxlength="5" value="<?php echo $time[0] ?>"
				onchange="document.event_<?php echo $row[0] ?>.arg.value='change_starttime'; 
					document.event_<?php echo $row[0] ?>.round.value='<?php echo $time[2] ?>';
					document.event_<?php echo $row[0] ?>.submit()" 
				name="starttime" id="starttime_<?php echo $oldx."_".$oldg ?>">
			</td>
				<?php
			}
			
			?>
			<td class='dialog'>
				<input type="button" name="delete" value="<?php echo $strDelete ?>"
				onclick="document.event_<?php echo $row[0] ?>.arg.value='delete_discipline'; document.event_<?php echo $row[0] ?>.submit()">
			</td>
			</form>
		</tr>
			<?php
			
		}       		
		
	}	// end loop disciplines
	//mysql_free_result($result);
    
    
     ?>
        
     
         
         <?php  
	
     if($_POST['wTyp'] > $cfgEventType[$strEventTypeSingleCombined]
                && $row[2] != $cfgEventType[$strEventTypeTeamSM] && $nulltime_exist)         // not single event
            {   
    ?>
     <table>
     <tr><hr></hr><td class="highlight_nulltime_red"><?php echo $strRemNulltime; ?> 
     </td></tr>
     </table>
             
    <?php
    }
    
	if($cType == $cfgEventType[$strEventTypeTeamSM] && $tsm > 0){
		?>
	</table>         
		<?php           
	}    	
	if($cType == $cfgEventType[$strEventTypeSingleCombined] || $cNewCombined ){ 		// add new combined event
		if($comb > 0){
			?>        			
		
		<tr>
			<form action='meeting_definition_category.php' method='post' name='newdiscipline_<?php echo $comb ?>'>
			<input name='arg' type='hidden' value='new_discipline' />
			<input name='cat' type='hidden' value='<?php echo $cCategory; ?>' />
			<input name='punktetabelle' type='hidden' value='<?php echo $punktetabelle; ?>' />
			<input name='combtype' type='hidden' value='<?php echo $comb; ?>' />
			<?php if (!$fix_disc) { $dd = new GUI_DisciplineDropDown(0, true, false, $keys, "document.newdiscipline_$comb.submit()");} ?>
			<td class='dialog' colspan='6'></td>
			</form>
		</tr>
			<?php
		}
		?>
	</table>
<br>



<table class='dialog'>
<tr>
<th class='dialog'><?php echo $strNew." ".$strCombinedDiscipline; ?></th>
</tr>
<tr>
<td class='dialog'>
	<form action='meeting_definition_category.php' method='post' name='addcombtype'>
	<input name='arg' type='hidden' value='add_combtype' />
	<input name='cat' type='hidden' value='<?php echo $cCategory; ?>' />
	<select name="combinedtype" onchange="document.addcombtype.submit()">
		<option value="-">-</option>
			<?php
              if ($ukc_meeting == 'y')  {
                    $sql_ukc = " AND d.Code = 408 ";
                }
                else {
                      $sql_ukc = " AND d.Code != 408 ";   
                }
			$res_comb = mysql_query("select Code, Name from disziplin_" . $_COOKIE['language'] . " AS d where Typ = ".$cfgDisciplineType[$strDiscCombined] ." ". $sql_ukc . " ORDER BY Anzeige");
			$sql="select Code, Name from disziplin_" . $_COOKIE['language'] . " AS d where Typ = ".$cfgDisciplineType[$strDiscCombined] ." ". $sql_ukc . " ORDER BY Anzeige";
           
            if(mysql_errno() > 0) {	// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}else{
				while($row_comb = mysql_fetch_array($res_comb)){
					
					?>
		<option value="<?php echo $row_comb[0] ?>"><?php echo $row_comb[1] ?></option>
					<?php
				}
			}
			?>
	</select>
	</form>
</td>
</tr>
</table>

<script type="text/javascript">
	// set focus for round start time
	if("<?php echo $_POST['arg'] ?>" == "change_starttime"){
		var o = document.getElementById("starttime_<?php echo $_POST['item']."_".$_POST['g'] ?>");
		o.focus();
		o.select();
	}
</script>
			<?php
		
	} // end if combined
	
 
    
    if ($cNewSVM) {
        $wTyp=12;
        
             
             if (mysql_num_rows($result) == 0) {
                
                ?>
<br>
<table class='dialog'>
<tr>
    <th class='dialog'><?php echo $strSvmCategory; ?></th>
</tr>
<tr>
    <td class='dialog'>
        <form action='meeting_definition_category.php' method='post' name='svmcat'>
        <input name='arg' type='hidden' value='add_svmcat' />
        <input name='conv_changed' type='hidden' value='no' />
        <input name='cat' type='hidden' value='<?php echo $category; ?>' /> 
        <select name="svmcategory" onchange="document.svmcat.submit()">
            <option value="-">-</option>
                <?php
                $res_comb = mysql_query("select xKategorie_SVM, Name from kategorie_svm ORDER BY Code");
                if(mysql_errno() > 0) {    // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }else{
                    while($row_comb = mysql_fetch_array($res_comb)){
                        if($row[8] == $row_comb[0]){
                            $sel = "selected";
                        }else{
                            $sel = "";
                        }
                        ?>
            <option value="<?php echo $row_comb[0] ?>" <?php echo $sel ?>><?php echo $row_comb[1] ?></option>
                        <?php
                    }
                }
                ?>
        </select>
        </form>
    </td>
</tr>
</table>
        
        
    <?php    
            }
mysql_free_result($result);      
    
    }   
    
	if($cType > $cfgEventType[$strEventTypeSingleCombined]
		&& $cType != $cfgEventType[$strEventTypeTeamSM]) 		// not single event
	{
?>
	</td>
</tr>
</table>
<?php
	}
    
  
}		// ET DB error


   if ($cType == $cfgEventType[$strEventTypeSVMNL] ||
          $cType == $cfgEventType[$strEventTypeClubBasic] ||   
          $cType == $cfgEventType[$strEventTypeClubAdvanced] ||   
          $cType == $cfgEventType[$strEventTypeClubTeam] ||   
          $cType == $cfgEventType[$strEventTypeClubCombined] ||  
          $cType == $cfgEventType[$strEventTypeClubMixedTeam]) {        
     
                ?>
                </table>
<br>
<table class='dialog'>
<tr>
    <th class='dialog'><?php echo $strSvmCategory; ?></th>
</tr>
<tr>
    <td class='dialog'>
        <form action='meeting_definition_category.php' method='post' name='new_svmcat'>
        <input name='arg' type='hidden' value='add_svmcat' />
        <input name='conv_changed' type='hidden' value='no' />
        <input name='cat' type='hidden' value='<?php echo $category; ?>' /> 
        <select name="svmcategory" onchange="document.new_svmcat.submit()">
            <option value="-">-</option>
                <?php
                $res_comb = mysql_query("select xKategorie_SVM, Name from kategorie_svm ORDER BY Code");
                if(mysql_errno() > 0) {    // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }else{
                    while($row_comb = mysql_fetch_array($res_comb)){
                        if($row[8] == $row_comb[0]){
                            $sel = "selected";
                        }else{
                            $sel = "";
                        }
                        ?>
            <option value="<?php echo $row_comb[0] ?>" <?php echo $sel ?>><?php echo $row_comb[1] ?></option>
                        <?php
                    }
                }
                ?>
        </select>
        </form>
    </td>
</tr>
</table>
        

      <?php
   }

$page->endPage();

?>
