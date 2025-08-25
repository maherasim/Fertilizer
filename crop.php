<?php
$pdo = new PDO("mysql:host=localhost;dbname=fertilizer", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $season = $_POST['season'];
    $pdo->prepare("INSERT INTO Crop (CropName, CropType, Season) VALUES (?, ?, ?)")
        ->execute([$name, $type, $season]);
}

$crops = $pdo->query("SELECT * FROM Crop")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Crop Management</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-image: url('https://cropconsult.com.au/wp-content/uploads/2017/05/cropped-crop-background-02.jpg');
            background-size: cover;
            background-attachment: fixed;
            color: #333;
        }

        .overlay {
            
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 30px;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        form div {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 600;
            margin-bottom: 6px;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        input[type="submit"] {
            grid-column: span 2;
            background-color: #43a047;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #2e7d32;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 14px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #2e7d32;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f4fdf4;
        }

        .crop-img {
            width: 28px;
            vertical-align: middle;
            margin-right: 8px;
        }

        a.back-home {
            display: inline-block;
            margin-top: 25px;
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
        }

        a.back-home:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }

            input[type="submit"] {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
<div class="overlay">
    <div class="container">
        <h2>🌾 Add New Crop</h2>
        <form method="POST">
            <div>
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>

            <div>
                <label>Type:</label>
                <input type="text" name="type" required>
            </div>

            <div>
                <label>Season:</label>
                <input type="text" name="season" required>
            </div>

            <input type="submit" value="➕ Add Crop">
        </form>

        <h2>🌱 All Crops</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Season</th>
            </tr>
            <?php foreach ($crops as $c): ?>
                <tr>
                    <td><?= $c['CropID'] ?></td>
                    <td><img src="https://cdn-icons-png.flaticon.com/512/765/765449.png" alt="Bag" class="crop-img"><?= htmlspecialchars($c['CropName']) ?></td>
                    <td><?= htmlspecialchars($c['CropType']) ?></td>
                    <td><?= htmlspecialchars($c['Season']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <a href="index.php" class="back-home">← Back to Home</a>
    </div>
</div>
</body>
</html>
