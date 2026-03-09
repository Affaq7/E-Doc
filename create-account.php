<?php
// 1. Load PHPMailer (Adjust path if you installed via Composer)
// If using manual download, make sure the 'PHPMailer' folder is in the same directory
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();

$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

// Set timezone and current date
date_default_timezone_set('Asia/Kolkata');
$_SESSION["date"] = date('Y-m-d');

// Import database
include("connection.php");

$error = ''; // Initialize error variable

if ($_POST) {
    // Sanitize and escape all input data (Crucial!)
    $fname = $database->real_escape_string($_SESSION['personal']['fname']);
    $lname = $database->real_escape_string($_SESSION['personal']['lname']);
    $name = $fname . " " . $lname;
    $address = $database->real_escape_string($_SESSION['personal']['address']);
    $nic = $database->real_escape_string($_SESSION['personal']['nic']);
    $dob = $database->real_escape_string($_SESSION['personal']['dob']);
    
    $email = $database->real_escape_string($_POST['newemail']);
    $tele_raw = str_replace(' ', '', $_POST['tele']);
    $tele = $database->real_escape_string($tele_raw);
    
    $newpassword = $database->real_escape_string($_POST['newpassword']);
    $cpassword = $database->real_escape_string($_POST['cpassword']);
    
    if ($newpassword == $cpassword) {
        // Check if email exists
        $stmt_check = $database->prepare("SELECT * FROM webuser WHERE email=?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        // FIX: Must close the statement before proceeding with new queries/calls
        $stmt_check->close();

        if ($result->num_rows == 1) {
            $error = 'Already have an account for this Email address.';
        } else {
            
            // ============================================================
            // 🌟 PROCEDURE CALL (Replaces original two INSERT queries) 🌟
            // ============================================================

            $stmt_proc = $database->prepare("CALL create_new_patient_account(?, ?, ?, ?, ?, ?, ?)");
            $stmt_proc->bind_param("ssssssi", $email, $name, $newpassword, $address, $nic, $dob, $tele);
            
            if ($stmt_proc->execute()) {
                // Account created successfully

                // Clear any remaining results from the procedure call before starting a new session
                while($database->more_results() && $database->next_result()) {
                    $database->store_result();
                }

                $_SESSION["user"] = $email;
                $_SESSION["usertype"] = "p";
                $_SESSION["username"] = $fname;

                // ============================================================
                // SEND WELCOME EMAIL LOGIC
                // ============================================================
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'afaqsoomro14079@gmail.com'; // ⚠️ REPLACE
                    $mail->Password   = 'fzqoriqswlyrakfp';         // ⚠️ REPLACE
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    // Recipients
                    $mail->setFrom('no-reply@edoc.com', 'eDoc Administrator');
                    $mail->addAddress($email, $name);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to eDoc - Account Created Successfully';
                    
                    $mailBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h1 style='color: #3b82f6;'>eDoc</h1>
                            <p style='color: #666;'>E-Channeling System</p>
                        </div>
                        <div style='background-color: #f9fafb; padding: 20px; border-radius: 8px;'>
                            <h2 style='color: #1f2937; margin-top: 0;'>Hello, $fname!</h2>
                            <p style='color: #4b5563; line-height: 1.6;'>
                                Welcome to eDoc! We are thrilled to have you on board. Your account has been created successfully.
                            </p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='http://localhost/edoc/login.php' style='background-color: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login to Your Account</a>
                            </div>
                            <p style='color: #6b7280; font-size: 12px; margin-top: 20px;'>
                                Account Details:<br>
                                Email: <strong>$email</strong><br>
                                Phone: <strong>$tele</strong>
                            </p>
                        </div>
                        <div style='text-align: center; margin-top: 20px; color: #9ca3af; font-size: 12px;'>
                            &copy; " . date("Y") . " eDoc System. All rights reserved.
                        </div>
                    </div>
                    ";

                    $mail->Body = $mailBody;
                    $mail->AltBody = "Welcome to eDoc, $fname! Your account has been created successfully.";

                    $mail->send();
                } catch (Exception $e) {
                    // Email logging can go here
                }
                
                header('Location: patient/index.php');
                exit();

            } else {
                // Handle procedure execution failure
                 $error = 'Database Error: Could not create account. Please try again.';
            }
            $stmt_proc->close();
        }
    } else {
        $error = 'Password Confirmation Error! Reconfirm Password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-cyan-50 to-purple-50 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white rounded-xl shadow-xl p-8 animate-slide-in">
        <h1 class="text-3xl font-bold text-gray-900 text-center mb-2">Let's Get Started</h1>
        <p class="text-gray-600 text-center mb-6">It's okay, now create your user account</p>

        <?php if ($error) : ?>
            <p class="text-red-600 text-center mb-4 font-semibold"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="newemail" class="block text-gray-700 font-semibold mb-1">Email</label>
                <input type="email" name="newemail" placeholder="Email Address" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div>
                <label for="tele" class="block text-gray-700 font-semibold mb-1">Mobile Number</label>
                <div class="flex">
                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 rounded-l-lg text-gray-700">+92</span>
                    <input type="tel" id="tele" name="tele" placeholder="3XX XXXXXXX" maxlength="11"
                        pattern="^3\d{2}\s\d{7}$"
                        title="Enter a valid Pakistani mobile number, e.g. 300 1234567"
                        class="flex-1 px-4 py-2 border rounded-r-lg focus:ring-2 focus:ring-blue-400"
                        oninput="formatPakMobile(this)">
                </div>
            </div>

            <script>
            function formatPakMobile(input) {
                let value = input.value.replace(/\D/g, ''); // Remove non-digits
                if (value.length > 3) {
                    input.value = value.slice(0, 3) + ' ' + value.slice(3, 10);
                } else {
                    input.value = value;
                }
            }
            </script>

            <div>
                <label for="newpassword" class="block text-gray-700 font-semibold mb-1">Create New Password</label>
                <input type="password" name="newpassword" placeholder="New Password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div>
                <label for="cpassword" class="block text-gray-700 font-semibold mb-1">Confirm Password</label>
                <input type="password" name="cpassword" placeholder="Confirm Password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
            </div>

            <div class="flex gap-4">
                <button type="reset" class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 rounded-lg transition">Reset</button>
                <button type="submit" class="w-1/2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded-lg transition">Sign Up</button>
            </div>
        </form>

        <p class="text-center mt-6 text-gray-600">
            Already have an account? 
            <a href="login.php" class="text-blue-500 font-semibold hover:underline">Login</a>
        </p>
    </div>
</body>
</html>