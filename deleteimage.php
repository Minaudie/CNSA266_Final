<?php
  require("config.php");
  require("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  //TODO: possibly change to $_REQUEST
  //need both post and get parts
  if(isset($_POST['image_id']) || isset($_POST['item_id'])) {
    $validimageid = pf_validate_number($_POST['image_id'], "redirect", $config_basedir);
    $validitemid = pf_validate_number($_POST['item_id'], "redirect", $config_basedir);
  } elseif(isset($_GET['image_id']) || isset($_POST['item_id'])) {
    $validimageid = pf_validate_number($_GET['image_id'], "redirect", $config_basedir);
    $validitemid = pf_validate_number($_GET['item_id'], "redirect", $config_basedir);
  } else {
    $url = $config_basedir . "index.php";
    //redirect($url);
  }

?>
  <form action="deleteimage.php" method="POST">
    <!-- used to keep item ID after submit -->
    <input type="hidden" name="item_id" value="<?php echo $validitemid ?>">
    <!-- used to keep image ID after submit -->
    <input type="hidden" name="image_id" value="<?php echo $validimageid ?>">
    <!-- continued around line 55 -->
<?php

  //if yes to delete image
  if(isset($_POST['submityes']) && $_POST['submityes']) {
    //replaced with prep stmt
    /*$imagesql = mysqli_real_escape_string($db, "SELECT name FROM images WHERE id=" .
    $validimageid);
    $imageresult = mysqli_query($db, $imagesql);*/

    $imagesql = $db->prepare("SELECT name FROM images WHERE id=?;");
    $imagesql->bind_param("i", $validimageid);
    $imagesql->execute();
    $imageresult = $imagesql->get_result();

    $imagerow = mysqli_fetch_assoc($imageresult);

    //deletes file given as parameter
    //needs to be direct file path
    unlink("C:\CNSA266_Final\Images\\" . $imagerow['name']);

    $imagesql->close();

    //replaced with prep stmt
    /*$delsql = mysqli_real_escape_string($db, "DELETE FROM images WHERE id=" .
    $validimageid);
    mysqli_query($db, $delsql);*/

    $delsql = $db->prepare("DELETE FROM images WHERE id=?;");
    $delsql->bind_param("i", $validimageid);
    $delsql->execute();
    $delsql->close();

    //redirect to add images
    $url = $config_basedir . "addimages.php?id=" . $validitemid;
    redirect($url);
  } elseif(isset($_POST['submitno']) && $_POST["submitno"]) {
    //if not deleting, redirect to add images
    $url = $config_basedir . "addimages.php?id=" . $validitemid;
    redirect($url);
  } else {
    require("header.php");
?>
  <!-- continued from around line 20 -->
  <h2>Delete Image?</h2>
  Are you sure you want to delete this image?
  <p>
    <input type="submit" name="submityes" value="Yes">
    <input type="submit" name="submitno" value="No">
  </p>
</form>

<?php
}
require("footer.php");
?>
