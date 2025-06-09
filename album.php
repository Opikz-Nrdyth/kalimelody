<?php require_once("./php/album.php") ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Album - Kalimelody | A Collection of Notes from the Heart</title>

    <meta name="description" content="Jelajahi setiap nada dan tulisan dalam album Kalimelody. Dengarkan melodi yang menenangkan dan temukan kedamaian di setiap halaman koleksi dari Karya Opik Studio.">

    <meta name="keywords" content="Album Kalimelody, koleksi, galeri, tracklist, Kalimelody, kalimba, musik, ketenangan, jiwa, buku, seni, nada, Opik Studio, relaksasi">

    <meta name="author" content="Karya Opik Studio">

    <link rel="canonical" href="https://www.kalimelody.opikstudio.my.id/album">

    <meta property="og:title" content="Album - Kalimelody | A Collection of Notes from the Heart">
    <meta property="og:description" content="Jelajahi setiap nada dan tulisan dalam album Kalimelody yang menenangkan jiwa.">
    <meta property="og:image" content="http://googleusercontent.com/image_generation_content/1">
    <meta property="og:url" content="https://www.kalimelody.opikstudio.my.id/album">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Album - Kalimelody | A Collection of Notes from the Heart">
    <meta name="twitter:description" content="Jelajahi setiap nada dan tulisan dalam album Kalimelody yang menenangkan jiwa.">
    <meta name="twitter:image" content="http://googleusercontent.com/image_generation_content/1">
    <link rel="icon" href="/assets/images/icon.png" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <link rel="stylesheet" href="/assets/css/album.css">
</head>

<body class="flex flex-col items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-4xl">
        <div class="flex flex-wrap gap-4 justify-between items-center mb-4">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 font-semibold"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar</a>
            <div class="flex items-center gap-3">
                <button id="filter-btn" class="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-300 rounded-lg shadow-sm">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <button id="export-pdf-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Ekspor ke PDF
                </button>
            </div>
        </div>

        <div id="page-content-container" class="w-full max-w-3xl mx-auto min-h-[80vh] p-8"></div>

        <div class="flex flex-col items-center mt-4 w-full max-w-3xl mx-auto">
            <div class="flex items-center gap-4">
                <button id="prev-btn" class="text-2xl text-slate-600 hover:text-black disabled:text-slate-300 disabled:cursor-not-allowed"><i class="fas fa-chevron-left"></i></button>
                <span id="page-indicator">Halaman <span id="current-page">0</span> / <span id="total-pages">0</span></span>
                <button id="next-btn" class="text-2xl text-slate-600 hover:text-black disabled:text-slate-300 disabled:cursor-not-allowed"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div id="page-numbers-container" class="mt-3 flex flex-wrap justify-center gap-1 max-h-24 overflow-y-auto p-2 rounded-lg bg-white border"></div>
        </div>
    </div>

    <div id="filter-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <h3 class="text-xl font-bold p-4 border-b">Pilih Lagu untuk Ditampilkan</h3>

            <div class="p-4 border-b">
                <input type="text" id="search-filter-input" placeholder="Cari judul lagu..." class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                <div class="flex gap-2 mt-2">
                    <button type="button" id="check-all-btn" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold py-1 px-3 rounded-full">Pilih Semua</button>
                    <button type="button" id="uncheck-all-btn" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-1 px-3 rounded-full">Hapus Pilihan</button>
                </div>
            </div>

            <div id="filter-checkboxes" class="p-4 max-h-64 overflow-y-auto">
                <?php foreach ($all_songs_data as $index => $song): ?>
                    <label class="flex items-center space-x-3 mb-2">
                        <input type="checkbox" name="song_filter" value="<?= $index ?>" class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                        <span><?= !empty($song['title']) ? htmlspecialchars($song['title']) : 'Tanpa Judul' ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="flex justify-end p-4 border-t bg-slate-50">
                <button id="apply-filter-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Terapkan</button>
            </div>
        </div>
    </div>

    <div id="paper-size-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
            <h3 class="text-xl font-bold p-5 border-b">Pilih Ukuran Kertas Cetak</h3>
            <div id="paper-options-container" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            </div>
            <div class="flex justify-end p-4 border-t bg-slate-50 gap-3">
                <button id="cancel-export-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">Batal</button>
                <button id="continue-export-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Lanjutkan & Ekspor</button>
            </div>
        </div>
    </div>

    <script>
        const allSongsData = <?php echo json_encode($all_songs_data); ?>;
    </script>
    <script src="/assets/js/album.js"></script>

</body>

</html>