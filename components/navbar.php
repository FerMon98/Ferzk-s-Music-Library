<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$BASE = (strpos($_SERVER['SCRIPT_NAME'], '/Webpages/') !== false) ? '../' : './';
?>
<header class="site-header">
  <div class="brand" style="display:flex;align-items:center;gap:.6rem;">
    <a href="<?= $BASE ?>index.php" class="brand-link" style="display:flex;align-items:center;gap:.6rem;color:white;text-decoration:none;">
      <img src="<?= $BASE ?>Resources/Images/logo/library-roundel.svg" alt="Logo" style="width:65px;height:60px;border-radius:8px;">
      <strong>Ferzk’s Music Library</strong>
    </a>
  </div>
  <nav class="main-nav">
    <ul>
      <li><a href="<?= $BASE ?>index.php">Home</a></li>
      <li><a href="<?= $BASE ?>Webpages/musicplayer.php">Playlists</a></li>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <li class="right"><a href="<?= $BASE ?>php_setup_files/login.php">Login</a></li>
      <?php else: ?>
        <li class="right"><a href="<?= $BASE ?>Webpages/userPlaylist.php">My Playlist</a></li>
        <li><a href="<?= $BASE ?>Webpages/lyricsWeb.php">Lyrics</a></li>
        <li><a href="<?= $BASE ?>Webpages/forum.php">Forum</a></li>
        <li><a href="<?= $BASE ?>php_setup_files/logOut.php">Logout</a></li>
      <?php endif; ?>
      <li><a href="<?= $BASE ?>Webpages/formadd-remove.php">Song Requests</a></li>
      <li><a href="<?= $BASE ?>Webpages/contact.php">Contact</a></li>
    </ul>
  </nav>
</header>
