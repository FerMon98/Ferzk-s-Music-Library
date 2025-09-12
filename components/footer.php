<?php $BASE = (strpos($_SERVER['SCRIPT_NAME'], '/Webpages/') !== false) ? '../' : './'; ?>
<footer class="site-footer">
  <div class="footer-inner">
    <p>© <?= date('Y') ?> Ferzk’s Music Library</p>
    <nav class="footer-nav">
      <a href="<?= $BASE ?>Webpages/contact.php">Contact</a>
      <a href="<?= $BASE ?>Webpages/forum.php">Forum</a>
      <a href="<?= $BASE ?>Webpages/lyricsWeb.php">Lyrics</a>
      <a href="<?= $BASE ?>index.php#popular-songs">Popular</a>
    </nav>
  </div>
</footer>
