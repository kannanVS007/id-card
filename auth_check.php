<?php
require_once 'config.php';

// ===============================
// LOGIN CHECK
// ===============================
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ===============================
// ROLE HELPERS
// ===============================
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}
