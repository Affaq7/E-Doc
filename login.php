<?php
session_start();

// Clear any existing session
$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

// Set timezone and current date
date_default_timezone_set('Asia/Kolkata');
$_SESSION["date"] = date('Y-m-d');

include("connection.php");

$error = '<span class="text-red-500"></span>';

if ($_POST) {
    $email = $_POST['useremail'];
    $password = $_POST['userpassword'];

    $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
    if ($result->num_rows == 1) {
        $utype = $result->fetch_assoc()['usertype'];
        if ($utype == 'p') {
            $checker = $database->query("SELECT * FROM patient WHERE pemail='$email' AND ppassword='$password'");
            if ($checker->num_rows == 1) {
                $_SESSION['user'] = $email;
                $_SESSION['usertype'] = 'p';
                header('location: patient/index.php');
                exit();
            } else {
                $error = '<span class="text-red-600">Wrong credentials: Invalid email or password</span>';
            }
        } elseif ($utype == 'a') {
            $checker = $database->query("SELECT * FROM admin WHERE aemail='$email' AND apassword='$password'");
            if ($checker->num_rows == 1) {
                $_SESSION['user'] = $email;
                $_SESSION['usertype'] = 'a';
                header('location: admin/index.php');
                exit();
            } else {
                $error = '<span class="text-red-600">Wrong credentials: Invalid email or password</span>';
            }
        } elseif ($utype == 'd') {
            $checker = $database->query("SELECT * FROM doctor WHERE docemail='$email' AND docpassword='$password'");
            if ($checker->num_rows == 1) {
                $_SESSION['user'] = $email;
                $_SESSION['usertype'] = 'd';
                header('location: doctor/index.php');
                exit();
            } else {
                $error = '<span class="text-red-600">Wrong credentials: Invalid email or password</span>';
            }
        }
    } else {
        $error = '<span class="text-red-600">No account found for this email.</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50 flex items-center justify-center min-h-screen">

<div class="w-full max-w-md bg-white rounded-xl shadow-xl p-8">
    <h1 class="text-3xl font-bold text-gray-900 text-center mb-4">Welcome Back!</h1>
    <p class="text-gray-600 text-center mb-6">Login with your details to continue</p>

    <form method="POST" class="space-y-4">
        <div>
            <label for="useremail" class="block text-gray-700 font-semibold mb-1">Email</label>
            <input type="email" name="useremail" placeholder="Email Address" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div>
            <label for="userpassword" class="block text-gray-700 font-semibold mb-1">Password</label>
            <input type="password" name="userpassword" placeholder="Password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div class="text-center">
            <?php echo $error; ?>
        </div>

        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded-lg transition">Login</button>
    </form>

    <p class="text-center mt-6 text-gray-600">
        Don't have an account? 
        <a href="signup.php" class="text-blue-500 font-semibold hover:underline">Sign Up</a>
    </p>
</div>

</body>
</html>
