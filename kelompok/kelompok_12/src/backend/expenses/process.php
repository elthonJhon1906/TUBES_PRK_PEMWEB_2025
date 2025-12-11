<?php
require_once '../../koneksi/database.php'; 
require_once '../manajemenUser/auth_middleware.php';

checkAuthorization(['OWNER', 'ADMIN']); 

$frontend_path = '../../frontend/expenses/'; 
$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

function catatLog($conn, $uid, $user, $role, $action, $target, $desc) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $desc = mysqli_real_escape_string($conn, $desc);
    $sql = "INSERT INTO system_logs (user_id, username, role, action_type, target_id, description) 
            VALUES ('$uid', '$user', '$role', '$action', '$target', '$desc')";
    @mysqli_query($conn, $sql);
}

if (isset($_POST['add_expense'])) {
    $expense_date = $_POST['expense_date'];
    $description  = bersihkan_input($_POST['description']);
    $category     = bersihkan_input($_POST['category']);
    $amount       = (float)$_POST['amount'];

    if (empty($expense_date) || empty($description) || empty($category) || $amount <= 0) {
        header("Location: {$frontend_path}expenses.php?status=error&msg=" . urlencode("Semua field wajib diisi dan nominal harus lebih dari nol."));
        exit();
    }

    $sql = "INSERT INTO expenses (expense_date, description, category, amount, created_by) 
            VALUES (?, ?, ?, ?, ?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$expense_date, $description, $category, $amount, $user_id]);
        
        $new_id = $pdo->lastInsertId();
        catatLog($conn, $user_id, $username, $role, 'ADD_EXPENSE', $new_id, "Input pengeluaran baru: $description (Rp " . number_format($amount, 0) . ")");

        header("Location: {$frontend_path}expenses.php?status=success");
        exit();
    } catch (PDOException $e) {
        $error_msg = urlencode("Gagal menyimpan data pengeluaran: " . $e->getMessage());
        header("Location: {$frontend_path}expenses.php?status=error&msg=" . $error_msg);
        exit();
    }
}

if (isset($_POST['edit_expense'])) {
    checkAuthorization(['OWNER', 'ADMIN']); 
    
    $id             = (int)$_POST['id'];
    $expense_date   = $_POST['expense_date'];
    $description    = bersihkan_input($_POST['description']);
    $category       = bersihkan_input($_POST['category']);
    $amount         = (float)$_POST['amount'];

    if ($id <= 0 || empty($expense_date) || $amount <= 0) {
        header("Location: {$frontend_path}expenses.php?status=error&msg=" . urlencode("Data tidak valid untuk diedit."));
        exit();
    }
    
    $sql = "UPDATE expenses SET 
                expense_date = ?, 
                description = ?, 
                category = ?, 
                amount = ?
            WHERE id = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$expense_date, $description, $category, $amount, $id]);
        
        catatLog($conn, $user_id, $username, $role, 'EDIT_EXPENSE', $id, "Mengedit pengeluaran ID $id.");

        header("Location: {$frontend_path}expenses.php?status=success_edit");
        exit();
    } catch (PDOException $e) {
        $error_msg = urlencode("Gagal mengedit data pengeluaran: " . $e->getMessage());
        header("Location: {$frontend_path}expenses.php?status=error&msg=" . $error_msg);
        exit();
    }
}

if (isset($_GET['delete_id'])) {
    checkAuthorization(['OWNER', 'ADMIN']);
    
    $id = (int)$_GET['delete_id'];
    
    if ($id <= 0) {
        header("Location: {$frontend_path}expenses.php?status=error&msg=" . urlencode("ID pengeluaran tidak valid."));
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$id]);
        
        catatLog($conn, $user_id, $username, $role, 'DELETE_EXPENSE', $id, "Menghapus pengeluaran ID $id");
        
        header("Location: {$frontend_path}expenses.php?status=success_delete");
        exit();
    } catch (PDOException $e) {
        $error_msg = urlencode("Gagal menghapus data pengeluaran: " . $e->getMessage());
        header("Location: {$frontend_path}expenses.php?status=error&msg=" . $error_msg);
        exit();
    }
}

?>