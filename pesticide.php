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

<!DOCTYPE html>
<html>
<head>
    <title>Pesticide Management</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-image: url('https://www.shutterstock.com/image-photo/rear-view-male-farmer-spraying-260nw-2358307271.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .overlay {
            
            min-height: 100vh;
            padding: 50px 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .bag-image {
            position: absolute;
            top: -40px;
            right: -40px;
            width: 120px;
            z-index: 10;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        form {
            margin-bottom: 40px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        input[type="submit"] {
            background: #28a745;
            color: white;
            padding: 12px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 14px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        a.back-home {
            display: inline-block;
            margin-top: 25px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        a.back-home:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .bag-image {
                width: 80px;
                top: -20px;
                right: -20px;
            }

            input[type="submit"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="overlay">
    <div class="container">
        <img src="https://cdn-icons-png.flaticon.com/512/3144/3144456.png" alt="Fertilizer Bag" class="bag-image">
        <h2>Add New Pesticide</h2>
        <form method="POST">
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

            <input type="submit" value="Add Pesticide">
        </form>

        <h2>All Pesticides</h2>
        <table>
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

        <a href="index.php" class="back-home">← Back to Home</a>
    </div>
</div>
</body>
</html>
