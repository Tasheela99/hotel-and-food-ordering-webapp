<?php

session_start();
include '../../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Process the booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $table_id = $_POST['table_id'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];

    // Validate inputs (basic example)
    if (empty($table_id) || empty($booking_date) || empty($booking_time)) {
        // Redirect back with an error if any field is empty
        header("Location: user_dashboard.php?booking_error=1");
        exit();
    }

    // Insert the booking into the table_bookings table
    $sql = "INSERT INTO table_bookings (user_id, table_id, booking_date, booking_time, status) VALUES (?, ?, ?, ?, 'PENDING')";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("iiss", $user_id, $table_id, $booking_date, $booking_time);
        if ($stmt->execute()) {
            // Redirect back to the dashboard with a success message
            header("Location: ../user_dashboard.php?booking_success=1");
        } else {
            // Redirect back to the dashboard with an error message
            header("Location: ../user_dashboard.php?booking_error=1");
        }
        $stmt->close();
    } else {
        // Redirect back if the statement failed to prepare
        header("Location: ../user_dashboard.php?booking_error=1");
    }
    $conn->close();
    exit();
}

// Redirect back if accessed without POST
header("Location: ../user_dashboard.php");
exit();
