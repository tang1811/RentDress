<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;

if (!$productId) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "product_id is required"
    ]);
    exit;
}

try {
    $query = "SELECT id, product_id, image_url, is_primary, sort_order, created_at
              FROM product_images
              WHERE product_id = :product_id
              ORDER BY is_primary DESC, sort_order ASC";

    $stmt = $db->prepare($query);
    $stmt->execute([':product_id' => $productId]);

    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $images
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
