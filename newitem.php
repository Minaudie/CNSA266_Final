<?php
  //session_start();

  require_once("config.php");
  require_once("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  require_once("header.php");
?>
  <h1>Add a new item</h1>
  <strong>Step 1</strong> - Add your item details.
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
  <form action="newitem.php" method="POST">
    <table>
      <?php
        //TODO: replace
        $catsql = mysqli_real_escape_string($db, "SELECT * FROM categories ORDER BY cat;");
        $catresult = mysqli_query($db, $catsql);
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
          <table>
            <tr>
              <td>Month</td>
              <td>Day</td>
              <td>Year</td>
              <td>Hour</td>
              <td>Minute</td>
            </tr>
            <tr>
              <td>
                <select name="month">
                  <?php
                    for($i=1;$i<=12;$i++) {
                      echo "<option>" . $i . "</option>";
                    }
                  ?>
                </select>
              </td>
              <td>
                <select name="day">
                  <?php
                    for($i=1;$i<=31;$i++) {
                      echo "<option>" . $i . "</option>";
                    }
                  ?>
                </select>
              </td>
              <td>
                <select name="year">
                  <?php
                    for($i=2022;$i<=2030;$i++) {
                      echo "<option>" . $i . "</option>";
                    }
                  ?>
                </select>
              </td>
              <td>
                <select name="hour">
                  <?php
                    for($i=0;$i<=23;$i++) {
                      echo "<option>" . sprintf("%02d", $i) . "</option>";
                    }
                  ?>
                </select>
              </td>
              <td>
                <select name="minute">
                  <?php
                    for($i=0;$i<=59;$i++) {
                      echo "<option>" . sprintf("%02d", $i) . "</option>";
                    }
                  ?>
                </select>
              </td>
            </tr>
          </table>
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
        <td><input type="submit" name="submit" value="Post!"></td>
      </tr>
    </table>
  </form>
<?php

  //this isn't working, currently always sends to newitems regardless of login status
  if(isset($_SESSION)) {

  } else {
    $url = $config_basedir . "login.php?ref=newitem");
    redirect($url);
  }

  //will check left and if false, will not check right
  if(isset($_POST['submit']) && $_POST['submit']) {
    $validdate = checkdate($_POST['day'], $_POST['month'], $_POST['year']);

    if($validdate) {
      $concatdate = $_POST['year'] . "-" . sprintf("%02d", $_POST['day']) .
        "-" . sprintf("%02d", $_POST['month']) . " " . $_POST['hour'] .
        ":" . $_POST['minute'] . ":00";

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

      //prep stmt stage 1
      $itemsql = $db->prepare(
        "INSERT INTO items(user_id, cat_id, name, startingprice, description, dateends)" .
        " VALUES(?,?,?,?,?,?);");
      //prep stmt stage 2
      $itemsql->bind_param("iisdss", $userid, $category, $itemname, $itemprice,
        $itemdesc, $concatdate);
      $itemsql->execute();
      //get the id of the previously inserted record
      $item_id = $itemsql->insert_id;

      //mysqli_query($db, $itemsql);

      $url = $config_basedir . "addimages.php?id=" . $item_id);
      redirect($url);
    } else {
      $url = $config_basedir . "newitem.php?error=date");
      redirect($url);
    }
  }

require("footer.php");
?>
