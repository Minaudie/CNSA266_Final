<?php
  require_once("config.php");
  require_once("header.php");

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

  //replaced with prep stmt
  /*$itemssql = mysqli_real_escape_string($db, "SELECT users.username, users.email," .
    " items.id, items.name FROM items, users WHERE dateends < NOW() AND " .
    "items.user_id = users.id AND endnotified = 0;");
  $itemsresult = mysqli_query($db, $itemssql);*/

  $itemssql = $db->prepare("SELECT users.username, users.email, items.id, items.name " .
    "FROM items, users WHERE dateends < NOW() AND items.user_id = users.id AND endnotified=0;");
  //no parameters
  $itemssql->execute();
  $itemsresult = $itemssql->get_results();

  while($itemsrow = mysqli_fetch_assoc($itemsresult)) {
    //replaced with prep stmt
    /*$bidssql = mysqli_real_escape_string($db, "SELECT bids.amount, users.username, " .
      "user.email FROM bids, users WHERE bids.user_id = users.id AND item_id=" .
      $itemsrow['id'] . " ORDER BY amount DESC LIMIT 1;");
    $bidsresult = mysqli_query($db, $bidssql);*/

    $bidssql = $db->prepare("SELECT bids.amount, users.username, users.email FROM " .
      "bids, users WHERE bids.user_id = users.id AND item_id=? ORDER BY amount DESC LIMIT 1;");
    $bidssql->bind_param("i", $itemsrow['id']);
    $bidssql->execute();
    $bidsresult = $bidssql->get_results();

    $bidsnumrows = mysqli_num_rows($bidsresult);

    $own_user = $itemsrow['username'];
    $own_email = $itemsrow['email'];
    $own_item = $itemsrow['name'];

    //TODO: set up external html file to bring in for email body
    //https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail.phps

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

      //TODO: set up if/else for bids
      //if no bids, email owner
      if($bidsnumrows == 0) {
        //recipient
        $mail->addAddress($own_email, $own_user);
        //subject
        $mail->Subject = "$own_item has not sold";
        //set email body content type to HTML
        $mail->isHTML(TRUE);
        //set mail body, HTML
        $mail->Body = "Hello $own_user, <br>Your item, $own_item, did not have " .
          "any bids placed on it.";
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
          //TODO: turn off for final
          echo $mail->ErrorInfo();
        }

      } else { //email owner and winner
        echo "item with bids" . $itemsrow['id'];
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
        $mail->Body = "Hi $win_user,<br>" .
          "Congrats! Your bid of $config_currency$own_highestbid for the item " .
          "$own_item was the highest bid!<br><br>Bid Details:<br>Item: $own_item<br>" .
          "Amount: $config_currency$own_highestbid<br>Item owner: $own_user ($own_email)" .
          "<br><br>Please contact the owner within 3 days.";
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
          //phpmailer error
          //TODO: turn off for final
          echo $mail->ErrorInfo();
        }

        //recipient
        $mail->addAddress($own_email, $own_user);
        //subject
        $mail->Subject = "$own_item has been sold!";
        //set email body content type to HTML
        $mail->isHTML(TRUE);
        //set mail body, HTML
        $mail->Body = "Hi $own_user,<br>" .
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
          //phpmailer error
          //TODO: turn off for final
          echo $mail->ErrorInfo();
        }

    } catch (Exception $ex) {
      //phpmailer exception
      echo $ex->errorMessage();
    }

    $updsql = $db->prepare("UPDATE items SET endnotified = 1 WHERE id=?;");
    $updsql->bind_param("i", $itemsrow['id']);
    $updsql->execute();
  }

  $itemssql->close();
  $bidssql->close();
  $updsql->close();

  require("footer.php");

//** OLD mail stuff **//
    //replaced with phpmailer
    //if no bids
    /*if($bidsnumrows == 0) {
      /*$owner_body=<<<_OWNER_
      Hi $own_owner,

      Sorry, but your item '$own_name', did not have any bids placed on it.

      _OWNER_;

      mail($own_email, "Your item '" . $own_name . "' did not sell", $owner_body);

    } else { //if bids
      echo "item with bids" . $itemsrow['id'];
      $bidsrow = mysqli_fetch_assoc($bidsresult);

      $own_highestbid = $bidsrow['amount'];

      $win_winner = $bidsrow['username'];
      $win_email = $bidsrow['email'];

      $owner_body=<<<_OWNER_

      Hi $own_owner,

      Congratulations! The auction for your item '$own_name' has completed with
      a winning bid of $config_currency$own_highestbid, bid by $win_winner!

      Bid details:

      Item: $own_name
      Amount: $config_currency$own_highestbid
      Winning bidder: $win_winner ($win_email)

      It is recommended that you contact the winning bidder within 3 days.

      _OWNER_;

      $winner_body=<<<_WINNER_

      Hi $win_winner,

      Congratulations! Your bid of $config_currency$own_highestbid for the item
      '$own_name' was the highest bid!

      Bid details:

      Item: $own_name
      Amount: $config_currency$own_highestbid
      Winning bidder: $own_owner ($own_email)

      It is recommended that you contact the owner of the item within 3 days.

      _WINNER_;

      mail($own_email, "Your item '" . $own_name . "' has sold", $owner_body);
      mail($win_email, "You won item '" . $own_name . "'!", $winner_body);

    }*/

    //replaced with prep stmt
    /*$updsql = mysqli_real_escape_string($db, "UPDATE items SET endnotified = 1 WHERE id=" .
    $itemsrow['id']);
    echo $updsql;
    mysqli_query($db, $updsql);*/

?>
