<?php
// Database connection
$host = "localhost";
$user = "root";   // change if you have another username
$pass = "";       // set your DB password
$db   = "ministryproject";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>