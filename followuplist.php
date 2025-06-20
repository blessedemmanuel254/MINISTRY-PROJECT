<?php
include 'connection.php';
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
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && isset($_POST['status'])) {
  // Get and sanitize the POST values (using prepared statements below)
  $id = $_POST['id'];
  $status = $_POST['status'];

  // Prepare the update query to change the user's status
  $stmt = $conn->prepare("UPDATE followuplist SET status = ? WHERE id = ?");
  $stmt->bind_param("ii", $status, $id);

  // Execute the update query
  if (!$stmt->execute()) {
    error_log("Error executing update: " . $stmt->error);
  }
  
  $stmt->close();
  exit; // End the script so no HTML is output in response to the AJAX call
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Follow up list</title>
  <link rel="website icon" type="png" href="Images/Ministry Logo.avif">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
  <a href="index.php" id="overlay" class="overlay"></a>
  <div id="popUp">
    <h4>Did you do the follow up? If you have already called the servant and the follow up done successfully, select <span>Successful</span>. Else if there is no response or you did not communicate select <span>No answer</span></h4>
    <div class="response">
      <p class="noResponce">No&nbsp;answer</p>
      <p>Successful</p>
    </div>
  </div>
  <div class="container">
    <header>
      <img src="Images/Ministry Logo.avif" alt="Ministry Logo"> <p>PWANI UNIVERSITY ALTAR</p>
    </header>
    <h1>Follow up list</h1>
    <ul>
      <li>All</li>
      <li>Hospital&nbsp;Mission</li>
      <li>Earlybird&nbsp;(Outreach)</li>
      <li>Earlybird&nbsp;(In&nbsp;school)</li>
      <li>Hostel&nbsp;Evangelism</li>
      <li>Lunch&nbsp;Hour&nbsp;Outreach</li>
      <li class="active">General&nbsp;Outreach</li>
      <li>Sunday&nbsp;Visitor</li>
      <li>Other</li>
    </ul>
    <a href="addServant.php">+ Add new servant</a>
    <div class="tableContainer">
      <!-- Give the table an ID for DataTables -->
      <table id="myTable">
        <thead>
          <th>#</th>
          <th>First&nbsp;Name</th>
          <th>Number</th>
          <th>Action</th>
          <th>Status</th>
          <th>Evangelist</th>
          <th>M.&nbsp;Point</th>
          <th>Date</th>
        </thead>
        <tbody>
          <?php
          $query = "SELECT * FROM followuplist";
          $result = $conn->query($query);
          $counter = 1;

          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                // Create masked phone number
                $maskedPhone = maskPhone($row['phoneNumber']);
                echo "<tr>
                        <td>{ $counter}.</td>
                        <td>{$row['fname']}</td>
                        <!-- Display the masked phone number, and store the actual number in data-phone -->
                        <td data-phone='{$row['phoneNumber']}'>{$maskedPhone}</td>
                        <td>
                          <p class='copy' style='cursor:pointer;'>Copy</p>
                          <p class='update' style='cursor:pointer;' data-userid='{$row['id']}'>Update</p>
                        </td>
                        <td><i class='fa-solid " . ($row['status'] == '2' ? 'fa-check' : ($row['status'] == '1' ? 'fa-x' : 'fa-minus')) . "'></i></td>
                        <td>{$row['evangelist']}</td>
                        <td>{$row['venue']}</td>
                        <td>{$row['dDate']}</td>
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
  
  <!-- Include jQuery and DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  
  <script>
    // Initialize DataTables with 25 rows per page and disable sorting
    $(document).ready(function(){
      $('#myTable').DataTable({
        "pageLength": 25,
        "ordering": false
      });
    });
    
    document.addEventListener("DOMContentLoaded", function(){
      const copyButtons = document.querySelectorAll(".copy");
      
      copyButtons.forEach(function(btn) {
        btn.addEventListener("click", function(){
          // Get the row in which the button is located.
          const row = btn.closest("tr");
          // Get the first name from the second cell.
          const firstName = row.getElementsByTagName("td")[1].textContent.trim();
          // Instead of reading the masked text, retrieve the original phone number from the data attribute in the third cell.
          const phoneCell = row.querySelector("td[data-phone]");
          const phoneNumber = phoneCell.getAttribute("data-phone");

          if(navigator.clipboard) {
            navigator.clipboard.writeText(phoneNumber).then(function(){
              alert(`You have copied ${firstName}'s number: ${phoneNumber}. Let's bring the sheep of Christ home.`);
            }).catch(function(err){
              console.error("Error copying text: ", err);
            });
          } else {
            // Fallback for browsers that do not support Clipboard API
            const textarea = document.createElement("textarea");
            textarea.value = phoneNumber;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand("copy");
            document.body.removeChild(textarea);
            alert(`You have copied ${firstName}'s number. Let's bring the sheep of Christ home.`);
          }
        });
      });

      // Update functionality
      const updateButtons = document.querySelectorAll(".update");
      const overlay = document.getElementById("overlay");
      let currentUserId = null; // Will store the id of the user to update
      
      updateButtons.forEach(function(btn) {
        btn.addEventListener("click", function(){
          currentUserId = btn.getAttribute("data-userid");
          document.getElementById("popUp").style.display = "block";
          document.getElementById("overlay").style.display = "block";
        });
      });
      
      // Handle clicks on the popup responses
      const popUp = document.getElementById("popUp");
      const responses = popUp.querySelectorAll(".response p");
      responses.forEach(function(resp) {
        resp.addEventListener("click", function(){
          let newStatus;
          const responseText = resp.textContent.trim().toLowerCase().replace(/\u00A0/g, ' ');
          if(responseText === "successful") {
              newStatus = 2;
          } else if(responseText === "no answer") {
              newStatus = 1;
          }
          
          // Send an AJAX POST request to update the user's status
          fetch("", {  // Empty string ("") sends the request to the same page
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "id=" + encodeURIComponent(currentUserId) + "&status=" + encodeURIComponent(newStatus)
          })
          .then(response => response.text())
          .then(data => {
            // Hide the popup after the update
            popUp.style.display = "none";
            overlay.style.display = "block";
            // Optionally, reload the page or update the status icon dynamically
            location.reload();
          })
          .catch(error => {
            console.error("Error updating status:", error);
          });
        });
      });
    });
  </script>
</body>
</html>

<!--Make this ul to have a slide in effect on selecting active tabs; HERE IS THE HTML 
    <ul>
      <li>All</li>
      <li>Hospital&nbsp;Mission</li>
      <li>Earlybird&nbsp;(Outreach)</li>
      <li>Earlybird&nbsp;(In&nbsp;school)</li>
      <li>Hostel&nbsp;Evangelism</li>
      <li>Lunch&nbsp;Hour&nbsp;Outreach</li>
      <li class="active">General&nbsp;Outreach</li>
      <li>Sunday&nbsp;Visitor</li>
      <li>Other</li>
    </ul>   HERE IS THE ALREADY WRITTEN CSS THEREFORE ADJUST IT; 

.container ul {
  display: flex;
  gap: 10px;
  font-size: 14px;
  overflow-x: auto;
}

.container ul li {
  list-style: none;
  cursor: pointer;
  background-color: #898888;
  color: white;
  padding: 2px;
  border-radius: 10px;
  font-size: 14px;
}

.container ul li:hover {
  opacity: .75;
}

.container ul li.active {
  background-color: #0f0f0f;
}-->