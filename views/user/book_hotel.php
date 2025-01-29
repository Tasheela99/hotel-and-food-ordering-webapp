<?php
session_start();
include '../../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch available hotels
$sqlHotels = "SELECT * FROM hotels ORDER BY name ASC";
$resultHotels = $conn->query($sqlHotels);

// Process the booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $hotel_id = $_POST['hotel_id'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $number_of_people = $_POST['number_of_people']; // <-- New field

    // Validate inputs
    if (empty($hotel_id) || empty($booking_date) || empty($booking_time) || empty($number_of_people)) {
        header("Location: book_hotel.php?hotel_booking_error=1");
        exit();
    }

    // Insert the booking into the hotel_bookings table
    $sql = "INSERT INTO hotel_bookings (user_id, hotel_id, booking_date, booking_time, number_of_people, status) 
            VALUES (?, ?, ?, ?, ?, 'PENDING')";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the parameters: i = integer, s = string
        $stmt->bind_param("iissi", $user_id, $hotel_id, $booking_date, $booking_time, $number_of_people);
        if ($stmt->execute()) {
            header("Location: book_hotel.php?hotel_booking_success=1");
        } else {
            header("Location: book_hotel.php?hotel_booking_error=1");
        }
        $stmt->close();
    } else {
        header("Location: book_hotel.php?hotel_booking_error=1");
    }
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Hotel</title>
    <link rel="stylesheet" href="../../css/book_hotel.css">
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
        <h2>Book a Hotel</h2>

        <!-- Success & Error Messages -->
        <?php if (isset($_GET['hotel_booking_success'])): ?>
            <p class="success">Hotel booking successful!</p>
        <?php elseif (isset($_GET['hotel_booking_error'])): ?>
            <p class="error">Error booking hotel. Please try again.</p>
        <?php endif; ?>

        <!-- Booking Form -->
        <form method="POST" action="book_hotel.php">
            <label for="hotel_id">Select Hotel:</label>
            <select id="hotel_id" name="hotel_id" required>
                <option value="">-- Select a Hotel --</option>
                <?php while ($row = $resultHotels->fetch_assoc()): ?>
                    <option value="<?php echo $row['hotel_id']; ?>">
                        <?php echo htmlspecialchars($row['name']) . " - " . htmlspecialchars($row['location']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="booking_date">Booking Date:</label>
            <input type="date" id="booking_date" name="booking_date" required>

            <label for="booking_time">Booking Time:</label>
            <input type="time" id="booking_time" name="booking_time" required>

            <!-- Number of People -->
            <label for="number_of_people">Number of People:</label>
            <input type="number" id="number_of_people" name="number_of_people" min="1" required>

            <button type="submit">Book Hotel</button>
        </form>
    </div>
</main>

</body>
</html>

<?php $conn->close(); ?>
