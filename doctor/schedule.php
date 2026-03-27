<?php
session_start();
include("../connection.php");

if(!isset($_SESSION["user"]) || $_SESSION['usertype']!='d'){
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
$userrow = $database->query("SELECT * FROM doctor WHERE docemail='$useremail'");
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["docid"];
$username = $userfetch["docname"];

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// Fetch sessions
$sqlmain = "SELECT schedule.scheduleid, schedule.title, schedule.scheduledate, schedule.scheduletime, schedule.nop
            FROM schedule
            WHERE schedule.docid=$userid";

if ($_POST && !empty($_POST["sheduledate"])) {
    $sheduledate = $_POST["sheduledate"];
    $sqlmain .= " AND schedule.scheduledate='$sheduledate'";
}

$sqlmain .= " ORDER BY schedule.scheduledate ASC";
$result = $database->query($sqlmain);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Sessions</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50">

<!-- Sidebar (Same as before) -->
<aside class="w-64 bg-white shadow-xl flex flex-col">
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold rounded-full">
                <?= strtoupper(substr($username,0,2)); ?>
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
        <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">My Sessions</a>
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
        <h1 class="text-2xl font-black text-gray-900">My Sessions</h1>
        <div class="flex items-center gap-2 text-gray-600">
            <span>📅</span>
            <div>
                <p class="text-sm font-semibold text-gray-500">Today's Date</p>
                <p class="font-bold"><?= $today; ?></p>
            </div>
        </div>
    </header>

    <div class="p-8 space-y-6">

        <!-- Filter by Date -->
        <div class="bg-white shadow-lg rounded-xl p-6 flex items-center gap-4">
            <form method="POST" class="flex gap-2 w-full items-center">
                <label for="sheduledate" class="font-semibold text-gray-700">Filter by Date:</label>
                <input type="date" name="sheduledate" id="sheduledate" class="flex-1 pl-3 h-12 font-semibold rounded-lg border border-gray-200">
                <button type="submit" class="h-12 px-6 bg-blue-600 hover:bg-blue-700 font-bold rounded-lg text-white">Filter</button>
            </form>
        </div>

        <!-- Sessions Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($result->num_rows==0): ?>
                <div class="col-span-full text-center py-12">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full mb-4">📅</div>
                    <p class="text-gray-500 font-semibold">No sessions found.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white shadow-md rounded-xl p-6 flex flex-col justify-between hover:shadow-xl transition">
                        <div>
                            <h2 class="font-bold text-lg text-gray-800"><?= substr($row['title'],0,25); ?></h2>
                            <p class="text-gray-500 mt-2">Scheduled: <?= $row['scheduledate']; ?> | <?= substr($row['scheduletime'],0,5); ?></p>
                            <p class="text-gray-500 mt-1">Max Patients: <?= $row['nop']; ?></p>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="?action=view&id=<?= $row['scheduleid']; ?>" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold px-4 py-2 rounded-lg text-center transition">View</a>
                            <a href="?action=drop&id=<?= $row['scheduleid']; ?>&name=<?= urlencode($row['title']); ?>" class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-4 py-2 rounded-lg text-center transition">Cancel</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Modal Popups -->
<?php
if($_GET){
    $id=$_GET["id"];
    $action=$_GET["action"];

    // 1. DROP CONFIRMATION
    if($action=='drop'){
        $nameget = $_GET["name"];
        echo '
        <div class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
            <div class="bg-white rounded-xl shadow-lg p-8 w-96 text-center">
                <h2 class="text-xl font-bold mb-4">Are you sure?</h2>
                <p class="mb-6">Do you want to delete this session: <b>'.substr($nameget,0,40).'</b>?</p>
                <div class="flex justify-center gap-4">
                    <a href="delete-session.php?id='.$id.'" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Yes</a>
                    <a href="schedule.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold">No</a>
                </div>
            </div>
        </div>';
    } 
    
    // 2. VIEW SESSION DETAILS
    elseif($action=='view'){
        $sqlmain= "SELECT schedule.scheduleid, schedule.title, schedule.scheduledate, schedule.scheduletime, 
                          schedule.nop, schedule.session_link, schedule.account_number, schedule.account_holder, 
                          schedule.bank_name, schedule.channeling_fee, doctor.docname 
                   FROM schedule 
                   INNER JOIN doctor ON schedule.docid=doctor.docid 
                   WHERE schedule.scheduleid=$id";

        $row = $database->query($sqlmain)->fetch_assoc();

        // Fetch appointments for this session
        $sqlmain12= "SELECT appointment.appoid, appointment.apponum, appointment.pid, patient.pname, patient.ptel 
                     FROM appointment 
                     INNER JOIN patient ON patient.pid = appointment.pid 
                     WHERE scheduleid = $id";
        $appointments = $database->query($sqlmain12);

        echo '
        <div class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
            <div class="bg-white rounded-xl shadow-lg p-6 w-4/5 max-w-6xl overflow-auto max-h-[90vh]">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Session Details</h2>
                    <a href="schedule.php" class="text-gray-500 hover:text-gray-800 font-bold text-2xl">&times;</a>
                </div>

                <div class="mb-4 space-y-2">
                    <p><b>Title:</b> '.$row['title'].'</p>
                    <p><b>Date:</b> '.$row['scheduledate'].' | Time: '.$row['scheduletime'].'</p>
                    <p><b>Registered:</b> '.$appointments->num_rows.'/'.$row['nop'].'</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border">ID</th>
                                <th class="px-4 py-2 border">Name</th>
                                <th class="px-4 py-2 border">Appt #</th>
                                <th class="px-4 py-2 border">Phone</th>
                                <th class="px-4 py-2 border">E-Prescription</th> <!-- NEW COLUMN -->
                            </tr>
                        </thead>
                        <tbody>';
                        
                        if($appointments->num_rows == 0){
                            echo '<tr><td colspan="5" class="text-center py-4">No patients registered.</td></tr>';
                        } else {
                            while($app = $appointments->fetch_assoc()){
                                $appoid = $app['appoid'];
                                $pid = $app['pid'];
                                $pname = $app['pname'];
                                echo '<tr class="text-center">
                                        <td class="border px-2 py-1">'.$pid.'</td>
                                        <td class="border px-2 py-1">'.$pname.'</td>
                                        <td class="border px-2 py-1">'.$app['apponum'].'</td>
                                        <td class="border px-2 py-1">'.$app['ptel'].'</td>
                                        <td class="border px-2 py-1">
                                            <a href="?action=prescription&id='.$id.'&appoid='.$appoid.'&pid='.$pid.'&name='.$pname.'" 
                                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm font-bold">
                                               + Add Prescription
                                            </a>
                                        </td>
                                      </tr>';
                            }
                        }

        echo '          </tbody>
                    </table>
                </div>
            </div>
        </div>';
    }

    // 3. ADD PRESCRIPTION FORM POPUP
    // 3. ADD PRESCRIPTION FORM POPUP
    elseif($action == 'prescription'){
        $appoid = $_GET['appoid'];
        $pid = $_GET['pid'];
        $pname = $_GET['name'];
        $scheduleid = $_GET['id']; 

        echo '
        <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
            <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-3xl relative max-h-[90vh] overflow-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Add Prescription</h2>
                    <a href="schedule.php?action=view&id='.$scheduleid.'" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</a>
                </div>

                <div class="bg-blue-50 p-3 rounded-lg mb-4">
                    <p class="text-sm text-gray-600">Patient Name</p>
                    <p class="font-bold text-lg">'.$pname.'</p>
                </div>

                <form action="add-prescription.php" method="POST" class="space-y-4">
                    <input type="hidden" name="appoid" value="'.$appoid.'">
                    <input type="hidden" name="pid" value="'.$pid.'">
                    <input type="hidden" name="docid" value="'.$userid.'">
                    <input type="hidden" name="scheduleid" value="'.$scheduleid.'">

                    <div id="medication-container" class="space-y-4">
                        <div class="med-row border border-gray-200 rounded-lg p-4 bg-gray-50 relative">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                <div class="md:col-span-4">
                                    <label class="block font-bold text-xs text-gray-700 mb-1">Medication Name</label>
                                    <input type="text" name="medication[0]" required class="w-full border rounded px-2 py-2" placeholder="e.g. Paracetamol">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block font-bold text-xs text-gray-700 mb-1">Dosage</label>
                                    <input type="text" name="dosage[0]" required class="w-full border rounded px-2 py-2" placeholder="e.g. 500mg">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block font-bold text-xs text-gray-700 mb-1 text-center">Frequency (M-E-N)</label>
                                    <div class="flex justify-center gap-2">
                                        <label class="cursor-pointer bg-white border rounded px-2 py-1 hover:bg-blue-50"><input type="checkbox" name="morning[0]" value="1"> M</label>
                                        <label class="cursor-pointer bg-white border rounded px-2 py-1 hover:bg-blue-50"><input type="checkbox" name="evening[0]" value="1"> E</label>
                                        <label class="cursor-pointer bg-white border rounded px-2 py-1 hover:bg-blue-50"><input type="checkbox" name="night[0]" value="1"> N</label>
                                    </div>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block font-bold text-xs text-gray-700 mb-1">Notes/Instructions</label>
                                    <input type="text" name="notes[0]" class="w-full border rounded px-2 py-2" placeholder="Take with food/After breakfast">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addMedRow()" class="text-blue-600 hover:text-blue-800 font-bold text-sm flex items-center gap-1">
                        <span class="text-xl">+</span> Add Another Medicine
                    </button>

                    <div class="border-t pt-4 mt-4">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg shadow-lg">
                            Save All Prescriptions
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        let rowIndex = 1;

        function addMedRow() {
            const container = document.getElementById("medication-container");
            const newRow = document.createElement("div");
            newRow.className = "med-row border border-gray-200 rounded-lg p-4 bg-gray-50 relative";
            
            newRow.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" class="absolute top-1 right-2 text-red-500 font-bold text-xs hover:text-red-700">Remove</button>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-4">
                        <label class="block font-bold text-xs text-gray-700 mb-1">Medication Name</label>
                        <input type="text" name="medication[${rowIndex}]" required class="w-full border rounded px-2 py-2" placeholder="e.g. Amoxicillin">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold text-xs text-gray-700 mb-1">Dosage</label>
                        <input type="text" name="dosage[${rowIndex}]" required class="w-full border rounded px-2 py-2" placeholder="e.g. 250mg">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block font-bold text-xs text-gray-700 mb-1 text-center">Frequency (M-E-N)</label>
                        <div class="flex justify-center gap-2">
                            <label class="cursor-pointer bg-white border rounded px-2 py-1 hover:bg-blue-50"><input type="checkbox" name="morning[${rowIndex}]" value="1"> M</label>
                            <label class="cursor-pointer bg-white border rounded px-2 py-1 hover:bg-blue-50"><input type="checkbox" name="evening[${rowIndex}]" value="1"> E</label>
                            <label class="cursor-pointer bg-white border rounded px-2 py-1 hover:bg-blue-50"><input type="checkbox" name="night[${rowIndex}]" value="1"> N</label>
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block font-bold text-xs text-gray-700 mb-1">Notes/Instructions</label>
                        <input type="text" name="notes[${rowIndex}]" class="w-full border rounded px-2 py-2" placeholder="Take with food/After dinner">
                    </div>
                </div>
            `;

            container.appendChild(newRow);
            rowIndex++;
        }
        </script>
        ';
    }
}
?>

</body>
</html>