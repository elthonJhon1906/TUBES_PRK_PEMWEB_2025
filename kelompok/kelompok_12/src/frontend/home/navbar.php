<nav id="navbar" class="fixed w-full z-50 top-0 transition-all duration-300 py-4 bg-transparent">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-2 group">
            <div class="bg-npcGreen text-white font-bold text-2xl px-3 py-1 rounded-lg shadow-lg group-hover:scale-105 transition">
                <i class="fa-solid fa-print"></i>
            </div>
            <div class="flex flex-col">
                <span id="logo-text" class="font-extrabold text-white text-lg leading-tight transition-colors">NPC SYSTEM</span>
                <span id="logo-sub" class="text-[10px] font-medium text-gray-300 tracking-widest uppercase transition-colors">Nagoya Print</span>
            </div>
        </a>

        <div class="hidden md:flex items-center space-x-8">
            <a href="#beranda" class="nav-link text-sm font-medium text-white/90 hover:text-npcGreen transition">Beranda</a>
            <a href="#tentang" class="nav-link text-sm font-medium text-white/90 hover:text-npcGreen transition">Tentang</a>
            <a href="#layanan" class="nav-link text-sm font-medium text-white/90 hover:text-npcGreen transition">Layanan</a>
            <a href="#lacak" class="nav-link text-sm font-medium text-white/90 hover:text-npcGreen transition">Lacak Order</a>
        </div>

        <div class="flex items-center gap-4">
            <a href="frontend/login/login.php" class="bg-white text-npcDark border border-transparent px-6 py-2.5 rounded-full text-sm font-bold hover:bg-gray-100 transition shadow-lg flex items-center gap-2">
                <i class="fa-regular fa-user"></i> Masuk / Daftar
            </a>
        </div>
    </div>
</nav>