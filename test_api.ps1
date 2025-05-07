# Test API endpoints with revised logic

# Ensure PowerShell uses UTF-8 encoding for input and output
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$PSDefaultParameterValues['Out-File:Encoding'] = 'utf8'
$PSDefaultParameterValues['*:Encoding'] = 'utf8'


# Function to make API requests and display results in a readable format
function Test-ApiEndpoint {
    param (
        [string]$Method,
        [string]$Route,
        [object]$Body = $null
    )
    
    Write-Host "`n====== Testing $Method $Route ======" -ForegroundColor Cyan
    
    $Uri = "http://localhost:5500/api/index.php?route=$Route"
    $Headers = @{
        "Content-Type" = "application/json; charset=utf-8"
        "Accept" = "application/json"
    }
    
    try {
        if ($Body) {
            # Use UTF-8 encoding for the request body
            $BodyJson = ConvertTo-Json -InputObject $Body -Depth 10
            Write-Host "Request Body: $BodyJson" -ForegroundColor Gray
            
            $utf8Encoding = [System.Text.Encoding]::UTF8
            $bodyBytes = $utf8Encoding.GetBytes($BodyJson)
            
            $Response = Invoke-WebRequest -Uri $Uri -Method $Method -Headers $Headers -Body $bodyBytes
        } else {
            $Response = Invoke-WebRequest -Uri $Uri -Method $Method -Headers $Headers
        }
        
        Write-Host "Status: $($Response.StatusCode) $($Response.StatusDescription)" -ForegroundColor Green
        
        try {
            $Content = $Response.Content | ConvertFrom-Json
            Write-Host "Response:" -ForegroundColor Green
            $Content | ConvertTo-Json -Depth 10 | Write-Host
        } catch {
            Write-Host "Raw Response:" -ForegroundColor Yellow
            Write-Host $Response.Content
        }
    } catch {
        Write-Host "Error: $_" -ForegroundColor Red
        Write-Host "Response: $($_.Exception.Response)" -ForegroundColor Red
    }
}

# 1. Test 'test' endpoint
Test-ApiEndpoint -Method "GET" -Route "test"

# 2. Test address validation with a valid address
$validAddress = @{
    AdressZeile1 = "Firmenname GmbH"
    AdressZeile2 = "Abteilung Einkauf"
    Strasse = "Mariahilfer Straße"
    Hausnummer = "50"
    PLZ = "1060"
    Ort = "Wien"
    Land = "AT"
}
Test-ApiEndpoint -Method "POST" -Route "validate" -Body $validAddress

# 3. Test PLZ recommendations (minimum 2 characters)
$plzQuery = @{
    query = "10"
}
Test-ApiEndpoint -Method "POST" -Route "recommend-plz" -Body $plzQuery

# 4. Test PLZ recommendations with Ort filter
$plzQueryWithFilter = @{
    query = "10"
    ort = "Wien"
}
Test-ApiEndpoint -Method "POST" -Route "recommend-plz" -Body $plzQueryWithFilter

# 5. Test Ort (city) recommendations (minimum 3 characters)
$ortQuery = @{
    query = "Wie"
}
Test-ApiEndpoint -Method "POST" -Route "recommend-stadt" -Body $ortQuery

# 6. Test street recommendations (minimum 3 characters)
$streetQuery = @{
    query = "Mar"
}
Test-ApiEndpoint -Method "POST" -Route "recommend-strasse" -Body $streetQuery

# 7. Test street recommendations with PLZ filter
$streetQueryWithPLZ = @{
    query = "Mar"
    plz = "1060"
}
Test-ApiEndpoint -Method "POST" -Route "recommend-strasse" -Body $streetQueryWithPLZ

# 8. Test street recommendations with Ort filter
$streetQueryWithOrt = @{
    query = "Mar"
    ort = "Wien"
}
Test-ApiEndpoint -Method "POST" -Route "recommend-strasse" -Body $streetQueryWithOrt

# 9. Test street recommendations with both PLZ and Ort filters
$streetQueryWithBothFilters = @{
    query = "Mar"
    plz = "1060"
    ort = "Wien"
}
Test-ApiEndpoint -Method "POST" -Route "recommend-strasse" -Body $streetQueryWithBothFilters

# 10. Test with complex street name that includes house number
$complexAddress = @{
    AdressZeile1 = "Firmenname GmbH"
    AdressZeile2 = "Abteilung Einkauf"
    Strasse = "Mariahilfer Straße 50/Stiege 7/8. Stock"
    Hausnummer = ""
    PLZ = "1060"
    Ort = "Wien"
    Land = "AT"
}
Test-ApiEndpoint -Method "POST" -Route "validate" -Body $complexAddress

Write-Host "`nAll tests completed." -ForegroundColor Cyan