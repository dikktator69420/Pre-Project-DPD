// Global variables for storing form elements
let formElements = {};
const API_BASE_URL = "http://localhost:5500/api";
const MIN_CHARS_FOR_RECOMMENDATION = 3;

// Initialize the form when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Initialize form elements
  formElements = {
    AdressZeile1: document.getElementById("Adresszeile1"),
    AdressZeile2: document.getElementById("Adresszeile2"),
    Strasse: document.getElementById("Straße"), // Keep original element ID
    Hausnummer: document.getElementById("Hausnummer"),
    Tuernummer: document.getElementById("Türnummer"), // Keep original element ID 
    PLZ: document.getElementById("PLZ"),
    Ort: document.getElementById("Ort"),
    Land: document.getElementById("Land"),
    resultElement: document.getElementById("result")
  };

  // Check if all elements exist
  for (const [key, element] of Object.entries(formElements)) {
    if (!element && key !== 'resultElement') {
      console.error(`Element ${key} not found`);
    }
  }

  // Add event listeners for real-time recommendations
  if (formElements.Strasse) {
    formElements.Strasse.addEventListener('input', debounce(function() {
      const value = this.value.trim();
      if (value.length >= MIN_CHARS_FOR_RECOMMENDATION) {
        getStreetRecommendations(value);
      } else {
        hideRecommendations('strasse-recommendations');
      }
    }, 300));
  }

  if (formElements.Ort) {
    formElements.Ort.addEventListener('input', debounce(function() {
      const value = this.value.trim();
      if (value.length >= MIN_CHARS_FOR_RECOMMENDATION) {
        getCityRecommendations(value);
      } else {
        hideRecommendations('city-recommendations');
      }
    }, 300));
  }
  
  // Add click event listener to document to hide recommendations when clicking outside
  document.addEventListener('click', function(event) {
    const strasseRecs = document.getElementById('strasse-recommendations');
    const cityRecs = document.getElementById('city-recommendations');
    
    // If clicking outside of recommendations, hide them
    if (strasseRecs && !strasseRecs.contains(event.target) && event.target !== formElements.Strasse) {
      hideRecommendations('strasse-recommendations');
    }
    
    if (cityRecs && !cityRecs.contains(event.target) && event.target !== formElements.Ort) {
      hideRecommendations('city-recommendations');
    }
  });
});

// Debounce function to avoid excessive API calls
function debounce(func, wait) {
  let timeout;
  return function() {
    const context = this;
    const args = arguments;
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      func.apply(context, args);
    }, wait);
  };
}

// Submit form data and update fields with corrected values
async function Submit() {
  try {
    // Clear any existing result or error messages
    if (formElements.resultElement) {
      formElements.resultElement.textContent = "Processing...";
      formElements.resultElement.className = "processing";
    }

    // Create address object from form fields
    const address = {};
    for (const [key, element] of Object.entries(formElements)) {
      if (element && key !== 'resultElement') {
        address[key] = element.value.trim();
      }
    }

    // Send validation request
    const response = await validateAddress(address, API_BASE_URL);

    // Handle the response
    if (response.status === 1 && response.corrected_address) {
      // Update form fields with corrected values
      updateFormFields(response.corrected_address);
      
      // Display success message
      if (formElements.resultElement) {
        formElements.resultElement.textContent = response.message || "Address validated and corrected successfully!";
        formElements.resultElement.className = "success";
      }
    } else {
      // Display error message
      if (formElements.resultElement) {
        formElements.resultElement.textContent = response.message || "Address validation failed.";
        formElements.resultElement.className = "error";
      }
    }
  } catch (error) {
    console.error("Submission failed:", error);
    
    // Display error message
    if (formElements.resultElement) {
      formElements.resultElement.textContent = `Error: ${error.message}`;
      formElements.resultElement.className = "error";
    }
  }
}

// Update form fields with corrected address
function updateFormFields(correctedAddress) {
  // Map API response fields to form fields
  if (correctedAddress.Strasse && formElements.Strasse) {
    formElements.Strasse.value = correctedAddress.Strasse;
    highlightField(formElements.Strasse);
  }
  
  if (correctedAddress.Hausnummer && formElements.Hausnummer) {
    formElements.Hausnummer.value = correctedAddress.Hausnummer;
    highlightField(formElements.Hausnummer);
  }
  
  if (correctedAddress.PLZ && formElements.PLZ) {
    formElements.PLZ.value = correctedAddress.PLZ;
    highlightField(formElements.PLZ);
  }
  
  if (correctedAddress.Ort && formElements.Ort) {
    formElements.Ort.value = correctedAddress.Ort;
    highlightField(formElements.Ort);
  }
}

// Highlight field that has been corrected
function highlightField(element) {
  element.classList.add('corrected');
  
  // Remove the highlight after a delay
  setTimeout(() => {
    element.classList.remove('corrected');
  }, 3000);
}

// Fetch street recommendations
async function getStreetRecommendations(query) {
  try {
    console.log("Fetching street recommendations for:", query);
    const response = await fetch(`${API_BASE_URL}/index.php?route=recommend-strasse`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json; charset=utf-8',
        'Accept': 'application/json; charset=utf-8'
      },
      body: JSON.stringify({ query: query })
    });

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data = await response.json();
    console.log("Street recommendations received:", data);
    
    if (data.status === 1 && data.recommendations) {
      displayRecommendations(data.recommendations, 'Strasse', 'strasse-recommendations');
    } else {
      hideRecommendations('strasse-recommendations');
    }
  } catch (error) {
    console.error("Error fetching street recommendations:", error);
    hideRecommendations('strasse-recommendations');
  }
}

// Fetch city recommendations
async function getCityRecommendations(query) {
  try {
    console.log("Fetching city recommendations for:", query);
    const response = await fetch(`${API_BASE_URL}/index.php?route=recommend-stadt`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json; charset=utf-8',
        'Accept': 'application/json; charset=utf-8'
      },
      body: JSON.stringify({ query: query })
    });

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data = await response.json();
    console.log("City recommendations received:", data);
    
    if (data.status === 1 && data.recommendations) {
      displayRecommendations(data.recommendations, 'Ort', 'city-recommendations');
    } else {
      hideRecommendations('city-recommendations');
    }
  } catch (error) {
    console.error("Error fetching city recommendations:", error);
    hideRecommendations('city-recommendations');
  }
}

// Display recommendations in a dropdown
function displayRecommendations(recommendations, fieldName, containerId) {
  // Get the input field and its parent form group
  const inputField = formElements[fieldName];
  if (!inputField) return;
  
  const formGroup = inputField.parentElement;
  
  // Create or get existing recommendations container
  let recommendationsContainer = document.getElementById(containerId);
  
  if (!recommendationsContainer) {
    recommendationsContainer = document.createElement('div');
    recommendationsContainer.id = containerId;
    recommendationsContainer.className = 'recommendations';
    formGroup.appendChild(recommendationsContainer);
  }
  
  // Clear previous recommendations
  recommendationsContainer.innerHTML = '';
  
  // Add new recommendations
  recommendations.forEach(item => {
    const recommendation = document.createElement('div');
    recommendation.className = 'recommendation-item';
    recommendation.textContent = item;
    
    // Add click handler to select this recommendation
    recommendation.addEventListener('click', () => {
      inputField.value = item;
      hideRecommendations(containerId);  // Explicitly hide after selection
    });
    
    recommendationsContainer.appendChild(recommendation);
  });
  
  // Show the recommendations container
  recommendationsContainer.style.display = 'block';
}

// Hide recommendations dropdown
function hideRecommendations(containerId) {
  const container = document.getElementById(containerId);
  if (container) {
    container.style.display = 'none';
  }
}

// Send address validation request to API
async function validateAddress(address, baseUrl) {
  console.log('Sending request to:', `${baseUrl}/index.php?route=validate`);
  console.log('Request body:', address);
  
  try {
    const response = await fetch(`${baseUrl}/index.php?route=validate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json; charset=utf-8',
        'Accept': 'application/json; charset=utf-8'
      },
      mode: 'cors',
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