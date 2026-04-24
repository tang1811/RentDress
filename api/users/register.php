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

if (!$data || !isset($data->name) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อ อีเมล และรหัสผ่าน"]);
    exit;
}

try {
    $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$data->email]);
    if ($checkStmt->fetch()) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "อีเมลนี้ถูกใช้งานแล้ว"]);
        exit;
    }

    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);
    $token = md5(uniqid(rand(), true));

    $phone   = isset($data->phone)   ? $data->phone   : '';
    $address = isset($data->address) ? $data->address : '';

    $stmt = $db->prepare(
        "INSERT INTO users (name, email, password, phone, address, role, session_token)
         VALUES (?, ?, ?, ?, ?, 'customer', ?)"
    );
    $stmt->execute([$data->name, $data->email, $hashedPassword, $phone, $address, $token]);

    $userId = $db->lastInsertId();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "สมัครสมาชิกสำเร็จ",
        "data" => [
            "user" => [
                "id"    => $userId,
                "name"  => $data->name,
                "email" => $data->email,
                "phone" => $phone,
                "role"  => "customer"
            ],
            "token" => $token
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
}