<?php
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
