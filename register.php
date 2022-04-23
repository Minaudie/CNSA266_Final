<?php
  //removed session start as it is called on header.php
  //session_start();

  //PHPMailer - library for sending email via SMTP
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  use PHPMailer\PHPMailer\SMTP;

  //Exception class, required
  require "C:\phpmailer\src\Exception.php";
  //Main PHPMailer class, required
  require "C:\phpmailer\src\PHPMailer.php";
  //SMTP class, for if using SMTP, optional?
  require "C:\phpmailer\src\SMTP.php";

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
          $url = $config_basedir . "register.php?error=taken";
          redirect($url);
        } else {
          for($i=0; $i < 16; $i++) {
            $randomstring .= chr(mt_rand(32,126));
          }

          $verifyurl = "http://127.0.0.1/verify.php";
          $verifystring = urlencode($randomstring);
          $verifyemail = urlencode($email);
          //have this line previously, changed variable name below
          //$validusername = $_POST['username'];

          //TODO: TEMPORARY LOCATION
          //changed active to 1 to bypass verify req.
          $sql = "INSERT INTO users(username,password,email,verifystring,active)" .
            " VALUES('" . $username . "', '" . $password1 .
            "', '" . $email . "', '" . addslashes($randomstring) . "', 1);";
          mysqli_query($db, $sql);
          $url = $config_basedir . "login.php";
          redirect($url);

          //default php way of doing email, requires local mailserver
          //replaced with PHPMailer library
        /*  $mail_body=<<<_MAIL_
          Hi $username,
          Please click on the following link to verify your new account:
          $verifyurl?email=$verifyemail&verify=$verifystring
          _MAIL_;

          mail($email, $config_forumsname . " User verification", $mail_body);
          */

/*
2022-04-22 15:21:30 SERVER -> CLIENT: 535-5.7.8 Username and Password not accepted.
Learn more at535 5.7.8 https://support.google.com/mail/?p=BadCredentials
h75-20020a379e4e000000b0069db8210ffbsm1015258qke.12 - gsmtp
2022-04-22 15:21:30 SMTP ERROR: Password command failed: 535-5.7.8 Username and
Password not accepted. Learn more at535 5.7.8
https://support.google.com/mail/?p=BadCredentials
h75-20020a379e4e000000b0069db8210ffbsm1015258qke.12 - gsmtp
*/


          //TODO: set up external html file to bring in for email body
          //https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail.phps
/*
          // *** PHPMailer *** //
          //set time zone for php
          date_default_timezone_set('Etc/UTC');

          //create new phpmailer object. Passing true as param enables exceptions
          $mail = new PHPMailer(TRUE);
          try {
            //set phpmailer to SMTP
            $mail->isSMTP();
            //smtp debugging
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            //set hostname
            $mail->Host = 'smtp.gmail.com';

            $mail->SMTPSecure = 'TLS';
            //set port num, 465 TLS, 587 SMTP + STARTTLS
            $mail->Port = 587;
            //ecryption mechanism
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            //enable smtp authentication
            $mail->SMTPAuth = TRUE;

            //smtp username and password
            $mail->username="auctionsite.cnsa@gmail.com";
            $mail->password=$dbpassword;

            //mail sender, with gmail has to be same as username
            $mail->setFrom('auctionsite.cnsa@gmail.com', 'Auction Site');
            //recipient
            $mail->addAddress($email, $username);
            //subject
            $mail->Subject = 'Verify Account';
            //set email body content type to HTML
            $mail->isHTML(TRUE);
            //set mail body, HTML
            $mail->Body = "Hi $username,<br>" .
              "Please click on the following link to verify your new account:<br>" .
              "$verifyurl?email=$verifyemail&verify=$verifystring";
            //alt body, no HTML
            $mail->AltBody = "Hi $username, Please click on the following link " .
              "to verify your new account: $verifyurl?email=$verifyemail&verify=$verifystring";

            //disable some ssl checks
            $mail->SMTPOptions = array(
              'ssl' => array(
                'verify_peer' => true,
                'verify_depth' => 3,
                'verify_peer_name' => false,
                'allow_self_signed' => true
              )
            );

            //send email
            if(!$mail->send()) {
              //phpmailer error
              //TODO: turn off for final
              echo $mail->ErrorInfo();
            } else { //create account
              //TODO: change to prepared statement, move before email
              $sql = "INSERT INTO users(username,password,email,verifystring,active)" .
                " VALUES('" . $username . "', '" . $password1 .
                "', '" . $email . "', '" . addslashes($randomstring) . "', 0);";
              mysqli_query($db, $sql);

              //require("header.php");
              echo "A link has been emailed to the address you entered above.<br>" .
                "Please follow the link in the email to validate your account.";
            }

          } catch (Exception $ex) {
            //phpmailer exception
            echo $ex->errorMessage();
          }
*/
        }
      } else {
        $url = $config_basedir . "register.php?error=pass";
        redirect($url);
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
