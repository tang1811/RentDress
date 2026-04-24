<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

include_once '../config/database.php';
include_once '../config/auth.php';

$database = new Database();
$db = $database->getConnection();

// ✅ FIX #2: ต้อง login ก่อนจึงจะจองได้
$authUser = requireAuth($db);

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->product_id) || !isset($data->pickup_date) || !isset($data->return_date)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required fields: product_id, pickup_date, return_date"]);
    exit;
}

// ใช้ user_id จาก token แทน (ปลอดภัยกว่ารับจาก client)
$userId = $authUser['id'];

// Validate dates
if ($data->pickup_date > $data->return_date) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "วันรับชุดต้องไม่เกินวันคืนชุด"]);
    exit;
}

if ($data->pickup_date < date('Y-m-d')) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ไม่สามารถจองวันในอดีตได้"]);
    exit;
}

try {
    // ✅ FIX #4: ใช้ positional ? แทน named params ซ้ำ
    $checkQuery = "SELECT p.*,
                   (SELECT COUNT(*) FROM bookings b
                    WHERE b.product_id = p.id
                    AND b.status NOT IN ('cancelled', 'completed')
                    AND b.pickup_date <= ?
                    AND DATE_ADD(b.return_date, INTERVAL p.buffer_days DAY) >= ?
                   ) as booking_conflict
                   FROM products p WHERE p.id = ?";

    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$data->return_date, $data->pickup_date, $data->product_id]);
    $product = $checkStmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "ไม่พบชุดนี้"]);
        exit;
    }

    if ($product['status'] !== 'available') {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "ชุดนี้ไม่พร้อมให้เช่าในขณะนี้"]);
        exit;
    }

    if ($product['booking_conflict'] > 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "ชุดนี้ถูกจองในช่วงวันที่เลือกแล้ว"]);
        exit;
    }

    $pickupDate  = new DateTime($data->pickup_date);
    $returnDate  = new DateTime($data->return_date);
    $days        = $returnDate->diff($pickupDate)->days + 1;
    $totalPrice  = $product['rental_price'] * $days;
    $depositAmount = $product['deposit_price'];

    $stmt = $db->prepare("INSERT INTO bookings (user_id, product_id, pickup_date, return_date, total_price, deposit_amount, status, notes)
                          VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([
        $userId,
        $data->product_id,
        $data->pickup_date,
        $data->return_date,
        $totalPrice,
        $depositAmount,
        $data->notes ?? ''
    ]);

    $bookingId = $db->lastInsertId();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "จองสำเร็จ",
        "data"    => [
            "id"             => $bookingId,
            "total_price"    => $totalPrice,
            "deposit_amount" => $depositAmount,
            "rental_days"    => $days
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
