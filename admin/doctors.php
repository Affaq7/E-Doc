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
if($_POST){
    $keyword = $_POST["search"];
    $sqlmain = "SELECT * FROM doctor 
                WHERE docemail='$keyword' 
                   OR docname='$keyword' 
                   OR docname LIKE '$keyword%' 
                   OR docname LIKE '%$keyword' 
                   OR docname LIKE '%$keyword%'";
} else {
    $sqlmain = "SELECT * FROM doctor ORDER BY docid DESC";
}

$list11 = $database->query($sqlmain);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctors — Admin</title>
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
.login-btn { padding:0.5rem 1rem; border-radius:0.5rem; cursor:pointer; }
.btn-primary { background:#0ea5e9; color:white; }
.btn-primary-soft { background:#e0f2fe; color:#0ea5e9; }
.label-td { padding:0.5rem 0; }
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
                <button type="submit" class="w-full text-sm font-semibold text-red-600 border border-red-200 px-3 py-2 rounded-lg hover:bg-red-50">
                    Log out
                </button>
            </form>
        </div>
        <nav class="px-4 py-6 space-y-1">
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100  font-bold text-gray-700">Dashboard</a>
            <a href="doctors.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-bold shadow">Doctors</a>
           <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Schedule</a>
        <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Appointment</a>
        <a href="patient.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Patients</a>
      </nav>

        
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-auto">
        <header class="bg-white sticky top-0 z-10 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Doctors</h1>
                    <p class="small-text mt-0.5">Manage doctor records</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Today's Date</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($today); ?></p>
                    </div>
                    <button class="p-2 rounded-lg bg-white border border-gray-100 shadow-sm">
                        <img src="../img/calendar.svg" alt="calendar" class="w-6 h-6">
                    </button>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <form method="POST" class="flex gap-2">
                    <input type="search" name="search" placeholder="Search Doctor name or Email" class="px-3 py-2 border rounded-lg" list="doctors">
                    <input type="submit" value="Search" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                </form>
                <a href="?action=add&id=none&error=0">
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg flex items-center gap-2">
                        <img src="../img/icons/add.svg" class="w-5 h-5"> Add New Doctor
                    </button>
                </a>
                <datalist id="doctors">
                    <?php $listAll = $database->query("SELECT docname, docemail FROM doctor"); 
                    while($r = $listAll->fetch_assoc()){
                        echo "<option value='".$r['docname']."'>";
                        echo "<option value='".$r['docemail']."'>";
                    } ?>
                </datalist>
            </div>

            <!-- Doctors Table -->
            <div class="bg-white rounded-xl card-shadow overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        if($list11->num_rows==0){
                            echo '<tr><td colspan="4" class="text-center py-12">
                                  <img src="../img/notfound.svg" class="mx-auto w-36 mb-4">
                                  <p class="text-gray-500 font-semibold">No doctors found</p>
                                  </td></tr>';
                        } else {
                            while($row = $list11->fetch_assoc()){
                                $docid = $row['docid'];
                                $name = $row['docname'];
                                $email = $row['docemail'];
                                $spe = $row['specialties'];
                                $speName = $database->query("SELECT sname FROM specialties WHERE id='$spe'")->fetch_assoc()['sname'];
                                echo "<tr>
                                        <td class='px-6 py-4'>$name</td>
                                        <td class='px-6 py-4'>$email</td>
                                        <td class='px-6 py-4'>$speName</td>
                                        <td class='px-6 py-4 flex gap-2'>
                                            <a href='?action=view&id=$docid' class='px-3 py-1 bg-blue-100 text-blue-700 rounded'>View</a>
                                            <a href='?action=edit&id=$docid&error=0' class='px-3 py-1 bg-yellow-100 text-yellow-700 rounded'>Edit</a>
                                            <a href='?action=drop&id=$docid&name=$name' class='px-3 py-1 bg-red-100 text-red-700 rounded'>Delete</a>
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
<!-- Popup handling -->
 <?php
if ($_GET) {
    $id = $_GET["id"] ?? null;
    $action = $_GET["action"] ?? null;

    function showPopup($content) {
        echo '
        <div style="
            position: fixed; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.6); display:flex; justify-content:center; align-items:center;
            z-index:9999;
        ">
            <div style="
                background:#fff; border-radius:12px; width:90%; max-width:600px;
                padding:25px 30px; box-shadow:0 12px 35px rgba(0,0,0,0.25);
                font-family: Arial, sans-serif; position: relative;
                animation: fadeIn 0.3s ease-out;
            ">
                <a href="doctors.php" style="
                    position:absolute; top:15px; right:20px; font-size:24px;
                    text-decoration:none; color:#888;">&times;</a>
                ' . $content . '
            </div>
        </div>
        <style>
            @keyframes fadeIn { from {opacity:0; transform:translateY(-20px);} to {opacity:1; transform:translateY(0);} }
            .popup-table { width:100%; border-collapse: collapse; margin-top:15px; }
            .popup-table td { padding:10px; color:#555; }
            .popup-table td:first-child { font-weight:bold; color:#333; width:35%; }
            .popup-input, .popup-select { width:95%; padding:10px; margin:5px 0; border-radius:8px; border:1px solid #ccc; font-size:14px; }
            .popup-btn { padding:10px 20px; border:none; border-radius:8px; cursor:pointer; font-size:16px; margin:10px; transition:0.2s; }
            .popup-btn-primary { background:#4CAF50; color:#fff; }
            .popup-btn-primary:hover { background:#45a049; }
            .popup-btn-secondary { background:#f0f0f0; color:#333; }
            .popup-btn-secondary:hover { background:#ddd; }
            h2 { font-weight:bold; color:#333; margin-bottom:15px; }
        </style>
        ';
    }

    $btnPrimary = "popup-btn popup-btn-primary";
    $btnSecondary = "popup-btn popup-btn-secondary";

    if ($action === 'drop' && $id) {
        $nameget = $_GET["name"] ?? '';
        $content = '
            <h2>Confirm Deletion</h2>
            <div style="color:#555; margin-bottom:20px;">Are you sure you want to delete this record?<br>(' . substr($nameget,0,40) . ')</div>
            <div style="text-align:center;">
                <a href="delete-doctor.php?id=' . $id . '"><button class="'.$btnPrimary.'">Yes</button></a>
                <a href="doctors.php"><button class="'.$btnSecondary.'">No</button></a>
            </div>
        ';
        showPopup($content);

    } elseif ($action === 'view' && $id) {
        $row = $database->query("SELECT * FROM doctor WHERE docid='$id'")->fetch_assoc();
        $spcil_name = $database->query("SELECT sname FROM specialties WHERE id='{$row['specialties']}'")->fetch_assoc()['sname'];

        $content = '
            <h2>Doctor Details</h2>
            <table class="popup-table">
                <tr><td>Name:</td><td>'.$row['docname'].'</td></tr>
                <tr><td>Email:</td><td>'.$row['docemail'].'</td></tr>
                <tr><td>NIC:</td><td>'.$row['docnic'].'</td></tr>
                <tr><td>Telephone:</td><td>'.$row['doctel'].'</td></tr>
                <tr><td>Specialty:</td><td>'.$spcil_name.'</td></tr>
            </table>
            <div style="text-align:center; margin-top:20px;">
                <a href="doctors.php"><button class="'.$btnPrimary.'">OK</button></a>
            </div>
        ';
        showPopup($content);

    } elseif ($action === 'add') {
        $error_1 = $_GET["error"] ?? '0';
        $errorlist = [
            '1'=>'<div style="color:red; margin-bottom:15px;">Email already exists!</div>',
            '2'=>'<div style="color:red; margin-bottom:15px;">Password confirmation error!</div>',
            '3'=>'', '4'=>'', '0'=>''
        ];
        $options = '';
        $list11 = $database->query("SELECT * FROM specialties ORDER BY sname ASC;");
        while ($row = $list11->fetch_assoc()) $options .= "<option value='{$row['id']}'>{$row['sname']}</option>";

        $content = '
            '.($errorlist[$error_1] ?? '').'
            <h2>Add New Doctor</h2>
            <form action="add-new.php" method="POST">
                <input class="popup-input" type="text" name="name" placeholder="Name" required><br>
                <input class="popup-input" type="email" name="email" placeholder="Email" required><br>
                <input class="popup-input" type="text" name="nic" placeholder="NIC" required><br>
                <input class="popup-input" type="tel" name="Tele" placeholder="Telephone" required><br>
                <select class="popup-select" name="spec">'.$options.'</select><br>
                <input class="popup-input" type="password" name="password" placeholder="Password" required><br>
                <input class="popup-input" type="password" name="cpassword" placeholder="Confirm Password" required><br>
                <div style="text-align:center; margin-top:15px;">
                    <input type="reset" value="Reset" class="'.$btnSecondary.'">
                    <input type="submit" value="Add" class="'.$btnPrimary.'">
                </div>
            </form>
        ';
        showPopup($content);

    } elseif ($action === 'edit' && $id) {
        $row = $database->query("SELECT * FROM doctor WHERE docid='$id'")->fetch_assoc();
        $spcil_name = $database->query("SELECT sname FROM specialties WHERE id='{$row['specialties']}'")->fetch_assoc()['sname'];
        $options = '';
        $list11 = $database->query("SELECT * FROM specialties");
        while ($r = $list11->fetch_assoc()) $options .= "<option value='{$r['id']}'>{$r['sname']}</option>";

        $content = '
            <h2>Edit Doctor Details (ID: '.$id.')</h2>
            <form action="edit-doc.php" method="POST">
                <input type="hidden" name="id00" value="'.$id.'">
                <input type="hidden" name="oldemail" value="'.$row['docemail'].'">
                <input class="popup-input" type="email" name="email" value="'.$row['docemail'].'" required><br>
                <input class="popup-input" type="text" name="name" value="'.$row['docname'].'" required><br>
                <input class="popup-input" type="text" name="nic" value="'.$row['docnic'].'" required><br>
                <input class="popup-input" type="tel" name="Tele" value="'.$row['doctel'].'" required><br>
                <select class="popup-select" name="spec">Current: '.$spcil_name.$options.'</select><br>
                <input class="popup-input" type="password" name="password" placeholder="Password" required><br>
                <input class="popup-input" type="password" name="cpassword" placeholder="Confirm Password" required><br>
                <div style="text-align:center; margin-top:15px;">
                    <input type="reset" value="Reset" class="'.$btnSecondary.'">
                    <input type="submit" value="Save" class="'.$btnPrimary.'">
                </div>
            </form>
        ';
        showPopup($content);
    }
}
?>



</body>
</html>
