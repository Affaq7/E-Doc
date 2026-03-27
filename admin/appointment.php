<?php
session_start();
include("../connection.php");

// Admin authentication
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// Filtering logic
$whereClauses = [];
if ($_POST) {
    if (!empty($_POST['sheduledate'])) {
        $date = $_POST['sheduledate'];
        $whereClauses[] = "schedule.scheduledate='$date'";
    }
    if (!empty($_POST['docid'])) {
        $docid = $_POST['docid'];
        $whereClauses[] = "doctor.docid=$docid";
    }
}

$sqlmain = "SELECT appointment.appoid, schedule.scheduleid, schedule.title, doctor.docname, patient.pname, schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate
FROM schedule
INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid
INNER JOIN patient ON patient.pid = appointment.pid
INNER JOIN doctor ON schedule.docid = doctor.docid";

if (!empty($whereClauses)) {
    $sqlmain .= " WHERE " . implode(" AND ", $whereClauses);
}

$sqlmain .= " ORDER BY schedule.scheduledate DESC";
$result = $database->query($sqlmain);

// Get doctor list for filter dropdown
$doctorsList = $database->query("SELECT * FROM doctor ORDER BY docname ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments — Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .sidebar-width { width: 260px; }
    .card-shadow { box-shadow: 0 6px 18px rgba(13, 38, 59, 0.08); }
    .small-text { font-size: 0.9rem; color: #6b7280; }
    .overlay {
        position: fixed; top:0; left:0; right:0; bottom:0;
        background: rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center;
        z-index:50;
    }
    .popup {
        background:white; padding:2rem; border-radius:1rem; width:90%; max-width:600px; position:relative;
    }
    .close {
        position:absolute; top:1rem; right:1rem; font-size:1.5rem; font-weight:bold; text-decoration:none; color:#333;
    }
    .btn-primary { background:#0ea5e9; color:white; }
    .btn-primary-soft { background:#e0f2fe; color:#0ea5e9; }
</style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50 min-h-screen">

<div class="flex">

    <!-- Sidebar -->
    <aside class="sidebar-width bg-white border-r border-gray-100 min-h-screen">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3 mb-4">
                <img src="../img/user.png" alt="admin" class="w-12 h-12 rounded-full object-cover" />
                <div>
                    <p class="font-bold text-gray-900">Administrator</p>
                    <p class="text-sm text-gray-500">admin@edoc.com</p>
                </div>
            </div>
            <form method="POST" action="../logout.php">
                <button type="submit" class="w-full text-sm  font-bold text-red-600 border border-red-200 px-3 py-2 rounded-lg hover:bg-red-50">
                    Log out
                </button>
            </form>
        </div>
        <nav class="px-4 py-6 space-y-1">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100  font-bold text-gray-700">Dashboard</a>
            <a href="doctors.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100  font-bold text-gray-700">Doctors</a>
            <a href="schedule.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100  font-bold text-gray-700">Schedule</a>
            <a href="appointment.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-bold shadow">Appointments</a>
            <a href="patient.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100  font-bold text-gray-700">Patients</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-auto">
        <header class="bg-white sticky top-0 z-10 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Appointments</h1>
                    <p class="small-text mt-0.5">Manage patient appointments</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Today's Date</p>
                        <p class=" font-bold"><?php echo htmlspecialchars($today); ?></p>
                    </div>
                    <button class="p-2 rounded-lg bg-white border border-gray-100 shadow">
                        <img src="../img/calendar.svg" alt="calendar" class="w-6 h-6">
                    </button>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">

            <!-- Filter -->
            <form method="POST" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="small-text">Date</label>
                    <input type="date" name="sheduledate" value="<?php echo $_POST['sheduledate'] ?? ''; ?>" class="px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="small-text">Doctor</label>
                    <select name="docid" class="px-3 py-2 border rounded-lg">
                        <option value="" disabled selected hidden>Select Doctor</option>
                        <?php while($doc = $doctorsList->fetch_assoc()) {
                            $selected = ($_POST['docid'] ?? '') == $doc['docid'] ? 'selected' : '';
                            echo "<option value='{$doc['docid']}' $selected>{$doc['docname']}</option>";
                        } ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filter</button>
                </div>
            </form>

            <!-- Appointments Table -->
            <div class="bg-white rounded-xl card-shadow overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appointment #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appointment Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        if ($result->num_rows == 0) {
                            echo '<tr><td colspan="7" class="text-center py-12">
                                  <img src="../img/notfound.svg" class="mx-auto w-36 mb-4">
                                  <p class="text-gray-500  font-bold">No appointments found</p>
                                  </td></tr>';
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                $appoid = $row['appoid'];
                                $pname = $row['pname'];
                                $apponum = $row['apponum'];
                                $docname = $row['docname'];
                                $title = $row['title'];
                                $scheduledate = $row['scheduledate'];
                                $scheduletime = $row['scheduletime'];
                                $appodate = $row['appodate'];

                                echo "<tr>
                                    <td class='px-6 py-4'>$pname</td>
                                    <td class='px-6 py-4 text-center'>$apponum</td>
                                    <td class='px-6 py-4'>$docname</td>
                                    <td class='px-6 py-4'>$title</td>
                                    <td class='px-6 py-4 text-center'>$scheduledate <br>$scheduletime</td>
                                    <td class='px-6 py-4 text-center'>$appodate</td>
                                    <td class='px-6 py-4'>
                                        <a href='?action=drop&id=$appoid&name=$pname&apponum=$apponum' class='px-3 py-1 bg-red-100 text-red-700 rounded'>Cancel</a>
                                    </td>
                                </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>

<!-- Popup Handling -->
<?php
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'drop' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $name = $_GET['name'];
    $apponum = $_GET['apponum'];

    echo '
    <div class="overlay">
        <div class="popup">
            <a href="appointment.php" class="close">&times;</a>
            <h2 class="text-xl font-bold mb-4">Confirm Cancellation</h2>
            <p class="mb-4">Are you sure you want to cancel this appointment?</p>
            <p class="mb-4"><strong>Patient:</strong> '.$name.'<br><strong>Appointment #:</strong> '.$apponum.'</p>
            <div class="flex justify-center gap-4 mt-4">
                <a href="delete-appointment.php?id='.$id.'"><button class="px-4 py-2 btn-primary rounded-lg">Yes</button></a>
                <a href="appointment.php"><button class="px-4 py-2 btn-primary-soft rounded-lg">No</button></a>
            </div>
        </div>
    </div>';
}
?>
</body>
</html>
