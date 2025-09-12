<?php
// seed.php — ensure schema, seed user, scan MP3s, enrich with YT meta, sample rows
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/connection.php';

/* Util */
function youtube_id_from_url(?string $url): ?string {
    if (!$url) return null;
    $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
    if (preg_match($pattern, $url, $m)) return $m[1];
    return null;
}
function yt_cover(?string $link): ?string {
    $id = youtube_id_from_url($link);
    return $id ? "https://i.ytimg.com/vi/$id/hqdefault.jpg" : null;
}
function prep_or_die(mysqli $conn, string $sql): mysqli_stmt {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error . "\nSQL: $sql\n");
    }
    return $stmt;
}

/* 0) Ensure schema */
$schemaSql = file_get_contents(__DIR__ . '/schema.sql');
if ($schemaSql === false) die("Could not read schema.sql\n");
if (!$conn->multi_query($schemaSql)) die("Schema error: " . $conn->error . "\n");
while ($conn->more_results() && $conn->next_result()) {}

/* 1) Seed base user (fully parameterized) */
$findUser = prep_or_die($conn, "SELECT id_user FROM users WHERE email=?");
$email = 'fernanda@ftest.com';
$findUser->bind_param('s', $email);
$findUser->execute();
$findUser->store_result();

if ($findUser->num_rows === 0) {
    $findUser->close();
    $name = 'Fernanda';
    $last = 'Montalvan';
    $username = 'fernanda';
    $passhash = password_hash('123456', PASSWORD_DEFAULT);
    $phone = '+34999999999';

    $insUser = prep_or_die($conn,
        "INSERT INTO users (name, last_name, username, email, password, phone_number)
         VALUES (?, ?, ?, ?, ?, ?)");
    $insUser->bind_param('ssssss', $name, $last, $username, $email, $passhash, $phone);
    if ($insUser->execute()) {
        echo "User seeded: $email / 123456\n";
    } else {
        echo "User seed failed: " . $insUser->error . "\n";
    }
    $insUser->close();
} else {
    echo "User already exists: $email\n";
    $findUser->close();
}

/* 2) Scan /songs and insert missing */
$songsDir = realpath(__DIR__ . '/../songs');
if ($songsDir) {
    $files = glob($songsDir . '/*.mp3');
    if ($files) {
        $chkPath = prep_or_die($conn, "SELECT song_id FROM songs WHERE file_path=?");
        $insSong = prep_or_die($conn, "INSERT INTO songs (title, genre, file_path) VALUES (?, 'Unknown', ?)");

        foreach ($files as $full) {
            $bn    = basename($full);
            $title = preg_replace('/\.mp3$/i','', $bn);
            $title = str_replace(['_', '-'], ' ', $title);
            $path  = 'songs/' . $bn;

            $chkPath->bind_param('s', $path);
            $chkPath->execute();
            $chkPath->store_result();
            if ($chkPath->num_rows === 0) {
                $insSong->bind_param('ss', $title, $path);
                if ($insSong->execute()) echo "Inserted song by file: $title\n";
            } else {
                echo "Song exists: $bn\n";
            }
        }
        $chkPath->close();
        $insSong->close();
    }
}

/* 3) Enrich with YouTube link/artist/genre/cover */
$known = [
  [ 'match' => 'Holding Out For A Hero', 'artist' => 'Bonnie Tyler',        'genre' => 'Pop Rock, 80s',
    'link' => 'https://youtu.be/bWcASV2sey0?si=G_n02uuI5r6lcTxb' ],
  [ 'match' => 'One More Time',          'artist' => 'Daft Punk',           'genre' => 'Electronic, Dance',
    'link' => 'https://youtu.be/FGBhQbmPwH8?si=BJidy8GU6yX9_LmU' ],
  [ 'match' => "I Don't Want To Miss A Thing", 'artist' => 'Aerosmith',     'genre' => 'Rock, Ballad',
    'link' => 'https://youtu.be/JkK8g6FMEXE?si=UXAHTu0IRwWWlhKx' ],
  [ 'match' => "CAN'T STOP THE FEELING!", 'artist' => 'Justin Timberlake',  'genre' => 'Pop',
    'link' => 'https://youtu.be/ru0K8uYEZWw?si=m3TiJPS3LjZ76xf0' ],
  [ 'match' => "U Can't Touch This",      'artist' => 'M.C. Hammer',         'genre' => 'Hip Hop',
    'link' => 'https://youtu.be/otCpCn0l4Wo?si=Pz7zy4nl7LA9gXhf' ],
  [ 'match' => 'Smooth Criminal',         'artist' => 'Michael Jackson',     'genre' => 'Pop',
    'link' => 'https://youtu.be/h_D3VFfhvs4?si=5fOCEFQ017q2BLM2' ],
  [ 'match' => 'Livin La Vida Loca',      'artist' => 'Ricky Martin',        'genre' => 'Latin Pop',
    'link' => 'https://youtu.be/p47fEXGabaY?si=iWkvbKg8sMdeyr7v' ],
  [ 'match' => 'Murder On The Dancefloor','artist' => 'Sophie Ellis-Bextor', 'genre' => 'Dance Pop',
    'link' => 'https://youtu.be/hAx6mYeC6pY?si=P_AlntsvoxLrNBvg' ],
  [ 'match' => 'Part Time Lover',         'artist' => 'Stevie Wonder',       'genre' => 'R&B',
    'link' => 'https://youtu.be/bgmMpT_m-t4?si=uL_CtfrSEpxjqlX3' ],
  [ 'match' => 'YMCA',                    'artist' => 'Village People',      'genre' => 'Disco',
    'link' => 'https://youtu.be/CS9OO0S5w2k?si=UQGzAclHS8mr7-aU' ],
  [ 'match' => 'Somebody Told Me',        'artist' => 'The Killers',         'genre' => 'Alternative Rock',
    'link' => 'https://youtu.be/Y5fBdpreJiU?si=yLspamA1_wX1P-T-' ],
];

$findTitle = prep_or_die($conn, "SELECT song_id FROM songs WHERE title LIKE ? LIMIT 1");
$upd = prep_or_die($conn, "UPDATE songs SET artist=?, genre=?, link=?, album_cover=? WHERE song_id=?");
$ins = prep_or_die($conn, "INSERT INTO songs (title, artist, genre, link, album_cover) VALUES (?, ?, ?, ?, ?)");

foreach ($known as $k) {
    $cover = yt_cover($k['link']);
    $like  = '%' . $k['match'] . '%';

    $findTitle->bind_param('s', $like);
    $findTitle->execute();
    $res = $findTitle->get_result();

    if ($row = $res->fetch_assoc()) {
        $id = (int)$row['song_id'];
        $upd->bind_param('ssssi', $k['artist'], $k['genre'], $k['link'], $cover, $id);
        $upd->execute();
        echo "Enriched: {$k['match']}\n";
    } else {
        $title = $k['match'];
        $ins->bind_param('sssss', $title, $k['artist'], $k['genre'], $k['link'], $cover);
        $ins->execute();
        echo "Inserted YT-only song: {$k['match']}\n";
    }
}

/* 4) Sample rows */
$conn->query("INSERT INTO contact (username,email,message) VALUES ('TestUser','test@example.com','Hello! This is a sample contact message.')");
$conn->query("INSERT INTO forum_posts (username,song_title,post_content) VALUES ('MusicFan01','Holding Out For A Hero','This song always pumps me up!')");
$postId = $conn->insert_id;
if ($postId) $conn->query("INSERT INTO forum_comments (post_id,username,comment_content) VALUES ($postId,'ReplyGuy','Totally agree, it’s a classic!')");
$conn->query("INSERT INTO uploaded_files (file_name,file_path) VALUES ('demo.txt','uploads/demo.txt')");

$conn->close();
echo "Seeding complete with YouTube covers & sample rows.\n";
