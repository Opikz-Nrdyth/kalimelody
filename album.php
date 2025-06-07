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
    <link rel="icon" href="/icon.png" type="image/png">

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

        .page-footer {
            text-align: center;
            margin-top: auto;
            font-size: 12px;
            color: #94a3b8;
            padding-top: 10px;
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
            margin-bottom: 10px;
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
    </style>
</head>

<body class="flex flex-col items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-4xl">
        <div class="flex flex-wrap gap-4 justify-between items-center mb-4">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 font-semibold"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar</a>
            <div class="flex gap-3">
                <button id="filter-btn" class="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-300 rounded-lg shadow-sm">
                    <i class="fas fa-filter mr-2"></i>Filter Lagu
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

            function paginateContent(songs) {
                let pages = [];
                let pageLinks = {};
                if (songs.length === 0) return ['<div class="text-center text-slate-500">Tidak ada lagu yang dipilih.</div>'];
                let tocHTML = '<div class="toc-title">Daftar Isi</div>';
                songs.forEach((song, index) => {
                    tocHTML += `<div class="toc-item" data-song-index="${index}"><span>${song.title || 'Tanpa Judul'}</span><span class="dots"></span><span class="page-placeholder"></span></div>`;
                });
                pages.push(tocHTML);
                songs.forEach((song, songIndex) => {
                    pageLinks[songIndex] = pages.length;
                    let songHTML = `<div class="song-title">${song.title || 'Tanpa Judul'}</div>`;
                    song.lines.forEach(line => {
                        let lineHTML = '<div class="line-preview">';
                        line.forEach(slot => {
                            lineHTML += `<div class="slot-preview"><span class="note-preview">${slot.note || '&nbsp;'}</span><span class="lyric-preview">${slot.lyric || '&nbsp;'}</span></div>`;
                        });
                        lineHTML += '</div>';
                        if ((songHTML + lineHTML).length > 1800) {
                            pages.push(songHTML);
                            songHTML = '';
                        }
                        songHTML += lineHTML;
                    });
                    pages.push(songHTML);
                });
                let tocPage = new DOMParser().parseFromString(pages[0], 'text/html');
                tocPage.querySelectorAll('.toc-item').forEach(item => {
                    const songIndex = item.dataset.songIndex;
                    const pageNum = pageLinks[songIndex] + 1;
                    item.querySelector('.page-placeholder').textContent = pageNum;
                });
                pages[0] = tocPage.body.innerHTML;
                return pages;
            }

            // ==========================================================
            // === FUNGSI EKSPOR PDF DENGAN PERBAIKAN FINAL V2 ===
            // ==========================================================
            async function exportAllToPDF() {
                if (filteredSongsData.length === 0) {
                    alert("Silakan pilih setidaknya satu lagu untuk diekspor ke PDF.");
                    return;
                }

                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'p',
                    unit: 'mm',
                    format: 'a6'
                });
                const pageMargin = 10;
                const pageHeight = doc.internal.pageSize.getHeight();
                const pageWidth = doc.internal.pageSize.getWidth();
                const fileName = (filteredSongsData.length === 1) ?
                    `${filteredSongsData[0].title || 'lagu'}.pdf` :
                    'Kalimelody.pdf';

                const renderSongToDoc = (song, yPos) => {
                    let currentY = yPos;
                    doc.setFont("helvetica", "bold").setFontSize(14).setTextColor(0, 0, 0).text(song.title || 'Tanpa Judul', pageWidth / 2, currentY, {
                        align: 'center'
                    });
                    currentY += 10;
                    doc.setFont("courier", "normal");
                    song.lines.forEach(line => {
                        if (currentY > pageHeight - 15) {
                            doc.addPage();
                            currentY = pageMargin;
                        }
                        let currentX = pageMargin;
                        line.forEach(slot => {
                            doc.setFontSize(9);
                            const noteWidth = doc.getTextWidth(slot.note);
                            doc.setFontSize(7);
                            const lyricWidth = doc.getTextWidth(slot.lyric);

                            const contentWidth = Math.max(noteWidth, lyricWidth);
                            const blockPadding = 1; // 1mm padding di setiap sisi blok
                            const fullBlockWidth = contentWidth + (blockPadding * 2);
                            const spaceBetweenBlocks = 1.5; // Jarak antar blok

                            if (currentX + fullBlockWidth > pageWidth - pageMargin) {
                                currentY += 9;
                                currentX = pageMargin;
                                if (currentY > pageHeight - 15) {
                                    doc.addPage();
                                    currentY = pageMargin;
                                }
                            }

                            if (slot.note.trim() !== '') {
                                doc.setFillColor(235, 235, 235);
                                doc.rect(currentX, currentY - 3.5, fullBlockWidth, 7.5, 'F');
                            }

                            const noteX = currentX + blockPadding + (contentWidth - noteWidth) / 2;
                            const lyricX = currentX + blockPadding + (contentWidth - lyricWidth) / 2;

                            doc.setFontSize(9).setTextColor(0, 0, 0).text(slot.note, noteX, currentY);
                            if (slot.lyric.trim()) {
                                doc.setFontSize(7).setTextColor(80, 80, 80).text(slot.lyric, lyricX, currentY + 3.5);
                            }

                            currentX += fullBlockWidth + spaceBetweenBlocks;
                        });
                        currentY += 9;
                    });
                    return currentY;
                };

                if (filteredSongsData.length === 1) {
                    renderSongToDoc(filteredSongsData[0], 20);
                    doc.save(fileName);
                } else {
                    const coverImg = new Image();
                    coverImg.src = 'cover buku.png';
                    coverImg.onload = function() {
                        doc.addImage(this, 'PNG', 0, 0, pageWidth, pageHeight);
                        doc.addPage();
                        let pageLinks = {};
                        let currentPageNum = 3;
                        filteredSongsData.forEach((song, index) => {
                            pageLinks[index] = currentPageNum;
                            let tempY = pageMargin + 5 + 10;
                            song.lines.forEach(line => {
                                if (tempY > pageHeight - 15) {
                                    currentPageNum++;
                                    tempY = pageMargin;
                                }
                                tempY += 9;
                            });
                            currentPageNum++;
                        });

                        let yPos = 20;
                        doc.setFontSize(18).text('Album Kalimba', pageWidth / 2, yPos, {
                            align: 'center'
                        });
                        yPos += 12;
                        doc.setFontSize(14).text('Daftar Isi', pageWidth / 2, yPos, {
                            align: 'center'
                        });
                        yPos += 12;
                        doc.setFont("courier", "normal").setFontSize(9);
                        filteredSongsData.forEach((song, index) => {
                            if (yPos > pageHeight - 15) {
                                doc.addPage();
                                yPos = pageMargin;
                            }
                            const title = song.title || 'Tanpa Judul';
                            const pageNum = pageLinks[index].toString();
                            const dots = '.'.repeat(Math.max(0, 40 - title.length - pageNum.length));
                            doc.text(`${title} ${dots} ${pageNum}`, pageMargin, yPos);
                            yPos += 6;
                        });

                        filteredSongsData.forEach(song => {
                            doc.addPage();
                            renderSongToDoc(song, 20);
                        });

                        doc.save(fileName);
                    };
                    coverImg.onerror = function() {
                        alert('Gagal memuat "cover buku.png". Pastikan file ada di direktori yang sama. PDF akan dibuat tanpa cover.');
                        doc.save(fileName);
                    }
                }
            }

            dom.prevBtn.addEventListener('click', () => renderPage(currentPageIndex - 1));
            dom.nextBtn.addEventListener('click', () => renderPage(currentPageIndex + 1));
            dom.pageNumbersContainer.addEventListener('click', (e) => {
                if (e.target.matches('.page-num-btn')) renderPage(parseInt(e.target.dataset.page));
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
            dom.exportPdfBtn.addEventListener('click', exportAllToPDF);
            setupApplication(filteredSongsData);
        };
    </script>

</body>

</html>