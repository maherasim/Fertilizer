<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$validTypes = ['fertilizer', 'pesticide'];
if (!in_array($type, $validTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

try {
    if ($type === 'fertilizer') {
        // Columns StockQuantity, Unit, SalePrice are optional; default accordingly.
        $stmt = $pdo->query("SELECT FertilizerID AS id, FertilizerName AS name, 
                                    COALESCE(StockQuantity, 0) AS stock_quantity, 
                                    COALESCE(Unit, '') AS unit,
                                    SalePrice AS sale_price
                             FROM Fertilizer ORDER BY FertilizerName ASC");
    } else {
        $stmt = $pdo->query("SELECT PesticideID AS id, PesticideName AS name, 
                                    COALESCE(StockQuantity, 0) AS stock_quantity, 
                                    COALESCE(Unit, '') AS unit,
                                    SalePrice AS sale_price
                             FROM Pesticide ORDER BY PesticideName ASC");
    }
    $items = $stmt->fetchAll();
    echo json_encode(['items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}

