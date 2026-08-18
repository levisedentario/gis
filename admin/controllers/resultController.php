<?php
require_once __DIR__ . '/../config/db.php';

function normalizeApiRequestKey(string $apiName, ?float $lat, ?float $lng, ?float $elevation): string
{
    $apiName = strtolower(trim($apiName));
    $latValue = is_numeric($lat) ? (float) $lat : 0.0;
    $lngValue = is_numeric($lng) ? (float) $lng : 0.0;
    $elevationValue = is_numeric($elevation) ? (float) $elevation : 0.0;

    return $apiName . '|' . number_format($latValue, 8, '.', '') . '|' . number_format($lngValue, 8, '.', '') . '|' . number_format($elevationValue, 2, '.', '');
}

function getCachedApiResult(mysqli $conn, string $apiName, ?float $lat, ?float $lng, ?float $elevation): ?array
{
    $requestKey = normalizeApiRequestKey($apiName, $lat, $lng, $elevation);

    $stmt = $conn->prepare(
        "SELECT response_payload FROM api_result
         WHERE api_name = ?
           AND request_key = ?
           AND expires_at > NOW()
         ORDER BY updated_at DESC
         LIMIT 1"
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('ss', $apiName, $requestKey);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        $stmt->close();
        return null;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    $payload = $row['response_payload'] ?? null;
    if (!is_string($payload)) {
        return null;
    }

    $decoded = json_decode($payload, true);
    return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
}

function saveApiResult(mysqli $conn, string $apiName, ?float $lat, ?float $lng, ?float $elevation, int $responseCode, array $payload): bool
{
    $requestKey = normalizeApiRequestKey($apiName, $lat, $lng, $elevation);
    $latitude = is_numeric($lat) ? (float) $lat : null;
    $longitude = is_numeric($lng) ? (float) $lng : null;

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        $json = json_encode(['error' => 'Invalid payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $stmt = $conn->prepare(
        "INSERT INTO api_result (
            api_name,
            request_key,
            latitude,
            longitude,
            response_code,
            response_payload,
            expires_at
        ) VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
        ON DUPLICATE KEY UPDATE
            latitude = VALUES(latitude),
            longitude = VALUES(longitude),
            response_code = VALUES(response_code),
            response_payload = VALUES(response_payload),
            expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE),
            updated_at = CURRENT_TIMESTAMP()"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ssddis', $apiName, $requestKey, $latitude, $longitude, $responseCode, $json);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function saveWeatherApiResult(mysqli $conn, ?float $lat, ?float $lng, ?float $elevation, int $responseCode, array $payload): bool
{
    return saveApiResult($conn, 'meteoblue', $lat, $lng, $elevation, $responseCode, $payload);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
        exit;
    }

    $apiName = isset($data['api_name']) ? trim((string) $data['api_name']) : 'meteoblue';
    $lat = isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null;
    $lng = isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null;
    $elevation = isset($data['elevation']) && $data['elevation'] !== '' ? (float) $data['elevation'] : null;
    $responseCode = isset($data['response_code']) ? (int) $data['response_code'] : 200;
    $payload = is_array($data['payload'] ?? null) ? $data['payload'] : $data;

    $saved = saveApiResult($conn, $apiName, $lat, $lng, $elevation, $responseCode, $payload);

    echo json_encode([
        'success' => $saved,
        'message' => $saved ? 'Weather result saved' : 'Failed to save weather result'
    ]);
    exit;
}

/*
Example usage:

$weatherPayload = [
    'dateText' => '2026-08-15',
    'intensityText' => 'Light',
    'precipitationText' => '2.50 mm',
    'probabilityText' => '65%'
];

saveWeatherApiResult($conn, 10.242412, 123.808390, 16.0, 200, $weatherPayload);
*/
