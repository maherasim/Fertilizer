<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $error = "❌ Invalid form submission. Please try again.";
    } else {
    $item_type = trim($_POST['item_type'] ?? '');
    $item_name = trim($_POST['item_name'] ?? '');
    $quantity = $_POST['quantity'] ?? '';
    $total_sales = $_POST['total_sales'] ?? '';
    $unit_price = $_POST['unit_price'] ?? '';
    $unit = trim($_POST['unit'] ?? '');
    $report_date = trim($_POST['report_date'] ?? '');
    $order_date = trim($_POST['order_date'] ?? '');

    // Validation
    $validTypes = ['fertilizer', 'pesticide'];
    $validUnits = ['kg', 'ltr', 'gm', 'ml'];

    $quantity = is_numeric($quantity) ? (float)$quantity : null;
    $total_sales = ($total_sales === '' ? 0 : (is_numeric($total_sales) ? (float)$total_sales : null));
    $unit_price = ($unit_price === '' ? null : (is_numeric($unit_price) ? (float)$unit_price : null));

    $reportDateValid = DateTime::createFromFormat('Y-m-d', $report_date) !== false;
    $orderDateValid = DateTime::createFromFormat('Y-m-d', $order_date) !== false;

    if (!in_array($item_type, $validTypes, true)) {
        $error = "❗ Invalid item type.";
    } elseif ($item_name === '') {
        $error = "❗ Item name is required.";
    } elseif (!in_array($unit, $validUnits, true)) {
        $error = "❗ Invalid unit selected.";
    } elseif ($quantity === null || $quantity <= 0) {
        $error = "❗ Quantity must be a positive number.";
    } elseif ($total_sales === null && $unit_price === null) {
        $error = "❗ Provide either Total sales or Unit price.";
    } elseif (!$reportDateValid || !$orderDateValid) {
        $error = "❗ Dates must be valid (YYYY-MM-DD).";
    } else {
        // Auto-calc missing field
        if ($total_sales === null && $unit_price !== null) {
            $total_sales = $unit_price * $quantity;
        }
        if ($total_sales !== null && $unit_price === null && $quantity > 0) {
            $unit_price = $total_sales / $quantity;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO DailyReport (item_type, item_name, quantity, total_sales, unit, report_date, order_date)
                                   VALUES (:item_type, :item_name, :quantity, :total_sales, :unit, :report_date, :order_date)");
            $stmt->execute([
                ':item_type' => $item_type,
                ':item_name' => $item_name,
                ':quantity' => $quantity,
                ':total_sales' => $total_sales,
                ':unit' => $unit,
                ':report_date' => $report_date,
                ':order_date' => $order_date,
            ]);
            $success = "✅ Report inserted successfully. Unit price: Rs " . number_format($unit_price, 2);
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Daily Report</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #eef5f0;
            margin: 0;
            padding: 30px;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        body {
    font-family: 'Segoe UI', sans-serif;
    background: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTv9HhJz378pHqf8bKFo3J1P9rrdVQ3ODvmig&s') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    padding: 30px;
}

        h2 {
            text-align: center;
            color: #2d4739;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .btn {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background: #28a745;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover {
            background: #218838;
        }
        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 6px;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Create Daily Report</h2>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label for="item_type">Item Type</label>
        <select name="item_type" id="item_type" required>
            <option value="">-- Select Type --</option>
            <option value="fertilizer">Fertilizer</option>
            <option value="pesticide">Pesticide</option>
        </select>

        <label for="item_name">Item Name</label>
        <input type="text" name="item_name" id="item_name" required>

        <label for="quantity">Quantity</label>
        <input type="number" step="0.01" name="quantity" id="quantity" required>

        <div style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <label for="total_sales">Total Sales (Rs)</label>
                <input type="number" step="0.01" name="total_sales" id="total_sales">
            </div>
            <div style="flex:1; min-width:200px;">
                <label for="unit_price">Unit Price (Rs)</label>
                <input type="number" step="0.01" name="unit_price" id="unit_price" placeholder="Optional; auto-calculated">
            </div>
        </div>

        <label for="unit">Unit</label>
        <select name="unit" id="unit" required>
            <option value="">-- Select Unit --</option>
            <option value="kg">kg</option>
            <option value="ltr">ltr</option>
            <option value="gm">gm</option>
            <option value="ml">ml</option>
        </select>

        <label for="report_date">Report Date</label>
        <input type="date" name="report_date" id="report_date" required>

        <label for="order_date">Order Date</label>
        <input type="date" name="order_date" id="order_date" required>

        <button type="submit" class="btn">Insert Report</button>
    </form>

    <div class="back-link">
        <a href="daily_report.php">← Back to Report</a>
    </div>
</div>

</body>
</html>
