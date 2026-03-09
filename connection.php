<?php
$servername = "localhost";
$username = "root";  
$password = ""; 
$dbname = "edoc"; 

$database = new mysqli($servername, $username, $password, $dbname);

if ($database->connect_error) {
    die("Connection Failed : " . $database->connect_error);
}
?>
