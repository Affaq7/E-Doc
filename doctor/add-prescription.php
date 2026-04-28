<?php
session_start();
include("../connection.php");

if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'd') {
    header("location: ../login.php");
    exit();
}

if ($_POST) {
    $appoid = $_POST['appoid'];
    $pid = $_POST['pid'];
    $docid = $_POST['docid'];
    $scheduleid = $_POST['scheduleid'];
    
    // Get arrays of inputs
    $medications = $_POST['medication'];
    $dosages = $_POST['dosage'];
    $notes_array = $_POST['notes']; // NEW: Array for notes

    // Prepare the statement once (UPDATED to include notes)
    $sql = "INSERT INTO prescriptions (appoid, pid, docid, medication_name, dosage, frequency, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $database->prepare($sql);

    // Loop through the medications array
    foreach ($medications as $index => $med_name) {
        // Skip if medication name is empty
        if(trim($med_name) == "") continue;

        $dose = $dosages[$index];
        $notes = $notes_array[$index]; // Get notes for this index

        // Handle Frequency checkboxes for THIS specific row index (M-E-N format)
        $m = isset($_POST['morning'][$index]) ? '1' : '0';
        $e = isset($_POST['evening'][$index]) ? '1' : '0'; // CHANGED: Noon to Evening
        $n = isset($_POST['night'][$index]) ? '1' : '0';
        
        $frequency = "($m+$e+$n)"; // FORMAT: M+E+N

        // UPDATED bind_param (s for notes)
        $stmt->bind_param("iiissss", $appoid, $pid, $docid, $med_name, $dose, $frequency, $notes); 
        $stmt->execute();
    }

    // Redirect after loop finishes (back to session details view)
    header("Location: schedule.php?action=view&id=".$scheduleid."&msg=success");
}
?>