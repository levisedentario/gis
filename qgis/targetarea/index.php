<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="initial-scale=1,user-scalable=no,maximum-scale=1,width=device-width">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <link rel="stylesheet" href="css/leaflet.css">
        <link rel="stylesheet" href="css/L.Control.Layers.Tree.css">
        <link rel="stylesheet" href="css/qgis2web.css">
        <link rel="stylesheet" href="css/fontawesome-all.min.css">
        <link rel="stylesheet" href="css/leaflet.photon.css">
        <link rel="stylesheet" href="css/leaflet-measure.css">
        <style>
        html, body, #map {
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
        }
        #map {
            position: relative;
        }
        .area-info-button {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 50%;
            background: #f2f22f;
            color: #ff0000;
            cursor: pointer;
            font-size: 18px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.24);
        }
        .area-info-button:hover {
            background: #ffc800;
        }
        .area-info-marker {
            background: transparent;
            border: 0;
        }
        .area-info-card {
            width: 250px;
            padding: 16px;
            border-radius: 8px;
            background: #fff;
            color: #172033;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.24);
            font-family: Arial, sans-serif;
        }
        .area-info-card h2,
        .area-info-card h3,
        .area-info-card p {
            margin: 0;
        }
        .area-info-card h2 {
            font-size: 18px;
        }
        .area-info-risk {
            margin-top: 6px !important;
            color: #b42318;
            font-size: 14px;
            font-weight: 700;
        }
        .area-info-card h3 {
            margin-top: 14px;
            font-size: 13px;
        }
        .area-info-list {
            margin: 7px 0 14px;
            padding-left: 18px;
            font-size: 13px;
            line-height: 1.55;
        }
        .area-info-guide {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 14px;
            font-size: 12px;
            line-height: 1.45;
            color: #0f172a;
            background: #f8fafc;
            border: 1px solid #dbe4ee;
            border-radius: 6px;
            padding: 8px 10px;
        }
        .area-info-guide i {
            color: #0f766e;
        }
        .area-live-button {
            width: 100%;
            border: 0;
            border-radius: 4px;
            background: #0f766e;
            color: #fff;
            padding: 9px 12px;
            cursor: pointer;
            font-weight: 700;
        }
        .area-live-button:hover {
            background: #115e59;
        }
        .area-help-control {
            background: transparent;
            box-shadow: none;
        }
        .area-help-card {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            width: 280px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.95);
            color: #172033;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(15, 118, 110, 0.14);
            font-family: Arial, sans-serif;
            backdrop-filter: blur(6px);
        }
        .area-help-icon {
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0f766e;
            color: #fff;
            box-shadow: 0 6px 14px rgba(15, 118, 110, 0.26);
            font-size: 16px;
        }
        .area-help-copy {
            min-width: 0;
        }
        .area-help-copy h2 {
            margin: 0 0 4px;
            font-size: 14px;
            line-height: 1.2;
        }
        .area-help-copy p {
            margin: 0;
            font-size: 12px;
            line-height: 1.45;
            color: #334155;
        }
        @media (max-width: 480px) {
            .area-info-card {
                width: min(250px, calc(100vw - 92px));
            }
            .area-help-card {
                width: min(280px, calc(100vw - 92px));
            }
        }
        </style>
        <title></title>
    </head>
    <body>
        <div id="map"></div>
        <script src="js/qgis2web_expressions.js"></script>
        <script src="js/leaflet.js"></script>
        <script src="js/L.Control.Layers.Tree.min.js"></script>
        <script src="js/leaflet.rotatedMarker.js"></script>
        <script src="js/leaflet.pattern.js"></script>
        <script src="js/leaflet-hash.js"></script>
        <script src="js/Autolinker.min.js"></script>
        <script src="js/rbush.min.js"></script>
        <script src="js/labelgun.min.js"></script>
        <script src="js/labels.js"></script>
        <script src="js/leaflet.photon.js"></script>
        <script src="js/leaflet-measure.js"></script>
        <script src="data/TungkilBoundary_2.js"></script>
        <script src="data/River_3.js"></script>
        <script src="data/TargetArea_4.js"></script>
        <script>
        // ===== CONFIGURATION: Add your weather API endpoints here =====
        // Format: { baseUrl: "https://...", apikey: "your_key_here", lat: "latitude", lon: "longitude" }
        const weatherApiEndpoints = [
            {
                baseUrl: "https://my.meteoblue.com/packages/basic-day",
                apikey: "apikey=Be8VLcNijdqtDfZx",
                lat: 10.245,
                lon: 123.796
            },
            {
                baseUrl: "https://my.meteoblue.com/packages/basic-day",
                apikey: "10G4JxM5BnSEfHiq",
                lat: 10.245,
                lon: 123.796
            },
            {
                baseUrl: "https://my.meteoblue.com/packages/basic-day",
                apikey: "gymdWYMKhuNIS15p",
                lat: 10.245,
                lon: 123.796
            },
            {
                baseUrl: "https://my.meteoblue.com/packages/basic-day",
                apikey: "f5d3r69bmsNQLMt2",
                lat: 10.245,
                lon: 123.796
            },
            {
                baseUrl: "https://my.meteoblue.com/packages/basic-day",
                apikey: "lJeCrtzOubQiTN71",
                lat: 10.245,
                lon: 123.796
            }
        ];
        // ============================================================
        
        var highlightLayer;
        function highlightFeature(e) {
            highlightLayer = e.target;
            highlightLayer.openPopup();
        }
        var map = L.map('map', {
            zoomControl:false, maxZoom:28, minZoom:10
        })
        var hash = new L.Hash(map);
        map.attributionControl.setPrefix('<a href="https://github.com/qgis2web/qgis2web" target="_blank">qgis2web</a> &middot; <a href="https://leafletjs.com" title="A JS library for interactive maps">Leaflet</a> &middot; <a href="https://qgis.org">QGIS</a>');
        var autolinker = new Autolinker({truncate: {length: 30, location: 'smart'}});
        // remove popup's row if "visible-with-data"
        function removeEmptyRowsFromPopupContent(content, feature) {
         var tempDiv = document.createElement('div');
         tempDiv.innerHTML = content;
         var rows = tempDiv.querySelectorAll('tr');
         for (var i = 0; i < rows.length; i++) {
             var td = rows[i].querySelector('td.visible-with-data');
             var key = td ? td.id : '';
             if (td && td.classList.contains('visible-with-data') && feature.properties[key] == null) {
                 rows[i].parentNode.removeChild(rows[i]);
             }
         }
         return tempDiv.innerHTML;
        }
        // modify popup if contains media
        function addClassToPopupIfMedia(content, popup) {
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            var imgTd = tempDiv.querySelector('td img');
            if (imgTd) {
                var src = imgTd.getAttribute('src');
                if (/\.(jpg|jpeg|png|gif|bmp|webp|avif)$/i.test(src)) {
                    popup._contentNode.classList.add('media');
                    var img = popup._contentNode.querySelector('td img');
                    if (img) {
                        // If already loaded (cache), update immediately
                        if (img.complete && img.naturalHeight > 0) {
                            popup.update();
                        } else {
                            img.addEventListener('load', function() {
                                popup.update();
                            });
                            img.addEventListener('error', function() {
                                popup.update();
                            });
                        }
                    }
                } else if (/\.(mp3|wav|ogg|aac)$/i.test(src)) {
                    var audio = document.createElement('audio');
                    audio.controls = true;
                    audio.src = src;
                    imgTd.parentNode.replaceChild(audio, imgTd);
                    popup._contentNode.classList.add('media');
                    setTimeout(function() {
                        popup.setContent(tempDiv.innerHTML);
                        popup.update();
                    }, 10);
                } else if (/\.(mp4|webm|ogg|mov)$/i.test(src)) {
                    var video = document.createElement('video');
                    video.controls = true;
                    video.src = src;
                    video.style.width = "400px";
                    video.style.height = "300px";
                    video.style.maxHeight = "60vh";
                    video.style.maxWidth = "60vw";
                    imgTd.parentNode.replaceChild(video, imgTd);
                    popup._contentNode.classList.add('media');
                    // Aggiorna il popup quando il video carica i metadati
                    video.addEventListener('loadedmetadata', function() {
                        popup.update();
                    });
                    setTimeout(function() {
                        popup.setContent(tempDiv.innerHTML);
                        popup.update();
                    }, 10);
                } else {
                    popup._contentNode.classList.remove('media');
                }
            } else {
                popup._contentNode.classList.remove('media');
            }
        }
        var zoomControl = L.control.zoom({
            position: 'topleft'
        }).addTo(map);
        var measureControl = new L.Control.Measure({
            position: 'topleft',
            primaryLengthUnit: 'feet',
            secondaryLengthUnit: 'miles',
            primaryAreaUnit: 'sqfeet',
            secondaryAreaUnit: 'sqmiles'
        });
        measureControl.addTo(map);
        document.getElementsByClassName('leaflet-control-measure-toggle')[0].innerHTML = '';
        document.getElementsByClassName('leaflet-control-measure-toggle')[0].className += ' fas fa-ruler';
        var bounds_group = new L.featureGroup([]);
        function setBounds() {
            if (bounds_group.getLayers().length) {
                var bounds = bounds_group.getBounds();
                map.fitBounds(bounds);
                map.setMaxBounds(bounds);
                map.setMinZoom(map.getZoom());
                map.zoomIn(1);
            }
        }
        map.createPane('pane_GoogleSatellite_0');
        map.getPane('pane_GoogleSatellite_0').style.zIndex = 400;
        var layer_GoogleSatellite_0 = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            pane: 'pane_GoogleSatellite_0',
            opacity: 1.0,
            attribution: '<a href="https://www.google.at/permissions/geoguidelines/attr-guide.html">Map data ©2015 Google</a>',
            minZoom: 10,
            maxZoom: 28,
            minNativeZoom: 0,
            maxNativeZoom: 20
        });
        layer_GoogleSatellite_0;
        map.addLayer(layer_GoogleSatellite_0);
        map.createPane('pane_GoogleLabels_1');
        map.getPane('pane_GoogleLabels_1').style.zIndex = 401;
        var layer_GoogleLabels_1 = L.tileLayer('https://mt1.google.com/vt/lyrs=h&x={x}&y={y}&z={z}', {
            pane: 'pane_GoogleLabels_1',
            opacity: 1.0,
            attribution: '<a href="https://www.google.at/permissions/geoguidelines/attr-guide.html">Map data ©2015 Google</a>',
            minZoom: 10,
            maxZoom: 28,
            minNativeZoom: 0,
            maxNativeZoom: 20
        });
        layer_GoogleLabels_1;
        map.addLayer(layer_GoogleLabels_1);
        function pop_TungkilBoundary_2(feature, layer) {
            layer.on({
                mouseout: function(e) {
                    if (typeof layer.closePopup == 'function') {
                        layer.closePopup();
                    } else {
                        layer.eachLayer(function(feature){
                            feature.closePopup()
                        });
                    }
                },
                mouseover: highlightFeature,
            });
            var popupContent = '<table>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['GID_3'] !== null ? autolinker.link(String(feature.properties['GID_3']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['GID_0'] !== null ? autolinker.link(String(feature.properties['GID_0']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['COUNTRY'] !== null ? autolinker.link(String(feature.properties['COUNTRY']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['GID_1'] !== null ? autolinker.link(String(feature.properties['GID_1']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['NAME_1'] !== null ? autolinker.link(String(feature.properties['NAME_1']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['NL_NAME_1'] !== null ? autolinker.link(String(feature.properties['NL_NAME_1']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['GID_2'] !== null ? autolinker.link(String(feature.properties['GID_2']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['NAME_2'] !== null ? autolinker.link(String(feature.properties['NAME_2']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['NL_NAME_2'] !== null ? autolinker.link(String(feature.properties['NL_NAME_2']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['NAME_3'] !== null ? autolinker.link(String(feature.properties['NAME_3']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['VARNAME_3'] !== null ? autolinker.link(String(feature.properties['VARNAME_3']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['NL_NAME_3'] !== null ? autolinker.link(String(feature.properties['NL_NAME_3']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['TYPE_3'] !== null ? autolinker.link(String(feature.properties['TYPE_3']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['ENGTYPE_3'] !== null ? autolinker.link(String(feature.properties['ENGTYPE_3']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['CC_3'] !== null ? autolinker.link(String(feature.properties['CC_3']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['HASC_3'] !== null ? autolinker.link(String(feature.properties['HASC_3']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                </table>';
            var content = removeEmptyRowsFromPopupContent(popupContent, feature);
			layer.on('popupopen', function(e) {
				addClassToPopupIfMedia(content, e.popup);
			});
			layer.bindPopup(content, { maxHeight: 400 });
        }

        function style_TungkilBoundary_2_0() {
            return {
                pane: 'pane_TungkilBoundary_2',
                opacity: 1,
                color: 'rgba(255,0,0,1.0)',
                dashArray: '',
                lineCap: 'square',
                lineJoin: 'bevel',
                weight: 1.0,
                fillOpacity: 0,
                interactive: false,
            }
        }
        map.createPane('pane_TungkilBoundary_2');
        map.getPane('pane_TungkilBoundary_2').style.zIndex = 402;
        map.getPane('pane_TungkilBoundary_2').style['mix-blend-mode'] = 'normal';
        var layer_TungkilBoundary_2 = new L.geoJson(json_TungkilBoundary_2, {
            attribution: '',
            interactive: false,
            dataVar: 'json_TungkilBoundary_2',
            layerName: 'layer_TungkilBoundary_2',
            pane: 'pane_TungkilBoundary_2',
            onEachFeature: pop_TungkilBoundary_2,
            style: style_TungkilBoundary_2_0,
        });
        bounds_group.addLayer(layer_TungkilBoundary_2);
        map.addLayer(layer_TungkilBoundary_2);
        function pop_River_3(feature, layer) {
            layer.on({
                mouseout: function(e) {
                    if (typeof layer.closePopup == 'function') {
                        layer.closePopup();
                    } else {
                        layer.eachLayer(function(feature){
                            feature.closePopup()
                        });
                    }
                },
                mouseover: highlightFeature,
            });
            var popupContent = '<table>\
                    <tr>\
                        <td colspan="2">' + (feature.properties['id'] !== null ? autolinker.link(String(feature.properties['id']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                </table>';
            var content = removeEmptyRowsFromPopupContent(popupContent, feature);
			layer.on('popupopen', function(e) {
				addClassToPopupIfMedia(content, e.popup);
			});
			layer.bindPopup(content, { maxHeight: 400 });
        }

        function style_River_3_0() {
            return {
                pane: 'pane_River_3',
                opacity: 1,
                color: 'rgba(128,14,16,1.0)',
                dashArray: '',
                lineCap: 'butt',
                lineJoin: 'miter',
                weight: 1.0, 
                fill: true,
                fillOpacity: 1,
                fillColor: 'rgba(228,26,28,1.0)',
                interactive: false,
            }
        }
        map.createPane('pane_River_3');
        map.getPane('pane_River_3').style.zIndex = 403;
        map.getPane('pane_River_3').style['mix-blend-mode'] = 'normal';
        var layer_River_3 = new L.geoJson(json_River_3, {
            attribution: '',
            interactive: false,
            dataVar: 'json_River_3',
            layerName: 'layer_River_3',
            pane: 'pane_River_3',
            onEachFeature: pop_River_3,
            style: style_River_3_0,
        });
        bounds_group.addLayer(layer_River_3);
        map.addLayer(layer_River_3);
        function pop_TargetArea_4(feature, layer) {
            layer.on({
                mouseout: function(e) {
                    if (typeof layer.closePopup == 'function') {
                        layer.closePopup();
                    } else {
                        layer.eachLayer(function(feature){
                            feature.closePopup()
                        });
                    }
                },
                mouseover: highlightFeature,
            });
            var popupContent = '<table>\
                    <tr>\
                        <th scope="row">id</th>\
                        <td>' + (feature.properties['id'] !== null ? autolinker.link(String(feature.properties['id']).replace(/'/g, '\'').toLocaleString()) : '') + '</td>\
                    </tr>\
                </table>';
            var content = removeEmptyRowsFromPopupContent(popupContent, feature);
			layer.on('popupopen', function(e) {
				addClassToPopupIfMedia(content, e.popup);
			});
			layer.bindPopup(content, { maxHeight: 400 });
        }

        function style_TargetArea_4_0() {
            return {
                pane: 'pane_TargetArea_4',
                opacity: 1,
                color: 'rgba(35,35,35,1.0)',
                dashArray: '',
                lineCap: 'butt',
                lineJoin: 'miter',
                weight: 1.0, 
                fill: true,
                fillOpacity: 0.5,
                fillColor: 'rgba(251,255,0,1.0)',
                interactive: false,
            }
        }
        map.createPane('pane_TargetArea_4');
        map.getPane('pane_TargetArea_4').style.zIndex = 404;
        map.getPane('pane_TargetArea_4').style['mix-blend-mode'] = 'normal';
        var layer_TargetArea_4 = new L.geoJson(json_TargetArea_4, {
            attribution: '',
            interactive: false,
            dataVar: 'json_TargetArea_4',
            layerName: 'layer_TargetArea_4',
            pane: 'pane_TargetArea_4',
            onEachFeature: pop_TargetArea_4,
            style: style_TargetArea_4_0,
        });
        bounds_group.addLayer(layer_TargetArea_4);
        map.addLayer(layer_TargetArea_4);
        function escHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function getRainIntensity(precipitationAmount) {
            if (precipitationAmount >= 50) {
                return 'Extreme';
            }
            if (precipitationAmount >= 20) {
                return 'Very Heavy';
            }
            if (precipitationAmount >= 10) {
                return 'Heavy';
            }
            if (precipitationAmount >= 2.5) {
                return 'Moderate';
            }
            if (precipitationAmount > 0) {
                return 'Light';
            }
            return 'None';
        }

        function buildAreaInfoContent(payload) {
            var title = payload && payload.title ? payload.title : 'Target Area';
            var status = payload && payload.status ? payload.status : 'Weather data unavailable';
            var dateText = payload && payload.dateText ? payload.dateText : 'Not available';
            var intensityText = payload && payload.intensityText ? payload.intensityText : 'N/A';
            var probabilityText = payload && payload.probabilityText ? payload.probabilityText : 'N/A';
            var precipitationText = payload && payload.precipitationText ? payload.precipitationText : 'N/A';

            return '<section class="area-info-card" aria-label="Target area weather information">' +
                '<h2>' + escHtml(title) + '</h2>' +
                '<p class="area-info-risk">Weather Situation: ' + escHtml(status) + '</p>' +
                '<h3>Daily Forecast (Meteoblue):</h3>' +
                '<ul class="area-info-list">' +
                    '<li>Date: ' + escHtml(dateText) + '</li>' +
                    '<li>Rain Intensity: ' + escHtml(intensityText) + '</li>' +
                    '<li>Precipitation Amount: ' + escHtml(precipitationText) + '</li>' +
                    '<li>Precipitation Probability: ' + escHtml(probabilityText) + '</li>' +
                '</ul>' +
                '<p class="area-info-guide"><i class="fas fa-map-marker-alt" aria-hidden="true"></i><i class="fas fa-hand-pointer" aria-hidden="true"></i> Click marked locations on the map to view their details.</p>' +
                '<button class="area-live-button" type="button" onclick="window.location.href=\'../../ta.php?phase=FP\'">View Possible Flooded area</button>' +
            '</section>';
        }

        function getMetricFromCandidates(source, metricCandidates, index) {
            if (!source) {
                return null;
            }

            for (var i = 0; i < metricCandidates.length; i++) {
                var key = metricCandidates[i];
                var value = source[key];
                if (Array.isArray(value)) {
                    var item = value[index];
                    if (typeof item === 'number' && !isNaN(item)) {
                        return item;
                    }
                } else if (typeof value === 'number' && !isNaN(value)) {
                    return value;
                }
            }

            return null;
        }
                // API HERE with backup fallback
        function fetchTargetAreaWeather() {
            var tryNextApi = function(apiIndex) {
                if (apiIndex >= weatherApiEndpoints.length) {
                    return Promise.reject(new Error('All weather APIs failed'));
                }

                var api = weatherApiEndpoints[apiIndex];
                var endpoint = api.baseUrl +
                    '?apikey=' + encodeURIComponent(api.apikey) +
                    '&lat=' + encodeURIComponent(api.lat) +
                    '&lon=' + encodeURIComponent(api.lon) +
                    '&asl=16' +
                    '&format=json';

                return fetch(endpoint)
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Weather API ' + (apiIndex + 1) + ' failed with status ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        if (!data || !data.data_day) {
                            throw new Error('Weather API ' + (apiIndex + 1) + ' returned an invalid payload');
                        }

                        var day = data.data_day;
                        var idx = 0;
                        var forecastDate = Array.isArray(day.time) && day.time.length ? day.time[idx] : 'Not available';
                        var precipitationAmount = getMetricFromCandidates(day, [
                            'precipitation',
                            'precipitation_total',
                            'precipitation_amount',
                            'rain',
                            'rain_sum',
                            'totalprecipitation'
                        ], idx);
                        var precipitationProbability = getMetricFromCandidates(day, [
                            'precipitation_probability',
                            'precipitation_probability_mean',
                            'precipitation_probability_max',
                            'rain_probability',
                            'probability_precipitation'
                        ], idx);

                        if (precipitationAmount === null && precipitationProbability === null) {
                            throw new Error('Required precipitation fields are missing in API ' + (apiIndex + 1) + ' response');
                        }

                        var amountValue = precipitationAmount === null ? 0 : precipitationAmount;
                        var probabilityValue = precipitationProbability === null ? 0 : precipitationProbability;
                        var intensity = getRainIntensity(amountValue);

                        var weatherSituation = intensity === 'None'
                            ? 'No Significant Rain Expected'
                            : intensity + ' Rain Expected';

                        return buildAreaInfoContent({
                            title: 'Target Area',
                            status: weatherSituation,
                            dateText: forecastDate,
                            intensityText: intensity,
                            probabilityText: probabilityValue.toFixed(0) + '%',
                            precipitationText: amountValue.toFixed(2) + ' mm'
                        });
                    })
                    .catch(function(error) {
                        console.warn('Weather API ' + (apiIndex + 1) + ' error:', error.message);
                        // Try next API
                        return tryNextApi(apiIndex + 1);
                    });
            };

            return tryNextApi(0);
        }

        var areaInfoContent = buildAreaInfoContent({
            title: 'Target Area',
            status: 'Loading latest weather...'
        });
        var areaInfoMarker = L.marker(layer_TargetArea_4.getBounds().getCenter(), {
            icon: L.divIcon({
                className: 'area-info-marker',
                html: '<button class="area-info-button" type="button" aria-label="View flood information" title="Flood information"><i class="fas fa-info"></i></button>',
                iconSize: [42, 42],
                iconAnchor: [21, 21]
            }),
            keyboard: true,
            title: 'Flood information'
        }).bindPopup(areaInfoContent, { maxWidth: 282, closeButton: true }).addTo(map);
        var areaHelpControl = L.control({ position: 'topright' });
        areaHelpControl.onAdd = function() {
            var container = L.DomUtil.create('div', 'leaflet-bar area-help-control');
            container.innerHTML = '' +
                '<section class="area-help-card" aria-label="Map instructions">' +
                    '<div class="area-help-icon" aria-hidden="true"><i class="fas fa-info"></i></div>' +
                    '<div class="area-help-copy">' +
                        '<h2>What to click</h2>' +
                        '<p>Click the yellow info marker to open flood details, then click the map markers to view their descriptions.</p>' +
                    '</div>' +
                '</section>';
            L.DomEvent.disableClickPropagation(container);
            L.DomEvent.disableScrollPropagation(container);
            return container;
        };
        areaHelpControl.addTo(map);
        fetchTargetAreaWeather()
            .then(function(content) {
                areaInfoContent = content;
                areaInfoMarker.setPopupContent(areaInfoContent);
            })
            .catch(function() {
                areaInfoContent = buildAreaInfoContent({
                    title: 'Target Area',
                    status: 'Unable to load weather data'
                });
                areaInfoMarker.setPopupContent(areaInfoContent);
            });
        const url = {"Nominatim OSM": "https://nominatim.openstreetmap.org/search?format=geojson&addressdetails=1&",
        "France BAN": "https://api-adresse.data.gouv.fr/search/?"}
        var photonControl = L.control.photon({
            url: url["Nominatim OSM"],
            feedbackLabel: '',
            position: 'topleft',
            includePosition: true,
            initial: true,
            // resultsHandler: myHandler,
        }).addTo(map);
        photonControl._container.childNodes[0].style.borderRadius="10px"
        // Create a variable to store the geoJSON data
        var x = null;
        // Create a variable to store the marker
        var marker = null;
        // Add an event listener to the Photon control to create a marker from the returned geoJSON data
        var z = null;
        photonControl.on('selected', function(e) {
            console.log(photonControl.search.resultsContainer);
            if (x != null) {
                map.removeLayer(obj3.marker);
                map.removeLayer(x);
            }
            obj2.gcd = e.choice;
            x = L.geoJSON(obj2.gcd).addTo(map);
            var label = typeof obj2.gcd.properties.label === 'undefined' ? obj2.gcd.properties.display_name : obj2.gcd.properties.label;
            obj3.marker = L.marker(x.getLayers()[0].getLatLng()).bindPopup(label).addTo(map);
            map.setView(x.getLayers()[0].getLatLng(), 17);
            z = typeof e.choice.properties.label === 'undefined'? e.choice.properties.display_name : e.choice.properties.label;
            console.log(e);
            e.target.input.value = z;
        });
        var search = document.getElementsByClassName("leaflet-photon leaflet-control")[0];
        search.classList.add("leaflet-control-search")
        search.style.display = "flex";
        search.style.backgroundColor="rgba(255,255,255,0.5)" 

        // Create the new button element
        var button = document.createElement("div");
        button.id = "gcd-button-control";
        button.className = "gcd-gl-btn fa fa-search search-button";

        // Insert the button at the beginning of the search control
        search.insertBefore(button, search.firstChild);
        last = search.lastChild;
        last.style.display = "none";
        button.addEventListener("click", function (e) {
            if (last.style.display === "none") {
                last.style.display = "block";
            } else {
                last.style.display = "none";
            }
        });
        var overlaysTree = [
            {label: '<img src="legend/TargetArea_4.png" /> Target Area', layer: layer_TargetArea_4},
            {label: '<img src="legend/River_3.png" /> River', layer: layer_River_3},
            {label: '<img src="legend/TungkilBoundary_2.png" /> Tungkil Boundary', layer: layer_TungkilBoundary_2},
            {label: "Google Labels", layer: layer_GoogleLabels_1},
            {label: "Google Satellite", layer: layer_GoogleSatellite_0},]
        var lay = L.control.layers.tree(null, overlaysTree,{
            //namedToggle: true,
            //selectorBack: false,
            //closedSymbol: '&#8862; &#x1f5c0;',
            //openedSymbol: '&#8863; &#x1f5c1;',
            //collapseAll: 'Collapse all',
            //expandAll: 'Expand all',
            collapsed: true,
        });
        lay.addTo(map);
        setBounds();
        </script>        
    </body>
</html>
