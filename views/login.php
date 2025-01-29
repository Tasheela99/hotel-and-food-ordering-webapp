<?php
session_start();
include '../includes/database.php'; // Ensure the correct path

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL query to fetch user data
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the hashed password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on user role
                switch ($user['role']) {
                    case 'ADMIN':
                        header("Location: ../views/admin_dashboard.php");
                        break;
                    case 'HOTEL':
                        header("Location: ../views/hotel/hotel_dashboard.php");
                        break;
                    default:
                        header("Location: ../views/user_dashboard.php");
                        break;
                }
                exit(); // Stop further script execution
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No user found with this email.";
        }

        $stmt->close();
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Link to your updated CSS file -->
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="login-page">
    <!-- Image / Left Side -->
    <div class="image-container">
        <!-- Replace the src with your actual image path -->
        <img src="../assets/images/login.jpg" alt="Login Background">
    </div>

    <!-- Form / Right Side -->
    <div class="form-container">
        <div class="login-content">
            <h2>Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <input type="email" name="email" required placeholder="Enter Email Here"><br>
                <input type="password" name="password" required placeholder="Enter Password Here"><br>
                <button type="submit">Login</button>
                <p class="or">OR</p>
                <button class="register-btn">
                    <a href="register.php">Register</a>
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
