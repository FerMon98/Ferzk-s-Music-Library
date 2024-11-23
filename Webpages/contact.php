<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../php_setup_files/connection.php';

// Define the response message (empty by default)
$responseMessage = '';

// Check if the form is submitted via POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $username = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $message = $_POST['message'] ?? null;

    // Validate the form fields (ensure none are empty)
    if (!empty($username) && !empty($email) && !empty($message)) {
        // Prepare an SQL statement to insert the data into the contact table
        $stmt = $conn->prepare("INSERT INTO contact (username, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $message);  // "sss" means all parameters are strings

        // Execute the SQL statement
        if ($stmt->execute()) {
            // If the query is successful, show a success message
            $responseMessage = "Your message has been sent successfully!";
        } else {
            // If there is an error with the query, show an error message
            $responseMessage = "Error sending message: " . $stmt->error;
        }
        // Close the statement
        $stmt->close();
    } else {
        // If any of the required fields are empty, show an error message
        $responseMessage = "Please fill in all fields.";
    }
}

?>

<!-- HTML to display the result message -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Fernanda">
    <meta name="description" content="Ferzk's Music Library">
    <meta name="keywords" content="music library, music player, lyrics, music, Ferzk">
    <title>Contact Form Submission</title>
    <link rel="stylesheet" href="../Resources/CSS/contact.css">
    <link rel="shortcut icon" href="../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
</head>
<body>
    <header>
        <a href="../index.php">🏡</a>
        <h3>Do you want to collaborate with us or have any questions?</h3>
    </header>

    <main>
        <!-- Display success or error message -->
        <?php if ($responseMessage): ?>
            <p class="message"><?php echo htmlspecialchars($responseMessage); ?></p>
        <?php endif; ?>

        <!-- Form to send a message -->
        <form action="contact.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Message:</label>
            <textarea id="message" name="message" required></textarea>
            
            <div>
                <button type="submit">Submit</button>
                <button type="reset">Reset</button>
            </div>
        </form>
    </main>

    <footer>
        <section>
            <h4>If you want you can also give us a call and one of our specialists will help you out.</h4>
            <p><strong>Phone:</strong> +34 649 71 12 33</p>
            <p><strong>Email:</strong> fernanda.r.montalvan@gmail.com</p>
            <p><strong>Address:</strong> Can Masallera, Sant Boi de Llobregat, Catalunya.</p>
        </section>
    </footer>
</body>
</html>
