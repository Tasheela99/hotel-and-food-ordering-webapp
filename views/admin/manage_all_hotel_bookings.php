<?php
session_start();
include '../../includes/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

// Fetch all hotel bookings
$bookings_sql = "SELECT hb.booking_id, u.name AS customer_name, h.name AS hotel_name, 
                        hb.booking_date, hb.booking_time, hb.status
                 FROM hotel_bookings hb
                 JOIN users u ON hb.user_id = u.user_id
                 JOIN hotels h ON hb.hotel_id = h.hotel_id
                 ORDER BY hb.booking_date DESC, hb.booking_time DESC";
$bookings_result = $conn->query($bookings_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotel Bookings</title>
    <link rel="stylesheet" href="/css/manage_all_hotel_bookings.css">
</head>
<body>
<header>
    <h1>Manage Hotel Bookings</h1>
    <a href="../admin_dashboard.php" class="back-link">Back to Dashboard</a>
</header>
<main>
    <section>
        <h2>All Hotel Bookings</h2>
        <table>
            <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Hotel Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                        <td><?php echo htmlspecialchars($booking['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hotel bookings found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>

<?php $conn->close(); ?>
