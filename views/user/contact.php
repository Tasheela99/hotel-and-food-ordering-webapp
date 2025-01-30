<?php
session_start();
include 'includes/database.php';  // adjust path if needed

// Make sure this only runs on POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form fields
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Basic validation
    if (!empty($name) && !empty($email) && !empty($message)) {
        // Prepare insert statement
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            // Redirect back with success parameter
            header("Location: index.php?msg=success");
            exit;
        } else {
            // Redirect back with error parameter
            header("Location: index.php?msg=error");
            exit;
        }
    } else {
        // Incomplete form data
        header("Location: index.php?msg=error");
        exit;
    }
}
?>
