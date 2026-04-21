<?php
require_once '../includes/functions.php';

// Destroy session
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
session_start(); // Need a fresh session to hold the flash message
setFlash('success', 'You have been successfully logged out.');
redirect('../index.php');
?>
