<?php
session_start();                                    // Start the session

$_SESSION = [];                                     // Clear all session variables

session_destroy();                                  // Destroy the session

header('Location: index.php');                   // Redirect to the Home page
exit();                                             // Make sure to exit after the redirect
