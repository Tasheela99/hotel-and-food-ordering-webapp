<?php
session_start();
include '../includes/database.php'; // Update with your actual database connection file path

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

// Handle user deletion if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = (int) $_POST['delete_user_id'];

    // Safety check: Ensure we're not deleting the currently logged-in admin
    // (Optional) If you want to allow self-deletion, remove this check
    if ($delete_user_id === (int) $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        // Perform the deletion
        $delete_sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $delete_user_id);

        if ($stmt->execute()) {
            $success = "User with ID #{$delete_user_id} deleted successfully.";
        } else {
            $error = "Error deleting user: " . $conn->error;
        }

        $stmt->close();
    }
}

// Fetch all users
$users_sql = "SELECT user_id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Make sure the path to your CSS file is correct -->
    <link rel="stylesheet" href="/css/admin_dashboard.css">
</head>
<body>
<header>
    <h1>Welcome to the Admin Dashboard</h1>
</header>

<!-- Navigation -->
<nav>
    <ul>
        <li><a href="#">Manage Users</a></li>
        <li><a href="/views/admin/manage_table_bookings.php">Manage Table Bookings</a></li>
        <li><a href="/views/admin/manage_order_tracking.php">Manage Orders</a></li>
        <li><a href="/views/admin/manage_tables.php">Manage Tables</a></li>
        <li><a href="/views/admin/manage_food_items.php">Manage Food Items</a></li>
        <li><a href="/views/admin/manage_all_hotel_bookings.php">Hotel Bookings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<!-- Main Content -->
<main>
    <h2>Hello, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
    <p>This is your admin dashboard.</p>

    <!-- Display success or error messages for user deletion -->
    <?php if (isset($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <!-- Section to Show All Users -->
    <section>
        <h2>All Registered Users</h2>
        <table>
            <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($users_result && $users_result->num_rows > 0): ?>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="delete_user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No users found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<!-- Footer -->
<footer>
    <p>&copy; 2025 Restaurant Management System</p>
</footer>

</body>
</html>
<?php
$conn->close();
?>
