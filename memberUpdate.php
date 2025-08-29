<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "connection.php";

// Redirect if altar not logged in
if (!isset($_SESSION['altar_id'])) {
    header("Location: altarLogin.php");
    exit();
}

$altar_id   = $_SESSION['altar_id'];
$altar_name = $_SESSION['altar_name'];
$altar_type = $_SESSION['altar_type'];

$errorMsg   = "";
$successMsg = "";

// --- Initialize variables (so they can be pre-filled later) ---
$fname = $sname = $gender = $phone = $member_status = "";

/**
 * Normalize phone number to a consistent format
 */
function normalizePhone($phone) {
    $cleaned = preg_replace('/[^\d+]/', '', $phone);
    if (strpos($cleaned, '+') === 0) {
        return $cleaned;
    } elseif (strpos($cleaned, '0') === 0) {
        return '+254' . substr($cleaned, 1);
    } elseif (strlen($cleaned) >= 9) {
        return '+' . $cleaned;
    }
    return '';
}

// --- GET the member’s data ---
if (isset($_GET['id'])) {
    $member_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT first_name, second_name, gender, phone, status 
                            FROM members WHERE member_id = ? AND altar_id = ?");
    $stmt->bind_param("ii", $member_id, $altar_id);
    $stmt->execute();
    $stmt->bind_result($fname, $sname, $gender, $encodedPhone, $member_status);
    if ($stmt->fetch()) {
        $fname = ucfirst(strtolower($fname));
        $sname = ucfirst(strtolower($sname));
        $phone = base64_decode($encodedPhone);
    } else {
        $errorMsg = "Member not found!";
    }
    $stmt->close();
}

// --- Handle form submission (Update Logic) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member'])) {
    $member_id     = intval($_POST['member_id']); // keep member_id from hidden input
    $fname         = ucfirst(strtolower(trim($_POST['fname'])));
    $sname         = ucfirst(strtolower(trim($_POST['sname'])));
    $gender        = trim($_POST['gender']);
    $phone         = trim($_POST['phone']);
    $member_status = trim($_POST['member_status']);

    $normalizedPhone = normalizePhone($phone);

    // Validate
    if (empty($fname) || empty($sname) || empty($gender) || empty($phone) || empty($member_status)) {
        $errorMsg = "All fields are required.";
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $normalizedPhone)) {
        $errorMsg = "Please enter a valid phone number.";
    } elseif (!in_array($gender, ['Male', 'Female'])) {
        $errorMsg = "Invalid gender selected.";
    } elseif (!in_array($member_status, ['Active', 'Inactive'])) {
        $errorMsg = "Invalid member status.";
    }

    // If no validation errors, update
    if (empty($errorMsg)) {
        $encodedPhone = base64_encode($normalizedPhone);

        $stmt = $conn->prepare("UPDATE members 
                                SET first_name = ?, second_name = ?, gender = ?, phone = ?, status = ? 
                                WHERE member_id = ? AND altar_id = ?");
        $stmt->bind_param("sssssis", $fname, $sname, $gender, $encodedPhone, $member_status, $member_id, $altar_id);

        if ($stmt->execute()) {
            $successMsg = "Member updated successfully!";
        } else {
            $errorMsg = "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Member | Returntoholiness</title>

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
<body class="nwAltr">
  <div class="mjCntr">
    <div class="fmContnr container">
      <header>
        <img src="Images/Altar Logo.png" alt="Ministry Logo" width="50">
        <p><?php echo strtoupper($altar_name); ?></p>
      </header>
      <div class="fmTp">
        <h1>UPDATE MEMBER</h1>
        <p>Edit details of this member:</p>
      </div>
      <!-- Display error or success message inside the form -->
      <form action="" method="POST">
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member_id ?? ''); ?>">

        <?php if (!empty($errorMsg)) { echo "<p class='errorMsg'><i class='fa-solid fa-triangle-exclamation'></i> $errorMsg</p>"; } ?>
        <?php if (!empty($successMsg)) { echo "<p class='successMsg'><i class='fa-solid fa-circle-check'></i> $successMsg</p>"; } ?>

        <div class="fmInCntnr">
          <div class="inpBox">
            <span>First Name:</span>
            <input name="fname" type="text" value="<?php echo htmlspecialchars($fname ?? ''); ?>" required>
          </div>

          <div class="inpBox">
            <span>Second Name:</span>
            <input name="sname" type="text" value="<?php echo htmlspecialchars($sname ?? ''); ?>" required>
          </div>
          
          <div class="inpBox">
            <span>Gender:</span>
            <select name="gender" required>
              <option value="">-- Select gender --</option>
              <option value="Male" <?php echo ($gender ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo ($gender ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
          </div>

          <div class="inpBox">
            <span>Phone:</span>
            <input name="phone" type="text" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
          </div>

          <div class="inpBox">
            <span>Status:</span>
            <select name="member_status" required>
              <option value="">-- Member status --</option>
              <option value="Active" <?php echo ($member_status ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
              <option value="Inactive" <?php echo ($member_status ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>

          <button type="submit" name="update_member">Update Member</button>

        </div>
      </form>

    </div>
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
          <i class="fab fa-instagram"></i>
        </a>
        <a href="#">
          <i class="fab fa-linkedin-in"></i>
        </a>
        <a href="#">
          <i class="fab fa-youtube"></i>
        </a>
      </div>
      <p>&copy;2025 <a href="">returntoholiness.org,</a> All Rights Reserved</p>
    </div>
  </footer>
</body>
</html>