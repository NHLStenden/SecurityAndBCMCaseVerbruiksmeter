/**
  * Selecteer aantal meterstanden per postcodegebied
 */
SELECT a_postcode as postcode,
       ms_datum as datum,
       ms_product as product,
       count(*) as aantalMeterstanden
FROM `tbl_meters_standen`
     JOIN tbl_meters ON m_idMeter = ms_fk_idMeter
     JOIN tbl_adressen ON m_fk_idAdres = a_idAdres
GROUP BY a_postcode, ms_datum,ms_product;

/**
  * Selecteer aantal meterstanden per gemeente, per maand, alleen verbruik, geen teruglevering
 */
SELECT a_gemeente as gemeente,
       DATE_FORMAT(ms_datum, '%Y-%m') as datum,
       ms_product as product,
       count(*) as aantalMeterstanden
FROM `tbl_meters_standen`
     JOIN tbl_meters ON m_idMeter = ms_fk_idMeter
     JOIN tbl_adressen ON m_fk_idAdres = a_idAdres
WHERE ms_product in ('E','G')
  AND ms_telwerk in (1,2)
GROUP BY a_gemeente, datum ,ms_product;

/**
  * Selecteer aantal meterstanden per postcodegebied zonder letters
 */
SELECT substr(a_postcode,1, 4) as postcode,
       ms_datum as datum,
       ms_product as product,
       count(*) as aantalMeterstanden
FROM `tbl_meters_standen`
         JOIN tbl_meters ON m_idMeter = ms_fk_idMeter
         JOIN tbl_adressen ON m_fk_idAdres = a_idAdres
GROUP BY postcode, ms_datum,ms_product;

/**
  Alle meterstanden van een meter, per product in de tijd uitgezet
 */
SELECT *
FROM `tbl_meters_standen`
WHERE ms_fk_idMeter=293058
order by ms_fk_idMeter,
         ms_product,
         ms_telwerk,
         ms_datum,
         ms_tijd