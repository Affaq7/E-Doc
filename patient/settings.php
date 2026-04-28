<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
include("../connection.php");

$userfetch = $database->query("SELECT * FROM patient WHERE pemail='$useremail'")->fetch_assoc();
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
<title>Settings</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
.popup { animation: transitionIn-Y-bottom 0.5s; }
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

  <!-- Navigation -->
  <nav class="flex-1 p-4 space-y-2">
    <a href="index.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Home</a>
    <a href="doctors.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">All Doctors</a>
    <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Scheduled Sessions</a>
    <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Bookings</a>
    <a href="settings.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">Settings</a>
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
    <h1 class="text-2xl font-black text-gray-900">Settings</h1>
    <div class="flex items-center gap-2 text-gray-600">
      <span>📅</span>
      <div>
        <p class="text-sm font-semibold text-gray-500">Today's Date</p>
        <p class="font-bold"><?= $today ?></p>
      </div>
    </div>
  </header>

  <!-- Settings Cards -->
   <div class="p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <!-- Account Settings -->
            <a href="?action=edit&id=<?= $userid ?>&error=0" class="bg-white shadow-lg rounded-xl p-6 hover:shadow-2xl transition flex gap-4 items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <img src="../img/icons/doctors-hover.svg" class="w-6 h-6" alt="icon">
            </div>
            <div>
                <h2 class="font-bold text-lg">Account Settings</h2>
                <p class="text-gray-500 text-sm">Edit account details & change password</p>
            </div>
            </a>

            <!-- View Account -->
            <a href="?action=view&id=<?= $userid ?>" class="bg-white shadow-lg rounded-xl p-6 hover:shadow-2xl transition flex gap-4 items-center">
            <div class="w-12 h-12 bg-cyan-100 rounded-full flex items-center justify-center">
                <img src="../img/icons/view-iceblue.svg" class="w-6 h-6" alt="icon">
            </div>
            <div>
                <h2 class="font-bold text-lg">View Account</h2>
                <p class="text-gray-500 text-sm">View your personal account information</p>
            </div>
            </a>

            <!-- Delete Account -->
            <a href="?action=drop&id=<?= $userid ?>&name=<?= urlencode($username) ?>" class="bg-white shadow-lg rounded-xl p-6 hover:shadow-2xl transition flex gap-4 items-center">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <img src="../img/icons/patients-hover.svg" class="w-6 h-6" alt="icon">
            </div>
            <div>
                <h2 class="font-bold text-lg text-red-600">Delete Account</h2>
                <p class="text-gray-500 text-sm">Permanently remove your account</p>
            </div>
            </a>
        </div>
  </div>
</main>

<!-- Popups -->
<?php
if ($_GET) {
    $id = $_GET["id"];
    $action = $_GET["action"];

    if ($action == 'drop') {
        $nameget = $_GET["name"];
        echo '
        <div class="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-xl shadow-lg w-96 text-center">
                <h2 class="text-xl font-bold mb-4">Are you sure?</h2>
                <p class="mb-4">Delete your account (' . substr($nameget, 0, 40) . ')?</p>
                <div class="flex justify-center gap-4">
                    <a href="delete-account.php?id=' . $id . '" class="px-4 py-2 bg-red-500 text-white rounded-lg">Yes</a>
                    <a href="settings.php" class="px-4 py-2 bg-gray-200 rounded-lg">No</a>
                </div>
            </div>
        </div>';
    } elseif ($action == 'view') {
        $row = $database->query("SELECT * FROM patient WHERE pid='$id'")->fetch_assoc();
        echo '
        <div class="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-xl shadow-lg w-96">
                <h2 class="text-xl font-bold mb-4">Account Details</h2>
                <table class="w-full text-left text-gray-700">
                    <tr><td class="font-semibold">Name:</td><td>' . $row["pname"] . '</td></tr>
                    <tr><td class="font-semibold">Email:</td><td>' . $row["pemail"] . '</td></tr>
                    <tr><td class="font-semibold">NIC:</td><td>' . $row["pnic"] . '</td></tr>
                    <tr><td class="font-semibold">Telephone:</td><td>' . $row["ptel"] . '</td></tr>
                    <tr><td class="font-semibold">Address:</td><td>' . $row["paddress"] . '</td></tr>
                    <tr><td class="font-semibold">Date of Birth:</td><td>' . $row["pdob"] . '</td></tr>
                </table>
                <div class="text-center mt-6">
                    <a href="settings.php" class="px-4 py-2 bg-blue-500 text-white rounded-lg">OK</a>
                </div>
            </div>
        </div>';
    } elseif ($action == 'edit') {
        $row = $database->query("SELECT * FROM patient WHERE pid='$id'")->fetch_assoc();
        $error_1 = $_GET["error"];
        $errorlist = ['0' => '', '1' => 'Email already exists.', '2' => 'Password confirmation error.'];
        echo '
        <div class="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-xl shadow-lg w-96 relative">
                <a href="settings.php" class="absolute top-3 right-3 text-gray-500 text-xl font-bold">&times;</a>
                <h2 class="text-xl font-bold mb-4">Edit Account</h2>
                <form action="edit-user.php" method="POST" class="space-y-3">
                    <div class="text-red-500 font-semibold">' . ($errorlist[$error_1] ?? '') . '</div>
                    <input type="hidden" name="id00" value="' . $id . '">
                    <input type="hidden" name="oldemail" value="' . $row["pemail"] . '">
                    <input type="email" name="email" value="' . $row["pemail"] . '" placeholder="Email" class="w-full border rounded px-2 py-1" required>
                    <input type="text" name="name" value="' . $row["pname"] . '" placeholder="Name" class="w-full border rounded px-2 py-1" required>
                    <input type="text" name="nic" value="' . $row["pnic"] . '" placeholder="NIC" class="w-full border rounded px-2 py-1" required>
                    <input type="tel" name="Tele" value="' . $row["ptel"] . '" placeholder="Telephone" class="w-full border rounded px-2 py-1" required>
                    <input type="text" name="address" value="' . $row["paddress"] . '" placeholder="Address" class="w-full border rounded px-2 py-1" required>
                    <input type="password" name="password" placeholder="Password" class="w-full border rounded px-2 py-1" required>
                    <input type="password" name="cpassword" placeholder="Confirm Password" class="w-full border rounded px-2 py-1" required>
                    <div class="flex gap-4 justify-end">
                        <input type="reset" value="Reset" class="px-4 py-2 border rounded">
                        <input type="submit" value="Save" class="px-4 py-2 bg-blue-500 text-white rounded">
                    </div>
                </form>
            </div>
        </div>';
    }
}
?>
</body>
</html>
