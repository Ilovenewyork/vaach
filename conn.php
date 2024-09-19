<?php
$servername = "localhost";
$server_username = "root";
$server_password = "";
$dbname = "langbase_db";

global $conn;
$conn = new mysqli($servername, $server_username, $server_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
