<?php
session_start();
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../vendor/autoload.php';

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
try {
    $dotenv->load();
    echo "Dotenv loaded successfully.<br>";
    var_dump($dotenv);
} catch (Exception $e) {
    echo "Dotenv loading error: " . $e->getMessage() . "<br>";
    exit();
}

// Replace with your own Spotify Client ID and Secret
$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];
// $clientId = 'b8ea511d6c5a44fc9fcd25d1a87f9f80';
// $clientSecret = '4d4615b9f3ae45b39ce07ea425ddb984';
echo "CLIENT_SECRET: " . $_ENV['CLIENT_SECRET'] . "<br>";
echo "CLIENT_ID: " . getenv('CLIENT_ID') . "<br>";


$redirectUri = 'http://localhost:80/ferzk-music/Public/Webpages/musicplayer.php';
$auth_url = "https://accounts.spotify.com/authorize?client_id=$client_id&redirect_uri=" . urlencode($redirect_uri) . "&response_type=code&scope=user-read-private+playlist-read-private";


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
header('Location: $auth_url');
exit();
?>

<!-- *********************************************************************** -->