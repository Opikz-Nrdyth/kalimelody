<?php
// BAGIAN LOGIKA PHP (BACKEND) - TIDAK ADA PERUBAHAN DI SINI

// --- LOGIKA UNTUK MEMUAT DATA SAAT EDIT (GET REQUEST) ---
$initial_data_json = '{"title":"","lines":[[{"note":"","lyric":""}]]}';
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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalimba Tab Creator - Buat Notasi Angka Kalimba Sendiri | Kalimelody</title>
    <meta name="description" content="Gunakan Kalimba Tab Creator dari Kalimelody untuk membuat, menulis, dan membagikan notasi angka kalimba Anda sendiri dengan mudah. Alat online gratis untuk semua musisi kalimba.">
    <meta name="keywords" content="kalimba tab creator, kalimba tab maker, buat tabs kalimba, penulis notasi kalimba, alat musik kalimba, notasi angka kalimba, kalimba online tool, generator tab kalimba, Kalimelody">
    <meta name="author" content="Karya Opik Studio">
    <link rel="canonical" href="https://www.kalimelody.opikstudio.my.id/creator">
    <meta property="og:title" content="Kalimba Tab Creator | Kalimelody">
    <meta property="og:description" content="Coba alat online gratis untuk membuat, menulis, dan membagikan notasi angka kalimba Anda sendiri dengan mudah.">
    <meta property="og:image" content="http://googleusercontent.com/image_generation_content/2">
    <meta property="og:url" content="https://www.kalimelody.opikstudio.my.id/creator">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Kalimba Tab Creator | Kalimelody">
    <meta name="twitter:description" content="Coba alat online gratis untuk membuat, menulis, dan membagikan notasi angka kalimba Anda sendiri dengan mudah.">
    <meta name="twitter:image" content="http://googleusercontent.com/image_generation_content/2">
    <link rel="icon" href="/images/icon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        html,
        body {
            overflow-x: hidden;
        }

        body {
            background-color: #f0f4f8;
            font-family: 'Inter', sans-serif;
        }

        .lyric-input {
            color: #888;
        }

        .note-input:focus,
        .lyric-input:focus {
            transform: scale(1.05);
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.4);
        }

        #preview-area {
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #e2e8f0;
            font-family: 'Courier New', Courier, monospace;
        }

        .keyboard-key {
            touch-action: manipulation;
        }

        /* Gaya untuk CapsLock Aktif */
        .caps-active {
            background-color: #3b82f6 !important;
            /* bg-blue-500 */
            color: white !important;
        }

        @media (max-width: 920px) {
            #virtual-keyboard-container {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 50;
                transition: transform 0.3s ease-in-out;
                transform: translateY(100%);
                border-top: 1px solid #cbd5e1;
                width: 100% !important;
            }

            #virtual-keyboard-container.keyboard-visible {
                transform: translateY(0);
            }

            #app {
                padding-bottom: 250px;
            }
        }
    </style>
</head>

<body class="antialiased text-slate-800">

    <div id="app" class="container mx-auto p-4 md:p-8 max-w-5xl">
        <header class="flex flex-col md:flex-row justify-between items-center mb-6 pb-4 border-b border-slate-300">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-700 flex items-center">
                <i class="fa-solid fa-music text-blue-500 mr-3"></i>
                Kalimelody Creator
            </h1>
            <div class="flex gap-2 mt-4 md:mt-0">
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md">
                    <i class="fas fa-list-ul mr-2"></i>Daftar Lagu
                </a>
            </div>
        </header>

        <div class="mb-6">
            <label for="title" class="block text-sm font-medium text-slate-600 mb-1">Judul Lagu</label>
            <input type="text" id="title" placeholder="Masukkan judul lagu di sini..." class="w-full p-3 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div id="notation-container" class="grid bg-white p-4 rounded-lg shadow-inner border border-slate-200 mb-6 min-h-[150px] w-full">
            <div id="lines-container" class="overflow-x-auto pb-4"></div>
            <div class="flex gap-2 items-center mt-4 ">
                <button id="add-line-btn" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-5 rounded-lg text-xs md:text-sm">
                    <i class="fas fa-plus mr-2"></i>Tambah Baris Baru
                </button>
                <button id="save-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg shadow-md transition-transform transform hover:scale-105 text-xs md:text-sm">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </div>
        <div id="virtual-keyboard-container" class="overflow-x-auto bg-slate-100 md:bg-white md:static md:p-4 md:rounded-lg md:shadow-md md:border md:border-slate-200">
            <div class="flex border-b border-slate-200 mb-3">
                <button id="toggle-notes" class="keyboard-toggle flex-1 py-3 px-4 font-semibold text-blue-600 border-b-2 border-blue-600">Keyboard Notasi</button>
                <button id="toggle-lyrics" class="keyboard-toggle flex-1 py-3 px-4 font-semibold text-slate-500 md:hidden">Keyboard Lirik</button>
                <button id="hide-keyboard-btn" class="md:hidden p-3 text-slate-600"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div id="keyboard-notes" class="p-2 grid grid-cols-7 sm:grid-cols-9 md:grid-cols-11 gap-1.5 w-full"></div>
            <div id="keyboard-lyrics" class="p-2 hidden w-full"></div>
        </div>
        <div class="hidden md:block mt-5">
            <h2 class="text-2xl font-bold text-slate-700 mb-3">Pratinjau</h2>
            <pre id="preview-area" class="w-full p-4 rounded-lg text-sm md:text-base leading-relaxed"></pre>
        </div>
    </div>



    <div id="notification" class="fixed top-5 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-xl transition-all duration-300 opacity-0 -translate-y-10"></div>

    <script>
        window.onload = function() {
            let tabData = <?php echo $initial_data_json; ?>;

            const dom = {
                title: document.getElementById('title'),
                linesContainer: document.getElementById('lines-container'),
                addLineBtn: document.getElementById('add-line-btn'),
                keyboardContainer: document.getElementById('virtual-keyboard-container'),
                keyboardNotes: document.getElementById('keyboard-notes'),
                keyboardLyrics: document.getElementById('keyboard-lyrics'),
                toggleNotes: document.getElementById('toggle-notes'),
                toggleLyrics: document.getElementById('toggle-lyrics'),
                hideKeyboardBtn: document.getElementById('hide-keyboard-btn'),
                previewArea: document.getElementById('preview-area'),
                saveBtn: document.getElementById('save-btn'),
                notification: document.getElementById('notification'),
            };

            const noteKeys = ["1'", "2'", "3'", "4'", "5'", "6'", "7'", "1''", "2''", "3''", "4''", "5''", "6''", "7''", "1", "2", "3", "4", "5", "6", "7", "~", "(", ")", ".", "-", "Del"];
            // --- PERUBAHAN 1: Mengganti 'Shift' menjadi 'CapsLock' ---
            const lyricKeys = ['QWERTYUIOP'.split(''), 'ASDFGHJKL'.split(''), ['CL', ...'ZXCVBNM'.split(''), 'Bksp'],
                ['Space']
            ];

            let activeInput = null;
            // --- PERUBAHAN 2: Menambah state untuk CapsLock ---
            let isCapsLockOn = false;

            function renderApp() {
                dom.title.value = tabData.title;
                renderNotationGrid();
                updatePreview();
            }

            function renderNotationGrid(focusTarget = null) {
                // ... (fungsi ini tidak berubah, biarkan seperti aslinya) ...
                dom.linesContainer.innerHTML = '';
                tabData.lines.forEach((line, lineIndex) => {
                    const lineDiv = document.createElement('div');
                    lineDiv.className = 'line-wrapper flex items-center space-x-2 mb-3';
                    const slotsDiv = document.createElement('div');
                    slotsDiv.className = 'flex items-center space-x-2';
                    line.forEach((slot, slotIndex) => {
                        const slotDiv = document.createElement('div');
                        slotDiv.className = 'relative flex flex-col space-y-1 pt-3';
                        const deleteBtn = document.createElement('button');
                        deleteBtn.innerHTML = '<i class="fas fa-minus-circle text-red-400 hover:text-red-600"></i>';
                        deleteBtn.className = 'delete-slot-btn absolute top-0 left-0 text-lg leading-none';
                        deleteBtn.dataset.line = lineIndex;
                        deleteBtn.dataset.slot = slotIndex;
                        slotDiv.appendChild(deleteBtn);
                        const noteInput = document.createElement('input');
                        noteInput.type = 'text';
                        noteInput.value = slot.note;
                        noteInput.className = 'note-input w-16 h-10 text-center border border-slate-300 rounded-md focus:outline-none';
                        noteInput.dataset.line = lineIndex;
                        noteInput.dataset.slot = slotIndex;
                        noteInput.dataset.type = 'note';
                        noteInput.readOnly = true;
                        slotDiv.appendChild(noteInput);
                        const lyricInput = document.createElement('input');
                        lyricInput.type = 'text';
                        lyricInput.value = slot.lyric;
                        lyricInput.className = 'lyric-input w-16 h-8 text-center text-sm border border-slate-300 rounded-md focus:outline-none';
                        lyricInput.dataset.line = lineIndex;
                        lyricInput.dataset.slot = slotIndex;
                        lyricInput.dataset.type = 'lyric';
                        lyricInput.readOnly = window.screen.width < 768
                        slotDiv.appendChild(lyricInput);
                        slotsDiv.appendChild(slotDiv);
                    });
                    lineDiv.appendChild(slotsDiv);
                    const addSlotBtn = document.createElement('button');
                    addSlotBtn.innerHTML = '<i class="fas fa-plus"></i>';
                    addSlotBtn.className = 'add-slot-btn bg-green-100 text-green-600 w-8 h-8 rounded-full hover:bg-green-200 flex-shrink-0';
                    addSlotBtn.dataset.line = lineIndex;
                    lineDiv.appendChild(addSlotBtn);
                    dom.linesContainer.appendChild(lineDiv);
                });
                if (focusTarget) {
                    const selector = `input[data-line="${focusTarget.line}"][data-slot="${focusTarget.slot}"][data-type="note"]`;
                    const inputToFocus = dom.linesContainer.querySelector(selector);
                    if (inputToFocus) {
                        inputToFocus.focus();
                        inputToFocus.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'center'
                        });
                    }
                }
            }

            function updatePreview() {
                // ... (fungsi ini tidak berubah, biarkan seperti aslinya) ...
                function centerText(text, width) {
                    if (text.length >= width) return text;
                    const paddingTotal = width - text.length;
                    const paddingLeft = Math.floor(paddingTotal / 2);
                    const paddingRight = Math.ceil(paddingTotal / 2);
                    return ' '.repeat(paddingLeft) + text + ' '.repeat(paddingRight);
                }
                let previewText = `${tabData.title}\n`;
                previewText += '-'.repeat(tabData.title.length > 0 ? tabData.title.length : 10) + '\n\n';
                tabData.lines.forEach(line => {
                    const slotsWithWidth = line.map(s => {
                        const width = Math.max(s.note.length, s.lyric.length);
                        return {
                            note: centerText(s.note, width),
                            lyric: centerText(s.lyric, width)
                        };
                    });
                    let noteLine = slotsWithWidth.map(s => s.note).join(' ');
                    let lyricLine = slotsWithWidth.map(s => s.lyric).join(' ');
                    previewText += `${noteLine}\n`;
                    if (lyricLine.trim()) {
                        previewText += `${lyricLine}\n`;
                    }
                    previewText += '\n';
                });
                dom.previewArea.textContent = previewText;
            }

            // --- PERUBAHAN 3: Fungsi baru untuk update tampilan keyboard lirik ---
            function updateLyricKeyboardCase() {
                const capsLockKey = document.getElementById('caps-lock-key');
                if (capsLockKey) {
                    capsLockKey.classList.toggle('caps-active', isCapsLockOn);
                }

                const letterKeys = dom.keyboardLyrics.querySelectorAll('.lyric-letter-key');
                letterKeys.forEach(btn => {
                    const originalKey = btn.dataset.key;
                    btn.textContent = isCapsLockOn ? originalKey.toUpperCase() : originalKey.toLowerCase();
                });
            }

            function generateKeyboards() {
                // ... (Bagian keyboard notasi tidak berubah) ...
                dom.keyboardNotes.innerHTML = '';
                noteKeys.forEach(key => {
                    const keyBtn = document.createElement('button');
                    keyBtn.textContent = key;
                    keyBtn.dataset.key = key;
                    keyBtn.className = 'keyboard-key h-12 rounded-lg shadow font-semibold transition transform hover:scale-105 w-full ' + ((key === 'Del') ? 'bg-red-200 text-red-800' : 'bg-slate-200');
                    dom.keyboardNotes.appendChild(keyBtn);
                });

                // --- PERUBAHAN 4: Modifikasi pembuatan keyboard lirik ---
                dom.keyboardLyrics.innerHTML = '';
                const lyricWrapper = document.createElement('div');
                lyricWrapper.className = 'flex flex-col items-center space-y-2';
                lyricKeys.forEach(row => {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'flex justify-center space-x-1.5 w-full';
                    row.forEach(key => {
                        const keyBtn = document.createElement('button');
                        keyBtn.textContent = key; // Teks awal (huruf besar)
                        keyBtn.dataset.key = key;
                        keyBtn.className = 'keyboard-key h-12 rounded-lg shadow bg-slate-200 font-semibold transition';

                        if (key.length > 1) { // Tombol fungsi (CapsLock, Bksp, Spasi)
                            keyBtn.classList.add('px-3', 'text-xs');
                            if (key === 'CL') keyBtn.id = 'caps-lock-key';
                        } else { // Tombol huruf biasa
                            keyBtn.classList.add('w-10', 'lyric-letter-key'); // Tambah kelas untuk identifikasi
                        }

                        if (key === 'Space') {
                            keyBtn.textContent = 'Spasi';
                            keyBtn.classList.add('flex-grow');
                        }
                        rowDiv.appendChild(keyBtn);
                    });
                    lyricWrapper.appendChild(rowDiv);
                });
                dom.keyboardLyrics.appendChild(lyricWrapper);
                updateLyricKeyboardCase(); // Panggil untuk set tampilan awal (huruf kecil)
            }

            function showNotification(message, isError = false) {
                dom.notification.textContent = message;
                dom.notification.className = 'fixed top-5 right-5 text-white py-2 px-4 rounded-lg shadow-xl transition-all duration-300 z-50 ' + (isError ? 'bg-red-500' : 'bg-green-500');
                dom.notification.classList.remove('opacity-0', '-translate-y-10');
                setTimeout(() => dom.notification.classList.add('opacity-0', '-translate-y-10'), 3000);
            }

            // --- EVENT LISTENERS ---
            dom.title.addEventListener('input', (e) => {
                tabData.title = e.target.value;
                updatePreview();
            });
            dom.linesContainer.addEventListener('focusin', (e) => {
                if (e.target.matches('.note-input, .lyric-input')) {
                    activeInput = e.target;
                    dom.keyboardContainer.classList.add('keyboard-visible');
                }
            });
            dom.linesContainer.addEventListener('input', (e) => {
                if (e.target.matches('.note-input, .lyric-input')) {
                    const {
                        line,
                        slot,
                        type
                    } = e.target.dataset;
                    tabData.lines[line][slot][type] = e.target.value;
                    updatePreview();
                }
            });
            dom.linesContainer.addEventListener('click', (e) => {
                const addSlotBtn = e.target.closest('.add-slot-btn');
                if (addSlotBtn) {
                    const lineIndex = parseInt(addSlotBtn.dataset.line);
                    const newSlotIndex = tabData.lines[lineIndex].length;
                    tabData.lines[lineIndex].push({
                        note: '',
                        lyric: ''
                    });
                    renderNotationGrid({
                        line: lineIndex,
                        slot: newSlotIndex
                    });
                }
                const deleteSlotBtn = e.target.closest('.delete-slot-btn');
                if (deleteSlotBtn) {
                    const {
                        line,
                        slot
                    } = deleteSlotBtn.dataset;
                    tabData.lines[line].splice(slot, 1);
                    if (tabData.lines[line].length === 0) tabData.lines.splice(line, 1);
                    if (tabData.lines.length === 0) tabData.lines.push([{
                        note: '',
                        lyric: ''
                    }]);
                    renderNotationGrid();
                }
            });
            dom.addLineBtn.addEventListener('click', () => {
                const newLineIndex = tabData.lines.length;
                tabData.lines.push([{
                    note: '',
                    lyric: ''
                }]);
                renderNotationGrid({
                    line: newLineIndex,
                    slot: 0
                });
            });

            // --- PERUBAHAN 5: Memperbarui logika input keyboard ---
            function handleKeyboardInput(key) {
                if (!activeInput) return;
                const {
                    type
                } = activeInput.dataset;

                if (type === 'note') {
                    activeInput.value = (key === 'Del') ? activeInput.value.slice(0, -1) : activeInput.value + key;
                } else if (type === 'lyric') {
                    if (key === 'Bksp') {
                        activeInput.value = activeInput.value.slice(0, -1);
                    } else if (key === 'Space') {
                        activeInput.value += ' ';
                    } else if (key !== 'CL') { // Abaikan tombol CapsLock
                        activeInput.value += isCapsLockOn ? key.toUpperCase() : key.toLowerCase();
                    }
                }
                const {
                    line,
                    slot
                } = activeInput.dataset;
                tabData.lines[line][slot][type] = activeInput.value;
                updatePreview();
            }

            // --- PERUBAHAN 6: Menambah logika klik untuk CapsLock ---
            dom.keyboardLyrics.addEventListener('click', (e) => {
                const keyBtn = e.target.closest('.keyboard-key');
                if (!keyBtn) return;

                const key = keyBtn.dataset.key;
                if (key === 'CL') {
                    isCapsLockOn = !isCapsLockOn; // Toggle state
                    updateLyricKeyboardCase(); // Update tampilan
                } else if (key) {
                    e.preventDefault();
                    handleKeyboardInput(key);
                    activeInput?.focus();
                }
            });

            // Event listener untuk keyboard notasi (dipisah agar tidak bentrok)
            dom.keyboardNotes.addEventListener('click', (e) => {
                const key = e.target.closest('.keyboard-key')?.dataset.key;
                if (key) {
                    e.preventDefault();
                    handleKeyboardInput(key);
                    activeInput?.focus();
                }
            });

            dom.toggleNotes.addEventListener('click', () => {
                dom.keyboardNotes.classList.remove('hidden');
                dom.keyboardLyrics.classList.add('hidden');
                dom.toggleNotes.className = 'keyboard-toggle flex-1 py-3 px-4 font-semibold text-blue-600 border-b-2 border-blue-600';
                dom.toggleLyrics.className = 'keyboard-toggle flex-1 py-3 px-4 font-semibold text-slate-500';
            });
            dom.toggleLyrics.addEventListener('click', () => {
                dom.keyboardLyrics.classList.remove('hidden');
                dom.keyboardNotes.classList.add('hidden');
                dom.toggleLyrics.className = 'keyboard-toggle flex-1 py-3 px-4 font-semibold text-blue-600 border-b-2 border-blue-600';
                dom.toggleNotes.className = 'keyboard-toggle flex-1 py-3 px-4 font-semibold text-slate-500';
            });
            dom.hideKeyboardBtn.addEventListener('click', () => {
                dom.keyboardContainer.classList.remove('keyboard-visible');
                activeInput?.blur();
            });

            dom.saveBtn.addEventListener('click', async () => {
                // ... (fungsi ini tidak berubah, biarkan seperti aslinya) ...
                dom.saveBtn.disabled = true;
                dom.saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                const urlParams = new URLSearchParams(window.location.search);
                const existingFilename = urlParams.get('file');
                let dataToSave = JSON.parse(JSON.stringify(tabData));
                if (existingFilename) {
                    dataToSave.existingFilename = existingFilename;
                }
                try {
                    const response = await fetch('creator.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(dataToSave)
                    });
                    const result = await response.json();
                    if (response.ok) {
                        showNotification(result.message);
                        if (!existingFilename && result.filename) {
                            window.history.replaceState({}, '', `creator.php?file=${result.filename}`);
                        }
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    showNotification(error.message || 'Gagal menyimpan. Server tidak memberikan respon JSON.', true);
                } finally {
                    dom.saveBtn.disabled = false;
                    dom.saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan';
                }
            });

            // --- INISIALISASI ---
            renderApp();
            generateKeyboards();
        };
    </script>

</body>

</html>