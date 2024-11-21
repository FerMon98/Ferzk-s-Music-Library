<?php
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../php_setup_files/connection.php';

$sql = "SELECT title, artist, album, album_cover, genre, duration, lyrics, link, file_path FROM songs";
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
    <meta name="description" content="Ferzk's Music Library">
    <meta name="keywords" content="music library, music player, lyrics, music, Ferzk">
    <title>Music Player</title>
    <link rel="stylesheet" href="../Resources/CSS/musicplayer.css">
    <link rel="shortcut icon" href="../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">

    <script>
        function togglePlay(audioId) {
            const audioElement = document.getElementById(audioId);

            if (!audioElement) {
                console.error(`Audio element with ID '${audioId}' not found.`);
                return;
            }

            if (audioElement.paused) {
                // Pause any other playing audio before playing the new one
                const allAudios = document.querySelectorAll("audio");
                allAudios.forEach(audio => {
                    if (!audio.paused) {
                        audio.pause();
                    }
                });

                audioElement.play();
            } else {
                audioElement.pause();
            }
        }
    </script>
</head>
<body>
    <header>
        <a href="../index.php">🏡 </a>
        <h1>Have a blast with your favorite songs!</h1>
    </header>

    <main>
        <h2>You can listen from our collection or create your own Playlists 😎</h2>

        <section id="made_playlists">
            <div class="playlist">
                <h1>80's Songs</h1>
                <ul>
                    <?php
                    foreach ($songs as $row) {
                        if (strpos($row['genre'], '80') !== false) {
                            echo '<li>';
                            echo '<img src="' . htmlspecialchars($row['album_cover']) . '" alt="' . htmlspecialchars($row['album']) . '">';
                            if (!empty($row['artist'])) {
                                echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['artist']) . ' - ' . htmlspecialchars($row['title']) . '</a>';
                            } else {
                                echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['title']) . '</a>';
                            }
                            // Audio element with play button
                            echo '<audio id="audio-' . htmlspecialchars($row['title']) . '" src="' . htmlspecialchars($row['file_path']) . '" type="audio/mpeg"></audio>';
                            echo '<span class="play-button" onclick="togglePlay(\'audio-' . htmlspecialchars($row['title']) . '\')">▶</span>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>

            <div class="playlist">
                <h1>Pop Songs</h1>
                <ul>
                    <?php
                    foreach ($songs as $row) {
                        // Check if 'Pop' is part of the genre column (case-insensitive)
                        if (stripos(trim($row['genre']), 'Pop') !== false) {
                            echo '<li>';
                            echo '<img src="' . htmlspecialchars($row['album_cover']) . '" alt="' . htmlspecialchars($row['album']) . '">';
                            if (!empty($row['artist'])) {
                                echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['artist']) . ' - ' . htmlspecialchars($row['title']) . '</a>';
                            } else {
                                echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['title']) . '</a>';
                            }
                            // Audio element with play button
                            echo '<audio id="audio-' . htmlspecialchars($row['title']) . '" src="' . htmlspecialchars($row['file_path']) . '" type="audio/mpeg"></audio>';
                            echo '<span class="play-button" onclick="togglePlay(\'audio-' . htmlspecialchars($row['title']) . '\')">▶</span>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>

            <div class="playlist">
                <h1>Rock Songs</h1>
                <ul>
                    <?php
                    foreach ($songs as $row) {
                        // Check if 'Pop' is part of the genre column (case-insensitive)
                        if (stripos(trim($row['genre']), 'Rock') !== false) {
                            echo '<li>';
                            echo '<img src="' . htmlspecialchars($row['album_cover']) . '" alt="' . htmlspecialchars($row['album']) . '">';
                            if (!empty($row['artist'])) {
                                echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['artist']) . ' - ' . htmlspecialchars($row['title']) . '</a>';
                            } else {
                                echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['title']) . '</a>';
                            }
                            // Audio element with play button
                            echo '<audio id="audio-' . htmlspecialchars($row['title']) . '" src="' . htmlspecialchars($row['file_path']) . '" type="audio/mpeg"></audio>';
                            echo '<span class="play-button" onclick="togglePlay(\'audio-' . htmlspecialchars($row['title']) . '\')">▶</span>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>

                <?php
                $file_path = '../songs/IDontWantToMissAThing.mp3';

                if (file_exists($file_path)) {
                    echo "File exists: " . realpath($file_path);
                } else {
                    echo "File does not exist: " . $file_path;
                }
                ?>

            </div>
        </section>
    </main>
    
</body>
</html>
