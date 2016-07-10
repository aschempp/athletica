<html>
<head>
<link rel="stylesheet" href="../css/stylesheet.css" type="text/css">
</head>
<body>
<?php
mysql_connect("localhost", "root", "root");
mysql_select_db("athletica");


if($_POST['arg'] == "save"){
	
	$res = mysql_query("SELECT 
				PosTop, PosLeft, Seite
			FROM
				faq
			WHERE	xFaq = ".$_POST['faq']."");
	$row = mysql_fetch_array($res);
	mysql_free_result($res);
	
	mysql_query("INSERT INTO faq  (
				Frage, Antwort
				, Zeigen, PosTop, PosLeft, Seite
				, Sprache
			)
			VALUES( 
				'".htmlentities($_POST['question'])."', '".htmlentities($_POST['answer'])."'
				, 'y', ".$row[0].", ".$row[1].", '".$row[2]."'
				, '".$_POST['lang']."'
			)");
	echo mysql_error();
}

// foreign languages are:
$l = array("fr", "it");
asort($l);

$res = mysql_query("select * from faq where Sprache = 'de'");

// compare with german as base
?>

<table class='dialog' width="800">
<?php

while($row = mysql_Fetch_assoc($res)){
	
	?>
	<tr>
		<th> </th>
		<th><?php echo $row['Frage'] ?></th>
		<th><?php echo $row['Antwort'] ?></th>
	</tr>
	<?php
	
	// get same faq for other lang (we have no faq+lang identifier so compare site and positions)
	$res2 = mysql_query("select * from faq where Sprache != 'de' and Seite = '".$row['Seite']."'
				and PosTop = ".$row['PosTop']." and PosLeft = ".$row['PosLeft']." order by Sprache ASC");
	$row2 = mysql_fetch_assoc($res2);
	foreach($l as $lang){
		
		if($row2 && $row2['Sprache'] == $lang){
			
			
			$row2 = mysql_fetch_assoc($res2);
		}else{
			?>
	<tr>
		<form method="post" action="translate_faq.php" name="form">
		<td><?php echo $lang ?></td>
		<td class='forms'><input type="text" size="30" name="question"></td>
		<td class='forms'><textarea rows="7" cols="50" name="answer"></textarea>
			<input type="submit" name="blah" value="insert"></td>
		<input type="hidden" name="arg" value="save">
		<input type="hidden" name="lang" value="<?php echo $lang ?>">
		<input type="hidden" name="faq" value="<?php echo $row['xFaq'] ?>">
		</form>
	</tr>
			<?php
		}
		
	}
	
}

?>
</table>
</body>
</html>
