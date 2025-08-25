<?php
$pdo = new PDO("mysql:host=localhost;dbname=fertilizer", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO Planting (LandID, CropID, FertilizerID, PesticideID, PlantingDate, HarvestDate) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['land'], $_POST['crop'], $_POST['fert'], $_POST['pest'], $_POST['pdate'], $_POST['hdate']]);
}

$lands = $pdo->query("SELECT * FROM Land")->fetchAll();
$crops = $pdo->query("SELECT * FROM Crop")->fetchAll();
$ferts = $pdo->query("SELECT * FROM Fertilizer")->fetchAll();
$pests = $pdo->query("SELECT * FROM Pesticide")->fetchAll();
$plantings = $pdo->query("SELECT * FROM Planting")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Planting Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-image: url('https://www.shutterstock.com/image-photo/tractor-spraying-pesticides-on-soy-260nw-1889566384.jpg'); /* Farming background */
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            font-family: 'Segoe UI', sans-serif;
            color: #333;
        }

        .overlay {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-top: 40px;
        }

        h2 {
            color: #2c3e50;
        }

        .form-label {
            font-weight: 600;
        }

        table th {
            background-color: #28a745;
            color: white;
        }

        .btn-primary {
            background-color: #28a745;
            border: none;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        a.back-home {
            display: inline-block;
            margin-top: 20px;
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
        }

        a.back-home:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="overlay">
        <h2 class="mb-4">Add New Planting</h2>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Land</label>
                    <select class="form-select" name="land" required>
                        <?php foreach ($lands as $l): ?>
                            <option value="<?= $l['LandID'] ?>">Land <?= $l['LandID'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Crop</label>
                    <select class="form-select" name="crop" required>
                        <?php foreach ($crops as $c): ?>
                            <option value="<?= $c['CropID'] ?>"><?= $c['CropName'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Fertilizer</label>
                    <select class="form-select" name="fert" required>
                        <?php foreach ($ferts as $f): ?>
                            <option value="<?= $f['FertilizerID'] ?>"><?= $f['FertilizerName'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Pesticide</label>
                    <select class="form-select" name="pest" required>
                        <?php foreach ($pests as $p): ?>
                            <option value="<?= $p['PesticideID'] ?>"><?= $p['PesticideName'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Planting Date</label>
                    <input type="date" name="pdate" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Harvest Date</label>
                    <input type="date" name="hdate" class="form-control" required>
                </div>

                <div class="col-12">
                    <input type="submit" value="Add Planting" class="btn btn-primary w-100">
                </div>
            </div>
        </form>

        <h2 class="mt-5">All Plantings</h2>
        <div class="table-responsive">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Land</th>
                        <th>Crop</th>
                        <th>Fertilizer</th>
                        <th>Pesticide</th>
                        <th>Planting Date</th>
                        <th>Harvest Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plantings as $p): ?>
                        <tr>
                            <td><?= $p['PlantingID'] ?></td>
                            <td><?= $p['LandID'] ?></td>
                            <td><?= $p['CropID'] ?></td>
                            <td><?= $p['FertilizerID'] ?></td>
                            <td><?= $p['PesticideID'] ?></td>
                            <td><?= $p['PlantingDate'] ?></td>
                            <td><?= $p['HarvestDate'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="back-home">← Back to Home</a>
    </div>
</div>

</body>
</html>
