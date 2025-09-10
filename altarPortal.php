<?php
session_start();
require_once "connection.php"; // your DB connection

// Redirect if altar not logged in
if (!isset($_SESSION['altar_id'])) {
  header("Location: altarLogin.php");
  exit();
}

$altar_id = $_SESSION['altar_id'];
$altar_name = $_SESSION['altar_name'];
$altar_type = $_SESSION['altar_type'];

// ===== Fetch Altar Name =====
$stmt = $conn->prepare("SELECT altar_name FROM altars WHERE altar_id = ?");
$stmt->bind_param("i", $altar_id);
$stmt->execute();
$stmt->bind_result($altar_name);
$stmt->fetch();
$stmt->close();

// ===== Fetch Statistics =====

// Total members
$result = $conn->query("SELECT COUNT(*) AS total FROM members WHERE altar_id = $altar_id");
$totalMembers = $result->fetch_assoc()['total'];

// Active members (status = 'Active')
$result = $conn->query("SELECT COUNT(*) AS total FROM members WHERE altar_id = $altar_id AND status = 'Active'");
$activeMembers = $result->fetch_assoc()['total'];

// Inactive members (status = 'Inactive')
$result = $conn->query("SELECT COUNT(*) AS total FROM members WHERE altar_id = $altar_id AND status = 'Inactive'");
$inactiveMembers = $result->fetch_assoc()['total'];

// ===== NEW: First Years =====
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM followup_details WHERE altar_id = ? AND mission_type = 'FYR'");
$stmt->bind_param("i", $altar_id);
$stmt->execute();
$result = $stmt->get_result();
$firstYears = $result->fetch_assoc()['total'];
$stmt->close();

// ===== NEW: Pending Followups =====
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM followup_details WHERE altar_id = ? AND status = 0");
$stmt->bind_param("i", $altar_id);
$stmt->execute();
$result = $stmt->get_result();
$pendingFollowups = $result->fetch_assoc()['total'];
$stmt->close();

// Pending followups
$result = $conn->query("SELECT COUNT(*) AS total FROM followup_details WHERE altar_id = $altar_id AND status = '0'");
$pendingFollowups = $result->fetch_assoc()['total'];

/*
// Active activities
$result = $conn->query("SELECT COUNT(*) AS total FROM activities WHERE altar_id = $altar_id AND status = 'Active'");
$activities = $result->fetch_assoc()['total'];

// Announcements
$result = $conn->query("SELECT COUNT(*) AS total FROM announcements WHERE altar_id = $altar_id");
$announcements = $result->fetch_assoc()['total']; */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_all'])) {
    $entered_code = trim($_POST['entered_code']);

    // Fetch altar's unique code
    $stmt = $conn->prepare("SELECT unique_code FROM altars WHERE altar_id = ?");
    $stmt->bind_param("i", $altar_id);
    $stmt->execute();
    $stmt->bind_result($db_code);
    $stmt->fetch();
    $stmt->close();

    if ($entered_code === $db_code) {
        // Reset followup statuses
        $stmt = $conn->prepare("UPDATE followup_details SET status = 0 WHERE altar_id = ?");
        $stmt->bind_param("i", $altar_id);
        $stmt->execute();
        $stmt->close();

        // ✅ Store success message in session
        $_SESSION['reset_success'] = "All pending followups have been reset successfully!";

        // ✅ Redirect instead of reload (prevents resubmission)
        header("Location: altarPortal.php");
        exit();
    } else {
        $_SESSION['reset_error'] = "Invalid code. Reset denied!";
        header("Location: altarPortal.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal | Returntoholiness</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        <img src="Images/Altar Logo.png" alt="Altar Logo" width="60">
        <h1><?php echo strtoupper($altar_name); ?></h1>
      </a>
      <div class="contnrHA">
        <a class="rdCll" href="tel:+254777445851"><i class="fa-solid fa-phone-volume"></i> Call&nbsp;the&nbsp;Radio</a>
        <a href="#" class="help-icon">
          <i class="fa-regular fa-circle-question"></i>
          <p class="help-text">Help</p>
        </a>
      </div>
      <i class="fa-solid fa-bars scnnd" onclick="toggleSideBar()"></i>
    </section>
    <section class="container scnnd">
      <ul>
        <a href="altarPortal.php" class="active"><li>Dashboard</li></a>
        <a href="memberAltar.php"><li>Members</li></a>
        <a href="radioPage.php"><li>J.I.L&nbsp;Radio</li></a>
        <li class="drpdwn">
          <a onclick="toggleDropdown()">Followup&nbsp;▼</a>
          <div class="dropdown-content" id="Dropdown">
            <a href="followupAltar.php">Evangelism</a>
            <a href="visitorsAltar.php">Visitors</a>
            <?php if ($altar_type === 'RHSF'): ?>
              <a href="firstYearFollowup.php">First&nbsp;Years</a>
            <?php endif; ?>
            <a href="inactiveMembers.php">INACTIVE&nbsp;MEMBERS</a>
          </div>
        </li>
        <a href=""><li>Make&nbsp;Announcement</li></a>
        <a href=""><li>Activities</li></a>
        <a href=""><li>FAQs</li></a>
      </ul>
    </section>
    <!-- Breadcrumb -->
    <nav class="container">
      <!-- Home -->
      <a href="altarPortal.php" class="text-blue-600 hover:underline">Home</a>

      <!-- Arrow -->
      <svg class="mx-2 h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>

      <!-- Inventory -->
      <a href="altarPortal.php" class="curnt">Dashboard</a>
    </nav>
  </header>

  <?php if (isset($_SESSION['reset_success'])): ?>
    <script>alert('<?php echo $_SESSION['reset_success']; ?>');</script>
    <?php unset($_SESSION['reset_success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['reset_error'])): ?>
    <script>alert('<?php echo $_SESSION['reset_error']; ?>');</script>
    <?php unset($_SESSION['reset_error']); ?>
  <?php endif; ?>

  <div class="overlay" id="overlay" onclick="toggleSideBar()"></div>
  <div class="overlayDropdown" id="overlayDropdown" onclick="toggleDropdown()"></div>
  <div class="overlayDropdown" id="overlayDropdown" onclick="toggleDropdown()"></div>
  <div class="resetPopupOverlay" id="resetPopupOverlay" onclick="toggleResetPopup()"></div>

  <!-- Reset All Popup -->
  <div class="resetPopup" id="resetPopup">
    <h2>Enter Unique Altar Code:</h2>
    <form method="POST" action="">
      <input type="password" name="entered_code" required>
      <div class="popup-actions">
        <button type="submit" name="reset_all">Confirm&nbsp;Reset</button>
        <button type="button" onclick="toggleResetPopup()">Cancel</button>
      </div>
    </form>
  </div>

  <div class="sideBar" id="sidebar">
    <div class="sContainer">
      <img src="Images/Jesus is Lord Radio Logo.avif" alt="Jesus is Lord Radio Logo" width="140">
      <i class="fa-solid fa-xmark" onclick="toggleSideBar()"></i>
    </div>
    <ul>
      <a href="altarPortal.php" class="active"><li>Dashboard</li></a>
      <a href="memberAltar.php"><li>Members</li></a>
      <a href="radioPage.php"><li>J.I.L&nbsp;Radio</li></a>
      <li class="drpdwn">
        <a onclick="toggleDropdownS()">Followup&nbsp;▼</a>
        <div class="dropdown-content" id="DropdownS">
          <a href="followupAltar.php">Evangelism</a>
          <a href="visitorsAltar.php">Visitors</a>
          <?php if ($altar_type === 'RHSF'): ?>
            <a href="firstYearFollowup.php">First&nbsp;Years</a>
          <?php endif; ?>
          <a href="inactiveMembers.php">INACTIVE&nbsp;MEMBERS</a>
        </div>
      </li>
      <a href=""><li>Make&nbsp;Announcement</li></a>
      <a href="" onclick="toggleCodePopup()"><li>Activities</li></a>
      <a href=""><li>FAQs</li></a>
    </ul>
    <a class="rdCll" href="tel:+254777445851"><i class="fa-solid fa-phone-volume"></i> Call&nbsp;the&nbsp;Radio</a>
    <a class="ercr" href="#"><i class="fa-regular fa-circle-question"></i> Help</a>
  </div>
  <main id="mnFrAlp">
    <!-- <div class="devUpdts container">
      <div class="ttlDiv">
        <h2><i class="fa-solid fa-triangle-exclamation"></i> Developer&nbsp;Updates</h2>
        <i class="fa-solid fa-xmark"></i>
      </div>
      <p>We are launching the August Evangelism Drive. Every altar should submit at least 10 new first-year follow-ups by the 20th. Let’s work together for greater impact!</p>
    </div> -->
    <div class="mnCtnr container">
      <div class="crdsStcsDiv">
        <div class="card">
          <h1 class="hd">Active Members</h1>
          <div class="midCd">
            <i class="fa-solid fa-earth-americas"></i>
            <div class="crdDesc">
              <h1><?php echo $activeMembers; ?></h1>
              <p>Consistently present</p>
            </div>
          </div>
          <p class="lstDc">Consistently present</p>
        </div>

        <?php if ($altar_type === 'RHSF'): ?>
          <div class="card fstYrCd">
            <h1 class="hd">First Years</h1>
            <div class="midCd">
              <i class="fa-solid fa-user-graduate"></i>
              <div class="crdDesc">
                <h1><?php echo $firstYears; ?></h1>
                <p>Total registered</p>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <div class="card pdgCd">
          <h1 class="hd">Pending Followups</h1>
          <div class="midCd">
            <i class="fa-solid fa-chart-simple"></i>
            <div class="crdDesc">
              <h1><?php echo $pendingFollowups; ?></h1>
              <p>Let's bring them home</p>
            </div>
          </div>
          <button class="lstDc" onclick="toggleResetPopup()">Reset All</button>

        </div>

        <div class="card ttMCd">
          <h1 class="hd">Total Members</h1>
          <div class="midCd">
            <i class="fa-solid fa-people-group"></i>
            <div class="crdDesc">
              <h1><?php echo $totalMembers; ?></h1>
              <p>Sheep of Christ</p>
            </div>
          </div>
        </div>

        <div class="card intvCd">
          <h1 class="hd">Inactive Members</h1>
          <div class="midCd">
            <i class="fa-solid fa-person-arrow-down-to-line"></i>
            <div class="crdDesc">
              <h1><?php echo $inactiveMembers; ?></h1>
              <p>Let's go for them</p>
            </div>
          </div>
        </div>
        <div class="card acvtsCd">
          <h1 class="hd">Activities</h1>
          <div class="midCd">
            <i class="fa-solid fa-chart-line"></i>
            <div class="crdDesc">
              <h1>2</h1>
              <p>Active session(s)</p>
            </div>
          </div>
          <p class="lstDc">Currently Active</p>
        </div>
        <div class="card anctsCd">
          <h1 class="hd">Announcements</h1>
          <div class="midCd">
            <i class="fab fa-twitter"></i>
            <div class="crdDesc">
              <h1>1</h1>
              <p>What's popping</p>
            </div>
          </div>
          <p class="lstDc">The headlines</p>
        </div>
      </div>
      <div class="lwrStcsDiv">
        <div id="chart-container">
          <canvas id="membersLineChart"></canvas>
        </div><!-- 
        <div class="lwrTbl">
          <table>
            <thead>
              <td>Test</td>
            </thead>
            <tbody>
              <tr></tr>
              <tr></tr>
              <tr></tr>
              <tr></tr>
            </tbody>
          </table>
        </div> -->
      </div>
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

  <script src="Scripts/general.js"></script>
</body>
</html>