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
    $paid_amount = $_POST['paid_amount'] ?? '';
    $payment_status = trim($_POST['payment_status'] ?? '');

    // No restrictions/validations: coerce numeric fields only
    $quantity = is_numeric($quantity) ? (float)$quantity : 0.0;
    $total_sales = ($total_sales === '' ? 0.0 : (is_numeric($total_sales) ? (float)$total_sales : 0.0));
    $unit_price = ($unit_price === '' ? null : (is_numeric($unit_price) ? (float)$unit_price : null));
    $paid_amount = ($paid_amount === '' ? 0.0 : (is_numeric($paid_amount) ? (float)$paid_amount : 0.0));

    {
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

            // Use DB name for consistency; leave unit as user-selected
            $item_name = $itemRow['name'];

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

            // Build dynamic insert aligned with detected columns (use positional placeholders to avoid HY093)
            $columns = ['item_type', 'item_name', 'customer_name', 'quantity', 'total_sales', 'unit', 'report_date', 'order_date'];
            if ($hasItemId) {
                array_splice($columns, 1, 0, 'item_id'); // after item_type
            }
            // Detect optional payment columns
            $hasPaid = false; $hasStatus = false;
            try {
                $dbNameStmt2 = $pdo->query('SELECT DATABASE()');
                $currentDb2 = (string)$dbNameStmt2->fetchColumn();
                $meta2 = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'DailyReport' AND COLUMN_NAME IN ('paid_amount','payment_status')");
                $meta2->execute([':db' => $currentDb2]);
                $cols = $meta2->fetchAll(PDO::FETCH_COLUMN);
                $hasPaid = in_array('paid_amount', $cols, true);
                $hasStatus = in_array('payment_status', $cols, true);
            } catch (Throwable $e) { /* ignore */ }
            if ($hasPaid) { $columns[] = 'paid_amount'; }
            if ($hasStatus) { $columns[] = 'payment_status'; }
            $placeholders = array_fill(0, count($columns), '?');
            $values = [];
            foreach ($columns as $c) {
                switch ($c) {
                    case 'item_id': $values[] = $item_id; break;
                    case 'item_type': $values[] = $item_type; break;
                    case 'item_name': $values[] = $item_name; break;
                    case 'customer_name': $values[] = $customer_name; break;
                    case 'quantity': $values[] = $quantity; break;
                    case 'total_sales': $values[] = $total_sales; break;
                    case 'unit': $values[] = $unit; break;
                    case 'report_date': $values[] = $report_date; break;
                    case 'order_date': $values[] = $order_date; break;
                    case 'paid_amount': $values[] = $paid_amount; break;
                    case 'payment_status': $values[] = $payment_status; break;
                    default: $values[] = null; break;
                }
            }
            $sql = 'INSERT INTO DailyReport (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            // Deduct stock without restriction (may go negative)
            if ($item_type === 'fertilizer') {
                $upd = $pdo->prepare("UPDATE Fertilizer SET StockQuantity = COALESCE(StockQuantity,0) - :q WHERE FertilizerID = :id");
            } else {
                $upd = $pdo->prepare("UPDATE Pesticide SET StockQuantity = COALESCE(StockQuantity,0) - :q WHERE PesticideID = :id");
            }
            $upd->execute([':q' => $quantity, ':id' => $item_id]);

            $newId = (int)$pdo->lastInsertId();
            if ($newId <= 0) {
                try {
                    $idStmt = $pdo->query('SELECT MAX(id) FROM DailyReport');
                    $newId = (int)$idStmt->fetchColumn();
                } catch (Throwable $e) { /* ignore */ }
            }
            $pdo->commit();
            $invoiceId = $newId > 0 ? $newId : 0;
            $remaining = max(0, ($total_sales ?? 0) - ($paid_amount ?? 0));
            $remainingText = ($payment_status === 'partial' || $remaining > 0)
                ? " — Remaining: Rs " . number_format($remaining, 2)
                : '';
            $success = "✅ Report inserted and stock updated. Unit price: Rs " . number_format($unit_price ?? 0, 2) . $remainingText .
                        "<script>(function(){try{var a=document.createElement('a');a.href='invoice.php?id=" . $invoiceId . "&download=1';a.download='invoice-" . $invoiceId . ".html';document.body.appendChild(a);a.click();a.remove();}catch(e){console.error(e);}})();</script>";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = "❌ Error: " . $e->getMessage();
        }
    }
    }
}
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="card-agri">
  <div class="card-header">Create Daily Report</div>
  <div class="card-body">

    <?php if ($success): ?>
        <div class="message success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" id="reportForm" class="form-grid">
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
        <div id="stockInfo" class="hint"></div>

        <label for="quantity">Quantity</label>
        <input type="number" step="0.01" name="quantity" id="quantity" required>

        <label for="unit_price">Sale Amount (Rs)</label>
        <input type="number" step="0.01" name="unit_price" id="unit_price" placeholder="Auto-loaded" readonly>

        <label for="total_sales">Total Sales (Rs)</label>
        <input type="number" step="0.01" name="total_sales" id="total_sales">

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

        <label for="paid_amount">Paid Amount (Rs)</label>
        <input type="number" step="0.01" name="paid_amount" id="paid_amount" placeholder="0.00">

        <label for="payment_status">Payment Status</label>
        <select name="payment_status" id="payment_status">
            <option value="">-- Select --</option>
            <option value="paid">Paid</option>
            <option value="partial">Partial</option>
            <option value="unpaid">Unpaid</option>
        </select>

        <div style="grid-column: 1 / -1;">
            <button type="submit" class="btn-agri" style="width:100%;">Insert Report</button>
        </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
<script>
// Dependent dropdowns and live stock display
(function(){
    const typeEl = document.getElementById('item_type');
    const itemEl = document.getElementById('item_id');
    const nameEl = document.getElementById('item_name');
    const unitEl = document.getElementById('unit');
    const qtyEl = document.getElementById('quantity');
    const stockInfo = document.getElementById('stockInfo');
    const unitPriceEl = document.getElementById('unit_price');
    const totalSalesEl = document.getElementById('total_sales');
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
        // keep unit free for user input
    }

    typeEl.addEventListener('change', async function(){
        clearItems();
        // Clear price and totals when type changes
        unitPriceEl.value = '';
        totalSalesEl.value = '';
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
                if (typeof it.sale_price !== 'undefined' && it.sale_price !== null) {
                    o.dataset.price = it.sale_price;
                }
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
        stockInfo.textContent = opt && opt.value ? ('Available stock: ' + stock.toFixed(2) + ' ' + (unit || '')) : '';
        // Autofill unit price from item sale price, if available
        const price = opt && opt.dataset.price ? Number(opt.dataset.price) : null;
        if (price !== null && !Number.isNaN(price)) {
            unitPriceEl.value = price.toFixed(2);
            // Recompute total on selection
            const q = Number(qtyEl.value || 0);
            if (q > 0) totalSalesEl.value = (q * price).toFixed(2);
        }
    });

    // Auto-calc total when quantity or unit price changes
    function recalc(){
        const q = Number(qtyEl.value || 0);
        const p = Number(unitPriceEl.value || 0);
        if (!Number.isNaN(q) && !Number.isNaN(p)) {
            totalSalesEl.value = (q * p).toFixed(2);
        }
    }
    qtyEl.addEventListener('input', recalc);
    unitPriceEl.addEventListener('input', recalc);
})();
</script>

