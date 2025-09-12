<?php
// reset_and_seed.php
// Drops all relevant tables, recreates them via schema.sql, then seeds data.

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/connection.php';

// --- Drop existing tables (in dependency order) ---
$dropSql = "
    SET FOREIGN_KEY_CHECKS = 0;
    DROP TABLE IF EXISTS forum_comments;
    DROP TABLE IF EXISTS forum_posts;
    DROP TABLE IF EXISTS user_playlists;
    DROP TABLE IF EXISTS songs;
    DROP TABLE IF EXISTS users;
    DROP TABLE IF EXISTS contact;
    DROP TABLE IF EXISTS uploaded_files;
    SET FOREIGN_KEY_CHECKS = 1;
";

if (!$conn->multi_query($dropSql)) {
    exit("Drop error: " . $conn->error);
}
while ($conn->more_results() && $conn->next_result()) { /* flush */ }
echo "Dropped old tables.\n";

// --- Recreate schema ---
$schemaSql = file_get_contents(__DIR__ . '/schema.sql');
if (!$schemaSql) {
    exit("Could not read schema.sql\n");
}
if (!$conn->multi_query($schemaSql)) {
    exit("Schema error: " . $conn->error . "\n");
}
while ($conn->more_results() && $conn->next_result()) { /* flush */ }
echo "Schema recreated.\n";

// --- Include seed script ---
require __DIR__ . '/seed.php';
