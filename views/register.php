<?php
session_start();
include 'includes/database.php';
$userSuccess = $userError = $hotelSuccess = $hotelError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_user'])) {
    $name = trim($_POST['user_name']);
    $email = trim($_POST['user_email']);
    $phone = trim($_POST['user_phone']);
    $password = $_POST['user_password'];
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $userError = "All fields are required for user registration.";
    } else {
        $check_sql = "SELECT user_id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $userError = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $insert_sql = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'USER')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
            if ($insert_stmt->execute()) {
                $userSuccess = "Registration successful! <a href='login.php'>Login here</a>.";
            } else {
                $userError = "Error: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_hotel'])) {
    $hotelName = trim($_POST['hotel_name']);
    $hotelEmail = trim($_POST['hotel_email']);
    $hotelPhone = trim($_POST['hotel_phone']);
    $hotelPassword = $_POST['hotel_password'];
    $hotelLocation = trim($_POST['hotel_location']);

    if (empty($hotelName) || empty($hotelEmail) || empty($hotelPhone) || empty($hotelPassword) || empty($hotelLocation)) {
        $hotelError = "All fields are required for hotel registration.";
    } else {
        $check_sql = "SELECT user_id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $hotelEmail);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $hotelError = "Email is already registered.";
        } else {
            $hashed_password = password_hash($hotelPassword, PASSWORD_BCRYPT);
            $insert_sql = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'HOTEL')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $hotelName, $hotelEmail, $hotelPhone, $hashed_password);
            if ($insert_stmt->execute()) {
                $user_id = $insert_stmt->insert_id;
                if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['hotel_image']['tmp_name'];
                    $fileName = $_FILES['hotel_image']['name'];
                    $fileSize = $_FILES['hotel_image']['size'];
                    $fileType = $_FILES['hotel_image']['type'];
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));
                    $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
                    if (in_array($fileExtension, $allowedfileExtensions)) {
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $uploadFileDir = '/uploads/hotels/'; // Absolute path from web root
                        $dest_path = $_SERVER['DOCUMENT_ROOT'] . $uploadFileDir . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $hotelImage = $uploadFileDir . $newFileName; // Store absolute path
                        } else {
                            $hotelImage = NULL;
                        }
                    } else {
                        $hotelImage = NULL;
                    }
                } else {
                    $hotelImage = NULL;
                }
                $insert_hotel_sql = "INSERT INTO hotels (name, location, image, contact, user_id) VALUES (?, ?, ?, ?, ?)";
                $insert_hotel_stmt = $conn->prepare($insert_hotel_sql);
                $insert_hotel_stmt->bind_param("ssssi", $hotelName, $hotelLocation, $hotelImage, $hotelPhone, $user_id);
                if ($insert_hotel_stmt->execute()) {
                    $hotelSuccess = "Hotel registration successful! <a href='login.php'>Login here</a>.";
                } else {
                    $hotelError = "Error: " . $insert_hotel_stmt->error;
                }
                $insert_hotel_stmt->close();
            } else {
                $hotelError = "Error: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/register.css">
</head>
<body>
<div class="register-page">
    <div class="image-container">
        <img src="../assets/images/register.jpg" alt="Register Background">
    </div>
    <div class="form-container">
        <div class="register-content">
            <h2 class="reg-title">Register</h2>
            <div class="tabs">
                <button class="tab-link active" onclick="openTab(event, 'User')">Register as User</button>
                <button class="tab-link" onclick="openTab(event, 'Hotel')">Register as Hotel</button>
            </div>
            <div id="User" class="tab-content active">
                <?php if (!empty($userSuccess)): ?>
                    <p class="success"><?php echo $userSuccess; ?></p>
                <?php endif; ?>
                <?php if (!empty($userError)): ?>
                    <p class="error"><?php echo $userError; ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="user_name" required placeholder="Enter Your Name Here"><br>
                    <input type="email" name="user_email" required placeholder="Enter Your Email Here"><br>
                    <input type="text" name="user_phone" required placeholder="Enter Your Phone Here"><br>
                    <input type="password" name="user_password" required placeholder="Enter Your Password Here"><br>
                    <button type="submit" name="register_user">Register as User</button>
                    <p class="or">OR</p>
                    <button type="button" class="login-btn"><a href='login.php'>Login here</a></button>
                </form>
            </div>
            <div id="Hotel" class="tab-content">
                <?php if (!empty($hotelSuccess)): ?>
                    <p class="success"><?php echo $hotelSuccess; ?></p>
                <?php endif; ?>
                <?php if (!empty($hotelError)): ?>
                    <p class="error"><?php echo $hotelError; ?></p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="hotel_name" required placeholder="Enter Hotel Name Here"><br>
                    <input type="email" name="hotel_email" required placeholder="Enter Hotel Email Here"><br>
                    <input type="text" name="hotel_phone" required placeholder="Enter Hotel Phone Here"><br>
                    <input type="password" name="hotel_password" required placeholder="Enter Your Password Here"><br>
                    <input type="text" name="hotel_location" required placeholder="Enter Hotel Location Here"><br>
                    <input type="file" name="hotel_image" accept="image/*"><br>
                    <button type="submit" name="register_hotel">Register as Hotel</button>
                    <p class="or">OR</p>
                    <button type="button" class="login-btn"><a href='login.php'>Login here</a></button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }
</script>
</body>
</html>
