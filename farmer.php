<?php
// --- Database Connection ---
$host = 'localhost';
$dbname = 'fertilizer';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $address = $_POST['address'] ?? '';

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO Farmer (FarmerName, ContactNumber, Address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $contact, $address]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Management - AgriTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: url('https://tse4.mm.bing.net/th/id/OIP.UN15CcZPI_O8dj_CYmfP6gHaFi?pid=Api&P=0&h=220') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 100px auto;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.3);
        }
        h1, h2 {
            text-align: center;
            color: #2d572c;
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
        a.back-link {
            color: #28a745;
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Farmer Management</h1>
    <form method="POST" class="row g-3 mt-4">
        <div class="col-md-6">
            <label class="form-label">Farmer Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary px-4">Add Farmer</button>
        </div>
    </form>

    <h2 class="mt-5">Farmer List</h2>
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>Farmer Name</th>
                    <th>Contact Number</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM Farmer ORDER BY FarmerID DESC");
                foreach ($stmt as $row) {
                    echo "<tr>
                        <td>{$row['FarmerID']}</td>
                        <td>{$row['FarmerName']}</td>
                        <td>{$row['ContactNumber']}</td>
                        <td>{$row['Address']}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="text-center">
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
