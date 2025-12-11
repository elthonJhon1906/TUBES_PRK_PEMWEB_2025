<?php
if (!function_exists('format_rupiah')) {
    function format_rupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

$pickup_code = isset($_GET['pickup_code']) ? trim(htmlspecialchars($_GET['pickup_code'])) : '';
$order_found = false;
$order_data = null;
$order_items = [];
$error_message = '';

if (!empty($pickup_code)) {
    $sql_order = "SELECT o.*, u.full_name as customer_name 
                  FROM orders o 
                  JOIN users u ON o.customer_id = u.id 
                  WHERE o.pickup_code = ?";
    
    $stmt = $conn->prepare($sql_order);
    $stmt->bind_param("s", $pickup_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order_found = true;
        $order_data = $result->fetch_assoc();

        $sql_items = "SELECT oi.*, 
                             s.name as service_name, 
                             p.name as product_name,
                             s.unit as service_unit,
                             p.unit as product_unit
                      FROM order_items oi
                      LEFT JOIN services s ON oi.service_id = s.id
                      LEFT JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = ?";
        
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("i", $order_data['id']);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        
        while($row = $result_items->fetch_assoc()) {
            $order_items[] = $row;
        }
    } else {
        $error_message = "Kode Pickup <b>" . htmlspecialchars($pickup_code) . "</b> tidak ditemukan.";
    }
}

if (!function_exists('getStatusStep')) {
    function getStatusStep($current_status) {
        $statuses = ['pending', 'processing', 'ready', 'completed'];
        $key = array_search($current_status, $statuses);
        return ($key === false) ? -1 : $key;
    }
}
?>

<section id="lacak" class="py-24 bg-npcDark relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 opacity-20">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-green-500 rounded-full mix-blend-screen filter blur-3xl animate-pulse"></div>
        <div class="absolute top-1/2 right-0 w-64 h-64 bg-blue-500 rounded-full mix-blend-screen filter blur-3xl"></div>
    </div>

    <div class="max-w-4xl mx-auto px-6 relative z-10">
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Lacak Pesanan Anda</h2>
            <p class="text-gray-400 max-w-lg mx-auto">Masukkan Kode Pickup yang tertera pada invoice atau riwayat pesanan untuk melihat status pengerjaan secara langsung.</p>
        </div>
 
        <form action="#lacak" method="GET" class="bg-white/10 p-2 rounded-2xl border border-white/10 backdrop-blur-md flex flex-col sm:flex-row gap-2 max-w-lg mx-auto shadow-2xl mb-12">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <input type="text" name="pickup_code" value="<?php echo htmlspecialchars($pickup_code); ?>" placeholder="Kode Pickup (Cth: ABCD)" class="w-full bg-transparent text-white pl-10 pr-4 py-4 rounded-xl focus:outline-none placeholder:text-gray-500 font-medium uppercase">
            </div>
            <button type="submit" class="bg-npcGreen px-8 py-4 rounded-xl font-bold text-white hover:bg-green-600 transition shadow-lg whitespace-nowrap">
                Cek Status
            </button>
        </form>

        <?php if(!empty($error_message)): ?>
            <div class="max-w-lg mx-auto mt-4 bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-xl text-sm flex items-center justify-center gap-2 animate-pulse mb-8">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if($order_found && $order_data): ?>
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 animate-fade-in-up">

                <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kode Pickup</span>
                        <h2 class="text-3xl font-extrabold text-npcDark tracking-tight"><?php echo strtoupper($order_data['pickup_code']); ?></h2>
                        <div class="text-sm text-gray-500 mt-1">
                            <i class="fa-regular fa-calendar mr-1"></i> <?php echo date('d M Y H:i', strtotime($order_data['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <?php 
                            $badge_color = 'bg-gray-100 text-gray-600';
                            $icon_status = 'fa-clock';
                            if($order_data['status'] == 'processing') { $badge_color = 'bg-blue-100 text-blue-700'; $icon_status = 'fa-gears'; }
                            elseif($order_data['status'] == 'ready') { $badge_color = 'bg-yellow-100 text-yellow-700'; $icon_status = 'fa-box-open'; }
                            elseif($order_data['status'] == 'completed') { $badge_color = 'bg-green-100 text-green-700'; $icon_status = 'fa-check-circle'; }
                            elseif($order_data['status'] == 'cancelled') { $badge_color = 'bg-red-100 text-red-700'; $icon_status = 'fa-ban'; }
                        ?>
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm <?php echo $badge_color; ?>">
                            <i class="fa-solid <?php echo $icon_status; ?>"></i>
                            <?php echo strtoupper($order_data['status']); ?>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <?php if($order_data['status'] != 'cancelled'): ?>
                        <div class="relative mb-10">
                            <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-200 -translate-y-1/2 z-0 rounded"></div>
                            <?php 
                                $step = getStatusStep($order_data['status']); 
                                $width = ($step / 3) * 100;
                            ?>
                            <div class="absolute top-1/2 left-0 h-1 bg-npcGreen -translate-y-1/2 z-0 rounded transition-all duration-1000" style="width: <?php echo $width; ?>%;"></div>

                            <div class="relative z-10 flex justify-between w-full text-center">

                                <?php 
                                $steps_labels = ['Diterima', 'Diproses', 'Siap Ambil', 'Selesai'];
                                $steps_icons = ['fa-receipt', 'fa-print', 'fa-box', 'fa-check'];
                                
                                foreach($steps_labels as $index => $label): 
                                    $active = $step >= $index;
                                ?>
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center border-4 <?php echo $active ? 'bg-npcGreen border-npcGreen text-white' : 'bg-white border-gray-300 text-gray-300'; ?>">
                                        <i class="fa-solid <?php echo $steps_icons[$index]; ?> text-xs md:text-sm"></i>
                                    </div>
                                    <span class="text-[10px] md:text-xs font-bold <?php echo $active ? 'text-npcGreen' : 'text-gray-400'; ?>"><?php echo $label; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl flex gap-3 items-start mb-8">
                            <i class="fa-solid fa-circle-info text-blue-500 mt-1"></i>
                            <div class="text-sm text-blue-800">
                                <?php if($order_data['status'] == 'ready'): ?>
                                    <span class="font-bold">Pesanan siap diambil!</span> Silakan datang ke outlet dengan menunjukkan Kode Pickup ini.
                                <?php else: ?>
                                    Status pesanan Anda saat ini adalah: <strong><?php echo ucfirst($order_data['status']); ?></strong>.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="p-3">Item</th>
                                    <th class="p-3">Qty</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-100">
                                <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td class="p-3 font-medium text-gray-700">
                                        <?php echo $item['item_type'] == 'service' ? $item['service_name'] : $item['product_name']; ?>
                                    </td>
                                    <td class="p-3">
                                        <?php echo $item['quantity']; ?> 
                                    </td>
                                    <td class="p-3 text-right font-bold text-gray-700"><?php echo format_rupiah($item['subtotal']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="border-t border-gray-100">
                                <tr>
                                    <td colspan="2" class="p-3 text-right font-bold text-gray-500">Total</td>
                                    <td class="p-3 text-right font-bold text-npcGreen text-lg"><?php echo format_rupiah($order_data['total_amount']); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>