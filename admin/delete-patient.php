<?php
session_start();
include("../connection.php");

if(isset($_GET["id"])){
    $id = $_GET["id"];

    // 1. Get the email first (to delete login credentials)
    $sql_get_email = "SELECT pemail FROM patient WHERE pid=$id";
    $result = $database->query($sql_get_email);
    
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $email = $row['pemail'];

        // 2. Delete from webuser (Login Access)
        $database->query("DELETE FROM webuser WHERE email='$email'");

        // 3. Delete from patient (Profile)
        $database->query("DELETE FROM patient WHERE pid=$id");
        
        // Optional: Delete appointments associated with this patient to prevent database errors
        $database->query("DELETE FROM appointment WHERE pid=$id");
        $database->query("DELETE FROM booking_requests WHERE pid=$id");
    }
}

header("location: patient.php");
exit();
?>