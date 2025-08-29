<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

function decodePhone($phone) {
    // Example: if phones are base64 encoded in DB
    return base64_decode($phone);
}

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

// --- Handle AJAX requests ---
// --- Handle AJAX requests ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if ($action === "delete") {
      $stmt = $conn->prepare("DELETE FROM members WHERE member_id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->close();

    } elseif ($action === "inactive") {
      $stmt = $conn->prepare("UPDATE members SET status = 'Inactive' WHERE member_id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->close();

    } elseif ($action === "active") {
      $stmt = $conn->prepare("UPDATE members SET status = 'Active' WHERE member_id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->close();

    } elseif ($action === "update" && isset($_POST['status'])) {
      // status is a text column ('Active' / 'Inactive'), so bind as string
      $status = $_POST['status'];
      $stmt = $conn->prepare("UPDATE members SET status = ? WHERE member_id = ?");
      $stmt->bind_param("si", $status, $id);
      $stmt->execute();
      $stmt->close();
    }

    exit; // important for AJAX
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Members | Returntoholiness</title>
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
        <a href="memberAltar.php" class="active"><li>Members</li></a>
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
      <a href="memberAltar.php" class="curnt">Members</a>
    </nav>
  </header>

  <div class="overlay" id="overlay" onclick="toggleSideBar()"></div>
  <div class="overlayDropdown" id="overlayDropdown" onclick="toggleDropdown()"></div>
  <div class="sideBar" id="sidebar">
    <div class="sContainer">
      <img src="Images/Jesus is Lord Radio Logo.avif" alt="Jesus is Lord Radio Logo" width="140">
      <i class="fa-solid fa-xmark" onclick="toggleSideBar()"></i>
    </div>
    <ul>
      <a href="altarPortal.php"><li>Dashboard</li></a>
      <a href="memberAltar.php" class="active"><li>Members</li></a>
      <a href="radioPage.php"><li>J.I.L&nbsp;Radio</li></a>
      <li class="drpdwn">
        <a onclick="toggleDropdownS()">Followup&nbsp;▼</a>
        <div class="dropdown-content" id="DropdownS">
          <a href="followupAltar.php">Evangelism</a>
          <a href="visitorsAltar.php">Visitors</a>
          <?php if ($altar_type === 'RHSF'): ?>
            <a href="firstYearFollowup.php">First&nbsp;Years</a>
          <?php endif; ?>
        </div>
      </li>
      <a href=""><li>Make&nbsp;Announcement</li></a>
      <a href="" onclick="toggleCodePopup()"><li>Activities</li></a>
      <a href=""><li>FAQs</li></a>
    </ul>
    <a class="rdCll" href="tel:+254777445851"><i class="fa-solid fa-phone-volume"></i> Call&nbsp;the&nbsp;Radio</a>
    <a class="ercr" href="#"><i class="fa-regular fa-circle-question"></i> Help</a>
  </div>
  <main><!-- 
    <a href="index.php" id="overlay" class="overlay"></a>
    <div id="popUp">
      <h4>Did you do the follow up? If you have already called the servant and the follow up done successfully, select <span>Successful</span>. Else if there is no response or you did not communicate select <span>No answer</span></h4>
      <div class="response">
        <p class="noResponce">No&nbsp;answer</p>
        <p>Successful</p>
      </div>
    </div> -->
    <div class="containerFp container">
      <h1>Members list</h1>
      <a class="addS" href="addMember.php">+ Add new member</a>
      <div class="tableContainer">
        <!-- Give the table an ID for DataTables -->
        <table id="myTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Gender</th>
              <th>Action</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Move</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $stmt = $conn->prepare("SELECT member_id, first_name, second_name, gender, phone, status, date_registered 
                        FROM members 
                        WHERE altar_id = ? AND status = 'Active'");
            $stmt->bind_param("i", $altar_id);
            $stmt->execute();

            $result = $stmt->get_result();  // ✅ mysqli_result
            $counter = 1;

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  // Decode phone number first
                  $decodedPhone = decodePhone($row['phone']);
                  // Now mask decoded number
                  $maskedPhone = maskPhone($decodedPhone);

                  // Capitalize first letters of names
                  $firstName = ucfirst(strtolower($row['first_name']));
                  $secondName = ucfirst(strtolower($row['second_name']));
                  echo "<tr>
                          <td>{$counter}.</td>
                          <td>{$firstName}&nbsp;{$secondName}</td>
                          <!-- Display the masked phone number, and store the actual number in data-phone -->
                          <td>{$row['gender']}</td>
                          <td class='ffth'>
                            <a class='call' href='tel:{$decodedPhone}'><i class='fa-solid fa-phone-volume'></i> Call</a>
                            <p class='update' style='cursor:pointer;' data-userid='{$row['member_id']}'>Update</p>
                            <p class='delete' style='cursor:pointer;' data-userid='{$row['member_id']}'>Delete</p>
                          </td>
                          <td data-phone='{$decodedPhone}'>{$maskedPhone}</td>
                          <td class='ffth svth'><p class='actvIllsttr'>{$row['status']}</p></td>
                          <td><p class='mMve' data-userid='{$row['member_id']}'><i class='fa-solid fa-folder-closed'></i>Move&nbsp;to&nbsp;inactive</p></td>
                          <td>{$row['date_registered']}</td>
                        </tr>";
                        $counter++;
                }
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

  <script src="Scripts/general.js"></script><script>
  /*  // Handle update status
    document.querySelectorAll(".update").forEach(btn => {
      btn.addEventListener("click", function() {
        const userId = this.dataset.userid;
        const newStatus = prompt("Enter new status (1 = Not Reached, 2 = Reached):");
        if (newStatus) {
          fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=update&id=" + userId + "&status=" + newStatus
          }).then(() => location.reload());
        }
      });
    }); */

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

    // Handle move to inactive
    document.querySelectorAll(".mMve").forEach(btn => {
      btn.addEventListener("click", function() {
        const userId = this.dataset.userid;
        // Normalize the text content
        const txt = this.textContent.replace(/\u00A0/g, ' ').toLowerCase();
        // IMPORTANT: check 'inactive' first because it contains 'active'
        const toInactive = txt.includes("move to inactive");
        const action = toInactive ? "inactive" : "active";
        const label = toInactive ? "INACTIVE" : "ACTIVE";

        if (confirm("Move this member to " + label + "?")) {
          fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=" + encodeURIComponent(action) + "&id=" + encodeURIComponent(userId)
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
    // Handle update -> redirect to memberUpdate.php
    document.querySelectorAll(".update").forEach(btn => {
      btn.addEventListener("click", function() {
        const userId = this.dataset.userid;
        window.location.href = "memberUpdate.php?id=" + encodeURIComponent(userId);
      });
    });

  </script>
</body>
</html>