<?php
session_start();
include("../connection.php");

if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}

if (isset($_POST['approve'])) {
    $req_id = $_POST['req_id'];
    $scheduleid = $_POST['scheduleid'];
    $pid = $_POST['pid'];
    $date = date('Y-m-d');

    // 1. Generate Appointment Number
    $sql2 = "SELECT * FROM appointment WHERE scheduleid=$scheduleid";
    $result2 = $database->query($sql2);
    $apponum = $result2->num_rows + 1;

    // 2. Create the Appointment
    $sql = "INSERT INTO appointment (pid, apponum, scheduleid, appodate) VALUES (?, ?, ?, ?)";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("iiis", $pid, $apponum, $scheduleid, $date);
    
    if ($stmt->execute()) {
        // 3. Update Request Status to Approved
        $update = $database->query("UPDATE booking_requests SET status='approved' WHERE id=$req_id");
        header("location: schedule.php?action=requests&msg=approved");
    } else {
        echo "Error: " . $database->error;
    }

} elseif (isset($_POST['reject'])) {
    $req_id = $_POST['req_id'];
    // Update Request Status to Rejected
    $database->query("UPDATE booking_requests SET status='rejected' WHERE id=$req_id");
    header("location: schedule.php?action=requests&msg=rejected");
}
?>