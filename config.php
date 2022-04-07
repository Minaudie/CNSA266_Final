<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $config_forumsname; ?></title>
	<meta http-equiv="content-type"	content="text/html; charset=iso-8859-1"/>
</head>
<body>
	<p>
		<?php
			//database information
			$dbhost = "localhost";
			$dbuser = "root";
			$dbpassword = "";
			$dbdatabase = "auction";

			// Admin information
			$config_admin = "Miranda Dorosz";
			$config_adminemail = "mdorosz202 AT stevenscollege DOT edu";

			// location of files on web server
			$config_basedir = "http://localhost/CNSA266_Final/";

			// The currency used on the auction
			$config_currency = "$";
		?>
	</p>
</body>
</html>
