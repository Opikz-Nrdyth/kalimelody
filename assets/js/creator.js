window.onload = function () {
  const dom = {
    title: document.getElementById("title"),
    refrensi: document.getElementById("refrensi"),
    linesContainer: document.getElementById("lines-container"),
    addLineBtn: document.getElementById("add-line-btn"),
    keyboardContainer: document.getElementById("virtual-keyboard-container"),
    keyboardNotes: document.getElementById("keyboard-notes"),
    keyboardLyrics: document.getElementById("keyboard-lyrics"),
    toggleNotes: document.getElementById("toggle-notes"),
    toggleLyrics: document.getElementById("toggle-lyrics"),
    hideKeyboardBtn: document.getElementById("hide-keyboard-btn"),
    previewArea: document.getElementById("preview-area"),
    saveBtn: document.getElementById("save-btn"),
    notification: document.getElementById("notification"),
    status: document.getElementById("status"),
    timestampInfo: document.getElementById("timestamp-info"),
  };

  const noteKeys = [
    "1°",
    "2°",
    "3°",
    "4°",
    "5°",
    "6°",
    "7°",
    "1°°",
    "2°°",
    "3°°",
    "4°°",
    "5°°",
    "6°°",
    "7°°",
    "1",
    "2",
    "3",
    "4",
    "5",
    "6",
    "7",
    "~",
    "(",
    ")",
    ".",
    "-",
    "Del",
  ];

  const lyricKeys = [
    "QWERTYUIOP".split(""),
    "ASDFGHJKL".split(""),
    ["CL", ..."ZXCVBNM".split(""), "Bksp"],
    ["Space"],
  ];

  let activeInput = null;
  let isCapsLockOn = false;

  function renderApp() {
    if (!tabData.status) {
      tabData.status = "draf";
    }
    dom.title.value = tabData.title || "";
    dom.refrensi.value = tabData.refrensi || "";
    dom.status.value = tabData.status || "draf";
    renderNotationGrid();
    updatePreview();
    updateTimestampDisplay(tabData);
  }

  function updateTimestampDisplay(data) {
    if (!data.created_at && !data.updated_at) {
      dom.timestampInfo.innerHTML = "";
      return;
    }

    const formatDate = (dateString) => {
      if (!dateString) return "";
      const d = new Date(dateString);
      return d.toLocaleString("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
      });
    };

    let html = "";
    if (data.created_at) {
      html += `<span>Dibuat: ${formatDate(data.created_at)}</span>`;
    }
    if (data.updated_at) {
      html += `<br><span>Diperbarui: ${formatDate(data.updated_at)}</span>`;
    }

    dom.timestampInfo.innerHTML = html;
  }

  function renderNotationGrid(focusTarget = null) {
    dom.linesContainer.innerHTML = "";
    tabData.lines.forEach((line, lineIndex) => {
      const lineDiv = document.createElement("div");
      lineDiv.className = "line-wrapper flex items-center space-x-2 mb-3";
      const slotsDiv = document.createElement("div");
      slotsDiv.className = "flex items-center space-x-2";
      line.forEach((slot, slotIndex) => {
        const slotDiv = document.createElement("div");
        slotDiv.className = "relative flex flex-col space-y-1 pt-3";
        const deleteBtn = document.createElement("button");
        deleteBtn.innerHTML =
          '<i class="fas fa-minus-circle text-red-400 hover:text-red-600"></i>';
        deleteBtn.className =
          "delete-slot-btn absolute top-0 left-0 text-lg leading-none";
        deleteBtn.dataset.line = lineIndex;
        deleteBtn.dataset.slot = slotIndex;
        slotDiv.appendChild(deleteBtn);
        const noteInput = document.createElement("input");
        noteInput.type = "text";
        noteInput.value = slot.note;
        noteInput.className =
          "note-input w-16 h-10 text-center border border-slate-300 rounded-md focus:outline-none";
        noteInput.dataset.line = lineIndex;
        noteInput.dataset.slot = slotIndex;
        noteInput.dataset.type = "note";
        noteInput.readOnly = true;
        slotDiv.appendChild(noteInput);
        const lyricInput = document.createElement("input");
        lyricInput.type = "text";
        lyricInput.value = slot.lyric;
        lyricInput.className =
          "lyric-input w-16 h-8 text-center text-sm border border-slate-300 rounded-md focus:outline-none";
        lyricInput.dataset.line = lineIndex;
        lyricInput.dataset.slot = slotIndex;
        lyricInput.dataset.type = "lyric";
        lyricInput.readOnly = window.screen.width < 768;
        slotDiv.appendChild(lyricInput);
        slotsDiv.appendChild(slotDiv);
      });
      lineDiv.appendChild(slotsDiv);
      const addSlotBtn = document.createElement("button");
      addSlotBtn.innerHTML = '<i class="fas fa-plus"></i>';
      addSlotBtn.className =
        "add-slot-btn bg-green-100 text-green-600 w-8 h-8 rounded-full hover:bg-green-200 flex-shrink-0";
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
          behavior: "smooth",
          block: "center",
          inline: "center",
        });
      }
    }
  }

  function updatePreview() {
    function centerText(text, width) {
      if (!text) return " ".repeat(width); // Handle jika teks null atau undefined
      if (text.length >= width) return text;
      const paddingTotal = width - text.length;
      const paddingLeft = Math.floor(paddingTotal / 2);
      const paddingRight = Math.ceil(paddingTotal / 2);
      return " ".repeat(paddingLeft) + text + " ".repeat(paddingRight);
    }
    let previewText = `${tabData.title || "Tanpa Judul"}\n`;
    if (tabData.refrensi && tabData.refrensi.trim() !== "") {
      previewText += `Referensi: ${tabData.refrensi}\n`;
    }
    previewText +=
      "-".repeat(tabData.title.length > 0 ? tabData.title.length : 10) + "\n\n";

    tabData.lines.forEach((line) => {
      const slotsWithWidth = line.map((s) => {
        const width = Math.max((s.note || "").length, (s.lyric || "").length);
        return {
          note: centerText(s.note, width),
          lyric: centerText(s.lyric, width),
        };
      });
      let noteLine = slotsWithWidth.map((s) => s.note).join(" ");
      let lyricLine = slotsWithWidth.map((s) => s.lyric).join(" ");
      previewText += `${noteLine}\n`;
      if (lyricLine.trim()) {
        previewText += `${lyricLine}\n`;
      }
      previewText += "\n";
    });
    dom.previewArea.textContent = previewText;
  }

  // --- Keyboard Lirik ---
  function updateLyricKeyboardCase() {
    const capsLockKey = document.getElementById("caps-lock-key");
    if (capsLockKey) {
      capsLockKey.classList.toggle("caps-active", isCapsLockOn);
    }

    const letterKeys = dom.keyboardLyrics.querySelectorAll(".lyric-letter-key");
    letterKeys.forEach((btn) => {
      const originalKey = btn.dataset.key;
      btn.textContent = isCapsLockOn
        ? originalKey.toUpperCase()
        : originalKey.toLowerCase();
    });
  }

  function generateKeyboards() {
    dom.keyboardNotes.innerHTML = "";
    noteKeys.forEach((key) => {
      const keyBtn = document.createElement("button");
      keyBtn.textContent = key;
      keyBtn.dataset.key = key;
      keyBtn.className =
        "keyboard-key h-12 rounded-lg shadow font-semibold transition transform hover:scale-105 w-full " +
        (key === "Del" ? "bg-red-200 text-red-800" : "bg-slate-200");
      dom.keyboardNotes.appendChild(keyBtn);
    });

    dom.keyboardLyrics.innerHTML = "";
    const lyricWrapper = document.createElement("div");
    lyricWrapper.className = "flex flex-col items-center space-y-2";
    lyricKeys.forEach((row) => {
      const rowDiv = document.createElement("div");
      rowDiv.className = "flex justify-center space-x-1.5 w-full";
      row.forEach((key) => {
        const keyBtn = document.createElement("button");
        keyBtn.textContent = key; // Teks awal (huruf besar)
        keyBtn.dataset.key = key;
        keyBtn.className =
          "keyboard-key h-12 rounded-lg shadow bg-slate-200 font-semibold transition";

        if (key.length > 1) {
          // Tombol fungsi (CapsLock, Bksp, Spasi)
          keyBtn.classList.add("px-3", "text-xs");
          if (key === "CL") keyBtn.id = "caps-lock-key";
        } else {
          // Tombol huruf biasa
          keyBtn.classList.add("w-10", "lyric-letter-key");
        }

        if (key === "Space") {
          keyBtn.textContent = "Spasi";
          keyBtn.classList.add("flex-grow");
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
    dom.notification.className =
      "fixed top-5 right-5 text-white py-2 px-4 rounded-lg shadow-xl transition-all duration-300 z-50 " +
      (isError ? "bg-red-500" : "bg-green-500");
    dom.notification.classList.remove("opacity-0", "-translate-y-10");
    setTimeout(
      () => dom.notification.classList.add("opacity-0", "-translate-y-10"),
      3000
    );
  }

  // --- logika input keyboard ---
  function handleKeyboardInput(key) {
    if (!activeInput) return;
    const { type } = activeInput.dataset;

    if (type === "note") {
      activeInput.value =
        key === "Del"
          ? activeInput.value.slice(0, -1)
          : activeInput.value + key;
    } else if (type === "lyric") {
      if (key === "Bksp") {
        activeInput.value = activeInput.value.slice(0, -1);
      } else if (key === "Space") {
        activeInput.value += " ";
      } else if (key !== "CL") {
        // Abaikan tombol CapsLock
        activeInput.value += isCapsLockOn
          ? key.toUpperCase()
          : key.toLowerCase();
      }
    }
    const { line, slot } = activeInput.dataset;
    tabData.lines[line][slot][type] = activeInput.value;
    updatePreview();
  }

  // --- EVENT LISTENERS ---

  dom.title.addEventListener("input", (e) => {
    tabData.title = e.target.value;
    updatePreview();
  });

  dom.refrensi.addEventListener("input", (e) => {
    tabData.refrensi = e.target.value;
    updatePreview();
  });

  dom.status.addEventListener("change", () => {
    tabData.status = dom.status.value;
  });

  dom.linesContainer.addEventListener("focusin", (e) => {
    if (e.target.matches(".note-input, .lyric-input")) {
      activeInput = e.target;
      dom.keyboardContainer.classList.add("keyboard-visible");
    }
  });

  dom.linesContainer.addEventListener("input", (e) => {
    if (e.target.matches(".note-input, .lyric-input")) {
      const { line, slot, type } = e.target.dataset;
      tabData.lines[line][slot][type] = e.target.value;
      updatePreview();
    }
  });

  dom.linesContainer.addEventListener("click", (e) => {
    const addSlotBtn = e.target.closest(".add-slot-btn");
    if (addSlotBtn) {
      const lineIndex = parseInt(addSlotBtn.dataset.line);
      const newSlotIndex = tabData.lines[lineIndex].length;
      tabData.lines[lineIndex].push({
        note: "",
        lyric: "",
      });
      renderNotationGrid({
        line: lineIndex,
        slot: newSlotIndex,
      });
    }
    const deleteSlotBtn = e.target.closest(".delete-slot-btn");
    if (deleteSlotBtn) {
      const { line, slot } = deleteSlotBtn.dataset;
      tabData.lines[line].splice(slot, 1);
      if (tabData.lines[line].length === 0) tabData.lines.splice(line, 1);
      if (tabData.lines.length === 0)
        tabData.lines.push([
          {
            note: "",
            lyric: "",
          },
        ]);
      renderNotationGrid();
    }
  });

  dom.addLineBtn.addEventListener("click", () => {
    const newLineIndex = tabData.lines.length;
    tabData.lines.push([
      {
        note: "",
        lyric: "",
      },
    ]);
    renderNotationGrid({
      line: newLineIndex,
      slot: 0,
    });
  });

  // ---  klik untuk CapsLock ---
  dom.keyboardLyrics.addEventListener("click", (e) => {
    const keyBtn = e.target.closest(".keyboard-key");
    if (!keyBtn) return;

    const key = keyBtn.dataset.key;
    if (key === "CL") {
      isCapsLockOn = !isCapsLockOn; // Toggle state
      updateLyricKeyboardCase(); // Update tampilan
    } else if (key) {
      e.preventDefault();
      handleKeyboardInput(key);
      activeInput?.focus();
    }
  });

  // Event listener untuk keyboard notasi (dipisah agar tidak bentrok)
  dom.keyboardNotes.addEventListener("click", (e) => {
    const key = e.target.closest(".keyboard-key")?.dataset.key;
    if (key) {
      e.preventDefault();
      handleKeyboardInput(key);
      activeInput?.focus();
    }
  });

  dom.toggleNotes.addEventListener("click", () => {
    dom.keyboardNotes.classList.remove("hidden");
    dom.keyboardLyrics.classList.add("hidden");
    dom.toggleNotes.className =
      "keyboard-toggle flex-1 py-3 px-4 font-semibold text-blue-600 border-b-2 border-blue-600";
    dom.toggleLyrics.className =
      "keyboard-toggle flex-1 py-3 px-4 font-semibold text-slate-500";
  });

  dom.toggleLyrics.addEventListener("click", () => {
    dom.keyboardLyrics.classList.remove("hidden");
    dom.keyboardNotes.classList.add("hidden");
    dom.toggleLyrics.className =
      "keyboard-toggle flex-1 py-3 px-4 font-semibold text-blue-600 border-b-2 border-blue-600";
    dom.toggleNotes.className =
      "keyboard-toggle flex-1 py-3 px-4 font-semibold text-slate-500";
  });

  dom.hideKeyboardBtn.addEventListener("click", () => {
    dom.keyboardContainer.classList.remove("keyboard-visible");
    activeInput?.blur();
  });

  dom.saveBtn.addEventListener("click", async () => {
    dom.saveBtn.disabled = true;
    dom.saveBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    const urlParams = new URLSearchParams(window.location.search);
    const existingFilename = urlParams.get("file");
    let dataToSave = JSON.parse(JSON.stringify(tabData));
    if (existingFilename) {
      dataToSave.existingFilename = existingFilename;
    }
    try {
      const response = await fetch("creator.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(dataToSave),
      });
      const result = await response.json();
      if (response.ok) {
        showNotification(result.message);
        updateTimestampDisplay(tabData);
        if (!existingFilename && result.filename) {
          window.history.replaceState(
            {},
            "",
            `creator.php?file=${result.filename}`
          );
        }
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      showNotification(
        error.message ||
          "Gagal menyimpan. Server tidak memberikan respon JSON.",
        true
      );
    } finally {
      dom.saveBtn.disabled = false;
      dom.saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan';
    }
  });

  // --- INISIALISASI ---
  renderApp();
  generateKeyboards();
};
