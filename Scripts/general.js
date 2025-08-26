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

function toggleDropdown() {
  const overlayDropdown = document.getElementById('overlayDropdown');
  const Dropdown = document.getElementById('Dropdown');
  const DropdownS = document.getElementById('DropdownS');
  overlayDropdown.classList.toggle('active');
  Dropdown.classList.toggle('active');
  DropdownS.classList.toggle('active');
}

function toggleDropdownS() {
  const DropdownS = document.getElementById('DropdownS');
  DropdownS.classList.toggle('active');
}

/* Members progress graph Js */

const ctx = document.getElementById('membersLineChart').getContext('2d');

new Chart(ctx, {
  type: 'line',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'], // months
    datasets: [
      {
        label: 'Active Members',
        data: [120, 150, 180, 200, 220, 250, 280], // example data
        borderColor: 'green',
        backgroundColor: 'rgba(15, 38, 140, 0.2)',
        tension: 0.3,
        fill: true
      },
      {
        label: 'Inactive Members',
        data: [50, 60, 55, 70, 65, 75, 80], // example data
        borderColor: 'red',
        backgroundColor: 'rgba(255, 0, 0, 0.2)',
        tension: 0.3,
        fill: true
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Active vs Inactive Members Over Time'
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

/* Active vs Inactive Line Graph Js */

const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleBtn");
const dash = document.querySelector(".cntnrDash");

toggleBtn.addEventListener("click", () => {
  sidebar.classList.toggle("active");
  dash.classList.toggle("shift"); // optional: shift dashboard content
});