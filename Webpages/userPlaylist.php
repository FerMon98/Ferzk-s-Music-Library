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

$user_id = $_SESSION['user_id'];  // Get the logged-in user's ID

// Fetch the user's playlist songs from the database
$sql = "
    SELECT s.song_id, s.title, s.artist, s.album, s.album_cover, s.file_path
    FROM songs s
    INNER JOIN user_playlists up ON s.song_id = up.song_id
    WHERE up.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}

// Check if the 'removeFromPlaylist' and 'song_id' are being passed correctly
if (isset($_POST['removeFromPlaylist']) && isset($_POST['song_id'])) {
    $song_id = $_POST['song_id'];  // Get the song ID from the request

    // Ensure the song ID is valid (is numeric)
    if (is_numeric($song_id)) {
        // Prepare SQL to delete from user_playlists table
        $delete_sql = "DELETE FROM user_playlists WHERE user_id = ? AND song_id = ?";
        $stmt = $conn->prepare($delete_sql);
        if ($stmt === false) {
            die('Error preparing statement: ' . $conn->error);
        }
        $stmt->bind_param("ii", $user_id, $song_id);  // Bind user ID and song ID

        if ($stmt->execute()) {
            // Successfully deleted song
            echo "Song removed from your playlist.";
        } else {
            // Error removing song
            echo "There was an error removing the song. Please try again.";
            echo " Error: " . $stmt->error;  // Display the error if any
        }
        $stmt->close();  // Close the prepared statement
    } else {
        echo "Invalid song ID.";
    }

    exit();  // Exit after processing the request
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
    <title>Let's Rock!</title>
    <link rel="stylesheet" href="../Resources/CSS/userPlaylist.css">
    <link rel="shortcut icon" href="../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
    <script>
        // Pass PHP data to JavaScript
        var songs = <?php echo json_encode($songs); ?>;
        let currentSongIndex = 0;
        let currentSong = null;
        let isPlaying = false;

        // Update song information
        function updateSongDetails(songIndex) {
            const song = songs[songIndex];
            const audioPlayer = document.getElementById('audioPlayer');
            const songCover = document.getElementById('songCover');
            const songTitle = document.getElementById('songTitle');
            const songArtistAlbum = document.getElementById('songArtistAlbum');
            const noSongMessage = document.getElementById('noSongMessage');
            
            if (song) {
                // Hide the "Choose a song" message
                noSongMessage.style.display = 'none';

                // Show the song details
                songCover.style.display = 'block';
                songTitle.style.display = 'block';
                songArtistAlbum.style.display = 'block';
                audioPlayer.style.display = 'block';

                // Update the song details
                audioPlayer.src = song.file_path;
                songCover.src = song.album_cover || 'cover.jpg';
                songTitle.textContent = song.title;
                songArtistAlbum.textContent = `${song.artist || 'Unknown Artist'} - ${song.album || 'Unknown Album'}`;

                // Play the song once it's ready
                audioPlayer.addEventListener('canplay', () => {
                    audioPlayer.play().catch(error => {
                        console.error("Playback error:", error);
                    });
                });
            } else {
                // If no song is available, show the "Choose a song" message
                noSongMessage.style.display = 'block';
                songCover.style.display = 'none';
                songTitle.style.display = 'none';
                songArtistAlbum.style.display = 'none';
                audioPlayer.style.display = 'none';
            }
        }

        // Function to play/pause the current song
        function togglePlayPause() {
            const audioPlayer = document.getElementById('audioPlayer');
            const playPauseBtn = document.getElementById('play-pause-button');

            if (audioPlayer.paused) {
                audioPlayer.play();
                playPauseBtn.textContent = "⏸ Pause";
            } else {
                audioPlayer.pause();
                playPauseBtn.textContent = "▶ Play";
            }

            isPlaying = !audioPlayer.paused;
        }

        // Play next song
        function playNext() {
            if (currentSongIndex < songs.length - 1) {
                currentSongIndex++;
                updateSongDetails(currentSongIndex);
            }
        }

        // Play previous song
        function playPrevious() {
            if (currentSongIndex > 0) {
                currentSongIndex--;
                updateSongDetails(currentSongIndex);
            }
        }

        // Initial call to display the "Choose a song to listen to" message if no song is playing
        document.addEventListener('DOMContentLoaded', () => {
            updateSongDetails(currentSongIndex);
        });

        // Function to remove song from the playlist
        function removeFromPlaylist(songId) {
            // Make the POST request to remove the song
            fetch('userPlaylist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'removeFromPlaylist': 'true',
                    'song_id': songId
                })
            })
            .then(response => response.text())
            .then(data => {
                alert(data);  // Show the result from PHP (success or error)
                
                // After successful removal, remove the song from the UI
                const songElement = document.querySelector(`button[onclick="removeFromPlaylist(${songId})"]`).closest('li');
                songElement.remove();  // Remove the song from the DOM
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

    </script>
</head>
<body>
    <header>
        <a href="../index.php">🏡 </a>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!</h1>
        <a href="../php_setup_files/logOut.php" style="padding: 2rem; font-size: 2rem">Logout</a>
    </header>

    <main>
        <section>
            <div id="userSongs">
                <h3>My Songs</h3><br>
                <ul id="playlist">
                    <!-- Dynamically displaying the user's playlist -->
                    <?php if (count($songs) > 0): ?>
                        <?php foreach ($songs as $index => $song): ?>
                            <li>
                                <div class="song" style="width: 100%">
                                    <div>
                                        <img src="<?= htmlspecialchars($song['album_cover'] ?? 'cover.jpg') ?>" alt="Album Cover">
                                        <a href="javascript:void(0)" onclick="updateSongDetails(<?= $index ?>)">
                                            <?= htmlspecialchars($song['artist'] ?? 'Unknown Artist') ?> - <?= htmlspecialchars($song['title']) ?>
                                        </a>
                                    </div>
                                    <button class="remove-button" onclick="removeFromPlaylist(<?= $song['song_id'] ?>)">Remove</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Your playlist is empty. Add some songs from our playlists to get started!</p>
                    <?php endif; ?>
                </ul>
            </div>

            <div id="nowPlaying">
                <h2 id="nowPlayingTitle">Currently playing: </h2>
                <div id="songCard">
                    <p id="noSongMessage">Choose a song to listen to</p>
                    <img id="songCover" src="cover.jpg" alt="Song Cover" style="display:none;">
                    <h3 id="songTitle" style="display:none;">Song Title</h3>
                    <p id="songArtistAlbum" style="display:none;">Artist - Album</p>
                    <audio id="audioPlayer" controls style="display:none;">
                    </audio>
                </div>

                <!-- Floating Controls Panel -->
                <div id="controls">
                    <button id="prev-button" onclick="playPrevious()" >⏮️ Previous</button>
                    <button id="play-pause-button" onclick="togglePlayPause()">▶ Play</button>
                    <button id="next-button" onclick="playNext()" >⏭️ Next</button>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
