<?php
require_once __DIR__ . '/config.php';

// Filters
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$itemType = isset($_GET['item_type']) ? trim($_GET['item_type']) : '';
$export = isset($_GET['export']) ? $_GET['export'] : '';

$validTypes = ['', 'fertilizer', 'pesticide'];
if (!in_array($itemType, $validTypes, true)) {
    $itemType = '';
}

// Build WHERE clause
$where = [];
$params = [];

if ($startDate !== '') {
    $where[] = 'report_date >= :start_date';
    $params[':start_date'] = $startDate;
}
if ($endDate !== '') {
    $where[] = 'report_date <= :end_date';
    $params[':end_date'] = $endDate;
}
if ($itemType !== '') {
    $where[] = 'item_type = :item_type';
    $params[':item_type'] = $itemType;
}

$sqlBase = 'FROM DailyReport';
if ($where) {
    $sqlBase .= ' WHERE ' . implode(' AND ', $where);
}

// Fetch filtered reports
// Pagination
$page = isset($_GET['page']) && ctype_digit($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize = 20;
$offset = ($page - 1) * $pageSize;

// Count total
$countStmt = $pdo->prepare('SELECT COUNT(*) ' . $sqlBase);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $pageSize));

$sql = 'SELECT * ' . $sqlBase . ' ORDER BY report_date DESC, id DESC LIMIT :limit OFFSET :offset';
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reports = $stmt->fetchAll();

// CSV export
if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="daily_report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Type', 'Item', 'Quantity', 'Unit', 'Total Sales (Rs)', 'Report Date', 'Order Date']);
    foreach ($reports as $row) {
        fputcsv($out, [
            $row['item_type'],
            $row['item_name'],
            number_format((float)$row['quantity'], 2, '.', ''),
            $row['unit'],
            number_format((float)$row['total_sales'], 2, '.', ''),
            $row['report_date'],
            $row['order_date'],
        ]);
    }
    fclose($out);
    exit;
}

// Top fertilizer and pesticide within date range (ignores item_type filter to always show both)
$dateWhere = [];
$dateParams = [];
if ($startDate !== '') {
    $dateWhere[] = 'report_date >= :start_date';
    $dateParams[':start_date'] = $startDate;
}
if ($endDate !== '') {
    $dateWhere[] = 'report_date <= :end_date';
    $dateParams[':end_date'] = $endDate;
}

$fertSql = "SELECT item_name, SUM(total_sales) as total FROM DailyReport WHERE item_type = 'fertilizer'";
if ($dateWhere) {
    $fertSql .= ' AND ' . implode(' AND ', $dateWhere);
}
$fertSql .= ' GROUP BY item_name ORDER BY total DESC LIMIT 1';
$topFertilizerStmt = $pdo->prepare($fertSql);
$topFertilizerStmt->execute($dateParams);
$topFertilizer = $topFertilizerStmt->fetch();

$pestSql = "SELECT item_name, SUM(total_sales) as total FROM DailyReport WHERE item_type = 'pesticide'";
if ($dateWhere) {
    $pestSql .= ' AND ' . implode(' AND ', $dateWhere);
}
$pestSql .= ' GROUP BY item_name ORDER BY total DESC LIMIT 1';
$topPesticideStmt = $pdo->prepare($pestSql);
$topPesticideStmt->execute($dateParams);
$topPesticide = $topPesticideStmt->fetch();
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="card-agri">
  <div class="card-header">All Sales Report</div>
  <div class="card-body">

        <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            <div>
                <label for="start_date" style="display:block; font-weight:600; color:#2d4739;">Start date</label>
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" style="padding:8px; border:1px solid #ccc; border-radius:6px;">
            </div>
            <div>
                <label for="end_date" style="display:block; font-weight:600; color:#2d4739;">End date</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" style="padding:8px; border:1px solid #ccc; border-radius:6px;">
            </div>
            <div>
                <label for="item_type" style="display:block; font-weight:600; color:#2d4739;">Type</label>
                <select id="item_type" name="item_type" style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                    <option value="" <?= $itemType === '' ? 'selected' : '' ?>>All</option>
                    <option value="fertilizer" <?= $itemType === 'fertilizer' ? 'selected' : '' ?>>Fertilizer</option>
                    <option value="pesticide" <?= $itemType === 'pesticide' ? 'selected' : '' ?>>Pesticide</option>
                </select>
            </div>
            <div>
                <button type="submit" style="background:#28a745; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">Filter</button>
            </div>
            <div>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" style="background:#1d6f42; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none;">Export CSV</a>
            </div>
            <div>
                <?php $printUrl = 'daily_report_print.php?' . http_build_query(['start_date' => $startDate, 'end_date' => $endDate, 'item_type' => $itemType]); ?>
                <a href="<?= $printUrl ?>" style="background:#0b5ed7; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none;">Print / PDF</a>
            </div>
        </form>

        <?php if (count($reports)): ?>
            <div class="summary" style="margin-top:10px;">
                <?php
                $sumQty = 0.0; $sumSales = 0.0; $countRows = 0;
                foreach ($reports as $r) { $sumQty += (float)$r['quantity']; $sumSales += (float)$r['total_sales']; $countRows++; }
                $avgUnitPrice = ($sumQty > 0) ? ($sumSales / $sumQty) : 0;
                ?>
                <p><strong>Records:</strong> <?= number_format($countRows) ?>,
                   <strong>Total Qty:</strong> <?= number_format($sumQty, 2) ?>,
                   <strong>Total Sales:</strong> Rs <?= number_format($sumSales, 2) ?>,
                   <strong>Avg Unit Price:</strong> Rs <?= number_format($avgUnitPrice, 2) ?></p>
            </div>
            <table class="table-agri">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Customer</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Total Sales (Rs)</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Date</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalQty = 0;
                    $totalSales = 0;
                    foreach ($reports as $row): 
                        $totalQty += $row['quantity'];
                        $totalSales += $row['total_sales'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['item_type']) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['customer_name'] ?? '') ?></td>
                            <td><?= number_format($row['quantity'], 2) ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td><?= number_format($row['total_sales'], 2) ?></td>
                            <?php $paidAmt = isset($row['paid_amount']) ? (float)$row['paid_amount'] : 0.0; $bal = max(0, ((float)$row['total_sales']) - $paidAmt); ?>
                            <td><?= number_format($paidAmt, 2) ?></td>
                            <td><?= number_format($bal, 2) ?></td>
                            <td><?= date('d-m-Y', strtotime($row['report_date'])) ?></td>
                            <td><a href="invoice.php?id=<?= urlencode((string)$row['id']) ?>" style="color:#007bff; text-decoration:none;">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-summary">
                Total Quantity: <?= number_format($totalQty, 2) ?> |
                Total Sales: Rs <?= number_format($totalSales, 2) ?> |
                Page <?= $page ?> of <?= $totalPages ?> (<?= number_format($totalRows) ?> rows)
            </div>

            <div class="summary">
                <h3>📌 Top Sales Summary</h3>
                <p><strong>Top Fertilizer:</strong> 
                    <?= $topFertilizer ? htmlspecialchars($topFertilizer['item_name']) . ' (Rs ' . number_format($topFertilizer['total'], 2) . ')' : 'N/A' ?>
                </p>
                <p><strong>Top Pesticide:</strong> 
                    <?= $topPesticide ? htmlspecialchars($topPesticide['item_name']) . ' (Rs ' . number_format($topPesticide['total'], 2) . ')' : 'N/A' ?>
                </p>
                <canvas id="salesChart" width="400" height="160" style="margin-top:10px;"></canvas>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:10px;">
                <?php
                $baseQuery = $_GET; unset($baseQuery['page']);
                $buildUrl = function($p) use ($baseQuery){ return '?' . http_build_query(array_merge($baseQuery, ['page' => $p])); };
                ?>
                <?php if ($page > 1): ?>
                    <a href="<?= $buildUrl($page - 1) ?>" style="padding:8px 12px; background:#e8f5ec; border:1px solid #cfe8d5; border-radius:6px; text-decoration:none; color:#1c3b2c;">Prev</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= $buildUrl($page + 1) ?>" style="padding:8px 12px; background:#e8f5ec; border:1px solid #cfe8d5; border-radius:6px; text-decoration:none; color:#1c3b2c;">Next</a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <p class="no-data">No records found in the database.</p>
        <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Prepare small chart data grouped by date (server-rendered dataset)
(function(){
    const rows = <?= json_encode(array_map(function($row){
        return [
            'date' => date('Y-m-d', strtotime($row['report_date'])),
            'sales' => (float)$row['total_sales']
        ];
    }, $reports)) ?>;
    const map = new Map();
    rows.forEach(r => { map.set(r.date, (map.get(r.date) || 0) + r.sales); });
    const labels = Array.from(map.keys()).sort();
    const data = labels.map(d => map.get(d));
    const ctx = document.getElementById('salesChart');
    if (ctx && labels.length) {
        new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ label: 'Total Sales (Rs)', data, borderColor: '#28a745', tension: 0.2 }] },
            options: { plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true } } }
        });
    }
})();
</script>
<?php include __DIR__ . '/footer.php'; ?>
