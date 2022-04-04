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
require(“header.php”);
$catsql = “SELECT * FROM categories ORDER BY category ASC;”;
$catresult = mysql_query($catsql);
echo “<h1>Categories</h1>”;
echo “<ul>”;
echo “<li><a href=’index.php’>View All</a></li>”;
while($catrow = mysql_fetch_assoc($catresult)) {
echo “<li><a href=’index.php?id=”
. $catrow[‘id’] . “‘>” . $catrow[‘category’]
. “</a></li>”;
}
echo “</ul>”;
?>
</p>
</body>
</html>