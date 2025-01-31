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
// Mariahilferstrasse
//
// strasse has hausnummer
//
// MaRiAhIlFeRsTrAsSe
//
// ort = max rottenmanner

function determineResponse(straße, hausnummer, türnummer, plz, ort, land) {
  if (land != "AT") {
    return "invalid country";
  } else if (straße == "Mariahilferstrasse") {
    var strasse = "Mariahilferstraße";
    return { strasse, hausnummer, türnummer, plz, ort, land };
  } else if (straße == "Mariahilferstraße 1") {
    var strasse = "Mariahilferstraße";
    var hausnummer = "1";
    return { strasse, hausnummer: "1", türnummer, plz, ort, land };
  } else if (straße == "MaRiAhIlFeRsTrAsSe") {
    var strasse = "Mariahilferstraße";
    return { strasse, hausnummer, türnummer, plz, ort, land };
  } else {
    return "invalid input";
  }
}
