<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
	header("Location: ../index.php?status=error&message=Invalid+location+id");
	exit;
}

$stmt = $conn->prepare("DELETE FROM locations WHERE id = ?");
$stmt->bind_param("i", $id);
$isDeleted = $stmt->execute();
$stmt->close();

$conn->close();

if ($isDeleted) {
	header("Location: ../index.php?status=success&message=Location+deleted+successfully");
	exit;
}

header("Location: ../index.php?status=error&message=Failed+to+delete+location");
exit;