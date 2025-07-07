<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'phone_shop';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
