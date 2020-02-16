<?php

  include_once "./constants.inc.php";

  function setParameterValues($statement, $parameters){
    foreach ($parameters as $key => $value){
      $statement->bindValue(":" . $key, $value);
    }
  }//SetParameterValues


  define ("METERSTANDEN_DATE_START", "2016-01-01");
  define ("NR_OF_METERSTANDEN", 12);
  define ("MAX_CITIES",50);
  define ("MAX_STREETS_PER_CITY", 20);
  define ("MAX_HUISNUMMERS_PER_STRAAT",40);

  echo "Performing cleanup\n.......\n";

  $db = new PDO(MYSQL_DSN, DB_USERNAME, DB_PASSWORD);
  $db->prepare("SET NAMES utf8;")->execute();

  echo "Telwerken.......\n";
  $db->prepare("DELETE FROM tbl_meter_telwerken;")->execute();
  echo "Meterstanden.......\n";
  $db->prepare("DELETE FROM tbl_meters_standen;")->execute();
  echo "Meters.......\n";
  $db->prepare("DELETE FROM tbl_meters;")->execute();
  echo "Adressen.......\n";
  $db->prepare("DELETE FROM tbl_adressen;")->execute();

  $sql_new_meter = "
      INSERT INTO tbl_meters (
          m_idMeter,
          m_fk_idAdres) 
      VALUES (
          :idMeter,
          :idAdres
      )";

  $sql_new_adres = "
      INSERT INTO tbl_adressen (
          a_plaatsnaam,
          a_straatnaam,
          a_huisnummer,
          a_postcode
      )  
      VALUES (
        :plaats,
        :straat,
        :huisnr, 
        :postcode                 
      )";
  $sql_new_telwerk = "
      INSERT INTO  tbl_meter_telwerken (
        mt_fk_idMeter,
        mt_product,
        mt_telwerk,
        mt_type                   
      )  
      VALUES (
      :idMeter,
      :product,
      :telwerk,
      :type
      )";
  $sql_new_meterstand =  "
    INSERT INTO tbl_meters_standen (
      ms_fk_idMeter, 
      ms_product, 
      ms_telwerk, 
      ms_stand, 
      ms_datum, 
      ms_tijd)
    VALUES (
      :idMeter, 
      :product, 
      :telwerk, 
      :stand, 
      date_add(:datum, INTERVAL :days DAY ), 
      :tijd
      ); 
  ";
  $statement_new_adres      = $db->prepare($sql_new_adres);
  $statement_new_meter      = $db->prepare($sql_new_meter);
  $statement_new_telwerk    = $db->prepare($sql_new_telwerk);
  $statement_new_meterstand = $db->prepare($sql_new_meterstand);

  if ($statement_new_adres      === false ) {var_dump($db->errorInfo());die();}
  if ($statement_new_meter      === false ) {var_dump($db->errorInfo());die();}
  if ($statement_new_telwerk    === false ) {var_dump($db->errorInfo());die();}
  if ($statement_new_meterstand === false ) {var_dump($db->errorInfo());die();}

  $metercounter = 1;
  $meternr_start = 293034;

  $streets = file_get_contents("./datafiles/streetnames.sorted.txt");
  $places  = file_get_contents("./datafiles/plaatsnamen.txt");
  $streetnames = explode("\n", $streets);
  $placenames  = explode("\n", $places);
  $count_streets = count($streetnames);
  $count_places  = count($placenames);

  echo "Found:  $count_places places and $count_streets streets\n";

  for ($nrOfCities = 0 ; $nrOfCities < MAX_CITIES; $nrOfCities++) {
    $placenr = random_int(0, $count_places - 1);
    $placename = $placenames[$placenr];
    $postcode_base = random_int(1111, $count_places);

    echo  "Using ($nrOfCities of ".  MAX_CITIES . ") $placename and ZIP-code $postcode_base\n";

    for ($nrOfStreets = 0; $nrOfStreets < MAX_STREETS_PER_CITY; $nrOfStreets++) {

      $streetnr = random_int(0, $count_streets - 1);
      $streetname = $streetnames[$streetnr];

      $postcodesize = random_int(5,20);

      $nrOfHouses = random_int(5, MAX_HUISNUMMERS_PER_STRAAT);
      for ($i = 0; $i < $nrOfHouses; $i++) {

        if ($i % $postcodesize == 0) {
          $postcode_letter1 = chr(ord('A') + random_int(0, 25));
          $postcode_letter2 = chr(ord('A') + random_int(0, 25));
        }
        $postcode = $postcode_base . $postcode_letter1 . $postcode_letter2;


        $idNewMeter = $meternr_start + $metercounter++;

        /**
         * Nieuw adres
         */
        $values = [
            "plaats" => $placename,
            "straat" => $streetname,
            "huisnr" => $i+1,
            "postcode" => $postcode,
        ];
        setParameterValues($statement_new_adres, $values);
        if (!$statement_new_adres->execute()) {
          var_dump($values, $db->errorInfo());
          die();
        }
        $idNewAdres = (int)$db->lastInsertId();

        /**
         * Nieuwe meter
         */
        $values = [
            "idMeter" => $idNewMeter,
            "idAdres" => $idNewAdres
        ];
        setParameterValues($statement_new_meter, $values);
        if (!$statement_new_meter->execute()) {
          var_dump($values, $db->errorInfo());
          die();
        }

        /**
         * Telwerken aan meters toevoegen
         */
        $telwerken = [
            ["product" => "G", "type" => "V", "nr" => 1, "stand" => random_int(10,5000)],  // gas levering
            ["product" => "E", "type" => "V", "nr" => 1, "stand" => random_int(10,5000)],  // electra, verbruik, hoog
            ["product" => "E", "type" => "V", "nr" => 2, "stand" => random_int(10,5000)],  // electra, verbruik laag
            ["product" => "E", "type" => "T", "nr" => 3, "stand" => random_int(10,5000)],  // electra, teruglevering hoog
            ["product" => "E", "type" => "T", "nr" => 4, "stand" => random_int(10,5000)],  // electra, teruglevering laag
        ];

        foreach ($telwerken as $telwerk) {
          $values = [
              "idMeter"    => $idNewMeter,
              "product"    => $telwerk['product'],
              "telwerk"    => $telwerk['nr'],
              "type"       => $telwerk['type'],
          ];
          setParameterValues($statement_new_telwerk, $values);
          $statement_new_telwerk->execute();

        }

        /**
         * Tellerstanden aan meter/telwerken toevoegen
         */
        $begin_stand = random_int(40,1000);
        foreach ($telwerken as $telwerk) {
          $stand = $telwerk['stand'];

          for ($j = 0; $j < NR_OF_METERSTANDEN; $j++) {

            $values = [
                "idMeter" => $idNewMeter,
                "product" => $telwerk['product'],
                "telwerk" => $telwerk['nr'],
                "stand" => $stand,
                "datum" => METERSTANDEN_DATE_START,
                "days" => $j * 30,
                "tijd" => date("H:i:s", time() + random_int(-5000, 5000))
            ];
            setParameterValues($statement_new_meterstand, $values);
            if (!$statement_new_meterstand->execute()) {
              var_dump($db->errorInfo());
              die();
            }

            $stand = $stand * (1 + random_int(1,15) / 100);

          }//for a number of months
        }// for each telwerk
      } // loop through housenr
    }// loop through streets
  }// Loop through cities