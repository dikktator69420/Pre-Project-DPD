    CREATE DATABASE if not exists address_validator;
    use address_validator;

    DROP TABLE IF EXISTS addresses;

    CREATE TABLE addresses (
        id VARCHAR(7) PRIMARY KEY,
        straße VARCHAR(255),
        haus_nummer VARCHAR(50),
        plz VARCHAR(20),
        stadt VARCHAR(100)
    );


    DROP TABLE IF EXISTS ortschaften;

    CREATE TABLE ortschaften (
        id VARCHAR(5) PRIMARY KEY,
        ort_name VARCHAR(255)
    );


    DROP TABLE IF EXISTS strassen;

    CREATE TABLE strassen (
        id VARCHAR(6) PRIMARY KEY,
        strasse_name VARCHAR(255)
    );

    LOAD DATA INFILE 'ORTSCHAFT.csv' 
    INTO TABLE ortschaften
    FIELDS TERMINATED BY ';'
    ENCLOSED BY '"'
    LINES TERMINATED BY '\n'
    IGNORE 1 ROWS
    (@GKZ, @OKZ, @ORTSNAME)  
    SET 
        id = @OKZ,
        ort_name = @ORTSNAME;




    LOAD DATA INFILE 'STRASSE.csv' 
    INTO TABLE strassen
    FIELDS TERMINATED BY ';'
    ENCLOSED BY '"'
    LINES TERMINATED BY '\n'
    IGNORE 1 ROWS
    (@SKZ, @STRASSENNAME, @STRASSENNAMENZUSATZ, @SZUSADRBEST, @GKZ, @ZUSTELLORT, @ZUSTELLORT_ID)  
    SET 
        id = @SKZ,
        strasse_name = @STRASSENNAME;  




    LOAD DATA INFILE 'ADRESSE.csv' 
    INTO TABLE addresses
    FIELDS TERMINATED BY ';'
    ENCLOSED BY '"'
    LINES TERMINATED BY '\n'
    IGNORE 1 ROWS
    (@ADRCD, @GKZ, @OKZ, @PLZ, @SKZ, @ZAEHLSPRENGEL, @HAUSNRTEXT, @HAUSNRZAHL1, @HAUSNRBUCHSTABE1, @HAUSNRVERBINDUNG1, @HAUSNRZAHL2, @HAUSNRBUCHSTABE2, @HAUSNRBEREICH, @HNR_ADR_ZUSAMMEN, @GNRADRESSE, @HOFNAME, @RW, @HW, @EPSG, @QUELLADRESSE, @BESTIMMUNGSART)  
    SET 
        id = @ADRCD,
        straße = (
            SELECT strasse_name
            FROM strassen
            WHERE id = @sKZ
        ),
        haus_nummer = concat(@HAUSNRTEXT, @HAUSNRZAHL1, @HAUSNRBUCHSTABE1, @HAUSNRVERBINDUNG1, @HAUSNRZAHL2, @HAUSNRBUCHSTABE2, @HAUSNRBEREICH),
        plz = @PLZ,
        stadt = (
            SELECT ort_name
            FROM ortschaften
            WHERE id = @OKZ
        );