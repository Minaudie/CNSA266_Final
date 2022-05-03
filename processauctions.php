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
  require_once("header.php");

  $itemssql = $db->prepare("SELECT users.username, users.email, items.id, items.name " .
    "FROM items, users WHERE dateends < NOW() AND items.user_id = users.id AND endnotified=0;");
  //no parameters
  $itemssql->execute();
  $itemsresult = $itemssql->get_result();

  while($itemsrow = mysqli_fetch_assoc($itemsresult)) {
    $bidssql = $db->prepare("SELECT bids.amount, users.username, users.email FROM " .
      "bids, users WHERE bids.user_id = users.id AND item_id=? ORDER BY amount DESC LIMIT 1;");
    $bidssql->bind_param("i", $itemsrow['id']);
    $bidssql->execute();
    $bidsresult = $bidssql->get_result();

    $bidsnumrows = mysqli_num_rows($bidsresult);

    $own_user = $itemsrow['username'];
    $own_email = $itemsrow['email'];
    $own_item = $itemsrow['name'];

    // *** PHPMailer *** //
    //set time zone for php
    date_default_timezone_set('Etc/UTC');

    //create new phpmailer object. Passing true as param enables exceptions
    $mail = new PHPMailer(TRUE);
    try {
      //set phpmailer to SMTP
      $mail->isSMTP();
      //smtp debugging
      //SMTP::DEBUG_SERVER - client and server messages
      //SMTP::DEBUG_OFF - no messages
      $mail->SMTPDebug = SMTP::DEBUG_OFF;
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

      //mail sender, with gmail has to be same as username
      $mail->setFrom('auctionsite.cnsa@gmail.com', 'Auction Site');

      //if no bids, email owner
      if($bidsnumrows == 0) {
        //recipient
        $mail->addAddress($own_email, $own_user);
        //subject
        $mail->Subject = "$own_item has not sold";
        //set email body content type to HTML
        $mail->isHTML(TRUE);
        //set mail body, HTML
        $mail->Body =
          "Hello $own_user,<br><br>" .
          "Your item, $own_item, did not have any bids placed on it.";
        //alt body, no HTML
        $mail->AltBody = "Hello $own_user, Your item, $own_item, did not have " .
          "any bids placed on it.";

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
          echo $mail->ErrorInfo();
        }

      } else { //email owner and winner
        //echo "Item id with bids: " . $itemsrow['id'];
        $bidsrow = mysqli_fetch_assoc($bidsresult);

        $own_highestbid = $bidsrow['amount'];

        $win_user = $bidsrow['username'];
        $win_email = $bidsrow['email'];

        //recipient
        $mail->addAddress($win_email, $win_user);
        //subject
        $mail->Subject = "You won $own_item!";
        //set email body content type to HTML
        $mail->isHTML(TRUE);
        //set mail body, HTML
        $mail->Body = "Hi $win_user,<br><br>" .
          "Congrats! Your bid of $config_currency$own_highestbid for the item " .
          "$own_item was the highest bid!<br><br>" .
          "Bid Details:<br>Item: $own_item<br>" .
          "Amount: $config_currency$own_highestbid<br>" .
          "Item owner: $own_user ($own_email)<br><br>" .
          "Please contact the owner within 3 days.";
        //alt body, no HTML
        $mail->AltBody = "Hi $win_user,   " .
          "Congrats! Your bid of $config_currency$own_highestbid for the item " .
          "$own_item was the highest bid! Bid Details:   Item: $own_item   " .
          "Amount: $config_currency$own_highestbid   Item owner: $own_user ($own_email)" .
          "   Please contact the owner within 3 days.";

        //disable some ssl checks
        $mail->SMTPOptions = array(
          'ssl' => array(
            'verify_peer' => true,
            'verify_depth' => 3,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          )
        );

        //send winner email
        //send email
        if(!$mail->send()) {
          //phpmailer error, for testing
          //echo $mail->ErrorInfo();
        }

        //recipient
        $mail->addAddress($own_email, $own_user);
        //subject
        $mail->Subject = "$own_item has been sold!";
        //set email body content type to HTML
        $mail->isHTML(TRUE);
        //set mail body, HTML
        $mail->Body = "Hi $own_user,<br><br>" .
          "Congrats! Your item $own_item has been sold for $config_currency$own_highestbid." .
          "<br><br>Bid Details:<br>Item: $own_item<br>" .
          "Amount: $config_currency$own_highestbid<br>Winner: $win_user ($win_email)" .
          "<br><br>Please contact the winner within 3 days.";
        //alt body, no HTML
        $mail->AltBody = "Hi $own_user,   " .
          "Congrats! Your item $own_item has been sold for $config_currency$own_highestbid." .
          "   Bid Details: Item: $own_item   " .
          "Amount: $config_currency$own_highestbid   Winner: $win_user ($win_email)" .
          "   Please contact the winner within 3 days.";

        //send owner email
        if(!$mail->send()) {
          //phpmailer error, for testing
          //echo $mail->ErrorInfo();
        }
      }
    } catch (Exception $ex) {
      //phpmailer exception
      echo $ex->errorMessage();
    }

    $updsql = $db->prepare("UPDATE items SET endnotified = 1 WHERE id=?;");
    $updsql->bind_param("i", $itemsrow['id']);
    $updsql->execute();
  }

  require("footer.php");
?>
