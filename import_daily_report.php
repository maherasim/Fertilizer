<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $error = '❌ Invalid submission. Please try again.';
    } else if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error = '❌ Please upload a valid CSV file.';
    } else {
        $tmpPath = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($tmpPath, 'r')) !== false) {
            $rowNum = 0; $inserted = 0; $skipped = 0;
            $pdo->beginTransaction();
            try {
                while (($data = fgetcsv($handle)) !== false) {
                    $rowNum++;
                    if ($rowNum === 1) {
                        // Expect header: item_type,item_name,quantity,unit,total_sales,report_date,order_date
                        continue;
                    }
                    list($item_type, $item_name, $quantity, $unit, $total_sales, $report_date, $order_date) = array_map('trim', $data + array_fill(0, 7, ''));
                    if (!in_array($item_type, ['fertilizer', 'pesticide'], true)) { $skipped++; continue; }
                    if ($item_name === '' || !is_numeric($quantity) || $quantity <= 0) { $skipped++; continue; }
                    if (!in_array($unit, ['kg', 'ltr', 'gm', 'ml'], true)) { $skipped++; continue; }
                    if ($total_sales === '') { $total_sales = 0; }
                    if (!is_numeric($total_sales) || $total_sales < 0) { $skipped++; continue; }
                    $rd = DateTime::createFromFormat('Y-m-d', $report_date);
                    $od = DateTime::createFromFormat('Y-m-d', $order_date);
                    if ($rd === false || $od === false) { $skipped++; continue; }

                    $stmt = $pdo->prepare("INSERT INTO DailyReport (item_type, item_name, quantity, total_sales, unit, report_date, order_date)
                                            VALUES (:item_type, :item_name, :quantity, :total_sales, :unit, :report_date, :order_date)");
                    $stmt->execute([
                        ':item_type' => $item_type,
                        ':item_name' => $item_name,
                        ':quantity' => (float)$quantity,
                        ':total_sales' => (float)$total_sales,
                        ':unit' => $unit,
                        ':report_date' => $report_date,
                        ':order_date' => $order_date,
                    ]);
                    $inserted++;
                }
                $pdo->commit();
                $success = "✅ Imported {$inserted} records. Skipped {$skipped}.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = '❌ Import failed: ' . $e->getMessage();
            }
            fclose($handle);
        } else {
            $error = '❌ Could not read the uploaded file.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Import Daily Reports (CSV)</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background:#eef5f0; margin:0; padding:30px; }
        .container { max-width:700px; margin:auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 5px 20px rgba(0,0,0,0.1); }
        .message { margin-top: 20px; padding: 12px; border-radius: 6px; }
        .success { background:#d4edda; color:#155724; }
        .error { background:#f8d7da; color:#721c24; }
        a { color:#007bff; text-decoration:none; }
    </style>
    <script>
        function downloadSample() {
            const header = 'item_type,item_name,quantity,unit,total_sales,report_date,order_date\n';
            const sample = 'fertilizer,Urea,10,kg,5000,2025-01-01,2025-01-01\n';
            const blob = new Blob([header + sample], {type:'text/csv'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'daily_report_sample.csv'; a.click();
            URL.revokeObjectURL(url);
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Import Daily Reports (CSV)</h2>
        <?php if ($success): ?><div class="message success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <p>Upload a CSV with header: <code>item_type,item_name,quantity,unit,total_sales,report_date,order_date</code></p>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="file" name="csv_file" accept=".csv" required>
            <div style="margin-top:12px;">
                <button type="submit" style="background:#28a745; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">Import</button>
                <button type="button" onclick="downloadSample()" style="background:#1d6f42; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">Download Sample</button>
                <a href="daily_report.php" style="margin-left:8px;">Back to Daily Report</a>
            </div>
        </form>
    </div>
</body>
</html>

