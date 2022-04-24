<?php
	require_once("config.php");
	require_once("functions.php");

	//pf_validate_number checks isset, however, was getting an error on this page
	//function will return 0 if not set and second param is not "redirect"
	if(isset($_GET['id'])) {
		$validid = pf_validate_number($_GET['id'], "value", $config_basedir);
	} else {
		$validid = 0;
	}

	require("header.php");

	//if no category has been selected
	if($validid == 0) {
		//$sql = mysqli_real_escape_string($db, "SELECT items.* FROM items WHERE dateends > NOW()");
		$sql = $db->prepare("SELECT * FROM items WHERE dateends > NOW();");
		//no params to bind
	} else {
		//$sql = mysqli_real_escape_string($db, "SELECT * FROM items WHERE dateends > NOW()" .
			//"AND cat_id = " .	$validid . ";");
		$sql = $db->prepare("SELECT * FROM items WHERE dateends > NOW() AND cat_id=?;");
		$sql->bind_param("i", $validid);
	}

	//$result = mysqli_query($db, $sql);
	$sql->execute();
	$result = $sql->get_result();

	$numrows = mysqli_num_rows($result);

	//this line doesn't show up?
	echo "<h1>Items available</h1>";
	//table of available items
	echo "<table cellpadding='5'>";
	echo "<tr>";
	//column headings
	echo "<th>Image</th>";
	echo "<th>Item</th>";
	echo "<th>Bids</th>";
	echo "<th>Price</th>";
	echo "<th>End Date</th>"; //book didn't have this for some reason??
	echo "</tr>";

	//column data
	if($numrows == 0) {
		echo "<tr><td colspan=4>No items!</td></tr>";
	} else {
		while($row = mysqli_fetch_assoc($result)) {

			//Image
			//replaced with prep stmt
			/*$imagesql = mysqli_real_escape_string($db, "SELECT * FROM images WHERE item_id = " .
				$row['id'] . " LIMIT 1");
			$imageresult = mysqli_query($db, $imagesql);*/

			$imagesql = $db->prepare("SELECT * FROM images WHERE item_id=? LIMIT 1;");
			$imagesql->bind_param("i", $row['id']);
			$imagesql->execute();
			$imageresult = $imagesql->get_result();

			$imagenumrows = mysqli_num_rows($imageresult);

			echo "<tr>";
			if($imagenumrows == 0) {
				echo "<td>No image</td>";
			} else {
				$imagenumrows = mysqli_fetch_assoc($imageresult);
				echo "<td><img src='" . $config_basedir . "Images/" . $imagenumrows['name'] .
					"' width='100'></td>";
			}

			//item name
			echo "<td>";
			echo "<a href='itemdetails.php?id=" . $row['id'] .
				"'>" . $row['name'] . "</a>";

			//show button to edit item if user submitted item
			//check if session variable is set and if so, is it equal to userID
			if(isset($_SESSION['USERID']) && $_SESSION['USERID'] == $row['user_id']) {
				echo " - [<a href='edititem.php?id=" . $row['id'] .
					"'>edit</a>]";
			}
			echo "</td>";

			//number of bids
			//replaced with prep stmt
			/*$bidsql = mysqli_real_escape_string($db, "SELECT item_id, MAX(amount) AS highestbid," .
				" COUNT(id) AS numberofbids FROM bids WHERE item_id=" .
				$row['id'] . " GROUP BY item_id;");
			$bidresult = mysqli_query($db, $bidsql);*/

			$bidsql = $db->prepare("SELECT item_id, MAX(amount) AS highestbid, " .
				"COUNT(id) AS numberofbids FROM bids WHERE item_id=? GROUP BY item_id;");
			$bidsql->bind_param("i", $row['id']);
			$bidsql->execute();
			$bidresult = $bidsql->get_result();

			$bidrow = mysqli_fetch_assoc($bidresult);
			$bidnumrows = mysqli_num_rows($bidresult);

			echo "<td>";
			if($bidnumrows == 0) {
				echo "0";
			} else {
				echo $bidrow['numberofbids'] . "</td>";
			}

			//price, shows highest bid or starting price if no bids
			echo "<td>" . $config_currency;
			if($bidnumrows == 0) {
				echo sprintf('%.2f', $row['startingprice']);
			} else {
				echo sprintf('%.2f', $bidrow['highestbid']);
			}
			echo "</td>";

			//end date of auction
			echo "<td>" . date("D jS F Y g.iA", strtotime($row['dateends'])) . "</td>";
			echo "</tr>";
		}
		$sql->close();
		$imagesql->close();
		$bidsql->close();
	}

	echo "</table>";
	require("footer.php");
?>
