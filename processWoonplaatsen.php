<?php

  $woonplaatsen_fc = file_get_contents('datafiles/woonplaatsen-01.json');
  $gemeenten_fc    = file_get_contents('datafiles/woonplaatsen-gemeentes.json');

  $woonplaatsen = json_decode($woonplaatsen_fc)->value;
  $gemeentes    = json_decode($gemeenten_fc)->value;


  $places = [];
  $counties = [];

  foreach ($woonplaatsen as $woonplaats) {
      $gemeente_naam = $woonplaats->Title;
      $id   = $woonplaats->Key;
      $places[$id] = $gemeente_naam;
      if ($gemeente_naam == "Meppel") {
          echo $gemeente_naam . " : " . $id . "\n";
      }
  }

  echo "------------------------\n";

  $fp = fopen("datafiles/plaatsen.txt", "w");

  foreach($gemeentes as $gemeente) {
      $gemeente_naam = trim($gemeente->Naam_2);
      $gemeente_id   = trim($gemeente->Code_3);
      $provincie     = trim($gemeente->Naam_4);
      $regio         = trim($gemeente->Naam_6);
      $plaats_id     = trim($gemeente->Woonplaatscode_1);
      $plaatsnaam    = $places[$plaats_id];

      if ($gemeente_naam == "Meppel") {
          echo $gemeente_naam . "\n- ";
          echo $gemeente_id . "\n- ";
          echo $plaats_id . "\n- ";
          echo $provincie . "\n- ";
          echo $plaatsnaam . "\n";
      }

      fprintf($fp, "%s|%s|%s|%s\n" , $plaatsnaam, $gemeente_naam, $provincie, $regio);
  }

  fclose($fp);