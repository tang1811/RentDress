<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/database.php';

// สร้างโฟลเดอร์ถ้ายังไม่มี
$uploadDir = '../../uploads/products/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ตรวจสอบว่ามีไฟล์ถูกอัพโหลดหรือไม่
if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "No files uploaded"
    ]);
    exit;
}

$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

$uploadedFiles = [];
$errors = [];

$files = $_FILES['images'];
$fileCount = count($files['name']);

for ($i = 0; $i < $fileCount; $i++) {
    $fileName = $files['name'][$i];
    $fileTmpName = $files['tmp_name'][$i];
    $fileSize = $files['size'][$i];
    $fileType = $files['type'][$i];
    $fileError = $files['error'][$i];

    // ตรวจสอบ error
    if ($fileError !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading: $fileName";
        continue;
    }

    // ตรวจสอบประเภทไฟล์
    if (!in_array($fileType, $allowedTypes)) {
        $errors[] = "Invalid file type: $fileName (allowed: jpg, png, gif, webp)";
        continue;
    }

    // ตรวจสอบขนาดไฟล์
    if ($fileSize > $maxFileSize) {
        $errors[] = "File too large: $fileName (max 5MB)";
        continue;
    }

    // สร้างชื่อไฟล์ใหม่
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('dress_') . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $newFileName;

    // อัพโหลดไฟล์
    if (move_uploaded_file($fileTmpName, $targetPath)) {
        $imageUrl = '/uploads/products/' . $newFileName;
        $uploadedFiles[] = [
            'filename' => $newFileName,
            'url' => $imageUrl
        ];

        // ถ้ามี product_id ให้บันทึกลงฐานข้อมูล
        if ($productId) {
            try {
                $database = new Database();
                $db = $database->getConnection();

                // ตรวจสอบว่าเป็นรูปแรกหรือไม่
                $checkQuery = "SELECT COUNT(*) as count FROM product_images WHERE product_id = :product_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->execute([':product_id' => $productId]);
                $isPrimary = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'] == 0 ? 1 : 0;

                $query = "INSERT INTO product_images (product_id, image_url, is_primary, sort_order)
                          VALUES (:product_id, :image_url, :is_primary, :sort_order)";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':product_id' => $productId,
                    ':image_url' => $imageUrl,
                    ':is_primary' => $isPrimary,
                    ':sort_order' => $i
                ]);

                $uploadedFiles[count($uploadedFiles) - 1]['id'] = $db->lastInsertId();
                $uploadedFiles[count($uploadedFiles) - 1]['is_primary'] = $isPrimary;
            } catch (PDOException $e) {
                $errors[] = "Database error for $fileName: " . $e->getMessage();
            }
        }
    } else {
        $errors[] = "Failed to move file: $fileName";
    }
}

if (count($uploadedFiles) > 0) {
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Files uploaded successfully",
        "files" => $uploadedFiles,
        "errors" => $errors
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "No files were uploaded",
        "errors" => $errors
    ]);
}
