<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->name) || !isset($data->category) || !isset($data->size) ||
    !isset($data->rental_price) || !isset($data->deposit_price)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields: name, category, size, rental_price, deposit_price"
    ]);
    exit;
}

try {
    $query = "INSERT INTO products (name, description, category, size, color, rental_price, deposit_price, image_url, status, buffer_days)
              VALUES (:name, :description, :category, :size, :color, :rental_price, :deposit_price, :image_url, :status, :buffer_days)";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':name' => $data->name,
        ':description' => $data->description ?? '',
        ':category' => $data->category,
        ':size' => $data->size,
        ':color' => $data->color ?? '',
        ':rental_price' => $data->rental_price,
        ':deposit_price' => $data->deposit_price,
        ':image_url' => $data->image_url ?? '',
        ':status' => $data->status ?? 'available',
        ':buffer_days' => $data->buffer_days ?? 2
    ]);

    $lastId = $db->lastInsertId();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Product created successfully",
        "id" => $lastId
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
