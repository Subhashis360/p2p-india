<?php
error_reporting(0);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smp2p";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed");
}else{
    // echo "Connected Successfully";
}



?>