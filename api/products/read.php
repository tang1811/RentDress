<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : null;
$size = isset($_GET['size']) ? $_GET['size'] : null;
$color = isset($_GET['color']) ? $_GET['color'] : null;
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : 'available';
$search = isset($_GET['search']) ? $_GET['search'] : null;

// Build query - รวมรูปหลักจากตาราง product_images
$query = "SELECT p.*,
          COALESCE(AVG(r.rating), 0) as avg_rating,
          COUNT(DISTINCT r.id) as review_count,
          COALESCE(pi.image_url, p.image_url) as primary_image
          FROM products p
          LEFT JOIN reviews r ON p.id = r.product_id
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
          WHERE 1=1";
$params = [];

if ($category) {
    $query .= " AND p.category = :category";
    $params[':category'] = $category;
}

if ($size) {
    $query .= " AND p.size = :size";
    $params[':size'] = $size;
}

if ($color) {
    $query .= " AND p.color LIKE :color";
    $params[':color'] = '%' . $color . '%';
}

if ($min_price) {
    $query .= " AND p.rental_price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price) {
    $query .= " AND p.rental_price <= :max_price";
    $params[':max_price'] = $max_price;
}

if ($status && $status !== '') {
    $query .= " AND p.status = :status";
    $params[':status'] = $status;
}

if ($search) {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search2)";
    $params[':search'] = '%' . $search . '%';
    $params[':search2'] = '%' . $search . '%';
}

$query .= " GROUP BY p.id ORDER BY p.created_at DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);

    $products = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => $products,
        "count" => count($products)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
