async function Submit() {
  try {
    const elements = {
      AdressZeile1: document.getElementById("Adresszeile1"),
      AdressZeile2: document.getElementById("Adresszeile2"),
      Strasse: document.getElementById("Straße"),
      Hausnummer: document.getElementById("Hausnummer"),
      Türnummer: document.getElementById("Türnummer"),
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
      "http://localhost:5500/api"  // remove /validate from here
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
  console.log('Sending request to:', `${baseUrl}/index.php?route=validate`);
  console.log('Request body:', address);
  
  try {
    const response = await fetch(`${baseUrl}/index.php?route=validate`, {
      method: 'POST',  // Make sure it's uppercase
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      mode: 'cors',  // Add this explicitly
      body: JSON.stringify(address)
    });

    console.log('Response status:', response.status);
    console.log('Response headers:', [...response.headers.entries()]);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log('Response data:', data);
    return data;
  } catch (error) {
    console.error('Validation error:', error);
    throw error;
  }
}
