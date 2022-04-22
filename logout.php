<?php
  //deprecated a decade ago :)
  //session_unregister("USERNAME");

  session_start();

  session_destroy();

  require("config.php");

  header("Location: " . $config_basedir . "index.php");
?>
