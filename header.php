<?php
	session_start();

	require("config.php");

	$db = mysql_connect($dbhost, $dbuser, $dbpassword);
	mysql_select_db($dbdatabase, $db);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $config_forumsname; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
	<link rel="stylesheet" href="stylesheet.css" type="text/css">
</head>
<body>
	<div id="header">
		<h1>Auctions</h1>

		<div id="menu">
			<a href="index.php">Home</a>
			<?php
				if(isset($_SESSION['USERNAME']) == TRUE) {
					echo "<a href='logout.php'>Logout</a>";
				} else {
					echo "<a href='login.php'>Login</a>";
				}
			?>

			<a href="newitem.php">New Item</a>
		</div>
		<div id="container">
			<div id="bar">
				<?php
					require("bar.php");
				?>
			</div>
			<div id="main">
<!-- closing tags in other files -->
