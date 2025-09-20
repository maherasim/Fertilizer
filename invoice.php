<?php
require_once __DIR__ . '/config.php';

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid invoice id';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM DailyReport WHERE id = :id');
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();
if (!$row) {
    http_response_code(404);
    echo 'Invoice not found';
    exit;
}

$quantity = (float)$row['quantity'];
$totalSales = (float)$row['total_sales'];
$unitPrice = $quantity > 0 ? $totalSales / $quantity : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= htmlspecialchars((string)$id) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin:0; background:#f6f8f9; }
        .container { max-width: 800px; margin: 30px auto; background:#fff; border-radius:10px; box-shadow:0 10px 25px rgba(0,0,0,0.08); overflow:hidden; }
        .header { display:flex; justify-content:space-between; align-items:center; padding:24px; border-bottom:4px solid #28a745; }
        .brand { font-size:22px; font-weight:700; color:#1b3e29; }
        .meta { text-align:right; color:#666; }
        .section { padding:24px; }
        .row { display:flex; gap:20px; flex-wrap:wrap; }
        .col { flex:1; min-width:240px; }
        h3 { margin:0 0 10px; color:#2d4739; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
        th { background:#f3fdf3; color:#1b3e29; }
        .total { text-align:right; padding:16px 0; font-weight:700; color:#1b3e29; }
        .footer { padding:16px 24px 24px; display:flex; justify-content:space-between; align-items:center; }
        .btn { background:#28a745; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer; text-decoration:none; }
        @media print {
            .no-print { display:none !important; }
            body { background:#fff; }
            .container { box-shadow:none; margin:0; }
        }
    </style>
    <script>
        function doPrint(){ window.print(); }
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.get('auto') === '1') {
                setTimeout(() => window.print(), 250);
            }
        })();
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">AgriTrack Store</div>
            <div class="meta">
                <div><strong>Invoice #</strong> <?= htmlspecialchars((string)$id) ?></div>
                <div><strong>Date</strong> <?= htmlspecialchars(date('d M Y', strtotime($row['order_date'] ?? $row['report_date']))) ?></div>
            </div>
        </div>

        <div class="section">
            <div class="row">
                <div class="col">
                    <h3>Bill To</h3>
                    <div><?= htmlspecialchars($row['customer_name'] ?? 'Walk-in Customer') ?></div>
                </div>
                <div class="col">
                    <h3>Details</h3>
                    <div>Type: <?= htmlspecialchars($row['item_type']) ?></div>
                    <div>Date: <?= htmlspecialchars(date('d-m-Y', strtotime($row['report_date']))) ?></div>
                </div>
            </div>
        </div>

        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Price (Rs)</th>
                        <th>Total (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= number_format($quantity, 2) ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                        <td><?= number_format($unitPrice, 2) ?></td>
                        <td><?= number_format($totalSales, 2) ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="total">Grand Total: Rs <?= number_format($totalSales, 2) ?></div>
        </div>

        <div class="footer no-print">
            <a class="btn" href="daily_report.php">Back</a>
            <button class="btn" onclick="doPrint()">Print</button>
        </div>
    </div>
</body>
</html>

