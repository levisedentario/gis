<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$name = $_POST['location_name'];
$lat  = $_POST['latitude'];
$lng  = $_POST['longitude'];

$stmt = $conn->prepare("
    INSERT INTO locations
    (location_name, latitude, longitude)
    VALUES (?, ?, ?)
");

$stmt->bind_param("sdd", $name, $lat, $lng);

if ($stmt->execute()) {

    header("Location: ../index.php?success=1");
    exit;

} else {

    echo "Database Error: " . $conn->error;

}

$stmt->close();
$conn->close();

?>