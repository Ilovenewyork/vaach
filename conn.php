<?php
$servername = "127.0.0.1";
$server_username = "u314458897_vaachdbusr1";
$server_password = "@1|>NTT|4t";
$dbname = "u314458897_vaach_db";
global $conn;
$conn = new mysqli($servername, $server_username, $server_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
