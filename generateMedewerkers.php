<?php
include_once "./constants.inc.php";
include_once "./lib/dbfunctions.lib.php";
include_once "./lib/medewerkers.lib.php";

srand(12345);

$beschikbarefuncties = [
    ["aantal" => 1,  "naam" => "manager backoffice",         "status" => ['A']],
    ["aantal" => 1,  "naam" => "manager callcenter",         "status" => ['A']],
    ["aantal" => 1,  "naam" => "manager personeelszaken",    "status" => ['A']],
    ["aantal" => 1,  "naam" => "manager ICT",                "status" => ['A']],
    ["aantal" => 2,  "naam" => "admin ICT",                  "status" => ['A']],
    ["aantal" => 40, "naam" => "medewerker backoffice",      "status" => Medewerkers::$alleEmployeeStatussen],
    ["aantal" => 80, "naam" => "medewerker callcenter",      "status" => Medewerkers::$alleEmployeeStatussen],
    ["aantal" => 10, "naam" => "medewerker ICT",             "status" => Medewerkers::$alleEmployeeStatussen],
    ["aantal" => 10, "naam" => "medewerker personeelszaken", "status" => Medewerkers::$alleEmployeeStatussen],
];

$db = new PDO(MYSQL_DSN, DB_USERNAME, DB_PASSWORD);
echo "Medewerkers .......\n";

TruncateTableMedewerkers($db);

$db->prepare("SET NAMES utf8;")->execute();

$medewerkers = new Medewerkers($db);

$medewerkers->GenereerMedewerkers($beschikbarefuncties);
