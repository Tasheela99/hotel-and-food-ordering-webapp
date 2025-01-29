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

// Fetch order details
$order_sql = "
    SELECT 
        o.order_id, 
        o.total_amount, 
        o.order_date, 
        p.status AS payment_status, 
        od.food_id, 
        od.quantity, 
        od.price, 
        f.name
    FROM orders o
    JOIN payments p ON o.order_id = p.order_id
    JOIN order_details od ON o.order_id = od.order_id
    JOIN food_items f ON od.food_id = f.food_id
    WHERE o.order_id = ?
";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows > 0) {
    $order = [];
    while ($row = $order_result->fetch_assoc()) {
        // Store basic order info once
        $order['info'] = [
            'order_id'      => $row['order_id'],
            'total_amount'  => $row['total_amount'],
            'order_date'    => $row['order_date'],
            'payment_status'=> $row['payment_status']
        ];
        // Push each item into 'items'
        $order['items'][] = [
            'name'      => $row['name'],
            'quantity'  => $row['quantity'],
            'price'     => $row['price']
        ];
    }
} else {
    echo "Order not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="../../css/payment.css">
</head>
<body>

<!-- Consistent Header -->
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
        <h2>Order Confirmation</h2>
        <section class="order-info">
            <h3>Order Details</h3>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['info']['order_id']); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($order['info']['total_amount'], 2); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['info']['order_date']); ?></p>
            <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($order['info']['payment_status']); ?></p>
        </section>

        <section class="items">
            <h3>Items Ordered</h3>
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($order['items'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Track Order Button -->
        <section class="track-order-section">
            <a href="order_tracking.php?order_id=<?php echo $order['info']['order_id']; ?>" class="track-order-button">
                Track Order
            </a>
        </section>
    </div>
</main>

<footer>
    <p>&copy; 2025 Restaurant Management System. All rights reserved.</p>
</footer>

<?php $conn->close(); ?>
</body>
</html>
