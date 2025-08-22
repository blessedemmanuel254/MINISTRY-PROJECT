<?php 
include 'connection.php';
$success = "";
$error = "";

// Encryption settings for email
define('ENCRYPTION_KEY', 'your-32-character-secret-key-here'); // store securely (env var, not code)
define('ENCRYPTION_METHOD', 'AES-256-CBC');

function encryptEmail($email) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($email, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . '::' . $encrypted);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect inputs
    $altar_name       = trim($_POST['altar_name']);
    $altar_type       = trim($_POST['altar_type']);
    $snr_pst_fullname = trim($_POST['snr_pst_fullname']);
    $snr_pst_title    = trim($_POST['snr_pst_title']);
    $altar_status     = trim($_POST['altar_status']);
    $phone_1          = trim($_POST['phone_1']);
    $phone_2          = trim($_POST['phone_2']);
    $email            = trim($_POST['email']);
    $county           = trim($_POST['county']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation (same as yours)...

    if (empty($altar_name) || strlen($altar_name) < 3 || strlen($altar_name) > 100) {
        $error = "Altar name must be between 3 and 100 characters!";
    } elseif (empty($snr_pst_fullname) || !preg_match("/^[a-zA-Z\s.]+$/", $snr_pst_fullname) || str_word_count($snr_pst_fullname) < 2) {
        $error = "Senior Pastor fullname must be at least 2 and only letters, spaces, or periods!";
    } elseif (strlen($snr_pst_title) < 5) {
        $error = "Senior Pastor title must be at least 5 characters long!";
    } elseif (empty($phone_1) || !preg_match("/^07\d{8}$/", $phone_1)) {
        $error = "Please enter a valid phone number!";
    } elseif (!empty($phone_2) && $phone_1 === $phone_2) {
        $error = "Phone 2 cannot be the same as Phone 1.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // --- Check uniqueness for altar_name ---
        $stmt = $conn->prepare("SELECT altar_id FROM altars WHERE altar_name = ? LIMIT 1");
        $stmt->bind_param("s", $altar_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "An altar with this name already exists!";
        }
        $stmt->close();

        // --- Check uniqueness for email (if provided) ---
        if (empty($error) && !empty($email)) {
            $encEmail = encryptEmail($email);

            $stmt = $conn->prepare("SELECT altar_id FROM altars WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $encEmail);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "This email is already registered with another altar!";
            }
            $stmt->close();
        }
    }

    // Insert if no error
    if (empty($error)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $encEmail = !empty($email) ? encryptEmail($email) : null;

        $stmt = $conn->prepare("INSERT INTO altars 
            (altar_name, altar_type, snr_pst_fullname, snr_pst_title, altar_status, phone_1, phone_2, email, county, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "ssssssssss",
            $altar_name,
            $altar_type,
            $snr_pst_fullname,
            $snr_pst_title,
            $altar_status,
            $phone_1,
            $phone_2,
            $encEmail,
            $county,
            $hashedPassword
        );

        if ($stmt->execute()) {
            $success = "Amen please, your altar has been registered successfully! Wait for verification email to login. We may contact you for more information. #PROTECTINGTHEGLORY";
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
  <title>Register Altar | Returntoholiness</title>

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
        <h1>ADD NEW ALTAR</h1>
        <p>Fill in the details below to register your altar or fellowship:</p>
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
            <select name="altar_type" value="<?php echo htmlspecialchars($altar_type ?? ''); ?>" required>
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
            <input type="text" name="snr_pst_title" placeholder="e.g Deputy Arch Bishop" value="<?php echo htmlspecialchars($snr_pst_title ?? ''); ?>" required>
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
              <option value="030" <?php echo ($county ?? '') === '030' ? 'selected' : ''; ?>>Baringo</option>
              <option value="036" <?php echo ($county ?? '') === '036' ? 'selected' : ''; ?>>Bomet</option>
              <option value="040" <?php echo ($county ?? '') === '040' ? 'selected' : ''; ?>>Busia</option>
              <option value="039" <?php echo ($county ?? '') === '039' ? 'selected' : ''; ?>>Bungoma</option>
              <option value="035" <?php echo ($county ?? '') === '035' ? 'selected' : ''; ?>>Kericho</option>
              <option value="022" <?php echo ($county ?? '') === '022' ? 'selected' : ''; ?>>Kiambu</option>
              <option value="045" <?php echo ($county ?? '') === '045' ? 'selected' : ''; ?>>Kisii</option>
              <option value="042" <?php echo ($county ?? '') === '042' ? 'selected' : ''; ?>>Kisumu</option>
              <option value="003" <?php echo ($county ?? '') === '003' ? 'selected' : ''; ?>>Kilifi</option>
              <option value="015" <?php echo ($county ?? '') === '015' ? 'selected' : ''; ?>>Kitui</option>
              <option value="002" <?php echo ($county ?? '') === '002' ? 'selected' : ''; ?>>Kwale</option>
              <option value="005" <?php echo ($county ?? '') === '005' ? 'selected' : ''; ?>>Lamu</option>
              <option value="031" <?php echo ($county ?? '') === '031' ? 'selected' : ''; ?>>Laikipia</option>
              <option value="016" <?php echo ($county ?? '') === '016' ? 'selected' : ''; ?>>Machakos</option>
              <option value="017" <?php echo ($county ?? '') === '017' ? 'selected' : ''; ?>>Makueni</option>
              <option value="009" <?php echo ($county ?? '') === '009' ? 'selected' : ''; ?>>Mandera</option>
              <option value="010" <?php echo ($county ?? '') === '010' ? 'selected' : ''; ?>>Marsabit</option>
              <option value="012" <?php echo ($county ?? '') === '012' ? 'selected' : ''; ?>>Meru</option>
              <option value="044" <?php echo ($county ?? '') === '044' ? 'selected' : ''; ?>>Migori</option>
              <option value="001" <?php echo ($county ?? '') === '001' ? 'selected' : ''; ?>>Mombasa</option>
              <option value="021" <?php echo ($county ?? '') === '021' ? 'selected' : ''; ?>>Murang'a</option>
              <option value="018" <?php echo ($county ?? '') === '018' ? 'selected' : ''; ?>>Nyandarua</option>
              <option value="046" <?php echo ($county ?? '') === '046' ? 'selected' : ''; ?>>Nyamira</option>
              <option value="019" <?php echo ($county ?? '') === '019' ? 'selected' : ''; ?>>Nyeri</option>
              <option value="032" <?php echo ($county ?? '') === '032' ? 'selected' : ''; ?>>Nakuru</option>
              <option value="033" <?php echo ($county ?? '') === '033' ? 'selected' : ''; ?>>Narok</option>
              <option value="047" <?php echo ($county ?? '') === '047' ? 'selected' : ''; ?>>Nairobi</option>
              <option value="029" <?php echo ($county ?? '') === '029' ? 'selected' : ''; ?>>Nandi</option>
              <option value="043" <?php echo ($county ?? '') === '043' ? 'selected' : ''; ?>>Homa Bay</option>
              <option value="011" <?php echo ($county ?? '') === '011' ? 'selected' : ''; ?>>Isiolo</option>
              <option value="034" <?php echo ($county ?? '') === '034' ? 'selected' : ''; ?>>Kajiado</option>
              <option value="037" <?php echo ($county ?? '') === '037' ? 'selected' : ''; ?>>Kakamega</option>
              <option value="038" <?php echo ($county ?? '') === '038' ? 'selected' : ''; ?>>Vihiga</option>
              <option value="041" <?php echo ($county ?? '') === '041' ? 'selected' : ''; ?>>Siaya</option>
              <option value="025" <?php echo ($county ?? '') === '025' ? 'selected' : ''; ?>>Samburu</option>
              <option value="014" <?php echo ($county ?? '') === '014' ? 'selected' : ''; ?>>Embu</option>
              <option value="028" <?php echo ($county ?? '') === '028' ? 'selected' : ''; ?>>Elgeyo-Marakwet</option>
              <option value="027" <?php echo ($county ?? '') === '027' ? 'selected' : ''; ?>>Uasin Gishu</option>
              <option value="026" <?php echo ($county ?? '') === '026' ? 'selected' : ''; ?>>Trans Nzoia</option>
              <option value="023" <?php echo ($county ?? '') === '023' ? 'selected' : ''; ?>>Turkana</option>
              <option value="004" <?php echo ($county ?? '') === '004' ? 'selected' : ''; ?>>Tana River</option>
              <option value="006" <?php echo ($county ?? '') === '006' ? 'selected' : ''; ?>>Taita-Taveta</option>
              <option value="013" <?php echo ($county ?? '') === '013' ? 'selected' : ''; ?>>Tharaka-Nithi</option>
              <option value="007" <?php echo ($county ?? '') === '007' ? 'selected' : ''; ?>>Garissa</option>
              <option value="008" <?php echo ($county ?? '') === '008' ? 'selected' : ''; ?>>Wajir</option>
            </select>
          </div>
          <div class="inpBox">
            <span>Password</span>
            <input type="password" name="password" required>
          </div>
          <div class="inpBox">
            <span>Confirm password</span>
            <input type="password" name="confirm_password" required>
          </div>
        </div>
        <button type="submit">Register Altar</button>
        <p class="lgIn">Already have your altar's account with us? <a href="altarLogin.html">Login here</a></p>
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