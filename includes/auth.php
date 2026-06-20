<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['id_user'])) {
    redirect('login.php');
}
