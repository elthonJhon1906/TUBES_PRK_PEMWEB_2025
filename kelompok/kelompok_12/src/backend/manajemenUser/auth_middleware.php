<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function checkAuthorization($allowed_roles = []) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
        header("Location: ../../frontend/login/login.php"); 
        exit();
    }
    
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = $_SESSION['user']['id'];
    }
    if (!isset($_SESSION['role'])) {
        $_SESSION['role'] = $_SESSION['user']['role'];
    }

    if (!empty($allowed_roles)) {
        if (!in_array(strtoupper($_SESSION['role']), array_map('strtoupper', $allowed_roles))) {
            echo "<script>alert('Akses Ditolak: Anda tidak memiliki izin.'); window.history.back();</script>";
            exit();
        }
    }
}
?>