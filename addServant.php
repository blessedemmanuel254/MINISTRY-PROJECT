<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'connection.php';

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve and trim form data
  $fname = trim($_POST['fname']);
  $phoneNumber = trim($_POST['phoneNumber']);
  $evangelist = trim($_POST['evangelist']);
  $venue = trim($_POST['venue']);

  // Check if the phone number already exists
  $checkStmt = $conn->prepare("SELECT id FROM followuplist WHERE phoneNumber = ?");
  $checkStmt->bind_param("s", $phoneNumber);
  $checkStmt->execute();
  $checkStmt->store_result();
  if ($checkStmt->num_rows > 0) {
    $errorMessage = "Phone number already exists in follow-up records.";
  }
  $checkStmt->close();

  // Validate input
  if (empty($fname) || empty($phoneNumber) || empty($evangelist) || empty($venue)) {
    $errorMessage = "All fields are required.";
  }

  // Validate: Servant's name must be only one word (no spaces)
  if (strpos($fname, ' ') !== false) {
    $errorMessage = "Please enter only one name for the servant.";
  }
  // Validate: Phone number must match a professional pattern (optional '+' and 10 to 15 digits)
  elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phoneNumber)) {
    $errorMessage = "Please enter a valid phone number.";
  }
  // Validate: Evangelist's name must be only one word
  elseif (strpos($evangelist, ' ') !== false) {
    $errorMessage = "Please enter only one name for the evangelist.";
  }
  // Validate: Meeting point must be only one word
  elseif (strpos($venue, ' ') !== false) {
    $errorMessage = "Please enter only one name for the meeting point.";
  }
  
  // If there are no validation errors, insert into the database
  if (empty($errorMessage)) {
    $fname = strtoupper($fname);
    $evangelist = strtoupper($evangelist);
    $venue = ucfirst(strtolower($venue));
    $stmt = $conn->prepare("INSERT INTO followuplist (fname, phoneNumber, evangelist, venue) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fname, $phoneNumber, $evangelist, $venue);

    if ($stmt->execute()) {
      $successMessage = 'Servant`s details updated successfully !<br><span>1 Corinthians 15:58</span> <span>"Therefore, my dear brothers and sisters, stand firm. Let nothing move you. Always give yourselves fully to the work of the Lord, because you know that YOUR LABOR IN THE LORD IS NOT IN VAIN.</span>"';

      $fname = '';
      $phoneNumber = '';
      $evangelist = '';
      $venue = '';
    } else {
      $errorMessage = "Error inserting record: " . $stmt->error;
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
  <title>Add new follow-up servant</title>
  <link rel="website icon" type="png" href="images/ministrylogo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container newServant">
    <header class="newServantHeader">
      <img src="images/ministrylogo.png" alt="Ministry Logo">
      <p>PWANI UNIVERSITY ALTAR</p>
    </header>
    <p class="newServant">Add new follow-up</p>
    <!-- Display error or success message inside the form -->
    <form action="" method="POST">
      <p class="<?php echo !empty($successMessage) ? 'successMessage' : 'errorMessage'; ?>">
        <?php 
          if (!empty($errorMessage)) {
            echo $errorMessage;
          } elseif (!empty($successMessage)) {
            echo $successMessage;
          }
        ?>
      </p>
      
      <span>Servant's Name:</span>
      <input name="fname" type="text" value="<?php echo htmlspecialchars($fname ?? ''); ?>" required>
      
      <span>Phone:</span>
      <input name="phoneNumber" type="text" value="<?php echo htmlspecialchars($phoneNumber ?? ''); ?>" required>
      
      <span>Your Name:</span>
      <input name="evangelist" type="text" value="<?php echo htmlspecialchars($evangelist ?? ''); ?>" required>
      
      <span>Meeting Point:</span>
      <input name="venue" type="text" value="<?php echo htmlspecialchars($venue ?? ''); ?>" required>

      <span>Mission Type:</span>
      <select id="missionType" name="missionType" required>
        <option value="">--Select mission/category--</option>
        <option value="HSMN">Hospital Mission</option>
        <option value="EBEO">Earlybird (Outreach)</option>
        <option value="EBEI">Earlybird (In-school)</option>
        <option value="HEVM">Hostel Evangelism</option>
        <option value="EBEI">Lunch Hour Outreach</option>
        <option value="GEVM">General Outreach</option>
        <option value="SUV">Sunday Visitor</option>
        <option value="OTH">Evangelism (other)</option>
      </select>
      
      <button type="submit">SUBMIT</button>
    </form>
  </div>
</body>
</html>