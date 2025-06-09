<?php
// --- LOGIKA PHP UNTUK MENGHAPUS FILE ---

$view_status = isset($_GET['show']) && $_GET['show'] === 'draf' ? 'draf' : 'publish';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';


// --- LOGIKA PHP UNTUK MENAMPILKAN DAFTAR LAGU ---
function get_saved_tabs()
{
    $tabsDir = 'tabs';
    $songs = [];
    if (!is_dir($tabsDir)) return [];

    $files = glob($tabsDir . '/*.json');
    rsort($files); // Urutkan agar yang terbaru di atas
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['title'])) {
            $songs[] = [
                'filename' => basename($file),
                'title' => !empty($data['title']) ? htmlspecialchars($data['title']) : 'Tanpa Judul',
                'status' => $data['status'] ?? 'publish',
                'content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
            ];
        }
    }
    return $songs;
}

function handle_song_backup($fileName)
{
    $safeFileName = basename($fileName);
    $tabsDir = 'tabs';
    $backupsDir = 'backups';
    $sourceFile = $tabsDir . '/' . $safeFileName;

    if (!file_exists($sourceFile)) {
        header('Location: index.php?status=backup_failed&reason=file_not_found');
        exit;
    }

    // Buat direktori backup khusus untuk lagu ini jika belum ada
    $songBackupDir = $backupsDir . '/' . $safeFileName;
    if (!is_dir($songBackupDir)) {
        mkdir($songBackupDir, 0777, true);
    }

    // Buat nama file backup dengan timestamp
    $backupFileName = 'backup_' . date('Y-m-d_H-i-s') . '.json';
    $destinationFile = $songBackupDir . '/' . $backupFileName;

    if (copy($sourceFile, $destinationFile)) {
        header('Location: index.php');
        exit;
    } else {
        header('Location: index.php?status=backup_failed&reason=copy_failed');
        exit;
    }
}

// --- FUNGSI UNTUK MENANGANI RESTORE PER LAGU ---
function handle_song_restore($fileName)
{
    $safeFileName = basename($fileName);
    $tabsDir = 'tabs';
    $backupsDir = 'backups';
    $targetFile = $tabsDir . '/' . $safeFileName;
    $songBackupDir = $backupsDir . '/' . $safeFileName;

    if (!is_dir($songBackupDir)) {
        header('Location: index.php?status=restore_failed&reason=no_backups_for_this_song');
        exit;
    }

    // Cari file backup terbaru di dalam folder backup lagu ini
    $allBackups = scandir($songBackupDir, SCANDIR_SORT_DESCENDING);
    $latestBackupFile = '';
    foreach ($allBackups as $backup) {
        // TAMBAHKAN KONDISI 'strpos' DI SINI
        if ($backup !== '.' && $backup !== '..' && strpos($backup, 'before_restore_') === false) {
            $latestBackupFile = $backup;
            break;
        }
    }

    if (empty($latestBackupFile)) {
        header('Location: index.php?status=restore_failed&reason=no_backups_found');
        exit;
    }

    $restoreSource = $songBackupDir . '/' . $latestBackupFile;

    // (Opsional tapi direkomendasikan) Buat backup mikro sebelum menimpa
    copy($targetFile, $songBackupDir . '/before_restore_' . date('Y-m-d_H-i-s') . '.json');

    // Timpa file asli dengan file dari backup
    if (copy($restoreSource, $targetFile)) {
        return true;
    } else {
        return false;
    }
}

function get_restorable_songs()
{
    $tabsDir = 'tabs';
    $backupsDir = 'backups';

    if (!is_dir($backupsDir)) {
        return [];
    }

    // 1. Dapatkan semua nama file yang ada di folder /tabs
    $current_files_paths = glob($tabsDir . '/*.json');
    $current_files = array_map('basename', $current_files_paths);

    // 2. Dapatkan semua nama folder backup per-lagu
    $backup_dirs_paths = glob($backupsDir . '/*', GLOB_ONLYDIR);
    $backup_dirs = array_map('basename', $backup_dirs_paths);

    // 3. Cari perbedaannya: folder backup yang tidak memiliki file di /tabs
    $restorable_files = array_diff($backup_dirs, $current_files);

    return $restorable_files;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json');

    if (isset($_POST['filename'])) {
        // Keamanan: Gunakan basename() untuk mencegah directory traversal attack (../)
        $filenameToDelete = basename($_POST['filename']);
        $filePath = 'tabs/' . $filenameToDelete;

        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                echo json_encode(['status' => 'success', 'message' => 'Lagu berhasil dihapus.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus file di server.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nama file tidak diberikan.']);
    }
    exit; // Wajib ada agar tidak merender HTML
}

$saved_songs = get_saved_tabs();
$filtered_songs = array_filter($saved_songs, function ($song) use ($view_status) {
    return $song['status'] === $view_status;
});

$songs_to_display = $filtered_songs;
if (!empty($search_term)) {
    $songs_to_display = array_filter($filtered_songs, function ($song) use ($search_term) {
        return stripos($song['title'], $search_term) !== false;
    });
}

// --- CONTROLLER UTAMA UNTUK MENANGANI AKSI ---
if (isset($_GET['action']) && isset($_GET['file'])) {
    $file = $_GET['file'];
    if ($_GET['action'] === 'backup_song') {
        handle_song_backup($file);
    }
    if ($_GET['action'] === 'restore_song') {
        handle_song_restore($file);
    }
}

// Penanganan aksi POST dari modal restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'restore_selected' && isset($_POST['songs_to_restore']) && is_array($_POST['songs_to_restore'])) {
        $restored_count = 0;
        foreach ($_POST['songs_to_restore'] as $file_to_restore) {
            handle_song_restore($file_to_restore);
        }
        header('Location: index.php?status=restore_selected_success');
        exit;
    }
}
