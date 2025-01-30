<?php
session_start();
include '../../includes/database.php';

// Ensure only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; // Current user role

// Check if the user already owns a hotel
$checkHotelSql = "SELECT * FROM hotels WHERE user_id = ?";
$checkStmt = $conn->prepare($checkHotelSql);
$checkStmt->bind_param("i", $user_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$userHasHotel = ($checkResult->num_rows > 0);
$checkStmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$userHasHotel) {
    $hotel_name = $_POST['hotel_name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];

    $imagePath = null; // Default if no image is uploaded

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageExtension, $allowedExtensions)) {
            $uploadDir = '../../uploads/hotels/'; // Ensure correct directory
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $uniqueFileName = uniqid() . '.' . $imageExtension;
            $imagePath = $uploadDir . $uniqueFileName; // Absolute path

            if (!move_uploaded_file($imageTmpPath, $imagePath)) {
                $error = "Failed to upload image.";
            } else {
                // Save relative path
                $imagePath = 'uploads/hotels/' . $uniqueFileName;
            }
        } else {
            $error = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }

    }

    // Insert hotel into database
    if (empty($error)) {
        $conn->begin_transaction(); // Start transaction

        try {
            $sql = "INSERT INTO hotels (name, location, contact, image, user_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $hotel_name, $location, $contact, $imagePath, $user_id);

            if ($stmt->execute()) {
                $success = "Hotel added successfully.";

                // Update role to HOTEL if the user is still a USER
                if ($user_role === 'USER') {
                    $updateRoleSql = "UPDATE users SET role = 'HOTEL' WHERE user_id = ?";
                    $updateStmt = $conn->prepare($updateRoleSql);
                    $updateStmt->bind_param("i", $user_id);
                    if ($updateStmt->execute()) {
                        $_SESSION['role'] = 'HOTEL'; // Update session role
                    }
                    $updateStmt->close();
                }
            } else {
                throw new Exception("Error adding hotel: " . $stmt->error);
            }

            $stmt->close();
            $conn->commit(); // Commit transaction
        } catch (Exception $e) {
            $conn->rollback(); // Rollback in case of failure
            $error = $e->getMessage();
        }
    }
}

// Fetch the user's hotel
$sql = "SELECT * FROM hotels WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userHotel = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotel</title>
    <link rel="stylesheet" href="/css/add_hotels.css">
</head>
<body>
<header>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>!</h1>
    <nav>
        <a href="/views/user_dashboard.php">Dashboard</a> |
        <a href="/views/user/view_cart.php">View Cart</a> |
        <a href="/views/user/view_orders.php">View Orders</a> |
        <a href="/views/user/add_hotel.php">Add Hotel</a> |
        <a href="/views/user/book_hotel.php">Book Hotel</a> |
        <a href="/views/user/view_hotel_my_bookings.php">My Hotel Bookings</a> |
        <a href="/views/logout.php">Logout</a>
    </nav>
</header>

<main>
    <section>
        <h2>Add Hotel</h2>
        <?php if ($success) echo "<p class='success'>$success</p>"; ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>

        <?php if ($userHasHotel): ?>
            <p class="info">You have already added a hotel.</p>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <label for="hotel_name">Hotel Name:</label>
                <input type="text" id="hotel_name" name="hotel_name" required><br>

                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required><br>

                <label for="contact">Contact Number:</label>
                <input type="text" id="contact" name="contact" required><br>

                <label for="image">Hotel Image:</label>
                <input type="file" id="image" name="image" accept="image/*"><br>

                <button type="submit">Add Hotel</button>
            </form>
        <?php endif; ?>
    </section>

    <section>
        <h2>Your Hotel</h2>
        <?php if ($userHotel): ?>
            <table>
                <tr>
                    <th>Name</th>
                    <td><?php echo htmlspecialchars($userHotel['name']); ?></td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td><?php echo htmlspecialchars($userHotel['location']); ?></td>
                </tr>
                <tr>
                    <th>Contact</th>
                    <td><?php echo htmlspecialchars($userHotel['contact']); ?></td>
                </tr>
                <tr>
                    <th>Image</th>
                    <td>
                        <?php if (!empty($userHotel['image'])): ?>
                            <img src="<?php echo htmlspecialchars($userHotel['image']); ?>" width="100">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        <?php else: ?>
            <p>No hotel found.</p>
        <?php endif; ?>
    </section>
</main>

</body>
</html>

<?php
$conn->close();
?>
