<?php
session_start();
include '../../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch items in the cart and calculate total price
$sql = "SELECT c.food_id, c.quantity, f.price, f.name 
        FROM cart c 
        JOIN food_items f ON c.food_id = f.food_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;

while ($row = $result->fetch_assoc()) {
    $total_price += $row['price'] * $row['quantity'];
    $cart_items[] = $row;
}

$stmt->close();

// If the cart has items (total_price > 0), process the order
if ($total_price > 0) {
    // 1. Insert a new order
    $order_sql = "INSERT INTO orders (user_id, total_amount, status) 
                  VALUES (?, ?, 'COMPLETED')";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("id", $user_id, $total_price);
    $order_stmt->execute();
    $order_id = $order_stmt->insert_id;
    $order_stmt->close();

    // 2. Insert order details for each cart item
    $order_details_sql = "INSERT INTO order_details (order_id, food_id, quantity, price) 
                          VALUES (?, ?, ?, ?)";
    $order_details_stmt = $conn->prepare($order_details_sql);

    foreach ($cart_items as $item) {
        $order_details_stmt->bind_param(
            "iiid",
            $order_id,
            $item['food_id'],
            $item['quantity'],
            $item['price']
        );
        $order_details_stmt->execute();
    }
    $order_details_stmt->close();

    // 3. Insert payment record (assuming CASH payment)
    $payment_sql = "INSERT INTO payments (order_id, amount, payment_method, status) 
                    VALUES (?, ?, 'CASH', 'COMPLETED')";
    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param("id", $order_id, $total_price);
    $payment_stmt->execute();
    $payment_stmt->close();

    // 4. Clear the cart
    $delete_sql = "DELETE FROM cart WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // 5. Redirect to the payment confirmation page
    header("Location: payment_confirmation.php?order_id=$order_id");
    exit();
}

// If the code reaches here, it means the cart is empty.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empty Cart</title>
    <link rel="stylesheet" href="../../css/payment_out.css">
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
        <h2>Your cart is empty</h2>
        <p>Please add items to your cart before proceeding to payment.</p>
        <a href="/views/user_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
</main>

</body>
</html>

<?php
$conn->close();
?>
