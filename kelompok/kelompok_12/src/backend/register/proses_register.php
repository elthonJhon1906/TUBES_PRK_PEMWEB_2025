<?php
session_start();
require_once '../../koneksi/database.php';

$full_name = bersihkan_input($_POST['full_name']);
$email     = bersihkan_input($_POST['email']);
$phone     = bersihkan_input($_POST['phone']);
$username  = bersihkan_input($_POST['username']);
$password  = $_POST['password'];

$role      = 'customer'; 

if(empty($full_name) || empty($username) || empty($password)) {
    header("location:../../frontend/register/register.php?pesan=kosong");
    exit;
}

$cek_user = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
if(mysqli_num_rows($cek_user) > 0) {
    header("location:../../frontend/register/register.php?pesan=duplicate");
    exit;
}

$password_simpan = mysqli_real_escape_string($conn, $password);

$query_insert = "INSERT INTO users (username, password, full_name, email, phone, role, is_active) 
                 VALUES ('$username', '$password_simpan', '$full_name', '$email', '$phone', '$role', 1)";

if(mysqli_query($conn, $query_insert)) {
    header("location:../../frontend/login/login.php?pesan=registered");
} else {
    echo "Error: " . $query_insert . "<br>" . mysqli_error($conn);
}
?>