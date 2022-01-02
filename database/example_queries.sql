/**
  * Selecteer aantal meterstanden per postcodegebied
 */
SELECT a_postcode as postcode,
       ms_datum   as datum,
       m_product  as product,
       count(*)   as aantalMeterstanden
FROM tbl_adressen
         JOIN tbl_meters ON m_fk_idAdres = a_idAdres
         JOIN tbl_meter_telwerken ON mt_fk_idMeter = m_idMeter
         JOIN tbl_meters_standen ON ms_fk_idMeterTelwerk = mt_idMeterTelwerk
GROUP BY a_postcode, ms_datum, m_product;

/**
  * Selecteer aantal meterstanden per gemeente, per maand, alleen verbruik, geen teruglevering
 */
SELECT a_gemeente                     as gemeente,
       DATE_FORMAT(ms_datum, '%Y-%m') as datum,
       m_product                      as product,
       count(*)                       as aantalMeterstanden
FROM tbl_adressen
         JOIN tbl_meters ON m_fk_idAdres = a_idAdres
         JOIN tbl_meter_telwerken ON mt_fk_idMeter = m_idMeter
         JOIN tbl_meters_standen ON ms_fk_idMeterTelwerk = mt_idMeterTelwerk
WHERE m_product in ('E', 'G')
  AND mt_telwerk in (1, 2)
GROUP BY a_gemeente, datum, m_product;

/**
  * Selecteer aantal meterstanden per postcodegebied zonder letters
 */
SELECT substr(a_postcode, 1, 4) as postcode,
       ms_datum                 as datum,
       m_product                as product,
       count(*)                 as aantalMeterstanden
FROM tbl_adressen
         JOIN tbl_meters ON m_fk_idAdres = a_idAdres
         JOIN tbl_meter_telwerken ON mt_fk_idMeter = m_idMeter
         JOIN tbl_meters_standen ON ms_fk_idMeterTelwerk = mt_idMeterTelwerk
GROUP BY postcode, ms_datum, m_product;

/**
  Alle meterstanden van een meter, per product in de tijd uitgezet
 */
SELECT m_idMeter  as meternummer,
       m_product  as product,
       mt_telwerk as telwerk,
       mt_type    as type,
       ms_stand,
       ms_datum,
       ms_tijd
FROM tbl_meters
         JOIN tbl_meter_telwerken tmt on tbl_meters.m_idMeter = tmt.mt_fk_idMeter
         JOIN tbl_meters_standen tms on tmt.mt_idMeterTelwerk = tms.ms_fk_idMeterTelwerk
WHERE m_idMeter = 293058
order by m_product, mt_telwerk, ms_datum, ms_tijd

/**
  Aantal medewerkers per status
 */
select emp_status, count(*) as aantal
from tbl_medewerkers
GROUP BY emp_status;
