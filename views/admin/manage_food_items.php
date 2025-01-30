<?php
session_start();
include '../../includes/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['form_submitted'])) {
    $_SESSION['form_submitted'] = true; // Prevent resubmission on refresh

    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageExtension, $allowedExtensions)) {
            $uploadDir = '../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create directory if not exists
            }
            $uploadPath = $uploadDir . uniqid() . '.' . $imageExtension;

            if (move_uploaded_file($imageTmpPath, $uploadPath)) {
                $imagePath = $uploadPath;

                // Insert into database
                $sql = "INSERT INTO food_items (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("ssdss", $name, $description, $price, $category, $imagePath);
                    if ($stmt->execute()) {
                        $success = "Food item added successfully.";
                    } else {
                        $error = "Error adding food item: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    } else {
        $error = "Please select a valid image file.";
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];

    // Fetch the image path from the database
    $sql = "SELECT image FROM food_items WHERE food_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = $row['image'];

        // Delete the image file
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Delete the record from the database
        $sql = "DELETE FROM food_items WHERE food_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            $success = "Food item deleted successfully.";
        } else {
            $error = "Error deleting food item: " . $stmt->error;
        }
    } else {
        $error = "Food item not found.";
    }

    $stmt->close();
}

// Fetch food items from the database
$sql = "SELECT * FROM food_items ORDER BY name ASC";
$result = $conn->query($sql);
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Item</title>
        <link rel="stylesheet" href="/css/add_food_item.css">
    </head>
    <body>
    <header>
        <h1>Add Item</h1>
        <nav>
            <a href="/views/admin_dashboard.php">Dashboard</a>
        </nav>
    </header>
    <main style="display: flex; gap: 20px;">
        <section style="flex: 1;">
            <h2>Add New Item</h2>
            <?php if ($success) echo "<p class='success'>$success</p>"; ?>
            <?php if ($error) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" enctype="multipart/form-data">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea><br>

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" required><br>

                <label for="category">Category:</label>
                <input type="text" id="category" name="category" required><br>

                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*" required><br>

                <button type="submit">Add Item</button>
            </form>
        </section>

        <section style="flex: 2;">
            <h2>Available Items</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['food_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($row['image']); ?>"
                                     alt="<?php echo htmlspecialchars($row['name']); ?>" class="food-image"></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['food_id']; ?>">
                                    <button type="submit" class="delete-btn"
                                            onclick="return confirm('Are you sure you want to delete this item?');">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No items found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolorum, quidem.
            </table>
        </section>
    </main>
    </body>
    </html>

<?php
// Clear the form submission session variable to allow new submissions
unset($_SESSION['form_submitted']);
$conn->close();
?>