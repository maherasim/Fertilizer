<?php
$pdo = new PDO("mysql:host=localhost;dbname=fertilizer", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch ALL reports (no date filter)
$stmt = $pdo->prepare("SELECT * FROM DailyReport ORDER BY report_date DESC");
$stmt->execute();
$reports = $stmt->fetchAll();

// Top fertilizer
$topFertilizerStmt = $pdo->query("SELECT item_name, SUM(total_sales) as total FROM DailyReport WHERE item_type = 'fertilizer' GROUP BY item_name ORDER BY total DESC LIMIT 1");
$topFertilizer = $topFertilizerStmt->fetch();

// Top pesticide
$topPesticideStmt = $pdo->query("SELECT item_name, SUM(total_sales) as total FROM DailyReport WHERE item_type = 'pesticide' GROUP BY item_name ORDER BY total DESC LIMIT 1");
$topPesticide = $topPesticideStmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daily Report</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: url('https://tse3.mm.bing.net/th/id/OIP.Pe1l8_ckcbE8bQ3ntdWA5gHaFj?pid=Api&P=0&h=220') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .overlay {
            min-height: 100vh;
            padding: 50px 20px;
        }
        .report-container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #28a745;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .report-header h1 {
            font-size: 26px;
            color: #2d4739;
            margin: 0;
        }
        .report-header .date {
            font-weight: 600;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead th {
            background-color: #28a745;
            color: white;
            text-align: left;
            padding: 14px 10px;
        }
        tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
            color: #333;
        }
        tbody tr:hover {
            background: #f3fdf3;
        }
        .summary {
            margin-top: 30px;
            background: #f9fff9;
            border: 1px solid #cfe8d5;
            padding: 15px;
            border-radius: 6px;
            color: #1c3b2c;
        }
        .summary h3 {
            margin-top: 0;
        }
        .total-summary {
            margin-top: 20px;
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            color: #1b3e29;
        }
        .no-data {
            text-align: center;
            color: #888;
            font-style: italic;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="overlay">
    <div class="report-container">
        <div class="report-header">
            <h1>🌾 All Sales Report</h1>
            <div class="date"><?= date('l, d M Y') ?></div>
        </div>

        <?php if (count($reports)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Total Sales (Rs)</th>
                        <th>Date</th>
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
                            <td><?= number_format($row['quantity'], 2) ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td><?= number_format($row['total_sales'], 2) ?></td>
                            <td><?= date('d-m-Y', strtotime($row['report_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-summary">
                Total Quantity: <?= number_format($totalQty, 2) ?> |
                Total Sales: Rs <?= number_format($totalSales, 2) ?>
            </div>

            <div class="summary">
                <h3>📌 Top Sales Summary</h3>
                <p><strong>Top Fertilizer:</strong> 
                    <?= $topFertilizer ? htmlspecialchars($topFertilizer['item_name']) . ' (Rs ' . number_format($topFertilizer['total'], 2) . ')' : 'N/A' ?>
                </p>
                <p><strong>Top Pesticide:</strong> 
                    <?= $topPesticide ? htmlspecialchars($topPesticide['item_name']) . ' (Rs ' . number_format($topPesticide['total'], 2) . ')' : 'N/A' ?>
                </p>
            </div>

        <?php else: ?>
            <p class="no-data">No records found in the database.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
