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

function maskPhone($phone) {
  $len = strlen($phone);
  if ($len <= 6) {
      return $phone; // If too short, just return as is.
  }
  $first3 = substr($phone, 0, 3);
  $last3 = substr($phone, -3);
  $maskLength = $len - 6;
  $mask = str_repeat('*', $maskLength);
  return $first3 . $mask . $last3;
}

// Check if this is an AJAX POST request for updating the status
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if ($action === "delete") {
      $stmt = $conn->prepare("DELETE FROM followup_details WHERE followup_id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->close();

    }
  }
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Precious First Years | Returntoholiness</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="stylesheet" href="Styles/general.css">
  <link rel="stylesheet" href="Styles/styles.css">

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
  
  <!-- jQuery + DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
        <a href="altarPortal.php"><li>Dashboard</li></a>
        <a href="memberAltar.php"><li>Members</li></a>
        <a href="radioPage.php"><li>J.I.L&nbsp;Radio</li></a>
        <li class="drpdwn">
          <a onclick="toggleDropdown()" class="active">Followup&nbsp;▼</a>
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
      <a href="#">Followup</a>
      <!-- Arrow -->
      <svg class="mx-2 h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>

      <!-- Inventory -->
      <a href="firstYearFollowup.php" class="curnt">First Years</a>
    </nav>
  </header>

  <div class="overlay" id="overlay" onclick="toggleSideBar()"></div>
  <div class="overlayDropdown" id="overlayDropdown" onclick="toggleDropdown()"></div>
  <div class="overlayFup" id="overlayFup" onclick="toggleFollowupResponseBar()"></div>
  <form action="" method="post" class="rspnsDiv" id="rspnsDiv">
    <h1>Did you communicate?</h1>
    <div class="spnAns">
      <span>Yes</span>
      <span>No</span>
    </div>
  </form>
  <div class="sideBar" id="sidebar">
    <div class="sContainer">
      <img src="Images/Jesus is Lord Radio Logo.avif" alt="Jesus is Lord Radio Logo" width="140">
      <i class="fa-solid fa-xmark" onclick="toggleSideBar()"></i>
    </div>
    <ul>
      <a href="altarPortal.php"><li>Dashboard</li></a>
      <a href="memberAltar.php"><li>Members</li></a>
      <a href="radioPage.php"><li>J.I.L&nbsp;Radio</li></a>
      <li class="drpdwn">
        <a onclick="toggleDropdownS()" class="active">Followup&nbsp;▼</a>
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
  <main>
    <div class="containerFp container">
      <h1>Precious First Years</h1>
      <a class="addS" href="addFirstyrRhsfAltars.php">+ Add new student</a>
      <div class="tableContainer">
        <!-- Give the table an ID for DataTables -->
        <table id="myTable">
          <thead>
            <th>#</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Action</th>
            <th class="fStUpth">Status</th>
            <th>Evangelist</th>
            <th>M.&nbsp;Point</th>
            <th>Date</th>
          </thead>
          <tbody>
            
            <?php
            $stmt = $conn->prepare("
              SELECT followup_id, first_name, second_name, phone, evangelist_name, meeting_point, mission_type, 
                      DATE(date_evangelized) AS date_evangelized, status 
              FROM followup_details 
              WHERE altar_id = ? AND mission_type = 'FYR'
            ");
            $stmt->bind_param("i", $altar_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $counter = 1;
            if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $phoneDecoded = base64_decode($row['phone']);
              $maskedPhone = maskPhone($phoneDecoded);
                  echo "<tr>
                          <td>{$counter}.</td>
                          <td>{$row['first_name']}&nbsp;{$row['second_name']}</td>
                          <!-- Display the masked phone number, and store the actual number in data-phone -->
                          <td data-phone='{$row['phone']}'>{$maskedPhone}</td>
                          <td class='ffth'>
                            <a href='tel:{$phoneDecoded}' class='call' style='cursor:pointer;'><i class='fa-solid fa-phone-volume'></i>Call</a>
                            <p class='update' style='cursor:pointer;' data-userid='{$row['followup_id']}'>Update</p>
                            <p class='delete' style='cursor:pointer;' data-userid='{$row['followup_id']}'>Delete</p>
                          </td>
                          <td class='fStUp'><i class='fa-solid " . ($row['status'] == '2' ? 'fa-check' : ($row['status'] == '1' ? 'fa-x' : 'fa-minus')) . "'></i></td>
                          <td>{$row['evangelist_name']}</td>
                          <td>{$row['meeting_point']}</td>
                          <td>{$row['date_evangelized']}</td>
                        </tr>";
                        $counter++;
                }
              } else {
                    echo "<tr><td colspan='8'>No records found</td></tr>";
                }
            ?>
          </tbody>
        </table>
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
  <script>
    
    // Handle delete
    document.querySelectorAll(".delete").forEach(btn => {
      btn.addEventListener("click", function() {
        const userId = this.dataset.userid;
        if (confirm("Are you sure you want to DELETE this record?")) {
          fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=delete&id=" + encodeURIComponent(userId)
          }).then(() => location.reload());
        }
      });
    });

    // DataTables Script Js
    $(document).ready(function () {
      $('#myTable').DataTable({
        pagingType: "simple_numbers", // only numbers + prev/next
        pageLength: 15,                // rows per page
        lengthChange: false,          // hide "Show X entries"
        searching: true,              // keep search box
        ordering: true,               // column sorting
        language: {
          paginate: {
            previous: "PREV",
            next: "NEXT"
          }
        }
      });
    });
  </script>
</body>
</html>