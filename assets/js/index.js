document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("preview-modal");
  const modalTitle = document.getElementById("modal-title");
  const modalRefrensi = document.getElementById("modal-refrensi");
  const modalContent = document.getElementById("modal-content");
  const modalCloseBtn = document.getElementById("modal-close-btn");
  const songListTable = document.getElementById("song-list-table");
  const notification = document.getElementById("notification");
  const modalFullscreenBtn = document.getElementById("modal-fullscreen-btn");
  const showRestoreModalBtn = document.getElementById("show-restore-modal-btn");
  const restoreModal = document.getElementById("restore-modal");
  const cancelRestoreBtn = document.getElementById("cancel-restore-btn");
  const backupButtons = document.querySelectorAll(".backup-btn");
  const DOUBLE_CLICK_THRESHOLD = 400;

  function closeModal() {
    const modalContainer = modal.querySelector(".modal-container");
    const icon = modalFullscreenBtn.querySelector("i");
    if (modalContainer.classList.contains("modal-fullscreen")) {
      modalContainer.classList.remove("modal-fullscreen");
      icon.classList.remove("fa-compress");
      icon.classList.add("fa-expand");
      modalFullscreenBtn.title = "Layar Penuh";
    }
    modal.classList.add("hidden");
  }

  function formatPreviewHTML(tabData) {
    let htmlOutput = "";
    tabData.lines.forEach((line) => {
      let lineHTML = '<div class="line-preview">';
      line.forEach((slot) => {
        lineHTML += `<div class="slot-preview"><span class="note-preview text-black dark:text-white">${
          slot.note || "&nbsp;"
        }</span><span class="lyric-preview text-gray-800 dark:text-gray-500">${
          slot.lyric || "&nbsp;"
        }</span></div>`;
      });
      lineHTML += "</div>";
      htmlOutput += lineHTML;
    });
    return htmlOutput;
  }

  function showNotification(message, isError = false) {
    notification.textContent = message;
    notification.className =
      "fixed top-5 right-5 text-white py-2 px-4 rounded-lg shadow-xl transition-all duration-300 z-50 " +
      (isError ? "bg-red-500" : "bg-green-500");
    notification.classList.remove("opacity-0", "-translate-y-10");
    setTimeout(
      () => notification.classList.add("opacity-0", "-translate-y-10"),
      3000
    );
  }

  backupButtons.forEach((button) => {
    button.dataset.lastClick = 0;

    button.addEventListener("click", function (event) {
      const currentTime = new Date().getTime();
      const lastClickTime = parseInt(button.dataset.lastClick);

      // Cek selisih waktu antara klik sekarang dan klik terakhir
      if (currentTime - lastClickTime < DOUBLE_CLICK_THRESHOLD) {
        button.dataset.lastClick = 0;
        if (!confirm("Konfirmasi backup untuk lagu ini?")) {
          event.preventDefault();
        }
      } else {
        event.preventDefault();
        button.dataset.lastClick = currentTime;
        const originalHTML = button.innerHTML;
        button.innerHTML =
          '<i class="fas fa-exclamation-circle mr-1"></i>Klik lagi!';
        setTimeout(() => {
          if (parseInt(button.dataset.lastClick) !== 0) {
            button.innerHTML = originalHTML;
            button.dataset.lastClick = 0; // Reset
          }
        }, 500); // Reset setelah 0.5 detik
      }
    });
  });

  // Modal Controller
  showRestoreModalBtn.addEventListener("click", () => {
    restoreModal.classList.remove("hidden");
  });

  cancelRestoreBtn.addEventListener("click", () => {
    restoreModal.classList.add("hidden");
  });

  restoreModal.addEventListener("click", (e) => {
    if (e.target === dom.restoreModal) {
      restoreModal.classList.add("hidden");
    }
  });

  // --- EVENT LISTENERS ---
  songListTable.addEventListener("click", async (e) => {
    const previewBtn = e.target.closest(".preview-btn");
    const deleteBtn = e.target.closest(".delete-btn");

    if (previewBtn) {
      const songDataJSON = previewBtn.dataset.songContent;
      const songData = JSON.parse(songDataJSON);
      modalTitle.textContent = songData.title || "Pratinjau";
      if (!songData.refrensi) {
        modalRefrensi.classList.add("hidden");
      } else {
        modalRefrensi.classList.remove("hidden");
      }
      modalRefrensi.setAttribute("href", songData.refrensi || "");
      modalContent.innerHTML = formatPreviewHTML(songData);
      modal.classList.remove("hidden");
    }

    if (deleteBtn) {
      const title = deleteBtn.dataset.title;
      const filename = deleteBtn.dataset.filename;
      if (!confirm(`Apakah Anda yakin ingin menghapus lagu "${title}"?`))
        return;

      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("filename", filename);
      try {
        const response = await fetch("index.php", {
          method: "POST",
          body: formData,
        });
        const result = await response.json();
        if (response.ok && result.status === "success") {
          showNotification(result.message, false);
          deleteBtn.closest("tr").remove();
          if (songListTable.querySelector("tbody tr") === null) {
            const tbody = songListTable.querySelector("tbody");
            tbody.innerHTML = `<tr id="no-songs-row"><td colspan="2" class="p-8 text-center text-slate-500">Belum ada lagu yang disimpan.</td></tr>`;
          }
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        showNotification(error.message || "Terjadi kesalahan.", true);
      }
    }
  });

  // Event listener untuk tombol fullscreen
  modalFullscreenBtn.addEventListener("click", () => {
    const modalContainer = modal.querySelector(".modal-container");
    const icon = modalFullscreenBtn.querySelector("i");

    modalContainer.classList.toggle("modal-fullscreen");

    if (modalContainer.classList.contains("modal-fullscreen")) {
      icon.classList.remove("fa-expand");
      icon.classList.add("fa-compress");
      modalFullscreenBtn.title = "Kembali ke normal";
    } else {
      icon.classList.remove("fa-compress");
      icon.classList.add("fa-expand");
      modalFullscreenBtn.title = "Layar Penuh";
    }
  });

  modalCloseBtn.addEventListener("click", closeModal);
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      closeModal();
    }
  });
});
