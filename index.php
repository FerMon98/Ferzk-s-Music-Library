<?php 
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php_setup_files/connection.php';

$sql = "SELECT title, artist, album, album_cover, genre, duration, lyrics, link FROM songs";
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
    <link rel="shortcut icon" href="./Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
</head>
<body>
    <header>
        <h1>Welcome to our website!</h1>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="./Webpages/musicplayer.php">Playlists</a></li>
                <li><a href="./Webpages/formadd-remove.php">Song Requests</a></li>
                <li><a href="./Webpages/contact.php">Contact</a></li>
                <li><a href="./Webpages/lyricsWeb.php">Lyrics</a></li>
                <li><a href="./Webpages/forum.php">Forum</a></li>
            </ul>
        </nav>
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
                                    echo '<img src="' . $row['album_cover'] . '" alt="Cover photo of ' . $row['title'] . '" class="song-cover">';
                                    echo '<h5>' . htmlspecialchars($row['title']) . '</h5>';
                            
                                    // Display artist if it's not null
                                    if (!empty($row['artist'])) {
                                        echo '<p><strong>Artist:</strong> ' . htmlspecialchars($row['artist']) . '</p>';
                                    }
                            
                                    // Display album if it's not null
                                    if (!empty($row['album'])) {
                                        echo '<p><strong>Album:</strong> ' . htmlspecialchars($row['album']) . '</p>';
                                    }
                            
                                    // Display duration if it's not null
                                    if (!empty($row['duration'])) {
                                        echo '<strong><p>Duration:</strong> ' . htmlspecialchars($row['duration']) . '</p>';
                                    }
                            
                                    echo '<p><strong>Genre:</strong> ' . htmlspecialchars($row['genre']) . '</p>';
                                    echo '<a href="' . htmlspecialchars($row['link']) . '" target="_blank">Listen on Youtube</a>';
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

    <footer></footer>
</body>
</html>