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
            <div class="flex gap-2">
                <a href="album.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-5 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <i class="fas fa-book-open mr-2"></i>Lihat Album
                </a>
                <a href="creator.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Buat Baru
                </a>
            </div>
        </header>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
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
                            <tr class="border-t border-slate-200">
                                <td class="p-4 font-semibold"><?= $song['title'] ?></td>
                                <td class="p-4 flex justify-center items-center gap-4">
                                    <button class="preview-btn text-slate-600 hover:text-blue-600 transition-colors" data-song-content='<?= $song['content'] ?>'>
                                        <i class="fas fa-eye mr-1"></i> Pratinjau
                                    </button>
                                    <a href="creator.php?file=<?= urlencode($song['filename']) ?>" class="text-slate-600 hover:text-green-600 transition-colors">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <button class="delete-btn text-slate-600 hover:text-red-600 transition-colors" data-filename="<?= urlencode($song['filename']) ?>" data-title="<?= $song['title'] ?>">
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