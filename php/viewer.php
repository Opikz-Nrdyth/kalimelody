<?php
function get_published_songs()
{
    $tabsDir = 'tabs';
    $songs = [];
    if (!is_dir($tabsDir)) return [];

    $files = glob($tabsDir . '/*.json');
    sort($files);

    foreach ($files as $file) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $status = $data['status'] ?? 'publish';
            if ($status === 'publish') {
                $data['filename'] = pathinfo($file, PATHINFO_FILENAME);
                $songs[] = $data;
            }
        }
    }
    return $songs;
}

$song_data = null;
$recommendations = [];
$error_message = '';

if (isset($_GET['song'])) {
    $file_from_url = basename($_GET['song']);
    $file_path = 'tabs/' . "tab_" . $file_from_url . '.json';

    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        $song_data = json_decode($content, true);

        if (($song_data['status'] ?? 'publish') !== 'publish') {
            $song_data = null;
            $error_message = 'Lagu ini masih dalam status Draf dan tidak dapat ditampilkan secara publik.';
        } else {
            $all_published_songs = get_published_songs();
            $other_songs = array_filter($all_published_songs, function ($song) use ($song_data) {
                return $song['title'] !== $song_data['title'];
            });
            shuffle($other_songs);
            $recommendations = array_slice($other_songs, 0, 20);
        }
    } else {
        $error_message = 'Lagu yang Anda cari tidak dapat ditemukan.';
    }
} else {
    $error_message = 'Tidak ada lagu yang dipilih untuk ditampilkan.';
}
