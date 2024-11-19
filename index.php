<?php 
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);



//access the environment variables
$dbAPI = getenv('DB_API_KEY');


$URL = "https://api.spotify.com/";

echo $dbAPI

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
    <link rel="stylesheet" href="Resources/CSS/style.css">
    <link rel="shortcut icon" href="Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
</head>
<body>
    <header>
        <h1>Welcome to our website!</h1>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="./Public/Webpages/musicplayer.php">Playlists</a></li>
                <li><a href="./Public/Webpages/formadd-remove.html">Song Requests</a></li>
                <li><a href="./Public/Webpages/contact.html">Contact</a></li>
                <li><a href="./Public/Webpages/userchat.html">Forum</a></li>
                <li><a href="./Public/Webpages/lyricsweb.html">Lyrics</a></li>
                <li><a href="./Public/Webpages/donations.html">Donate</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <section id="banner">
            
             <p>This is a simple website for a music library. You can find all the music you love here. Just click on the song you want to listen to and enjoy the ride</p>  
        </section>

        <div>
            <h2>Popular Songs</h2>
            <div id="popular-songs">
                <!-- Song cards will be dynamically generated here -->
                 <!-- Example:
                 <div class="song-card">
                     <img src="Resources/Images/music/song1.jpg" alt="Song 1">
                     <h3>Song 1</h3>
                     <p>Artist: Artist 1</p>
                 </div>
                 -->
            </div>
        </div>
        <!--<audio controls></audio>
            <source src="Resources/Music/ytmp3free.cc_the-killers-somebody-told-me-official-music-video-youtubemp3free.org.mp3" type="audio/mpeg">
        </audio> -->
    </main>

    <footer></footer>
</body>
</html>