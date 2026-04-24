<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

include_once '../config/database.php';
include_once '../config/auth.php';

$database = new Database();
$db = $database->getConnection();

// ✅ FIX: ต้อง login ก่อนถึงจะอัปเดตสถานะได้
$authUser = requireAuth($db);

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Booking ID is required"]);
    exit;
}

try {
    // ดึงข้อมูล booking เพื่อตรวจสิทธิ์
    $checkStmt = $db->prepare("SELECT user_id, status FROM bookings WHERE id = ?");
    $checkStmt->execute([$data->id]);
    $booking = $checkStmt->fetch();

    if (!$booking) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "ไม่พบการจอง"]);
        exit;
    }

    // User ยกเลิกได้เฉพาะของตัวเอง และเฉพาะสถานะ pending เท่านั้น
    // Admin อัปเดตได้ทุกสถานะ
    if ($authUser['role'] !== 'admin') {
        if ($booking['user_id'] != $authUser['id']) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "ไม่มีสิทธิ์แก้ไขการจองนี้"]);
            exit;
        }
        if ($data->status !== 'cancelled') {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "ผู้ใช้ทั่วไปยกเลิกได้เท่านั้น"]);
            exit;
        }
        if ($booking['status'] !== 'pending') {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ไม่สามารถยกเลิกการจองที่ยืนยันแล้วได้"]);
            exit;
        }
    }

    $stmt = $db->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$data->status, $data->id]);

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "อัปเดตสำเร็จ"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
