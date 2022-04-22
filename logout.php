<?php
  //this refuses to be set in the config file properly
  ini_set('session.save_path', 'C:/php/tmp');
  session_start();

  session_destroy();

  require_once("config.php");
  $url = $config_basedir . "index.php");
  redirect($url);
?>
