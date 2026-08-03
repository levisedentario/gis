<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$id=$_POST['id'];
$name=$_POST['location_name'];
$lat=$_POST['latitude'];
$lng=$_POST['longitude'];

mysqli_query($conn,"
UPDATE locations
SET
location_name='$name',
latitude='$lat',
longitude='$lng'
WHERE id='$id'
");

header("Location: ../index.php");