<?php
// Database-less Configuration
define('AUTH_USER', 'admin');
define('AUTH_PASS', 'kannanvs123'); // Highly recommended to change this

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global project settings
define('PROJECT_NAME', 'Little Krish ID Generator');
?>
