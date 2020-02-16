sed -n 's/\(.*"Title":"\)\([A-Za-z0-9 ]*\)\(".*\)/\2/p' PlaatsEnGemeentenamen.json > plaatsnamen.txt
cat achternamen.txt | sort | uniq > achternamen.sorted.txt
cat voornamen.txt   | sort | uniq > voornamen.sorted.txt
cat streetnames.txt | sort | uniq > streetnames.sorted.txt
cat plaatsnamen.txt | sort | uniq > plaatsnamen.sorted.txt
