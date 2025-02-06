var config = {
    cUrl: 'https://api.countrystatecity.in/v1/countries',
    ckey: 'NHhvOEcyWk50N2Vna3VFTE00bFp3MjFKR0ZEOUhkZlg4RTk1MlJlaA=='
}

var stateSelect = document.querySelector('#region'),
    citySelect = document.querySelector('#district')

function loadRegions() {
    const TANZANIA_CODE = 'TZ'
    
    stateSelect.innerHTML = '<option value="">Select Region</option>'
    citySelect.innerHTML = '<option value="">Select District</option>'

    fetch(`${config.cUrl}/${TANZANIA_CODE}/states`, {
        headers: {"X-CSCAPI-KEY": config.ckey}
    })
    .then(response => response.json())
    .then(data => {
        data.forEach(state => {
            const option = document.createElement('option')
            option.value = state.iso2
            option.textContent = state.name
            stateSelect.appendChild(option)
        })
    })
    .catch(error => console.error('Error loading regions:', error))
}

function loadDistricts() {
    const TANZANIA_CODE = 'TZ'
    const selectedRegionCode = stateSelect.value
    
    citySelect.innerHTML = '<option value="">Select District</option>'

    fetch(`${config.cUrl}/${TANZANIA_CODE}/states/${selectedRegionCode}/cities`, {
        headers: {"X-CSCAPI-KEY": config.ckey}
    })
    .then(response => response.json())
    .then(data => {
        data.forEach(city => {
            const option = document.createElement('option')
            option.value = city.iso2
            option.textContent = city.name
            citySelect.appendChild(option)
        })
    })
}

// Event listener for region selection
stateSelect.addEventListener('change', loadDistricts)

// Load regions on page load
window.onload = loadRegions