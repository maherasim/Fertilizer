<?php
$pdo = new PDO("mysql:host=localhost;dbname=fertilizer", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $method = trim($_POST['method'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $stock = $_POST['stock_quantity'] ?? '0';
    $purchase = $_POST['purchase_price'] ?? '';
    $sale = $_POST['sale_price'] ?? '';

    $validUnits = ['kg','ltr','gm','ml'];
    $stock = is_numeric($stock) ? max(0, (float)$stock) : 0.0;
    if (!in_array($unit, $validUnits, true)) { $unit = ''; }

    try {
        $stmt = $pdo->prepare("INSERT INTO Pesticide (PesticideName, Type, ApplicationMethod, StockQuantity, Unit, PurchasePrice, SalePrice) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $type, $method, $stock, $unit, ($purchase === '' ? null : (float)$purchase), ($sale === '' ? null : (float)$sale)]);
    } catch (Throwable $e) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Pesticide (PesticideName, Type, ApplicationMethod, StockQuantity, Unit) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $type, $method, $stock, $unit]);
        } catch (Throwable $e2) {
            $stmt = $pdo->prepare("INSERT INTO Pesticide (PesticideName, Type, ApplicationMethod) VALUES (?, ?, ?)");
            $stmt->execute([$name, $type, $method]);
        }
    }
}

$items = $pdo->query("SELECT * FROM Pesticide")->fetchAll();
?>

<?php include __DIR__ . '/header.php'; ?>
<div class="card-agri">
  <div class="card-header">Add New Pesticide</div>
  <div class="card-body">
        <form method="POST" class="form-grid">
            <label>Name:</label>
            <input name="name" type="text" required>

            <label>Type:</label>
            <input name="type" type="text" required>

            <label>Application Method:</label>
            <input name="method" type="text" required>

            <label>Unit:</label>
            <select name="unit">
                <option value="">-- Select Unit --</option>
                <option value="kg">kg</option>
                <option value="ltr">ltr</option>
                <option value="gm">gm</option>
                <option value="ml">ml</option>
            </select>

            <label>Initial Stock Quantity:</label>
            <input type="text" name="stock_quantity" placeholder="e.g., 100.00">

            <label>Purchase Price (Rs):</label>
            <input type="text" name="purchase_price" placeholder="e.g., 40000">

            <label>Sale Price (Rs):</label>
            <input type="text" name="sale_price" placeholder="e.g., 43000">

            <div style="grid-column: 1 / -1;">
                <button class="btn-agri" type="submit" style="width:100%;">Add Pesticide</button>
            </div>
        </form>

        <h2 style="margin-top:20px; color:#224c38;">All Pesticides</h2>
        <table class="table-agri">
            <tr><th>ID</th><th>Name</th><th>Type</th><th>Method</th><th>Unit</th><th>Stock</th><th>Purchase</th><th>Sale</th><th>Profit/Unit</th></tr>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td><?= $i['PesticideID'] ?></td>
                    <td><?= $i['PesticideName'] ?></td>
                    <td><?= $i['Type'] ?></td>
                    <td><?= $i['ApplicationMethod'] ?></td>
                    <td><?= htmlspecialchars($i['Unit'] ?? '') ?></td>
                    <td><?= htmlspecialchars(number_format((float)($i['StockQuantity'] ?? 0), 2)) ?></td>
                    <td><?= isset($i['PurchasePrice']) ? number_format((float)$i['PurchasePrice'], 2) : '-' ?></td>
                    <td><?= isset($i['SalePrice']) ? number_format((float)$i['SalePrice'], 2) : '-' ?></td>
                    <?php $p = isset($i['PurchasePrice']) ? (float)$i['PurchasePrice'] : null; $s = isset($i['SalePrice']) ? (float)$i['SalePrice'] : null; $profit = ($p !== null && $s !== null) ? ($s - $p) : null; ?>
                    <td><?= $profit !== null ? number_format($profit, 2) : '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
