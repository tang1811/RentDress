<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

try {
    $query = "SELECT r.*, u.name as user_name, p.name as product_name
              FROM reviews r
              JOIN users u ON r.user_id = u.id
              JOIN products p ON r.product_id = p.id
              WHERE 1=1";
    $params = [];

    if ($product_id) {
        $query .= " AND r.product_id = :product_id";
        $params[':product_id'] = $product_id;
    }

    if ($user_id) {
        $query .= " AND r.user_id = :user_id";
        $params[':user_id'] = $user_id;
    }

    $query .= " ORDER BY r.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    $reviews = $stmt->fetchAll();

    // Calculate average rating if filtering by product
    $avgRating = 0;
    if ($product_id && count($reviews) > 0) {
        $totalRating = array_sum(array_column($reviews, 'rating'));
        $avgRating = round($totalRating / count($reviews), 1);
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => $reviews,
        "count" => count($reviews),
        "avg_rating" => $avgRating
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
