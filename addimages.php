<?php
  //session_start();

  require_once("config.php");
  require_once("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);
  $MAX_FILE_SIZE = 3000000;

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  //TODO: possibly change to $_REQUEST
  //need both post and get parts
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

  <form enctype="multipart/form-data" action="addimages.php"
    method="POST">

    <!--Did not work
    <input type="hidden" name="MAX_FILE_SIZE" value="3000000">
    -->

    <!-- used to keep item ID after submit -->
    <input type="hidden" name="id" value="<?php echo $validid ?>">

    <table>
      <tr>
        <td>Image to upload</td>
        <td><input name="userfile" type="file"></td>
      </tr>
      <td>
        <td colspan="2"><input type="submit" name="submit" value="Upload File"></td>
      </tr>
    </table>
  </form>

  When you have finished adding photos, go and
    <a href="<?php echo 'itemdetails.php?id=' . $validid; ?>">see your item!</a>

<?php

  //TODO: test ref
  if(isset($_SESSION['USERNAME']) == FALSE) {
    $url = $config_basedir . "login.php?ref=images&id=" . $validid;
    redirect($url);
  }

  //replaced with prepared statement
  //$theitemsql = mysqli_real_escape_string($db, "SELECT user_id FROM items WHERE id=" .
    //$validid . ";");
  //$theitemresult = mysqli_query($db, $theitemsql);

  //find user ID for item id from GET
  //prep stmt stage 1
  $theitemsql = $db->prepare("SELECT user_id FROM items WHERE id=?;");
  //prep stmt stage 2
  $theitemsql->bind_param("i", $validid);
  $theitemsql->execute();
  //get result
  $theitemresult = $theitemsql->get_result();

  $theitemrow = mysqli_fetch_assoc($theitemresult);

  if($theitemrow['user_id'] != $_SESSION['USERID']) {
    $url = $config_basedir . "index.php";
    redirect($url);
  }
  $theitemsql->close();

  //the original of this function made me want to scream
  if(isset($_POST['submit']) && $_POST['submit']) {
    if($_FILES['userfile']['name'] == '') {
      $url = $config_basedir . "addimages.php?error=nophoto";
      redirect($url);
    } elseif($_FILES['userfile']['size'] == 0) {
      $url = $config_basedir . "addimages.php?error=photoprob";
      redirect($url);
    } elseif($_FILES['userfile']['size'] > $MAX_FILE_SIZE) {
      $url = $config_basedir . "addimages.php?error=large";
      redirect($url);
    } elseif(!getimagesize($_FILES['userfile']['tmp_name'])) {
      $url = $config_basedir . "addimages.php?error=invalid";
      redirect($url);
    } else {
      //file will be uploaded to C:\CNSA266_Final\Images\<itemid>-<filename+ext>
      $uploaddir = "C:\CNSA266_Final\Images\\";
      $uploadfile = $uploaddir . $validid . "-" . $_FILES['userfile']['name'];

      //move_uploaded_file will check if first param is valid upload file,
      //then move it to second param
      if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
        //replaced with prepared statement
        /*$inssql = mysqli_real_escape_string($db, "INSERT INTO images(item_id, name)" .
          "VALUES(" . $validid . ", '" . $_FILES['userfile']['name'] . "')");
        mysqli_query($db, $inssql);*/

        //prep stmt stage 1
        $inssql = $db->prepare("INSERT INTO images(item_id, name) VALUES(?,?);");
        //prep stmt stage 2
        $filename = $validid . "-" . $_FILES['userfile']['name'];
        $inssql->bind_param("is", $validid, $filename);
        $inssql->execute();
        //no result needed
        $inssql->close();

        $url = $config_basedir. "addimages.php?id=" . $validid;
        redirect($url);
      } else {
        echo "There was a problem uploading your file.<br>";
      }
    }
  } else {
    //replaced with prep stmt
    /*$imagessql = mysqli_real_escape_string($db, "SELECT * FROM images WHERE item_id=" .
      $validid . ";");
    $imagesresult = mysqli_query($db, $imagessql);*/

    //prep stmt stage 1
    $imagessql = $db->prepare("SELECT * FROM images WHERE item_id=?;");
    //prep stmt stage 2
    $imagessql->bind_param("i", $validid);
    $imagessql->execute();
    $imagesresult = $imagessql->get_result();

    $imagesnumrows = mysqli_num_rows($imagesresult);

    echo "<h1>Current images</h1>";

    if($imagesnumrows == 0) {
      echo "No images.";
    } else {
      echo "<table>";
      while($imagesnumrows = mysqli_fetch_assoc($imagesresult)) {
        echo "<tr>";
        echo "<td><img src='" . $config_basedir . "/Images/" . $imagesnumrows['name'] .
          "' width='100'></td>";
        echo "<td>[<a href='deleteimage.php?image_id=" . $imagesnumrows['id'] .
          "&item_id=" . $validid . "'>delete</a>]</td>";
        echo "</tr>";
      }
      echo "</table>";
    }

    $imagessql->close();

    if(isset($_GET['error'])) {
      switch($_GET['error']) {
        case "empty":
          echo "You did not select anything.";
          break;
        case "nophoto":
          echo "You did not select a photo to upload.";
          break;
        case "photoprob":
          echo "There appears to be a problem with the photo you are uploading.";
          break;
        case "large":
          echo "The photo you selected is too large.";
          break;
        case "invalid":
          echo "The photo you selected is not a valid image file";
          break;
        default:
          echo "Unknown error.";
          break;
      }
    }
  }
  require("footer.php");
?>
