<?php
	//causes an infinite loop
	//require("header.php");

	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	$catsql = mysqli_real_escape_string($db, "SELECT * FROM categories ORDER BY cat ASC;");
	$catresult = mysqli_query($db, $catsql);

	echo "<h1>Categories</h1>";
	echo "<ul>";
	echo "<li><a href='index.php'>View All</a></li>";

	while($catrow = mysqli_fetch_assoc($catresult)) {
		echo "<li><a href='index.php?id=" . $catrow['id'] . "'>" .
			$catrow['cat'] . "</a></li>";
	}

	echo "</ul>";
?>
