<?php

$conn = mysqli_connect("localhost", "root", '', "iot");

if (mysqli_connect_errno()) {
    die('Unable to connect to database ' . mysqli_connect_error());
}

$temperature = $_GET['temperature'];
$humidity = $_GET['humidity'];

$qry = $conn->prepare("INSERT INTO dht_sensor (temperature, humidity) VALUES ('" . $temperature . "','" . $humidity . "')");

If ($qry->execute()) {
    echo "Operation Successful";
    } else {
    echo "Operation Failed";
    }

