<?php
session_start();
include("../connection.php");

// Authentication: admin only
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}

// Today's date and timezone
date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// Fetch counts (keeps your original queries)
$patientrow = $database->query("select * from patient;");
$doctorrow = $database->query("select * from doctor;");
$appointmentrow = $database->query("select * from appointment where appodate>='$today';");
$schedulerow = $database->query("select * from schedule where scheduledate='$today';");

// For the two quick lists (upcoming 7 days)
$nextweek = date("Y-m-d", strtotime("+1 week"));

// Upcoming appointments query (same as your original)
$sql_appointments = "
    select appointment.appoid, schedule.scheduleid, schedule.title, doctor.docname, patient.pname,
           schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate
    from schedule
    inner join appointment on schedule.scheduleid = appointment.scheduleid
    inner join patient on patient.pid = appointment.pid
    inner join doctor on schedule.docid = doctor.docid
    where schedule.scheduledate >= '$today' and schedule.scheduledate <= '$nextweek'
    order by schedule.scheduledate desc
";
$upcomingAppointments = $database->query($sql_appointments);

// Upcoming sessions query (same as your original)
$sql_sessions = "
    select schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop
    from schedule
    inner join doctor on schedule.docid=doctor.docid
    where schedule.scheduledate >= '$today' and schedule.scheduledate <= '$nextweek'
    order by schedule.scheduledate desc
";
$upcomingSessions = $database->query($sql_sessions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard — eDoc</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Match some of your legacy classes visually if needed */
    .sidebar-width { width: 260px; }
    .card-shadow { box-shadow: 0 6px 18px rgba(13, 38, 59, 0.08); }
    /* Small helpers to mimic your previous spacing */
    .small-text { font-size: 0.9rem; color: #6b7280; }
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
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-bold shadow">
          Dashboard
        </a>

        <a href="doctors.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Doctors</a>
        <a href="schedule.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Schedule</a>
        <a href="appointment.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Appointment</a>
        <a href="patient.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-gray-700 hover:bg-gray-100">Patients</a>
      </nav>

      
    </aside>

    <!-- Main content -->
    <main class="flex-1 overflow-auto">
      <!-- Header -->
      <header class="bg-white sticky top-0 z-10 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Dashboard</h1>
            <p class="small-text mt-0.5">Overview & quick actions</p>
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

        <!-- Top cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white rounded-xl p-5 card-shadow flex items-center justify-between">
            <div>
              <p class="text-3xl font-extrabold"><?php echo $doctorrow->num_rows; ?></p>
              <p class="text-sm text-gray-500 mt-1">Doctors</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">👨‍⚕️</div>
          </div>

          <div class="bg-white rounded-xl p-5 card-shadow flex items-center justify-between">
            <div>
              <p class="text-3xl font-extrabold"><?php echo $patientrow->num_rows; ?></p>
              <p class="text-sm text-gray-500 mt-1">Patients</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">🧑‍🤝‍🧑</div>
          </div>

          <div class="bg-white rounded-xl p-5 card-shadow flex items-center justify-between">
            <div>
              <p class="text-3xl font-extrabold"><?php echo $appointmentrow->num_rows; ?></p>
              <p class="text-sm text-gray-500 mt-1">New Booking</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">📅</div>
          </div>

          <div class="bg-white rounded-xl p-5 card-shadow flex items-center justify-between">
            <div>
              <p class="text-3xl font-extrabold"><?php echo $schedulerow->num_rows; ?></p>
              <p class="text-sm text-gray-500 mt-1">Today Sessions</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-cyan-100 flex items-center justify-center">⏰</div>
          </div>
        </div>

        <!-- Two column content: upcoming appointments and sessions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Upcoming Appointments -->
          <section class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-start justify-between">
              <div>
                <h2 class="text-xl font-extrabold text-gray-900">Upcoming Appointments</h2>
                <p class="text-sm text-gray-500 mt-1">Appointments within next 7 days (quick view)</p>
              </div>
              <a href="appointment.php" class="text-sm font-semibold text-blue-600 hover:underline">Show all Appointments →</a>
            </div>

            <div class="mt-6">
              <?php if ($upcomingAppointments->num_rows == 0): ?>
                <div class="text-center py-12">
                  <img src="../img/notfound.svg" class="mx-auto w-36 mb-4" alt="no data">
                  <p class="text-gray-500 font-semibold">No upcoming appointments</p>
                </div>
              <?php else: ?>
                <div class="overflow-auto">
                  <table class="w-full text-left border-collapse">
                    <thead>
                      <tr class="border-b border-gray-200">
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">#</th>
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">Patient</th>
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">Doctor</th>
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">Session</th>
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">When</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($r = $upcomingAppointments->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                          <td class="py-3 px-3 font-semibold text-lg text-indigo-600"><?php echo htmlspecialchars($r['apponum']); ?></td>
                          <td class="py-3 px-3 font-medium"><?php echo htmlspecialchars(substr($r['pname'],0,30)); ?></td>
                          <td class="py-3 px-3"><?php echo htmlspecialchars(substr($r['docname'],0,30)); ?></td>
                          <td class="py-3 px-3"><?php echo htmlspecialchars(substr($r['title'],0,25)); ?></td>
                          <td class="py-3 px-3"><?php echo htmlspecialchars($r['scheduledate'] . ' @' . substr($r['scheduletime'],0,5)); ?></td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </section>

          <!-- Upcoming Sessions -->
          <section class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-start justify-between">
              <div>
                <h2 class="text-xl font-extrabold text-gray-900">Upcoming Sessions</h2>
                <p class="text-sm text-gray-500 mt-1">Sessions scheduled within next 7 days</p>
              </div>
              <a href="schedule.php" class="text-sm font-semibold text-blue-600 hover:underline">Show all Sessions →</a>
            </div>

            <div class="mt-6">
              <?php if ($upcomingSessions->num_rows == 0): ?>
                <div class="text-center py-12">
                  <img src="../img/notfound.svg" class="mx-auto w-36 mb-4" alt="no data">
                  <p class="text-gray-500 font-semibold">No upcoming sessions</p>
                </div>
              <?php else: ?>
                <div class="overflow-auto">
                  <table class="w-full text-left border-collapse">
                    <thead>
                      <tr class="border-b border-gray-200">
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">Session</th>
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">Doctor</th>
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">Date & Time</th>
                        <th class="py-3 px-3 text-sm font-semibold text-gray-700">Slots</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($s = $upcomingSessions->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                          <td class="py-3 px-3 font-medium"><?php echo htmlspecialchars(substr($s['title'],0,40)); ?></td>
                          <td class="py-3 px-3"><?php echo htmlspecialchars(substr($s['docname'],0,30)); ?></td>
                          <td class="py-3 px-3"><?php echo htmlspecialchars($s['scheduledate'] . ' @' . substr($s['scheduletime'],0,5)); ?></td>
                          <td class="py-3 px-3"><?php echo htmlspecialchars($s['nop']); ?></td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </section>
        </div>

        <!-- CTA buttons row (mirrors your Show all buttons) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <a href="appointment.php" class="block">
            <div class="bg-white rounded-xl p-4 flex items-center justify-center card-shadow hover:shadow-lg">
              <button class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg w-full">Show all Appointments</button>
            </div>
          </a>

          <a href="schedule.php" class="block">
            <div class="bg-white rounded-xl p-4 flex items-center justify-center card-shadow hover:shadow-lg">
              <button class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg w-full">Show all Sessions</button>
            </div>
          </a>
        </div>

      </div>
    </main>
  </div>

</body>
</html>
