<?php
session_start();
include '../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch available food items
$food_items_sql = "SELECT * FROM food_items ORDER BY name ASC";
$food_items_result = $conn->query($food_items_sql);

// Fetch available tables
$table_sql = "SELECT * FROM restaurant_tables WHERE is_available = 1";
$table_result = $conn->query($table_sql);

// Fetch the user's current bookings
$user_bookings_sql = "SELECT tb.booking_id, rt.table_number, tb.booking_date, tb.booking_time, tb.status
                      FROM table_bookings tb
                      JOIN restaurant_tables rt ON tb.table_id = rt.table_id
                      WHERE tb.user_id = ?
                      ORDER BY tb.booking_date DESC, tb.booking_time DESC";
$user_bookings_stmt = $conn->prepare($user_bookings_sql);
$user_bookings_stmt->bind_param("i", $user_id);
$user_bookings_stmt->execute();
$user_bookings_result = $user_bookings_stmt->get_result();
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Dashboard</title>
        <link rel="stylesheet" href="../css/user_dashboard.css">
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
        <!-- items section -->
        <section>
            <h3>Available Items</h3>
            <div class="food-grid">
                <?php if ($food_items_result && $food_items_result->num_rows > 0): ?>
                    <?php while ($row = $food_items_result->fetch_assoc()): ?>
                        <div class="food-card">
                            <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                            <p><strong>$<?php echo number_format($row['price'], 2); ?></strong></p>
                            <form method="POST" action="add_to_cart.php">
                                <input type="hidden" name="food_id" value="<?php echo $row['food_id']; ?>">
                                <button type="submit">Add to Cart</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No items available.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Table booking section -->
        <section>
            <h3>Book a Table</h3>
            <form method="POST" action="/views/user/book_table.php">
                <label for="table_id">Select Table:</label>
                <select id="table_id" name="table_id" required>
                    <?php if ($table_result && $table_result->num_rows > 0): ?>
                        <?php while ($table = $table_result->fetch_assoc()): ?>
                            <option value="<?php echo $table['table_id']; ?>">Table <?php echo $table['table_number']; ?> (Seats: <?php echo $table['capacity']; ?>)</option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No tables available</option>
                    <?php endif; ?>
                </select>
                <label for="booking_date">Booking Date:</label>
                <input type="date" id="booking_date" name="booking_date" required>
                <label for="booking_time">Booking Time:</label>
                <input type="time" id="booking_time" name="booking_time" required>
                <button type="submit">Book Table</button>
            </form>
        </section>

        <!-- User's booking details -->
        <section>
            <h3>Your Bookings</h3>
            <table>
                <thead>
                <tr>
                    <th>Table</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($user_bookings_result && $user_bookings_result->num_rows > 0): ?>
                    <?php while ($booking = $user_bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td>Table <?php echo htmlspecialchars($booking['table_number']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No bookings found.</td>
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
