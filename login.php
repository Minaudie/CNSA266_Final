<?php
  require_once("config.php");
  require_once("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

  require_once("header.php");
?>

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

  //will evaluate left side and if false, not evaluate right side
  if(isset($_POST['submit']) && $_POST['submit']) {
    $username = $_POST['username'];
    $password = $_POST['password'];

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

        $_SESSION['USERNAME'] = $row['username'];
        $_SESSION['USERID'] = $row['id'];

        if(isset($_GET['ref]'])) {
          switch($_GET['ref']) {
            case "addbid":
              $url = $config_basedir . "/itemdetails.php?id=" . $_GET['id'] . "#bidbox";
              redirect($url);
              break;
            case "newitem":
              $url = $config_basedir . "/newitem.php";
              redirect($url);
              break;
            case "images":
              $url = $config_basedir . "/addimages.php?id=" . $_GET['id'];
              redirect($url);
              break;
            case "edititem":
              $url = $config_basedir . "/edititem.php?id=" . $_GET['id'];
            default:
              $url = $config_basedir . "/index.php";
              redirect($url);
              break;
          }
        }

        $url = $config_basedir . "/index.php";
        redirect($url);
      } else {
        //TODO: test 
        require("header.php");
        echo "<br>This account is not verified yet. You were emailed a link to verify " .
          "the account.<br> Please click on the link in the email to continue.";
      }
    } else {
        $url = $config_basedir . "/login.php?error=1";
        redirect($url);
    }
  }

  //will evaluate left side and if false, not evaluate right side
  if(isset($_GET['error']) && $_GET['error']) {
    echo "<br><b>Incorrect login, please try again!</b>";
  }

  require_once("footer.php");
?>
