<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../php_setup_files/connection.php';

$sql = "SELECT title, artist, album, album_cover, genre, duration, lyrics, link, file_path FROM songs";
$result = $conn->query($sql);

$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}
ob_end_flush();
?>


<!-- *********************************************************************** -->

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
        // Passing the PHP genres array to JavaScript
        var genres = <?php echo json_encode($genres); ?>;
        console.log("Genres array from PHP:", genres);  // This will help you debug
    </script>

    <script>
        var songs = <?php echo json_encode($songs); ?>;
        console.log("Songs array from PHP:", songs);  // Log it to check
    </script>


</head>
<body>
    <header>
        <a href="../index.php">🏡 </a>
        <h1>Have a blast with your favorite songs!</h1>
    </header>

    <main>
        <h2>You can listen from our collection or create your own Playlists 😎</h2>

        <?php
            // Function to slugify text for HTML id attributes
            function slugify($text) {
                $text = preg_replace('~[^\pL\d]+~u', '-', $text);
                $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
                $text = preg_replace('~[^-\w]+~', '', $text);
                $text = trim($text, '-');
                $text = preg_replace('~-+~', '-', $text);
                $text = strtolower($text);
                return $text;
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
            ?>

            <section id="made_playlists">
                <?php
                foreach ($genres as $genre) {
                    $playlistId = slugify($genre); // Create a unique ID for each playlist
                    echo '<div class="playlist" data-genre="' . htmlspecialchars($genre) . '">';
                    echo '<h1>' . htmlspecialchars($genre) . ' Songs';
                    echo ' <button class="play-all" onclick="playPlaylist(this)">Play All</button></h1>';
                    echo '<ul id="' . $playlistId . '">';

                    foreach ($songs as $row) {
                        if (stripos(trim($row['genre']), $genre) !== false) {
                            $audioId = slugify($row['title']);
                            echo '<li data-title="' . htmlspecialchars($row['title']) . '">';
                            echo '<img src="' . htmlspecialchars($row['album_cover']) . '" alt="' . htmlspecialchars($row['album']) . '">';
                            if (!empty($row['artist'])) {
                                echo '<a href="javascript:void(0)" onclick="playSingleSong(\'audio-' . $audioId . '\', \'' . $playlistId . '\')">'
                                    . htmlspecialchars($row['artist']) . ' - ' . htmlspecialchars($row['title']) . '</a>';
                            } else {
                                echo '<a href="javascript:void(0)" onclick="playSingleSong(\'audio-' . $audioId . '\', \'' . $playlistId . '\')">'
                                    . htmlspecialchars($row['title']) . '</a>';
                            }
                            echo '<audio id="audio-' . $audioId . '" src="' . htmlspecialchars($row['file_path']) . '" type="audio/mpeg"></audio>';
                            echo '<span class="play-button" onclick="togglePlay(\'audio-' . $audioId . '\')">▶</span>';
                            echo '</li>';
                        }
                    }

                    echo '</ul>';
                    echo '</div>';
                }
                ?>
            </section>


    </main>

    <div id="floating-controls">
        <button id="prev-button">⏮ Previous</button>
        <button id="play-pause-button">⏯ Play</button>
        <button id="next-button">⏭ Next</button>
        <div id="now-playing">
            <span id="current-song-title">No song playing</span>
        </div>
    </div>
</body>
</html>

