<?php

session_start();
include '../../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Process the booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $hotel_id = $_POST['hotel_id'];
    $table_id = $_POST['table_id'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Validate inputs (basic example)
    if (empty($hotel_id) || empty($table_id) || empty($booking_date) || empty($booking_time)) {
        // Redirect back with an error if any required field is empty
        header("Location: ../user_dashboard.php?booking_error=1");
        exit();
    }

    // Optional: Further validation (e.g., check date format, time format)

    // Check for existing bookings to prevent double booking
    $availability_sql = "SELECT * FROM table_bookings 
                         WHERE table_id = ? AND booking_date = ? AND booking_time = ? AND status != 'CANCELLED'";
    $availability_stmt = $conn->prepare($availability_sql);
    $availability_stmt->bind_param("iss", $table_id, $booking_date, $booking_time);
    $availability_stmt->execute();
    $availability_result = $availability_stmt->get_result();

    if ($availability_result->num_rows > 0) {
        // Table is already booked for this date and time
        $availability_stmt->close();
        header("Location: ../user_dashboard.php?booking_error=2"); // Error code 2: Table not available
        exit();
    }

    $availability_stmt->close();

    // Insert the booking into the table_bookings table
    $sql = "INSERT INTO table_bookings (user_id, table_id, hotel_id, booking_date, booking_time, description, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'PENDING')";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("iiisss", $user_id, $table_id, $hotel_id, $booking_date, $booking_time, $description);
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
?>
