<?php
session_start();

require '../../vendor/autoload.php';

use SpotifyWebAPI\SpotifyWebAPI;

// Check if the user is authenticated 
if (!isset($_SESSION['accessToken'])) {
    header('Location: login.php'); 
    exit();
}

$accessToken = $_SESSION['accessToken'];
$refreshToken = $_SESSION['refreshToken'];

$api = new SpotifyWebAPI();
$api->setAccessToken($accessToken);

// Now you can make API requests 
try { 
    $me = $api->me(); 
    echo 'Hello, ' . $me->display_name; 
} catch (Exception $e) { 
    // Handle token expiration or other errors 
    // Redirect to login or refresh token as needed 
    echo 'An error occurred: ' . $e->getMessage(); 
}
?>

<!-- *********************************************************************** -->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" name="Fernanda">
    <meta name="description" content="Ferzk's Music Library">
    <meta name="keywords" content="music library, music player, lyrics, music, Ferzk">
    <title>Let's Rock It!</title>
    <link rel="stylesheet" href="../../Resources/CSS/musicplayer.css">
    <link rel="shortcut icon" href="../../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
</head>
<body>
    <header>
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Search for a song...">
    </header>
    <main>

    </main>
</body>
</html>