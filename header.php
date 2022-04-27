<?php
	//added save path to php.ini
	//session.save_path = "C:\php\tmp"
	//except it decided to stop working properly and won't accept my config change
	ini_set('session.save_path', 'C:\php\tmp');
	session_start();

	require_once("config.php");

	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo $config_forumsname; ?></title>
	<link rel="stylesheet" href="stylesheet.css" type="text/css">
</head>
<body>
	<div id="header">
		<h1>Auctions</h1>

		<div id="menu">
			<a href="index.php">Home</a>

			<?php
				if(isset($_SESSION['USERNAME'])) {
					echo "<a href='logout.php'>Logout</a>";
				} else {
					echo "<a href='login.php'>Login</a>";
				}
			?>

			<a href="newitem.php">New Item</a>
		</div>
		<div id="container">
			<div id="bar">
				<?php	require_once("bar.php");	?>
			</div>
			<div id="main">
<!-- closing tags in other files -->
