<?php
include_once "./constants.inc.php";

function setParameterValues($statement, $parameters){
  foreach ($parameters as $key => $value){
    $statement->bindValue(":" . $key, $value);
  }
}//SetParameterValues

srand(12345);

echo "Performing cleanup\n.......\n";

$db = new PDO(MYSQL_DSN, DB_USERNAME, DB_PASSWORD);
echo "Klanten.......\n";
$db->prepare("DELETE FROM tbl_klanten;")->execute();
$db->prepare("SET NAMES utf8;")->execute();

$sql_new_klant = "
  INSERT INTO tbl_klanten (
    k_achternaam,
    k_klantnummer,
    k_fk_idAdres, 
    k_voornaam) VALUES (
    :achternaam,  
    :klantnummer,
    :idAdres,
    :voornaam
  );
";
$sql_select_adressen = "
  SELECT * FROM tbl_adressen;
";

$statement_new_klant       = $db->prepare($sql_new_klant);
$statement_select_adressen = $db->prepare($sql_select_adressen);
if ($statement_new_klant       === false ) {var_dump($db->errorInfo());die();}
if ($statement_select_adressen === false ) {var_dump($db->errorInfo());die();}

$voornamen_file = file_get_contents("./datafiles/voornamen.sorted.txt");
$achternamen_file  = file_get_contents("./datafiles/achternamen.sorted.txt");
$voornamen = explode("\n", $voornamen_file);
$achternamen  = explode("\n", $achternamen_file);
$count_voornamen = count($voornamen);
$count_achternamen  = count($achternamen);

if (!$statement_select_adressen->execute()) {
  var_dump($db->errorInfo());
  die();
}

$adressen = $statement_select_adressen->fetchAll();
while (count($adressen)> 0) {
  $count_adressen = count($adressen);

  if ($count_adressen % 100 == 0) echo "Adres: $count_adressen \n";

  $pAdres = rand(0, count($adressen)-1);
  $idAdres = $adressen[$pAdres]['a_idAdres'];
  array_splice($adressen, $pAdres, 1);

  $voornaam   = $voornamen[rand(0,$count_voornamen-1)];
  $achternaam = $achternamen[rand(0,$count_achternamen-1)];

  $klantnummer = crc32($voornaam . $achternaam . $idAdres . $pAdres);

  $new_klant_values = [
     "achternaam" => $achternaam,
     "klantnummer" => $klantnummer,
     "voornaam" => $voornaam,
     "idAdres" => $idAdres,
  ];
  setParameterValues($statement_new_klant, $new_klant_values);
  if (!$statement_new_klant->execute()) {
    var_dump($new_klant_values, $db->errorInfo());
    die();
  }

}