<?php
session_start();
require_once '../../koneksi/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login/login.php');
    exit;
}

$token = $_GET['token'] ?? '';
$decodedPath = $token ? base64_decode(strtr($token, ' ', '+'), true) : false;

$projectRoot = realpath(__DIR__ . '/../../..');
$uploadsDir = realpath($projectRoot . '/assets/uploads/payments');
$publicUrl = null;
$filename = null;
$error = null;

if (!$decodedPath) {
    $error = 'Bukti pembayaran tidak ditemukan.';
} else {
    $relativePath = ltrim($decodedPath, '/');

        // Normalize path jika masih menyertakan prefiks src/
        if (strpos($relativePath, 'src/') === 0) {
            $relativePath = substr($relativePath, 4);
        }
    $fullPath = realpath($projectRoot . '/' . $relativePath);

    if (!$fullPath || !$uploadsDir || strpos($fullPath, $uploadsDir) !== 0 || !is_file($fullPath)) {
        $error = 'File bukti pembayaran tidak tersedia.';
    } else {
        $publicUrl = '/' . $relativePath;
        $filename = basename($fullPath);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Bukti Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 w-full max-w-3xl overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase text-slate-400 tracking-[0.2em] font-semibold">Bukti Pembayaran</p>
                <h1 class="text-2xl font-bold text-slate-900 mt-1">Preview File</h1>
                <?php if ($filename): ?>
                    <p class="text-sm text-slate-500">Nama file: <strong><?php echo htmlspecialchars($filename); ?></strong></p>
                <?php endif; ?>
            </div>
            <div class="flex gap-2">
                <button onclick="window.history.back()" class="px-4 py-2 text-sm font-semibold rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50">Kembali</button>
                <?php if ($publicUrl): ?>
                    <a href="<?php echo htmlspecialchars($publicUrl); ?>" download class="px-4 py-2 text-sm font-semibold rounded-2xl bg-slate-900 text-white shadow hover:bg-slate-800">Unduh</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="p-6 bg-slate-50 min-h-[420px] flex items-center justify-center">
            <?php if ($error): ?>
                <div class="text-center text-slate-500">
                    <p class="font-semibold mb-2">Oops!</p>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php else: ?>
                <div class="w-full">
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <?php if (preg_match('/\.(pdf)$/i', $filename ?? '')): ?>
                            <iframe src="<?php echo htmlspecialchars($publicUrl); ?>" class="w-full h-[480px]"></iframe>
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($publicUrl); ?>" alt="Bukti Pembayaran" class="w-full max-h-[520px] object-contain bg-white">
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-400 text-center mt-3">Jika gambar tidak tampil, klik tombol unduh untuk membuka file secara langsung.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
