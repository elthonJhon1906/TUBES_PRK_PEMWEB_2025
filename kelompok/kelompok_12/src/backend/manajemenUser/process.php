<?php
require_once '../../koneksi/database.php'; 
require_once 'auth_middleware.php';

checkAuthorization(['OWNER']);

$frontend_path = '../../frontend/manajemenUser/'; 

if (isset($_POST['add_user'])) {
    $username  = $_POST['username'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role      = $_POST['role']; 
    $email     = $_POST['email'];
    $full_name = $_POST['full_name'];

    try {
        $sql = "INSERT INTO users (username, email, password, full_name, role) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $password, $full_name, $role]);
        
        header("Location: {$frontend_path}index.php?status=success_add");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
             $error_msg = urlencode("Username atau Email sudah terdaftar. Gunakan yang lain.");
             header("Location: {$frontend_path}create.php?status=error&msg=" . $error_msg);
             exit();
        } else {
             $error_msg = urlencode("Terjadi kesalahan database: " . $e->getMessage());
             header("Location: {$frontend_path}create.php?status=error&msg=" . $error_msg);
             exit();
        }
    }
}

if (isset($_POST['edit_user_full'])) {
    $id        = $_POST['id'];
    $role      = $_POST['role'];
    $is_customer_edit = $_POST['is_customer_edit'] ?? '0'; 
    
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $full_name = $_POST['full_name'];
    $password_input = $_POST['password'];

    if ($is_customer_edit === '1') {
        
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $params = [$role, $id];
        
    } else {
        
        $params = [$username, $email, $full_name, $role, $id];
        
        if (!empty($password_input)) {
            $password = password_hash($password_input, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET 
                        username=?, 
                        email=?, 
                        full_name=?, 
                        password=?, 
                        role=? 
                    WHERE id=?";
            
            array_splice($params, 3, 0, $password);
            
        } else {
            $sql = "UPDATE users SET 
                        username=?, 
                        email=?, 
                        full_name=?, 
                        role=? 
                    WHERE id=?";
        }
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        header("Location: {$frontend_path}index.php?status=success_edit");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() === '23000' && $is_customer_edit !== '1') {
             $error_msg = urlencode("Username atau Email sudah terdaftar. Gunakan yang lain.");
             header("Location: {$frontend_path}edit.php?id=$id&status=error&msg=" . $error_msg);
             exit();
        } else {
             $error_msg = urlencode("Terjadi kesalahan database: " . $e->getMessage());
             header("Location: {$frontend_path}edit.php?id=$id&status=error&msg=" . $error_msg);
             exit();
        }
    }
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    if ($id == ($_SESSION['user_id'] ?? null)) {
        header("Location: {$frontend_path}index.php?status=error_self");
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: {$frontend_path}index.php?status=success_delete");
        exit();
    } catch (PDOException $e) {
        $error_msg = urlencode("Gagal menghapus user: " . $e->getMessage());
        header("Location: {$frontend_path}index.php?status=error&msg=" . $error_msg);
        exit();
    }
}
?>