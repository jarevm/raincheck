<?php
// connect to the database and start the session
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

define('ADMIN_CODE', 'MAKEMEADMIN');
$conn = new mysqli('localhost', 'root', '149315', 'raincheck');
$conn->set_charset('utf8mb4');

// pages that need a logged in user call this
function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// pages for the admin only call this
function checkAdmin()
{
    checkLogin();
    if ($_SESSION['role'] != 'ADMIN') {
        header('Location: index.php');
        exit;
    }
}
