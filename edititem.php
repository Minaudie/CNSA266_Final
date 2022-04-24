<?php
  //session_start();

  require_once("config.php");
  require_once("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  //TODO: possible change to $_REQUEST
	if(isset($_POST['id'])) {
    $validid = pf_validate_number($_POST['id'], "redirect", "index.php");
  } elseif(isset($_GET['id'])) {
    $validid = pf_validate_number($_GET['id'], "redirect", "index.php");
  } else {
    $url = $config_basedir . "index.php";
    redirect($url);
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
  </p><!-- TODO change action -->
  <form action="newitem.php" method="POST">
    <table>
      <?php
        //replaced by prepared statement
        //$catsql = mysqli_real_escape_string($db, "SELECT * FROM categories ORDER BY cat;");
        //$catresult = mysqli_query($db, $catsql);

        //prepared statement stage 1
        $catsql = $db->prepare("SELECT * FROM categories ORDER BY cat;");
        //prepared statement stage 2
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
                echo "<option value='" . $catrow['id'] . "'>" . $catrow['cat'] .
                  "</option>";
              }
            ?>
          </select>
        </td>
      </tr>
      <tr>
        <td>Item name</td>
        <td><input type="text" name="name"></td>
      </tr>
      <tr>
        <td>Item Description</td>
        <td>
          <textarea name="description" rows="10" cols="50"></textarea>
        </td>
      </tr>
      <tr>
        <td>Ending Date</td>
        <td>
          <?php
            //attempting to set min date to today + 1, not working
            //TODO: set min date value or check min date later
            $date = new DateTime('1 days');
            $dtMin = $date->format('d-m-Y\TH:i:s');
            echo '<input type="datetime-local" name="dateTimeSelect" min="$dtMin">';
          ?>
        </td>
      </tr>
      <tr>
        <td>Price</td>
        <td>
          <?php echo $config_currency; ?>
          <input type="text" name="price">
        </td>
      </tr>
      <tr>
        <td></td>
        <td><input type="submit" name="submit" value="Edit Images"></td>
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
      //replaced by prepared statement
      /*$itemsql = mysqli_real_escape_string($db, "INSERT INTO" . " items(user_id, cat_id," .
        " name, startingprice, " . "description, dateends)" . "VALUES(" .
        $_SESSION['USERID'] . ", '" . $_POST['cat'] . ", '" .
        addslashes($_POST['name']) . "', " . $_POST['price'] . ", '" .
        addslashes($_POST['description']) . "', '" . $concatdate . "');");*/

      $userid = $_SESSION['USERID'];
      $category = $_POST['cat'];
      $itemname = $_POST['name'];
      $itemprice = $_POST['price'];
      $itemdesc = $_POST['description'];
      $date = $_POST['dateTimeSelect'];

      //TODO: change to update
      //prep stmt stage 1
      $itemsql = $db->prepare(
        "INSERT INTO items(user_id, cat_id, name, startingprice, description, dateends)" .
        " VALUES(?,?,?,?,?,?);");
      //prep stmt stage 2
      $itemsql->bind_param("iisdss", $userid, $category, $itemname, $itemprice,
        $itemdesc, $date);
      $itemsql->execute();
      //get the id of the previously inserted record
      $item_id = $itemsql->insert_id;

      //mysqli_query($db, $itemsql);

      //TODO: add images to this page?
      //if successful, go to add images page
      $url = $config_basedir . "addimages.php?id=" . $item_id;
      redirect($url);
    //} else {
      //$url = $config_basedir . "newitem.php?error=date";
      //redirect($url);
    //}
  }

require("footer.php");
?>
