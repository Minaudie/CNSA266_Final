<?php
	session_start();
  require("config.php");
  require("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

  if(isset($_REQUEST['image_id']) && isset($_REQUEST['item_id'])) {
    $validimageid = pf_validate_number($_REQUEST['image_id'], "redirect", $config_basedir);
    $validitemid = pf_validate_number($_REQUEST['item_id'], "redirect", $config_basedir);
  } else {
    $url = $config_basedir . "index.php";
    redirect($url);
  }

?>
  <form action="deleteimage.php" method="POST">
    <!-- used to keep item ID after submit -->
    <input type="hidden" name="item_id" value="<?php echo $validitemid ?>">
    <!-- used to keep image ID after submit -->
    <input type="hidden" name="image_id" value="<?php echo $validimageid ?>">
    <!-- continued around line 55 -->
<?php

	if(isset($_SESSION['USERNAME']) == FALSE) {
		$url = $config_basedir . "login.php";
		redirect($url);
	}

  //if yes to delete image
  if(isset($_POST['submityes']) && $_POST['submityes']) {

    $imagesql = $db->prepare("SELECT name FROM images WHERE id=?;");
    $imagesql->bind_param("i", $validimageid);
    $imagesql->execute();
    $imageresult = $imagesql->get_result();

    $imagerow = mysqli_fetch_assoc($imageresult);

    //deletes file given as parameter
    //needs to be direct file path
    unlink("C:\CNSA266_Final\Images\\" . $imagerow['name']);

    $imagesql->close();

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
