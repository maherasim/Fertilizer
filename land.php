<?php
$pdo = new PDO("mysql:host=localhost;dbname=fertilizer", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $farmer = $_POST['farmer'];
    $location = $_POST['location'];
    $area = $_POST['area'];
    $stmt = $pdo->prepare("INSERT INTO Land (FarmerID, LandLocation, Area) VALUES (?, ?, ?)");
    $stmt->execute([$farmer, $location, $area]);
}

$farmerList = $pdo->query("SELECT * FROM Farmer")->fetchAll(PDO::FETCH_ASSOC);
$lands = $pdo->query("SELECT Land.*, FarmerName FROM Land JOIN Farmer ON Land.FarmerID = Farmer.FarmerID")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Land Management - AgriTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: url('https://tse1.mm.bing.net/th/id/OIP.l_JjSTH520q5O-P4uzGunQHaEK?pid=Api&P=0&h=220') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }

        .main-container {
            max-width: 900px;
            margin: 100px auto;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #2e4e1e;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            background-color: #28a745;
            border: none;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        table {
            margin-top: 25px;
        }

        .table thead {
            background-color: #28a745;
            color: #fff;
        }

        a.back-home {
            color: #28a745;
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
        }

        a.back-home:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="main-container">
    <h2>Add New Land</h2>
    <form method="POST" class="row g-3 mt-4">
        <div class="col-md-6">
            <label class="form-label">Farmer:</label>
            <select name="farmer" class="form-select" required>
                <?php foreach ($farmerList as $f): ?>
                    <option value="<?= $f['FarmerID'] ?>"><?= $f['FarmerName'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Location:</label>
            <input type="text" name="location" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Area (acres):</label>
            <input type="text" name="area" class="form-control" required>
        </div>

        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary px-4">Add Land</button>
        </div>
    </form>

    <h2 class="mt-5">All Lands</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Farmer</th>
                    <th>Location</th>
                    <th>Area (acres)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lands as $land): ?>
                    <tr>
                        <td><?= $land['LandID'] ?></td>
                        <td><?= $land['FarmerName'] ?></td>
                        <td><?= $land['LandLocation'] ?></td>
                        <td><?= $land['Area'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center">
        <a href="index.php" class="back-home">← Back to Home</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
