<?php
include_once "./constants.inc.php";
function setParameterValues($statement, $parameters)
{
    foreach ($parameters as $key => $value) {
        $statement->bindValue(":" . $key, $value);
    }
}//SetParameterValues

function randomMySQLdate($minAgeInDays, $maxAgeInDays)
{
    $dt = new DateTime();
    $days = rand($minAgeInDays, $maxAgeInDays);
    $sub = new DateInterval("P${days}D");
    $dt->sub($sub);
    $text = $dt->format("Y-m-d");
    return $text;
}

/**
 * Selecteer een willekeurig item uit een lijst op basis van een gewicht. dit wordt gedaan door een lijst met getallen
 * op te geven; is het willekeurige getal kleiner dan het opgegeven gewicht, dan wordt dit item gekozen
 * @param $items
 * @param $weights
 */
function gewogenRandomFromArray($items, $weights) {
    $number = rand(0, 100);
    for ($i=0; $i < count($items); $i++){
        if ($number < $weights[$i]) {
            return $items[$i];
        }
    }
}

srand(12345);
$alleEmployeeStatussen = ['P', 'U', 'A'];
$employeeGender        = ['M', 'V', 'X'];
$emailDomain           = 'mijn-nrg.nl';
$beschikbarefuncties = [
    ["aantal" => 1,  "naam" => "manager backoffice",         "status" => ['A']],
    ["aantal" => 1,  "naam" => "manager callcenter",         "status" => ['A']],
    ["aantal" => 1,  "naam" => "manager personeelszaken",    "status" => ['A']],
    ["aantal" => 1,  "naam" => "manager ICT",                "status" => ['A']],
    ["aantal" => 2,  "naam" => "admin ICT",                  "status" => ['A']],
    ["aantal" => 40, "naam" => "medewerker backoffice",      "status" => $alleEmployeeStatussen],
    ["aantal" => 80, "naam" => "medewerker callcenter",      "status" => $alleEmployeeStatussen],
    ["aantal" => 10, "naam" => "medewerker ICT",             "status" => $alleEmployeeStatussen],
    ["aantal" => 10, "naam" => "medewerker personeelszaken", "status" => $alleEmployeeStatussen],
];

$db = new PDO(MYSQL_DSN, DB_USERNAME, DB_PASSWORD);
echo "Medewerkers .......\n";
$db->prepare("DELETE FROM tbl_medewerkers;")->execute();
$db->prepare("SET NAMES utf8;")->execute();

$sql_new_mdw = "INSERT INTO tbl_medewerkers (
    emp_achternaam,      
    emp_voornaam,     
    emp_bsn,    
    emp_email,   
    emp_status,  
    emp_personeelsnummer, 
    emp_geslacht,
    emp_functie,
    emp_datum_in_dienst,
    emp_datum_uit_dienst                         
) VALUES (
    :achternaam, :voornaam, :bsn, :mail, :status, :personeelsnr, :geslacht, :functie,:datumInDienst, :datumUitDienst
);
";
$statement_new_mdw = $db->prepare($sql_new_mdw);
if ($statement_new_mdw === false) {
    var_dump($db->errorInfo());
    die();
}

$voornamen_file = file_get_contents("./datafiles/voornamen.sorted.txt");
$achternamen_file = file_get_contents("./datafiles/achternamen.sorted.txt");
$voornamen = explode("\n", $voornamen_file);
$achternamen = explode("\n", $achternamen_file);
$count_voornamen = count($voornamen);
$count_achternamen = count($achternamen);

foreach ($beschikbarefuncties as $functie) {
    $aantalTeGenerern   = $functie["aantal"];
    $functienaam        = $functie["naam"];
    $mogelijkeStatussen = $functie["status"];

    for ($i = 0; $i < $aantalTeGenerern; $i++) {
        $voornaam = $voornamen[rand(0, $count_voornamen - 1)];
        $achternaam = $achternamen[rand(0, $count_achternamen - 1)];

        $personeelsnr = crc32($voornaam . $achternaam . rand());
        $bsn = crc32(rand());
        $mail_voornaam   = preg_replace("/ /", "-", $voornaam);
        $mail_achternaam = preg_replace("/(.*)\, (.*)/", '${2}.${1}', $achternaam);
        $mail_achternaam = preg_replace("/ /", "-", $mail_achternaam);

        $mail = "$mail_voornaam.$mail_achternaam@${emailDomain}";
        if (count($mogelijkeStatussen)>1){
            $status = gewogenRandomFromArray($mogelijkeStatussen, [10, 30, 101]);
        }
        else{
            $status = $mogelijkeStatussen[0];
        }

        $geslacht = $employeeGender[rand(0, count($employeeGender) - 1)];
        $datumUitDienst = null;

        if ($status != 'A') {
            $datumUitDienst = randomMySQLdate(10, 100);
        }
        // zorg dat datum in dienst altijd vóór datum uit dienst ligt.
        $datumInDienst = randomMySQLdate(150, 5000);

        $new_mdw_values = [
            "achternaam" => $achternaam,
            "voornaam" => $voornaam,
            "bsn" => $bsn,
            "mail" => $mail,
            "status" => $status,
            "personeelsnr" => $personeelsnr,
            "geslacht" => $geslacht,
            "functie" => $functienaam,
            "datumInDienst" => $datumInDienst,
            "datumUitDienst" => $datumUitDienst,
        ];
        setParameterValues($statement_new_mdw, $new_mdw_values);
        if (!$statement_new_mdw->execute()) {
            var_dump($new_mdw_values, $db->errorInfo());
            die();
        }
    }
}
