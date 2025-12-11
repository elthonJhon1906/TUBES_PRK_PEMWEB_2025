<footer class="bg-white border-t border-gray-100 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="bg-npcGreen text-white font-bold text-lg px-2 py-0.5 rounded">NPC</div>
                        <span class="font-bold text-xl text-npcDark">Nagoya Print</span>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-sm mb-6">
                        Solusi percetakan modern yang mengutamakan kecepatan dan kualitas. Kami siap membantu kebutuhan dokumen bisnis dan pendidikan Anda.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-npcGreen hover:text-white transition"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-npcGreen hover:text-white transition"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-npcGreen hover:text-white transition"><i class="fa-regular fa-envelope"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-npcDark mb-6">Navigasi</h4>
                    <ul class="space-y-4 text-sm text-gray-500">
                        <li><a href="#beranda" class="hover:text-npcGreen transition">Beranda</a></li>
                        <li><a href="#tentang" class="hover:text-npcGreen transition">Tentang Kami</a></li>
                        <li><a href="#layanan" class="hover:text-npcGreen transition">Layanan & Harga</a></li>
                        <li><a href="frontend/login/login.php" class="hover:text-npcGreen transition">Login Member</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-npcDark mb-6">Hubungi Kami</h4>
                    <ul class="space-y-4 text-sm text-gray-500">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 text-npcGreen"></i>
                            <span>Jl. Kampung Baru,<br>Bandar Lampung</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-phone text-npcGreen"></i>
                            <span>(0890) 123-4567</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-clock text-npcGreen"></i>
                            <span>Senin - MInggu (08.00 - 20.00)</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Nagoya Print & Copy. All rights reserved.</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-npcDark">Privacy Policy</a>
                    <a href="#" class="hover:text-npcDark">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            const logoText = document.getElementById('logo-text');
            const logoSub = document.getElementById('logo-sub');
            const navLinks = document.querySelectorAll('.nav-link');

            if (window.scrollY > 50) {
                navbar.classList.add('bg-white/90', 'backdrop-blur-md', 'shadow-md', 'py-2');
                navbar.classList.remove('bg-transparent', 'py-4');
                
                logoText.classList.replace('text-white', 'text-npcDark');
                logoSub.classList.replace('text-gray-300', 'text-gray-500');
                
                navLinks.forEach(link => link.classList.replace('text-white/90', 'text-gray-600'));
            } else {
                navbar.classList.remove('bg-white/90', 'backdrop-blur-md', 'shadow-md', 'py-2');
                navbar.classList.add('bg-transparent', 'py-4');
                
                logoText.classList.replace('text-npcDark', 'text-white');
                logoSub.classList.replace('text-gray-500', 'text-gray-300');
                
                navLinks.forEach(link => link.classList.replace('text-gray-600', 'text-white/90'));
            }
        });

        <?php if(isset($_GET['pickup_code'])): ?>
            document.addEventListener("DOMContentLoaded", function() {
                const element = document.getElementById("lacak");
                if(element) {
                    element.scrollIntoView({ behavior: "smooth", block: "start" });
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>