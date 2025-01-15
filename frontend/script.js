const URL = "placeholder";

async function Submit() {
  var adresszeile1 = document.getElementById("Adresszeile1").value;
  var adresszeile2 = document.getElementById("Adresszeile2").value;
  var straße = document.getElementById("Straße").value;
  var hausnummer = document.getElementById("Hausnummer").value;
  var türnummer = document.getElementById("Türnummer").value;
  var pLZ = document.getElementById("PLZ").value;
  var ort = document.getElementById("Ort").value;
  var land = document.getElementById("Land").value;

  const address = {
    Adresszeile1: adresszeile1,
    Adresszeile2: adresszeile2,
    Straße: straße,
    Hausnummer: hausnummer,
    Türnummer: türnummer,
    PLZ: pLZ,
    Ort: ort,
    Land: land,
  };

  const response = await validateAddress(
    address,
    "http://localhost:8000/api/routes.php"
  );

  const data = await response.json();

  document.getElementById("result").textContent = data;
}

async function validateAddress(address, baseUrl) {
  try {
    const response = await fetch(`${baseUrl}/validate`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(address),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Address validation failed:", error);
    return {
      status: 0,
      message: error.message || "Address validation failed",
    };
  }
}
