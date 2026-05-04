<?php
session_start();
include("../connection.php");

// Redirect if not logged in or wrong user type
if (!isset($_SESSION['user']) || $_SESSION['usertype'] != 'p') {
    header("Location: ../login.php");
    exit();
}

$useremail = $_SESSION['user'];
$stmt = $database->prepare("SELECT * FROM patient WHERE pemail=?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$userid = $patient['pid'];
$patientName = $patient['pname'];

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// Fetch sessions
$sqlmain = "SELECT * FROM schedule 
            INNER JOIN doctor ON schedule.docid = doctor.docid 
            WHERE schedule.scheduledate >= '$today' 
            ORDER BY schedule.scheduledate ASC";

$searchQuery = "";
$searchText = "";
if ($_POST && !empty($_POST["search"])) {
    $keyword = $_POST["search"];
    $searchText = $keyword;
    $sqlmain = "SELECT * FROM schedule 
                INNER JOIN doctor ON schedule.docid = doctor.docid 
                WHERE schedule.scheduledate >= '$today' AND (
                    doctor.docname LIKE '%$keyword%' OR 
                    schedule.title LIKE '%$keyword%' OR
                    schedule.scheduledate LIKE '%$keyword%'
                ) 
                ORDER BY schedule.scheduledate ASC";
}

$result = $database->query($sqlmain);
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

<!-- Sidebar -->
<!-- Sidebar -->
<aside class="w-64 bg-white shadow-xl flex flex-col">
  <div class="p-6 border-b border-gray-100">
    <div class="flex items-center gap-3 mb-4">
      <!-- User Initials Circle -->
      <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold rounded-full">
        <?php echo strtoupper(substr($patientName, 0, 2)); ?>
      </div>
      <!-- User Info -->
      <div class="flex-1 min-w-0">
        <p class="font-bold text-gray-900 truncate"><?php echo $patientName; ?></p>
        <p class="text-sm text-gray-500 truncate"><?php echo $useremail; ?></p>
      </div>
    </div>
    <!-- Logout Button -->
    <form method="POST" action="../logout.php">
      <button type="submit" class="w-full font-bold text-red-600 border border-red-200 hover:bg-red-50 hover:text-red-700 px-4 py-2 rounded-lg flex items-center justify-center gap-2">
        Log out
      </button>
    </form>
  </div>

  <!-- Navigation Links -->
  <nav class="flex-1 p-4 space-y-2">
    <a href="index.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Home</a>
    <a href="doctors.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">All Doctors</a>
    <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">Scheduled Sessions</a>
    <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Bookings</a>
    <a href="settings.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Settings</a>
  </nav>

  <!-- Footer -->
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
    <h1 class="text-2xl font-black text-gray-900">Scheduled Sessions</h1>
    <div class="flex items-center gap-2 text-gray-600">
      <span>📅</span>
      <div>
        <p class="text-sm font-semibold text-gray-500">Today's Date</p>
        <p class="font-bold"><?php echo $today; ?></p>
      </div>
    </div>
  </header>

  <div class="p-8 space-y-6">
    <!-- Search Section -->
    <div class="bg-white shadow-lg rounded-xl p-6">
      <form method="POST" class="flex gap-2">
        <input type="text" name="search" placeholder="Search Doctor name, title or date (YYYY-MM-DD)" class="flex-1 pl-3 h-12 font-semibold rounded-lg border border-gray-200" list="sessions" value="<?php echo $searchText; ?>">
        <datalist id="sessions">
          <?php
          $docs = $database->query("SELECT DISTINCT docname FROM doctor");
          while($d = $docs->fetch_assoc()) { echo "<option value='{$d['docname']}'></option>"; }
          $titles = $database->query("SELECT DISTINCT title FROM schedule");
          while($t = $titles->fetch_assoc()) { echo "<option value='{$t['title']}'></option>"; }
          ?>
        </datalist>
        <button type="submit" class="h-12 px-6 bg-blue-600 hover:bg-blue-700 font-bold rounded-lg text-white">Search</button>
      </form>
    </div>

    <!-- Sessions Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php if ($result->num_rows == 0): ?>
        <div class="col-span-full text-center py-12">
          <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full mb-4">📅</div>
          <p class="text-gray-500 font-semibold">No sessions found.</p>
        </div>
      <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="bg-white shadow-md rounded-xl p-6 flex flex-col justify-between">
            <div>
              <h2 class="font-bold text-lg text-gray-800"><?php echo substr($row['title'],0,25); ?></h2>
              <p class="text-gray-600 mt-1"><?php echo substr($row['docname'],0,30); ?></p>
              <p class="text-gray-500 mt-2"><?php echo $row['scheduledate']; ?> | Starts: <b>@<?php echo substr($row['scheduletime'],0,5); ?></b> (24h)</p>
            </div>
            <a href="booking.php?id=<?php echo $row['scheduleid']; ?>" class="mt-4 inline-block w-full text-center bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold px-4 py-2 rounded-lg">Book Now</a>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</main>

</body>
</html>
