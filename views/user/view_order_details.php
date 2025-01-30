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

// Fetch the order items
$order_details_sql = "
    SELECT od.food_id, f.name, od.quantity, od.price, 
           (od.quantity * od.price) AS total
    FROM order_details od
    JOIN food_items f ON od.food_id = f.food_id
    WHERE od.order_id = ?
";
$order_details_stmt = $conn->prepare($order_details_sql);
$order_details_stmt->bind_param("i", $order_id);
$order_details_stmt->execute();
$order_details_result = $order_details_stmt->get_result();

// Fetch order and payment info
$order_info_sql = "
    SELECT o.total_amount, o.order_date, p.status AS payment_status
    FROM orders o
    JOIN payments p ON o.order_id = p.order_id
    WHERE o.order_id = ?
";
$order_info_stmt = $conn->prepare($order_info_sql);
$order_info_stmt->bind_param("i", $order_id);
$order_info_stmt->execute();
$order_info_result = $order_info_stmt->get_result();
$order_info = $order_info_result->fetch_assoc();

// If no result, handle gracefully
if (!$order_info) {
    echo "Order not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="/css/view_order_details.css">
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
        <h2>Order Summary</h2>
        <section class="order-info">
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($order_info['total_amount'], 2); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order_info['order_date']); ?></p>
            <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($order_info['payment_status']); ?></p>
        </section>

        <section class="items-ordered">
            <h3>Items Ordered</h3>
            <table>
                <thead>
                <tr>
                    <th>Food Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $order_details_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo (int)$row['quantity']; ?></td>
                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                        <td>$<?php echo number_format($row['total'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Track Order Button -->
        <section class="track-order">
            <form method="GET" action="order_tracking.php">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                <button type="submit">Track Order</button>
            </form>
        </section>
    </div>
</main>

<footer>
    <p>&copy; 2025  dreamplane.com. All rights reserved.</p>
</footer>

<?php
$conn->close();
?>
</body>
</html>
