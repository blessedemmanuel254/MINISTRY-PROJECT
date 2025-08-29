<?php
include 'connection.php';

// ✅ AJAX: Verify altar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $altar_id = intval($_POST['altar_id']);
  
  if ($_POST['action'] === 'verify') {
    $status = "Verified";  
  } elseif ($_POST['action'] === 'disprove') {
    $status = "Pending";  
  }

  $stmt = $conn->prepare("UPDATE altars SET verification_status = ? WHERE altar_id = ?");
  $stmt->bind_param("si", $status, $altar_id);

  if ($stmt->execute()) {
    echo "success";
  } else {
    echo "error";
  }
  $stmt->close();
  exit; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Altars Records | Returntoholiness Admin</title>

  <link rel="stylesheet" href="Styles/admin.css">

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
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="adContnr">
    <div class="sidebar" id="sidebar">
      <div class="hder">
        <img src="Images/Ministry Logo.avif" alt="Ministry Logo" width="35">
        <i class="fa-solid fa-circle-user"></i>
      </div>
      <section>
        <img src="Images/Admin Profile.jpg" alt="" width="100">
        <h2>Emmanuel&nbsp;Werangai</h2>
        <p>emmanueltindi23@gmail.com</p>
      </section>
      <ul>
        <a href="returnToHolinessAdminDashboard.php"><i class="fa-solid fa-house"></i>Dashboard</a>
        <a href="altarsRecordsAdmin.php" class="active"><i class="fa-solid fa-place-of-worship"></i>Altars</a>
        <a href="adminJILRadio.php"><i class="fa-solid fa-place-of-worship"></i>J.I.L&nbsp;Radio</a>
        <a href=""><i class="fa-solid fa-chart-line"></i>Activities</a>
        <a href=""><i class="fa-solid fa-pen"></i>Updates</a>
        <a href=""><i class="fa-solid fa-ticket"></i>Tickets</a>
        <a href=""><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
      </ul>
    </div>
    <div class="cntnrDash">
      <header>
        <section class="container">
          <div>
            <i class="fa-solid fa-bars" id="toggleBtn"></i>
            <h1>ADMIN</h1>
          </div>
          <div>
            <i class="fa-regular fa-bell"></i>
            <i class="fa-regular fa-comment-dots"></i>
          </div>
        </section>
      </header>
      <main>
        <div class="wlcmTp container">
          <h1>Altars Records<br><span>Verification and update of the Lord's altars</span></h1>
          <a href="#"><i class="fa-solid fa-envelope"></i>Email&nbsp;the&nbsp;Radio</a>
        </div>
        <div class="tcontnr container">
          <table id="myTable">
            <thead>
              <th>#</th>
              <th>Altar&nbsp;Name</th>
              <th>Snr&nbsp;Pastor&nbsp;Name</th>
              <th>County</th>
              <th>Action</th>
              <th>Verification&nbsp;Status</th>
              <th>Date</th>
            </thead>
            <tbody>
              <?php
                $stmt = $conn->prepare("SELECT altar_id, altar_name, snr_pst_fullname, county, unique_code, verification_status, created_at, phone_1, phone_2 
                        FROM altars ORDER BY created_at DESC");
                $stmt->execute();
                $result = $stmt->get_result();

                $counter = 1;
                while ($row = $result->fetch_assoc()):
                  $phone1 = !empty($row['phone_1']) ? base64_decode($row['phone_1']) : null;
                  $phone2 = !empty($row['phone_2']) ? base64_decode($row['phone_2']) : null;
              ?>
              <tr>
                <td><?= $counter++ ?>.</td>
                <td>
                  <p class="dfTd">
                    <img src="Images/Altar Logo.png" alt="Altar Logo" width="40">
                    <?= strtoupper(htmlspecialchars($row['altar_name'])) ?>
                  </p>
                </td>
                <td>
                  <p class="dfTd">
                    <i class="fa-solid fa-user"></i>
                    <?php
                      // Proper case for pastor name
                      $pastorName = ucwords(strtolower($row['snr_pst_fullname']));
                      echo htmlspecialchars($pastorName);
                    ?>
                  </p>
                </td>
                <td><div class="dfTd"><?= htmlspecialchars($row['county']) ?></div></td>
                <td>
                  <div  class="dfTd">
                    <a href="tel:<?= htmlspecialchars($phone1) ?>" 
                      onclick="return tryCall('<?= $phone1 ?>','<?= $phone2 ?>');">
                      <i class="fa-solid fa-phone-volume"></i>Call&nbsp;Snr&nbsp;Pastor
                    </a>
                    <p class="<?= $row['verification_status'] === 'verified' ? 'disprove' : 'verify' ?>" data-id="<?= $row['altar_id'] ?>">
                      <?= $row['verification_status'] === 'verified' ? 'Disprove' : 'Verify' ?>
                    </p>

                    <a href="updateAltarAdmin.php?id=<?= $row['altar_id'] ?>" class="update">
                      <i class="fa-solid fa-pen"></i> Update
                    </a>
                    <p class="delete" data-id="<?= $row['altar_id'] ?>">Delete<i class="fa-solid fa-trash-can"></i></p>
                    <p class="copy-code" data-code="<?= $row['unique_code'] ?>">Copy&nbsp;Unq.&nbsp;CODE<i class="fa-solid fa-link"></i></p>
                  </div>
                </td>
                <td>
                  <div class="vrfdTd">
                    <?php if ($row['verification_status'] === 'verified'): ?>
                      <p class="vrfd">Verified</p>
                    <?php else: ?>
                      <p class="not-vrfd">Pending</p>
                    <?php endif; ?>
                  </div>
                </td>
                <td><?= date("Y-m-d", strtotime($row['created_at'])) ?></td>
              </tr>
            <?php endwhile; $stmt->close(); ?>
          </table>
        </div>

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
      </main>
    </div>
  </div>
  <script src="Scripts/general.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Verify / Disprove toggle
      document.querySelectorAll(".verify, .disprove").forEach(btn => {
        btn.addEventListener("click", function() {
          const altarId = this.dataset.id;
          const action = this.classList.contains("verify") ? "verify" : "disprove";

          if (!confirm(`Are you sure you want to ${action} this altar?`)) return;

          fetch(window.location.href, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=" + action + "&altar_id=" + altarId
          })
          .then(res => res.text())
          .then(data => {
            if (data.trim() === "success") {
              location.reload(); // 🔄 Refresh page to reflect changes
            } else {
              alert("❌ Action failed: " + data);
            }
          })
          .catch(err => {
            alert("Error: " + err);
          });
        });
      });

      // Copy unique code
      document.querySelectorAll(".copy-code").forEach(btn => {
        btn.addEventListener("click", function() {
          const code = this.dataset.code;
          navigator.clipboard.writeText(code).then(() => {
            alert("Unique Code copied to clipboard!");
          });
        });
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