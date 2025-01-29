<?php
session_start();
include '../includes/database.php'; // Ensure the correct path to your database connection

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    // Hash the password
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Prepare and execute the SQL insert
    $sql = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $phone, $password);
        if ($stmt->execute()) {
            $success = "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Link to your updated CSS file -->
    <link rel="stylesheet" href="/css/register.css">
</head>
<body>
<div class="register-page">
    <!-- Image / Left Side -->
    <div class="image-container">
        <!-- Replace this path with your own image -->
        <img src="../assets/images/register.jpg" alt="Register Background">
    </div>

    <!-- Form / Right Side -->
    <div class="form-container">
        <div class="register-content">
            <h2 class="reg-title">Register</h2>

            <!-- Display success or error messages -->
            <?php if (isset($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="name" required placeholder="Enter Your Name Here"><br>
                <input type="email" name="email" required placeholder="Enter Your Email Here"><br>
                <input type="text" name="phone" required placeholder="Enter Your Phone Here"><br>
                <input type="password" name="password" required placeholder="Enter Your Password Here"><br>

                <button type="submit">Register</button>
                <p class="or">OR</p>
                <button class="login-btn">
                    <a href="login.php">Login</a>
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
