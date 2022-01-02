<?php

include_once "./constants.inc.php";

function setParameterValues($statement, $parameters)
{
    foreach ($parameters as $key => $value) {
        $statement->bindValue(":" . $key, $value);
    }
}//SetParameterValues

srand(12345);

define("METERSTANDEN_DATE_START", "2016-01-01");
define("NR_OF_METERSTANDEN", 49);

define("ONE_DAY", 24 * 60 * 60);

define("NR_OF_CITIES", 5);

define("MIN_STREETS_PER_CITY", 5);
define("MAX_STREETS_PER_CITY", 20);

define("MIN_HUISNUMMERS_PER_STRAAT", 10);
define("MAX_HUISNUMMERS_PER_STRAAT", 40);

// het gemiddelde jaarverbruik voor GAS en ELECTRA (bandbreedte MIN-MAX)
define("AVERAGE_USAGE_YEAR_E_MIN", 3500);
define("AVERAGE_USAGE_YEAR_E_MAX", 4500);
define("AVERAGE_USAGE_YEAR_G_MIN", 3000);
define("AVERAGE_USAGE_YEAR_G_MAX", 4500);

// array die aangeeft hoeveel dagen er in een maand zitten en welk percentage van het
// jaarverbruik er minimaal en maximaal in die maand bij kan komen
$monthDays = [
    1 =>  ['days' => 31, 'usage_low' => 10, 'usage_high' => 15], // jan
    2 =>  ['days' => 28, 'usage_low' => 10, 'usage_high' => 15], // feb
    3 =>  ['days' => 31, 'usage_low' => 8,  'usage_high' => 13], // mar
    4 =>  ['days' => 30, 'usage_low' => 8,  'usage_high' => 12], // apr
    5 =>  ['days' => 31, 'usage_low' => 7,  'usage_high' => 10], // mei
    6 =>  ['days' => 30, 'usage_low' => 4,  'usage_high' =>  7], // jun
    7 =>  ['days' => 31, 'usage_low' => 3,  'usage_high' =>  7], // jul
    8 =>  ['days' => 31, 'usage_low' => 3,  'usage_high' =>  7], // aug,
    9 =>  ['days' => 30, 'usage_low' => 6,  'usage_high' =>  9], // sep
    10 => ['days' => 31, 'usage_low' => 9,  'usage_high' => 11], // okt
    11 => ['days' => 30, 'usage_low' => 11, 'usage_high' => 13], // nov
    12 => ['days' => 31, 'usage_low' => 11, 'usage_high' => 15] //dec
];

echo "Performing cleanup\n.......\n";

$db = new PDO(MYSQL_DSN, DB_USERNAME, DB_PASSWORD);
$db->prepare("SET NAMES utf8;")->execute();

echo "Meterstanden.......\n";
$db->prepare("DELETE FROM tbl_meters_standen;")->execute();
echo "Telwerken.......\n";
$db->prepare("DELETE FROM tbl_meter_telwerken;")->execute();
echo "Meters.......\n";
$db->prepare("DELETE FROM tbl_meters;")->execute();
echo "Adressen.......\n";
$db->prepare("DELETE FROM tbl_adressen;")->execute();

$sql_new_meter = "
      INSERT INTO tbl_meters (
          m_idMeter,
          m_product,
          m_fk_idAdres) 
      VALUES (
          :idMeter,
          :idProduct,
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
        mt_telwerk,
        mt_type                   
      )  
      VALUES (
      :idMeter,
      :telwerk,
      :type
      )";
$sql_new_meterstand = "
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
$statement_new_adres = $db->prepare($sql_new_adres);

if ($statement_new_adres === false) {
    var_dump($db->errorInfo());
    die();
}
$statement_new_meter = $db->prepare($sql_new_meter);
if ($statement_new_meter === false) {
    var_dump($db->errorInfo());
    die();
}
$statement_new_telwerk = $db->prepare($sql_new_telwerk);
if ($statement_new_telwerk === false) {
    var_dump($db->errorInfo());
    die();
}
$statement_new_meterstand = $db->prepare($sql_new_meterstand);
if ($statement_new_meterstand === false) {
    var_dump($db->errorInfo());
    die();
}

$metercounter = 1;
$meternr_start = 293034;

$streets       = file_get_contents("./datafiles/streetnames.sorted.txt");
$places        = file_get_contents("./datafiles/plaatsen.txt");
$streetnames   = explode("\n", $streets);
$placenames    = explode("\n", $places);
$count_streets = count($streetnames);
$count_places  = count($placenames);

echo "Found:  $count_places places and $count_streets streets\n";
$db->exec("SET autocommit = OFF:  ;");

for ($nrOfCities = 0; $nrOfCities < NR_OF_CITIES; $nrOfCities++) {
    $placenr = rand(0, $count_places - 1);
    $placenameRecord = $placenames[$placenr];

    # Split the placename record into its parts (plaatsnaam, gemeente, provincie, regio)
    $placenameParts = explode("|", $placenameRecord);
    $placename = $placenameParts[0];
    $gemeente = $placenameParts[1];
    $provincie = $placenameParts[2];
    $regio = $placenameParts[3];

    $postcode_base = rand(1111, $count_places);

    $nrOfStreets = rand(MIN_STREETS_PER_CITY, MAX_STREETS_PER_CITY);

    echo "Using ($nrOfCities of " . NR_OF_CITIES . ") $placename and ZIP-code $postcode_base\n";
    echo " - Generating $nrOfStreets streets\n";


    for ($str = 0; $str < $nrOfStreets; $str++) {
        // selecteer een random index voor de array met ingelezen straatnamen.
        $streetnr = rand(0, $count_streets - 1);
        $streetname = $streetnames[$streetnr];

        // maak een random waarde voor het aantal adressen in een postcode.
        $postcodesize = rand(5, 20);

        $nrOfHouses = rand(MIN_HUISNUMMERS_PER_STRAAT, MAX_HUISNUMMERS_PER_STRAAT);

        // use transactions to speed up by postponing the disk writes. Every transaction is one house address
        // including all meters etc.
        $db->exec("START TRANSACTION;");

        echo "  - [$str] Generating $nrOfHouses home-addresses\n";
        for ($i = 0; $i < $nrOfHouses; $i++) {
            if ($i % $postcodesize == 0) {
                $postcode_letter1 = chr(ord('A') + rand(0, 25));
                $postcode_letter2 = chr(ord('A') + rand(0, 25));
            }
            $postcode = $postcode_base . $postcode_letter1 . $postcode_letter2;

            /**
             * Nieuw adres
             */
            $values = [
                "plaats" => $placename,
                "gemeente" => $gemeente,
                "provincie" => $provincie,
                "regio" => $regio,
                "straat" => $streetname,
                "huisnr" => $i + 1,
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

            $products = ["G","E"];
            $metersAtAddress = [];
            foreach ($products as $product) {
                $idNewMeter = $meternr_start + $metercounter++;
                $values = [
                    "idMeter" => $idNewMeter,
                    "idAdres" => $idNewAdres,
                    "idProduct" => $product
                ];
                setParameterValues($statement_new_meter, $values);
                if (!$statement_new_meter->execute()) {
                    var_dump($values, $db->errorInfo());
                    die();
                }
                $metersAtAddress[$product] = $idNewMeter;
            }

            /**
             * Telwerken aan meters toevoegen; bevat
             *   - een type (Verbruik of Teruglevering)
             *   - een telwerknummer per product
             *   - een willekeurige beginstand van het telwerk
             */

            $telwerken = [
                ["product" => "G", "type" => "V", "nr" => 1, "stand" => rand(10, 5000)],  // gas levering
                ["product" => "E", "type" => "V", "nr" => 1, "stand" => rand(10, 5000)],  // electra, verbruik, hoog
                ["product" => "E", "type" => "V", "nr" => 2, "stand" => rand(10, 5000)],  // electra, verbruik laag
                ["product" => "E", "type" => "T", "nr" => 3, "stand" => rand(10, 5000)],  // electra, teruglevering hoog
                ["product" => "E", "type" => "T", "nr" => 4, "stand" => rand(10, 5000)],  // electra, teruglevering laag
            ];

            // loop door alle telwerken heen en voeg deze toe.
            // in deze loop wordt het item in de array verrijkt met de PrimaryKey van het opgeslagen nieuwe telwerk
            foreach ($telwerken as $key => $telwerk) {
                $values = [
                    "idMeter" => $metersAtAddress[$telwerk['product']],
                    "telwerk" => $telwerk['nr'],
                    "type" => $telwerk['type'],
                ];
                setParameterValues($statement_new_telwerk, $values);
                $statement_new_telwerk->execute();

                // sla de nieuwe Primay Key op voor het later toevoegen van meterstanden
                $telwerken[$key]['fk_id_metertelwerk'] = $db->lastInsertId();

            }

            /**
             * Tellerstanden aan meter/telwerken toevoegen
             */

            // een dubbele lus die per maand / datum / tijd voor alle telwerken een meterstand opvoert
            // er wordt gewerkt met een tabel met percentages om de groei van de meterstand te bepalen met een random factor
            $addedDays = 1;
            for ($j = 0; $j < NR_OF_METERSTANDEN; $j++) {
                // bepaal maandnummer
                $monthNr = ($j % 12) + 1;

                // bepaal een random waarde voor de dag van de maand (alleen dag 1 t/m 8) waarin de meterstand wordt opgenomen.
                $randomDay = rand(1, 8);

                // bepaal random tijdstip op de gekozen waarop de meterstand wordt opgenomen.
                $randomTime = date("H:i:s", rand(1, ONE_DAY));

                // wat is de verwachte minimale en maximale stijging (als % vh jaarverbruik) van het verbruik in deze maand?
                $perc_low  = $monthDays[$monthNr]['usage_low'];
                $perc_high = $monthDays[$monthNr]['usage_high'];

                // kies een random jaarverbruik voor GAS en ELECTRA tussen de ingestelde bandbreedte
                $jaarverbruik_e = rand(AVERAGE_USAGE_YEAR_E_MIN, AVERAGE_USAGE_YEAR_E_MAX);
                $jaarverbruik_g = rand(AVERAGE_USAGE_YEAR_G_MIN, AVERAGE_USAGE_YEAR_G_MAX);

                foreach ($telwerken as $key => $telwerk) {
                    # echo "    # maand: $monthNr\n";

                    $values = [
                        "fk_id_metertelwerk" => $telwerk['fk_id_metertelwerk'],
                        "stand" => $telwerk['stand'],
                        "datum" => METERSTANDEN_DATE_START,
                        "days" => $addedDays + $randomDay,
                        "tijd" => $randomTime
                    ];
                    setParameterValues($statement_new_meterstand, $values);
                    if (!$statement_new_meterstand->execute()) {
                        var_dump($db->errorInfo());
                        die();
                    }
                    // randomly increase the value
                    $year_consume = 0;
                    switch($telwerk['product']){
                        case "G":
                            $year_consume = $jaarverbruik_g;
                            break;
                        case "E":
                            $year_consume = $jaarverbruik_g;
                            break;
                    }

                    $telwerken[$key]['stand'] += $year_consume * (rand($perc_low, $perc_high) / 100);
                }// for each telwerk

                // add a number of days according to the month of the year
                $addedDays += $monthDays[$monthNr]['days'];

            }//for a number of months
        } // loop through housenr

        $db->exec("COMMIT;");
    }// loop through streets
}// Loop through cities