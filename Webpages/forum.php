<?php
// Database connection
include '../php_setup_files/connection.php'; // Update with your connection file

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newPost'])) {
    $username = htmlspecialchars($_POST['username']);
    $songTitle = htmlspecialchars($_POST['songTitle']);
    $postContent = htmlspecialchars($_POST['postContent']);
    $sql = "INSERT INTO forum_posts (username, song_title, post_content) VALUES ('$username', '$songTitle', '$postContent')";
    mysqli_query($conn, $sql);
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newComment'])) {
    $postId = $_POST['postId'];
    $username = htmlspecialchars($_POST['username']);
    $commentContent = htmlspecialchars($_POST['commentContent']);
    $sql = "INSERT INTO forum_comments (post_id, username, comment_content) VALUES ('$postId', '$username', '$commentContent')";
    mysqli_query($conn, $sql);
}

// Fetch posts and comments
$posts = mysqli_query($conn, "SELECT * FROM forum_posts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Fernanda">
        <meta name="description" content="Ferzk's Music Library - Lyrics">
        <meta name="keywords" content="music, lyrics, Ferzk, song lyrics">
        <meta charset="UTF-8">
        <title>Forum</title>
        <link rel="shortcut icon" href="../Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
        <link rel="stylesheet" href="../Resources/CSS/forum.css">
</head>

<body>
    <header>
        <?php include __DIR__ . '/../components/navbar.php'; ?>
    </header>

    <section class="coming-next">
        <h2>Coming Next!</h2>
        <p>🎵 New playlist features</p>
        <p>🎤 Lyrics synchronization</p>
        <p>🎧 Personalized recommendations</p>
    </section>

    <section>
        <h2>Start a Discussion</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Your Name" required>
            <input type="text" name="songTitle" placeholder="Song Title" required>
            <textarea name="postContent" rows="4" placeholder="What do you think about this song?" required></textarea>
            <button type="submit" name="newPost">Post</button>
        </form>
    </section>

    <section>
        <h2>Discussions</h2>
        <?php while ($post = mysqli_fetch_assoc($posts)): ?>
            <div class="post">
                <h3><?= htmlspecialchars($post['song_title']) ?></h3>
                <p><strong><?= htmlspecialchars($post['username']) ?>:</strong> <?= htmlspecialchars($post['post_content']) ?></p>
                <p><small>Posted on: <?= $post['created_at'] ?></small></p>

                <!-- Fetch and display comments -->
                <?php
                $postId = $post['id'];
                $comments = mysqli_query($conn, "SELECT * FROM forum_comments WHERE post_id = $postId ORDER BY created_at ASC");
                ?>
                <div>
                    <h4>Comments</h4>
                    <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                        <div class="comment">
                            <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['comment_content']) ?></p>
                            <p><small>Commented on: <?= $comment['created_at'] ?></small></p>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Add a comment -->
                <form method="POST" style="margin-top: 1rem;">
                    <input type="hidden" name="postId" value="<?= $postId ?>">
                    <input type="text" name="username" placeholder="Your Name" required>
                    <textarea name="commentContent" rows="2" placeholder="Write a comment..." required></textarea>
                    <button type="submit" name="newComment">Comment</button>
                </form>
            </div>
        <?php endwhile; ?>
    </section>

    <?php include __DIR__ . '/../components/footer.php'; ?>

</body>

</html>