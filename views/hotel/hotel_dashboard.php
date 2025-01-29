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

// Fetch hotel bookings
$sqlBookings = "SELECT hb.booking_id, u.name AS user_name, hb.booking_date, hb.booking_time, hb.status 
                FROM hotel_bookings hb
                JOIN users u ON hb.user_id = u.user_id
                WHERE hb.hotel_id = ?
                ORDER BY hb.booking_date ASC, hb.booking_time ASC";

$stmt = $conn->prepare($sqlBookings);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$resultBookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Dashboard</title>
    <link rel="stylesheet" href="/css/hotel_dashboard.css">
</head>
<body>
<header>
    <h1>Welcome to the Hotel Dashboard</h1>
</header>
<nav>
    <ul>
        <li><a href="../hotel/manage_hotel.php">Manage Hotel</a></li>
        <li><a href="../hotel/manage_hotel_bookings.php">Manage Hotel Bookings</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>
<main>
    <h2>Hello, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
    <p>Below is a list of bookings for your hotel.</p>

    <div class="container">
        <h3>Hotel Bookings</h3>
        <table>
            <thead>
            <tr>
                <th>Guest Name</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($resultBookings->num_rows > 0): ?>
                <?php while ($row = $resultBookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No bookings found for your hotel.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<footer>
    <p>&copy; 2025 Restaurant Management System</p>
</footer>
</body>
</html>

<?php $conn->close(); ?>
