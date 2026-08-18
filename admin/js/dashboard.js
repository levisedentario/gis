var map;
var marker;

function getFieldValue(fieldId) {
    var field = document.getElementById(fieldId);

    if (!field) {
        return "";
    }

    return field.value || "";
}

function normalizeMitigationText(value) {
    if (value === null || value === undefined) {
        return "";
    }

    return String(value).trim().toLowerCase();
}

function getMitigationMeasureMap() {
    var measures = window.mitigationMeasures || [];
    var measureMap = {};

    measures.forEach(function(measure) {
        var factor = normalizeMitigationText(measure.dominant_factor);

        if (factor && !measureMap[factor]) {
            measureMap[factor] = {
                label: measure.dominant_factor || "",
                mitigation: measure.recommended_mitigation || ""
            };
        }
    });

    return measureMap;
}

function extractFirstNumber(value) {
    if (value === null || value === undefined) {
        return null;
    }

    var text = String(value).trim();

    if (!text) {
        return null;
    }

    var match = text.match(/-?\d+(?:\.\d+)?/);

    if (!match) {
        return null;
    }

    var numericValue = Number(match[0]);

    return Number.isNaN(numericValue) ? null : numericValue;
}

function extractMinimumNumber(value) {
    if (value === null || value === undefined) {
        return null;
    }

    var text = String(value).trim();

    if (!text) {
        return null;
    }

    var matches = text.match(/-?\d+(?:\.\d+)?/g);

    if (!matches || !matches.length) {
        return null;
    }

    var numbers = matches.map(function(match) {
        return Number(match);
    }).filter(function(number) {
        return !Number.isNaN(number);
    });

    if (!numbers.length) {
        return null;
    }

    return Math.min.apply(Math, numbers);
}

function containsAny(value, needles) {
    var text = normalizeMitigationText(value);

    if (!text) {
        return false;
    }

    return needles.some(function(needle) {
        return text.indexOf(needle.toLowerCase()) !== -1;
    });
}

function buildRecommendedMitigationText() {
    var measureMap = getMitigationMeasureMap();
    var selectedFactors = [];

    var hydraulicCapacity = extractFirstNumber(getFieldValue("hydraulic_capacity"));
    if (hydraulicCapacity !== null && hydraulicCapacity <= 1.5) {
        selectedFactors.push("low hydraulic capacity");
    }

    if (containsAny(getFieldValue("structural_condition"), ["poor", "damaged", "bad", "critical", "failing", "defective"])) {
        selectedFactors.push("poor structural condition");
    }

    var dimensions = extractMinimumNumber(getFieldValue("dimensions"));
    if (dimensions !== null && dimensions < 1.0) {
        selectedFactors.push("small drainage dimensions");
    }

    var structureType = normalizeMitigationText(getFieldValue("structure_type"));
    if (structureType && !containsAny(structureType, ["culvert", "ditch", "drain", "canal", "pipe", "box", "line"])) {
        selectedFactors.push("inappropriate drainage structure type");
    }

    if (containsAny(getFieldValue("shape"), ["irregular", "oval", "round", "circular", "inefficient"])) {
        selectedFactors.push("inefficient drainage shape");
    }

    var builtSpan = extractFirstNumber(getFieldValue("built_span"));
    if (builtSpan !== null && builtSpan <= 0.5) {
        selectedFactors.push("old built span (age)");
    }

    var elevation = extractFirstNumber(getFieldValue("elevation"));
    if (elevation !== null && elevation <= 2.5) {
        selectedFactors.push("very low elevation");
    }

    var slope = extractFirstNumber(getFieldValue("slope"));
    if (slope !== null && Math.abs(slope) <= 0.2) {
        selectedFactors.push("low slope");
    }

    var weatherSource = normalizeMitigationText(getFieldValue("weather_data_source"));
    if (weatherSource === "manual") {
        var rainIntensity = extractFirstNumber(getFieldValue("rain_intensity"));
        if (rainIntensity !== null && rainIntensity >= 15) {
            selectedFactors.push("high rainfall intensity");
        }

        var rainProbability = extractFirstNumber(getFieldValue("precipitation_probability"));
        if (rainProbability !== null && rainProbability >= 70) {
            selectedFactors.push("high rainfall amount");
        }
    }

    var infiltrationCapacity = getFieldValue("infiltration_capacity");
    var infiltrationNumber = extractFirstNumber(infiltrationCapacity);
    if (containsAny(infiltrationCapacity, ["low", "poor", "slow", "very low"]) || (infiltrationNumber !== null && infiltrationNumber <= 2.0)) {
        selectedFactors.push("low infiltration capacity");
    }

    var buildingDensity = extractFirstNumber(getFieldValue("building_density"));
    if (buildingDensity !== null && buildingDensity >= 50) {
        selectedFactors.push("high building density");
    }

    var impervious = getFieldValue("impervious");
    var imperviousNumber = extractFirstNumber(impervious);
    if (containsAny(impervious, ["high"]) || (imperviousNumber !== null && imperviousNumber >= 50)) {
        selectedFactors.push("high impervious surface percentage");
    }

    if (normalizeMitigationText(getFieldValue("garbage_accommodation")) !== "") {
        selectedFactors.push("high garbage accumulation");
    }

    var drainageObstruction = extractFirstNumber(getFieldValue("drainage_obstruction"));
    if (drainageObstruction !== null && drainageObstruction >= 2.0) {
        selectedFactors.push("high drainage obstruction");
    }

    var vegetationCover = extractFirstNumber(getFieldValue("vegetation_cover"));
    if (vegetationCover !== null && vegetationCover <= 30) {
        selectedFactors.push("low vegetation cover");
    }

    var recommendations = [];
    var seenFactors = {};

    selectedFactors.forEach(function(factor) {
        if (seenFactors[factor]) {
            return;
        }

        seenFactors[factor] = true;

        if (measureMap[factor]) {
            recommendations.push(measureMap[factor].label + ": " + measureMap[factor].mitigation);
        }
    });

    return recommendations.join("\n\n");
}

function refreshRecommendedMitigation() {
    var recommendationField = document.getElementById("recommendation");

    if (!recommendationField) {
        return;
    }

    recommendationField.value = buildRecommendedMitigationText();
}

function normalizeSelectValue(value) {
    if (value === null || value === undefined) {
        return "";
    }

    var normalized = String(value).trim();

    if (normalized === "") {
        return "";
    }

    var numericValue = Number(normalized);

    if (!Number.isNaN(numericValue) && Number.isFinite(numericValue)) {
        var roundedValue = Math.round(numericValue);

        if (Math.abs(numericValue - roundedValue) < 0.0000001) {
            return String(roundedValue);
        }
    }

    return normalized;
}

function setSelectValue(selectId, value) {
    var select = document.getElementById(selectId);

    if (!select) {
        return;
    }

    var normalizedValue = normalizeSelectValue(value);

    if (select.querySelector('option[value="' + normalizedValue.replace(/"/g, '\\"') + '"]')) {
        select.value = normalizedValue;
    } else {
        select.value = "";
    }
}

function updateWeatherManualFieldsVisibility() {
    var sourceSelect = document.getElementById("weather_data_source");
    var manualFields = document.getElementById("weatherManualFields");

    if (!sourceSelect || !manualFields) {
        return;
    }

    manualFields.hidden = normalizeSelectValue(sourceSelect.value) !== "manual";
}

function initMap() {
    var center = {
        lat: 10.243013056575343,
        lng: 123.81043633362316
    };

    map = L.map("map").setView([center.lat, center.lng], 18);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    map.on("click", function(e) {
        var clickedLat = e.latlng.lat;
        var clickedLng = e.latlng.lng;

        document.getElementById("lat").value = clickedLat.toFixed(6);
        document.getElementById("lng").value = clickedLng.toFixed(6);

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });
}

function editLocation(id, name, lat, lng, elevation, builtSpan, garbageAccommodation, buildingDensity, drainageObstruction, drainageConveyance, vegetationCover, structuralCondition, recommendation, hydraulicCapacity, impervious, infiltrationCapacity, soilType, structureType, dimensions, shape, manningN, material, slope, weatherDataSource, weatherInput, rainIntensity, rainIntensityUnit, precipitationProbability) {
    var latitude = parseFloat(lat);
    var longitude = parseFloat(lng);
    var selectedPoint = {
        lat: latitude,
        lng: longitude
    };

    document.getElementById("id").value = id;
    document.getElementById("name").value = name;
    document.getElementById("lat").value = latitude.toFixed(6);
    document.getElementById("lng").value = longitude.toFixed(6);
    document.getElementById("elevation").value = elevation;
    document.getElementById("built_span").value = builtSpan;
    setSelectValue("garbage_accommodation", garbageAccommodation);
    document.getElementById("building_density").value = buildingDensity;
    document.getElementById("drainage_obstruction").value = drainageObstruction;
    document.getElementById("drainage_conveyance").value = drainageConveyance;
    document.getElementById("vegetation_cover").value = vegetationCover;
    document.getElementById("structural_condition").value = structuralCondition;
    document.getElementById("recommendation").value = recommendation;
    document.getElementById("hydraulic_capacity").value = hydraulicCapacity;
    setSelectValue("impervious", impervious);
    setSelectValue("infiltration_capacity", infiltrationCapacity);
    document.getElementById("soil_type").value = soilType;
    document.getElementById("structure_type").value = structureType;
    document.getElementById("dimensions").value = dimensions;
    document.getElementById("shape").value = shape;
    document.getElementById("manning_n").value = manningN;
    document.getElementById("material").value = material;
    document.getElementById("slope").value = slope;
    setSelectValue("weather_data_source", weatherDataSource);

    var weatherInputField = document.getElementById("weather_input");
    var rainIntensityField = document.getElementById("rain_intensity");
    var rainIntensityUnitField = document.getElementById("rain_intensity_unit");
    var precipitationProbabilityField = document.getElementById("precipitation_probability");

    if (weatherInputField) {
        weatherInputField.value = weatherInput;
    }

    if (rainIntensityField) {
        rainIntensityField.value = rainIntensity;
    }

    if (rainIntensityUnitField) {
        rainIntensityUnitField.value = rainIntensityUnit;
    }

    if (precipitationProbabilityField) {
        precipitationProbabilityField.value = precipitationProbability;
    }

    updateWeatherManualFieldsVisibility();

    document.getElementById("frm").action = "controllers/update.php";
    document.getElementById("btn").innerHTML = "Update";

    if (map) {
        map.setView([selectedPoint.lat, selectedPoint.lng], 17);
    }

    if (marker) {
        marker.setLatLng([selectedPoint.lat, selectedPoint.lng]);
    } else {
        marker = L.marker([selectedPoint.lat, selectedPoint.lng]).addTo(map);
    }
}

window.initMap = initMap;
window.editLocation = editLocation;

function computeBuiltSpanScoreFromYears(yearBuilt, currentYear) {
    if (yearBuilt === null || yearBuilt === undefined || yearBuilt === '' || Number.isNaN(Number(yearBuilt))) {
        return null;
    }

    var builtYear = Number(yearBuilt);
    var presentYear = currentYear !== null && currentYear !== undefined && currentYear !== '' ? Number(currentYear) : new Date().getFullYear();

    if (Number.isNaN(builtYear) || Number.isNaN(presentYear)) {
        return null;
    }

    var rawAge = presentYear - builtYear;

    if (rawAge < 0) {
        return null;
    }

    var score = 1 - (rawAge / 50);
    return Math.max(0, Math.min(1, score));
}

function openBuiltSpanModal() {
    var modal = document.getElementById('builtSpanModal');
    if (!modal) {
        return;
    }

    var currentYearInput = document.getElementById('modal_current_year');
    var builtYearInput = document.getElementById('modal_year_built');
    var resultInput = document.getElementById('modal_age_score');

    if (currentYearInput) {
        currentYearInput.value = new Date().getFullYear();
    }

    if (builtYearInput) {
        builtYearInput.value = builtYearInput.value || '';
    }

    if (resultInput) {
        resultInput.value = '';
    }

    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
}

function closeBuiltSpanModal() {
    var modal = document.getElementById('builtSpanModal');
    if (!modal) {
        return;
    }

    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
}

function calculateBuiltSpanModalResult() {
    var builtYearInput = document.getElementById('modal_year_built');
    var currentYearInput = document.getElementById('modal_current_year');
    var resultInput = document.getElementById('modal_age_score');

    if (!builtYearInput || !currentYearInput || !resultInput) {
        return;
    }

    var score = computeBuiltSpanScoreFromYears(builtYearInput.value, currentYearInput.value);

    if (score === null) {
        resultInput.value = '';
        showToast('Please enter a valid year built and current year.', 'error');
        return;
    }

    resultInput.value = Number(score).toFixed(4);
}

function applyBuiltSpanScore() {
    var resultInput = document.getElementById('modal_age_score');
    var builtSpanInput = document.getElementById('built_span');

    if (!resultInput || !builtSpanInput) {
        return;
    }

    if (resultInput.value === '') {
        showToast('Compute the built span score first.', 'error');
        return;
    }

    builtSpanInput.value = resultInput.value;
    closeBuiltSpanModal();
    refreshRecommendedMitigation();
    showToast('Built span score inserted into the form.', 'success');
}

document.addEventListener('DOMContentLoaded', function() {
    var builtSpanButton = document.getElementById('built_span_calc_btn');
    var builtSpanInput = document.getElementById('built_span');
    var computeButton = document.getElementById('computeBuiltSpanScore');
    var applyButton = document.getElementById('applyBuiltSpanScore');
    var closeButton = document.getElementById('closeBuiltSpanModal');
    var modal = document.getElementById('builtSpanModal');

    if (builtSpanInput) {
        builtSpanInput.addEventListener('click', openBuiltSpanModal);
    }

    var weatherDataSourceSelect = document.getElementById('weather_data_source');

    if (weatherDataSourceSelect) {
        weatherDataSourceSelect.addEventListener('change', updateWeatherManualFieldsVisibility);
        updateWeatherManualFieldsVisibility();
    }

    if (builtSpanButton) {
        builtSpanButton.addEventListener('click', openBuiltSpanModal);
    }

    if (computeButton) {
        computeButton.addEventListener('click', calculateBuiltSpanModalResult);
    }

    if (applyButton) {
        applyButton.addEventListener('click', applyBuiltSpanScore);
    }

    if (closeButton) {
        closeButton.addEventListener('click', closeBuiltSpanModal);
    }

    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeBuiltSpanModal();
            }
        });
    }

    var locationForm = document.getElementById('frm');

    if (locationForm) {
        locationForm.addEventListener('input', function(event) {
            if (event.target && event.target.id !== 'recommendation') {
                refreshRecommendedMitigation();
            }
        });

        locationForm.addEventListener('change', function(event) {
            if (event.target && event.target.id !== 'recommendation') {
                refreshRecommendedMitigation();
            }
        });
    }

    refreshRecommendedMitigation();

    // Elevation/Slope modal handlers
    var elevationCalcBtn = document.getElementById('elevation_calc_btn');
    var slopeCalcBtn = document.getElementById('slope_calc_btn');
    var modalSegmentSelect = document.getElementById('modal_segment');
    var modalStationSelect = document.getElementById('modal_station');
    var elevationTypeSelect = document.getElementById('modal_elevation_type');
    var closeElevationSlopeBtn = document.getElementById('closeElevationSlopeModal');
    var applyElevationSlopeBtn = document.getElementById('applyElevationSlopeData');
    var elevationSlopeModal = document.getElementById('elevationSlopeModal');

    if (elevationCalcBtn) {
        elevationCalcBtn.addEventListener('click', openElevationSlopeModal);
    }

    if (slopeCalcBtn) {
        slopeCalcBtn.addEventListener('click', openElevationSlopeModal);
    }

    if (modalSegmentSelect) {
        modalSegmentSelect.addEventListener('change', updateStationOptions);
    }

    if (modalStationSelect) {
        modalStationSelect.addEventListener('change', updateElevationAndSlope);
    }

    if (elevationTypeSelect) {
        elevationTypeSelect.addEventListener('change', updateElevationAndSlope);
    }

    if (closeElevationSlopeBtn) {
        closeElevationSlopeBtn.addEventListener('click', closeElevationSlopeModal);
    }

    if (applyElevationSlopeBtn) {
        applyElevationSlopeBtn.addEventListener('click', applyElevationSlopeData);
    }

    if (elevationSlopeModal) {
        elevationSlopeModal.addEventListener('click', function(event) {
            if (event.target === elevationSlopeModal) {
                closeElevationSlopeModal();
            }
        });
    }
});

function openElevationSlopeModal() {
    var modal = document.getElementById('elevationSlopeModal');
    if (!modal) return;

    document.getElementById('modal_segment').value = '';
    document.getElementById('modal_station').value = '';
    document.getElementById('modal_elevation_value').value = '';
    document.getElementById('modal_slope_value').value = '';

    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
}

function closeElevationSlopeModal() {
    var modal = document.getElementById('elevationSlopeModal');
    if (!modal) return;

    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
}

function updateStationOptions() {
    var segment = document.getElementById('modal_segment').value;
    var stationSelect = document.getElementById('modal_station');

    if (!segment || !stationSelect) return;

    // This would need AJAX or embedded data. For now, clearing options.
    stationSelect.innerHTML = '<option value="">Select station</option>';
    
    // Embedded survey data - stations per segment
    var stationsMap = {
        '1': ['00+000', '00+020', '00+040', '00+060', '00+080', '00+100', '00+120', '00+140', '00+160', '00+180', '00+200', '00+220', '00+240'],
        '2': ['00+240', '00+260', '00+280', '00+300', '00+320', '00+340', '00+360', '00+380', '00+400', '00+420', '00+440', '00+460', '00+480', '00+500', '00+520'],
        '3': ['00+520', '00+540', '00+560', '00+580', '00+600', '00+620', '00+640', '00+660', '00+680', '00+700', '00+720', '00+740', '00+760', '00+780', '00+800'],
        '4': ['00+800', '00+820', '00+840', '00+860', '00+880', '00+900', '00+920', '00+940', '00+960', '00+980', '00+985']
    };

    if (stationsMap[segment]) {
        stationsMap[segment].forEach(function(station) {
            var option = document.createElement('option');
            option.value = station;
            option.textContent = station;
            stationSelect.appendChild(option);
        });
    }
}

function updateElevationAndSlope() {
    var segment = document.getElementById('modal_segment').value;
    var station = document.getElementById('modal_station').value;
    var elevationType = document.getElementById('modal_elevation_type').value;
    var elevationValue = document.getElementById('modal_elevation_value');
    var slopeValue = document.getElementById('modal_slope_value');

    if (!segment || !station) {
        if (elevationValue) elevationValue.value = '';
        if (slopeValue) slopeValue.value = '';
        return;
    }

    // Embedded survey data
    var surveyData = {
        '1': {'00+000': {fg: 3.836, dfl: 3.230}, '00+020': {fg: 3.840, dfl: 3.220}, '00+040': {fg: 3.805, dfl: 3.205}, '00+060': {fg: 3.895, dfl: 3.175}, '00+080': {fg: 4.072, dfl: 3.080}, '00+100': {fg: 4.490, dfl: 2.600}, '00+120': {fg: 4.490, dfl: 2.590}, '00+140': {fg: 4.490, dfl: 2.590}, '00+160': {fg: 4.490, dfl: 2.590}, '00+180': {fg: 4.490, dfl: 2.590}, '00+200': {fg: 4.490, dfl: 2.600}, '00+220': {fg: 4.490, dfl: 2.600}, '00+240': {fg: 4.490, dfl: 2.970}},
        '2': {'00+240': {fg: 4.490, dfl: 2.970}, '00+260': {fg: 4.450, dfl: 2.950}, '00+280': {fg: 4.450, dfl: 2.950}, '00+300': {fg: 4.450, dfl: 2.940}, '00+320': {fg: 4.420, dfl: 2.910}, '00+340': {fg: 4.450, dfl: 2.890}, '00+360': {fg: 4.400, dfl: 2.850}, '00+380': {fg: 4.400, dfl: 2.800}, '00+400': {fg: 4.400, dfl: 2.790}, '00+420': {fg: 4.400, dfl: 2.780}, '00+440': {fg: 4.400, dfl: 2.750}, '00+460': {fg: 4.400, dfl: 2.700}, '00+480': {fg: 4.400, dfl: 2.680}, '00+500': {fg: 4.400, dfl: 2.650}, '00+520': {fg: 4.400, dfl: 2.610}},
        '3': {'00+520': {fg: 4.400, dfl: 2.610}, '00+540': {fg: 4.350, dfl: 2.590}, '00+560': {fg: 4.350, dfl: 2.550}, '00+580': {fg: 4.300, dfl: 2.520}, '00+600': {fg: 4.250, dfl: 2.490}, '00+620': {fg: 4.200, dfl: 2.450}, '00+640': {fg: 4.150, dfl: 2.400}, '00+660': {fg: 4.100, dfl: 2.350}, '00+680': {fg: 4.050, dfl: 2.300}, '00+700': {fg: 4.000, dfl: 2.280}, '00+720': {fg: 3.950, dfl: 2.250}, '00+740': {fg: 3.950, dfl: 2.230}, '00+760': {fg: 3.950, dfl: 2.220}, '00+780': {fg: 3.930, dfl: 2.210}, '00+800': {fg: 3.915, dfl: 2.210}},
        '4': {'00+800': {fg: 3.915, dfl: 2.210}, '00+820': {fg: 3.700, dfl: 2.180}, '00+840': {fg: 3.500, dfl: 2.160}, '00+860': {fg: 3.200, dfl: 2.130}, '00+880': {fg: 2.900, dfl: 2.110}, '00+900': {fg: 2.600, dfl: 2.090}, '00+920': {fg: 2.300, dfl: 2.070}, '00+940': {fg: 2.100, dfl: 2.060}, '00+960': {fg: 1.800, dfl: 2.050}, '00+980': {fg: 1.600, dfl: 2.050}, '00+985': {fg: 1.540, dfl: 2.050}}
    };

    // Segment slopes in degrees
    var segmentSlopes = {'1': -0.150, '2': -0.074, '3': -0.082, '4': -0.735};

    if (surveyData[segment] && surveyData[segment][station]) {
        var data = surveyData[segment][station];
        var selectedElevation = elevationType === 'finished_grade' ? data.fg : data.dfl;
        
        if (elevationValue) elevationValue.value = selectedElevation.toFixed(3);
        if (slopeValue) slopeValue.value = (segmentSlopes[segment] || 0).toFixed(3);
    }
}

function applyElevationSlopeData() {
    var elevationValue = document.getElementById('modal_elevation_value').value;
    var slopeValue = document.getElementById('modal_slope_value').value;
    var elevationInput = document.getElementById('elevation');
    var slopeInput = document.getElementById('slope');

    if (!elevationValue || !slopeValue) {
        showToast('Please select a station first.', 'error');
        return;
    }

    if (elevationInput) elevationInput.value = elevationValue;
    if (slopeInput) slopeInput.value = slopeValue;

    closeElevationSlopeModal();
    refreshRecommendedMitigation();
    showToast('Elevation and slope values applied.', 'success');
}

function showToast(message, type = 'success') {
    var toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast show ' + type;
    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(function() {
        toast.className = 'toast';
    }, 3000);
}

window.showToast = showToast;
