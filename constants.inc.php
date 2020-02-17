<?php

$filecontents = file_get_contents("database.settings.txt");
$lines = explode("\n", $filecontents);

$username = "";
$password = "";
$host     = "";
$name     = "";

foreach ($lines as $line) {
  $items = explode("=", $line);
  switch($items[0]) {
    case "host":
      $host = $items[1];
      break;
    case "name":
      $name = $items[1];
      break;
    case "password":
       $password = $items[1];
      break;
    case "username":
      $username = $items[1];
      break;
  }
}

define ("DB_HOST",     $host );
define ("DB_NAME",     $name );
define ("DB_USERNAME", $username );
define ("DB_PASSWORD", $password );

define("MYSQL_DSN","mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . "");
