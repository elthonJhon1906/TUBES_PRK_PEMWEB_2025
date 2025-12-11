<?php
session_start();
require_once __DIR__ . '/../../koneksi/database.php'; 

if (!isset($_SESSION['user'])) {
    header("Location: ../../frontend/login/login.php");
    exit;
}

$role = isset($_SESSION['user']['role']) ? strtoupper($_SESSION['user']['role']) : '';

if (!in_array($role, ['ADMIN', 'OWNER'])) {
    header("Location: ../../frontend/dashboard/dashboard.php");
    exit;
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

function buildUrl($params = []) {
    $currentParams = $_GET;
    $newParams = array_merge($currentParams, $params);
    return '?' . http_build_query($newParams);
}

function renderPaymentBadge($status) {
    $statusLower = strtolower($status);
    switch ($statusLower) {
        case 'verified':
            return '<span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs font-bold uppercase border border-emerald-200"><i class="fa-solid fa-check-double"></i> Verified</span>';
        case 'paid':
            return '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase border border-green-200"><i class="fa-solid fa-check"></i> Lunas</span>';
        case 'partial':
            return '<span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-xs font-bold uppercase border border-amber-200"><i class="fa-solid fa-hourglass-half"></i> Cicil (DP)</span>';
        default:
            return '<span class="px-2 py-1 bg-rose-100 text-rose-700 rounded text-xs font-bold uppercase border border-rose-200"><i class="fa-solid fa-xmark"></i> Belum</span>';
    }
}

function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'completed': return '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold border border-green-200 flex items-center gap-1 justify-center"><i class="fa-solid fa-check-double"></i> Selesai</span>';
        case 'ready': return '<span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs font-bold border border-emerald-200 flex items-center gap-1 justify-center"><i class="fa-solid fa-box-open"></i> Siap Ambil</span>';
        case 'processing': return '<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-bold border border-blue-200 flex items-center gap-1 justify-center"><i class="fa-solid fa-gears"></i> Proses</span>';
        case 'pending': return '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold border border-yellow-200 flex items-center gap-1 justify-center"><i class="fa-regular fa-clock"></i> Antrian</span>';
        case 'cancelled': return '<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold border border-red-200 flex items-center gap-1 justify-center"><i class="fa-solid fa-xmark"></i> Batal</span>';
        default: return '<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold">-</span>';
    }
}

$sql_revenue = "SELECT 
                    SUM(total_amount) as total_revenue,
                    SUM(paid_amount) as total_cash_in,
                    COUNT(*) as total_orders
                FROM orders 
                WHERE status <> 'cancelled'
                AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$rev_data = ambil_satu_data($sql_revenue);
$total_revenue = $rev_data['total_revenue'] ?? 0;
$total_cash_in = $rev_data['total_cash_in'] ?? 0;
$total_trx = $rev_data['total_orders'] ?? 0;

$sql_expense = "SELECT SUM(amount) as total_expense 
                FROM expenses 
                WHERE expense_date BETWEEN '$start_date' AND '$end_date'";
$exp_data = ambil_satu_data($sql_expense);
$total_expense = $exp_data['total_expense'] ?? 0;

$net_profit = $total_cash_in - $total_expense;

$sql_chart = "SELECT 
                DATE(created_at) as tgl, 
                SUM(total_amount) as omset 
            FROM orders 
            WHERE status <> 'cancelled' 
            AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
            GROUP BY DATE(created_at)
            ORDER BY tgl ASC";
$chart_raw = ambil_banyak_data($sql_chart);

$chart_labels = [];
$chart_data = [];
$period = new DatePeriod(
     new DateTime($start_date),
     new DateInterval('P1D'),
     (new DateTime($end_date))->modify('+1 day')
);

foreach ($period as $dt) {
    $curr_date = $dt->format("Y-m-d");
    $val = 0;
    foreach($chart_raw as $cr) {
        if($cr['tgl'] == $curr_date) {
            $val = $cr['omset'];
            break;
        }
    }
    $chart_labels[] = date('d M', strtotime($curr_date)); 
    $chart_data[] = (float)$val;
}
$json_labels = json_encode($chart_labels);
$json_data   = json_encode($chart_data);

$page_ord       = isset($_GET['page_ord']) ? (int)$_GET['page_ord'] : 1;
$limit_ord_val  = isset($_GET['limit_ord']) ? $_GET['limit_ord'] : 10;

$sql_count_ord = "SELECT COUNT(*) as total FROM orders o 
                  WHERE status <> 'cancelled'
                  AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'";
$total_ord_data = ambil_satu_data($sql_count_ord)['total'];

if ($limit_ord_val == 'all') {
    $limit_ord = ($total_ord_data > 0) ? $total_ord_data : 1;
    $page_ord = 1;
} else {
    $limit_ord = (int)$limit_ord_val;
}
$total_ord_pages = ceil($total_ord_data / $limit_ord);
if ($page_ord > $total_ord_pages) $page_ord = ($total_ord_pages > 0) ? $total_ord_pages : 1;

$offset_ord = ($page_ord - 1) * $limit_ord;

$sql_table = "SELECT 
                o.id, o.order_code, o.pickup_code, o.total_amount, o.paid_amount, o.status, o.payment_status, o.created_at,
                u.full_name as customer_name
            FROM orders o
            JOIN users u ON o.customer_id = u.id
            WHERE status <> 'cancelled'
            AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
            ORDER BY o.created_at DESC
            LIMIT $limit_ord OFFSET $offset_ord";
$orders_report = ambil_banyak_data($sql_table);

$page_aud       = isset($_GET['page_aud']) ? (int)$_GET['page_aud'] : 1;
$limit_aud_val  = isset($_GET['limit_aud']) ? $_GET['limit_aud'] : 10;
$total_aud_data = 0;
$total_aud_pages = 1;
$audit_logs = [];

if (in_array($role, ['OWNER', 'ADMIN'], true)) {
    $sql_count_aud = "SELECT COUNT(*) as total FROM orders o 
                        WHERE DATE(o.updated_at) BETWEEN '$start_date' AND '$end_date'";
    $total_aud_data = ambil_satu_data($sql_count_aud)['total'] ?? 0;

    if ($limit_aud_val == 'all') {
        $limit_aud = ($total_aud_data > 0) ? $total_aud_data : 1;
        $page_aud = 1;
    } else {
        $limit_aud = (int)$limit_aud_val;
    }
    $total_aud_pages = ceil($total_aud_data / $limit_aud);
    if ($page_aud > $total_aud_pages) $page_aud = ($total_aud_pages > 0) ? $total_aud_pages : 1;

    $offset_aud = ($page_aud - 1) * $limit_aud;
    
    $sql_audit = "SELECT 
            o.id as order_id,
            o.order_code, o.pickup_code, o.total_amount, o.paid_amount,
            o.payment_status, o.status, o.created_at, o.updated_at,
            (SELECT proof_image 
             FROM order_payment_logs 
             WHERE order_id = o.id 
             AND proof_image IS NOT NULL 
             AND proof_image != ''
             ORDER BY created_at DESC LIMIT 1) as proof_image,
            cust.full_name AS customer_name,
            staff.full_name AS staff_name
        FROM orders o
        JOIN users cust ON o.customer_id = cust.id
        LEFT JOIN users staff ON o.staff_id = staff.id
        WHERE DATE(o.updated_at) BETWEEN '$start_date' AND '$end_date'
        ORDER BY o.updated_at DESC
        LIMIT $limit_aud OFFSET $offset_aud";
        
    $audit_logs = ambil_banyak_data($sql_audit);
}
?>