<?php
session_start();
include("../connection.php");

if (!isset($_SESSION['user']) || $_SESSION['usertype'] != 'p') {
    header("Location: ../login.php");
    exit();
}

if ($_POST && isset($_POST['id'])) {
    $appid = intval($_POST['id']);
    $stmt = $database->prepare("DELETE FROM appointment WHERE appoid=?");
    $stmt->bind_param("i", $appid);
    $stmt->execute();
}

header("Location: appointment.php"); // stays on the same page
exit();
?>
