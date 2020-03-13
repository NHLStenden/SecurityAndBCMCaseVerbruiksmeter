/**
  * Selecteer aantal meterstanden per postcodegebied
 */
SELECT a_postcode as postcode,
       ms_datum as datum,
       mt_product as product,
       count(*) as aantalMeterstanden
FROM tbl_adressen
     JOIN tbl_meters          ON m_fk_idAdres = a_idAdres
     JOIN tbl_meter_telwerken ON mt_fk_idMeter = m_idMeter
     JOIN tbl_meters_standen  ON ms_fk_idMeterTelwerk = mt_idMeterTelwerk
GROUP BY a_postcode, ms_datum,mt_product;

/**
  * Selecteer aantal meterstanden per gemeente, per maand, alleen verbruik, geen teruglevering
 */
SELECT a_gemeente as gemeente,
       DATE_FORMAT(ms_datum, '%Y-%m') as datum,
       mt_product as product,
       count(*) as aantalMeterstanden
FROM tbl_adressen
    JOIN tbl_meters          ON m_fk_idAdres = a_idAdres
    JOIN tbl_meter_telwerken ON mt_fk_idMeter = m_idMeter
    JOIN tbl_meters_standen  ON ms_fk_idMeterTelwerk = mt_idMeterTelwerk
WHERE mt_product in ('E','G')
  AND mt_telwerk in (1,2)
GROUP BY a_gemeente, datum ,mt_product;

/**
  * Selecteer aantal meterstanden per postcodegebied zonder letters
 */
SELECT substr(a_postcode,1, 4) as postcode,
       ms_datum as datum,
       mt_product as product,
       count(*) as aantalMeterstanden
FROM tbl_adressen
         JOIN tbl_meters          ON m_fk_idAdres = a_idAdres
         JOIN tbl_meter_telwerken ON mt_fk_idMeter = m_idMeter
         JOIN tbl_meters_standen  ON ms_fk_idMeterTelwerk = mt_idMeterTelwerk
GROUP BY postcode, ms_datum,mt_product;

/**
  Alle meterstanden van een meter, per product in de tijd uitgezet
 */
SELECT *
FROM tbl_meters_standen
     JOIN tbl_meter_telwerken ON mt_idMeterTelwerk = ms_fk_idMeterTelwerk
WHERE mt_fk_idMeter = 293058
order by mt_product,
         mt_telwerk,
         ms_datum,
         ms_tijd