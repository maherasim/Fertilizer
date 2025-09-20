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
    // Detect if SalePrice column exists to avoid SQL errors on older schemas
    $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $hasSalePrice = false;
    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND COLUMN_NAME = 'SalePrice' AND TABLE_NAME IN ('Fertilizer','Pesticide')");
        $chk->execute([':db' => $db]);
        $hasSalePrice = ((int)$chk->fetchColumn() > 0);
    } catch (Throwable $e) { $hasSalePrice = false; }

    if ($type === 'fertilizer') {
        $stmt = $pdo->query("SELECT FertilizerID AS id, FertilizerName AS name, 
                                    COALESCE(StockQuantity, 0) AS stock_quantity, 
                                    COALESCE(Unit, '') AS unit" .
                                    ($hasSalePrice ? ", SalePrice AS sale_price" : "") .
                             " FROM Fertilizer ORDER BY FertilizerName ASC");
    } else {
        $stmt = $pdo->query("SELECT PesticideID AS id, PesticideName AS name, 
                                    COALESCE(StockQuantity, 0) AS stock_quantity, 
                                    COALESCE(Unit, '') AS unit" .
                                    ($hasSalePrice ? ", SalePrice AS sale_price" : "") .
                             " FROM Pesticide ORDER BY PesticideName ASC");
    }
    $items = $stmt->fetchAll();
    echo json_encode(['items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}

