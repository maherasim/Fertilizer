<?php
$pdo = new PDO("mysql:host=localhost;dbname=fertilizer", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $method = $_POST['method'];
    $pdo->prepare("INSERT INTO Pesticide (PesticideName, Type, ApplicationMethod) VALUES (?, ?, ?)")
        ->execute([$name, $type, $method]);
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

            <input type="submit" value="Add Pesticide">
        </form>

        <h2>All Pesticides</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Type</th><th>Method</th></tr>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td><?= $i['PesticideID'] ?></td>
                    <td><?= $i['PesticideName'] ?></td>
                    <td><?= $i['Type'] ?></td>
                    <td><?= $i['ApplicationMethod'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <a href="index.php" class="back-home">← Back to Home</a>
    </div>
</div>
</body>
</html>
