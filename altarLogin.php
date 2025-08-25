<?php
session_start();
include 'connection.php'; // connect to your DB

$error = '';
$success = '';
$login = '';

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $login = trim($_POST['login'] ?? ''); // email or phone
  $password = $_POST['password'] ?? '';

  if (empty($login) || empty($password)) {
      $error = "Please enter your email/phone and password!";
  } else {
    $encrypted_email = base64_encode($login);
    $normalized_phone = normalizePhone($login);
    $encrypted_phone = base64_encode($normalized_phone);
    // query for email OR phone_1 OR phone_2
    $stmt = $conn->prepare("SELECT * FROM altars WHERE (email = ? OR phone_1 = ? OR phone_2 = ?) LIMIT 1");
    $stmt->bind_param("sss", $encrypted_email, $encrypted_phone1, $encrypted_phone2);
    $stmt->execute();
    $result = $stmt->get_result();
    $altar = $result->fetch_assoc();

    if ($altar && password_verify($password, $altar['password'])) {
        // Success
        $_SESSION['altar_id'] = $altar['altar_id'];
        $_SESSION['altar_name'] = $altar['altar_name'];
        $success = "✅ Login successful! Welcome " . htmlspecialchars($altar['snr_pst_fullname']);
        header("Location: altarPortal.php");
        exit;

    } else {
        $error = "Invalid email, or phone or password!";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Altar Account | Returntoholiness</title>

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
        <h1>LOGIN ALTAR ACCOUNT</h1>
        <p>Enter your altar name and altar password to login:</p>
      </div>
      <form action="" method="POST">
        <?php if (!empty($error)) { ?>
          <p class="errorMsg"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <?php if (!empty($success)) { ?>
          <p class="successMsg"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></p>
        <?php } ?>

        <div class="fmInCntnr">
          <div class="inpBox">
            <span>Email or Phone</span>
            <input type="text" name="login" value="<?php echo htmlspecialchars($login ?? ''); ?>">
          </div>
          <div class="inpBox">
            <span>Password</span>
            <input type="password" name="password">
          </div>
        </div>
        <button type="submit">Login</button>
        <p class="lgIn">Don't have altar's accout? <a href="registerAltar.php">Register Altar</a></p>
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