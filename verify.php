<?php
	session_start();
  require("header.php");

  if(isset($_GET['verify'])) {
    $verifystring = rawurldecode($_GET['verify']);
    $verifyemail = rawurldecode($_GET['email']);

    $sql = $db->prepare("SELECT id FROM users WHERE verifystring=? AND email=?;");
    $sql->bind_param("ss", $verifystring, $verifyemail);
    $sql->execute();
    $result = $sql->get_result();

    $numrows = mysqli_num_rows($result);

    if($numrows == 1) {
      $row = mysqli_fetch_assoc($result);

      $sql = $db->prepare("UPDATE users SET active=1 WHERE id=?;");
      $sql->bind_param("i", $row['id']);
      $sql->execute();

      echo "Your account has now been verified. You can now " .
        "<a href='login.php'>log in.</a>";
    } else {
      echo "This account could not be verified.";
      echo "email: " . $verifyemail;
      echo "<br>string: " . $verifystring;
    }

    $sql->close();

    require("footer.php");
  } else {
    $url = $config_basedir; //aka index.php
    redirect($url);
  }
?>
