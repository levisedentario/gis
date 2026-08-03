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
        .phase-toolbar {
            position: absolute;
            inset: 0;
            z-index: 1000;
            pointer-events: none;
        }
        .phase-option {
            position: absolute;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 999px;
            background: rgba(255,255,255,0.95);
            color: #0f172a;
            padding: 6px 10px;
            text-align: center;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            box-shadow: 0 3px 8px rgba(0,0,0,0.16);
            min-width: 44px;
            text-decoration: none;
            transform: translate(-50%, -50%);
            pointer-events: auto;
        }
        .phase-option:hover {
            background: #e5eefc;
        }
        .phase-option.active {
            background: #2563eb;
            color: #fff;
        }
        .phase-option.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        </style>
        <title></title>
    </head>
    <body>
        <div id="map">
            <?php $activePage = 'all'; $hiddenNavItems = ['all']; include '../includes/phase_nav.php'; ?>
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
        <script src="data/TungkilBoundary_2.js"></script>
        <script src="data/Phase3_3.js"></script>
        <script src="data/River2_4.js"></script>
        <script src="data/PH1AND5_5.js"></script>
        <script src="data/Phase2_6.js"></script>
        <script src="data/River_7.js"></script>
        <script src="data/TargetArea_8.js"></script>
        <script>
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
        function pop_Phase3_3(feature, layer) {
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

        function style_Phase3_3_0() {
            return {
                pane: 'pane_Phase3_3',
                opacity: 1,
                color: 'rgba(38,89,128,1.0)',
                dashArray: '',
                lineCap: 'butt',
                lineJoin: 'miter',
                weight: 1.0, 
                fill: true,
                fillOpacity: 0.3,
                fillColor: 'rgba(55,126,184,1.0)',
                interactive: false,
            }
        }
        map.createPane('pane_Phase3_3');
        map.getPane('pane_Phase3_3').style.zIndex = 403;
        map.getPane('pane_Phase3_3').style['mix-blend-mode'] = 'normal';
        var layer_Phase3_3 = new L.geoJson(json_Phase3_3, {
            attribution: '',
            interactive: false,
            dataVar: 'json_Phase3_3',
            layerName: 'layer_Phase3_3',
            pane: 'pane_Phase3_3',
            onEachFeature: pop_Phase3_3,
            style: style_Phase3_3_0,
        });
        bounds_group.addLayer(layer_Phase3_3);
        map.addLayer(layer_Phase3_3);
        function pop_River2_4(feature, layer) {
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

        function style_River2_4_0() {
            return {
                pane: 'pane_River2_4',
                opacity: 1,
                color: 'rgba(128,14,16,1.0)',
                dashArray: '',
                lineCap: 'butt',
                lineJoin: 'miter',
                weight: 1.0, 
                fill: true,
                fillOpacity: 0.3,
                fillColor: 'rgba(228,26,28,1.0)',
                interactive: false,
            }
        }
        map.createPane('pane_River2_4');
        map.getPane('pane_River2_4').style.zIndex = 404;
        map.getPane('pane_River2_4').style['mix-blend-mode'] = 'normal';
        var layer_River2_4 = new L.geoJson(json_River2_4, {
            attribution: '',
            interactive: false,
            dataVar: 'json_River2_4',
            layerName: 'layer_River2_4',
            pane: 'pane_River2_4',
            onEachFeature: pop_River2_4,
            style: style_River2_4_0,
        });
        bounds_group.addLayer(layer_River2_4);
        map.addLayer(layer_River2_4);
        function pop_PH1AND5_5(feature, layer) {
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

        function style_PH1AND5_5_0() {
            return {
                pane: 'pane_PH1AND5_5',
                opacity: 1,
                color: 'rgba(35,35,35,1.0)',
                dashArray: '',
                lineCap: 'butt',
                lineJoin: 'miter',
                weight: 1.0, 
                fill: true,
                fillOpacity: 0.3,
                fillColor: 'rgba(0,255,169,1.0)',
                interactive: false,
            }
        }
        map.createPane('pane_PH1AND5_5');
        map.getPane('pane_PH1AND5_5').style.zIndex = 405;
        map.getPane('pane_PH1AND5_5').style['mix-blend-mode'] = 'normal';
        var layer_PH1AND5_5 = new L.geoJson(json_PH1AND5_5, {
            attribution: '',
            interactive: false,
            dataVar: 'json_PH1AND5_5',
            layerName: 'layer_PH1AND5_5',
            pane: 'pane_PH1AND5_5',
            onEachFeature: pop_PH1AND5_5,
            style: style_PH1AND5_5_0,
        });
        bounds_group.addLayer(layer_PH1AND5_5);
        map.addLayer(layer_PH1AND5_5);
        function pop_Phase2_6(feature, layer) {
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

        function style_Phase2_6_0() {
            return {
                pane: 'pane_Phase2_6',
                opacity: 1,
                color: 'rgba(247,247,247,1.0)',
                dashArray: '',
                lineCap: 'butt',
                lineJoin: 'miter',
                weight: 1.0, 
                fill: true,
                fillOpacity: 0.3,
                fillColor: 'rgba(255,122,0,1.0)',
                interactive: false,
            }
        }
        map.createPane('pane_Phase2_6');
        map.getPane('pane_Phase2_6').style.zIndex = 406;
        map.getPane('pane_Phase2_6').style['mix-blend-mode'] = 'normal';
        var layer_Phase2_6 = new L.geoJson(json_Phase2_6, {
            attribution: '',
            interactive: false,
            dataVar: 'json_Phase2_6',
            layerName: 'layer_Phase2_6',
            pane: 'pane_Phase2_6',
            onEachFeature: pop_Phase2_6,
            style: style_Phase2_6_0,
        });
        bounds_group.addLayer(layer_Phase2_6);
        map.addLayer(layer_Phase2_6);
        function pop_River_7(feature, layer) {
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

        function style_River_7_0() {
            return {
                pane: 'pane_River_7',
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
        map.createPane('pane_River_7');
        map.getPane('pane_River_7').style.zIndex = 407;
        map.getPane('pane_River_7').style['mix-blend-mode'] = 'normal';
        var layer_River_7 = new L.geoJson(json_River_7, {
            attribution: '',
            interactive: false,
            dataVar: 'json_River_7',
            layerName: 'layer_River_7',
            pane: 'pane_River_7',
            onEachFeature: pop_River_7,
            style: style_River_7_0,
        });
        bounds_group.addLayer(layer_River_7);
        map.addLayer(layer_River_7);
        function pop_TargetArea_8(feature, layer) {
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

        function style_TargetArea_8_0() {
            return {
                pane: 'pane_TargetArea_8',
                opacity: 1,
                color: 'rgba(35,35,35,1.0)',
                dashArray: '',
                lineCap: 'butt',
                lineJoin: 'miter',
                weight: 1.0, 
                fill: true,
                fillOpacity: 0.3,
                fillColor: 'rgba(251,255,0,1.0)',
                interactive: false,
            }
        }
        map.createPane('pane_TargetArea_8');
        map.getPane('pane_TargetArea_8').style.zIndex = 408;
        map.getPane('pane_TargetArea_8').style['mix-blend-mode'] = 'normal';
        var layer_TargetArea_8 = new L.geoJson(json_TargetArea_8, {
            attribution: '',
            interactive: false,
            dataVar: 'json_TargetArea_8',
            layerName: 'layer_TargetArea_8',
            pane: 'pane_TargetArea_8',
            onEachFeature: pop_TargetArea_8,
            style: style_TargetArea_8_0,
        });
        bounds_group.addLayer(layer_TargetArea_8);
        map.addLayer(layer_TargetArea_8);
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
            {label: '<img src="legend/TargetArea_8.png" /> Target Area', layer: layer_TargetArea_8},
            {label: '<img src="legend/River_7.png" /> River', layer: layer_River_7},
            {label: '<img src="legend/Phase2_6.png" /> Phase 2', layer: layer_Phase2_6},
            {label: '<img src="legend/PH1AND5_5.png" /> PH1AND5', layer: layer_PH1AND5_5},
            {label: '<img src="legend/River2_4.png" /> River 2', layer: layer_River2_4},
            {label: '<img src="legend/Phase3_3.png" /> Phase 3', layer: layer_Phase3_3},
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
        var phaseLayers = {
            all: bounds_group,
            phase1: layer_PH1AND5_5,
            phase2: layer_Phase2_6,
            phase3: layer_Phase3_3,
            target: layer_TargetArea_8
        };
        function positionPhaseOptions() {
            document.querySelectorAll('.phase-option[data-phase]').forEach(function(phaseButton) {
                var phaseLayer = phaseLayers[phaseButton.dataset.phase];
                if (!phaseLayer || !phaseLayer.getBounds().isValid()) {
                    return;
                }
                var point = map.latLngToContainerPoint(phaseLayer.getBounds().getCenter());
                phaseButton.style.left = point.x + 'px';
                phaseButton.style.top = (point.y - (phaseButton.dataset.phase === 'target' ? 120 : 0)) + 'px';
            });
        }
        positionPhaseOptions();
        map.on('move zoom resize', positionPhaseOptions);
        resetLabels([layer_Phase2_6]);
        map.on("zoomend", function(){
            resetLabels([layer_Phase2_6]);
        });
        map.on("layeradd", function(){
            resetLabels([layer_Phase2_6]);
        });
        map.on("layerremove", function(){
            resetLabels([layer_Phase2_6]);
        });
        </script>        
    </body>
</html>
