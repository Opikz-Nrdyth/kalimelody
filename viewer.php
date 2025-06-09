<?php
require_once("./php/viewer.php");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Karya Opik Studio">

    <?php if ($song_data):
        $title = htmlspecialchars($song_data['title']);
        $filename_for_url = str_replace("tab_", "", urlencode($file_from_url));
        $canonical_url = "https://www.kalimelody.opikstudio.my.id/viewer.php?song=" . $filename_for_url;
        $description = "Pelajari cara memainkan '" . $title . "' di kalimba dengan notasi angka yang mudah diikuti dari Kalimelody. Tabs lengkap untuk pemula dan mahir.";
        $keywords = "notasi angka " . $title . ", tabs kalimba " . $title . ", " . $title . " kalimba, Kalimelody, notasi kalimba, belajar kalimba";
        $og_image = "http://googleusercontent.com/image_generation_content/1";
    ?>
        <title><?= $title ?> - Notasi Angka Kalimba | Kalimelody</title>
        <meta name="description" content="<?= $description ?>">
        <meta name="keywords" content="<?= $keywords ?>">
        <link rel="canonical" href="<?= $canonical_url ?>">
        <meta property="og:title" content="<?= $title ?> | Kalimelody">
        <meta property="og:description" content="<?= $description ?>">
        <meta property="og:url" content="<?= $canonical_url ?>">
        <meta property="og:image" content="<?= $og_image ?>">
        <meta property="og:type" content="article">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= $title ?> | Kalimelody">
        <meta name="twitter:description" content="<?= $description ?>">
        <meta name="twitter:image" content="<?= $og_image ?>">
    <?php else: ?>
        <title>Lagu Tidak Ditemukan | Kalimelody</title>
        <meta name="description" content="Lagu yang Anda cari tidak ditemukan di koleksi Kalimelody.">
        <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>

    <link rel="icon" href="/assets/images/icon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="/assets/css/viewer.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>

<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 transition-colors duration-300">
    <div class="container mx-auto p-4 md:p-8 max-w-4xl">
        <nav class="mb-8 flex justify-between items-center">
            <a href="index.php" class="flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold"><i class="fas fa-arrow-left mr-2"></i><span class="hidden md:block">Kembali ke Daftar Lagu</span></a>

            <div>
                <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                    <i id="theme-toggle-light-icon" class="fas fa-sun"></i>
                    <i id="theme-toggle-dark-icon" class="fas fa-moon"></i>
                </button>
                <a href="album.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-5 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <i class="fas fa-book-open mr-2"></i>Lihat Album
                </a>
            </div>
        </nav>

        <main class="bg-white dark:bg-slate-800 p-6 md:p-10 rounded-lg shadow-lg">
            <?php if ($song_data): ?>
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-slate-800 dark:text-slate-100"><?= htmlspecialchars($song_data['title']) ?></h1>
                    <?php if (!empty($song_data['refrensi'])): ?>
                        <p class="text-sm italic text-slate-500 dark:text-slate-400 mt-2"><?= htmlspecialchars($song_data['refrensi']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="song-content">
                    <?php foreach ($song_data['lines'] as $line): ?>
                        <div class="line-preview">
                            <?php foreach ($line as $slot): ?>
                                <div class="slot-preview">
                                    <span class="note-preview"><?= $slot['note'] ?: '&nbsp;' ?></span>
                                    <span class="lyric-preview"><?= $slot['lyric'] ?: '&nbsp;' ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($recommendations)): ?>
                    <hr class="my-12 border-slate-200 dark:border-slate-700">
                    <div class="recommendation-section">
                        <h2 class="text-2xl font-bold text-center mb-6 dark:text-slate-200">Rekomendasi Lainnya</h2>
                        <div class="flex flex-col gap-2">
                            <?php foreach ($recommendations as $rec): ?>
                                <a href="viewer.php?song=<?= str_replace("tab_", "", urlencode($rec['filename'])) ?>" class="recommendation-card block bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-blue-400 dark:hover:border-blue-500">
                                    <h3 class="font-semibold text-slate-700 dark:text-slate-300 truncate"><?= htmlspecialchars($rec['title']) ?></h3>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-16">
                    <i class="fas fa-exclamation-triangle text-5xl text-yellow-500 mb-4"></i>
                    <h2 class="text-2xl font-bold dark:text-slate-200">Terjadi Kesalahan</h2>
                    <p class="text-slate-600 dark:text-slate-400 mt-2"><?= $error_message ?></p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');

        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('color-theme', isDark ? 'dark' : 'light');
            });
        }
    </script>
</body>

</html>