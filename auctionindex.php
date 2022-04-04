<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>PHP Code Blocks</title>
<meta http-equiv="content-type"
	content="text/html; charset=iso-8859-1"/>
</head>
<body>
<p>
<?php
require(“config.php”);
require(“functions.php”);
require(“functions.php”);
$validid = pf_validate_number($_GET[‘id’], “value”, $config_basedir);
To begin displaying information on the page, add the header.php code:
$validid = pf_validate_number($_GET[‘id’], “value”, $config_basedir);
require(“header.php”);
?>
</p>
</body>
</html>