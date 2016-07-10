<?php

/**********
 *
 *	admin_scoretables.php
 *	------------------
 *
 */
 
$noMeetingCheck = true;

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_performance.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

$val_scoretable = (isset($_POST['xWertungstabelle'])) ? $_POST['xWertungstabelle'] : ((isset($_GET['xw'])) ? $_GET['xw'] : 0);
$val_discipline = (isset($_POST['xDisziplin'])) ? $_POST['xDisziplin'] : ((isset($_GET['disc'])) ? $_GET['disc'] : 0);

//
// Process add or change-request if required
//
if ($_POST['arg']=="addtbl" || $_POST['arg'] =="add" || $_POST['arg']=="change")
{
	if($_POST['arg']=="addtbl")
	{
		// Error: Empty fields
		if(empty($_POST['Name'])) {
			AA_printErrorMsg($strErrEmptyFields);
		}
		// OK: try to add item
		else {
			// check AUTO_INCREMENT (min. 100) of Wertungstabelle
			$sql_wt = "SELECT xWertungstabelle 
						 FROM wertungstabelle 
						WHERE xWertungstabelle < 100;";
			$query_wt = mysql_query($sql_wt);
			
			while($row_wt = mysql_fetch_assoc($query_wt)){
				$sql_max = "SELECT MAX(xWertungstabelle) AS max_id 
							  FROM wertungstabelle;";
				$query_max = mysql_query($sql_max);
				$max_id = (mysql_result($query_max, 0, 'max_id')>=100) ? mysql_result($query_max, 0, 'max_id') : 99;
				$new_id = ($max_id + 1);
				
				$sql_up = "UPDATE wertungstabelle 
							  SET xWertungstabelle = ".$new_id." 
							WHERE xWertungstabelle = ".$row_wt['xWertungstabelle'].";";
				$query_up = mysql_query($sql_up);
				
				if($query_up){
					$sql_up2 = "UPDATE wertungstabelle_punkte 
								   SET xWertungstabelle = ".$new_id." 
								 WHERE xWertungstabelle = ".$row_wt['xWertungstabelle'].";";
					$query_up2 = mysql_query($sql_up2);
				}
			}
			
			$sql_max = "SELECT MAX(xWertungstabelle) AS max_id 
						  FROM wertungstabelle;";
			$query_max = mysql_query($sql_max);
			$max_id = (mysql_num_rows($query_max)==1 && mysql_result($query_max, 0, 'max_id')>0) ? mysql_result($query_max, 0, 'max_id') : 99;
			$new_id = ($max_id + 1);
			
			$sql_ai = "ALTER TABLE wertungstabelle 
								   AUTO_INCREMENT = ".$new_id.";";
			$query_ai = mysql_query($sql_ai);
			
			$sql = "INSERT INTO wertungstabelle 
							SET Name = '".addslashes($_POST['Name'])."';";
			$query = mysql_query($sql);
			
			if($query && mysql_insert_id()>0){
				header('Location: admin_scoretables.php?xw='.mysql_insert_id());
				exit();
			}
		}
	}
	else
	{
		// Error: Empty fields
		if(empty($_POST['Punkte']) || empty($_POST['Leistung'])) {
			AA_printErrorMsg($strErrEmptyFields);
		}
		else {
			$sqld = "SELECT Typ 
					   FROM disziplin_" . $_COOKIE['language'] . " 
					  WHERE xDisziplin = ".$_POST['xDisziplin'].";";
			$queryd = mysql_query($sqld);
			
			$typ = mysql_result($queryd, 0, 'Typ');
			
			$perf = NULL;
			
			if(($typ == $cfgDisciplineType[$strDiscTypeTrack])
				|| ($typ == $cfgDisciplineType[$strDiscTypeTrackNoWind])
				|| ($typ == $cfgDisciplineType[$strDiscTypeRelay])
				|| ($typ == $cfgDisciplineType[$strDiscTypeDistance]))
				{
				$secflag = false;
				if(substr($_POST['Leistung'],0,2) >= 60){
					$secflag = true;
				}
				$pt = new PerformanceTime($_POST['Leistung'], $secflag);
				$perf = $pt->getPerformance();

			}
			else {
				$pa = new PerformanceAttempt($_POST['Leistung']);
				$perf = $pa->getPerformance();
			}
			if($perf == NULL) {	// invalid performance
				$perf = 0;
			}
			
			if($perf>0){
				// OK: try to add item
				if ($_POST['arg']=="add") {
				
					$sql = "INSERT INTO wertungstabelle_punkte 
									SET xWertungstabelle = ".$_POST['xWertungstabelle']."
										, xDisziplin = ".$_POST['xDisziplin']."
										, Geschlecht = '".$_POST['Geschlecht']."'
										, Leistung = '".$perf."'
										, Punkte = ".$_POST['Punkte'].";";
					mysql_query($sql);
				}
				// OK: try to change item
				else if ($_POST['arg']=="change") {
					$sql = "UPDATE wertungstabelle_punkte 
							   SET xWertungstabelle = ".$_POST['xWertungstabelle']."
								   , xDisziplin = ".$_POST['xDisziplin']."
								   , Geschlecht = '".$_POST['Geschlecht']."'
								   , Leistung = '".$perf."'
								   , Punkte = ".$_POST['Punkte']." 
							 WHERE xWertungstabelle_Punkte = ".$_POST['item'].";";
					mysql_query($sql);
				}
			} else {
				AA_printErrorMsg($strErrInvalidResult);
			}
		}
	}
}
//
// Process delete-request if required
//
else if ($_GET['arg']=="del")
{

	mysql_query("DELETE FROM wertungstabelle_punkte WHERE xWertungstabelle_Punkte = " . $_GET['item']);
	header('Location: admin_scoretables.php?xw='.$_GET['xw'].'&disc='.$_GET['disc']);
	exit();
	
}
else if ($_GET['arg']=="deltbl")
{

	// Check if not used anymore
	if(AA_checkReference("wettkampf", "Punktetabelle", $_GET['item']) == 0) {
		mysql_query("DELETE FROM wertungstabelle_punkte WHERE xWertungstabelle = " . $_GET['item']);
		mysql_query("DELETE FROM wertungstabelle WHERE xWertungstabelle = " . $_GET['item']);
		header('Location: admin_scoretables.php');
		exit();
	}
	// Error: still in use
	else {
		AA_printErrorMsg($strScoreTable . $strErrStillUsed);
	}
}


// Check if any error returned from DB
if(mysql_errno() > 0) {
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

//
// Display current data
//

$page = new GUI_Page('admin_scoretables', TRUE);
$page->startPage();
$page->printPageTitle($strScoreTables);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/scoring_tables.html', $strHelp, '_blank');
$menu->printMenu();

?>
<p/>
<?php

if(isset($_POST['xWertungstabelle']) && $_POST['xWertungstabelle']=='new')
{
	?>
	<form name="scoretable_new" action="admin_scoretables.php" method="post">
		<input name='arg' type='hidden' value='addtbl'/>
		<table class='dialog' width="300">
			<tr>
				<th class='dialog'><?php echo $strName; ?></th>
				<td class='forms'><input type="text" name="Name" value="" class="text"/></td>
				<td class='forms'>
					<input type="submit" name="submit" value="<?php echo $strSave; ?>"/>
				</td>
			</tr>
		</tr>
	</form>
	<?php
}
else 
{
	?>
	<input name='arg' type='hidden' value='select_scoretable' />
	<table>
		<tr>
			<th class='dialog'><?php echo $strScoreTable; ?></th>
			<td class='forms'>
				<form action='admin_scoretables.php' method='post' name='scoretable_selection'>
					<?php
					$dd = new GUI_ScoreTableDropDown($val_scoretable, "submitForm(document.scoretable_selection)");
					?>
				</form>
			</td>
			<?php
			if($val_scoretable>0){
				?>
				<th class='dialog'><?php echo $strDiscipline; ?></th>
				<td class='forms'>
					<form action='admin_scoretables.php' method='post' name='discipline_selection'>
						<input type="hidden" name="xWertungstabelle" value="<?php echo $val_scoretable; ?>"/>
						<?php
						$dd = new GUI_ScoreTableDisciplineDropDown($val_scoretable, $val_discipline, "submitForm(document.discipline_selection)");
						?>
					</form>
				</td>
				<?php
			}
			?>
		</tr>
	</table><br/>

	<?php
	if($val_scoretable>0)
	{
		?>
		<button name="deltbl" onclick="window.open('admin_scoretables.php?arg=deltbl&item=<?php echo $val_scoretable; ?>', '_self');"><?php echo $strDeleteScoreTable; ?></button>
		<?php
	}
	
	if($val_scoretable>0 && $val_discipline>0)
	{
		?>
		<br/><br/><br/>
		<table class='dialog'>
			<tr>
				<th class='dialog'><?php echo $strPoints; ?></th>
				<th class='dialog'><?php echo $strPerformance; ?></th>
				<th class='dialog'><?php echo $strSex; ?></th>
			</tr>
			<tr>
				<form action='admin_scoretables.php' method='post'>
					<td class='forms'>
						<input name='arg' type='hidden' value='add'/>
						<input type="hidden" name="xWertungstabelle" value="<?php echo $val_scoretable; ?>"/>
						<input type="hidden" name="xDisziplin" value="<?php echo $val_discipline; ?>"/>
						<input class='text' name='Punkte' type='text' maxlength='10' value="(<?php echo $strNew; ?>)" style="width: 80px;"/>
					</td>
					<td class='forms'>
						<input type='text' name='Leistung' class='textmedium' maxlength='50'/>
					</td>
					<td>
						<input type="radio" name="Geschlecht" value="M" checked="checked"/> <?php echo $strSexMShort; ?>
						<input type="radio" name="Geschlecht" value="W"/> <?php echo $strSexWShort; ?>
					</td>
					<td>
						<button type='submit'><?php echo $strSave; ?></button>
					</td>
				</form>
			</tr>
			<?php
			$sql = "SELECT xWertungstabelle_Punkte
						   , xWertungstabelle
						   , d.xDisziplin
						   , Geschlecht
						   , Leistung
						   , Punkte 
						   , Typ
					  FROM wertungstabelle_punkte 
				 LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d USING(xDisziplin) 
					 WHERE xWertungstabelle = ".$val_scoretable." 
					   AND d.xDisziplin = ".$val_discipline." 
				  ORDER BY Geschlecht ASC, 
						   Punkte DESC;";
			$result = mysql_query($sql);
			
			$counter = 0;
			$btn = new GUI_Button('', '');
			
			while($row = mysql_fetch_assoc($result))
			{
				$counter++;
				
				$rowclass = ($counter%2 == 0) ? 'odd' : 'even';
				
				if(($row['Typ'] == $cfgDisciplineType[$strDiscTypeTrack])
					|| ($row['Typ'] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
					|| ($row['Typ'] == $cfgDisciplineType[$strDiscTypeRelay])
					|| ($row['Typ'] == $cfgDisciplineType[$strDiscTypeDistance]))
					{
					$secflag = false;
					if(substr($row['Leistung'],0,2) >= 60){
						$secflag = true;
					}
					$perf = AA_formatResultTime($row['Leistung'], false, $secflag);
				}
				else {
					$perf = AA_formatResultMeter($row['Leistung']);
				}
				?>
				<tr class='<?php echo $rowclass; ?>'>
					<form name='pt<?php echo $counter; ?>' action='admin_scoretables.php#item_<?php echo $row[0]; ?>' method='post'>
						<td class='forms'>
							<input name='arg' type='hidden' value='change'/>
							<input type="hidden" name="xWertungstabelle" value="<?php echo $val_scoretable; ?>"/>
							<input type="hidden" name="xDisziplin" value="<?php echo $val_discipline; ?>"/>
							<input name='item' type='hidden' value='<?php echo $row['xWertungstabelle_Punkte']; ?>'/>
							<input class='text' name='Punkte' type='text' maxlength='10' value="<?php echo $row['Punkte']; ?>" style="width: 80px;" onChange='submitForm(document.pt<?php echo $counter;?>)'/>
						</td>
						<td class='forms'>
							<input type='text' name='Leistung' class='textmedium' value="<?php echo $perf;?>" maxlength='50' onChange='submitForm(document.pt<?php echo $counter;?>)'/>
						</td>
						<td>
							<?php
							$m = ($row['Geschlecht']=='M') ? ' checked="checked"' : '';
							$w = ($row['Geschlecht']=='W') ? ' checked="checked"' : '';
							?>
							<input type="radio" name="Geschlecht" value="M"<?php echo $m; ?> onclick='submitForm(document.pt<?php echo $counter; ?>)'/> <?php echo $strSexMShort; ?>
							<input type="radio" name="Geschlecht" value="W"<?php echo $w; ?> onclick='submitForm(document.pt<?php echo $counter; ?>)'/> <?php echo $strSexWShort; ?>
						</td>
						<td>
							<?php
							$btn->set("admin_scoretables.php?arg=del&item=".$row['xWertungstabelle_Punkte']."&disc=".$val_discipline."&xw=".$val_scoretable, $strDelete);
							$btn->printButton();
							?>
						</td>
					</form>
				</tr>
				<?php
			}
			
			mysql_free_result($result);
			?>
		</table>
		<?php
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