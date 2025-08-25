<?php
session_start();       // start the session
session_unset();       // remove all session variables
session_destroy();     // destroy the session

// Redirect to login page
header("Location: altarLogin.php");
exit();
?>