<?php
  session_start();

  require("config.php");
  require("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  if(isset($_SESSION['USERNAME']) == FALSE) {
    header("Location: " . $config_basedir . "/login.php?ref=newitem");
  }

  if($_POST['submit']) {
    $validdate = checkdate($_POST['month'], $_POST['day'], $_POST['year']);

    if($validdate == TRUE) {
      $concatdate = $_POST['year'] . "-" . sprintf("%02d", $_POST['day']) .
        "-" . sprintf("%02d", $_POST['month']) . " " . $_POST['hour'] .
        ":" . $_POST['minute'] . ":00";

      $itemsql = mysqli_real_escape_string("INSERT INTO" . " items(user_id, cat_id," .
        " name, startingprice, " . "description, dateends)" . "VALUES(" .
        $_SESSION['USERID'] . ", '" . $_POST['cat'] . ", '" .
        addslashes($_POST['name']) . "', " . $_POST['price'] . ", '" .
        addslashes($_POST['description']) . "', '" . $concatdate . "');");

      mysqli_query($db, $itemsql);
      $item_id = mysqli_insert_id();

      header("Location: " . $config_basedir . "/addimages.php?id=" . $itemid);
    } else {
      header("Location: " . $config_basedir . "/newitem.php?error=date");
    }
  } else {
    require("header.php");
?>

<h1>Add a new item</h1>
<strong>Step 1</strong> - Add your item details.
<p>
  <?php
    switch($_GET['error']) {
      case "date":
        echo "<strong>Invalid date - Please choose another!</strong>";
        break;
      default:
        echo "Unknown error.";
        break;
    }
  ?>
</p>
<form action="<?php echo pf_script_with_get($SCRIPT_NAME); ?>" method="POST">
  <table>
    <?php
      $catsql = mysqli_real_escape_string("SELECT * FROM categories ORDER BY category;");
      $catresult = mysqli_query($db, $catsql);
    ?>
    <tr>
      <td>Category</td>
      <td>
        <select name="cat">
          <?php
            while($catrow = mysqli_fetch_assoc($catresult)) {
              echo "<option value='" . $catrow['id'] . "'>" . $catrow['category'] .
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
            <td>Day</td>
            <td>Month</td>
            <td>Year</td>
            <td>Hour</td>
            <td>Minute</td>
          </tr>
          <tr>
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
              <select name="month">
                <?php
                  for($i=1;$i<=12;$i++) {
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
}

require("footer.php");
?>
