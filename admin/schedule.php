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

// Filter/search logic
if ($_POST) {
    $sqlpt1 = "";
    $sqlpt2 = "";

    if (!empty($_POST["sheduledate"])) {
        $sheduledate = $_POST["sheduledate"];
        $sqlpt1 = " schedule.scheduledate='$sheduledate' ";
    }

    if (!empty($_POST["docid"])) {
        $docid = $_POST["docid"];
        $sqlpt2 = " doctor.docid=$docid ";
    }

    $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop, schedule.session_link
                FROM schedule 
                INNER JOIN doctor ON schedule.docid=doctor.docid";

    $sqllist = array($sqlpt1, $sqlpt2);
    $sqlkeywords = array(" WHERE ", " AND ");
    $key2 = 0;
    foreach ($sqllist as $key) {
        if (!empty($key)) {
            $sqlmain .= $sqlkeywords[$key2] . $key;
            $key2++;
        }
    }
} else {
    $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop, schedule.session_link
                FROM schedule 
                INNER JOIN doctor ON schedule.docid=doctor.docid 
                ORDER BY schedule.scheduledate DESC";
}

$list110 = $database->query($sqlmain);
$list11 = $database->query("SELECT * FROM doctor ORDER BY docname ASC");

// NEW: Get Pending Requests Count for the notification badge
$req_count = $database->query("SELECT * FROM booking_requests WHERE status='pending'")->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Schedule — Admin</title>
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
    background:white; padding:2rem; border-radius:1rem; width:90%; max-width:800px; position:relative; max-height: 90vh; overflow-y: auto;
}
.close {
    position:absolute; top:1rem; right:1rem; font-size:1.5rem; font-weight:bold; text-decoration:none; color:#333;
}
.input-text, select { width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid #ccc; border-radius:0.5rem; }
.login-btn { padding:0.5rem 1rem; border-radius:0.5rem; cursor:pointer; }
.btn-primary { background:#0ea5e9; color:white; }
.btn-primary-soft { background:#e0f2fe; color:#0ea5e9; }
.label-td { padding:0.5rem 0; }
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
            <a href="schedule.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-bold shadow">Schedule</a>
            <a href="appointment.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100  font-bold text-gray-700">Appointments</a>
            <a href="patient.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100  font-bold text-gray-700">Patients</a>
        </nav>
        
    </aside>

    <main class="flex-1 overflow-auto">
        <header class="bg-white sticky top-0 z-10 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Schedule</h1>
                    <p class="small-text mt-0.5">Manage session records</p>
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

            <div class="flex justify-between items-center">
                <form method="POST" class="flex gap-2">
                    <input type="date" name="sheduledate" class="px-3 py-2 border rounded-lg" value="<?php echo $_POST['sheduledate'] ?? ''; ?>">
                    <select name="docid" class="px-3 py-2 border rounded-lg">
                        <option value="" disabled selected hidden>Choose Doctor</option>
                        <?php while($row00=$list11->fetch_assoc()): ?>
                            <option value="<?php echo $row00['docid']; ?>" <?php echo (isset($_POST['docid']) && $_POST['docid']==$row00['docid'])?'selected':''; ?>>
                                <?php echo $row00['docname']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <input type="submit" value="Filter" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                </form>

                <div class="flex gap-3">
                    <a href="?action=requests">
                        <button class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg flex items-center gap-2 shadow-lg relative">
                            Requests 
                            <?php if($req_count > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $req_count ?></span>
                            <?php endif; ?>
                        </button>
                    </a>

                    <a href="?action=add-session&id=none&error=0">
                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg flex items-center gap-2">
                            <img src="../img/icons/add.svg" class="w-5 h-5"> Add Session
                        </button>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl card-shadow overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Appointments</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meet Link</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        if($list110->num_rows==0){
                            echo '<tr><td colspan="6" class="text-center py-12">
                                    <img src="../img/notfound.svg" class="mx-auto w-36 mb-4">
                                    <p class="text-gray-500  font-bold">No sessions found</p>
                                    </td></tr>';
                        } else {
                            while($row=$list110->fetch_assoc()):
                                $scheduleid=$row['scheduleid'];
                                $title=$row['title'];
                                $docname=$row['docname'];
                                $scheduledate=$row['scheduledate'];
                                $scheduletime=$row['scheduletime'];
                                $nop=$row['nop'];
                                $meetlink=$row['session_link'];
                        ?>
                        <tr>
                            <td class="px-6 py-4"><?php echo $title; ?></td>
                            <td class="px-6 py-4"><?php echo $docname; ?></td>
                            <td class="px-6 py-4"><?php echo $scheduledate . " " . substr($scheduletime,0,5); ?></td>
                            <td class="px-6 py-4 text-center"><?php echo $nop; ?></td>
                            <td class="px-6 py-4 text-blue-600">
                                <button onclick="showLinkPopup('<?php echo $meetlink; ?>')" class="underline">View Link</button>
                            </td>

                            <td class="px-6 py-4 flex gap-2">
                                <a href="?action=view&id=<?php echo $scheduleid; ?>" class="px-3 py-1 bg-blue-100 text-blue-700 rounded">View</a>
                                <a href="?action=drop&id=<?php echo $scheduleid; ?>&name=<?php echo $title; ?>" class="px-3 py-1 bg-red-100 text-red-700 rounded">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; } ?>
                    </tbody>
                </table>
            </div>

        </div>
        
    </main>
</div>

<div id="linkPopup" class="overlay" style="display:none; justify-content:center; align-items:center;">
    <div class="popup" style="max-width: 400px; width: 90%; text-align:center; padding: 20px;">
        <h2 class="text-xl font-bold mb-4">Session Link</h2>
        <p id="popupLink" class="text-blue-600 break-words mb-6"></p>
        <button onclick="closeLinkPopup()" class="px-4 py-2 bg-blue-600 text-white rounded">Okay</button>
    </div>
</div>

<script>
function showLinkPopup(link) {
    document.getElementById('popupLink').innerHTML = '<a href="' + link + '" target="_blank">' + link + '</a>';
    document.getElementById('linkPopup').style.display = 'flex';
}

function closeLinkPopup() {
    document.getElementById('linkPopup').style.display = 'none';
}
</script>

<?php
if ($_GET) {
    $id = $_GET["id"] ?? null;
    $action = $_GET["action"] ?? null;

    function showPopup($content){
        echo '<div class="overlay"><div class="popup">'.$content.'<a class="close" href="schedule.php">&times;</a></div></div>';
    }



    // 2. DELETE SESSION POPUP
    if ($action==='drop' && $id) {
        $nameget = $_GET["name"] ?? '';
        $content = '<h2 class="text-xl font-bold mb-2">Confirm Deletion</h2>
                    <p class="mb-4">Are you sure you want to delete this session?<br>'.$nameget.'</p>
                    <div class="flex justify-center gap-4">
                        <a href="delete-session.php?id='.$id.'" class="px-4 py-2 bg-red-600 text-white rounded">Yes</a>
                        <a href="schedule.php" class="px-4 py-2 bg-gray-200 rounded">No</a>
                    </div>';
        showPopup($content);
    }

    // 3. ADD SESSION POPUP
    if ($action==='add-session') {
        // Fetch doctor list
        $docList = $database->query("SELECT * FROM doctor ORDER BY docname ASC");
        $options = '';
        while($row=$docList->fetch_assoc()){
            $options .= '<option value="'.$row['docid'].'">'.$row['docname'].'</option>';
        }

        // Popup content
        $content = '<h2 class="text-xl font-bold mb-4">Add New Session</h2>
                    <form action="add-session.php" method="POST" class="space-y-3">

                        <input type="text" name="title" placeholder="Session Title" class="input-text" required>

                        <select name="docid" class="input-text" required>
                            <option value="">Choose Doctor</option>'.$options.'
                        </select>

                        <input type="number" name="nop" placeholder="Max Appointments" class="input-text" required>

                        <input type="date" name="date" min="'.date('Y-m-d').'" class="input-text" required>

                        <input type="time" name="time" class="input-text" required>

                        <input type="text" name="session_link" placeholder="Session Link" class="input-text" required>


                        <h3 class="text-lg font-semibold mt-4 mb-2">Payment Details</h3>

                        <input type="text" 
                               name="account_number" 
                               placeholder="Account Number" 
                               class="input-text" required>

                        <input type="text" 
                               name="account_holder" 
                               placeholder="Account Holder Name" 
                               class="input-text" required>

                        <input type="text" 
                               name="bank_name" 
                               placeholder="Bank Name" 
                               class="input-text" required>

                        <input type="number" 
                               name="channeling_fee" 
                               placeholder="Channeling Fee (PKR)" 
                               class="input-text" required>


                        <div class="flex justify-center gap-3 mt-2">
                            <input type="reset" class="btn-primary-soft px-4 py-2 rounded" value="Reset">
                            <input type="submit" class="btn-primary px-4 py-2 rounded" value="Add Session">
                        </div>

                    </form>';

        showPopup($content);
    }

    // 4. VIEW SESSION DETAILS POPUP
    elseif($action=='view'){
        // Fetch session details including payment info
        $sqlmain= "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, 
                          schedule.nop, schedule.session_link, schedule.account_number, schedule.account_holder, 
                          schedule.bank_name, schedule.channeling_fee
                   FROM schedule 
                   INNER JOIN doctor ON schedule.docid=doctor.docid  
                   WHERE schedule.scheduleid=$id";
        $result= $database->query($sqlmain);
        $row=$result->fetch_assoc();
        $docname=$row["docname"];
        $scheduleid=$row["scheduleid"];
        $title=$row["title"];
        $scheduledate=$row["scheduledate"];
        $scheduletime=$row["scheduletime"];
        $nop=$row['nop'];
        $meetlink=$row['session_link'];
        $account_number = $row['account_number'];
        $account_holder = $row['account_holder'];
        $bank_name = $row['bank_name'];
        $channeling_fee = $row['channeling_fee'];

        // Fetch appointments for this session
        $sqlmain12= "SELECT * FROM appointment 
                     INNER JOIN patient ON patient.pid=appointment.pid 
                     WHERE appointment.scheduleid=$id";
        $result12= $database->query($sqlmain12);

        echo '
        <div id="popup1" class="overlay">
            <div class="popup" style="max-width: 800px; width: 90%; padding: 20px; border-radius: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 28px; font-weight: 600;">Session Details</h2>
                    <a class="close" href="schedule.php" style="font-size: 30px; font-weight: bold;">&times;</a>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Session Title</p>
                        <p style="margin: 0; font-size: 18px;">'.$title.'</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Doctor</p>
                        <p style="margin: 0; font-size: 18px;">'.$docname.'</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Scheduled Date</p>
                        <p style="margin: 0; font-size: 18px;">'.$scheduledate.'</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Scheduled Time</p>
                        <p style="margin: 0; font-size: 18px;">'.$scheduletime.'</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Meet Link</p>
                        <p style="margin: 0; font-size: 18px; color: blue;"><a href="'.$meetlink.'" target="_blank">Open Link</a></p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Registered Patients</p>
                        <p style="margin: 0; font-size: 18px;">'.$result12->num_rows.'/'.$nop.'</p>
                    </div>
                </div>

                <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 10px; border-top:1px solid #ddd; padding-top:10px;">Payment Details</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Account Number</p>
                        <p style="margin: 0; font-size: 18px;">'.$account_number.'</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Account Holder</p>
                        <p style="margin: 0; font-size: 18px;">'.$account_holder.'</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Bank Name</p>
                        <p style="margin: 0; font-size: 18px;">'.$bank_name.'</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-weight: 500; margin: 0 0 5px 0;">Channeling Fee (PKR)</p>
                        <p style="margin: 0; font-size: 18px;">'.$channeling_fee.'</p>
                    </div>
                </div>

                <div style="max-height: 400px; overflow-y: auto; border-top: 1px solid #ddd; padding-top: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f5f5f5; text-align: left;">
                                <th style="padding: 10px; font-weight: 600;">Patient ID</th>
                                <th style="padding: 10px; font-weight: 600;">Patient Name</th>
                                <th style="padding: 10px; font-weight: 600;">Appointment Number</th>
                                <th style="padding: 10px; font-weight: 600;">Telephone</th>
                            </tr>
                        </thead>
                        <tbody>';
                            if($result12->num_rows==0){
                                echo '<tr><td colspan="4" style="text-align:center; padding: 20px;">No appointments found.</td></tr>';
                            } else {
                                while($row=$result12->fetch_assoc()){
                                    echo '<tr style="border-bottom: 1px solid #eee; text-align:center;">
                                            <td style="padding: 10px;">'.substr($row["pid"],0,15).'</td>
                                            <td style="padding: 10px; font-weight:500;">'.substr($row["pname"],0,25).'</td>
                                            <td style="padding: 10px; font-weight:500; color: var(--btnnicetext);">'.$row["apponum"].'</td>
                                            <td style="padding: 10px;">'.substr($row["ptel"],0,25).'</td>
                                        </tr>';
                                }
                            }
        echo           '</tbody>
                    </table>
                </div>
            </div>
        </div>';
    }

    if ($action==='requests') {
        $reqs = $database->query("
            SELECT booking_requests.*, patient.pname, patient.ptel, schedule.title, schedule.scheduledate, doctor.docname 
            FROM booking_requests
            INNER JOIN patient ON booking_requests.pid = patient.pid
            INNER JOIN schedule ON booking_requests.scheduleid = schedule.scheduleid
            INNER JOIN doctor ON booking_requests.docid = doctor.docid
            WHERE booking_requests.status = 'pending'
        ");

        echo '
        <div id="popup1" class="overlay">
            <div class="popup" style="max-width: 900px; width: 95%; padding: 20px; border-radius: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Pending Booking Requests</h2>
                    <a class="close" href="schedule.php" style="font-size: 30px; font-weight: bold;">&times;</a>
                </div>
                
                <div style="max-height: 500px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background-color: #f5f5f5;">
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Patient</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Session / Doctor</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Fee paid</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Trx ID / Acc</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Action</th>
                            </tr>
                        </thead>
                        <tbody>';
                        
        if($reqs->num_rows == 0){
            echo '<tr><td colspan="5" style="text-align:center; padding: 30px; color: #666;">No pending requests found.</td></tr>';
        } else {
            while ($r = $reqs->fetch_assoc()) {
                echo '
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">
                        <span style="font-weight: 600;">'.$r['pname'].'</span><br>
                        <span style="font-size: 0.85em; color: #666;">Tel: '.$r['ptel'].'</span>
                    </td>
                    <td style="padding: 12px;">
                        <span style="font-weight: 600;">'.$r['title'].'</span><br>
                        <span style="font-size: 0.85em; color: #666;">'.$r['scheduledate'].' | Dr. '.$r['docname'].'</span>
                    </td>
                    <td style="padding: 12px; font-weight: 600;">PKR '.$r['channeling_fee'].'</td>
                    <td style="padding: 12px;">
                        <span style="color: blue;">Trx: '.$r['payment_proof'].'</span><br>
                        <span style="font-size: 0.85em; color: #666;">Acc: '.$r['notes'].'</span>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 8px;">
                            <form action="handle-request.php" method="POST" style="margin:0;">
                                <input type="hidden" name="req_id" value="'.$r['id'].'">
                                <input type="hidden" name="scheduleid" value="'.$r['scheduleid'].'">
                                <input type="hidden" name="pid" value="'.$r['pid'].'">
                                <button type="submit" name="approve" style="padding: 6px 12px; background: #16a34a; color: white; border: none; border-radius: 4px; font-size: 0.9em; font-weight: bold; cursor:pointer;">Approve</button>
                            </form>
                            <form action="handle-request.php" method="POST" style="margin:0;">
                                <input type="hidden" name="req_id" value="'.$r['id'].'">
                                <button type="submit" name="reject" style="padding: 6px 12px; background: #dc2626; color: white; border: none; border-radius: 4px; font-size: 0.9em; font-weight: bold; cursor:pointer;">Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>';
            }
        }
        
        echo '      </tbody>
                    </table>
                </div>
            </div>
        </div>';
    }

}
?>
</body>
</html>