<html>
<head>
<link rel="stylesheet" href="../css/stylesheet.css" type="text/css">
</head>
<body>
<?php

if($_POST['arg'] == "save"){
	echo '<b>Ausgabe:</b><br/>';
	foreach($_POST as $key => $val){
		if(!empty($val) && $key!='blah' && $key!='arg'){
			echo "\$$key = \"".addslashes($val)."\";\r\n";
		}
	}
	echo '<br/><br/><br/>';
}

$german = array();
$french = array();
$english = array();
$value = false;
$current = "";

$fh = fopen('german.inc.php','r');
while(!feof($fh)){
	$line = fgets($fh, '4096');
	if(substr($line,0,1) == "\$"){
		$current = trim(substr($line,1, (strpos($line,"=")-1)));
		$german[$current] = substr($line, (strpos($line,"=")+1));
		$value = true;
	}else{
		if($value){
			$german[$current] .= $line;
		}
	}
}
fclose($fh);

$datei = (isset($_GET['datei']) && file_exists($_GET['datei'])) ? $_GET['datei'] : 'french.inc.php';

$fh = fopen($datei,'r');
while(!feof($fh)){
	$line = fgets($fh, '4096');
	if(substr($line,0,1) == "\$"){
		$current = trim(substr($line,1, (strpos($line,"=")-1)));
		$french[$current] = substr($line, (strpos($line,"=")+1));
		$value = true;
	}else{
		if($value){
			$french[$current] .= $line;
		}
	}
}
fclose($fh);

/*$fh = fopen('english.inc.php','r');
while(!feof($fh)){
	$line = fgets($fh, '4096');
	if(substr($line,0,1) == "\$"){
		$current = trim(substr($line,1, (strpos($line,"=")-1)));
		$english[$current] = substr($line, (strpos($line,"=")+1));
		$value = true;
	}else{
		if($value){
			$english[$current] .= $line;
		}
	}
}
fclose($fh);*/

// compare with german as base
?>
<form method="post" action="translate.php" name="form">
<table class='dialog'>
<?php
foreach($german as $key => $text){
	if(!isset($french[$key])){
		?>
		<tr>
			<th valign='top'><?php echo $key; ?></td>
			<th><?php echo $text; ?></td>
		</tr>
		
		<tr>
			<td align='right'>f: </td>
			<td><input type="text" name="<?php echo $key ?>" value="<?php echo $_POST[$key] ?>" size=100></td>
		</tr>
		<?php
	}
		/*?>
		<tr>
			<th valign='top'><?php echo $key; ?></td>
			<th><?php echo $text; ?></td>
		</tr>
		<?php*/
}

?>
</table>
<input type="hidden" name="arg" value="save">
<input type="submit" name="blah">
</form>
</body>
</html>
