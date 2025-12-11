<?php
require_once '../../koneksi/database.php'; 
require_once '../manajemenUser/auth_middleware.php'; 

checkAuthorization([]); 

$frontend_path = '../../frontend/profile/'; 

if (isset($_POST['update_profile'])) {
    $id        = $_POST['id'];
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $full_name = $_POST['full_name'];
    $phone     = $_POST['phone'];
    $password  = $_POST['password']; 

    if ($id != ($_SESSION['user']['id'] ?? null)) {
        header("Location: {$frontend_path}profile.php?status=error&msg=" . urlencode("Akses ditolak. Anda hanya dapat mengedit profil sendiri."));
        exit();
    }
    
    $sql_base = "UPDATE users SET 
                    username=?, 
                    email=?, 
                    full_name=?,
                    phone=?
                 WHERE id=?";
    
    $params = [$username, $email, $full_name, $phone, $id];
    
    if (!empty($password)) {
        
        $password_plaintext = $password; 
        
        $sql_base = "UPDATE users SET 
                        username=?, 
                        email=?, 
                        full_name=?, 
                        phone=?,
                        password=? 
                     WHERE id=?";
        
        $params = [$username, $email, $full_name, $phone, $password_plaintext, $id];
    }
    
    try {
        $stmt = $pdo->prepare($sql_base);
        $stmt->execute($params);
        
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['full_name'] = $full_name;

        header("Location: {$frontend_path}profile.php?status=success");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
             $error_msg = urlencode("Username atau Email sudah terdaftar. Gunakan yang lain.");
             header("Location: {$frontend_path}profile.php?status=error&msg=" . $error_msg);
             exit();
        } else {
             $error_msg = urlencode("Terjadi kesalahan database: " . $e->getMessage());
             header("Location: {$frontend_path}profile.php?status=error&msg=" . $error_msg);
             exit();
        }
    }
}
?>