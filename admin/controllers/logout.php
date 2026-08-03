<?php

session_start();

// Remove all session data
$_SESSION = [];

// Destroy the session
session_destroy();

// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: ../login.php");
exit();