<?php
session_start();

// Clear any existing session
$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

// Set timezone and current date
date_default_timezone_set('Asia/Kolkata');
$_SESSION["date"] = date('Y-m-d');

if ($_POST) {
    $_SESSION["personal"] = array(
        'fname' => $_POST['fname'],
        'lname' => $_POST['lname'],
        'address' => $_POST['address'],
        'nic' => $_POST['nic'],
        'dob' => $_POST['dob']
    );

    header("location: create-account.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50 flex items-center justify-center min-h-screen">

<div class="w-full max-w-md bg-white rounded-xl shadow-xl p-8">
    <h1 class="text-3xl font-bold text-gray-900 text-center mb-2">Let's Get Started</h1>
    <p class="text-gray-600 text-center mb-6">Add your personal details to continue</p>

    <form method="POST" class="space-y-4">
        <div class="flex gap-4">
            <div class="flex-1">
                <label for="fname" class="block text-gray-700 font-semibold mb-1">First Name</label>
                <input type="text" name="fname" placeholder="First Name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="flex-1">
                <label for="lname" class="block text-gray-700 font-semibold mb-1">Last Name</label>
                <input type="text" name="lname" placeholder="Last Name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
            </div>
        </div>

        <div>
            <label for="address" class="block text-gray-700 font-semibold mb-1">Address</label>
            <input type="text" name="address" placeholder="Address" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div>
            <label for="nic" class="block text-gray-700 font-semibold mb-1">CNIC Number</label>
            <input type="text" id="nic" name="nic" placeholder="42123-4567890-1" maxlength="15"
                pattern="\d{5}-\d{7}-\d{1}"
                title="Enter a valid CNIC number, e.g. 12345-1234567-1"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
                oninput="formatCNIC(this)" required>
        </div>

        <script>
        function formatCNIC(input) {
            let value = input.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 5 && value.length <= 12) {
                input.value = value.slice(0, 5) + '-' + value.slice(5);
            } else if (value.length > 12) {
                input.value = value.slice(0, 5) + '-' + value.slice(5, 12) + '-' + value.slice(12, 13);
            } else {
                input.value = value;
            }
        }
        </script>


        <div>
            <label for="dob" class="block text-gray-700 font-semibold mb-1">Date of Birth</label>
            <input type="date" name="dob" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div class="flex gap-4">
            <button type="reset" class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 rounded-lg transition">Reset</button>
            <button type="submit" class="w-1/2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded-lg transition">Next</button>
        </div>
    </form>

    <p class="text-center mt-6 text-gray-600">
        Already have an account? 
        <a href="login.php" class="text-blue-500 font-semibold hover:underline">Login</a>
    </p>
</div>

</body>
</html>
