async function Submit() {
  const URL = "placeholder";

  var Straße = document.getElementById("Straße").value;
  var Hausnummer = document.getElementById("Hausnummer").value;
  var Türnummer = document.getElementById("Türnummer").value;
  var PLZ = document.getElementById("PLZ").value;
  var Ort = document.getElementById("Ort").value;
  var Land = document.getElementById("Land").value;

  var result = await fetch(
    `${URL}/${Straße}/${Hausnummer}/${Türnummer}/${PLZ}/${Ort}/${Land}`
  ).then((response) => response.json());
  document.getElementById("result").innerHTML = result.value;
}
