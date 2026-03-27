<?php
session_start();
include("../connection.php");

// Redirect if not logged in or wrong user type
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

// Default SQL for patients
$sqlmain = "SELECT DISTINCT patient.* FROM appointment 
            INNER JOIN patient ON patient.pid = appointment.pid 
            INNER JOIN schedule ON schedule.scheduleid = appointment.scheduleid 
            WHERE schedule.docid=$userid";

$selecttype = "My";
$current = "My patients Only";

if ($_POST) {
    if (isset($_POST["search"])) {
        $keyword = $_POST["search12"];
        $sqlmain = "SELECT * FROM patient 
                    WHERE pemail='$keyword' OR pname='$keyword' OR pname LIKE '$keyword%' OR pname LIKE '%$keyword' OR pname LIKE '%$keyword%'";
        $selecttype = "Search Results";
    }

    if (isset($_POST["filter"])) {
        if ($_POST["showonly"] == 'all') {
            $sqlmain = "SELECT * FROM patient";
            $selecttype = "All";
            $current = "All patients";
        } else {
            $sqlmain = "SELECT patient.* FROM appointment 
                        INNER JOIN patient ON patient.pid = appointment.pid 
                        INNER JOIN schedule ON schedule.scheduleid = appointment.scheduleid 
                        WHERE schedule.docid=$userid";
            $selecttype = "My";
            $current = "My patients Only";
        }
    }
}

$result = $database->query($sqlmain);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Patients</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50">

<!-- Sidebar -->
<aside class="w-64 bg-white shadow-xl flex flex-col">
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold rounded-full">
                <?= strtoupper(substr($username, 0, 2)); ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 truncate"><?= $username; ?></p>
                <p class="text-sm text-gray-500 truncate"><?= $useremail; ?></p>
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
        <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Appointments</a>
        <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Sessions</a>
        <a href="patient.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">My Patients</a>
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
        <h1 class="text-2xl font-black text-gray-900">My Patients</h1>
        <div class="flex items-center gap-2 text-gray-600">
            <span>📅</span>
            <div>
                <p class="text-sm font-semibold text-gray-500">Today's Date</p>
                <p class="font-bold"><?= $today; ?></p>
            </div>
        </div>
    </header>

    <div class="p-8 space-y-6">

        <!-- Search -->
        <div class="bg-white shadow-lg rounded-xl p-6 flex items-center gap-4">
            <form method="POST" class="flex gap-2 w-full items-center">
                <input type="search" name="search12" class="flex-1 pl-3 h-12 font-semibold rounded-lg border border-gray-200" placeholder="Search Patient name or Email" list="patient">
                <datalist id="patient">
                    <?php
                    $list11 = $database->query($sqlmain);
                    for ($y = 0; $y < $list11->num_rows; $y++) {
                        $row00 = $list11->fetch_assoc();
                        echo "<option value='{$row00['pname']}' />";
                        echo "<option value='{$row00['pemail']}' />";
                    }
                    ?>
                </datalist>
                <button type="submit" name="search" class="h-12 px-6 bg-blue-600 hover:bg-blue-700 font-bold rounded-lg text-white">Search</button>
            </form>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow-lg rounded-xl p-6 flex items-center gap-4">
            <form method="POST" class="flex gap-2 w-full items-center">
                <label for="showonly" class="font-semibold text-gray-700">Show Details About:</label>
                <select name="showonly" id="showonly" class="flex-1 h-12 rounded-lg border border-gray-200 px-3">
                    <option value="" disabled selected hidden><?= $current ?></option>
                    <option value="my">My Patients Only</option>
                    <option value="all">All Patients</option>
                </select>
                <button type="submit" name="filter" class="h-12 px-6 bg-blue-600 hover:bg-blue-700 font-bold rounded-lg text-white">Filter</button>
            </form>
        </div>

        <!-- Patients Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($result->num_rows == 0): ?>
                <div class="col-span-full text-center py-12">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full mb-4">🙁</div>
                    <p class="text-gray-500 font-semibold">No patients found.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white shadow-md rounded-xl p-6 flex flex-col justify-between hover:shadow-xl transition">
                        <div>
                            <h2 class="font-bold text-lg text-gray-800"><?= substr($row['pname'], 0, 25); ?></h2>
                            <p class="text-gray-600 mt-1">NIC: <?= $row['pnic']; ?></p>
                            <p class="text-gray-500 mt-1">Email: <?= $row['pemail']; ?></p>
                            <p class="text-gray-500 mt-1">Tel: <?= $row['ptel']; ?></p>
                            <p class="text-gray-500 mt-1">DOB: <?= $row['pdob']; ?></p>
                        </div>
                        <div class="mt-4 flex justify-center">
                            <a href="?action=view&id=<?= $row['pid']; ?>" class="w-full bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold px-4 py-2 rounded-lg text-center transition">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
// Popup for viewing patient details
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $patientData = $database->query("SELECT * FROM patient WHERE pid='$id'")->fetch_assoc();
    echo '
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-11/12 max-w-2xl relative">
            <a href="patient.php" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</a>
            <h2 class="text-2xl font-bold mb-4">Patient Details</h2>
            <div class="space-y-2">
                <p><strong>Patient ID:</strong> P-'.$patientData['pid'].'</p>
                <p><strong>Name:</strong> '.$patientData['pname'].'</p>
                <p><strong>Email:</strong> '.$patientData['pemail'].'</p>
                <p><strong>NIC:</strong> '.$patientData['pnic'].'</p>
                <p><strong>Telephone:</strong> '.$patientData['ptel'].'</p>
                <p><strong>Address:</strong> '.$patientData['paddress'].'</p>
                <p><strong>Date of Birth:</strong> '.$patientData['pdob'].'</p>
            </div>
            <div class="mt-6 flex justify-center">
                <a href="patient.php" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg">OK</a>
            </div>
        </div>
    </div>
    ';
}
?>

</body>
</html>
