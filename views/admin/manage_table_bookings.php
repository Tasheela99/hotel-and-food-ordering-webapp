<?php
session_start();
include '../../includes/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    // Update the status in the database
    $update_sql = "UPDATE table_bookings SET status = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all bookings
$bookings_sql = "SELECT tb.booking_id, u.name AS customer_name, rt.table_number, tb.booking_date, tb.booking_time, tb.status
                 FROM table_bookings tb
                 JOIN users u ON tb.user_id = u.user_id
                 JOIN restaurant_tables rt ON tb.table_id = rt.table_id
                 ORDER BY tb.booking_date DESC, tb.booking_time DESC";
$bookings_result = $conn->query($bookings_sql);
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Table Bookings</title>
        <link rel="stylesheet" href="../../css/manage_table_bookings.css">
    </head>
    <body>
    <header>
        <h1>Manage Table Bookings</h1>
        <a href="../admin_dashboard.php">Back to Dashboard</a>
    </header>
    <main>
        <section>
            <h2>Table Bookings</h2>
            <table>
                <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Table</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
                    <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                            <td>Table <?php echo htmlspecialchars($booking['table_number']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['status']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <select name="status" required>
                                        <option value="PENDING" <?php echo $booking['status'] === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="CONFIRMED" <?php echo $booking['status'] === 'CONFIRMED' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="CANCELLED" <?php echo $booking['status'] === 'CANCELLED' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No bookings found.</td>
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
