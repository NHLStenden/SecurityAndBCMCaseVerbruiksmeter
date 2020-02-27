<?php
/***
 * Dit script veranderd de gedownloade files van https://data.overheid.nl/community/dataverzoeken/lijst-van-woonplaatsen-gemeente-en-provincie-2018
 * naar één bestand met daarin de naam, gemeente, provincie en regio dat gebruikt kan worden
 * door het script 'generateMeters.php'.
 *
 * Gebruikers van het script 'generateMeters.php' hoeven dit script ('processWoonplaatsen') dus
 * niet zelf uit te voeren.
 *
 * Het resultaat van dit script wordt geplaatst in het bestand datafiles/plaatsen.txt
 *
 * De oude bestanden 'plaatsnamen.txt' komen hiermee te vervallen, maar worden voor de historische
 * waarde nog in de repository gelaten.
 */

  // lees JSON-bestanden in (raw)
  $woonplaatsen_fc = file_get_contents('./woonplaatsen-01.json');
  $gemeenten_fc    = file_get_contents('./woonplaatsen-gemeentes.json');

  # Converteer JSON naar objecten
  $woonplaatsen = json_decode($woonplaatsen_fc)->value;
  $gemeentes    = json_decode($gemeenten_fc)->value;

  # Setup final array for places
  $places = [];

  # Loop through all places
  foreach ($woonplaatsen as $woonplaats) {
      # Extract placename and unique key from array
      $plaats_naam = $woonplaats->Title;
      $id          = $woonplaats->Key;

      # add the new info to the array, using the unique ID as index for later retrieval
      $places[$id] = $plaats_naam;
  }

  # Open the output file.
  $fp = fopen("./plaatsen.txt", "w");

  # Loop through all counties
  foreach($gemeentes as $gemeente) {
      # Extract the information from the Object using arrow-notation
      $gemeente_naam = trim($gemeente->Naam_2);
      $gemeente_id   = trim($gemeente->Code_3);
      $provincie     = trim($gemeente->Naam_4);
      $regio         = trim($gemeente->Naam_6);
      $plaats_id     = trim($gemeente->Woonplaatscode_1);
      $plaatsnaam    = $places[$plaats_id];

      # User feedback
      echo $gemeente_naam . "\n- ";
      echo $gemeente_id . "\n- ";
      echo $plaats_id . "\n- ";
      echo $provincie . "\n- ";
      echo $plaatsnaam . "\n";

      # Add information to file
      fprintf($fp, "%s|%s|%s|%s\n" , $plaatsnaam, $gemeente_naam, $provincie, $regio);
  }

  # close the file
  fclose($fp);