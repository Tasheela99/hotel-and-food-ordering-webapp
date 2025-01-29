<?php
session_start();
include '../../includes/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

// Fetch all orders with their current status
$order_tracking_sql = "SELECT o.order_id, u.name AS customer_name, ot.status, ot.updated_at 
                       FROM orders o
                       JOIN users u ON o.user_id = u.user_id
                       LEFT JOIN order_tracking ot ON o.order_id = ot.order_id
                       ORDER BY ot.updated_at DESC";
$order_tracking_result = $conn->query($order_tracking_sql);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the status in the order_tracking table
    $update_sql = "INSERT INTO order_tracking (order_id, status) VALUES (?, ?)";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("is", $order_id, $status);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_order_tracking.php?status_updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Order Tracking</title>
    <link rel="stylesheet" href="../../css/manage_order_tracking.css">
</head>
<body>
<header>
    <h1>Manage Order Tracking</h1>
    <a href="../admin_dashboard.php">Back to Dashboard</a> |
</header>
<main>
    <section>
        <h2>All Orders</h2>
        <table>
            <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Status</th>
                <th>Updated At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($order_tracking_result && $order_tracking_result->num_rows > 0): ?>
                <?php while ($row = $order_tracking_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <select name="status" required>
                                    <option value="PROCESSING" <?php echo $row['status'] === 'PROCESSING' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="SHIPPED" <?php echo $row['status'] === 'SHIPPED' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="DELIVERED" <?php echo $row['status'] === 'DELIVERED' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="CANCELLED" <?php echo $row['status'] === 'CANCELLED' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit">Update Status</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No orders found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>

<?php
$conn->close();
?>
