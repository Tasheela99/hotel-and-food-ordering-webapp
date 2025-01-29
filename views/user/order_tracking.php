<?php
session_start();
include '../../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure order_id is provided
if (!isset($_GET['order_id'])) {
    echo "Order ID is required.";
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order tracking details
$order_tracking_sql = "
    SELECT ot.status, ot.updated_at 
    FROM order_tracking ot 
    WHERE ot.order_id = ? 
    ORDER BY ot.updated_at DESC
";
$order_tracking_stmt = $conn->prepare($order_tracking_sql);
$order_tracking_stmt->bind_param("i", $order_id);
$order_tracking_stmt->execute();
$order_tracking_result = $order_tracking_stmt->get_result();
$order_tracking_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking</title>
    <link rel="stylesheet" href="/css/order_tracking.css">
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
        <h2>Order Tracking</h2>
        <section>
            <h3>Your Order Status</h3>
            <table>
                <thead>
                <tr>
                    <th>Status</th>
                    <th>Updated At</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($order_tracking_result && $order_tracking_result->num_rows > 0): ?>
                    <?php while ($row = $order_tracking_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No tracking information found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</main>

<footer>
    <p>&copy; 2025 Restaurant Management System. All rights reserved.</p>
</footer>

<?php $conn->close(); ?>
</body>
</html>
