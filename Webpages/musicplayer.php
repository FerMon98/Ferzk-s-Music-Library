<?php
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../php_setup_files/connection.php';

$sql = "SELECT title, artist, album, album_cover, genre, duration, lyrics, link, file_path FROM songs";
$result = $conn->query($sql);
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
</head>
<body>
    <header>
        <a href="../index.php">🏡 </a>
        <h1>Have a blast with your favorite songs!</h1>
    </header>

    <main>
        <h2>You can listen from our collection or create your own Playlists 😎</h2>

        <section id="made_playlists">
            <div id="eightys">
                <h1>80's Songs</h1>
                <ul>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        // Check if '80' is part of the genre column
                        if (strpos($row['genre'], '80') !== false) {
                            echo '<li>';
                            echo '<img src="'. htmlspecialchars($row['album_cover']). '" alt="'. htmlspecialchars($row['album']). '">';

                            // Check if the artist is not empty and combine it with the title in the same link 
                            if (!empty($row['artist'])) { 
                                echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['artist']) . ' - ' . htmlspecialchars($row['title']) . '</a>'; 
                            } else { 
                                echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['title']) . '</a>'; 
                            }

                            echo '<audio id="audio-'. htmlspecialchars($row['title']) .'" src="'. htmlspecialchars($row['file_path']) . '" type="audio/mpeg"></audio>';
                            echo '<span class="play-button" onclick="togglePlay(\'audio-'. htmlspecialchars($row['title']) .'\')">▶</span>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>

            <div id="Pop">
                <h1>Pop Songs</h1>
                <ul>
                    <?php
                        while ($row = $result->fetch_assoc()) {
                            // Check if 'Pop' is part of the genre column (case-insensitive)
                            if (stripos(trim($row['genre']), 'Pop') !== false) {
                                echo '<li>';
                                    echo '<img src="'. htmlspecialchars($row['album_cover']). '" alt="'. htmlspecialchars($row['album']). '">';
                                    if (!empty($row['artist'])) { 
                                        echo '<a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['artist']) .' - '. htmlspecialchars($row['title']). '</a>'; 
                                    } else { 
                                        echo '<a href="' . htmlspecialchars($row['link']). '">' . htmlspecialchars($row['title']). '</a>'; 
                                    }
                                    echo '<audio id="audio-'. htmlspecialchars($row['title']) .'" src="'. htmlspecialchars($row['file_path']). '" type="audio/mpeg"></audio>';
                                    echo '<span class="play-button" onclick="togglePlay(\'audio-'. htmlspecialchars($row['title']).'\')">▶</span>';
                                echo '</li>';
                            }
                        }

                        while ($row = $result->fetch_assoc()) {
                            echo '<p>' . htmlspecialchars($row['genre']) . '</p>';
                        }
                        
                    ?>
                </ul>

                <img src="https://media.geeksforgeeks.org/wp-content/uploads/geeksforgeeks-13.png" alt="Lol">
                
            </div>
        </section>
    </main>
    
</body>
</html>
