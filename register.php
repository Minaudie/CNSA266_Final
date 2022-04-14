<?php
  session_start();
  require("config.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  if($_POST['submit']) {
    if($_POST['password1'] == $_POST['password2']) {
      $checksql = mysqli_real_escape_string("SELECT * FROM users WHERE username = '" .
        $_POST['username'] . "';");
      $checkresult = mysqli_query($db, $checksql);
      $checknumrows = mysqli_num_rows($checkresult);

      if($checknumrows == 1) {
        header("Location: " . $config_basedir . "register.php?error=taken");
      } else {
        for($i=0; $i < 16; $i++) {
          $randomstring .= chr(mt_rand(32,126));
        }

        $verifyurl = "http://127.0.0.1/CNSA266_Final/verify.php";
        $verifystring = urlencode($randomstring);
        $verifyemail = urlencode($_POST['email']);
        $validusername = $_POST['username'];

        $sql = "INSERT INTO users(username,password,email,verifystring,active)" .
          " VALUES('" . $_POST['username'] . "', '" . $_POST['password1'] .
          "', '" . $_POST['email'] . "', '" . addslashes($randomstring) . "', 0);";
        mysqli_query($db, $sql);

        $mail_body=<<<_MAIL_
        Hi $validusername,
        Please click on the following link to verify your new account:
        $verifyurl?email=$verifyemail&verify=$verifystring
        _MAIL_;

        mail($_POST['email'], $config_forumsname . " User verification", $mail_body);

        require("header.php");
        echo "A link has been emailed to the address you entered below. " .
          "Please follow the link in the email to validate your account.";
      }
    } else {
      header("Location: " . $config_basedir . "register.php?error=pass");
    }
  } else {
    require("header.php");

    switch($_GET['error']) {
      case "pass":
        echo "Passwords do not match!";
        break;

      case "taken":
        echo "Username taken, please use another.";
        break;

      case "no":
        echo "Incorrect login details!";
        break;

      default:
        echo "Unknown error.";
        break;
    }
?>

<h2>Register</h2>
To register on the
<?php echo $config_forumsname; ?>
site, fill in the form below.

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">
  <table>
    <tr>
      <td>Username</td>
      <td><input type="text" name="username"></td>
    </tr>
    <tr>
      <td>Password</td>
      <td><input type="password" name="password1"></td>
    </tr>
    <tr>
      <td>Password (confirm)</td>
      <td><input type="password" name="password2"></td>
    </tr>
    <tr>
      <td>Email</td>
      <td><input type="text" name="email"></td>
    </tr>
    <td></td>
    <td><input type="submit" name="submit" value="Register!"></td>
  </table>
</form>

<?php
}
require("footer.php");
?>
