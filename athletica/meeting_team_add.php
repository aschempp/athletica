<?php

/**********
 *
 *	meeting_team_add.php
 *	--------------------
 *	
 */

require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

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

if(!empty($_POST['category_svm'])) {
    $category_svm = $_POST['category_svm'];    // store selected category
}
else if(!empty($_GET['category_svm'])) {
    $category_svm = $_GET['category_svm'];    // store selected category
}

$club = 0;
if(!empty($_POST['club'])) {
	$club = $_POST['club'];	// store selected category
}

$nbrcheck = TRUE;
$nbr = 0;

//
// add team
//
if ($_POST['arg']=="add")
{
	$name = $_POST['name'];
	
	// Error: Empty fields
	if(empty($_POST['name']) || empty($category) || empty($_POST['club']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK: try to add item
	else
	{
		mysql_query("
			LOCK TABLES
				kategorie READ
				, meeting READ
				, verein READ
				, team as t WRITE
				, team WRITE
				, staffel READ
				, base_svm READ
				, base_relay READ
                , kategorie_svm AS ks READ   
		");
		
		// get the eventnumber of this meeting for generating a team id in the form eventnumber999 (xxxxxx999)
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
				
				$xTeamSQL = ", xTeam = ".$eventnr.$idcounter.", Athleticagen ='y' ";
				
			}
		}

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
				// OK, try to add team
				{
					if(!empty($_POST['id'])){
						// svm is added from base
						
						$xTeamSQL = ", xTeam = ".$_POST['id'].", Athleticagen ='n' ";
					}    
					
					mysql_query("
						INSERT INTO team SET 
							Name=\"". $_POST['name'] ."\"
							, xMeeting=" . $_COOKIE['meeting_id'] ."
							, xKategorie = " . $category  ."
							, xVerein=" . $_POST['club'] ." 
                            , xKategorie_svm = '" . $category_svm. "'" 
							.$xTeamSQL
					);
					
					if(mysql_errno() > 0) {                           
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}
					else {
						$xTeam = mysql_insert_id();	// get new ID
					}
				}		// ET Club valid; add or change
			}		// ET Meeting valid
		}		// ET Category valid
		// Check if any error returned from DB
		if(mysql_errno() > 0)
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		mysql_query("UNLOCK TABLES");
	}			// ET valid data
}			// ET add team


//
// display data
//
$page = new GUI_Page('meeting_team_add');
$page->startPage();
$page->printPageTitle("$strNewEntry $strTeams");

if ($_POST['arg']=="add")
{
	?>
<script>
	window.open("meeting_teamlist.php?item="
		+ <?php echo $xTeam; ?> + "#" + <?php echo $xTeam; ?>,
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
        echo "<form action='./meeting_team_add.php' method='post'>    
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
		<?php AA_printClubSelection("meeting_team_add.php", $club, $category, 0, true); ?>
	</td>
	<td class='forms'>
		<?php AA_printCategoryEntries("meeting_team_add.php", $category, $club, TRUE); ?>
	</td>
    <td class='forms'>
        <?php AA_printCategorySvmEntries("meeting_team_add.php", $category_svm, $category, $club, TRUE); ?>
    </td>
</tr>
</table>
<?php

if((!empty($category)) && (!empty($club)) && (!empty($category_svm)) )		// category & club selected
{
	?>
<br>
<table class="dialog">
<tr>
	<th class='dialog' colspan="2"><?php echo $strSvmFromBase ?></th>
</tr>
	<?php
	//
	// get relay from base data for current selection
	//
	$res = mysql_query("
		SELECT ks.Code FROM
			wettkampf as w
			LEFT JOIN kategorie_svm as ks USING(xKategorie_svm)
		WHERE w.xKategorie = $category
		AND w.xMeeting = ".$_COOKIE['meeting_id']);
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}else{
		$row = mysql_fetch_array($res);
		
		$res = mysql_query("
			SELECT * FROM
				base_svm as b
			WHERE
				b.svm_category = '$row[0]'
			AND	b.account_code = $club");
		
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			
			while($row = mysql_fetch_assoc($res)){
				?>
<tr>
	<form action='meeting_team_add.php' method='post' name='entry'>
	<td class='dialog'>
		<input name='arg' type='hidden' value='add' />
		<input name='category' type='hidden' value='<?php echo $category; ?>' />
		<input name='club' type='hidden' value='<?php echo $club; ?>' />
		<input name='id' type='hidden' value='<?php echo $row['id_svm']; ?>' />
         <input name='category_svm' type='hidden' value='<?php echo $category_svm; ?>' />       
		<?php echo $strName ?>: <input class='text' name='name' type='text' maxlength='40'
			value='<?php echo $row['svm_name'] ?>' />
	</td>
	<td class='dialog'><input type="submit" value="<?php echo $strEnter ?>"></td>
	</form>
</tr>
				<?php
			}
			
			if(mysql_num_rows($res) == 0) {
				?>
<tr>
	<td class='dialog' colspan="2"><?php echo $strSvmBaseNotFound ?></td>
</tr>
				<?php
			}
		}
	}
?>
</table>
<br>
<table class='dialog'>
<form action='meeting_team_add.php' method='post' name='entry'>
<tr>
	<th class='dialog'><?php echo $strName; ?></th>
	<td class='forms'>
		<input name='arg' type='hidden' value='add' />
		<input name='category' type='hidden' value='<?php echo $category; ?>' />
        <input name='category_svm' type='hidden' value='<?php echo $category_svm; ?>' />      
		<input name='club' type='hidden' value='<?php echo $club; ?>' />
		<input class='text' name='name' type='text' maxlength='40'
			value='' />
	</td>
</tr>
<tr>
	<td colspan="2" class='dialog'><?php echo $strTeamNameRemark ?></td>
</tr>
</table>
<p/>
<table>
	<tr>
		<td class='forms'>
			<button type='submit'>
				<?php echo $strSave; ?>
		  	</button>
		</td>
	</tr>
</form>	
</table>

<?php
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
