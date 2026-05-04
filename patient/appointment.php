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

// Today's date
date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// Fetch appointments + session link
$sqlmain = "SELECT 
                appointment.appoid, 
                schedule.scheduleid, 
                schedule.title, 
                doctor.docname,
                schedule.scheduledate, 
                schedule.scheduletime, 
                schedule.session_link,
                appointment.apponum, 
                appointment.appodate
            FROM schedule 
            INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid 
            INNER JOIN doctor ON schedule.docid = doctor.docid 
            WHERE appointment.pid = $userid";

if ($_POST && !empty($_POST['sheduledate'])) {
    $date = $_POST['sheduledate'];
    $sqlmain .= " AND schedule.scheduledate='$date'";
}

$sqlmain .= " ORDER BY appointment.appodate ASC";
$result = $database->query($sqlmain);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50">

<aside class="w-64 bg-white shadow-xl flex flex-col">
  <div class="p-6 border-b border-gray-100">
    <div class="flex items-center gap-3 mb-4">
      <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold rounded-full">
        <?php echo strtoupper(substr($patientName, 0, 2)); ?>
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-bold text-gray-900 truncate"><?php echo $patientName; ?></p>
        <p class="text-sm text-gray-500 truncate"><?php echo $useremail; ?></p>
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
    <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Scheduled Sessions</a>
    <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg">My Bookings</a>
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


<main class="flex-1 overflow-auto">
  <header class="bg-white shadow-sm px-8 py-4 flex items-center justify-between sticky top-0 z-10">
    <h1 class="text-2xl font-black text-gray-900">My Bookings</h1>
    
    <div class="flex items-center gap-4">
        <a href="?action=prescriptions" class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-bold py-2 px-4 rounded-lg shadow-lg flex items-center gap-2 transition">
            <span>💊</span> Past Prescriptions
        </a>

        <div class="flex items-center gap-2 text-gray-600 border-l pl-4">
          <span>📅</span>
          <div>
            <p class="text-sm font-semibold text-gray-500">Today's Date</p>
            <p class="font-bold"><?php echo $today; ?></p>
          </div>
        </div>
    </div>
  </header>

  <div class="p-8 space-y-6">

    <div class="bg-white shadow-lg rounded-xl p-6 flex items-center gap-4">
      <form method="POST" class="flex gap-2 w-full items-center">
        <label for="sheduledate" class="font-semibold text-gray-700">Filter by Date:</label>
        <input type="date" name="sheduledate" id="sheduledate" class="flex-1 pl-3 h-12 font-semibold rounded-lg border border-gray-200">
        <button type="submit" class="h-12 px-6 bg-blue-600 hover:bg-blue-700 font-bold rounded-lg text-white">Filter</button>
      </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php if ($result->num_rows == 0): ?>
        <div class="col-span-full text-center py-12">
          <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full mb-4">📅</div>
          <p class="text-gray-500 font-semibold">No bookings found.</p>
        </div>
      <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="bg-white shadow-md rounded-xl p-6 flex flex-col justify-between">
            <div>
              <h2 class="font-bold text-lg text-gray-800"><?php echo substr($row['title'],0,25); ?></h2>
              <p class="text-gray-600 mt-1">Doctor: <?php echo substr($row['docname'],0,30); ?></p>
              <p class="text-gray-500 mt-2">
                Scheduled: <?php echo $row['scheduledate']; ?> | Starts: <b>@<?php echo substr($row['scheduletime'],0,5); ?></b> (24h)
              </p>
              <p class="text-gray-500 mt-1">Booking Date: <?php echo $row['appodate']; ?></p>
              <p class="text-gray-500 mt-1">Appointment #: 0<?php echo $row['apponum']; ?> | Reference: OC-000-<?php echo $row['appoid']; ?></p>
              
              <p class="text-gray-500 mt-1">
                  Session Link: 
                  <a href="<?php echo $row['session_link']; ?>" 
                    target="_blank" 
                    class="text-blue-600 underline">
                    <?php echo $row['session_link']; ?>
                  </a>
              </p>
            </div>
            <div class="mt-4">
              <form method="POST" action="delete-appointment.php">
                <input type="hidden" name="id" value="<?php echo $row['appoid']; ?>">
                <button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-4 py-2 rounded-lg">Cancel Booking</button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php
if ($_GET && isset($_GET['action']) && $_GET['action'] == 'prescriptions') {
    // Fetch prescriptions ordered by session ID and date for visual grouping
    $sqlPres = "SELECT p.*, d.docname, s.title, s.scheduleid 
                FROM prescriptions p
                JOIN doctor d ON p.docid = d.docid
                JOIN appointment a ON p.appoid = a.appoid
                JOIN schedule s ON a.scheduleid = s.scheduleid
                WHERE p.pid = $userid
                ORDER BY s.scheduledate DESC, s.scheduleid DESC, p.pres_date DESC";
    
    $presResult = $database->query($sqlPres);
    $current_session_id = null; // Variable to track session change for grouping

    echo '
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-11/12 max-w-4xl relative max-h-[90vh] overflow-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">My Prescriptions History</h2>
                <a href="appointment.php" class="text-gray-400 hover:text-gray-600 text-3xl font-bold">&times;</a>
            </div>

            <div class="overflow-x-auto space-y-6">
                ';
                    
            if ($presResult->num_rows == 0) {
                echo '<p class="text-center py-6 text-gray-500">No prescriptions found in your history.</p>';
            } else {
                while ($row = $presResult->fetch_assoc()) {
                    // Check if a new session grouping is needed
                    if ($row['scheduleid'] !== $current_session_id) {
                        // Close previous table if it\'s not the first group
                        if ($current_session_id !== null) {
                            echo '</tbody></table></div>';
                        }
                        
                        // Start New Session Grouping (New Table/Header)
                        $current_session_id = $row['scheduleid'];
                        echo '
                        <div class="session-group bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h3 class="font-black text-lg text-purple-700 mb-3">
                                📅 Session: '.$row['title'].' (Dr. '.$row['docname'].')
                            </h3>
                            <table class="min-w-full border border-gray-300">
                                <thead class="bg-purple-100">
                                    <tr>
                                        <th class="px-4 py-2 border text-left text-purple-800">Date Prescribed</th>
                                        <th class="px-4 py-2 border text-left text-purple-800">Medication</th>
                                        <th class="px-4 py-2 border text-left text-purple-800">Dosage</th>
                                        <th class="px-4 py-2 border text-left text-purple-800">Frequency (M+E+N)</th> 
                                        <th class="px-4 py-2 border text-left text-purple-800">Instructions/Notes</th> </tr>
                                </thead>
                            <tbody>';
                    }
                    
                    // Display Prescription Row
                    echo '<tr class="hover:bg-white">
                            <td class="border px-4 py-2 text-sm">'.$row['pres_date'].'</td>
                            <td class="border px-4 py-2 font-bold text-blue-700">'.$row['medication_name'].'</td>
                            <td class="border px-4 py-2">'.$row['dosage'].'</td>
                            <td class="border px-4 py-2 text-center font-mono bg-gray-100">'.$row['frequency'].'</td>
                            <td class="border px-4 py-2 text-sm text-gray-600">'.$row['notes'].'</td>
                          </tr>';
                }
                
                // Close the last remaining table/container
                echo '</tbody></table></div>'; 
            }

    echo '      
            </div>
            <div class="mt-6 text-right">
                <a href="appointment.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg">Close</a>
            </div>
        </div>
    </div>';
}
?>

</body>
</html>