<?php

/********************
 *
 *	meeting_page_layout.php
 *	---------
 *	edit settings for page header and footer
 *	
 *******************/

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
include('./lib/cl_gui_select.lib.php');
include('./lib/cl_gui_dropdown.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}
if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$arg = "";
if(isset($_POST['arg'])){
	$arg = $_POST['arg'];
}elseif(isset($_GET['arg'])){
	$arg = $_GET['arg'];
}

$tempdir = "layout/";

if($arg == "save"){
	// copy pictures
	$pics = array();
	foreach($_FILES as $key => $file){
		if(!empty($file['name'])){
			move_uploaded_file($file['tmp_name'], $tempdir.$file['name']);
			$pics[$key] = $file['name'];
		}else{
			$pics[$key] = $_POST['c'.$key];
		}
	}
	
	if(empty($_POST['item'])){
		// create new page layout entry
		mysql_query("INSERT INTO layout SET
				BildT = '".$pics['pic_t']."'
                , TypTL = '".$_POST['type_tl']."'
				, TextTL = '".$_POST['text_tl']."'
				, BildTL = '".$pics['pic_tl']."'
				, TypTC = '".$_POST['type_tc']."'
				, TextTC = '".$_POST['text_tc']."'
				, BildTC = '".$pics['pic_tc']."'
				, TypTR = '".$_POST['type_tr']."'
				, TextTR = '".$_POST['text_tr']."'
				, BildTR = '".$pics['pic_tr']."'
                , BildB = '".$pics['pic_b']."'
				, TypBL = '".$_POST['type_bl']."'
				, TextBL = '".$_POST['text_bl']."'
				, BildBL = '".$pics['pic_bl']."'
				, TypBC = '".$_POST['type_bc']."'
				, TextBC = '".$_POST['text_bc']."'
				, BildBC = '".$pics['pic_bc']."'
				, TypBR = '".$_POST['type_br']."'
				, TextBR = '".$_POST['text_br']."'
				, BildBR = '".$pics['pic_br']."'
				, xMeeting = ".$_COOKIE['meeting_id']);
	}else{
		// update entry
		mysql_query("UPDATE layout SET
                BildT = '".$pics['pic_t']."'
				, TypTL = '".$_POST['type_tl']."'
				, TextTL = '".$_POST['text_tl']."'
				, BildTL = '".$pics['pic_tl']."'
				, TypTC = '".$_POST['type_tc']."'
				, TextTC = '".$_POST['text_tc']."'
				, BildTC = '".$pics['pic_tc']."'
				, TypTR = '".$_POST['type_tr']."'
				, TextTR = '".$_POST['text_tr']."'
				, BildTR = '".$pics['pic_tr']."'
                , BildB = '".$pics['pic_b']."'
				, TypBL = '".$_POST['type_bl']."'
				, TextBL = '".$_POST['text_bl']."'
				, BildBL = '".$pics['pic_bl']."'
				, TypBC = '".$_POST['type_bc']."'
				, TextBC = '".$_POST['text_bc']."'
				, BildBC = '".$pics['pic_bc']."'
				, TypBR = '".$_POST['type_br']."'
				, TextBR = '".$_POST['text_br']."'
				, BildBR = '".$pics['pic_br']."'
			WHERE
				xLayout = ".$_POST['item']);
	}
	
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}
}


$res = mysql_query("	SELECT
				*
			FROM
				layout
			WHERE
				xMeeting = ". $_COOKIE['meeting_id']);
if(mysql_errno() > 0){
	AA_printErrorMsg(mysql_errno().": ".mysql_error());
}else{
	$row = mysql_fetch_assoc($res);
	if(mysql_num_rows($res)==0){
		// set standard layout
		$row['TypTL']=1;
        $row['BildTC']="athletica-logo.png";
        $row['BildT']="";
		$row['BildB']="";
		$row['TypTC']=6;
		$row['TypTR']=3;
		$row['TypBL']=4;
		$row['TypBC']=2;
		$row['TypBR']=0;
	}
}

// Bilder die nicht mehr gebraucht werden löschen

$dir = dir('layout/');
while(($entry = $dir->read())!==false){
	if(preg_match('/(bmp|gif|jpg|jpeg|png)$/i', $entry)){
        if(!preg_match('/(athletica100.jpg|athletica-logo.png)$/i', $entry)){
			$sql = "SELECT COUNT(xLayout) AS total
					  FROM layout 
					 WHERE BildT ='".$entry."'
                        OR BildTL = '".$entry."' 
						OR BildTC = '".$entry."' 
						OR BildTr = '".$entry."' 
                        OR BildB = '".$entry."' 
						OR BildBL = '".$entry."' 
						OR BildBC = '".$entry."' 
						OR BildBR = '".$entry."';";
			$query = mysql_query($sql);
			if($query && mysql_num_rows($query)==1){
				$row_t = mysql_fetch_assoc($query);
				if($row_t['total']<=0){
					@unlink('layout/'.$entry);
				}
			}
		}
	}
}

$dir->close();

// Bilder im falschen Verzeichnis (tmp) ins Verzeichnis layout kopieren
$dir = dir('tmp/');
while(($entry = $dir->read())!==false){
	if(preg_match('/(bmp|gif|jpg|jpeg|png)$/i', $entry)){
        if(preg_match('/(athletica100.jpg|athletica-logo.png)$/i', $entry)){
			@unlink('tmp/'.$entry);
		} else {
			$sql = "SELECT COUNT(xLayout) AS total
					  FROM layout 
					 WHERE BildT = '".$entry."' 
                        OR BildTL = '".$entry."' 
						OR BildTC = '".$entry."' 
						OR BildTr = '".$entry."' 
                        OR BildB = '".$entry."' 
						OR BildBL = '".$entry."' 
						OR BildBC = '".$entry."' 
						OR BildBR = '".$entry."';";
			$query = mysql_query($sql);
			if($query && mysql_num_rows($query)==1){
				$row_t = mysql_fetch_assoc($query);
				if($row_t['total']>0){
					if(!file_exists('layout/'.$entry)){
						@copy('tmp/'.$entry, 'layout/'.$entry);
					}
					@unlink('tmp/'.$entry);
				}
			}
		}
	}
}
$dir->close();

//
// display the 6 positions for page layout
//

$page = new GUI_Page('meeting_page_layout');
$page->startPage();
$page->printPageTitle($strPageLayout);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/meeting/print_layout.html', $strHelp, '_blank');
$menu->printMenu();
?>
<p/>

<table class='dialog'>
<form name="layout" method="POST" action="meeting_page_layout.php" enctype="multipart/form-data">
<input type="hidden" name="arg" value="save">
<input type="hidden" name="item" value="<?php echo $row['xLayout'] ?>">
<tr>
	<th class='dialog'><?php echo $strPosition; ?></th>
	<th class='dialog'><?php echo $strDisplay; ?></th>
	<th class='dialog'><?php echo $strOwnText; ?></th>
	<th class='dialog' colspan="3"><?php echo $strPicture; ?></th>
</tr>
<tr>
    <td class='dialog'><?php echo $strHeaderBig ?></td>
    <td colspan="2"><?php echo $strHeaderBigInfo ?></td>
    <td class='forms'>
        <input type="file" name="pic_t">
    </td>
    <td>
        <?php
        if(!empty($row['BildT'])){
            ?>
        <img src="layout/<?php echo $row['BildT'] ?>" height="30px" alt="">
        <input type="hidden" name="cpic_t" value="<?php echo $row['BildT'] ?>">
            <?php
        }
        ?>
    </td>
    <td>
        <?php
        if(!empty($row['BildT'])){
            ?>
        <input type="button" value="<?php echo $strPicture." ".$strDelete ?>"
            onclick="document.forms[0].cpic_t.value='';
                document.forms[0].submit()">
            <?php
        }
        ?>
    </td>
</tr>

<tr>
	<td class='dialog'><?php echo $strPosTL ?></td>
	<?php
		$dd = new GUI_ConfigDropDown('type_tl', 'cfgPageLayout', $row['TypTL']);
	?>
	<td class='forms'>
		<input type="text" name="text_tl" maxlenght="255" size="30" value="<?php echo $row['TextTL'] ?>"
			onchange="document.forms[0].type_tl.value = 5">
	</td>
	<td class='forms'>
		<input type="file" name="pic_tl">
	</td>
	<td>
		<?php
		if(!empty($row['BildTL'])){
			?>
		<img src="layout/<?php echo $row['BildTL'] ?>" height="30px" alt="">
		<input type="hidden" name="cpic_tl" value="<?php echo $row['BildTL'] ?>">
			<?php
		}
		?>
	</td>
	<td>
		<?php
		if(!empty($row['BildTL'])){
			?>
		<input type="button" value="<?php echo $strPicture." ".$strDelete ?>"
			onclick="document.forms[0].cpic_tl.value='';
				document.forms[0].submit()">
			<?php
		}
		?>
	</td>
</tr>

<tr>
	<td class='dialog'><?php echo $strPosTC ?></td>
	<?php
		$dd = new GUI_ConfigDropDown('type_tc', 'cfgPageLayout', $row['TypTC']);
	?>
	<td class='forms'>
		<input type="text" name="text_tc" maxlenght="255" size="30" value="<?php echo $row['TextTC'] ?>"
			onchange="document.forms[0].type_tc.value = 5">
	</td>
	<td class='forms'>
		<input type="file" name="pic_tc">
	</td>
	<td>
		<?php
		if(!empty($row['BildTC'])){
			?>
		<img src="layout/<?php echo $row['BildTC'] ?>" height="30px" alt="">
		<input type="hidden" name="cpic_tc" value="<?php echo $row['BildTC'] ?>">
			<?php
		}
		?>
	</td>
	<td>
		<?php
		if(!empty($row['BildTC'])){
			?>
		<input type="button" value="<?php echo $strPicture." ".$strDelete ?>"
			onclick="document.forms[0].cpic_tc.value='';
				document.forms[0].submit()">
			<?php
		}
		?>
	</td>
</tr>

<tr>
	<td class='dialog'><?php echo $strPosTR ?></td>
	<?php
		$dd = new GUI_ConfigDropDown('type_tr', 'cfgPageLayout', $row['TypTR']);
	?>
	<td class='forms'>
		<input type="text" name="text_tr" maxlenght="255" size="30" value="<?php echo $row['TextTR'] ?>"
			onchange="document.forms[0].type_tr.value = 5">
	</td>
	<td class='forms'>
		<input type="file" name="pic_tr">
	</td>
	<td>
		<?php
		if(!empty($row['BildTR'])){
			?>
		<img src="layout/<?php echo $row['BildTR'] ?>" height="30px" alt="">
		<input type="hidden" name="cpic_tr" value="<?php echo $row['BildTR'] ?>">
			<?php
		}
		?>
	</td>
	<td>
		<?php
		if(!empty($row['BildTR'])){
			?>
		<input type="button" value="<?php echo $strPicture." ".$strDelete ?>"
			onclick="document.forms[0].cpic_tr.value='';
				document.forms[0].submit()">
			<?php
		}
		?>
	</td>
</tr>

<tr>
    <td class='dialog'><?php echo $strFooterBig ?></td>
    <td colspan="2"><?php echo $strFooterBigInfo ?></td>
    <td class='forms'>
        <input type="file" name="pic_b">
    </td>
    <td>
        <?php
        if(!empty($row['BildT'])){
            ?>
        <img src="layout/<?php echo $row['BildB'] ?>" height="30px" alt="">
        <input type="hidden" name="cpic_b" value="<?php echo $row['BildB'] ?>">
            <?php
        }
        ?>
    </td>
    <td>
        <?php
        if(!empty($row['BildB'])){
            ?>
        <input type="button" value="<?php echo $strPicture." ".$strDelete ?>"
            onclick="document.forms[0].cpic_b.value='';
                document.forms[0].submit()">
            <?php
        }
        ?>
    </td>
</tr>

<tr>
	<td class='dialog'><?php echo $strPosBL ?></td>
	<?php
		$dd = new GUI_ConfigDropDown('type_bl', 'cfgPageLayout', $row['TypBL']);
	?>
	<td class='forms'>
		<input type="text" name="text_bl" maxlenght="255" size="30" value="<?php echo $row['TextBL'] ?>"
			onchange="document.forms[0].type_bl.value = 5">
	</td>
	<td class='forms'>
		<input type="file" name="pic_bl">
	</td>
	<td>
		<?php
		if(!empty($row['BildBL'])){
			?>
		<img src="layout/<?php echo $row['BildBL'] ?>" height="30px" alt="">
		<input type="hidden" name="cpic_bl" value="<?php echo $row['BildBL'] ?>">
			<?php
		}
		?>
	</td>
	<td>
		<?php
		if(!empty($row['BildBL'])){
			?>
		<input type="button" value="<?php echo $strPicture." ".$strDelete ?>"
			onclick="document.forms[0].cpic_bl.value='';
				document.forms[0].submit()">
			<?php
		}
		?>
	</td>
</tr>

<tr>
	<td class='dialog'><?php echo $strPosBC ?></td>
	<?php
		$dd = new GUI_ConfigDropDown('type_bc', 'cfgPageLayout', $row['TypBC']);
	?>
	<td class='forms'>
		<input type="text" name="text_bc" maxlenght="255" size="30" value="<?php echo $row['TextBC'] ?>"
			onchange="document.forms[0].type_bc.value = 5">
	</td>
	<td class='forms'>
		<input type="file" name="pic_bc">
	</td>
	<td>
		<?php
		if(!empty($row['BildBC'])){
			?>
		<img src="layout/<?php echo $row['BildBC'] ?>" height="30px" alt="">
		<input type="hidden" name="cpic_bc" value="<?php echo $row['BildBC'] ?>">
			<?php
		}
		?>
	</td>
	<td>
		<?php
		if(!empty($row['BildBC'])){
			?>
		<input type="button" value="<?php echo $strPicture." ".$strDelete ?>"
			onclick="document.forms[0].cpic_bc.value='';
				document.forms[0].submit()">
			<?php
		}
		?>
	</td>
</tr>

<tr>
	<td class='dialog'><?php echo $strPosBR ?></td>
	<?php
		$dd = new GUI_ConfigDropDown('type_br', 'cfgPageLayout', $row['TypBR']);
	?>
	<td class='forms'>
		<input type="text" name="text_br" maxlenght="255" size="30" value="<?php echo $row['TextBR'] ?>"
			onchange="document.forms[0].type_br.value = 5">
	</td>
	<td class='forms'>
		<input type="file" name="pic_br">
	</td>
	<td>
		<?php
		if(!empty($row['BildBR'])){
			?>
		<img src="layout/<?php echo $row['BildBR'] ?>" height="30px" alt="">
		<input type="hidden" name="cpic_br" value="<?php echo $row['BildBR'] ?>">
			<?php
		}
		?>
	</td>
	<td>
		<?php
		if(!empty($row['BildBR'])){
			?>
		<input type="button" value="<?php echo $strPicture." ".$strDelete ?>"
			onclick="document.forms[0].cpic_br.value='';
				document.forms[0].submit()">
			<?php
		}
		?>
	</td>
</tr>
</table>

<br>
<input type="submit" value="<?php echo $strSave ?>">
</form>


<?php
$page->endPage();

?>
