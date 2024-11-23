<?php
session_start();  // Start the session

// Include the database connection
include '../php_setup_files/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

// Fetch the songs from the database, sorting by title
$sql = "
    SELECT song_id, title, artist, album, album_cover, file_path, lyrics 
    FROM songs 
    WHERE lyrics IS NOT NULL AND lyrics != '' 
    ORDER BY title ASC";
$result = $conn->query($sql);

$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Fernanda">
    <meta name="description" content="Ferzk's Music Library - Lyrics">
    <meta name="keywords" content="music, lyrics, Ferzk, song lyrics">
    <title>Song Lyrics</title>
    <link rel="stylesheet" href="../Resources/CSS/lyricsWeb.css">
    <link rel="shortcut icon" href="../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
</head>
<body>
    <header>
        <a href="../index.php">🏡</a>
        <h1>Song Lyrics</h1>
        <a href="userPlaylist.php">Back to My Playlist</a>
    </header>

    <main>
        <h2>Available Song Lyrics</h2>
        <section>

            <?php if (count($songs) > 0): ?>
                <div id="songList">
                    <ul>
                        <?php foreach ($songs as $song): ?>
                            <li>
                                <img src="<?= htmlspecialchars($song['album_cover'] ?? 'cover.jpg') ?>" alt="Album Cover">
                                <a href="javascript:void(0)" onclick="showLyrics(<?= $song['song_id'] ?>)">
                                    <?= htmlspecialchars($song['title']) ?> by <?= htmlspecialchars($song['artist']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div id="songLyrics">
                    <!-- Lyrics will be displayed here when a song is selected -->
                    <h3 id="lyricsTitle">Song Lyrics</h3>
                    <div id="lyricsContent"></div>
                </div>
            <?php else: ?>
                <p>No lyrics available for the songs in the library.</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        // JavaScript to display lyrics when a song is clicked
        function showLyrics(songId) {
            // Find the song by ID from the PHP array
            const song = <?php echo json_encode($songs); ?>.find(song => song.song_id == songId);

            if (song) {
                document.getElementById('lyricsTitle').textContent = song.title + " Lyrics";
                document.getElementById('lyricsContent').innerHTML = "<pre>" + song.lyrics + "</pre>";
            }
        }
    </script>
</body>
</html>
