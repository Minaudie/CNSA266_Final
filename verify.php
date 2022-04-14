<?php
  require("header.php");

  $verifystring = urldecode($_GET['verify']);
  $verifyemail = urldecode($_GET['email']);

  $sql = mysqli_real_escape_string("SELECT id FROM users WHERE verifystring = '" .
    $verifystring . "' AND email = '" . $verifyemail . "';");
  $result = mysqli_query($db, $sql);
  $numrows = mysqli_num_rows($result);

  if($numrows == 1) {
    $row = mysqli_fetch_assoc($result);

    $sql = mysqli_real_escape_string("UPDATE users SET active = 1 WHERE id=" .
      $row['id']);
    $result = mysqli_query($db, $sql);

    echo "Your account has now been verified. You can now <a href='login.php>log in.</a>'"
  } else {
    echo "This account could not be verified.";
  }

  require("footer.php");
?>
