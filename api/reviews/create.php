<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->user_id) || !isset($data->product_id) ||
    !isset($data->booking_id) || !isset($data->rating)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields: user_id, product_id, booking_id, rating"
    ]);
    exit;
}

// Validate rating
if ($data->rating < 1 || $data->rating > 5) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Rating must be between 1 and 5"
    ]);
    exit;
}

try {
    // Check if booking exists and belongs to user
    $checkQuery = "SELECT id, status FROM bookings
                   WHERE id = :booking_id AND user_id = :user_id AND product_id = :product_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([
        ':booking_id' => $data->booking_id,
        ':user_id' => $data->user_id,
        ':product_id' => $data->product_id
    ]);
    $booking = $checkStmt->fetch();

    if (!$booking) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Booking not found or does not belong to this user"
        ]);
        exit;
    }

    if (!in_array($booking['status'], ['returned', 'completed'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Can only review after returning the dress"
        ]);
        exit;
    }

    // Check if already reviewed
    $reviewCheckQuery = "SELECT id FROM reviews WHERE booking_id = :booking_id";
    $reviewCheckStmt = $db->prepare($reviewCheckQuery);
    $reviewCheckStmt->execute([':booking_id' => $data->booking_id]);

    if ($reviewCheckStmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "You have already reviewed this booking"
        ]);
        exit;
    }

    // Create review
    $query = "INSERT INTO reviews (user_id, product_id, booking_id, rating, comment, image_url)
              VALUES (:user_id, :product_id, :booking_id, :rating, :comment, :image_url)";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':user_id' => $data->user_id,
        ':product_id' => $data->product_id,
        ':booking_id' => $data->booking_id,
        ':rating' => $data->rating,
        ':comment' => $data->comment ?? '',
        ':image_url' => $data->image_url ?? ''
    ]);

    $reviewId = $db->lastInsertId();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Review created successfully",
        "data" => [
            "id" => $reviewId
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
