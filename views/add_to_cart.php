<?php
session_start();
include '../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['food_id'])) {
    $food_id = $_POST['food_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the item is already in the cart
    $check_sql = "SELECT * FROM cart WHERE user_id = ? AND food_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $food_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if already in cart
        $update_sql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND food_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $user_id, $food_id);
        $update_stmt->execute();
    } else {
        // Add a new item to the cart
        $insert_sql = "INSERT INTO cart (user_id, food_id, quantity) VALUES (?, ?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $food_id);
        $insert_stmt->execute();
    }

    header("Location: user_dashboard.php");
    exit();
}
?>
