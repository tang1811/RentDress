<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "กรุณากรอกอีเมลและรหัสผ่าน"]);
    exit;
}

try {
    $stmt = $db->prepare("SELECT id, name, email, password, phone, address, role FROM users WHERE email = ?");
    $stmt->execute([$data->email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "ไม่พบอีเมลนี้ในระบบ"]);
        exit;
    }

    if (!password_verify($data->password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "รหัสผ่านไม่ถูกต้อง"]);
        exit;
    }

    unset($user['password']);

    // สร้าง token ใหม่ทุกครั้งที่ login
    $token = md5(uniqid(rand(), true));

    $updateStmt = $db->prepare("UPDATE users SET session_token = ? WHERE id = ?");
    $updateStmt->execute([$token, $user['id']]);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "เข้าสู่ระบบสำเร็จ",
        "data" => [
            "user"  => $user,
            "token" => $token
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
}