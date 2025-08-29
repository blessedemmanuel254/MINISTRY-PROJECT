<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Jesus Is Lord Radio | Returntoholiness</title>
  <meta name="description" content="Listen live to Jesus Is Lord Radio." />

  <link rel="stylesheet" href="Styles/general.css">
  <link rel="preconnect" href="https://s3.radio.co" crossorigin />

  <link rel="stylesheet" href="Styles/general.css">

  <link rel="icon" type="image/png" href="Images/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="Images/favicon.svg" />
  <link rel="shortcut icon" href="Images/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="Images/apple-touch-icon.png" />
  <link rel="manifest" href="Images/site.webmanifest" />

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <section class="container">
      <a href="index.php" class="hContainer">
        <img src="Images/Ministry Logo.png" alt="Ministry Logo" width="40">
        <h1>THE&nbsp;MINISTRY&nbsp;OF<br>REPENTANCE&nbsp;AND&nbsp;HOLINESS</h1>
      </a>
      <div class="contnrHA">
        <a class="rdCll" href="tel:+254777445851"><i class="fa-solid fa-phone-volume"></i> Call&nbsp;the&nbsp;Radio</a>
        <a href="#" class="help-icon">
          <i class="fa-regular fa-circle-question"></i>
          <p class="help-text">Help</p>
        </a>
      </div>
      <i class="fa-solid fa-bars" onclick="toggleSideBar()"></i>
    </section>
    <section class="container scnd">
      <ul>
        <a href="index.php"><li>Home</li></a>
        <a onclick="toggleCodePopup()"><li>Announcements</li></a>
        <a onclick="toggleCodePopup()"><li>Activities</li></a>
        <a href="radioPage.php" class="active"><li>J.I.L Radio</li></a>
        <a href="altarPortal.php"><li>Altar&nbsp;Account</li></a>
        <a href=""><li>FAQs</li></a>
      </ul>
    </section>
  </header>

  <div class="overlay" id="overlay" onclick="toggleSideBar()"></div>
  <div class="codOverlay" id="codOverlay" onclick="toggleCodePopup()"></div>
  <form action="" id="codePrompt" class="codPopup">
    <span>Enter your unique Altar Code to proceed;</span>
    <input type="text" name="" id="">
    <button type="submit" id="codPopBtn">View</button>
  </form action="">
  <div class="sideBar" id="sidebar">
    <div class="sContainer">
      <img src="Images/Jesus is Lord Radio Logo.avif" alt="Jesus is Lord Radio Logo" width="140">
      <i class="fa-solid fa-xmark" onclick="toggleSideBar()"></i>
    </div>
    <ul>
      <a href="index.php">Home</a>
      <p onclick="toggleCodePopup(), toggleSideBar()">Announcements</p>
      <a href="radioPage.php" class="active">J.I.L&nbsp;Radio</a>
      <a onclick="toggleCodePopup()"><li>Activities</li></a>
      <a href="altarPortal.php">Altar Account</a>
      <a href="">FAQs</a>
    </ul>
    <a class="ercr" href="radioPage.php"><i class="fa-solid fa-radio"></i> Listen&nbsp;to&nbsp;Radio</a>
    <a class="ercr" href="#"><i class="fa-regular fa-circle-question"></i> Help</a>
  </div>
  <main id="home" class="rdHme">
    <div class="player" role="region" aria-label="Radio Player">
      <!-- Top Row -->
      <div class="row title">
        <span class="live-dot" aria-hidden="true"></span>
        <span>JESUS IS LORD RADIO</span>
        <span class="badge" id="stateBadge">Idle</span>
      </div>

      <!-- Main Controls -->
      <div class="panel">
        <button class="icon play" id="playBtn" aria-label="Play/Pause">
          <!-- Play icon -->
          <svg id="playIcon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M8 5v14l11-7z"></path>
          </svg>
          <!-- Pause icon (hidden by default) -->
          <svg id="pauseIcon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="display:none">
            <path d="M6 5h4v14H6zM14 5h4v14h-4z"></path>
          </svg>
        </button>

        <div class="eq" aria-hidden="true">
          <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </div>

        <div class="meta">
          <div class="sub" id="status">Ready to play</div>
        </div>

        <div class="spacer"></div>

        <button class="icon" id="muteBtn" aria-label="Mute/Unmute">
          <svg id="volIcon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M3 10v4h4l5 5V5L7 10H3z"></path>
          </svg>
        </button>

        <div class="vol" aria-label="Volume">
          <input id="volume" type="range" min="0" max="1" step="0.01" value="0.8" />
        </div>
      </div>

      <div class="foot">
        <span>Tip: Some browsers block autoplay. Click ▶️ to start.</span>
        <a class="link" href="https://s3.radio.co/s97f38db97/listen" target="_blank" rel="noopener"></a>
      </div>

      <!-- Hidden audio element -->
      <audio id="radio" preload="none" crossorigin="anonymous">
        <source src="https://s3.radio.co/s97f38db97/listen" type="audio/mpeg" />
        Your browser does not support HTML audio.
      </audio>
    </div>
  </main>

  <footer>
    <div class="container">
      <div>
        <a href="#">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="#">
          <i class="fab fa-twitter"></i>
        </a>
        <a href="#">
          <i class="fa-brands fa-tiktok"></i>
        </a>
        <a href="#">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="#">
          <i class="fab fa-linkedin-in"></i>
        </a>
        <a href="https://www.youtube.com/@repentpreparetheway" target="_blank">
          <i class="fab fa-youtube"></i>
        </a>
      </div>
      <p>&copy;2025 <a href="">returntoholiness.org,</a> All Rights Reserved</p>
    </div>
  </footer>

  <script>
    const audio   = document.getElementById('radio');
    const playBtn = document.getElementById('playBtn');
    const state   = document.getElementById('stateBadge');
    const status  = document.getElementById('status');
    const nowEl   = document.getElementById('nowTitle');
    const vol     = document.getElementById('volume');
    const muteBtn = document.getElementById('muteBtn');
    const volIcon = document.getElementById('volIcon');
    const playIcon  = document.getElementById('playIcon');
    const pauseIcon = document.getElementById('pauseIcon');

    // Set initial volume & progress color
    function updateVolGradient() {
      const pct = (vol.value * 100) + '%';
      vol.style.setProperty('--pos', pct);
    }
    vol.addEventListener('input', () => {
      audio.volume = +vol.value;
      updateVolGradient();
      if (audio.muted && audio.volume > 0) {
        audio.muted = false;
        volIcon.innerHTML = '<path d="M3 10v4h4l5 5V5L7 10H3z"/>';
      }
    });
    updateVolGradient();

    // Play / Pause
    async function togglePlay() {
      const player = document.querySelector('.player');

      if (audio.paused) {
        try {
          state.textContent = 'Connecting…';
          status.textContent = 'Buffering stream…';
          await audio.play();
          playBtn.classList.add('paused');
          playIcon.style.display = 'none';
          pauseIcon.style.display = 'block';
          state.textContent = 'Live';
          status.textContent = 'Playing';

          player.classList.add('playing'); // <-- start animations
        } catch (err) {
          state.textContent = 'Error';
          status.textContent = 'Click to try again (autoplay blocked)';
          console.error(err);
        }
      } else {
        audio.pause();
        playBtn.classList.remove('paused');
        playIcon.style.display = 'block';
        pauseIcon.style.display = 'none';
        state.textContent = 'Paused';
        status.textContent = 'Stopped';

        player.classList.remove('playing'); // <-- stop animations
      }
    }

    playBtn.addEventListener('click', togglePlay);

    // Mute / Unmute
    muteBtn.addEventListener('click', () => {
      audio.muted = !audio.muted;
      volIcon.innerHTML = audio.muted
        ? '<path d="M16.5 12l4.24-4.24-1.41-1.41L15.09 10.59 10.5 6v12l4.59-4.59 4.24 4.24 1.41-1.41L16.5 12zM3 10v4h4l5 5V5L7 10H3z"/>'
        : '<path d="M3 10v4h4l5 5V5L7 10H3z"/>';
      status.textContent = audio.muted ? 'Muted' : (audio.paused ? 'Paused' : 'Playing');
    });

    // Basic metadata stub (you can replace with your API if available)
    // If your streaming provider exposes "now playing" JSON, fetch it and update nowEl.
    nowEl.textContent = 'JESUS IS LORD RADIO';

    // Connection state helpers
    audio.addEventListener('playing', () => { state.textContent = 'Live'; status.textContent = 'Playing'; });
    audio.addEventListener('waiting', () => { state.textContent = 'Buffering'; status.textContent = 'Reconnecting…'; });
    audio.addEventListener('stalled', () => { status.textContent = 'Network stalled…'; });
    audio.addEventListener('error',   () => { state.textContent = 'Error'; status.textContent = 'Stream error (check link)'; });

    // Optional: resume if stream drops
    let retryTimer;
    audio.addEventListener('pause', () => {
      clearTimeout(retryTimer);
      if (!audio.ended && !audio.seeking && !audio.paused) {
        retryTimer = setTimeout(() => audio.play().catch(()=>{}), 1500);
      }
    });
  </script>
  <script src="Scripts/general.js"></script>
</body>
</html>