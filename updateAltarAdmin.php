<?php 
include 'connection.php';
$success = "";
$error = "";

// ----------------- Helper functions -----------------
function generateUniqueCode($length = 10) {
  $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $charLength = strlen($characters);
  $randomString = '';

  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[random_int(0, $charLength - 1)];
  }
  return $randomString;
}

function getUniqueAltarCode($conn, $length = 10) {
  do {
      $code = generateUniqueCode($length);

      $stmt = $conn->prepare("SELECT altar_id FROM altars WHERE unique_code = ? LIMIT 1");
      $stmt->bind_param("s", $code);
      $stmt->execute();
      $stmt->store_result();
      $exists = $stmt->num_rows > 0;
      $stmt->close();

  } while ($exists);

  return $code;
}

function normalizePhoneNumber($rawPhone) {
  $cleaned = preg_replace('/[^\d+]/', '', $rawPhone);

  if (strpos($cleaned, '+') === 0) {
      return $cleaned;
  } elseif (strpos($cleaned, '0') === 0 && strlen($cleaned) >= 10) {
      return '+254' . substr($cleaned, 1);
  } elseif (strlen($cleaned) >= 9 && !str_starts_with($cleaned, '+')) {
      return '+' . $cleaned;
  }
  return '';
}

// ----------------- Load altar to update -----------------
$altar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($altar_id <= 0) {
  header("Location: returnToHolinessAdminDashboard.php");
  exit();
}

$stmt = $conn->prepare("SELECT * FROM altars WHERE altar_id = ?");
$stmt->bind_param("i", $altar_id);
$stmt->execute();
$result = $stmt->get_result();
$altar = $result->fetch_assoc();
$stmt->close();

if (!$altar) {
  die("Altar not found.");
}

// Pre-fill values for form
$altar_name       = $altar['altar_name'];
$altar_type       = $altar['altar_type'];
$snr_pst_fullname = $altar['snr_pst_fullname'];
$snr_pst_title    = $altar['snr_pst_title'];
$altar_status     = $altar['altar_status'];
$phone_1          = base64_decode($altar['phone_1']);
$phone_2          = base64_decode($altar['phone_2']);
$email            = $altar['email'] ? base64_decode($altar['email']) : '';
$county = $altar['county'];

// ----------------- Handle update submission -----------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $altar_name       = trim($_POST['altar_name']);
    $altar_type       = trim($_POST['altar_type']);
    $snr_pst_fullname = trim($_POST['snr_pst_fullname']);
    $snr_pst_title    = trim($_POST['snr_pst_title']);
    $altar_status     = trim($_POST['altar_status']);
    $phone_1          = trim($_POST['phone_1']);
    $phone_2          = trim($_POST['phone_2']);
    $email            = trim($_POST['email']);
    $county           = trim($_POST['county']);

    // ----------------- Validation -----------------
    if (empty($altar_name) || strlen($altar_name) < 3 || strlen($altar_name) > 100) {
        $error = "Altar name must be between 3 and 100 characters!";
    } elseif (empty($snr_pst_fullname) || !preg_match("/^[a-zA-Z\s.]+$/", $snr_pst_fullname) || str_word_count($snr_pst_fullname) < 2) {
        $error = "Senior Pastor fullname must be at least 2 and only letters, spaces, or periods!";
    } elseif (empty($phone_1) || !preg_match("/^07\d{8}$/", $phone_1)) {
        $error = "Please enter a valid phone number!";
    } elseif (!empty($phone_2) && $phone_1 === $phone_2) {
        $error = "Phone 2 cannot be the same as Phone 1.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    }

    if (empty($error)) {
        // Normalize + encode
        $normalized_phone1 = normalizePhoneNumber($phone_1);
        $normalized_phone2 = normalizePhoneNumber($phone_2);
        $encrypted_phone1  = base64_encode($normalized_phone1);
        $encrypted_phone2  = base64_encode($normalized_phone2);
        $encrypted_email   = !empty($email) ? base64_encode($email) : null;

        // Generate a new unique verification code
        $unique_code = getUniqueAltarCode($conn, 12);

        // ----------------- Update -----------------
        $stmt = $conn->prepare("UPDATE altars SET 
          altar_name=?, altar_type=?, snr_pst_fullname=?, snr_pst_title=?, altar_status=?, 
          phone_1=?, phone_2=?, email=?, county=?, unique_code=? 
          WHERE altar_id=?");

        $stmt->bind_param(
          "ssssssssssi",
          $altar_name,
          $altar_type,
          $snr_pst_fullname,
          $snr_pst_title,
          $altar_status,
          $encrypted_phone1,
          $encrypted_phone2,
          $encrypted_email,
          $county,
          $unique_code,
          $altar_id
        );

        if ($stmt->execute()) {
          $success = "Altar updated successfully!";
        } else {
          $error = "Database Error: " . $stmt->error;
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
  <title>Update Altar Admin | Returntoholiness</title>

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
      <div class="fmTp">
        <h1>UPDATE ALTAR</h1>
        <p>Modify the altar details below:</p>
      </div>
      <form action="" method="POST">
        <?php if ($error) { echo "<p class='errorMsg'><i class='fa-solid fa-triangle-exclamation'></i>$error</p>"; } ?>
        <?php if ($success) { echo "<p class='successMsg'><i class='fa-solid fa-circle-check'></i>$success</p>"; } ?>
        <div class="fmInCntnr">
          <div class="inpBox">
            <span>Altar Name</span>
            <input type="text" name="altar_name" value="<?php echo htmlspecialchars($altar_name ?? ''); ?>" required>
          </div>
          <div class="inpBox">
            <span>Type of Altar</span>
            <select name="altar_type" required>
              <option value="">-- Choose altar type --</option>
              <option value="General" <?php echo ($altar_type ?? '') === 'General' ? 'selected' : ''; ?>>General</option>
              <option value="RHSF" <?php echo ($altar_type ?? '') === 'RHSF' ? 'selected' : ''; ?>>RHSF</option>
            </select>
          </div>
          <div class="inpBox">
            <span>Senior Pastor Full Name</span>
            <input type="text" name="snr_pst_fullname" value="<?php echo htmlspecialchars($snr_pst_fullname ?? ''); ?>" required>
          </div>
          <div class="inpBox">
            <span>Senior Pastor Title</span>
            <select name="snr_pst_title" required>
              <option value="">-- Select title --</option>
              <option value="PASTOR" <?php echo ($snr_pst_title ?? '') === 'PASTOR' ? 'selected' : ''; ?>>PASTOR</option>
              <option value="OVERSEER" <?php echo ($snr_pst_title ?? '') === 'OVERSEER' ? 'selected' : ''; ?>>OVERSEER</option>
              <option value="ASSISTANT SENIOR PASTOR" <?php echo ($snr_pst_title ?? '') === 'ASSISTANT SENIOR PASTOR' ? 'selected' : ''; ?>>ASSISTANT SENIOR PASTOR</option>
              <option value="BISHOP" <?php echo ($snr_pst_title ?? '') === 'BISHOP' ? 'selected' : ''; ?>>BISHOP</option>
              <option value="DEPUTY ARCH BISHOP" <?php echo ($snr_pst_title ?? '') === 'DEPUTY ARCH BISHOP' ? 'selected' : ''; ?>>DEPUTY ARCH BISHOP</option>
              <option value="SENIOR DEPUTY ARCH BISHOP" <?php echo ($snr_pst_title ?? '') === 'SENIOR DEPUTY ARCH BISHOP' ? 'selected' : ''; ?>>SENIOR DEPUTY ARCH BISHOP</option>
              <option value="EMERITUS" <?php echo ($snr_pst_title ?? '') === 'EMERITUS' ? 'selected' : ''; ?>>EMERITUS</option>
            </select>
          </div>
          <div class="inpBox">
            <span>Altar Status</span>
            <select name="altar_status" value="<?php echo htmlspecialchars($altar_status ?? ''); ?>" required>
              <option value="">-- Current altar status --</option>
              <option value="Active" <?php echo ($altar_status ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
              <option value="Inactive" <?php echo ($altar_status ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>
          <div class="inpCntnr">
            <div class="inpBox">
              <span>Contact Phone</span>
              <input type="text" name="phone_1" value="<?php echo htmlspecialchars($phone_1 ?? ''); ?>" required>
            </div>
            <div class="inpBox">
              <span>Other Phone</span>
              <input type="text" name="phone_2" value="<?php echo htmlspecialchars($phone_2 ?? ''); ?>" required>
            </div>
          </div>
          <div class="inpBox">
            <span>Contact Email</span>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
          </div>
          <div class="inpBox">
            <span>County Located</span>
            <select id="county" name="county" class="form-select" required>
              <option value="" selected disabled>-- Select County --</option>
              <option value="Baringo" <?php echo ($county ?? '') === 'Baringo' ? 'selected' : ''; ?>>Baringo</option>
              <option value="Bomet" <?php echo ($county ?? '') === 'Bomet' ? 'selected' : ''; ?>>Bomet</option>
              <option value="Busia" <?php echo ($county ?? '') === 'Busia' ? 'selected' : ''; ?>>Busia</option>
              <option value="Bungoma" <?php echo ($county ?? '') === 'Bungoma' ? 'selected' : ''; ?>>Bungoma</option>
              <option value="Kericho" <?php echo ($county ?? '') === 'Kericho' ? 'selected' : ''; ?>>Kericho</option>
              <option value="Kiambu" <?php echo ($county ?? '') === 'Kiambu' ? 'selected' : ''; ?>>Kiambu</option>
              <option value="Kisii" <?php echo ($county ?? '') === 'Kisii' ? 'selected' : ''; ?>>Kisii</option>
              <option value="Kisumu" <?php echo ($county ?? '') === 'Kisumu' ? 'selected' : ''; ?>>Kisumu</option>
              <option value="Kilifi" <?php echo ($county ?? '') === 'Kilifi' ? 'selected' : ''; ?>>Kilifi</option>
              <option value="Kitui" <?php echo ($county ?? '') === 'Kitui' ? 'selected' : ''; ?>>Kitui</option>
              <option value="Kwale" <?php echo ($county ?? '') === 'Kwale' ? 'selected' : ''; ?>>Kwale</option>
              <option value="Lamu" <?php echo ($county ?? '') === 'Lamu' ? 'selected' : ''; ?>>Lamu</option>
              <option value="Laikipia" <?php echo ($county ?? '') === 'Laikipia' ? 'selected' : ''; ?>>Laikipia</option>
              <option value="Machakos" <?php echo ($county ?? '') === 'Machakos' ? 'selected' : ''; ?>>Machakos</option>
              <option value="Makueni" <?php echo ($county ?? '') === 'Makueni' ? 'selected' : ''; ?>>Makueni</option>
              <option value="Mandera" <?php echo ($county ?? '') === 'Mandera' ? 'selected' : ''; ?>>Mandera</option>
              <option value="Marsabit" <?php echo ($county ?? '') === 'Marsabit' ? 'selected' : ''; ?>>Marsabit</option>
              <option value="Meru" <?php echo ($county ?? '') === 'Meru' ? 'selected' : ''; ?>>Meru</option>
              <option value="Migori" <?php echo ($county ?? '') === 'Migori' ? 'selected' : ''; ?>>Migori</option>
              <option value="Mombasa" <?php echo ($county ?? '') === 'Mombasa' ? 'selected' : ''; ?>>Mombasa</option>
              <option value="Murang'a" <?php echo ($county ?? '') === "Murang'a" ? 'selected' : ''; ?>>Murang'a</option>
              <option value="Nyandarua" <?php echo ($county ?? '') === 'Nyandarua' ? 'selected' : ''; ?>>Nyandarua</option>
              <option value="Nyamira" <?php echo ($county ?? '') === 'Nyamira' ? 'selected' : ''; ?>>Nyamira</option>
              <option value="Nyeri" <?php echo ($county ?? '') === 'Nyeri' ? 'selected' : ''; ?>>Nyeri</option>
              <option value="Nakuru" <?php echo ($county ?? '') === 'Nakuru' ? 'selected' : ''; ?>>Nakuru</option>
              <option value="Narok" <?php echo ($county ?? '') === 'Narok' ? 'selected' : ''; ?>>Narok</option>
              <option value="Nairobi" <?php echo ($county ?? '') === 'Nairobi' ? 'selected' : ''; ?>>Nairobi</option>
              <option value="Nandi" <?php echo ($county ?? '') === 'Nandi' ? 'selected' : ''; ?>>Nandi</option>
              <option value="Homa Bay" <?php echo ($county ?? '') === 'Homa Bay' ? 'selected' : ''; ?>>Homa Bay</option>
              <option value="Isiolo" <?php echo ($county ?? '') === 'Isiolo' ? 'selected' : ''; ?>>Isiolo</option>
              <option value="Kajiado" <?php echo ($county ?? '') === 'Kajiado' ? 'selected' : ''; ?>>Kajiado</option>
              <option value="Kakamega" <?php echo ($county ?? '') === 'Kakamega' ? 'selected' : ''; ?>>Kakamega</option>
              <option value="Vihiga" <?php echo ($county ?? '') === 'Vihiga' ? 'selected' : ''; ?>>Vihiga</option>
              <option value="Siaya" <?php echo ($county ?? '') === 'Siaya' ? 'selected' : ''; ?>>Siaya</option>
              <option value="Samburu" <?php echo ($county ?? '') === 'Samburu' ? 'selected' : ''; ?>>Samburu</option>
              <option value="Embu" <?php echo ($county ?? '') === 'Embu' ? 'selected' : ''; ?>>Embu</option>
              <option value="Elgeyo-Marakwet" <?php echo ($county ?? '') === 'Elgeyo-Marakwet' ? 'selected' : ''; ?>>Elgeyo-Marakwet</option>
              <option value="Uasin Gishu" <?php echo ($county ?? '') === 'Uasin Gishu' ? 'selected' : ''; ?>>Uasin Gishu</option>
              <option value="Trans Nzoia" <?php echo ($county ?? '') === 'Trans Nzoia' ? 'selected' : ''; ?>>Trans Nzoia</option>
              <option value="Turkana" <?php echo ($county ?? '') === 'Turkana' ? 'selected' : ''; ?>>Turkana</option>
              <option value="Tana River" <?php echo ($county ?? '') === 'Tana River' ? 'selected' : ''; ?>>Tana River</option>
              <option value="Taita-Taveta" <?php echo ($county ?? '') === 'Taita-Taveta' ? 'selected' : ''; ?>>Taita-Taveta</option>
              <option value="Tharaka-Nithi" <?php echo ($county ?? '') === 'Tharaka-Nithi' ? 'selected' : ''; ?>>Tharaka-Nithi</option>
              <option value="Garissa" <?php echo ($county ?? '') === 'Garissa' ? 'selected' : ''; ?>>Garissa</option>
              <option value="Wajir" <?php echo ($county ?? '') === 'Wajir' ? 'selected' : ''; ?>>Wajir</option>
            </select>
          </div>
        </div>
        <button type="submit">Update Altar</button>
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