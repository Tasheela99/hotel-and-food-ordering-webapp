<?php

session_start();
include '../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch available food items
$food_items_sql = "SELECT * FROM food_items ORDER BY name ASC";
$food_items_result = $conn->query($food_items_sql);

// Fetch available hotels
$hotels_sql = "SELECT * FROM hotels ORDER BY name ASC";
$hotels_result = $conn->query($hotels_sql);

// Fetch available tables (optionally, based on selected hotel)
// For simplicity, fetch all available tables; you can enhance this with JavaScript for dynamic selection
$table_sql = "SELECT * FROM restaurant_tables WHERE is_available = 1 ORDER BY table_number ASC";
$table_result = $conn->query($table_sql);

// Fetch the user's current bookings
$user_bookings_sql = "SELECT tb.booking_id, h.name AS hotel_name, rt.table_number, tb.booking_date, tb.booking_time, tb.description, tb.status
                      FROM table_bookings tb
                      JOIN restaurant_tables rt ON tb.table_id = rt.table_id
                      JOIN hotels h ON tb.hotel_id = h.hotel_id
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
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../css/user_dashboard.css">
</head>
<body>
<header>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>!</h1>
    <nav>
        <a href="../views/user_dashboard.php">Dashboard</a>
        <a href="../views/user/view_cart.php">View Cart</a>
        <a href="../views/user/view_orders.php">View Orders</a>
        <a href="../views/user/add_hotel.php">Add Hotel</a>
        <a href="../views/user/book_hotel.php">Book Hotel</a>
        <a href="../views/user/view_hotel_my_bookings.php">My Hotel Bookings</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<main>
    <!-- Display Booking Messages -->
    <?php
    if (isset($_GET['booking_success'])) {
        echo '<p style="color: green;">Your table has been booked successfully!</p>';
    }

    if (isset($_GET['booking_error'])) {
        if ($_GET['booking_error'] == 1) {
            echo '<p style="color: red;">All required fields are mandatory. Please try again.</p>';
        } elseif ($_GET['booking_error'] == 2) {
            echo '<p style="color: red;">The selected table is not available at the chosen date and time.</p>';
        } else {
            echo '<p style="color: red;">There was an error processing your booking. Please try again.</p>';
        }
    }
    ?>

    <!-- Items Section -->
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

    <!-- Table Booking Section -->
    <section>
        <h3>Plan Your Wedding With Us</h3>
        <h4>Make An Appointment With Us</h4>
        <form method="POST" action="/views/user/book_table.php">
            <label for="hotel_id">Select Hotel:</label>
            <select id="hotel_id" name="hotel_id" required>
                <option value="">-- Select Hotel --</option>
                <?php if ($hotels_result && $hotels_result->num_rows > 0): ?>
                    <?php while ($hotel = $hotels_result->fetch_assoc()): ?>
                        <option value="<?php echo $hotel['hotel_id']; ?>">
                            <?php echo htmlspecialchars($hotel['name']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">No hotels available</option>
                <?php endif; ?>
            </select>

            <label for="table_id">Select Table:</label>
            <select id="table_id" name="table_id" required>
                <option value="">-- Select Table --</option>
                <?php if ($table_result && $table_result->num_rows > 0): ?>
                    <?php while ($table = $table_result->fetch_assoc()): ?>
                        <option value="<?php echo $table['table_id']; ?>">
                            Table <?php echo htmlspecialchars($table['table_number']); ?> (Seats: <?php echo htmlspecialchars($table['capacity']); ?>)
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">No tables available</option>
                <?php endif; ?>
            </select>

            <label for="booking_date">Booking Date:</label>
            <input type="date" id="booking_date" name="booking_date" required>

            <label for="booking_time">Booking Time:</label>
            <input type="time" id="booking_time" name="booking_time" required>

            <label for="description">Description (optional):</label>
            <textarea id="description" name="description" rows="3" placeholder="Any special requests..."></textarea>

            <button type="submit">Book Table</button>
        </form>
    </section>

    <!-- User's Booking Details -->
    <section>
        <h3>Your Bookings</h3>
        <table>
            <thead>
            <tr>
                <th>Booking ID</th>
                <th>Hotel</th>
                <th>Table</th>
                <th>Date</th>
                <th>Time</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($user_bookings_result && $user_bookings_result->num_rows > 0): ?>
                <?php while ($booking = $user_bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                        <td>Table <?php echo htmlspecialchars($booking['table_number']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                        <td><?php echo htmlspecialchars($booking['description']); ?></td>
                        <td><?php echo htmlspecialchars($booking['status']); ?></td>
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
<footer>
    <p>&copy; 2025  dreamplane.com. All rights reserved.</p>
</footer>
</body>
</html>

<?php
$conn->close();
?>
