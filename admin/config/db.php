<?php

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "gis_mingsci"
);

if ($conn->connect_error) {
    die("Connection Failed");
}

$conn->set_charset('utf8mb4');

function ensureLocationsIdAutoIncrement(mysqli $conn): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $checked = true;

    $columnResult = $conn->query("SHOW COLUMNS FROM locations LIKE 'id'");

    if (!$columnResult) {
        return;
    }

    $column = $columnResult->fetch_assoc();

    if (!$column) {
        return;
    }

    if (stripos($column['Extra'] ?? '', 'auto_increment') !== false) {
        return;
    }

    $primaryResult = $conn->query("SHOW INDEX FROM locations WHERE Key_name = 'PRIMARY'");
    $hasPrimaryKey = $primaryResult && $primaryResult->num_rows > 0;

    if (!$hasPrimaryKey) {
        $conn->query("SET @rownum := 0");
        $conn->query("UPDATE locations SET id = (@rownum := @rownum + 1) ORDER BY created_at, latitude, longitude, location_name");
        $conn->query("ALTER TABLE locations ADD PRIMARY KEY (id)");
    }

    $conn->query("ALTER TABLE locations MODIFY id INT(11) NOT NULL AUTO_INCREMENT");
}

ensureLocationsIdAutoIncrement($conn);


 