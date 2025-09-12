<?php
// Webpages/lyricsWeb.php
declare(strict_types=1);

session_start();  // Start the session
require __DIR__ . '/../php_setup_files/connection.php';
$conn->set_charset('utf8mb4');

// --- Login guard (keep page private) ---
if (empty($_SESSION['user_id'])) {
    $loginUrl = '/php_setup_files/login.php?return=' . urlencode($_SERVER['REQUEST_URI']);
    header("Location: $loginUrl");
    exit();
}

// --- Helper: detect optional lyrics_source_url column ---
function column_exists(mysqli $conn, string $table, string $column): bool
{
    // Safe lookup without prepared SHOW
    $tableEsc = $conn->real_escape_string($table);
    $colEsc   = $conn->real_escape_string($column);

    $sql = "
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = '{$tableEsc}'
          AND COLUMN_NAME  = '{$colEsc}'
        LIMIT 1
    ";
    if (!$res = $conn->query($sql)) {
        // If the check fails for any reason, assume column not present
        return false;
    }
    return (bool) $res->fetch_row();
}


$has_source_url = column_exists($conn, 'songs', 'lyrics_source_url');

// --- Fetch songs (only those with lyrics) ---
$sql = "
    SELECT song_id, title, artist, album, album_cover, file_path, lyrics"
    . ($has_source_url ? ", lyrics_source_url" : "") .
    "   FROM songs
    WHERE lyrics IS NOT NULL AND TRIM(lyrics) <> ''
    ORDER BY title ASC";

$result = $conn->query($sql);
$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}

function youtube_id_from_url(?string $url): ?string {
    if (!$url) return null;
    $url = trim($url);
    // Short links: youtu.be/VIDEOID
    if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];
    // Watch links: youtube.com/watch?v=VIDEOID
    if (preg_match('~[?&]v=([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];
    // Shorts, embed, v/ forms
    if (preg_match('~/shorts/([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];
    if (preg_match('~/embed/([A-Za-z0-9_-]{6,})~i', $url, $m))  return $m[1];
    if (preg_match('~/v/([A-Za-z0-9_-]{6,})~i', $url, $m))      return $m[1];
    return null;
}

function cover_src(array $s): string {
    // 1) DB album_cover
    $raw = isset($s['album_cover']) ? trim((string)$s['album_cover']) : '';
    if ($raw !== '') {
        if (preg_match('#^https?://#i', $raw)) return $raw;              // absolute URL
        return '/' . ltrim($raw, '/');                                    // local path → root-absolute
    }
    // 2) Derive from YouTube link
    $link = $s['link'] ?? ($s['youtube_link'] ?? null);
    if ($id = youtube_id_from_url(is_string($link) ? $link : null)) {
        // Try max-res; YouTube serves fallback if not available
        return "https://img.youtube.com/vi/{$id}/maxresdefault.jpg";
    }
    // 3) Local placeholder
    return '/../Resources/Images/cover-placeholder.png';
}


// Preselect if arriving from add-lyrics.php?song_id=...
$preselect_id = isset($_GET['song_id']) ? (int)$_GET['song_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Fernanda">
    <meta name="description" content="Ferzk's Music Library - Lyrics">
    <meta name="keywords" content="music, lyrics, Ferzk, song lyrics">
    <title>Song Lyrics</title>
    <!-- Use root-absolute to avoid path issues -->
    <link rel="stylesheet" href="/Resources/CSS/lyricsWeb.css">
    <link rel="shortcut icon" href="/./Resources/Images/favicon/icons8-music.svg" type="image/x-icon">
    <style>
        /* Small helpers in case lyricsWeb.css misses these */
        .lyrics-top-actions {
            margin: .5rem 0 1rem;
        }

        .song-line {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .tiny-edit {
            font-size: .85rem;
            color: #fff;
            opacity: .9;
            border: 1px solid rgba(255, 255, 255, .45);
            border-radius: 999px;
            padding: .15rem .5rem;
            text-decoration: none;
        }

        .tiny-edit:hover {
            background: #000;
            opacity: 1;
        }

        #lyricsContent pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
        }
    </style>
</head>

<body>
    <header>
        <h1>Song Lyrics</h1>
        <?php include __DIR__ . '/../components/navbar.php'; ?>
    </header>

    <main>
        <h2>Available Song Lyrics</h2>

        <!-- Top action: Add / Edit Lyrics (user is logged-in due to guard) -->
        <div class="lyrics-top-actions">
            <a class="btn add" href="/Webpages/add-lyrics.php">➕ Add / Edit Lyrics</a>
        </div>

        <section>
            <?php if (count($songs) > 0): ?>
                <div id="songList">
                    <div class="scroll">
                        <ul>
                            <?php foreach ($songs as $song): ?>
                                <li>
                                    <div class="left">
                                        <img src="<?= htmlspecialchars(cover_src($song)) ?>" alt="Album Cover">
                                        <div class="song-line">
                                            <a href="javascript:void(0)" onclick="showLyrics(<?= (int)$song['song_id'] ?>)">
                                                <?= htmlspecialchars($song['title']) ?><?= !empty($song['artist']) ? ' by ' . htmlspecialchars($song['artist']) : '' ?>
                                            </a>
                                            <a class="tiny-edit" href="/Webpages/add-lyrics.php?song_id=<?= (int)$song['song_id'] ?>">Edit</a>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div id="songLyrics">
                    <h3 id="lyricsTitle">Song Lyrics</h3>
                    <div class="scroll">
                        <div id="lyricsContent">
                            <p>Select a song to view its lyrics.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p>No lyrics available for the songs in the library.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script>
        // Safely expose songs to JS
        const SONGS = <?= json_encode(
                            $songs,
                            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                        ) ?>;

        // Display lyrics safely inside a <pre>
        function showLyrics(songId) {
            const s = SONGS.find(x => String(x.song_id) === String(songId));
            if (!s) return;

            document.getElementById('lyricsTitle').textContent = (s.title || 'Song') + " Lyrics";

            const box = document.getElementById('lyricsContent');
            box.innerHTML = ''; // clear

            const pre = document.createElement('pre');
            pre.textContent = s.lyrics || '(No lyrics found)';
            box.appendChild(pre);

            // Optional source link if your DB has lyrics_source_url
            if (Object.prototype.hasOwnProperty.call(s, 'lyrics_source_url') && s.lyrics_source_url) {
                const p = document.createElement('p');
                p.style.marginTop = '.5rem';
                p.innerHTML = '<small>Source: <a href="' + s.lyrics_source_url +
                    '" target="_blank" rel="noopener">Genius</a></small>';
                box.appendChild(p);
            }
        }

        // If you came here with ?song_id=123, open it automatically
        <?php if ($preselect_id > 0): ?>
            document.addEventListener('DOMContentLoaded', () => showLyrics(<?= (int)$preselect_id ?>));
        <?php endif; ?>
    </script>
</body>

</html>