<?php
session_start();
include '../../includes/database.php';

// Check if the user is logged in and has the HOTEL role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'HOTEL') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the hotel owned by this user
$sqlHotel = "SELECT * FROM hotels WHERE user_id = ?";
$stmt = $conn->prepare($sqlHotel);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultHotel = $stmt->get_result();
$hotel = $resultHotel->fetch_assoc();

if (!$hotel) {
    die("No hotel found for this user.");
}

$hotel_id = $hotel['hotel_id'];
$success = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotel_name = $_POST['hotel_name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageExtension, $allowedExtensions)) {
            $uploadDir = '../uploads/hotels/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $imagePath = $uploadDir . uniqid() . '.' . $imageExtension;

            if (move_uploaded_file($imageTmpPath, $imagePath)) {
                // Delete old image
                if (!empty($hotel['image']) && file_exists($hotel['image'])) {
                    unlink($hotel['image']);
                }
                // Update hotel with new image
                $sqlUpdate = "UPDATE hotels SET name = ?, location = ?, contact = ?, image = ? WHERE hotel_id = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("ssssi", $hotel_name, $location, $contact, $imagePath, $hotel_id);
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    } else {
        // Update hotel without changing image
        $sqlUpdate = "UPDATE hotels SET name = ?, location = ?, contact = ? WHERE hotel_id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("sssi", $hotel_name, $location, $contact, $hotel_id);
    }

    // Execute update statement
    if (isset($stmtUpdate) && $stmtUpdate->execute()) {
        $success = "Hotel details updated successfully.";
        // Refresh hotel details after update
        header("Location: manage_hotel.php?update_success=1");
        exit();
    } else {
        $error = "Error updating hotel details: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotel</title>
    <link rel="stylesheet" href="/css/manage_hotel.css">
</head>
<body>
<header>
    <h1>Manage Your Hotel</h1>
</header>
<nav>
    <ul>
        <li><a href="hotel_dashboard.php">Dashboard</a></li>
        <li><a href="manage_hotel_bookings.php">View Bookings</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>
<main>
    <h2>Edit Your Hotel Details</h2>

    <!-- Success & Error Messages -->
    <?php if (isset($_GET['update_success'])): ?>
        <p class="success">Hotel details updated successfully!</p>
    <?php elseif (!empty($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="hotel_name">Hotel Name:</label>
        <input type="text" id="hotel_name" name="hotel_name" value="<?php echo htmlspecialchars($hotel['name']); ?>" required><br>

        <label for="location">Location:</label>
        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($hotel['location']); ?>" required><br>

        <label for="contact">Contact Number:</label>
        <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($hotel['contact']); ?>" required><br>

        <label for="image">Hotel Image:</label>
        <input type="file" id="image" name="image" accept="image/*"><br>

        <?php if (!empty($hotel['image'])): ?>
            <p>Current Image:</p>
            <img src="<?php echo htmlspecialchars($hotel['image']); ?>" width="100">
        <?php endif; ?>

        <button type="submit">Update Hotel</button>
    </form>
</main>
<footer>
    <p>&copy; 2025 Restaurant Management System</p>
</footer>
</body>
</html>

<?php $conn->close(); ?>
