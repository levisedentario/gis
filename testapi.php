<?php
$defaultLat = 10.244704;
$defaultLon = 123.813120;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Forecast UI</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #07111f;
            --panel: rgba(255,255,255,0.08);
            --panel-strong: rgba(255,255,255,0.14);
            --text: #f8fbff;
            --muted: #b8c4d9;
            --accent: #4cc9f0;
            --accent-2: #80ffdb;
            --border: rgba(255,255,255,0.16);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #07111f 0%, #11253f 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .card {
            width: min(1200px, 100%);
            background: var(--panel);
            backdrop-filter: blur(14px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .title h1 {
            margin: 0 0 6px;
            font-size: 1.8rem;
        }

        .title p {
            margin: 0;
            color: var(--muted);
        }

        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        input {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.08);
            color: var(--text);
            min-width: 120px;
        }

        button {
            padding: 10px 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #02111c;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover { filter: brightness(1.05); }

        .status {
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(255,255,255,0.06);
            color: var(--muted);
            margin-bottom: 18px;
        }

        .current {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 14px;
            margin-bottom: 12px;
        }

        .panel {
            background: var(--panel-strong);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 14px 16px;
        }

        .temp {
            font-size: 2.6rem;
            font-weight: 700;
            margin: 6px 0;
        }

        .meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 0.95rem;
            margin-top: 8px;
        }

        .forecast-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }

        .forecast-item {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-height: 140px;
        }

        .forecast-item strong {
            display: block;
            margin-bottom: 6px;
        }

        .metric-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            margin-top: 8px;
            font-size: 0.9rem;
            color: var(--muted);
        }

        .metric-list span {
            background: rgba(255,255,255,0.04);
            border-radius: 8px;
            padding: 6px 8px;
        }

        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(76, 201, 240, 0.16);
            border: 1px solid rgba(76, 201, 240, 0.28);
            color: var(--accent-2);
            font-weight: 600;
            margin-top: 8px;
        }

        .analysis {
            margin-top: 10px;
        }

        .analysis-grid {
            display: grid;
            gap: 10px;
            margin-top: 8px;
        }

        .rain-chart {
            background: rgba(255,255,255,0.05);
            border-radius: 14px;
            padding: 12px;
        }

        .rain-bars {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            align-items: end;
            min-height: 120px;
        }

        .rain-bar-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: var(--muted);
        }

        .rain-bar {
            width: 100%;
            min-height: 6px;
            border-radius: 8px 8px 4px 4px;
            background: linear-gradient(180deg, var(--accent-2), var(--accent));
        }

        .muted { color: var(--muted); }

        @media (max-width: 900px) {
            .current { grid-template-columns: 1fr; }
        }

        @media (max-width: 680px) {
            .card { padding: 16px; }
            .controls { width: 100%; }
            input { flex: 1; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="title">
                <h1>Weather Forecast UI</h1>
                <p>Realtime conditions for your selected location</p>
                <div class="location-badge">📍 Tungkil, Minglanilla, Cebu</div>
            </div>
            <div class="controls">
                <input id="lat" type="number" step="0.0001" value="<?php echo $defaultLat; ?>" placeholder="Latitude">
                <input id="lon" type="number" step="0.0001" value="<?php echo $defaultLon; ?>" placeholder="Longitude">
                <button id="loadBtn">Get Weather</button>
            </div>
        </div>

        <div id="status" class="status">Loading forecast...</div>

        <div class="current">
            <div class="panel">
                <div class="muted" id="locationLabel">Location</div>
                <div class="temp" id="currentTemp">--°C</div>
                <div id="condition">Fetching data...</div>
                <div class="meta">
                    <span id="currentWeather">Weather: --</span>
                    <span id="currentRain">Rain: --</span>
                    <span id="currentCloud">Cloud cover: --</span>
                    <span id="currentTime">Time: --</span>
                </div>
            </div>
            <div class="panel">
                <div class="muted">Next hours</div>
                <div class="forecast-grid" id="forecastList"></div>
            </div>
        </div>

        <div class="panel analysis">
            <div class="muted">Rain analysis</div>
            <div class="analysis-grid">
                <div id="rainSummary">Preparing rain trend...</div>
                <div class="rain-chart" id="rainChart"></div>
            </div>
        </div>
    </div>

    <script>
        const statusEl = document.getElementById('status');
        const locationLabelEl = document.getElementById('locationLabel');
        const currentTempEl = document.getElementById('currentTemp');
        const conditionEl = document.getElementById('condition');
        const currentWeatherEl = document.getElementById('currentWeather');
        const currentRainEl = document.getElementById('currentRain');
        const currentCloudEl = document.getElementById('currentCloud');
        const currentTimeEl = document.getElementById('currentTime');
        const forecastListEl = document.getElementById('forecastList');
        const rainSummaryEl = document.getElementById('rainSummary');
        const rainChartEl = document.getElementById('rainChart');
        const latInput = document.getElementById('lat');
        const lonInput = document.getElementById('lon');
        const loadBtn = document.getElementById('loadBtn');

        const weatherCodeMap = {
            0: { label: 'Clear sky', icon: '☀️' },
            1: { label: 'Mainly clear', icon: '🌤️' },
            2: { label: 'Partly cloudy', icon: '⛅' },
            3: { label: 'Overcast', icon: '☁️' },
            45: { label: 'Fog', icon: '🌫️' },
            48: { label: 'Rime fog', icon: '🌫️' },
            51: { label: 'Light drizzle', icon: '🌦️' },
            53: { label: 'Moderate drizzle', icon: '🌦️' },
            55: { label: 'Dense drizzle', icon: '🌧️' },
            61: { label: 'Slight rain', icon: '🌧️' },
            63: { label: 'Moderate rain', icon: '🌧️' },
            65: { label: 'Heavy rain', icon: '⛈️' },
            71: { label: 'Slight snow', icon: '🌨️' },
            73: { label: 'Moderate snow', icon: '❄️' },
            75: { label: 'Heavy snow', icon: '❄️' },
            95: { label: 'Thunderstorm', icon: '⛈️' },
            96: { label: 'Thunderstorm with hail', icon: '⛈️' },
            99: { label: 'Severe hail', icon: '⛈️' }
        };

        function formatTime(value) {
            const date = new Date(value);
            return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        }

        function getWeatherLabel(code) {
            return weatherCodeMap[code] || { label: 'Unknown', icon: '🌈' };
        }

        async function loadWeather() {
            const lat = latInput.value;
            const lon = lonInput.value;

            if (!lat || !lon) {
                statusEl.textContent = 'Please enter both latitude and longitude.';
                return;
            }

            statusEl.textContent = 'Fetching weather data...';

            try {
                // const url = `https://api.open-meteo.com/v1/forecast?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lon)}&current=temperature_2m,rain,precipitation,weather_code,cloud_cover&hourly=temperature_2m,precipitation,weather_code,cloud_cover&timezone=auto`;
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error('Unable to fetch weather data.');
                }

                const data = await response.json();
                const current = data.current;
                const hourly = data.hourly || {};
                const weatherInfo = getWeatherLabel(current.weather_code);
                const cloudCover = Math.round(current.cloud_cover ?? 0);
                const temperature = Math.round(current.temperature_2m ?? 0);

                const isDefaultLocation = Math.abs(lat - 10.244704) < 0.0001 && Math.abs(lon - 123.813120) < 0.0001;
                locationLabelEl.textContent = isDefaultLocation ? 'Tungkil, Minglanilla, Cebu • 10.244704, 123.813120' : `Location: ${lat}, ${lon}`;
                currentTempEl.textContent = `${temperature}°C`;
                conditionEl.innerHTML = `${weatherInfo.icon} ${weatherInfo.label}`;
                currentWeatherEl.textContent = `Weather: ${weatherInfo.icon} ${weatherInfo.label}`;
                currentRainEl.textContent = `Rain: ${Math.round(current.rain * 10) / 10} mm`;
                currentCloudEl.textContent = `Cloud cover: ${cloudCover}%`;
                currentTimeEl.textContent = `Time: ${formatTime(current.time)}`;

                const forecastCards = (hourly.time || []).slice(0, 6).map((time, index) => {
                    const hourWeatherCode = hourly.weather_code?.[index];
                    const hourWeather = getWeatherLabel(hourWeatherCode);
                    const hourRain = hourly.precipitation?.[index] ?? 0;
                    const hourCloudCover = Math.round(hourly.cloud_cover?.[index] ?? 0);
                    const hourTemp = Math.round(hourly.temperature_2m?.[index] ?? 0);

                    return `
                        <div class="forecast-item">
                            <strong>${formatTime(time)}</strong>
                            <div class="muted">${hourWeather.icon} ${hourWeather.label}</div>
                            <div class="muted">${hourTemp}°C</div>
                            <div class="muted">Rain: ${Math.round(hourRain * 10) / 10} mm</div>
                            <div class="muted">Cloud cover: ${hourCloudCover}%</div>
                        </div>
                    `;
                }).join('');

                forecastListEl.innerHTML = forecastCards;

                rainSummaryEl.innerHTML = `Weather: ${weatherInfo.icon} ${weatherInfo.label} • Rain: ${Math.round(current.rain * 10) / 10} mm • Cloud cover: ${cloudCover}%`;
                rainChartEl.innerHTML = `
                    <div class="rain-bars">
                        <div class="rain-bar-wrap">
                            <div class="rain-bar" style="height: ${Math.max(12, Math.round((current.rain || 0) * 20))}%"></div>
                            <span>Now</span>
                        </div>
                    </div>
                `;

                statusEl.textContent = 'Weather data loaded successfully.';
            } catch (error) {
                statusEl.textContent = error.message || 'Something went wrong.';
                forecastListEl.innerHTML = '';
                rainSummaryEl.textContent = 'Rain analysis unavailable.';
                rainChartEl.innerHTML = '';
            }
        }

        loadBtn.addEventListener('click', loadWeather);
        window.addEventListener('DOMContentLoaded', loadWeather);
    </script>
</body>
</html>
