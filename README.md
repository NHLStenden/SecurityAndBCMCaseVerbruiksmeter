# Security & Risk : Case Verbruiksmeter
Supporting code for the case of the "Verbruiksmeter"

## Aanmaken database
Maak via MySQL eerst zelf een database aan. Open daarna het bestand `CreateDatabase.sql` uit
de map `database` en voer deze uit binnen deze nieuwe database.

## Database instellingen opnemen
Maak in de root van deze map met PHP-bestanden een nieuw bestand genaamd `database.settings`. 
Zet daar onderstaande regels in en vul de juiste waarden in:
```ini
host=localhost
name=<databasenaam>
username=<myuser>
password=<mypassword>
```

## Instellen aantallen te genereren items
In het bestand `generateMeters.php` vind je bovenin een aantal instellingen:
```php
  define ("METERSTANDEN_DATE_START", "2016-01-01");
  define ("NR_OF_METERSTANDEN", 12);

  define ("NR_OF_CITIES",5);

  define ("MIN_STREETS_PER_CITY", 5);
  define ("MAX_STREETS_PER_CITY", 20);

  define ("MIN_HUISNUMMERS_PER_STRAAT",10);
  define ("MAX_HUISNUMMERS_PER_STRAAT",40);
```
Hiermee kun je regelen hoe groot de set aan gegevens moet zijn.  

## Aanmaken random adressen, meters, meterstanden & klanten
Open een terminal venster op de locatie van deze scripts en start onderstaande commando's

```bash
php generateMeters.php
php generatePersons.php
```

Je ziet enige rapportage over de voortgang. Genereren van meterstanden en adressen:
```text  
Performing cleanup
.......
Telwerken.......
Meterstanden.......
Meters.......
Adressen.......
Found:  5630 places and 8606 streets
Using (0 of 50) Steenenkruis and ZIP-code 4162
 - Generating 11 streets
  - [0] Generating 15 home-addresses
  - [1] Generating 21 home-addresses
  - [2] Generating 17 home-addresses
  - [3] Generating 11 home-addresses
  - [4] Generating 28 home-addresses
  - [5] Generating 16 home-addresses
  - [6] Generating 11 home-addresses
  - [7] Generating 32 home-addresses
  - [8] Generating 15 home-addresses
  - [9] Generating 16 home-addresses
.....
```

```text  
Performing cleanup
.......
Klanten.......
Adres: 11900 
Adres: 11800 
Adres: 11700 
Adres: 11600 
Adres: 11500 
Adres: 11400 
Adres: 11300 
Adres: 11200 
Adres: 11100 
Adres: 11000 
Adres: 10900 
Adres: 10800 
Adres: 10700 
Adres: 10600 
...........
```

Uiteindelijk zullen er vele willekeurige gegevens in de database geplaatst worden. 
Er worden grote hoeveelheden gegevens gegenereerd, dus enig geduld is wel nodig.
Af en toe kun je in je database management tool (bijv. PHPMyAdmin) kijken hoe het er voor staat. Een voorbeeld:

![image](./images/database_report.png)

Dit is met 50 steden als NR_OF_CITIES.

Zie vervolgens in de map `database` het bestand  `example_queries.sql` voor enkele voorbeelden. 
Het datamodel spreekt grofweg voor zichzelf:

1. Een adres heeft een uniek ID
1. Elke meter verwijst naar één adres via m_fk_idAdres
1. Een meter heeft één of meerdere telwerken 
1. Een meter heeft één of meerdere meterstanden, steeds per telwerk.

Hieronder vind je het Entity Relation Diagram dat de tabellen en hun relaties beschrijft.

![ERD](images/Entity%20Relationship%20Diagram.png)

## Verbeteringen
Deze data is natuurlijk pas het begin voor je project. Je zult zelf vast de nodige verbeteringen nodig hebben
om de applicatie af te kunnen maken. 

