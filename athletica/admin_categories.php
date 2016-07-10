<?php

/**********
 *
 *	admin_categories.php
 *	------------------
 *
 */
 
$noMeetingCheck = true;

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');  
require('./lib/meeting.lib.php'); 

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

 $ukc_meeting = AA_checkMeeting_UKC(); 
 
//
// Process add or change-request if required
//
if ($_POST['arg']=="add" || $_POST['arg']=="change")
{
	// Error: Empty fields
	if(empty($_POST['short']) || empty($_POST['name']) || empty($_POST['order']) || empty($_POST['age']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// Error: 'order' must be between 1 and 999
	else if($_POST['order']< 1 || $_POST['order'] > 999)
	{
		AA_printErrorMsg($strErrInvalidOrder);
	}
	// OK: try to add item
	else if ($_POST['arg']=="add")
	{   
        if ($ukc_meeting == 'y'){
             ?>
            <script type="text/javascript">  

            check = alert("<?php echo $strErrCatUkc; ?>");
            </script>   
        
            <?php    
            
        }
        else {
                $row_activ = 'y'; 
                if ($_POST['activ'] == 'i'){   
                    $row_activ = 'n'; 
                }
                       
		        mysql_query("
			        INSERT INTO kategorie SET 
				        Kurzname=\"" . strtoupper($_POST['short']) . "\"
				        , Name=\"" . $_POST['name'] . "\"
				        , Geschlecht=\"" . $_POST['sex'] . "\"
				        , Anzeige=" . $_POST['order'] . "
				        , Alterslimite=" . $_POST['age'] ."
                        , aktiv='" . $row_activ . "'"    
		        );
        }
	}
	// OK: try to change item
	else if ($_POST['arg']=="change")
	{  	mysql_query("LOCK TABLES kategorie WRITE"); 
       
		if (empty($_POST['sex']) || empty($_POST['activ'] )){ 
			
			$query="SELECT 
						Geschlecht,
                        aktiv 
			        From 
			        	kategorie 
			        WHERE xKategorie =" . $_POST['item'];
			
	     	$res=mysql_query($query);
	     	
	     	if(mysql_errno() > 0) {
				$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			}
			else {
	     		$row=mysql_fetch_row($res);	
	     	    if (empty($_POST['sex'])){
                     $row_sex = $row[0];
                }
                else {
                    $row_sex = $_POST['sex']; 
                }
                 if ($_POST['activ'] == 'i'){   
                     $row_activ = 'n'; 
                }
                elseif ($_POST['activ'] == 'a') {
                   $row_activ = 'y';   
                }
                 else { 
                    $row_activ = $row[1];   
                 }
	       		mysql_query("
				UPDATE kategorie SET
					Kurzname=\"" . strtoupper($_POST['short']) . "\"
					, Name=\"" . $_POST['name'] . "\"
					, Geschlecht=\"" . $row_sex . "\"
					, Anzeige=" . $_POST['order'] . "
					, Alterslimite=" . $_POST['age'] . "
                    , aktiv='" . $row_activ . "' 
				WHERE xKategorie=" . $_POST['item']
				);
				if(mysql_errno() > 0) {
					$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
				}  			   
			}
	    }
		else {   
                if ($_POST['activ'] == 'i'){
                     $row_activ = 'n';
                }  
                else {
                     $row_activ = 'y';   
                }
                    	   
			   mysql_query("
			   UPDATE kategorie SET
					Kurzname=\"" . strtoupper($_POST['short']) . "\"
					, Name=\"" . $_POST['name'] . "\"
					, Geschlecht=\"" . $_POST['sex'] . "\"
					, Anzeige=" . $_POST['order'] . "
					, Alterslimite=" . $_POST['age'] . "
                    , aktiv='" . $row_activ . "'  
			   WHERE xKategorie=" . $_POST['item']
			   );
			   
			   if(mysql_errno() > 0) {
					$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			   } 		
		} 		
		
		mysql_query("UNLOCK TABLES");  
	}
}
//
// Process delete-request if required
//
else if ($_GET['arg']=="del")
{
	mysql_query("LOCK TABLES anmeldung READ, staffel READ, team WRITE,"
							. " wettkampf WRITE, kategorie WRITE");

	// Still in use?
	$rows = AA_checkReference("anmeldung", "xKategorie", $_GET['item']);
	$rows = $rows + AA_checkReference("staffel", "xKategorie", $_GET['item']);
	$rows = $rows + AA_checkReference("team", "xKategorie", $_GET['item']);
	$rows = $rows + AA_checkReference("wettkampf", "xKategorie", $_GET['item']);
	// OK: not used anymore
	if($rows == 0)
	{
		mysql_query("DELETE FROM kategorie WHERE xKategorie=" . $_GET['item']);
	}
	// Error: still in use
	else
	{
		AA_printErrorMsg($strCategory . $strErrStillUsed);
	}
	mysql_query("UNLOCK TABLES");
}

// Check if any error returned from DB
if(mysql_errno() > 0)
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}


//
//	Display current data
//

$page = new GUI_Page('admin_categories', TRUE);
$page->startPage();
$page->printPageTitle($strCategories);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/categories.html', $strHelp, '_blank');
$menu->printMenu();

// sort argument
$img_cat="img/sort_inact.gif";
$img_short="img/sort_inact.gif";
$img_age="img/sort_inact.gif";
$img_order="img/sort_inact.gif";
$img_sex="img/sort_inact.gif";
$img_activ="img/sort_inact.gif"; 

if ($_GET['arg']=="cat") {
	$argument="Name";
	$img_cat="img/sort_act.gif";
} else if ($_GET['arg']=="short") {
	$argument="Kurzname";
	$img_short="img/sort_act.gif";
} else if ($_GET['arg']=="age") {
	$argument="Alterslimite";
	$img_age="img/sort_act.gif";
} else if ($_GET['arg']=="order") {
	$argument="Anzeige";
	$img_order="img/sort_act.gif";
} else if ($_GET['arg']=="sex") {
	$argument="Geschlecht";
	$img_sex="img/sort_act.gif";
} else if ($_GET['arg']=="activ") { 
    $argument="aktiv";
    $img_activ="img/sort_act.gif";  
} else {
	$argument="Anzeige";
	$img_order="img/sort_act.gif";
}

?>
<p/>
<table class='dialog'>
<tr>
	<th class='dialog'>
		<a href='admin_categories.php?arg=cat'>
			<?php echo $strCategory; ?>
			<img src='<?php echo $img_cat; ?>' />
		</a>
	</th>
	<th class='dialog'>
		<a href='admin_categories.php?arg=short'>
			<?php echo $strShortname; ?>
			<img src='<?php echo $img_short; ?>' />
		</a>
	</th>
	<th class='dialog'>
		<a href='admin_categories.php?arg=age'>
			<?php echo $strAgelimit; ?>
			<img src='<?php echo $img_age; ?>' />
		</a>
	</th>
	<th class='dialog'>
		<a href='admin_categories.php?arg=order'>
			<?php echo $strOrder; ?>
			<img src='<?php echo $img_order; ?>' />
		</a>
	</th>
	<th class='dialog'>
		<a href='admin_categories.php?arg=sex'>
			<?php echo $strSex; ?>
			<img src='<?php echo $img_sex; ?>' />
		</a>
	</th>
    <th class='dialog'>
        <a href='admin_categories.php?arg=activ'>
            <?php echo $strActiv; ?>
            <img src='<?php echo $img_activ; ?>' />
        </a>
    </th>
</tr>

<tr>
	<form action='admin_categories.php' method='post'>
	<td class='forms'>
		<input name='arg' type='hidden' value='add'>
		<input class='text' name='name' type='text' maxlength='30'
			value="(<?php echo $strNew; ?>)" ></td>
	<td class='forms_ctr'>
		<input class='textshort' name='short' type='text' maxlength='4'></td>
	<td class='forms_ctr'>
		<input class='nbr' name='age' type='text' maxlength='2'></td>
	<td class='forms_ctr'>
		<input class='nbr' name='order' type='text' maxlength='3'></td>
	<td class='forms_ctr'>
		<input type="radio" name="sex" value="m" checked><?php echo $strSexMShort ?>
		<input type="radio" name="sex" value="w"><?php echo $strSexWShort ?></td>    
    <td class='forms_ctr'>
        <input type="radio" name="activ" value="a" checked><?php echo $strActivShort ?>
        <input type="radio" name="activ" value="i"><?php echo $strInactivShort ?></td>    
	<td class='forms_ctr'>
		<button type='submit'>
			<?php echo $strSave; ?>
		</button>
	</td>
	</form>	
</tr>

<?php      

$result = mysql_query("SELECT xKategorie"
						. ", Kurzname"
						. ", Name"
						. ", Anzeige"
						. ", Alterslimite"
						. ", Geschlecht"
						. ", Code"
                        . ", aktiv"   
						. " FROM kategorie WHERE Code != 'U12X' ORDER BY " . $argument);
      
$i = 0;
$btn = new GUI_Button('', '');	// create button object

while ($row = mysql_fetch_row($result))
{
	$i++;		// line counter
	
	$sexm = "";
	$sexw = "";
	if($row[5] == "w"){
		$sexw = "checked";
	}else{
		$sexm = "checked";
	}
    
    $activ = "";
    $inactiv = "";
    if($row[7] == "y"){            // activ = 'y'
        $activ = "checked";
    }else{
        $inactiv = "checked";
    }
	
	// disable sex option if category is a standard
	$sexDis = "";
	if(!empty($row[6])){
		$sexDis = "disabled";
	}
	
	if( $i % 2 == 0 ) {		// even row number
		$rowclass = 'odd';
	}
	else {	// odd row number
		$rowclass = 'even';
	}
?>
<tr class='<?php echo $rowclass; ?>'>
	<form action='admin_categories.php#item_<?php echo $row[0]; ?>'
		method='post' name='cat<?php echo $i; ?>'>
	<td class='forms'>
		<input name='arg' type='hidden' value='change'>
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>'>
		<input class='text' name='name' type='text' maxlength='30'
			value="<?php echo $row[2]; ?>"
			onChange='submitForm(document.cat<?php echo $i; ?>)'>
	</td>
	<td class='forms_ctr'>
		<input class='textshort' name='short' type='text' maxlength='4'
			value="<?php echo $row[1]; ?>"
			onChange='submitForm(document.cat<?php echo $i; ?>)'>
	</td>
	<td class='forms_ctr'>
		<input class='nbr' name='age' type='text' maxlength='2'
			value='<?php echo $row[4]; ?>'
			onChange='submitForm(document.cat<?php echo $i; ?>)'>
	</td>
	<td class='forms_ctr'>
		<input class='nbr' name='order' type='text' maxlength='3'
			value='<?php echo $row[3]; ?>'
			onChange='submitForm(document.cat<?php echo $i; ?>)'>
	</td>
	<td class='forms_ctr'>
		<input type="radio" name="sex" value="m" onChange='submitForm(document.cat<?php echo $i; ?>)' 
			<?php echo $sexm ?> <?php echo $sexDis ?>><?php echo $strSexMShort ?>
		<input name='sex_test<?php echo $i;?>' type='hidden' value='m'> 
		<input type="radio" name="sex" value="w" onChange='submitForm(document.cat<?php echo $i; ?>)' 
			<?php echo $sexw ?> <?php echo $sexDis ?>><?php echo $strSexWShort ?>
	</td>
    <td class='forms_ctr'>
        <input type="radio" name="activ" value="a" onChange='submitForm(document.cat<?php echo $i; ?>)' 
            <?php echo $activ ?> ><?php echo $strActivShort ?>
        <input name='sex_test<?php echo $i;?>' type='hidden' value='m'> 
        <input type="radio" name="activ" value="i" onChange='submitForm(document.cat<?php echo $i; ?>)' 
            <?php echo $inactiv ?> ><?php echo $strInactivShort ?>
    </td>
	<td>
<?php
	if(empty($row[6])){
		$btn->set("admin_categories.php?arg=del&item=$row[0]", $strDelete);
		$btn->printButton();
	} else {
		?>
		&nbsp;
		<?php
	}
?>
	</td>
	</form>
</tr>
<?php
}

mysql_free_result($result);
?>

</table>

<script type="text/javascript">
<!--
	scrollDown();
//-->
</script>

<?php

$page->endPage();
?>
