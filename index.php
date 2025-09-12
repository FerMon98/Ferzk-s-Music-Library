<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // to know if the user is logged in

include 'php_setup_files/connection.php';
include 'php_setup_files/helpers.php';

// include song_id so we can add to playlist
$sql = "SELECT song_id, title, artist, album, album_cover, genre, duration, lyrics, link FROM songs";
$result = $conn->query($sql);
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
    <title>Ferzk's Library</title>
    <link rel="stylesheet" href="./Resources/CSS/style.css">
    <link rel="icon" type="image/svg+xml" href="./Resources/Images/logo/library-roundel.svg">
    <script>
        function addToPlaylist(songId) {
            fetch('./Webpages/musicplayer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        addToPlaylist: 'true',
                        song_id: songId
                    })
                })
                .then(r => r.text())
                .then(t => alert(t))
                .catch(e => alert('Error: ' + e));
        }
    </script>

</head>

<body>
    <header>
        <?php include __DIR__ . '/components/navbar.php'; ?>
    </header>

    <main>
        <section id="banner">
            <p style="padding: 2rem 4rem;">This is a simple website for a music library. <br> You can find all the music you love here, just <a href="php_setup_files/login.php" style="color: white">create an account</a> with us and enjoy all your favourites! <br> Here's a list of all of our popular songs: </p>
        </section>

        <section>
            <div id="popular-songs">
                <h2>Popular Songs</h2>
                <!-- Song cards will be dynamically generated here -->
                <div>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="song-card">';
                            $cover = htmlspecialchars(cover_from_row($row));
                            echo '<img src="' . $cover . '" alt="Cover photo of ' . htmlspecialchars($row['title']) . '" class="song-cover">';

                            echo '<h5>' . htmlspecialchars($row['title']) . '</h5>';

                            if (!empty($row['artist'])) {
                                echo '<p><strong>Artist:</strong> ' . htmlspecialchars($row['artist']) . '</p>';
                            }
                            if (!empty($row['album'])) {
                                echo '<p><strong>Album:</strong> ' . htmlspecialchars($row['album']) . '</p>';
                            }
                            if (!empty($row['duration'])) {
                                echo '<p><strong>Duration:</strong> ' . htmlspecialchars($row['duration']) . '</p>';
                            }
                            echo '<p><strong>Genre:</strong> ' . htmlspecialchars($row['genre']) . '</p>';

                            if (!empty($row['link'])) {
                                echo '<a class="btn ghost" href="' . htmlspecialchars($row['link']) . '" target="_blank">Listen on YouTube</a>';
                            }

                            // Add to playlist button (only if logged in)
                            if (isset($_SESSION['user_id'])) {
                                echo '<button class="btn add" onclick="addToPlaylist(' . (int)$row['song_id'] . ')">+ Add to My Playlist</button>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No songs found.</p>';
                    }

                    $conn->close();
                    ?>
                </div>
            </div>
        </section>
        <!--<audio controls></audio>
            <source src="Resources/Music/ytmp3free.cc_the-killers-somebody-told-me-official-music-video-youtubemp3free.org.mp3" type="audio/mpeg">
        </audio> -->
    </main>

    <?php include __DIR__ . '/components/footer.php'; ?>

</body>

</html>