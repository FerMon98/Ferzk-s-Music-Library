# Ferzk’s Music Library

A small full-stack PHP/MySQL music library where users can browse songs, play tracks, manage a personal playlist, and (optionally) add lyrics. Built to practice classic LAMP skills with a modern, responsive UI.

## Features

- 🔐 **Accounts & Auth**
  - Register/Login (PHP sessions)
  - Logout endpoint
  - Login guard with `?return` param so users come back to the page they were trying to open

- 🎧 **Music Library**
  - Browse the global songs catalog with cover art, artist, genre, duration, and YouTube link
  - Responsive playlists by genre/mood (grid expands to 4 columns on ultra-wide screens)
  - **Cover fallback**: when a song has no `album_cover`, artwork is derived from its YouTube link; final fallback is a local placeholder

- ➕ **My Playlist**
  - Add or remove songs from the user’s personal playlist
  - Floating controls (Prev / Play-Pause / Next)
  - **No autoplay** on initial page load (starts only after user action)

- 📝 **Lyrics**
  - **Private Add/Edit Lyrics** page (for logged-in users)
  - Paste lyrics and (optionally) store a `lyrics_source_url`
  - Lyrics page lists songs with available lyrics and displays them when selected

- 💬 **Forum** *(basic)*
  - Create simple posts and comments (for future expansion)

- ✉️ **Contact & Requests**
  - Contact form (stored)
  - Song request form to propose new songs for the catalog

## 🎥 Demo
<video src="./FerzkMusicLibraryDemo.mp4" controls width="600"></video>

## Tech Stack

- **Backend**: PHP 8+, mysqli
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS, vanilla JS
- **Editor/Dev**: VS Code (Live Server only for static), PHP built-in server/XAMPP for PHP
- **OS**: Works on Windows/macOS/Linux

## Project Structure

/php_setup_files
├─ connection.php # DB connection (local credentials)
├─ login.php # Login/Register (+ ?return)
└─ logOut.php # Session destroy → redirect

/Webpages
├─ index.php # Home
├─ musicplayer.php # Library grouped by genre
├─ userPlaylist.php # User’s playlist (no autoplay)
├─ lyricsWeb.php # View lyrics (requires login)
├─ add-lyrics.php # Add/Edit lyrics (private)
├─ formadd-remove.php # Song request form
├─ contact.php # Contact form
├─ forum.php # Simple forum demo
└─ ... # other pages

/components
├─ navbar.php
└─ footer.php

/Resources
├─ CSS/
│ ├─ style.css # Base, min-height:100vh, color gradient vibe
│ ├─ musicplayer.css # 3→4 columns @ ≥1600px, spacing
│ ├─ userPlaylist.css # Controls & responsive improvements
│ └─ lyricsWeb.css # Two-panel layout (list + lyrics)
├─ Images/
│ ├─ favicon/
│ └─ cover-placeholder.png
└─ javascript/
└─ playmusic.js


## Database (tables used)

- `songs`  
  `song_id` (PK), `title`, `artist`, `album`, `album_cover` (URL),  
  `genre`, `duration`, `lyrics`, `lyrics_source_url`, `link` (YouTube), `file_path` (mp3 path, optional)

- `users`  
  `id_user` (PK), `name`, `last_name`, `username`, `email`, `password` (bcrypt), `phone_number`

- `user_playlists`  
  `user_id`, `song_id` (composite PK) – links users to songs

- `contact`  
  `username`, `email`, `message`, `created_at`

- `forum_posts` / `forum_comments`  
  Minimal schema to support post + comment streams

- `uploaded_files` *(optional)*  
  Basic storage for uploaded assets

> If `lyrics_source_url` doesn’t exist, the app works without it; add later via:
> ```sql
> ALTER TABLE songs ADD COLUMN lyrics_source_url VARCHAR(255) NULL AFTER lyrics;
> ```

## Local Development

### 1) MySQL
Create the database and a user (or use `root` locally):

```sql
CREATE DATABASE ferzk_music_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

### 2) Configure PHP

Edit /php_setup_files/connection.php with your local credentials:
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ferzk_music_library";

### 3) Start a PHP dev server

From the project root:
php -S 127.0.0.1:8000

Open http://127.0.0.1:8000/index.php
Tip: Don’t use VS Code “Live Server” for PHP — it doesn’t execute PHP.

### 4) Seed some songs

Use Webpages/formadd-remove.php to add songs with YouTube links.

Covers will auto-derive from YouTube if album_cover is empty.

Paste lyrics via Webpages/add-lyrics.php (requires login).

Notable Implementation Details

Cover fallback:
Extracts YouTube video id from songs.link and uses
https://img.youtube.com/vi/<id>/maxresdefault.jpg with a local
placeholder fallback.

Auth redirects:
When a protected page is accessed while logged out, users are sent to
/php_setup_files/login.php?return=<original-url> and redirected back
after login.

No autoplay:
The user playlist page initializes quietly; playback only begins after a
user click.

Roadmap

Richer playlists (mood/tags)

Search & filters

Role-based moderation for lyrics/forum

Optional media storage (S3/Cloudinary) for artwork and audio

## License

Personal/educational project. Do not redistribute the embedded audio assets.
