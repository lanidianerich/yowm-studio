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
