async function Submit() {
  try {
    const elements = {
      AdressZeile1: document.getElementById("Adresszeile1"),
      AdressZeile2: document.getElementById("Adresszeile2"),
      Strasse: document.getElementById("Straße"),
      Hausnummer: document.getElementById("Hausnummer"),
      Tuernummer: document.getElementById("Türnummer"),
      PLZ: document.getElementById("PLZ"),
      Ort: document.getElementById("Ort"),
      Land: document.getElementById("Land"),
    };

    for (const [key, element] of Object.entries(elements)) {
      if (!element) {
        throw new Error(`Element ${key} not found`);
      }
    }

    const address = Object.fromEntries(
      Object.entries(elements).map(([key, element]) => [key, element.value])
    );

    const response = await validateAddress(
      address,
      "http://localhost:8000/api/validate"
    );

    const resultElement = document.getElementById("result");

    if (resultElement) {
      resultElement.textContent = JSON.stringify(response);
    }
  } catch (error) {
    console.error("Submission failed:", error);
    const resultElement = document.getElementById("result");
    if (resultElement) {
      resultElement.textContent = `Error: ${error.message}`;
    }
  }
}

async function validateAddress(address, baseUrl) {
  try {
    const response = await fetch(baseUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(address),
    });

    if (!response.ok) {
      throw new Error(
        `HTTP error! response is not ok! status: ${response.status}`
      );
    }

    return await response.json();
  } catch (error) {
    console.error("Address validation failed:", error);
    return {
      status: 0,
      message: error.message || "Address validation failed",
    };
  }
}
