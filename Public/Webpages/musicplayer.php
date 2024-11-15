<?php
session_start();
require '../../vendor/autoload.php';

use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;

// Check if the user is authenticated 
if (!isset($_SESSION['accessToken'])) {
    header('Location: login.php'); 
    exit();
}

$accessToken = $_SESSION['accessToken'];
$refreshToken = $_SESSION['refreshToken'];

// Check if the access token has expired and refresh it if necessary
if ($accessToken && isset($refreshToken)) {
    // Set the Spotify API instance
    $api = new SpotifyWebAPI();
    $api->setAccessToken($accessToken);

    try {
        // Make a test request to see if the token is still valid
        $me = $api->me();
    } catch (Exception $e) {
        // If the token is expired, attempt to refresh it
        $session = new Session(
            $_ENV['CLIENT_ID'],
            $_ENV['CLIENT_SECRET'],
            'http://localhost/ferzk-music/Public/Webpages/musicplayer.php'
        );
        $session->requestAccessToken($refreshToken);
        $_SESSION['accessToken'] = $session->getAccessToken();
        $_SESSION['refreshToken'] = $session->getRefreshToken();
        
        // Now try the API call again
        $api->setAccessToken($_SESSION['accessToken']);
        $me = $api->me();
    }

    // Show the user's name
    echo 'Hello, ' . $me->display_name;
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
            <button onclick="playSong()">Play</button>
        </div>
    </header>
    <main>

    </main>
</body>
</html>