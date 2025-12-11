<?php
session_start();

$pesan_sukses = "";
$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = "Format email tidak valid.";
    } else {
        $pesan_sukses = "Link reset password telah dikirim ke <b>$email</b>. Silakan cek inbox atau folder spam Anda.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - NPC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { npcGreen: '#10B981', npcDark: '#0F172A' }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans text-gray-600 h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        
        <div class="bg-npcDark p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white/10 text-npcGreen text-xl mb-3">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="text-2xl font-bold text-white">Lupa Password?</h2>
            <p class="text-gray-400 text-sm mt-1">Jangan khawatir, kami akan membantu Anda.</p>
        </div>

        <div class="p-8">
            
            <?php if ($pesan_sukses): ?>
                <div class="text-center">
                    <div class="mb-6 text-green-500 text-5xl">
                        <i class="fa-regular fa-envelope-open"></i>
                    </div>
                    <div class="bg-green-50 text-green-800 p-4 rounded-lg text-sm mb-6 border border-green-100">
                        <?php echo $pesan_sukses; ?>
                    </div>
                    <a href="../login/login.php" class="block w-full bg-npcDark text-white font-bold py-3 rounded-xl hover:bg-gray-800 transition text-center">
                        Kembali ke Halaman Login
                    </a>
                </div>

            <?php else: ?>
                <?php if ($pesan_error): ?>
                    <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm flex items-center gap-2 border border-red-100">
                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $pesan_error; ?>
                    </div>
                <?php endif; ?>

                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    Masukan alamat email yang terdaftar pada akun Anda. Kami akan mengirimkan instruksi untuk mengatur ulang kata sandi.
                </p>

                <form action="" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Email Terdaftar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" name="email" class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-3.5 pl-11 pr-4 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-npcGreen focus:border-transparent transition outline-none" placeholder="contoh@email.com" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-npcGreen text-white font-bold py-3.5 rounded-xl hover:bg-green-600 transition shadow-lg transform active:scale-95">
                        Kirim Link Reset
                    </button>
                </form>
                
                <div class="mt-8 text-center border-t border-gray-100 pt-6">
                    <a href="../login/login.php" class="text-sm font-semibold text-gray-400 hover:text-npcDark transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Login
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>