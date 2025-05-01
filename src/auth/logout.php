<?php
session_start();
session_unset();
session_destroy(); // Destroy the session

// Redirect to the register page after logout
header('Location: register.php');
exit; // Make sure no further code is executed
