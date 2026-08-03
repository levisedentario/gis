<?php
header('Content-Type: application/javascript; charset=utf-8');

$apiKey = getenv('GOOGLE_MAPS_API_KEY');
if ($apiKey === false || trim($apiKey) === '') {
    http_response_code(500);
    echo 'console.error("Google Maps API key is not configured.");';
    exit;
}

$callback = isset($_GET['callback']) ? preg_replace('/[^A-Za-z0-9_]/', '', $_GET['callback']) : '';

$params = ['key' => $apiKey];
if ($callback !== '') {
    $params['callback'] = $callback;
}

$scriptUrl = 'https://maps.googleapis.com/maps/api/js?' . http_build_query($params);
echo "(function(){var s=document.createElement('script');s.src=" . json_encode($scriptUrl) . ";s.async=true;s.defer=true;document.head.appendChild(s);})();";
exit;
