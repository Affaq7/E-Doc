<?php
session_start();
include("../connection.php");

// Check if user is logged in as doctor
if (!isset($_SESSION['user']) || $_SESSION['usertype'] != 'd') {
    header("Location: ../login.php");
    exit();
}

// Fetch doctor info
$useremail = $_SESSION['user'];
$stmt = $database->prepare("SELECT * FROM doctor WHERE docemail=?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userfetch = $stmt->get_result()->fetch_assoc();
$userid = $userfetch["docid"];
$username = $userfetch["docname"];

// Today's date
date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// Fetch counts for dashboard cards
$patientCount = $database->query("SELECT * FROM patient")->num_rows;
$doctorCount = $database->query("SELECT * FROM doctor")->num_rows;
$newBookingCount = $database->query("SELECT * FROM appointment WHERE appodate >= '$today'")->num_rows;
$todaySessionCount = $database->query("SELECT * FROM schedule WHERE scheduledate = '$today' AND docid=$userid")->num_rows;

// Fetch upcoming sessions for this doctor (next 5 sessions)
$upcomingSessions = [];
$sessionQuery = $database->query("
    SELECT schedule.scheduleid, schedule.title, schedule.scheduledate, schedule.scheduletime, schedule.nop
    FROM schedule
    WHERE schedule.docid = $userid AND schedule.scheduledate >= '$today'
    ORDER BY schedule.scheduledate ASC, schedule.scheduletime ASC
    LIMIT 5
");
while ($row = $sessionQuery->fetch_assoc()) {
    $upcomingSessions[] = [
        'title' => $row['title'],
        'dateTime' => $row['scheduledate'] . ' ' . $row['scheduletime'],
        'nop' => $row['nop']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
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
    <a href="index.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">Home</a>
    <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Appointments</a>
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
    <h1 class="text-2xl font-black text-gray-900">Home</h1>
    <div class="flex items-center gap-2 text-gray-600">
      <span>📅</span>
      <div>
        <p class="text-sm font-semibold text-gray-500">Today's Date</p>
        <p class="font-bold"><?= $today ?></p>
      </div>
    </div>
  </header>

  <div class="p-8 space-y-6">

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-cyan-400 via-blue-400 to-purple-400 shadow-xl rounded-xl p-8 relative">
      <p class="text-white/90 font-bold mb-2">Welcome!</p>
      <h2 class="text-white text-3xl font-black mb-4">Dr. <?= $username ?>.</h2>
      <p class="text-white/95 font-semibold mb-1">
        Here you can manage your sessions, view patient appointments, and track your bookings.
      </p>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white p-6 rounded-xl shadow flex justify-between items-center">
        <div>
          <p class="text-3xl font-bold"><?= $doctorCount ?></p>
          <p class="text-gray-500 font-semibold">All Doctors</p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">👨‍⚕️</div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow flex justify-between items-center">
        <div>
          <p class="text-3xl font-bold"><?= $patientCount ?></p>
          <p class="text-gray-500 font-semibold">All Patients</p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">🧑‍🤝‍🧑</div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow flex justify-between items-center">
        <div>
          <p class="text-3xl font-bold"><?= $newBookingCount ?></p>
          <p class="text-gray-500 font-semibold">New Bookings</p>
        </div>
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">📅</div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow flex justify-between items-center">
        <div>
          <p class="text-3xl font-bold"><?= $todaySessionCount ?></p>
          <p class="text-gray-500 font-semibold">Today Sessions</p>
        </div>
        <div class="w-12 h-12 bg-cyan-100 rounded-full flex items-center justify-center">⏰</div>
      </div>
    </div>

    <!-- Upcoming Sessions Table -->
    <div class="bg-white shadow-lg rounded-xl p-6 mt-6">
      <h3 class="text-xl font-black text-gray-900 mb-4">Your Upcoming Sessions</h3>
      <?php if (count($upcomingSessions) === 0): ?>
        <div class="text-center py-12">
          <p class="text-gray-500 font-semibold mb-2">No upcoming sessions</p>
          <p class="text-gray-400 text-sm font-semibold">Your scheduled sessions will appear here</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="py-3 px-4 font-bold text-gray-700">Session Title</th>
                <th class="py-3 px-4 font-bold text-gray-700">Scheduled Date & Time</th>
                <th class="py-3 px-4 font-bold text-gray-700 text-center">No. of Patients</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($upcomingSessions as $session): ?>
              <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="py-3 px-4 font-semibold"><?= $session['title'] ?></td>
                <td class="py-3 px-4 font-semibold"><?= $session['dateTime'] ?></td>
                <td class="py-3 px-4 font-semibold text-center"><?= $session['nop'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>
</body>
</html>
