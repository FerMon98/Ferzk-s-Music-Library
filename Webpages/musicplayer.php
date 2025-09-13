<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../php_setup_files/connection.php';

// Include helper functions
include '../php_setup_files/helpers.php';

// Fetch all songs from the database
$sql = "SELECT song_id, title, artist, album, album_cover, genre, duration, lyrics, link, file_path FROM songs";
$result = $conn->query($sql);

$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}

// Extract unique genres
$genres = [];
foreach ($songs as $row) {
    $songGenres = explode(',', $row['genre']);
    foreach ($songGenres as $genre) {
        $genre = trim($genre);
        if (!in_array($genre, $genres)) {
            $genres[] = $genre;
        }
    }
}

// Function to slugify text for HTML id attributes
function slugify($text)
{
    // Add a prefix to ensure the ID starts with a letter
    $text = 'playlist-' . $text;
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text;
}

session_start();  // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: ../php_setup_files/login.php");
    exit();
}

// Check if the addToPlaylist request is made
if (isset($_POST['addToPlaylist']) && isset($_POST['song_id'])) {
    $user_id = $_SESSION['user_id'];  // Get the logged-in user's ID
    $song_id = $_POST['song_id'];  // Get the song ID

    // Check if the song is already in the user's playlist
    $check_sql = "SELECT * FROM user_playlists WHERE user_id = ? AND song_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $song_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the song is not already in the playlist, add it
    if ($result->num_rows == 0) {
        $insert_sql = "INSERT INTO user_playlists (user_id, song_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ii", $user_id, $song_id);
        if ($stmt->execute()) {
            echo "Song added to your playlist!";
        } else {
            echo "There was an error adding the song to your playlist. Please try again.";
        }
    } else {
        echo "This song is already in your playlist.";
    }

    exit();  // End the script after handling the AJAX request
}

function webroot_src($p)
{
    if (!$p) return '';
    if (preg_match('#^https?://#i', $p)) return $p; // external
    return '/' . ltrim($p, '/');                     // -> /songs/filename.mp3
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
    <title>Music Player</title>
    <link rel="stylesheet" href="../Resources/CSS/musicplayer.css">
    <link rel="shortcut icon" href="../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
    <script src="../Resources/javascript/playmusic.js" defer></script>
    <script>
        // Passing PHP data to JavaScript
        var songs = <?php echo json_encode($songs); ?>;
        console.log("Songs array from PHP:", songs);
    </script>
</head>

<body>
    <header>
        <?php include __DIR__ . '/../components/navbar.php'; ?>

        <h1>Have a blast with your favorite songs!</h1>
    </header>

    <main>
        <P>You can listen from our collection or access <a href="userPlaylist.php" style="color: lightpink;">your own collection</a> 😎</P>

        <section id="made_playlists">
            <?php foreach ($genres as $genre): ?>
                <?php
                $playlistId = slugify($genre);
                ?>
                <div class="playlist" data-genre="<?= htmlspecialchars($genre) ?>">
                    <p><?= htmlspecialchars($genre) ?> Songs
                        <button class="play-all" onclick="playPlaylist('<?= $playlistId ?>')">Play All</button>
                    </p>
                    <ul id="<?= $playlistId ?>">
                        <?php foreach ($songs as $row): ?>
                            <?php if (stripos(trim($row['genre']), $genre) !== false): ?>
                                <?php
                                $audioId = slugify($row['title']);
                                ?>
                                <li data-title="<?= htmlspecialchars($row['title']) ?>">
                                    <div>
                                        <?php $cover = htmlspecialchars(cover_from_row($row)); ?>
                                        <img src="<?= $cover ?>" alt="<?= htmlspecialchars($row['album'] ?? 'Album') ?>">

                                        <a href="javascript:void(0)"
                                            onclick="playSingleSong('audio-<?= $audioId ?>', '<?= $playlistId ?>')">
                                            <?= !empty($row['artist'])
                                                ? htmlspecialchars($row['artist']) . ' - ' . htmlspecialchars($row['title'])
                                                : htmlspecialchars($row['title']) ?>
                                        </a>
                                    </div>
                                    <div>
                                        <button class="add-to-playlist" onclick="addToPlaylist(<?= $row['song_id'] ?>)" style="padding: 0.3rem; border-radius: 15px; background-color:black; color: white ">+</button>
                                        <audio id="audio-<?= $audioId ?>" src="<?= htmlspecialchars(webroot_src($row['file_path'])) ?>" type="audio/mpeg"></audio>
                                        <span class="play-button" onclick="playSingleSong('audio-<?= $audioId ?>', '<?= $playlistId ?>')">😎</span>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <div id="floating-controls">
        <button id="prev-button">⏮ Previous</button>
        <button id="play-pause-button">▶ Play</button>
        <button id="next-button">⏭ Next</button>
        <div id="now-playing">
            <span id="current-song-title">No song playing</span>
        </div>
    </div>

    <button id="toTop" type="button" aria-label="Scroll to top" title="Back to top">↑</button>    
    <?php include __DIR__ . '/../components/footer.php'; ?>

</body>

</html>