<?php

/****************
 *
 * status.php
 * ----------
 *
 */

require('./lib/common.lib.php');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title>status</title>

	<link rel="stylesheet" href="css/navigation.css" type="text/css">
</head>

<body class='status'>
<?php
	if(!empty($_GET['msg'])) {
		echo $_GET['msg'];
	}
	else if(!empty($_POST['msg'])) {
		echo $_POST['msg'];
	}
	else if(!empty($php_errormsg)) {
			echo "$strError: $php_errormsg";
	}
	else {
		echo "$strOK";
	}
?>
</body>
</html>
