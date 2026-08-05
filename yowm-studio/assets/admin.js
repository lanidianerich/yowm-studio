(function () {
  "use strict";

  function updateResourcePanels() {
    const selected = document.querySelector('input[name="yowm_resource_type"]:checked');
    if (selected) {
      document.querySelectorAll("[data-yowm-resource-panel]").forEach((panel) => {
        panel.hidden = panel.dataset.yowmResourcePanel !== selected.value;
      });
    }

    const scope = document.querySelector('input[name="yowm_resource_scope"]:checked');
    const choices = document.querySelector("[data-yowm-cohort-choices]");
    if (scope && choices) {
      choices.hidden = scope.value !== "specific";
    }

    const lessonScope = document.querySelector('input[name="yowm_lesson_scope"]:checked');
    const lessonChoices = document.querySelector("[data-yowm-lesson-cohort-choices]");
    if (lessonScope && lessonChoices) {
      lessonChoices.hidden = lessonScope.value !== "specific";
    }
  }

  document.addEventListener("click", (event) => {
    const addVersion = event.target.closest("[data-yowm-add-version]");
    if (addVersion) {
      event.preventDefault();
      const template = document.querySelector("[data-yowm-version-template]");
      const list = document.querySelector("[data-yowm-version-list]");
      if (!template || !list) return;
      const id = `v${Date.now().toString(36)}`;
      list.insertAdjacentHTML("beforeend", template.innerHTML.replaceAll("__VERSION_ID__", id));

      document.querySelectorAll("[data-yowm-version-assignment-select]").forEach((select) => {
        const option = document.createElement("option");
        option.value = id;
        option.textContent = "Untitled version";
        option.dataset.yowmVersionOption = id;
        select.appendChild(option);
      });

      const added = list.querySelector(`[data-yowm-version-id="${id}"]`);
      const label = added ? added.querySelector("[data-yowm-version-label]") : null;
      if (label) label.focus();
      return;
    }

    const selectAllLecture = event.target.closest("[data-yowm-select-all-lecture-cohorts]");
    if (selectAllLecture) {
      event.preventDefault();
      document.querySelectorAll("[data-yowm-lecture-cohort]").forEach((checkbox) => {
        checkbox.checked = true;
        checkbox.dispatchEvent(new Event("change", { bubbles: true }));
      });
      return;
    }

    const clearLecture = event.target.closest("[data-yowm-clear-lecture-cohorts]");
    if (clearLecture) {
      event.preventDefault();
      document.querySelectorAll("[data-yowm-lecture-cohort]").forEach((checkbox) => {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event("change", { bubbles: true }));
      });
      return;
    }

    const imageButton = event.target.closest("[data-yowm-image-target]");
    if (imageButton) {
      event.preventDefault();
      const target = document.querySelector(imageButton.dataset.yowmImageTarget);
      const preview = document.querySelector("[data-yowm-artwork-preview]");
      if (!target || !window.wp || !wp.media) return;

      const frame = wp.media({
        title: "Choose podcast artwork",
        button: { text: "Use this artwork" },
        library: { type: "image" },
        multiple: false,
      });

      frame.on("select", () => {
        const attachment = frame.state().get("selection").first().toJSON();
        target.value = attachment.id || "";
        if (preview) {
          const url =
            attachment.sizes && attachment.sizes.medium
              ? attachment.sizes.medium.url
              : attachment.url;
          preview.innerHTML = `<img src="${url}" alt="">`;
        }
      });

      frame.open();
      return;
    }

    const clearImageButton = event.target.closest("[data-yowm-clear-image]");
    if (clearImageButton) {
      event.preventDefault();
      const target = document.querySelector(clearImageButton.dataset.yowmClearImage);
      const preview = document.querySelector("[data-yowm-artwork-preview]");
      if (target) target.value = "";
      if (preview) preview.innerHTML = "<span>No artwork selected</span>";
      return;
    }

    const mediaButton = event.target.closest("[data-yowm-media-target]");
    if (mediaButton) {
      event.preventDefault();
      const target = document.querySelector(mediaButton.dataset.yowmMediaTarget);
      if (!target || !window.wp || !wp.media) return;

      const frame = wp.media({
        title: "Choose an audio file",
        button: { text: "Use this audio" },
        library: { type: "audio" },
        multiple: false,
      });

      frame.on("select", () => {
        const attachment = frame.state().get("selection").first().toJSON();
        let status = mediaButton.parentElement.querySelector(".yowm-conversion-status");

        if (!status) {
          status = document.createElement("span");
          status.className = "yowm-conversion-status";
          mediaButton.insertAdjacentElement("afterend", status);
        }

        const filename = attachment.filename || attachment.url || "";
        const extension = filename.includes(".")
          ? filename.split(".").pop().toLowerCase().split(/[?#]/)[0]
          : "";

        target.value = attachment.url || "";
        target.dispatchEvent(new Event("change", { bubbles: true }));

        if (extension === "mp3") {
          status.textContent = "Podcast-ready MP3 selected.";
          status.classList.remove("is-error");
          return;
        }

        status.textContent =
          "This file is not an MP3. Convert it on your computer, upload the MP3, and select that version before publishing.";
        status.classList.add("is-error");
      });

      frame.open();
      return;
    }

    const copyButton = event.target.closest("[data-yowm-copy]");
    if (copyButton) {
      event.preventDefault();
      const target = document.querySelector(copyButton.dataset.yowmCopy);
      if (!target) return;

      target.select();
      target.setSelectionRange(0, target.value.length);
      navigator.clipboard.writeText(target.value).then(() => {
        const original = copyButton.textContent;
        copyButton.textContent = "Copied";
        window.setTimeout(() => {
          copyButton.textContent = original;
        }, 1400);
      });
    }
  });

  function updateVersionLabel(event) {
    if (!event.target.matches("[data-yowm-version-label]")) return;
    const version = event.target.closest("[data-yowm-version-id]");
    if (!version) return;
    const id = version.dataset.yowmVersionId;
    const label = event.target.value.trim() || "Untitled version";
    const archived = Boolean(version.querySelector("[data-yowm-version-archived]")?.checked);
    const summary = version.querySelector("[data-yowm-version-summary]");
    if (summary) summary.textContent = label;
    document.querySelectorAll(`[data-yowm-version-option="${id}"]`).forEach((option) => {
      option.textContent = archived ? `${label} — Archived` : label;
    });
  }

  document.addEventListener("input", updateVersionLabel);

  document.addEventListener("change", (event) => {
    if (
      event.target.matches('input[name="yowm_resource_type"]') ||
      event.target.matches('input[name="yowm_resource_scope"]') ||
      event.target.matches('input[name="yowm_lesson_scope"]')
    ) {
      updateResourcePanels();
    }

    if (event.target.matches("[data-yowm-lecture-cohort]")) {
      const row = event.target.closest(".yowm-lecture-cohort-row");
      const release = row ? row.querySelector("[data-yowm-lecture-release-wrap]") : null;
      if (release) release.hidden = !event.target.checked;
    }

    if (event.target.matches("[data-yowm-version-label]")) {
      const version = event.target.closest("[data-yowm-version-id]");
      if (!version) return;
      const id = version.dataset.yowmVersionId;
      const label = event.target.value.trim() || "Untitled version";
      const archived = Boolean(version.querySelector("[data-yowm-version-archived]")?.checked);
      const optionLabel = archived ? `${label} — Archived` : label;

      version.querySelector("[data-yowm-version-summary]").textContent = label;
      document.querySelectorAll(`[data-yowm-version-option="${id}"]`).forEach((option) => {
        option.textContent = optionLabel;
      });
    }

    if (event.target.matches("[data-yowm-version-archived]")) {
      const version = event.target.closest("[data-yowm-version-id]");
      if (!version) return;
      const id = version.dataset.yowmVersionId;
      const label = version.querySelector("[data-yowm-version-label]")?.value.trim() || "Untitled version";
      const archived = event.target.checked;
      const status = version.querySelector("[data-yowm-version-status]");
      if (status) status.textContent = archived ? "Archived" : "Current";

      document.querySelectorAll(`[data-yowm-version-option="${id}"]`).forEach((option) => {
        option.textContent = archived ? `${label} — Archived` : label;
      });
    }
  });

  updateResourcePanels();
})();
