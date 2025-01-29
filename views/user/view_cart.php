<?php
session_start();
include '../../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$sql = "SELECT c.cart_id, f.name, f.price, c.quantity, (f.price * c.quantity) AS total 
        FROM cart c 
        JOIN food_items f ON c.food_id = f.food_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Cart</title>
    <link rel="stylesheet" href="../../css/view_cart.css">
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
        <h2>Your Cart</h2>

        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php $total_price += $row['total']; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo (int)$row['quantity']; ?></td>
                        <td>$<?php echo number_format($row['total'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Your cart is empty.</td>
                </tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th>$<?php echo number_format($total_price, 2); ?></th>
            </tr>
            </tfoot>
        </table>

        <form method="POST" action="payment.php">
            <button type="submit">Proceed to Payment</button>
        </form>
    </div>
</main>

<?php $conn->close(); ?>
</body>
</html>
