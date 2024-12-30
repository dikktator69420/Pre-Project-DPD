const URL = "placeholder";

async function Submit() {
  var Straße = document.getElementById("Straße").value;
  var Hausnummer = document.getElementById("Hausnummer").value;
  var Türnummer = document.getElementById("Türnummer").value;
  var PLZ = document.getElementById("PLZ").value;
  var Ort = document.getElementById("Ort").value;
  var Land = document.getElementById("Land").value;

  const response = await fetch(`${URL}/process.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      strings: [Straße, Hausnummer, Türnummer, PLZ, Ort, Land],
    }),
  });

  const data = await response.json();
  document.getElementById("result").textContent =
    data.status === "success" ? data.result : data.message;

  //This is still theoretical, process.php doesnt exist yet, however, this is a functionally complete frontend
}
