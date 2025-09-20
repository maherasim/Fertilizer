<?php
require_once __DIR__ . '/config.php';

$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$itemType = isset($_GET['item_type']) ? trim($_GET['item_type']) : '';

$where = [];
$params = [];
if ($startDate !== '') { $where[] = 'report_date >= :start_date'; $params[':start_date'] = $startDate; }
if ($endDate !== '') { $where[] = 'report_date <= :end_date'; $params[':end_date'] = $endDate; }
if ($itemType !== '') { $where[] = 'item_type = :item_type'; $params[':item_type'] = $itemType; }

$sql = 'SELECT * FROM DailyReport';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY report_date DESC, id DESC';
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->execute();
$rows = $stmt->fetchAll();

$sumQty = 0.0; $sumSales = 0.0;
foreach ($rows as $r) { $sumQty += (float)$r['quantity']; $sumSales += (float)$r['total_sales']; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Printable Daily Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin:0; background:#f6f8f9; }
        .container { max-width: 1000px; margin: 20px auto; background:#fff; border-radius:10px; box-shadow:0 6px 20px rgba(0,0,0,0.08); overflow:hidden; }
        .toolbar { display:flex; gap:8px; justify-content:space-between; align-items:center; padding:14px 16px; background:#e8f5ec; border-bottom:1px solid #cfe8d5; }
        .filters { display:flex; gap:8px; flex-wrap:wrap; }
        .btn { background:#28a745; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:#1d6f42; }
        .content { padding:16px; }
        h2 { margin:8px 0 16px; color:#2d4739; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
        th { background:#f3fdf3; color:#1b3e29; }
        .summary { margin-top:12px; font-weight:600; text-align:right; }
        @media print { .no-print { display:none !important; } body { background:#fff; } .container { box-shadow:none; margin:0; } }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
    <script>
        function doPrint(){ window.print(); }
        function doPdf(){
            const el = document.getElementById('printArea');
            const opt = { filename: 'daily-report.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'pt', format: 'a4', orientation: 'portrait' } };
            html2pdf().from(el).set(opt).save();
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="toolbar no-print">
            <form class="filters" method="get">
                <div>
                    <label style="display:block; font-size:12px; color:#1b3e29;">Start</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" style="padding:6px; border:1px solid #cfe8d5; border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; color:#1b3e29;">End</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" style="padding:6px; border:1px solid #cfe8d5; border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; color:#1b3e29;">Type</label>
                    <select name="item_type" style="padding:6px; border:1px solid #cfe8d5; border-radius:6px;">
                        <option value="" <?= $itemType === '' ? 'selected' : '' ?>>All</option>
                        <option value="fertilizer" <?= $itemType === 'fertilizer' ? 'selected' : '' ?>>Fertilizer</option>
                        <option value="pesticide" <?= $itemType === 'pesticide' ? 'selected' : '' ?>>Pesticide</option>
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Apply</button>
                </div>
            </form>
            <div>
                <button class="btn" onclick="doPrint()">Print</button>
                <button class="btn secondary" onclick="doPdf()">Download PDF</button>
            </div>
        </div>
        <div class="content" id="printArea">
            <h2>Daily Report</h2>
            <div style="color:#666; font-size:14px;">Generated: <?= date('d M Y H:i') ?></div>
            <table style="margin-top:10px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Customer</th>
                        <th>Qty</th>
                        <th>Unit Price (Rs)</th>
                        <th>Unit</th>
                        <th>Total (Rs)</th>
                        <th>Paid (Rs)</th>
                        <th>Balance (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): $paidAmt = isset($row['paid_amount']) ? (float)$row['paid_amount'] : 0.0; $bal = max(0, ((float)$row['total_sales']) - $paidAmt); ?>
                    <tr>
                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['report_date']))) ?></td>
                        <td><?= htmlspecialchars($row['item_type']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name'] ?? '') ?></td>
                        <td><?= number_format((float)$row['quantity'], 2) ?></td>
                        <?php $u = ((float)$row['quantity'] > 0) ? ((float)$row['total_sales'] / (float)$row['quantity']) : 0; ?>
                        <td><?= number_format($u, 2) ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                        <td><?= number_format((float)$row['total_sales'], 2) ?></td>
                        <td><?= number_format($paidAmt, 2) ?></td>
                        <td><?= number_format($bal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="summary">Total Qty: <?= number_format($sumQty, 2) ?> | Total Sales: Rs <?= number_format($sumSales, 2) ?></div>
        </div>
    </div>
</body>
</html>

