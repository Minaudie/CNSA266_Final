<?php
	require("header.php");

	$catsql = mysqli_real_escape_string("SELECT * FROM categories ORDER BY category ASC;");
	$catresult = msqli_query($db, $catsql);

	echo "<h1>Categories</h1>";
	echo "<ul>";
	echo "<li><a href='index.php'>View All</a></li>";

	while($catrow = mysqli_fetch_assoc($catresult)) {
		echo "<li><a href='index.php?id=" . $catrow['id'] . "'>" .
			$catrow['category'] . "</a></li>";
	}

	echo "</ul>";
?>
