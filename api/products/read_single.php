<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Product ID is required"
    ]);
    exit;
}

try {
    // Get product details with average rating
    $query = "SELECT p.*,
              COALESCE(AVG(r.rating), 0) as avg_rating,
              COUNT(r.id) as review_count
              FROM products p
              LEFT JOIN reviews r ON p.id = r.product_id
              WHERE p.id = :id
              GROUP BY p.id";

    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);

    $product = $stmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

    // Get all images for this product
    $imageQuery = "SELECT id, image_url, is_primary, sort_order
                   FROM product_images
                   WHERE product_id = :product_id
                   ORDER BY is_primary DESC, sort_order ASC";
    $imageStmt = $db->prepare($imageQuery);
    $imageStmt->execute([':product_id' => $id]);
    $images = $imageStmt->fetchAll();

    $product['images'] = $images;

    // Set primary image if available
    if (count($images) > 0) {
        $primaryImage = array_filter($images, fn($img) => $img['is_primary'] == 1);
        if (count($primaryImage) > 0) {
            $product['image_url'] = reset($primaryImage)['image_url'];
        } else {
            $product['image_url'] = $images[0]['image_url'];
        }
    }

    // Get reviews for this product
    $reviewQuery = "SELECT r.*, u.name as user_name
                    FROM reviews r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.product_id = :product_id
                    ORDER BY r.created_at DESC";
    $reviewStmt = $db->prepare($reviewQuery);
    $reviewStmt->execute([':product_id' => $id]);
    $reviews = $reviewStmt->fetchAll();

    $product['reviews'] = $reviews;

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => $product
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
