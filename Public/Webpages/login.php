<?php
require '../../vendor/autoload.php';

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

session_start();

$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';
$redirectUri = 'http://localhost/ferzk-music/Public/Webpages/.html';

$session = new Session(
    $clientId,
    $clientSecret,
    $redirectUri
);

// Step 1: If no code is present, redirect to Spotify's authorization page
if (!isset($_GET['code'])) {
    $options = [
        'scope' => [
            'user-read-private',
            'playlist-read-private',
            // Add any additional scopes you need
        ],
    ];

    header('Location: ' . $session->getAuthorizeUrl($options));
    exit();
}

// Step 2: Request an access token using the provided authorization code
$session->requestAccessToken($_GET['code']);
$accessToken = $session->getAccessToken();
$refreshToken = $session->getRefreshToken();

// Store tokens in session or database
$_SESSION['accessToken'] = $accessToken;
$_SESSION['refreshToken'] = $refreshToken;

// Use the access token to make API requests
$api = new SpotifyWebAPI();
$api->setAccessToken($accessToken);

// Redirect to another page or your application's main page
header('Location: musicplayer.php');
exit();
?>

<!-- *********************************************************************** -->