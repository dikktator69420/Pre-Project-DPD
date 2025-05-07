CREATE DATABASE IF NOT EXISTS address_validator DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE address_validator;

-- Clean up existing tables if they exist
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS ortschaften;
DROP TABLE IF EXISTS strassen;

-- Create tables with ASCII column names but UTF-8 character support
CREATE TABLE ortschaften (
    id VARCHAR(5) PRIMARY KEY,
    ort_name VARCHAR(255)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE strassen (
    id VARCHAR(6) PRIMARY KEY,
    strasse_name VARCHAR(255)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE addresses (
    id VARCHAR(7) PRIMARY KEY,
    strasse VARCHAR(255),
    haus_nummer VARCHAR(50),
    plz VARCHAR(20),
    stadt VARCHAR(100)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Insert test data for ortschaften (cities/municipalities)
INSERT INTO ortschaften (id, ort_name) VALUES
('W01', 'Wien'),
('S01', 'Salzburg'),
('I01', 'Innsbruck'),
('G01', 'Graz'),
('L01', 'Linz'),
('K01', 'Klagenfurt'),
('B01', 'Bregenz'),
('E01', 'Eisenstadt'),
('SP1', 'St. Pölten');

-- Insert test data for strassen (streets) with special characters preserved
INSERT INTO strassen (id, strasse_name) VALUES
-- Vienna streets
('VW001', 'Stephansplatz'),
('VW002', 'Kärntner Straße'),
('VW003', 'Mariahilfer Straße'),
('VW004', 'Landstraßer Hauptstraße'),
('VW005', 'Favoritenstraße'),
('VW006', 'Neubaugasse'),
-- Salzburg streets
('SZ001', 'Getreidegasse'),
('SZ002', 'Linzer Gasse'),
('SZ003', 'Schwarzstraße'),
-- Innsbruck streets
('IN001', 'Maria-Theresien-Straße'),
('IN002', 'Museumstraße'),
-- Graz streets
('GZ001', 'Herrengasse'),
('GZ002', 'Sporgasse'),
-- Linz streets
('LZ001', 'Landstraße'),
('LZ002', 'Hauptplatz'),
-- Klagenfurt streets
('KL001', 'Alter Platz'),
('KL002', 'St. Veiter Straße'),
-- Bregenz streets
('BR001', 'Kaiserstraße'),
('BR002', 'Anton-Schneider-Straße'),
-- Eisenstadt streets
('ES001', 'Hauptstraße'),
('ES002', 'Esterhazystraße'),
-- St. Pölten streets
('SP001', 'Kremser Gasse'),
('SP002', 'Wiener Straße');

-- Insert test data for addresses with special characters preserved
INSERT INTO addresses (id, strasse, haus_nummer, plz, stadt) VALUES
-- Vienna addresses
('VW00101', 'Stephansplatz', '1', '1010', 'Wien'),
('VW00201', 'Kärntner Straße', '38', '1010', 'Wien'),
('VW00301', 'Mariahilfer Straße', '1', '1060', 'Wien'),
('VW00302', 'Mariahilfer Straße', '45', '1060', 'Wien'),
('VW00303', 'Mariahilfer Straße', '120', '1070', 'Wien'),
('VW00401', 'Landstraßer Hauptstraße', '28', '1030', 'Wien'),
('VW00501', 'Favoritenstraße', '51', '1040', 'Wien'),
('VW00601', 'Neubaugasse', '25', '1070', 'Wien'),
-- Salzburg addresses
('SZ00101', 'Getreidegasse', '9', '5020', 'Salzburg'),
('SZ00201', 'Linzer Gasse', '31', '5020', 'Salzburg'),
('SZ00301', 'Schwarzstraße', '20', '5020', 'Salzburg'),
-- Innsbruck addresses
('IN00101', 'Maria-Theresien-Straße', '18', '6020', 'Innsbruck'),
('IN00201', 'Museumstraße', '15', '6020', 'Innsbruck'),
-- Graz addresses
('GZ00101', 'Herrengasse', '16', '8010', 'Graz'),
('GZ00201', 'Sporgasse', '11', '8010', 'Graz'),
-- Linz addresses
('LZ00101', 'Landstraße', '33', '4020', 'Linz'),
('LZ00201', 'Hauptplatz', '8', '4020', 'Linz'),
-- Klagenfurt addresses
('KL00101', 'Alter Platz', '9', '9020', 'Klagenfurt'),
('KL00201', 'St. Veiter Straße', '27', '9020', 'Klagenfurt'),
-- Bregenz addresses
('BR00101', 'Kaiserstraße', '14', '6900', 'Bregenz'),
('BR00201', 'Anton-Schneider-Straße', '11', '6900', 'Bregenz'),
-- Eisenstadt addresses
('ES00101', 'Hauptstraße', '15', '7000', 'Eisenstadt'),
('ES00201', 'Esterhazystraße', '11', '7000', 'Eisenstadt'),
-- St. Pölten addresses
('SP00101', 'Kremser Gasse', '17', '3100', 'St. Pölten'),
('SP00201', 'Wiener Straße', '23', '3100', 'St. Pölten');

-- Special cases for testing address normalization
INSERT INTO addresses (id, strasse, haus_nummer, plz, stadt) VALUES
('SP00301', 'Wiener Straße', '5-7', '3100', 'St. Pölten'),
('VW00701', 'Schönbrunner Straße', '213', '1120', 'Wien'),
('VW00801', 'Kärntner Ring', '5-7', '1010', 'Wien'),
('VW00901', 'Burggasse', '47/Tür 11', '1070', 'Wien'),
('VW01001', 'Josefstädter Straße', '29/3/14', '1080', 'Wien');