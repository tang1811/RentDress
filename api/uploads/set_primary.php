<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->image_id)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "image_id is required"
    ]);
    exit;
}

try {
    // ดึง product_id ของรูป
    $query = "SELECT product_id FROM product_images WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $data->image_id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Image not found"
        ]);
        exit;
    }

    // รีเซ็ตรูปหลักทั้งหมดของ product นี้
    $resetQuery = "UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id";
    $resetStmt = $db->prepare($resetQuery);
    $resetStmt->execute([':product_id' => $image['product_id']]);

    // ตั้งรูปที่เลือกเป็นรูปหลัก
    $updateQuery = "UPDATE product_images SET is_primary = 1 WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([':id' => $data->image_id]);

    echo json_encode([
        "success" => true,
        "message" => "Primary image updated successfully"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
