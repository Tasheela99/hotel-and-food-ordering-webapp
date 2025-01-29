<?php
session_start();
include '../../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user_id from session
$user_id = $_SESSION['user_id'];

// Fetch the user's hotel bookings from the database
$sql = "
    SELECT hb.*, h.name AS hotel_name, h.location AS hotel_location
    FROM hotel_bookings hb
    JOIN hotels h ON hb.hotel_id = h.hotel_id
    WHERE hb.user_id = ?
    ORDER BY hb.booking_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Hotel Bookings</title>
    <link rel="stylesheet" href="../../css/view_my_hotel_bookings.css">
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
    <div class="container">
        <h2>My Hotel Bookings</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                <tr>
                    <th>Hotel Name</th>
                    <th>Location</th>
                    <th>Booking Date</th>
                    <th>Booking Time</th>
                    <th># of People</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['hotel_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['hotel_location']); ?></td>
                        <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_of_people']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no hotel bookings at the moment.</p>
        <?php endif; ?>
    </div>
</main>

<?php $conn->close(); ?>
</body>
</html>
