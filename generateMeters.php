<?php

  include_once "./constants.inc.php";

  function setParameterValues($statement, $parameters){
    foreach ($parameters as $key => $value){
      $statement->bindValue(":" . $key, $value);
    }
  }//SetParameterValues


  define ("METERSTANDEN_DATE_START", "2016-01-01");
  define ("NR_OF_METERSTANDEN", 12);

  define ("NR_OF_CITIES",5);

  define ("MIN_STREETS_PER_CITY", 5);
  define ("MAX_STREETS_PER_CITY", 20);

  define ("MIN_HUISNUMMERS_PER_STRAAT",10);
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
          a_gemeente,
          a_provincie,
          a_regio,
          a_straatnaam,
          a_huisnummer,
          a_postcode
      )  
      VALUES (
        :plaats,
        :gemeente,
        :provincie,
        :regio,
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
      ms_fk_idMeterTelwerk, 
      ms_stand, 
      ms_datum, 
      ms_tijd)
    VALUES (
      :fk_id_metertelwerk, 
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
  #$places  = file_get_contents("./datafiles/plaatsnamen.txt");
  $places  = file_get_contents("./datafiles/plaatsen.txt");
  $streetnames = explode("\n", $streets);
  $placenames  = explode("\n", $places);
  $count_streets = count($streetnames);
  $count_places  = count($placenames);

  echo "Found:  $count_places places and $count_streets streets\n";

  for ($nrOfCities = 0 ; $nrOfCities < NR_OF_CITIES; $nrOfCities++) {
    $placenr = random_int(0, $count_places - 1);
    $placenameRecord = $placenames[$placenr];

    # Split the placename record into its parts (plaatsnaam, gemeente, provincie, regio)
    $placenameParts = explode("|", $placenameRecord);
    $placename = $placenameParts[0];
    $gemeente  = $placenameParts[1];
    $provincie = $placenameParts[2];
    $regio     = $placenameParts[3];

    $postcode_base = random_int(1111, $count_places);

    $nrOfStreets  = random_int(MIN_STREETS_PER_CITY, MAX_STREETS_PER_CITY);

    echo  "Using ($nrOfCities of ".  NR_OF_CITIES . ") $placename and ZIP-code $postcode_base\n";
    echo " - Generating $nrOfStreets streets\n";

    for ($str = 0; $str < $nrOfStreets; $str++) {

      $streetnr = random_int(0, $count_streets - 1);
      $streetname = $streetnames[$streetnr];

      $postcodesize = random_int(5,20);

      $nrOfHouses = random_int(MIN_HUISNUMMERS_PER_STRAAT, MAX_HUISNUMMERS_PER_STRAAT);
      echo "  - [$str] Generating $nrOfHouses home-addresses\n";
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
            "plaats"    => $placename,
            "gemeente"  => $gemeente,
            "provincie" => $provincie,
            "regio"     => $regio,
            "straat"    => $streetname,
            "huisnr"    => $i+1,
            "postcode"  => $postcode,
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

        foreach ($telwerken as $key => $telwerk) {
          $values = [
              "idMeter"    => $idNewMeter,
              "product"    => $telwerk['product'],
              "telwerk"    => $telwerk['nr'],
              "type"       => $telwerk['type'],
          ];
          setParameterValues($statement_new_telwerk, $values);
          $statement_new_telwerk->execute();

          // sla de nieuwe Primay Key op voor het later toevoegen van meterstanden
          $telwerken[$key]['fk_id_metertelwerk'] = $db->lastInsertId();

        }
        /**
         * Tellerstanden aan meter/telwerken toevoegen
         */
        $begin_stand = random_int(40,1000);
        foreach ($telwerken as $telwerk) {
          $stand = $telwerk['stand'];

          for ($j = 0; $j < NR_OF_METERSTANDEN; $j++) {

            $values = [
                "fk_id_metertelwerk" => $telwerk['fk_id_metertelwerk'],
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