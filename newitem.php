<?php
	session_start();
  require_once("config.php");
  require_once("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

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
          <input type="datetime-local" name="dateTimeSelect"
            min="<?php $md = new DateTime('+1 day');
              $mindate = $md->format('Y-m-d\TH:i');
              echo $mindate; ?>">
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

  if(isset($_SESSION['USERNAME']) == FALSE) {
    $url = $config_basedir . "login.php?ref=newitem";
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

    $itemsql = $db->prepare(
      "INSERT INTO items(user_id, cat_id, name, startingprice, description, dateends,endnotified)" .
      " VALUES(?,?,?,?,?,?,0);");
    $itemsql->bind_param("iisdss", $userid, $category, $itemname, $itemprice,
      $itemdesc, $date);
    $itemsql->execute();
    //get the id of the previously inserted record
    $item_id = $itemsql->insert_id;

    //if successful, go to add images page
    $url = $config_basedir . "addimages.php?id=" . $item_id;
    redirect($url);
  }

  require("footer.php");
?>
