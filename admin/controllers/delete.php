<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$id = (int)$_GET['id'];

mysqli_query($conn, "DELETE FROM locations WHERE id='$id'");

header("Location: ../index.php");
exit;