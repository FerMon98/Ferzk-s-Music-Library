<?php
session_start();  // Start the session for handling user login status

// Include the database connection file
include 'connection.php';

// Check if the login form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Get the login form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL query to fetch user by username
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); // 's' denotes the type (string) of the parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];

            // Redirect to the welcome page or dashboard
            header("Location: ../Webpages/userPlaylist.php");
            exit();
        } else {
            // Invalid password
            $error_message = "Incorrect username or password!";
        }
    } else {
        // User not found
        $error_message = "No user found with that username.";
    }
}

// Check if the registration form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Get the registration form data
    $name = $_POST['register_name'];
    $last_name = $_POST['register_last_name'];
    $username = $_POST['register_username'];
    $email = $_POST['register_email'];
    $password = $_POST['register_password'];
    $phone_number = $_POST['register_phone_number'];

    // Check if the username already exists
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); // 's' denotes the type (string) of the parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // If the username already exists
    if ($result->num_rows > 0) {
        $error_message = "Username is already taken. Please choose another one.";
    } else {
        // If the username is not taken, proceed with registration

        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $sql = "INSERT INTO users (name, last_name, username, email, password, phone_number) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $last_name, $username, $email, $hashed_password, $phone_number); // 'ssssssi' denotes the types (string, string, string, string, string, int, int) of the parameters
        if ($stmt->execute()) {
            // Registration successful, redirect to login page
            $success_message = "Registration successful! You can now log in.";
        } else {
            $error_message = "There was an error during registration. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Fernanda">
    <meta name="description" content="Ferzk's Music Library">
    <meta name="keywords" content="music library, music player, lyrics, music, Ferzk">
    <title>Sign in</title>
    <link rel="stylesheet" href="../Resources/CSS/login.css">
    <link rel="shortcut icon" href="../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
</head>
<body>
    <main>
        <header>
            <a href="../index.php">🏡 </a>
            <h2>Login</h2>
        </header>
        
        <!-- Display error or success messages -->
        <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
        <?php if (isset($success_message)) { echo "<p style='color:green;'>$success_message</p>"; } ?>

        <!-- Login form -->
        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <input type="submit" name="login" value="Login">
        </form>

        <p>Don't have an account? <br> <a href="javascript:void(0);" onclick="document.getElementById('registerForm').style.display='block';">Register here</a></p>

        <!-- Registration form (hidden by default) -->
        <div id="registerForm" style="display: none;">
            <h2>Register</h2>
            <form action="login.php" method="POST">
                <label for="register_name">Name:</label>
                <input type="text" id="register_name" name="register_name" required><br>

                <label for="register_last_name">Last Name:</label>
                <input type="text" id="register_last_name" name="register_last_name" required><br>

                <label for="register_username">Username:</label>
                <input type="text" id="register_username" name="register_username" required><br>

                <label for="register_email">Email:</label>
                <input type="email" id="register_email" name="register_email" required><br>

                <label for="register_password">Password:</label>
                <input type="password" id="register_password" name="register_password" required><br>

                <label for="register_phone_number">Phone Number:</label>
                <input type="tel" id="register_phone_number" name="register_phone_number" required><br>

                <input type="submit" name="register" value="Register">
            </form>
        </div>
    </main>
</body>
</html>
