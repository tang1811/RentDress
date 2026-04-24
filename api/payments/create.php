<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->booking_id) || !isset($data->amount) || !isset($data->payment_type)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields: booking_id, amount, payment_type"
    ]);
    exit;
}

try {
    // Check if booking exists
    $checkQuery = "SELECT id, status FROM bookings WHERE id = :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([':id' => $data->booking_id]);
    $booking = $checkStmt->fetch();

    if (!$booking) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Booking not found"
        ]);
        exit;
    }

    // Create payment record
    $query = "INSERT INTO payments (booking_id, amount, payment_type, payment_method, payment_status)
              VALUES (:booking_id, :amount, :payment_type, :payment_method, :payment_status)";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':booking_id' => $data->booking_id,
        ':amount' => $data->amount,
        ':payment_type' => $data->payment_type,
        ':payment_method' => $data->payment_method ?? 'cash',
        ':payment_status' => $data->payment_status ?? 'completed'
    ]);

    $paymentId = $db->lastInsertId();

    // Update booking status based on payment type
    if ($data->payment_type === 'deposit' && $booking['status'] === 'pending') {
        $updateQuery = "UPDATE bookings SET status = 'confirmed' WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([':id' => $data->booking_id]);
    }

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Payment recorded successfully",
        "data" => [
            "id" => $paymentId
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
