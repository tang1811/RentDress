<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));
$id = isset($data->id) ? $data->id : (isset($_GET['id']) ? $_GET['id'] : null);

if (!$id) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Product ID is required"
    ]);
    exit;
}

try {
    // Check if product has active bookings
    $checkQuery = "SELECT COUNT(*) as count FROM bookings
                   WHERE product_id = :id
                   AND status NOT IN ('completed', 'cancelled')";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([':id' => $id]);
    $result = $checkStmt->fetch();

    if ($result['count'] > 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Cannot delete product with active bookings"
        ]);
        exit;
    }

    $query = "DELETE FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Product deleted successfully"
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found"
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
