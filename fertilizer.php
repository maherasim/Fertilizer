<?php
$pdo = new PDO("mysql:host=localhost;dbname=fertilizer", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $method = $_POST['method'];
    $pdo->prepare("INSERT INTO Fertilizer (FertilizerName, Type, ApplicationMethod) VALUES (?, ?, ?)")
        ->execute([$name, $type, $method]);
}

$items = $pdo->query("SELECT * FROM Fertilizer")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fertilizer Management</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('https://tse4.mm.bing.net/th/id/OIP.Yo77jJWNFtlnxlf0-zUgaQHaD4?pid=Api&P=0&h=220') no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
           
            min-height: 100vh;
            padding: 50px 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .form-section {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            align-items: center;
            margin-bottom: 40px;
        }

        .form-section img {
            max-width: 300px;
            border-radius: 10px;
        }

        form {
            flex: 1;
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
            margin-top: 15px;
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
            .form-section {
                flex-direction: column;
                text-align: center;
            }

            .form-section img {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="overlay">
    <div class="container">
        <h2>Add Fertilizer</h2>
        <div class="form-section">
            <img src="https://cdn.pixabay.com/photo/2021/06/18/12/22/fertilizer-6345174_1280.png" alt="Fertilizer Bag">
            <form method="POST">
                <label>Name:</label>
                <input type="text" name="name" required>

                <label>Type:</label>
                <input type="text" name="type" required>

                <label>Application Method:</label>
                <input type="text" name="method" required>

                <input type="submit" value="Add Fertilizer">
            </form>
        </div>

        <h2>Fertilizer List</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Type</th><th>Method</th></tr>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td><?= htmlspecialchars($i['FertilizerID']) ?></td>
                    <td><?= htmlspecialchars($i['FertilizerName']) ?></td>
                    <td><?= htmlspecialchars($i['Type']) ?></td>
                    <td><?= htmlspecialchars($i['ApplicationMethod']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <a href="index.php" class="back-home">← Back to Home</a>
    </div>
</div>
</body>
</html>
