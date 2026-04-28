<?php
session_start();
include("../connection.php");

// Redirect if not logged in or wrong user type
if (!isset($_SESSION['user']) || $_SESSION['usertype'] != 'p') {
    header("Location: ../login.php");
    exit();
}

$useremail = $_SESSION['user'];
$userfetch = $database->query("SELECT * FROM patient WHERE pemail='$useremail'")->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];

// Handle search
$searchQuery = "";
if ($_POST && !empty($_POST['search'])) {
    $keyword = $_POST['search'];
    $searchQuery = "WHERE docemail='$keyword' OR docname='$keyword' OR docname LIKE '$keyword%' OR docname LIKE '%$keyword' OR docname LIKE '%$keyword%'";
}

$doctors = $database->query("SELECT * FROM doctor $searchQuery ORDER BY docid DESC");

// Current date
date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Doctors</title>
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
        <?php echo strtoupper(substr($username,0,2)); ?>
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-bold text-gray-900 truncate"><?php echo $username; ?></p>
        <p class="text-sm text-gray-500 truncate"><?php echo $useremail; ?></p>
      </div>
    </div>
    <form method="POST" action="../logout.php">
      <button type="submit" class="w-full font-bold text-red-600 border border-red-200 hover:bg-red-50 hover:text-red-700 px-4 py-2 rounded-lg flex items-center justify-center gap-2">Log out</button>
    </form>
  </div>

  <nav class="flex-1 p-4 space-y-2">
    <a href="index.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Home</a>
    <a href="doctors.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">All Doctors</a>
    <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Scheduled Sessions</a>
    <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">My Bookings</a>
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
    <h1 class="text-2xl font-black text-gray-900">All Doctors</h1>
    <div class="flex items-center gap-2 text-gray-600">
      <span>📅</span>
      <div>
        <p class="text-sm font-semibold text-gray-500">Today's Date</p>
        <p class="font-bold"><?php echo $today; ?></p>
      </div>
    </div>
  </header>

  <div class="p-8 space-y-6">

    <!-- Search Bar -->
    <div class="bg-white/95 backdrop-blur-sm rounded-xl p-6 shadow-lg">
      <h3 class="text-gray-900 font-black mb-3">Search Doctor</h3>
      <form method="POST" class="flex gap-2">
        <input type="text" name="search" placeholder="Doctor name or Email" class="flex-1 pl-3 h-12 font-semibold rounded-lg border border-gray-200" list="doctors">
        <datalist id="doctors">
          <?php
          $allDoctors = $database->query("SELECT docname, docemail FROM doctor");
          while($d = $allDoctors->fetch_assoc()){
              echo "<option value='{$d['docname']}'></option>";
              echo "<option value='{$d['docemail']}'></option>";
          }
          ?>
        </datalist>
        <button type="submit" class="h-12 px-6 bg-blue-600 hover:bg-blue-700 font-bold rounded-lg text-white">Search</button>
      </form>
    </div>

    <!-- Doctors Table -->
    <div class="bg-white shadow-lg rounded-xl p-6 overflow-x-auto">
      <?php if($doctors->num_rows==0): ?>
        <div class="text-center py-12">
          <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full mb-4">👨‍⚕️</div>
          <p class="text-gray-500 font-semibold mb-2">No doctors found</p>
        </div>
      <?php else: ?>
      <table class="min-w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="py-3 px-4 font-bold text-gray-700">Doctor Name</th>
            <th class="py-3 px-4 font-bold text-gray-700">Email</th>
            <th class="py-3 px-4 font-bold text-gray-700">Specialties</th>
            <th class="py-3 px-4 font-bold text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $doctors->fetch_assoc()):
            $spe = $row['specialties'];
            $spRes = $database->query("SELECT sname FROM specialties WHERE id='$spe'");
            $sp = $spRes->fetch_assoc()['sname'];
          ?>
          <tr class="border-b border-gray-100 hover:bg-gray-50">
            <td class="py-3 px-4 font-semibold"><?php echo $row['docname']; ?></td>
            <td class="py-3 px-4 font-semibold"><?php echo $row['docemail']; ?></td>
            <td class="py-3 px-4 font-semibold"><?php echo $sp; ?></td>
            <td class="py-3 px-4 flex gap-2">
              <a href="?action=view&id=<?php echo $row['docid']; ?>" class="px-4 py-2 bg-blue-100 hover:bg-blue-200 rounded-lg font-semibold text-blue-700">View</a>
              <a href="?action=session&id=<?php echo $row['docid']; ?>&name=<?php echo urlencode($row['docname']); ?>" class="px-4 py-2 bg-cyan-100 hover:bg-cyan-200 rounded-lg font-semibold text-cyan-700">Sessions</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

  </div>
</main>

<!-- Popups -->
<?php
if($_GET){
    $id=$_GET["id"];
    $action=$_GET["action"];

    if($action=='view'){
        $stmt = $database->prepare("SELECT * FROM doctor WHERE docid=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $spcil_name = $database->query("SELECT sname FROM specialties WHERE id='".$row["specialties"]."'")->fetch_assoc()['sname'];
        echo '
        <div class="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
          <div class="bg-white p-6 rounded-xl shadow-lg w-96 relative">
            <a href="doctors.php" class="absolute top-3 right-3 text-gray-500 text-xl font-bold">&times;</a>
            <h2 class="text-2xl font-bold mb-4">'.$row['docname'].'</h2>
            <p><strong>Email:</strong> '.$row['docemail'].'</p>
            <p><strong>NIC:</strong> '.$row['docnic'].'</p>
            <p><strong>Telephone:</strong> '.$row['doctel'].'</p>
            <p><strong>Specialty:</strong> '.$spcil_name.'</p>
            <div class="mt-4 text-center">
              <a href="doctors.php" class="px-4 py-2 bg-blue-100 hover:bg-blue-200 rounded-lg font-semibold">OK</a>
            </div>
          </div>
        </div>';
    }

    if($action=='session'){
        $name = $_GET["name"];
        echo '
        <div class="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
          <div class="bg-white p-6 rounded-xl shadow-lg w-96 relative">
            <a href="doctors.php" class="absolute top-3 right-3 text-gray-500 text-xl font-bold">&times;</a>
            <h2 class="text-2xl font-bold mb-4">View Sessions</h2>
            <p class="mb-4">You want to view all sessions by <strong>'.$name.'</strong>?</p>
            <form action="schedule.php" method="post" class="text-center">
              <input type="hidden" name="search" value="'.$name.'">
              <button type="submit" class="px-4 py-2 bg-cyan-100 hover:bg-cyan-200 rounded-lg font-semibold">Yes</button>
            </form>
          </div>
        </div>';
    }
}
?>
</body>
</html>
