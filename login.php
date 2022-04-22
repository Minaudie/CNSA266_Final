<?php
  //removed session start, as it is called on index.php
  //session_start();

  require("config.php");
  require("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  require("header.php");
?>

<!-- the book has $SCRIPT_NAME in the action attribute, which is supposedly
     a special variable that is the name of the current script. I cannot find
     anything on google about this. So I just hardcoded it. There are so
     many hard coded pages in this project that this specific usage as a
     way to avoid hardcoding is baffling to me. This book sucks.
     Also this got moved from under the PHP script because it wasn't showing
     up at all.
 -->
<form action="login.php" method="POST">
  <table>
    <tr>
      <td>Username</td>
      <td><input type="text" name="username"></td>
    </tr>
    <tr>
      <td>Password</td>
      <td><input type="password" name="password"></td>
    </tr>
    <tr>
      <td></td>
      <td><input type="submit" name="submit" value="Login!"></td>
    </tr>
  </table>
</form>

Don't have an account? Go and <a href="register.php">Register!</a>

<?php

  if(isset($_POST['submit'])) {
    if($_POST['submit']) {
      $username = $_POST['username'];
      $password = $_POST['password'];

      //removed for prepared statements
      //$sql = mysqli_real_escape_string($db, "SELECT id, username, active " .
        //"FROM users WHERE username = " . "$user" . " AND password = " . "$pass" . ";");
      //$result = mysqli_query($db, $sql);

      //prepared stmt, stage 1
      $sql = $db->prepare("SELECT * FROM users WHERE username=? AND password=?");
      //prepared stmt, stage 2, s means string
      $sql->bind_param("ss", $username, $password);
      $sql->execute();
      //result of prepared statement
      $result = $sql->get_result();

      $numrows = mysqli_num_rows($result);

      if($numrows == 1) {
        $row = mysqli_fetch_assoc($result);

        if($row['active'] == 1) {
          //deprecated/removed over a decade ago :)
          //session_register("USERNAME");
          //session_register("USERID");

          $_SESSION['USERNAME'] = $row['username'];
          $_SESSION['USERID'] = $row['id'];

          if(isset($_GET['ref]'])) {
            switch($_GET['ref']) {
              case "addbid":
                header("Location: " . $config_basedir . "/itemdetails.php?id=" .
                  $_GET['id'] . "#bidbox");
                break;
              case "newitem":
                header("Location: " . $config_basedir . "/newitem.php");
                break;
              case "images":
                header("Location: " . $config_basedir . "/addimages.php?id=" .
                  $_GET['id']);
                break;
              default:
                header("Location: " . $config_basedir . "/index.php");
                break;
            }
          }
        } else {
          //require("header.php");
          echo "<br>This account is not verified yet. You were emailed a link to verify " .
            "the account.<br> Please click on the link in the email to continue.";
        }
      } else {
          header("Location: " . $config_basedir . "/login.php?error=1");
      }
    } else {
      //require("header.php");
      echo "<h1>Login</h1>";

      //TODO: fix this, not recognizing when there's an error via get
      if($_GET['error']) {
        echo "Incorrect login, please try again!";
      }
    }
  }
  require("footer.php");
?>
