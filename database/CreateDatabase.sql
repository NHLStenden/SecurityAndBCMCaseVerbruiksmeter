-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Gegenereerd op: 16 feb 2020 om 22:55
-- Serverversie: 5.7.29-0ubuntu0.18.04.1
-- PHP-versie: 7.2.24-0ubuntu0.18.04.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS tbl_adressen  ;
DROP TABLE IF EXISTS tbl_klanten  ;
DROP TABLE IF EXISTS tbl_meter_telwerken  ;
DROP TABLE IF EXISTS tbl_meters  ;
DROP TABLE IF EXISTS tbl_meters_standen  ;

--
-- Database: `secriskyouthenergy`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tbl_adressen`
--

CREATE TABLE `tbl_adressen` (
    `a_idAdres` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `a_plaatsnaam` varchar(80) NOT NULL,
    `a_gemeente` varchar(80) NOT NULL,
    `a_provincie` varchar(80) NOT NULL,
    `a_regio` varchar(80) NOT NULL,
    `a_straatnaam` varchar(200) NOT NULL,
    `a_huisnummer` varchar(16) NOT NULL,
    `a_postcode` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tbl_klanten`
--

CREATE TABLE `tbl_klanten` (
   `k_idKlant` int(11) NOT NULL PRIMARY KEY  AUTO_INCREMENT,
   `k_klantnummer` varchar(24) NOT NULL UNIQUE,
   `k_achternaam` varchar(100) NOT NULL,
   `k_voornaam` varchar(80) NOT NULL,
   `k_fk_idAdres` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Klanten en verwijzing naar adres';

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tbl_meters`
--

CREATE TABLE `tbl_meters` (
  `m_idMeter` int(11) NOT NULL ,
  `m_fk_idAdres` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Meternummers en adressen';

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tbl_meters_standen`
--

CREATE TABLE `tbl_meters_standen` (
  `ms_idMeterstand` int(11) NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `ms_fk_idMeterTelwerk` int(11) NOT NULL,
  `ms_stand` int(11) NOT NULL,
  `ms_datum` date NOT NULL,
  `ms_tijd` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tbl_meter_telwerken`
--

CREATE TABLE `tbl_meter_telwerken` (
   `mt_idMeterTelwerk` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `mt_fk_idMeter` int(11) NOT NULL,
   `mt_product` varchar(1) NOT NULL,
   `mt_telwerk` smallint(6) NOT NULL,
   `mt_type` varchar(1) NOT NULL COMMENT 'Verbruik of teruglevering'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `tbl_adressen`
--
ALTER TABLE `tbl_adressen`
    ADD KEY `postcode` (`a_postcode`);

--
-- Indexen voor tabel `tbl_klanten`
--
ALTER TABLE `tbl_klanten`
    ADD KEY `idAdres` (`k_fk_idAdres`);

--
-- Indexen voor tabel `tbl_meters`
--
ALTER TABLE `tbl_meters`
    ADD KEY `idAdres` (`m_fk_idAdres`) USING BTREE;

--
-- Indexen voor tabel `tbl_meters_standen`
--
ALTER TABLE `tbl_meters_standen`
    ADD KEY `idMeterTelwerk` (`ms_fk_idMeterTelwerk`);

--
-- Indexen voor tabel `tbl_meter_telwerken`
--
ALTER TABLE `tbl_meter_telwerken`
    ADD UNIQUE KEY `mt_fk_idMeter` (`mt_fk_idMeter`,`mt_telwerk`,`mt_type`,`mt_product`),
    ADD KEY `idMeter` (`mt_fk_idMeter`);
