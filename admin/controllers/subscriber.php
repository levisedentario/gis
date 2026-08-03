<?php

require_once "../config/db.php";

$phase = $_POST['phase'];
$email = trim($_POST['email']);

$stmt = $conn->prepare("
INSERT INTO subscribers
(phase,email)
VALUES(?,?)
");

$stmt->bind_param("ss",$phase,$email);

if($stmt->execute()){

    echo "<script>
    alert('Subscription successful!');
    window.location='../../index.php';
    </script>";

}else{

    echo "<script>
    alert('Failed to subscribe.');
    window.location='../subscribe.php';
    </script>";

}
?>