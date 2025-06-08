<?php
// Fungsi untuk membaca semua data lagu dari folder /tabs
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
        if (json_last_error() === JSON_ERROR_NONE) {
            $songs[] = $data;
        }
    }
    return $songs;
}

$all_songs_data = get_all_songs_data();
?>
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
    <link rel="icon" href="/images/icon.png" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        body {
            background-color: #f1f5f9;
        }

        #page-content-container {
            font-family: 'Georgia', serif;
            color: #334155;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .toc-title {
            font-size: 24px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .toc-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .toc-item .dots {
            flex-grow: 1;
            border-bottom: 1px dotted #999;
            margin: 0 5px;
            transform: translateY(-4px);
        }

        .song-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            color: #1e3a8a;
        }

        .line-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 24px;
            font-family: 'Courier New', monospace;
        }

        .slot-preview {
            background-color: #eef2ff;
            border-radius: 4px;
            padding: 4px 6px;
            text-align: center;
            min-width: 35px;
        }

        .note-preview {
            display: block;
            font-weight: bold;
            font-size: 12px;
        }

        .lyric-preview {
            display: block;
            font-size: 10px;
            color: #4b5563;
            margin-top: 2px;
        }

        .paper-option {
            transition: all 0.2s ease-in-out;
            border: 2px solid #e5e7eb;
        }

        .paper-option:hover {
            border-color: #93c5fd;
            background-color: #eff6ff;
        }

        .paper-option.selected {
            border-color: #2563eb;
            background-color: #dbeafe;
            transform: scale(1.03);
        }
    </style>
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
        window.onload = function() {
            const allSongsData = <?php echo json_encode($all_songs_data); ?>;
            let filteredSongsData = [...allSongsData];
            let pagesHTML = [];
            let currentPageIndex = 0;

            const dom = {
                contentContainer: document.getElementById('page-content-container'),
                prevBtn: document.getElementById('prev-btn'),
                nextBtn: document.getElementById('next-btn'),
                currentPage: document.getElementById('current-page'),
                totalPages: document.getElementById('total-pages'),
                pageNumbersContainer: document.getElementById('page-numbers-container'),
                filterBtn: document.getElementById('filter-btn'),
                exportPdfBtn: document.getElementById('export-pdf-btn'),
                filterModal: document.getElementById('filter-modal'),
                applyFilterBtn: document.getElementById('apply-filter-btn'),
                filterCheckboxes: document.getElementById('filter-checkboxes'),
                paperSizeModal: document.getElementById('paper-size-modal'),
                paperOptionsContainer: document.getElementById('paper-options-container'),
                cancelExportBtn: document.getElementById('cancel-export-btn'),
                continueExportBtn: document.getElementById('continue-export-btn'),
            };

            const pdfConfigs = {
                a4: {
                    name: 'A4',
                    description: 'Jurnal Besar / Workbook',
                    margin: 20,
                    titleSize: 24,
                    tocTitleSize: 20,
                    songTitleSize: 18,
                    noteSize: 10,
                    lyricSize: 8,
                    dots: 80,
                    lineGap: 16,
                    wrapGap: 12
                },
                a5: {
                    name: 'A5',
                    description: 'Novel / Buku Tulis',
                    margin: 15,
                    titleSize: 20,
                    tocTitleSize: 16,
                    songTitleSize: 14,
                    noteSize: 9,
                    lyricSize: 7,
                    dots: 50,
                    lineGap: 14,
                    wrapGap: 10
                },
                a6: {
                    name: 'A6',
                    description: 'Buku Saku / Diary Kecil',
                    margin: 10,
                    titleSize: 18,
                    tocTitleSize: 14,
                    songTitleSize: 12,
                    noteSize: 9,
                    lyricSize: 7,
                    dots: 40,
                    lineGap: 14,
                    wrapGap: 9
                },
                b4: {
                    name: 'B4',
                    description: 'Majalah Besar / Buku Gambar',
                    margin: 22,
                    titleSize: 26,
                    tocTitleSize: 22,
                    songTitleSize: 20,
                    noteSize: 11,
                    lyricSize: 9,
                    dots: 90,
                    lineGap: 18,
                    wrapGap: 13
                },
                b5: {
                    name: 'B5',
                    description: 'Buku Jurnal / Catatan',
                    margin: 18,
                    titleSize: 22,
                    tocTitleSize: 18,
                    songTitleSize: 16,
                    noteSize: 10,
                    lyricSize: 8,
                    dots: 60,
                    lineGap: 15,
                    wrapGap: 11
                },
                b6: {
                    name: 'B6',
                    description: 'Buku Saku / Manga',
                    margin: 12,
                    titleSize: 19,
                    tocTitleSize: 15,
                    songTitleSize: 13,
                    noteSize: 9,
                    lyricSize: 7,
                    dots: 45,
                    lineGap: 14,
                    wrapGap: 10
                },

            };

            function setupApplication(songs) {
                pagesHTML = paginateContent(songs);
                currentPageIndex = 0;
                renderPage(currentPageIndex);
                generatePageNumbers();
            }

            function renderPage(index) {
                if (index < 0 || index >= pagesHTML.length) return;
                currentPageIndex = index;
                dom.contentContainer.innerHTML = pagesHTML[index];
                updateNavControls();
            }

            function updateNavControls() {
                dom.currentPage.textContent = currentPageIndex + 1;
                dom.totalPages.textContent = pagesHTML.length;
                dom.prevBtn.disabled = (currentPageIndex === 0);
                dom.nextBtn.disabled = (currentPageIndex === pagesHTML.length - 1);
                dom.pageNumbersContainer.querySelectorAll('.page-num-btn').forEach(btn => {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-slate-200', 'text-slate-700');
                    if (parseInt(btn.dataset.page) === currentPageIndex) {
                        btn.classList.add('bg-blue-600', 'text-white');
                        btn.classList.remove('bg-slate-200', 'text-slate-700');
                    }
                });
            }

            function generatePageNumbers() {
                dom.pageNumbersContainer.innerHTML = '';
                for (let i = 0; i < pagesHTML.length; i++) {
                    const pageNumBtn = document.createElement('button');
                    pageNumBtn.textContent = i + 1;
                    pageNumBtn.dataset.page = i;
                    pageNumBtn.className = 'page-num-btn px-3 py-1 rounded-md text-sm font-semibold transition';
                    dom.pageNumbersContainer.appendChild(pageNumBtn);
                }
            }

            // GANTI DENGAN FUNGSI BARU INI
            function paginateContent(songs) {
                const LINES_PER_PAGE = 10; // Tentukan perkiraan jumlah baris per halaman, bisa disesuaikan
                let pages = [];
                let pageLinks = {}; // Untuk menyimpan nomor halaman awal setiap lagu

                if (songs.length === 0) {
                    return ['<div class="text-center text-slate-500">Tidak ada lagu yang dipilih.</div>'];
                }

                // --- Langkah 1: Buat Halaman Daftar Isi (ToC) dulu ---
                let tocHTML = '<div class="toc-title">Daftar Isi</div>';
                songs.forEach((song, index) => {
                    tocHTML += `<div class="toc-item" data-song-index="${index}"><span>${song.title || 'Tanpa Judul'}</span><span class="dots"></span><span class="page-placeholder"></span></div>`;
                });
                pages.push(tocHTML);

                // --- Langkah 2: Buat Halaman Konten dengan Pagination ---
                let currentPageHTML = '';
                let linesOnCurrentPage = 0;

                songs.forEach((song, songIndex) => {
                    const songTitle = song.title || 'Tanpa Judul';
                    const titleHeight = 3; // Anggap judul memakan tinggi 3 baris
                    let isFirstPartOfSong = true;

                    // Cek apakah judul saja sudah membuat halaman baru
                    if (linesOnCurrentPage > 0 && linesOnCurrentPage + titleHeight > LINES_PER_PAGE) {
                        pages.push(currentPageHTML); // Simpan halaman sebelumnya
                        currentPageHTML = ''; // Mulai halaman baru
                        linesOnCurrentPage = 0;
                    }

                    // Catat halaman awal lagu ini
                    pageLinks[songIndex] = pages.length;

                    // Tambahkan judul ke halaman
                    currentPageHTML += `<div class="song-title">${songTitle}</div>`;
                    linesOnCurrentPage += titleHeight;

                    // Loop per baris notasi di dalam lagu
                    song.lines.forEach(line => {
                        const lineIsEmpty = line.every(slot => !slot.note && !slot.lyric);
                        if (lineIsEmpty) return; // Lewati baris kosong

                        const lineHeight = 2; // Anggap satu baris notasi memakan tinggi 2 baris

                        // Cek apakah baris ini muat di halaman saat ini
                        if (linesOnCurrentPage > 0 && linesOnCurrentPage + lineHeight > LINES_PER_PAGE) {
                            pages.push(currentPageHTML); // Simpan halaman saat ini
                            currentPageHTML = ''; // Mulai halaman baru
                            linesOnCurrentPage = 0;
                            // Baris yang menulis ulang judul "(Lanjutan)" sudah dihapus.
                        }

                        // Render baris notasi ke HTML
                        let lineHTML = '<div class="line-preview">';
                        line.forEach(slot => {
                            lineHTML += `<div class="slot-preview"><span class="note-preview">${slot.note || '&nbsp;'}</span><span class="lyric-preview">${slot.lyric || '&nbsp;'}</span></div>`;
                        });
                        lineHTML += '</div>';
                        currentPageHTML += lineHTML;
                        linesOnCurrentPage += lineHeight;
                    });

                    // Beri jarak setelah lagu selesai, dan mulai halaman baru jika perlu
                    linesOnCurrentPage += 2; // Jarak antar lagu
                });

                // Masukkan halaman terakhir yang belum tersimpan
                if (currentPageHTML.trim() !== '') {
                    pages.push(currentPageHTML);
                }

                // --- Langkah 3: Isi Nomor Halaman di Daftar Isi ---
                let tocPageParser = new DOMParser().parseFromString(pages[0], 'text/html');
                tocPageParser.querySelectorAll('.toc-item').forEach(item => {
                    const songIndex = item.dataset.songIndex;
                    const pageNum = pageLinks[songIndex] + 1; // Nomor halaman dimulai dari 1
                    item.querySelector('.page-placeholder').textContent = pageNum;
                });
                pages[0] = tocPageParser.body.innerHTML;

                return pages;
            }

            function populatePaperOptions() {
                dom.paperOptionsContainer.innerHTML = '';
                Object.keys(pdfConfigs).forEach((key) => {
                    const config = pdfConfigs[key];
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'paper-option p-4 rounded-lg cursor-pointer text-center';
                    optionDiv.dataset.size = key;
                    if (key === 'a6') {
                        optionDiv.classList.add('selected');
                    }
                    optionDiv.innerHTML = `
                <div class="font-bold text-lg">${config.name}</div>
                <div class="text-sm text-gray-500">${config.description}</div>
            `;
                    dom.paperOptionsContainer.appendChild(optionDiv);
                });
            }

            // GANTI DENGAN FUNGSI BARU INI
            async function exportAllToPDF(paperSize) {
                if (filteredSongsData.length === 0) {
                    alert("Silakan pilih setidaknya satu lagu untuk diekspor.");
                    return;
                }

                const config = pdfConfigs[paperSize];
                if (!config) {
                    alert("Ukuran kertas tidak valid.");
                    return;
                }

                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'p',
                    unit: 'mm',
                    format: paperSize
                });
                const pageHeight = doc.internal.pageSize.getHeight();
                const pageWidth = doc.internal.pageSize.getWidth();
                const margin = config.margin;
                const pageBottom = pageHeight - margin;

                const addPageNumbers = (doc) => {
                    const totalPages = doc.internal.getNumberOfPages();
                    for (let i = 1; i <= totalPages; i++) {
                        doc.setPage(i);
                        doc.setFontSize(8).setTextColor(150, 150, 150);
                        const text = `Kalimelody | ${i}`;

                        // --- PERUBAHAN DI BARIS INI ---
                        // Kita gunakan koordinat X di ujung kanan margin dan tambahkan opsi { align: 'right' }
                        doc.text(text, pageWidth - config.margin, pageHeight - 7, {
                            align: 'right'
                        });
                        // --- AKHIR PERUBAHAN ---
                    }
                };

                let y = margin; // Posisi Y (vertikal) saat ini
                let tocData = [];

                // --- RENDER SEMUA LAGU DENGAN PAGINATION YANG BENAR ---
                filteredSongsData.forEach((song) => {
                    const songTitle = song.title || 'Tanpa Judul';
                    const titleHeight = config.songTitleSize / 2; // Perkiraan tinggi judul
                    let isFirstPartOfSong = true;

                    // Cek apakah judul muat. Jika tidak, buat halaman baru.
                    if (y + titleHeight > pageBottom && y > margin) {
                        doc.addPage();
                        y = margin;
                    }

                    // Catat halaman awal lagu untuk ToC
                    tocData.push({
                        title: songTitle,
                        page: doc.internal.getNumberOfPages()
                    });

                    // Tulis Judul
                    doc.setFont("helvetica", "bold").setFontSize(config.songTitleSize).setTextColor(0, 0, 0);
                    doc.text(songTitle, pageWidth / 2, y, {
                        align: 'center'
                    });
                    y += titleHeight;

                    // Loop per baris notasi
                    song.lines.forEach(line => {
                        const lineHeight = config.lineGap;

                        // Jika baris berikutnya tidak muat, buat halaman baru
                        if (y + lineHeight > pageBottom) {
                            doc.addPage();
                            y = margin;
                            // Baris yang menulis ulang judul "(Lanjutan)" sudah dihapus.
                        }

                        // Render satu baris notasi (dengan wrapping horizontal jika perlu)
                        let x = margin;
                        doc.setFont("courier", "normal");
                        line.forEach(slot => {
                            const noteText = slot.note || '';
                            const lyricText = slot.lyric || '';

                            doc.setFontSize(config.noteSize);
                            const noteWidth = doc.getTextWidth(noteText);
                            doc.setFontSize(config.lyricSize);
                            const lyricWidth = doc.getTextWidth(lyricText);

                            const slotWidth = Math.max(noteWidth, lyricWidth) + 2; // Lebar slot + padding kecil

                            // Jika slot berikutnya tidak muat secara horizontal, pindah baris
                            if (x + slotWidth > pageWidth - margin) {
                                x = margin;
                                y += lineHeight;

                                // Cek lagi setelah pindah baris, mungkin perlu pindah halaman juga
                                if (y + lineHeight > pageBottom) {
                                    doc.addPage();
                                    y = margin;
                                    // Baris yang menulis ulang judul "(Lanjutan)" sudah dihapus.
                                }
                            }

                            // Gambar slot
                            doc.setFontSize(config.noteSize).setTextColor(0, 0, 0);
                            doc.text(noteText, x + 1, y);

                            if (lyricText) {
                                doc.setFontSize(config.lyricSize).setTextColor(100);
                                doc.text(lyricText, x + 1, y + (config.lyricSize / 2));
                            }

                            x += slotWidth; // Pindah posisi X untuk slot berikutnya
                        });

                        y += lineHeight; // Pindah posisi Y untuk baris berikutnya
                    });

                    y += config.wrapGap / 2; // Jarak antar lagu
                });

                // --- RENDER DAFTAR ISI DI HALAMAN PERTAMA ---
                if (filteredSongsData.length > 1) {
                    doc.insertPage(1); // Sisipkan halaman baru di paling depan untuk ToC
                    doc.setPage(1);
                    y = margin;

                    doc.setFont("helvetica", "bold").setFontSize(config.tocTitleSize);
                    doc.text("Daftar Isi", pageWidth / 2, y, {
                        align: 'center'
                    });
                    y += config.wrapGap;

                    doc.setFont("times", "normal").setFontSize(config.noteSize);
                    tocData.forEach(item => {
                        const pageNumberText = (item.page + 1).toString(); // Tambah 1 karena ToC jadi halaman 1
                        const titleText = item.title;

                        if (y + config.lineGap > pageBottom) {
                            doc.addPage();
                            y = margin;
                        }

                        // Gambar judul, titik-titik, dan nomor halaman
                        const titleWidth = doc.getTextWidth(titleText);
                        const pageNumWidth = doc.getTextWidth(pageNumberText);
                        const availableWidth = pageWidth - margin * 2 - titleWidth - pageNumWidth - 2;
                        const dotWidth = doc.getTextWidth('.');
                        const numDots = Math.floor(availableWidth / dotWidth);

                        const dots = '.'.repeat(numDots > 0 ? numDots : 0);

                        doc.text(titleText, margin, y);
                        doc.text(dots, margin + titleWidth + 1, y);
                        doc.text(pageNumberText, pageWidth - margin - pageNumWidth, y);

                        y += config.lineGap / 1.5;
                    });
                }

                // --- TAMBAHKAN NOMOR HALAMAN DI SEMUA HALAMAN ---
                addPageNumbers(doc);

                // --- Simpan PDF ---
                doc.save(`Kalimelody_Album_${paperSize}.pdf`);
            }

            // --- EVENT LISTENERS ---
            dom.exportPdfBtn.addEventListener('click', () => {
                dom.paperSizeModal.classList.remove('hidden');
            });
            dom.cancelExportBtn.addEventListener('click', () => {
                dom.paperSizeModal.classList.add('hidden');
            });
            dom.continueExportBtn.addEventListener('click', () => {
                const selectedOption = dom.paperOptionsContainer.querySelector('.paper-option.selected');
                if (selectedOption) {
                    const paperSize = selectedOption.dataset.size;
                    exportAllToPDF(paperSize);
                    dom.paperSizeModal.classList.add('hidden');
                } else {
                    alert('Silakan pilih ukuran kertas terlebih dahulu.');
                }
            });
            dom.paperOptionsContainer.addEventListener('click', (e) => {
                const selectedOption = e.target.closest('.paper-option');
                if (!selectedOption) return;
                dom.paperOptionsContainer.querySelectorAll('.paper-option').forEach(opt => opt.classList.remove('selected'));
                selectedOption.classList.add('selected');
            });
            dom.filterBtn.addEventListener('click', () => dom.filterModal.classList.remove('hidden'));
            dom.applyFilterBtn.addEventListener('click', () => {
                const selectedIndexes = Array.from(dom.filterCheckboxes.querySelectorAll('input:checked')).map(cb => parseInt(cb.value));
                filteredSongsData = allSongsData.filter((_, index) => selectedIndexes.includes(index));
                setupApplication(filteredSongsData);
                dom.filterModal.classList.add('hidden');
            });
            dom.filterModal.addEventListener('click', (e) => {
                if (e.target === dom.filterModal) dom.filterModal.classList.add('hidden');
            });
            dom.prevBtn.addEventListener('click', () => renderPage(currentPageIndex - 1));
            dom.nextBtn.addEventListener('click', () => renderPage(currentPageIndex + 1));
            dom.pageNumbersContainer.addEventListener('click', (e) => {
                if (e.target.matches('.page-num-btn')) renderPage(parseInt(e.target.dataset.page));
            });

            // --- INISIALISASI ---
            setupApplication(filteredSongsData);
            populatePaperOptions();
        };
    </script>

</body>

</html>