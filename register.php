<?php
  //removed session start as it is called on header.php
  //session_start();

  require("config.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

	//not needed with mysqli, replaced with 4th parameter in mysqli_connect
	//mysql_select_db($dbdatabase, $db);

  require("header.php");
?>

<h2>Register</h2>
To register on the
<?php echo $config_forumsname; ?>
site, fill in the form below.

<!-- see login.php for why the action attrb. changed -->
<form action="register.php" method="POST">
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

  if(isset($_POST['submit'])) {
    if($_POST['submit']) {
      if($_POST['password1'] == $_POST['password2']) {

        $username = $_POST['username'];
        $password1 = $_POST['password1'];
        $email = $_POST['email'];

        //removed in favor of prepared statements
        //was having a big issue with the variable in the statement
        //$checksql = mysqli_real_escape_string($db, "SELECT * FROM users WHERE username = " .
          //$username . ";");
        //$checkresult = mysqli_query($db, $checksql);

        //prepared statement stage 1
        $checksql = $db->prepare("SELECT * FROM users WHERE username = ?");
        //prepared statement stage 2, s means string
        $checksql->bind_param("s", $username);
        $checksql->execute();
        //getting result of prepared statement
        $checkresult = $checksql->get_result();

        $checknumrows = mysqli_num_rows($checkresult);

        $randomstring = "";
        if($checknumrows == 1) {
          header("Location: " . $config_basedir . "register.php?error=taken");
        } else {
          for($i=0; $i < 16; $i++) {
            $randomstring .= chr(mt_rand(32,126));
          }

          $verifyurl = "http://127.0.0.1/verify.php";
          $verifystring = urlencode($randomstring);
          $verifyemail = urlencode($email);
          //have this line previously, changed variable name below
          //$validusername = $_POST['username'];

          //TODO: change to prepared statement
          $sql = "INSERT INTO users(username,password,email,verifystring,active)" .
            " VALUES('" . $username . "', '" . $password1 .
            "', '" . $email . "', '" . addslashes($randomstring) . "', 0);";
          mysqli_query($db, $sql);

          $mail_body=<<<_MAIL_
          Hi $username,
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
    }
  } else {

    if(isset($_GET['error'])) {
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
    }
  }

require("footer.php");
?>
