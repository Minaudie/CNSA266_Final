<?php
	session_start();

  include("config.php");
  include("functions.php");

	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  $validid = pf_validate_number($_GET['id'], "redirect", $config_basedir);

  if($_POST['submit']) {

		if(is_numeric($_POST['bid']) == FALSE) {
      $url = $config_basedir . "itemdetails.php?id=" . $validid . "&error=letter");
			redirect($url);
    }

    $theitemsql = mysqli_real_escape_string($db, "SELECT * FROM items WHERE id="
			. $validid . ";");
    $theitemresult = mysqli_query($db, $theitemsql);
    $theitemrow = mysqli_fetch_assoc($theitemresult);

    $checkbidsql = mysqli_real_escape_string($db, "SELECT item_id, MAX(amount)" .
		 	. "AS highestbid, COUNT(id) AS number_of_bids FROM bids WHERE item_id="
			. $validid . " GROUP BY item_id;");
    $checkbidresult = mysqli_query($checkbidsql);
    $checkbidnumrows = mysqli_num_rows($checkbidresult);

    if($checkbidnumrows == 0) {

			if($theitemrow['startingprice'] > $_POST['bid']) {
        $url = $config_basedir . "itemdetails.php?id=" . $validid . "&error=lowprice#bidbox");
				redirect($url);
      }
    } else {
      $checkbidrow = mysqli_fetch_assoc($checkbidresult);

      if($checkbidrow['highestbid'] > $_POST['bid']) {
        $url = $config_basedir . "itemdetails.php?id=" . $validid . "&error=lowprice#bidbox");
				redirect($url);
      }
		}

    $inssql = mysqli_real_escape_string($db, "INSERT INTO "
			. "bids(item_id, amount,user_id) VALUES(" . $validid . ", " .
			$_POST['bid'] . ", " . $_SESSION['USERID'] . ");");
    mysqli_query($db, $inssql);

    $url = $config_basedir . "itemdetails.php?id=" . $validid);
		redirect($url);

  } else {
	  require("header.php");

	  $itemsql = mysqli_real_escape_string($db, "SELECT UNIX_TIMESTAMP(dateends)" .
		 	"AS dateepoch, items.* FROM items WHERE id=" . $validid . ";");
	  $itemresult = mysqli_query($db, $itemsql);

	  $itemrow = mysqli_fetch_assoc($itemresult);

	  $nowepoch = mktime();
	  $rowepoch = $itemrow['dateepoch'];

	  if($rowepoch > $nowepoch) {
	    $VALIDAUCTION = 1;
	  }

	  echo "<h2>" . $itemrow['name'] . "</h2>";

	  $imagesql = mysqli_real_escape_string($db, "SELECT * FROM images WHERE item_id=" .
			$validid . ";");
	  $imageresult = mysqli_query($db, $imagesql);
	  $imagenumrows = mysqli_num_rows($imageresult);

	  $bidsql = mysqli_real_escape_string($db, "SELECT item_id, MAX(amount) AS" .
		 	" highestbid, COUNT(id) AS number_of_bids FROM bids WHERE item_id=" .
			$validid . " GROUP BY item_id;");
	  $bidresult = mysqli_query($db, $bidsql);
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

	  echo " - <strong>Auction ends</strong>: " . date("D jS F Y g.iA", $rowepoch);

	  echo "</p>";

	  if($imagenumrows == 0) {
	    echo "No images";
	  } else {
	    while($imagerow = mysqli_fetch_assoc($imageresult)) {
	      echo "<img src='./images/" . $imagerow['name'] . "' width='200'>";
	    }
	  }

	  echo "<p>" . nl2br($itemrow['description']) . "</p>";

	  echo "<a name='bidbox'>";
	  echo "<h2>Bid for this item</h2>";

	  if(isset($_SESSION['USERNAME']) == FALSE) {
	    echo "To bid, you need to login. <a href='login.php?id=" .
	      $validid . "&ref=addbid'>Login here</a>";
	  } else {
	    if($VALIDAUCTION == 1) {
	      echo "Enter the bid amount into the box below.";
	      echo "<p>";

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
?>

<form action="<?php echo pf_script_with_get($SCRIPT_NAME); ?>" method="POST">
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

      $historysql = mysqli_real_escape_string($db, "SELECT bids.amount," .
				" users.username FROM bids, users WHERE bids.user_id = users.id AND item_id=" .
        $validid . " ORDER BY amount DESC;");
      $historyresult = mysqli_query($db, $historysql);
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
    } //close of login else
}
    require("footer.php");
?>
