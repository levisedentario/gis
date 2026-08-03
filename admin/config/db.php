<?php

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "gis_mingsci"
);

if($conn->connect_error){
    die("Connection Failed");
}