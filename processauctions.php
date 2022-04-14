<?php
  require("config.php");
  require("header.php");

  $itemssql = mysqli_real_escape_string("SELECT users.username, users.email," .
    " items.id, items.name FROM items, users WHERE dateends < NOW() AND " .
    "items.user_id = users.id AND endnotified = 0;");
  $itemsresult = mysqli_query($db, $itemssql);

  while($itemsrow = mysqli_fetch_assoc($itemsresult)) {
    $bidssql = mysqli_real_escape_string("SELECT bids.amount, users.username, " .
      "user.email FROM bids, users WHERE bids.user_id = users.id AND item_id=" .
      $itemsrow['id'] . " ORDER BY amount DESC LIMIT 1;");
    $bidsresult = mysqli_query($db, $bidssql);
    $bidsnumrows = mysqli_num_rows($bidsresult);

    $own_owner = $itemsrow['username'];
    $own_email = $itemsrow['email'];
    $own_name = $itemsrow['name'];

    if($bidsnumrows == 0) {
      $owner_body=<<<_OWNER_
      Hi $own_owner,

      Sorry, but your item '$own_name', did not have any bids placed on it.

      _OWNER_;

      mail($own_email, "Your item '" . $own_name . "' did not sell", $owner_body);
    } else {
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
    }

    $updsql = mysqli_real_escape_string("UPDATE items SET endnotified = 1 WHERE id=" .
    $itemsrow['id']);
    echo $updsql;
    mysqli_query($db, $updsql);
}

require("footer.php");

?>