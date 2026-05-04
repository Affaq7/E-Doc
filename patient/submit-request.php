<?php
session_start();
include("../connection.php");

if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

if (isset($_POST['bookrequest'])) {
    
    // Get Patient ID
    $useremail = $_SESSION["user"];
    $stmt = $database->prepare("SELECT pid FROM patient WHERE pemail=?");
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $pid = $stmt->get_result()->fetch_assoc()['pid'];

    // Get Form Data
    $scheduleid = $_POST['scheduleid'];
    $docid = $_POST['docid'];
    $fee = $_POST['fee'];
    
    // We map: Account Num -> notes, Transaction ID -> payment_proof
    $account_num = $_POST['account_num'];
    $trans_id = $_POST['trans_id'];

    // Insert into booking_requests
    $sql = "INSERT INTO booking_requests (pid, docid, scheduleid, payment_proof, notes, channeling_fee, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            
    $stmt = $database->prepare($sql);
    $stmt->bind_param("iiissd", $pid, $docid, $scheduleid, $trans_id, $account_num, $fee);
    
    if ($stmt->execute()) {
        // Redirect back with success
        header("location: booking.php?id=".$scheduleid);
    } else {
        echo "Error: " . $database->error;
    }
}
?>