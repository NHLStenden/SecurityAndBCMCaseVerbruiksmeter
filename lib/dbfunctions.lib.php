<?php

function setParameterValues(PDOStatement $statement, $parameters)
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

function TruncateTableMedewerkers($db) {
    $db->prepare("DELETE FROM tbl_medewerkers;")->execute();
}