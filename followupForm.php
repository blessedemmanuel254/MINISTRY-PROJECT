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

$errorMsg = "";
$successMsg = "";
/**
 * Normalize phone number to a consistent format
 */
function normalizePhone($phone) {
    // Keep only digits and plus
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve and trim form data
  $fname = trim($_POST['fname']);
  $sname = trim($_POST['sname']);
  $phoneNumber = trim($_POST['phoneNumber']);
  $gender = trim($_POST['gender']);
  $evangelist = trim($_POST['evangelist']);
  $venue = trim($_POST['venue']);
  $missionType = trim($_POST['missionType']);

  // Normalize phone
  $phoneNumber = normalizePhone($phoneNumber);

  // --- Validate inputs ---
  if (empty($fname) || empty($phoneNumber) || empty($gender) || empty($evangelist) || empty($venue) || empty($missionType)) {
    $errorMsg = "All fields are required!";
  } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phoneNumber)) {
    $errorMsg = "Please enter a valid phone number!";
  } elseif (strpos($evangelist, ' ') !== false) {
    $errorMsg = "Please enter only one name for the evangelist!";
  } elseif (strpos($venue, ' ') !== false) {
    $errorMsg = "Please enter only one name for the meeting point!";
  }

  // --- Check if phone already exists ---
  if (empty($errorMsg)) {
    $encodedPhone = base64_encode($phoneNumber);

    $checkStmt = $conn->prepare("SELECT followup_id FROM followup_details WHERE phone = ?");
    $checkStmt->bind_param("s", $encodedPhone);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
      $errorMsg = "Phone number already exists in follow-up records!";
    }
    $checkStmt->close();
  }

  // --- If no errors, insert into DB ---
  if (empty($errorMsg)) {
    $fname = ucfirst(strtolower($fname));
    $sname = ucfirst(strtolower($sname));
    $evangelist = strtoupper($evangelist);
    $venue = ucfirst(strtolower($venue));

    $stmt = $conn->prepare("INSERT INTO followup_details 
      (first_name, second_name, phone, gender, evangelist_name, meeting_point, mission_type, altar_id) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssi", $fname, $sname, $encodedPhone, $gender, $evangelist, $venue, $missionType, $altar_id);

    if ($stmt->execute()) {
      $successMsg = ' Servant`s details updated successfully !<br>
      <span>1 Corinthians 15:58</span> 
      <span>"Therefore, my dear brothers and sisters, stand firm. Let nothing move you. 
      Always give yourselves fully to the work of the Lord, because you know that 
      YOUR LABOR IN THE LORD IS NOT IN VAIN.</span>"';

      // reset inputs
      $fname = $phoneNumber = $gender = $evangelist = $venue = $missionType = '';
    } else {
      $errorMsg = "Error inserting record: " . $stmt->error;
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
  <title>Follow up form | Returntoholiness</title>

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
      <header class="newServantHeader">
        <img src="Images/Altar Logo.png" alt="Ministry Logo" width="60">
        <p><?php echo strtoupper($altar_name); ?></p>
      </header>
      <div class="fmTp">
        <h1>ADD NEW SERVANT</h1>
        <p>Input servant's details in the spaces below:</p>
      </div>
      <!-- Display error or success message inside the form -->
      <form action="" method="POST">
        <?php if ($errorMsg) { echo "<p class='errorMsg'><i class='fa-solid fa-triangle-exclamation'></i>$errorMsg</p>"; } ?>
        <?php if ($successMsg) { echo "<p class='successMsg flwUp'><i class='fa-solid fa-circle-check'></i>$successMsg</p>"; } ?>
        <div class="fmInCntnr">
          <div class="inpBox">
            <span>First Name:</span>
            <input name="fname" type="text" value="<?php echo htmlspecialchars($fname ?? ''); ?>" required>

          </div>
          <div class="inpBox">
            <span>Second Name (optional):</span>
            <input name="sname" type="text" value="<?php echo htmlspecialchars($sname ?? ''); ?>">

          </div>
          <div class="inpBox">
            <span>Phone:</span>
            <input name="phoneNumber" type="text" value="<?php echo htmlspecialchars($phoneNumber ?? ''); ?>" required>
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
            <span>Evangelist Name:</span>
            <input name="evangelist" type="text" value="<?php echo htmlspecialchars($evangelist ?? ''); ?>" required>
          </div>
          <div class="inpBox">
            <span>Meeting Point:</span>
            <input name="venue" type="text" value="<?php echo htmlspecialchars($venue ?? ''); ?>" required>
          </div>
          <div class="inpBox">
            <span>Mission Type:</span>
            <select id="missionType" name="missionType" required>
              <option value="">--Select mission/category--</option>
              <option value="Hospital Mission" <?php echo ($missionType ?? '') === 'Hospital Mission' ? 'selected' : ''; ?>>Hospital Mission</option>
              <option value="Earlybird (Outreach)" <?php echo ($missionType ?? '') === 'Earlybird (Outreach)' ? 'selected' : ''; ?>>Earlybird (Outreach)</option>
              <option value="Earlybird (In-school)" <?php echo ($missionType ?? '') === 'Earlybird (In-school)' ? 'selected' : ''; ?>>Earlybird (In-school)</option>
              <option value="Hostel Evangelism" <?php echo ($missionType ?? '') === 'Hostel Evangelism' ? 'selected' : ''; ?>>Hostel Evangelism</option>
              <option value="Lunch Hour Outreach" <?php echo ($missionType ?? '') === 'Lunch Hour Outreach' ? 'selected' : ''; ?>>Lunch Hour Outreach</option>
              <option value="Outreach" <?php echo ($missionType ?? '') === 'Outreach' ? 'selected' : ''; ?>>Outreach</option>
              <option value="Evangelism" <?php echo ($gender ?? '') === 'Evangelism' ? 'selected' : ''; ?>>Evangelism</option>
            </select>
          </div>

          <button type="submit">SUBMIT</button>
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