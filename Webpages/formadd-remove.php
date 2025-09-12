<?php
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../php_setup_files/connection.php';

function youtube_id_from_url(?string $url): ?string {
    if (!$url) return null;
    $p = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
    if (preg_match($p, $url, $m)) return $m[1];
    return null;
}
function yt_cover(?string $link): ?string {
    $id = youtube_id_from_url($link);
    return $id ? "https://i.ytimg.com/vi/$id/hqdefault.jpg" : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $title = $_POST['title'] ?? null;
    $artist = $_POST['artist'] ?? null;
    $album = $_POST['album'] ?? null;
    $albumCover = $_POST['album-cover'] ?? null;
    // if link is YouTube and no cover provided, compute it
    if ((empty($albumCover) || trim($albumCover) === '') && !empty($link)) {
        $maybe = yt_cover($link);
        if ($maybe) $albumCover = $maybe;
    }
    $genre = $_POST['genre'] ?? null;
    $duration = $_POST['duration'] ?? null;
    $lyrics = $_POST['lyrics'] ?? null;
    $link = $_POST['link'] ?? null;

    // Validate required fields
    if (!empty($title) && !empty($albumCover) && !empty($genre) && !empty($link)) {
        // Insert data into the database
        $stmt = $conn->prepare("INSERT INTO songs (title, artist, album, album_cover, genre, duration, lyrics, link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $title, $artist, $album, $albumCover, $genre, $duration, $lyrics, $link);

        if ($stmt->execute()) {
            $message = "Song added successfully!";
        } else {
            $message = "Error adding song: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
    }
}
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
    <title>Add a new song</title>
    <link rel="stylesheet" href="../Resources/CSS/form.css">
    <link rel="shortcut icon" href="../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
</head>

<body>
    <header>
        <?php include __DIR__ . '/../components/navbar.php'; ?>
    </header>

    <main>
        <!-- Display success or error message -->
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        
        <div style="text-align: center;">
            <h2>Can't find your song?</h2>
            <p>Fill in the form and let us handle the rest!</p>
        </div>

        <form action="formadd-remove.php" method="POST">
            <div>
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="artist">Artist:</label>
                <input type="text" id="artist" name="artist">

                <label for="album">Album:</label>
                <input type="text" id="album" name="album">

                <label for="album-cover">Album Cover link:</label>
                <input type="text" id="album-cover" name="album-cover" required>

                <label for="genre">Genre:</label>
                <input type="text" id="genre" name="genre" required>

                <label for="duration">Duration:</label>
                <input type="text" id="duration" name="duration">

                <label for="lyrics">Lyrics:</label>
                <textarea id="lyrics" name="lyrics"></textarea>

                <label for="link">Youtube Link:</label>
                <textarea id="link" name="link" required></textarea>
            </div>

            <button type="submit">Add Song</button>
        </form>

        <a href="../index.php">Go back to the library</a>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

</body>

</html>