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
    $item_id = isset($_POST['item_id']) && ctype_digit((string)$_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $item_name = trim($_POST['item_name'] ?? ''); // may be auto-filled from DB
    $customer_name = trim($_POST['customer_name'] ?? '');
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
    } elseif ($item_id <= 0) {
        $error = "❗ Please select a valid item.";
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
            $pdo->beginTransaction();

            // Fetch selected item to validate stock and get authoritative name/unit
            if ($item_type === 'fertilizer') {
                $it = $pdo->prepare("SELECT FertilizerID AS id, FertilizerName AS name, COALESCE(StockQuantity,0) AS stock_quantity, COALESCE(Unit,'') AS db_unit FROM Fertilizer WHERE FertilizerID = :id");
            } else {
                $it = $pdo->prepare("SELECT PesticideID AS id, PesticideName AS name, COALESCE(StockQuantity,0) AS stock_quantity, COALESCE(Unit,'') AS db_unit FROM Pesticide WHERE PesticideID = :id");
            }
            $it->execute([':id' => $item_id]);
            $itemRow = $it->fetch();
            if (!$itemRow) {
                throw new Exception('Selected item not found.');
            }

            // Use DB unit and name to avoid tampering
            $item_name = $itemRow['name'];
            $dbUnit = $itemRow['db_unit'];
            if ($dbUnit && in_array($dbUnit, $validUnits, true)) {
                $unit = $dbUnit;
            }
            if (!in_array($unit, $validUnits, true)) {
                throw new Exception('Item has invalid unit configuration.');
            }
            if ($quantity > (float)$itemRow['stock_quantity']) {
                throw new Exception('Insufficient stock. Available: ' . number_format((float)$itemRow['stock_quantity'], 2));
            }

            // Optional item_id column handling (robust INFORMATION_SCHEMA check)
            $hasItemId = false;
            try {
                // Infer current DB name safely from PDO if available
                $dbNameStmt = $pdo->query('SELECT DATABASE()');
                $currentDb = (string)$dbNameStmt->fetchColumn();
                $meta = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'DailyReport' AND COLUMN_NAME = 'item_id'");
                $meta->execute([':db' => $currentDb]);
                $hasItemId = ((int)$meta->fetchColumn() > 0);
            } catch (Throwable $e) { /* ignore and assume false */ }

            // Build dynamic insert aligned with detected columns
            $columns = ['item_type', 'item_name', 'customer_name', 'quantity', 'total_sales', 'unit', 'report_date', 'order_date'];
            $params = [
                ':item_type' => $item_type,
                ':item_name' => $item_name,
                ':customer_name' => $customer_name,
                ':quantity' => $quantity,
                ':total_sales' => $total_sales,
                ':unit' => $unit,
                ':report_date' => $report_date,
                ':order_date' => $order_date,
            ];
            if ($hasItemId) {
                array_splice($columns, 1, 0, 'item_id'); // after item_type
                $params[':item_id'] = $item_id;
            }
            $placeholders = array_map(function($c){ return ':' . $c; }, $columns);
            // Map our params to the computed placeholders
            $execParams = [];
            foreach ($columns as $c) {
                $key = ':' . $c;
                if (!array_key_exists($key, $params)) {
                    // Fill from known variables if missing
                    switch ($c) {
                        case 'item_id': $execParams[$key] = $item_id; break;
                        case 'item_type': $execParams[$key] = $item_type; break;
                        case 'item_name': $execParams[$key] = $item_name; break;
                        case 'customer_name': $execParams[$key] = $customer_name; break;
                        case 'quantity': $execParams[$key] = $quantity; break;
                        case 'total_sales': $execParams[$key] = $total_sales; break;
                        case 'unit': $execParams[$key] = $unit; break;
                        case 'report_date': $execParams[$key] = $report_date; break;
                        case 'order_date': $execParams[$key] = $order_date; break;
                        default: $execParams[$key] = null; break;
                    }
                } else {
                    $execParams[$key] = $params[$key];
                }
            }
            $sql = 'INSERT INTO DailyReport (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($execParams);

            // Deduct stock atomically, prevent negative
            if ($item_type === 'fertilizer') {
                $upd = $pdo->prepare("UPDATE Fertilizer SET StockQuantity = StockQuantity - :q WHERE FertilizerID = :id AND StockQuantity >= :q");
            } else {
                $upd = $pdo->prepare("UPDATE Pesticide SET StockQuantity = StockQuantity - :q WHERE PesticideID = :id AND StockQuantity >= :q");
            }
            $upd->execute([':q' => $quantity, ':id' => $item_id]);
            if ($upd->rowCount() !== 1) {
                throw new Exception('Failed to update stock. Try again.');
            }

            $newId = (int)$pdo->lastInsertId();
            $pdo->commit();
            $success = "✅ Report inserted and stock updated. Unit price: Rs " . number_format($unit_price, 2) . 
                        " — <a href=\"invoice.php?id=" . $newId . "\" style=\"color:#007bff;\">View Invoice</a>";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
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

    <form method="post" id="reportForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label for="customer_name">Customer  Name</label>
        <input type="text" name="customer_name" id="customer_name" required>
        <label for="item_type">Item Type</label>
        <select name="item_type" id="item_type" required>
            <option value="">-- Select Type --</option>
            <option value="fertilizer">Fertilizer</option>
            <option value="pesticide">Pesticide</option>
        </select>

        <label for="item_id">Item</label>
        <select name="item_id" id="item_id" required disabled>
            <option value="">-- Select Item --</option>
        </select>
        <input type="hidden" name="item_name" id="item_name">
        <div id="stockInfo" style="margin-top:6px; color:#1b3e29; font-size:14px;"></div>

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
        <select name="unit" id="unit" required disabled>
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

<script>
// Dependent dropdowns and live stock display
(function(){
    const typeEl = document.getElementById('item_type');
    const itemEl = document.getElementById('item_id');
    const nameEl = document.getElementById('item_name');
    const unitEl = document.getElementById('unit');
    const qtyEl = document.getElementById('quantity');
    const stockInfo = document.getElementById('stockInfo');
    let items = [];

    function setUnit(u){
        for (const opt of unitEl.options) { opt.selected = (opt.value === u); }
    }

    function clearItems(){
        itemEl.innerHTML = '<option value="">-- Select Item --</option>';
        itemEl.disabled = true;
        stockInfo.textContent = '';
        nameEl.value = '';
        setUnit('');
        unitEl.disabled = true;
    }

    typeEl.addEventListener('change', async function(){
        clearItems();
        const t = typeEl.value;
        if (!t) return;
        try {
            const res = await fetch('api_items.php?type=' + encodeURIComponent(t));
            const data = await res.json();
            items = Array.isArray(data.items) ? data.items : [];
            items.forEach(it => {
                const o = document.createElement('option');
                o.value = String(it.id);
                o.textContent = it.name;
                o.dataset.unit = it.unit || '';
                o.dataset.stock = (typeof it.stock_quantity !== 'undefined') ? it.stock_quantity : 0;
                itemEl.appendChild(o);
            });
            itemEl.disabled = items.length === 0;
        } catch (e) {
            console.error(e);
        }
    });

    itemEl.addEventListener('change', function(){
        const opt = itemEl.options[itemEl.selectedIndex];
        const unit = opt ? (opt.dataset.unit || '') : '';
        const stock = opt ? Number(opt.dataset.stock || 0) : 0;
        nameEl.value = opt ? opt.textContent : '';
        setUnit(unit);
        unitEl.disabled = false; // keep locked value but allow form to submit
        stockInfo.textContent = opt && opt.value ? ('Available stock: ' + stock.toFixed(2) + ' ' + (unit || '')) : '';
    });

    // Client-side guard: quantity not exceed stock
    qtyEl.addEventListener('input', function(){
        const opt = itemEl.options[itemEl.selectedIndex];
        const stock = opt ? Number(opt.dataset.stock || 0) : 0;
        const q = Number(qtyEl.value || 0);
        if (q > stock && stock > 0) {
            qtyEl.setCustomValidity('Quantity exceeds available stock');
        } else {
            qtyEl.setCustomValidity('');
        }
    });
})();
</script>

</body>
</html>
