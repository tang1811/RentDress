<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$imageId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$imageId) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Image id is required"
    ]);
    exit;
}

try {
    // ดึงข้อมูลรูปก่อนลบ
    $query = "SELECT image_url, product_id, is_primary FROM product_images WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Image not found"
        ]);
        exit;
    }

    // ลบไฟล์จริง
    $filePath = '../../' . ltrim($image['image_url'], '/');
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // ลบจากฐานข้อมูล
    $deleteQuery = "DELETE FROM product_images WHERE id = :id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->execute([':id' => $imageId]);

    // ถ้าลบรูปหลัก ให้ตั้งรูปถัดไปเป็นรูปหลัก
    if ($image['is_primary'] == 1) {
        $updateQuery = "UPDATE product_images
                        SET is_primary = 1
                        WHERE product_id = :product_id
                        ORDER BY sort_order ASC
                        LIMIT 1";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([':product_id' => $image['product_id']]);
    }

    echo json_encode([
        "success" => true,
        "message" => "Image deleted successfully"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
