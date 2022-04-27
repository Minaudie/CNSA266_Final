<?php
	require_once("config.php");
  require_once("functions.php");

	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//TODO: possible change to $_REQUEST
	/*if(isset($_POST['id'])) {
    $validid = pf_validate_number($_POST['id'], "redirect", "index.php");
  } elseif(isset($_GET['id'])) {
    $validid = pf_validate_number($_GET['id'], "redirect", "index.php");
  } else {
    $url = $config_basedir . "index.php";
    redirect($url);
  }*/

  if(isset($_REQUEST['id'])) {
    $validid = pf_validate_number($_REQUEST['id'], "redirect", "index.php");
  } else {
    $url = $config_basedir . "index.php";
    redirect($url);
  }
?>

<form action="itemdetails.php" method="POST">
	<!-- used to keep item ID after submit -->
	<input type="hidden" name="id" value="<?php echo $validid ?>">
	<!-- continued around line 205 -->

<?php

  if(isset($_POST['submit']) && $_POST['submit']) {

		if(isset($_POST['bid']) && is_numeric($_POST['bid']) == FALSE) {
      $url = $config_basedir . "itemdetails.php?id=" . $validid . "&error=letter";
			redirect($url);
    }

		$theitemsql = $db->prepare("SELECT * FROM items WHERE id=?;");
		$theitemsql->bind_param("i", $validid);
		$theitemsql->execute();
		$theitemresult = $theitemsql->get_result();

		$theitemrow = mysqli_fetch_assoc($theitemresult);

		$checkbidsql = $db->prepare("SELECT item_id, MAX(amount) AS highestbid, COUNT(id)" .
			" AS number_of_bids FROM bids WHERE item_id=? GROUP BY item_id;");
		$checkbidsql->bind_param("i", $validid);
		$checkbidsql->execute();
		$checkbidresult = $checkbidsql->get_result();

    $checkbidnumrows = mysqli_num_rows($checkbidresult);

    if($checkbidnumrows == 0) {

			if($theitemrow['startingprice'] > $_POST['bid']) {
        $url = $config_basedir . "itemdetails.php?id=" . $validid . "&error=lowprice#bidbox";
				redirect($url);
      }
    } else {
      $checkbidrow = mysqli_fetch_assoc($checkbidresult);

      if($checkbidrow['highestbid'] > $_POST['bid']) {
        $url = $config_basedir . "itemdetails.php?id=" . $validid . "&error=lowprice#bidbox";
				redirect($url);
      }
		}
		$theitemsql->close();
		$checkbidsql->close();

		$inssql = $db->prepare("INSERT INTO bids(item_id, amount, user_id) " .
			"VALUES(?,?,?);");
		$inssql->bind_param("idi", $validid, $_POST['bid'], $_SESSION['USERID']);
		$inssql->execute();
		$inssql->close();

    $url = $config_basedir . "itemdetails.php?id=" . $validid;
		redirect($url);

  } else {
	  require_once("header.php");

		$itemsql = $db->prepare("SELECT UNIX_TIMESTAMP(dateends) AS dateepoch, items.* " .
			"FROM items WHERE id=?;");
		$itemsql->bind_param("i", $validid);
		$itemsql->execute();
		$itemresult = $itemsql->get_result();

	  $itemrow = mysqli_fetch_assoc($itemresult);

		//mktime w/o args is deprecated
	  $nowepoch = time();
	  $rowepoch = $itemrow['dateepoch'];

	  if($rowepoch > $nowepoch) {
	    $VALIDAUCTION = 1;
	  }

	  echo "<h2>" . $itemrow['name'] . "</h2>";

		$imagesql = $db->prepare("SELECT * FROM images WHERE item_id=?;");
		$imagesql->bind_param("i", $validid);
		$imagesql->execute();
		$imageresult = $imagesql->get_result();

	  $imagenumrows = mysqli_num_rows($imageresult);

		$bidsql = $db->prepare("SELECT item_id, MAX(amount) AS highestbid, COUNT(id) " .
			"AS number_of_bids FROM bids WHERE item_id=? GROUP BY item_id;");
		$bidsql->bind_param("i", $validid);
		$bidsql->execute();
		$bidresult = $bidsql->get_result();

	  $bidnumrows = mysqli_num_rows($bidresult);

	  echo "<p>";

	  if($bidnumrows == 0) {
	    echo "<strong>This item has no bids</strong> - <strong>Starting Price</strong>: " .
	      $config_currency . sprintf('%.2f', $itemrow['startingprice']);
	  } else {
	    $bidrow = mysqli_fetch_assoc($bidresult);
	    echo "<strong>Number of Bids</strong>: " . $bidrow['number_of_bids'] .
	      " - <strong>Current Price</strong>: " . $config_currency .
	      sprintf('%.2f', $bidrow['highestbid']);
	  }

		$bidsql->close();

	  echo " - <strong>Auction ends</strong>: " . date("D jS F Y g.iA", $rowepoch);
	  echo "</p>";

	  if($imagenumrows == 0) {
	    echo "No images";
	  } else {
	    while($imagerow = mysqli_fetch_assoc($imageresult)) {
	      echo "<img src='./images/" . $imagerow['name'] . "' width='200'>";
	    }
	  }

		$imagesql->close();

		//nl2br inserts <br> before all newlines in ()
	  echo "<p>" . nl2br($itemrow['description']) . "</p>";

		$itemsql->close();

	  echo "<a name='bidbox'>";
	  echo "<h2>Bid for this item</h2>";

	  if(isset($_SESSION['USERNAME']) == FALSE) {
	    echo "To bid, you need to login. <a href='login.php?id=" .
	      $validid . "&ref=addbid'>Login here</a>";
	  } else {
	    if($VALIDAUCTION == 1) { //TODO: check for possible errors
	      echo "Enter the bid amount into the box below.";
	      echo "<p>";

				if(isset($_GET['error'])) {
					switch($_GET['error']) {
		        case "lowprice":
		          echo "The bid entered is too low. Please enter another price.";
		          break;
		        case "letter":
		          echo "The value entered is not a number.";
		          break;
		        default:
		          echo "Unknown error.";
		          break;
		      }
				}

?>
	<!-- started around line 25 -->
  <table>
    <tr>
      <td><input type="text" name="bid"></td>
      <td><input type="submit" name="submit" value="Bid!"></td>
    </tr>
  </table>
</form>

<?php
      } else { //valid auction else
        echo "This auction has now ended.";
      }

			$historysql = $db->prepare("SELECT bids.amount, users.username FROM bids " .
				", users WHERE bids.user_id = users.id AND item_id=? ORDER BY amount DESC;");
			$historysql->bind_param("i", $validid);
			$historysql->execute();
			$historyresult = $historysql->get_result();

      $historynumrows = mysqli_num_rows($historyresult);

      if($historynumrows >= 1) {
        echo "<h2>Bid History</h2>";
        echo "<ul>";

        while($historyrow = mysqli_fetch_assoc($historyresult)) {
          echo "<li>" . $historyrow['username'] . " - " .
            $config_currency . sprintf('%.2f', $historyrow['amount']) .
            "</li>";
        }

        echo "</ul>";
      }

			$historysql->close();
    } //close of login else
	}
	require_once("footer.php");
?>
