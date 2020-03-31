<?php

define("FILE_SETTINGS", "database.settings.txt");

if (! file_exists(FILE_SETTINGS)){
  die("#ERROR => Bestand " . FILE_SETTINGS . " niet gevonden. Zie README.md voor instructies.\n");
}
$filecontents = file_get_contents(FILE_SETTINGS);
$lines = explode("\n", $filecontents);

$username = "";
$password = "";
$host     = "";
$name     = "";

foreach ($lines as $line) {
  $items = explode("=", $line);
  $key   = $items[0];
  $value = $items[1];

  echo "[$key] = {$value}\n";
  switch($key) {
    case "host":
      $host = $value;
      break;
    case "name":
      $name = $value;
      break;
    case "password":
       $password = $value;
      break;
    case "username":
      $username = $value;
      break;
  }
}
echo "HOST=$host\n";
echo "DBNAME=$name\n";
echo "USER=$username\n";
echo "PASSWORD=$password\n";

define ("DB_HOST",     $host );
define ("DB_NAME",     $name );
define ("DB_USERNAME", $username );
define ("DB_PASSWORD", $password );

$connectStr = "mysql:host=" . $host  . ";dbname=" . $name;

define("MYSQL_DSN",$connectStr);
