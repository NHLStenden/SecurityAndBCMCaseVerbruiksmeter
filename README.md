# SecurityAndRiskCaseMetering
Supporting code for the case of the "Verbruiksmeter"

# Aanmaken database
Maak via MySQL eerst zelf een database aan. Open daarna het bestand `CreateDatabase.sql` uit
de map `database` en voer deze uit.

# Aanmaken random adressen, meters, meterstanden & klanten
Wijzig eerst het bestand `constants.inc.php` om de juiste database informatie op te nemen in 
dit script. 

Open daarna een terminal venster op de locatie van deze scripts en start onderstaande commando's

```bash
php generateMeters.php
php generatePersons.php
```

Je ziet enige rapportage over de voortgang. Uiteindelijk zullen er vele willekeurige gegevens
in de database geplaatst worden.

Zie vervolgens in de map `database` het bestand  `example_queries.sql` voor enkele voorbeelden. 
Het datamodel spreekt grofweg voor zichzelf:

1. Een adres heeft een uniek ID
1. Elke meter verwijst naar één adres via m_fk_idAdres
1. Een meter heeft één of meerdere telwerken 
1. Een meter heeft één of meerdere meterstanden, steeds per telwerk.
