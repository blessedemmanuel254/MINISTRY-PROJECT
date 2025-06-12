<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Landing page | repentancestats.co.ke</title>

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
</head>
<body>
  <div class="container">
    <header>
      <a href="index.php" class="hContainer">
        <img src="Images/Ministry Logo.png" alt="Ministry Logo" width="40">
        <h1>THE&nbsp;MINISTRY&nbsp;OF<br>REPENTANCE&nbsp;&&nbsp;HOLINESS</h1>
      </a>
      <div class="contnrHA">
        <a href="https://www.jesusislordradio.info/" target="_blank"><i class="fa-solid fa-radio"></i> Listen&nbsp;to&nbsp;Radio</a>
        <a href="#" class="help-icon">
          <i class="fa-regular fa-circle-question"></i>
          <p class="help-text">Help</p>
        </a>
      </div>
      <i class="fa-solid fa-bars" onclick="toggleSideBar()"></i>
    </header>

    <div class="overlay" id="overlay" onclick="toggleSideBar()"></div>
    <div class="sideBar" id="sidebar">
      <div class="sContainer">
        <img src="Images/Jesus is Lord Radio Logo.avif" alt="Jesus is Lord Radio Logo" width="100">
        <i class="fa-solid fa-xmark" onclick="toggleSideBar()"></i>
      </div>
      <a class="ercr" href="https://www.jesusislordradio.info/" target="_blank"><i class="fa-solid fa-radio"></i> Listen&nbsp;to&nbsp;Radio</a>
      <a class="ercr" href="#">Help</a>
    </div>
  </div>
</body>
</html>