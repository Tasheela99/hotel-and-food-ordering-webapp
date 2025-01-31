<?php
session_start();
include '../../includes/database.php';

// Check if the user is logged in and is a HOTEL user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'HOTEL') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the hotel owned by this user
$sqlHotel = "SELECT hotel_id FROM hotels WHERE user_id = ?";
$stmt = $conn->prepare($sqlHotel);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultHotel = $stmt->get_result();
$hotel = $resultHotel->fetch_assoc();

if (!$hotel) {
    die("No hotel found for this user.");
}

$hotel_id = $hotel['hotel_id'];

$sqlBookings = "SELECT hb.booking_id, u.name AS user_name, u.phone AS user_mobile, hb.booking_date, hb.booking_time, hb.status 
                FROM hotel_bookings hb
                JOIN users u ON hb.user_id = u.user_id
                WHERE hb.hotel_id = ?
                ORDER BY hb.booking_date ASC, hb.booking_time ASC";


$stmt = $conn->prepare($sqlBookings);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$resultBookings = $stmt->get_result();

// Process status update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];

    // Ensure valid status values
    $valid_status = ['PENDING', 'CONFIRMED', 'CANCELLED'];
    if (in_array($new_status, $valid_status)) {
        $sqlUpdate = "UPDATE hotel_bookings SET status = ? WHERE booking_id = ? AND hotel_id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("sii", $new_status, $booking_id, $hotel_id);

        if ($stmtUpdate->execute()) {
            header("Location: manage_hotel_bookings.php?status_updated=1");
            exit();
        } else {
            header("Location: manage_hotel_bookings.php?status_error=1");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotel Bookings</title>
    <link rel="stylesheet" href="/css/manage_hotel_bookings.css">
</head>
<body>

<!-- Navigation Bar -->
<nav>
    <ul>
        <li><a href="hotel_dashboard.php">Dashboard</a></li>
        <li><a href="manage_hotel_bookings.php">View Bookings</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>Manage Hotel Bookings</h2>

    <!-- Success & Error Messages -->
    <?php if (isset($_GET['status_updated'])): ?>
        <p class="success">Booking status updated successfully!</p>
    <?php elseif (isset($_GET['status_error'])): ?>
        <p class="error">Error updating status. Please try again.</p>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>Guest Name</th>
            <th>Mobile</th>
            <th>Booking Date</th>
            <th>Booking Time</th>
            <th>Status</th>
            <th>Update Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($resultBookings->num_rows > 0): ?>
            <?php while ($row = $resultBookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_mobile']); ?></td>
                    <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>

                    <td>
                        <form method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                            <select name="status">
                                <option value="PENDING" <?php if ($row['status'] === 'PENDING') echo 'selected'; ?>>PENDING</option>
                                <option value="CONFIRMED" <?php if ($row['status'] === 'CONFIRMED') echo 'selected'; ?>>CONFIRMED</option>
                                <option value="CANCELLED" <?php if ($row['status'] === 'CANCELLED') echo 'selected'; ?>>CANCELLED</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No bookings found for your hotel.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php $conn->close(); ?>
