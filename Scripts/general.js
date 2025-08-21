/* Sidebar Js */
function toggleSideBar() {
  const popupbar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  popupbar.classList.toggle('active');
  overlay.classList.toggle('active');
}

/* Code Popup Overlay */
function toggleCodePopup() {
  const codOverlay = document.getElementById('codOverlay');
  const codePrompt = document.getElementById('codePrompt');
  codOverlay.classList.toggle('active');
  codePrompt.classList.toggle('active');
}