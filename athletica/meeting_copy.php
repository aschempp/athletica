<?php
/**********
 *
 *	meeting_copy.php
 *	------------------
 *	
 */

require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');
require('./lib/meeting.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$page = new GUI_Page('meeting_copy');
$page->startPage();
$page->printPageTitle($strCopyMeeting . ": " . $_COOKIE['meeting']);

$arg = "";
if(isset($_POST['arg'])){
	$arg = $_POST['arg'];
}

// check on new name != empty for copy prozess
if(empty($arg) || empty($_POST['newname'])){
?>
<form name="copy" action="meeting_copy.php" method="post">
	<input type="hidden" name="arg" value="copy">
	
	<table width="700" border="0" cellpadding="0" cellspacing="0" class="dialog">
		<colgroup>
			<col width="150"/>
			<col width="150"/>
			<col width="150"/>
			<col width="150"/>
			<col width="100"/>
		</colgroup>
		<tr>
			<th colspan="5" class="dialog"><?php echo $strMeetingNew; ?></th>
		</tr>
		<tr>
			<td><?php echo $strNewName?></td>
			<td><input type="text" name="newname" value="<?php echo $_COOKIE['meeting']; ?>"/></td>
			<td><?php echo $strNewNumber; ?></td>
			<td><input type="text" name="newnumber" value=""/></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><?php echo $strNewDate;?></td>
			<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<?php AA_meeting_printDate('from', ''); ?>
					</tr>
				</table>
			</td>
			<td colspan="2">&nbsp;</td>
			<td style="text-align: right;"><input type="submit" name="submit" value="<?php echo$strCopy; ?>"/></td>
		</tr>
	</table>
</form>

<?php
}
elseif($arg == "copy")
{
?>
<table class='dialog'>
<?php
	// new meeting name
	$newname = $_POST['newname'];
	$newnumber = $_POST['newnumber'];
	$newxMeeting = 0;
	$new_date = $_POST['from_year'].'-'.$_POST['from_month'].'-'.$_POST['from_day'];
	
	mysql_query("LOCK TABLES meeting WRITE, wettkampf WRITE, wettkampf as w READ ,runde WRITE, kategorie as k READ");
	
	// copy meeting entry
	$resFields = mysql_query("SHOW COLUMNS FROM meeting");
	$resData = mysql_query("SELECT * FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}else{
		
		$data = mysql_fetch_assoc($resData);
		
		$data_array = array();
		$data_index = array();
		$count = 0;
		for($a=strtotime($data['DatumVon']); $a<=strtotime($data['DatumBis']); $a++){
			$key = date('Y-m-d', $a);
			$data_array[$key] = $key;
			$data_index[$count] = $key;
			
			$a--;			
			$a = strtotime('+1 day', $a);
			$count++;
		}
		
		// get the start and end date
		$dateDiff = (strtotime($data['DatumBis']) - strtotime($data['DatumVon']));
		$daysDiff = ($dateDiff==0) ? 0 : (floor($dateDiff / 86400));
		$dateFrom = $new_date;
		$dateTo = ($daysDiff>0) ? date('Y-m-d', strtotime('+'.$daysDiff.(($daysDiff==1) ? 'day' : 'days'), strtotime($dateFrom))) : $dateFrom;
		
		$count = 0;
		for($a=strtotime($dateFrom); $a<=strtotime($dateTo); $a++){
			$key = date('Y-m-d', $a);
			$data_array[$data_index[$count]] = $key;
			
			$a--;
			$a = strtotime('+1 day', $a);
			$count++;
		}
		
		$sql = "";
		while($f = mysql_fetch_assoc($resFields)){  
			if($f['Key'] != "PRI" && $f['Field'] != "Name" && $f['Field'] != "Nummer" && $f['Field'] != "DatumVon" && $f['Field'] != "DatumBis" && $f['Field'] != "xControl"){ // exclude primary key and 2 fields
				$sql .= ", ".$f['Field']." = '".$data[$f['Field']]."' ";  
			}
			
		} 
		
		mysql_query("INSERT INTO meeting SET
				Name = '$newname'
				, Nummer = '$newnumber'
				, DatumVon = '$dateFrom'
				, DatumBis = '$dateTo' 
                , xControl = 0 
				$sql
		");
		
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			$newxMeeting = mysql_insert_id();
		}
		
		mysql_free_result($resData);
		mysql_free_result($resFields);               
      
		
	}
	
	if($newxMeeting > 0){
		
		// copy discipline entrys
		$resFields = mysql_query("SHOW COLUMNS FROM wettkampf");
		$fields = array();
		while($row = mysql_fetch_assoc($resFields)){
			$fields[] = $row;
		}
		
		$resData = mysql_query("SELECT * FROM wettkampf as w LEFT JOIN kategorie as k ON (w.xKategorie = k.xKategorie) WHERE w.xMeeting = ".$_COOKIE['meeting_id'] ." AND k.Code != 'U12X'");
		
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			
			while($data = mysql_fetch_assoc($resData)){
				$xWettkampfOld = $data['xWettkampf'];
				
				$sql = "";
				foreach($fields as $f){
					
					if($f['Key'] != "PRI" && $f['Field'] != "xMeeting"){ // exclude primary key and meeting id
						$sql .= ", ".$f['Field']." = '".$data[$f['Field']]."' ";
					}
					
				}
				
				$query = "INSERT INTO wettkampf 
								  SET xMeeting = ".$newxMeeting.$sql.";";
				mysql_query($query);
				
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				} else {
					$xWettkampf = mysql_insert_id();
					
					$resFields2 = mysql_query("SHOW COLUMNS FROM runde");
					$fields2 = array();
					while($row2 = mysql_fetch_assoc($resFields2)){
						$fields2[] = $row2;
					}
					
					$resData2 = mysql_query("SELECT * FROM runde WHERE xWettkampf = ".$xWettkampfOld);
		
					if(mysql_errno() > 0) {
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}else{
						while($data2 = mysql_fetch_assoc($resData2)){
							$datumOld = $data2['Datum'];
							
							$sql2 = "";
							foreach($fields2 as $f2){
								if($f2['Key'] != "PRI" && $f2['Field'] != "xRunde" && $f2['Field'] != "Datum" && $f2['Field'] != "xWettkampf" && $f2['Field'] != "Status" && $f2['Field'] != "Speakerstatus" && $f2['Field'] != "StatusZeitmessung" && $f2['Field'] != "StatusUpload"){ // exclude primary key and meeting id
										$sql2 .= ", ".$f2['Field']." = '".$data2[$f2['Field']]."' ";
									}
								
							}
							
							$query2 = "INSERT INTO runde 
											  SET Datum = '".$data_array[$datumOld]."', 
												  xWettkampf = ".$xWettkampf.$sql2.";";
							mysql_query($query2);
						}
				}
			}			
		}
		
		mysql_free_result($resData);
		mysql_free_result($resFields);
		mysql_free_result($resData2);
		mysql_free_result($resFields2);                    
     
		
		// unlock all tables
		mysql_query("UNLOCK TABLES");
			
		?>
		<tr>
			<th class='dialog'><?php echo $strCopyMade ?></th>
		</tr>
		<tr>
			<td><input type="button" name="" value="<?php echo $strBack ?>" onclick="parent.location = 'index.php'"></td>
		</tr>
		<?php
	}
	
	

?>
</table>
<?php

}
}

$page->endPage();
?>
