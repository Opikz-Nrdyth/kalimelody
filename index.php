<?php require_once("./php/index.php") ?>
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
    <link rel="icon" href="/assets/images/icon.png" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/index.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        .dark #theme-toggle-light-icon {
            display: none;
        }

        #theme-toggle-dark-icon {
            display: none;
        }

        .dark #theme-toggle-dark-icon {
            display: block;
        }
    </style>
</head>

<body class="antialiased bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 transition-colors duration-300">

    <div class="container mx-auto p-4 md:p-8 max-w-4xl">
        <header class="flex flex-wrap gap-4 justify-between items-center mb-6 pb-4 border-b border-slate-300 dark:border-slate-700">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-700 dark:text-slate-200 flex items-center">
                <i class="fa-solid fa-list-music text-blue-500 dark:text-blue-400 mr-3"></i>
                Daftar <?= ($view_status === 'draf') ? 'Draf' : 'Lagu' ?>
            </h1>
            <div class="flex gap-2 flex-wrap items-center">
                <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                    <i id="theme-toggle-light-icon" class="fas fa-sun"></i>
                    <i id="theme-toggle-dark-icon" class="fas fa-moon"></i>
                </button>
                <button id="show-restore-modal-btn" class="bg-gray-500 hover:bg-gray-600 dark:bg-slate-600 dark:hover:bg-slate-500 text-white font-bold py-2 px-4 rounded-lg shadow-md">
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
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-lg w-full">
                <h3 class="text-xl font-bold p-5 border-b dark:border-slate-700">Restore Lagu dari Backup</h3>
                <form action="index.php" method="POST">
                    <input type="hidden" name="action" value="restore_selected">
                    <div id="restorable-songs-list" class="p-6 max-h-80 overflow-y-auto">
                        <?php
                        $restorable_songs = get_restorable_songs();
                        if (empty($restorable_songs)) {
                            echo '<p class="text-gray-500 dark:text-gray-400">Tidak ada lagu yang bisa direstore saat ini.</p>';
                        } else {
                            foreach ($restorable_songs as $song_file) {
                                $title_from_file = str_replace(['tab_', '.json'], '', $song_file);
                                $title_from_file = str_replace('_', ' ', $title_from_file);
                                $title_display = preg_replace('/_\d+$/', '', $title_from_file);
                                echo '<label class="flex items-center space-x-3 mb-2 cursor-pointer">';
                                echo '<input type="checkbox" name="songs_to_restore[]" value="' . htmlspecialchars($song_file) . '" class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600">';
                                echo '<span>' . htmlspecialchars(ucwords($title_display)) . '</span>';
                                echo '</label>';
                            }
                        }
                        ?>
                    </div>
                    <div class="flex justify-end p-4 border-t bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50 gap-3">
                        <button type="button" id="cancel-restore-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-slate-600 dark:hover:bg-slate-500 dark:text-slate-200 font-bold py-2 px-4 rounded-lg">Batal</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Restore Lagu Terpilih</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-4">
            <form action="index.php" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari berdasarkan judul..." class="w-full p-3 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:placeholder-gray-400 dark:focus:ring-blue-500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <input type="hidden" name="show" value="<?= htmlspecialchars($view_status) ?>">
                <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:bg-blue-700">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md overflow-x-auto">
            <table id="song-list-table" class="w-full">
                <thead class="bg-slate-100 dark:bg-slate-700/50 text-left text-slate-600 dark:text-slate-400">
                    <tr>
                        <th class="p-4">Judul Lagu</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($songs_to_display)): ?>
                        <tr id="no-songs-row">
                            <td colspan="2" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                Belum ada lagu yang disimpan. <a href="creator.php" class="text-blue-500 dark:text-blue-400 hover:underline">Mulai buat sekarang!</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($songs_to_display as $song): ?>
                            <tr class="border-t border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-gray-200 dark:hover:bg-slate-700/50">
                                <td class="p-4 font-semibold whitespace-nowrap"><a href="viewer.php?song=<?= str_replace([".json", "tab_"], ["", ""], urlencode($song['filename'])) ?>"><?= $song['title'] ?></a></td>
                                <td class="p-4 flex justify-center items-center gap-4 text-slate-600 dark:text-slate-400">
                                    <a href="index.php?action=backup_song&file=<?= urlencode($song['filename']) ?>" class="text-transparent backup-btn whitespace-nowrap">
                                        <i class="fas fa-save mr-1"></i>Backup
                                    </a>
                                    <div class="whitespace-nowrap preview-btn" data-song-content='<?= $song['content'] ?>'><i class="fa-solid fa-eye"></i> Preview</div>
                                    <a href="index.php?action=restore_song&file=<?= urlencode($song['filename']) ?>" onclick="return confirm('PERINGATAN: Lagu ini akan ditimpa dengan versi backup terakhir. Lanjutkan?')" class="hover:text-orange-500 dark:hover:text-orange-400 whitespace-nowrap">
                                        <i class="fas fa-undo mr-1"></i>Restore
                                    </a>
                                    <a href="creator.php?file=<?= urlencode($song['filename']) ?>" class="hover:text-green-600 dark:hover:text-green-500 transition-colors whitespace-nowrap">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <button class="delete-btn hover:text-red-600 dark:hover:text-red-500 transition-colors whitespace-nowrap" data-filename="<?= urlencode($song['filename']) ?>" data-title="<?= $song['title'] ?>">
                                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-8">
            <?php
            // Menyiapkan kelas yang sama untuk kedua tombol
            $toggle_view_classes = "bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200";
            if ($view_status === 'publish'): ?>
                <a href="index.php?show=draf" class="<?= $toggle_view_classes ?>">
                    <i class="fas fa-eye-slash mr-2"></i>Tampilkan Draf
                </a>
            <?php else: ?>
                <a href="index.php" class="<?= $toggle_view_classes ?>">
                    <i class="fas fa-eye mr-2"></i>Tampilkan Publish
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="preview-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="modal-container bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b dark:border-slate-700 flex-shrink-0">
                <div class="flex flex-col items-start gap-1">
                    <h3 id="modal-title" class="text-2xl font-bold">Pratinjau</h3>
                    <a href="" target="_blank" id="modal-refrensi" class="font-bold hidden text-blue-600 dark:text-blue-400"><i class="fa-solid fa-arrow-up-right-from-square"></i> Refrensi</a>
                </div>
                <div class="flex items-center gap-4">
                    <button id="modal-fullscreen-btn" title="Layar Penuh" class="text-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button id="modal-close-btn" title="Tutup" class="text-2xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100">&times;</button>
                </div>
            </div>
            <div id="modal-content" class="p-6 text-sm overflow-y-auto">
            </div>
        </div>
    </div>

    <div id="notification" class="fixed top-5 right-5 text-white py-2 px-4 rounded-lg shadow-xl transition-all duration-300 opacity-0 -translate-y-10 z-50"></div>

    <script src="/assets/js/index.js"></script>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        // Cek tema saat halaman dimuat
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        // Tambahkan event listener ke tombol pengalih tema
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('color-theme', isDark ? 'dark' : 'light');
            });
        }
    </script>
</body>

</html>