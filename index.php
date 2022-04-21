<?php
	require("config.php");
	require("functions.php");

	$validid = pf_validate_number($_GET['id'], "value", $config_basedir);

	require("header.php");

	if($validid == 0) {
		$sql = mysqli_real_escape_string($db, "SELECT items.* FROM items WHERE dateends > NOW()");
	} else {
		$sql = mysqli_real_escape_string($db, "SELECT * FROM items WHERE dateends > NOW()" .
			"AND cat_id = " .	$validid . ";");
	}

	$result = mysqli_query($db, $sql);
	$numrows = mysqli_num_rows($result);

	echo "<h1>Items available</h1>";
	echo "<table cellpadding='5'>";
	echo "<tr>";
	echo "<th>Image</th>";
	echo "<th>Item</th>";
	echo "<th>Bids</th>";
	echo "<th>Price</th>";
	echo "</tr>";

	if($numrows == 0) {
		echo "<tr><td colspan=4>No items!</td></tr>";
	} else {
		while($row = mysqli_fetch_assoc($result)) {
			$imagesql = mysqli_real_escape_string($db, "SELECT * FROM images WHERE item_id = " .
				$row['id'] . " LIMIT 1");
			$imageresult = mysqli_query($db, $imagesql);
			$imagenumrows = mysqli_num_rows($imageresult);

			echo "<tr>";
			if($imagenumrows == 0) {
				echo "<td>No image</td>";
			} else {
				$imagerow = mysqli_fetch_assoc($imageresult);
				echo "<td><img src='./images/" . $imagerow['name'] .
					"' width='100'></td>";
			}

			echo "<td>";
			echo "<a href='itemdetails.php?id=" . $row['id'] .
				"'>" . $row['name'] . "</a>";
			if($_SESSION['USERID'] == $row['user_id']) {
				echo " - [<a href='edititem.php?id=" . $row['id'] .
					"'>edit</a>]";
			}
			echo "</td>";

			$bidsql = mysqli_real_escape_string($db, "SELECT item_id, MAX(amount) AS highestbid," .
				" COUNT(id) AS numberofbids FROM bids WHERE item_id=" .
				$row['id'] . " GROUP BY item_id;");
			$bidresult = mysqli_query($db, $bidsql);
			$bidrow = mysqli_fetch_assoc($bidresult);
			$bidnumrows = mysqli_num_rows($bidresult);

			echo "<td>";
			if($bidnumrows == 0) {
				echo "0";
			} else {
				echo $bidrow['numberofbids'] . "</td>";
			}

			echo "<td>" . $config_currency;
			if($bidnumrows == 0) {
				echo sprintf('%.2f', $row['startingprice']);
			} else {
				echo sprintf('%.2f', $bidrow['highestbid']);
			}
			echo "</td>";

			echo "<td>" . date("D jS F Y g.iA", strtotime($row['dateends'])) . "</td>";
			echo "</tr>";
		}
	}

	echo "</table>";
	require("footer.php");
?>
