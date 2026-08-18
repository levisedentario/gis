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
        .elevation-legend {
            position: absolute;
            right: 12px;
            top: 12px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
            padding: 8px 10px;
            font: 12px/1.3 Arial, sans-serif;
            color: #222;
        }
        .elevation-legend .legend-title {
            font-weight: 700;
            margin-bottom: 6px;
            text-align: center;
        }
        .elevation-legend .legend-bar {
            height: 10px;
            width: 150px;
            border-radius: 999px;
            background: linear-gradient(90deg, #7b1fa2 0%, #4a5bdc 16.6%, #2d9cdb 33.3%, #2ecf7a 50%, #8bc34a 66.6%, #fdd835 83.3%, #ff9800 91.6%, #f44336 100%);
        }
        .elevation-legend .legend-labels {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-top: 4px;
            color: #444;
        }
        .map-back-btn {
            position: absolute;
            left: 50%;
            top: 12px;
            transform: translateX(-50%);
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
            padding: 8px 12px;
            font: 12px/1.3 Arial, sans-serif;
            color: #222;
            text-decoration: none;
            cursor: pointer;
        }
        .map-back-btn:hover {
            background: rgba(240, 240, 240, 0.98);
        }
        .side-image {
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 999;
            width: 280px;
            max-width: 28vw;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            pointer-events: none;
        }
        </style>
        <title></title>
    </head>
    <body>
        <a href="../../mapping.php" class="map-back-btn">← Back</a>
        <img src="images/image.png" alt="Side image" class="side-image">
        <div id="map">
        </div>
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
        <script src="data/ta_2.js"></script>
        <script>
        var highlightLayer;
        function highlightFeature(e) {
            highlightLayer = e.target;
            highlightLayer.openPopup();
        }
        var map = L.map('map', {
            zoomControl:false, maxZoom:28, minZoom:11
        }).fitBounds([[10.240598979794472,123.80761931630063],[10.244877355244297,123.81620334232932]]);
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
        var elevationStops = [
            {value: 0, color: '#7b1fa2'},
            {value: 0.16, color: '#4a5bdc'},
            {value: 0.33, color: '#2d9cdb'},
            {value: 0.5, color: '#2ecf7a'},
            {value: 0.66, color: '#8bc34a'},
            {value: 0.83, color: '#fdd835'},
            {value: 0.92, color: '#ff9800'},
            {value: 1, color: '#f44336'}
        ];
        function hexToRgb(hex) {
            var clean = hex.replace('#', '');
            if (clean.length === 3) {
                clean = clean.split('').map(function(ch) { return ch + ch; }).join('');
            }
            var value = parseInt(clean, 16);
            return {
                r: (value >> 16) & 255,
                g: (value >> 8) & 255,
                b: value & 255
            };
        }
        function rgbToHex(r, g, b) {
            return '#' + [r, g, b].map(function(component) {
                var hex = component.toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }).join('');
        }
        function getElevationColor(value) {
            var normalized = Math.max(0, Math.min(1, value));
            if (normalized <= 0) return elevationStops[0].color;
            if (normalized >= 1) return elevationStops[elevationStops.length - 1].color;
            for (var i = 0; i < elevationStops.length - 1; i++) {
                var start = elevationStops[i];
                var end = elevationStops[i + 1];
                if (normalized <= end.value) {
                    var range = end.value - start.value;
                    var ratio = range === 0 ? 0 : (normalized - start.value) / range;
                    var startRgb = hexToRgb(start.color);
                    var endRgb = hexToRgb(end.color);
                    var r = Math.round(startRgb.r + (endRgb.r - startRgb.r) * ratio);
                    var g = Math.round(startRgb.g + (endRgb.g - startRgb.g) * ratio);
                    var b = Math.round(startRgb.b + (endRgb.b - startRgb.b) * ratio);
                    return rgbToHex(r, g, b);
                }
            }
            return elevationStops[elevationStops.length - 1].color;
        }
        function getElevationScale() {
            var values = [];
            var features = typeof json_ta_2 !== 'undefined' && json_ta_2.features ? json_ta_2.features : [];
            var keys = ['elevation', 'elev', 'value', 'height', 'altitude', 'z'];
            features.forEach(function(feature) {
                if (!feature || !feature.properties) return;
                keys.forEach(function(key) {
                    var candidate = feature.properties[key];
                    if (candidate !== null && candidate !== undefined && candidate !== '' && !isNaN(candidate)) {
                        values.push(Number(candidate));
                    }
                });
                if (feature.properties.id !== null && feature.properties.id !== undefined && feature.properties.id !== '' && !isNaN(feature.properties.id)) {
                    values.push(Number(feature.properties.id));
                }
            });
            if (values.length === 0) return {min: 0, max: 1};
            var min = Math.min.apply(null, values);
            var max = Math.max.apply(null, values);
            return {min: min, max: max === min ? min + 1 : max};
        }
        function getElevationValue(feature) {
            var scale = getElevationScale();
            if (feature && feature.properties) {
                var props = feature.properties;
                var keys = ['elevation', 'elev', 'value', 'height', 'altitude', 'z'];
                for (var i = 0; i < keys.length; i++) {
                    var candidate = props[keys[i]];
                    if (candidate !== null && candidate !== undefined && candidate !== '' && !isNaN(candidate)) {
                        return (Number(candidate) - scale.min) / (scale.max - scale.min || 1);
                    }
                }
                if (props.id !== null && props.id !== undefined && props.id !== '' && !isNaN(props.id)) {
                    return (Number(props.id) - scale.min) / (scale.max - scale.min || 1);
                }
            }
            return 0.5;
        }
        var zoomControl = L.control.zoom({
            position: 'topleft'
        }).addTo(map);
        var elevationLegend = L.control({position: 'topright'});
        elevationLegend.onAdd = function() {
            var div = L.DomUtil.create('div', 'elevation-legend');
            div.innerHTML = '<div class="legend-title">Low → High Elevation</div><div class="legend-bar"></div><div class="legend-labels"><span>Low</span><span>High</span></div>';
            return div;
        };
        elevationLegend.addTo(map);
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
            map.setMaxBounds(map.getBounds());
            map.setMinZoom(map.getZoom());
        }
        map.createPane('pane_GoogleHybrid_0');
        map.getPane('pane_GoogleHybrid_0').style.zIndex = 400;
        var layer_GoogleHybrid_0 = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            pane: 'pane_GoogleHybrid_0',
            opacity: 1.0,
            attribution: '<a href="https://www.google.at/permissions/geoguidelines/attr-guide.html">Map data ©2015 Google</a>',
            minZoom: 11,
            maxZoom: 28,
            minNativeZoom: 0,
            maxNativeZoom: 20
        });
        layer_GoogleHybrid_0;
        map.addLayer(layer_GoogleHybrid_0);
        map.createPane('pane_Clippedmask_1');
        map.getPane('pane_Clippedmask_1').style.zIndex = 401;
        var img_Clippedmask_1 = 'data/Clippedmask_1.png';
        var img_bounds_Clippedmask_1 = [[10.236944444446046,123.80638888889693],[10.250277777779381,123.81638888889694]];
        var layer_Clippedmask_1 = new L.imageOverlay(img_Clippedmask_1,
                                              img_bounds_Clippedmask_1,
                                              {pane: 'pane_Clippedmask_1'});
        bounds_group.addLayer(layer_Clippedmask_1);
        map.addLayer(layer_Clippedmask_1);
        function pop_ta_2(feature, layer) {
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

        function style_ta_2_0(feature) {
            var elevationValue = getElevationValue(feature);
            var normalized = Math.max(0, Math.min(1, elevationValue));
            var fillColor = getElevationColor(normalized);
            return {
                pane: 'pane_ta_2',
                opacity: 1,
                color: '#ff0000',
                dashArray: '',
                lineCap: 'square',
                lineJoin: 'bevel',
                weight: 1.8,
                fillColor: 'none',
                fillOpacity: 0,
                interactive: true,
            }
        }
        map.createPane('pane_ta_2');
        map.getPane('pane_ta_2').style.zIndex = 402;
        map.getPane('pane_ta_2').style['mix-blend-mode'] = 'normal';
        var layer_ta_2 = new L.geoJson(json_ta_2, {
            attribution: '',
            interactive: true,
            dataVar: 'json_ta_2',
            layerName: 'layer_ta_2',
            pane: 'pane_ta_2',
            onEachFeature: pop_ta_2,
            style: style_ta_2_0,
        });
        bounds_group.addLayer(layer_ta_2);
        map.addLayer(layer_ta_2);
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
        setBounds();
        L.ImageOverlay.include({
            getBounds: function () {
                return this._bounds;
            }
        });
        </script>        
    </body>
</html>
