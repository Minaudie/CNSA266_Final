<?php
  require("header.php");

  if(isset($_GET['verify'])) {
    $verifystring = urldecode($_GET['verify']);
    $verifyemail = urldecode($_GET['email']);

    //replaced with prep stmt
    /*$sql = mysqli_real_escape_string($db, "SELECT id FROM users WHERE verifystring = '" .
      $verifystring . "' AND email = '" . $verifyemail . "';");
    $result = mysqli_query($db, $sql);*/

    $sql = $db->prepare("SELECT id FROM users WHERE verifystring=? AND email=?;");
    $sql->bind_param("ss", $verifystring, $verifyemail);
    $sql->execute();
    $result = $sql->get_result();

    $numrows = mysqli_num_rows($result);

    if($numrows == 1) {
      $row = mysqli_fetch_assoc($result);

      //replaced with prep stmt
      /*$sql = mysqli_real_escape_string($db, "UPDATE users SET active = 1 WHERE id=" .
        $row['id']);*/

      $sql = $db->prepare("UPDATE users SET active=1 WHERE id=?;");
      $sql->bind_param("i", $row['id']);
      $sql->execute();

      echo "Your account has now been verified. You can now <a href='login.php>log in.</a>'"
    } else {
      echo "This account could not be verified.";
    }

    $sql->close();

    require("footer.php");
  } else {
    $url = $config_basedir; //aka index.php
    redirect($url);
  }
?>
