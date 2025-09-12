<?php
session_start();
include 'connection.php';

/* FLASH after logout */
if (isset($_GET['logged_out'])) {
    $success_message = "You’ve been logged out.";
}

/* LOGIN */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($u = $result->fetch_assoc()) {
        if (password_verify($password, $u['password'])) {
            $_SESSION['user_id'] = $u['id_user'];
            $_SESSION['username'] = $u['username'];
            header("Location: ../Webpages/userPlaylist.php");
            exit();
        }
    }
    $error_message = "Incorrect username or password!";
}

/* REGISTER */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = $_POST['register_name'];
    $last_name = $_POST['register_last_name'];
    $username = $_POST['register_username'];
    $email = $_POST['register_email'];
    $password = $_POST['register_password'];
    $phone_number = $_POST['register_phone_number'];

    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error_message = "Username is already taken. Please choose another one.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO users (name,last_name,username,email,password,phone_number) VALUES (?,?,?,?,?,?)");
        $ins->bind_param("ssssss", $name, $last_name, $username, $email, $hashed, $phone_number);
        if ($ins->execute()) $success_message = "Registration successful! You can now log in.";
        else $error_message = "There was an error during registration. Please try again later.";
    }

    $dest = '/Webpages/userPlaylist.php'; // default
    if (!empty($_GET['return']) && str_starts_with($_GET['return'], '/')) {
        $dest = $_GET['return']; // safe, stays on your site
    }
    header("Location: $dest");
    exit();

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/Resources/Images/favicon/icons8-music.svg">
    <!-- Global site styles (navbar/footer + shared tweaks) -->
    <link rel="stylesheet" href="/Resources/CSS/style.css">
    <title>Sign in</title>
    <link rel="stylesheet" href="/Resources/CSS/login.css">
</head>

<body>

    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <header class="site-header">
        <div class="brand" style="display:flex;align-items:center;gap:.6rem;">
            <a href="/index.php" class="brand-link" style="display:flex;align-items:center;gap:.6rem;color:white;text-decoration:none;">
                <img src="/Resources/Images/logo/library-roundel.svg" alt="Logo" style="width:65px;height:60px;border-radius:8px;">
                <strong>Ferzk’s Music Library</strong>
            </a>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="/index.php">Home</a></li>
                <li><a href="/Webpages/musicplayer.php">Playlists</a></li>

                <?php if (empty($_SESSION['user_id'])): ?>
                    <li class="right"><a href="/php_setup_files/login.php">Login</a></li>
                <?php else: ?>
                    <li class="right"><a href="/Webpages/userPlaylist.php">My Playlist</a></li>
                    <li><a href="/Webpages/lyricsWeb.php">Lyrics</a></li>
                    <li><a href="/Webpages/forum.php">Forum</a></li>
                    <li><a href="/php_setup_files/logOut.php">Logout</a></li>
                <?php endif; ?>

                <li><a href="/Webpages/formadd-remove.php">Song Requests</a></li>
                <li><a href="/Webpages/contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Flash messages -->
        <?php if (!empty($error_message)): ?>
            <p class="flash flash--error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <p class="flash flash--success"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <!-- Login form -->
        <form action="login.php" method="POST" class="auth-form">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" name="login" value="Login">
        </form>

        <p class="register-cta">
            Don't have an account?
            <a class="auth-link" href="javascript:void(0);" onclick="document.getElementById('registerForm').style.display='block';">Register here</a>
        </p>

        <!-- Registration form (hidden by default) -->
        <div id="registerForm" style="display:none;">
            <h2>Register</h2>
            <form action="login.php" method="POST" class="auth-form">
                <label for="register_name">Name:</label>
                <input type="text" id="register_name" name="register_name" required>

                <label for="register_last_name">Last Name:</label>
                <input type="text" id="register_last_name" name="register_last_name" required>

                <label for="register_username">Username:</label>
                <input type="text" id="register_username" name="register_username" required>

                <label for="register_email">Email:</label>
                <input type="email" id="register_email" name="register_email" required>

                <label for="register_password">Password:</label>
                <input type="password" id="register_password" name="register_password" required>

                <label for="register_phone_number">Phone Number:</label>
                <input type="tel" id="register_phone_number" name="register_phone_number" required>

                <input type="submit" name="register" value="Register">
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>

</html>