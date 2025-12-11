<?php 
session_start();
require_once 'koneksi/database.php'; 

if(isset($_SESSION['status']) && $_SESSION['status'] == "login"){
    header("location: frontend/dashboard/dashboard.php");
    exit;
}

require_once 'frontend/home/header_index.php';

require_once 'frontend/home/navbar.php';

require_once 'frontend/home/hero.php';
require_once 'frontend/home/tentang.php';
require_once 'frontend/home/layanan.php';

require_once 'frontend/home/lacak.php';

require_once 'frontend/home/footer.php';
?>