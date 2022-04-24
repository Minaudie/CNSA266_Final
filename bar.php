<?php
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//replaced with prep stmt 
	/*$catsql = mysqli_real_escape_string($db, "SELECT * FROM categories ORDER BY cat ASC;");
	$catresult = mysqli_query($db, $catsql);*/

	$catsql = $db->prepare("SELECT * FROM categories ORDER BY cat ASC;");
	$catsql->execute();
	$catresult = $catsql->get_result();

	echo "<h1>Categories</h1>";
	echo "<ul>";
	echo "<li><a href='index.php'>View All</a></li>";

	while($catrow = mysqli_fetch_assoc($catresult)) {
		echo "<li><a href='index.php?id=" . $catrow['id'] . "'>" .
			$catrow['cat'] . "</a></li>";
	}

	echo "</ul>";
?>
