<?php require_once("./php/creator.php") ?>
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
    <link rel="icon" href="/assets/images/icon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/creator.css">

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

    <div id="app" class="container mx-auto p-4 md:p-8 max-w-5xl">
        <header class="flex flex-col md:flex-row justify-between items-center mb-6 pb-4 border-b border-slate-300 dark:border-slate-700">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-700 dark:text-slate-200 flex items-center">
                <i class="fa-solid fa-music text-blue-500 dark:text-blue-400 mr-3"></i>
                Kalimelody Creator
            </h1>
            <div class="flex gap-2 mt-4 md:mt-0 items-center">
                <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                    <i id="theme-toggle-light-icon" class="fas fa-sun"></i>
                    <i id="theme-toggle-dark-icon" class="fas fa-moon"></i>
                </button>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 dark:bg-slate-600 dark:hover:bg-slate-500 text-white font-bold py-2 px-4 rounded-lg shadow-md">
                    <i class="fas fa-list-ul mr-2"></i>Daftar Lagu
                </a>
            </div>
        </header>

        <div id="timestamp-info" class="text-xs text-slate-500 dark:text-slate-400 ml-auto">
        </div>
        <div class="my-6">
            <label for="title" class="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Judul Lagu</label>
            <input type="text" id="title" placeholder="Masukkan judul lagu di sini..." class="w-full p-3 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:placeholder-gray-400">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="md:col-span-2">
                <label for="refrensi" class="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Refrensi Tabs</label>
                <input type="text" id="refrensi" placeholder="Contoh: Url Refrensi atau penulis" class="w-full p-3 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:placeholder-gray-400">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Status</label>
                <select id="status" class="w-full p-3 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    <option value="draf">Draf</option>
                    <option value="publish">Publish</option>
                </select>
            </div>
        </div>

        <div id="notation-container" class="grid bg-white dark:bg-slate-800 p-4 rounded-lg shadow-inner border border-slate-200 dark:border-slate-700 mb-6 min-h-[150px] w-full">
            <div id="lines-container" class="overflow-x-auto pb-4"></div>
            <div class="flex gap-2 items-center mt-4 ">
                <button id="add-line-btn" class="bg-slate-200 hover:bg-slate-300 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 font-semibold py-2 px-5 rounded-lg text-xs md:text-sm">
                    <i class="fas fa-plus mr-2"></i>Tambah Baris Baru
                </button>
                <button id="save-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg shadow-md transition-transform transform hover:scale-105 text-xs md:text-sm">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </div>
        <div id="virtual-keyboard-container" class="overflow-x-auto bg-slate-100 dark:bg-slate-800 md:bg-white md:dark:bg-slate-800 md:static md:p-4 md:rounded-lg md:shadow-md md:border md:border-slate-200 md:dark:border-slate-700">
            <div class="flex border-b border-slate-200 dark:border-slate-700 mb-3">
                <button id="toggle-notes" class="keyboard-toggle flex-1 py-3 px-4 font-semibold text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400">Keyboard Notasi</button>
                <button id="toggle-lyrics" class="keyboard-toggle flex-1 py-3 px-4 font-semibold text-slate-500 dark:text-slate-400 md:hidden">Keyboard Lirik</button>
                <button id="hide-keyboard-btn" class="md:hidden p-3 text-slate-600 dark:text-slate-400"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div id="keyboard-notes" class="p-2 grid grid-cols-7 sm:grid-cols-9 md:grid-cols-11 gap-1.5 w-full"></div>
            <div id="keyboard-lyrics" class="p-2 hidden w-full"></div>
        </div>
        <div class="hidden md:block mt-5">
            <h2 class="text-2xl font-bold text-slate-700 dark:text-slate-200 mb-3">Pratinjau</h2>
            <pre id="preview-area" class="w-full p-4 rounded-lg text-sm md:text-base leading-relaxed bg-slate-100 border border-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300"></pre>
        </div>
    </div>

    <div id="notification" class="fixed top-5 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-xl transition-all duration-300 opacity-0 -translate-y-10"></div>

    <script>
        let tabData = <?php echo $initial_data_json; ?>;
    </script>
    <script src="/assets/js/creator.js"></script>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');

        // Fungsi ini perlu dipanggil dari creator.js jika ada perubahan dinamis
        function applyThemeToDynamicElements() {
            // Contoh: jika ada elemen dinamis yang perlu di-update gayanya saat tema berubah
            // Anda bisa letakkan logika update di sini.
            // Saat ini, kebanyakan sudah ditangani oleh CSS, tapi ini berguna untuk elemen yang dibuat JS.
        }

        // Cek tema saat halaman dimuat
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        applyThemeToDynamicElements();

        // Tambahkan event listener ke tombol pengalih tema
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('color-theme', isDark ? 'dark' : 'light');
                applyThemeToDynamicElements();
            });
        }
    </script>
</body>

</html>