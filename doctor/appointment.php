<?php
session_start();
include("../connection.php");

// Check if logged in as doctor
if (!isset($_SESSION['user']) || $_SESSION['usertype'] != 'd') {
    header("Location: ../login.php");
    exit();
}

$useremail = $_SESSION['user'];
$userrow = $database->query("SELECT * FROM doctor WHERE docemail='$useremail'");
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["docid"];
$username = $userfetch["docname"];

// Today's date
date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// Fetch appointments
$sqlmain = "SELECT appointment.appoid, schedule.scheduleid, schedule.title, patient.pname, 
                   schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate
            FROM schedule
            INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid
            INNER JOIN patient ON patient.pid = appointment.pid
            WHERE schedule.docid = $userid";

if ($_POST && !empty($_POST['sheduledate'])) {
    $date = $_POST['sheduledate'];
    $sqlmain .= " AND schedule.scheduledate='$date'";
}

$sqlmain .= " ORDER BY appointment.appodate ASC";
$result = $database->query($sqlmain);
$totalAppointments = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Appointments</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
/* Optional smooth hover transition */
.card:hover { transform: translateY(-4px); transition: 0.3s; }
</style>
</head>
<body class="flex h-screen bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50">

<!-- Sidebar -->
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
    <a href="index.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Dashboard</a>
    <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">My Appointments</a>
    <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Sessions</a>
    <a href="patient.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Patients</a>
    <a href="settings.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Settings</a>
  </nav>

  <div class="p-6 border-t border-gray-100">
    <div class="flex items-center gap-2 justify-center">
      <div class="bg-gradient-to-br from-blue-500 to-cyan-500 p-2 rounded-lg text-white font-bold">♥</div>
      <div>
        <p class="font-black text-gray-900">eDoc</p>
        <p class="text-xs text-gray-500 font-semibold">E-Channeling</p>
      </div>
    </div>
  </div>
</aside>
<!-- Main Content -->
<main class="flex-1 overflow-auto">
    <header class="bg-white shadow-sm px-8 py-4 flex items-center justify-between sticky top-0 z-10">
  <h1 class="text-2xl font-black text-gray-900">Appointment Manager</h1>
  <div class="flex items-center gap-2 text-gray-600">
    <span>📅</span>
    <div>
      <p class="text-sm font-semibold text-gray-500">Today's Date</p>
      <p class="font-bold"><?= $today ?></p>
    </div>
  </div>
</header>
<div class="p-8 space-y-6">
    <!-- Filter Section -->
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6 flex items-center gap-4">
        <form method="POST" class="flex gap-2 w-full items-center">
            <label for="sheduledate" class="font-semibold text-gray-700">Filter by Date:</label>
            <input type="date" name="sheduledate" id="sheduledate" class="flex-1 pl-3 h-12 font-semibold rounded-lg border border-gray-200">
            <button type="submit" class="h-12 px-6 bg-blue-600 hover:bg-blue-700 font-bold rounded-lg text-white">Filter</button>
        </form>
    </div>

    <!-- Appointment Cards -->
    <?php if ($totalAppointments == 0): ?>
        <div class="text-center py-12">
            <img src="../img/notfound.svg" class="mx-auto mb-4 w-1/4">
            <p class="text-gray-500 font-semibold mb-4">No appointments found.</p>
            <a href="appointment.php" class="inline-block">
                <button class="px-6 py-2 bg-blue-100 hover:bg-blue-200 font-semibold rounded-lg">Show All Appointments</button>
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="bg-white shadow-md rounded-xl p-6 flex flex-col justify-between card">
                    <div>
                        <h2 class="font-bold text-lg text-gray-800"><?= substr($row['pname'],0,25); ?></h2>
                        <p class="text-gray-600 mt-1">Appointment #: <?= $row['apponum']; ?></p>
                        <p class="text-gray-500 mt-1">Session: <?= substr($row['title'],0,25); ?></p>
                        <p class="text-gray-500 mt-1">Scheduled: <?= $row['scheduledate']; ?> @<?= substr($row['scheduletime'],0,5); ?></p>
                        <p class="text-gray-500 mt-1">Booking Date: <?= $row['appodate']; ?></p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="?action=drop&id=<?= $row['appoid']; ?>&name=<?= $row['pname']; ?>&session=<?= $row['title']; ?>&apponum=<?= $row['apponum']; ?>" class="w-full">
                            <button class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-4 py-2 rounded-lg">Cancel</button>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <!-- Modals -->
    <?php
    if($_GET){
        $action=$_GET['action']??'';
        $id=$_GET['id']??'';
        $name=$_GET['name']??'';
        $session=$_GET['session']??'';
        $apponum=$_GET['apponum']??'';

        if($action=='drop'){
            echo '
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl shadow-lg p-6 w-96 text-center">
                    <h2 class="text-xl font-bold mb-4">Are you sure?</h2>
                    <p class="mb-4">Patient: <b>'.substr($name,0,25).'</b></p>
                    <p class="mb-4">Appointment #: <b>'.$apponum.'</b></p>
                    <div class="flex justify-center gap-4">
                        <a href="delete-appointment.php?id='.$id.'" class="w-1/2">
                            <button class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg">Yes</button>
                        </a>
                        <a href="appointment.php" class="w-1/2">
                            <button class="w-full bg-gray-200 hover:bg-gray-300 font-semibold px-4 py-2 rounded-lg">No</button>
                        </a>
                    </div>
                </div>
            </div>
            ';
        }
    }
    ?>
</div>
</main>
</body>
</html>
