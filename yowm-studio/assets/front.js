document.addEventListener("click", async (event) => {
  const button = event.target.closest("[data-yowm-copy-url]");
  if (!button) return;
  event.preventDefault();
  const url = button.dataset.yowmCopyUrl;
  if (!url) return;
  const original = button.textContent;
  try {
    await navigator.clipboard.writeText(url);
  } catch (error) {
    const field = document.createElement("textarea");
    field.value = url;
    field.style.position = "fixed";
    field.style.opacity = "0";
    document.body.appendChild(field);
    field.select();
    document.execCommand("copy");
    field.remove();
  }
  button.textContent = "Copied!";
  window.setTimeout(() => { button.textContent = original; }, 1800);
});

// On student-facing classroom pages, open every link that leaves the site in a
// new tab so students never lose their place. Internal classroom navigation
// (same host) stays in the same tab.
function yowmExternalLinksNewTab() {
  if (!document.body.classList.contains("yowm-cohort-view")) return;
  document.querySelectorAll("a[href]").forEach((link) => {
    let url;
    try {
      url = new URL(link.href, window.location.href);
    } catch (error) {
      return;
    }
    if (url.protocol !== "http:" && url.protocol !== "https:") return; // skip mailto:, tel:, etc.
    if (url.host === window.location.host) return; // keep internal links in the same tab
    link.target = "_blank";
    link.rel = "noopener noreferrer";
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", yowmExternalLinksNewTab);
} else {
  yowmExternalLinksNewTab();
}
