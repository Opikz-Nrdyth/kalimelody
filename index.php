<?php
// --- LOGIKA PHP UNTUK MENGHAPUS FILE ---
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
                'content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
            ];
        }
    }
    return $songs;
}

$saved_songs = get_saved_tabs();

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
        if ($backup !== '.' && $backup !== '..') {
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
        return true; // Kembalikan true jika berhasil
    } else {
        return false; // Kembalikan false jika gagal
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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kalimelody - A Collection of Notes from the Heart</title>

    <meta name="description" content="Temukan ketenangan jiwa melalui Kalimelody, sebuah koleksi nada dan tulisan dari hati. Sebuah karya seni yang menenangkan oleh Karya Opik Studio.">

    <meta name="keywords" content="Kalimelody, kalimba, musik, ketenangan, jiwa, buku, seni, nada, Opik Studio, relaksasi, melodi hati">

    <meta name="author" content="Karya Opik Studio">

    <link rel="canonical" href="https://www.kalimelody.opikstudio.my.id">

    <meta property="og:title" content="Kalimelody - A Collection of Notes from the Heart">
    <meta property="og:description" content="Temukan ketenangan jiwa melalui Kalimelody, sebuah koleksi nada dan tulisan dari hati yang menenangkan.">
    <meta property="og:image" content="http://googleusercontent.com/image_generation_content/1">
    <meta property="og:url" content="https://www.kalimelody.opikstudio.my.id">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Kalimelody - A Collection of Notes from the Heart">
    <meta name="twitter:description" content="Temukan ketenangan jiwa melalui Kalimelody, sebuah koleksi nada dan tulisan dari hati yang menenangkan.">
    <meta name="twitter:image" content="http://googleusercontent.com/image_generation_content/1">
    <link rel="icon" href="/images/icon.png" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8;
        }

        .line-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 1rem;
            font-family: 'Courier New', Courier, monospace;
        }

        .slot-preview {
            background-color: #e9e9e9;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
            min-width: 40px;
        }

        .note-preview {
            display: block;
            font-weight: bold;
            font-size: 1em;
        }

        .lyric-preview {
            display: block;
            font-size: 0.85em;
            color: #555;
            margin-top: 2px;
        }

        /* Style untuk transisi dan mode fullscreen modal */
        #preview-modal .modal-container {
            transition: all 0.3s ease-in-out;
        }

        #preview-modal .modal-fullscreen {
            width: 100vw;
            height: 100vh;
            max-width: 100vw;
            max-height: 100vh;
            border-radius: 0;
        }
    </style>
</head>

<body class="antialiased text-slate-800">

    <div class="container mx-auto p-4 md:p-8 max-w-4xl">
        <header class="flex flex-wrap gap-4 justify-between items-center mb-6 pb-4 border-b border-slate-300">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-700 flex items-center">
                <i class="fa-solid fa-list-music text-blue-500 mr-3"></i>
                Daftar Lagu
            </h1>
            <div class="flex gap-2 flex-wrap">
                <button id="show-restore-modal-btn" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md">
                    <i class="fas fa-trash-restore mr-2"></i>Restore
                </button>
                <a href="album.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-5 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <i class="fas fa-book-open mr-2"></i>Lihat Album
                </a>
                <a href="creator.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Buat Baru
                </a>
            </div>
        </header>

        <div id="restore-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <h3 class="text-xl font-bold p-5 border-b">Restore Lagu dari Backup</h3>

                <form action="index.php" method="POST">
                    <input type="hidden" name="action" value="restore_selected">

                    <div id="restorable-songs-list" class="p-6 max-h-80 overflow-y-auto">

                        <?php
                        $restorable_songs = get_restorable_songs();
                        if (empty($restorable_songs)) {
                            echo '<p class="text-gray-500">Tidak ada lagu yang bisa direstore saat ini.</p>';
                        } else {
                            foreach ($restorable_songs as $song_file) {
                                // Ambil judul dari nama file untuk ditampilkan
                                $title_from_file = str_replace(['tab_', '.json'], '', $song_file);
                                $title_from_file = str_replace('_', ' ', $title_from_file);
                                // Potong timestamp jika ada
                                $title_display = preg_replace('/_\d+$/', '', $title_from_file);

                                echo '<label class="flex items-center space-x-3 mb-2">';
                                echo '<input type="checkbox" name="songs_to_restore[]" value="' . htmlspecialchars($song_file) . '" class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">';
                                echo '<span>' . htmlspecialchars(ucwords($title_display)) . '</span>';
                                echo '</label>';
                            }
                        }
                        ?>

                    </div>

                    <div class="flex justify-end p-4 border-t bg-slate-50 gap-3">
                        <button type="button" id="cancel-restore-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">Batal</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Restore Lagu Terpilih</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table id="song-list-table" class="w-full">
                <thead class="bg-slate-100 text-left text-slate-600">
                    <tr>
                        <th class="p-4">Judul Lagu</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($saved_songs)): ?>
                        <tr id="no-songs-row">
                            <td colspan="2" class="p-8 text-center text-slate-500">
                                Belum ada lagu yang disimpan. <a href="creator.php" class="text-blue-500 hover:underline">Mulai buat sekarang!</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($saved_songs as $song): ?>
                            <tr class="border-t border-slate-200 cursor-pointer hover:bg-gray-200">
                                <td class="p-4 font-semibold whitespace-nowrap preview-btn" data-song-content='<?= $song['content'] ?>'><?= $song['title'] ?></td>
                                <td class="p-4 flex justify-center items-center gap-4">
                                    <a href="index.php?action=backup_song&file=<?= urlencode($song['filename']) ?>" class="text-transparent backup-btn whitespace-nowrap">
                                        <i class="fas fa-save mr-1"></i>Backup
                                    </a>

                                    <a href="index.php?action=restore_song&file=<?= urlencode($song['filename']) ?>" onclick="return confirm('PERINGATAN: Lagu ini akan ditimpa dengan versi backup terakhir. Lanjutkan?')" class="text-gray-600 hover:text-orange-500 whitespace-nowrap">
                                        <i class="fas fa-undo mr-1"></i>Restore
                                    </a>
                                    <a href="creator.php?file=<?= urlencode($song['filename']) ?>" class="text-slate-600 hover:text-green-600 transition-colors whitespace-nowrap">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <button class="delete-btn text-slate-600 hover:text-red-600 transition-colors whitespace-nowrap" data-filename="<?= urlencode($song['filename']) ?>" data-title="<?= $song['title'] ?>">
                                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="preview-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="modal-container bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b flex-shrink-0">
                <h3 id="modal-title" class="text-2xl font-bold">Pratinjau</h3>
                <div class="flex items-center gap-4">
                    <button id="modal-fullscreen-btn" title="Layar Penuh" class="text-lg text-slate-500 hover:text-slate-800">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button id="modal-close-btn" title="Tutup" class="text-2xl text-slate-500 hover:text-slate-800">&times;</button>
                </div>
            </div>
            <div id="modal-content" class="p-6 text-sm overflow-y-auto">
            </div>
        </div>
    </div>

    <div id="notification" class="fixed top-5 right-5 text-white py-2 px-4 rounded-lg shadow-xl transition-all duration-300 opacity-0 -translate-y-10 z-50"></div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('preview-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalContent = document.getElementById('modal-content');
            const modalCloseBtn = document.getElementById('modal-close-btn');
            const songListTable = document.getElementById('song-list-table');
            const notification = document.getElementById('notification');
            // Tombol fullscreen baru
            const modalFullscreenBtn = document.getElementById('modal-fullscreen-btn');
            const showRestoreModalBtn = document.getElementById('show-restore-modal-btn');
            const restoreModal = document.getElementById('restore-modal');
            const cancelRestoreBtn = document.getElementById('cancel-restore-btn');
            const backupButtons = document.querySelectorAll('.backup-btn');
            const DOUBLE_CLICK_THRESHOLD = 400;

            backupButtons.forEach(button => {
                // Gunakan 'lastClick' sebagai penanda waktu klik terakhir untuk setiap tombol
                button.dataset.lastClick = 0;

                button.addEventListener('click', function(event) {
                    const currentTime = new Date().getTime();
                    const lastClickTime = parseInt(button.dataset.lastClick);

                    // Cek selisih waktu antara klik sekarang dan klik terakhir
                    if (currentTime - lastClickTime < DOUBLE_CLICK_THRESHOLD) {
                        // INI ADALAH DOUBLE-CLICK!
                        // Biarkan aksi default (pindah halaman) berjalan.
                        // Reset waktu klik agar urutan dimulai lagi.
                        button.dataset.lastClick = 0;
                        // Anda bisa tambahkan konfirmasi final di sini jika mau
                        if (!confirm('Konfirmasi backup untuk lagu ini?')) {
                            event.preventDefault(); // Batalkan jika pengguna menekan "Cancel"
                        }
                    } else {
                        // INI ADALAH KLIK PERTAMA (ATAU KLIK YANG TERLALU LAMBAT)
                        // 1. Cegah aksi default link agar tidak pindah halaman
                        event.preventDefault();

                        // 2. Simpan waktu klik saat ini pada tombol
                        button.dataset.lastClick = currentTime;

                        // 3. Beri feedback visual kepada pengguna (opsional tapi sangat disarankan)
                        const originalHTML = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Klik lagi!';

                        // 4. Kembalikan teks tombol ke semula setelah beberapa saat
                        setTimeout(() => {
                            // Hanya kembalikan jika belum ada klik kedua
                            if (parseInt(button.dataset.lastClick) !== 0) {
                                button.innerHTML = originalHTML;
                                button.dataset.lastClick = 0; // Reset
                            }
                        }, 1500); // Reset setelah 1.5 detik
                    }
                });
            });

            // Modal Controller
            showRestoreModalBtn.addEventListener('click', () => {
                restoreModal.classList.remove('hidden');
            });
            cancelRestoreBtn.addEventListener('click', () => {
                restoreModal.classList.add('hidden');
            });
            restoreModal.addEventListener('click', (e) => {
                if (e.target === dom.restoreModal) {
                    restoreModal.classList.add('hidden');
                }
            });

            // --- EVENT LISTENERS ---
            songListTable.addEventListener('click', async (e) => {
                /* ... (fungsi ini tidak berubah) ... */
                const previewBtn = e.target.closest('.preview-btn');
                const deleteBtn = e.target.closest('.delete-btn');

                if (previewBtn) {
                    const songDataJSON = previewBtn.dataset.songContent;
                    const songData = JSON.parse(songDataJSON);
                    modalTitle.textContent = songData.title || 'Pratinjau';
                    modalContent.innerHTML = formatPreviewHTML(songData);
                    modal.classList.remove('hidden');
                }

                if (deleteBtn) {
                    const title = deleteBtn.dataset.title;
                    const filename = deleteBtn.dataset.filename;
                    if (!confirm(`Apakah Anda yakin ingin menghapus lagu "${title}"?`)) return;

                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('filename', filename);
                    try {
                        const response = await fetch('index.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        if (response.ok && result.status === 'success') {
                            showNotification(result.message, false);
                            deleteBtn.closest('tr').remove();
                            if (songListTable.querySelector('tbody tr') === null) {
                                const tbody = songListTable.querySelector('tbody');
                                tbody.innerHTML = `<tr id="no-songs-row"><td colspan="2" class="p-8 text-center text-slate-500">Belum ada lagu yang disimpan.</td></tr>`;
                            }
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (error) {
                        showNotification(error.message || 'Terjadi kesalahan.', true);
                    }
                }
            });

            // Event listener untuk tombol fullscreen
            modalFullscreenBtn.addEventListener('click', () => {
                const modalContainer = modal.querySelector('.modal-container');
                const icon = modalFullscreenBtn.querySelector('i');

                modalContainer.classList.toggle('modal-fullscreen');

                if (modalContainer.classList.contains('modal-fullscreen')) {
                    icon.classList.remove('fa-expand');
                    icon.classList.add('fa-compress');
                    modalFullscreenBtn.title = 'Kembali ke normal';
                } else {
                    icon.classList.remove('fa-compress');
                    icon.classList.add('fa-expand');
                    modalFullscreenBtn.title = 'Layar Penuh';
                }
            });

            // --- FUNGSI-FUNGSI HELPER ---
            function closeModal() {
                // Pastikan keluar dari mode fullscreen saat ditutup
                const modalContainer = modal.querySelector('.modal-container');
                const icon = modalFullscreenBtn.querySelector('i');
                if (modalContainer.classList.contains('modal-fullscreen')) {
                    modalContainer.classList.remove('modal-fullscreen');
                    icon.classList.remove('fa-compress');
                    icon.classList.add('fa-expand');
                    modalFullscreenBtn.title = 'Layar Penuh';
                }
                modal.classList.add('hidden');
            }
            modalCloseBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            function formatPreviewHTML(tabData) {
                /* ... (fungsi ini tidak berubah) ... */
                let htmlOutput = '';
                tabData.lines.forEach(line => {
                    let lineHTML = '<div class="line-preview">';
                    line.forEach(slot => {
                        lineHTML += `<div class="slot-preview"><span class="note-preview">${slot.note || '&nbsp;'}</span><span class="lyric-preview">${slot.lyric || '&nbsp;'}</span></div>`;
                    });
                    lineHTML += '</div>';
                    htmlOutput += lineHTML;
                });
                return htmlOutput;
            }

            function showNotification(message, isError = false) {
                /* ... (fungsi ini tidak berubah) ... */
                notification.textContent = message;
                notification.className = 'fixed top-5 right-5 text-white py-2 px-4 rounded-lg shadow-xl transition-all duration-300 z-50 ' + (isError ? 'bg-red-500' : 'bg-green-500');
                notification.classList.remove('opacity-0', '-translate-y-10');
                setTimeout(() => notification.classList.add('opacity-0', '-translate-y-10'), 3000);
            }
        });
    </script>

</body>

</html>