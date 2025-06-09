<?php
// --- LOGIKA UNTUK MEMUAT DATA SAAT EDIT (GET REQUEST) ---
$initial_data_json = '{"title":"","refrensi":"","status":"draf","created_at":"","updated_at":"","lines":[[{"note":"","lyric":""}]]}';
if (isset($_GET['file'])) {
    $file_to_load = basename($_GET['file']);
    $file_path = 'tabs/' . $file_to_load;
    if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) == 'json') {
        $initial_data_json = file_get_contents($file_path);
    }
}

// --- LOGIKA UNTUK MENYIMPAN DATA (POST REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $jsonPayload = file_get_contents('php://input');
    $data = json_decode($jsonPayload, true);

    $timestamp = date('c');

    $data['updated_at'] = $timestamp;

    if (empty($data['created_at'])) {
        $data['created_at'] = $timestamp;
    }

    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['title']) || !isset($data['lines'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Data JSON tidak valid.']);
        exit;
    }

    $tabsDir = 'tabs';
    if (!is_dir($tabsDir)) {
        mkdir($tabsDir, 0777, true);
    }

    $fileName = '';
    if (isset($data['existingFilename']) && !empty($data['existingFilename'])) {
        $safeFilename = basename($data['existingFilename']);
        $fileName = $tabsDir . '/' . $safeFilename;
        if (!file_exists($fileName)) {
            $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['title']);
            $fileName = $tabsDir . '/tab_' . ($safeTitle ?: 'untitled') . '_' . time() . '.json';
        }
        unset($data['existingFilename']);
    } else {
        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['title']);
        $fileName = $tabsDir . '/tab_' . ($safeTitle ?: 'untitled') . '_' . time() . '.json';
    }

    if (file_put_contents($fileName, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['status' => 'success', 'message' => 'Tab berhasil disimpan!', 'filename' => basename($fileName)]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file.']);
    }

    exit;
}
