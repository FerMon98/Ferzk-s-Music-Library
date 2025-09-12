<?php
// Extract the YouTube video ID from many URL shapes
function youtube_id_from_url(?string $url): ?string {
    if (!$url) return null;
    $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
    if (preg_match($pattern, $url, $m)) {
        return $m[1];
    }
    return null;
}

// Pick a cover image for a song row
function cover_from_row(array $row): string {
    if (!empty($row['album_cover'])) return $row['album_cover'];
    if (!empty($row['link'])) {
        $id = youtube_id_from_url($row['link']);
        if ($id) return "https://i.ytimg.com/vi/$id/hqdefault.jpg";
    }
    return './Resources/Images/placeholder/cover-default.jpg';
}
