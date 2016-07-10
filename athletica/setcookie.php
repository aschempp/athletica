<?php
$name = $_GET['name'];
$value = $_GET['value'];

setcookie($name, $value, strtotime('+5 years'));
?>