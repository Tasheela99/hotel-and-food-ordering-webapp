<?php
session_start();
include 'includes/database.php';

// 1. Handle form submission in the SAME file
$contactMessage = '';

// Check if the user just submitted the form via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name'], $_POST['email'], $_POST['message'])) {
    // Retrieve and sanitize form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Basic validation
    if (!empty($name) && !empty($email) && !empty($message)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        // Attempt to execute the statement
        if ($stmt->execute()) {
            $contactMessage = '<p style="color: green;">Your message has been sent successfully!</p>';
        } else {
            $contactMessage = '<p style="color: red;">There was a problem sending your message. Please try again.</p>';
        }

        $stmt->close();
    } else {
        // If required fields are empty
        $contactMessage = '<p style="color: red;">All fields are required. Please try again.</p>';
    }
}

// 2. Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// 3. Fetch food items from the database
$sqlFood = "SELECT * FROM food_items ORDER BY name ASC";
$resultFood = $conn->query($sqlFood);

// 4. Fetch hotels from the database
$sqlHotels = "SELECT * FROM hotels ORDER BY name ASC";
$resultHotels = $conn->query($sqlHotels);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">

    <script>
        function toggleMenu() {
            const navMenu = document.querySelector('.nav-menu');
            navMenu.classList.toggle('active');
        }
    </script>
</head>
<body>

<nav class="nav">
    <div class="nav-container">
        <!-- BRAND/LOGO AREA (Optional) -->
        <div class="nav-brand">
            <a href="#">MyWedding</a>
        </div>

        <!-- NAVIGATION LINKS -->
        <ul class="nav-menu">
            <li><a href="#">Home</a></li>
            <li><a href="#">Wedding Catering Menu</a></li>
            <li><a href="#">Wedding Venues</a></li>

            <!-- Show Login if not logged in, Logout if logged in -->
            <?php if ($isLoggedIn): ?>
                <li><a href="views/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="views/login.php">Login</a></li>
            <?php endif; ?>
        </ul>

        <!-- BURGER ICON FOR RESPONSIVE (Optional) -->
        <div class="burger" onclick="toggleMenu()">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
    </div>
</nav>

<main>
    <!-- Hero Section with Wedding-Themed Content -->
    <section class="long-image">
        <div class="main-div">
            <div>
                <h1 class="title">Celebrate Your Special Day with Our Exquisite Wedding Catering</h1>
            </div>
            <div>
                <h3 class="sub-title">
                    Make your wedding celebration extraordinary with a delectable selection of dishes crafted to delight
                    every palate. Our catering services ensure that you and your guests enjoy an unforgettable dining
                    experience. Whether you’re dreaming of an intimate gathering or a grand affair, our culinary team will
                    bring elegance and flavor to your special day.
                </h3>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="about-section">
        <div class="container-aboutus">
            <div class="content">
                <h2>About Us</h2>
                <p>
                    Welcome to our Wedding & Hotel Management platform, where your dream ceremony meets exceptional hospitality.
                    We are dedicated to helping you create memories that last a lifetime by offering top-notch venues, catering services,
                    and a seamless booking experience.
                </p>
                <p>
                    Our commitment to excellence ensures that every detail of your wedding celebration—from the venue ambiance
                    to the culinary delights—is handled with the utmost care and professionalism. Let us help you plan a perfect wedding
                    that reflects your unique style.
                </p>
            </div>
            <div class="image">
                <img src="assets/images/aboutus.jpg" alt="About Us">
            </div>
        </div>
    </section>

    <!-- Another Hero-Like Section -->
    <section id="hero" class="hero section light-background">
        <div class="container-1">
            <div class="inc">
                <div class="text-col col-lg-5 order-2 order-lg-1 d-flex flex-column justify-content-center">
                    <h1 data-aos="fade-up">Your Dream Wedding Awaits</h1>
                    <p data-aos="fade-up" data-aos-delay="100">
                        Let us bring your wedding vision to life! Our dedicated team of professionals will guide you through
                        every step, ensuring that your big day is stress-free and magical. From venue selection to menu planning,
                        we have all the details covered.
                    </p>
                    <div class="d-flex" data-aos="fade-up" data-aos-delay="200">
                        <a href="" class="btn-get-started">Plan Your Wedding</a>
                    </div>
                </div>
                <div class="image-col col-lg-5 order-1 order-lg-2 hero-img" data-aos="zoom-out">
                    <img src="assets/images/wedding03.jpg" class="img-fluid animated" alt="Wedding">
                </div>
            </div>
        </div>
    </section>

    <!-- Section for Available Food (Wedding Catering) Items -->
    <section id="catering">
        <h3 class="row-title">Our Wedding Catering Options</h3>
        <div class="grid-container">
            <?php if ($resultFood && $resultFood->num_rows > 0): ?>
                <?php while ($row = $resultFood->fetch_assoc()): ?>
                    <div class="grid-card">
                        <img src="<?php echo htmlspecialchars($row['image']); ?>"
                             alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p><strong>$<?php echo number_format($row['price'], 2); ?></strong></p>

                        <!-- Show "Add to Cart" only if user is logged in -->
                        <?php if ($isLoggedIn): ?>
                            <form method="POST" action="/views/add_to_cart.php">
                                <input type="hidden" name="food_id" value="<?php echo $row['food_id']; ?>">
                                <button type="submit">Add to Wedding Cart</button>
                            </form>
                        <?php else: ?>
                            <p><a href="views/login.php">Login to Add to Cart</a></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No catering items available at the moment.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Divider / Text for Wedding Venues -->
    <div class="food-row">
        <h1 class="book-hotel">Book Your Perfect Wedding Venue</h1>
    </div>

    <!-- Section for Available Hotels (Wedding Venues) -->
    <section>
        <h3 class="row-title">Our Wedding Venue Partners</h3>
        <div class="grid-container">
            <?php if ($resultHotels && $resultHotels->num_rows > 0): ?>
                <?php while ($row = $resultHotels->fetch_assoc()): ?>
                    <div class="grid-card">
                        <?php
                        $imagePath = !empty($row['image']) ? $row['image'] : 'assets/images/default_hotel.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['contact']); ?></p>

                        <!-- Show "Book Hotel" only if user is logged in -->
                        <?php if ($isLoggedIn): ?>
                            <form method="POST" action="/views/user/book_hotel.php">
                                <input type="hidden" name="hotel_id" value="<?php echo $row['hotel_id']; ?>">
                                <button type="submit">Reserve Now</button>
                            </form>
                        <?php else: ?>
                            <p><a href="views/login.php">Login to Book a Venue</a></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No venues available at the moment.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container-contact">
            <h2>Contact Us</h2>

            <!-- Display success/error message if any -->
            <?php echo $contactMessage; ?>

            <p>Have any questions or special requests for your wedding? Feel free to reach out to us!</p>
            <!-- Note: 'action' is empty, so it submits to the same page -->
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2025 Wedding & Venue Management System. All rights reserved.</p>
</footer>

<?php
$conn->close();
?>
