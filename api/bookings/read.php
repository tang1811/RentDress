<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

include_once '../config/database.php';
include_once '../config/auth.php';

$database = new Database();
$db = $database->getConnection();

// ✅ FIX: ต้อง login
$authUser = requireAuth($db);

try {
    if ($authUser['role'] === 'admin' && isset($_GET['all'])) {
        // Admin ดูได้ทั้งหมด
        $query = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                         p.name as product_name, p.image_url as product_image
                  FROM bookings b
                  JOIN users u ON b.user_id = u.id
                  JOIN products p ON b.product_id = p.id
                  ORDER BY b.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
    } else {
        // User เห็นเฉพาะของตัวเอง
        $query = "SELECT b.*, p.name as product_name, p.image_url as product_image
                  FROM bookings b
                  JOIN products p ON b.product_id = p.id
                  WHERE b.user_id = ?
                  ORDER BY b.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$authUser['id']]);
    }

    $bookings = $stmt->fetchAll();
    http_response_code(200);
    echo json_encode(["success" => true, "data" => $bookings]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
