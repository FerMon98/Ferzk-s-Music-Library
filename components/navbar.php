<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$BASE = (strpos($_SERVER['SCRIPT_NAME'], '/Webpages/') !== false) ? '../' : './';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$curr = trim(basename($path), '/');
$curr = $curr === '' ? 'index.php' : $curr;
function isActive($file, $curr) { return basename($file) === $curr ? 'class="active" aria-current="page"' : ''; }
?>
<header class="site-header">
  <div class="brand">
    <a href="<?= $BASE ?>index.php" class="brand-link">
      <img src="<?= $BASE ?>Resources/Images/logo/library-roundel.svg" alt="Logo" class="brand-logo">
      <strong>Ferzk’s Music Library</strong>
    </a>
  </div>

  <!-- Botón hamburguesa (móvil) -->
  <button class="menu-toggle" aria-label="Toggle navigation" aria-controls="primary-navigation" aria-expanded="false">☰</button>

  <nav class="main-nav" id="primary-navigation">
    <ul>
      <li><a href="<?= $BASE ?>index.php"                <?= isActive('index.php', $curr) ?>>Home</a></li>
      <li><a href="<?= $BASE ?>Webpages/musicplayer.php" <?= isActive('musicplayer.php', $curr) ?>>Playlists</a></li>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <li><a href="<?= $BASE ?>php_setup_files/login.php" <?= isActive('login.php', $curr) ?>>Login</a></li>
      <?php else: ?>
        <li><a href="<?= $BASE ?>Webpages/userPlaylist.php" <?= isActive('userPlaylist.php', $curr) ?>>My Playlist</a></li>
        <li><a href="<?= $BASE ?>Webpages/lyricsWeb.php"    <?= isActive('lyricsWeb.php', $curr) ?>>Lyrics</a></li>
        <li><a href="<?= $BASE ?>Webpages/forum.php"        <?= isActive('forum.php', $curr) ?>>Forum</a></li>
        <li><a href="<?= $BASE ?>php_setup_files/logOut.php"<?= isActive('logOut.php', $curr) ?>>Logout</a></li>
      <?php endif; ?>
      <li><a href="<?= $BASE ?>Webpages/formadd-remove.php" <?= isActive('formadd-remove.php', $curr) ?>>Song Requests</a></li>
      <li><a href="<?= $BASE ?>Webpages/contact.php"        <?= isActive('contact.php', $curr) ?>>Contact</a></li>
    </ul>
  </nav>
</header>

<script>
  document.addEventListener("DOMContentLoaded", () => {

  // Toggle accesible del menú móvil
    const toggle = document.querySelector(".menu-toggle");
    const nav = document.getElementById("primary-navigation");

    const setExpanded = (open) => {
      nav.classList.toggle("open", open);
      toggle.setAttribute("aria-expanded", String(open));
    };

    setExpanded(false);
    toggle.addEventListener("click", () => setExpanded(!nav.classList.contains("open")));
    
  // Scroll-to-top
  const toTop = document.getElementById("toTop");
  if (toTop) {
    const showAfter = 120; // px scrolled before showing (lowered)

    const toggleBtn = () => {
      if (window.scrollY > showAfter) toTop.classList.add("show");
      else toTop.classList.remove("show");
    };

    // initial + on scroll
    toggleBtn();
    window.addEventListener("scroll", toggleBtn, { passive: true });

    // click -> smooth scroll to top (respects reduced motion)
    toTop.addEventListener("click", () => {
      const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      window.scrollTo({ top: 0, behavior: reduced ? "auto" : "smooth" });
    });
  }
});
</script>
