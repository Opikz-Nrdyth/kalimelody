window.onload = function () {
  let filteredSongsData = [...allSongsData];
  let pagesHTML = [];
  let currentPageIndex = 0;

  const dom = {
    contentContainer: document.getElementById("page-content-container"),
    prevBtn: document.getElementById("prev-btn"),
    nextBtn: document.getElementById("next-btn"),
    currentPage: document.getElementById("current-page"),
    totalPages: document.getElementById("total-pages"),
    pageNumbersContainer: document.getElementById("page-numbers-container"),
    filterBtn: document.getElementById("filter-btn"),
    exportPdfBtn: document.getElementById("export-pdf-btn"),
    filterModal: document.getElementById("filter-modal"),
    applyFilterBtn: document.getElementById("apply-filter-btn"),
    filterCheckboxes: document.getElementById("filter-checkboxes"),
    paperSizeModal: document.getElementById("paper-size-modal"),
    paperOptionsContainer: document.getElementById("paper-options-container"),
    cancelExportBtn: document.getElementById("cancel-export-btn"),
    continueExportBtn: document.getElementById("continue-export-btn"),
    searchFilterInput: document.getElementById("search-filter-input"),
    checkAllBtn: document.getElementById("check-all-btn"),
    uncheckAllBtn: document.getElementById("uncheck-all-btn"),
  };

  const pdfConfigs = {
    a4: {
      name: "A4",
      description: "Jurnal Besar / Workbook",
      margin: 20,
      titleSize: 24,
      tocTitleSize: 20,
      songTitleSize: 18,
      noteSize: 10,
      lyricSize: 8,
      dots: 80,
      lineGap: 16,
      wrapGap: 12,
    },
    a5: {
      name: "A5",
      description: "Novel / Buku Tulis",
      margin: 15,
      titleSize: 20,
      tocTitleSize: 16,
      songTitleSize: 14,
      noteSize: 9,
      lyricSize: 7,
      dots: 50,
      lineGap: 14,
      wrapGap: 10,
    },
    a6: {
      name: "A6",
      description: "Buku Saku / Diary Kecil",
      margin: 10,
      titleSize: 18,
      tocTitleSize: 14,
      songTitleSize: 12,
      noteSize: 8,
      lyricSize: 7,
      dots: 40,
      lineGap: 14,
      wrapGap: 10,
    },
    b4: {
      name: "B4",
      description: "Majalah Besar / Buku Gambar",
      margin: 22,
      titleSize: 26,
      tocTitleSize: 22,
      songTitleSize: 20,
      noteSize: 11,
      lyricSize: 9,
      dots: 90,
      lineGap: 18,
      wrapGap: 13,
    },
    b5: {
      name: "B5",
      description: "Buku Jurnal / Catatan",
      margin: 18,
      titleSize: 22,
      tocTitleSize: 18,
      songTitleSize: 16,
      noteSize: 10,
      lyricSize: 8,
      dots: 60,
      lineGap: 15,
      wrapGap: 11,
    },
    b6: {
      name: "B6",
      description: "Buku Saku / Manga",
      margin: 12,
      titleSize: 19,
      tocTitleSize: 15,
      songTitleSize: 13,
      noteSize: 9,
      lyricSize: 7,
      dots: 45,
      lineGap: 14,
      wrapGap: 10,
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
    dom.prevBtn.disabled = currentPageIndex === 0;
    dom.nextBtn.disabled = currentPageIndex === pagesHTML.length - 1;
    dom.pageNumbersContainer
      .querySelectorAll(".page-num-btn")
      .forEach((btn) => {
        btn.classList.remove("bg-blue-600", "text-white");
        btn.classList.add("bg-slate-200", "text-slate-700");
        if (parseInt(btn.dataset.page) === currentPageIndex) {
          btn.classList.add("bg-blue-600", "text-white");
          btn.classList.remove("bg-slate-200", "text-slate-700");
        }
      });
  }

  function generatePageNumbers() {
    dom.pageNumbersContainer.innerHTML = "";
    for (let i = 0; i < pagesHTML.length; i++) {
      const pageNumBtn = document.createElement("button");
      pageNumBtn.textContent = i + 1;
      pageNumBtn.dataset.page = i;
      pageNumBtn.className =
        "page-num-btn px-3 py-1 rounded-md text-sm font-semibold transition";
      dom.pageNumbersContainer.appendChild(pageNumBtn);
    }
  }

  function paginateContent(songs) {
    const LINES_PER_PAGE = 10; // Tentukan perkiraan jumlah baris per halaman, bisa disesuaikan
    let pages = [];
    let pageLinks = {};

    if (songs.length === 0) {
      return [
        '<div class="text-center text-slate-500">Tidak ada lagu yang dipilih.</div>',
      ];
    }

    let tocHTML = '<div class="toc-title">Daftar Isi</div>';
    songs.forEach((song, index) => {
      tocHTML += `<div class="toc-item" data-song-index="${index}"><span>${
        song.title || "Tanpa Judul"
      }</span><span class="dots"></span><span class="page-placeholder"></span></div>`;
    });
    pages.push(tocHTML);

    let currentPageHTML = "";
    let linesOnCurrentPage = 0;

    songs.forEach((song, songIndex) => {
      const songTitle = song.title || "Tanpa Judul";
      const titleHeight = 3;
      let isFirstPartOfSong = true;

      if (
        linesOnCurrentPage > 0 &&
        linesOnCurrentPage + titleHeight > LINES_PER_PAGE
      ) {
        pages.push(currentPageHTML);
        currentPageHTML = "";
        linesOnCurrentPage = 0;
      }

      pageLinks[songIndex] = pages.length;

      currentPageHTML += `<div class="song-title">${songTitle}</div>`;
      if (song.refrensi && song.refrensi.trim() !== "") {
        currentPageHTML += `<div class="song-refrensi">Referensi: ${song.refrensi}</div>`;
        linesOnCurrentPage += 1;
      }
      linesOnCurrentPage += titleHeight;

      song.lines.forEach((line) => {
        const lineIsEmpty = line.every((slot) => !slot.note && !slot.lyric);
        if (lineIsEmpty) return;

        const lineHeight = 2;

        if (
          linesOnCurrentPage > 0 &&
          linesOnCurrentPage + lineHeight > LINES_PER_PAGE
        ) {
          pages.push(currentPageHTML);
          currentPageHTML = "";
          linesOnCurrentPage = 0;
        }

        // Render baris notasi ke HTML
        let lineHTML = '<div class="line-preview">';
        line.forEach((slot) => {
          lineHTML += `<div class="slot-preview"><span class="note-preview">${
            slot.note || "&nbsp;"
          }</span><span class="lyric-preview">${
            slot.lyric || "&nbsp;"
          }</span></div>`;
        });
        lineHTML += "</div>";
        currentPageHTML += lineHTML;
        linesOnCurrentPage += lineHeight;
      });

      linesOnCurrentPage += 2;
    });

    if (currentPageHTML.trim() !== "") {
      pages.push(currentPageHTML);
    }

    let tocPageParser = new DOMParser().parseFromString(pages[0], "text/html");
    tocPageParser.querySelectorAll(".toc-item").forEach((item) => {
      const songIndex = item.dataset.songIndex;
      const pageNum = pageLinks[songIndex] + 1; // Nomor halaman dimulai dari 1
      item.querySelector(".page-placeholder").textContent = pageNum;
    });
    pages[0] = tocPageParser.body.innerHTML;

    return pages;
  }

  function populatePaperOptions() {
    dom.paperOptionsContainer.innerHTML = "";
    Object.keys(pdfConfigs).forEach((key) => {
      const config = pdfConfigs[key];
      const optionDiv = document.createElement("div");
      optionDiv.className =
        "paper-option p-4 rounded-lg cursor-pointer text-center";
      optionDiv.dataset.size = key;
      if (key === "a6") {
        optionDiv.classList.add("selected");
      }
      optionDiv.innerHTML = `
                <div class="font-bold text-lg">${config.name}</div>
                <div class="text-sm text-gray-500">${config.description}</div>`;
      dom.paperOptionsContainer.appendChild(optionDiv);
    });
  }

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

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
      orientation: "p",
      unit: "mm",
      format: paperSize,
    });
    const pageHeight = doc.internal.pageSize.getHeight();
    const pageWidth = doc.internal.pageSize.getWidth();
    const margin = config.margin;
    const pageBottom = pageHeight - margin;

    const addPageNumbers = (doc, hasCover) => {
      const totalPages = doc.internal.getNumberOfPages();
      for (let i = 1; i <= totalPages; i++) {
        if (hasCover && i === 1) continue; // Jangan nomori halaman cover
        doc.setPage(i);
        doc.setFontSize(8).setTextColor(150, 150, 150);
        const text = `Kalimelody | ${i - (hasCover ? 1 : 0)}`;
        doc.text(text, pageWidth - margin, pageHeight - 7, {
          align: "right",
        });
      }
    };

    // --- KASUS 1: HANYA SATU LAGU (TANPA COVER) ---
    if (filteredSongsData.length === 1) {
      const song = filteredSongsData[0];
      let y = margin;
      const songTitle = song.title || "Tanpa Judul";
      const songRefrensi = song.refrensi || "";
      const titleHeight = config.songTitleSize / 2;

      // Tulis Judul
      doc
        .setFont("helvetica", "bold")
        .setFontSize(config.songTitleSize)
        .setTextColor(0, 0, 0);
      doc.text(songTitle, pageWidth / 2, y, {
        align: "center",
      });
      y += titleHeight / 2 + 2; // Beri sedikit jarak

      // --- TAMBAHKAN BLOK IF INI UNTUK MENCETAK REFERENSI ---
      if (songRefrensi.trim() !== "") {
        doc
          .setFont("helvetica", "italic")
          .setFontSize(config.noteSize - 1)
          .setTextColor(100);
        doc.text(`${songRefrensi}`, pageWidth / 2, y, {
          align: "center",
        });
        y += config.noteSize / 2;
      }
      song.lines.forEach((line) => {
        const lineHeight = config.lineGap;
        if (y + lineHeight > pageBottom) {
          doc.addPage();
          y = margin;
        }
        let x = margin;
        doc.setFont("courier", "normal");
        line.forEach((slot) => {
          const noteText = slot.note || "";
          const lyricText = slot.lyric || "";
          doc.setFontSize(config.noteSize);
          const noteWidth = doc.getTextWidth(noteText);
          doc.setFontSize(config.lyricSize);
          const lyricWidth = doc.getTextWidth(lyricText);
          const slotWidth = Math.max(noteWidth, lyricWidth) + 2;
          if (x + slotWidth > pageWidth - margin) {
            x = margin;
            y += lineHeight;
            if (y + lineHeight > pageBottom) {
              doc.addPage();
              y = margin;
            }
          }
          doc.setFontSize(config.noteSize).setTextColor(0, 0, 0);
          doc.text(noteText, x + 1, y);
          if (lyricText) {
            doc.setFontSize(config.lyricSize).setTextColor(100);
            doc.text(lyricText, x + 1, y + config.lyricSize / 2);
          }
          x += slotWidth;
        });
        y += lineHeight;
      });
      addPageNumbers(doc, false);
      const safeTitle = (song.title || "Tanpa Judul")
        .replace(/[^A-Za-z0-9_\-]/g, "_")
        .replace(/_+/g, "_");
      const fileName = `${safeTitle}_${paperSize}.pdf`;
      doc.save(fileName);
    } else {
      // --- KASUS 2: ALBUM (LEBIH DARI SATU LAGU) DENGAN COVER ---
      const coverImg = new Image();
      coverImg.src = "assets/images/cover buku.png";

      const generateAlbumPDF = (addCover) => {
        let y = margin;
        let tocData = [];

        if (addCover) {
          doc.addImage(coverImg, "PNG", 0, 0, pageWidth, pageHeight);
        }

        const songPages = [];
        let tempDoc = new jsPDF({
          orientation: "p",
          unit: "mm",
          format: paperSize,
        });
        filteredSongsData.forEach((song) => {
          let tempY = margin;
          songPages.push(tempDoc.internal.getNumberOfPages());
          tempY += config.songTitleSize / 2;
          song.lines.forEach((line) => {
            tempY += config.lineGap;
            if (tempY > pageBottom) {
              tempDoc.addPage();
              tempY = margin;
            }
          });
        });

        // Render Lagu
        filteredSongsData.forEach((song, songIndex) => {
          doc.addPage();
          y = margin;
          const songTitle = song.title || "Tanpa Judul";
          const titleHeight = config.songTitleSize / 2;
          tocData.push({
            title: songTitle,
            page: doc.internal.getNumberOfPages(),
          });
          const songRefrensi = song.refrensi || "";
          doc
            .setFont("helvetica", "bold")
            .setFontSize(config.songTitleSize)
            .setTextColor(0, 0, 0);
          doc.text(songTitle, pageWidth / 2, y, {
            align: "center",
          });
          y += titleHeight / 2 + 2;

          if (songRefrensi.trim() !== "") {
            doc
              .setFont("helvetica", "italic")
              .setFontSize(config.noteSize - 1)
              .setTextColor(100);
            doc.text(`${songRefrensi}`, pageWidth / 2, y, {
              align: "center",
            });
            y += config.noteSize / 2;
          }
          song.lines.forEach((line) => {
            const lineHeight = config.lineGap;
            if (y + lineHeight > pageBottom) {
              doc.addPage();
              y = margin;
            }
            let x = margin;
            doc.setFont("courier", "normal");
            line.forEach((slot) => {
              const noteText = slot.note || "";
              const lyricText = slot.lyric || "";
              doc.setFontSize(config.noteSize);
              const noteWidth = doc.getTextWidth(noteText);
              doc.setFontSize(config.lyricSize);
              const lyricWidth = doc.getTextWidth(lyricText);
              const slotWidth = Math.max(noteWidth, lyricWidth) + 2;
              if (x + slotWidth > pageWidth - margin) {
                x = margin;
                y += lineHeight;
                if (y + lineHeight > pageBottom) {
                  doc.addPage();
                  y = margin;
                }
              }
              doc.setFontSize(config.noteSize).setTextColor(0, 0, 0);
              doc.text(noteText, x + 1, y);
              if (lyricText) {
                doc.setFontSize(config.lyricSize).setTextColor(100);
                doc.text(lyricText, x + 1, y + config.lyricSize / 2);
              }
              x += slotWidth;
            });
            y += lineHeight;
          });
        });

        // Buat Daftar Isi di halaman kedua
        const tocPage = addCover ? 2 : 1;
        doc.insertPage(tocPage);
        doc.setPage(tocPage);
        y = margin;
        doc.setFont("helvetica", "bold").setFontSize(config.tocTitleSize);
        doc.text("Daftar Isi", pageWidth / 2, y, {
          align: "center",
        });
        y += config.wrapGap;
        doc.setFont("times", "normal").setFontSize(config.noteSize);
        tocData.forEach((item) => {
          const pageNumberText = item.page.toString();
          const titleText = item.title;
          if (y + config.lineGap > pageBottom) {
            doc.addPage();
            y = margin;
          }
          const titleWidth = doc.getTextWidth(titleText);
          const pageNumWidth = doc.getTextWidth(pageNumberText);
          const availableWidth =
            pageWidth - margin * 2 - titleWidth - pageNumWidth - 2;
          const dotWidth = doc.getTextWidth(".");
          const numDots = Math.floor(availableWidth / dotWidth);
          const dots = ".".repeat(numDots > 0 ? numDots : 0);
          doc.text(titleText, margin, y);
          doc.text(dots, margin + titleWidth + 1, y);
          doc.text(pageNumberText, pageWidth - margin - pageNumWidth, y);
          y += config.lineGap / 1.5;
        });

        addPageNumbers(doc, addCover);
        const fileName = `Kalimelody_${paperSize}.pdf`;
        doc.save(fileName);
      };

      coverImg.onload = () => generateAlbumPDF(true);
      coverImg.onerror = () => {
        alert('Gagal memuat "cover buku". PDF akan dibuat tanpa cover.');
        generateAlbumPDF(false);
      };
    }
  }

  // --- EVENT LISTENERS ---
  dom.exportPdfBtn.addEventListener("click", () => {
    dom.paperSizeModal.classList.remove("hidden");
  });

  dom.cancelExportBtn.addEventListener("click", () => {
    dom.paperSizeModal.classList.add("hidden");
  });

  dom.continueExportBtn.addEventListener("click", () => {
    const selectedOption = dom.paperOptionsContainer.querySelector(
      ".paper-option.selected"
    );
    if (selectedOption) {
      const paperSize = selectedOption.dataset.size;
      exportAllToPDF(paperSize);
      dom.paperSizeModal.classList.add("hidden");
    } else {
      alert("Silakan pilih ukuran kertas terlebih dahulu.");
    }
  });

  dom.paperOptionsContainer.addEventListener("click", (e) => {
    const selectedOption = e.target.closest(".paper-option");
    if (!selectedOption) return;
    dom.paperOptionsContainer
      .querySelectorAll(".paper-option")
      .forEach((opt) => opt.classList.remove("selected"));
    selectedOption.classList.add("selected");
  });

  dom.filterBtn.addEventListener("click", () =>
    dom.filterModal.classList.remove("hidden")
  );

  dom.applyFilterBtn.addEventListener("click", () => {
    const selectedIndexes = Array.from(
      dom.filterCheckboxes.querySelectorAll("input:checked")
    ).map((cb) => parseInt(cb.value));
    filteredSongsData = allSongsData.filter((_, index) =>
      selectedIndexes.includes(index)
    );
    setupApplication(filteredSongsData);
    dom.filterModal.classList.add("hidden");
  });

  dom.filterModal.addEventListener("click", (e) => {
    if (e.target === dom.filterModal) dom.filterModal.classList.add("hidden");
  });

  dom.prevBtn.addEventListener("click", () => renderPage(currentPageIndex - 1));
  dom.nextBtn.addEventListener("click", () => renderPage(currentPageIndex + 1));
  dom.pageNumbersContainer.addEventListener("click", (e) => {
    if (e.target.matches(".page-num-btn"))
      renderPage(parseInt(e.target.dataset.page));
  });

  dom.searchFilterInput.addEventListener("input", function () {
    const searchTerm = this.value.toLowerCase();
    const labels = dom.filterCheckboxes.querySelectorAll("label");

    labels.forEach((label) => {
      const songTitle = label.querySelector("span").textContent.toLowerCase();
      if (songTitle.includes(searchTerm)) {
        label.style.display = "flex"; // Tampilkan jika cocok
      } else {
        label.style.display = "none"; // Sembunyikan jika tidak cocok
      }
    });
  });

  dom.checkAllBtn.addEventListener("click", () => {
    const checkboxes = dom.filterCheckboxes.querySelectorAll(
      'input[type="checkbox"]'
    );
    checkboxes.forEach((cb) => {
      // Hanya pengaruhi checkbox yang terlihat (tidak disembunyikan oleh filter pencarian)
      if (cb.closest("label").style.display !== "none") {
        cb.checked = true;
      }
    });
  });

  dom.uncheckAllBtn.addEventListener("click", () => {
    const checkboxes = dom.filterCheckboxes.querySelectorAll(
      'input[type="checkbox"]'
    );
    checkboxes.forEach((cb) => {
      // Hanya pengaruhi checkbox yang terlihat
      if (cb.closest("label").style.display !== "none") {
        cb.checked = false;
      }
    });
  });

  // --- INISIALISASI ---
  setupApplication(filteredSongsData);
  populatePaperOptions();
};
