<?php
  //PHPMailer - library for sending email via SMTP
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  use PHPMailer\PHPMailer\SMTP;
  //League Google OAuth2
  use PHPMailer\PHPMailer\OAuth;
  use League\OAuth2\Client\Provider\Google;

  //load dependencies from composer
  require 'C:\Windows\System32\vendor\autoload.php';

  require_once("config.php");
  require_once("functions.php");

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdatabase);

  require_once("header.php");
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

  if(isset($_POST['submit']) && $_POST['submit']) {
    if($_POST['password1'] == $_POST['password2']) {

      $username = $_POST['username'];
      $password1 = $_POST['password1'];
      $email = $_POST['email'];

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

          //$mail->SMTPSecure = 'TLS';
          //set port num, 465 TLS, 587 SMTP + STARTTLS
          $mail->Port = 465;
          //ecryption mechanism
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
          //enable smtp authentication
          $mail->SMTPAuth = TRUE;
          //set authtype
          $mail->AuthType = 'XOAUTH2';

          //option 1 in phpmailer example
          $userName = 'auctionsite.cnsa@gmail.com';
          $clientId= '493528140636-2c04e6nlu286sf0ie7t8ige6nn4v6t1e.apps.googleusercontent.com';
          $clientSecret = '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';

          //obtained by config and running get_oauth_token.php
          //after setting up app in Google Dev console
          $refreshToken = '1//0djcdQ2-pJfoLCgYIARAAGA0SNwF-L9IrTBEtIAihJHLygRGShOmq2sr4bl6SZCX5kIs3JpD73bCEfjlR9l7VaMgesZwtn7zLgi4';

          //create new OAuth2 provider license
          $provider = new Google(
            [
              'clientId' => $clientId,
              'clientSecret' => $clientSecret,
            ]
          );

          //pass OAuth provider instance to phpmailer
          $mail->setOAuth(
            new OAuth(
              [
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $userName,
              ]
            )
          );
          //end option 1

          //smtp username and password
          //$mail->username="auctionsite.cnsa@gmail.com";
          //$mail->password=$dbpassword;

          //mail sender, same as the one used to authenticate
          $mail->setFrom('auctionsite.cnsa@gmail.com', 'Auction Site');
          //recipient
          $mail->addAddress($email, $username);
          //subject
          $mail->Subject = 'Verify Account';

          //TODO: set up external html file to bring in for email body
          //decided against this as there's variables going into it.
          //https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail.phps
          //read html message body from ext file, convert images to embedded
          //$mail->CharSet = PHPMailer::CHARSET_UTF8;
          //$mail->msgHTML(file_get_contents('contentsutf8.html'), __DIR__);

          //set email body content type to HTML
          $mail->isHTML(TRUE);
          //set mail body, HTML
          $mail->Body =
            "Hi $username,<br><br>" .
            "Please click on the following link to verify your new account:<br><br>" .
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
            //echo $mail->ErrorInfo();
          } else { //create account
            $sql = $db->prepare("INSERT INTO users(username, password, email, verifystring,active)" .
              " VALUES(?,?,?,?,0);");
            $sql->bind_param("ssss", $username, $password1, $email, $randomstring);
            $sql->execute();

            //require("header.php");
            echo "A link has been emailed to the address you entered above.<br>" .
              "Please follow the link in the email to validate your account.";
          }

        } catch (Exception $ex) {
          //phpmailer exception
          echo $ex->errorMessage();
        }

      }
    } else {
      $url = $config_basedir . "register.php?error=pass";
      redirect($url);
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
