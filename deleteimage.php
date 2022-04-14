<?php
  require("config.php");
  require("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  $validimageid = pf_validate_number($_GET['image_id'], "redirect", $config_basedir);
  $validitemid = pf_validate_number($_GET['item_id'], "redirect", $config_basedir);

  if($_POST['submityes']) {
    $imagesql = mysqli_real_escape_string("SELECT name FROM images WHERE id=" .
    $validimageid);
    $imageresult = mysqli_query($db, $imagesql);
    $imagesrow = mysqli_fetch_assoc($imageresult);

    unlink("./images/" . $imagerow['name']);

    $delsql = mysqli_real_escape_string("DELETE FROM images WHERE id=" .
    $validimageid);
    mysqli_query($db, $delsql);

    header("Location: " . $config_basedir . "addimages.php?id=" . $validitemid);
  } elseif($_POST["submitno"]) {
    header("Location: " . $config_basedir . "addimages.php?id=" . $validitemid);
  } else {
    require("header.php");
?>

<h2>Delete iamge?</h2>
<form action="<?php echo pf_script_with_get($SCRIPT_NAME); ?>" method="POST">
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
