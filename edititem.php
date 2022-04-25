<?php
  //session_start();

  require_once("config.php");
  require_once("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  //TODO: possible change to $_REQUEST
/*	if(isset($_POST['id'])) {
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

  //get information from database
  $infosql = $db->prepare("SELECT * FROM items WHERE id=?;");
  $infosql->bind_param("i", $validid);
  $infosql->execute();
  $inforesult = $infosql->get_result();
  $infoassoc = mysqli_fetch_assoc($inforesult);

  //determine price to be show: aka highest bid or startingprice
  $checkbidsql = $db->prepare("SELECT item_id, MAX(amount) AS highestbid, COUNT(id)" .
    " AS number_of_bids FROM bids WHERE item_id=? GROUP BY item_id;");
  $checkbidsql->bind_param("i", $validid);
  $checkbidsql->execute();
  $checkbidresult = $checkbidsql->get_result();
  $checkbidnumrows = mysqli_num_rows($checkbidresult);
  $price = 0;
  if($checkbidnumrows == 0) { //no bids
    $price = $infoassoc['startingprice'];
  } else { //get highest bid
    $checkbidrow = mysqli_fetch_assoc($checkbidresult);
    $price = $checkbidrow['highestbid'];
  }

  require_once("header.php");
?>
  <h1>Edit Existing Item</h1>
  <strong>Step 1</strong> - Edit your item details.
  <p>
    <?php
      if(isset($_GET['error'])) {
        switch($_GET['error']) {
          case "date":
            echo "<strong>Invalid date - Please choose another!</strong>";
            break;
          default:
            echo "Unknown error.";
            break;
        }
      }
    ?>
  </p>
  <form action="edititem.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $validid; ?>">
    <table>
      <?php
        $catsql = $db->prepare("SELECT * FROM categories ORDER BY cat;");
        //no bind_param as there are no variables to insert
        $catsql->execute();
        //getting result
        $catresult = $catsql->get_result();

      ?>
      <tr>
        <td>Category</td>
        <td>
          <select name="cat">
            <?php
              while($catrow = mysqli_fetch_assoc($catresult)) {
                echo "<option value='" . $catrow['id'] . "' ";
                //set up default selection based on item's category
                if($infoassoc['cat_id'] == $catrow['id']) {
                  echo "selected";
                }
                echo ">" . $catrow['cat'] . "</option>";
              }
            ?>
          </select>
        </td>
      </tr>
      <tr>
        <td>Item name</td>
        <td><input type="text" name="name"
              value="<?php echo $infoassoc['name']; ?>"></td>
      </tr>
      <tr>
        <td>Item Description</td>
        <td><!-- in one line otherwise it adds extra white space -->
          <textarea name="description" rows="10" cols="50"><?php echo $infoassoc['description']; ?></textarea>
        </td>
      </tr>
      <tr>
        <td>Ending Date</td>
        <td>
          <input type="datetime-local" name="dateTimeSelect"
            value="<?php $ed = new DateTime($infoassoc['dateends']);
              $enddate = $ed->format('Y-m-d\TH:i');
              echo $enddate; ?>"
            min="<?php $md = new DateTime('+1 day');
              $mindate = $md->format('Y-m-d\TH:i');
              echo $mindate; ?>">
        </td>
      </tr>
      <tr>
        <td>Price</td>
        <td>
          <?php echo $config_currency; ?>
          <input type="text" name="price"
            value="<?php echo $price; ?>">
        </td>
      </tr>
      <tr>
        <td></td>
        <td><input type="submit" name="submit" value="Save"></td>
      </tr>
    </table>
  </form>
<?php

  //TODO: test
  if(isset($_SESSION['USERNAME']) == FALSE) {
    $url = $config_basedir . "login.php?ref=edititem&id=" . $validid;
    redirect($url);
  }

  //will check left and if false, will not check right
  if(isset($_POST['submit']) && $_POST['submit']) {

      $userid = $_SESSION['USERID'];
      $category = $_POST['cat'];
      $itemname = $_POST['name'];
      $itemprice = $_POST['price'];
      $itemdesc = $_POST['description'];
      $date = $_POST['dateTimeSelect'];

      //update item information
      $itemsql = $db->prepare(
        "UPDATE items SET cat_id=?, name=?, startingprice=?, description=?, dateends=? " .
        "WHERE id=?;");
      $itemsql->bind_param("isdssi", $category, $itemname, $itemprice, $itemdesc, $date, $validid);
      $itemsql->execute();

      //if successful, go to add images page
      $url = $config_basedir . "addimages.php?id=" . $validid;
      $itemsql->close();
      redirect($url);
  }

  $infosql->close();
  $checkbidsql->close();
  $catsql->close();

  require("footer.php");
?>
