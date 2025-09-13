<?php
// Webpages/add-lyrics.php
declare(strict_types=1);

session_start();
ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../php_setup_files/connection.php';
$conn->set_charset('utf8mb4');

// --- helpers ---
function h(?string $s): string
{
  // Null-tolerant HTML escape
  return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function column_exists(mysqli $conn, string $table, string $column): bool
{
  $tableEsc = $conn->real_escape_string($table);
  $colEsc   = $conn->real_escape_string($column);
  $sql = "
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = '{$tableEsc}'
          AND COLUMN_NAME  = '{$colEsc}'
        LIMIT 1";
  if (!$res = $conn->query($sql)) return false;
  return (bool)$res->fetch_row();
}

// --- login guard ---
if (empty($_SESSION['user_id'])) {
  $loginUrl = '/php_setup_files/login.php?return=' . urlencode($_SERVER['REQUEST_URI']);
  header("Location: $loginUrl");
  exit();
}

$has_source_url = column_exists($conn, 'songs', 'lyrics_source_url');

// ---- load songs for dropdown ----
$songs = [];
if ($res = $conn->query("SELECT song_id, title, COALESCE(artist,'') AS artist FROM songs ORDER BY title ASC")) {
  while ($row = $res->fetch_assoc()) $songs[] = $row;
}

// ---- state ----
$success = $error = '';
$selected_id = isset($_GET['song_id']) ? (int)$_GET['song_id'] : 0;

// ---- handle POST (save) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $selected_id = isset($_POST['song_id']) ? (int)$_POST['song_id'] : 0;
  $lyrics  = isset($_POST['lyrics']) ? trim((string)$_POST['lyrics']) : '';
  $source  = isset($_POST['lyrics_source_url']) ? trim((string)$_POST['lyrics_source_url']) : '';

  if ($selected_id <= 0) {
    $error = "Please select a song.";
  } elseif ($lyrics === '') {
    $error = "Please paste some lyrics.";
  } else {
    if ($has_source_url) {
      $stmt = $conn->prepare("UPDATE songs SET lyrics = ?, lyrics_source_url = ? WHERE song_id = ?");
      if ($stmt) {
        $stmt->bind_param('ssi', $lyrics, $source, $selected_id);
        if ($stmt->execute()) $success = "Lyrics saved!";
        else $error = "Failed to save lyrics. " . $stmt->error;
      } else {
        $error = "Failed to prepare update. " . $conn->error;
      }
    } else {
      $stmt = $conn->prepare("UPDATE songs SET lyrics = ? WHERE song_id = ?");
      if ($stmt) {
        $stmt->bind_param('si', $lyrics, $selected_id);
        if ($stmt->execute()) $success = "Lyrics saved!";
        else $error = "Failed to save lyrics. " . $stmt->error;
      } else {
        $error = "Failed to prepare update. " . $conn->error;
      }
    }
  }
}

// ---- prefill current lyrics/source for selected song ----
$current_lyrics = '';
$current_source = '';
if ($selected_id > 0) {
  if ($has_source_url) {
    if ($stmt = $conn->prepare("SELECT lyrics, lyrics_source_url FROM songs WHERE song_id = ?")) {
      $stmt->bind_param('i', $selected_id);
      $stmt->execute();
      $stmt->bind_result($current_lyrics, $current_source);
      $stmt->fetch();
      $stmt->close();
    }
  } else {
    if ($stmt = $conn->prepare("SELECT lyrics FROM songs WHERE song_id = ?")) {
      $stmt->bind_param('i', $selected_id);
      $stmt->execute();
      $stmt->bind_result($current_lyrics);
      $stmt->fetch();
      $stmt->close();
    }
  }
}
// normalize to strings for safe echo
$current_lyrics = (string)($current_lyrics ?? '');
$current_source = (string)($current_source ?? '');

function is_sel($a, $b)
{
  return ((string)$a === (string)$b) ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php if (file_exists(__DIR__ . '/../components/head.php')) {
    include __DIR__ . '/../components/head.php';
  } ?>
  <link rel="stylesheet" href="/Resources/CSS/style.css">
  <title>Add / Edit Lyrics</title>
  <style>
    .panel {
      background: rgba(0, 0, 0, .35);
      border: 1px solid rgba(255, 255, 255, .2);
      border-radius: 12px;
      padding: 1rem;
    }

    .stack {
      display: flex;
      flex-direction: column;
      gap: .75rem;
    }

    .row {
      display: flex;
      gap: .75rem;
      align-items: center;
      flex-wrap: wrap;
    }

    .wide {
      width: min(1000px, 95%);
      margin: 1.25rem auto;
    }

    select,
    textarea,
    input[type=url] {
      width: 100%;
      padding: .7rem .8rem;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, .35);
      color: #111;
      background: #fff;
    }

    textarea {
      min-height: 40vh;
      resize: vertical;
      white-space: pre-wrap;
    }

    .actions {
      display: flex;
      gap: .75rem;
      justify-content: flex-end;
    }

    .btn-primary {
      padding: .6rem 1rem;
      border-radius: 10px;
      border: 2px solid darkblue;
      background: darkblue;
      color: #fff;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-primary:hover {
      background: #ddd;
      color: #000;
    }

    .flash {
      margin: .5rem 0;
      padding: .6rem .9rem;
      border-radius: 10px;
    }

    .flash.ok {
      background: rgba(0, 180, 0, .15);
      border: 1px solid rgba(0, 180, 0, .35);
      color: #e7ffe7;
    }

    .flash.err {
      background: rgba(255, 0, 0, .15);
      border: 1px solid rgba(255, 0, 0, .35);
      color: #ffdede;
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 1rem;
      align-items: start;
    }

    @media (max-width: 900px) {
      .grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <?php if (file_exists(__DIR__ . '/../components/navbar.php')) {
    include __DIR__ . '/../components/navbar.php';
  } ?>

  <main class="wide">
    <h1>Add / Edit Lyrics</h1>

    <?php if ($success): ?><div class="flash ok"><?= h($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="flash err"><?= h($error) ?></div><?php endif; ?>

    <form method="post" class="panel stack">
      <div class="grid">
        <div class="stack">
          <label for="song_id"><strong>Song</strong></label>
          <select id="song_id" name="song_id" required onchange="onSongChange(this)">
            <option value="">— Select a song —</option>
            <?php foreach ($songs as $s): ?>
              <option value="<?= (int)$s['song_id'] ?>" <?= is_sel($selected_id, $s['song_id']) ?>>
                <?= h($s['title'] . ($s['artist'] ? " — " . $s['artist'] : "")) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <?php if ($has_source_url): ?>
            <label for="lyrics_source_url"><strong>Source URL (optional)</strong></label>
            <input type="url" id="lyrics_source_url" name="lyrics_source_url" placeholder="https://…" value="<?= h($current_source) ?>">
          <?php endif; ?>

          <p style="opacity:.9;font-size:.95rem">
            Tip: verify on <a href="/Webpages/lyricsWeb.php<?= $selected_id ? ('?song_id=' . (int)$selected_id) : '' ?>" target="_blank" rel="noopener">Lyrics page</a> after saving.
          </p>
        </div>

        <div class="stack">
          <label for="lyrics"><strong>Lyrics</strong></label>
          <textarea id="lyrics" name="lyrics" placeholder="Paste lyrics here…" required><?= h($current_lyrics) ?></textarea>
        </div>
      </div>

      <div class="actions">
        <button class="btn-primary" type="submit">Save Lyrics</button>
      </div>
    </form>
  </main>

  <button id="toTop" type="button" aria-label="Scroll to top" title="Back to top">↑</button>
  <?php if (file_exists(__DIR__ . '/../components/footer.php')) { include __DIR__ . '/../components/footer.php'; } ?>

  <script>
    function onSongChange(sel) {
      var id = sel.value;
      if (!id) {
        return;
      }
      var url = new URL(window.location.href);
      url.searchParams.set('song_id', id);
      window.location.href = url.toString();
    }
  </script>
</body>

</html>