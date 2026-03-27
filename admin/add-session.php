<?php
session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'a') {
        header("location: ../login.php");
        exit();
    }
} else {
    header("location: ../login.php");
    exit();
}

if ($_POST) {

    include("../connection.php");

    $title = $_POST["title"];
    $docid = $_POST["docid"];
    $nop = $_POST["nop"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $session_link = $_POST["session_link"]; 

    // Payment details
    $account_number = $_POST['account_number'];
    $account_holder = $_POST['account_holder'];
    $bank_name = $_POST['bank_name'];
    $channeling_fee = $_POST['channeling_fee'];

    // INSERT QUERY INCLUDING PAYMENT DETAILS
    $sql = "INSERT INTO schedule 
            (docid, title, scheduledate, scheduletime, nop, session_link, account_number, account_holder, bank_name, channeling_fee)
            VALUES 
            ($docid, '$title', '$date', '$time', $nop, '$session_link', '$account_number', '$account_holder', '$bank_name', $channeling_fee);";

    $result = $database->query($sql);

    header("location: schedule.php?action=session-added&title=$title");
    exit();
}
?>
