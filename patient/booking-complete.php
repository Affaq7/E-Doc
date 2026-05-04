<?php
// Start session and check user
session_start();

if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];

// Include database connection
include("../connection.php");

// Fetch patient info
$sqlmain = "SELECT * FROM patient WHERE pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// Process booking
if ($_POST && isset($_POST["booknow"])) {
    $apponum = $_POST["apponum"];
    $scheduleid = $_POST["scheduleid"];
    $date = $_POST["date"];

    // Check if patient already booked this session
    $check = $database->query("SELECT * FROM appointment WHERE scheduleid=$scheduleid AND pid=$userid");
    if ($check->num_rows == 0) {
        $sql2 = "INSERT INTO appointment(pid,apponum,scheduleid,appodate) VALUES ($userid,$apponum,$scheduleid,'$date')";
        $database->query($sql2);
        header("location: appointment.php?action=booking-added&id=" . $apponum . "&titleget=none");
        exit();
    } else {
        $alreadyBooked = true;
    }
}

// Fetch session details if ID is set
$schedule = null;
$apponum = null;
$alreadyBooked = false;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sqlmain = "SELECT * FROM schedule INNER JOIN doctor ON schedule.docid = doctor.docid WHERE schedule.scheduleid=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = $result->fetch_assoc();

    if ($schedule) {
        // Check if already booked
        $check = $database->query("SELECT * FROM appointment WHERE scheduleid=$id AND pid=$userid");
        $alreadyBooked = ($check->num_rows > 0);

        // Calculate next appointment number
        $result12 = $database->query("SELECT * FROM appointment WHERE scheduleid=$id");
        $apponum = ($result12->num_rows) + 1;
    }
}
?>