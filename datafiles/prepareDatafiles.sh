#!/bin.bash
# Versie : 2
# Datum  : 2020-02-27
# Dit script bereid de input bestanden voor gebruik door de twee generate-scripts
# Dit script moet op Linux Bash gestart worden

# onderstaande twee scripts komen te vervallen doordat er nu een .PHP-script voor in de plaats
# is gekomen
# sed -n 's/\(.*"Title":"\)\([A-Za-z0-9 ]*\)\(".*\)/\2/p' PlaatsEnGemeentenamen.json > plaatsnamen.txt
# cat plaatsnamen.txt | sort | uniq > plaatsnamen.sorted.txt

cat achternamen.txt | sort | uniq > achternamen.sorted.txt
cat voornamen.txt   | sort | uniq > voornamen.sorted.txt
cat streetnames.txt | sort | uniq > streetnames.sorted.txt

php processWoonplaatsen.php
