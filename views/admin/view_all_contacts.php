<?php
session_start();
include '../../includes/database.php'; // Corrected path to database.php

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] !== 'ADMIN')) {
    header("Location: ../../login.php"); // Redirect to login page or unauthorized page
    exit();
}

// Fetch all contact messages from the database
$contacts_sql = "SELECT contact_id, name, email, message, created_at FROM contacts ORDER BY created_at DESC";
$contacts_result = $conn->query($contacts_sql);

// Check for query execution errors
if (!$contacts_result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Contacts (Admin)</title>
    <link rel="stylesheet" href="../../css/manage_contacts.css"> <!-- Link to separate CSS file -->
</head>
<body>
<header>
    <h1>Manage Contacts</h1>
    <a href="../admin_dashboard.php">Back to Dashboard</a>

</header>
<main>
    <section>
        <h2>All Contact Submissions</h2>
        <table>
            <thead>
            <tr>
                <th>Contact ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Submitted At</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($contacts_result && $contacts_result->num_rows > 0): ?>
                <?php while ($contact = $contacts_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($contact['contact_id']); ?></td>
                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($contact['message'])); ?></td>
                        <td><?php echo htmlspecialchars($contact['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No contact submissions found.</td>
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
