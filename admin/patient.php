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

// Search logic
if ($_POST) {
    $keyword = $_POST["search"];
    $sqlmain = "SELECT * FROM patient 
                WHERE pemail='$keyword' 
                   OR pname='$keyword' 
                   OR pname LIKE '$keyword%' 
                   OR pname LIKE '%$keyword' 
                   OR pname LIKE '%$keyword%'";
} else {
    $sqlmain = "SELECT * FROM patient ORDER BY pid DESC";
}

$list11 = $database->query($sqlmain);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patients — Admin</title>
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
.input-text, .box, select { width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid #ccc; border-radius:0.5rem; }
</style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50 min-h-screen">

<div class="flex">

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
            <a href="appointment.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100  font-bold text-gray-700">Appointments</a>
            <a href="patient.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-bold shadow">Patients</a>
        </nav>
    </aside>

    <main class="flex-1 overflow-auto">
        <header class="bg-white sticky top-0 z-10 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Patients</h1>
                    <p class="small-text mt-0.5">Manage patient records</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Today's Date</p>
                        <p class=" font-bold"><?php echo htmlspecialchars($today); ?></p>
                    </div>
                    <button class="p-2 rounded-lg bg-white border border-gray-100 shadow-sm">
                        <img src="../img/calendar.svg" alt="calendar" class="w-6 h-6">
                    </button>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">

            <form method="POST" class="flex gap-2">
                <input type="search" name="search" placeholder="Search Patient name or Email" class="px-3 py-2 border rounded-lg" list="patients">
                <input type="submit" value="Search" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                <datalist id="patients">
                    <?php
                    $listAll = $database->query("SELECT pname, pemail FROM patient");
                    while($r = $listAll->fetch_assoc()){
                        echo "<option value='".$r['pname']."'>";
                        echo "<option value='".$r['pemail']."'>";
                    }
                    ?>
                </datalist>
            </form>

            <div class="bg-white rounded-xl card-shadow overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telephone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DOB</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if($list11->num_rows==0){
                            echo '<tr><td colspan="6" class="text-center py-12">
                                    <img src="../img/notfound.svg" class="mx-auto w-36 mb-4">
                                    <p class="text-gray-500  font-bold">No patients found</p>
                                    </td></tr>';
                        } else {
                            while($row = $list11->fetch_assoc()){
                                $pid = $row['pid'];
                                $name = $row['pname'];
                                $email = $row['pemail'];
                                $nic = $row['pnic'];
                                $dob = $row['pdob'];
                                $tel = $row['ptel'];
                                echo "<tr>
                                        <td class='px-6 py-4'>$name</td>
                                        <td class='px-6 py-4'>$nic</td>
                                        <td class='px-6 py-4'>$tel</td>
                                        <td class='px-6 py-4'>$email</td>
                                        <td class='px-6 py-4'>$dob</td>
                                        <td class='px-6 py-4 flex gap-2'>
                                            <a href='?action=view&id=$pid' class='px-3 py-1 bg-blue-100 text-blue-700 rounded'>View</a>
                                            <a href='?action=drop&id=$pid&name=$name' class='px-3 py-1 bg-red-100 text-red-700 rounded'>Delete</a>
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

<?php
if ($_GET) {
    $id = $_GET["id"] ?? null;
    $action = $_GET["action"] ?? null;

    // VIEW POPUP
    if ($action === 'view' && $id) {
        $row = $database->query("SELECT * FROM patient WHERE pid='$id'")->fetch_assoc();
        $content = '
            <h2 class="text-xl font-bold mb-4">Patient Details</h2>
            <table class="popup-table w-full text-left mb-4">
                <tr><td class=" font-bold">Patient ID:</td><td>P-'.$row['pid'].'</td></tr>
                <tr><td class=" font-bold">Name:</td><td>'.$row['pname'].'</td></tr>
                <tr><td class=" font-bold">Email:</td><td>'.$row['pemail'].'</td></tr>
                <tr><td class=" font-bold">NIC:</td><td>'.$row['pnic'].'</td></tr>
                <tr><td class=" font-bold">Telephone:</td><td>'.$row['ptel'].'</td></tr>
                <tr><td class=" font-bold">Address:</td><td>'.$row['paddress'].'</td></tr>
                <tr><td class=" font-bold">Date of Birth:</td><td>'.$row['pdob'].'</td></tr>
            </table>
            <div class="text-center">
                <a href="patient.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg">OK</a>
            </div>
        ';
        echo '<div class="overlay"><div class="popup relative">'.$content.'<a href="patient.php" class="close">&times;</a></div></div>';
    }

    // DELETE POPUP (New)
    if ($action === 'drop' && $id) {
        $nameget = $_GET["name"] ?? '';
        $content = '
            <div class="text-center">
                <h2 class="text-2xl font-bold mb-2 text-red-600">Are you sure?</h2>
                <p class="mb-6 text-gray-600">You want to delete this patient:<br><b>'.$nameget.'</b></p>
                <div class="flex justify-center gap-4">
                    <a href="delete-patient.php?id='.$id.'" class="px-6 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700">Yes, Delete</a>
                    <a href="patient.php" class="px-6 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300">Cancel</a>
                </div>
            </div>
        ';
        echo '<div class="overlay"><div class="popup relative">'.$content.'<a href="patient.php" class="close">&times;</a></div></div>';
    }
}
?>

</body>
</html>