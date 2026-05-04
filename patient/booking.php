<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
include("../connection.php");

$stmt = $database->prepare("SELECT * FROM patient WHERE pemail=?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$userfetch = $result->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scheduled Sessions</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50">

<aside class="w-64 bg-white shadow-xl flex flex-col">
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold rounded-full">
                <?= strtoupper(substr($username, 0, 2)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 truncate"><?= $username ?></p>
                <p class="text-sm text-gray-500 truncate"><?= $useremail ?></p>
            </div>
        </div>
        <form method="POST" action="../logout.php">
            <button type="submit" class="w-full font-bold text-red-600 border border-red-200 hover:bg-red-50 hover:text-red-700 px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                Log out
            </button>
        </form>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <a href="index.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Home</a>
        <a href="doctors.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">All Doctors</a>
        <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">Scheduled Sessions</a>
        <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Bookings</a>
        <a href="settings.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Settings</a>
    </nav>
</aside>

<main class="flex-1 overflow-auto p-8">
    <header class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Scheduled Sessions</h1>
        <div class="text-right text-gray-600">
            <p class="text-sm font-semibold">Today's Date</p>
            <p class="font-bold"><?= $today ?></p>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6">
        <?php
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            // Note: Included docid in selection
            $sqlmain = "SELECT schedule.*, doctor.docname, doctor.docemail, doctor.docid FROM schedule INNER JOIN doctor ON schedule.docid=doctor.docid WHERE schedule.scheduleid=? ORDER BY schedule.scheduledate DESC";
            $stmt = $database->prepare($sqlmain);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $scheduleid = $row["scheduleid"];
            $docid = $row["docid"]; 
            $title = $row["title"];
            $docname = $row["docname"];
            $docemail = $row["docemail"];
            $scheduledate = $row["scheduledate"];
            $scheduletime = $row["scheduletime"];
            $channeling_fee = $row["channeling_fee"];
            
            // Payment Info to display
            $admin_account = $row["account_number"];
            $admin_bank = $row["bank_name"];

            // 1. Check if ALREADY BOOKED (Approved)
            $check = $database->query("SELECT * FROM appointment WHERE scheduleid=$id AND pid=$userid");
            $alreadyBooked = ($check->num_rows > 0);

            // 2. Check if REQUEST PENDING
            $checkReq = $database->query("SELECT * FROM booking_requests WHERE scheduleid=$id AND pid=$userid AND status='pending'");
            $isPending = ($checkReq->num_rows > 0);
        ?>
        
        <form action="submit-request.php" method="post" class="bg-white shadow-md rounded-xl p-6 flex flex-col lg:flex-row gap-6 items-start">
            <input type="hidden" name="scheduleid" value="<?= $scheduleid ?>">
            <input type="hidden" name="docid" value="<?= $docid ?>">
            <input type="hidden" name="fee" value="<?= $channeling_fee ?>">

            <div class="flex-1 bg-gray-50 p-6 rounded-lg shadow-inner">
                <h2 class="text-2xl font-bold mb-4">Session Details</h2>
                <p class="text-gray-700 mb-2"><b>Doctor:</b> <?= $docname ?></p>
                <p class="text-gray-700 mb-2"><b>Title:</b> <?= $title ?></p>
                <p class="text-gray-700 mb-2"><b>Date:</b> <?= $scheduledate ?> @ <?= substr($scheduletime,0,5) ?></p>
                
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <h3 class="font-bold text-lg text-blue-600">Make Payment To:</h3>
                    <p class="text-gray-600">Bank: <?= $admin_bank ?></p>
                    <p class="text-gray-600">Account: <?= $admin_account ?></p>
                    <p class="text-xl font-bold mt-2">Fee: PKR <?= number_format($channeling_fee, 2) ?></p>
                </div>
            </div>

            <div class="w-full lg:w-80 flex flex-col gap-4">
                
                <?php if ($alreadyBooked): ?>
                    <div class="w-full bg-green-100 text-green-700 font-bold py-4 rounded-lg text-center border border-green-200">
                        ✓ Already Booked
                    </div>
                <?php elseif ($isPending): ?>
                    <div class="w-full bg-gray-200 text-gray-500 font-bold py-4 rounded-lg text-center border border-gray-300 cursor-not-allowed">
                        ⌛ Waiting for Approval
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Your Account Number</label>
                        <input type="text" name="account_num" required class="w-full p-2 border rounded mb-3" placeholder="Enter sending account #">
                        
                        <label class="block text-sm font-bold text-gray-700 mb-1">Transaction ID</label>
                        <input type="text" name="trans_id" required class="w-full p-2 border rounded" placeholder="Enter Trx ID">
                    </div>

                    <button type="submit" name="bookrequest" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition shadow-lg">
                        Submit Booking Request
                    </button>
                <?php endif; ?>

            </div>

        </form>
        <?php } ?>
    </div>
</main>
</body>
</html>