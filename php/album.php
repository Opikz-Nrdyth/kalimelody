<?php
function get_all_songs_data()
{
    $tabsDir = 'tabs';
    $songs = [];
    if (!is_dir($tabsDir)) return [];

    $files = glob($tabsDir . '/*.json');
    sort($files); // Urutkan dari yang terlama ke terbaru
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        $status = $data['status'] ?? 'publish';
        if ($status === 'publish' && json_last_error() === JSON_ERROR_NONE) {
            $songs[] = $data;
        }
    }
    return $songs;
}

$all_songs_data = get_all_songs_data();
