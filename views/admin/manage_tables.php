<?php
session_start();
include '../../includes/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}
$success = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_number = $_POST['table_number'];
    $capacity = $_POST['capacity'];

    // Validate inputs
    if (!empty($table_number) && !empty($capacity) && is_numeric($capacity)) {
        $sql = "INSERT INTO restaurant_tables (table_number, capacity) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ii", $table_number, $capacity);
            if ($stmt->execute()) {
                $success = "Table added successfully.";
            } else {
                $error = "Error adding table: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Error preparing statement: " . $conn->error;
        }
    } else {
        $error = "Please provide valid inputs.";
    }
}

$sql = "SELECT * FROM restaurant_tables ORDER BY table_number";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tables</title>
    <link rel="stylesheet" href="/css/manage_tables.css">
</head>
<body>
<header>
    <h1>Manage Appointments</h1>
    <nav>
        <a href="/views/admin_dashboard.php">Dashboard</a>
    </nav>
</header>

<main>
    <section>
        <h2>Add Tables For The Appointment</h2>
        <?php if ($success) echo "<p class='success'>$success</p>"; ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label for="table_number">Table Number:</label>
            <input type="number" id="table_number" name="table_number" required><br>

            <label for="capacity">Capacity:</label>
            <input type="number" id="capacity" name="capacity" required><br>

            <button type="submit">Add Table</button>
        </form>
    </section>

    <section>
        <h2>Existing Tables</h2>
        <table>
            <thead>
            <tr>
                <th>Table ID</th>
                <th>Table Number</th>
                <th>Capacity</th>
                <th>Availability</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['table_id']; ?></td>
                        <td><?php echo $row['table_number']; ?></td>
                        <td><?php echo $row['capacity']; ?></td>
                        <td><?php echo $row['is_available'] ? 'Available' : 'Unavailable'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No tables found.</td>
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
