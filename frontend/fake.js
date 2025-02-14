function Submit() {
  var AdressZeile1 = document.getElementById("Adresszeile1")?.value;
  var AdressZeile2 = document.getElementById("Adresszeile2")?.value;
  var Strasse = document.getElementById("Straße")?.value;
  var Hausnummer = document.getElementById("Hausnummer")?.value;
  var Türnummer = document.getElementById("Türnummer")?.value;
  var PLZ = document.getElementById("PLZ")?.value;
  var Ort = document.getElementById("Ort")?.value;
  var Land = document.getElementById("Land")?.value;

  const response = determineResponse(
    Strasse,
    Hausnummer,
    Türnummer,
    PLZ,
    Ort,
    Land
  );

  const resultElement = document.getElementById("result");

  if (resultElement) {
    resultElement.textContent = JSON.stringify(response);
  }
}

// country DE
//
// Mariahilfer Strasse
//
// strasse has hausnummer
//
// MaRiAhIlFeR sTrAsSe
//
// ort = max rottenmanner

function determineResponse(straße, hausnummer, türnummer, plz, ort, land) {
  if (land != "AT") {
    return "invalid country";
  } else if (straße == "Mariahilfer Strasse") {
    var strasse = "Mariahilfer Straße";
    return { strasse, hausnummer, türnummer, plz, ort, land };
  } else if (straße == "Mariahilfer Straße 1") {
    var strasse = "Mariahilfer Straße";
    var hausnummer = "1";
    return { strasse, hausnummer: "1", türnummer, plz, ort, land };
  } else if (straße == "MaRiAhIlFeR sTrAsSe") {
    var strasse = "Mariahilfer Straße";
    return { strasse, hausnummer, türnummer, plz, ort, land };
  } else if (straße == "Mariahilfer Straße") {
    return { strasse, hausnummer, türnummer, plz, ort, land };
  } else if (straße == "Mariahilferstraße") {
    var strasse = "Mariahilfer Straße";
    return { strasse, hausnummer, türnummer, plz, ort, land };
  } else if (straße == "Mariahilfer Str.") {
    var strasse = "Mariahilfer Straße";
    return { strasse, hausnummer, türnummer, plz, ort, land };
  } else if (straße == "Spenger Gasse") {
    var strasse = "Spengergasse";
    return { strasse, hausnummer, türnummer, plz, ort, land };
  } else {
    return "invalid input";
  }
}
