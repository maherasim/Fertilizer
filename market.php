<?php 
$pdo = new PDO("mysql:host=localhost;dbname=fertilizer", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop = $_POST['crop'];
    $market = $_POST['market'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $pdo->prepare("INSERT INTO Market (CropID, MarketName, PricePerKg, Location) VALUES (?, ?, ?, ?)")
        ->execute([$crop, $market, $price, $location]);
}

$crops = $pdo->query("SELECT * FROM Crop")->fetchAll();
$markets = $pdo->query("SELECT Market.*, CropName FROM Market JOIN Crop ON Market.CropID = Crop.CropID")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Market Information</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #f8fff8, #f0f4f8);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff url('bag.png') no-repeat bottom right;
            background-size: 150px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        form label {
            font-weight: 600;
            display: block;
            margin: 15px 0 5px;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input[type="submit"] {
            margin-top: 20px;
            background-color: #28a745;
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    background: url('https://www.shutterstock.com/image-photo/spraying-apple-orchard-protect-against-260nw-2128453559.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #333;
}

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            background: #fff;
        }

        th, td {
            text-align: left;
            padding: 14px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #28a745;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        a.back-home {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #28a745;
            font-weight: 600;
        }

        a.back-home:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                background-size: 100px;
            }

            input[type="submit"] {
                width: 100%;
            }

            th, td {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Market</h2>
    <form method="POST">
        <label for="crop">Crop:</label>
        <select name="crop" id="crop" required>
            <?php foreach ($crops as $c): ?>
                <option value="<?= $c['CropID'] ?>"><?= $c['CropName'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="market">Market Name:</label>
        <input name="market" id="market" type="text" required>

        <label for="price">Price/Kg </label>
        <input name="price" id="price" type="number" step="0.01" required>

        <label for="location">Location:</label>
        <input name="location" id="location" type="text" required>

        <input type="submit" value="Add Market">
    </form>

    <h2>Markets</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Crop</th>
            <th>Market</th>
            <th>Price/Kg</th>
            <th>Location</th>
        </tr>
        <?php foreach ($markets as $m): ?>
            <tr>
                <td><?= $m['MarketID'] ?></td>
                <td><?= $m['CropName'] ?></td>
                <td><?= $m['MarketName'] ?></td>
                <td>₹<?= number_format($m['PricePerKg'], 2) ?></td>
                <td><?= $m['Location'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="index.php" class="back-home">← Back to Home</a>
</div>

</body>
</html>
